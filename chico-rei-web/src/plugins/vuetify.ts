import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

export default createVuetify({
  components,
  directives,
  icons: { defaultSet: 'mdi' },
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        dark: false,
        colors: {
          background: '#FFFFFF',
          surface: '#FFFFFF',
          primary: '#111111',
          secondary: '#6B6B6B',
          accent: '#E4572E',
          error: '#C62828',
          success: '#2E7D32',
          warning: '#ED8B00',
          info: '#1565C0',
        },
      },
    },
  },
  defaults: {
    VBtn: { rounded: 'sm', elevation: 0, class: 'text-none font-weight-medium' },
    VCard: { rounded: 'lg', elevation: 0 },
    VTextField: { variant: 'outlined', density: 'comfortable', color: 'primary' },
    VTextarea: { variant: 'outlined', density: 'comfortable', color: 'primary' },
    VSelect: { variant: 'outlined', density: 'comfortable', color: 'primary' },
    VFileInput: { variant: 'outlined', density: 'comfortable', color: 'primary' },
  },
})
