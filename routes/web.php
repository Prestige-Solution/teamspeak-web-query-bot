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

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'viewLogin'])->name('public.view.login');
});

Route::middleware(['throttle:login'])->group(function () {
    Route::post('/logging-in', [LoginController::class, 'authenticate'])->name('logging-in');
});

Route::middleware(['auth'])->group(function () {
    Route::prefix('/dashboard')->name('backend.')->group(function () {
        Route::get('/control-center', [BackendController::class, 'viewBotControlCenter'])->name('view.botControlCenter');
        Route::get('/password-reset', [BackendController::class, 'viewChangePassword'])->name('view.changePassword');
        Route::post('/change-password', [BackendController::class, 'updateChangePassword'])->name('update.changePassword');

        Route::prefix('/logs')->group(function () {
            Route::get('/bot-logs', [BackendController::class, 'viewBotLogs'])->name('view.botLogs');
        });
    });

    Route::prefix('/server')->name('serverConfig.')->group(function () {
        Route::get('/list', [ServerController::class, 'viewServerList'])->name('view.serverList');
        Route::post('/create-new-server', [ServerController::class, 'createServer'])->name('create.server');
        Route::post('/update-server', [ServerController::class, 'updateServer'])->name('update.server');
        Route::post('/initialisieren', [ServerController::class, 'updateServerInit'])->name('update.serverInit');
        Route::post('/update-default-server', [ServerController::class, 'updateSwitchDefaultServer'])->name('update.switchDefaultServer');
        Route::post('/delete-server', [ServerController::class, 'deleteServer'])->name('delete.server');
    });

    Route::prefix('/channels')->name('channel.')->group(function () {
        Route::prefix('/channel-creator')->group(function () {
            Route::get('/job-list', [ChannelController::class, 'viewChannelJobs'])->name('view.channelJobs');
            Route::post('/upsert', [ChannelController::class, 'upsertChannelJob'])->name('upsert.channelJob');
            Route::post('/delete', [ChannelController::class, 'deleteChannelJob'])->name('delete.channelJob');
        });

        Route::prefix('/channel-remover')->group(function () {
            Route::get('/job-list', [ChannelRemoverController::class, 'viewChannelRemoverJobs'])->name('view.listChannelRemover');
            Route::post('/upsert', [ChannelRemoverController::class, 'upsertChannelRemoverJob'])->name('upsert.newChannelRemover');
            Route::post('/delete', [ChannelRemoverController::class, 'deleteChannelRemoverJob'])->name('delete.channelRemover');
        });
    });

    Route::prefix('/worker')->name('worker.')->group(function () {
        Route::prefix('/afk')->group(function () {
            Route::get('/settings', [ClientController::class, 'viewAfkWorker'])->name('view.createOrUpdateAfkWorker');
            Route::post('/update', [ClientController::class, 'updateAfkWorkerSettings'])->name('update.afkWorker');
        });

        Route::prefix('/police')->group(function () {
            Route::get('/settings', [ClientController::class, 'viewPoliceWorker'])->name('view.upsertPoliceWorker');
            Route::post('/update', [ClientController::class, 'updatePoliceWorkerSettings'])->name('create.updatePoliceWorkerSettings');
        });

        Route::prefix('/bad-nicknames')->group(function () {
            Route::get('/list', [BadNameController::class, 'viewListBadNames'])->name('view.badNames');
            Route::post('/create-new-bad-name', [BadNameController::class, 'createNewBadName'])->name('create.newBadName');
            Route::post('/delete-bad-name', [BadNameController::class, 'deleteBadName'])->name('delete.badName');
        });
    });

    Route::prefix('/bot-control')->name('ts3.')->group(function () {
        Route::post('/start-bot', [Ts3ConfigController::class, 'ts3StartBot'])->name('start.ts3Bot');
        Route::post('/stopp-bot', [Ts3ConfigController::class, 'ts3StopBot'])->name('stop.ts3Bot');
    });

    Route::prefix('/banner')->name('banner.')->group(function () {
        Route::get('/list', [BannerController::class, 'viewListBanner'])->name('view.listBanner');
        Route::post('/upload', [BannerController::class, 'createUploadedTemplate'])->name('create.uploadedTemplate');
        Route::get('/config', [BannerController::class, 'viewConfigBanner'])->name('view.configBanner');
        Route::post('/upsert', [BannerController::class, 'upsertConfigBanner'])->name('upsert.configBanner');
        Route::post('/delete', [BannerController::class, 'deleteBanner'])->name('delete.banner');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});
