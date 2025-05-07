<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Product;
use Stripe\Checkout\Session;
use Stripe\Price;
use Stripe\Product as StripeProduct;
use Stripe\Webhook;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StripeService
{
    public function __construct(ParameterBagInterface $params)
    {
        Stripe::setApiKey($params->get('stripe.secret_key'));
    }

    public function createProduct(Product $product): array
    {
        $stripeProduct = StripeProduct::create([
            'name' => $product->getName(),
            'description' => $product->getDescription(),
        ]);

        $stripePrice = Price::create([
            'unit_amount' => $product->getPrice(),
            'currency' => 'eur',
            'product' => $stripeProduct->id,
        ]);

        return [
            'product_id' => $stripeProduct->id,
            'price_id' => $stripePrice->id,
        ];
    }

    public function createCheckoutSession(array $products, Order $order): Session
    {
        $lineItems = [];

        foreach ($products as $product) {
            $lineItems[] = [
                'price' => $product->getStripePriceId(),
                'quantity' => 1,
            ];
        }

        return Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'metadata' => ['order_id' => $order->getId()],
            'success_url' => "http://localhost:5173/success/{$order->getId()}",
            'cancel_url' => "http://localhost:5173/cancel/{$order->getId()}",
        ]);
    }

    public function handleWebhook(string $payload, string $sigHeader): array
    {
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'];

        $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

        return [
            'type' => $event->type,
            'status' => $event->data->object->status ?? null,
            'order_id' => $event->data->object->metadata['order_id'] ?? null,
        ];
    }

    public function checkSessionStatus(string $sessionId): array
    {
        $session = Session::retrieve($sessionId);

        return ['status' => $session->payment_status];
    }
}