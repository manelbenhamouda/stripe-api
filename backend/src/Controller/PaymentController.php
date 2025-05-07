<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class PaymentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private ProductRepository $productRepository,
        private StripeService $stripeService
    ) {}

    #[Route('/checkout', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['product_ids']) || empty($data['product_ids'])) {
            return $this->json([
                'message' => 'Aucun produit sélectionné'
            ], Response::HTTP_BAD_REQUEST);
        }

        $productIds = $data['product_ids'];
        $products = [];
        $total = 0;

        foreach ($productIds as $productId) {
            $product = $this->productRepository->find($productId);

            if (!$product) {
                return $this->json([
                    'message' => 'Produit non trouvé: ' . $productId
                ], Response::HTTP_BAD_REQUEST);
            }

            $products[] = $product;
            $total += $product->getPrice();
        }

        $order = new Order();
        $order->setStatus('pending');
        $order->setTotal($total);
        $order->setCreatedAt(new \DateTimeImmutable());
        foreach ($products as $product) {
            $order->addProduct($product);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        try {
            $session = $this->stripeService->createCheckoutSession($products, $order);

            $order->setStripeSessionId($session->id);
            $this->entityManager->flush();

            return $this->json([
                'sessionId' => $session->id,
                'url' => $session->url,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la création de la session de paiement',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        if (!$sigHeader) {
            return $this->json([
                'message' => 'Header stripe-signature manquant'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = $this->stripeService->handleWebhook($payload, $sigHeader);

            if ($event['type'] === 'checkout.session.completed' && $event['status'] === 'completed') {
                $orderId = $event['order_id'];
                $order = $this->orderRepository->find($orderId);

                if ($order) {
                    $order->setStatus('paid');
                    $this->entityManager->flush();
                }
            }

            return $this->json(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur webhook Stripe',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/payment/success/{orderId}', name: 'payment_success', methods: ['GET'])]
    public function paymentSuccess(int $orderId): JsonResponse
    {
        $order = $this->orderRepository->find($orderId);

        if (!$order) {
            return $this->json(['message' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        try {
            $status = $this->stripeService->checkSessionStatus($order->getStripeSessionId());

            if ($status['status'] === 'paid') {
                $order->setStatus('paid');
                $this->entityManager->flush();
            }

            return $this->json([
                'message' => 'Paiement réussi',
                'order' => [
                    'id' => $order->getId(),
                    'status' => $order->getStatus(),
                    'total' => $order->getTotal(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la vérification du paiement',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/payment/cancel/{orderId}', name: 'payment_cancel', methods: ['GET'])]
    public function paymentCancel(int $orderId): JsonResponse
    {
        $order = $this->orderRepository->find($orderId);

        if (!$order) {
            return $this->json(['message' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $order->setStatus('cancelled');
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Paiement annulé',
            'order' => [
                'id' => $order->getId(),
                'status' => $order->getStatus(),
            ]
        ]);
    }
}