<?php

namespace Rydeen\Pricing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'rydeen_promotions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'type',
        'value',
        'min_qty',
        'starts_at',
        'ends_at',
        'scope',
        'scope_id',
        'active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'active'    => 'boolean',
        'value'     => 'float',
    ];

    /**
     * Get the promotion items for this promotion.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PromotionItem::class, 'promotion_id');
    }
}
