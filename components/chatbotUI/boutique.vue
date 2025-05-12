<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-3 gap-6 p-4">
        <div v-for="groupe in produitsGroupes" :key="groupe.title" 
            class="product-card bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:shadow-xl hover:-translate-y-1">
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
                        @click="viewProduct(getActiveProduct(groupe, selectedPointures[groupe.title]).id)" 
                        class="w-full bg-primary-500 hover:bg-primary-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
                            <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
                        </svg>
                        Voir
                    </button>
                </div>

                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    <span>ID Produit : {{ getActiveProduct(groupe, selectedPointures[groupe.title])?.id || 'N/A' }}</span>
                    <br>
                    <span>{{ groupe.variants.length }} variante(s) disponible(s)</span>
                </div>
            </div>
        </div>
    </div>
    <UPagination v-model="page" :items-per-page="6" :total="produitsGroupes.length" :sibling-count="1"/>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const produits = ref([]);
const panier = ref([]);
const page = ref(1);
const selectedPointures = ref({});

// Regrouper les produits par nom
const produitsGroupes = computed(() => {
  const groupes = {};
  
  produits.value.forEach(produit => {
    const nomProduit = produit.title;
    
    if (!groupes[nomProduit]) {
      groupes[nomProduit] = {
        ...produit,
        variants: [produit],
        pointures: [produit.pointure_label],
        selectedPointure: produit.pointure_label
      };
    } else {
      groupes[nomProduit].variants.push(produit);
      if (!groupes[nomProduit].pointures.includes(produit.pointure_label)) {
        groupes[nomProduit].pointures.push(produit.pointure_label);
      }
    }
  });
  
  // Trier les pointures numériquement
  Object.values(groupes).forEach(groupe => {
    groupe.pointures.sort((a, b) => parseFloat(a) - parseFloat(b));
  });
  
  return Object.values(groupes);
});

onMounted(async () => {
    try {
        const dataProduits = await fetch('http://localhost/apiSneakMe/api/produits.php');
        produits.value = await dataProduits.json();
        
        // Initialiser les pointures sélectionnées
        produitsGroupes.value.forEach(groupe => {
            selectedPointures.value[groupe.title] = groupe.pointures[0];
        });
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
  return groupe.variants.find(variant => variant.pointure_label === pointure);
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
}
</script>

<style scoped>
.product-card {
  display: flex;
  flex-direction: column;
  height: 100%;
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