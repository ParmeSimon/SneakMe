<script setup>
definePageMeta({
  layout: 'connection'
})

const sneakmeData = JSON.parse(localStorage.getItem('sneakmedata'))

if (sneakmeData) {
    const res = await $fetch('/api/auth/login', {
      method: 'POST',
      body: sneakmeData
    })
    if (res.success) {
      navigateTo('/admin')
    }
    
}

</script>
<template>

</template>