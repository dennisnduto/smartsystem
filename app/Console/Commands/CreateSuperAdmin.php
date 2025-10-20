<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Institution;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-super-admin {--email=admin@example.com} {--password=password} {--name=Super Admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');
        
        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists!");
            return 1;
        }
        
        // Create or get first institution
        $institution = Institution::first();
        if (!$institution) {
            $institution = Institution::create(['name' => 'Default Institution']);
            $this->info('Created default institution.');
        }
        
        // Create super admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'super_admin',
            'institution_id' => $institution->id,
            'email_verified_at' => now(),
        ]);
        
        $this->info("Super admin user created successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Role', $user->role],
                ['Institution', $institution->name],
            ]
        );
        
        $this->info("You can now login at /login with:");
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");
        
        return 0;
    }
}
