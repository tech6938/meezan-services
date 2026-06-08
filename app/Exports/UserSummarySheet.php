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
            'D' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2196F3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F:F')->getNumberFormat()->setFormatCode('"PKR"#,##0.00');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8, 'B' => 10, 'C' => 25, 'D' => 18, 'E' => 15,
            'F' => 22, 'G' => 18, 'H' => 18, 'I' => 18, 'J' => 18,
            'K' => 18, 'L' => 15, 'M' => 15,
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

                // Add title
                $sheet->setCellValue('O1', '📊 User Summary Report');
                $sheet->setCellValue('O2', 'Generated on: ' . now()->format('Y-m-d H:i:s'));
                $sheet->setCellValue('O3', 'Total Users: ' . $this->users->count());
                $sheet->setCellValue('O4', 'Total Orders: ' . $this->users->sum('total_orders_count'));
                $sheet->setCellValue('O5', 'Total Amount: PKR ' . number_format($this->users->sum('total_amount_spent'), 2));

                $sheet->getStyle('O1:O5')->getFont()->setSize(10);
                $sheet->getStyle('O1')->getFont()->setBold(true);
                $sheet->getColumnDimension('O')->setWidth(40);
            },
        ];
    }
}
