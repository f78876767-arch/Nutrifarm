<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $customerRole = Role::firstOrCreate(['name' => 'Customer']);
        $vendorRole = Role::firstOrCreate(['name' => 'Vendor']);
        $moderatorRole = Role::firstOrCreate(['name' => 'Moderator']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);

        // Assign roles to existing users
        $users = User::all();
        
        foreach ($users as $user) {
            // Clear existing role assignments to avoid duplicates
            $user->roles()->detach();
            
            if (str_contains($user->email, 'admin') || str_contains($user->email, 'superadmin')) {
                // Admin users get Admin role
                $user->roles()->attach($adminRole->id);
                $this->command->info("Assigned Admin role to: {$user->email}");
            } else {
                // Regular users get Customer role by default
                $user->roles()->attach($customerRole->id);
                $this->command->info("Assigned Customer role to: {$user->email}");
            }
        }

        $this->command->info('Roles created and assigned successfully!');
    }
}
