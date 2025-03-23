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
    protected $signature = 'app:create-account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle(): int
    {
        //ask and validate information from user
        $nickname = $this->ask('Enter your Nickname?');
        $mail = $this->ask('Enter your E-Mail Adresse');

        $password = $this->secret('Enter your Password');
        $passwordConfirm = $this->secret('Confirm your Password');

        while ($password != $passwordConfirm) {
            $this->info('Passwords are different.');
            $password = $this->secret('Enter your Password');
            $passwordConfirm = $this->secret('Confirm your Password');
        }

        //create new user in db
        User::query()->updateOrCreate([
            'nickname'=>$nickname,
            'password'=>$password,
            'email'=>$mail,
        ]);

        return CommandAlias::SUCCESS;
    }
}
