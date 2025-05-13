<template>
  <div class="w-full max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-white mb-6">Connexion</h2>
    
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
      {{ errorMessage }}
    </div>
    
    <div v-if="successMessage" class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
      {{ successMessage }}
    </div>
    
    <form @submit.prevent="handleLogin" class="space-y-4">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
        <input 
          type="email" 
          id="email" 
          v-model="email" 
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
          placeholder="votre@email.com"
        />
      </div>
      
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mot de passe</label>
        <input 
          type="password" 
          id="password" 
          v-model="password" 
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
          placeholder="••••••••"
        />
      </div>
      
      <div>
        <button 
          type="submit" 
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          :disabled="isLoading"
        >
          <span v-if="isLoading">Connexion en cours...</span>
          <span v-else>Se connecter</span>
        </button>
      </div>
    </form>
    
    <div class="mt-4 text-center">
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Pas encore de compte ? 
        <a href="#" @click.prevent="goToRegister" class="font-medium text-primary-600 hover:text-primary-500">
          S'inscrire
        </a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// État du formulaire
const email = ref('');
const password = ref('');
const errorMessage = ref('');
const successMessage = ref('');
const isLoading = ref(false);

// Fonction pour gérer la soumission du formulaire
async function handleLogin() {
  // Réinitialiser les messages
  errorMessage.value = '';
  successMessage.value = '';
  isLoading.value = true;
  
  try {
    // Appel à l'API pour authentifier l'utilisateur
    const response = await fetch('http://localhost/sneakme/api/auth.php?action=login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value
      })
    });
    
    const data = await response.json();
    
    if (response.ok) {
      // Authentification réussie
      successMessage.value = 'Connexion réussie !';
      
      // Stocker les informations de l'utilisateur en session
      localStorage.setItem('user_sneakme', JSON.stringify(data.user));
      
      // Émettre un événement pour informer le parent
      emit('login-success', data.user);
      
      // Réinitialiser le formulaire
      email.value = '';
      password.value = '';
      
    } else {
      // Erreur d'authentification
      errorMessage.value = data.message || 'Erreur lors de la connexion';
    }
  } catch (error) {
    console.error('Erreur lors de la connexion:', error);
    errorMessage.value = 'Une erreur est survenue lors de la connexion';
  } finally {
    isLoading.value = false;
  }
}

// Fonction pour rediriger vers l'inscription
function goToRegister() {
  emit('send-message', 'register');
}

// Définir les événements émis par le composant
const emit = defineEmits(['send-message', 'login-success']);
</script>