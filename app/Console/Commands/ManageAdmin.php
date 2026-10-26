<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Creates or promotes an administrator for the /admin panel, or revokes
 * the role. The password is always entered interactively; there is no
 * default account.
 */
class ManageAdmin extends Command
{
    protected $signature = 'info:admin {email : E-mail address of the administrator} {--revoke : Remove administrator rights}';

    protected $description = 'Create or promote an administrator for the admin panel, or revoke the role';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        if (Validator::make(['email' => $email], ['email' => ['required', 'email:rfc', 'max:254']])->fails()) {
            $this->error('Invalid e-mail address.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($this->option('revoke')) {
            if ($user === null || ! $user->is_admin) {
                $this->warn("{$email} is not an administrator.");

                return self::SUCCESS;
            }

            $user->forceFill(['is_admin' => false])->save();
            $this->info("Administrator rights removed from {$email}.");

            return self::SUCCESS;
        }

        if ($user !== null) {
            $user->forceFill(['is_admin' => true])->save();
            $this->info("{$email} is now an administrator.");

            return self::SUCCESS;
        }

        $name = (string) $this->ask('Name');
        $password = (string) $this->secret('Password (min. 12 characters, mixed case and a number)');

        $validator = Validator::make(['name' => $name, 'password' => $password], [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', Password::min(12)->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        if ($password !== (string) $this->secret('Repeat password')) {
            $this->error('The passwords do not match.');

            return self::FAILURE;
        }

        $user = new User(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->forceFill(['is_admin' => true])->save();

        $this->info("Administrator {$email} created. Log in at ".url('/admin').'.');

        return self::SUCCESS;
    }
}
