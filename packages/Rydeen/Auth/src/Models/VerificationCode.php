<?php

namespace Rydeen\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'rydeen_verification_codes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'email',
        'code_hash',
        'expires_at',
        'used',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used'       => 'boolean',
        ];
    }
}
