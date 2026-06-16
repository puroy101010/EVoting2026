<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminAccount;

class AdminAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // INSERT INTO `admin_accounts` (`adminId`, `firstName`, `middleName`, `lastName`, `level`, `userId`, `isActive`, `createdAt`, `updatedAt`, `deletedAt`, `restoredAt`, `createdBy`, `updatedBy`, `deletedBy`, `restoredBy`) VALUES (NULL, 'Froilan', NULL, 'Asuncion', '3', '1', '1', '2023-09-12 01:34:45', NULL, NULL, NULL, '1', NULL, NULL, NULL);

        $adminAccount = new AdminAccount();
        $adminAccount->firstName = 'Froilan';
        $adminAccount->middleName = null;
        $adminAccount->lastName = 'Asuncion';
        $adminAccount->isActive = true;
        $adminAccount->isDefault = true; // Assuming this is a default admin account
        $adminAccount->userId = 1; // Assuming user ID 1 is the superadmin user

        $adminAccount->createdBy = 1; // Assuming 1 is the ID of the user who created this admin account
        $adminAccount->save();
    }
}
