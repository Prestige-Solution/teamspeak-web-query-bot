<?php

namespace App\Http\Controllers\sys;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function viewRegister(): View|Factory|Application
    {
        return view('auth.register');
    }

    public function createRegisterNewUser(Request $request): RedirectResponse
    {
        //validator
        $rules = [
            'NickName'=>'required|unique:users,nickname',
            'Email'=>'required|unique:users,email',
            'Birthday'=>'required',
            'Password'=>'required|confirmed',
        ];

        $messages = [
            'NickName.required'=>'Bitte gib einen Benutzernamen ein',
            'Nickname.unique'=>'Der Benutzername existiert bereits',
            'Email.required'=>'Bitte gib eine E-Mail Adresse an',
            'Email.unique'=>'Die E-Mail Adresse ist bereits vergeben',
            'Birthday.required'=>'Bitte gib dein Geburtsdatum an',
            'Password.required'=>'Bitte gib ein Passwort ein',
            'Password.confirmed'=>'Dein Passwort stimmt nicht überein',
        ];

        //create validator with parameters
        $validator = validator::make($request->all(), $rules, $messages);

        //validate data
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::query()->create([
            'nickname'=>$request->input('NickName'),
            'email'=>$request->input('Email'),
            'birthday'=>$request->input('Birthday'),
            'password'=>$request->input('Password'),
        ]);

        //send mail
//        event(new Registered($user));

        return redirect()->route('start.view.dashboard');

    }

    public function verifyEmail(): View|Factory|Application
    {
        return view('auth.verify-email-notice');
    }

    public function emailVerificationRequest(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('start.view.dashboard');
    }

    public function verificationNotification(Request $request): RedirectResponse
    {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Die Bestätigungsmail wurde versendet!');
    }
}
