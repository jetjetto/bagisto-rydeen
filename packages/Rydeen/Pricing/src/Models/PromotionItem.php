<?php

namespace Rydeen\Pricing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionItem extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'rydeen_promotion_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'promotion_id',
        'product_id',
        'override_price',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'override_price' => 'float',
    ];

    /**
     * Get the promotion that owns this item.
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
}
