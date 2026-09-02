import { h } from 'vue'
import * as icons from '@tabler/icons-vue'

function toTablerComponentName(raw = '') {
    const name = raw
        .replace(/^tabler:/, '')
        .replace(/^tabler-/, '')

    const pascal = name
        .split('-')
        .filter(Boolean)
        .map(part => part.charAt(0).toUpperCase() + part.slice(1))
        .join('')

    return `Icon${pascal}`
}

export const tabler = {
    component: (props) => {
        const rawIcon =
            typeof props.icon === 'string'
                ? props.icon
                : props.icon?.icon || ''

        const componentName = toTablerComponentName(rawIcon)
        const IconComponent = icons[componentName]

        if (!IconComponent) {
            console.warn('Tabler icon not found:', rawIcon, componentName)
            return null
        }

        return h(IconComponent, {
            size: props.size ?? 24,
            stroke: 2,
        })
    },
}
