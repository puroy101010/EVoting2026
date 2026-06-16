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
                ]
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
                'config' => 'terms_and_conditions_online',
                'value' => null,
                'description' => null,
                'type' => 'text'
            ],

            [
                'config' => 'terms_and_conditions_proxy',
                'value' => null,
                'description' => null,
                'type' => 'text'
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
