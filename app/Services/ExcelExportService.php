<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BaseExport;
use DateTime;

class ExcelExportService
{
    /**
     * Export data to Excel file and download
     *
     * @param array $data
     * @param string $fileName
     * @param array $headers
     * @param string $sheetName
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToExcel(array $data, string $fileName, array $headers, string $sheetName = 'Sheet1')
    {
        // Add timestamp to filename to prevent conflicts
        $fileName = $fileName . '_' . now()->format('Y-m-d_H-i-s');

        return Excel::download(
            new BaseExport($data, $headers, $sheetName),
            $fileName . '.xlsx'
        );
    }

    /**
     * Format data for Excel export
     *
     * @param \Illuminate\Database\Eloquent\Collection|array $records
     * @param array $columns
     * @return array
     */
    public static function formatDataForExport($records, array $columns): array
    {
        $data = [];

        foreach ($records as $record) {
            $row = [];
            foreach ($columns as $column => $label) {
                $row[$label] = $this->getValueFromRecord($record, $column);
            }
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get value from record using dot notation
     *
     * @param mixed $record
     * @param string $column
     * @return mixed
     */
    private function getValueFromRecord($record, string $column)
    {
        $value = $record;

        // Handle dot notation for nested relationships
        foreach (explode('.', $column) as $key) {
            if (is_array($value)) {
                $value = $value[$key] ?? null;
            } elseif (is_object($value)) {
                $value = $value->$key ?? null;
            } else {
                return null;
            }
        }

        // Format dates
        if ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }

    /**
     * Build query from filters
     *
     * @param mixed $query
     * @param array $filters
     * @return mixed
     */
    public static function applyFilters($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query;
    }

    /**
     * Apply sorting
     *
     * @param mixed $query
     * @param string $sortBy
     * @param string $sortOrder
     * @return mixed
     */
    public static function applySorting($query, ?string $sortBy = null, ?string $sortOrder = 'asc')
    {
        if ($sortBy && in_array($sortBy, ['created_at', 'updated_at', 'name', 'email', 'phone'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }
}
