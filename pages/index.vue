<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col">
    <!-- En-tête du chatbot -->
    <header class="bg-white dark:bg-gray-800 shadow-sm py-4 px-6">
      <div class="max-w-7xl mx-auto flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
          <UIcon name="i-heroicons-chat-bubble-left-right" class="mr-2 text-primary" size="lg" />
          SneakMe Assistant
        </h1>
      </div>
    </header>

    <!-- Contenu principal -->
    <main class="flex-1 flex flex-col max-w-3xl w-full mx-auto p-4 pb-3 sm:p-6 sm:pb-3 lg:p-8 lg:pb-3">
      <!-- Zone des messages -->
      <div class="flex-1 overflow-y-auto mb-4 space-y-4" ref="messagesContainer">
        <template v-for="(message, index) in messages" :key="index">
          <!-- Message de l'assistant -->
          <div v-if="message.role === 'assistant'" class="flex items-start">
            <div class="flex-shrink-0 mr-3">
              <UAvatar
                src=""
                alt="Assistant"
                :ui="{ background: 'bg-primary-100 dark:bg-primary-900' }"
                :icon="'i-heroicons-sparkles'"
                size="sm"
              />
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 max-w-[80%] shadow-sm">
              <p class="text-gray-700 dark:text-gray-200">{{ message.content }}</p>
            </div>
          </div>

          <!-- Message de l'utilisateur -->
          <div v-else class="flex items-start justify-end">
            <div class="bg-primary-500 rounded-lg p-4 max-w-[80%] shadow-sm">
              <p class="text-white">{{ message.content }}</p>
            </div>
            <div class="flex-shrink-0 ml-3">
              <UAvatar
                :alt="'Vous'"
                :icon="'i-heroicons-user'"
                size="sm"
              />
            </div>
          </div>
        </template>

        <!-- Indicateur de chargement -->
        <div v-if="isLoading" class="flex items-start">
          <div class="flex-shrink-0 mr-3">
            <UAvatar
              :icon="'i-heroicons-sparkles'"
              size="sm"
              :ui="{ background: 'bg-primary-100 dark:bg-primary-900' }"
            />
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <ULoader size="sm" />
          </div>
        </div>
      </div>

      <!-- Suggestions de messages -->
      <div v-if="suggestions.length > 0 && messages.length < 3" class="mb-4 flex flex-wrap gap-2">
        <UBadge
          v-for="(suggestion, index) in suggestions"
          :key="index"
          color="gray"
          variant="soft"
          class="cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors py-2 px-3"
          @click="sendMessage(suggestion)"
        >
          {{ suggestion }}
        </UBadge>
      </div>

      <!-- Zone de saisie -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-2">
        <form @submit.prevent="handleSubmit" class="flex items-center">
          <UTextarea
            v-model="newMessage"
            placeholder="Écrivez votre message ici..."
            variant="none"
            :ui="{
              base: 'w-full resize-none max-h-32 border-[0px]',
              wrapper: 'flex-1',
              input: 'min-h-[40px] py-2 px-3'
            }"
            :rows="1"
            :autogrow="true"
            @keydown.enter.prevent="handleSubmit"
          />
          <UButton
            color="primary"
            variant="solid"
            icon="i-heroicons-paper-airplane"
            :disabled="!newMessage.trim() || isLoading"
            type="submit"
            class="ml-2"
            aria-label="Envoyer"
          />
        </form>
      </div>
    </main>

    <!-- Pied de page -->
    <footer class="bg-white dark:bg-gray-800 py-3 px-6 shadow-inner">
      <div class="max-w-7xl mx-auto text-center text-sm text-gray-500 dark:text-gray-400">
        <p>SneakMe Assistant &copy; {{ new Date().getFullYear() }} - Propulsé par Nuxt UI</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue';

// Référence au conteneur de messages pour le défilement automatique
const messagesContainer = ref(null);

// État du chatbot
const newMessage = ref('');
const isLoading = ref(false);

// Messages de chat
const messages = reactive([
  {
    role: 'assistant',
    content: 'Bonjour ! Je suis SneakMe Assistant. Comment puis-je vous aider aujourd\'hui ?'
  }
]);

// Suggestions de messages
const suggestions = [
  'Quels sont les derniers modèles de sneakers ?',
  'Comment suivre ma commande ?',
  'Quelles sont vos politiques de retour ?',
  'Je cherche des sneakers pour le running'
];

// Fonction pour envoyer un message
const sendMessage = async (content) => {
  // Si un contenu est fourni, l'utiliser, sinon utiliser le message saisi
  const messageContent = content || newMessage.value.trim();
  
  if (!messageContent) return;
  
  // Ajouter le message de l'utilisateur
  messages.push({
    role: 'user',
    content: messageContent
  });
  
  // Réinitialiser le champ de saisie
  newMessage.value = '';
  
  // Faire défiler vers le bas
  await scrollToBottom();
  
  // Simuler une réponse du chatbot
  await simulateResponse(messageContent);
};

// Fonction pour gérer la soumission du formulaire
const handleSubmit = () => {
  sendMessage();
};

// Simuler une réponse du chatbot
const simulateResponse = async (userMessage) => {
  isLoading.value = true;
  
  // Simuler un délai de réponse
  await new Promise(resolve => setTimeout(resolve, 1500));
  
  // Générer une réponse basée sur le message de l'utilisateur
  let response = '';
  
  if (userMessage.toLowerCase().includes('sneakers') || userMessage.toLowerCase().includes('chaussures')) {
    response = 'Nous avons une large gamme de sneakers pour tous les styles. Vous recherchez plutôt des modèles casual, sport ou collection limitée ?';
  } else if (userMessage.toLowerCase().includes('commande') || userMessage.toLowerCase().includes('suivre')) {
    response = 'Pour suivre votre commande, veuillez vous connecter à votre compte et accéder à la section "Mes commandes". Vous y trouverez toutes les informations de suivi.';
  } else if (userMessage.toLowerCase().includes('retour') || userMessage.toLowerCase().includes('remboursement')) {
    response = 'Notre politique de retour vous permet de renvoyer les articles dans les 30 jours suivant la réception. Les articles doivent être non portés et dans leur emballage d\'origine.';
  } else if (userMessage.toLowerCase().includes('running') || userMessage.toLowerCase().includes('course')) {
    response = 'Pour le running, je vous recommande nos modèles avec amorti renforcé et semelle adaptée. Les collections Nike Air Zoom et Adidas Ultraboost sont particulièrement appréciées pour cette activité.';
  } else {
    response = 'Merci pour votre message. Comment puis-je vous aider davantage concernant nos sneakers ou nos services ?';
  }
  
  // Ajouter la réponse du chatbot
  messages.push({
    role: 'assistant',
    content: response
  });
  
  isLoading.value = false;
  
  // Faire défiler vers le bas
  await scrollToBottom();
};

// Fonction pour faire défiler vers le bas
const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

// Faire défiler vers le bas au chargement initial
onMounted(() => {
  scrollToBottom();
});
</script>

<style scoped>
/* Styles pour la zone de messages avec défilement */
.overflow-y-auto {
  max-height: calc(100vh - 250px);
  scroll-behavior: smooth;
}

/* Animation de transition pour les messages */
.flex-1 > div {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>