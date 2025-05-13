<template>
    <div>
        <div v-if="!user" class="flex flex-col gap-2">
            Vous n'êtes pas connecté, veuillez vous connecter ou vous inscrire.
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
        <div v-else>
            <p>Bonjour {{ user.username }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
const user = ref(null);
const emit = defineEmits(['send-message']);

const userLS = localStorage.getItem('user_sneakme');
if (userLS) {
    user.value = JSON.parse(userLS);
    console.log('user', user.value);
}

</script>