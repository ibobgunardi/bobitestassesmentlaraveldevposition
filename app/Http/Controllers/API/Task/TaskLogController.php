<?php

namespace App\Http\Controllers\API\Task;

use App\Http\Controllers\Controller;
use App\Models\TaskLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Get paginated task logs for the authenticated user's company
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'task_id' => 'sometimes|exists:tasks,id'
        ]);

        $perPage = $request->input('per_page', 15);
        
        $query = TaskLog::with(['task', 'user'])
            ->where('company_id', Auth::user()->company_id)
            ->latest();
            
        if ($taskId = $request->input('task_id')) {
            $query->where('task_id', $taskId);
        }
        
        $logs = $query->paginate($perPage);
        
        return response()->json([
            'data' => $logs->items(),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
