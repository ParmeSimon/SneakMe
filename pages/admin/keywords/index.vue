<script setup>
import {ref, h} from "vue";
import '~/css/admin.css'
definePageMeta({
  layout: 'admin'
})
const columns = [
  { header: 'Mot clé', accessorKey: 'title' },
  { header: 'Description', accessorKey: 'description' },
  { header: 'Réponse', accessorKey: 'result' },
  { 
    header: 'Actions', 
    cell: ({row}) => h('div', { class: 'action-buttons' }, [
      h('button', { 
        class: 'btn btn-sm btn-outline-primary me-2', 
        'data-bs-toggle': 'modal',
        'data-bs-target': '#editKeywordModal',
        onClick: () => editKeyword(row.original)
      }, [h('i', { class: 'fa-solid fa-pen' })]),
      h('button', { 
        class: 'btn btn-sm btn-outline-danger', 
        onClick: () => deleteKeyword(row.original)
      }, [h('i', { class: 'fa-solid fa-trash' })])
    ])
  }
]
const data = ref((await useFetch('http://localhost/SneakMe/api/keywords.php')).data)

// État du formulaire
const formStatus = ref({
  loading: false,
  message: '',
  isError: false
})

// Fonction pour rafraîchir la liste des mots-clés
const refreshKeywords = async () => {
  const response = await useFetch('http://localhost/SneakMe/api/keywords.php')
  data.value = response.data.value
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
    
    const response = await fetch(`http://localhost/SneakMe/api/keywords.php?id=${keywordToEdit.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(keywordToEdit.value)
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
      formStatus.value.message = result.message || 'Mot-clé modifié avec succès!'
      formStatus.value.isError = false
      
      // Rafraîchir les données
      await refreshKeywords()
      
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

// Fonction pour supprimer un mot-clé
const deleteKeyword = async (keyword) => {
  if (!confirm(`Êtes-vous sûr de vouloir supprimer le mot-clé "${keyword.title}" ?`)) {
    return
  }
  
  try {
    const response = await fetch(`http://localhost/SneakMe/api/keywords.php?id=${keyword.id}`, {
      method: 'DELETE'
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
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

// Fonction pour soumettre le formulaire d'ajout
const submitKeyword = async () => {
  try {
    formStatus.value.loading = true
    formStatus.value.message = ''
    formStatus.value.isError = false
    
    const response = await fetch('http://localhost/SneakMe/api/keywords.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newKeyword.value)
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
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
      
      // Fermer le modal après 1 seconde
      setTimeout(() => {
        document.getElementById('closeKeywordModal').click()
        formStatus.value.message = ''
      }, 1000)
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
  <div class="container">
    <h1 class="title">Mot clé</h1>
    <div class="right">
      <UButton class="ajouter" data-bs-toggle="modal" data-bs-target="#keywordModal">Ajouter un mot-clé</UButton>
    </div>
    <UTable class="u-table" :data="data" :columns="columns" />
  </div>

<!-- Modal d'ajout -->
<div class="modal fade" id="keywordModal" tabindex="-1" aria-labelledby="keywordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="keywordModalLabel">Ajouter un mot-clé</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Message de statut -->
        <div v-if="formStatus.message" :class="['alert', formStatus.isError ? 'alert-danger' : 'alert-success']">
          {{ formStatus.message }}
        </div>
        
        <form @submit.prevent="submitKeyword">
          <div class="mb-3">
            <label for="title" class="form-label">Mot-clé</label>
            <input type="text" class="form-control" id="title" v-model="newKeyword.title" required>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" class="form-control" id="description" v-model="newKeyword.description" required>
          </div>
          
          <div class="mb-3">
            <label for="result" class="form-label">Réponse</label>
            <textarea class="form-control" id="result" rows="5" v-model="newKeyword.result" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="closeKeywordModal" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" @click="submitKeyword" :disabled="formStatus.loading">
          <span v-if="formStatus.loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
          Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="editKeywordModal" tabindex="-1" aria-labelledby="editKeywordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="editKeywordModalLabel">Modifier un mot-clé</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Message de statut -->
        <div v-if="formStatus.message" :class="['alert', formStatus.isError ? 'alert-danger' : 'alert-success']">
          {{ formStatus.message }}
        </div>
        
        <form @submit.prevent="updateKeyword">
          <div class="mb-3">
            <label for="edit-title" class="form-label">Mot-clé</label>
            <input type="text" class="form-control" id="edit-title" v-model="keywordToEdit.title" required>
          </div>
          
          <div class="mb-3">
            <label for="edit-description" class="form-label">Description</label>
            <input type="text" class="form-control" id="edit-description" v-model="keywordToEdit.description" required>
          </div>
          
          <div class="mb-3">
            <label for="edit-result" class="form-label">Réponse</label>
            <textarea class="form-control" id="edit-result" rows="5" v-model="keywordToEdit.result" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="closeEditModal" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" @click="updateKeyword" :disabled="formStatus.loading">
          <span v-if="formStatus.loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
          Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>
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
