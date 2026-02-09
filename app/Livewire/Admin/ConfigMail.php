<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Setting;

#[Layout('components.layouts.app')]
class ConfigMail extends Component
{
    public string $mail_mailer = 'log';
    public string $mail_host = '';
    public string $mail_port = '587';
    public string $mail_username = '';
    public string $mail_password = '';
    public string $mail_encryption = 'tls';
    public string $mail_from_address = '';
    public string $mail_from_name = '';
    public string $notification_production_email = '';

    /** Le mot de passe n'est pas rechargé depuis la base (sécurité). Vide = ne pas modifier. */
    public bool $password_placeholder = true;

    protected function rules(): array
    {
        $rules = [
            'mail_mailer' => 'required|in:log,smtp',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string|max:255',
            'notification_production_email' => 'nullable|email',
        ];
        if ($this->mail_mailer === 'smtp' && $this->mail_password !== '') {
            $rules['mail_password'] = 'string|min:1';
        } else {
            $rules['mail_password'] = 'nullable|string';
        }
        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'mail_mailer' => 'envoi des e-mails',
            'mail_host' => 'serveur SMTP',
            'mail_port' => 'port',
            'mail_username' => 'utilisateur SMTP',
            'mail_password' => 'mot de passe SMTP',
            'mail_encryption' => 'chiffrement',
            'mail_from_address' => 'adresse expéditeur',
            'mail_from_name' => 'nom expéditeur',
            'notification_production_email' => 'e-mail direction production',
        ];
    }

    public function mount(): void
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }
        $this->mail_mailer = Setting::get(Setting::MAIL_MAILER, 'log');
        $this->mail_host = Setting::get(Setting::MAIL_HOST, '');
        $this->mail_port = (string) (Setting::get(Setting::MAIL_PORT) ?: '587');
        $this->mail_username = Setting::get(Setting::MAIL_USERNAME, '');
        $this->mail_encryption = Setting::get(Setting::MAIL_ENCRYPTION, 'tls') ?: '';
        $this->mail_from_address = Setting::get(Setting::MAIL_FROM_ADDRESS, '');
        $this->mail_from_name = Setting::get(Setting::MAIL_FROM_NAME, '');
        $this->notification_production_email = Setting::get(Setting::NOTIFICATION_PRODUCTION_EMAIL, '');
        $this->mail_password = '';
    }

    public function save(): void
    {
        $this->validate();

        Setting::set(Setting::MAIL_MAILER, $this->mail_mailer);
        Setting::set(Setting::MAIL_HOST, $this->mail_host);
        Setting::set(Setting::MAIL_PORT, $this->mail_port ?: null);
        Setting::set(Setting::MAIL_USERNAME, $this->mail_username ?: null);
        if ($this->mail_password !== '') {
            Setting::set(Setting::MAIL_PASSWORD, $this->mail_password);
        }
        Setting::set(Setting::MAIL_ENCRYPTION, $this->mail_encryption ?: null);
        Setting::set(Setting::MAIL_FROM_ADDRESS, $this->mail_from_address ?: null);
        Setting::set(Setting::MAIL_FROM_NAME, $this->mail_from_name ?: null);
        Setting::set(Setting::NOTIFICATION_PRODUCTION_EMAIL, $this->notification_production_email ?: null);

        $this->mail_password = '';
        $this->password_placeholder = true;
        session()->flash('success', 'Configuration e-mail enregistrée.');
    }

    public function render()
    {
        return view('livewire.admin.config-mail');
    }
}
