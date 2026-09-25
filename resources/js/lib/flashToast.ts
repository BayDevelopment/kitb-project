import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import type { FlashToast } from '@/types/ui'

let lastMessage = ''
let lastShownAt = 0

export function initializeFlashToast(): void {
    router.on('success', (event) => {
        const flash = event.detail.page.props.flash as
            | { toast?: FlashToast }
            | undefined

        const data = flash?.toast

        if (!data?.message) {
            return
        }

        const now = Date.now()

        if (
            data.message === lastMessage &&
            now - lastShownAt < 1000
        ) {
            return
        }

        lastMessage = data.message
        lastShownAt = now

        toast[data.type](data.message)
    })
}
