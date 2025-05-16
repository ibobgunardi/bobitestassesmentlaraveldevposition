@props(['task', 'projectId'])

@php
// Map numeric priority to high/medium/low categories
$priorityMap = [
    'high' => [
        'range' => [67, 100],
        'bg' => 'bg-red-500',
        'text' => 'text-white',
        'border' => 'border-red-500',
        'label' => 'High'
    ],
    'medium' => [
        'range' => [34, 66],
        'bg' => 'bg-yellow-500',
        'text' => 'text-yellow-900',
        'border' => 'border-yellow-500',
        'label' => 'Medium'
    ],
    'low' => [
        'range' => [1, 33],
        'bg' => 'bg-green-500',
        'text' => 'text-white',
        'border' => 'border-green-500',
        'label' => 'Low'
    ]
];

// Convert priority to integer and ensure it's within 1-100 range
$priorityValue = min(100, max(1, (int)$task->priority));

// Determine priority level based on value
$priorityLevel = 'low';
$priorityLabel = '';
$priorityBg = '';
$priorityText = '';
$priorityBorder = '';

foreach ($priorityMap as $level => $data) {
    if ($priorityValue >= $data['range'][0] && $priorityValue <= $data['range'][1]) {
        $priorityLevel = $level;
        $priorityLabel = $data['label'];
        $priorityBg = $data['bg'];
        $priorityText = $data['text'];
        $priorityBorder = $data['border'];
        break;
    }
}

$isOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast();
@endphp

<div class="task-card group bg-white dark:bg-gray-800 rounded-xl border-l-4 {{ $priorityBorder }} shadow-sm hover:shadow-lg transition-all duration-200 cursor-move transform hover:-translate-y-0.5 relative overflow-hidden" 
    draggable="true" 
    data-task-id="{{ $task->id }}" 
    data-project-id="{{ $projectId }}">
    
    <!-- Priority indicator -->
    <div class="absolute top-0 right-0 w-2 h-full {{ $priorityBg }} opacity-10"></div>
    
    <div class="p-4">
        <!-- Header with title and menu -->
        <div class="flex items-center justify-between mb-2 relative" x-data="{ open: false }">
            <h4 class="font-semibold text-gray-800 dark:text-gray-100 text-sm leading-tight pr-4">
                {{ $task->title }}
            </h4>
            <div class="relative z-50">
                <button 
                    @click.stop="open = !open" 
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 focus:outline-none relative"
                    aria-haspopup="true"
                    :aria-expanded="open"
                    @click="
                        open = !open;
                        if (open) {
                            const rect = $el.getBoundingClientRect();
                            $nextTick(() => {
                                const dropdown = $refs.dropdown;
                                const dropdownRect = dropdown.getBoundingClientRect();
                                const viewportHeight = window.innerHeight;
                                
                                // Position the dropdown below the button
                                dropdown.style.top = `${rect.bottom + window.scrollY}px`;
                                dropdown.style.left = `${rect.right - dropdownRect.width + window.scrollX}px`;
                                
                                // Adjust if dropdown would go off screen
                                if (rect.bottom + dropdownRect.height > viewportHeight) {
                                    dropdown.style.top = `${rect.top - dropdownRect.height + window.scrollY}px`;
                                }
                                
                                if (rect.right - dropdownRect.width < 0) {
                                    dropdown.style.left = `${rect.left + window.scrollX}px`;
                                }
                            });
                        }
                    ">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                
                <!-- Dropdown menu -->
                <div 
                    x-show="open"
                    @click.stop
                    x-transition:enter="transition ease-out duration-100 transform"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75 transform"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-1 w-40 bg-white dark:bg-gray-800 rounded-md shadow-xl ring-1 ring-black ring-opacity-5 z-50"
                    style="transform-origin: top right; position: fixed;"
                    x-cloak
                    x-ref="dropdown"
                    x-on:click.away="open = false">
                    <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                        <button 
                            @click="
                                $dispatch('edit-task', { taskId: '{{ $task->id }}' });
                                open = false;
                            "
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                            role="menuitem">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </button>
                        <button 
                            @click="
                                if (confirm('Are you sure you want to delete this task?')) {
                                    $dispatch('delete-task', { taskId: '{{ $task->id }}' });
                                }
                                open = false;
                            "
                            class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30"
                            role="menuitem">
                            <i class="fas fa-trash-alt mr-2"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($task->description)
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 line-clamp-2">
            {{ $task->description }}
        </p>
        @endif

        <!-- Footer -->
        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
            <!-- Priority Badge -->
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityBg }} {{ $priorityText }}">
                  {{ $priorityLabel }}
                </span>
                @if($task->status)
                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                {{ $priorityValue }}
                </span>
                @endif
            </div>

            <!-- Due Date -->
            @if($task->due_date)
            <div class="flex items-center text-xs {{ $isOverdue ? 'text-red-500' : 'text-gray-500 dark:text-gray-400' }}">
                <i class="far fa-calendar-alt mr-1"></i>
                <span>{{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</span>
                @if($isOverdue)
                <span class="ml-1 px-1.5 py-0.5 text-xs rounded-full bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200">
                    Due
                </span>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
