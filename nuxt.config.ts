// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',
  devtools: { enabled: false },
  css: [
    '~/assets/css/colors.css',
  ],
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
  },
  ssr: false,
  ui: {
    primary: 'blue',
    secondary: 'purple',
    colors: {
      primary: {
        50: '239 246 255',
        100: '219 234 254',
        200: '191 219 254',
        300: '147 197 253',
        400: '96 165 250',
        500: '59 130 246',
        600: '37 99 235',
        700: '29 78 216',
        800: '30 64 175',
        900: '30 58 138',
        950: '23 37 84'
      },
      secondary: {
        50: '250 245 255',
        100: '243 232 255',
        200: '233 213 255',
        300: '216 180 254',
        400: '192 132 252',
        500: '168 85 247',
        600: '147 51 234',
        700: '126 34 206',
        800: '107 33 168',
        900: '88 28 135',
        950: '59 7 100'
      }
    }
  }
})
