// Task Rendering
export function renderProjectView(groupedTasks, container, initializeDragAndDrop) {
    if (!container) return;
    container.innerHTML = '';
    if (!groupedTasks || Object.keys(groupedTasks).length === 0) {
        container.innerHTML = `<div class="flex flex-col items-center justify-center w-full py-12 text-center">
    <i class="fas fa-tasks text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
    <p class="text-gray-500 dark:text-gray-400">No projects found</p>
</div>`;
        return;
    }
    Object.entries(groupedTasks).forEach(([projectId, projectTasks]) => {
        if (!projectTasks || projectTasks.length === 0) return;
        const project = projectTasks[0].project;
        const taskCount = projectTasks.length;
        const projectColumn = document.createElement('div');
        projectColumn.className = 'project-column flex-shrink-0 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col hover:shadow-md transition-shadow duration-200';
        const projectHeader = `<div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 rounded-t-xl">
    <div class="flex items-start justify-between">
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center">
                <i class="fas fa-circle text-blue-500 text-xs mr-2"></i>
                ${project?.name || 'Unnamed Project'}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-4">
                ${taskCount} ${taskCount === 1 ? 'task' : 'tasks'}
            </p>
        </div>
        <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors duration-200">
            <i class="fas fa-ellipsis-v"></i>
        </button>
    </div>
    ${project?.description ? `<p class="text-xs text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">${project.description}</p>` : ''}
</div>`;
        let taskList = `<div class="task-list flex-1 p-3 overflow-y-auto min-h-[100px] bg-gray-50/50 dark:bg-gray-800/50" data-project-id="${projectId}">`;
        if (taskCount > 0) {
            let position = 1;
            projectTasks.forEach(task => {
                taskList += createTaskCard(task, projectId, position);
                position++;
            });
        } else {
            taskList += `<div class="flex flex-col items-center justify-center h-full py-8 text-center">
        <i class="fas fa-tasks text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
        <p class="text-sm text-gray-500 dark:text-gray-400">No tasks yet</p>
       
    </div>`;
        }
        taskList += '</div>';
        const addTaskButton = ``;
        projectColumn.innerHTML = projectHeader + taskList + addTaskButton;
        container.appendChild(projectColumn);
    });
    if (typeof initializeDragAndDrop === 'function') {
        setTimeout(() => initializeDragAndDrop(), 100);
    }
}

export function createTaskCard(task, projectId,position = null) {
    // Map numeric priority to high/medium/low categories
    const priorityValue = Math.min(100, Math.max(1, parseInt(task.priority) || 50));
    const priority = task.priority_level;

    const priorityLabel = priority.label;
    const priorityBg = priority.bg;
    const priorityText = priority.text;
    const priorityBorder = priority.border;
    let dueDate = '';
    let isOverdue = false;
    if (task.due_date) {
        const date = new Date(task.due_date);
        dueDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        isOverdue = date < new Date() && task.status !== 'completed';
    }
    // Position badge HTML (only shown if position is provided)
    const positionBadge = position ? `
        <span class="absolute -left-0 -top-0 bg-blue-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center shadow-md">
            ${position}
        </span>
    ` : '';
    return `
    <div class="task-card group relative bg-white dark:bg-gray-800 rounded-lg border-l-4 ${priorityBorder} shadow-sm hover:shadow-md transition-all duration-200 cursor-move mb-2" 
         draggable="true" 
         data-task-id="${task.id}" 
         data-priority="${task.priority}" 
         data-project-id="${projectId}">
         
        
        <div class="p-3">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">${task.title}</h4>
                    ${task.description ? `<p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">${task.description}</p>` : ''}
                </div>
                <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 flex-shrink-0 ml-2">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${priorityBg} ${priorityText}">
                    ${priorityLabel.trim()}
                </span>
                ${dueDate ? `
                    <div class="flex items-center text-xs ${isOverdue ? 'text-red-500' : 'text-gray-500 dark:text-gray-400'}">
                        <i class="far fa-calendar-alt mr-1"></i>
                        <span>${dueDate}</span>
                    </div>
                ` : ''}
            </div>
        </div>
    </div>
`;
}

export function displayTasks(tasks, container) {
    if (!tasks || tasks.length === 0) {
        container.innerHTML = '';
        const emptyState = document.createElement('div');
        emptyState.className = 'col-span-full text-center py-12 text-gray-500 dark:text-gray-400';
        emptyState.textContent = 'No tasks found.';
        container.parentElement.prepend(emptyState);
        return;
    }
    const existingEmptyState = container.parentElement.querySelector('.col-span-full.text-center');
    if (existingEmptyState) existingEmptyState.remove();
    let html = '';
    tasks.forEach(task => {
        html += createTaskCard(task, task.project_id);
    });
    container.innerHTML = html;
}
