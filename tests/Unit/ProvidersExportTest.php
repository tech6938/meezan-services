<?php

namespace Tests\Unit;

use App\Exports\ProvidersExport;
use App\Models\Provider;
use Tests\TestCase;

class ProvidersExportTest extends TestCase
{
    public function test_headings_match_expected_report_columns()
    {
        $export = new ProvidersExport(collect());

        $this->assertSame([
            'Provider ID',
            'Provider Name',
            'Phone Number',
            'Total Amount Earned',
            'Total Orders Count',
            'Accepted Orders Count',
            'Pending Orders Count',
            'In Progress Orders Count',
            'Cancelled Orders Count',
            'Completed Orders Count',
        ], $export->headings());
    }

    public function test_map_returns_expected_report_row()
    {
        $provider = new Provider([
            'full_name' => 'John Doe',
            'phone' => '+1234567890',
        ]);
        $provider->id = 10;
        $provider->total_amount_earned = 1500.5;
        $provider->total_orders_count = 20;
        $provider->accepted_orders_count = 12;
        $provider->pending_orders_count = 3;
        $provider->in_progress_orders_count = 2;
        $provider->cancelled_orders_count = 1;
        $provider->completed_orders_count = 14;

        $export = new ProvidersExport(collect([$provider]));
        $row = $export->map($provider);

        $this->assertSame([
            10,
            'John Doe',
            '+1234567890',
            1500.5,
            20,
            12,
            3,
            2,
            1,
            14,
        ], $row);
    }
}
