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

class RequestBidsSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting, WithEvents
{
    protected $requests;
    protected $filters;
    protected $rows;

    public function __construct($requests, $filters = [])
    {
        $this->requests = $requests;
        $this->filters = $filters;
        $this->buildRows();
    }

    protected function buildRows()
    {
        $this->rows = collect();

        foreach ($this->requests as $request) {
            $bookings = $request->bookingRequests;

            if ($bookings->count() > 0) {
                foreach ($bookings as $booking) {
                    $this->rows->push([
                        'request_id' => $request->id,
                        'request_desc' => $request->desc,
                        'user_name' => $request->user->name ?? 'N/A',
                        'user_phone' => $request->user->phone ?? 'N/A',
                        'provider_id' => $booking->provider_id,
                        'provider_name' => $booking->provider->full_name ?? $booking->provider->name ?? 'N/A',
                        'provider_phone' => $booking->provider->phone ?? 'N/A',
                        'bid_price' => $booking->price ?? 0,
                        'booking_status' => $booking->status,
                        'order_no' => $booking->order_no,
                        'booking_created_at' => $booking->created_at,
                        'is_accepted_booking' => in_array($booking->status, ['in_progress', 'complete_booking', 'completed']) ? 'Yes' : 'No',
                    ]);
                }
            } else {
                // Request with no bids
                $this->rows->push([
                    'request_id' => $request->id,
                    'request_desc' => $request->desc,
                    'user_name' => $request->user->name ?? 'N/A',
                    'user_phone' => $request->user->phone ?? 'N/A',
                    'provider_id' => 'No Bids',
                    'provider_name' => 'No Provider Bids Yet',
                    'provider_phone' => 'N/A',
                    'bid_price' => 0,
                    'booking_status' => 'No Bids',
                    'order_no' => 'N/A',
                    'booking_created_at' => null,
                    'is_accepted_booking' => 'No',
                ]);
            }
        }
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Sr. No',
            'Request ID',
            'Request Description',
            'User Name',
            'User Phone',
            'Provider ID',
            'Provider Name',
            'Provider Phone',
            'Bid/Price (PKR)',
            'Booking Status',
            'Order No',
            'Is Accepted Booking',
            'Bid Created At',
        ];
    }

    public function map($row): array
    {
        static $serialNumber = 0;
        $serialNumber++;

        // Format order number
        $orderNo = $row['order_no'];
        if (is_numeric($orderNo) && strlen((string)$orderNo) > 10) {
            $orderNo = "'" . $orderNo;
        }

        return [
            $serialNumber,
            $row['request_id'],
            $row['request_desc'],
            $row['user_name'],
            (string) $row['user_phone'],
            $row['provider_id'],
            $row['provider_name'],
            (string) $row['provider_phone'],
            (float) $row['bid_price'],
            $row['booking_status'],
            $orderNo,
            $row['is_accepted_booking'],
            $row['booking_created_at'] ? $row['booking_created_at']->format('Y-m-d H:i:s') : 'N/A',
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
            'No Bids' => '9E9E9E',
            default => null,
        };
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_TEXT,  // User Phone
            'H' => NumberFormat::FORMAT_TEXT,  // Provider Phone
            'I' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Price
            'K' => NumberFormat::FORMAT_TEXT,  // Order No
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
            'B' => 10,  // Request ID
            'C' => 40,  // Request Description
            'D' => 25,  // User Name
            'E' => 18,  // User Phone
            'F' => 12,  // Provider ID
            'G' => 25,  // Provider Name
            'H' => 18,  // Provider Phone
            'I' => 18,  // Bid/Price
            'J' => 18,  // Booking Status
            'K' => 20,  // Order No
            'L' => 18,  // Is Accepted Booking
            'M' => 20,  // Bid Created At
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'M';
                $lastRow = $sheet->getHighestRow();
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
                $sheet->freezePane('A2');

                // Add summary
                $totalRequests = $this->requests->count();
                $totalBids = $this->rows->where('provider_id', '!=', 'No Bids')->count();
                $acceptedBookings = $this->rows->where('is_accepted_booking', 'Yes')->count();

                $sheet->setCellValue('O1', '💰 Bids & Bookings Summary');
                $sheet->setCellValue('O2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
                $sheet->setCellValue('O3', 'Total Requests: ' . $totalRequests);
                $sheet->setCellValue('O4', 'Total Bids: ' . $totalBids);
                $sheet->setCellValue('O5', 'Accepted Bookings: ' . $acceptedBookings);

                $sheet->getStyle('O1:O5')->getFont()->setSize(10);
                $sheet->getStyle('O1')->getFont()->setBold(true);
                $sheet->getColumnDimension('O')->setWidth(40);
            },
        ];
    }
}
