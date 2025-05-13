<template>
    <div class="panier-container">
        <div v-if="panierItems.length === 0" class="empty-cart">
            <div class="flex flex-col items-center justify-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="text-xl font-medium text-gray-700 dark:text-gray-200 mb-2">Votre panier est vide</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Ajoutez des produits pour commencer vos achats</p>
                <button class="bg-primary-500 hover:bg-primary-600 text-white font-medium py-2 px-6 rounded-lg transition-colors duration-200">
                    Découvrir nos produits
                </button>
            </div>
        </div>

        <div v-else class="cart-content">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Mon Panier</h2>
            
            <!-- En-têtes des colonnes -->
            <div class="hidden md:grid grid-cols-12 gap-4 py-3 border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400">
                <div class="col-span-6">Produit</div>
                <div class="col-span-2 text-center">Prix</div>
                <div class="col-span-2 text-center">Quantité</div>
                <div class="col-span-2 text-right">Total</div>
            </div>
            
            <!-- Liste des produits dans le panier -->
            <div class="space-y-4 mt-4">
                <div v-for="(item, index) in panierItems" :key="index" 
                    class="grid grid-cols-1 md:grid-cols-12 gap-4 py-4 border-b border-gray-200 dark:border-gray-700 items-center"
                    :class="{ 'bg-red-50 dark:bg-red-900/20': !item.inStock }">
                    <!-- Produit (image + infos) -->
                    <div class="col-span-1 md:col-span-6 flex space-x-4">
                        <div class="w-20 h-20 flex-shrink-0 rounded-md overflow-hidden relative">
                            <img :src="item.url_image" :alt="item.title" class="w-full h-full object-cover" :class="{ 'opacity-50': !item.inStock }">
                            <div v-if="!item.inStock" class="absolute inset-0 flex items-center justify-center bg-red-500/30">
                                <span class="text-xs font-bold text-white bg-red-500 px-2 py-1 rounded-sm">RUPTURE</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-base font-medium" :class="item.inStock ? 'text-gray-900 dark:text-white' : 'text-red-600 dark:text-red-400'">{{ item.title }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ item.categorie_label }}</p>
                            
                            <!-- Couleur -->
                            <div class="flex items-center mt-1">
                                <span class="inline-block w-3 h-3 rounded-full mr-1" :style="{ backgroundColor: couleurLabel(item.couleur) }"></span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ item.couleur }}</span>
                            </div>
                            
                            <!-- Pointure -->
                            <div class="mt-1 flex items-center">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Pointure: </span>
                                <span class="text-xs font-medium ml-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-0.5 rounded-full">{{ item.pointure }}</span>
                            </div>
                            
                            <!-- Message de rupture de stock -->
                            <div v-if="!item.inStock" class="mt-1 text-xs text-red-600 dark:text-red-400 font-medium">
                                Ce produit n'est plus en stock
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prix unitaire -->
                    <div class="col-span-1 md:col-span-2 text-left md:text-center">
                        <span class="md:hidden text-sm text-gray-500 dark:text-gray-400 mr-2">Prix:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ formatPrice(item.price) }}</span>
                    </div>
                    
                    <!-- Quantité -->
                    <div class="col-span-1 md:col-span-2 flex items-center">
                        <span class="md:hidden text-sm text-gray-500 dark:text-gray-400 mr-2">Quantité:</span>
                        <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-md">
                            <button @click="decrementQuantity(index)" class="px-2 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                -
                            </button>
                            <span class="px-3 py-1 text-gray-800 dark:text-white">{{ item.quantity }}</span>
                            <button @click="incrementQuantity(index)" class="px-2 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                +
                            </button>
                        </div>
                    </div>
                    
                    <!-- Total ligne -->
                    <div class="col-span-1 md:col-span-2 flex justify-between items-center">
                        <span class="md:hidden text-sm text-gray-500 dark:text-gray-400">Total:</span>
                        <div class="flex items-center">
                            <span class="font-medium text-gray-900 dark:text-white mr-4">{{ formatPrice(item.price * item.quantity) }}</span>
                            <button @click="removeItem(index)" class="text-gray-400 hover:text-red-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Résumé du panier -->
            <div class="mt-8 md:flex md:justify-end">
                <div class="md:w-1/3 bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Résumé de la commande</h3>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Sous-total</span>
                            <span>{{ formatPrice(subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Frais de livraison</span>
                            <span>{{ formatPrice(shippingCost) }}</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 my-2 pt-2">
                            <div class="flex justify-between font-medium text-gray-900 dark:text-white">
                                <span>Total</span>
                                <span>{{ formatPrice(total) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <button class="w-full mt-6 bg-primary-500 hover:bg-primary-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                        Procéder au paiement
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

// Récupération des produits dans le panier depuis localStorage
const panierItems = ref([]);
const produits = ref([]);

// Vérifier si un produit est en stock en interrogeant directement la route des produits
const checkStock = async (productId) => {
    try {
        // Appel à l'API des produits avec l'ID spécifique
        const response = await fetch(`http://localhost/sneakme/api/produits.php?id=${productId}`);
        
        // Si la réponse n'est pas OK, le produit est considéré comme n'étant plus en stock
        if (!response.ok) {
            console.log(`Produit ${productId} non disponible: Erreur HTTP ${response.status}`);
            return false;
        }
        
        // Analyser la réponse JSON
        const data = await response.json();
        
        // Si la réponse est vide ou ne contient pas le produit, il n'est plus en stock
        if (!data || data.length === 0 || (Array.isArray(data) && data.length === 0)) {
            console.log(`Produit ${productId} non disponible: Aucune donnée retournée`);
            return false;
        }
        
        // Le produit existe dans la base de données, donc il est en stock
        console.log(`Produit ${productId} disponible en stock`);
        return true;
    } catch (error) {
        console.error(`Erreur lors de la vérification du stock pour le produit ${productId}:`, error);
        // En cas d'erreur, on considère que le produit n'est plus en stock
        return false;
    }
};

// Vérifier le stock pour tous les produits du panier
const checkAllStock = async () => {
    console.log("Vérification du stock pour tous les produits du panier...");
    
    for (let i = 0; i < panierItems.value.length; i++) {
        const item = panierItems.value[i];
        try {
            // Vérifier si le produit est en stock
            const inStock = await checkStock(item.id);
            
            // Mettre à jour le statut de stock du produit
            panierItems.value[i] = { ...item, inStock };
            
            console.log(`Produit ${item.id} (${item.title}): ${inStock ? 'En stock' : 'Rupture de stock'}`);
        } catch (error) {
            console.error(`Erreur lors de la vérification du stock pour ${item.title}:`, error);
            // En cas d'erreur, on considère que le produit n'est plus en stock
            panierItems.value[i] = { ...item, inStock: false };
        }
    }
    
    console.log("Vérification du stock terminée.");
};

// Chargement des données
onMounted(async () => {
    // Récupérer les produits du panier
    const dataLocalStorage = localStorage.getItem('panierSneakMe');
    const panierData = dataLocalStorage ? JSON.parse(dataLocalStorage) : [];
    
    // Si le panier n'existe pas encore, le créer
    if (!dataLocalStorage) {
        localStorage.setItem('panierSneakMe', JSON.stringify([]));
    }
    
    // Vérifier le format des données du panier
    if (panierData.length > 0 && typeof panierData[0] === 'object') {
        // Nouveau format : objets produits complets
        console.log('Nouveau format de panier détecté (objets produits)');
        // Ajouter la quantité à chaque produit
        const produitMap = {};
        panierData.forEach(produit => {
            const id = produit.id;
            if (!produitMap[id]) {
                produitMap[id] = { ...produit, quantity: 1, inStock: true }; // Par défaut, on suppose que le produit est en stock
            } else {
                produitMap[id].quantity += 1;
            }
        });
        panierItems.value = Object.values(produitMap);
    } else {
        // Ancien format : IDs simples
        console.log('Ancien format de panier détecté (IDs simples)');
        // Charger tous les produits
        try {
            const dataProduits = await fetch('http://localhost/sneakme/api/produits.php');
            produits.value = await dataProduits.json();
            // Créer les éléments du panier avec les détails des produits
            const countMap = {};
            panierData.forEach(id => {
                countMap[id] = (countMap[id] || 0) + 1;
            });
            
            Object.keys(countMap).forEach(id => {
                const produit = produits.value.find(p => p.id === parseInt(id));
                if (produit) {
                    panierItems.value.push({
                        ...produit,
                        quantity: countMap[id],
                        inStock: true // Par défaut, on suppose que le produit est en stock
                    });
                }
            });
        } catch (error) {
            console.error('Erreur lors du chargement des produits:', error);
        }
    }
    
    // Vérifier le stock pour tous les produits du panier
    await checkAllStock();
});

// Sauvegarder le panier dans localStorage
const savePanier = () => {
    // Convertir les éléments du panier en format de stockage
    const panierStorage = [];
    panierItems.value.forEach(item => {
        // Ajouter l'item autant de fois que sa quantité
        for (let i = 0; i < item.quantity; i++) {
            panierStorage.push({
                id: item.id,
                title: item.title,
                price: item.price,
                pointure: item.pointure,
                couleur: item.couleur_label,
                url_image: item.url_image
            });
        }
    });
    localStorage.setItem('panierSneakMe', JSON.stringify(panierStorage));
};

// Ajouter un produit au panier
const addProduct = (produit) => {
    // Vérifier si le produit existe déjà dans le panier
    const existingIndex = panierItems.value.findIndex(item => item.id === produit.id);
    
    if (existingIndex !== -1) {
        // Si le produit existe déjà, augmenter sa quantité
        panierItems.value[existingIndex].quantity += 1;
    } else {
        // Sinon, ajouter le nouveau produit avec quantité 1
        panierItems.value.push({
            ...produit,
            quantity: 1
        });
    }
    
    savePanier();
};

// Supprimer un élément du panier
const removeItem = (index) => {
    panierItems.value.splice(index, 1);
    savePanier();
};

// Augmenter la quantité d'un produit
const incrementQuantity = (index) => {
    panierItems.value[index].quantity += 1;
    savePanier();
};

// Diminuer la quantité d'un produit
const decrementQuantity = (index) => {
    if (panierItems.value[index].quantity > 1) {
        panierItems.value[index].quantity -= 1;
        savePanier();
    }
};

// Calculer le sous-total
const subtotal = computed(() => {
    return panierItems.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

// Frais de livraison (gratuit au-dessus de 50€)
const shippingCost = computed(() => {
    return subtotal.value > 5000 ? 0 : 499;
});

// Total
const total = computed(() => {
    return subtotal.value + shippingCost.value;
});

// Formater le prix
const formatPrice = (price) => {
    return `${(price / 100).toFixed(2)}€`;
};

// Convertir les noms de couleur en codes couleur CSS
function couleurLabel(couleur) {
    couleur = couleur?.toLowerCase() || '';
    switch (couleur) {
        case 'blanc': return 'white';
        case 'noir': return 'black';
        case 'rouge': return 'red';
        case 'bleu': return 'blue';
        case 'vert': return 'green';
        case 'marron': return 'brown';
        case 'beige': return '#E8DCCA';
        case 'gris': return 'gray';
        default: return 'white';
    }
}
</script>