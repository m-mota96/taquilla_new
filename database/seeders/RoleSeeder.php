<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Crea los roles que usa la aplicación (HomeController y routes/web.php).
     *
     * @return void
     */
    public function run()
    {
        foreach (['customer', 'cashier', 'admin_change_center'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
