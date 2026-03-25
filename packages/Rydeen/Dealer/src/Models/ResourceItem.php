<?php

namespace Rydeen\Dealer\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rydeen_resource_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'category',
        'content',
        'file_path',
        'sort_order',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Scope to filter only active items.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
