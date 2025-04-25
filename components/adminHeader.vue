<template>
  <header class="header">
    <ULink to="/admin/commandes">
      <UButton :class="buttonClass('/admin/commandes')">Commandes</UButton>
    </ULink>
    <ULink to="/admin/keywords">
      <UButton :class="buttonClass('/admin/keywords')">Mot clés</UButton>
    </ULink>
    <ULink to="/admin/produits">
      <UButton :class="buttonClass('/admin/produits')">Produits</UButton>
    </ULink>
    <ULink to="/admin/users">
      <UButton :class="buttonClass('/admin/users')">Utilisateurs</UButton>
    </ULink>
  </header>
</template>

<script setup>
import {useRoute} from 'vue-router'
import '~/css/header.css'

const route = useRoute()

const buttonClass = (path) => {
  return {
    'button': true,
    'button-active': route.path === path
  }
}
</script>
