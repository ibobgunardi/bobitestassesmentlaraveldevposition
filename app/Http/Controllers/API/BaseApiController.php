<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BaseApiController extends Controller
{
    /**
     * Run validation with standardized error response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array|ApiResponse
     */
    protected function validateRequest(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = Validator::make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    /**
     * Check if the user has access to a company resource.
     *
     * @param  int  $companyId
     * @return bool|ApiResponse
     */
    protected function checkCompanyAccess($companyId)
    {
        $user = auth()->user();
        
        if ($user->company_id && $companyId !== $user->company_id) {
            return ApiResponse::forbidden('You do not have access to this resource');
        }

        return true;
    }

    /**
     * Format pagination data for API responses.
     *
     * @param  \Illuminate\Pagination\LengthAwarePaginator  $paginator
     * @return array
     */
    protected function getPaginationData($paginator)
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    /**
     * Apply common filters to a query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $searchFields
     * @param  array  $filterFields
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilters($query, Request $request, array $searchFields = [], array $filterFields = [])
    {
        // Apply search filter
        if ($request->has('search') && !empty($searchFields)) {
            $search = $request->search;
            $query->where(function($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        // Apply specific filters
        foreach ($filterFields as $field) {
            if ($request->has($field)) {
                $query->where($field, $request->input($field));
            }
        }

        return $query;
    }

    /**
     * Apply sorting to a query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $allowedSortFields
     * @param  string  $defaultSortField
     * @param  string  $defaultSortDirection
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySorting($query, Request $request, array $allowedSortFields, $defaultSortField = 'created_at', $defaultSortDirection = 'desc')
    {
        $sortField = $request->input('sort_field', $defaultSortField);
        $sortDirection = $request->input('sort_direction', $defaultSortDirection);
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy($defaultSortField, $defaultSortDirection === 'asc' ? 'asc' : 'desc');
        }

        return $query;
    }
}
