<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AdminAccountSeeder::class,
            ActivityCodeSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            ConfigurationSeeder::class,

        ]);

        if (app()->environment('local')) {
            $this->call([
                NonMemberTestSeeder::class,
                CandidateTestSeeder::class,
                AmendmentTestSeeder::class,
                AgendaTestSeeder::class,
                UserTestSeeder::class
            ]);
        }

        $user = User::findOrFail(1);
        if ($user) {
            $user->assignRole('superadmin');
        }
    }
}
