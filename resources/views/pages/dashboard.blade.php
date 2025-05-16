@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-8">
        <h1 class="text-2xl font-bold text-white mb-2">Welcome back, {{ Auth::user()->name }}!</h1>
        <p class="text-blue-100">Use the navigation bar to search and explore.</p>
    </div>



    <!-- Task Activity Log -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <i class="fas fa-history text-blue-500 mr-2"></i>
                Task Activity Log
                <span class="ml-2 px-2 py-1 text-xs font-semibold bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">
                    Live Updates
                </span>
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Recent task activities will appear here in real-time<span class="text-xs text-gray-400 dark:text-gray-500"><br><i class="fas fa-info-circle text-blue-500 mr-2"></i>Make sure artisan queue:work is running</span></p>
        </div>
        <div id="tasks-log" class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto">
            <!-- Log entries will be added here dynamically -->
            @foreach($taskLogs as $taskLog) 
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors fade-in">
               <div class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-lg transition-colors">
                    <!-- Icon Container -->
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="h-9 w-9 rounded-full bg-blue-100 dark:bg-blue-900/70 flex items-center justify-center">
                            <i class="fas fa-arrows-alt-v text-blue-600 dark:text-blue-400 text-sm"></i>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <!-- Header Row -->
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ $taskLog->user->name }} reordered task: 
                                <span class="font-semibold">{{ $taskLog->task->title || 'Untitled Task' }}</span>
                            </p>
                            <span class="text-xs text-gray-400 dark:text-gray-500 font-mono bg-gray-100 dark:bg-gray-700/50 px-2 py-0.5 rounded-full">
                                {{ $taskLog->created_at }}
                            </span>
                        </div>
                        
                        <!-- Priority Change -->
                        <div class="mt-1.5 flex items-center text-sm text-gray-600 dark:text-gray-300">
                            <span class="inline-flex items-center">
                                Priority changed from
                                <span class="font-medium ml-1 px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200">
                                    {{ $taskLog->old_values['priority'] }}
                                </span>
                            </span>
                            <i class="fas fa-arrow-right mx-2 text-xs text-gray-400"></i>
                            <span class="inline-flex items-center">
                                to
                                <span class="font-medium ml-1 px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200">
                                    {{ $taskLog->new_values['priority'] }}
                                </span>
                            </span>
                        </div>
                        
                      
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<!-- Laravel Echo and Pusher -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.min.js"></script>
<!-- Add Laravel Echo configuration -->
<script>
    // Initialize Pusher
    window.Pusher = Pusher;
    
    // Initialize Echo with Pusher's WebSocket service
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ config('broadcasting.connections.pusher.key') }}',
        wsHost: `ws-{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}.pusher.com`,
        wsPort: 80,
        wssPort: 443,
        forceTLS: true,
        encrypted: true,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}',
        auth: {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }
    });
    
    // Handle connection state changes
    window.Echo.connector.pusher.connection.bind('state_change', function(states) {
        console.log('Pusher connection state changed:', states);
    });
    
    // Handle connection errors
    window.Echo.connector.pusher.connection.bind('error', function(err) {
        console.error('Pusher connection error:', err);
        });
    
    // Log when Echo is ready
    console.log('Echo initialized successfully');
    
    // Start setting up Echo listeners when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const tasksLog = document.getElementById('tasks-log');
        
        
        // Function to set up Echo listeners
        function setupEchoListeners() {
            console.log('Setting up Echo listeners...');
            
            // Wait for Echo to be ready
            if (typeof window.Echo === 'undefined') {
                window.addEventListener('echo:ready', function() {
                    if (window.Echo) {
                        subscribeToTaskUpdates();
                    }
                });
            } else {
                subscribeToTaskUpdates();
            }
        }
        
        // Function to subscribe to task updates
        function subscribeToTaskUpdates() {
            console.log('Subscribing to task-updates channel...');
            
            try {
                // Join the presence channel
                const channel = window.Echo.join('task-updates');
                
                // Handle successful subscription
                channel.here(users => {
                    console.log('Users currently in channel:', users);
                    updateOnlineUsers(users);
                });
                
                // Handle new users joining
                channel.joining(user => {
                    console.log('User joined:', user);
                    showToast(`${user.name || 'A user'} joined the channel`, 'info');
                });
                
                // Handle users leaving
                channel.leaving(user => {
                    console.log('User left:', user);
                    showToast(`${user.name || 'A user'} left the channel`, 'warning');
                });
                
                // Listen for task reordered events
                channel.listen('.task.reordered', (e) => {
                    try {
                        console.log('Received task.reordered event:', e);
                        
                        const task = e.task || {};
                        const oldPriority = e.old_priority || 'N/A';
                        const newPriority = e.new_priority || 'N/A';
                        const userName = e.user_name || 'Someone';
                        
                        console.log('Processing task reorder:', {
                            taskId: task.id,
                            taskTitle: task.title,
                            oldPriority,
                            newPriority,
                            userName
                        });
                        
                        // Add to task log
                        addTaskLogEntry({
                            task,
                            oldPriority,
                            newPriority,
                            userName
                        });
                        
                        // Show a toast notification
                        showToast(`Task "${task.title || 'Untitled'}" priority changed from ${oldPriority} to ${newPriority}`, 'success');
                        
                        // Optionally refresh the task list if needed
                        // window.location.reload();
                    } catch (error) {
                        console.error('Error handling task.reordered event:', error);
                        showToast('Error updating task list: ' + error.message, 'error');
                    }
                });
                
                console.log('Successfully subscribed to task-updates channel and listening for events');
                
                // Store the channel for later use
                window.taskUpdatesChannel = channel;
                
                console.log('Successfully subscribed to task-updates channel');
                
                // Add initial message
                const initialMessage = document.createElement('div');
                initialMessage.className = 'p-4 text-sm text-gray-500 dark:text-gray-400 text-center';
                initialMessage.textContent = 'Listening for task updates...';
                tasksLog.appendChild(initialMessage);
                
            } catch (error) {
                console.error('Error subscribing to channel:', error);
                showError('Error setting up real-time updates. Please refresh the page.');
            }
        }
        
        // Helper function to update online users list
        function updateOnlineUsers(users) {
            const onlineUsersContainer = document.getElementById('online-users');
            if (!onlineUsersContainer) return;
            
            if (users.length === 0) {
                onlineUsersContainer.innerHTML = '<div class="text-sm text-gray-500 dark:text-gray-400">No other users online</div>';
                return;
            }
            
            const usersHtml = users.map(user => `
                <div class="flex items-center py-2 px-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <div class="h-2 w-2 rounded-full bg-green-500 mr-2"></div>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">${user.name || 'Anonymous'}</span>
                </div>
            `).join('');
            
            onlineUsersContainer.innerHTML = usersHtml;
        }
        
        // Helper function to show toast notifications
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) return;
            
            const toast = document.createElement('div');
            const types = {
                'success': 'bg-green-800 border-white text-white',
                'error': 'bg-red-800 border-white text-white',
                'warning': 'bg-yellow-800 border-white text-white',
                'info': 'bg-blue-800 border-white text-white'
            };
            
            const typeClass = types[type] || types['info'];
            
            toast.className = `border-l-4 p-4 mb-2 rounded-r-lg shadow-lg ${typeClass} dark:bg-opacity-10 dark:border-opacity-50 dark:text-opacity-90`;
            toast.textContent = message;
            
            toastContainer.appendChild(toast);
            
            // Auto-remove toast after 5 seconds
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
        
        // Start the process
        setupEchoListeners();
        
        function addTaskLogEntry({ task, oldPriority, newPriority, userName }) {
            const logEntry = document.createElement('div');
            logEntry.className = 'p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors fade-in';
            
            const timestamp = new Date().toLocaleTimeString();
            
            logEntry.innerHTML = `
               <div class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-lg transition-colors">
                    <!-- Icon Container -->
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="h-9 w-9 rounded-full bg-blue-100 dark:bg-blue-900/70 flex items-center justify-center">
                            <i class="fas fa-arrows-alt-v text-blue-600 dark:text-blue-400 text-sm"></i>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <!-- Header Row -->
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                ${userName} reordered task: 
                                <span class="font-semibold">${task.title || 'Untitled Task'}</span>
                            </p>
                            <span class="text-xs text-gray-400 dark:text-gray-500 font-mono bg-gray-100 dark:bg-gray-700/50 px-2 py-0.5 rounded-full">
                                ${timestamp}
                            </span>
                        </div>
                        
                        <!-- Priority Change -->
                        <div class="mt-1.5 flex items-center text-sm text-gray-600 dark:text-gray-300">
                            <span class="inline-flex items-center">
                                Priority changed from
                                <span class="font-medium ml-1 px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200">
                                    ${oldPriority}
                                </span>
                            </span>
                            <i class="fas fa-arrow-right mx-2 text-xs text-gray-400"></i>
                            <span class="inline-flex items-center">
                                to
                                <span class="font-medium ml-1 px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200">
                                    ${newPriority}
                                </span>
                            </span>
                        </div>
                        
                        
                    </div>
                </div>
            `;
            
      
            
            // Remove the initial message if it exists
            const initialMessage = tasksLog.querySelector('.text-center');
            if (initialMessage) {
                tasksLog.removeChild(initialMessage);
            }
            
            // Add the new log entry at the top
            if (tasksLog.firstChild) {
                tasksLog.insertBefore(logEntry, tasksLog.firstChild);
            } else {
                tasksLog.appendChild(logEntry);
            }
            
            // Keep only the last 50 log entries
            while (tasksLog.children.length > 50) {
                tasksLog.removeChild(tasksLog.lastChild);
            }
        }
        
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'p-4 text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg';
            errorDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            tasksLog.appendChild(errorDiv);
        }
    });
</script>
@endpush
@endsection
