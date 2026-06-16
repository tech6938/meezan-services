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

class UserSummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting, WithEvents
{
    protected $users;
    protected $filters;

    public function __construct($users, $filters = [])
    {
        $this->users = $users;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        return [
            'User ID',
            'User Name',
            'Phone Number',
            'Address',
            'Total Amount Spent (PKR)',
            'Total Orders Request',
            'Accepted Orders Request',
            'Pending Orders Request',
            'Cancel Orders',
            'Total Bookings',
            'In Progress Bookings',
            'Pending Bookings',
            'Completed Bookings',
            'Cancel Bookings',
            'Registered Date',
        ];
    }

    public function map($user): array
    {
        // Get user's address
        $address = $user->addresses->first();
        $addressString = 'N/A';
        if ($address) {
            $addressParts = [];
            if ($address->address) $addressParts[] = $address->address;
            if ($address->city) $addressParts[] = $address->city;
            if ($address->state) $addressParts[] = $address->state;
            if ($address->country) $addressParts[] = $address->country;
            $addressString = implode(', ', $addressParts);
        }

        // Get all booking requests for the user
        $bookingRequests = $user->bookingRequests;

        // Calculate booking statistics using the correct field values
        $totalBookings = $bookingRequests->count() ?? 0;
        $acceptedOrders = $bookingRequests->where('goto', '1')->count() ?? 0;
        $pendingOrders = $bookingRequests->where('status', 'pending')->where('goto', '0')->count() ?? 0;
        $ongoingBookings = $bookingRequests->where('status', 'pending')->where('goto', '2')->count() ?? 0;
        $inProgressBookings = $bookingRequests->where('status', 'in_progress')->count() ?? 0;
        $completedBookings = $bookingRequests->where('status', 'complete_booking')->count() ?? 0;
        $cancelBookings = $bookingRequests->where('status', 'cancel')->count() ?? 0;

        // Total Orders Request (all booking requests)
        $totalOrdersRequest = $bookingRequests->count();

        return [
            $user->id,
            $user->name ?? 'N/A',
            (string) ($user->phone ?? 'N/A'),
            $addressString,
            (float) ($user->total_amount_spent ?? 0),
            $totalOrdersRequest,
            $acceptedOrders,
            $pendingOrders,
            $cancelBookings, // Cancel Orders (from cancel status)
            $totalBookings,
            $inProgressBookings, // In Progress Bookings
            $ongoingBookings, // Pending Bookings (goto = 2)
            $completedBookings,
            $cancelBookings, // Cancel Bookings
            $user->created_at ? $user->created_at->format('Y-m-d') : 'N/A',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,  // Phone Number
            'E' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Total Amount Spent
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Style for header
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2196F3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E:E')->getNumberFormat()->setFormatCode('"PKR"#,##0.00');

        // Add color coding for numeric columns based on values
        $lastRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $lastRow; $row++) {
            // Color for Total Amount Spent (Column E)
            $totalAmount = $sheet->getCell('E' . $row)->getValue();
            if ($totalAmount > 0) {
                $sheet->getStyle('E' . $row)->getFont()->getColor()->setRGB('4CAF50');
            }

            // Color for Total Orders (Column F)
            $totalOrders = $sheet->getCell('F' . $row)->getValue();
            if ($totalOrders > 0) {
                $sheet->getStyle('F' . $row)->getFont()->getColor()->setRGB('2196F3');
            }

            // Color for Accepted Orders (Column G)
            $accepted = $sheet->getCell('G' . $row)->getValue();
            if ($accepted > 0) {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('4CAF50');
            }

            // Color for Pending Orders (Column H)
            $pending = $sheet->getCell('H' . $row)->getValue();
            if ($pending > 0) {
                $sheet->getStyle('H' . $row)->getFont()->getColor()->setRGB('FFC107');
            }

            // Color for Cancel Orders (Column I)
            $cancel = $sheet->getCell('I' . $row)->getValue();
            if ($cancel > 0) {
                $sheet->getStyle('I' . $row)->getFont()->getColor()->setRGB('F44336');
            }

            // Color for Total Bookings (Column J)
            $totalBookings = $sheet->getCell('J' . $row)->getValue();
            if ($totalBookings > 0) {
                $sheet->getStyle('J' . $row)->getFont()->getColor()->setRGB('9C27B0');
            }

            // Color for In Progress Bookings (Column K)
            $inProgress = $sheet->getCell('K' . $row)->getValue();
            if ($inProgress > 0) {
                $sheet->getStyle('K' . $row)->getFont()->getColor()->setRGB('2196F3');
            }

            // Color for Pending Bookings (Column L)
            $pendingBookings = $sheet->getCell('L' . $row)->getValue();
            if ($pendingBookings > 0) {
                $sheet->getStyle('L' . $row)->getFont()->getColor()->setRGB('FF9800');
            }

            // Color for Completed Bookings (Column M)
            $completed = $sheet->getCell('M' . $row)->getValue();
            if ($completed > 0) {
                $sheet->getStyle('M' . $row)->getFont()->getColor()->setRGB('8BC34A');
            }

            // Color for Cancel Bookings (Column N)
            $cancelBookings = $sheet->getCell('N' . $row)->getValue();
            if ($cancelBookings > 0) {
                $sheet->getStyle('N' . $row)->getFont()->getColor()->setRGB('F44336');
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // User ID
            'B' => 25,  // User Name
            'C' => 18,  // Phone Number
            'D' => 35,  // Address
            'E' => 22,  // Total Amount Spent
            'F' => 18,  // Total Orders Request
            'G' => 22,  // Accepted Orders Request
            'H' => 22,  // Pending Orders Request
            'I' => 15,  // Cancel Orders
            'J' => 15,  // Total Bookings
            'K' => 18,  // In Progress Bookings
            'L' => 18,  // Pending Bookings
            'M' => 18,  // Completed Bookings
            'N' => 18,  // Cancel Bookings
            'O' => 15,  // Registered Date
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'O';
                $lastRow = $sheet->getHighestRow();
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
                $sheet->freezePane('A2');

                // Add title and summary with colors
                $summaryStartRow = 1;
                $summaryCol = 'Q';

                $sheet->setCellValue($summaryCol . '1', '📊 User Summary Report');
                $sheet->setCellValue($summaryCol . '2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
                $sheet->setCellValue($summaryCol . '3', '─────────────────────────');
                $sheet->setCellValue($summaryCol . '4', 'Total Users: ' . $this->users->count());
                $sheet->setCellValue($summaryCol . '5', 'Total Orders: ' . $this->users->sum(function($user) {
                    return $user->bookingRequests->count();
                }));
                $sheet->setCellValue($summaryCol . '6', 'Total Amount: PKR ' . number_format($this->users->sum('total_amount_spent'), 2));
                $sheet->setCellValue($summaryCol . '7', 'Total Bookings: ' . $this->users->sum(function($user) {
                    return $user->bookingRequests->count();
                }));
                $sheet->setCellValue($summaryCol . '8', '─────────────────────────');

                // Calculate summary statistics across all users
                $totalAcceptedOrders = $this->users->sum(function($user) {
                    return $user->bookingRequests->where('goto', '1')->count();
                });
                $totalPendingOrders = $this->users->sum(function($user) {
                    return $user->bookingRequests->where('status', 'pending')->where('goto', '0')->count();
                });
                $totalCancelOrders = $this->users->sum(function($user) {
                    return $user->bookingRequests->where('status', 'cancel')->count();
                });
                $totalInProgressBookings = $this->users->sum(function($user) {
                    return $user->bookingRequests->where('status', 'in_progress')->count();
                });
                $totalPendingBookings = $this->users->sum(function($user) {
                    return $user->bookingRequests->where('status', 'pending')->where('goto', '2')->count();
                });
                $totalCompletedBookings = $this->users->sum(function($user) {
                    return $user->bookingRequests->where('status', 'complete_booking')->count();
                });

                $sheet->setCellValue($summaryCol . '10', '📊 Summary Breakdown:');
                $sheet->setCellValue($summaryCol . '11', '✅ Accepted Orders: ' . $totalAcceptedOrders);
                $sheet->setCellValue($summaryCol . '12', '⏳ Pending Orders: ' . $totalPendingOrders);
                $sheet->setCellValue($summaryCol . '13', '❌ Cancel Orders: ' . $totalCancelOrders);
                $sheet->setCellValue($summaryCol . '14', '▶️ In Progress Bookings: ' . $totalInProgressBookings);
                $sheet->setCellValue($summaryCol . '15', '🔄 Pending Bookings: ' . $totalPendingBookings);
                $sheet->setCellValue($summaryCol . '16', '✔️ Completed Bookings: ' . $totalCompletedBookings);
                $sheet->setCellValue($summaryCol . '17', '❌ Cancel Bookings: ' . $totalCancelOrders);

                // Style the summary
                $sheet->getStyle($summaryCol . '1:' . $summaryCol . '17')->getFont()->setSize(10);
                $sheet->getStyle($summaryCol . '1')->getFont()->setBold(true);
                $sheet->getStyle($summaryCol . '3')->getFont()->setBold(true);
                $sheet->getStyle($summaryCol . '8')->getFont()->setBold(true);
                $sheet->getStyle($summaryCol . '10')->getFont()->setBold(true);

                // Color code the summary rows
                $sheet->getStyle($summaryCol . '11')->getFont()->getColor()->setRGB('4CAF50'); // Green - Accepted
                $sheet->getStyle($summaryCol . '12')->getFont()->getColor()->setRGB('FFC107'); // Yellow - Pending Orders
                $sheet->getStyle($summaryCol . '13')->getFont()->getColor()->setRGB('F44336'); // Red - Cancel Orders
                $sheet->getStyle($summaryCol . '14')->getFont()->getColor()->setRGB('2196F3'); // Blue - In Progress
                $sheet->getStyle($summaryCol . '15')->getFont()->getColor()->setRGB('FF9800'); // Orange - Pending Bookings
                $sheet->getStyle($summaryCol . '16')->getFont()->getColor()->setRGB('8BC34A'); // Light Green - Completed
                $sheet->getStyle($summaryCol . '17')->getFont()->getColor()->setRGB('F44336'); // Red - Cancel Bookings

                $sheet->getColumnDimension($summaryCol)->setWidth(45);

                // Add color legend
                $legendRow = 19;
                $sheet->setCellValue($summaryCol . $legendRow, '🎨 Color Legend:');
                $sheet->setCellValue($summaryCol . ($legendRow + 1), '🟢 Accepted Orders / Completed');
                $sheet->setCellValue($summaryCol . ($legendRow + 2), '🟡 Pending Orders');
                $sheet->setCellValue($summaryCol . ($legendRow + 3), '🟠 Pending Bookings');
                $sheet->setCellValue($summaryCol . ($legendRow + 4), '🔵 In Progress');
                $sheet->setCellValue($summaryCol . ($legendRow + 5), '🔴 Cancelled');
                $sheet->getStyle($summaryCol . $legendRow . ':' . $summaryCol . ($legendRow + 5))->getFont()->setSize(9);
                $sheet->getStyle($summaryCol . $legendRow)->getFont()->setBold(true);
            },
        ];
    }
}
