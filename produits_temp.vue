<script setup>
import {ref, h, onMounted} from "vue";
import '~/css/admin.css'
import '~/css/produits.css'

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
  console.log("Produit original:", product);
  
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
  
  console.log("ProductToEdit après conversion:", productToEdit.value);
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

<!-- Modal d'ajout -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="productModalLabel">Ajouter un produit</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Message de statut -->
        <div v-if="formStatus.message" :class="['alert', formStatus.isError ? 'alert-danger' : 'alert-success']">
          {{ formStatus.message }}
        </div>
        
        <form @submit.prevent="submitProduct">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="title" class="form-label">Nom</label>
              <input type="text" class="form-control" id="title" v-model="newProduct.title" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="price" class="form-label">Prix (en centimes)</label>
              <input type="number" step="0.01" class="form-control" id="price" v-model="newProduct.price" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" rows="3" v-model="newProduct.description" required></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Image du produit</label>
            <input type="file" class="form-control" id="image" accept="image/*" @change="handleImageChange">
            <div v-if="imagePreview" class="mt-2">
              <img :src="imagePreview" alt="Aperçu" class="img-thumbnail" style="max-height: 200px;">
            </div>
          </div>
          
          <div class="mb-3">
            <label for="url_image" class="form-label">URL de l'image (optionnel si image téléchargée)</label>
            <input type="text" class="form-control" id="url_image" v-model="newProduct.url_image">
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="id_couleur" class="form-label">Couleur</label>
              <select class="form-select" id="id_couleur" v-model.number="newProduct.id_couleur" required>
                <option value="" disabled selected>Sélectionner une couleur</option>
                <option v-for="couleur in couleurs" :key="couleur.id" :value="Number(couleur.id)">{{ couleur.nom }}</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="id_pointure" class="form-label">Pointure</label>
              <select class="form-select" id="id_pointure" v-model.number="newProduct.id_pointure" required>
                <option value="" disabled selected>Sélectionner une pointure</option>
                <option v-for="pointure in pointures" :key="pointure.id" :value="Number(pointure.id)">{{ pointure.nom }}</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="id_categorie" class="form-label">Catégorie</label>
              <select class="form-select" id="id_categorie" v-model.number="newProduct.id_categorie" required>
                <option value="" disabled selected>Sélectionner une catégorie</option>
                <option v-for="categorie in categories" :key="categorie.id" :value="Number(categorie.id)">{{ categorie.nom }}</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="id_sexe" class="form-label">Sexe</label>
              <select class="form-select" id="id_sexe" v-model.number="newProduct.id_sexe" required>
                <option value="" disabled selected>Sélectionner un sexe</option>
                <option v-for="sexe in sexes" :key="sexe.id" :value="Number(sexe.id)">{{ sexe.nom }}</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="closeProductModal" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" @click="submitProduct" :disabled="formStatus.loading">
          <span v-if="formStatus.loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
          Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="editProductModalLabel">Modifier un produit</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Message de statut -->
        <div v-if="formStatus.message" :class="['alert', formStatus.isError ? 'alert-danger' : 'alert-success']">
          {{ formStatus.message }}
        </div>
        
        <form @submit.prevent="updateProduct">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit-title" class="form-label">Nom</label>
              <input type="text" class="form-control" id="edit-title" v-model="productToEdit.title" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit-price" class="form-label">Prix (en centimes)</label>
              <input type="number" step="0.01" class="form-control" id="edit-price" v-model="productToEdit.price" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="edit-description" class="form-label">Description</label>
            <textarea class="form-control" id="edit-description" rows="3" v-model="productToEdit.description" required></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Image du produit</label>
            <input type="file" class="form-control" id="edit-image" accept="image/*" @change="handleEditImageChange">
            <div class="mt-2">
              <img :src="editImagePreview || productToEdit.url_image" alt="Aperçu" class="img-thumbnail" style="max-height: 200px;">
            </div>
          </div>
          
          <div class="mb-3">
            <label for="edit-url_image" class="form-label">URL de l'image (optionnel si image téléchargée)</label>
            <input type="text" class="form-control" id="edit-url_image" v-model="productToEdit.url_image">
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit-id_couleur" class="form-label">Couleur</label>
              <select class="form-select" id="edit-id_couleur" v-model.number="productToEdit.id_couleur" required>
                <option v-for="couleur in couleurs" :key="couleur.id" :value="Number(couleur.id)">{{ couleur.nom }}</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="edit-id_pointure" class="form-label">Pointure</label>
              <select class="form-select" id="edit-id_pointure" v-model.number="productToEdit.id_pointure" required>
                <option v-for="pointure in pointures" :key="pointure.id" :value="Number(pointure.id)">{{ pointure.nom }}</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit-id_categorie" class="form-label">Catégorie</label>
              <select class="form-select" id="edit-id_categorie" v-model.number="productToEdit.id_categorie" required>
                <option v-for="categorie in categories" :key="categorie.id" :value="Number(categorie.id)">{{ categorie.nom }}</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="edit-id_sexe" class="form-label">Sexe</label>
              <select class="form-select" id="edit-id_sexe" v-model.number="productToEdit.id_sexe" required>
                <option v-for="sexe in sexes" :key="sexe.id" :value="Number(sexe.id)">{{ sexe.nom }}</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="closeEditModal" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" @click="updateProduct" :disabled="formStatus.loading">
          <span v-if="formStatus.loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
          Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>
</template>



