<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProvidersMultiSheetExport implements WithMultipleSheets
{
    protected $providers;
    protected $bookingRequests;

    public function __construct($providers, $bookingRequests)
    {
        $this->providers = $providers;
        $this->bookingRequests = $bookingRequests;
    }

    public function sheets(): array
    {
        return [
            'Summary by Partner' => new ProvidersSummarySheet($this->providers, $this->bookingRequests),
            'Detailed Bookings' => new ProvidersDetailSheet($this->bookingRequests),
            'Pivot Table' => new ProvidersPivotSheet($this->bookingRequests),
        ];
    }
}
