<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingRequestsSeeder extends Seeder
{
    public function run(): void
    {
        $data = [];

        for ($i = 1; $i <= 1000; $i++) {
            $data[] = [
                'provider_id'     => rand(1, 50),
                'shopkeeper_id'   => rand(1, 50),
                'user_id'         => rand(1, 500),
                'request_id'      => rand(1000, 99999),
                'order_no'        => 'ORD-' . strtoupper(Str::random(8)),
                'price'           => rand(100, 5000) + (rand(0, 99) / 100),
                'cash_on_delivery'=> rand(0, 1),
                'payment_type'    => ['cash', 'card', 'wallet'][array_rand(['cash','card','wallet'])],
                'req_status'      => ['pending', 'accepted', 'completed'][array_rand(['pending','accepted','completed'])],
                'status'          => ['pending', 'accepted', 'completed'][array_rand(['pending','accepted','completed'])],
                'cancel_by'       => rand(0, 1) ? 'user' : null,
                'assigned'        => rand(0, 1),
                'goto'            => rand(0, 1),
                'cancel_reason'   => null,
                'details'         => 'Dummy booking request #' . $i,
                'audio'           => null,
                'is_seen'         => rand(0, 1),
                'seen_at'         => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        DB::table('booking_requests')->insert($data);
    }
}