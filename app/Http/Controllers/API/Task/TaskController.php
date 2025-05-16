<?php

namespace App\Http\Controllers\API\Task;

use App\Events\TaskReordered;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\PriorityHelper;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // checkProjectId Exist
            $projectId = $request->filled('projectId') ? $request->projectId : null;
            $query = Task::with('project')
                ->when($projectId, fn($q) => $q->where('project_id', $projectId));

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $query->orderBy('priority', 'desc');

            $perPage = $request->input('per_page', 100);
            $tasks = $query->paginate($perPage);

            // Calculate thresholds once for the given projectId
            $thresholds = PriorityHelper::calculateThresholds($projectId);

            // Inject and append attributes safely
            $tasks->getCollection()->each(function ($task) use ($thresholds) {
                $task->setPriorityThresholds($thresholds);
                $task->append('priority_level');
            });

            // Grouped by project for frontend use
            $groupedTasks = $tasks->getCollection()->groupBy('project_id');

            return ApiResponse::paginated(
                $tasks,
                'tasks',
                'Tasks retrieved successfully',
                200,
                ['grouped_tasks' => $groupedTasks]
            );
        } catch (\Exception $e) {
            \Log::error('TaskController index error: ' . $e->getMessage());
            return ApiResponse::error('Failed to retrieve tasks', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created task in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'projectId' => 'required|exists:projects,id',
            'status' => 'required|in:todo,in_progress,review,done',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        // Check if user has access to the project
        $project = Project::findOrFail($request->project_id);
        if (Auth::user()->company_id && $project->company_id !== Auth::user()->company_id) {
            return ApiResponse::forbidden('You do not have access to this project');
        }

        // Get the maximum order value for this project
        $maxOrder = Task::where('project_id', $request->project_id)->max('order') ?? 0;

        // Create task with the next order value
        $task = Task::create(array_merge(
            $validator->validated(),
            ['order' => $maxOrder + 1]
        ));

        return ApiResponse::success(['task' => $task], 'Task created successfully', 201);
    }

    /**
     * Display the specified task.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Task::with('project')->findOrFail($id);

        // Check if user has access to this task's project
        if (Auth::user()->company_id && $task->project->company_id !== Auth::user()->company_id) {
            return ApiResponse::forbidden('You do not have access to this task');
        }

        return ApiResponse::success(['task' => $task], 'Task retrieved successfully');
    }

    /**
     * Update the specified task in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $task = Task::with('project')->findOrFail($id);

        // Check if user has access to this task's project
        if (Auth::user()->company_id && $task->project->company_id !== Auth::user()->company_id) {
            return ApiResponse::forbidden('You do not have access to this task');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'sometimes|required|exists:projects,id',
            'status' => 'sometimes|required|in:todo,in_progress,review,done',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        // If project_id is being changed, check access to the new project
        if ($request->has('project_id') && $request->project_id != $task->project_id) {
            $newProject = Project::findOrFail($request->project_id);
            if (Auth::user()->company_id && $newProject->company_id !== Auth::user()->company_id) {
                return ApiResponse::forbidden('You do not have access to the target project');
            }

            // If moving to a new project, set order to the end of that project's tasks
            $maxOrder = Task::where('project_id', $request->project_id)->max('order') ?? 0;
            $task->order = $maxOrder + 1;
        }

        $task->update($validator->validated());

        return ApiResponse::success(['task' => $task], 'Task updated successfully');
    }

    /**
     * Remove the specified task from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        // Check if user has access to this task's project
        if (!$this->userHasAccessToProject($task->project_id)) {
            return ApiResponse::forbidden('You do not have access to this task.');
        }

        $task->delete();

        return ApiResponse::success(null, 'Task deleted successfully');
    }

    /**
     * Reorder tasks within a project.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request, $id)
    {
        try {
            $task = Task::with('project')->findOrFail($id);

            // Check if user has access to this task's project
            if (Auth::user()->company_id && $task->project->company_id !== Auth::user()->company_id) {
                return ApiResponse::forbidden('You do not have access to this task');
            }

            $validator = Validator::make($request->all(), [
                'target_task_id' => 'required|integer|min:1|exists:tasks,id',
            ]);

            if ($validator->fails()) {
                return ApiResponse::validationError($validator->errors()->toArray());
            }

            // Get the target task
            $targetTask = Task::findOrFail($request->target_task_id);

            // check update priority by project or no
            if ($request->is_all_tasks_view && $request->filled('project_id_filter')) {
                $projectId = $request->project_id_filter;  // is on all view with filtered project
            } else if ($request->is_all_tasks_view && !$request->filled('project_id_filter')) {
                $projectId = null;  // is on all view with no filtered project
            } else {
                $projectId = $task->project_id;  // not in all view
                // Ensure both tasks belong to the same project
                if ($task->project_id !== $targetTask->project_id) {
                    return ApiResponse::error('Cannot reorder tasks from different projects');
                }
            }

            $newPriority = $targetTask->priority;
            $currentPriority = $task->priority;

            // No change needed if trying to reorder to the same position
            if ($task->id === $targetTask->id || $newPriority === $currentPriority) {
                return ApiResponse::success(['task' => $task], 'Task position unchanged');
            }

            // Start a database transaction for data consistency
            DB::beginTransaction();

            try {
                if ($newPriority != $currentPriority) {
                    // Build the base query to affect only other tasks
                    $query = Task::where('id', '!=', $task->id);

                    // Apply project filter if needed
                    if ($projectId !== null) {
                        $query->where('project_id', $projectId);
                    }
                    // Handle priority shift logic based on direction
                    if ($newPriority > $currentPriority) {
                        // Moving down: pull others up
                        $query
                            ->whereBetween('priority', [$currentPriority + 1, $newPriority])
                            ->decrement('priority');
                    } else {
                        // Moving up: push others down
                        $query
                            ->whereBetween('priority', [$newPriority, $currentPriority - 1])
                            ->increment('priority');
                    }
                    // Update the task's priority to the target priority
                    $task->priority = $newPriority;
                }

                // Save the updated task
                if (!$task->save()) {
                    throw new \Exception('Failed to save task');
                }

                // Dispatch the event with error handling
                try {
                    $taskLog = \App\Models\TaskLog::create([
                        'company_id' => auth()->user()->company_id,
                        'task_id' => $task->id,
                        'user_id' => auth()->id(),
                        'action' => 'reordered',
                        'description' => "Task reordered from priority {$currentPriority} to {$newPriority}",
                        'old_values' => ['priority' => $currentPriority],
                        'new_values' => ['priority' => $newPriority],
                    ]);
                    $event = new TaskReordered($task, $currentPriority, $newPriority, $taskLog->id);
                    broadcast($event)->toOthers();
                    \Log::info('TaskReordered event dispatched', [
                        'task_id' => $task->id,
                        'old_priority' => $currentPriority,
                        'new_priority' => $newPriority,
                        'user_id' => auth()->id()
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to broadcast TaskReordered event', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'task_id' => $task->id
                    ]);
                }

                // Reload the task with relationships
                $task->load('project', 'assignee', 'creator');

                DB::commit();

                return ApiResponse::success([
                    'task' => $task,
                    'new_priority' => $task->priority
                ], 'Task reordered successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;  // Re-throw to be caught by the outer try-catch
            }
        } catch (\Exception $e) {
            \Log::error('Task reorder failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error('Failed to reorder task: ' . $e->getMessage());
        }
    }
}
