// Main Entry Point for Task Management (Refactored)
import { fetchProjects, fetchTasks } from '../modules/api_tasks_refactored.js';
import { initializeDragAndDrop } from '../modules/dragDrop_refactored.js';
import { renderProjectView, displayTasks } from '../modules/taskRenderer_refactored.js';
import { setupFilters } from '../modules/filter_refactored.js';
import { openCreateProjectModal, closeCreateProjectModal } from '../modules/modal_refactored.js';
import { showToastr } from '../modules/showToastr.js';

document.addEventListener('DOMContentLoaded', async function () {

 // Use event delegation for toggle functionality
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('toggle-details')) {
            const detailsContent = e.target.nextElementSibling;
            detailsContent.classList.toggle('hidden');
            e.target.textContent = detailsContent.classList.contains('hidden') 
                ? 'View Details' 
                : 'Hide Details';
        }
    });
    
    const modal = document.getElementById('ai-recommendation-modal');
    document.getElementById('ai-recommendation-btn').onclick = () => modal.classList.remove('hidden');
    document.getElementById('close-ai-recommendation-modal').onclick = () => modal.classList.add('hidden');
    // Tab switching
    document.querySelectorAll('.ai-modal-tab-btn').forEach(btn => {
        btn.onclick = function () {
            console.log("this.dataset.tab");
            document.querySelectorAll('.ai-modal-tab-btn').forEach(tab => tab.classList.remove('active', 'bg-blue-50', 'text-blue-700'));
            this.classList.add('active', 'bg-blue-50', 'text-blue-700');
            document.querySelectorAll('.ai-modal-tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById('ai-modal-tab-content-' + this.dataset.tab).classList.remove('hidden');
        };
    });
    const projectSelects = [
        document.getElementById('project-filter-mobile'),
        document.getElementById('project-filter'),
        document.getElementById('ai-project-select')
    ].filter(el => el);
    if (projectSelects.length === 0) return;
    projectSelects.forEach(select => {
        select.innerHTML = '<option value="">Loading projects...</option>';
    });
    try {
        const data = await fetchProjects();
        projectSelects.forEach(select => {
            select.innerHTML = '<option value="">All Projects</option>';
            if (data.data && data.data.projects) {
                data.data.projects.forEach(project => {
                    const option = document.createElement('option');
                    option.value = project.id;
                    option.textContent = project.name;
                    select.appendChild(option);
                });
            }
        });
    } catch (error) {
        projectSelects.forEach(select => {
            select.innerHTML = '<option value="">Error loading projects</option>';
        });
        showToastr(error.message);
    }
    // Fetch and render tasks
    async function fetchAndRenderTasks(param = {}) {
        try {
            const data = await fetchTasks(param);
            const tasks = data.data?.tasks || data.tasks || [];
            const groupedTasks = data.additionalData?.grouped_tasks || [];
            displayTasks(tasks, document.getElementById('tasks-container'));
            renderProjectView(groupedTasks, document.getElementById('project-view-container'), () => initializeDragAndDrop(fetchAndRenderTasks));
        } catch (error) {
            showToastr(error.message);
        }
    }
    await fetchAndRenderTasks();
    // Set up drag and drop
    initializeDragAndDrop(fetchAndRenderTasks);
    // Set up filters (IDs must match your HTML)
    setupFilters('task-search', 'project-filter', 'clear-filters', (search, projectId) => {
        fetchAndRenderTasks({ search, projectId });
    });
    // Modal controls
    window.openCreateProjectModal = openCreateProjectModal;
    window.closeCreateProjectModal = closeCreateProjectModal;



    const getRecommendationButton = document.getElementById('get-recommendation-btn');
    const form = document.getElementById('ai-recommendation-form');
    const projectSelect = document.getElementById('ai-project-select');
    const aiApiToken = document.querySelector('.ai-api-token');
    const loadingIndicator = document.getElementById('loading-indicator');

    getRecommendationButton.addEventListener('click', function () {
        // Show loading indicator
        loadingIndicator.classList.remove('hidden');
        getRecommendationButton.disabled = true;

        // Get form data
        const projectId = projectSelect.value;
        const reasoningModel = document.getElementById('reasoning-model').value;
        const resultModel = document.getElementById('result-model').value;
        const apiToken = document.getElementById('api-token').value;

        // Validate form
        if (!projectId) {
            showToastr('Please select a project');
            loadingIndicator.classList.add('hidden');
            getRecommendationButton.disabled = false;
            return;
        }

        if (!apiToken) {
            showToastr('Please enter your OpenRouter API token');
            loadingIndicator.classList.add('hidden');
            getRecommendationButton.disabled = false;
            return;
        }

        // Prepare data for AJAX request
        const data = {
            project_id: projectId,
            reasoning_model: reasoningModel,
            result_model: resultModel,
            api_token: apiToken
        };

        // Perform AJAX POST request
        fetch('/api/ai-recommendation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
            .then(response => {
                if (!response.ok) {
                    // Handle HTTP errors (4xx, 5xx)
                    return response.json().then(errData => {
                        throw new Error(errData.message || 'Failed to generate recommendation');
                    }).catch(() => {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                // Validate response structure
                if (!data || !data.data) {
                    throw new Error('Invalid response structure from server');
                }


                // Add to history
                updateAIHistory(data);

                // Switch to history tab
                const historyTabBtn = document.querySelector('[data-tab="ai-history"]');
                if (historyTabBtn) {
                    historyTabBtn.click();
                }

                // Show success message
                showToastr(data.message || 'Recommendation generated successfully!', 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                showToastr(error.message || 'Error generating recommendation. Please try again.', 'error');
            })
            .finally(() => {
                // Hide loading indicator
                if (loadingIndicator) loadingIndicator.classList.add('hidden');
                if (getRecommendationButton) getRecommendationButton.disabled = false;
            });
    });

    // Function to update AI history
    function updateAIHistory(data) {
        const historyContainer = document.getElementById('ai-modal-tab-content-ai-history');

        // Clear "No history yet" message if present
        if (historyContainer.textContent.trim() === 'No history yet.') {
            historyContainer.innerHTML = '';
        }

        // Create history item
        const historyItem = document.createElement('div');
        historyItem.className = 'border-b border-gray-200 dark:border-gray-700 py-3';

        const timestamp = new Date(data.data.created_at).toLocaleString();
        const reasoningModel = data.data.reasoning_model.split('/').pop().split(':')[0];
        const resultModel = data.data.result_model.split('/').pop().split(':')[0];

        // Create a collapsible section for the details
        historyItem.innerHTML = `
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">${reasoningModel} → ${resultModel}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">${data.message}</p>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">${timestamp}</div>
            </div>
            <div class="mt-2">
                <button class="toggle-details text-xs text-blue-600 dark:text-blue-400 hover:underline">
                    View Details
                </button>
                <div class="details-content hidden mt-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h5 class="font-medium mb-2">Reasoning Analysis</h5>
                            <div class="text-sm whitespace-pre-line">${data.data.reasoning_output}</div>
                        </div>
                        <div>
                            <h5 class="font-medium mb-2">Execution Plan</h5>
                            <div class="text-sm whitespace-pre-line">${data.data.result_output}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        Tokens used: ${data.data.reasoning_tokens} (reasoning) + ${data.data.result_tokens} (result)
                    </div>
                </div>
            </div>
        `;

        // Add toggle functionality
        const toggleBtn = historyItem.querySelector('.toggle-details');
        const detailsContent = historyItem.querySelector('.details-content');

        toggleBtn.addEventListener('click', () => {
            detailsContent.classList.toggle('hidden');
            toggleBtn.textContent = detailsContent.classList.contains('hidden') ? 'View Details' : 'Hide Details';
        });

        // Add to history container
        historyContainer.prepend(historyItem);
    }
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.ai-modal-tab-btn');
    const tabContents = document.querySelectorAll('.ai-modal-tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            const tabName = this.getAttribute('data-tab');

            // Update tab buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('bg-blue-50', 'dark:bg-gray-800', 'text-blue-700', 'dark:text-blue-300', 'active');
                btn.classList.add('text-gray-600', 'dark:text-gray-400', 'bg-gray-50');
            });

            this.classList.remove('text-gray-600', 'dark:text-gray-400', 'bg-gray-50');
            this.classList.add('bg-blue-50', 'dark:bg-gray-800', 'text-blue-700', 'dark:text-blue-300', 'active');

            // Update tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            document.getElementById(`ai-modal-tab-content-${tabName}`).classList.remove('hidden');
        });
    });
});
