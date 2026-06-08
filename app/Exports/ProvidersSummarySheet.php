<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProvidersSummarySheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected $providers;
    protected $bookingRequests;

    public function __construct($providers, $bookingRequests)
    {
        $this->providers = $providers;
        $this->bookingRequests = $bookingRequests;
    }

    public function array(): array
    {
        $summary = [];

        foreach ($this->providers as $provider) {
            $providerBookings = $this->bookingRequests->where('provider_id', $provider->id);

            $summary[] = [
                $provider->id,
                $provider->full_name ?? $provider->name ?? 'N/A',
                (string) ($provider->phone ?? 'N/A'),
                $providerBookings->count(),
                $providerBookings->whereIn('status', ['accept', 'accepted'])->count(),
                $providerBookings->where('status', 'pending')->count(),
                $providerBookings->where('status', 'in_progress')->count(),
                $providerBookings->whereIn('status', ['cancel', 'cancelled'])->count(),
                $providerBookings->whereIn('status', ['complete_booking', 'completed'])->count(),
                $providerBookings->sum('price'),
            ];
        }

        return $summary;
    }

    public function headings(): array
    {
        return [
            'Partner ID',
            'Partner Name',
            'Phone Number',
            'Total Orders',
            'Accepted',
            'Pending',
            'In Progress',
            'Cancelled',
            'Completed',
            'Total Amount (PKR)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2196F3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, 'B' => 30, 'C' => 18, 'D' => 15, 'E' => 12,
            'F' => 12, 'G' => 15, 'H' => 12, 'I' => 12, 'J' => 20,
        ];
    }
}
