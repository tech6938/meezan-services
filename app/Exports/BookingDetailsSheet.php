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

class BookingDetailsSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting, WithEvents
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
            'Deal Amount (PKR)',
            'Payment Type',
            'Cash on Delivery',
            'Booking Status',
            'Request Status',
            'Order Created Date',
            'Booking Pending Date',
            'Booking Accepted Date',
            'In Progress Date',
            'Completed Date',
            'Cancelled Date',
            'Days to Complete',
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

        $daysToComplete = $this->calculateDaysToComplete($booking);

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
            $booking->payment_type ?? 'N/A',
            $booking->cash_on_delivery ? 'Yes' : 'No',
            ucfirst($booking->status),
            $booking->req_status ?? 'N/A',
            $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : 'N/A',
            $this->getStatusDate($booking, 'pending'),
            $this->getStatusDate($booking, 'accepted'),
            $this->getStatusDate($booking, 'in_progress'),
            $this->getStatusDate($booking, 'completed'),
            $this->getStatusDate($booking, 'cancelled'),
            $daysToComplete,
        ];
    }

    protected function getStatusDate($booking, $status)
    {
        // Since you don't have status change logs, we'll use created_at/updated_at
        // For production, you should maintain a status_history table
        if ($booking->status == $status) {
            return $booking->updated_at ? $booking->updated_at->format('Y-m-d H:i:s') : 'N/A';
        }

        // If status is in the past, approximate based on created_at
        $statusOrder = ['pending' => 1, 'accepted' => 2, 'in_progress' => 3, 'completed' => 4, 'cancelled' => 5];
        $currentStatusOrder = $statusOrder[$booking->status] ?? 0;
        $targetStatusOrder = $statusOrder[$status] ?? 0;

        if ($targetStatusOrder < $currentStatusOrder) {
            // Status has passed, use approximate date
            return $booking->created_at ? $booking->created_at->modify("+{$targetStatusOrder} days")->format('Y-m-d H:i:s') : 'N/A';
        }

        return 'Not Yet';
    }

    protected function calculateDaysToComplete($booking)
    {
        if (in_array($booking->status, ['complete_booking', 'completed'])) {
            $start = $booking->created_at;
            $end = $booking->updated_at;
            if ($start && $end) {
                $days = $start->diffInDays($end);
                return $days . ' days';
            }
        }
        return 'In Progress';
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
            'I' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Deal Amount
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
            $status = $sheet->getCell('L' . $row)->getValue();
            $color = $this->getStatusColor($status);
            if ($color) {
                $sheet->getStyle('L' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($color);
                $sheet->getStyle('L' . $row)->getFont()->setColor(new Color(Color::COLOR_WHITE));
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
            'I' => 18,  // Deal Amount
            'J' => 15,  // Payment Type
            'K' => 15,  // Cash on Delivery
            'L' => 18,  // Booking Status
            'M' => 15,  // Request Status
            'N' => 20,  // Order Created Date
            'O' => 20,  // Booking Pending Date
            'P' => 20,  // Booking Accepted Date
            'Q' => 20,  // In Progress Date
            'R' => 20,  // Completed Date
            'S' => 20,  // Cancelled Date
            'T' => 15,  // Days to Complete
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'T';
                $lastRow = $sheet->getHighestRow();
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
                $sheet->freezePane('A2');

                // Add summary
                $totalBookings = $this->bookings->count();
                $totalAmount = $this->bookings->sum('price');
                $completedCount = $this->bookings->whereIn('status', ['complete_booking', 'completed'])->count();
                $pendingCount = $this->bookings->where('status', 'pending')->count();
                $inProgressCount = $this->bookings->where('status', 'in_progress')->count();
                $cancelledCount = $this->bookings->whereIn('status', ['cancel', 'cancelled'])->count();

                $sheet->setCellValue('V1', '📊 Booking Details Summary');
                $sheet->setCellValue('V2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
                $sheet->setCellValue('V3', 'Total Bookings: ' . $totalBookings);
                $sheet->setCellValue('V4', 'Total Amount: PKR ' . number_format($totalAmount, 2));
                $sheet->setCellValue('V5', 'Completed: ' . $completedCount);
                $sheet->setCellValue('V6', 'Pending: ' . $pendingCount);
                $sheet->setCellValue('V7', 'In Progress: ' . $inProgressCount);
                $sheet->setCellValue('V8', 'Cancelled: ' . $cancelledCount);

                $sheet->getStyle('V1:V8')->getFont()->setSize(10);
                $sheet->getStyle('V1')->getFont()->setBold(true);
                $sheet->getColumnDimension('V')->setWidth(40);
            },
        ];
    }
}
