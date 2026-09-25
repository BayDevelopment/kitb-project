<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ChevronDown } from 'lucide-vue-next'

import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar'

import { useCurrentUrl } from '@/composables/useCurrentUrl'
import type { NavItem } from '@/types'

defineProps<{
    items: NavItem[]
}>()

const { isCurrentUrl } = useCurrentUrl()

const openMenus = ref<string[]>([])

const hasActiveChild = (item: NavItem): boolean => {
    return item.items?.some((child) => {
        if (child.href && isCurrentUrl(child.href)) {
            return true
        }

        return child.items?.length
            ? hasActiveChild(child)
            : false
    }) ?? false
}

const toggleMenu = (title: string) => {
    if (openMenus.value.includes(title)) {
        openMenus.value = openMenus.value.filter(
            (item) => item !== title,
        )
    } else {
        openMenus.value.push(title)
    }
}
</script>

<template>
    <SidebarGroup class="relative overflow-hidden px-2 py-0">

        <!-- BLOBS BACKGROUND -->
        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
        >
            <!-- Blob kiri atas -->
            <div
                class="absolute -left-16 top-4 h-32 w-32 rounded-full
                       bg-blue-500/10 blur-3xl
                       transition-opacity duration-500
                       dark:bg-blue-400/10"
            />

            <!-- Blob kanan -->
            <div
                class="absolute -right-20 top-24 h-40 w-40 rounded-full
                       bg-cyan-500/8 blur-3xl
                       dark:bg-cyan-400/8"
            />

            <!-- Blob bawah -->
            <div
                class="absolute -bottom-16 left-8 h-36 w-36 rounded-full
                       bg-indigo-500/8 blur-3xl
                       dark:bg-indigo-400/8"
            />
        </div>

        <!-- CONTENT -->
        <div class="relative z-10">

            <SidebarGroupLabel
                class="mb-2 px-2 text-xs font-semibold uppercase tracking-wider
                       text-slate-500 dark:text-slate-400"
            >
                Platform
            </SidebarGroupLabel>

            <SidebarMenu>

                <SidebarMenuItem
                    v-for="item in items"
                    :key="item.title"
                >

                    <!-- MENU DENGAN SUBMENU -->
                    <template v-if="item.items?.length">

                        <SidebarMenuButton
                            :is-active="hasActiveChild(item)"
                            :tooltip="item.title"
                            class="
                                transition-all duration-200
                                hover:bg-blue-500/10
                                hover:text-blue-700
                                hover:shadow-[0_0_18px_rgba(59,130,246,0.08)]
                                dark:hover:bg-blue-400/10
                                dark:hover:text-blue-300
                            "
                            @click="toggleMenu(item.title)"
                        >
                            <component
                                :is="item.icon"
                                v-if="item.icon"
                                class="transition-colors duration-200"
                            />

                            <span>{{ item.title }}</span>

                            <ChevronDown
                                class="ml-auto size-4 transition-all duration-200"
                                :class="{
                                    'rotate-180 text-blue-600 dark:text-blue-400':
                                        openMenus.includes(item.title) ||
                                        hasActiveChild(item),
                                }"
                            />
                        </SidebarMenuButton>

                        <SidebarMenuSub
                            v-show="
                                openMenus.includes(item.title) ||
                                hasActiveChild(item)
                            "
                            class="border-blue-500/20 dark:border-blue-400/20"
                        >
                            <SidebarMenuSubItem
                                v-for="child in item.items"
                                :key="child.title"
                            >
                                <SidebarMenuSubButton
                                    v-if="child.href"
                                    as-child
                                    :is-active="isCurrentUrl(child.href)"
                                    class="
                                        transition-all duration-200
                                        hover:bg-blue-500/10
                                        hover:text-blue-700
                                        hover:translate-x-0.5
                                        dark:hover:bg-blue-400/10
                                        dark:hover:text-blue-300
                                    "
                                >
                                    <Link :href="child.href">
                                        <component
                                            :is="child.icon"
                                            v-if="child.icon"
                                            class="transition-colors duration-200"
                                        />

                                        <span>{{ child.title }}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>

                    </template>

                    <!-- MENU BIASA -->
                    <SidebarMenuButton
                        v-else-if="item.href"
                        as-child
                        :is-active="isCurrentUrl(item.href)"
                        :tooltip="item.title"
                        class="
                            transition-all duration-200
                            hover:bg-blue-500/10
                            hover:text-blue-700
                            hover:shadow-[0_0_18px_rgba(59,130,246,0.08)]
                            dark:hover:bg-blue-400/10
                            dark:hover:text-blue-300
                        "
                    >
                        <Link :href="item.href">
                            <component
                                :is="item.icon"
                                v-if="item.icon"
                                class="transition-colors duration-200"
                            />

                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>

                </SidebarMenuItem>

            </SidebarMenu>
        </div>
    </SidebarGroup>
</template>
