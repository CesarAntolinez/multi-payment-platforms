<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_customer_id',
        'payment_plan_id',
        'gateway',
        'gateway_subscription_id',
        'status',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'metadata',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relación con el cliente
     */
    public function customer()
    {
        return $this->belongsTo(PaymentCustomer::class, 'payment_customer_id');
    }

    /**
     * Relación con el plan
     */
    public function plan()
    {
        return $this->belongsTo(PaymentPlan::class, 'payment_plan_id');
    }

    /**
     * Scope para suscripciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
