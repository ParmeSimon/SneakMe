<template>
    <!-- Filtres en haut de la page -->
    <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Filtres</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Filtre par sexe -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sexe</label>
                <select 
                    v-model="filtres.sexe" 
                    class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous</option>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                    <option value="Unisexe">Unisexe</option>
                </select>
            </div>
            
            <!-- Filtre par couleur -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Couleur</label>
                <select 
                    v-model="filtres.couleur" 
                    class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Toutes</option>
                    <option v-for="couleur in couleursDisponibles" :key="couleur" :value="couleur">{{ couleur }}</option>
                </select>
            </div>
            
            <!-- Filtre par pointure -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pointure</label>
                <select 
                    v-model="filtres.pointure" 
                    class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Toutes</option>
                    <option v-for="pointure in pointuresDisponibles" :key="pointure" :value="pointure">{{ pointure }}</option>
                </select>
            </div>
            
            <!-- Bouton de réinitialisation -->
            <div class="flex items-end">
                <button 
                    @click="resetFiltres" 
                    class="w-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                    Réinitialiser
                </button>
            </div>
        </div>
    </div>
    
    <!-- Liste horizontale des produits -->
    <div class="overflow-x-auto pb-4">
        <div class="flex space-x-6 p-4" style="min-width: max-content;">
        <div v-for="groupe in produitsFiltres" :key="groupe.title" 
            class="product-card bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:shadow-xl hover:-translate-y-1"
            style="min-width: 280px; max-width: 320px;">
            <!-- Image avec badge de catégorie -->
            <div class="relative">
                <img :src="groupe.url_image" :alt="groupe.title" class="w-full h-56 object-cover">
                <span class="absolute top-3 right-3 bg-primary-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                    {{ groupe.categorie_label }}
                </span>
            </div>
            
            <!-- Contenu de la carte -->
            <div class="p-4">
                <!-- Titre et prix -->
                <div class="flex justify-between items-start mb-2">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ groupe.title }}</h2>
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ groupe.price / 100 }}€</span>
                </div>
                
                <!-- Description -->
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3 line-clamp-2">{{ groupe.description }}</p>
                
                <!-- Caractéristiques -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <!-- Sexe -->
                    <span class="inline-flex items-center bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs px-2 py-1 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ groupe.sexe_label }}
                    </span>
                    
                    <!-- Couleur -->
                    <span class="inline-flex items-center bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs px-2 py-1 rounded-full">
                        <span class="w-2 h-2 rounded-full mr-1" :style="{ backgroundColor: couleurLabel(groupe.couleur_label) }"></span>
                        {{ groupe.couleur_label }}
                    </span>
                    
                    <!-- Dropdown de pointures -->
                    <div class="relative w-full mt-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pointure disponible :</label>
                        <select 
                            v-model="selectedPointures[groupe.title]" 
                            class="w-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option v-for="pointure in groupe.pointures" :key="pointure" :value="pointure">{{ pointure }}</option>
                        </select>
                    </div>
                </div>
                
                <!-- Bouton d'action -->
                <div class="flex justify-center gap-2">
                    <button 
                        @click="addProduct(getActiveProduct(groupe, selectedPointures[groupe.title]))" 
                        class="w-full bg-primary-500 hover:bg-primary-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Ajouter au panier
                    </button>
                    <button 
                        @click="handleViewProduct(groupe, selectedPointures[groupe.title])" 
                        class="w-full bg-primary-500 hover:bg-primary-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                            <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                        </svg>
                        Voir
                    </button>
                </div>

                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    <span>ID Produit : {{ getProductId(groupe, selectedPointures[groupe.title]) }}</span>
                    <br>
                    <span>{{ groupe.variants.length }} variante(s) disponible(s)</span>
                </div>
            </div>
        </div>
        </div>
    </div>
    <UPagination v-model="page" :items-per-page="6" :total="produitsFiltres.length" :sibling-count="1"/>
</template>

<script setup>
import { ref, onMounted, computed, markRaw, watch } from 'vue';
import VoirPlus from '~/components/chatbotUI/voirplus.vue';

// Définir les émetteurs d'événements et les props
const emit = defineEmits(['view-product', 'send-message']);
const props = defineProps({
    filterType: {
        type: String,
        default: ''
    },
    filterValue: {
        type: String,
        default: ''
    }
});

// Fonction pour envoyer un message au composant parent
const sendMessage = (message) => {
    emit('send-message', message);
};

const produits = ref([]);
const panier = ref([]);
const page = ref(1);
const selectedPointures = ref({});

// Filtres
const filtres = ref({
    sexe: '',
    couleur: '',
    pointure: ''
});

// Listes des valeurs disponibles pour les filtres
const couleursDisponibles = computed(() => {
    const couleurs = new Set();
    produits.value.forEach(produit => {
        couleurs.add(produit.couleur_label);
    });
    return [...couleurs].sort();
});

const pointuresDisponibles = computed(() => {
    const pointures = new Set();
    produits.value.forEach(produit => {
        pointures.add(produit.pointure_label);
    });
    return [...pointures].sort((a, b) => parseFloat(a) - parseFloat(b));
});

// Regrouper les produits par nom
const produitsGroupes = computed(() => {
  const groupes = {};
  
  produits.value.forEach(produit => {
    const nomProduit = produit.title;
    // S'assurer que pointure_label est une chaîne de caractères
    const pointureLabel = String(produit.pointure_label).trim();
    
    if (!groupes[nomProduit]) {
      groupes[nomProduit] = {
        ...produit,
        variants: [produit],
        pointures: [pointureLabel],
        selectedPointure: pointureLabel
      };
    } else {
      groupes[nomProduit].variants.push(produit);
      // Vérifier si la pointure existe déjà en comparant les chaînes
      const pointureExiste = groupes[nomProduit].pointures.some(p => String(p).trim() === pointureLabel);
      if (!pointureExiste) {
        groupes[nomProduit].pointures.push(pointureLabel);
      }
    }
  });
  
  // Log des groupes pour débogage
  console.log('Groupes de produits:', groupes);
  
  // Trier les pointures numériquement
  Object.values(groupes).forEach(groupe => {
    groupe.pointures.sort((a, b) => parseFloat(a) - parseFloat(b));
  });
  
  return Object.values(groupes);
});

// Produits filtrés
const produitsFiltres = computed(() => {
  console.log('Calculating filtered products with filters:', filtres.value);
  
  return produitsGroupes.value.filter(groupe => {
    // Filtre par sexe
    if (filtres.value.sexe) {
      const sexeMatch = String(groupe.sexe_label).trim().toLowerCase() === String(filtres.value.sexe).trim().toLowerCase();
      if (!sexeMatch) {
        return false;
      }
    }
    
    // Filtre par couleur
    if (filtres.value.couleur) {
      const couleurMatch = String(groupe.couleur_label).trim().toLowerCase() === String(filtres.value.couleur).trim().toLowerCase();
      if (!couleurMatch) {
        console.log(`Couleur mismatch: ${groupe.couleur_label} !== ${filtres.value.couleur}`);
        return false;
      }
    }
    
    // Filtre par pointure
    if (filtres.value.pointure) {
      // Débugage
      console.log(`Vérification pointure ${filtres.value.pointure} pour ${groupe.title}`);
      console.log('Pointures du groupe:', groupe.pointures);
      
      // Vérifier si la pointure existe dans les pointures du groupe
      // Convertir toutes les valeurs en string pour la comparaison
      const pointureFiltre = String(filtres.value.pointure).trim();
      const pointureExiste = groupe.pointures.some(p => String(p).trim() === pointureFiltre);
      
      if (!pointureExiste) {
        console.log(`Pointure ${filtres.value.pointure} non trouvée dans ${groupe.title} (pointures: ${groupe.pointures.join(', ')})`);
        return false;
      } else {
        console.log(`Pointure ${filtres.value.pointure} trouvée dans ${groupe.title}`);
      }
    }
    
    return true;
  });
});

// Réinitialiser les filtres
const resetFiltres = () => {
  filtres.value = {
    sexe: '',
    couleur: '',
    pointure: ''
  };
};

// Appliquer les filtres reçus du chatbot
const applyFilterFromChatbot = (type, value) => {
  resetFiltres(); // Réinitialiser d'abord tous les filtres
  
  // Vérifier que les produits sont chargés
  if (produits.value.length === 0) {
    console.warn('Impossible d\'appliquer les filtres: produits non chargés');
    return;
  }

  console.log(`Applying filter: ${type} = ${value}`);

  switch(type.toLowerCase()) {
    case 'pointure':
      // Ajouter des logs pour débogage
      console.log('Pointure recherchée:', value);
      console.log('Pointures disponibles:', pointuresDisponibles.value);
      
      // Normaliser la valeur de la pointure (convertir en string pour éviter les problèmes de type)
      const pointureNormalisee = String(value).trim();
      
      // Vérifier si la pointure existe en comparant les strings
      const pointureExiste = pointuresDisponibles.value.some(p => String(p).trim() === pointureNormalisee);
      
      if (pointureExiste) {
        // Trouver la pointure exacte comme dans la liste
        const pointureExacte = pointuresDisponibles.value.find(p => 
          String(p).trim() === pointureNormalisee
        );
        
        // Appliquer le filtre avec la valeur exacte de la liste
        filtres.value.pointure = pointureExacte;
        console.log('Pointure trouvée et appliquée:', pointureExacte);
      } else {
        console.warn(`Pointure ${value} non disponible`);
        // Envoyer un message au chatbot pour informer l'utilisateur
        emit('send-message', `La pointure ${value} n'est pas disponible. Voici les pointures disponibles: ${pointuresDisponibles.value.join(', ')}`);
      }
      break;
    case 'sexe':
      // Normaliser la valeur pour correspondre aux options (première lettre en majuscule)
      const sexeNormalise = value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
      if (['Homme', 'Femme', 'Unisexe'].includes(sexeNormalise)) {
        filtres.value.sexe = sexeNormalise;
      } else {
        console.warn(`Sexe ${value} non valide`);
        emit('send-message', `La catégorie de sexe "${value}" n'est pas valide. Options disponibles: Homme, Femme, Unisexe`);
      }
      break;
    case 'couleur':
      // Normaliser la valeur pour correspondre aux options (première lettre en majuscule)
      const couleurNormalise = value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
      console.log('Normalized color:', couleurNormalise);
      console.log('Available colors:', couleursDisponibles.value);
      console.log('Types des couleurs:', couleursDisponibles.value.map(c => typeof c));
      
      // Vérifier si la couleur existe en comparant les strings (insensible à la casse)
      const couleurExiste = couleursDisponibles.value.some(c => 
        String(c).trim().toLowerCase() === couleurNormalise.toLowerCase()
      );
      
      if (couleurExiste) {
        // Trouver la couleur avec la casse exacte comme dans la liste
        const couleurExacte = couleursDisponibles.value.find(c => 
          String(c).trim().toLowerCase() === couleurNormalise.toLowerCase()
        );
        filtres.value.couleur = couleurExacte;
        console.log('Couleur trouvée et appliquée:', couleurExacte);
      } else {
        console.warn(`Couleur ${value} non disponible`);
        emit('send-message', `La couleur ${value} n'est pas disponible. Couleurs disponibles: ${couleursDisponibles.value.join(', ')}`);
      }
      break;
    default:
      console.warn(`Type de filtre inconnu: ${type}`);
  }
};

// Observer les changements de props pour appliquer les filtres automatiquement
// Mais sans immediate: true pour éviter l'exécution avant le chargement des produits
watch(() => props.filterType, (newType) => {
  if (newType && props.filterValue && produits.value.length > 0) {
    applyFilterFromChatbot(newType, props.filterValue);
  }
});

onMounted(async () => {
    try {
        const dataProduits = await fetch('http://localhost/sneakme/api/produits.php');
        produits.value = await dataProduits.json();
        
        // Initialiser les pointures sélectionnées
        produitsGroupes.value.forEach(groupe => {
            selectedPointures.value[groupe.title] = groupe.pointures[0];
        });
        
        // Appliquer les filtres après le chargement des produits si des filtres sont définis
        if (props.filterType && props.filterValue) {
            console.log('Applying filter after products loaded:', props.filterType, props.filterValue);
            console.log('Available colors:', couleursDisponibles.value);
            setTimeout(() => applyFilterFromChatbot(props.filterType, props.filterValue), 100);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des produits:', error);
    }

    const dataLocalStorage = localStorage.getItem('panierSneakMe')
    panier.value = dataLocalStorage ? JSON.parse(dataLocalStorage) : []

    if (!dataLocalStorage) {
        localStorage.setItem('panierSneakMe', JSON.stringify(panier.value))
    }
});

// Obtenir le produit actif en fonction de la pointure sélectionnée
const getActiveProduct = (groupe, pointure) => {
  return groupe.variants.find(variant => String(variant.pointure_label).trim() === String(pointure).trim());
};

// Récupérer l'ID du produit de manière sécurisée
const getProductId = (groupe, pointure) => {
  const produit = getActiveProduct(groupe, pointure);
  if (produit && produit.id) {
    return produit.id;
  }
  // Si le produit n'est pas trouvé, utiliser le premier produit du groupe comme fallback
  if (groupe.variants && groupe.variants.length > 0) {
    return groupe.variants[0].id;
  }
  return 'N/A';
};

function couleurLabel(couleur) {
    couleur = couleur.toLowerCase();
    switch (couleur) {
        case 'blanc':
            return 'white';
        case 'noir':
            return 'black';
        case 'rouge':
            return 'red';
        case 'bleu':
            return 'blue';
        case 'vert':
            return 'green';
        case 'marron':
            return 'brown';
        case 'beige':
            return '#E8DCCA';
        case 'gris':
            return 'gray';
        default:
            return 'white';
    }
}

const addProduct = (produitActif) => {
    if (!produitActif || !produitActif.id) {
        console.error('Produit invalide');
        return;
    }
    
    // Créer un objet avec les informations importantes du produit
    const produitPanier = {
        id: produitActif.id,
        title: produitActif.title,
        price: produitActif.price,
        pointure: produitActif.pointure_label,
        couleur: produitActif.couleur_label,
        url_image: produitActif.url_image
    };
    
    // Ajouter l'objet produit au panier
    panier.value.push(produitPanier);
    localStorage.setItem('panierSneakMe', JSON.stringify(panier.value));
    console.log('produit ajouté au panier :', produitPanier);
}

const viewProduct = (id) => {
    console.log('Affichage du produit :', id);
    // Émettre un événement avec les informations du composant à afficher
    emit('view-product', {
        isComponent: true,
        componentName: markRaw(VoirPlus),
        content: `Détails du produit #${id}`,
        props: { productId: id }
    });
}

// Fonction sécurisée pour gérer le clic sur le bouton Voir
const handleViewProduct = (groupe, pointure) => {
    // Obtenir le produit actif
    const produitActif = getActiveProduct(groupe, pointure);
    
    // Vérifier si le produit existe
    if (!produitActif || !produitActif.id) {
        console.error('Produit non trouvé pour la pointure:', pointure);
        // Utiliser le premier produit du groupe comme fallback
        if (groupe.variants && groupe.variants.length > 0) {
            const premierProduit = groupe.variants[0];
            console.log('Utilisation du premier produit comme fallback:', premierProduit.id);
            sendMessage(`voir produit ${premierProduit.id}`);
        } else {
            console.error('Aucun produit disponible dans ce groupe');
            // Informer l'utilisateur qu'il y a un problème
            emit('send-message', 'Désolé, je ne peux pas afficher les détails de ce produit pour le moment.');
        }
    } else {
        // Si le produit existe, envoyer le message pour voir le produit
        sendMessage(`voir produit ${produitActif.id}`);
    }
};
</script>

<style scoped>
.product-card {
  display: flex;
  flex-direction: column;
  height: 100%;
  flex-shrink: 0;
}

.product-card:hover img {
  transform: scale(1.05);
  transition: transform 0.5s ease;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>