<template>
  <div class="flex h-screen items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
      <UCard class="p-6 pt-2">
        <div class="text-center pb-4">
          <h2 class="text-3xl font-bold tracking-tight text-gray-900">
            Connexion Admin
          </h2>
        </div>
        <UForm :state="formState" @submit="login">
          <UFormGroup label="Adresse email">
            <UInput 
              v-model="formState.email" 
              type="email" 
              placeholder="Entrez votre email"
              autocomplete="email"
              required
            />
          </UFormGroup>
          
          <UFormGroup label="Mot de passe" class="mt-4">
            <UInput 
              v-model="formState.password" 
              type="password" 
              placeholder="Entrez votre mot de passe"
              autocomplete="current-password"
              required
            />
          </UFormGroup>
          
          <div class="mt-6">
            <UButton type="submit" color="primary" block :loading="loading">
              Connexion
            </UButton>
          </div>
        </UForm>
      </UCard>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const formState = ref({
  email: '',
  password: ''
})
const loading = ref(false)

const login = async () => {
    console.log(formState.value)
  loading.value = true
  try {
    const response = await fetch('http://localhost/sneakme/api/verify_admin.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: formState.value.email,
        password: formState.value.password,
      }),
    })
    const data = await response.json()
    
    if (data.success) {
      localStorage.setItem('admin_token', JSON.stringify({ email: formState.value.email, password: formState.value.password }))
      router.push('/admin/commandes')
    } else {
      console.log(data.message || 'Identifiants incorrects')
    }
  } catch (error) {
    console.error('Une erreur est survenue lors de la connexion:', error)
  } finally {
    loading.value = false
  }
}
</script>
