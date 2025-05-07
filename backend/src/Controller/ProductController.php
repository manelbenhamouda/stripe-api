<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductRepository $productRepository,
        private StripeService $stripeService
    ) {}

    #[Route('', name: 'product_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $products = $this->productRepository->findAll();

        return $this->json(
            ['products' => $products],
            Response::HTTP_OK,
            [],
            ['groups' => 'product:read']
        );
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json(['message' => 'Produit non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            ['product' => $product],
            Response::HTTP_OK,
            [],
            ['groups' => 'product:read']
        );
    }

    #[Route('', name: 'product_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['price'])) {
            return $this->json(['message' => 'Champs requis manquants'], Response::HTTP_BAD_REQUEST);
        }

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description'] ?? null);
        $product->setPrice($data['price']);

        try {
            $stripeIds = $this->stripeService->createProduct($product);
            $product->setStripeProductId($stripeIds['product_id']);
            $product->setStripePriceId($stripeIds['price_id']);

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->json(
                ['message' => 'Produit créé avec succès', 'product' => $product],
                Response::HTTP_CREATED,
                [],
                ['groups' => 'product:read']
            );
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Erreur lors de la création du produit',
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'product_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json(['message' => 'Produit non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }

        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }

        if (isset($data['price'])) {
            $product->setPrice($data['price']);
            // TODO: mettre à jour le prix dans Stripe
        }

        $this->entityManager->flush();

        return $this->json(
            ['message' => 'Produit mis à jour', 'product' => $product],
            Response::HTTP_OK,
            [],
            ['groups' => 'product:read']
        );
    }

    #[Route('/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json(['message' => 'Produit non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return $this->json(['message' => 'Produit supprimé'], Response::HTTP_OK);
    }
}