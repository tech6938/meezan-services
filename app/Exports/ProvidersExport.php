<?php

namespace App\Exports;

use App\Models\Provider;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ProvidersExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithColumnFormatting, WithEvents
{
    protected $providers;
    protected $startDate;
    protected $endDate;
    protected $day;
    protected $month;
    protected $year;

    public function __construct($providers, $filters = [])
    {
        $this->providers = $providers;
        $this->startDate = $filters['start_date'] ?? null;
        $this->endDate = $filters['end_date'] ?? null;
        $this->day = $filters['day'] ?? null;
        $this->month = $filters['month'] ?? null;
        $this->year = $filters['year'] ?? null;
    }

    public static function fromRequest(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'day' => $request->input('day'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ];

        $query = Provider::query();

        // Eager load wallet relationship
        $query->with('wallet');

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

        // Build date filter callback
        $dateFilter = self::createDateFilter($filters);

        // Add counts with date filter - Using correct field values
        $query->withCount([
            'bookingRequests as total_orders_count' => $dateFilter,
            'bookingRequests as accepted_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('goto', '1'); // Accepted Orders
            },
            'bookingRequests as pending_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'pending')->where('goto', '0'); // Pending Orders (not accepted)
            },
            'bookingRequests as cancel_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'cancel'); // Cancel Orders
            },
            'bookingRequests as total_bookings_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                // Total Bookings (all bookings)
            },
            'bookingRequests as pending_bookings_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'pending')->where('goto', '2'); // Pending Bookings
            },
            'bookingRequests as in_progress_bookings_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'in_progress'); // In Progress Bookings
            },
            'bookingRequests as completed_bookings_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'complete_booking'); // Completed Bookings
            },
            'bookingRequests as cancel_bookings_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'cancel'); // Cancel Bookings
            },
        ]);

        // Add sum for total earnings from completed bookings
        $query->withSum([
            'bookingRequests as total_amount_earned' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'complete_booking');
            }
        ], 'price');

        // Apply sorting
        $sortable = [
            'id', 'full_name', 'phone', 'created_at',
            'total_orders_count', 'accepted_orders_count', 'pending_orders_count',
            'cancel_orders_count', 'total_bookings_count', 'pending_bookings_count',
            'in_progress_bookings_count', 'completed_bookings_count', 'cancel_bookings_count',
            'total_amount_earned'
        ];

        $sortBy = $request->get('sort_by', 'created_at');
        if (!in_array($sortBy, $sortable, true)) {
            $sortBy = 'created_at';
        }

        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $providers = $query->get();

        return new self($providers, $filters);
    }

    protected static function createDateFilter($filters)
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $day = $filters['day'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;

        return function ($query) use ($startDate, $endDate, $day, $month, $year) {
            // Date range filter (highest priority)
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
                return;
            }

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            // If no date range, check individual filters
            if (!$startDate && !$endDate) {
                if ($day) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
                        $query->whereDate('created_at', $day);
                    } elseif (is_numeric($day)) {
                        $query->whereDay('created_at', intval($day));
                    }
                }

                if ($month) {
                    $query->whereMonth('created_at', intval($month));
                }

                if ($year) {
                    $query->whereYear('created_at', intval($year));
                }
            }
        };
    }

    /**
     * Format services array to readable string
     */
    protected function formatServices($provider)
    {
        // Get services from the 'services' column (stored as array)
        $services = $provider->services;

        if (empty($services)) {
            return 'No Services';
        }

        // If services is already an array
        if (is_array($services)) {
            $serviceNames = [];

            foreach ($services as $service) {
                if (is_array($service)) {
                    // If service has 'name' key
                    if (isset($service['name'])) {
                        $serviceNames[] = $service['name'];
                    }
                    // If service has 'sub_services' array
                    elseif (isset($service['sub_services']) && is_array($service['sub_services'])) {
                        foreach ($service['sub_services'] as $subService) {
                            if (is_string($subService)) {
                                $serviceNames[] = $subService;
                            } elseif (is_array($subService) && isset($subService['name'])) {
                                $serviceNames[] = $subService['name'];
                            }
                        }
                    }
                    // If service is an associative array with values
                    else {
                        $serviceNames[] = implode(', ', $service);
                    }
                }
                // If service is a string
                elseif (is_string($service)) {
                    $serviceNames[] = $service;
                }
            }

            return !empty($serviceNames) ? implode(', ', $serviceNames) : 'No Services';
        }

        // If services is JSON string, decode it
        if (is_string($services)) {
            $decoded = json_decode($services, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($decoded)) {
                return $this->formatServices((object)['services' => $decoded]);
            }
            return $services;
        }

        return 'No Services';
    }

    public function collection()
    {
        return $this->providers;
    }

    public function headings(): array
    {
        return [
            'Sr. No',
            'Partner ID',
            'Partner Name',
            'Phone Number',
            'Services',
            'Total Orders',
            'Accepted Orders',
            'Pending Orders',
            'Cancel Orders',
            'Total Bookings',
            'Pending Bookings',
            'In Progress Bookings',
            'Completed Bookings',
            'Cancel Bookings',
            'Total Earnings (PKR)',
            'Wallet Balance (PKR)',
        ];
    }

    public function map($provider): array
    {
        // Get wallet balance from relationship
        $walletBalance = $provider->wallet ? $provider->wallet->amount : 0;

        // Format services
        $services = $this->formatServices($provider);

        // Get current index (serial number)
        static $serialNumber = 0;
        $serialNumber++;

        // Calculate booking statistics using correct field values
        $bookingRequests = $provider->bookingRequests;

        $totalOrders = $bookingRequests->count() ?? 0;
        $acceptedOrders = $bookingRequests->where('goto', '1')->count() ?? 0;
        $pendingOrders = $bookingRequests->where('status', 'pending')->where('goto', '0')->count() ?? 0;
        $cancelOrders = $bookingRequests->where('status', 'cancel')->count() ?? 0;

        $totalBookings = $bookingRequests->count() ?? 0;
        $pendingBookings = $bookingRequests->where('status', 'pending')->where('goto', '2')->count() ?? 0;
        $inProgressBookings = $bookingRequests->where('status', 'in_progress')->count() ?? 0;
        $completedBookings = $bookingRequests->where('status', 'complete_booking')->count() ?? 0;
        $cancelBookings = $bookingRequests->where('status', 'cancel')->count() ?? 0;

        return [
            $serialNumber,
            $provider->id,
            $provider->full_name ?? $provider->name ?? 'N/A',
            (string) ($provider->phone ?? 'N/A'),
            $services,
            $totalOrders,
            $acceptedOrders,
            $pendingOrders,
            $cancelOrders,
            $totalBookings,
            $pendingBookings,
            $inProgressBookings,
            $completedBookings,
            $cancelBookings,
            (float) ($provider->total_amount_earned ?? 0),
            (float) $walletBalance,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,  // Phone Number
            'O' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Total Earnings
            'P' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Wallet Balance
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

        // Style earnings and wallet columns as PKR currency
        $sheet->getStyle('O:O')->getNumberFormat()->setFormatCode('"PKR"#,##0.00');
        $sheet->getStyle('P:P')->getNumberFormat()->setFormatCode('"PKR"#,##0.00');

        // Set services column to wrap text
        $sheet->getStyle('E:E')->getAlignment()->setWrapText(true);

        // Center align serial number column
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Add color coding for numeric columns
        $lastRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $lastRow; $row++) {
            // Color for Total Orders (Column F)
            $value = $sheet->getCell('F' . $row)->getValue();
            if ($value > 0) {
                $sheet->getStyle('F' . $row)->getFont()->getColor()->setRGB('2196F3');
            }

            // Color for Accepted Orders (Column G)
            $value = $sheet->getCell('G' . $row)->getValue();
            if ($value > 0) {
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('4CAF50');
            }

            // Color for Pending Orders (Column H)
            $value = $sheet->getCell('H' . $row)->getValue();
            if ($value > 0) {
                $sheet->getStyle('H' . $row)->getFont()->getColor()->setRGB('FFC107');
            }

            // Color for Cancel Orders (Column I)
            $value = $sheet->getCell('I' . $row)->getValue();
            if ($value > 0) {
                $sheet->getStyle('I' . $row)->getFont()->getColor()->setRGB('F44336');
            }

            // Color for Total Bookings (Column J)
            $value = $sheet->getCell('J' . $row)->getValue();
            if ($value > 0) {
                $sheet->getStyle('J' . $row)->getFont()->getColor()->setRGB('9C27B0');
            }

            // Color for Pending Bookings (Column K)
            $value = $sheet->getCell('K' . $row)->getValue();
            if ($value > 0) {
                $sheet->getStyle('K' . $row)->getFont()->getColor()->setRGB('FF9800');
            }

            // Color for In Progress Bookings (Column L)
            $value = $sheet->getCell('L' . $row)->getValue();
            if ($value > 0) {
                $sheet->getStyle('L' . $row)->getFont()->getColor()->setRGB('2196F3');
            }

            // Color for Completed Bookings (Column M)
            $value = $sheet->getCell('M' . $row)->getValue();
            if ($value > 0) {
                $sheet->getStyle('M' . $row)->getFont()->getColor()->setRGB('8BC34A');
            }

            // Color for Cancel Bookings (Column N)
            $value = $sheet->getCell('N' . $row)->getValue();
            if ($value > 0) {
                $sheet->getStyle('N' . $row)->getFont()->getColor()->setRGB('F44336');
            }

            // Color for Total Earnings (Column O)
            $value = $sheet->getCell('O' . $row)->getValue();
            if ($value > 0) {
                $sheet->getStyle('O' . $row)->getFont()->getColor()->setRGB('4CAF50');
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // Sr. No
            'B' => 12,  // Partner ID
            'C' => 25,  // Partner Name
            'D' => 18,  // Phone Number
            'E' => 40,  // Services
            'F' => 15,  // Total Orders
            'G' => 18,  // Accepted Orders
            'H' => 18,  // Pending Orders
            'I' => 15,  // Cancel Orders
            'J' => 15,  // Total Bookings (NEW)
            'K' => 18,  // Pending Bookings
            'L' => 20,  // In Progress Bookings
            'M' => 18,  // Completed Bookings
            'N' => 18,  // Cancel Bookings
            'O' => 22,  // Total Earnings
            'P' => 22,  // Wallet Balance
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'P';
                $lastRow = $sheet->getHighestRow();

                // Auto-filter
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");

                // Freeze header
                $sheet->freezePane('B2');

                // Add summary section with colors
                $summaryCol = 'R';
                $sheet->setCellValue($summaryCol . '1', '📊 Provider Summary Report');
                $sheet->setCellValue($summaryCol . '2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
                $sheet->setCellValue($summaryCol . '3', '─────────────────────────');
                $sheet->setCellValue($summaryCol . '4', 'Total Providers: ' . $this->providers->count());
                $sheet->setCellValue($summaryCol . '5', 'Total Orders: ' . $this->providers->sum('total_orders_count'));
                $sheet->setCellValue($summaryCol . '6', 'Total Bookings: ' . $this->providers->sum('total_bookings_count'));
                $sheet->setCellValue($summaryCol . '7', 'Total Earnings: PKR ' . number_format($this->providers->sum('total_amount_earned'), 2));
                $sheet->setCellValue($summaryCol . '8', '─────────────────────────');

                // Calculate summary breakdown
                $totalAccepted = $this->providers->sum('accepted_orders_count');
                $totalPendingOrders = $this->providers->sum('pending_orders_count');
                $totalCancelOrders = $this->providers->sum('cancel_orders_count');
                $totalPendingBookings = $this->providers->sum('pending_bookings_count');
                $totalInProgress = $this->providers->sum('in_progress_bookings_count');
                $totalCompleted = $this->providers->sum('completed_bookings_count');
                $totalCancelBookings = $this->providers->sum('cancel_bookings_count');

                $sheet->setCellValue($summaryCol . '10', '📊 Summary Breakdown:');
                $sheet->setCellValue($summaryCol . '11', '✅ Accepted Orders: ' . $totalAccepted);
                $sheet->setCellValue($summaryCol . '12', '⏳ Pending Orders: ' . $totalPendingOrders);
                $sheet->setCellValue($summaryCol . '13', '❌ Cancel Orders: ' . $totalCancelOrders);
                $sheet->setCellValue($summaryCol . '14', '🔄 Pending Bookings: ' . $totalPendingBookings);
                $sheet->setCellValue($summaryCol . '15', '▶️ In Progress Bookings: ' . $totalInProgress);
                $sheet->setCellValue($summaryCol . '16', '✔️ Completed Bookings: ' . $totalCompleted);
                $sheet->setCellValue($summaryCol . '17', '❌ Cancel Bookings: ' . $totalCancelBookings);

                // Style the summary
                $sheet->getStyle($summaryCol . '1:' . $summaryCol . '17')->getFont()->setSize(10);
                $sheet->getStyle($summaryCol . '1')->getFont()->setBold(true);
                $sheet->getStyle($summaryCol . '3')->getFont()->setBold(true);
                $sheet->getStyle($summaryCol . '8')->getFont()->setBold(true);
                $sheet->getStyle($summaryCol . '10')->getFont()->setBold(true);

                // Color code the summary rows
                $sheet->getStyle($summaryCol . '11')->getFont()->getColor()->setRGB('4CAF50');
                $sheet->getStyle($summaryCol . '12')->getFont()->getColor()->setRGB('FFC107');
                $sheet->getStyle($summaryCol . '13')->getFont()->getColor()->setRGB('F44336');
                $sheet->getStyle($summaryCol . '14')->getFont()->getColor()->setRGB('FF9800');
                $sheet->getStyle($summaryCol . '15')->getFont()->getColor()->setRGB('2196F3');
                $sheet->getStyle($summaryCol . '16')->getFont()->getColor()->setRGB('8BC34A');
                $sheet->getStyle($summaryCol . '17')->getFont()->getColor()->setRGB('F44336');

                $sheet->getColumnDimension($summaryCol)->setWidth(45);

                // Add color legend
                $legendRow = 19;
                $sheet->setCellValue($summaryCol . $legendRow, '🎨 Provider Booking Details:');
                $sheet->setCellValue($summaryCol . ($legendRow + 1), '🟢 Accepted / Completed / Earnings');
                $sheet->setCellValue($summaryCol . ($legendRow + 2), '🟡 Pending Orders');
                $sheet->setCellValue($summaryCol . ($legendRow + 3), '🟠 Pending Bookings');
                $sheet->setCellValue($summaryCol . ($legendRow + 4), '🔵 In Progress / Total Orders');
                $sheet->setCellValue($summaryCol . ($legendRow + 5), '🟣 Total Bookings');
                $sheet->setCellValue($summaryCol . ($legendRow + 6), '🔴 Cancelled');
                $sheet->getStyle($summaryCol . $legendRow . ':' . $summaryCol . ($legendRow + 6))->getFont()->setSize(9);
                $sheet->getStyle($summaryCol . $legendRow)->getFont()->setBold(true);
            },
        ];
    }
}
