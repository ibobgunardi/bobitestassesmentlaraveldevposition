<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle an API login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Get the credentials from the request
            $credentials = $request->only('email', 'password');
            
            // Attempt to authenticate the user
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials are incorrect.'
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            // If user has no company_id, check if we should use a default from .env
            if (empty($user->company_id)) {
                $defaultCompanyId = env('DEFAULT_COMPANY_ID', 1);
                
                // Check if the company exists
                $company = Company::find($defaultCompanyId);
                if ($company) {
                    $user->company_id = $defaultCompanyId;
                    $user->save();
                }
            }

            // Use the device name from request or fallback to user agent
            $deviceName = $request->device_name ?? $request->userAgent() ?? 'Unknown Device';
            
            // Create a token with abilities based on user role
            $abilities = ['*']; // Default to all abilities
            
            // Create the token
            $token = $user->createToken($deviceName, $abilities);
            // Regenerate the session to prevent session fixation
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
                'redirect_url' => 'dashboard',
                'token' => $token->plainTextToken
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle an API logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success([], 'Logged out successfully');
    }

    /**
     * Get the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function user(Request $request)
    {
        $user = $request->user();
        
        // Load company if available
        if ($user->company_id) {
            $user->load('company');
        }
        
        return ApiResponse::success(['user' => $user], 'User data retrieved successfully');
    }
    
    /**
     * Refresh the user's token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refreshToken(Request $request)
    {
        // Revoke the current token
        $currentToken = $request->user()->currentAccessToken();
        $tokenName = $currentToken->name;
        $currentToken->delete();
        
        // Create a new token
        $token = $request->user()->createToken($tokenName);
        
        return ApiResponse::success([
            'token' => $token->plainTextToken
        ], 'Token refreshed successfully');
    }
}
