<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLink extends Model
{
     use HasFactory;

    protected $fillable = [
        'gateway',
        'gateway_link_id',
        'amount',
        'currency',
        'description',
        'url',
        'status',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Verificar si el link está expirado
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope para links activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }
}
