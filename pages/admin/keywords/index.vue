<script setup>
import {ref} from "vue";
import '~/css/admin.css'
definePageMeta({
  layout: 'admin'
})
const columns = [
  { header: 'Mot clé', accessorKey: 'title' },
  { header: 'Description', accessorKey: 'description' },
  { header: 'Réponse', accessorKey: 'result' },
]
const data = ref((await useFetch('http://localhost/SneakMe/api/keywords.php')).data)


</script>

<template>
  <div class="container">
    <h1 class="title">Mot clé</h1>
    <UTable class="u-table" :data="data" :columns="columns" />
  </div>
</template>
