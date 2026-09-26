<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import {
    BookOpen,
    FolderGit2,
    LayoutGrid,
    Building2,
    Target,
    Network,
    Users,
} from 'lucide-vue-next'

import AppLogo from '@/components/AppLogo.vue'
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'

import { dashboard } from '@/routes'
import type { NavItem } from '@/types'

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },

    {
        title: 'Profil Perusahaan',
        icon: Building2,
        items: [
            {
                title: 'Tentang Kami',
                href: '/profil-perusahaan/tentang-kami',
                icon: Building2,
            },
            {
                title: 'Visi, Misi & Nilai',
                href: '/profil-perusahaan/visi-misi',
                icon: Target,
            },
            {
                title: 'Struktur Perusahaan',
                href: '/profil-perusahaan/struktur-perusahaan',
                icon: Network,
            },
            {
                title: 'Anak Usaha',
                href: '/profil-perusahaan/anak-usaha',
                icon: Users,
            },
        ],
    },
]

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
]
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>
