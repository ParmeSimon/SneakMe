<script setup>
import {ref} from "vue";
import '~/css/admin.css'
definePageMeta({
  layout: 'admin'
})
const columns = [
  { header: 'Utilisateur', accessorKey: 'id_user' },
  { header: 'Date', accessorKey: 'created_at' },
]
const data = ref((await useFetch('http://localhost/SneakMe/api/commandes.php')).data)


</script>

<template>
  <div class="container">
    <h1 class="title">liste des commandes</h1>
    <UTable class="u-table" :data="data" :columns="columns" />

  </div>
</template>
