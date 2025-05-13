<template>
  <div class="h-screen bg-gray-50 dark:bg-gray-900 flex flex-col">
    <!-- En-tête du chatbot -->
    <header class="bg-white dark:bg-gray-800 shadow-sm py-4 px-6">
      <div class="max-w-7xl mx-auto flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
          <UIcon name="i-heroicons-chat-bubble-left-right" class="mr-2 text-primary" size="lg" />
          SneakMe Assistant
        </h1>

        <div class="flex justify-center items-center">
          <p v-if="user_sneakme" class="text-sm text-gray-600 dark:text-gray-400">
            Connecté en tant que {{ user_sneakme.email }}
          </p>
          <p v-else class="text-sm text-gray-600 dark:text-gray-400">
            <UButton color="primary" class="me-3" @click="() => sendMessage('login')" label="Se connecter"/>
            <UButton color="primary" @click="() => sendMessage('register')" variant="soft" label="S'inscrire"/>
          </p>
          <UButton color="primary" class="font-bold rounded-full ms-6" icon="i-heroicons-information-circle" @click="() => sendMessage('/help')" variant="soft"/>
        </div>
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
                <p v-if="message.content" class="mb-2">{{ message.content }}</p>
                <component 
                  :is="message.componentName" 
                  v-bind="message.props || {}" 
                  @view-product="handleViewProduct"
                  @send-message="sendMessage"
                  @login-success="handleLoginSuccess"
                  @register-success="handleRegisterSuccess"
                />
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
import VoirPlus from '~/components/chatbotUI/voirplus.vue';
import Panier from '~/components/chatbotUI/panier.vue';
import Help from '~/components/chatbotUI/help.vue';
import Login from '~/components/chatbotUI/login.vue';
import Register from '~/components/chatbotUI/register.vue';
import VueAdmin from '~/components/chatbotUI/vue_admin.vue';
import Logout from '~/components/chatbotUI/logout.vue';

let user_sneakme = ref(null);

onMounted(() => {
  let user_sneakmeLS = localStorage.getItem('user_sneakme');
  if (user_sneakmeLS) {
    user_sneakme.value = JSON.parse(user_sneakmeLS);
  }
})
// Référence au conteneur de messages pour le défilement automatique
const messagesContainer = ref(null);

// État du chatbot
const newMessage = ref('');
const isLoading = ref(false);

// Messages de chat
const messages = ref([
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
  'Quelles sont vos marques disponibles ?',
  'catégorie pointure 42',
  'catégorie couleur noir',
  'catégorie sexe homme'
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
  messages.value.push({
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
  
  // Attendre un court délai pour simuler le traitement
  await new Promise(resolve => setTimeout(resolve, 1000));
  
  let response;

  // Normaliser le message (enlever les accents, mettre en minuscule)
  const normalizedMessage = userMessage.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

  // Vérifier si c'est une commande de catégorie avec attributs
  const categorieMatch = normalizedMessage.match(/categorie\s+(pointure|sexe|couleur)\s+(.+)/);
  if (categorieMatch) {
    const attributType = categorieMatch[1]; // pointure, sexe ou couleur
    const attributValue = categorieMatch[2]; // la valeur spécifiée
    
    response = {
      isComponent: true,
      componentName: markRaw(Boutique),
      content: `Voici les produits filtrés par ${attributType}: ${attributValue}`,
      props: { 
        filterType: attributType,
        filterValue: attributValue
      }
    };
  }
  // Vérifier si c'est une demande pour voir un produit
  else if (normalizedMessage.match(/voir produit (\d+)/)) {
    const productId = normalizedMessage.match(/voir produit (\d+)/)[1];
    response = {
      isComponent: true,
      componentName: markRaw(VoirPlus),
      content: `Détails du produit #${productId}`,
      props: { productId: productId }
    };
  }
  // Déterminer la réponse en fonction du message de l'utilisateur
  else if (normalizedMessage.includes('commande')) {
    response = generateResponse('commande');
  } else if (normalizedMessage.includes('boutique') || normalizedMessage.includes('sneaker')) {
    response = generateResponse('boutique');
  } else if (normalizedMessage.includes('panier')) {
    response = generateResponse('panier');
  } else if (normalizedMessage.startsWith('/help')) {
    response = generateResponse('/help');
  } else if (normalizedMessage === 'clear') {
    response = generateResponse('clear');
  } else if (normalizedMessage === 'vue admin') {
    response = generateResponse('vue admin');
  } else if (normalizedMessage === 'logout') {
    response = generateResponse('logout');
  } else if (normalizedMessage.includes('login')) {
    response = {
      isComponent: true,
      componentName: markRaw(Login),
      content: 'Voici la page de connexion',
      props: {}
    };
  } else if (normalizedMessage.includes('register')) {
    response = {
      isComponent: true,
      componentName: markRaw(Register),
      content: 'Voici la page d\'inscription',
      props: {}
    };
  } else {
    // Réponse par défaut
    response = generateResponse(userMessage);
  }
  
  // Ajouter la réponse du chatbot seulement si elle n'est pas null
  if (response !== null) {
    // Si response est un objet avec isComponent, on l'utilise directement
    if (typeof response === 'object' && response.isComponent !== undefined) {
      // Créer un nouvel objet message avec tous les champs de response
      const messageObj = {
        role: 'assistant',
        isComponent: response.isComponent,
        componentName: response.componentName,
        content: response.content || ''
      };
      
      // Ajouter les props si présents
      if (response.props) {
        messageObj.props = response.props;
      }
      
      messages.value.push(messageObj);
    } else {
      // Fallback pour la compatibilité avec d'anciennes réponses textuelles
      messages.value.push({
        role: 'assistant',
        isComponent: false,
        content: response
      });
    }
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
        content: 'Voici notre sélection de sneakers disponibles :'
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
    case 'vue admin':
      return {
        isComponent: true,
        componentName: markRaw(VueAdmin),
        content: ''
      };
    case 'logout':
      user_sneakme.value = null;
      return {
        isComponent: true,
        componentName: markRaw(Logout),
        content: ''
      };
    case 'clear':
      messages.value = [
      {
        role: 'assistant',
        isComponent: false,
        content: 'Bonjour ! Je suis SneakMe Assistant. Comment puis-je vous aider aujourd\'hui ?'
      }
      ];
      console.log(messages.value);
      // Ne pas ajouter de nouveau message après le clear
      return null;
      break;
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

// Gérer l'événement view-product émis par le composant boutique
const handleViewProduct = (productData) => {
  console.log('Event view-product reçu:', productData);
  
  // Ajouter le composant VoirPlus au chat
  messages.value.push({
    role: 'assistant',
    isComponent: productData.isComponent,
    componentName: productData.componentName,
    content: productData.content,
    props: productData.props
  });
  
  // Faire défiler vers le bas pour voir le nouveau message
  scrollToBottom();
};

// Faire défiler vers le bas au chargement initial
onMounted(() => {
  scrollToBottom();
});

// Gérer l'événement login-success émis par le composant login
const handleLoginSuccess = (user) => {
  console.log('Event login-success reçu:', user);
  let user_sneakmeLS = localStorage.getItem('user_sneakme');
  if (user_sneakmeLS) {
    user_sneakme.value = JSON.parse(user_sneakmeLS);
  }
}

// Gérer l'événement register-success émis par le composant register
const handleRegisterSuccess = () => {
  console.log('Event register-success reçu');
  // Aucune action spécifique nécessaire ici car le composant register
  // redirige déjà vers la page de connexion après l'inscription
};

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