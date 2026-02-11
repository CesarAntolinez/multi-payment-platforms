<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCard extends Model
{
use HasFactory;

    protected $fillable = [
        'payment_customer_id',
        'gateway',
        'gateway_card_id',
        'brand',
        'last4',
        'exp_month',
        'exp_year',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Relación con el cliente
     */
    public function customer()
    {
        return $this->belongsTo(PaymentCustomer::class, 'payment_customer_id');
    }

    /**
     * Verificar si la tarjeta está expirada
     */
    public function isExpired()
    {
        $now = now();
        return $this->exp_year < $now->year ||
               ($this->exp_year == $now->year && $this->exp_month < $now->month);
    }
}
