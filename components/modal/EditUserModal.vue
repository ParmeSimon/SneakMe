<template>
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

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  userToEdit: {
    type: Object,
    required: true
  },
  formStatus: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update'])

const updateUser = () => {
  emit('update')
}
</script> 