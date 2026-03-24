<?php

namespace Rydeen\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Customer\Models\Customer;

class TrustedDevice extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'rydeen_trusted_devices';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_id',
        'uuid',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the customer that owns the trusted device.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
