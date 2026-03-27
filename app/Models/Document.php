<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Document type constants
     */
    public const TYPE_SOA = 'soa';
    public const TYPE_PURCHASE_ORDER = 'purchase_order';
    public const TYPE_QUOTATION = 'quotation';
    public const TYPE_DELIVERY_RECEIPT = 'delivery_receipt';

    /**
     * Status constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINALIZED = 'finalized';
    public const STATUS_PAID = 'paid';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'control_number',
        'recipient_name',
        'recipient_email',
        'recipient_phone',
        'recipient_address',
        'document_date',
        'discount',
        'total_amount',
        'status',
    ];

    /**
     * Get the type-specific label for the reference number field.
     */
    public function getReferenceLabel(): string
    {
        return match ($this->type) {
            self::TYPE_QUOTATION => 'Control#',
            self::TYPE_PURCHASE_ORDER => 'Control No.',
            self::TYPE_SOA => 'SOA no.',
            self::TYPE_DELIVERY_RECEIPT => 'Reference',
            default => 'Reference No.',
        };
    }

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'document_date' => 'date',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get available document types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_SOA => 'Statement of Account',
            self::TYPE_PURCHASE_ORDER => 'Purchase Order',
            self::TYPE_QUOTATION => 'Quotation',
            self::TYPE_DELIVERY_RECEIPT => 'Delivery Receipt',
        ];
    }

    /**
     * Get the user that owns the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the line items for the document.
     */
    public function items(): HasMany
    {
        return $this->hasMany(DocumentItem::class)->orderBy('item_number');
    }

    /**
     * Get the public link for the document.
     */
    public function publicLink(): HasOne
    {
        return $this->hasOne(PublicLink::class);
    }

    /**
     * Calculate the total amount based on items and discount.
     */
    public function calculateTotal(): float
    {
        $subtotal = (float) $this->items->sum('total_cost');
        return max(0.0, $subtotal - (float) $this->discount);
    }

    /**
     * Recalculate and save the total amount.
     */
    public function updateTotal(): void
    {
        $this->total_amount = $this->calculateTotal();
        $this->save();
    }

    /**
     * Generate a unique control number.
     */
    public static function generateControlNumber(string $type): string
    {
        $prefix = match ($type) {
            self::TYPE_SOA              => 'S',
            self::TYPE_PURCHASE_ORDER   => 'PO',
            self::TYPE_QUOTATION        => 'Q',
            self::TYPE_DELIVERY_RECEIPT => 'DR',
            default                     => 'DOC',
        };

        // Offset so series starts at the correct number:
        // SOA & PO start at 1000 (offset 999), DR & QT start at 0000 (offset 0)
        $offset = match ($type) {
            self::TYPE_SOA            => 999,
            self::TYPE_PURCHASE_ORDER => 999,
            default                   => 0,
        };

        // Count only active (non-deleted) records so gaps are filled when documents are deleted
        $count = self::where('type', $type)->count();

        return sprintf('%s-%04d', $prefix, $count + $offset + 1);
    }

    /**
     * Get the formatted control number with red color for PDFs.
     */
    public function getFormattedControlNumberAttribute(): string
    {
        if (!$this->control_number) {
            return 'N/A';
        }

        return '<span style="color: #CC0000; font-weight: normal; font-size: 11px;">'
            . e($this->control_number)
            . '</span>';
    }
}
