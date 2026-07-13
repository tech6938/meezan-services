<?php

namespace Database\Seeders;

use Database\Seeders\BookingRequestsSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(BookingRequestsSeeder::class);
        $this->call(SuperAdminSeeder::class);
    }
}
