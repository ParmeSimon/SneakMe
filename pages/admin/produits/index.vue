<script setup>
import {ref, h, onMounted} from "vue";
import '~/assets/css/admin.css'
definePageMeta({
  layout: 'admin'
})

import { onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
let loadingAuth = ref(true)

async function checkAuth() {
    const adminTokenStr = localStorage.getItem('admin_token')
    if (!adminTokenStr) {
        console.log('Pas de token admin trouvé')
        return false
    }
    
    try {
        const adminToken = JSON.parse(adminTokenStr)
        
        // Affichage des données pour débogage
        console.log('Données envoyées:', { email: adminToken.email, password: adminToken.password })
        
        // Utilisation de la nouvelle API simplifiée pour la vérification d'admin
        const response = await fetch('http://localhost/sneakme/api/verify_admin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: adminToken.email,
                password: adminToken.password
            })
        })
        
        // Vérifier le statut de la réponse
        console.log('Statut de la réponse:', response.status)
        
        const data = await response.json()
        
        // Afficher toutes les données de réponse pour débogage
        console.log('Réponse complète:', data)
        
        if (data.success) {
            console.log('Authentification réussie')
            return true
        } else {
            console.log('Authentification échouée:', data.message || data.error)
            return false
        }
    } catch (error) {
        // Capturer et afficher les erreurs
        console.error('Erreur lors de la requête:', error)
        return false
    }
}

onBeforeMount(async () => {
    // Attendre le résultat de checkAuth avant de décider où rediriger
    const isAuthenticated = await checkAuth()
    console.log('Résultat de checkAuth:', isAuthenticated)
    
    if (isAuthenticated) {
        loadingAuth.value = false
    } else {
        router.push('/admin/login')
    }
})

// Données pour les select
const couleurs = ref([])
const pointures = ref([])
const categories = ref([])
const sexes = ref([])

// Chargement des données pour les selects
const loadSelectData = async () => {
  try {
    const timestamp = new Date().getTime()
    const [couleursRes, pointuresRes, categoriesRes, sexesRes] = await Promise.all([
      fetch(`http://localhost/sneakme/api/couleurs.php?t=${timestamp}`).then(res => res.json()),
      fetch(`http://localhost/sneakme/api/pointures.php?t=${timestamp}`).then(res => res.json()),
      fetch(`http://localhost/sneakme/api/categories.php?t=${timestamp}`).then(res => res.json()),
      fetch(`http://localhost/sneakme/api/sexes.php?t=${timestamp}`).then(res => res.json())
    ])
    
    couleurs.value = couleursRes
    pointures.value = pointuresRes
    categories.value = categoriesRes
    sexes.value = sexesRes
    console.log('Données des selects chargées correctement')
  } catch (error) {
    console.error('Erreur lors du chargement des données:', error)
  }
}

// Chargement des données au montage du composant
onMounted(() => {
  loadSelectData()
})

// Plus besoin de la définition des colonnes UTable
// Nous utilisons un tableau HTML standard maintenant
const data = ref([])

// État des modales
const showAddModal = ref(false)
const showEditModal = ref(false)

// État du formulaire
const formStatus = ref({
  loading: false,
  message: '',
  isError: false
})

// Fonction pour rafraîchir la liste des produits
const refreshProducts = async () => {
  try {
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/produits.php?t=${new Date().getTime()}`)
    
    if (response.ok) {
      data.value = await response.json()
      console.log('Données produits rafraîchies:', data.value)
    } else {
      console.error('Erreur lors du rafraîchissement des produits:', response.statusText)
    }
  } catch (error) {
    console.error('Erreur lors du rafraîchissement des produits:', error)
  }
}

// Charger les produits au montage du composant
onMounted(() => {
  refreshProducts()
})

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
  
  console.log("ProductToEdit après conversion:", productToEdit.value);
}

// Fonction pour mettre à jour un produit
const updateProduct = async () => {
  try {
    formStatus.value.loading = true
    formStatus.value.message = ''
    formStatus.value.isError = false
    
    // S'assurer que les IDs sont des nombres
    const dataToSend = {
      ...productToEdit.value,
      id_couleur: Number(productToEdit.value.id_couleur),
      id_pointure: Number(productToEdit.value.id_pointure),
      id_categorie: Number(productToEdit.value.id_categorie),
      id_sexe: Number(productToEdit.value.id_sexe)
    }
    
    console.log("Données à envoyer:", dataToSend)
    
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/produits.php?id=${productToEdit.value.id}&t=${new Date().getTime()}`, {
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
        showEditModal.value = false
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
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/produits.php?id=${product.id}&t=${new Date().getTime()}`, {
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
    
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/produits.php?t=${new Date().getTime()}`, {
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
      
      // Fermer le modal après 1 seconde
      setTimeout(() => {
        showAddModal.value = false
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
    <div v-if="loadingAuth" class="flex justify-center items-center h-screen w-screen">
      <span class="loader"></span>
    </div>
    <div v-else class="container p-4 mx-auto">
      <h1 class="text-2xl font-bold mb-6">Catalogue produits</h1>
      <div class="flex justify-end mb-4">
        <button 
        class="bg-primary-500 hover:bg-primary-600 text-white py-2 px-4 rounded-md transition-colors"
        @click="showAddModal = true"
      >
          Ajouter un produit
        </button>
      </div>
      
      <!-- Section d'information -->
      <div v-if="!data || data.length === 0" class="p-4 text-center text-gray-500 bg-white dark:bg-gray-800 rounded-lg shadow mb-4">
        Aucune donnée disponible. Vérifiez la connexion à l'API.
      </div>
      
      <!-- Tableau des produits -->
      <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Couleur</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pointure</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prix</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Image</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Catégorie</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sexe</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="product in data" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ product.title }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white max-w-xs truncate">{{ product.description }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ product.couleur }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ product.pointure }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ product.price }} €</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                <img :src="product.url_image" alt="Product image" class="h-14 w-14 object-cover rounded-md">
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ product.categorie }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ product.sexe }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-center space-x-2">
                  <button 
                    class="py-1 px-3 bg-primary-100 text-primary-700 hover:bg-primary-200 rounded-md text-sm font-medium transition-colors"
                    @click="editProduct(product); showEditModal = true"
                  >
                    Éditer
                  </button>
                  <button 
                    class="py-1 px-3 bg-red-100 text-red-700 hover:bg-red-200 rounded-md text-sm font-medium transition-colors"
                    @click="deleteProduct(product)"
                  >
                    Supprimer
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

<!-- Modal d'ajout avec Nuxt UI -->
<UModal v-if="!loadingAuth" v-model="showAddModal" size="2xl">
  <UCard>
    <template #header>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium">Ajouter un produit</h3>
        <UButton
          variant="ghost"
          icon="i-heroicons-x-mark"
          class="text-gray-500"
          @click="showAddModal = false"
        />
      </div>
    </template>
    
    <!-- Message de statut -->
    <UAlert 
      v-if="formStatus.message" 
      :variant="formStatus.isError ? 'destructive' : 'success'"
      class="mb-4"
    >
      {{ formStatus.message }}
    </UAlert>
    
    <form @submit.prevent="submitProduct" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <UFormGroup label="Nom" name="title">
          <UInput v-model="newProduct.title" placeholder="Nom du produit" required />
        </UFormGroup>
        
        <UFormGroup label="Prix" name="price">
          <UInput v-model="newProduct.price" type="number" step="0.01" placeholder="0.00" required />
        </UFormGroup>
      </div>
      
      <UFormGroup label="Description" name="description">
        <UTextarea v-model="newProduct.description" rows="3" placeholder="Description du produit" required />
      </UFormGroup>
      
      <UFormGroup label="URL de l'image" name="url_image">
        <UInput v-model="newProduct.url_image" placeholder="https://exemple.com/image.jpg" required />
      </UFormGroup>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <UFormGroup label="Couleur" name="id_couleur">
          <USelect 
            v-model="newProduct.id_couleur" 
            :options="couleurs.map(c => ({label: c.nom, value: Number(c.id)}))" 
            placeholder="Sélectionner une couleur"
            required
          />
        </UFormGroup>
        
        <UFormGroup label="Pointure" name="id_pointure">
          <USelect 
            v-model="newProduct.id_pointure" 
            :options="pointures.map(p => ({label: p.nom, value: Number(p.id)}))" 
            placeholder="Sélectionner une pointure"
            required
          />
        </UFormGroup>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <UFormGroup label="Catégorie" name="id_categorie">
          <USelect 
            v-model="newProduct.id_categorie" 
            :options="categories.map(c => ({label: c.nom, value: Number(c.id)}))" 
            placeholder="Sélectionner une catégorie"
            required
          />
        </UFormGroup>
        
        <UFormGroup label="Sexe" name="id_sexe">
          <USelect 
            v-model="newProduct.id_sexe" 
            :options="sexes.map(s => ({label: s.nom, value: Number(s.id)}))" 
            placeholder="Sélectionner un sexe"
            required
          />
        </UFormGroup>
      </div>
    </form>
    
    <template #footer>
      <div class="flex justify-end gap-x-4">
        <UButton
          variant="outline"
          @click="showAddModal = false"
        >
          Annuler
        </UButton>
        <UButton
          variant="solid"
          color="primary"
          @click="submitProduct"
          :loading="formStatus.loading"
        >
          Enregistrer
        </UButton>
      </div>
    </template>
  </UCard>
</UModal>

<!-- Modal d'édition avec Nuxt UI -->
<UModal v-if="!loadingAuth" v-model="showEditModal" size="2xl">
  <UCard>
    <template #header>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium">Modifier un produit</h3>
        <UButton
          variant="ghost"
          icon="i-heroicons-x-mark"
          class="text-gray-500"
          @click="showEditModal = false"
        />
      </div>
    </template>
    
    <!-- Message de statut -->
    <UAlert 
      v-if="formStatus.message" 
      :variant="formStatus.isError ? 'destructive' : 'success'"
      class="mb-4"
    >
      {{ formStatus.message }}
    </UAlert>
    
    <form @submit.prevent="updateProduct" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <UFormGroup label="Nom" name="title">
          <UInput v-model="productToEdit.title" placeholder="Nom du produit" required />
        </UFormGroup>
        
        <UFormGroup label="Prix" name="price">
          <UInput v-model="productToEdit.price" type="number" step="0.01" placeholder="0.00" required />
        </UFormGroup>
      </div>
      
      <UFormGroup label="Description" name="description">
        <UTextarea v-model="productToEdit.description" rows="3" placeholder="Description du produit" required />
      </UFormGroup>
      
      <UFormGroup label="URL de l'image" name="url_image">
        <UInput v-model="productToEdit.url_image" placeholder="https://exemple.com/image.jpg" required />
        <div class="mt-2">
          <img :src="productToEdit.url_image" alt="Aperçu" class="h-24 w-auto object-contain rounded-md border" />
        </div>
      </UFormGroup>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <UFormGroup label="Couleur" name="id_couleur">
          <USelect 
            v-model="productToEdit.id_couleur" 
            :options="couleurs.map(c => ({label: c.nom, value: Number(c.id)}))" 
            placeholder="Sélectionner une couleur"
            required
          />
        </UFormGroup>
        
        <UFormGroup label="Pointure" name="id_pointure">
          <USelect 
            v-model="productToEdit.id_pointure" 
            :options="pointures.map(p => ({label: p.nom, value: Number(p.id)}))" 
            placeholder="Sélectionner une pointure"
            required
          />
        </UFormGroup>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <UFormGroup label="Catégorie" name="id_categorie">
          <USelect 
            v-model="productToEdit.id_categorie" 
            :options="categories.map(c => ({label: c.nom, value: Number(c.id)}))" 
            placeholder="Sélectionner une catégorie"
            required
          />
        </UFormGroup>
        
        <UFormGroup label="Sexe" name="id_sexe">
          <USelect 
            v-model="productToEdit.id_sexe" 
            :options="sexes.map(s => ({label: s.nom, value: Number(s.id)}))" 
            placeholder="Sélectionner un sexe"
            required
          />
        </UFormGroup>
      </div>
    </form>
    
    <template #footer>
      <div class="flex justify-end gap-x-4">
        <UButton
          variant="outline"
          @click="showEditModal = false"
        >
          Annuler
        </UButton>
        <UButton
          variant="solid"
          color="primary"
          @click="updateProduct"
          :loading="formStatus.loading"
        >
          Enregistrer
        </UButton>
      </div>
    </template>
  </UCard>
</UModal>
</template>

<style scoped>
.action-buttons {
  display: flex;
  justify-content: center;
}

.action-buttons button {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
}

.table_image {
  max-width: 100px;
  max-height: 60px;
  object-fit: contain;
}
</style>
