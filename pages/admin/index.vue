<template>
    <!-- <template v-if="loading">
        <div class="flex justify-center items-center h-screen w-screen">
            <span class="loader"></span>
        </div>
    </template>
    <template v-else>
        <div>
            <h1>Page Admin</h1>
        </div>
    </template> -->
</template>

<script setup>
import { onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// let loading = ref(true)

// Fonction asynchrone pour vérifier l'authentification
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
        router.push('/admin/commandes')
    } else {
        router.push('/admin/login')
    }
})
</script>

<style scoped>
.loader {
  width: 48px;
  height: 48px;
  display: inline-block;
  position: relative;
  background: #FFF;
  box-sizing: border-box;
  animation: flipX 1s linear infinite;
}

@keyframes flipX {
  0% {
    transform: perspective(200px) rotateX(0deg) rotateY(0deg);
  }
  50% {
    transform: perspective(200px) rotateX(-180deg) rotateY(0deg);
  }
  100% {
    transform: perspective(200px) rotateX(-180deg) rotateY(-180deg);
  }
}
    
</style>