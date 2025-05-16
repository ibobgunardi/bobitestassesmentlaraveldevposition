<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Project::query();

        // Filter by company if user has a company
        if (Auth::user()->company_id) {
            $query->where('company_id', Auth::user()->company_id);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sort options
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSortFields = ['name', 'status', 'created_at', 'updated_at'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        $projects = $query->withCount('tasks')->paginate($perPage);

        return ApiResponse::success([
            'projects' => $projects->items(),
            'pagination' => [
                'total' => $projects->total(),
                'per_page' => $projects->perPage(),
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
            ]
        ], 'Projects retrieved successfully');
    }

    /**
     * Store a newly created project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:not_started,in_progress,on_hold,completed,cancelled',
            'company_id' => 'nullable|exists:companies,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        
        // If user has a company_id and no company_id is provided, use the user's company_id
        if (empty($data['company_id']) && Auth::user()->company_id) {
            $data['company_id'] = Auth::user()->company_id;
        }
        
        // Set the authenticated user as the creator of the project
        $data['created_by'] = Auth::id();

        $project = Project::create($data);

        return ApiResponse::success(['project' => $project], 'Project created successfully', 201);
    }

    /**
     * Display the specified project.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::with(['tasks' => function($query) {
            $query->orderBy('order');
        }])->findOrFail($id);

        // Check if user has access to this project
        if (Auth::user()->company_id && $project->company_id !== Auth::user()->company_id) {
            return ApiResponse::forbidden('You do not have access to this project');
        }

        return ApiResponse::success(['project' => $project], 'Project retrieved successfully');
    }

    /**
     * Update the specified project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Check if user has access to this project
        if (Auth::user()->company_id && $project->company_id !== Auth::user()->company_id) {
            return ApiResponse::forbidden('You do not have access to this project');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:active,completed,on_hold,cancelled',
            'company_id' => 'nullable|exists:companies,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $project->update($validator->validated());

        return ApiResponse::success(['project' => $project], 'Project updated successfully');
    }

    /**
     * Remove the specified project from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        // Check if user has access to this project
        if (Auth::user()->company_id && $project->company_id !== Auth::user()->company_id) {
            return ApiResponse::forbidden('You do not have access to this project');
        }

        // Check if project has tasks
        if ($project->tasks()->count() > 0) {
            return ApiResponse::error('Cannot delete project with tasks. Please delete tasks first or move them to another project.', 422);
        }

        $project->delete();

        return ApiResponse::success([], 'Project deleted successfully');
    }
}
