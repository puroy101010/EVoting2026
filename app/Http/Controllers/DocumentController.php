<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    public function index()
    {

        $data = Document::withTrashed()->with('createdBy.adminAccount')->get()->toarray();

        return view('admin.documents', ['data' => $data]);
    }


    public function edit(Request $request)
    {

        $action = "PROXY_EDIT";

        $memberId = null;

        try {

            $action = "EDIT_DOCUMENT";

            $validator = Validator::make($request->all(), [

                'id'              => 'required|numeric|min:1',
                'proxy_status'    => 'required|numeric|min:0|max:3',
                'proxy_download'  => 'required|numeric|min:0|max:1',


            ]);


            if ($validator->fails()) {

                AppController::systemm_log(400, $action, json_encode($validator->errors()));

                $result = array(

                    "success" => false,
                    "message" => "Bad Request",
                    "data" => json_decode(json_encode($validator->errors()), true)

                );

                return $result;
            }



            $id = $request->input('id');

            $stockholder = DB::select("SELECT users.stockholder FROM user_details LEFT JOIN users ON users.accountNo = user_details.accountNo WHERE user_details.id = :id", [":id" => $id]);

            $stockholderName =  $stockholder[0]->stockholder;

            $proxyStatus       = $request->input('proxy_status');
            $proxyDownload     = $request->input('proxy_download');


            $param = [":proxyStatus" => $proxyStatus, ":allowProxyDownload" => $proxyDownload, ":id" => $id];

            DB::insert("UPDATE user_details SET proxyStatus = :proxyStatus, allowProxyDownload = :allowProxyDownload WHERE id = :id", $param);

            AppController::activity_log($action, "edited a document | " . $stockholderName, $id);

            $result = array("success" => true, "message" => "Document has been updated.");

            return json_encode($result);
        } catch (Exception $e) {

            AppController::systemm_log(500, $action, $e->getMessage(), "SYSTEM_ERROR_CATCH", $memberId);
        }
    }


    // done 2022-08-26
    //view uploaded SPA and proxy forms
    public function view_file(Request $request)
    {

        try {

            $id         = $request->route()->parameter('id');

            $docType    = $request->route()->parameter('doc_type');


            if (!in_array($docType, ['proxy', 'spa']) or !is_numeric($id)) {

                return view('errors.response', ['code' => 400, 'message' => 'Bad Request']);
            }


            $table          = $docType === 'proxy' ? 'proxy_uploads' : 'spa_uploads';
            $fileDetails    = DB::table($table)->where('id', $id)->first();


            if ($fileDetails === null) {

                \Log::channel('evoting')->info('VIEW SPA/PROXY: File not found', ["userId" => Auth::user()->id, "email" => Auth::user()->email, 'table' => $table, 'fieldId' => $id]);

                ActivityController::log(['code' => '00046', 'remarks' => 'File not foundd. ID: ' . $id . " TABLE: $table"]);

                return view('errors.response', ['code' => 404, 'message' => 'File not found']);
            }


            $file = storage_path('app/' . $fileDetails->path . '/' . $fileDetails->filename);


            $mime = File::mimeType($file);


            $headers =  ['Content-Type' => $mime];


            $filename = $fileDetails->origFilename;


            $code = $docType === 'proxy' ? '00011' : '00012';


            $field = $docType === 'proxy' ? 'proxyUploadId' : 'spaUploadId';

            ActivityController::log(['code' => $code, $field => $id, 'accountNo' => null, 'accountKey' => $fileDetails->accountKey]);

            \Log::channel('evoting')->info('Viewed a document', ["userId" => Auth::user()->id, "email" => Auth::user()->email, 'table' => $table, 'fieldId' => $id]);

            return response()->download($file, $filename, $headers, 'inline');
        } catch (Exception $e) {

            \Log::channel('evoting')->critical('VIEW SPA/PROXY: Exception | ' . $e->getMessage(), ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

            return view('errors.response', ['code' => 500, 'message' => EApp::SERVER_ERROR]);
        }
    }


    // view document in user side in user side
    public function view_user_file(Request $request)
    {

        try {


            $filename = $request->route()->parameter('title');

            if ($filename === null) {

                return view('errors.response', ['code' => 400, 'message' => 'Bad Request']);
            }



            $fileInfo = DB::table('documents')->where('filename', $filename)->where('deleted', 0)->where('archived', 0)->first();


            if ($fileInfo === null) {


                \Log::channel('evoting')->alert('File not found: ' . $filename, ["userId" => Auth::user()->id, "email" => Auth::user()->email, 'title' => $filename]);


                ActivityController::log(['code' => '00046', 'remarks' => 'Filen not found: ' . $filename]);

                return view('errors.response', ['code' => 404, 'message' => 'File not found']);
            }


            $file = storage_path('app/' . $fileInfo->path . '/' . $fileInfo->filename);


            $mime = File::mimeType($file);


            $headers =  ['Content-Type' => $mime];


            \Log::channel('evoting')->info('Viewed document: ' . $fileInfo->title, ["userId" => Auth::user()->id, "email" => Auth::user()->email, 'documentId' => $fileInfo->documentId]);


            ActivityController::log(['code' => '00024', 'documentId' => $fileInfo->documentId]);


            return response()->download($file, $fileInfo->title, $headers, 'inline');
        } catch (Exception $e) {


            \Log::channel('evoting')->critical('VIEW DOCUMENT: Exception | ' . $e->getMessage(), ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

            return view('errors.response', ['code' => 500, 'message' => EApp::SERVER_ERROR]);
        }
    }

    // view document in admin
    public function show($id)
    {

        try {

            // Find the document by ID, or throw a 404 error if not found
            $document = Document::findOrFail($id);

            // Check if the document is soft-deleted
            if ($document->trashed()) {
                // Check if the user has the required role to view deleted documents
                if (!in_array(Auth::user()->role, ['admin', 'superadmin'])) {
                    // Return a 404 error response with a message
                    return view('errors.response', [
                        'code' => 404,
                        'message' => 'Viewing deleted documents is restricted to superadmin.'
                    ]);
                }
            }

            // Define the file path and get its MIME type
            $file = storage_path('app/' . $document->path . '/' . $document->filename);
            $mime = File::mimeType($file);

            // Set the response headers
            $headers = ['Content-Type' => $mime];

            // Log the activity for this document
            ActivityController::log(['activityCode' => '00024', 'documentId' => $document->documentId]);

            // Return a response to download the file
            return response()->download($file, $document->filename, $headers, 'inline');
        } catch (Exception $e) {

            Log::error($e);
            return view('errors.response', ['code' => 500, 'message' => EApp::SERVER_ERROR]);
        }
    }







    // done 2022-08-26
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:100',
                'file' => 'file|max:25000|mimes:jpeg,jpg,png,pdf,pptx'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return response()->json(["message" => $errors, "field" => $validator->errors()->keys()[0]], 400);
            }

            $user = Auth::user();
            $file = $request->file('file');
            $title = $request->input('title');
            $dateTime = EApp::datetime();

            if ($file !== null) {
                $path = 'system/user/document/' . date('Y', strtotime($dateTime));
                $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $file->extension();
                $filename = date('ymdhis') . md5(uniqid(rand(), true)) . '.' . $file->extension();
                $mimeType = $file->getMimeType();

                $file->storeAs($path, $filename);

                DB::beginTransaction();

                $documentId = EApp::generate_id('documents', 'documentId');

                $dbUserFile = Document::insert([
                    'documentId' => $documentId,
                    'title' => $title,
                    'filename' => $filename,
                    'origName' => $origName,
                    'createdBy' => $user->id,
                    'path' => $path,
                    'mimeType' => $mimeType,
                    'createdAt' => $dateTime
                ]);

                if (!$dbUserFile) {

                    return response()->json(['message' => "An error encountered while uploading the file."], 500);
                }

                ActivityController::log(['ActivityCode' => '00060', 'documentId' => $documentId]);

                DB::commit();

                return response()->json(['message' => 'File has been uploaded successfully']);
            }

            return response()->json([], 500);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json([], 500);
        }
    }


    public function manage_document(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'id'      => 'required|string|max:30',
                'action'  => 'required|in:delete,archive',
            ]);


            if ($validator->fails()) {

                $err = EApp::obj_to_array($validator->errors());

                return response()->json(["message" => $err[array_key_first($err)][0], "field" => array_key_first($err)], 400);
            }


            $user   = Auth::user();

            $id     = $request->input('id');
            $action = $request->input('action');

            $dateTime = EApp::datetime();


            DB::beginTransaction();


            if ($action === 'delete') {



                $dbUpdate = DB::table('documents')
                    ->where('documentId', $id)
                    ->where('deleted', 0)

                    ->update([
                        'deleted' => 1,
                        'deletedBy' => $user->id,
                        'deletedAt' => $dateTime
                    ]);


                ActivityController::log(['code' => '00062', 'documentId' => $id]);
            } else {
                $dbUpdate = DB::table('documents')
                    ->where('documentId', $id)
                    ->where('archived', 0)
                    ->where('deleted', 0)

                    ->update([
                        'archived' => 1,
                        'archivedBy' => $user->id,
                        'archivedAt' => $dateTime
                    ]);

                ActivityController::log(['code' => '00061', 'documentId' => $id]);
            }




            if ($dbUpdate === 1) {

                DB::commit();

                return response()->json(['message' => 'The document has been successfully ' . ($action === 'archive' ? 'archived.' : 'deleted.')], 200);
            }

            return response()->json(['message' => 'Bad request'], 400);
        } catch (Exception $e) {

            return response()->json(['message' => "An error encountered. Please contact you admin."], 500);
        }
    }
}
