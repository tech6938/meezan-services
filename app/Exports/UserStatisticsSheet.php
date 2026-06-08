<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UserStatisticsSheet implements FromArray, WithHeadings, WithStyles
{
    protected $users;
    protected $bookings;

    public function __construct($users, $bookings, $filters = [])
    {
        $this->users = $users;
        $this->bookings = $bookings;
    }

    public function array(): array
    {
        $totalUsers = $this->users->count();
        $totalBookings = $this->bookings->count();
        $totalAmount = $this->bookings->sum('price');

        $completedCount = $this->bookings->whereIn('status', ['complete_booking', 'completed'])->count();
        $pendingCount = $this->bookings->where('status', 'pending')->count();
        $acceptedCount = $this->bookings->whereIn('status', ['accept', 'accepted'])->count();
        $cancelledCount = $this->bookings->whereIn('status', ['cancel', 'cancelled'])->count();
        $inProgressCount = $this->bookings->where('status', 'in_progress')->count();

        // Monthly breakdown
        $monthlyData = [];
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        foreach ($months as $month) {
            $monthlyData[$month] = 0;
        }

        foreach ($this->bookings as $booking) {
            if ($booking->created_at) {
                $month = $booking->created_at->format('F');
                if (isset($monthlyData[$month])) {
                    $monthlyData[$month]++;
                }
            }
        }

        $rows = [];
        $rows[] = ['REPORT STATISTICS', ''];
        $rows[] = ['', ''];
        $rows[] = ['Generated On:', now()->format('Y-m-d H:i:s')];
        $rows[] = ['', ''];
        $rows[] = ['SUMMARY', ''];
        $rows[] = ['Total Users', $totalUsers];
        $rows[] = ['Total Bookings', $totalBookings];
        $rows[] = ['Total Amount (PKR)', number_format($totalAmount, 2)];
        $rows[] = ['', ''];
        $rows[] = ['BOOKING STATUS BREAKDOWN', ''];
        $rows[] = ['Completed', $completedCount];
        $rows[] = ['Accepted', $acceptedCount];
        $rows[] = ['Pending', $pendingCount];
        $rows[] = ['In Progress', $inProgressCount];
        $rows[] = ['Cancelled', $cancelledCount];
        $rows[] = ['', ''];
        $rows[] = ['MONTHLY BOOKINGS', ''];

        foreach ($monthlyData as $month => $count) {
            $rows[] = [$month, $count];
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']],
        ]);

        return [];
    }
}
