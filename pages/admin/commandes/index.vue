<script setup>
import {ref, h, onMounted} from "vue";
import '~/css/admin.css'
definePageMeta({
  layout: 'admin'
})

const columns = [
  { header: 'ID', accessorKey: 'id_commande' },
  { header: 'Client', accessorKey: 'client' },
  { header: 'Date', accessorKey: 'created_at' },
  { header: 'Statut', accessorKey: 'statut' },
  { header: 'Produits', accessorKey: 'produits_list' }
]

const rawData = ref([])
const data = ref([])
const expandedOrders = ref({})
const loading = ref(false)
const processingOrders = ref({})

function toggleExpand(orderId) {
  expandedOrders.value[orderId] = !expandedOrders.value[orderId]
}

async function fetchCommandes() {
  loading.value = true
  try {
    const response = await useFetch('http://localhost/SneakMe/api/commandes.php')
    rawData.value = response.data.value
    
    data.value = rawData.value.map(commande => {
      if (expandedOrders.value[commande.id_commande] === undefined) {
        expandedOrders.value[commande.id_commande] = false
      }
      
      let statut = 'En cours'
      if (commande.terminer == 1) statut = 'Terminée'
      if (commande.terminer == -1) statut = 'Annulée'
      
      return {
        id_commande: commande.id_commande,
        client: `${commande.prenom} ${commande.nom}`,
        created_at: new Date(commande.created_at).toLocaleDateString(),
        statut: statut,
        terminer: commande.terminer,
        produits_list: commande.produits.map(p => p.title).join(', '),
        produits: commande.produits
      }
    })
  } catch (error) {
    console.error("Erreur lors du chargement des commandes:", error)
    alert("Erreur lors du chargement des commandes")
  } finally {
    loading.value = false
  }
}

async function updateStatut(id, terminer, newValue) {
  if (processingOrders.value[id]) return
  
  processingOrders.value[id] = true
  try {
    const response = await $fetch(`http://localhost/SneakMe/api/commandes.php?id=${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ terminer: newValue !== undefined ? newValue : (terminer == 1 ? 0 : 1) })
    })
    
    if (response.success) {
      await fetchCommandes()
    } else {
      console.error("Erreur lors de la mise à jour du statut:", response.message)
      alert("Erreur lors de la mise à jour du statut: " + response.message)
    }
  } catch (error) {
    console.error("Erreur lors de la mise à jour du statut:", error)
    alert("Erreur de connexion à l'API")
  } finally {
    processingOrders.value[id] = false
  }
}

function annulerCommande(id) {
  if (confirm("Êtes-vous sûr de vouloir annuler cette commande ?")) {
    updateStatut(id, null, -1)
  }
}

onMounted(() => {
  fetchCommandes()
})
</script>

<template>
  <div class="container">
    <h1 class="title">Liste des commandes</h1>
    
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Chargement des commandes...</p>
    </div>
    
    <div v-else class="commandes-list">
      <div v-for="commande in data" :key="commande.id_commande" class="commande-card">
        <div class="commande-header">
          <div>
            <span class="commande-id">Commande #{{ commande.id_commande }}</span>
            <span :class="['commande-statut', 
              commande.statut === 'Terminée' ? 'termine' : 
              commande.statut === 'Annulée' ? 'annulee' : 'en-cours']">
              {{ commande.statut }}
            </span>
          </div>
          <div class="commande-client">
            <strong>Client:</strong> {{ commande.client }}
          </div>
          <div class="commande-date">
            <strong>Date:</strong> {{ commande.created_at }}
          </div>
          <div class="commande-actions">
            <button class="icon-btn" @click="toggleExpand(commande.id_commande)" :title="expandedOrders[commande.id_commande] ? 'Masquer les produits' : 'Voir les produits'">
              <i :class="expandedOrders[commande.id_commande] ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
            
            <template v-if="commande.terminer != -1">
              <button 
                :class="['icon-btn', commande.statut === 'Terminée' ? 'btn-annuler' : 'btn-terminer']"
                @click="updateStatut(commande.id_commande, commande.terminer)"
                :disabled="processingOrders[commande.id_commande]"
                :title="commande.statut === 'Terminée' ? 'Marquer comme en cours' : 'Marquer comme terminée'"
              >
                <i v-if="!processingOrders[commande.id_commande]" :class="commande.statut === 'Terminée' ? 'fas fa-times' : 'fas fa-check'"></i>
                <i v-else class="fas fa-spinner fa-spin"></i>
              </button>
              
              <button 
                class="icon-btn btn-cancel"
                @click="annulerCommande(commande.id_commande)"
                :disabled="processingOrders[commande.id_commande]"
                title="Annuler la commande"
              >
                <i v-if="!processingOrders[commande.id_commande]" class="fas fa-trash-alt"></i>
                <i v-else class="fas fa-spinner fa-spin"></i>
              </button>
            </template>
          </div>
        </div>
        
        <div v-if="expandedOrders[commande.id_commande]" class="commande-produits">
          <h3>Produits</h3>
          <div class="produits-grid">
            <div v-for="produit in commande.produits" :key="produit.id_produit" class="produit-item">
              <img :src="produit.url_image" :alt="produit.title" class="produit-image">
              <div class="produit-details">
                <div class="produit-title">{{ produit.title }}</div>
                <div class="produit-price">{{ produit.price }} €</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 50px 0;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.commandes-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 20px;
}

.commande-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  background-color: white;
}

.commande-header {
  padding: 15px;
  background-color: #f8f9fa;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  border-bottom: 1px solid #ddd;
}

.commande-id {
  font-weight: bold;
  margin-right: 10px;
}

.commande-statut {
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.9em;
}

.termine {
  background-color: #d4edda;
  color: #155724;
}

.en-cours {
  background-color: #fff3cd;
  color: #856404;
}

.annulee {
  background-color: #f8d7da;
  color: #721c24;
}

.commande-produits {
  padding: 15px;
}

.commande-produits h3 {
  margin-top: 0;
  margin-bottom: 10px;
  font-size: 1.1em;
}

.produits-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 15px;
}

.produit-item {
  display: flex;
  flex-direction: column;
  border: 1px solid #eee;
  border-radius: 5px;
  overflow: hidden;
}

.produit-image {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.produit-details {
  padding: 10px;
}

.produit-title {
  font-weight: bold;
  margin-bottom: 5px;
}

.produit-price {
  color: #e63946;
  font-weight: bold;
}

.commande-actions {
  display: flex;
  gap: 10px;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  transition: all 0.2s ease;
}

.icon-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.icon-btn:first-child {
  background-color: #007bff;
}

.btn-terminer {
  background-color: #28a745;
}

.btn-annuler {
  background-color: #dc3545;
}

.btn-cancel {
  background-color: #6c757d;
}

.icon-btn:first-child:hover:not(:disabled) {
  background-color: #0069d9;
}

.btn-terminer:hover:not(:disabled) {
  background-color: #218838;
}

.btn-annuler:hover:not(:disabled) {
  background-color: #c82333;
}

.btn-cancel:hover:not(:disabled) {
  background-color: #5a6268;
}
</style>

