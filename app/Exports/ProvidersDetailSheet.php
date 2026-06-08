<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProvidersDetailSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $bookingRequests;

    public function __construct($bookingRequests)
    {
        $this->bookingRequests = $bookingRequests;
    }

    public function collection()
    {
        return $this->bookingRequests;
    }

    public function headings(): array
    {
        return [
            'Partner ID', 'Partner Name', 'Partner Phone', 'Booking No', 'User Name',
            'User ID', 'User Phone', 'Booking Date', 'Booking Month', 'Booking Year', 'Amount', 'Status'
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->provider_id,
            $booking->provider->full_name ?? $booking->provider->name ?? 'N/A',
            (string) ($booking->provider->phone ?? 'N/A'),
            $booking->booking_no ?? $booking->id,
            $booking->user->name ?? 'N/A',
            $booking->user_id,
            (string) ($booking->user->phone ?? 'N/A'),
            $booking->created_at ? $booking->created_at->format('Y-m-d') : '',
            $booking->created_at ? $booking->created_at->format('F') : '',
            $booking->created_at ? $booking->created_at->format('Y') : '',
            $booking->price ?? 0,
            $booking->status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, 'B' => 25, 'C' => 18, 'D' => 15, 'E' => 25,
            'F' => 12, 'G' => 18, 'H' => 15, 'I' => 15, 'J' => 12, 'K' => 15, 'L' => 20
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'L';
                $lastRow = $sheet->getHighestRow();

                // Add auto-filter (dropdown filters)
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
                $sheet->freezePane('A2');
            },
        ];
    }
}
