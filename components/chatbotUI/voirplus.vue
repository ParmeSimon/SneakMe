<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
        <div v-if="loading" class="p-8 flex justify-center items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-500"></div>
        </div>
        
        <div v-else-if="error" class="p-8 text-center text-red-500">
            <p>{{ error }}</p>
        </div>
        
        <div v-else-if="produit" class="flex flex-col md:flex-row">
            <!-- Image du produit -->
            <div class="md:w-1/2">
                <div class="relative">
                    <img :src="produit.url_image" :alt="produit.title" class="w-full h-auto object-cover">
                    <span class="absolute top-3 right-3 bg-primary-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                        {{ produit.categorie_label }}
                    </span>
                </div>
            </div>
            
            <!-- Détails du produit -->
            <div class="p-6 md:w-1/2">
                <div class="mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ produit.title }}</h2>
                    <div class="flex items-center justify-between">
                        <span class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ produit.price / 100 }}€</span>
                        <span class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-3 py-1 rounded-full">
                            ID: {{ produit.id }}
                        </span>
                    </div>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-600 dark:text-gray-300">{{ produit.description }}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <!-- Caractéristiques -->
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-gray-700 dark:text-gray-300">{{ produit.sexe_label }}</span>
                    </div>
                    
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full mr-2" :style="{ backgroundColor: couleurLabel(produit.couleur_label) }"></span>
                        <span class="text-gray-700 dark:text-gray-300">{{ produit.couleur_label }}</span>
                    </div>
                    
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                        </svg>
                        <span class="text-gray-700 dark:text-gray-300">Pointure: {{ produit.pointure_label }}</span>
                    </div>
                    
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span class="text-gray-700 dark:text-gray-300">{{ produit.categorie_label }}</span>
                    </div>
                </div>
                
                <!-- Bouton d'action -->
                <button 
                    @click="addProduct(produit)" 
                    class="w-full bg-primary-500 hover:bg-primary-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Ajouter au panier
                </button>
            </div>
        </div>
        
        <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
            <p>Aucune information disponible pour ce produit.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Propriétés
const props = defineProps({
    productId: {
        type: [Number, String],
        required: true
    }
});

const produit = ref(null);
const loading = ref(true);
const error = ref(null);
const panier = ref([]);

// Charger les données du produit
onMounted(async () => {
    try {
        // Récupérer le panier du localStorage
        const dataLocalStorage = localStorage.getItem('panierSneakMe');
        panier.value = dataLocalStorage ? JSON.parse(dataLocalStorage) : [];
        
        // Charger les données du produit
        const response = await fetch(`http://localhost/sneakme/api/produits.php?id=${props.productId}`);
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (Array.isArray(data) && data.length > 0) {
            produit.value = data[0]; // Prendre le premier élément si c'est un tableau
        } else if (typeof data === 'object' && data !== null) {
            produit.value = data; // Utiliser l'objet directement
        } else {
            error.value = "Format de données inattendu";
        }
    } catch (err) {
        error.value = `Erreur lors du chargement du produit: ${err.message}`;
        console.error(err);
    } finally {
        loading.value = false;
    }
});

// Fonction pour convertir les noms de couleurs en codes CSS
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

// Fonction pour ajouter au panier
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
</script>

<style scoped>
/* Animation pour le chargement */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
