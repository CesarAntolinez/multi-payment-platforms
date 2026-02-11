<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway',
        'gateway_plan_id',
        'name',
        'amount',
        'currency',
        'interval',
        'interval_count',
        'active',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Relación con las suscripciones
     */
    public function subscriptions()
    {
        return $this->hasMany(PaymentSubscription::class);
    }

    /**
     * Scope para planes activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
