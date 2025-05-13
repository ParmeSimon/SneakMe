<template>
  <div class="w-full max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-white mb-6">Inscription</h2>
    
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
      {{ errorMessage }}
    </div>
    
    <div v-if="successMessage" class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
      {{ successMessage }}
    </div>
    
    <form @submit.prevent="handleRegister" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label for="prenom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prénom</label>
          <input 
            type="text" 
            id="prenom" 
            v-model="prenom" 
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
            placeholder="Prénom"
          />
        </div>
        
        <div>
          <label for="nom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom</label>
          <input 
            type="text" 
            id="nom" 
            v-model="nom" 
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
            placeholder="Nom"
          />
        </div>
      </div>
      
      <div>
        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom d'utilisateur</label>
        <input 
          type="text" 
          id="username" 
          v-model="username" 
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
          placeholder="Nom d'utilisateur"
        />
      </div>
      
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
        <label for="confirmPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirmer le mot de passe</label>
        <input 
          type="password" 
          id="confirmPassword" 
          v-model="confirmPassword" 
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
          <span v-if="isLoading">Inscription en cours...</span>
          <span v-else>S'inscrire</span>
        </button>
      </div>
    </form>
    
    <div class="mt-4 text-center">
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Déjà inscrit ? 
        <a href="#" @click.prevent="goToLogin" class="font-medium text-primary-600 hover:text-primary-500">
          Se connecter
        </a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// État du formulaire
const username = ref('');
const nom = ref('');
const prenom = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const errorMessage = ref('');
const successMessage = ref('');
const isLoading = ref(false);

// Validation du formulaire
const isFormValid = computed(() => {
  return (
    username.value.trim() !== '' &&
    nom.value.trim() !== '' &&
    prenom.value.trim() !== '' &&
    email.value.trim() !== '' &&
    password.value.trim() !== '' &&
    confirmPassword.value.trim() !== '' &&
    password.value === confirmPassword.value
  );
});

// Fonction pour gérer la soumission du formulaire
async function handleRegister() {
  // Réinitialiser les messages
  errorMessage.value = '';
  successMessage.value = '';
  
  // Vérifier que les mots de passe correspondent
  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Les mots de passe ne correspondent pas';
    return;
  }
  
  isLoading.value = true;
  
  try {
    // Appel à l'API pour créer un nouvel utilisateur
    const response = await fetch('http://localhost/sneakme/api/auth.php?action=register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        username: username.value,
        nom: nom.value,
        prenom: prenom.value,
        email: email.value,
        password: password.value
      })
    });
    
    const data = await response.json();
    
    if (response.ok) {
      // Inscription réussie
      successMessage.value = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
      
      // Réinitialiser le formulaire
      username.value = '';
      nom.value = '';
      prenom.value = '';
      email.value = '';
      password.value = '';
      confirmPassword.value = '';
      
      // Émettre un événement pour informer le parent
      emit('register-success');
      
      // Rediriger vers la connexion après un court délai
      setTimeout(() => {
        emit('send-message', 'login');
      }, 2000);
    } else {
      // Erreur d'inscription
      errorMessage.value = data.message || 'Erreur lors de l\'inscription';
    }
  } catch (error) {
    console.error('Erreur lors de l\'inscription:', error);
    errorMessage.value = 'Une erreur est survenue lors de l\'inscription';
  } finally {
    isLoading.value = false;
  }
}

// Fonction pour rediriger vers la connexion
function goToLogin() {
  emit('send-message', 'login');
}

// Définir les événements émis par le composant
const emit = defineEmits(['send-message', 'register-success']);
</script>