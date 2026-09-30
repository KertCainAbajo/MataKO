<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    // We only store creation time for this MVP; Laravel's default updated_at is disabled.
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'total_score', 'risk_level'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function symptoms(): HasMany
    {
        return $this->hasMany(Symptom::class);
    }
}
