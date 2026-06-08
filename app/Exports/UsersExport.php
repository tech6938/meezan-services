<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithColumnFormatting, WithEvents
{
    protected $users;
    protected $startDate;
    protected $endDate;
    protected $day;
    protected $month;
    protected $year;

    public function __construct($users, $filters = [])
    {
        $this->users = $users;
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

        $query = User::query();

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Build date filter callback for bookings
        $dateFilter = self::createDateFilter($filters);

        // Add booking counts with date filter
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

        // Add sum for total amount spent by user
        $query->withSum([
            'bookingRequests as total_amount_spent' => function ($q) use ($dateFilter) {
                $dateFilter($q);
                $q->whereIn('status', ['complete_booking', 'completed']);
            }
        ], 'price');

        // Apply sorting
        $sortable = [
            'id', 'name', 'phone', 'email', 'created_at', 'status',
            'total_orders_count', 'accepted_orders_count', 'pending_orders_count',
            'in_progress_orders_count', 'cancelled_orders_count', 'completed_orders_count',
            'total_amount_spent'
        ];

        $sortBy = $request->get('sort_by', 'created_at');
        if (!in_array($sortBy, $sortable, true)) {
            $sortBy = 'created_at';
        }

        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->get();

        return new self($users, $filters);
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

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        return [
            'Sr. No',
            'User ID',
            'User Name',
            'Phone Number',
            'Total Orders',
            'Total Amount Spent (PKR)',
            'Accepted Orders',
            'Pending Orders',
            'In Progress Orders',
            'Cancelled Orders',
            'Completed Orders',
            'Status',
            'Registered Date',
        ];
    }

    public function map($user): array
    {
        static $serialNumber = 0;
        $serialNumber++;

        return [
            $serialNumber,
            $user->id,
            $user->name ?? 'N/A',
            (string) ($user->phone ?? 'N/A'),
            (int) ($user->total_orders_count ?? 0),
            (float) ($user->total_amount_spent ?? 0),
            (int) ($user->accepted_orders_count ?? 0),
            (int) ($user->pending_orders_count ?? 0),
            (int) ($user->in_progress_orders_count ?? 0),
            (int) ($user->cancelled_orders_count ?? 0),
            (int) ($user->completed_orders_count ?? 0),
            ucfirst($user->status ?? 'N/A'),
            $user->created_at ? $user->created_at->format('Y-m-d') : 'N/A',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,  // Phone Number
            'F' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Total Amount Spent
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
                'startColor' => ['rgb' => '2196F3'], // Blue color for users
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Center align serial number column
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style amount column as PKR currency
        $sheet->getStyle('F:F')->getNumberFormat()->setFormatCode('"PKR"#,##0.00');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // Sr. No
            'B' => 10,  // User ID
            'C' => 25,  // User Name
            'D' => 18,  // Phone Number
            'E' => 15,  // Total Orders
            'F' => 22,  // Total Amount Spent
            'G' => 18,  // Accepted Orders
            'H' => 18,  // Pending Orders
            'I' => 18,  // In Progress Orders
            'J' => 18,  // Cancelled Orders
            'K' => 18,  // Completed Orders
            'L' => 15,  // Status
            'M' => 15,  // Registered Date
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

                // Freeze header
                $sheet->freezePane('B2');
            },
        ];
    }
}
