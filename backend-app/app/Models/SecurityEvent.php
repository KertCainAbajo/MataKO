<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sign-in failures, lockouts and changes to how people sign in, for the admin Security page.
 */
class SecurityEvent extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    /** @var array<string, array{0: string, 1: string}> Event type => [label, severity: info|warning|danger]. */
    public const TYPES = [
        'login.failed' => ['Failed app sign-in', 'warning'],
        'admin.login.failed' => ['Failed admin sign-in', 'danger'],
        'account.locked' => ['Account locked after failed sign-ins', 'danger'],
        'login.blocked' => ['Sign-in tried while locked', 'warning'],
        'admin.login' => ['Admin signed in', 'info'],
        'admin.two_factor.failed' => ['Wrong two-factor code', 'danger'],
        'admin.two_factor.enabled' => ['Two-factor sign-in turned on', 'info'],
        'admin.two_factor.disabled' => ['Two-factor sign-in turned off', 'warning'],
        'admin.two_factor.recovery_used' => ['Recovery code used', 'warning'],
        'admin.session.expired' => ['Admin signed out after inactivity', 'info'],
        'password.changed' => ['Password changed', 'info'],
        'google.failed' => ['Rejected Google sign-in', 'warning'],
    ];

    protected $fillable = ['type', 'email', 'user_id', 'ip', 'user_agent', 'details'];

    protected $casts = ['created_at' => 'datetime'];

    /**
     * Event types that need an admin's attention.
     *
     * @return list<string>
     */
    public static function serious(): array
    {
        return array_keys(array_filter(self::TYPES, fn (array $type): bool => $type[1] === 'danger'));
    }

    public static function record(string $type, ?string $email = null, ?User $user = null, ?string $details = null): self
    {
        $request = request();

        return static::create([
            'type' => $type,
            'email' => $email ?? $user?->email,
            'user_id' => $user?->id,
            'ip' => $request?->ip(),
            'user_agent' => mb_substr((string) $request?->userAgent(), 0, 255) ?: null,
            'details' => $details ? mb_substr($details, 0, 255) : null,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return self::TYPES[$this->type][0] ?? $this->type;
    }

    public function severity(): string
    {
        return self::TYPES[$this->type][1] ?? 'info';
    }

    /**
     * Security records are kept for 90 days (`php artisan model:prune`, run daily by the scheduler).
     *
     * @return Builder<SecurityEvent>
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(90));
    }
}
