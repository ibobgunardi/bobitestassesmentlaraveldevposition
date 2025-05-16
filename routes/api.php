<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Task\TaskController;
use App\Http\Controllers\API\Project\ProjectController;
use App\Http\Controllers\API\AI\AiRecommendationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// API Authentication Routes (Sanctum)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('web');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});

// Protected API routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Task API routes
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{task}', [TaskController::class, 'show']);
        Route::put('/{task}', [TaskController::class, 'update']);
        Route::delete('/{task}', [TaskController::class, 'destroy']);
        Route::post('/{task}/reorder', [TaskController::class, 'reorder']);
        
        // Task logs
        Route::get('/{task}/logs', [\App\Http\Controllers\API\Task\TaskLogController::class, 'index']);
    });
    
    // Global task logs (all tasks)
    Route::get('/task-logs', [\App\Http\Controllers\API\Task\TaskLogController::class, 'index']);

    // Project API routes
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::get('/{project}', [ProjectController::class, 'show']);
        Route::put('/{project}', [ProjectController::class, 'update']);
        Route::delete('/{project}', [ProjectController::class, 'destroy']);
    });

    // AI Recommendation API routes
    Route::prefix('ai-recommendation')->group(function () {
        Route::post('/', [AiRecommendationController::class, 'getRecommendations']);
        Route::get('/{id}/status', [AiRecommendationController::class, 'checkStatus']);
        Route::get('/history', [AiRecommendationController::class, 'getHistory']);
    });
});
