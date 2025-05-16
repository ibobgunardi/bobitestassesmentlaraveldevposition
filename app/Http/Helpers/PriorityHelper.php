<?php

namespace App\Http\Helpers;

use App\Models\Task;

class PriorityHelper
{
    /**
     * Get priority thresholds for high and medium levels.
     *
     * @param int|null $projectId
     * @return array
     */
    public static function calculateThresholds(?int $projectId = null): array
    {
        $query = Task::query()
            ->whereNotNull('priority')
            ->orderByDesc('priority');

        if ($projectId !== null) {
            $query->where('project_id', $projectId);
        }

        $priorities = $query->pluck('priority')->filter()->values();

        $count = $priorities->count();

        if ($count === 0) {
            return [
                'high_min' => PHP_INT_MAX,
                'medium_min' => PHP_INT_MAX,
            ];
        }

        $highIndex = max(0, floor($count * 0.05) - 1);
        $mediumIndex = max(0, floor($count * 0.50) - 1);

        return [
            'high_min' => (int) $priorities[$highIndex],
            'medium_min' => (int) $priorities[$mediumIndex],
        ];
    }
}
