<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AmendmentController;
use App\Http\Controllers\AmendmentProxyController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\AdminBallotController;
use App\Http\Controllers\AvailableVoteInquiryController;
use App\Http\Controllers\BallotController;
use App\Http\Controllers\BODProxyController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\NonMemberController;
use App\Http\Controllers\OnsiteMeetingRegistration;
use App\Http\Controllers\OnsiteMeetingRegistrationController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\ProxyVotingBallotController;
use App\Http\Controllers\PublicDocumentViewerController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DeveloperStockController;
use App\Http\Controllers\StockholderController;
use App\Http\Controllers\StockholderOnlineBallotController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoteController;
use App\Http\Middleware\EnsureUserCanVoteProxy;
use App\Http\Middleware\EnsureUserCanVoteStockholderOnline;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsAuthorizedVoter;
use App\Services\AmendmentProxyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;



Route::get('test/{id}', function ($id) {




    return;
    if (app()->environment() !== 'local') {
        abort(403, 'Unauthorized action.');
    }

    Auth::loginUsingId($id);
    if (!in_array(Auth::user()->role, ['admin', 'superadmin'])) {
        return redirect('/user/vote');
    }
    return redirect('/admin');
});

Route::get('/', [HomePageController::class, 'index'])->name('home');

Route::prefix('admin')->middleware([EnsureUserIsAdmin::class])->group(function () {


    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');


    Route::resource('role', RoleController::class);
    Route::get('role/{id}/users', [RoleController::class, 'users']);
    Route::resource('amendment', AmendmentController::class);
    Route::resource('agenda', AgendaController::class);
    Route::resource('candidate', CandidateController::class);
    Route::resource('non-member', NonMemberController::class);
    Route::resource('admin-account', AdminController::class);

    Route::get('stockholder/load', [StockholderController::class, 'load_stockholders']);
    Route::get('stockholder/assignee', [StockholderController::class, 'load_option_assignees']);

    Route::get('stockholder/export', [StockholderController::class, 'export']);
    Route::get('stockholder/user', [StockholderController::class, 'load_filter_data_users']);
    // Route::get('stockholder/import', [StockholderController::class, 'import']);

    Route::resource('stockholder', StockholderController::class)->only(['index', 'store', 'show', 'edit', 'update']);


    Route::get('stock', [DeveloperStockController::class, 'index']);
    Route::resource('announcement', AnnouncementController::class);

    Route::post('amendment-proxy/{id}/cancel', [AmendmentProxyController::class, 'cancel']);
    Route::post('amendment-proxy/{id}/audit', [AmendmentProxyController::class, 'audit']);
    Route::get('amendment-proxy/export', [AmendmentProxyController::class, 'export']);
    Route::get('amendment-proxy/masterlist', [AmendmentProxyController::class, 'masterlist']);
    Route::get('amendment-proxy/masterlist/export', [AmendmentProxyController::class, 'exportMasterlist']);
    Route::get('amendment-proxy/active/export', [AmendmentProxyController::class, 'exportActiveProxies']);
    Route::get('amendment-proxy/summary', [AmendmentProxyController::class, 'summary']); //used
    Route::get('amendment-proxy/summary/{id}', [AmendmentProxyController::class, 'proxy_list']); //used
    Route::resource('amendment-proxy', AmendmentProxyController::class);

    Route::post('bod-proxy/{id}/cancel', [BODProxyController::class, 'cancel']);
    Route::post('bod-proxy/{id}/audit', [BODProxyController::class, 'audit']);
    Route::get('bod-proxy/export', [BODProxyController::class, 'export']);
    Route::get('bod-proxy/masterlist', [BODProxyController::class, 'masterlist']);
    Route::get('bod-proxy/masterlist/export', [BODProxyController::class, 'exportMasterlist']);
    Route::get('bod-proxy/active/export', [BODProxyController::class, 'exportActiveProxies']);
    Route::get('bod-proxy/print-by-assignee', [BODProxyController::class, 'printProxyByAssignee']);
    Route::get('bod-proxy/summary', [BODProxyController::class, 'summary']);
    Route::get('bod-proxy/summary/{id}', [BODProxyController::class, 'proxy_list']);


    Route::get('proxy/history/{id}', [BODProxyController::class, 'history']);


    Route::resource('bod-proxy', BODProxyController::class);



    Route::resource('document', DocumentController::class);

    Route::get('activity', [ActivityController::class, 'index']);
    Route::get('activity/filter', [ActivityController::class, 'load_users']);
    Route::get('activity/load', [ActivityController::class, 'load_activity']);

    Route::get('ballots', [AdminBallotController::class, 'index']);
    Route::get('ballots/export', [AdminBallotController::class, 'export']);
    Route::get('ballots/masterlist', [BallotController::class, 'masterlist']);
    Route::get('ballots/preview/{id}', [AdminBallotController::class, 'preview']);

    Route::get('setting', [SettingController::class, 'index']);
    Route::post('setting/date/update', [SettingController::class, 'update']);
    Route::post('setting/date/remove', [SettingController::class, 'destroy']);
    Route::post('setting/amendment-module/update', [SettingController::class, 'toggleAmendmentModule']);
    Route::post('setting/bod-module/update', [SettingController::class, 'toggleBodModule']);

    Route::post('setting/amendment-restriction/update', [SettingController::class, 'toggleAmendmentRestriction']);

    Route::post('setting/otp-login/update', [SettingController::class, 'toggleOtpLogin']);
    Route::post('setting/votes-per-share/update', [SettingController::class, 'updateVotesPerShare']);
    Route::post('setting/voting-receipt/update', [SettingController::class, 'toggleVotingReceipt']);
    Route::post('setting/terms-and-conditions/update', [SettingController::class, 'updateTermsAndConditions']);

    Route::post('password/change', [AdminController::class, 'changePassword']);
    Route::post('password/reset', [AdminController::class, 'resetPassword']);
    Route::get('login/attempt/details', [OTPController::class, 'login_details']);

    Route::get('logout', [AdminController::class, 'logout']);

    Route::get('attendance', [AttendanceController::class, 'index']);
    Route::get('attendance/export', [AttendanceController::class, 'export']);
    Route::get('attendance/print', [AttendanceController::class, 'print']);


    Route::post('otp/override', [OTPController::class, 'override']);

    Route::resource('available-vote-inquiry', AvailableVoteInquiryController::class)->only(['index', 'show', 'search']);
});




Route::get('admin/login', [AdminLoginController::class, 'index'])->name('admin.login');
Route::post('admin/login', [AdminLoginController::class, 'login']);




Route::prefix('otp')->group(function () {
    Route::post('request', [OTPController::class, 'store']);
    Route::post('verify', [OTPController::class, 'verify']);
});


Route::prefix('user/ballot')->middleware([EnsureUserCanVoteStockholderOnline::class])->group(function () {
    Route::post('stockholder-online', [StockholderOnlineBallotController::class, 'store']);
    Route::post('stockholder-online/submit', [StockholderOnlineBallotController::class, 'submit']);
    Route::post('stockholder-online/{id}/summary', [StockholderOnlineBallotController::class, 'summary']);
    Route::get('stockholder-online/{id}', [StockholderOnlineBallotController::class, 'show']);
});


Route::prefix('user/ballot')->middleware([EnsureUserCanVoteProxy::class])->group(function () {
    Route::post('proxy-voting', [ProxyVotingBallotController::class, 'store']);
    Route::post('proxy-voting/submit', [ProxyVotingBallotController::class, 'submit']);
    Route::post('proxy-voting/{id}/summary', [ProxyVotingBallotController::class, 'summary']);
    Route::get('proxy-voting/{id}', [ProxyVotingBallotController::class, 'show']);
});




Route::get('user/vote', [VoteController::class, 'index'])->middleware([EnsureUserIsAuthorizedVoter::class]);
Route::get('user/login', [LoginController::class, 'index'])->name('user.login');


Route::get('user/onsite-register', [OnsiteMeetingRegistrationController::class, 'store']);

Route::get('logout', [UserController::class, 'logout']);


Route::resource('public-documents', PublicDocumentViewerController::class)->only(['show']);
