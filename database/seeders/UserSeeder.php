<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {



        $user = new User();
        $user->id = 1;
        $user->email = 'almazan.froilan1010@gmail.com';
        $user->password = bcrypt('catchMeIfYouCan_1010');
        $user->role = 'superadmin';
        // $user->isActive = true;
        $user->createdBy = 1; // Assuming 1 is the ID of the user who created this user

        $user->save();
    }
}
