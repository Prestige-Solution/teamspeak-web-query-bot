<?php

use App\Http\Controllers\BackendController;
use App\Http\Controllers\banner\BannerController;
use App\Http\Controllers\channel\ChannelController;
use App\Http\Controllers\channel\ChannelRemoverController;
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
    Route::prefix('dashboard')->name('backend.')->group(function (){
        Route::get('/control-center',[BackendController::class,'viewBotControlCenter'])->name('view.botControlCenter');
        Route::get('/password-reset',[BackendController::class, 'viewChangePassword'])->name('view.changePassword');
        Route::post('/change-password',[BackendController::class,'updateChangePassword'])->name('update.changePassword');

        Route::prefix('logs')->group(function (){
            Route::get('/bot-logs',[BackendController::class,'viewBotLogs'])->name('view.botLogs');
        });
    });

    Route::prefix('bad-nicknames')->name('backend.')->group(function (){
        Route::get('/list',[BadNameController::class,'viewListBadNames'])->name('view.badNames');
        Route::get('/global-list',[BadNameController::class,'viewGlobalListBadNames'])->name('view.globalBadNames');
        Route::post('/create-new-bad-name',[BadNameController::class,'createNewBadName'])->name('create.newBadName');
        Route::post('/delete-bad-name',[BadNameController::class,'deleteBadName'])->name('delete.badName');
    });

    Route::prefix('server')->name('serverConfig.')->group(function (){
        Route::get('/list',[ServerController::class,'viewServerList'])->name('view.serverList');
        Route::get('/new-server',[BackendController::class,'viewCreateServer'])->name('view.createServer');
        Route::post('/create-new-server',[ServerController::class, 'createServer'])->name('create.server');
        Route::any('/edit',[BackendController::class,'viewUpdateServer'])->name('view.updateServer');
        Route::post('/update-server',[ServerController::class, 'updateServer'])->name('update.server');
        Route::post('/initialisieren',[ServerController::class, 'updateServerInit'])->name('update.serverInit');
        Route::post('/update-default-server',[ServerController::class, 'updateSwitchDefaultServer'])->name('update.switchDefaultServer');
        Route::post('/delete-server',[ServerController::class, 'deleteServer'])->name('delete.server');
    });

    Route::prefix('channels')->name('channel.')->group(function (){
        Route::get('/job-list',[ChannelController::class,'viewChannels'])->name('view.listChannel');
        Route::get('/job-create',[ChannelController::class,'viewCreateChannel'])->name('view.createJobChannel');
        Route::post('/job-edit',[ChannelController::class,'viewUpsertChannel'])->name('view.upsertJobChannel');
        Route::post('/upsert-job',[ChannelController::class,'upsertChannelJob'])->name('upsert.channelJob');
        Route::post('/delete-job',[ChannelController::class,'deleteChannelJob'])->name('delete.channelJob');
    });

    Route::prefix('worker')->name('worker.')->group(function (){
        Route::prefix('afk')->group(function (){
            Route::get('/settings',[ClientController::class,'viewUpsertAfkWorker'])->name('view.createOrUpdateAfkWorker');
            Route::post('/update-settings',[ClientController::class,'updateAfkWorkerSettings'])->name('update.afkWorker');
        });

        Route::prefix('channel-remover')->group(function (){
            Route::get('/job-list',[ChannelRemoverController::class,'viewChannelRemover'])->name('view.listChannelRemover');
            Route::get('/job-create',[ChannelRemoverController::class,'viewCreateChannelRemover'])->name('view.createChannelRemover');
            Route::post('/job-edit',[ChannelRemoverController::class,'viewUpsertChannelRemover'])->name('view.upsertChannelRemover');
            Route::post('/upsert-channel',[ChannelRemoverController::class,'upsertChannelRemover'])->name('create.newChannelRemover');
            Route::post('/delete-channel',[ChannelRemoverController::class,'deleteChannelRemover'])->name('delete.channelRemover');
        });

        Route::prefix('guardian')->group(function (){
            Route::get('/settings',[ClientController::class,'viewUpsertPoliceWorker'])->name('view.upsertPoliceWorker');
            Route::post('/update',[ClientController::class,'updatePoliceWorkerSettings'])->name('create.updatePoliceWorkerSettings');
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
