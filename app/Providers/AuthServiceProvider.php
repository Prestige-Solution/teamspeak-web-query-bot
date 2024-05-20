<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->greeting('Willkommen,')
                ->subject('Deine Registrierung bei PS-Bot')
                ->line(new HtmlString('wir freuen uns, dass du dich für den <b>PS-Bot</b> entschieden hast. Um den Service nutzen zu können, bestätige bitte deine E-Mail-Adresse mit Klick auf den nachfolgenden Button.'))
                ->action('E-Mail Adresse bestätigen', $url)
                ->line(new HtmlString('Wir haben dir eine Tutorial Seite erstellt, in der wir Schritt für Schritt erklären wie du deinen <b>Bot "sicher" erstellen</b> kannst. Dort findest du auch die Voraussetzungen welche Berechtigungen der Bot benötigt und wie du eine extra Identität anlegen kannst.'));
        });
    }
}
