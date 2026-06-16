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
            'Created At',
        ];
    }

    public function map($booking): array
    {
        static $serialNumber = 0;
        $serialNumber++;

        // Format order number
        $orderNo = $booking->order_no ?? $booking->booking_no;
        if (is_numeric($orderNo) && strlen((string)$orderNo) > 10) {
            $orderNo = "'" . $orderNo;
        }

        // Get status display name
        $statusDisplay = $this->getStatusDisplay($booking->status, $booking->goto);

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
            $statusDisplay,
            $booking->created_at ? $booking->created_at->format('Y-m-d') : '',
            $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    protected function getStatusDisplay($status, $goto)
    {
        if ($status == 'pending' && $goto == '0') {
            return 'Pending Order';
        } elseif ($status == 'pending' && $goto == '2') {
            return 'Pending Booking';
        } elseif ($status == 'in_progress') {
            return 'In Progress';
        } elseif ($status == 'complete_booking') {
            return 'Completed';
        } elseif ($status == 'cancel') {
            return 'Cancelled';
        } elseif ($goto == '1') {
            return 'Accepted';
        }
        return $status ?? 'N/A';
    }

    protected function getStatusColor($status, $goto)
    {
        if ($status == 'pending' && $goto == '0') {
            return 'FFC107'; // Yellow - Pending Order
        } elseif ($status == 'pending' && $goto == '2') {
            return 'FF9800'; // Orange - Pending Booking
        } elseif ($status == 'in_progress') {
            return '2196F3'; // Blue - In Progress
        } elseif ($status == 'complete_booking') {
            return '4CAF50'; // Green - Completed
        } elseif ($status == 'cancel') {
            return 'F44336'; // Red - Cancelled
        } elseif ($goto == '1') {
            return '8BC34A'; // Light Green - Accepted
        }
        return null;
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

            // Determine color based on status text
            $color = match($status) {
                'Pending Order' => 'FFC107',
                'Pending Booking' => 'FF9800',
                'In Progress' => '2196F3',
                'Completed' => '4CAF50',
                'Cancelled' => 'F44336',
                'Accepted' => '8BC34A',
                default => null,
            };

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
            'J' => 20,  // Status
            'K' => 15,  // Booking Date
            'L' => 20,  // Created At
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'L';
                $lastRow = $sheet->getHighestRow();
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
                $sheet->freezePane('A2');

                // Calculate summary statistics
                $totalBookings = $this->bookings->count();
                $totalAmount = $this->bookings->where('status', 'complete_booking')->sum('price');

                // Count by status
                $completedCount = $this->bookings->where('status', 'complete_booking')->count();
                $pendingOrders = $this->bookings->where('status', 'pending')->where('goto', '0')->count();
                $pendingBookings = $this->bookings->where('status', 'pending')->where('goto', '2')->count();
                $inProgressCount = $this->bookings->where('status', 'in_progress')->count();
                $acceptedCount = $this->bookings->where('goto', '1')->count();
                $cancelledCount = $this->bookings->where('status', 'cancel')->count();

                // Add summary with separate row for cancelled bookings
                $sheet->setCellValue('N1', '📋 Booking Details Summary');
                $sheet->setCellValue('N2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
                $sheet->setCellValue('N3', 'Total Bookings: ' . $totalBookings);
                $sheet->setCellValue('N4', 'Total Amount: PKR ' . number_format($totalAmount, 2));
                $sheet->setCellValue('N5', '─────────────────────────');
                $sheet->setCellValue('N6', '📊 Status Breakdown:');
                $sheet->setCellValue('N7', '✅ Accepted: ' . $acceptedCount);
                $sheet->setCellValue('N8', '⏳ Pending Orders: ' . $pendingOrders);
                $sheet->setCellValue('N9', '🔄 Pending Bookings: ' . $pendingBookings);
                $sheet->setCellValue('N10', '▶️ In Progress: ' . $inProgressCount);
                $sheet->setCellValue('N11', '✔️ Completed: ' . $completedCount);
                $sheet->setCellValue('N12', '❌ Cancelled: ' . $cancelledCount); // Separate row for cancelled

                // Style the summary
                $sheet->getStyle('N1:N12')->getFont()->setSize(10);
                $sheet->getStyle('N1')->getFont()->setBold(true);
                $sheet->getStyle('N5')->getFont()->setBold(true);
                $sheet->getStyle('N6')->getFont()->setBold(true);

                // Color code the summary rows
                $sheet->getStyle('N7')->getFont()->getColor()->setRGB('4CAF50');
                $sheet->getStyle('N8')->getFont()->getColor()->setRGB('FFC107');
                $sheet->getStyle('N9')->getFont()->getColor()->setRGB('FF9800');
                $sheet->getStyle('N10')->getFont()->getColor()->setRGB('2196F3');
                $sheet->getStyle('N11')->getFont()->getColor()->setRGB('8BC34A');
                $sheet->getStyle('N12')->getFont()->getColor()->setRGB('F44336');

                $sheet->getColumnDimension('N')->setWidth(40);

                // Add a color legend
                $sheet->setCellValue('N14', '🎨 Color Legend:');
                $sheet->setCellValue('N15', '🟢 Accepted / Completed');
                $sheet->setCellValue('N16', '🟡 Pending Orders');
                $sheet->setCellValue('N17', '🟠 Pending Bookings');
                $sheet->setCellValue('N18', '🔵 In Progress');
                $sheet->setCellValue('N19', '🔴 Cancelled');
                $sheet->getStyle('N14:N19')->getFont()->setSize(9);
                $sheet->getStyle('N14')->getFont()->setBold(true);
            },
        ];
    }
}
