<?php

namespace App\Http\Controllers;

use App\Models\PaymentWebhook;
use App\Models\PaymentSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class WebhookController extends Controller
{
    /**
     * Manejar webhooks de Stripe
     */
    public function handleStripe(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            // Verificar la firma del webhook
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Webhook Stripe - Payload inválido', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Webhook Stripe - Firma inválida', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Guardar el webhook
        $webhook = PaymentWebhook::create([
            'gateway' => 'stripe',
            'event_type' => $event->type,
            'event_id' => $event->id,
            'payload' => json_decode($payload, true),
        ]);

        // Procesar el evento
        try {
            $this->processStripeEvent($event);
            $webhook->markAsProcessed();
        } catch (\Exception $e) {
            Log::error('Error procesando webhook Stripe', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            $webhook->markAsError($e->getMessage());
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Procesar eventos de Stripe
     */
    protected function processStripeEvent($event)
    {
        switch ($event->type) {
            case 'customer.subscription.updated':
            case 'customer.subscription.created':
                $this->handleSubscriptionUpdate($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;

            default:
                Log::info('Evento Stripe no manejado', ['type' => $event->type]);
        }
    }

    /**
     * Manejar actualización de suscripción
     */
    protected function handleSubscriptionUpdate($stripeSubscription)
    {
        $subscription = PaymentSubscription::where('gateway_subscription_id', $stripeSubscription->id)
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => $stripeSubscription->status,
                'current_period_start' => date('Y-m-d H:i:s', $stripeSubscription->current_period_start),
                'current_period_end' => date('Y-m-d H:i:s', $stripeSubscription->current_period_end),
            ]);

            Log::info('Suscripción actualizada', [
                'subscription_id' => $subscription->id,
                'status' => $stripeSubscription->status,
            ]);
        }
    }

    /**
     * Manejar cancelación de suscripción
     */
    protected function handleSubscriptionDeleted($stripeSubscription)
    {
        $subscription = PaymentSubscription::where('gateway_subscription_id', $stripeSubscription->id)
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
            ]);

            Log::info('Suscripción cancelada', ['subscription_id' => $subscription->id]);
        }
    }

    /**
     * Manejar pago exitoso
     */
    protected function handleInvoicePaymentSucceeded($invoice)
    {
        Log::info('Pago exitoso', [
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount_paid / 100,
        ]);

        // Aquí puedes agregar lógica adicional, como enviar emails
    }

    /**
     * Manejar pago fallido
     */
    protected function handleInvoicePaymentFailed($invoice)
    {
        Log::warning('Pago fallido', [
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount_due / 100,
        ]);

        // Aquí puedes agregar lógica para notificar al usuario
    }

    /**
     * Manejar webhooks de PayPal
     */
    public function handlePayPal(Request $request)
    {
        $payload = $request->all();

        // Guardar el webhook
        $webhook = PaymentWebhook::create([
            'gateway' => 'paypal',
            'event_type' => $payload['event_type'] ?? 'unknown',
            'event_id' => $payload['id'] ?? uniqid('paypal_'),
            'payload' => $payload,
        ]);

        try {
            // Procesar eventos de PayPal
            $this->processPayPalEvent($payload);
            $webhook->markAsProcessed();
        } catch (\Exception $e) {
            Log::error('Error procesando webhook PayPal', [
                'error' => $e->getMessage(),
            ]);
            $webhook->markAsError($e->getMessage());
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Procesar eventos de PayPal
     */
    protected function processPayPalEvent($payload)
    {
        $eventType = $payload['event_type'] ?? '';

        switch ($eventType) {
            case 'BILLING.SUBSCRIPTION.ACTIVATED':
            case 'BILLING.SUBSCRIPTION.UPDATED':
                Log::info('Suscripción PayPal actualizada', ['payload' => $payload]);
                break;

            case 'BILLING.SUBSCRIPTION.CANCELLED':
                Log::info('Suscripción PayPal cancelada', ['payload' => $payload]);
                break;

            case 'PAYMENT.SALE.COMPLETED':
                Log::info('Pago PayPal completado', ['payload' => $payload]);
                break;

            default:
                Log::info('Evento PayPal no manejado', ['type' => $eventType]);
        }
    }

    /**
     * Manejar webhooks de Mercado Pago
     */
    public function handleMercadoPago(Request $request)
    {
        $payload = $request->all();

        // Guardar el webhook
        $webhook = PaymentWebhook::create([
            'gateway' => 'mercadopago',
            'event_type' => $payload['type'] ?? 'unknown',
            'event_id' => $payload['id'] ?? uniqid('mercadopago_'),
            'payload' => $payload,
        ]);

        try {
            // Procesar eventos de Mercado Pago
            $this->processMercadoPagoEvent($payload);
            $webhook->markAsProcessed();
        } catch (\Exception $e) {
            Log::error('Error procesando webhook Mercado Pago', [
                'error' => $e->getMessage(),
            ]);
            $webhook->markAsError($e->getMessage());
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Procesar eventos de Mercado Pago
     */
    protected function processMercadoPagoEvent($payload)
    {
        $eventType = $payload['type'] ?? '';

        switch ($eventType) {
            case 'payment':
                $this->handleMercadoPagoPayment($payload);
                break;

            case 'subscription_preapproval':
            case 'subscription_authorized_payment':
                $this->handleMercadoPagoSubscription($payload);
                break;

            case 'preapproval':
                $this->handleMercadoPagoPreapproval($payload);
                break;

            default:
                Log::info('Evento Mercado Pago no manejado', ['type' => $eventType]);
        }
    }

    /**
     * Manejar pago de Mercado Pago
     */
    protected function handleMercadoPagoPayment($payload)
    {
        $action = $payload['action'] ?? '';
        
        switch ($action) {
            case 'payment.created':
            case 'payment.updated':
                Log::info('Pago Mercado Pago actualizado', [
                    'payment_id' => $payload['data']['id'] ?? null,
                    'action' => $action,
                ]);
                break;

            default:
                Log::info('Acción de pago Mercado Pago no manejada', ['action' => $action]);
        }
    }

    /**
     * Manejar suscripción de Mercado Pago
     */
    protected function handleMercadoPagoSubscription($payload)
    {
        $subscriptionId = $payload['data']['id'] ?? null;

        if ($subscriptionId) {
            $subscription = PaymentSubscription::where('gateway_subscription_id', $subscriptionId)
                ->first();

            if ($subscription) {
                Log::info('Suscripción Mercado Pago actualizada', [
                    'subscription_id' => $subscription->id,
                    'gateway_subscription_id' => $subscriptionId,
                ]);
            }
        }

        Log::info('Evento de suscripción Mercado Pago', [
            'type' => $payload['type'] ?? 'unknown',
            'subscription_id' => $subscriptionId,
        ]);
    }

    /**
     * Manejar preapproval de Mercado Pago
     */
    protected function handleMercadoPagoPreapproval($payload)
    {
        Log::info('Preapproval Mercado Pago', [
            'payload' => $payload,
        ]);
    }
}
