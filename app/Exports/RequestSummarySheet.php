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

class RequestSummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting, WithEvents
{
    protected $requests;
    protected $filters;

    public function __construct($requests, $filters = [])
    {
        $this->requests = $requests;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->requests;
    }

    public function headings(): array
    {
        return [
            'Sr. No',
            'Request ID',
            'User Name',
            'User Phone',
            'Category',
            'Sub Category',
            'Description',
            'Live Latitude',
            'Live Longitude',
            'Saved Address',
            'Media Files',
            'Total Bids',
            'Status',
            'Created At',
        ];
    }

    public function map($request): array
    {
        static $serialNumber = 0;
        $serialNumber++;

        // Get saved address
        $savedAddress = 'N/A';
        if ($request->address) {
            $addressParts = [];
            if ($request->address->address) $addressParts[] = $request->address->address;
            if ($request->address->area) $addressParts[] = $request->address->area;
            if ($request->address->city) $addressParts[] = $request->address->city;
            $savedAddress = implode(', ', $addressParts);
        }

        // Get media files
        $mediaFiles = 'N/A';
        if ($request->file && is_array($request->file) && count($request->file) > 0) {
            $mediaFiles = implode("\n", $request->file);
        }

        return [
            $serialNumber,
            $request->id,
            $request->user->name ?? 'N/A',
            (string) ($request->user->phone ?? 'N/A'),
            $request->category->name ?? 'N/A',
            $request->subCategory->name ?? 'N/A',
            $request->desc ?? 'N/A',
            (string) ($request->lat ?? 'N/A'),
            (string) ($request->lang ?? 'N/A'),
            $savedAddress,
            $mediaFiles,
            $request->bookingRequests->count(),
            ucfirst($request->status),
            $request->created_at ? $request->created_at->format('Y-m-d H:i:s') : 'N/A',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,  // User Phone
            'H' => NumberFormat::FORMAT_TEXT,  // Latitude
            'I' => NumberFormat::FORMAT_TEXT,  // Longitude
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
        $sheet->getStyle('K:K')->getAlignment()->setWrapText(true); // Wrap media files

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // Sr. No
            'B' => 10,  // Request ID
            'C' => 25,  // User Name
            'D' => 18,  // User Phone
            'E' => 20,  // Category
            'F' => 20,  // Sub Category
            'G' => 40,  // Description
            'H' => 15,  // Live Latitude
            'I' => 15,  // Live Longitude
            'J' => 40,  // Saved Address
            'K' => 50,  // Media Files
            'L' => 12,  // Total Bids
            'M' => 15,  // Status
            'N' => 20,  // Created At
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'N';
                $lastRow = $sheet->getHighestRow();
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
                $sheet->freezePane('A2');
            },
        ];
    }
}
