<?php

namespace App\Exports;

use App\Models\Provider;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Http\Request;

class ProvidersExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    protected $providers;

    public function __construct($providers)
    {
        $this->providers = $providers;
    }

    public static function fromRequest(Request $request)
    {
        $query = Provider::query();

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
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
        return $this->providers;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Status',
            // 'City',
            'Skills',
            'Created At',
        ];
    }

    public function map($provider): array
    {
        $serviceItems = 'N/A';
        $services = $provider->services;

        if (is_array($services) && !empty($services)) {
            $serviceItems = array_filter(array_map(function ($service) {
                if (is_array($service)) {
                    $subServices = $service['sub_services'] ?? [];
                    if (is_array($subServices)) {
                        return implode(', ', $subServices);
                    }
                    return is_string($subServices) ? $subServices : null;
                }
                return is_string($service) ? $service : json_encode($service);
            }, $services));

            $serviceItems = $serviceItems ? implode(' | ', $serviceItems) : 'N/A';
        } elseif (is_string($services) && $services !== '') {
            $serviceItems = $services;
        }

        $createdAt = $provider->created_at;
        if ($createdAt instanceof \DateTimeInterface) {
            $createdAtFormatted = $createdAt->format('Y-m-d H:i:s');
        } elseif (is_string($createdAt) && $createdAt !== '') {
            $createdAtFormatted = $createdAt;
        } else {
            $createdAtFormatted = 'N/A';
        }

        return [
            $provider->id,
            $provider->full_name ?? $provider->name ?? 'N/A',
            $provider->email ?? 'N/A',
            $provider->phone ?? 'N/A',
            ucfirst($provider->status ?? 'N/A'),
            // $provider->city ?? 'N/A',
            $serviceItems,
            $createdAtFormatted,
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
            'D' => 15,
            'E' => 15,
            'F' => 20,
            'G' => 25,
            'H' => 20,
        ];
    }
}
