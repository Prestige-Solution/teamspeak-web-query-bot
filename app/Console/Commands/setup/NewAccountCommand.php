<?php

namespace App\Console\Commands\setup;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
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
        $nickname = $this->askWithValidation('What is your Nickname?',NULL, function ($value){
                return $this->validateInput('nickname', 'required',$value);
            }
        );
        $mail = $this->askWithValidation('Enter the E-Mail Adresse',NULL, function ($value){
            return $this->validateInput('email', 'email',$value);
            }
        );

        $password = $this->secret('Enter your Password');
        $passwordConfirm = $this->secret('Confirm your Password');

        while($password != $passwordConfirm)
        {
            $this->info('Passwords are different.');
            $password = $this->secret('Enter your Password');
            $passwordConfirm = $this->secret('Confirm your Password');
        }

        if($this->confirm('Is this Account a Administrator?') == true)
        {
            $roleID = 1;
        }else
        {
            $roleID = 0;
        }

        //create new user in db
        User::query()->create([
            'nickname'=>$nickname,
            'password'=>$password,
            'email'=>$mail,
            'role_id'=>$roleID,
        ]);

        return CommandAlias::SUCCESS;
    }

    public function askWithValidation($question, $default = NULL, $validator = NULL)
    {
        return $this->output->ask($question, $default, $validator);
    }

    /**
     * @throws \Exception
     */
    protected function validateInput(string $attribute, string $validate, $value): bool
    {
        if (! is_array($value) && ! is_bool($value) && 0 === strlen($value)) {
            throw new \Exception('A value is required.');
        }

        $validator = Validator::make([$attribute => $value],[$attribute => $validate]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first($attribute));
        }

        return $value;
    }
}
