# Stripe API – Symfony 7 + React

Mini projet e-commerce développée avec Symfony 7 (backend) et React (frontend), intégrant Stripe pour la gestion des paiements. Il permet la création de produits, la gestion de commandes et le traitement sécurisé des paiements via Stripe Checkout.

---

##Installation du backend Symfony

1. Cloner ce dépôt et se placer dans le dossier du projet.
2. Installer les dépendances PHP avec Composer.
3. Créer un fichier `.env.local` à la racine du projet contenant les variables suivantes :
   - DATABASE_URL : configuration de votre base de données MySQL.
   - STRIPE_SECRET_KEY : votre clé secrète Stripe.
   - STRIPE_PUBLIC_KEY : votre clé publique Stripe.
   - STRIPE_WEBHOOK_SECRET : le secret généré par Stripe CLI pour les webhooks.
4. Créer la base de données.
5. Générer et exécuter les migrations Doctrine.
6. Lancer le serveur Symfony pour démarrer l’API.

---

##Fonctionnement général de l’API

L’API repose sur deux entités principales : Product et Order.

- Un produit peut être créé via l’API et est automatiquement enregistré sur Stripe.
- Une commande est générée avec un ou plusieurs produits, puis associée à une session Stripe.
- Les webhooks Stripe mettent à jour automatiquement le statut de la commande une fois le paiement confirmé.

---

##Liste des routes API

### Produits

- `GET /api/products` : Liste tous les produits.
- `GET /api/products/{id}` : Détail d’un produit.
- `POST /api/products` : Création d’un produit avec les champs `name`, `description` et `price`.

### Paiement / Commande

- `POST /api/checkout` : Crée une commande et une session Stripe. Nécessite une liste `product_ids`.
- `GET /api/payment/success/{orderId}` : Vérifie si la commande a été payée.
- `GET /api/payment/cancel/{orderId}` : Annule la commande.
- `POST /api/webhook` : Reçoit les événements Stripe (checkout.session.completed).

---

## Tests avec Insomnia ou Postman

- Créer une requête `POST /api/products` avec les champs JSON `name`, `description`, `price`.
- Créer une requête `POST /api/checkout` avec un JSON contenant `product_ids` (ex : [1]).
- Vérifier une commande avec `GET /api/payment/success/{id}`.
- Simuler un échec avec `GET /api/payment/cancel/{id}`.
- Pour les webhooks, envoyer un `POST /api/webhook` avec le header `stripe-signature` et un payload simulé.

---

## Tests Stripe CLI

1. Installer Stripe CLI (stripe.dev).
2. Lancer l’écoute : `stripe listen --forward-to localhost:8000/api/webhook`.
3. Copier le secret `whsec_xxx` dans `.env.local` sous `STRIPE_WEBHOOK_SECRET`.
4. Déclencher un événement de paiement simulé : `stripe trigger checkout.session.completed`.
5. Le webhook mettra automatiquement à jour le statut de la commande correspondante.

---

## Frontend React

Le frontend affichera les produits et permettra d’initier les paiements via Stripe.js, avec une navigation fluide, une interface responsive, et une intégration complète avec l’API Symfony.

---

## Structure du projet

- `src/Controller/` : les routes API.
- `src/Entity/` : entités Product et Order.
- `src/Service/StripeService.php` : logique Stripe (création, webhook, statut).
- `config/` : configuration Symfony et services.
- `public/` : entrée du projet.
- `migrations/` : fichiers de migration Doctrine.

---

