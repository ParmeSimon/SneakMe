<script setup>
import {ref, h} from "vue";
import '~/css/admin.css'
import '~/css/users.css'
// Import the modal components
import AddUserModal from '~/components/modal/AddUserModal.vue'
import EditUserModal from '~/components/modal/EditUserModal.vue'

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

// Nouvel utilisateur
const newUser = ref({
  username: '',
  prenom: '',
  nom: '',
  email: '',
  password: '',
  isAdmin: 0
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

  <!-- Modals -->
  <AddUserModal 
    :newUser="newUser" 
    :formStatus="formStatus" 
    @submit="submitUser" 
  />
  
  <EditUserModal 
    :userToEdit="userToEdit" 
    :formStatus="formStatus" 
    @update="updateUser" 
  />
</template>
