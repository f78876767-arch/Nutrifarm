<?php

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__ . '/../bootstrap/app.php';

/** @var Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

$email = 'kevin@admin.com';
$password = '123456';

$user = User::updateOrCreate(
    ['email' => $email],
    [
        'name' => 'Kevin Admin',
        'password' => Hash::make($password),
        'email_verified_at' => now(),
    ]
);

$adminRole = Role::firstOrCreate(['name' => 'Admin']);
$user->roles()->syncWithoutDetaching([$adminRole->id]);

DB::commit();

echo "Created/updated admin user: {$email} with password: {$password}\n";
echo "Assigned role: Admin\n";
