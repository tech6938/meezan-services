<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProvidersPivotSheet implements FromArray, WithHeadings, WithStyles
{
    protected $bookingRequests;

    public function __construct($bookingRequests)
    {
        $this->bookingRequests = $bookingRequests;
    }

    public function array(): array
    {
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $statuses = ['pending', 'accepted', 'in_progress', 'cancelled', 'completed'];

        // Initialize array
        $data = [];
        foreach ($statuses as $status) {
            $data[$status] = array_fill(0, 12, 0);
        }

        // Count bookings by status and month
        foreach ($this->bookingRequests as $booking) {
            if ($booking->created_at) {
                $monthIndex = $booking->created_at->format('n') - 1;
                $status = $booking->status;

                // Map status variations
                if (in_array($status, ['accept', 'accepted'])) $status = 'accepted';
                if (in_array($status, ['cancel', 'cancelled'])) $status = 'cancelled';
                if (in_array($status, ['complete_booking', 'completed'])) $status = 'completed';

                if (isset($data[$status][$monthIndex])) {
                    $data[$status][$monthIndex]++;
                }
            }
        }

        // Format for output
        $rows = [];
        foreach ($statuses as $status) {
            $row = [ucfirst($status)];
            foreach ($data[$status] as $count) {
                $row[] = $count;
            }
            $row[] = array_sum($data[$status]);
            $rows[] = $row;
        }

        // Add total row
        $totalRow = ['TOTAL'];
        for ($i = 0; $i < 12; $i++) {
            $colTotal = 0;
            foreach ($statuses as $status) {
                $colTotal += $data[$status][$i];
            }
            $totalRow[] = $colTotal;
        }
        $totalRow[] = $this->bookingRequests->count();
        $rows[] = $totalRow;

        return $rows;
    }

    public function headings(): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return array_merge(['Status / Month'], $months, ['Total']);
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9C27B0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Style total row
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:N{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC107']],
        ]);

        return [];
    }
}
