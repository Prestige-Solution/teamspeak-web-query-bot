<?php

use App\Http\Controllers\BackendController;
use App\Http\Controllers\banner\BannerController;
use App\Http\Controllers\channel\ChannelController;
use App\Http\Controllers\client\ClientController;
use App\Http\Controllers\sys\LoginController;
use App\Http\Controllers\sys\ServerController;
use App\Http\Controllers\ts3Config\BadNameController;
use App\Http\Controllers\ts3Config\Ts3ConfigController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')->group(function (){
    Route::get('/',[LoginController::class,'viewLogin'])->name('public.view.login');
});

Route::middleware(['throttle:login'])->group(function (){
    Route::post('/logging-in',[LoginController::class,'authenticate'])->name('logging-in');
});

Route::middleware(['auth'])->group(function (){
    Route::prefix('start')->name('start.')->middleware('auth')->group(function (){
        Route::get('/dashboard',[BackendController::class,'viewBackendDashboard'])->name('view.dashboard');
//        Route::get('/invite-code',[BackendController::class,'viewUseInvite'])->name('view.useInviteCode');
    });

    Route::prefix('dashboard')->name('backend.')->group(function (){
        Route::get('/bot-control-center',[BackendController::class,'viewBotControlCenter'])->name('view.botControlCenter');
        Route::get('/password-reset',[BackendController::class, 'viewChangePassword'])->name('view.changePassword');
        Route::post('/password-change',[BackendController::class,'updateChangePassword'])->name('update.changePassword');

        Route::prefix('server-settings')->group(function (){
            Route::get('/server-list',[ServerController::class,'viewServerList'])->name('view.serverList');
            Route::post('/create-new-server',[BackendController::class,'upsertServer'])->name('create.createOrUpdateServer');
            Route::get('/edit-server',[BackendController::class,'viewUpdateServer'])->name('view.createOrUpdateServer');
        });

        Route::prefix('logs')->group(function (){
            Route::get('/bot-logs',[BackendController::class,'viewBotLogs'])->name('view.botLogs');
        });

        Route::prefix('bad-names')->group(function (){
            Route::get('/bad-names',[BadNameController::class,'viewListBadNames'])->name('view.badNames');
            Route::get('/global-bad-names',[BadNameController::class,'viewGlobalListBadNames'])->name('view.globalBadNames');
            Route::post('/create-new-bad-name',[BadNameController::class,'createNewBadName'])->name('create.newBadName');
            Route::post('/delete-bad-name',[BadNameController::class,'deleteBadName'])->name('delete.badName');
        });

//        Route::get('/manage-invites',[BackendController::class,'viewManageInvite'])->name('view.manageInvite');
//        Route::post('/create-new-invite',[BackendController::class,'createNewInviteCode'])->name('create.newInvite');
//        Route::post('/use-invite-code',[BackendController::class,'updateUseInviteCode'])->name('update.useInviteCode');
//        Route::post('/delete-invite',[BackendController::class,'deleteInvite'])->name('delete.invite');
//        Route::get('/bot-verify',[BackendController::class,'viewVerifyBot'])->name('view.verifyBot');
//        Route::post('/verify-bot',[BackendController::class,'updateBotVerification'])->name('update.verifyBot');

    });

    Route::prefix('channels')->name('channel.')->group(function (){
        Route::get('/channel-job-list',[ChannelController::class,'viewListChannelJobs'])->name('view.channelList');
        Route::get('/create-channel-job',[ChannelController::class,'viewUpsertChannelJobs'])->name('view.createOrUpdateJobChannel');
        Route::post('/create-new-bot-job',[ChannelController::class,'createChannelJob'])->name('createOrUpdate.BotJob');
        Route::get('/delete-channel-job',[ChannelController::class,'deleteChannelJob'])->name('delete.channelJob');
    });

    Route::prefix('worker')->name('worker.')->group(function (){
        Route::prefix('afk-settings')->group(function (){
            Route::get('/afk-worker-settings',[ClientController::class,'viewUpsertAfkWorker'])->name('view.createOrUpdateAfkWorker');
            Route::post('/create-afk-worker',[ClientController::class,'updateAfkWorkerSettings'])->name('update.afkWorker');
        });

        Route::prefix('remover-settings')->group(function (){
            Route::get('/channel-remover-list',[ChannelController::class,'viewListChannelRemover'])->name('view.listChannelRemover');
            Route::post('/create-channel-remover',[ChannelController::class,'createChannelRemover'])->name('create.newChannelRemover');
            Route::get('/edit-channel-remover',[ChannelController::class,'viewCreateOrUpdateChannelRemoverChannel'])->name('view.createOrUpdateChannelRemover');
        });

        Route::prefix('police-settings')->group(function (){
            Route::get('/edit-police-worker',[ClientController::class,'viewUpsertPoliceWorker'])->name('view.createOrUpdatePoliceWorker');
            Route::post('/update-police-worker',[ClientController::class,'updatePoliceWorkerSettings'])->name('create.createOrUpdatePoliceWorkerSettings');
        });
    });

    Route::prefix('ts3')->name('ts3.')->group(function (){
        Route::post('/start-bot',[Ts3ConfigController::class,'ts3StartBot'])->name('start.ts3Bot');
        Route::post('/stopp-bot',[Ts3ConfigController::class,'ts3StopBot'])->name('stop.ts3Bot');
    });

    Route::prefix('banner')->name('banner.')->group(function (){
        Route::get('/banner-list',[BannerController::class,'viewListBanner'])->name('view.listBanner');
        Route::post('/upload-banner',[BannerController::class, 'createUploadedBanner'])->name('create.uploadedBanner');
        Route::get('/create-banner',[BannerController::class,'viewCreateBanner'])->name('view.createBanner');
        Route::get('/update-banner',[BannerController::class,'updateBanner'])->name('update.updateBanner');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [LoginController::class,'logout'])->name('logout');
});
