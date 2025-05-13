<script setup>
import {ref, h} from "vue";
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

// Plus besoin de la définition des colonnes UTable
// Nous utilisons un tableau HTML standard maintenant
const data = ref([])

// User à modifier
const userToEdit = ref({
  id: null,
  username: '',
  prenom: '',
  nom: '',
  email: '',
  password: '',
  isAdmin: 0
})

// État des modales
const showAddModal = ref(false)
const showEditModal = ref(false)

// État du formulaire
const formStatus = ref({
  loading: false,
  message: '',
  isError: false
})

// Fonction pour rafraîchir la liste des utilisateurs
const refreshUsers = async () => {
  try {
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/users.php?t=${new Date().getTime()}`)
    
    if (response.ok) {
      data.value = await response.json()
      console.log('Données utilisateurs rafraîchies:', data.value)
    } else {
      console.error('Erreur lors du rafraîchissement des utilisateurs:', response.statusText)
    }
  } catch (error) {
    console.error('Erreur lors du rafraîchissement des utilisateurs:', error)
  }
}

// Charger les utilisateurs au montage du composant
onMounted(() => {
  refreshUsers()
})

// Fonction pour préparer l'édition d'un utilisateur
const editUser = (user) => {
  userToEdit.value = { 
    id: user.id,
    username: user.username,
    prenom: user.prenom,
    nom: user.nom,
    email: user.email,
    password: '',
    isAdmin: user.isAdmin
  }
}

// Fonction pour mettre à jour un utilisateur
const updateUser = async () => {
  try {
    formStatus.value.loading = true
    formStatus.value.message = ''
    formStatus.value.isError = false
    
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/users.php?id=${userToEdit.value.id}&t=${new Date().getTime()}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(userToEdit.value)
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
      formStatus.value.message = result.message || 'Utilisateur modifié avec succès!'
      formStatus.value.isError = false
      
      // Rafraîchir les données
      await refreshUsers()
      
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

// Fonction pour supprimer un utilisateur
const deleteUser = async (user) => {
  if (!confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.username} ?`)) {
    return
  }
  
  try {
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/users.php?id=${user.id}&t=${new Date().getTime()}`, {
      method: 'DELETE'
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
      alert(result.message || 'Utilisateur supprimé avec succès!')
      
      // Rafraîchir les données
      await refreshUsers()
    } else {
      alert(result.message || 'Erreur lors de la suppression')
    }
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    alert('Erreur de connexion au serveur')
  }
}

// Nouvel utilisateur
const newUser = ref({
  username: '',
  prenom: '',
  nom: '',
  email: '',
  password: '',
  isAdmin: 0
})

// Fonction pour soumettre le formulaire
const submitUser = async () => {
  try {
    formStatus.value.loading = true
    formStatus.value.message = ''
    formStatus.value.isError = false
    
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/users.php?t=${new Date().getTime()}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newUser.value)
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
      // Afficher le message de succès
      formStatus.value.message = result.message || 'Utilisateur ajouté avec succès!'
      formStatus.value.isError = false
      
      // Rafraîchir les données
      await refreshUsers()
      
      // Réinitialiser le formulaire
      newUser.value = { username: '', prenom: '', nom: '', email: '', password: '', isAdmin: 0 }
      
      // Fermer le modal après 1 seconde
      setTimeout(() => {
        showAddModal.value = false
        formStatus.value.message = ''
      }, 1000)
    } else {
      // Afficher l'erreur
      formStatus.value.message = result.message || 'Erreur lors de l\'ajout de l\'utilisateur'
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
    <h1 class="text-2xl font-bold mb-6">Utilisateurs</h1>
    <div class="flex justify-end mb-4">
      <button 
        class="bg-primary-500 hover:bg-primary-600 text-white py-2 px-4 rounded-md transition-colors"
        @click="showAddModal = true"
      >
        Ajouter un utilisateur
      </button>
    </div>
    
    <!-- Section d'information -->
    <div v-if="!data || data.length === 0" class="p-4 text-center text-gray-500 bg-white dark:bg-gray-800 rounded-lg shadow mb-4">
      Aucune donnée disponible. Vérifiez la connexion à l'API.
    </div>
    
    <!-- Tableau des utilisateurs -->
    <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pseudo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prénom</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rôle</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="user in data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ user.username }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ user.prenom }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ user.nom }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ user.email }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ user.created_at }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
              <span 
                :class="user.isAdmin == 1 ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'" 
                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
              >
                {{ user.isAdmin == 1 ? 'Administrateur' : 'Utilisateur' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex justify-center space-x-2">
                <button 
                  class="py-1 px-3 bg-primary-100 text-primary-700 hover:bg-primary-200 rounded-md text-sm font-medium transition-colors"
                  @click="editUser(user); showEditModal = true"
                >
                  Éditer
                </button>
                <button 
                  class="py-1 px-3 bg-red-100 text-red-700 hover:bg-red-200 rounded-md text-sm font-medium transition-colors"
                  @click="deleteUser(user)"
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

<!-- Modal d'ajout d'utilisateur avec Nuxt UI -->
<UModal v-if="!loadingAuth" v-model="showAddModal">
  <UCard>
    <template #header>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium">Ajouter un utilisateur</h3>
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
    
    <form @submit.prevent="submitUser" class="space-y-4">
      <UFormGroup label="Pseudo" name="username">
        <UInput v-model="newUser.username" placeholder="Entrez un pseudonyme" required />
      </UFormGroup>
      
      <UFormGroup label="Prénom" name="prenom">
        <UInput v-model="newUser.prenom" placeholder="Entrez le prénom" required />
      </UFormGroup>
      
      <UFormGroup label="Nom" name="nom">
        <UInput v-model="newUser.nom" placeholder="Entrez le nom" required />
      </UFormGroup>
      
      <UFormGroup label="Email" name="email">
        <UInput v-model="newUser.email" type="email" placeholder="email@exemple.com" required />
      </UFormGroup>
      
      <UFormGroup label="Mot de passe" name="password">
        <UInput v-model="newUser.password" type="password" placeholder="Mot de passe" required />
      </UFormGroup>
      
      <UFormGroup>
        <UCheckbox v-model="newUser.isAdmin" name="isAdmin" true-value="1" false-value="0" label="Administrateur" />
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
          @click="submitUser"
          :loading="formStatus.loading"
        >
          Enregistrer
        </UButton>
      </div>
    </template>
  </UCard>
</UModal>

<!-- Modal d'édition d'utilisateur avec Nuxt UI -->
<UModal v-if="!loadingAuth" v-model="showEditModal">
  <UCard>
    <template #header>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium">Modifier un utilisateur</h3>
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
    
    <form @submit.prevent="updateUser" class="space-y-4">
      <UFormGroup label="Pseudo" name="username">
        <UInput v-model="userToEdit.username" placeholder="Entrez un pseudonyme" required />
      </UFormGroup>
      
      <UFormGroup label="Prénom" name="prenom">
        <UInput v-model="userToEdit.prenom" placeholder="Entrez le prénom" required />
      </UFormGroup>
      
      <UFormGroup label="Nom" name="nom">
        <UInput v-model="userToEdit.nom" placeholder="Entrez le nom" required />
      </UFormGroup>
      
      <UFormGroup label="Email" name="email">
        <UInput v-model="userToEdit.email" type="email" placeholder="email@exemple.com" required />
      </UFormGroup>
      
      <UFormGroup label="Mot de passe (laisser vide pour ne pas modifier)" name="password">
        <UInput v-model="userToEdit.password" type="password" placeholder="Nouveau mot de passe" />
      </UFormGroup>
      
      <UFormGroup>
        <UCheckbox v-model="userToEdit.isAdmin" name="isAdmin" true-value="1" false-value="0" label="Administrateur" />
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
          @click="updateUser"
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
</style>
