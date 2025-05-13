<script setup>
import {ref, h, onMounted} from "vue";
import '~/css/admin.css'
import '~/css/commandes.css'
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
const processingOrders = ref({})

function toggleExpand(orderId) {
  expandedOrders.value[orderId] = !expandedOrders.value[orderId]
}

async function fetchCommandes() {
  try {
    const response = await fetch('http://localhost/SneakMe/api/commandes.php')
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    rawData.value = await response.json()
    
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
  }
}

async function updateStatut(id, terminer, newValue) {
  if (processingOrders.value[id]) return
  
  processingOrders.value[id] = true
  try {
    const response = await fetch(`http://localhost/SneakMe/api/commandes.php?id=${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ terminer: newValue !== undefined ? newValue : (terminer == 1 ? 0 : 1) })
    })
    
    const result = await response.json()
    
    if (response.ok && result.success) {
      await fetchCommandes()
    } else {
      console.error("Erreur lors de la mise à jour du statut:", result.message)
      alert("Erreur lors de la mise à jour du statut: " + result.message)
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
    <div class="commandes-list">
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
                <div class="produit-price">{{ produit.price/100 }} €</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


