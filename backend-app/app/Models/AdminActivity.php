<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AdminActivity extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['admin_id', 'action', 'description'];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Record an admin action, attributed to the signed-in admin.
     */
    public static function record(string $action, string $description): void
    {
        static::create(['admin_id' => Auth::id(), 'action' => $action, 'description' => $description]);
    }
}
