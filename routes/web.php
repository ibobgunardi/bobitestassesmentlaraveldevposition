<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;
use App\Http\Controllers\API\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication routes - only view/redirect operations
Route::get('/login', [WebController::class, 'showLoginForm'])->name('login');
Route::get('/api-test', [WebController::class, 'apiTest'])->name('api.test')->middleware('auth');

// Home route (redirects to dashboard)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard route
Route::get('/dashboard', [WebController::class, 'dashboard'])->name('dashboard')->middleware('auth');

// Web routes - protected by auth middleware - only view/redirect operations
// Logout route
Route::post('/logout', [WebController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // User routes - only view operations
    Route::get('/user', [WebController::class, 'userInfo'])->name('user.info');
    
    // Task routes - view only
    Route::get('/tasks', [WebController::class, 'tasksIndex'])->name('tasks.index');
    Route::get('/tasks/create', [WebController::class, 'taskCreate'])->name('tasks.create');
    Route::get('/tasks/{task}/edit', [WebController::class, 'taskEdit'])->name('tasks.edit');
    Route::get('/tasks/{task}', [WebController::class, 'taskShow'])->name('tasks.show');
    Route::post('/tasks/{task}/reorder', [WebController::class, 'taskReorder'])->name('tasks.reorder');
    
    // Project routes - view only
    Route::get('/projects', [WebController::class, 'projectsIndex'])->name('projects.index');
    Route::get('/projects/create', [WebController::class, 'projectCreate'])->name('projects.create');
    Route::get('/projects/{project}/edit', [WebController::class, 'projectEdit'])->name('projects.edit');
    Route::get('/projects/{project}', [WebController::class, 'projectShow'])->name('projects.show');
    
    // AI Recommendation routes - view only
    Route::get('/ai-recommendations/form', [WebController::class, 'aiRecommendationsForm'])->name('ai-recommendations.form');
    Route::get('/ai-recommendations/{id}/results', [WebController::class, 'aiRecommendationsResults'])->name('ai-recommendations.results');
    
    // Note: All data operations are handled by API endpoints in routes/api.php
    // Frontend JavaScript should use AJAX to call these API endpoints directly
});
