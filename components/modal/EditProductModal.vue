<template>
  <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="editProductModalLabel">Modifier un produit</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Message de statut -->
          <div v-if="formStatus.message" :class="['alert', formStatus.isError ? 'alert-danger' : 'alert-success']">
            {{ formStatus.message }}
          </div>
          
          <form @submit.prevent="updateProduct">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="edit-title" class="form-label">Nom</label>
                <input type="text" class="form-control" id="edit-title" v-model="productToEdit.title" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="edit-price" class="form-label">Prix (en centimes)</label>
                <input type="number" step="0.01" class="form-control" id="edit-price" v-model="productToEdit.price" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="edit-description" class="form-label">Description</label>
              <textarea class="form-control" id="edit-description" rows="3" v-model="productToEdit.description" required></textarea>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Image du produit</label>
              <input type="file" class="form-control" id="edit-image" accept="image/*" @change="handleEditImageChange">
              <div class="mt-2">
                <img :src="editImagePreview || productToEdit.url_image" alt="Aperçu" class="img-thumbnail" style="max-height: 200px;">
              </div>
            </div>
            
            <div class="mb-3">
              <label for="edit-url_image" class="form-label">URL de l'image (optionnel si image téléchargée)</label>
              <input type="text" class="form-control" id="edit-url_image" v-model="productToEdit.url_image">
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="edit-id_couleur" class="form-label">Couleur</label>
                <select class="form-select" id="edit-id_couleur" v-model.number="productToEdit.id_couleur" required>
                  <option v-for="couleur in couleurs" :key="couleur.id" :value="Number(couleur.id)">{{ couleur.nom }}</option>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="edit-id_pointure" class="form-label">Pointure</label>
                <select class="form-select" id="edit-id_pointure" v-model.number="productToEdit.id_pointure" required>
                  <option v-for="pointure in pointures" :key="pointure.id" :value="Number(pointure.id)">{{ pointure.nom }}</option>
                </select>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="edit-id_categorie" class="form-label">Catégorie</label>
                <select class="form-select" id="edit-id_categorie" v-model.number="productToEdit.id_categorie" required>
                  <option v-for="categorie in categories" :key="categorie.id" :value="Number(categorie.id)">{{ categorie.nom }}</option>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="edit-id_sexe" class="form-label">Sexe</label>
                <select class="form-select" id="edit-id_sexe" v-model.number="productToEdit.id_sexe" required>
                  <option v-for="sexe in sexes" :key="sexe.id" :value="Number(sexe.id)">{{ sexe.nom }}</option>
                </select>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" id="closeEditModal" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="button" class="btn btn-primary" @click="updateProduct" :disabled="formStatus.loading">
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
  productToEdit: {
    type: Object,
    required: true
  },
  formStatus: {
    type: Object,
    required: true
  },
  couleurs: {
    type: Array,
    required: true
  },
  pointures: {
    type: Array,
    required: true
  },
  categories: {
    type: Array,
    required: true
  },
  sexes: {
    type: Array,
    required: true
  },
  editImagePreview: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update', 'edit-image-change'])

const updateProduct = () => {
  emit('update')
}

const handleEditImageChange = (event) => {
  emit('edit-image-change', event)
}
</script> 