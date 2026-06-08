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
use PhpOffice\PhpSpreadsheet\Style\Color;

class UserDetailsSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting, WithEvents
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
            'Order No',
            'User ID',
            'User Name',
            'User Phone',
            'Provider ID',
            'Provider Name',
            'Provider Phone',
            'Price (PKR)',
            'Status',
            'Booking Date',
            'Booking Month',
            'Booking Year',
            'Created At',
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
            $orderNo,
            $booking->user_id,
            $booking->user->name ?? 'N/A',
            (string) ($booking->user->phone ?? 'N/A'),
            $booking->provider_id,
            $booking->provider->full_name ?? $booking->provider->name ?? 'N/A',
            (string) ($booking->provider->phone ?? 'N/A'),
            (float) ($booking->price ?? 0),
            $booking->status,
            $booking->created_at ? $booking->created_at->format('Y-m-d') : '',
            $booking->created_at ? $booking->created_at->format('F') : '',
            $booking->created_at ? $booking->created_at->format('Y') : '',
            $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    protected function getStatusColor($status)
    {
        return match ($status) {
            'pending' => 'FFC107',
            'accept', 'accepted' => '4CAF50',
            'in_progress' => '2196F3',
            'cancel', 'cancelled' => 'F44336',
            'complete_booking', 'completed' => '8BC34A',
            default => null,
        };
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,  // Order No
            'E' => NumberFormat::FORMAT_TEXT,  // User Phone
            'H' => NumberFormat::FORMAT_TEXT,  // Provider Phone
            'I' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Price
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF9800']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I:I')->getNumberFormat()->setFormatCode('"PKR"#,##0.00');

        // Color coding for status
        $lastRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $lastRow; $row++) {
            $status = $sheet->getCell('J' . $row)->getValue();
            $color = $this->getStatusColor($status);
            if ($color) {
                $sheet->getStyle('J' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($color);
                $sheet->getStyle('J' . $row)->getFont()->setColor(new Color(Color::COLOR_WHITE));
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // Sr. No
            'B' => 20,  // Order No
            'C' => 10,  // User ID
            'D' => 25,  // User Name
            'E' => 18,  // User Phone
            'F' => 12,  // Provider ID
            'G' => 25,  // Provider Name
            'H' => 18,  // Provider Phone
            'I' => 15,  // Price
            'J' => 18,  // Status
            'K' => 15,  // Booking Date
            'L' => 15,  // Booking Month
            'M' => 12,  // Booking Year
            'N' => 20,  // Created At
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

                // Add summary
                $totalBookings = $this->bookings->count();
                $totalAmount = $this->bookings->sum('price');
                $completedCount = $this->bookings->whereIn('status', ['complete_booking', 'completed'])->count();
                $pendingCount = $this->bookings->where('status', 'pending')->count();

                $sheet->setCellValue('P1', '📋 Booking Details Summary');
                $sheet->setCellValue('P2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
                $sheet->setCellValue('P3', 'Total Bookings: ' . $totalBookings);
                $sheet->setCellValue('P4', 'Total Amount: PKR ' . number_format($totalAmount, 2));
                $sheet->setCellValue('P5', 'Completed: ' . $completedCount);
                $sheet->setCellValue('P6', 'Pending: ' . $pendingCount);

                $sheet->getStyle('P1:P6')->getFont()->setSize(10);
                $sheet->getStyle('P1')->getFont()->setBold(true);
                $sheet->getColumnDimension('P')->setWidth(40);
            },
        ];
    }
}
