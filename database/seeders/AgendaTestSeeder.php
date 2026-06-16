<?php

namespace Database\Seeders;

use App\Models\Agenda;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgendaTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {



        $agenda = new Agenda();
        $agenda->agendaCode = 'AG01';
        $agenda->agendaDesc = 'To approve the minutes of the 2024 Annual stockholders’ meeting.';
        $agenda->agendaLink = 'http://example.com/agenda1';
        $agenda->sorter = 1;
        $agenda->isActive = true;
        $agenda->createdBy = 1; // Assuming admin user ID is 1
        $agenda->save();

        $agenda = new Agenda();
        $agenda->agendaCode = 'AG02';
        $agenda->agendaDesc = 'To approve the Company’s 2025 Annual Report and Audited Financial Statements.';
        $agenda->agendaLink = 'http://example.com/agenda2';
        $agenda->sorter = 2;
        $agenda->isActive = true;
        $agenda->createdBy = 1; // Assuming admin user ID is 1
        $agenda->save();

        $agenda = new Agenda();
        $agenda->agendaCode = 'AG03';
        $agenda->agendaDesc = 'To confirm and ratify all acts and resolutions of the Board of Directors & Management (July 1, 2024 to June 30, 2025 inclusive).';
        $agenda->agendaLink = 'http://example.com/agenda3';
        $agenda->sorter = 3;
        $agenda->isActive = true;
        $agenda->createdBy = 1; // Assuming admin user ID is 1
        $agenda->save();

        $agenda = new Agenda();
        $agenda->agendaCode = 'AG04';
        $agenda->agendaDesc = 'To confirm and ratify all acts and resolutions of the Board of Directors & Management (July 1, 2024 to June 30, 2025 inclusive).';
        $agenda->agendaLink = 'http://example.com/agenda4';
        $agenda->sorter = 4;
        $agenda->isActive = true;
        $agenda->createdBy = 1; // Assuming admin user ID is 1
        $agenda->save();
    }
}
