<?php

namespace App\Exports;

use App\Models\ServiceRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Http\Request;

class RequestsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    protected $requests;

    public function __construct($requests)
    {
        $this->requests = $requests;
    }

    public static function fromRequest(Request $request)
    {
        $query = ServiceRequest::with(['user', 'subCategory']);

        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })->orWhereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        return new self($query->get());
    }

    public function collection()
    {
        return $this->requests;
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            // 'Title',
            'Category',
            'SubCategory',
            'Status',
            // 'Budget',
            'Created At',
            'Updated At',
        ];
    }

    public function map($serviceRequest): array
    {
        return [
            $serviceRequest->id,
            $serviceRequest->user->name ?? 'N/A',
            // $serviceRequest->title ?? 'N/A',
            $serviceRequest->category->name ?? 'N/A',
            $serviceRequest->subCategory->name ?? 'N/A',
            ucfirst($serviceRequest->status),
            // $serviceRequest->budget ?? 'N/A',
            $serviceRequest->created_at->format('Y-m-d H:i:s'),
            $serviceRequest->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Style header row
        $sheet->getStyle('1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,
            'C' => 30,
            'D' => 25,
            'E' => 15,
            'F' => 15,
            'G' => 20,
            'H' => 20,
        ];
    }
}
