<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BookingSummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting, WithEvents
{
    protected $bookings;
    protected $filters;

    public function __construct($bookings, $filters = [])
    {
        $this->bookings = $bookings;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->bookings;
    }

    public function headings(): array
    {
        return [
            'Sr. No',
            'Booking ID',
            'Order No',
            'User ID',
            'User Name',
            'User Phone',
            'Provider ID',
            'Provider Name',
            'Provider Phone',
            'Deal Amount (PKR)',
            'Booking Status',
            'Order Created Date',
            'Booking Pending Date',
            'Completed Date',
        ];
    }

    public function map($booking): array
    {
        static $serialNumber = 0;
        $serialNumber++;

        // Format order number
        $orderNo = $booking->order_no;
        if (is_numeric($orderNo) && strlen((string)$orderNo) > 10) {
            $orderNo = "'" . $orderNo;
        }

        return [
            $serialNumber,
            $booking->id,
            $orderNo,
            $booking->user_id,
            $booking->user->name ?? 'N/A',
            (string) ($booking->user->phone ?? 'N/A'),
            $booking->provider_id,
            $booking->provider->full_name ?? $booking->provider->name ?? 'N/A',
            (string) ($booking->provider->phone ?? 'N/A'),
            (float) ($booking->price ?? 0),
            ucfirst($booking->status),
            $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : 'N/A',
            $this->getPendingDate($booking),
            $this->getCompletedDate($booking),
        ];
    }

    protected function getPendingDate($booking)
    {
        if ($booking->status == 'pending') {
            return $booking->updated_at ? $booking->updated_at->format('Y-m-d H:i:s') : 'N/A';
        }

        // Check if there's a status change log or just use updated_at
        if ($booking->created_at && $booking->status != 'pending') {
            return $booking->created_at->format('Y-m-d H:i:s');
        }

        return 'N/A';
    }

    protected function getCompletedDate($booking)
    {
        if (in_array($booking->status, ['complete_booking', 'completed'])) {
            return $booking->updated_at ? $booking->updated_at->format('Y-m-d H:i:s') : 'N/A';
        }
        return 'N/A';
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT,  // User Phone
            'I' => NumberFormat::FORMAT_TEXT,  // Provider Phone
            'J' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Deal Amount
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J:J')->getNumberFormat()->setFormatCode('"PKR"#,##0.00');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // Sr. No
            'B' => 10,  // Booking ID
            'C' => 20,  // Order No
            'D' => 10,  // User ID
            'E' => 25,  // User Name
            'F' => 18,  // User Phone
            'G' => 12,  // Provider ID
            'H' => 25,  // Provider Name
            'I' => 18,  // Provider Phone
            'J' => 18,  // Deal Amount
            'K' => 18,  // Booking Status
            'L' => 20,  // Order Created Date
            'M' => 20,  // Booking Pending Date
            'N' => 20,  // Completed Date
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'N';
                $lastRow = $sheet->getHighestRow();
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
                $sheet->freezePane('A2');
            },
        ];
    }
}
