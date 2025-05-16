// Drag and Drop Utilities
import { reorderTask } from './api_tasks_refactored.js';
import { showToastr } from './showToastr.js';

let draggedTaskElement = null;
let dropTargetElement = null;
let isAllTasksView = false;
let projectIdFilter = null;
let taskfilter = {};
export function initializeDragAndDrop(fetchTasks) {
    // Clean up existing event listeners
    document.querySelectorAll('.task-card').forEach(card => {
        card.removeEventListener('dragstart', card._dragStartHandler);
        card.removeEventListener('dragend', card._dragEndHandler);
    });
    document.querySelectorAll('.task-list, .all-tasks-container .grid').forEach(dropZone => {
        dropZone.removeEventListener('dragover', dropZone._dragOverHandler);
        dropZone.removeEventListener('dragleave', dropZone._dragLeaveHandler);
        dropZone.removeEventListener('drop', dropZone._dropHandler);
    });

    // Make task cards draggable
    document.querySelectorAll('.task-card').forEach(card => {
        card.setAttribute('draggable', 'true');
        card._dragStartHandler = function (e) {
            draggedTaskElement = this;
            this.classList.add('opacity-50', 'border-blue-400');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.dataset.taskId);
            this.style.cursor = 'grabbing';
            this.dataset.originalProjectId = this.dataset.projectId;
            const currentProjectId = this.dataset.projectId;
            document.querySelectorAll(`.task-list[data-project-id="${currentProjectId}"]`).forEach(zone => {
                zone.classList.add('border-blue-400', 'bg-blue-50', 'dark:bg-blue-900/30', 'drop-zone', 'outline', 'outline-2', 'outline-offset-2', 'outline-blue-400', 'rounded-lg', 'transition-all', 'duration-200');
            });
        };
        card._dragEndHandler = function () {
            this.classList.remove('opacity-50', 'border-blue-400');
            this.style.cursor = 'grab';
            draggedTaskElement = null;
            dropTargetElement = null;
            document.querySelectorAll('.drop-zone').forEach(zone => {
                zone.classList.remove('border-blue-400', 'bg-blue-50', 'dark:bg-blue-900/30', 'border-red-400', 'bg-red-50', 'dark:bg-red-900/30', 'outline', 'outline-2', 'outline-offset-2', 'outline-blue-400', 'outline-red-400', 'rounded-lg', 'transition-all', 'duration-200', 'drop-zone');
            });
        };
        card.addEventListener('dragstart', card._dragStartHandler);
        card.addEventListener('dragend', card._dragEndHandler);
    });

    // Set up drop zones
    document.querySelectorAll('.task-list, .all-tasks-container .grid').forEach(dropZone => {
        dropZone._dragOverHandler = function (e) {
            e.preventDefault();
            e.stopPropagation();
            // Check if this is the "All Tasks" view

            isAllTasksView = this.closest('.all-tasks-container') !== null;

            const draggable = draggedTaskElement;
            if (!draggable) return;
            const dropX = e.clientX;
            const dropY = e.clientY;
            const projectId = this.dataset.projectId;

            let taskCards;
            if (isAllTasksView) {
                taskCards = Array.from(document.querySelectorAll(`.task-card`));
            } else {
                taskCards = Array.from(document.querySelectorAll(`.task-card[data-project-id="${projectId}"]`));
            }

            let targetTaskCard = null;
            let closestDistance = Infinity;
            taskCards.forEach(card => {
                // Removed: if (card === draggable) return;
                // Now includes the dragged card in the calculation, so dropping on itself can be detected
                const rect = card.getBoundingClientRect();
                const cardCenterX = rect.left + rect.width / 2;
                const cardCenterY = rect.top + rect.height / 2;
                const distance = Math.sqrt(Math.pow(dropX - cardCenterX, 2) + Math.pow(dropY - cardCenterY, 2));
                if (distance < closestDistance) {
                    closestDistance = distance;
                    targetTaskCard = card;
                }
            });
            dropTargetElement = targetTaskCard;
            const targetProjectId = this.closest('[data-project-id]')?.dataset.projectId;
            const sourceProjectId = draggable.dataset.projectId;



            if (isAllTasksView) {
                console.log('sourceTaskId', draggable.dataset.taskId);
                console.log('targetTaskId', targetTaskCard.dataset.taskId);
            }
            if (targetProjectId && sourceProjectId !== targetProjectId && !isAllTasksView) {
                this.classList.add('border-red-400', 'bg-red-50', 'dark:bg-red-900/30', 'drop-zone', 'outline', 'outline-2', 'outline-offset-2', 'outline-red-400', 'rounded-lg', 'transition-all', 'duration-200');
                e.dataTransfer.dropEffect = 'none';
                return;
            }
            this.classList.add('border-blue-400', 'bg-blue-50', 'dark:bg-blue-900/30', 'drop-zone', 'outline', 'outline-2', 'outline-offset-2', 'outline-blue-400', 'rounded-lg', 'transition-all', 'duration-200');
            e.dataTransfer.dropEffect = 'move';
        };

        dropZone._dragLeaveHandler = function () {
            this.classList.remove('border-red-400', 'bg-red-50', 'dark:bg-red-900/30');
            const draggable = draggedTaskElement;
            if (draggable) {
                const targetProjectId = this.closest('[data-project-id]')?.dataset.projectId;
                const sourceProjectId = draggable.dataset.projectId;
                if (targetProjectId !== sourceProjectId) {
                    this.classList.remove('border-blue-400', 'bg-blue-50', 'dark:bg-blue-900/30', 'drop-zone');
                }
            }
        };

        dropZone._dropHandler = async function (e) {
            e.preventDefault();
            this.classList.remove('border-blue-400', 'bg-blue-50', 'dark:bg-blue-900/30', 'border-red-400', 'bg-red-50', 'dark:bg-red-900/30', 'drop-zone');
            const taskId = e.dataTransfer.getData('text/plain');
            const sourceTaskCard = draggedTaskElement;
            if (!sourceTaskCard) return;

            // Added: Check drop zone's projectId against source task's projectId
            const targetProjectId = this.dataset.projectId || this.closest('[data-project-id]')?.dataset.projectId;
            const sourceProjectId = sourceTaskCard.dataset.projectId;
            if (targetProjectId && targetProjectId !== sourceProjectId) {
                showToastr('You cannot move a task to a different project');
                return;
            }

            const projectId = this.closest('[data-project-id]')?.dataset.projectId;
            let taskCards;
            if (isAllTasksView) {
                taskCards = Array.from(document.querySelectorAll(`.task-card`));
            } else {
                taskCards = Array.from(document.querySelectorAll(`.task-card[data-project-id="${projectId}"]`));
            }
            let targetTaskCard = dropTargetElement;
            let newPriority;

            if (targetTaskCard) {
                // If dropped on itself, exit early (no reordering needed)
                if (targetTaskCard.dataset.taskId === sourceTaskCard.dataset.taskId) {
                    return;
                }
                const targetRect = targetTaskCard.getBoundingClientRect();
                const isDroppingInLowerHalf = e.clientY > (targetRect.top + targetRect.height / 2);
                const targetIndex = taskCards.indexOf(targetTaskCard);
                if (isDroppingInLowerHalf) {
                    const nextTask = taskCards[targetIndex + 1];
                    newPriority = nextTask ? (parseInt(targetTaskCard.dataset.priority, 10) + parseInt(nextTask.dataset.priority, 10)) / 2 : parseInt(targetTaskCard.dataset.priority, 10) + 1;
                } else {
                    const prevTask = taskCards[targetIndex - 1];
                    newPriority = prevTask ? (parseInt(prevTask.dataset.priority, 10) + parseInt(targetTaskCard.dataset.priority, 10)) / 2 : parseInt(targetTaskCard.dataset.priority, 10) / 2;
                }
            } else {
                const lastTask = taskCards[taskCards.length - 1];
                newPriority = lastTask ? lastTask.dataset.priority : 1;
            }

            if (targetTaskCard && targetTaskCard.dataset.projectId !== sourceTaskCard.dataset.projectId && !isAllTasksView) {
                showToastr('You cannot move a task to a different project');
                return;
            }

            try {
                projectIdFilter = document.getElementById('project-filter').value;
                taskfilter = {
                    search: document.getElementById('task-search').value,
                    projectId: projectIdFilter
                };

                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                await reorderTask(taskId, newPriority, targetTaskCard ? parseInt(targetTaskCard.dataset.taskId, 10) : null, csrfToken, isAllTasksView, projectIdFilter);
            } catch (error) {
                showToastr('Failed to update task order: ' + error.message);
            }
            fetchTasks(taskfilter);
        };

        dropZone.addEventListener('dragover', dropZone._dragOverHandler);
        dropZone.addEventListener('dragleave', dropZone._dragLeaveHandler);
        dropZone.addEventListener('drop', dropZone._dropHandler);
    });
}