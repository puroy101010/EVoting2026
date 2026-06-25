<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuration;
use Carbon\Carbon;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (app()->environment('local')) {
            Configuration::insert([
                [
                    'config' => 'vote_in_person_start',
                    'value' => Carbon::now(),
                    'description' => 'Start time for in-person voting',
                    'type' => 'datetime'
                ],
                [
                    'config' => 'vote_in_person_end',
                    'value' => Carbon::now()->addHours(100),
                    'description' => 'End time for in-person voting',
                    'type' => 'datetime'
                ],
                [
                    'config' => 'vote_by_proxy_start',
                    'value' => Carbon::now(),
                    'description' => 'Start time for voting by proxy',
                    'type' => 'datetime'
                ],
                [
                    'config' => 'vote_by_proxy_end',
                    'value' => Carbon::now()->addHours(100),
                    'description' => 'End time for voting by proxy',
                    'type' => 'datetime'
                ],
                [
                    'config' => 'terms_and_conditions_online',
                    'value' => "By confirming my participation, I, [voter_name], hereby declare my intent to exercise my right to vote at the Special Stockholders’ Meeting of Valley Golf & Country Club, Inc. on June 27,  2026. In accordance with the Data Privacy Act of 2012 (Republic Act No. 10173), I authorize Valley Golf & Country Club, Inc. to collect, record, organize, use, and disclose my personal information solely for verifying my identity and facilitating my voting at the meeting. I acknowledge that this consent is given only for this specific purpose and does not waive any of my rights under the Data Privacy Act of 2012 or other applicable laws.
                                Voting Rule:
                                For voting purposes, each share of stock shall be entitled to one (1) vote. 
                                Disclaimer:
                                The information contained on this site has been compiled for the convenience and general information of the stockholders. Valley Golf and Country Club, Inc. has no right to change the information you provided on the website. Although Valley Golf provided the voting platform to facilitate an E-voting system for stockholders related matters, we have endeavored to ensure appropriate checks are in place and to enable true and fair voting, and that data is not accessed by any person when the voting window is open. However, Valley Golf shall not be liable in case of any unauthorized access by any person.",
                    'description' => null,
                    'type' => 'text'
                ],
                [
                    'config' => 'terms_and_conditions_proxy',
                    'value' => "By confirming my participation, I, [voter_name], hereby declare my intent to exercise my right to vote in absentia at the Special Stockholders’ Meeting of Valley Golf & Country Club, Inc. on June 27,  2026. In accordance with the Data Privacy Act of 2012 (Republic Act No. 10173), I authorize Valley Golf & Country Club, Inc. to collect, record, organize, use, and disclose my personal information solely for verifying my identity and facilitating my  voting at the meeting. I acknowledge that this consent is given only for this specific purpose and does not waive any of my rights under the Data Privacy Act of 2012 or other applicable laws.
                                Voting Rule:
                                For voting purposes, each share of stock shall be entitled to one (1) vote. 
                                Disclaimer:
                                The information contained on this site has been compiled for the convenience and general information of the stockholders. Valley Golf and Country Club, Inc. has no right to change the information you provided on the website. Although Valley Golf provided the voting platform to facilitate an E-voting system for stockholders related matters, we have endeavored to ensure appropriate checks are in place and to enable true and fair voting, and that data is not accessed by any person when the voting window is open. However, Valley Golf shall not be liable in case of any unauthorized access by any person.By confirming my participation, I, [voter_name], hereby declare my intent to exercise my right to vote in absentia at the Special Stockholders’ Meeting of Valley Golf & Country Club, Inc. on June 27,  2026. In accordance with the Data Privacy Act of 2012 (Republic Act No. 10173), I authorize Valley Golf & Country Club, Inc. to collect, record, organize, use, and disclose my personal information solely for verifying my identity and facilitating my  voting at the meeting. I acknowledge that this consent is given only for this specific purpose and does not waive any of my rights under the Data Privacy Act of 2012 or other applicable laws.
                                Voting Rule:
                                For voting purposes, each share of stock shall be entitled to one (1) vote. 
                                Disclaimer:
                                The information contained on this site has been compiled for the convenience and general information of the stockholders. Valley Golf and Country Club, Inc. has no right to change the information you provided on the website. Although Valley Golf provided the voting platform to facilitate an E-voting system for stockholders related matters, we have endeavored to ensure appropriate checks are in place and to enable true and fair voting, and that data is not accessed by any person when the voting window is open. However, Valley Golf shall not be liable in case of any unauthorized access by any person.",
                    'description' => null,
                    'type' => 'text'
                ],

            ]);
        } else {
            Configuration::insert([
                [
                    'config' => 'vote_in_person_start',
                    'value' => null,
                    'description' => 'Start time for in-person voting',
                    'type' => 'datetime'
                ],
                [
                    'config' => 'vote_in_person_end',
                    'value' => null,
                    'description' => 'End time for in-person voting',
                    'type' => 'datetime'
                ],
                [
                    'config' => 'vote_by_proxy_start',
                    'value' => null,
                    'description' => 'Start time for voting by proxy',
                    'type' => 'datetime'
                ],
                [
                    'config' => 'vote_by_proxy_end',
                    'value' => null,
                    'description' => 'End time for voting by proxy',
                    'type' => 'datetime'
                ]






            ]);
        }









        Configuration::insert([
            [
                'config' => 'votes_per_share',
                'value' => '9',
                'description' => 'Number of votes per share',
                'type' => 'integer'
            ],

            [
                'config' => 'amendment_enabled',
                'value' => true,
                'description' => 'Enable amendment of votes',
                'type' => 'boolean'
            ],

            [
                'config' => 'bod_module_enabled',
                'value' => true,
                'description' => 'Enable Board of Director module',
                'type' => 'boolean'
            ],

            [
                'config' => 'otp_login_enabled',
                'value' => true,
                'description' => 'Enable OTP for two-factor authentication',
                'type' => 'boolean'
            ],

            [
                'config' => 'send_voting_confirmation_receipt_enabled',
                'value' => false,
                'description' => 'Send voting confirmation receipt to users',
                'type' => 'boolean'
            ],

            [
                'config' => 'terms_and_conditions_general',
                'value' => null,
                'description' => null,
                'type' => 'text'
            ],


            //setting thhat togle is amendment is restricted to general manager only or not
            [
                'config' => 'amendment_restricted_to_gm',
                'value' => false,
                'description' => 'Restrict amendment of votes to General Manager only',
                'type' => 'boolean'
            ]
        ]);
    }
}
