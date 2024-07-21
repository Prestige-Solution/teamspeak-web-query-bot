<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ts3Config\Ts3ConfigController;
use App\Http\Requests\Backend\ViewUpdateServerRequest;
use App\Models\sys\invite;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BackendController extends Controller
{
    public function viewBackendDashboard(): View|Factory|RedirectResponse|Application
    {
        //check if user has a server_id
        if (Auth::user()->server_id == 0)
        {
            return view('backend.start');
        }else
        {
            return redirect()->route('backend.view.botControlCenter',['server_id'=>Auth::user()->server_id]);
        }
    }

    public function viewCreateServer(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('backend.server.upsert-server');
    }

    public function viewUpdateServer(ViewUpdateServerRequest $request): View|Factory|RedirectResponse|Application
    {
        $server = ts3ServerConfig::query()
            ->with(
                'rel_bot_status',
            )
            ->where('id','=',$request->input('ServerID'));

        //TODO proof this Workaround with FormRequest
        if ($server->count() == 0)
        {
            $servers = ts3ServerConfig::query()->orderByDesc('server_ip')->get();

            return view('backend.server.servers')->with([
                'servers'=>$servers,
            ]);
        }

        return view('backend.server.upsert-server')->with([
            'server'=>$server->first(),
            'update'=>1,
        ]);
    }

    public function viewBotControlCenter(): Factory|View|RedirectResponse|Application
    {
        $server = ts3ServerConfig::query()
            ->with(
                'rel_bot_status',
            )
            ->where('default','=',true)
            ->first();

        $availableServers = ts3ServerConfig::query()->orderBy('server_ip')->get(['id', 'server_name','server_ip']);

        return view('backend.bot-control')->with([
            'server'=>$server,
            'availableServers'=>$availableServers,
        ]);
    }

//    public function viewUseInvite(): View|\Illuminate\Foundation\Application|Factory|Application
//    {
//        return view('backend.invite.use-invite-code');
//    }

//    public function viewManageInvite(): View|\Illuminate\Foundation\Application|Factory|Application
//    {
//        //get invites
//        $invites = invite::query()->where('server_id','=',Auth::user()->server_id)->get();
//
//        return view('backend.invite.list-invite-code')->with([
//            'invites'=>$invites,
//        ]);
//    }

//    public function viewVerifyBot(): View|\Illuminate\Foundation\Application|Factory|Application
//    {
//        return view('auth.verify-bot');
//    }

    public function viewBotLogs(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $botLogs = ts3BotLog::query()->with('rel_bot_status')
            ->where('server_id','=',Auth::user()->server_id)
            ->where('job','!=','queuingWorkers')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('backend.server.bot-logs')->with([
            'botLogs'=>$botLogs,
        ]);
    }

    public function viewChangePassword(): \Illuminate\Foundation\Application|View|Factory|Application
    {
        return view('auth.changePassword');
    }

//    public function createNewInviteCode(Request $request): RedirectResponse
//    {
//        //validator
//        $rules = [
//            'Email'=>'required|unique:invites,email',
//        ];
//
//        $messages = [
//            'Email.required'=>'Bitte gib eine E-Mail Adresse an.',
//            'Email.unique'=>'Für diese E-Mail Adresse existiert bereits eine Einladung.',
//        ];
//
//        //create validator with parameters
//        $validator = validator::make($request->all(), $rules, $messages);
//
//        //validate data
//        if ($validator->fails()) {
//            return redirect()->back()
//                ->withErrors($validator)
//                ->withInput();
//        }
//
//        if (Auth::user()->server_owner == true)
//        {
//            invite::query()->create([
//                'server_id'=>Auth::user()->server_id,
//                'invited_by'=>Auth::user()->id,
//                'email'=>$request->input('Email'),
//                'invite_code'=>uniqid("invite-"),
//                'expire_at'=>Carbon::now()->addDays(3),
//            ]);
//        }
//
//        return redirect()->route('backend.view.manageInvite');
//    }

//    public function updateUseInviteCode(Request $request)
//    {
//        //validator
//        $rules = [
//            'InviteMail'=>'required',
//            'InviteCode'=>'required',
//        ];
//
//        $messages = [
//            'InviteMail.required'=>'Deine E-Mail Adresse wird benötigt',
//            'InviteCode.required'=>'Bitte gib den Invite Code ein',
//        ];
//
//        //create validator with parameters
//        $validator = validator::make($request->all(), $rules, $messages);
//
//        //validate data
//        if ($validator->fails()) {
//            return redirect()->back()
//                ->withErrors($validator)
//                ->withInput();
//        }
//
//        $inviteData = invite::query()
//            ->where('email','=',Auth::user()->email)
//            ->where('invite_code','=',$request->input('InviteCode'))
//            ->where('expire_at','>',Carbon::now())
//            ->where('invite_accepted','=',false)
//            ->first();
//
//        if(empty($inviteData) === true)
//        {
//            return redirect()->back()->withErrors(['error'=>'Es ist keine Einladung vorhanden']);
//        }else
//        {
//            User::query()->where('id','=',Auth::user()->id)->update([
//                'server_id'=>$inviteData->server_id,
//            ]);
//
//            invite::query()->where('id','=',$inviteData->id)->update([
//                'invite_accepted' => true,
//                'accepted_user_id'=>Auth::user()->id,
//            ]);
//        }
//        return redirect()->route('backend.view.botControlCenter',['server_id'=>Auth::user()->server_id]);
//    }

//    public function updateBotVerification(Request $request)
//    {
//        //validator
//        $rules = [
//            'ServerID'=>'numeric',
//        ];
//
//        $messages = [
//            'BotToken.required'=>'Bitte gib den Bot Token ein',
//            'ServerID.required'=>'Hoppla, da lief etwas schief',
//            'ServerID.numeric'=>'Hoppla, da lief etwas schief',
//        ];
//
//        //create validator with parameters
//        $validator = validator::make($request->all(), $rules, $messages);
//
//        //validate data
//        if ($validator->fails()) {
//            return redirect()->back()
//                ->withErrors($validator)
//                ->withInput();
//        }
//
//        if ($request->has('BotVerifyID'))
//        {
//            $ts3Config = ts3ServerConfig::query()->where('id','=',$request->input('BotVerifyID'))->first();
//
//            if ($ts3Config->bot_confirm_token == $request->input('BotToken'))
//            {
//                //bot confirm
//                ts3ServerConfig::query()->where('id','=',$ts3Config->id)->update([
//                    'bot_confirmed'=>true,
//                    'bot_confirmed_at'=>Carbon::now(),
//                ]);
//
//                //initializing bot
//                $ts3ConfigController = new Ts3ConfigController();
//                $returnCode = $ts3ConfigController->ts3ServerInitializing($ts3Config->id);
//
//                if ($returnCode['status'] == 1)
//                {
//                    return redirect()->route('backend.view.botControlCenter',['server_id'=>Auth::user()->server_id]);
//                }else
//                {
//                    return redirect()->back()->withErrors(['error'=>$returnCode['msg']]);
//                }
//
//            }else
//            {
//                return redirect()->back()->withErrors(['error'=>'Verifizierung fehlgeschlagen. Bitte überprüfe deinen Token']);
//            }
//        }
//
//        return redirect()->route('backend.view.botControlCenter',['server_id'=>Auth::user()->server_id]);
//    }
//
//    public function updateChangePassword(Request $request): RedirectResponse
//    {
//        User::query()->where('id','=',Auth::user()->id)->update([
//            'password'=>Hash::make($request->input('NewPassword')),
//        ]);
//
//        return redirect()->route('backend.view.botControlCenter');
//
//    }

//    public function deleteInvite(Request $request): RedirectResponse
//    {
//        //validator
//        $rules = [
//            'InviteID'=>'required|numeric',
//        ];
//
//        $messages = [
//            'InviteID.required'=>'Hoppla, da lief etwas schief',
//            'InviteID.numeric'=>'Hoppla, da lief etwas schief',
//        ];
//
//        //create validator with parameters
//        $validator = validator::make($request->all(), $rules, $messages);
//
//        //validate data
//        if ($validator->fails()) {
//            return redirect()->back()
//                ->withErrors($validator)
//                ->withInput();
//        }
//
//        //get accepted user_id and delete rights
//        $invite = invite::query()->where('id','=',$request->input('InviteID'))->first();
//
//        //set server_id to 0
//        User::query()->where('id','=',$invite->accepted_user_id)->update([
//            'server_id'=>0,
//        ]);
//
//        invite::query()->where('id','=',$request->input('InviteID'))->delete();
//
//        return redirect()->route('backend.view.manageInvite');
//
//    }
}
