<?php

namespace App\Http\Controllers;


use App\Models\Document;
use App\Models\Announcement;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function index()
    {
        return view('user.homepage', [
            'documents' => Document::selectRaw('documentId, title')->where('isActive', 1)->orderBy('documentId', 'ASC')->get(),
            'announcements' => Announcement::orderBy('createdAt', 'DESC')->get()
        ]);
    }
}
