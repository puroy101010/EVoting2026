<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\UtilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $announcements = Announcement::with('creator')
                ->orderBy('createdAt', 'desc')
                ->get();

            return view('admin.announcements', compact('announcements'));
        } catch (Exception $e) {
            return view('admin.announcements', ['announcements' => collect()]);
        }
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
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'status' => 'required|in:active,inactive',
                'priority' => 'required|in:normal,high,urgent'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $announcement = Announcement::create([
                'title' => $request->title,
                'content' => $request->content,
                'status' => $request->status,
                'priority' => $request->priority,
                'createdBy' => Auth::id(),
                'updatedBy' => Auth::id()
            ]);

            // Log activity
            ActivityController::log(['activityCode' => '00119']); // Create announcement

            return response()->json([
                'message' => 'Announcement created successfully',
                'data' => $announcement
            ], 201);
        } catch (Exception $e) {

            UtilityService::logServerError($request, $e, 'Failed to create announcement');
            return response()->json([
                'message' => 'Failed to create announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $announcement = Announcement::with('creator')->findOrFail($id);

            return response()->json([
                'message' => 'Announcement retrieved successfully',
                'data' => $announcement
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Announcement not found'
            ], 404);
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
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'status' => 'required|in:active,inactive',
                'priority' => 'required|in:normal,high,urgent'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $announcement = Announcement::findOrFail($id);

            $announcement->update([
                'title' => $request->title,
                'content' => $request->content,
                'status' => $request->status,
                'priority' => $request->priority
            ]);

            // Log activity
            ActivityController::log(['activityCode' => '00120']); // Update announcement

            return response()->json([
                'message' => 'Announcement updated successfully',
                'data' => $announcement
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $announcement = Announcement::findOrFail($id);
            $announcement->delete();

            // Log activity
            ActivityController::log(['activityCode' => '00121']); // Delete announcement

            return response()->json([
                'message' => 'Announcement deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
