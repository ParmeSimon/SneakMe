<script setup>
import {ref, h, onMounted} from "vue";
import '~/assets/css/admin.css'
definePageMeta({
  layout: 'admin'
})

import { onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'

let loadingAuth = ref(true)

const router = useRouter()

// let loading = ref(true)

// Fonction asynchrone pour vérifier l'authentification
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


const { data: apiData } = await useFetch('http://localhost/sneakme/api/actions.php')
const data = ref(apiData.value)

// Log pour débogage
console.log('Données reçues de l\'API:', apiData.value)

// État du formulaire
const formStatus = ref({
  loading: false,
  message: '',
  isError: false
})

// État des modales
const showAddModal = ref(false)
const showEditModal = ref(false)

// Fonction pour rafraîchir la liste des mots-clés
const refreshKeywords = async () => {
  try {
    // Utiliser fetch sans en-têtes spéciaux pour éviter les problèmes CORS
    const response = await fetch('http://localhost/sneakme/api/actions.php', {
      method: 'GET'
    })
    
    if (response.ok) {
      const freshData = await response.json()
      console.log('Données rafraîchies:', freshData)
      // Assurons-nous que data est un tableau JavaScript standard
      if (Array.isArray(freshData)) {
        // Convertir en tableau JavaScript standard
        data.value = [...freshData]
        console.log('Données après conversion:', data.value)
      } else {
        console.error('Les données reçues ne sont pas un tableau:', freshData)
        data.value = []
      }
    } else {
      console.error('Erreur lors du rafraîchissement des données:', response.statusText)
    }
  } catch (error) {
    console.error('Erreur lors du rafraîchissement des données:', error)
  }
}

// Mot-clé à modifier
const keywordToEdit = ref({
  id: null,
  title: '',
  description: '',
  result: ''
})

// Fonction pour préparer l'édition d'un mot-clé
const editKeyword = (keyword) => {
  console.log("Mot-clé original:", keyword);
  
  keywordToEdit.value = { 
    id: keyword.id,
    title: keyword.title,
    description: keyword.description,
    result: keyword.result
  }
  
  console.log("KeywordToEdit après préparation:", keywordToEdit.value);
}

// Fonction pour mettre à jour un mot-clé
const updateKeyword = async () => {
  try {
    formStatus.value.loading = true
    formStatus.value.message = ''
    formStatus.value.isError = false
    
    console.log("Données à envoyer:", keywordToEdit.value)
    
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/actions.php?id=${keywordToEdit.value.id}&t=${new Date().getTime()}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(keywordToEdit.value)
    })
    
    const result = await response.json()
    console.log("Données reçues:", result)
    
    if (response.ok) {
      formStatus.value.message = result.message || 'Mot-clé modifié avec succès!'
      formStatus.value.isError = false
      
      // Rafraîchir les données
      await refreshKeywords()
      
      // Fermer le modal immédiatement
      showEditModal.value = false
      
      // Effacer le message après un délai
      setTimeout(() => {
        formStatus.value.message = ''
      }, 2000)
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

// Fonction pour supprimer un mot-clé
const deleteKeyword = async (keyword) => {
  if (!confirm(`Êtes-vous sûr de vouloir supprimer le mot-clé "${keyword.title}" ?`)) {
    return
  }
  
  try {
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/actions.php?id=${keyword.id}&t=${new Date().getTime()}`, {
      method: 'DELETE'
    })
    
    const result = await response.json()
    
    if (response.ok) {
      alert(result.message || 'Mot-clé supprimé avec succès!')
      
      // Rafraîchir les données
      await refreshKeywords()
    } else {
      alert(result.message || 'Erreur lors de la suppression')
    }
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    alert('Erreur de connexion au serveur')
  }
}

// Nouveau mot-clé
const newKeyword = ref({
  title: '',
  description: '',
  result: ''
})

// Rafraîchir les données au chargement de la page
onMounted(() => {
  console.log('Composant monté, rafraîchissement des données...')
  refreshKeywords()
})

// Fonction pour soumettre le formulaire d'ajout
const submitKeyword = async () => {
  try {
    formStatus.value.loading = true
    formStatus.value.message = ''
    formStatus.value.isError = false
    
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/actions.php?t=${new Date().getTime()}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newKeyword.value)
    })
    
    const result = await response.json()
    
    if (response.ok) {
      // Afficher le message de succès
      formStatus.value.message = result.message || 'Mot-clé ajouté avec succès!'
      formStatus.value.isError = false
      
      // Rafraîchir les données
      await refreshKeywords()
      
      // Réinitialiser le formulaire
      newKeyword.value = { 
        title: '',
        description: '',
        result: ''
      }
      
      // Fermer le modal immédiatement
      showAddModal.value = false
      
      // Effacer le message après un délai
      setTimeout(() => {
        formStatus.value.message = ''
      }, 2000)
    } else {
      // Afficher l'erreur
      formStatus.value.message = result.message || 'Erreur lors de l\'ajout du mot-clé'
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
  <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mots-clés</h1>
      <UButton color="primary" @click="showAddModal = true" icon="i-heroicons-plus">
        Ajouter un mot-clé
      </UButton>
    </div>
    
    <!-- Section d'information -->
    <div v-if="!data || data.length === 0" class="p-4 text-center text-gray-500 bg-white dark:bg-gray-800 rounded-lg shadow mb-4">
      Aucune donnée disponible. Vérifiez la connexion à l'API.
    </div>
    
    <!-- Tableau des mots-clés -->
    <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mot clé</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Réponse</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="item in data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
              {{ item.title }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
              {{ item.description }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
              {{ item.result }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
              <div class="flex justify-center space-x-2">
                <button 
                  @click="editKeyword(item); showEditModal = true"
                  class="py-1 px-3 bg-primary-100 text-primary-700 hover:bg-primary-200 rounded-md text-sm font-medium transition-colors"
                >
                  Éditer
                </button>
                <button 
                  @click="deleteKeyword(item)"
                  class="py-1 px-3 bg-red-100 text-red-700 hover:bg-red-200 rounded-md text-sm font-medium transition-colors"
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
  <UModal v-if="!loadingAuth" v-model="showAddModal">
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium">Ajouter un mot clé</h3>
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
        :variant="formStatus.isError ? 'solid' : 'soft'"
        :color="formStatus.isError ? 'red' : 'green'"
        class="mb-4"
      >
        {{ formStatus.message }}
      </UAlert>
      
      <form @submit.prevent="submitKeyword" class="space-y-4">
        <UFormGroup label="Mot clé" name="title">
          <UInput v-model="newKeyword.title" placeholder="Entrez un mot clé" required />
        </UFormGroup>
        
        <UFormGroup label="Description" name="description">
          <UInput v-model="newKeyword.description" placeholder="Entrez une description" required />
        </UFormGroup>
        
        <UFormGroup label="Résultat" name="result">
          <UTextarea v-model="newKeyword.result" placeholder="Résultat affiché quand l'utilisateur saisit ce mot clé" required />
        </UFormGroup>
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
            @click="submitKeyword"
            :loading="formStatus.loading"
          >
            Enregistrer
          </UButton>
        </div>
      </template>
    </UCard>
  </UModal>

  <!-- Modal d'édition avec Nuxt UI -->
  <UModal v-if="!loadingAuth" v-model="showEditModal">
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium">Modifier un mot clé</h3>
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
        :variant="formStatus.isError ? 'solid' : 'soft'"
        :color="formStatus.isError ? 'red' : 'green'"
        class="mb-4"
      >
        {{ formStatus.message }}
      </UAlert>
      
      <form @submit.prevent="updateKeyword" class="space-y-4">
        <UFormGroup label="Mot clé" name="title">
          <UInput v-model="keywordToEdit.title" placeholder="Entrez un mot clé" required />
        </UFormGroup>
        
        <UFormGroup label="Description" name="description">
          <UInput v-model="keywordToEdit.description" placeholder="Entrez une description" required />
        </UFormGroup>
        
        <UFormGroup label="Résultat" name="result">
          <UTextarea v-model="keywordToEdit.result" placeholder="Résultat affiché quand l'utilisateur saisit ce mot clé" required />
        </UFormGroup>
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
            @click="updateKeyword"
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
/* Tous les styles sont maintenant gérés par Tailwind CSS */
</style>
