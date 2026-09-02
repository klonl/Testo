import WebFont from 'webfontloader'

WebFont.load({
    google: {
        families: ['Tektur']
    },

    loading() {
        console.log('Шрифты начали загружаться')
    },

    active() {
        console.log('✅ Шрифты успешно загружены')
    },

    inactive() {
        console.error('❌ Ошибка загрузки шрифтов')
    }
})
