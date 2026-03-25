<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PublicLink extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_id',
        'token',
        'allow_download',
        'expires_at',
        'access_count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'allow_download' => 'boolean',
        'expires_at' => 'datetime',
        'access_count' => 'integer',
    ];

    /**
     * Get the document that this link provides access to.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Generate a unique token for the link.
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Check if the link is expired.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if the link is valid (not expired).
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Increment the access count.
     */
    public function recordAccess(): void
    {
        $this->increment('access_count');
    }
}
