<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Company;
use App\Models\AiRecommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WebController extends Controller
{
    /**
     * Show the dashboard page.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $tasks = \App\Models\Task::with('project')->orderBy('priority')->get();
        $taskLogs = \App\Models\TaskLog::with(['task.project', 'user'])->orderBy('created_at', 'desc')->get();
        return view('pages.dashboard', compact('tasks', 'taskLogs', ));
    }

    public function tasksIndex()
    {   

        $aiHistory = AiRecommendation::with('project')->orderBy('created_at', 'desc')->get();
        return view('pages.tasks.index', compact('aiHistory'));
    }

    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('pages.auth.login');
    }

    /**
     * Handle the login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();

                $user = Auth::user();

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

                // Check if it's an AJAX request
                if ($request->expectsJson() || $request->ajax() || $request->header('Content-Type') == 'application/json' || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                    return response()->json([
                        'success' => true,
                        'redirect' => route('dashboard'),
                        'message' => 'Login successful'
                    ]);
                }

                return redirect()->intended(route('dashboard'));
            }

            // Handle failed login for AJAX requests
            if ($request->expectsJson() || $request->ajax() || $request->header('Content-Type') == 'application/json' || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => trans('auth.failed'),
                    'errors' => [
                        'email' => [trans('auth.failed')]
                    ]
                ], 422);
            }

            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        } catch (ValidationException $e) {
            // Handle validation errors for AJAX requests
            if ($request->expectsJson() || $request->ajax() || $request->header('Content-Type') == 'application/json' || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors()
                ], 422);
            }

            throw $e;
        }
    }

    /**
     * Log the user out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // If this is an API request and user has a token, revoke it
        if ($request->bearerToken() && $request->user()) {
            $request->user()->currentAccessToken()->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logged out successfully'
                ]);
            }
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Generate a Sanctum token for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to get a token'
            ], 401);
        }

        $user = Auth::user();
        $deviceName = $request->input('device_name', $request->userAgent() ?? 'Web Client');

        // Create a token with abilities based on user role
        $abilities = ['*']; // Default to all abilities

        // Create the token
        $token = $user->createToken($deviceName, $abilities);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token->plainTextToken
            ],
            'message' => 'Token generated successfully'
        ]);
    }

    /**
     * Show user information.
     *
     * @return \Illuminate\View\View
     */
    public function userInfo()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);

        return view('auth.user-info', compact('user', 'company'));
    }

    /**
     * Show the projects index page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function projectsIndex(Request $request)
    {
        $query = Project::query();

        // Filter by company if user has a company
        if (Auth::user()->company_id) {
            $query->where('company_id', Auth::user()->company_id);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sort options
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSortFields = ['name', 'status', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $projects = $query->withCount('tasks')->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.projects._project_list', compact('projects'))->render()
            ]);
        }

        return view('pages.projects.index', compact('projects'));
    }

    /**
     * Show a specific project.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function projectShow($id)
    {
        $project = Project::with(['tasks' => function($query) {
            $query->orderBy('order');
        }])->findOrFail($id);

        // Check if user has access to this project
        if (Auth::user()->company_id && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'You do not have access to this project');
        }

        return view('pages.projects.show', compact('project'));
    }

    /**
     * Show the tasks index page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    // public function tasksIndex(Request $request)
    // {

    //     return view('pages.tasks.index');
    // }

    /**
     * Show a specific task.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function taskShow($id)
    {
        $task = Task::with('project')->findOrFail($id);

        // Check if user has access to this task's project
        if (Auth::user()->company_id && $task->project->company_id !== Auth::user()->company_id) {
            abort(403, 'You do not have access to this task');
        }

        return view('pages.tasks.show', compact('task'));
    }

    // Task-related methods - view only

    /**
     * Show task creation form.
     *
     * @return \Illuminate\View\View
     */
    public function taskCreate()
    {
        $projects = Project::when(Auth::user()->company_id, function($query) {
            return $query->where('company_id', Auth::user()->company_id);
        })->get();

        return view('pages.tasks.create', compact('projects'));
    }

    /**
     * Show task edit form.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function taskEdit($id)
    {
        $task = Task::with('project')->findOrFail($id);

        // Check if user has access to this task's project
        if (Auth::user()->company_id && $task->project->company_id !== Auth::user()->company_id) {
            abort(403, 'You do not have access to this task');
        }

        $projects = Project::when(Auth::user()->company_id, function($query) {
            return $query->where('company_id', Auth::user()->company_id);
        })->get();

        return view('pages.tasks.edit', compact('task', 'projects'));
    }

    // Project-related methods - view only

    /**
     * Show project creation form.
     *
     * @return \Illuminate\View\View
     */
    public function projectCreate()
    {
        $companies = Company::all();
        return view('pages.projects.create', compact('companies'));
    }

    /**
     * Show project edit form.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function projectEdit($id)
    {
        $project = Project::findOrFail($id);

        // Check if user has access to this project
        if (Auth::user()->company_id && $project->company_id !== Auth::user()->company_id) {
            abort(403, 'You do not have access to this project');
        }

        $companies = Company::all();
        return view('pages.projects.edit', compact('project', 'companies'));
    }

    // AI Recommendation-related methods - view only

    /**
     * Show AI recommendation form.
     *
     * @return \Illuminate\View\View
     */
    public function aiRecommendationsForm()
    {
        $projects = Project::when(Auth::user()->company_id, function($query) {
            return $query->where('company_id', Auth::user()->company_id);
        })->get();

        return view('pages.ai-recommendations.form', compact('projects'));
    }

    /**
     * Show AI recommendation results.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function aiRecommendationsResults($id)
    {
        // This would be a view-only page to display recommendation results
        return view('pages.ai-recommendations.results', compact('id'));
    }

    /**
     * Show the API test page.
     *
     * @return \Illuminate\View\View
     */
    public function apiTest()
    {
        return view('auth.api-test');
    }
}