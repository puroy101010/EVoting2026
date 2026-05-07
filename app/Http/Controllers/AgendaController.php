<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Http\Requests\EditAgendaRequest;
use App\Http\Requests\IndexAgendaRequest;
use App\Http\Requests\StoreAgendaRequest;
use App\Services\AgendaService;
use App\Services\UtilityService;
use Exception;


class AgendaController extends Controller
{

    private $agendaService;


    public function __construct(AgendaService $agendaService)
    {
        $this->agendaService = $agendaService;
    }

    public function index(IndexAgendaRequest $request)
    {
        return $this->agendaService->index($request);
    }

    public function store(StoreAgendaRequest $request)
    {
        return $this->agendaService->store($request);
    }

    public function update(EditAgendaRequest $request)
    {
        return $this->agendaService->update($request);
    }
}
