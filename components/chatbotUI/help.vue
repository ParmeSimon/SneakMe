<template>
    <div>
        <h1 class="text-2xl font-bold mb-3">Liste des commandes disponibles :</h1>
        <UAccordion type="multiple" class="min-w-[60vw]" :items="items" />
    </div>
</template>

<script setup lang="ts">
import type { AccordionItem } from '@nuxt/ui'

const items = ref<AccordionItem[]>([
  {
    label: 'Commande',
    icon: 'i-lucide-shopping-bag',
    content: 'Affiche la liste de vos commandes passées et leur statut.'
  },
  {
    label: 'Boutique',
    icon: 'i-lucide-store',
    content: 'Accède à notre collection de sneakers et explore nos produits disponibles.'
  },
  {
    label: 'Panier',
    icon: 'i-lucide-shopping-cart',
    content: 'Affiche les produits actuellement dans votre panier d\'achat.'
  },
  {
    label: 'Ajouter au panier ${id}',
    icon: 'i-lucide-plus-circle',
    content: 'Ajoute le produit spécifié par son ID à votre panier d\'achat.'
  },
  {
    label: 'Voir produit ${id}',
    icon: 'i-lucide-eye',
    content: 'Affiche les détails du produit correspondant à l\'ID spécifié.'
  },
  {
    label: 'Catégorie ${type} ${value}',
    icon: 'i-lucide-tag',
    content: 'Filtre les produits selon le type de catégorie et la valeur spécifiés.'
  },
  {
    label: 'login',
    icon: 'i-lucide-log-in',
    content: 'Accède à la page de connexion pour vous identifier avec votre compte.'
  },
  {
    label: 'register',
    icon: 'i-lucide-user-plus',
    content: 'Accède au formulaire d\'inscription pour créer un nouveau compte utilisateur.'
  },
  {
    label: 'logout',
    icon: 'i-lucide-log-out',
    content: 'Déconnecte votre session utilisateur actuelle.'
  },
  {
    label: '/help',
    icon: 'i-lucide-help-circle',
    content: 'Affiche cette page d\'aide avec la liste des commandes disponibles.'
  }
])
</script>