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
                    @click="navigateTo('/login')"
                >
                    Se connecter
                </UButton>
                <UButton
                    class="w-[calc(50%-7.5px)]"
                    variant="soft"
                    color="primary"
                    icon="i-heroicons-user-plus"
                    @click="navigateTo('/register')"
                >
                    S'inscrire
                </UButton>
            </div>
        </div>
        <div v-else>
            <p>Bonjour {{ user.name }}</p>
        </div>
    </div>
</template>

<script setup>
const dataUser = localStorage.getItem('userSneakMe');
const user = ref(dataUser ? JSON.parse(dataUser) : null);

</script>