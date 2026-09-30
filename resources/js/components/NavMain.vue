<script setup lang="ts">
import { nextTick, ref, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { ChevronDown } from "lucide-vue-next";

import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from "@/components/ui/sidebar";

import { useCurrentUrl } from "@/composables/useCurrentUrl";
import type { NavItem } from "@/types";

/* =========================================================
   PROPS & DEPENDENCIES
========================================================= */

const props = defineProps<{
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();
const page = usePage();

/* =========================================================
   OPEN / CLOSE STATE

   Default: SEMUA grup terbuka.
   Yang disimpan adalah grup yang DITUTUP oleh pengguna, jadi
   grup baru yang nanti ditambahkan otomatis tampil terbuka.
========================================================= */

const STORAGE_KEY = "kitb-sidebar-closed-groups";

const loadClosedMenus = (): string[] => {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        const parsed = saved ? JSON.parse(saved) : [];

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
};

const closedMenus = ref<string[]>(loadClosedMenus());

watch(
    closedMenus,
    (value) => {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
        } catch {
            /* penyimpanan tidak tersedia, abaikan */
        }
    },
    { deep: true },
);

const isOpen = (item: NavItem): boolean =>
    !closedMenus.value.includes(item.title);

const toggleMenu = (item: NavItem) => {
    closedMenus.value = isOpen(item)
        ? [...closedMenus.value, item.title]
        : closedMenus.value.filter((title) => title !== item.title);

    scrollActiveIntoView();
};

/* =========================================================
   ACTIVE STATE
========================================================= */

/** Apakah menu punya child yang sedang aktif (support nested). */
const hasActiveChild = (item: NavItem): boolean =>
    item.items?.some((child) => {
        if (child.href && isCurrentUrl(child.href)) {
            return true;
        }

        return child.items?.length ? hasActiveChild(child) : false;
    }) ?? false;

/* =========================================================
   SCROLL KE ITEM AKTIF
========================================================= */

const rootEl = ref<HTMLElement | null>(null);

const prefersReducedMotion = () =>
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/**
 * Menggulung sidebar ke item aktif, tetapi hanya jika item itu
 * benar-benar berada di luar area yang terlihat.
 */
const scrollActiveIntoView = async () => {
    await nextTick();

    const activeEl = rootEl.value?.querySelector<HTMLElement>(
        '[data-active="true"]',
    );

    const container = activeEl?.closest<HTMLElement>(
        '[data-sidebar="content"]',
    );

    if (!activeEl || !container) {
        return;
    }

    const c = container.getBoundingClientRect();
    const e = activeEl.getBoundingClientRect();

    if (e.top >= c.top && e.bottom <= c.bottom) {
        return;
    }

    activeEl.scrollIntoView({
        behavior: prefersReducedMotion() ? "auto" : "smooth",
        block: "nearest",
    });
};

watch(() => page.url, scrollActiveIntoView, { immediate: true });

/* =========================================================
   HELPERS
========================================================= */

const submenuId = (item: NavItem): string =>
    `sidebar-submenu-${item.title.toLowerCase().replace(/\s+/g, "-")}`;

/* Class dipakai berulang, dikumpulkan agar template mudah dibaca */

const menuButtonClass =
    "transition-all duration-200 hover:bg-blue-500/10 hover:text-blue-700 hover:shadow-[0_0_18px_rgba(59,130,246,0.08)] data-[active=true]:bg-blue-50 data-[active=true]:font-medium data-[active=true]:text-blue-950 dark:hover:bg-blue-400/10 dark:hover:text-blue-300 dark:data-[active=true]:bg-blue-950/40 dark:data-[active=true]:text-blue-200";

const subButtonClass =
    "transition-all duration-200 hover:bg-blue-500/10 hover:text-blue-700 hover:translate-x-0.5 data-[active=true]:border-l-2 data-[active=true]:border-blue-800 data-[active=true]:bg-blue-50 data-[active=true]:font-medium data-[active=true]:text-blue-950 dark:hover:bg-blue-400/10 dark:hover:text-blue-300 dark:data-[active=true]:border-blue-300 dark:data-[active=true]:bg-blue-950/40 dark:data-[active=true]:text-blue-200";
</script>

<template>
    <SidebarGroup class="relative shrink-0 px-2 py-0">
        <!-- =====================================================
             BACKGROUND
        ====================================================== -->
        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -left-16 top-4 h-32 w-32 rounded-full bg-blue-500/10 blur-3xl dark:bg-blue-400/10"
            />

            <div
                class="absolute -right-20 top-24 h-40 w-40 rounded-full bg-cyan-500/8 blur-3xl dark:bg-cyan-400/8"
            />

            <div
                class="absolute -bottom-16 left-8 h-36 w-36 rounded-full bg-indigo-500/8 blur-3xl dark:bg-indigo-400/8"
            />
        </div>

        <!-- =====================================================
             CONTENT
        ====================================================== -->
        <div ref="rootEl" class="relative z-10">
            <SidebarGroupLabel
                class="mb-2 px-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
            >
                Platform
            </SidebarGroupLabel>

            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <!-- MENU DENGAN SUBMENU -->
                    <template v-if="item.items?.length">
                        <SidebarMenuButton
                            type="button"
                            :is-active="hasActiveChild(item)"
                            :tooltip="item.title"
                            :aria-expanded="isOpen(item)"
                            :aria-controls="submenuId(item)"
                            :class="menuButtonClass"
                            @click="toggleMenu(item)"
                        >
                            <component
                                :is="item.icon"
                                v-if="item.icon"
                                class="transition-colors duration-200"
                            />

                            <span>{{ item.title }}</span>

                            <ChevronDown
                                class="ml-auto size-4 transition-transform duration-200"
                                :class="{
                                    'rotate-180 text-blue-600 dark:text-blue-400':
                                        isOpen(item),
                                }"
                            />
                        </SidebarMenuButton>

                        <SidebarMenuSub
                            v-show="isOpen(item)"
                            :id="submenuId(item)"
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
                                    :class="subButtonClass"
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
                        :class="menuButtonClass"
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
