<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use App\Models\VendorRequest;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Créer une session de paiement Stripe Checkout.
     */
    public function createCheckoutSession(User $user, Plan $plan, VendorRequest $vendorRequest): Session
    {
        return Session::create([
            'payment_method_types' => ['card', 'mobile_money'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($plan->currency),
                    'product_data' => [
                        'name' => 'Plan '.$plan->name,
                        'description' => $plan->description,
                        'images' => [asset('images/logo.png')],
                    ],
                    'unit_amount' => (int) ($plan->price * 100), // En centimes
                    'recurring' => [
                        'interval' => $plan->interval,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => route('vendor.payment.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('vendor.payment.cancel'),
            'customer_email' => $user->email,
            'metadata' => [
                'vendor_request_id' => $vendorRequest->id,
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ],
            'allow_promotion_codes' => true,
            'billing_address_collection' => 'required',
            'tax_id_collection' => [
                'enabled' => true,
            ],
        ]);
    }

    /**
     * Vérifier le statut d'une session de paiement.
     */
    public function verifyCheckoutSession(string $sessionId): array
    {
        try {
            $session = Session::retrieve($sessionId);

            return [
                'id' => $session->id,
                'status' => $session->payment_status,
                'customer_id' => $session->customer,
                'subscription_id' => $session->subscription,
                'metadata' => $session->metadata->toArray(),
                'amount_total' => $session->amount_total / 100,
                'currency' => $session->currency,
                'customer_email' => $session->customer_details->email,
                'customer_name' => $session->customer_details->name,
            ];
        } catch (\Exception $e) {
            Log::error('Erreur vérification session Stripe', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Traiter le webhook Stripe.
     */
    public function handleWebhook(string $payload, string $signature): array
    {
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );

            return match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
                'invoice.paid' => $this->handleInvoicePaid($event->data->object),
                'invoice.payment_failed' => $this->handleInvoiceFailed($event->data->object),
                'customer.subscription.deleted' => $this->handleSubscriptionCancelled($event->data->object),
                default => ['status' => 'unhandled', 'type' => $event->type],
            };
        } catch (\Exception $e) {
            Log::error('Erreur webhook Stripe', [
                'error' => $e->getMessage(),
            ]);

            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    /**
     * Gérer le succès d'un checkout.
     */
    private function handleCheckoutCompleted($session): array
    {
        $vendorRequest = VendorRequest::find($session->metadata->vendor_request_id);

        if ($vendorRequest) {
            $vendorRequest->update([
                'payment_session_id' => $session->id,
                'status' => VendorRequest::STATUS_APPROVED,
                'approved_at' => now(),
            ]);
        }

        return ['status' => 'success', 'vendor_request_id' => $vendorRequest?->id];
    }

    /**
     * Gérer le paiement d'une facture.
     */
    private function handleInvoicePaid($invoice): array
    {
        Log::info('Facture payée', ['invoice_id' => $invoice->id]);

        return ['status' => 'success', 'invoice_id' => $invoice->id];
    }

    /**
     * Gérer l'échec d'un paiement.
     */
    private function handleInvoiceFailed($invoice): array
    {
        Log::warning('Paiement échoué', ['invoice_id' => $invoice->id]);

        return ['status' => 'failed', 'invoice_id' => $invoice->id];
    }

    /**
     * Gérer l'annulation d'un abonnement.
     */
    private function handleSubscriptionCancelled($subscription): array
    {
        Log::info('Abonnement annulé', ['subscription_id' => $subscription->id]);

        return ['status' => 'cancelled', 'subscription_id' => $subscription->id];
    }

    /**
     * Générer une facture PDF (optionnel).
     */
    public function generateInvoice(string $invoiceId): string
    {
        // Implémentation avec un package PDF (ex: barryvdh/laravel-dompdf)
        // Retourne le chemin du PDF généré
        return '';
    }
}
