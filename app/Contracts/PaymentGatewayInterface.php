<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Crear un cliente en la pasarela de pago
     *
     * @param array $customerData
     * @return array
     */
    public function createCustomer(array $customerData): array;

    /**
     * Actualizar un cliente en la pasarela de pago
     *
     * @param string $gatewayCustomerId
     * @param array $customerData
     * @return array
     */
    public function updateCustomer(string $gatewayCustomerId, array $customerData): array;

    /**
     * Crear una tarjeta para un cliente
     *
     * @param string $gatewayCustomerId
     * @param string $tokenOrCardData
     * @return array
     */
    public function createCard(string $gatewayCustomerId, string $tokenOrCardData): array;

    /**
     * Crear un plan de suscripción
     *
     * @param array $planData
     * @return array
     */
    public function createPlan(array $planData): array;

    /**
     * Actualizar un plan de suscripción
     *
     * @param string $gatewayPlanId
     * @param array $planData
     * @return array
     */
    public function updatePlan(string $gatewayPlanId, array $planData): array;

    /**
     * Crear una suscripción
     *
     * @param array $subscriptionData
     * @return array
     */
    public function createSubscription(array $subscriptionData): array;

    /**
     * Actualizar una suscripción
     *
     * @param string $gatewaySubscriptionId
     * @param array $subscriptionData
     * @return array
     */
    public function updateSubscription(string $gatewaySubscriptionId, array $subscriptionData): array;

    /**
     * Crear un link de pago
     *
     * @param array $linkData
     * @return array
     */
    public function createPaymentLink(array $linkData): array;

    /**
     * Obtener el nombre de la pasarela
     *
     * @return string
     */
    public function getGatewayName(): string;
}
