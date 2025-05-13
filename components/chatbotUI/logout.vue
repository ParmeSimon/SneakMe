<template>
    <div>
        <p>Vous êtes maintenant déconnecté</p>
    </div>
</template>

<script setup>
import { ref } from 'vue';
const emit = defineEmits(['send-message']);
const user = localStorage.getItem('user_sneakme');
if (user) {
    localStorage.removeItem('user_sneakme');
}
</script>