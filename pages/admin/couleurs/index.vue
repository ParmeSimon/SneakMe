<script setup>
// Utilisation de l'auto-import de Nuxt au lieu de l'import explicite
// import { ref, onMounted, watch } from 'vue'  
import CategoryCard from '~/components/CategoryCard.vue'
import ColorModal from '~/components/modal/ColorModal.vue'
import '~/assets/css/admin.css'
import '~/assets/css/couleurs.css'

definePageMeta({
  layout: 'admin'
})

const couleurs = ref([])
const modalOpen = ref(false)
const isEditing = ref(false)
const selectedCouleur = ref({ id: null, label: '' })
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Récupérer les couleurs
const fetchCouleurs = async () => {
  loading.value = true
  try {
    const response = await fetch('http://localhost/SneakMe/api/couleurs.php')
    const data = await response.json()
    couleurs.value = data
    loading.value = false
  } catch (error) {
    console.error('Erreur lors de la récupération des couleurs', error)
    errorMessage.value = 'Erreur lors de la récupération des couleurs'
    loading.value = false
  }
}

// Ajouter une couleur
const addCouleur = async (couleur) => {
  loading.value = true
  errorMessage.value = '';
  
  try {
    const response = await fetch('http://localhost/SneakMe/api/couleurs.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ label: couleur.label })
    })
    
    const result = await response.json()
    
    if (result.success) {
      successMessage.value = 'couleur ajoutée avec succès'
      await fetchCouleurs()
      modalOpen.value = false;
    } else {
      errorMessage.value = result.message || 'Erreur lors de l\'ajout de la couleur'
      // Ne pas fermer le modal en cas d'erreur
    }
  } catch (error) {
    console.error('Erreur lors de l\'ajout de la couleur', error)
    errorMessage.value = 'Erreur lors de l\'ajout de la couleur'
  } finally {
    loading.value = false
  }
}

// Modifier une couleur
const updateCouleur = async (couleur) => {
  loading.value = true
  errorMessage.value = '';
  
  try {
    const response = await fetch('http://localhost/SneakMe/api/couleurs.php', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: couleur.id, label: couleur.label })
    })
    
    const result = await response.json()
    
    if (result.success) {
      successMessage.value = 'couleur mise à jour avec succès'
      await fetchCouleurs()
      modalOpen.value = false;
    } else {
      errorMessage.value = result.message || 'Erreur lors de la mise à jour de la couleur'
      // Ne pas fermer le modal en cas d'erreur
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour de la couleur', error)
    errorMessage.value = 'Erreur lors de la mise à jour de la couleur'
  } finally {
    loading.value = false
  }
}

// Supprimer une couleur
const deleteCouleur = async (id) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette couleur ?')) {
    return
  }
  
  loading.value = true
  try {
    const response = await fetch(`http://localhost/SneakMe/api/couleurs.php?id=${id}`, {
      method: 'DELETE'
    })
    
    const result = await response.json()
    
    if (result.success) {
      successMessage.value = 'couleur supprimée avec succès'
      await fetchCouleurs()
    } else {
      errorMessage.value = result.message || 'Erreur lors de la suppression de la couleur'
    }
  } catch (error) {
    console.error('Erreur lors de la suppression de la couleur', error)
    errorMessage.value = 'Erreur lors de la suppression de la couleur'
  } finally {
    loading.value = false
  }
}

// Ouvrir le modal pour modifier
const openEditModal = (couleur) => {
  selectedCouleur.value = { ...couleur }
  isEditing.value = true
  modalOpen.value = true
}

// Ouvrir le modal pour ajouter
const openAddModal = () => {
  selectedCouleur.value = { id: null, label: '' }
  isEditing.value = false
  modalOpen.value = true
}

// Sauvegarder (ajouter ou modifier)
const saveCouleur = (couleur) => {
  if (isEditing.value) {
    updateCouleur(couleur)
  } else {
    addCouleur(couleur)
  }
}

// Fermer les messages d'alerte après 3 secondes
watch(successMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  }
})

watch(errorMessage, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      errorMessage.value = ''
    }, 3000)
  }
})

onMounted(() => {
  fetchCouleurs()
})
</script>

<template>
    <div class="container">
        <h1 class="title">Couleurs</h1>
        <div class="right">
            <UButton class="ajouter" @click="openAddModal">Ajouter une couleur</UButton>
        </div>
        
        <!-- Messages d'alerte -->
        <div v-if="errorMessage" class="alert alert-error">
            <span>{{ errorMessage }}</span>
            <button class="close-btn" @click="errorMessage = ''">×</button>
        </div>
        <div v-if="successMessage" class="alert alert-success">
            <span>{{ successMessage }}</span>
            <button class="close-btn" @click="successMessage = ''">×</button>
        </div>
        
        <div class="couleur-grid">
            <CategoryCard 
                v-for="couleur in couleurs" 
                :key="couleur.id" 
                :category="couleur"
                @edit="openEditModal"
                @delete="deleteCouleur"
            />
        </div>
        <ColorModal
        :is-open="modalOpen"
        :couleur="selectedCouleur"
        :is-editing="isEditing"
        :error-message="errorMessage"
        @close="modalOpen = false"
        @save="saveCouleur"
        />
    </div>
</template>