<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Pencatat audit log. Jangan pernah menyimpan password/token/secret —
 * nilai sensitif otomatis di-redact (lihat sanitize()).
 */
class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public static function log(
        string $action,
        ?Model $auditable = null,
        ?array $old = null,
        ?array $new = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $old === null ? null : self::sanitize($old),
            'new_values' => $new === null ? null : self::sanitize($new),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Sembunyikan nilai sensitif secara rekursif sebelum masuk log.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public static function sanitize(array $values): array
    {
        $blockedKeywords = ['password', 'token', 'secret', 'api_key', 'app_key', 'credential'];
        $clean = [];

        foreach ($values as $key => $value) {
            $lowerKey = strtolower((string) $key);

            $blocked = in_array($lowerKey, $blockedKeywords, true)
                || str_contains($lowerKey, 'password');

            if ($blocked) {
                $clean[$key] = '[REDACTED]';

                continue;
            }

            $clean[$key] = is_array($value) ? self::sanitize($value) : $value;
        }

        return $clean;
    }
}
