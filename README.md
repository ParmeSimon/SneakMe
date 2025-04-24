# Nuxt 3 Minimal Starter

Look at the [Nuxt 3 documentation](https://nuxt.com/docs/getting-started/introduction) to learn more.

## Setup

Make sure to install the dependencies:

```bash
# yarn
yarn install

# npm
npm install

# pnpm
pnpm install
```

## Development Server

Start the development server on http://localhost:3000

```bash
npm run dev
```

## Production

Build the application for production:

```bash
npm run build
```

Locally preview production build:

```bash
npm run preview
```

Check out the [deployment documentation](https://nuxt.com/docs/getting-started/deployment) for more information.




# Routes API

api/
├── auth
│   ├── login
│   │   └── post
│   │       └── se connecter au compte
│   └── register
│       └── post
│           └── inscription compte
│  
├── accounts
│   ├── get
│   │   └── retour les infos du compte
│   ├── put
│   │   └── modifie les infos du compte
│   └── delete
│       └── supprime le compte
│ 
├── admin
│   ├── get
│   │   └── retour les infos du compte admin
│   ├── put
│   │   └── modifie les infos du compte admin
│   └── delete
│       └── supprime le compte admin
│  
├── products
│   ├── get
│   │   └── retour les produits
│   ├── post
│   │   └── creer un produit
│   ├── put
│   │   └── modifie un produit
│   └── delete
│       └── supprime un produit
│  
├── support
│   ├── get
│   │   └── retourne les tickets
│   ├── post
│   │   └── creer un ticket
│   ├── put
│   │   └── modifie un ticket
│   └── delete
│       └── supprime un ticket
│  
├── invoices
│   ├── get
│   │   └── retourne les factures
│   ├── post
│   │   └── creer une facture
│   ├── put
│   │   └── modifie une facture
│   └── delete
│       └── supprime une facture
│  
├── leads
│   ├── get
│   │   └── retour les leads
│   ├── post
│   │   └── creer un lead
│   ├── put
│   │   └── modifie un lead
│   └── delete
│       └── supprime un lead
│  
├── customers
│   ├── get
│   │   └── retourne les clients
│   ├── post
│   │   └── creer un client
│   ├── put
│   │   └── modifie un client
│   └── delete
│       └── supprime un client
│  
├── suppliers
│   ├── get
│   │   └── retour les fournisseurs
│   ├── post
│   │   └── creer un fournisseur
│   ├── put
│   │   └── modifie un fournisseur
│   └── delete
│       └── supprime un fournisseur
│  
├── orders
│   ├── get
│   │   └── retourne les commandes
│   ├── post
│   │   └── creer une commande
│   ├── put
│   │   └── modifie une commande
│   └── delete
│       └── supprime une commande
│  
├── shipments
│   ├── get
│   │   └── retourne les shipments
│   ├── post
│   │   └── creer un shipment
│   ├── put
│   │   └── modifie un shipment
│   └── delete
│       └── supprime un shipment
│  
├── payments
│   ├── get
│   │   └── retourne les payments
│   ├── post
│   │   └── creer un payment
│   ├── put
│   │   └── modifie un payment
│   └── delete
│       └── supprime un payment
│  
├── products
│   ├── get
│   │   └── retourne les produits
│   ├── post
│   │   └── creer un produit
│   ├── put
│   │   └── modifie un produit
│   └── delete
│       └── supprime un produit
