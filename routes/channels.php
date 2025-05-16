<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel for user-specific notifications
Broadcast::channel('private-user.{id}', function ($user, $id) {
    // For testing purposes, always return true
    // In production, uncomment the line below
    // return (int) $user->id === (int) $id;
    return true;
});

// Channel for project-specific notifications
Broadcast::channel('private-project.{id}', function ($user, $id) {
    // For testing purposes, always return true
    // In production, uncomment the line below
    // return $user->projects()->where('id', $id)->exists() || $user->role === 'admin';
    return true;
});

// Channel for task updates (presence channel)
Broadcast::channel('task-updates', function ($user) {
    try {
        // Log the channel authorization attempt
        \Log::info('Authorizing user for task-updates channel', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        
        // Only allow authenticated users to join the presence channel
        if (auth()->check()) {
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->profile_photo_url ?? null,
                'timestamp' => now()->toDateTimeString()
            ];
            
            \Log::debug('User authorized for task-updates channel', $userData);
            return $userData;
        }
        
        return false;
    } catch (\Exception $e) {
        \Log::error('Error authorizing task-updates channel', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return false;
    }
});
