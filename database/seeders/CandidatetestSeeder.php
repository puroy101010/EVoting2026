<?php

namespace Database\Seeders;

use App\Models\Candidate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CandidateTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $candidate = new Candidate();
        $candidate->firstName = '	Francis';
        $candidate->middleName = 'C';
        $candidate->lastName = 'Aguilar';
        $candidate->isActive = true;
        $candidate->type = 'independent';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();



        $candidate = new Candidate();
        $candidate->firstName = 'Carlo';
        $candidate->middleName = 'J';
        $candidate->lastName = 'Carpio';
        $candidate->isActive = true;
        $candidate->type = 'independent';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();

        $candidate = new Candidate();
        $candidate->firstName = 'Michael';
        $candidate->middleName = 'T.';
        $candidate->lastName = 'Echavez';
        $candidate->isActive = true;
        $candidate->type = 'regular';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();


        $candidate = new Candidate();
        $candidate->firstName = 'Ma. Cecilia';
        $candidate->middleName = 'Ng';
        $candidate->lastName = 'Esguerra';
        $candidate->isActive = true;
        $candidate->type = 'regular';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();


        $candidate = new Candidate();
        $candidate->firstName = 'Federico';
        $candidate->middleName = 'H.';
        $candidate->lastName = 'Feliciano';
        $candidate->isActive = true;
        $candidate->type = 'regular';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();

        $candidate = new Candidate();
        $candidate->firstName = 'Jose';
        $candidate->middleName = 'R.';
        $candidate->lastName = 'Guiang';
        $candidate->isActive = true;
        $candidate->type = 'regular';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();


        $candidate = new Candidate();
        $candidate->firstName = 'Constantine';
        $candidate->middleName = 'L.';
        $candidate->lastName = 'Kohchet-Chua';
        $candidate->isActive = true;
        $candidate->type = 'regular';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();


        $candidate = new Candidate();
        $candidate->firstName = 'Ricky';
        $candidate->middleName = 'S.';
        $candidate->lastName = 'Libago';
        $candidate->isActive = true;
        $candidate->type = 'regular';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();


        $candidate = new Candidate();
        $candidate->firstName = 'Edward';
        $candidate->middleName = 'P.';
        $candidate->lastName = 'Lim';
        $candidate->isActive = true;
        $candidate->type = 'independent';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();



        $candidate = new Candidate();
        $candidate->firstName = 'Perdo';
        $candidate->middleName = 'H.';
        $candidate->lastName = 'Maniego, Jr.';
        $candidate->isActive = true;
        $candidate->type = 'regular';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();


        $candidate = new Candidate();
        $candidate->firstName = 'Ron Nelson';
        $candidate->middleName = 'P.';
        $candidate->lastName = 'See';
        $candidate->isActive = true;
        $candidate->type = 'regular';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();


        $candidate = new Candidate();
        $candidate->firstName = 'Rio Sesinando';
        $candidate->middleName = 'E .';
        $candidate->lastName = 'Venturanza';
        $candidate->isActive = true;
        $candidate->type = 'regular';
        $candidate->createdBy = 1; // Assuming admin user ID is 1
        $candidate->save();
    }
}
