// API Layer for Task Management
import { blockUI } from './fullscreen-blockui.js';
import { showToastr } from './showToastr.js';

export async function fetchProjects(taskFilter = {}) {
    let url = '/api/projects';
    const search = new URLSearchParams(taskFilter).toString();
    if (search) url += '?' + search;
    const res = await fetch(url, {
        headers: { 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error('Failed to load projects');
    return res.json();
}

export async function fetchTasks(params = {}) {
    let url = '/api/tasks';
    const search = new URLSearchParams(params).toString();
    if (search) url += '?' + search;
    const res = await fetch(url, {
        headers: { 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error('Failed to load tasks');
    return res.json();
}

export async function reorderTask(taskId, newPriority, targetTaskId, csrfToken, isAllTasksView = false, projectIdFilter = null) {
    blockUI.show({
        message: 'Processing..',
        subtext: 'Please wait while we secure your transaction',
        spinnerIcon: 'fa-circle-notch'
    });
    try {
        const response = await fetch(`/api/tasks/${taskId}/reorder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                target_task_id: targetTaskId,
                is_all_tasks_view: isAllTasksView,
                project_id_filter: projectIdFilter
            })
        });
        const data = await response.json();
        if (!response.ok) {
            showToastr(data.message, 'error');
            return;
            
        }
        showToastr('Task reordered successfully', 'success');
        return data; // Return the response data
    } catch (error) {
        showToastr(error.message, 'error');
    } finally {
        blockUI.hide();
    }
}


