<?php

namespace Database\Seeders;

use App\Models\NonMemberAccount;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NonMemberTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $user = new User();
        $user->id = 14;
        $user->email = 'nonmember9999@example.com';
        $user->password = null;
        $user->role = 'non-member';
        // $user->isActive = true;
        $user->createdBy = 1; // Assuming 1 is the ID of the user who created this user
        $user->save();


        $user = new User();
        $user->id = 15;
        $user->email = 'nonmember0000@example.com';
        $user->password = null;
        $user->role = 'non-member';
        $user->createdBy = 1; // Assuming 1 is the ID of the user who created this user
        $user->save();

        $user = new User();
        $user->id = 16;
        $user->email = 'nonmember7777@example.com';
        $user->password = null;
        $user->role = 'non-member';
        $user->createdBy = 1; // Assuming 1 is the ID of the user who created this user
        $user->save();


        $nonMember = new NonMemberAccount();
        $nonMember->nonmemberAccountNo = '9999';
        $nonMember->firstName = 'Ruel';
        $nonMember->middleName = 'B.';
        $nonMember->lastName = 'Querioso';
        $nonMember->userId = 14; // Assuming user ID is 14
        $nonMember->isGM = false;
        $nonMember->isActive = true;
        $nonMember->createdBy = 1; // Assuming admin user ID is 1
        $nonMember->save();


        $nonMember = new NonMemberAccount();
        $nonMember->nonmemberAccountNo = '0000';
        $nonMember->firstName = 'Bob';
        $nonMember->middleName = 'C.';
        $nonMember->lastName = 'Smith';
        $nonMember->userId = 15; // Assuming user ID is 15
        $nonMember->isGM = true;
        $nonMember->isActive = true;
        $nonMember->createdBy = 1; // Assuming admin user ID is 1
        $nonMember->save();

        $nonMember = new NonMemberAccount();
        $nonMember->nonmemberAccountNo = '7777';
        $nonMember->firstName = 'Brad';
        $nonMember->middleName = 'C.';
        $nonMember->lastName = 'Pitt';
        $nonMember->userId = 16; // Assuming user ID is 16
        $nonMember->isGM = false;
        $nonMember->isActive = false;
        $nonMember->createdBy = 1; // Assuming admin user ID is 1
        $nonMember->save();
    }
}
