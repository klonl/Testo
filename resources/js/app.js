import './bootstrap'
import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from 'ziggy-js'
import vuetify from '@/plugins/vuetify'
import { createPinia } from 'pinia'
import axios from 'axios'

console.log('ss')
window.axios = axios

window.axios.defaults.timeout = 0
const pinia = createPinia()
const appName = import.meta.env.APP_NAME || 'ESNOW'

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob('./pages/**/*.vue')
        ),
    progress: false,
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(vuetify)
            .use(pinia)

        vueApp.mount(el)
    },
})
