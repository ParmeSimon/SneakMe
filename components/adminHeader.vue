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
    <UButton :class="buttonClass('/admin/select')" class="expendHover">Options</UButton>
    <div class="subMenu">
      <ULink to="/admin/categories">
        <UButton :class="buttonClass('/admin/categories')" class="subMenuButton">Catégories</UButton>
      </ULink>
      <ULink to="/admin/couleurs">
        <UButton :class="buttonClass('/admin/couleurs')" class="subMenuButton">Couleurs</UButton>
      </ULink>
      <ULink to="/admin/pointures">
        <UButton :class="buttonClass('/admin/pointures')" class="subMenuButton">Pointures</UButton>
      </ULink>
    </div>
    
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
