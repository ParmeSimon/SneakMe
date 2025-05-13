<script setup>
import { ref, onMounted, watch } from 'vue'
import CategoryCard from '~/components/CategoryCard.vue'
import PointureModal from '~/components/modal/PointureModal.vue'
import '~/css/admin.css'
import '~/css/pointures.css'

definePageMeta({
  layout: 'admin'
})

const pointures = ref([])
const modalOpen = ref(false)
const isEditing = ref(false)
const selectedPointure = ref({ id: null, nom: '' })
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Récupérer les pointures
const fetchPointures = async () => {
  loading.value = true
  try {
    const response = await fetch('http://localhost/SneakMe/api/pointures.php')
    const data = await response.json()
    pointures.value = data
    loading.value = false
  } catch (error) {
    console.error('Erreur lors de la récupération des pointures', error)
    errorMessage.value = 'Erreur lors de la récupération des pointures'
    loading.value = false
  }
}

// Ajouter une pointure
const addPointure = async (pointure) => {
  loading.value = true
  errorMessage.value = '';
  
  try {
    const response = await fetch('http://localhost/SneakMe/api/pointures.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ nom: pointure.nom })
    })
    
    const result = await response.json()
    
    if (result.success) {
      successMessage.value = 'pointure ajoutée avec succès'
      await fetchPointures()
      modalOpen.value = false;
    } else {
      errorMessage.value = result.message || 'Erreur lors de l\'ajout de la pointure'
      // Ne pas fermer le modal en cas d'erreur
    }
  } catch (error) {
    console.error('Erreur lors de l\'ajout de la pointure', error)
    errorMessage.value = 'Erreur lors de l\'ajout de la pointure'
  } finally {
    loading.value = false
  }
}

// Modifier une pointure
const updatePointure = async (pointure) => {
  loading.value = true
  errorMessage.value = '';
  
  try {
    const response = await fetch('http://localhost/SneakMe/api/pointures.php', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: pointure.id, nom: pointure.nom })
    })
    
    const result = await response.json()
    
    if (result.success) {
      successMessage.value = 'pointure mise à jour avec succès'
      await fetchPointures()
      modalOpen.value = false;
    } else {
      errorMessage.value = result.message || 'Erreur lors de la mise à jour de la pointure'
      // Ne pas fermer le modal en cas d'erreur
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour de la pointure', error)
    errorMessage.value = 'Erreur lors de la mise à jour de la pointure'
  } finally {
    loading.value = false
  }
}

// Supprimer une pointure
const deletePointure = async (id) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette pointure ?')) {
    return
  }
  
  loading.value = true
  try {
    const response = await fetch(`http://localhost/SneakMe/api/pointures.php?id=${id}`, {
      method: 'DELETE'
    })
    
    const result = await response.json()
    
    if (result.success) {
      successMessage.value = 'pointure supprimée avec succès'
      await fetchPointures()
    } else {
      errorMessage.value = result.message || 'Erreur lors de la suppression de la pointure'
    }
  } catch (error) {
    console.error('Erreur lors de la suppression de la pointure', error)
    errorMessage.value = 'Erreur lors de la suppression de la pointure'
  } finally {
    loading.value = false
  }
}

// Ouvrir le modal pour modifier
const openEditModal = (pointure) => {
  selectedPointure.value = { ...pointure }
  isEditing.value = true
  modalOpen.value = true
}

// Ouvrir le modal pour ajouter
const openAddModal = () => {
  selectedPointure.value = { id: null, nom: '' }
  isEditing.value = false
  modalOpen.value = true
}

// Sauvegarder (ajouter ou modifier)
const savePointure = (pointure) => {
  if (isEditing.value) {
    updatePointure(pointure)
  } else {
    addPointure(pointure)
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
  fetchPointures()
})
</script>

<template>
    <div class="container">
        <h1 class="title">Pointures</h1>
        <div class="right">
            <UButton class="ajouter" @click="openAddModal">Ajouter une pointure</UButton>
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
        
        <div class="pointure-grid">
            <CategoryCard 
                v-for="pointure in pointures" 
                :key="pointure.id" 
                :category="pointure"
                @edit="openEditModal"
                @delete="deletePointure"
            />
        </div>
        <PointureModal
        :is-open="modalOpen"
        :pointure="selectedPointure"
        :is-editing="isEditing"
        :error-message="errorMessage"
        @close="modalOpen = false"
        @save="savePointure"
        />
    </div>
</template>


