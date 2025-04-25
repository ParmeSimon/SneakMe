<script setup>
import {ref} from "vue";
import '~/css/admin.css'
definePageMeta({
  layout: 'admin'
})
const columns = [
  { header: 'Pseudo', accessorKey: 'username' },
  { header: 'Prénom', accessorKey: 'prenom' },
  { header: 'Nom', accessorKey: 'nom' },
  { header: 'Email', accessorKey: 'email' },
  { header: 'Date de création', accessorKey: 'created_at' }
]
const data = ref((await useFetch('http://localhost/SneakMe/api/users.php')).data)


</script>

<template>
  <div class="container">
    <h1 class="title">utilisateurs</h1>
    <UTable class="u-table" :data="data" :columns="columns" />
  </div>
</template>
