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

interface AuthUser {
    name?: string;
    email?: string;
    email_verified_at?: string | null;
}

const page = usePage<{ auth?: { user?: AuthUser } }>();

const isEmailVerified = computed(
    () => !!page.props.auth?.user?.email_verified_at,
);

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

function resolveHref(href: NavItem["href"]): string {
    return typeof href === "string" ? href : (href?.url ?? "");
}

function isActiveHref(href: string, currentUrl: string): boolean {
    if (!href) {
        return false;
    }

    return currentUrl === href || currentUrl.startsWith(`${href}/`);
}

const activeGroupTitles = computed<string[]>(() => {
    const currentUrl = page.url;

    return mainNavItems
        .filter((item) =>
            item.items?.some((sub) =>
                isActiveHref(resolveHref(sub.href), currentUrl),
            ),
        )
        .map((item) => item.title);
});

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
        <!-- Header -->
        <SidebarHeader
            class="relative overflow-hidden border-b border-slate-200/80 bg-white dark:border-slate-800 dark:bg-[#0b1728]"
        >
            <!-- Header decoration -->
            <div
                class="pointer-events-none absolute -left-12 -top-16 size-36 rounded-full bg-[#0b1f3a]/8 blur-3xl dark:bg-white/5"
                aria-hidden="true"
            />

            <div
                class="pointer-events-none absolute -right-10 -top-12 size-32 rounded-full bg-blue-500/8 blur-3xl dark:bg-blue-400/8"
                aria-hidden="true"
            />

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

        <!-- Navigation -->
        <SidebarContent
            class="relative flex min-h-0 flex-1 flex-col overflow-y-auto bg-slate-50 dark:bg-[#07111f]"
        >
            <!-- Background decoration -->
            <div
                class="pointer-events-none absolute inset-0 overflow-hidden"
                aria-hidden="true"
            >
                <div
                    class="absolute -left-24 top-24 size-48 rounded-full bg-[#0b1f3a]/4 blur-3xl dark:bg-blue-500/4"
                />

                <div
                    class="absolute -right-24 bottom-24 size-56 rounded-full bg-blue-500/4 blur-3xl dark:bg-cyan-500/4"
                />
            </div>

            <div class="relative z-10 min-w-0">
                <Transition name="nav-fade" mode="out-in">
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

                        <!-- Dashboard skeleton -->
                        <div
                            class="flex h-9 items-center gap-3 rounded-lg px-3"
                        >
                            <div
                                class="size-4 shrink-0 animate-pulse rounded-md bg-slate-200 dark:bg-slate-700"
                            />

                            <div
                                class="h-4 w-24 animate-pulse rounded-md bg-slate-200 dark:bg-slate-700"
                            />
                        </div>

                        <!-- Group skeleton -->
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
                                    class="size-4 shrink-0 animate-pulse rounded-md bg-slate-200 dark:bg-slate-700"
                                />

                                <div
                                    class="h-4 animate-pulse rounded-md bg-slate-200 dark:bg-slate-700"
                                    :style="{ width: group.titleWidth }"
                                />
                            </div>

                            <div class="space-y-2 pl-5">
                                <div
                                    v-for="(child, index) in group.children"
                                    :key="index"
                                    class="h-8 animate-pulse rounded-lg bg-slate-200/70 dark:bg-slate-700/70"
                                    :style="{ width: child.width }"
                                />
                            </div>
                        </template>
                    </div>

                    <!-- Navigation -->
                    <NavMain
                        v-else
                        key="navigation"
                        :items="mainNavItems"
                        :default-open-groups="activeGroupTitles"
                    />
                </Transition>
            </div>
        </SidebarContent>

        <!-- Footer -->
        <SidebarFooter
            class="relative border-t border-slate-200/80 bg-white dark:border-slate-800 dark:bg-[#0b1728]"
        >
            <Transition name="nav-fade" mode="out-in">
                <!-- Footer Skeleton -->
                <div
                    v-if="!isEmailVerified"
                    key="footer-skeleton"
                    class="space-y-2 px-2 pb-2"
                    aria-hidden="true"
                >
                    <div class="flex h-8 items-center gap-3 px-3">
                        <div
                            class="size-4 animate-pulse rounded-md bg-slate-200 dark:bg-slate-700"
                        />

                        <div
                            class="h-3.5 w-20 animate-pulse rounded-md bg-slate-200 dark:bg-slate-700"
                        />
                    </div>

                    <div class="flex h-8 items-center gap-3 px-3">
                        <div
                            class="size-4 animate-pulse rounded-md bg-slate-200 dark:bg-slate-700"
                        />

                        <div
                            class="h-3.5 w-24 animate-pulse rounded-md bg-slate-200 dark:bg-slate-700"
                        />
                    </div>

                    <div
                        class="mt-2 flex h-11 items-center gap-3 rounded-lg border border-slate-200/70 px-3 dark:border-slate-700/70"
                    >
                        <div
                            class="size-7 animate-pulse rounded-full bg-slate-200 dark:bg-slate-700"
                        />

                        <div class="flex-1 space-y-1.5">
                            <div
                                class="h-3 w-20 animate-pulse rounded-md bg-slate-200 dark:bg-slate-700"
                            />

                            <div
                                class="h-2.5 w-28 animate-pulse rounded-md bg-slate-200/70 dark:bg-slate-700/70"
                            />
                        </div>
                    </div>
                </div>

                <!-- Footer Navigation -->
                <div v-else key="footer-navigation" class="min-w-0">
                    <NavFooter :items="footerNavItems" />
                    <NavUser />
                </div>
            </Transition>
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>

<style scoped>
/* =========================================================
   KITB SIDEBAR
========================================================= */

.kitb-sidebar {
    --kitb-navy: #0b1f3a;
    --kitb-navy-light: #163b68;
    --kitb-blue: #2563eb;
    --kitb-light-hover: rgba(22, 59, 104, 0.08);
    --kitb-light-active: rgba(22, 59, 104, 0.12);
    --kitb-dark-hover: rgba(255, 255, 255, 0.075);
    --kitb-dark-active: rgba(255, 255, 255, 0.11);
}

/* =========================================================
   SCROLLBAR
========================================================= */

:deep(.kitb-sidebar [data-sidebar="content"]) {
    scrollbar-width: thin;
    scrollbar-color: rgba(100, 116, 139, 0.25) transparent;
}

:deep(.kitb-sidebar [data-sidebar="content"]::-webkit-scrollbar) {
    width: 5px;
}

:deep(.kitb-sidebar [data-sidebar="content"]::-webkit-scrollbar-track) {
    background: transparent;
}

:deep(.kitb-sidebar [data-sidebar="content"]::-webkit-scrollbar-thumb) {
    border-radius: 999px;
    background: rgba(100, 116, 139, 0.25);
}

:deep(.kitb-sidebar [data-sidebar="content"]::-webkit-scrollbar-thumb:hover) {
    background: rgba(100, 116, 139, 0.4);
}

/* =========================================================
   MENU BUTTON — BASE
========================================================= */

:deep(.kitb-sidebar [data-sidebar="menu-button"]) {
    position: relative;
    min-width: 0;
    overflow: hidden;
    border: 1px solid transparent;
    color: #475569;
    transition:
        background-color 160ms ease,
        border-color 160ms ease,
        color 160ms ease,
        box-shadow 160ms ease;
}

/* =========================================================
   LIGHT — HOVER
========================================================= */

:deep(.kitb-sidebar [data-sidebar="menu-button"]:hover) {
    border-color: rgba(22, 59, 104, 0.1);
    background: var(--kitb-light-hover);
    color: var(--kitb-navy-light);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.8),
        0 2px 8px rgba(15, 23, 42, 0.035);
}

/* =========================================================
   LIGHT — ACTIVE
========================================================= */

:deep(.kitb-sidebar [data-sidebar="menu-button"][data-active="true"]) {
    border-color: rgba(22, 59, 104, 0.13);
    background: var(--kitb-light-active);
    color: var(--kitb-navy);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.7),
        0 3px 10px rgba(11, 31, 58, 0.055);
}

/* Active indicator */

:deep(.kitb-sidebar [data-sidebar="menu-button"][data-active="true"]::before) {
    position: absolute;
    top: 50%;
    left: 0;
    width: 3px;
    height: 20px;
    content: "";
    border-radius: 0 999px 999px 0;
    background: var(--kitb-navy);
    transform: translateY(-50%);
}

/* =========================================================
   ICONS — LIGHT
========================================================= */

:deep(.kitb-sidebar [data-sidebar="menu-button"] svg) {
    flex-shrink: 0;
    transition:
        color 160ms ease,
        opacity 160ms ease;
}

:deep(.kitb-sidebar [data-sidebar="menu-button"]:hover svg) {
    color: var(--kitb-navy-light);
}

:deep(.kitb-sidebar [data-sidebar="menu-button"][data-active="true"] svg) {
    color: var(--kitb-navy);
}

/* =========================================================
   DARK MODE
========================================================= */

:deep(.dark .kitb-sidebar) {
    --kitb-light-hover: rgba(255, 255, 255, 0.075);
    --kitb-light-active: rgba(255, 255, 255, 0.11);
}

/* =========================================================
   DARK — HOVER
========================================================= */

:deep(.dark .kitb-sidebar [data-sidebar="menu-button"]:hover) {
    border-color: rgba(255, 255, 255, 0.1);
    background: var(--kitb-dark-hover);
    color: #ffffff;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.045),
        0 2px 8px rgba(0, 0, 0, 0.12);
}

/* =========================================================
   DARK — ACTIVE
========================================================= */

:deep(.dark .kitb-sidebar [data-sidebar="menu-button"][data-active="true"]) {
    border-color: rgba(255, 255, 255, 0.14);
    background: var(--kitb-dark-active);
    color: #ffffff;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.06),
        0 3px 12px rgba(0, 0, 0, 0.16);
}

/* Active indicator */

:deep(
    .dark .kitb-sidebar [data-sidebar="menu-button"][data-active="true"]::before
) {
    background: #ffffff;
}

/* =========================================================
   DARK — ICON
========================================================= */

:deep(.dark .kitb-sidebar [data-sidebar="menu-button"]:hover svg) {
    color: #ffffff;
}

:deep(
    .dark .kitb-sidebar [data-sidebar="menu-button"][data-active="true"] svg
) {
    color: #ffffff;
}

/* =========================================================
   SUBMENU
========================================================= */

:deep(.kitb-sidebar [data-sidebar="menu-sub-button"]) {
    position: relative;
    min-width: 0;
    border: 1px solid transparent;
    color: #64748b;
    transition:
        background-color 160ms ease,
        border-color 160ms ease,
        color 160ms ease;
}

/* Light submenu hover */

:deep(.kitb-sidebar [data-sidebar="menu-sub-button"]:hover) {
    border-color: rgba(22, 59, 104, 0.08);
    background: rgba(22, 59, 104, 0.055);
    color: var(--kitb-navy-light);
}

/* Light submenu active */

:deep(.kitb-sidebar [data-sidebar="menu-sub-button"][data-active="true"]) {
    border-color: rgba(22, 59, 104, 0.1);
    background: rgba(22, 59, 104, 0.09);
    color: var(--kitb-navy);
    font-weight: 500;
}

/* Submenu indicator */

:deep(
    .kitb-sidebar [data-sidebar="menu-sub-button"][data-active="true"]::before
) {
    position: absolute;
    top: 50%;
    left: 0;
    width: 2px;
    height: 16px;
    content: "";
    border-radius: 0 999px 999px 0;
    background: var(--kitb-navy);
    transform: translateY(-50%);
}

/* Dark submenu */

:deep(.dark .kitb-sidebar [data-sidebar="menu-sub-button"]:hover) {
    border-color: rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.06);
    color: #ffffff;
}

:deep(
    .dark .kitb-sidebar [data-sidebar="menu-sub-button"][data-active="true"]
) {
    border-color: rgba(255, 255, 255, 0.11);
    background: rgba(255, 255, 255, 0.085);
    color: #ffffff;
    font-weight: 500;
}

:deep(
    .dark
        .kitb-sidebar
        [data-sidebar="menu-sub-button"][data-active="true"]::before
) {
    background: #ffffff;
}

/* =========================================================
   FOOTER
========================================================= */

:deep(
    .kitb-sidebar [data-sidebar="footer"] [data-sidebar="menu-button"]:hover
) {
    border-color: rgba(22, 59, 104, 0.08);
    background: rgba(22, 59, 104, 0.055);
    color: var(--kitb-navy-light);
}

:deep(
    .dark
        .kitb-sidebar
        [data-sidebar="footer"]
        [data-sidebar="menu-button"]:hover
) {
    border-color: rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.06);
    color: #ffffff;
}

/* =========================================================
   COLLAPSED / ICON MODE
========================================================= */

:deep(.kitb-sidebar[data-collapsible="icon"] [data-sidebar="menu-button"]) {
    justify-content: center;
}

:deep(.kitb-sidebar[data-collapsible="icon"] [data-sidebar="menu-button"] svg) {
    margin-inline: auto;
}

/*
 * Saat collapsed:
 * - tidak ada translate
 * - tidak ada perubahan width
 * - icon tetap center
 * - active indicator tetap di sisi kiri
 */

:deep(
    .kitb-sidebar[data-collapsible="icon"]
        [data-sidebar="menu-button"][data-active="true"]::before
) {
    width: 2px;
    height: 18px;
}

/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 767px) {
    :deep(.kitb-sidebar [data-sidebar="menu-button"]) {
        min-height: 2.75rem;
    }

    :deep(.kitb-sidebar [data-sidebar="menu-sub-button"]) {
        min-height: 2.5rem;
    }
}

/* =========================================================
   REDUCED MOTION
========================================================= */

@media (prefers-reduced-motion: reduce) {
    :deep(.kitb-sidebar [data-sidebar="menu-button"]),
    :deep(.kitb-sidebar [data-sidebar="menu-sub-button"]),
    :deep(.kitb-sidebar [data-sidebar="menu-button"] svg) {
        transition: none !important;
    }
}
</style>
