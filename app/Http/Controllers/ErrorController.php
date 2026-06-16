<?php

namespace App\Http\Controllers;


class ErrorController extends Controller
{
    public function index()
    {

        return view('errors.response', ['code' => EApp::clean($code), 'message' => EApp::clean($message)]);
    }
}
