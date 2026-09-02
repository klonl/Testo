import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import '@mdi/font/css/materialdesignicons.css'
import {aliases, custom} from "@/plugins/customIcons/custom.js";
import { tabler } from './tabler-icons'
import {en, ru} from "vuetify/locale";

export default createVuetify({
    utilities: false,
    components,
    directives,
    locale: {
        locale: 'ru',        // Язык по умолчанию
        fallback: 'en',      // Язык, если перевода для ключа нет
        messages: { ru, en }, // Регистрируем загруженные файлы
    },
    theme: {
        layers: true,
        defaultTheme: 'dark',
        themes: {
            light: {
                colors: {
                    primary: '#e68300',
                    secondary: '#424242',
                    accent: '#82B1FF',
                    error: '#FF5252',
                    info: '#2196F3',
                    success: '#4CAF50',
                    warning: '#FFC107',
                },
            },
            dark: {
                colors: {
                    primary: '#e68300',
                    secondary: '#151720',
                    secondaryWhite: '#7378a2',
                    accent: '#82B1FF',
                    error: '#FF5252',
                    info: '#2196F3',
                    success: '#4CAF50',
                    warning: '#FFC107',
                },
            },
        },
    },
    icons: {
        defaultSet: 'mdi', // This is already the default value - only for display purposes
        aliases,              // Передаём алиасы
        sets: {
            tabler,
            custom,             // Регистрируем наш кастомный набор
        },
    },
    defaults: {
        VBtn: {
            color: 'primary',
        },
        VTextField: {
            color: 'primary',
            variant: 'outlined',
        },
        VDataTableServer: {
            itemsPerPage: 10,
            itemsPerPageOptions: [10, 20, 50, 100],
        },
    },
});
