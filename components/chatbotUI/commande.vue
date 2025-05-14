<template>
    <div class="commande-container">
        <!-- Affichage si l'utilisateur n'est pas connecté -->
        <div v-if="!user" class="flex flex-col gap-2">
            <p class="text-gray-700 dark:text-gray-300 mb-3">Vous n'êtes pas connecté, veuillez vous connecter ou vous inscrire pour voir vos commandes.</p>
            <div class="flex gap-[15px]">
                <UButton
                    class="w-[calc(50%-7.5px)]"
                    variant="solid"
                    color="primary"
                    icon="i-heroicons-user"
                    @click="() => emit('send-message', 'login')"
                >
                    Se connecter
                </UButton>
                <UButton
                    class="w-[calc(50%-7.5px)]"
                    variant="soft"
                    color="primary"
                    icon="i-heroicons-user-plus"
                    @click="() => emit('send-message', 'register')"
                >
                    S'inscrire
                </UButton>
            </div>
        </div>

        <!-- Affichage si l'utilisateur est connecté -->
        <div v-else class="user-commandes">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Bonjour {{ user.username }}, voici vos commandes</h3>
                <UBadge v-if="commandes.length > 0" color="primary">{{ commandes.length }} commande(s)</UBadge>
            </div>

            <!-- État de chargement -->
            <div v-if="loading" class="flex justify-center items-center py-8">
                <UIcon name="i-heroicons-arrow-path" class="animate-spin h-8 w-8 text-primary-500" />
                <span class="ml-2 text-gray-600 dark:text-gray-400">Chargement de vos commandes...</span>
            </div>

            <!-- Message si aucune commande -->
            <div v-else-if="commandes.length === 0" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 text-center">
                <UIcon name="i-heroicons-shopping-bag" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                <p class="text-gray-600 dark:text-gray-400">Vous n'avez pas encore passé de commande.</p>
                <UButton 
                    class="mt-4" 
                    variant="soft" 
                    color="primary"
                    @click="() => emit('send-message', 'boutique')"
                >
                    Découvrir nos produits
                </UButton>
            </div>

            <!-- Liste des commandes -->
            <div v-else class="space-y-4">
                <UCard 
                    v-for="commande in commandes" 
                    :key="commande.id"
                    class="commande-card"
                >
                    <template #header>
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="font-semibold">Commande #{{ commande.id }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
                                    {{ formatDate(commande.created_at) }}
                                </span>
                            </div>
                            <UBadge :color="getStatusColor(commande.state)">
                                {{ commande.state }}
                            </UBadge>
                        </div>
                    </template>

                    <!-- Contenu de la commande -->
                    <div class="space-y-3">
                        <!-- Liste des produits -->
                        <div v-for="item in commande.produits" :key="item.item_id" class="flex items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div class="flex-shrink-0 w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded overflow-hidden mr-3">
                                <img 
                                    v-if="item.url_image" 
                                    :src="item.url_image" 
                                    :alt="item.title" 
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-600">
                                    <UIcon name="i-heroicons-photo" class="h-8 w-8" />
                                </div>
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-medium">{{ item.title }}</h4>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <UBadge size="xs" color="gray">{{ item.categorie }}</UBadge>
                                    <UBadge size="xs" color="gray">{{ item.couleur }}</UBadge>
                                    <UBadge size="xs" color="gray">Pointure: {{ item.pointure }}</UBadge>
                                    <UBadge size="xs" color="gray">{{ item.sexe }}</UBadge>
                                </div>
                            </div>
                            <div class="flex-shrink-0 font-medium">
                                {{ formatPrice(item.price) }}
                            </div>
                        </div>

                        <!-- Total de la commande -->
                        <div class="flex justify-between items-center pt-2 font-semibold">
                            <span>Total</span>
                            <span>{{ formatPrice(calculateTotal(commande.produits)) }}</span>
                        </div>
                    </div>

                    <!-- Actions sur la commande -->
                    <template #footer>
                        <div class="flex justify-end space-x-2">
                            <!-- Bouton pour voir le premier produit de la commande -->
                            <UButton 
                                v-if="commande.produits && commande.produits.length > 0"
                                color="primary" 
                                variant="soft" 
                                size="sm"
                                @click="() => emit('send-message', `Voir produit ${commande.produits[0].id_produit}`)"
                            >
                                Revoir le produit
                            </UButton>
                            
                            <!-- Boutons pour les autres produits -->
                            <template v-if="commande.produits && commande.produits.length > 1">
                                <UButton 
                                    v-for="(item, index) in commande.produits.slice(1)" 
                                    :key="index"
                                    color="gray" 
                                    variant="soft" 
                                    size="sm"
                                    class="ml-2"
                                    @click="() => emit('send-message', `Voir produit ${item.id_produit}`)"
                                >
                                    Produit {{ index + 2 }}
                                </UButton>
                            </template>
                        </div>
                    </template>
                </UCard>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const user = ref(null);
const commandes = ref([]);
const loading = ref(false);
const error = ref(null);
const cancellingOrder = ref(null);

const emit = defineEmits(['send-message']);

// Récupérer l'utilisateur depuis le localStorage
onMounted(() => {
    const userLS = localStorage.getItem('user_sneakme');
    if (userLS) {
        user.value = JSON.parse(userLS);
        fetchUserOrders();
    }
});

// Récupérer les commandes de l'utilisateur
async function fetchUserOrders() {
    if (!user.value || !user.value.id) return;
    
    loading.value = true;
    error.value = null;
    
    try {
        const response = await fetch(`http://localhost/sneakme/api/commandes.php?user_id=${user.value.id}`);
        if (!response.ok) {
            throw new Error('Erreur lors de la récupération des commandes');
        }
        
        const data = await response.json();
        commandes.value = data;
    } catch (err) {
        console.error('Erreur:', err);
        error.value = err.message;
    } finally {
        loading.value = false;
    }
}

// Annuler une commande
async function cancelOrder(orderId) {
    if (!confirm('Êtes-vous sûr de vouloir annuler cette commande ?')) return;
    
    cancellingOrder.value = orderId;
    
    try {
        const response = await fetch(`http://localhost/sneakme/api/commandes.php?id=${orderId}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) {
            throw new Error('Erreur lors de l\'annulation de la commande');
        }
        
        // Recharger les commandes après l'annulation
        await fetchUserOrders();
    } catch (err) {
        console.error('Erreur:', err);
        alert('Erreur lors de l\'annulation de la commande: ' + err.message);
    } finally {
        cancellingOrder.value = null;
    }
}

// Formater la date
function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}

// Formater le prix (conversion des centimes en euros)
function formatPrice(price) {
    // Convertir les centimes en euros (diviser par 100)
    const priceInEuros = parseFloat(price) / 100;
    
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(priceInEuros);
}

// Calculer le total d'une commande (les prix sont en centimes)
function calculateTotal(items) {
    // Vérifier que items est un tableau avant d'appeler reduce
    if (!items || !Array.isArray(items)) {
        return 0;
    }
    // Somme des prix en centimes
    const totalCents = items.reduce((total, item) => total + parseFloat(item.price), 0);
    // Pas besoin de convertir en euros ici car formatPrice s'en chargera
    return totalCents;
}

// Obtenir la couleur du badge en fonction du statut
function getStatusColor(status) {
    switch(status) {
        case 'En attente': return 'yellow';
        case 'En préparation': return 'blue';
        case 'Expédiée': return 'indigo';
        case 'Livrée': return 'green';
        case 'Annulée': return 'red';
        default: return 'gray';
    }
}

</script>