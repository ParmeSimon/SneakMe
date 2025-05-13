<script setup>
import { ref, onMounted, watch } from 'vue'
import CategoryCard from '~/components/CategoryCard.vue'
import CategoryModal from '~/components/modal/CategoryModal.vue'
import '~/css/admin.css'
import '~/css/categories.css'

definePageMeta({
  layout: 'admin'
})

const categories = ref([])
const modalOpen = ref(false)
const isEditing = ref(false)
const selectedCategory = ref({ id: null, nom: '' })
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Récupérer les catégories
const fetchCategories = async () => {
  loading.value = true
  try {
    const response = await fetch('http://localhost/SneakMe/api/categories.php')
    const data = await response.json()
    categories.value = data
    loading.value = false
  } catch (error) {
    console.error('Erreur lors de la récupération des catégories', error)
    errorMessage.value = 'Erreur lors de la récupération des catégories'
    loading.value = false
  }
}

// Ajouter une catégorie
const addCategory = async (category) => {
  loading.value = true
  errorMessage.value = '';
  
  try {
    const response = await fetch('http://localhost/SneakMe/api/categories.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ nom: category.nom })
    })
    
    const result = await response.json()
    
    if (result.success) {
      successMessage.value = 'Catégorie ajoutée avec succès'
      await fetchCategories()
      modalOpen.value = false;
    } else {
      errorMessage.value = result.message || 'Erreur lors de l\'ajout de la catégorie'
      // Ne pas fermer le modal en cas d'erreur
    }
  } catch (error) {
    console.error('Erreur lors de l\'ajout de la catégorie', error)
    errorMessage.value = 'Erreur lors de l\'ajout de la catégorie'
  } finally {
    loading.value = false
  }
}

// Modifier une catégorie
const updateCategory = async (category) => {
  loading.value = true
  errorMessage.value = '';
  
  try {
    const response = await fetch('http://localhost/SneakMe/api/categories.php', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: category.id, nom: category.nom })
    })
    
    const result = await response.json()
    
    if (result.success) {
      successMessage.value = 'Catégorie mise à jour avec succès'
      await fetchCategories()
      modalOpen.value = false;
    } else {
      errorMessage.value = result.message || 'Erreur lors de la mise à jour de la catégorie'
      // Ne pas fermer le modal en cas d'erreur
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour de la catégorie', error)
    errorMessage.value = 'Erreur lors de la mise à jour de la catégorie'
  } finally {
    loading.value = false
  }
}

// Supprimer une catégorie
const deleteCategory = async (id) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
    return
  }
  
  loading.value = true
  try {
    const response = await fetch(`http://localhost/SneakMe/api/categories.php?id=${id}`, {
      method: 'DELETE'
    })
    
    const result = await response.json()
    
    if (result.success) {
      successMessage.value = 'Catégorie supprimée avec succès'
      await fetchCategories()
    } else {
      errorMessage.value = result.message || 'Erreur lors de la suppression de la catégorie'
    }
  } catch (error) {
    console.error('Erreur lors de la suppression de la catégorie', error)
    errorMessage.value = 'Erreur lors de la suppression de la catégorie'
  } finally {
    loading.value = false
  }
}

// Ouvrir le modal pour modifier
const openEditModal = (category) => {
  selectedCategory.value = { ...category }
  isEditing.value = true
  modalOpen.value = true
}

// Ouvrir le modal pour ajouter
const openAddModal = () => {
  selectedCategory.value = { id: null, nom: '' }
  isEditing.value = false
  modalOpen.value = true
}

// Sauvegarder (ajouter ou modifier)
const saveCategory = (category) => {
  if (isEditing.value) {
    updateCategory(category)
  } else {
    addCategory(category)
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
  fetchCategories()
})
</script>

<template>
    <div class="container">
        <h1 class="title">Catégories</h1>
        <div class="right">
            <UButton class="ajouter" @click="openAddModal">Ajouter une catégorie</UButton>
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
        
        <div class="category-grid">
            <CategoryCard 
                v-for="category in categories" 
                :key="category.id" 
                :category="category"
                @edit="openEditModal"
                @delete="deleteCategory"
            />
        </div>
        <CategoryModal
        :is-open="modalOpen"
        :category="selectedCategory"
        :is-editing="isEditing"
        :error-message="errorMessage"
        @close="modalOpen = false"
        @save="saveCategory"
        />
    </div>
</template>


