// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',
  devtools: { enabled: true },
  modules: [
    'shadcn-nuxt',
    '@pinia/nuxt',
    '@nuxt/ui',
    '@nuxtjs/color-mode',
  ],
  shadcn: {
    prefix: 'Ui',
    componentDir: './components/ui'
  },
  // Ajout de la configuration du routeur
  pages: true,
  experimental: {
    payloadExtraction: false
  }
})
