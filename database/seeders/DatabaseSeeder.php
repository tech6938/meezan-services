<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\BookingRequestsSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(BookingRequestsSeeder::class);

        // User::factory()->create([
        //     'name' => 'Admin',
        //     'email' => 'admin@gmail.com',
        //     'role' => 1,
        //     'password' => Hash::make('123456'),
        // ]);
    }
}
