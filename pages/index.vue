<template>
  <div class="h-screen bg-gray-50 dark:bg-gray-900 flex flex-col">
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
    <main class="flex-1 flex flex-col justify-between max-w-6xl w-full mx-auto p-4 pb-3 sm:p-6 sm:pb-3 lg:p-8 lg:pb-3">
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
              <!-- Rendu dynamique du contenu du message -->
              <div v-if="message.isComponent" class="text-gray-700 dark:text-gray-200">
                <p class="mb-2">{{ message.content }}</p>
                <component :is="message.componentName" />
              </div>
              <p v-else class="text-gray-700 dark:text-gray-200">{{ message.content }}</p>
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
        </div>
      </div>

      <div class="mb-2">
        <!-- Suggestions de messages -->
        <div v-if="suggestions.length > 0" class="mb-4 flex flex-wrap gap-2">
          <UBadge
            v-for="(suggestion, index) in suggestions"
            :key="index"
            color="gray"
            variant="soft"
            class="cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors py-2 px-3 suggestion-badge"
            :class="{ 'disabled-suggestion': isLoading }"
            @click="!isLoading && sendMessage(suggestion)"
            @mousedown="!isLoading && animateSuggestion($event)"
          >
            {{ suggestion }}
          </UBadge>
        </div>

        <!-- Zone de saisie -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-2 relative">
          <!-- Overlay de cooldown -->
          <div v-if="isLoading" class="absolute inset-0 bg-gray-100 dark:bg-gray-700 bg-opacity-30 dark:bg-opacity-30 z-10 flex items-center justify-center rounded-lg">
            <span class="text-sm text-gray-600 dark:text-gray-300 font-medium">Assistant en train de répondre...</span>
          </div>
          
          <form @submit.prevent="handleSubmit" class="flex items-center">
            <UTextarea
              v-model="newMessage"
              placeholder="Écrivez votre message ici..."
              variant="none"
              :disabled="isLoading"
              :ui="{
                base: 'w-full resize-none max-h-32 border-[0px]',
                wrapper: 'flex-1',
                input: 'min-h-[40px] py-2 px-3'
              }"
              :rows="1"
              :autogrow="true"
              @keydown.enter.prevent="!isLoading && handleSubmit()"
            />
            <UButton
              color="primary"
              variant="solid"
              icon="i-heroicons-paper-airplane"
              :disabled="!newMessage.trim() || isLoading"
              type="submit"
              class="ml-2 relative"
              aria-label="Envoyer"
            >
              <UIcon v-if="isLoading" name="i-heroicons-clock" class="absolute inset-0 flex items-center justify-center animate-pulse" />
            </UButton>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick, markRaw } from 'vue';
import Commande from '~/components/chatbotUI/commande.vue';
import Boutique from '~/components/chatbotUI/boutique.vue';
import Panier from '~/components/chatbotUI/panier.vue';
import Help from '~/components/chatbotUI/help.vue';



// Référence au conteneur de messages pour le défilement automatique
const messagesContainer = ref(null);

// État du chatbot
const newMessage = ref('');
const isLoading = ref(false);

// Messages de chat
const messages = reactive([
  {
    role: 'assistant',
    isComponent: false,
    content: 'Bonjour ! Je suis SneakMe Assistant. Comment puis-je vous aider aujourd\'hui ?'
  }
]);

// Suggestions de messages initiales
const initialSuggestions = [
  'Quels sont les derniers modèles de sneakers ?',
  'Comment suivre ma commande ?',
  'Quelles sont vos politiques de retour ?',
  'Je cherche des sneakers pour le running'
];

// Suggestions actuelles (réactives)
const suggestions = ref([...initialSuggestions]);

// Suggestions contextuelles basées sur la conversation
const sneakersSuggestions = [
  'Avez-vous des modèles pour enfants ?',
  'Je préfère les sneakers basses',
  'Quelles sont vos marques disponibles ?'
];

const commandeSuggestions = [
  'Ma commande est en retard',
  'Comment modifier ma commande ?',
  'Puis-je changer l\'adresse de livraison ?'
];

const retourSuggestions = [
  'Comment faire un retour ?',
  'Combien coûte un retour ?',
  'Puis-je échanger au lieu de retourner ?'
];

const runningSuggestions = [
  'Quelles tailles sont disponibles ?',
  'Avez-vous des modèles imperméables ?',
  'Je recherche des modèles avec bon amorti'
];

// Fonction pour envoyer un message
const sendMessage = async (content) => {
  // Si un contenu est fourni, l'utiliser, sinon utiliser le message saisi
  const messageContent = content || newMessage.value.trim();
  
  // Vérifier si un message est en cours de traitement ou si le message est vide
  if (isLoading.value || !messageContent) return;
  
  // Activer l'état de chargement
  isLoading.value = true;
  
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
  // isLoading est déjà activé dans sendMessage
  
  // Générer une réponse basée sur le message de l'utilisateur
  let response = '';

  response = generateResponse(userMessage.toLowerCase())
  
  // Ajouter la réponse du chatbot
  // Si response est un objet avec isComponent, on l'utilise directement
  if (typeof response === 'object' && response.isComponent !== undefined) {
    messages.push({
      role: 'assistant',
      ...response // Spread l'objet response qui contient isComponent, componentName et content
    });
  } else {
    // Fallback pour la compatibilité avec d'anciennes réponses textuelles
    messages.push({
      role: 'assistant',
      isComponent: false,
      content: response
    });
  }
  
  isLoading.value = false;
  
  // Faire défiler vers le bas
  await scrollToBottom();
};

function generateResponse(id) {
  const contientAjouterAuPanier = (chaine) => {
    const normalisee = chaine.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    return normalisee.includes("ajouter au panier");
  }
  
  // Vérifier d'abord si le message contient "ajouter au panier"
  if (contientAjouterAuPanier(id)) {
    return {
      isComponent: false,
      content: 'Produit ajouté au panier. Merci pour votre message. Comment puis-je vous aider davantage concernant nos sneakers ou nos services ?'
    };
  }
  
  // Vérifier si nous devons renvoyer un composant ou du texte
  switch(id) {
    case 'commande':
      // Retourner un objet qui indique qu'il s'agit d'un composant
      return {
        isComponent: true,
        componentName: markRaw(Commande),
        content: '' // Contenu vide car nous utilisons le composant
      };
    case 'boutique':
      return {
        isComponent: true,
        componentName: markRaw(Boutique),
        content: 'Pour suivre votre commande, veuillez vous connecter à votre compte et accéder à la section "Mes commandes". Vous y trouverez toutes les informations de suivi.'
      };
    case 'panier':
      return {
        isComponent: true,
        componentName: markRaw(Panier),
        content: 'Pour suivre votre commande, veuillez vous connecter à votre compte et accéder à la section "Mes commandes". Vous y trouverez toutes les informations de suivi.'
      };
    case '/help':
      return {
        isComponent: true,
        componentName: markRaw(Help),
        content: ''
      };
    default:
      return {
        isComponent: false,
        content: 'Merci pour votre message. Comment puis-je vous aider davantage concernant nos sneakers ou nos services ?'
      };
  } 
}

// Fonction pour faire défiler vers le bas
const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

// Animation pour les suggestions lors du clic
const animateSuggestion = (event) => {
  const badge = event.currentTarget;
  badge.classList.add('clicked');
  
  // Retirer la classe après l'animation
  setTimeout(() => {
    badge.classList.remove('clicked');
  }, 200);
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

/* Animation pour les suggestions */
.suggestion-badge {
  transition: all 0.2s ease;
}

.suggestion-badge.clicked {
  transform: scale(0.95);
  opacity: 0.8;
}

/* Style pour les suggestions désactivées pendant le chargement */
.disabled-suggestion {
  opacity: 0.5;
  cursor: not-allowed !important;
  pointer-events: none;
}

/* Animation pour l'indicateur de chargement */
@keyframes pulse {
  0% { opacity: 0.6; }
  50% { opacity: 1; }
  100% { opacity: 0.6; }
}
</style>