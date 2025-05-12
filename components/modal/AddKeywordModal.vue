<template>
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
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  newKeyword: {
    type: Object,
    required: true
  },
  formStatus: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['submit'])

const submitKeyword = () => {
  emit('submit')
}
</script> 