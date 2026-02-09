<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    /** Clés de paramètres mail (stockés en base, utilisés par la vue admin) */
    public const MAIL_MAILER = 'mail_mailer';
    public const MAIL_HOST = 'mail_host';
    public const MAIL_PORT = 'mail_port';
    public const MAIL_USERNAME = 'mail_username';
    public const MAIL_PASSWORD = 'mail_password';
    public const MAIL_ENCRYPTION = 'mail_encryption';
    public const MAIL_FROM_ADDRESS = 'mail_from_address';
    public const MAIL_FROM_NAME = 'mail_from_name';
    public const NOTIFICATION_PRODUCTION_EMAIL = 'notification_production_email';

    /** Clés dont la valeur doit être déchiffrée à la lecture */
    private const ENCRYPTED_KEYS = [self::MAIL_PASSWORD];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (in_array($key, self::ENCRYPTED_KEYS, true)) {
            $row = static::find($key);
            if (!$row || $row->value === null || $row->value === '') {
                return $default;
            }
            try {
                return Crypt::decryptString($row->value);
            } catch (\Throwable) {
                return $default;
            }
        }
        $cacheKey = 'setting.' . $key;
        $value = Cache::remember($cacheKey, 3600, function () use ($key) {
            $row = static::find($key);
            return $row && $row->value !== null ? $row->value : null;
        });
        return $value ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        if (in_array($key, self::ENCRYPTED_KEYS, true) && $value !== null && $value !== '') {
            $value = Crypt::encryptString($value);
        }
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('setting.' . $key);
    }

    /** Retourne true si au moins un paramètre mail est défini en base (priorité sur .env) */
    public static function hasMailConfig(): bool
    {
        return static::whereIn('key', [
            self::MAIL_MAILER,
            self::MAIL_HOST,
        ])->whereNotNull('value')->where('value', '!=', '')->exists();
    }
}
