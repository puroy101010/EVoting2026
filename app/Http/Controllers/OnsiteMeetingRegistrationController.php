<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnsiteMeetingRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        if (!Auth::check()) {

            return redirect('user/login');
        }


        if (!in_array(Auth::user()->role, ['stockholder', 'corp-rep', 'non-member'])) {

            return view('errors.response', ['code' => 403, 'message' => 'Forbidden']);
        }



        ActivityController::log(['activityCode' => '00104']);

        return redirect('https://docs.google.com/forms/d/e/1FAIpQLScrGMexyVVD_auYhp_69nDJvlr8yzwyO5EvgXINd9nOQ_pB0g/viewform?usp=sharing&ouid=110478211756799432363');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
