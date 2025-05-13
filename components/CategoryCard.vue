<script setup>
const props = defineProps({
  category: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['edit', 'delete']);

const handleEdit = () => {
  emit('edit', props.category);
};

const handleDelete = () => {
  emit('delete', props.category.id);
};
</script>

<template>
  <div class="category-card">
    <div class="category-content">
      <h3 class="category-name">{{ category.nom }}</h3>
    </div>
    <div class="category-actions">
      <button class="btn-edit" @click="handleEdit">
        <i class="fas fa-edit"></i>
      </button>
      <button class="btn-delete" @click="handleDelete">
        <i class="fas fa-trash"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
.category-card {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 16px;
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.category-content {
  flex: 1;
}

.category-name {
  margin: 0;
  font-size: 1.2rem;
  color: #333;
}

.category-actions {
  display: flex;
  gap: 8px;
}

.btn-edit, .btn-delete {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.btn-edit {
  color: #4f46e5;
}

.btn-delete {
  color: #ef4444;
}

.btn-edit:hover {
  background-color: rgba(79, 70, 229, 0.1);
}

.btn-delete:hover {
  background-color: rgba(239, 68, 68, 0.1);
}
</style> 