<script setup>
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  category: {
    type: Object,
    default: () => ({ id: null, label: '' })
  },
  isEditing: {
    type: Boolean,
    default: false
  },
  errorMessage: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['close', 'save']);

const formData = ref({
  id: props.category.id,
  label: props.category.label || ''
});

watch(() => props.category, (newValue) => {
  formData.value = {
    id: newValue.id,
    label: newValue.label || ''
  };
}, { deep: true });

const submitForm = () => {
  if (!formData.value.label.trim()) {
    return;
  }
  
  emit('save', { ...formData.value });
  // Le modal reste ouvert si une erreur survient
};
</script>

<template>
  <div v-if="isOpen" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h3>{{ isEditing ? 'Modifier' : 'Ajouter' }} une catégorie</h3>
        <button class="modal-close" @click="$emit('close')">&times;</button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label for="category-name">Nom de la catégorie</label>
            <input
              id="category-name"
              v-model="formData.label"
              type="text"
              placeholder="Entrez le nom de la catégorie"
              required
            />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="$emit('close')">Annuler</button>
            <button type="submit" class="btn-save">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 100;
}

.modal-container {
  background-color: white;
  border-radius: 8px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #111827;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #6b7280;
}

.modal-body {
  padding: 16px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
}

.form-group input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 1rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
}

.btn-cancel, .btn-save {
  padding: 8px 16px;
  border-radius: 4px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-cancel {
  background-color: white;
  border: 1px solid #d1d5db;
  color: #374151;
}

.btn-save {
  background-color: #4f46e5;
  border: 1px solid #4f46e5;
  color: white;
}

.btn-cancel:hover {
  background-color: #f3f4f6;
}

.btn-save:hover {
  background-color: #4338ca;
}

.error-message {
  background-color: #fee2e2;
  border-left: 4px solid #ef4444;
  color: #b91c1c;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 4px;
}
</style> 