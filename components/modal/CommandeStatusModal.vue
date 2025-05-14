<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  commande: {
    type: Object,
    default: () => ({ id_commande: null, statut: '' })
  }
});

const emit = defineEmits(['close', 'update']);

const selectedStatus = ref('');

watch(() => props.commande, (newValue) => {
  if (newValue && newValue.statut) {
    if (newValue.statut === 'Terminée') {
      selectedStatus.value = 'Terminée';
    } else if (newValue.statut === 'Annulée') {
      selectedStatus.value = 'Annulée';
    } else {
      selectedStatus.value = 'En cours';
    }
  }
}, { deep: true });

watch(() => props.isOpen, (isOpen) => {
  if (isOpen && props.commande && props.commande.statut) {
    if (props.commande.statut === 'Terminée') {
      selectedStatus.value = 'Terminée';
    } else if (props.commande.statut === 'Annulée') {
      selectedStatus.value = 'Annulée';
    } else {
      selectedStatus.value = 'En cours';
    }
  }
});

const submitForm = () => {
  emit('update', { id: props.commande.id_commande, state: selectedStatus.value });
};
</script>

<template>
  <div v-if="isOpen" class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h3>Modifier le statut de la commande #{{ commande.id_commande }}</h3>
        <button class="modal-close" @click="$emit('close')"><UIcon name="i-heroicons-x-mark" /></button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label for="status">Statut de la commande</label>
            <select id="status" v-model="selectedStatus" class="status-select">
              <option value="En cours">En cours</option>
              <option value="Terminée">Terminée</option>
              <option value="Annulée">Annulée</option>
            </select>
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
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #718096;
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
}

.status-select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  font-size: 1rem;
  margin-bottom: 16px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}

.btn-cancel {
  padding: 8px 16px;
  background-color: #e2e8f0;
  color: #4a5568;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-save {
  padding: 8px 16px;
  background-color: #3182ce;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-save:hover {
  background-color: #2c5282;
}

.btn-cancel:hover {
  background-color: #cbd5e0;
}
</style>
