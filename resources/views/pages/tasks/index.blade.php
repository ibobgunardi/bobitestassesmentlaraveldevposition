@extends('layouts.app')

@section('full-width', true)
@section('content')

    <div class="min-h-screen bg-gray-50 transition-colors duration-200">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 gap-6">
                <!-- Sticky Action Button -->
                <div class="fixed bottom-8 right-8 z-40" x-data="{ actionsExpanded: false, showNewTaskModal: false, showNewProjectModal: false }">
                    <!-- Dropdown Menu -->
                    <div class="absolute bottom-full right-0 mb-4 w-48 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden transition-all duration-200 transform origin-bottom-right"
                        x-show="actionsExpanded" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95" @click.away="actionsExpanded = false">
                        <button class="w-full btn btn-sm btn-outline-primary flex items-center gap-2">
                            <i class="fas fa-plus-circle mr-1"></i>
                            New Task
                        </button>
                        <button class="w-full btn btn-sm btn-outline-primary flex items-center gap-2">
                            <i class="far fa-copy mr-1"></i>
                            New Project
                        </button>
                    </div>
                    <!-- Main Button -->
                    <button @click="actionsExpanded = !actionsExpanded"
                        class="w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-lg flex items-center justify-center transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i x-show="!actionsExpanded" class="fas fa-plus text-xl"></i>
                        <i x-show="actionsExpanded" class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Main Content with Alpine.js Tabs -->
                <div class="w-full flex flex-col min-h-[calc(100vh-10rem)]" x-data="{
                    activeTab: 'project',
                    clearFilters() {
                        const searchInput = document.getElementById('task-search');
                        const projectFilter = document.getElementById('project-filter');
                        const searchInputMobile = document.getElementById('task-search-mobile');
                        const projectFilterMobile = document.getElementById('project-filter-mobile');
                
                        if (searchInput) searchInput.value = '';
                        if (projectFilter) projectFilter.value = '';
                        if (searchInputMobile) searchInputMobile.value = '';
                        if (projectFilterMobile) projectFilterMobile.value = '';
                
                        if (typeof filterTasks === 'function') {
                            filterTasks();
                        }
                    }
                }">
                    <!-- Tab Navigation with Search and Actions -->
                    <div class="mb-6">
                        <!-- Mobile View - Stacked -->
                        <div class="sm:hidden space-y-3">
                            <!-- Tabs -->
                            <div class="border-b border-gray-200">
                                <nav class="flex space-x-2 overflow-x-auto pb-1 hide-scrollbar" aria-label="Tabs">
                                    <button @click="activeTab = 'project';"
                                        :class="[
                                            'inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-200',
                                            activeTab === 'project' ?
                                            'bg-blue-50 text-blue-700 border border-blue-200 shadow-sm' :
                                            'text-gray-600 hover:bg-gray-50 border border-transparent hover:border-gray-300'
                                        ]">
                                        <i class="fas fa-project-diagram mr-2 text-sm"></i>
                                        <span>Project View</span>
                                    </button>
                                    <button @click="activeTab = 'all';"
                                        :class="[
                                            'inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-200',
                                            activeTab === 'all' ?
                                            'bg-blue-50 text-blue-700 border border-blue-200 shadow-sm' :
                                            'text-gray-600 hover:bg-gray-50 border border-transparent hover:border-gray-300'
                                        ]">
                                        <i class="fas fa-tasks mr-2 text-sm"></i>
                                        <span>All Tasks</span>
                                    </button>
                                </nav>
                            </div>

                            <!-- Search and Filter -->
                            <div class="space-y-3">
                                <div class="relative">
                                    <input type="text" id="task-search-mobile" class="w-full form-input"
                                        placeholder="Search tasks...">
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <select id="project-filter-mobile" class="w-full form-select">
                                        <option value="">Loading projects...</option>
                                    </select>
                                    <button id="clear-filters-mobile" class="w-full btn btn-outline-secondary">
                                        Clear
                                    </button>
                                    <button id="ai-recommendation-btn-mobile" type="button"
                                        class="w-full btn btn-primary bg-blue-600 hover:bg-blue-700 text-white">
                                        <i class="fas fa-robot mr-2"></i> AI Recommendation
                                    </button>
                                </div>
                            </div>

                        </div>

                        <!-- Desktop View - Side by Side -->
                        <div class="hidden sm:flex items-center justify-between">
                            <!-- Tabs - Centered -->
                            <div class="flex-1 flex justify-center">
                                <div class="border-b border-gray-200">
                                    <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                                        <button @click="activeTab = 'project'; "
                                            :class="[
                                                'inline-flex items-center px-5 py-2 text-sm font-medium rounded-t-md transition-all duration-200 border-b-2',
                                                activeTab === 'project' ?
                                                'border-blue-500 text-blue-600 bg-white' :
                                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                            ]">
                                            <i class="fas fa-project-diagram mr-2 text-sm"></i>
                                            <span>Project View</span>
                                        </button>
                                        <button @click="activeTab = 'all'; "
                                            :class="[
                                                'inline-flex items-center px-5 py-2 text-sm font-medium rounded-t-md transition-all duration-200 border-b-2',
                                                activeTab === 'all' ?
                                                'border-blue-500 text-blue-600 bg-white' :
                                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                            ]">
                                            <i class="fas fa-tasks mr-2 text-sm"></i>
                                            <span>All Tasks</span>
                                        </button>
                                    </nav>
                                </div>
                            </div>

                            <!-- Search and Filter - Right Aligned -->
                            <div class="flex items-center space-x-3">
                                <div class="relative w-48">
                                    <input type="text" id="task-search" class="w-full form-control text-sm"
                                        placeholder="Search tasks...">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <select id="project-filter" class="block w-40 form-control text-sm">
                                        <option value="">Loading projects...</option>
                                    </select>
                                    <button id="clear-filters" class="inline-flex btn btn-outline-secondary">
                                        Clear
                                    </button>
                                    <button id="ai-recommendation-btn"
                                        class="w-full btn btn-primary flex items-center justify-center">
                                        <i class="fas fa-robot mr-1"></i>
                                        <span>AI Assist</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Tab Content -->
                    <div class="flex-1 flex flex-col h-[calc(100vh-180px)]">
                        <!-- Project Tab Content -->
                        <div x-show="activeTab === 'project'" class="h-full flex flex-col tab-content-container">
                            <!-- Loading indicator -->
                            <div id="error" class="text-center py-6 hidden bg-red-100 text-red-800 rounded-lg m-4">
                            </div>
                            <div
                                class="project-groups-container flex-1 flex flex-nowrap gap-4 p-4 overflow-x-auto overflow-y-hidden">

                                <div id="project-view-container"
                                    class="project-groups-container flex-1 flex flex-nowrap gap-4 p-4 overflow-x-auto overflow-y-hidden">
                                    <!-- Project columns will be dynamically inserted here -->
                                </div>
                            </div>
                        </div>

                        <!-- All Tasks Tab Content -->
                        <div x-show="activeTab === 'all'" class="h-full flex flex-col tab-content-container">
                            <div class="all-tasks-container flex-1 flex flex-col overflow-hidden">
                                <!-- Loading indicator -->
                                <div id="loading" class="text-center py-6 hidden">
                                    <i class="fas fa-spinner fa-spin text-blue-500 text-xl"></i>
                                    <p class="mt-2 text-gray-600">Loading tasks...</p>
                                </div>
                                <!-- Error message -->
                                <div id="error" class="text-center py-6 hidden bg-red-100 text-red-800 rounded-lg m-4">
                                </div>
                                <!-- Tasks container -->
                                <div class="flex-1 overflow-y-auto p-4">
                                    <div id="tasks-container"
                                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 min-h-min">
                                        @foreach ($aiHistory as $history)
                                            <div class="border-b border-gray-200 dark:border-gray-700 py-3">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $history->reasoning_model }} → {{ $history->result_model }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            {{ $history->message }}</p>
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $history->created_at->format('Y-m-d') }}</div>
                                                </div>
                                                <div class="mt-2">
                                                     <div class="flex justify-between items-center">
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                            {{ $history->project_name ?? 'No Project' }}
                                                        </span>
                                                        <button class="toggle-details text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                            View Details
                                                        </button>
                                                    </div>
                                                    <div
                                                        class="details-content hidden mt-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                            <div>
                                                                <h5 class="font-medium mb-2">Reasoning Analysis</h5>
                                                                <div class="text-sm whitespace-pre-line">
                                                                    {{ $history->reasoning_output }}</div>
                                                            </div>
                                                            <div>
                                                                <h5 class="font-medium mb-2">Execution Plan</h5>
                                                                <div class="text-sm whitespace-pre-line">
                                                                    {{ $history->result_output }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                                            Tokens used: {{ $history->reasoning_tokens }} (reasoning) +
                                                            {{ $history->result_tokens }} (result)
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- AI Recommendation Modal -->
                <div id="ai-recommendation-modal"
                    class="fixed inset-0 z-50 hidden bg-gray-50 bg-opacity-90 flex items-center justify-center">
                    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl mx-auto">
                        <div
                            class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <i class="fas fa-robot mr-2"></i> AI Recommendation
                            </h3>
                            <button id="close-ai-recommendation-modal"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl">&times;</button>
                        </div>
                        <div class="px-6 pt-2 pb-6">
                            <!-- Modal Tabs -->
                            <div class="mb-4 border-b border-gray-200 dark:border-gray-700 flex gap-2" id="ai-modal-tabs">
                                <button type="button"
                                    class="ai-modal-tab-btn px-4 py-2 text-sm font-medium rounded-t bg-blue-50 dark:bg-gray-800 text-blue-700 dark:text-blue-300 border border-b-0 border-blue-200 dark:border-gray-700 active"
                                    data-tab="get-ai-recommendation">
                                    Get AI Recommendation
                                </button>
                                <button type="button"
                                    class="ai-modal-tab-btn px-4 py-2 text-sm font-medium rounded-t text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 border border-b-0 border-gray-200 dark:border-gray-700"
                                    data-tab="ai-history">
                                    History
                                </button>
                            </div>
                            <!-- Tab Contents -->
                            <div id="ai-modal-tab-content-get-ai-recommendation" class="ai-modal-tab-content">
                                <form>
                                    <div class="mb-4">
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project</label>
                                        <select id="ai-project-select"
                                            class="block w-full form-control ai-project-select text-sm">
                                            <option value="">Loading projects...</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">AI
                                            Model for Reasoning</label>
                                        <select id="reasoning-model" class="form-select w-full">
                                            <option value="deepseek/deepseek-prover-v2:free">DeepSeek Prover (Reasoning)
                                            </option>
                                            <option value="tngtech/deepseek-r1t-chimera:free">DeepSeek R1T Chimera
                                                (Reasoning)</option>
                                            <option value="mistralai/mistral-nemo:free">Mistral Nemo (Reasoning)</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">AI
                                            Model for Result</label>
                                        <select id="result-model" class="form-select w-full">
                                            <option value="deepseek/deepseek-prover-v2:free">DeepSeek Prover (Result)
                                            </option>
                                            <option value="tngtech/deepseek-r1t-chimera:free">DeepSeek R1T Chimera (Result)
                                            </option>
                                            <option value="mistralai/mistral-nemo:free">Mistral Nemo (Result)</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">OpenRouter
                                            API Token</label>
                                        <input type="password"
                                            value="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"
                                            id="api-token" class="form-control ai-api-token w-full"
                                            placeholder="Enter your OpenRouter token">
                                    </div>
                                    <button type="button" id="get-recommendation-btn"
                                        class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white w-full">
                                        Get Recommendation
                                    </button>
                                    <div id="loading-indicator" class="mt-3 text-center hidden">
                                        <div
                                            class="inline-block animate-spin rounded-full h-6 w-6 border-t-2 border-b-2 border-blue-500">
                                        </div>
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Processing...</span>
                                    </div>
                                </form>
                            </div>
                            <div id="ai-modal-tab-content-ai-history" class="ai-modal-tab-content hidden"
                                style="max-height: 320px; overflow-y: auto;">
                                <!-- History content will go here -->
                                @if (!empty($aiHistory))
                                    @foreach ($aiHistory as $history)
                                        <div class="border-b border-gray-200 dark:border-gray-700 py-3">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $history->reasoning_model }} → {{ $history->result_model }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $history->message }}</p>
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $history->created_at->diffForHumans() }}</div>
                                            </div>
                                            <div class="mt-2">
                                                <button
                                                    class="toggle-details text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                    View Details
                                                </button>
                                                <div
                                                    class="details-content hidden mt-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div>
                                                            <h5 class="font-medium mb-2">Reasoning Analysis</h5>
                                                            <div class="text-sm whitespace-pre-line">
                                                                {{ $history->reasoning_output }}</div>
                                                        </div>
                                                        <div>
                                                            <h5 class="font-medium mb-2">Execution Plan</h5>
                                                            <div class="text-sm whitespace-pre-line">
                                                                {{ $history->result_output }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                                        Tokens used: {{ $history->reasoning_tokens }} (reasoning) +
                                                        {{ $history->result_tokens }} (result)
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-gray-400 py-12">No history yet.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>



    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/drag-drop.css') }}">
    @endpush

    @push('scripts')
        <script type="module" src="{{ asset('assets/js/pages/task_refactored.js') }}"></script>
    @endpush
@endsection
