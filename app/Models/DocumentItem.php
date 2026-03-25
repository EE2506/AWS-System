<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_id',
        'item_number',
        'name',
        'description',
        'quantity',
        'unit_cost',
        'total_cost',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the document that owns this item.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Boot the model - auto-calculate total_cost.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            if ($item->unit_cost !== null && $item->quantity !== null) {
                $item->total_cost = $item->unit_cost * $item->quantity;
            }
        });

        static::saved(function ($item) {
            // Update the parent document's total
            $item->document?->updateTotal();
        });

        static::deleted(function ($item) {
            // Update the parent document's total
            $item->document?->updateTotal();
        });
    }
}
