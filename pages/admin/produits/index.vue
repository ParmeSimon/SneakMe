<script setup>
import {ref} from "vue";
import '~/css/admin.css'
definePageMeta({
  layout: 'admin'
})
const columns = [
  { header: 'nom', accessorKey: 'title' },
  { header: 'description', accessorKey: 'description' },
  { header: 'couleur', accessorKey: 'couleur' },
  { header: 'pointure', accessorKey: 'pointure' },
  { header: 'prix', accessorKey: 'price' },
  { header: 'image', accessorKey: 'url_image', cell: ({row}) =>  h("img",{src:row.getValue("url_image"), class:"table_image"})},
  { header: 'categorie', accessorKey: 'categorie' },
  { header: 'sexe', accessorKey: 'sexe' },
]
const data = ref((await useFetch('http://localhost/SneakMe/api/produits.php')).data)


</script>

<template>
    <div class="container">
      <h1 class="title">Catalogue produits</h1>
      <div class="right">
        <UButton class="ajouter" label="Open" color="neutral"  variant="subtle" >Ajouter un produit</UButton>
      </div>
      <UTable class="u-table" :data="data" :columns="columns"/>
    </div>
</template>
