<script setup>
import {ref, h} from "vue";
import '~/css/admin.css'
definePageMeta({
  layout: 'admin'
})
const columns = [
  { header: 'Pseudo', accessorKey: 'username' },
  { header: 'Prénom', accessorKey: 'prenom' },
  { header: 'Nom', accessorKey: 'nom' },
  { header: 'Email', accessorKey: 'email' },
  { header: 'Date de création', accessorKey: 'created_at' },
  { header: 'role', accessorKey: 'isAdmin', cell: ({row}) => row.getValue("isAdmin") == 1 ? "Administrateur" : "Utilisateur" },
  { 
    header: 'Actions', 
    cell: ({row}) => h('div', { class: 'action-buttons' }, [
      h('button', { 
        class: 'btn btn-sm btn-outline-primary me-2', 
        'data-bs-toggle': 'modal',
        'data-bs-target': '#editUserModal',
        onClick: () => editUser(row.original)
      }, [h('i', { class: 'fa-solid fa-pen' })]),
      h('button', { 
        class: 'btn btn-sm btn-outline-danger', 
        onClick: () => deleteUser(row.original)
      }, [h('i', { class: 'fa-solid fa-trash' })])
    ])
  }
]
const data = ref((await useFetch('http://localhost/SneakMe/api/users.php')).data)

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

// État du formulaire
const formStatus = ref({
  loading: false,
  message: '',
  isError: false
})

// Fonction pour rafraîchir la liste des utilisateurs
const refreshUsers = async () => {
  const response = await useFetch('http://localhost/SneakMe/api/users.php')
  data.value = response.data.value
}

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
    
    const response = await fetch(`http://localhost/SneakMe/api/users.php?id=${userToEdit.value.id}`, {
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

// Fonction pour supprimer un utilisateur
const deleteUser = async (user) => {
  if (!confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.username} ?`)) {
    return
  }
  
  try {
    const response = await fetch(`http://localhost/SneakMe/api/users.php?id=${user.id}`, {
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
    
    const response = await fetch('http://localhost/SneakMe/api/users.php', {
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
        document.getElementById('closeUserModal').click()
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
  <div class="container">
    <h1 class="title">utilisateurs</h1>
    <div class="right">
        <UButton class="ajouter" data-bs-toggle="modal" data-bs-target="#userModal">Ajouter un utilisateur</UButton>
      </div>
    <UTable class="u-table" :data="data" :columns="columns" />
  </div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="userModalLabel">Ajouter un utilisateur</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Message de statut -->
        <div v-if="formStatus.message" :class="['alert', formStatus.isError ? 'alert-danger' : 'alert-success']">
          {{ formStatus.message }}
        </div>
        <form @submit.prevent="submitUser">
          <div class="mb-3">
            <label for="username" class="form-label">Pseudo</label>
            <input type="text" class="form-control" id="username" v-model="newUser.username" required>
          </div>
          <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" v-model="newUser.prenom" required>
          </div>
          <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" v-model="newUser.nom" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" v-model="newUser.email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" v-model="newUser.password" required>
          </div>
          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="isAdmin" v-model="newUser.isAdmin" true-value="1" false-value="0">
            <label class="form-check-label" for="isAdmin">Administrateur</label>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="closeUserModal" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" @click="submitUser" :disabled="formStatus.loading">
          <span v-if="formStatus.loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
          Enregistrer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="editUserModalLabel">Modifier un utilisateur</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Message de statut -->
        <div v-if="formStatus.message" :class="['alert', formStatus.isError ? 'alert-danger' : 'alert-success']">
          {{ formStatus.message }}
        </div>
        
        <form @submit.prevent="updateUser">
          <div class="mb-3">
            <label for="edit-username" class="form-label">Pseudo</label>
            <input type="text" class="form-control" id="edit-username" v-model="userToEdit.username" required>
          </div>
          <div class="mb-3">
            <label for="edit-prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="edit-prenom" v-model="userToEdit.prenom" required>
          </div>
          <div class="mb-3">
            <label for="edit-nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="edit-nom" v-model="userToEdit.nom" required>
          </div>
          <div class="mb-3">
            <label for="edit-email" class="form-label">Email</label>
            <input type="email" class="form-control" id="edit-email" v-model="userToEdit.email" required>
          </div>
          <div class="mb-3">
            <label for="edit-password" class="form-label">Mot de passe (laisser vide pour ne pas modifier)</label>
            <input type="password" class="form-control" id="edit-password" v-model="userToEdit.password">
          </div>
          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="edit-isAdmin" v-model="userToEdit.isAdmin" true-value="1" false-value="0">
            <label class="form-check-label" for="edit-isAdmin">Administrateur</label>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="closeEditModal" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" @click="updateUser" :disabled="formStatus.loading">
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
