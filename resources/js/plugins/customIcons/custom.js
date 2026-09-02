import { h } from 'vue'

import VkIcon from '@/plugins/customIcons/icons/vk.vue'
import TgIcon from '@/plugins/customIcons/icons/telegram.vue'

const aliases = {
    vk: 'custom:vk',
    tg: 'custom:tg',
}


// 2. Карта соответствия строкового ключа → Vue-компонент
const iconComponentMap = {
    'vk': VkIcon,
    'tg': TgIcon,
}

const custom = {
    component: (props) => {

        const Component = iconComponentMap[props.icon]

        if (!Component) {
            console.warn(`Иконка "${props.icon}" не найдена в кастомном наборе`)
            return h('span') // или можно вернуть заглушку
        }

        return h(Component, {
            name: props.icon,

            size: props.size,
            color: props.color,
            class: props.class,
        })
    },
}

export { aliases, custom }
