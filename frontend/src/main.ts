import { createApp } from 'vue'
import App from './App.vue'

import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import { router } from './router/index'

//Définition du thème personnalisé
const customTheme = {
  dark: false,
  colors: {
    background: '#F5F7FB',
    surface: '#FFFFFF',
    primary: '#1E88E5',  
    secondary: '#43A047', 
    accent: '#FFB300',    
    error: '#E53935',
    info: '#039BE5',
    success: '#43A047',
    warning: '#FB8C00',
  },
}

// Création de l'instance Vuetify avec le thème
const vuetify = createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'customTheme',
    themes: {
      customTheme,
    },
  },
})

createApp(App)
  .use(router)
  .use(vuetify)
  .mount('#app')
