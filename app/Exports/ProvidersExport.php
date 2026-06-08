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

        // Add counts with date filter
        $query->withCount([
            'bookingRequests as total_orders_count' => $dateFilter,
            'bookingRequests as accepted_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->whereIn('status', ['accept', 'accepted']);
            },
            'bookingRequests as pending_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'pending');
            },
            'bookingRequests as in_progress_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->where('status', 'in_progress');
            },
            'bookingRequests as cancelled_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->whereIn('status', ['cancel', 'cancelled']);
            },
            'bookingRequests as completed_orders_count' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->whereIn('status', ['complete_booking', 'completed']);
            },
        ]);

        // Add sum for earnings from completed bookings
        $query->withSum([
            'bookingRequests as total_amount_earned' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->whereIn('status', ['complete_booking', 'completed']);
            }
        ], 'price');

        // Apply sorting
        $sortable = [
            'id', 'full_name', 'phone', 'created_at',
            'total_orders_count', 'accepted_orders_count', 'pending_orders_count',
            'in_progress_orders_count', 'cancelled_orders_count', 'completed_orders_count',
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
            'Sr. No',  // NEW: Serial number column
            'Partner ID',
            'Partner Name',
            'Phone Number',
            'Services',
            'Total Orders',
            'Accepted Orders',
            'Pending Orders',
            'In Progress Orders',
            'Cancelled Orders',
            'Completed Orders',
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

        // Get current index (serial number) - will be set in collection iteration
        static $serialNumber = 0;
        $serialNumber++;

        return [
            $serialNumber,  // NEW: Serial number
            $provider->id,
            $provider->full_name ?? $provider->name ?? 'N/A',
            (string) ($provider->phone ?? 'N/A'),
            $services,
            (int) ($provider->total_orders_count ?? 0),
            (int) ($provider->accepted_orders_count ?? 0),
            (int) ($provider->pending_orders_count ?? 0),
            (int) ($provider->in_progress_orders_count ?? 0),
            (int) ($provider->cancelled_orders_count ?? 0),
            (int) ($provider->completed_orders_count ?? 0),
            (float) ($provider->total_amount_earned ?? 0),
            (float) $walletBalance,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,  // Phone Number (now column D)
            'L' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Total Earnings (now column L)
            'M' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Wallet Balance (now column M)
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
        $sheet->getStyle('L:L')->getNumberFormat()->setFormatCode('"PKR"#,##0.00');
        $sheet->getStyle('M:M')->getNumberFormat()->setFormatCode('"PKR"#,##0.00');

        // Set services column to wrap text (now column E)
        $sheet->getStyle('E:E')->getAlignment()->setWrapText(true);

        // Center align serial number column
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // Sr. No (small width)
            'B' => 12,  // Partner ID
            'C' => 25,  // Partner Name
            'D' => 18,  // Phone Number
            'E' => 40,  // Services
            'F' => 15,  // Total Orders
            'G' => 18,  // Accepted Orders
            'H' => 18,  // Pending Orders
            'I' => 18,  // In Progress Orders
            'J' => 18,  // Cancelled Orders
            'K' => 18,  // Completed Orders
            'L' => 22,  // Total Earnings
            'M' => 22,  // Wallet Balance
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'M';
                $lastRow = $sheet->getHighestRow();

                // Auto-filter
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");

                // Freeze header (keep first row visible, first column visible)
                $sheet->freezePane('B2');
            },
        ];
    }
}
