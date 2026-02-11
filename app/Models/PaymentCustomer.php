<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gateway',
        'gateway_customer_id',
        'email',
        'name',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con las tarjetas
     */
    public function cards()
    {
        return $this->hasMany(PaymentCard::class);
    }

    /**
     * Relación con las suscripciones
     */
    public function subscriptions()
    {
        return $this->hasMany(PaymentSubscription::class);
    }
}
