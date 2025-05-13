<script setup>
import {ref, h, onMounted} from "vue";
import '~/css/admin.css'
import '~/css/produits.css'
// Import the modal components
import AddProductModal from '~/components/modal/AddProductModal.vue'
import EditProductModal from '~/components/modal/EditProductModal.vue'

definePageMeta({
  layout: 'admin'
})

// Données pour les select
const couleurs = ref([])
const pointures = ref([])
const categories = ref([])
const sexes = ref([])

// Chargement des données pour les selects
const loadSelectData = async () => {
  try {
    const [couleursRes, pointuresRes, categoriesRes, sexesRes] = await Promise.all([
      fetch('http://localhost/SneakMe/api/couleurs.php').then(res => res.json()),
      fetch('http://localhost/SneakMe/api/pointures.php').then(res => res.json()),
      fetch('http://localhost/SneakMe/api/categories.php').then(res => res.json()),
      fetch('http://localhost/SneakMe/api/sexes.php').then(res => res.json())
    ])
    
    couleurs.value = couleursRes
    pointures.value = pointuresRes
    categories.value = categoriesRes
    sexes.value = sexesRes
  } catch (error) {
    console.error('Erreur lors du chargement des données:', error)
  }
}

// Chargement des données au montage du composant
onMounted(() => {
  loadSelectData()
})

const columns = [
  { header: 'nom', accessorKey: 'title' },
  { header: 'description', accessorKey: 'description' },
  { header: 'couleur', accessorKey: 'couleur' },
  { header: 'pointure', accessorKey: 'pointure' },
  { header: 'prix', accessorKey: 'price', cell: ({row}) => row.getValue("price")/100 + " €" },
  { header: 'image', accessorKey: 'url_image', cell: ({row}) =>  h("img",{src:row.getValue("url_image"), class:"table_image"})},
  { header: 'categorie', accessorKey: 'categorie' },
  { header: 'sexe', accessorKey: 'sexe' },
  { 
    header: 'Actions', 
    cell: ({row}) => h('div', { class: 'action-buttons' }, [
      h('button', { 
        class: 'btn btn-sm btn-outline-primary me-2', 
        'data-bs-toggle': 'modal',
        'data-bs-target': '#editProductModal',
        onClick: () => editProduct(row.original)
      }, [h('i', { class: 'fa-solid fa-pen' })]),
      h('button', { 
        class: 'btn btn-sm btn-outline-danger', 
        onClick: () => deleteProduct(row.original)
      }, [h('i', { class: 'fa-solid fa-trash' })])
    ])
  }
]
const data = ref((await useFetch('http://localhost/SneakMe/api/produits.php')).data)

// État du formulaire
const formStatus = ref({
  loading: false,
  message: '',
  isError: false
})

// Fonction pour rafraîchir la liste des produits
const refreshProducts = async () => {
  const response = await useFetch('http://localhost/SneakMe/api/produits.php')
  data.value = response.data.value
}

// Produit à modifier
const productToEdit = ref({
  id: null,
  title: '',
  description: '',
  price: '',
  url_image: '',
  id_couleur: '',
  id_pointure: '',
  id_categorie: '',
  id_sexe: ''
})

// Gestion des images
const newImageFile = ref(null)
const editImageFile = ref(null)
const imagePreview = ref('')
const editImagePreview = ref('')

// Fonction pour gérer le changement d'image (ajout)
const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    newImageFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Fonction pour gérer le changement d'image (édition)
const handleEditImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    editImageFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      editImagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Fonction pour télécharger l'image
const uploadImage = async (file) => {
  if (!file) return null
  
  const formData = new FormData()
  formData.append('image', file)
  
  try {
    const response = await fetch('http://localhost/SneakMe/api/upload.php', {
      method: 'POST',
      body: formData
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
      return result.url
    } else {
      throw new Error(result.message || 'Erreur lors du téléchargement de l\'image')
    }
  } catch (error) {
    console.error('Erreur lors du téléchargement:', error)
    throw error
  }
}

// Fonction pour préparer l'édition d'un produit
const editProduct = (product) => {
  
  productToEdit.value = { 
    id: product.id,
    title: product.title,
    description: product.description,
    price: product.price,
    url_image: product.url_image,
    // Conversion explicite en nombre
    id_couleur: Number(product.id_couleur),
    id_pointure: Number(product.id_pointure),
    id_categorie: Number(product.id_categorie),
    id_sexe: Number(product.id_sexe)
  }
  
  // Réinitialiser l'image d'édition
  editImageFile.value = null
  editImagePreview.value = product.url_image
  
}

// Fonction pour mettre à jour un produit
const updateProduct = async () => {
  try {
    formStatus.value.loading = true
    formStatus.value.message = ''
    formStatus.value.isError = false
    
    // Télécharger l'image si une nouvelle a été sélectionnée
    if (editImageFile.value) {
      try {
        const imageUrl = await uploadImage(editImageFile.value)
        if (imageUrl) {
          productToEdit.value.url_image = imageUrl
        }
      } catch (error) {
        formStatus.value.message = 'Erreur lors du téléchargement de l\'image: ' + error.message
        formStatus.value.isError = true
        formStatus.value.loading = false
        return
      }
    }
    
    // S'assurer que les IDs sont des nombres
    const dataToSend = {
      ...productToEdit.value,
      id_couleur: Number(productToEdit.value.id_couleur),
      id_pointure: Number(productToEdit.value.id_pointure),
      id_categorie: Number(productToEdit.value.id_categorie),
      id_sexe: Number(productToEdit.value.id_sexe)
    }
    
    console.log("Données à envoyer:", dataToSend)
    
    const response = await fetch(`http://localhost/SneakMe/api/produits.php?id=${productToEdit.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(dataToSend)
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
      formStatus.value.message = result.message || 'Produit modifié avec succès!'
      formStatus.value.isError = false
      
      // Rafraîchir les données
      await refreshProducts()
      
      // Fermer le modal après 1 seconde
      setTimeout(() => {
        document.getElementById('closeEditModal').click()
        formStatus.value.message = ''
      }, 1000)
    } else {
      formStatus.value.message = result.message || 'Erreur lors de la modification'
      formStatus.value.isError = true
    }
  } catch (error) {
    console.error('Erreur lors de la modification:', error)
    formStatus.value.message = 'Erreur de connexion au serveur'
    formStatus.value.isError = true
  } finally {
    formStatus.value.loading = false
  }
}

// Fonction pour supprimer un produit
const deleteProduct = async (product) => {
  if (!confirm(`Êtes-vous sûr de vouloir supprimer le produit ${product.title} ?`)) {
    return
  }
  
  try {
    const response = await fetch(`http://localhost/SneakMe/api/produits.php?id=${product.id}`, {
      method: 'DELETE'
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
      alert(result.message || 'Produit supprimé avec succès!')
      
      // Rafraîchir les données
      await refreshProducts()
    } else {
      alert(result.message || 'Erreur lors de la suppression')
    }
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    alert('Erreur de connexion au serveur')
  }
}

// Nouveau produit
const newProduct = ref({
  title: '',
  description: '',
  price: '',
  url_image: '',
  id_couleur: '',
  id_pointure: '',
  id_categorie: '',
  id_sexe: ''
})

// Fonction pour soumettre le formulaire d'ajout
const submitProduct = async () => {
  try {
    formStatus.value.loading = true
    formStatus.value.message = ''
    formStatus.value.isError = false
    
    // Télécharger l'image si disponible
    if (newImageFile.value) {
      try {
        const imageUrl = await uploadImage(newImageFile.value)
        if (imageUrl) {
          newProduct.value.url_image = imageUrl
        }
      } catch (error) {
        formStatus.value.message = 'Erreur lors du téléchargement de l\'image: ' + error.message
        formStatus.value.isError = true
        formStatus.value.loading = false
        return
      }
    }
    
    const response = await fetch('http://localhost/SneakMe/api/produits.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newProduct.value)
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
      // Afficher le message de succès
      formStatus.value.message = result.message || 'Produit ajouté avec succès!'
      formStatus.value.isError = false
      
      // Rafraîchir les données
      await refreshProducts()
      
      // Réinitialiser le formulaire
      newProduct.value = { 
        title: '',
        description: '',
        price: '',
        url_image: '',
        id_couleur: '',
        id_pointure: '',
        id_categorie: '',
        id_sexe: ''
      }
      newImageFile.value = null
      imagePreview.value = ''
      
      // Fermer le modal après 1 seconde
      setTimeout(() => {
        document.getElementById('closeProductModal').click()
        formStatus.value.message = ''
      }, 1000)
    } else {
      // Afficher l'erreur
      formStatus.value.message = result.message || 'Erreur lors de l\'ajout du produit'
      formStatus.value.isError = true
    }
  } catch (error) {
    console.error('Erreur lors de l\'ajout:', error)
    formStatus.value.message = 'Erreur de connexion au serveur'
    formStatus.value.isError = true
  } finally {
    formStatus.value.loading = false
  }
}
</script>

<template>
    <div class="container">
      <h1 class="title">Catalogue produits</h1>
      <div class="right">
        <UButton class="ajouter" data-bs-toggle="modal" data-bs-target="#productModal">Ajouter un produit</UButton>
      </div>
      <UTable class="u-table" :data="data" :columns="columns"/>
    </div>

    <!-- Modals -->
    <AddProductModal 
      :newProduct="newProduct"
      :formStatus="formStatus"
      :couleurs="couleurs"
      :pointures="pointures"
      :categories="categories"
      :sexes="sexes"
      :imagePreview="imagePreview"
      @submit="submitProduct"
      @image-change="handleImageChange"
    />
    
    <EditProductModal 
      :productToEdit="productToEdit"
      :formStatus="formStatus"
      :couleurs="couleurs"
      :pointures="pointures"
      :categories="categories"
      :sexes="sexes"
      :editImagePreview="editImagePreview"
      @update="updateProduct"
      @edit-image-change="handleEditImageChange"
    />
</template>
