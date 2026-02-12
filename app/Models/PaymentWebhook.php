<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway',
        'event_type',
        'event_id',
        'payload',
        'processed',
        'processed_at',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Marcar el webhook como procesado
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
        ]);
    }

    /**
     * Marcar el webhook con error
     */
    public function markAsError(string $error): void
    {
        $this->update([
            'processed' => false,
            'error' => $error,
        ]);
    }

    /**
     * Scope para webhooks no procesados
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }
}
