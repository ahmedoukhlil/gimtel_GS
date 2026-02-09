<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix pour MySQL : limite la longueur des chaînes pour les index
        Schema::defaultStringLength(100);

        $this->applyMailConfigFromSettings();
    }

    /**
     * Si une configuration mail est stockée en base (vue admin), l'appliquer pour remplacer le .env.
     */
    private function applyMailConfigFromSettings(): void
    {
        try {
            if (!Schema::hasTable('settings') || !Setting::hasMailConfig()) {
                return;
            }
            $mailer = Setting::get(Setting::MAIL_MAILER);
            if ($mailer !== null && $mailer !== '') {
                Config::set('mail.default', $mailer);
            }
            $host = Setting::get(Setting::MAIL_HOST);
            if ($host !== null) {
                Config::set('mail.mailers.smtp.host', $host);
            }
            $port = Setting::get(Setting::MAIL_PORT);
            if ($port !== null && $port !== '') {
                Config::set('mail.mailers.smtp.port', (int) $port);
            }
            $username = Setting::get(Setting::MAIL_USERNAME);
            if ($username !== null) {
                Config::set('mail.mailers.smtp.username', $username);
            }
            $password = Setting::get(Setting::MAIL_PASSWORD);
            if ($password !== null) {
                Config::set('mail.mailers.smtp.password', $password);
            }
            $encryption = Setting::get(Setting::MAIL_ENCRYPTION);
            if ($encryption !== null) {
                Config::set('mail.mailers.smtp.encryption', $encryption);
            }
            $fromAddress = Setting::get(Setting::MAIL_FROM_ADDRESS);
            if ($fromAddress !== null && $fromAddress !== '') {
                Config::set('mail.from.address', $fromAddress);
            }
            $fromName = Setting::get(Setting::MAIL_FROM_NAME);
            if ($fromName !== null) {
                Config::set('mail.from.name', $fromName);
            }
        } catch (\Throwable) {
            // Table settings absente ou erreur : garder la config .env
        }
    }
}
