<?php

namespace App\Console\Commands\setup;

use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class NewAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update admin account';

    public function handle(): int
    {
        $nickname = $this->ask('Enter your nickname');
        $mail = $this->ask('Enter your E-Mail adresse');

        $password = $this->secret('Enter your password');
        $passwordConfirm = $this->secret('Confirm your password');

        while ($password != $passwordConfirm) {
            $this->info('Passwords are different.');
            $password = $this->secret('Enter your password');
            $passwordConfirm = $this->secret('Confirm your password');
        }

        User::query()->updateOrCreate(
            [
                'nickname'=>$nickname,
                'email'=>$mail,
            ],
            [
                'password'=>$password,
            ]);

        return CommandAlias::SUCCESS;
    }
}
