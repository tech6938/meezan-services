<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeSuperAdmin extends Command
{
    protected $signature = 'admin:make-super-admin {id=1}';
    protected $description = 'Mark an admin as super admin';

    public function handle()
    {
        $id = $this->argument('id');

        $updated = DB::table('admins')
            ->where('id', $id)
            ->update([
                'is_super_admin' => 1,
            ]);

        if ($updated) {
            $admin = DB::table('admins')->where('id', $id)->first();
            $this->info("✓ Admin #{$id} ({$admin->name}) is now a super admin!");
        } else {
            $this->error("✗ Admin #{$id} not found!");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
