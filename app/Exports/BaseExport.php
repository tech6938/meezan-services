<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BaseExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    private $data;
    private $headings;
    private $sheetName;

    public function __construct(array $data, array $headings, string $sheetName = 'Sheet1')
    {
        $this->data = $data;
        $this->headings = $headings;
        $this->sheetName = $sheetName;
    }

    /**
     * Return the array to be exported
     */
    public function array(): array
    {
        return $this->data;
    }

    /**
     * Define headings for the export
     */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * Apply styles to the worksheet
     */
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

        // Center align all data cells
        $sheet->getStyle('A2:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [];
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        $columns = [];
        $columnCount = count($this->headings);

        for ($i = 1; $i <= $columnCount; $i++) {
            $columns[chr(64 + $i)] = 20;
        }

        return $columns;
    }

    /**
     * Set sheet name
     */
    public function title(): string
    {
        return $this->sheetName;
    }
}
