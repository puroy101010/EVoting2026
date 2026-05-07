<?php

namespace Database\Seeders;

use App\Models\Amendment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AmendmentTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $amendment = new Amendment();
        $amendment->amendmentCode = 'AM01';
        $amendment->amendmentDesc = 'I. ARTICLE II, SECONDARY PURPOSES to offer and/or sell its proprietary shares to the public as may be allowed by the Securities and Exchange Commission under existing laws and its implementing rules and regulations.';
        $amendment->amendmentLink = 'http://example.com/amendment1';
        $amendment->sorter = 1;
        $amendment->isActive = true;
        $amendment->createdBy = 1; // Assuming admin user ID is 1
        $amendment->save();

        $amendment = new Amendment();
        $amendment->amendmentCode = 'AM02';
        $amendment->amendmentDesc = '	II. ARTICLE III That the place where the principal office of the corporation is to be established or located is Main Clubhouse, Valley Golf & Country Club, Inc., Don Celso S. Tuason Avenue, Victoria Valley, Barangay Munting Dilaw, Antipolo, Rizal, Philippines.';
        $amendment->amendmentLink = 'http://example.com/amendment2';
        $amendment->sorter = 2;
        $amendment->isActive = true;
        $amendment->createdBy = 1; // Assuming admin user ID is 1
        $amendment->save();

        $amendment = new Amendment();
        $amendment->amendmentCode = 'AM03';
        $amendment->amendmentDesc = 'III. ARTICLE IV That said corporation shall have perpetual existence.';
        $amendment->amendmentLink = 'http://example.com/amendment3';
        $amendment->sorter = 3;
        $amendment->isActive = true;
        $amendment->createdBy = 1; // Assuming admin user ID is 1
        $amendment->save();



        $amendment = new Amendment();
        $amendment->amendmentCode = 'AM04';
        $amendment->amendmentDesc = 'IV. ARTICLE VII That the capital stock of said corporation is Sixteen Million Two Hundred Thousand Pesos (PhP16,200,000.00) divided into One Thousand Eight Hundred (1,800) common shares of the par value of Nine Thousand Pesos (PhP9,000) each (as amended on September 13, 1981). The stock certificates shall be issued within sixty (60) business days from the date of their full payment. Any person who owns or buys a share shall be qualified before the actual sale or transfer of the share or certificate. Shareholders shall have the right to share in the assets of the corporation upon its dissolution or liquidation.';
        $amendment->amendmentLink = 'http://example.com/amendment4';
        $amendment->sorter = 4;
        $amendment->isActive = true;
        $amendment->createdBy = 1; // Assuming admin user ID is 1
        $amendment->save();
    }
}
