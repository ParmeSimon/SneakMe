<script setup>
import {ref, h} from "vue";
import '~/css/admin.css'
import '~/css/keywords.css'
// Import the modal components
import AddKeywordModal from '~/components/modal/AddKeywordModal.vue'
import EditKeywordModal from '~/components/modal/EditKeywordModal.vue'

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

  <!-- Modals -->
  <AddKeywordModal 
    :newKeyword="newKeyword"
    :formStatus="formStatus"
    @submit="submitKeyword"
  />
  
  <EditKeywordModal 
    :keywordToEdit="keywordToEdit"
    :formStatus="formStatus"
    @update="updateKeyword"
  />
</template>
