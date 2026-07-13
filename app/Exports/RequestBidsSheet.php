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
use App\Http\Controllers\ServiceRequestController;

class RequestBidsSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting, WithEvents
{
    protected $requests;
    protected $filters;
    protected $rows;
    protected $controller;

    public function __construct($requests, $filters = [])
    {
        $this->requests = $requests;
        $this->filters = $filters;
        $this->controller = new ServiceRequestController();
        $this->buildRows();
    }

    protected function buildRows()
    {
        $this->rows = collect();

        foreach ($this->requests as $request) {
            $bookings = $request->bookingRequests;
            
            // Get the request status using the same logic as blade
            $booking = $this->controller->getGoverningBooking($request);
            $requestStatus = $this->controller->resolveDisplayStatus($request->status, $booking);

            if ($bookings->count() > 0) {
                foreach ($bookings as $booking) {
                    // Get booking status display
                    $bookingStatusDisplay = $this->getBookingStatusDisplay($booking);
                    
                    $this->rows->push([
                        'request_id' => $request->id,
                        'request_desc' => $request->desc,
                        'user_name' => $request->user->name ?? 'N/A',
                        'user_phone' => $request->user->phone ?? 'N/A',
                        'provider_id' => $booking->provider_id,
                        'provider_name' => $booking->provider->full_name ?? $booking->provider->name ?? 'N/A',
                        'provider_phone' => $booking->provider->phone ?? 'N/A',
                        'bid_price' => $booking->price ?? 0,
                        'booking_status' => $bookingStatusDisplay,
                        'request_status' => $requestStatus,
                        'order_no' => $booking->order_no,
                        'booking_created_at' => $booking->created_at,
                        'is_accepted_booking' => $booking->assigned == 1 ? 'Yes' : 'No',
                        'assigned' => $booking->assigned ?? 0,
                        'goto' => $booking->goto ?? 0,
                        'req_status' => $booking->req_status ?? 'N/A',
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
                    'request_status' => $requestStatus,
                    'order_no' => 'N/A',
                    'booking_created_at' => null,
                    'is_accepted_booking' => 'No',
                    'assigned' => 0,
                    'goto' => 0,
                    'req_status' => 'N/A',
                ]);
            }
        }
    }

    /**
     * Get booking status display text
     */
    protected function getBookingStatusDisplay($booking)
    {
        $status = $booking->status ?? 'N/A';
        $reqStatus = $booking->req_status ?? 'N/A';
        $assigned = $booking->assigned ?? 0;
        $goto = $booking->goto ?? 0;

        // Map status based on the same logic as the blade
        if ($status == 'cancel' || $reqStatus == 'cancel') {
            return 'Cancelled';
        }

        if ($status == 'complete_booking' || $status == 'completed' || $reqStatus == 'complete') {
            return 'Completed';
        }

        if ($status == 'in_progress') {
            return 'In Progress';
        }

        if ($reqStatus == 'accept' && $assigned == 0 && $goto == 0) {
            return 'Accept Order';
        }

        if ($reqStatus == 'accept' && $assigned == 1 && $goto == 1) {
            return 'Assigned';
        }

        if ($reqStatus == 'accept' && $assigned == 1 && $goto == 2) {
            return 'Pending Booking';
        }

        return ucfirst($status);
    }

    /**
     * Get status color for Excel
     */
    protected function getStatusColor($status)
    {
        $colors = [
            'Pending Order' => ['background' => 'FFC107', 'text' => '212529'],
            'Accept Order' => ['background' => '28A745', 'text' => 'FFFFFF'],
            'Accepted' => ['background' => '28A745', 'text' => 'FFFFFF'],
            'Assigned' => ['background' => 'FD7E14', 'text' => 'FFFFFF'],
            'Completed' => ['background' => '17A2B8', 'text' => 'FFFFFF'],
            'Cancelled' => ['background' => 'DC3545', 'text' => 'FFFFFF'],
            'Pending Booking' => ['background' => '6F42C1', 'text' => 'FFFFFF'],
            'In Progress' => ['background' => '2196F3', 'text' => 'FFFFFF'],
            'No Bids' => ['background' => '9E9E9E', 'text' => 'FFFFFF'],
            'pending' => ['background' => 'FFC107', 'text' => '212529'],
            'accept' => ['background' => '28A745', 'text' => 'FFFFFF'],
            'accepted' => ['background' => '28A745', 'text' => 'FFFFFF'],
            'in_progress' => ['background' => '2196F3', 'text' => 'FFFFFF'],
            'cancel' => ['background' => 'DC3545', 'text' => 'FFFFFF'],
            'cancelled' => ['background' => 'DC3545', 'text' => 'FFFFFF'],
            'complete_booking' => ['background' => '17A2B8', 'text' => 'FFFFFF'],
            'completed' => ['background' => '17A2B8', 'text' => 'FFFFFF'],
        ];

        return $colors[$status] ?? ['background' => 'F8F9FA', 'text' => '212529'];
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
            'Request Status',
            'Order No',
            'Is Accepted Booking',
            'Bid Created At',
            'Assigned',
            'Goto',
            'Req Status',
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
            $row['request_status'],
            $orderNo,
            $row['is_accepted_booking'],
            $row['booking_created_at'] ? $row['booking_created_at']->format('Y-m-d H:i:s') : 'N/A',
            $row['assigned'],
            $row['goto'],
            $row['req_status'],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_TEXT,  // User Phone
            'H' => NumberFormat::FORMAT_TEXT,  // Provider Phone
            'I' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Price
            'L' => NumberFormat::FORMAT_TEXT,  // Order No
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header styling
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF9800']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I:I')->getNumberFormat()->setFormatCode('"PKR"#,##0.00');

        // Color coding for Booking Status (Column J)
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            for ($row = 2; $row <= $lastRow; $row++) {
                $status = $sheet->getCell('J' . $row)->getValue();
                $color = $this->getStatusColor($status);
                if ($color) {
                    $sheet->getStyle('J' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => $color['text']]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color['background']]],
                    ]);
                }
            }
            
            // Color coding for Request Status (Column K)
            for ($row = 2; $row <= $lastRow; $row++) {
                $status = $sheet->getCell('K' . $row)->getValue();
                $color = $this->getStatusColor($status);
                if ($color) {
                    $sheet->getStyle('K' . $row)->applyFromArray([
                        'font' => ['color' => ['rgb' => $color['text']]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color['background']]],
                    ]);
                }
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
            'K' => 18,  // Request Status
            'L' => 20,  // Order No
            'M' => 18,  // Is Accepted Booking
            'N' => 20,  // Bid Created At
            'O' => 12,  // Assigned
            'P' => 10,  // Goto
            'Q' => 15,  // Req Status
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'Q';
                $lastRow = $sheet->getHighestRow();
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
                $sheet->freezePane('A2');

                // Add summary
                $totalRequests = $this->requests->count();
                $totalBids = $this->rows->where('provider_id', '!=', 'No Bids')->count();
                $acceptedBookings = $this->rows->where('is_accepted_booking', 'Yes')->count();

                $sheet->setCellValue('S1', '💰 Bids & Bookings Summary');
                $sheet->setCellValue('S2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
                $sheet->setCellValue('S3', 'Total Requests: ' . $totalRequests);
                $sheet->setCellValue('S4', 'Total Bids: ' . $totalBids);
                $sheet->setCellValue('S5', 'Accepted Bookings: ' . $acceptedBookings);

                $sheet->getStyle('S1:S5')->getFont()->setSize(10);
                $sheet->getStyle('S1')->getFont()->setBold(true);
                $sheet->getColumnDimension('S')->setWidth(40);
            },
        ];
    }
}