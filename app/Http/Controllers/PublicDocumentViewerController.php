<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\UtilityService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PublicDocumentViewerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {

            Log::info('Public document view requested', ['documentId' => $id]);
            $document = Document::findOrFail($id);

            // Define the file path and get its MIME type
            $file = storage_path('app/private/' . $document->path . '/' . $document->filename);
            $mime = File::mimeType($file);

            // Set the response headers
            $headers = ['Content-Type' => $mime];

            // Log the activity for this document
            ActivityController::log(['activityCode' => '00024', 'documentId' => $document->documentId, 'remarks' => 'Viewed public document -- <span class="font-weight-bold">' . $document->title . '</span>']);

            Log::info('Public document displayed', ['documentId' => $id]);
            // Return a response to download the file
            return response()->download($file, $document->filename, $headers, 'inline');
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, "Error occurred while showing public document");

            return view('errors.response', ['code' => 500, 'message' => EApp::SERVER_ERROR]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
