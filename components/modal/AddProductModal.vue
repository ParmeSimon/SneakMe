<template>
  <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="productModalLabel">Ajouter un produit</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Message de statut -->
          <div v-if="formStatus.message" :class="['alert', formStatus.isError ? 'alert-danger' : 'alert-success']">
            {{ formStatus.message }}
          </div>
          
          <form @submit.prevent="submitProduct">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="title" class="form-label">Nom</label>
                <input type="text" class="form-control" id="title" v-model="newProduct.title" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="price" class="form-label">Prix (en centimes)</label>
                <input type="number" step="0.01" class="form-control" id="price" v-model="newProduct.price" required>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" id="description" rows="3" v-model="newProduct.description" required></textarea>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Image du produit</label>
              <input type="file" class="form-control" id="image" accept="image/*" @change="handleImageChange">
              <div v-if="imagePreview" class="mt-2">
                <img :src="imagePreview" alt="Aperçu" class="img-thumbnail" style="max-height: 200px;">
              </div>
            </div>
            
            <div class="mb-3">
              <label for="url_image" class="form-label">URL de l'image (optionnel si image téléchargée)</label>
              <input type="text" class="form-control" id="url_image" v-model="newProduct.url_image">
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="id_couleur" class="form-label">Couleur</label>
                <select class="form-select" id="id_couleur" v-model.number="newProduct.id_couleur" required>
                  <option value="" disabled selected>Sélectionner une couleur</option>
                  <option v-for="couleur in couleurs" :key="couleur.id" :value="Number(couleur.id)">{{ couleur.nom }}</option>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="id_pointure" class="form-label">Pointure</label>
                <select class="form-select" id="id_pointure" v-model.number="newProduct.id_pointure" required>
                  <option value="" disabled selected>Sélectionner une pointure</option>
                  <option v-for="pointure in pointures" :key="pointure.id" :value="Number(pointure.id)">{{ pointure.nom }}</option>
                </select>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="id_categorie" class="form-label">Catégorie</label>
                <select class="form-select" id="id_categorie" v-model.number="newProduct.id_categorie" required>
                  <option value="" disabled selected>Sélectionner une catégorie</option>
                  <option v-for="categorie in categories" :key="categorie.id" :value="Number(categorie.id)">{{ categorie.nom }}</option>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="id_sexe" class="form-label">Sexe</label>
                <select class="form-select" id="id_sexe" v-model.number="newProduct.id_sexe" required>
                  <option value="" disabled selected>Sélectionner un sexe</option>
                  <option v-for="sexe in sexes" :key="sexe.id" :value="Number(sexe.id)">{{ sexe.nom }}</option>
                </select>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" id="closeProductModal" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="button" class="btn btn-primary" @click="submitProduct" :disabled="formStatus.loading">
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
  newProduct: {
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
  imagePreview: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['submit', 'image-change'])

const submitProduct = () => {
  emit('submit')
}

const handleImageChange = (event) => {
  emit('image-change', event)
}
</script> 