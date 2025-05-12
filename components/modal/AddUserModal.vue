<template>
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
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  newUser: {
    type: Object,
    required: true
  },
  formStatus: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['submit'])

const submitUser = () => {
  emit('submit')
}
</script> 