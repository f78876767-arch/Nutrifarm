<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class TestSmtpConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:smtp {email : The email address to send test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMTP email configuration by sending a verification code email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $testCode = '123456';

        $this->info('Testing SMTP configuration...');
        $this->info('Email Driver: ' . config('mail.default'));
        $this->info('SMTP Host: ' . config('mail.mailers.smtp.host'));
        $this->info('SMTP Port: ' . config('mail.mailers.smtp.port'));
        $this->info('From Address: ' . config('mail.from.address'));
        $this->newLine();

        try {
            $this->info("Sending test verification email to: {$email}");
            
            Mail::to($email)->send(new VerificationCodeMail($testCode, $email));
            
            if (config('mail.default') === 'log') {
                $this->info('✅ Email logged successfully!');
                $this->info('Check storage/logs/laravel.log to see the email content.');
            } else {
                $this->info('✅ Email sent successfully via SMTP!');
                $this->info("Test verification code: {$testCode}");
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email:');
            $this->error($e->getMessage());
            $this->newLine();
            
            $this->warn('Common solutions:');
            $this->line('1. Check SMTP credentials in .env file');
            $this->line('2. For Gmail: Enable 2FA and use App Password');
            $this->line('3. Check firewall/network connectivity');
            $this->line('4. Try different MAIL_ENCRYPTION (tls/ssl/null)');
            $this->line('5. Switch to MAIL_MAILER=log for testing');
            
            return 1;
        }

        return 0;
    }
}
