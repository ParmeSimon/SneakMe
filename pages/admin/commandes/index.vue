<script setup>
import {ref, h, onMounted} from "vue";
import CommandeStatusModal from '~/components/modal/CommandeStatusModal.vue';
import '~/assets/css/admin.css'
definePageMeta({
  layout: 'admin'
})

import { onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
let loadingAuth = ref(true)

async function checkAuth() {
    const adminTokenStr = localStorage.getItem('admin_token')
    if (!adminTokenStr) {
        console.log('Pas de token admin trouvé')
        return false
    }
    
    try {
        const adminToken = JSON.parse(adminTokenStr)
        
        // Affichage des données pour débogage
        console.log('Données envoyées:', { email: adminToken.email, password: adminToken.password })
        
        // Utilisation de la nouvelle API simplifiée pour la vérification d'admin
        const response = await fetch('http://localhost/sneakme/api/verify_admin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: adminToken.email,
                password: adminToken.password
            })
        })
        
        // Vérifier le statut de la réponse
        console.log('Statut de la réponse:', response.status)
        
        const data = await response.json()
        
        // Afficher toutes les données de réponse pour débogage
        console.log('Réponse complète:', data)
        
        if (data.success) {
            console.log('Authentification réussie')
            return true
        } else {
            console.log('Authentification échouée:', data.message || data.error)
            return false
        }
    } catch (error) {
        // Capturer et afficher les erreurs
        console.error('Erreur lors de la requête:', error)
        return false
    }
}

onBeforeMount(async () => {
    // Attendre le résultat de checkAuth avant de décider où rediriger
    const isAuthenticated = await checkAuth()
    console.log('Résultat de checkAuth:', isAuthenticated)
    
    if (isAuthenticated) {
        loadingAuth.value = false
    } else {
        router.push('/admin/login')
    }
})

const rawData = ref([])
const data = ref([])
const loading = ref(false)
const processingOrders = ref({})
const statusModalOpen = ref(false)
const selectedCommande = ref(null)

async function fetchCommandes() {
  loading.value = true
  try {
    // Ajouter un timestamp pour éviter le cache et les problèmes CORS
    const response = await fetch(`http://localhost/sneakme/api/commandes.php?t=${new Date().getTime()}`)
    
    if (response.ok) {
      const responseData = await response.json()
      console.log('Données de commandes reçues:', responseData)
      
      // Vérifier si responseData est un tableau
      if (Array.isArray(responseData)) {
        rawData.value = responseData
        
        data.value = rawData.value.map(commande => {
          
          let statut = 'En cours'
          if (commande.state == 'Terminée') statut = 'Terminée'
          if (commande.state == 'Annulée') statut = 'Annulée'
          
          return {
            id_commande: commande.id,
            client: `${commande.prenom} ${commande.nom}`,
            created_at: new Date(commande.created_at).toLocaleDateString(),
            statut: statut,
            state: commande.state,
            produits_list: commande.produits && Array.isArray(commande.produits) ? commande.produits.map(p => p.title).join(', ') : '',
            produits: commande.produits || []
          }
        })
      } else {
        // Si ce n'est pas un tableau, initialiser avec un tableau vide
        console.error("La réponse de l'API n'est pas un tableau:", responseData)
        rawData.value = []
        data.value = []
      }
    } else {
      console.error("Erreur HTTP lors du chargement des commandes:", response.status)
      alert("Erreur lors du chargement des commandes: " + response.statusText)
    }
  } catch (error) {
    console.error("Erreur lors du chargement des commandes:", error)
    alert("Erreur lors du chargement des commandes")
  } finally {
    loading.value = false
  }
}

async function updateStatut(id, oldState, newValue) {
  if (processingOrders.value[id]) return
  
  processingOrders.value[id] = true
  try {
    // Utiliser des paramètres d'URL pour envoyer les données au lieu du corps JSON
    const response = await fetch(`http://localhost/sneakme/api/commandes.php?id=${id}&state=${encodeURIComponent(newValue)}&t=${new Date().getTime()}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      }
      // Pas de corps JSON, les données sont dans l'URL
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
      await fetchCommandes()
    } else {
      console.error("Erreur lors de la mise à jour du statut:", result.message || response.statusText)
      alert("Erreur lors de la mise à jour du statut: " + (result.message || response.statusText))
    }
  } catch (error) {
    console.error("Erreur lors de la mise à jour du statut:", error)
    alert("Erreur de connexion à l'API")
  } finally {
    processingOrders.value[id] = false
  }
}

function openStatusModal(commande) {
  selectedCommande.value = commande
  statusModalOpen.value = true
}

function handleStatusUpdate(data) {
  updateStatut(data.id, null, data.status)
  statusModalOpen.value = false
}

function closeStatusModal() {
  statusModalOpen.value = false
}

onMounted(() => {
  fetchCommandes()
})
</script>

<template>
  <div v-if="loadingAuth" class="flex justify-center items-center h-screen w-screen">
    <span class="loader"></span>
  </div>
  <div v-else class="container">
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
            <button 
              class="icon-btn btn-status"
              @click="openStatusModal(commande)"
              :disabled="processingOrders[commande.id_commande]"
              title="Modifier le statut"
            >
              <UIcon v-if="!processingOrders[commande.id_commande]" name="i-heroicons-pencil-square" />
              <UIcon v-else name="i-heroicons-arrow-path" class="animate-spin" />
            </button>
          </div>
        </div>
        
        <div class="commande-produits">
          <h3>Produits</h3>
          <div class="produits-grid">
            <div v-for="produit in commande.produits" :key="produit.id_produit" class="produit-item">
              <img :src="produit.url_image" :alt="produit.title" class="produit-image">
              <div class="produit-details">
                <div class="produit-title">{{ produit.title }}</div>
                <div class="produit-price">{{ produit.price }} €</div>
                <div class="produit-info">
                  <div class="info-item" v-if="produit.categorie">
                    <span class="info-label">Catégorie:</span>
                    <span class="info-value">{{ produit.categorie }}</span>
                  </div>
                  <div class="info-item" v-if="produit.couleur">
                    <span class="info-label">Couleur:</span>
                    <span class="info-value">{{ produit.couleur }}</span>
                  </div>
                  <div class="info-item" v-if="produit.pointure">
                    <span class="info-label">Pointure:</span>
                    <span class="info-value">{{ produit.pointure }}</span>
                  </div>
                  <div class="info-item" v-if="produit.sexe">
                    <span class="info-label">Sexe:</span>
                    <span class="info-value">{{ produit.sexe }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Modal pour modifier le statut de la commande -->
  <CommandeStatusModal 
    :is-open="statusModalOpen" 
    :commande="selectedCommande" 
    @close="closeStatusModal" 
    @update="handleStatusUpdate"
  />
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
  font-weight: bold;
  color: #3498db;
  margin-bottom: 8px;
}

.produit-info {
  margin-top: 8px;
  font-size: 0.9rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.info-item {
  display: flex;
  flex-direction: column;
  background-color: #f8f9fa;
  padding: 6px;
  border-radius: 4px;
}

.info-label {
  font-weight: 600;
  color: #555;
  font-size: 0.8rem;
}

.info-value {
  color: #333;
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

.btn-status {
  background-color: #4299e1;
  color: white;
}

.btn-status:hover {
  background-color: #3182ce;
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

