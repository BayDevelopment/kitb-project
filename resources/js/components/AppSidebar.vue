<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import {
    BookOpen,
    BriefcaseBusiness,
    Building,
    Building2,
    Construction,
    FolderGit2,
    Landmark,
    LayoutGrid,
    Map,
    MapPinned,
    Network,
    Newspaper,
    Route,
    Target,
    Users,
} from "lucide-vue-next";
import { computed } from "vue";

import AppLogo from "@/components/AppLogo.vue";
import NavFooter from "@/components/NavFooter.vue";
import NavMain from "@/components/NavMain.vue";
import NavUser from "@/components/NavUser.vue";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/ui/sidebar";
import { dashboard } from "@/routes";
import type { NavItem } from "@/types";

/* =========================================================
   AUTH
========================================================= */

interface AuthUser {
    name?: string;
    email?: string;
    email_verified_at?: string | null;
}

const page = usePage<{
    auth?: {
        user?: AuthUser;
    };
}>();

const isEmailVerified = computed(
    () => !!page.props.auth?.user?.email_verified_at,
);

/* =========================================================
   MENU
========================================================= */

const mainNavItems: NavItem[] = [
    {
        title: "Dashboard",
        href: dashboard(),
        icon: LayoutGrid,
    },

    {
        title: "Profil Perusahaan",
        icon: Building2,
        items: [
            {
                title: "Tentang Kami",
                href: "/profil-perusahaan/tentang-kami",
                icon: Building2,
            },
            {
                title: "Visi, Misi & Nilai",
                href: "/profil-perusahaan/visi-misi",
                icon: Target,
            },
            {
                title: "Struktur Perusahaan",
                href: "/profil-perusahaan/struktur-perusahaan",
                icon: Network,
            },
            {
                title: "Anak Usaha",
                href: "/profil-perusahaan/anak-usaha",
                icon: Users,
            },
        ],
    },

    {
        title: "Kawasan",
        icon: Map,
        items: [
            {
                title: "Profil Kawasan",
                href: "/kawasan/profil-kawasan",
                icon: Landmark,
            },
            {
                title: "Infrastruktur",
                href: "/kawasan/infrastruktur",
                icon: Construction,
            },
            {
                title: "Fasilitas",
                href: "/kawasan/fasilitas",
                icon: Building,
            },
            {
                title: "Peta Kawasan",
                href: "/kawasan/peta-kawasan",
                icon: MapPinned,
            },
        ],
    },

    {
        title: "Hubungan Investor",
        icon: BriefcaseBusiness,
        items: [
            {
                title: "Peluang Investasi",
                href: "/hubungan-investor/peluang-investasi",
                icon: BriefcaseBusiness,
            },
            {
                title: "Ease of Doing Business",
                href: "/hubungan-investor/ease-of-doing-business",
                icon: Landmark,
            },
            {
                title: "Kunjungan Lahan",
                href: "/hubungan-investor/kunjungan-lahan",
                icon: MapPinned,
            },
            {
                title: "Rute Pelayaran & Lokasi",
                href: "/hubungan-investor/rute-pelayaran-lokasi",
                icon: Route,
            },
        ],
    },
    {
        title: "Pusat Informasi",
        icon: Newspaper,
        items: [
            {
                title: "Berita",
                href: "/pusat-informasi/berita",
                icon: Newspaper,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: "Repository",
        href: "https://github.com/laravel/vue-starter-kit",
        icon: FolderGit2,
    },
    {
        title: "Documentation",
        href: "https://laravel.com/docs/starter-kits#vue",
        icon: BookOpen,
    },
];

/* =========================================================
   SKELETON
========================================================= */

const skeletonWidths = ["100%", "92%", "96%", "88%", "94%", "90%"];

const navSkeletonGroups = mainNavItems.map((item, groupIndex) => ({
    title: item.title,
    hasChildren: !!item.items?.length,
    titleWidth: groupIndex === 0 ? "6rem" : "8rem",
    children: (item.items ?? []).map((_, childIndex) => ({
        width: skeletonWidths[
            (groupIndex + childIndex) % skeletonWidths.length
        ],
    })),
}));
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="kitb-sidebar">
        <!-- HEADER -->
        <SidebarHeader class="border-b border-sidebar-border bg-transparent">
            <SidebarMenu class="relative z-10">
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="rounded-xl">
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <!-- CONTENT -->
        <SidebarContent
            class="kitb-sidebar-content relative flex min-h-0 flex-1 flex-col overflow-y-auto"
        >
            <div class="relative z-10 min-w-0 px-1 pb-6">
                <Transition name="kitb-nav-fade" mode="out-in">
                    <!-- Skeleton -->
                    <div
                        v-if="!isEmailVerified"
                        key="navigation-skeleton"
                        class="space-y-2 px-2 py-3"
                        role="status"
                        aria-live="polite"
                        aria-busy="true"
                    >
                        <span class="sr-only"> Memuat navigasi… </span>

                        <div
                            class="flex h-9 items-center gap-3 rounded-lg px-3"
                        >
                            <div
                                class="kitb-skeleton size-4 shrink-0 rounded-md"
                            />
                            <div class="kitb-skeleton h-4 w-24 rounded-md" />
                        </div>

                        <template
                            v-for="group in navSkeletonGroups.filter(
                                (g) => g.hasChildren,
                            )"
                            :key="group.title"
                        >
                            <div
                                class="mt-3 flex h-9 items-center gap-3 rounded-lg px-3"
                            >
                                <div
                                    class="kitb-skeleton size-4 shrink-0 rounded-md"
                                />
                                <div
                                    class="kitb-skeleton h-4 rounded-md"
                                    :style="{ width: group.titleWidth }"
                                />
                            </div>

                            <div class="space-y-2 pl-5">
                                <div
                                    v-for="(child, index) in group.children"
                                    :key="index"
                                    class="kitb-skeleton h-8 rounded-lg"
                                    :style="{ width: child.width }"
                                />
                            </div>
                        </template>
                    </div>

                    <!-- Navigation -->
                    <NavMain v-else key="navigation" :items="mainNavItems" />
                </Transition>
            </div>
        </SidebarContent>

        <!-- FOOTER -->
        <SidebarFooter class="border-t border-sidebar-border bg-transparent">
            <Transition name="kitb-nav-fade" mode="out-in">
                <div
                    v-if="!isEmailVerified"
                    key="footer-skeleton"
                    class="space-y-2 px-2 pb-2"
                    aria-hidden="true"
                >
                    <div class="flex h-8 items-center gap-3 px-3">
                        <div class="kitb-skeleton size-4 rounded-md" />
                        <div class="kitb-skeleton h-3.5 w-20 rounded-md" />
                    </div>

                    <div class="flex h-8 items-center gap-3 px-3">
                        <div class="kitb-skeleton size-4 rounded-md" />
                        <div class="kitb-skeleton h-3.5 w-24 rounded-md" />
                    </div>

                    <div
                        class="kitb-user-skeleton mt-2 flex h-11 items-center gap-3 rounded-lg border px-3"
                    >
                        <div class="kitb-skeleton size-7 rounded-full" />

                        <div class="flex-1 space-y-1.5">
                            <div class="kitb-skeleton h-3 w-20 rounded-md" />
                            <div class="kitb-skeleton h-2.5 w-28 rounded-md" />
                        </div>
                    </div>
                </div>

                <div v-else key="footer-navigation" class="min-w-0">
                    <NavFooter :items="footerNavItems" />
                    <NavUser />
                </div>
            </Transition>
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>

<!--
    Sengaja TIDAK memakai <style scoped>.
    Sidebar (shadcn) merender elemen di dalam komponen anak, bahkan di portal
    untuk mobile, sehingga selector scoped + :deep() tidak menjangkaunya dan
    styling light/dark jadi tidak konsisten. Semua selector di bawah diberi
    prefix unik supaya tidak bocor ke komponen lain.

    Warna menu/hover/aktif memakai token --sidebar-* dari app.css, jadi
    light/dark otomatis ikut class .dark di <html>.
-->
<style>
/* =========================================================
   VARIABEL KHUSUS SIDEBAR (light default, dark lewat .dark)
========================================================= */

:root {
    --ksb-glow: rgb(46 111 191 / 0.08);
    --ksb-glow-2: rgb(6 182 212 / 0.05);
    --ksb-from: #ffffff;
    --ksb-mid: #f6f8fb;
    --ksb-to: #eaf0f8;
    --ksb-scroll: rgb(100 116 139 / 0.28);
    --ksb-skeleton-a: #e2e8f0;
    --ksb-skeleton-b: #f1f5f9;
}

.dark {
    --ksb-glow: rgb(76 139 219 / 0.16);
    --ksb-glow-2: rgb(34 211 238 / 0.06);
    --ksb-from: #0c2040;
    --ksb-mid: #0a1930;
    --ksb-to: #081224;
    --ksb-scroll: rgb(148 163 184 / 0.25);
    --ksb-skeleton-a: #13284a;
    --ksb-skeleton-b: #1d3760;
}

/* =========================================================
   BACKGROUND (desktop + mobile sheet)
========================================================= */

.kitb-sidebar [data-sidebar="sidebar"],
[data-sidebar="sidebar"][data-mobile="true"] {
    background:
        radial-gradient(circle at 0% 0%, var(--ksb-glow), transparent 45%),
        radial-gradient(
            circle at 100% 100%,
            var(--ksb-glow-2),
            transparent 45%
        ),
        linear-gradient(
            180deg,
            var(--ksb-from) 0%,
            var(--ksb-mid) 55%,
            var(--ksb-to) 100%
        );
    color: var(--sidebar-foreground);
}

/* =========================================================
   SCROLLBAR
========================================================= */

:is(.kitb-sidebar, [data-sidebar="sidebar"][data-mobile="true"])
    [data-sidebar="content"] {
    scrollbar-width: thin;
    scrollbar-color: var(--ksb-scroll) transparent;
}

:is(.kitb-sidebar, [data-sidebar="sidebar"][data-mobile="true"])
    [data-sidebar="content"]::-webkit-scrollbar {
    width: 5px;
}

:is(.kitb-sidebar, [data-sidebar="sidebar"][data-mobile="true"])
    [data-sidebar="content"]::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: var(--ksb-scroll);
}

/* =========================================================
   INDIKATOR MENU AKTIF (warna dari --sidebar-primary)
========================================================= */

:is(.kitb-sidebar, [data-sidebar="sidebar"][data-mobile="true"])
    :is([data-sidebar="menu-button"], [data-sidebar="menu-sub-button"]) {
    position: relative;
}

:is(.kitb-sidebar, [data-sidebar="sidebar"][data-mobile="true"])
    :is(
        [data-sidebar="menu-button"],
        [data-sidebar="menu-sub-button"]
    )[data-active="true"]::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    width: 3px;
    height: 20px;
    border-radius: 0 999px 999px 0;
    background: var(--sidebar-primary);
    transform: translateY(-50%);
}

:is(.kitb-sidebar, [data-sidebar="sidebar"][data-mobile="true"])
    [data-sidebar="menu-sub-button"][data-active="true"]::before {
    width: 2px;
    height: 16px;
}

:is(.kitb-sidebar, [data-sidebar="sidebar"][data-mobile="true"])
    [data-sidebar="menu-button"][data-active="true"]
    svg {
    color: var(--sidebar-primary);
}

/* =========================================================
   FOOTER (NavFooter bawaan starter kit memakai warna netral)
========================================================= */

:is(.kitb-sidebar, [data-sidebar="sidebar"][data-mobile="true"])
    [data-sidebar="footer"]
    [data-sidebar="menu-button"] {
    color: var(--sidebar-foreground);
    opacity: 0.8;
}

:is(.kitb-sidebar, [data-sidebar="sidebar"][data-mobile="true"])
    [data-sidebar="footer"]
    [data-sidebar="menu-button"]:hover {
    color: var(--sidebar-accent-foreground);
    opacity: 1;
}

/* =========================================================
   SKELETON
========================================================= */

.kitb-skeleton {
    background: linear-gradient(
        90deg,
        var(--ksb-skeleton-a) 0%,
        var(--ksb-skeleton-b) 50%,
        var(--ksb-skeleton-a) 100%
    );
    background-size: 200% 100%;
    animation: kitb-skeleton 1.5s ease-in-out infinite;
}

.kitb-user-skeleton {
    border-color: var(--sidebar-border);
}

@keyframes kitb-skeleton {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}

/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 767px) {
    [data-sidebar="sidebar"][data-mobile="true"] [data-sidebar="menu-button"] {
        min-height: 2.75rem;
    }

    [data-sidebar="sidebar"][data-mobile="true"]
        [data-sidebar="menu-sub-button"] {
        min-height: 2.5rem;
    }
}

/* =========================================================
   TRANSISI NAVIGASI
========================================================= */

.kitb-nav-fade-enter-active,
.kitb-nav-fade-leave-active {
    transition:
        opacity 220ms ease,
        transform 220ms ease;
}

.kitb-nav-fade-enter-from {
    opacity: 0;
    transform: translateY(4px);
}

.kitb-nav-fade-leave-to {
    opacity: 0;
    transform: translateY(-3px);
}

/* =========================================================
   REDUCED MOTION
========================================================= */

@media (prefers-reduced-motion: reduce) {
    .kitb-nav-fade-enter-active,
    .kitb-nav-fade-leave-active {
        transition: none !important;
    }

    .kitb-nav-fade-enter-from,
    .kitb-nav-fade-leave-to {
        transform: none;
    }

    .kitb-skeleton {
        animation: none !important;
    }
}
</style>
