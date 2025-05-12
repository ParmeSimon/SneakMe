<template>
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

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  keywordToEdit: {
    type: Object,
    required: true
  },
  formStatus: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update'])

const updateKeyword = () => {
  emit('update')
}
</script> 