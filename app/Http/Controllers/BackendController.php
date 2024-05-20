<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ts3Config\Ts3ConfigController;
use App\Http\Requests\Backend\UpsertServerRequest;
use App\Http\Requests\Backend\ViewUpdateServerRequest;
use App\Models\sys\invite;
use App\Models\ts3Bot\ts3BotLog;
use App\Models\ts3Bot\ts3ServerConfig;
use App\Models\ts3BotWorkers\ts3BotWorkerPolice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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

    public function viewUpdateServer(ViewUpdateServerRequest $request): View|Factory|RedirectResponse|Application
    {
        if($request->has('update'))
        {
            $server = ts3ServerConfig::query()
                ->with(
                    'rel_bot_status',
                )
                ->where('id','=',Auth::user()->server_id)
                ->first();

            return view('backend.server.create-or-update-server')->with([
                'server'=>$server,
                'update'=>1,
            ]);
        }else
        {
            return view('backend.server.create-or-update-server');
        }
    }

    public function viewBotControlCenter(): Factory|View|RedirectResponse|Application
    {
        if (Auth::user()->server_id != 0)
        {
            $server = ts3ServerConfig::query()
                ->with(
                    'rel_bot_status',
                )
                ->where('id','=',Auth::user()->server_id)
                ->first();

            if ($server->bot_confirmed == true)
            {
                return view('backend.bot-control')->with([
                    'server'=>$server,
                ]);
            }else
            {
                return redirect()->route('backend.view.verifyBot');
            }
        }

        return redirect()->route('start.view.dashboard');
    }

    public function viewUseInvite(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('backend.invite.use-invite-code');
    }

    public function viewManageInvite(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        //get invites
        $invites = invite::query()->where('server_id','=',Auth::user()->server_id)->get();

        return view('backend.invite.list-invite-code')->with([
            'invites'=>$invites,
        ]);
    }

    public function viewVerifyBot(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('auth.verify-bot');
    }

    public function viewBotLogs(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $botLogs = ts3BotLog::query()->with('rel_bot_status')
            ->where('server_id','=',Auth::user()->server_id)
            ->where('job','!=','queuingWorkers')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('backend.server.list-bot-log')->with([
            'botLogs'=>$botLogs,
        ]);
    }

    public function viewChangePassword(): \Illuminate\Foundation\Application|View|Factory|Application
    {
        return view('auth.changePassword');
    }

    public function upsertServer(UpsertServerRequest $request): RedirectResponse
    {
        //check nullable standard server ports
        $serverQueryPort = 10011;
        $serverPort = 9987;

        if ($request->input('ServerQueryPort') !== NULL)
        {
            $serverQueryPort = $request->validated('ServerQueryPort');
        }
        if ($request->validated('ServerPort') !== NULL)
        {
            $serverPort = $request->validated('ServerPort');
        }

        // if > 0 then serverID is set and server config need update
        if ($request->validated('ServerID') > 0)
        {
            //abuse protection
            $abuseProtection = ts3ServerConfig::query()
                ->where('user_id','=',Auth::user()->id)
                ->where('id','=',$request->validated('ServerID'))
                ->first();

            //if user server_owner
            if (Auth::user()->server_owner == false)
            {
                return redirect()->back()->withErrors(['error'=>'Nur der Servereigentümer kann Einstellungen ändern']);
            }

            if ($abuseProtection->ipv4 != $request->validated('Ipv4') && $abuseProtection !== null)
            {
                ts3ServerConfig::query()->where('id','=',$abuseProtection->id)->update([
                    'bot_confirmed'=>false,
                    'bot_confirm_token'=>uniqid(),
                ]);
            }

            //update server config
            $serverID = $this->upsertServerConfig($request, $serverQueryPort, $serverPort);

            return redirect()->route('backend.view.botControlCenter',['server_id'=>$serverID]);
        }

        // if 0 = create new server
        if($request->validated('ServerID') == 0)
        {
            //create new server config
            $serverID = $this->upsertServerConfig($request,$serverQueryPort,$serverPort);

            //set token
            ts3ServerConfig::query()->where('id','=',$serverID)->update([
                'bot_confirm_token'=>uniqid(),
            ]);
            //set police settings
            ts3BotWorkerPolice::query()->create([
                'server_id'=>$serverID,
            ]);

            //set server_id and server owner to user
            User::query()->where('id','=',Auth::user()->id)->update([
                'server_id'=>$serverID,
                'server_owner'=>true,
            ]);

            //send token
            $botSendToken = new Ts3ConfigController();
            $botSendToken->ts3SendBotVerifyToken($serverID);

            //bot verification
            return redirect()->route('backend.view.verifyBot');

        }

        // if -1 = server re-ini
        if($request->validated('ServerID') == -1 && Auth::user()->server_id != 0)
        {
            //if user server_owner
            if (Auth::user()->server_owner == false)
            {
                return redirect()->back()->withErrors(['error'=>'Nur der Servereigentümer kann Einstellungen ändern']);
            }

            //re-ini Server
            $server = ts3ServerConfig::with('rel_ts3serverConfig')
                ->where('id','=',Auth::user()->server_id)
                ->whereRelation('rel_ts3serverConfig','server_owner','=',true)
                ->where('bot_confirmed','=',true)->first();

            if ($server !== null)
            {
                $reInit = new Ts3ConfigController();
                $returnCode = $reInit->ts3ServerInitializing($server->id);

                if ($returnCode['status'] == 1)
                {
                    return redirect()->back()->with('success','Der Server wurde erfolgreich neu eingerichtet');
                }else
                {
                    return redirect()->back()->withErrors(['error'=>$returnCode['msg']]);
                }
            }
        }

        //default back
        return redirect()->back();
    }

    //TODO create new design process
    public function createNewInviteCode(Request $request): RedirectResponse
    {
        //validator
        $rules = [
            'Email'=>'required|unique:invites,email',
        ];

        $messages = [
            'Email.required'=>'Bitte gib eine E-Mail Adresse an.',
            'Email.unique'=>'Für diese E-Mail Adresse existiert bereits eine Einladung.',
        ];

        //create validator with parameters
        $validator = validator::make($request->all(), $rules, $messages);

        //validate data
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (Auth::user()->server_owner == true)
        {
            invite::query()->create([
                'server_id'=>Auth::user()->server_id,
                'invited_by'=>Auth::user()->id,
                'email'=>$request->input('Email'),
                'invite_code'=>uniqid("invite-"),
                'expire_at'=>Carbon::now()->addDays(3),
            ]);
        }

        return redirect()->route('backend.view.manageInvite');
    }

    //TODO create new design process
    public function updateUseInviteCode(Request $request)
    {
        //validator
        $rules = [
            'InviteMail'=>'required',
            'InviteCode'=>'required',
        ];

        $messages = [
            'InviteMail.required'=>'Deine E-Mail Adresse wird benötigt',
            'InviteCode.required'=>'Bitte gib den Invite Code ein',
        ];

        //create validator with parameters
        $validator = validator::make($request->all(), $rules, $messages);

        //validate data
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $inviteData = invite::query()
            ->where('email','=',Auth::user()->email)
            ->where('invite_code','=',$request->input('InviteCode'))
            ->where('expire_at','>',Carbon::now())
            ->where('invite_accepted','=',false)
            ->first();

        if($inviteData->count() == 0)
        {
            return redirect()->back()->withErrors(['error'=>'Es ist keine Einladung vorhanden']);
        }else
        {
            User::query()->where('id','=',Auth::user()->id)->update([
                'server_id'=>$inviteData->server_id,
            ]);

            invite::query()->where('id','=',$inviteData->id)->update([
                'invite_accepted' => true,
                'accepted_user_id'=>Auth::user()->id,
            ]);
        }
        return redirect()->route('backend.view.botControlCenter',['server_id'=>Auth::user()->server_id]);
    }

    //TODO create new design process
    public function updateBotVerification(Request $request)
    {
        //validator
        $rules = [
            'ServerID'=>'numeric',
        ];

        $messages = [
            'BotToken.required'=>'Bitte gib den Bot Token ein',
            'ServerID.required'=>'Hoppla, da lief etwas schief',
            'ServerID.numeric'=>'Hoppla, da lief etwas schief',
        ];

        //create validator with parameters
        $validator = validator::make($request->all(), $rules, $messages);

        //validate data
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->has('BotVerifyID'))
        {
            $ts3Config = ts3ServerConfig::query()->where('id','=',$request->input('BotVerifyID'))->first();

            if ($ts3Config->bot_confirm_token == $request->input('BotToken'))
            {
                //bot confirm
                ts3ServerConfig::query()->where('id','=',$ts3Config->id)->update([
                    'bot_confirmed'=>true,
                    'bot_confirmed_at'=>Carbon::now(),
                ]);

                //initializing bot
                $ts3ConfigController = new Ts3ConfigController();
                $returnCode = $ts3ConfigController->ts3ServerInitializing($ts3Config->id);

                if ($returnCode['status'] == 1)
                {
                    return redirect()->route('backend.view.botControlCenter',['server_id'=>Auth::user()->server_id]);
                }else
                {
                    return redirect()->back()->withErrors(['error'=>$returnCode['msg']]);
                }

            }else
            {
                return redirect()->back()->withErrors(['error'=>'Verifizierung fehlgeschlagen. Bitte überprüfe deinen Token']);
            }
        }

        if ($request->has('BotSendVerifyID'))
        {
            $ts3Config = ts3ServerConfig::query()->where('id','=',$request->input('BotSendVerifyID'))->first();
            //send bot Token
            $ts3ConfigController = new Ts3ConfigController();
            $returnCode = $ts3ConfigController->ts3SendBotVerifyToken($ts3Config->id);

            if ($returnCode['status'] == 1)
            {
                return redirect()->back()->with('success',$returnCode['msg']);
            }else
            {
                return redirect()->back()->with('customError',$returnCode['msg']);
            }
        }
        
        return redirect()->route('backend.view.botControlCenter',['server_id'=>Auth::user()->server_id]);
    }

    public function updateChangePassword(Request $request): RedirectResponse
    {
        User::query()->where('id','=',Auth::user()->id)->update([
            'password'=>Hash::make($request->input('NewPassword')),
        ]);

        return redirect()->route('backend.view.botControlCenter');

    }

    //TODO create new design process
    public function deleteInvite(Request $request): RedirectResponse
    {
        //validator
        $rules = [
            'InviteID'=>'required|numeric',
        ];

        $messages = [
            'InviteID.required'=>'Hoppla, da lief etwas schief',
            'InviteID.numeric'=>'Hoppla, da lief etwas schief',
        ];

        //create validator with parameters
        $validator = validator::make($request->all(), $rules, $messages);

        //validate data
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        //get accepted user_id and delete rights
        $invite = invite::query()->where('id','=',$request->input('InviteID'))->first();

        //set server_id to 0
        User::query()->where('id','=',$invite->accepted_user_id)->update([
            'server_id'=>0,
        ]);

        invite::query()->where('id','=',$request->input('InviteID'))->delete();

        return redirect()->route('backend.view.manageInvite');

    }

    private function upsertServerConfig($request, $serverQueryPort, $serverPort)
    {
        ts3ServerConfig::query()->updateOrCreate(
            [
                'user_id'=>Auth::user()->id,
            ],
            [
                'ipv4'=>$request->input('Ipv4'),
                'server_name'=>$request->input('ServerName'),
                'qa_name'=>$request->input('QaName'),
                'qa_pw'=>Crypt::encryptString(($request->input('QaPW'))),
                'server_query_port'=>$serverQueryPort,
                'server_port'=>$serverPort,
                'description'=>$request->input('Description'),
                'qa_nickname'=>str_replace(' ','',$request->input('QueryNickname')),
            ]
        );

        //get server id
        return ts3ServerConfig::query()
            ->where('user_id','=',Auth::user()->id)
            ->where('ipv4','=',$request->input('Ipv4'))
            ->first(['id'])->id;
    }
}
