<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for handling Excel exports in controllers
 * Provides reusable methods for exporting data
 */
trait ExportableTrait
{
    /**
     * Apply common export filters to query
     *
     * @param Builder $query
     * @param Request $request
     * @param array $searchFields - Fields to search in
     * @param string $statusField - Status field name (default: 'status')
     * @return Builder
     */
    protected function applyExportFilters(
        Builder $query,
        Request $request,
        array $searchFields = [],
        string $statusField = 'status'
    ): Builder {
        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where($statusField, $request->status);
        }

        // Apply date range filters
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return $query;
    }

    /**
     * Apply sorting to query
     *
     * @param Builder $query
     * @param Request $request
     * @param array $allowedFields - Fields allowed to sort by
     * @param string $defaultField - Default sort field
     * @return Builder
     */
    protected function applySortingToExport(
        Builder $query,
        Request $request,
        array $allowedFields = ['created_at', 'updated_at', 'name', 'id'],
        string $defaultField = 'created_at'
    ): Builder {
        $sortBy = $request->get('sort_by', $defaultField);
        $sortOrder = $request->get('sort_order', 'desc');

        // Only allow valid sort fields
        if (in_array($sortBy, $allowedFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy($defaultField, 'desc');
        }

        return $query;
    }

    /**
     * Export with comprehensive filtering and sorting
     *
     * @param mixed $exportClass - Export class instance
     * @param string $fileName - Export filename
     * @param Request $request - HTTP request
     * @param array $searchFields - Fields to search in
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function performExport(
        $exportClass,
        string $fileName,
        Request $request,
        array $searchFields = []
    ) {
        // Generate filename with timestamp
        $timestampedFileName = $fileName . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(
            $exportClass,
            $timestampedFileName
        );
    }

    /**
     * Get export metadata
     *
     * @param string $moduleName
     * @param int $totalRecords
     * @return array
     */
    protected function getExportMetadata(string $moduleName, int $totalRecords): array
    {
        return [
            'module' => $moduleName,
            'total_records' => $totalRecords,
            'exported_at' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
        ];
    }

    /**
     * Log export action
     *
     * @param string $module
     * @param int $recordCount
     * @param Request $request
     * @return void
     */
    protected function logExport(string $module, int $recordCount, Request $request): void
    {
        \Log::info("Export executed", [
            'module' => $module,
            'records' => $recordCount,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'filters' => $request->all(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Validate export request
     *
     * @param Request $request
     * @return bool
     * @throws \Exception
     */
    protected function validateExportRequest(Request $request): bool
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            throw new \Exception('Unauthorized');
        }

        // Check date format if provided
        if ($request->has('start_date') && !$this->isValidDate($request->start_date)) {
            throw new \Exception('Invalid start_date format');
        }

        if ($request->has('end_date') && !$this->isValidDate($request->end_date)) {
            throw new \Exception('Invalid end_date format');
        }

        return true;
    }

    /**
     * Check if date format is valid
     *
     * @param string $date
     * @param string $format
     * @return bool
     */
    private function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Get export statistics
     *
     * @param string $moduleName
     * @param Request $request
     * @return array
     */
    public function getExportStats(string $moduleName, Request $request): array
    {
        return [
            'module' => $moduleName,
            'timestamp' => now()->toIso8601String(),
            'user_id' => auth()->id(),
            'filters_applied' => !empty($request->all()),
            'has_search' => $request->has('search'),
            'has_status_filter' => $request->has('status'),
            'has_date_range' => $request->has('start_date') || $request->has('end_date'),
            'sort_by' => $request->get('sort_by'),
            'sort_order' => $request->get('sort_order', 'desc'),
        ];
    }
}
