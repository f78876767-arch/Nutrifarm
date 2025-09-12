<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email} {--name=} {--password=}';
    protected $description = 'Create an admin user with the Admin role';

    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?: 'Admin';
        $password = $this->option('password') ?: \Str::random(12);

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($password)]
        );

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $this->info("Admin user ready: {$user->email}");
        $this->info("Password: {$password}");

        return self::SUCCESS;
    }
}
