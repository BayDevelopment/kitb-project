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

const props = defineProps<{
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();
const page = usePage();

/**
 * Elemen pembungkus konten nav (lihat `ref="rootEl"` di template).
 * Dipakai untuk mencari item yang sedang aktif (`[data-active="true"]`,
 * atribut yang otomatis diset SidebarMenuButton/SidebarMenuSubButton
 * lewat prop `is-active`) supaya bisa di-scroll ke posisinya.
 */
const rootEl = ref<HTMLElement | null>(null);

const prefersReducedMotion = () =>
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/**
 * Auto-scroll: begitu sidebar memanjang (grup terbuka, pindah
 * halaman, dll), item yang sedang aktif dipastikan kelihatan
 * tanpa user harus scroll manual duluan.
 *
 * `nextTick()` dipakai karena kita butuh DOM sudah ter-update
 * (mis. submenu baru saja jadi `v-show`-nya true) sebelum
 * mengukur posisi elemen aktifnya.
 */
const scrollActiveIntoView = async () => {
    await nextTick();

    const activeEl = rootEl.value?.querySelector<HTMLElement>(
        '[data-active="true"]',
    );

    activeEl?.scrollIntoView({
        behavior: prefersReducedMotion() ? "auto" : "smooth",
        block: "nearest",
    });
};

/**
 * Menyimpan menu yang sedang terbuka (manual ATAU karena
 * mengandung route aktif).
 */
const openMenus = ref<string[]>([]);

/**
 * Mengecek apakah sebuah menu memiliki child yang sedang aktif.
 *
 * Support nested menu secara recursive.
 */
const hasActiveChild = (item: NavItem): boolean => {
    return (
        item.items?.some((child) => {
            if (child.href && isCurrentUrl(child.href)) {
                return true;
            }

            return child.items?.length ? hasActiveChild(child) : false;
        }) ?? false
    );
};

/**
 * Membuka parent menu yang memiliki route aktif.
 *
 * Dipanggil saat:
 * - component pertama kali mounted (lewat watch immediate)
 * - URL Inertia berubah
 */
const syncActiveMenus = (items: NavItem[]) => {
    const activeMenus = items
        .filter((item) => item.items?.length && hasActiveChild(item))
        .map((item) => item.title);

    if (activeMenus.length === 0) {
        return;
    }

    const mergedMenus = new Set([...openMenus.value, ...activeMenus]);

    openMenus.value = Array.from(mergedMenus);
};

/**
 * Toggle parent menu.
 *
 * PENTING (berubah dari versi sebelumnya): menu yang masih
 * punya child aktif TIDAK BOLEH ditutup dari sini. Jadi mis.
 * grup "Kawasan" akan tetap terbuka selama user berada di
 * salah satu halaman Kawasan, dan baru bisa ditutup manual
 * setelah user pindah ke halaman lain di luar grup itu.
 */
const toggleMenu = (item: NavItem) => {
    const isOpen = openMenus.value.includes(item.title);

    if (isOpen) {
        if (hasActiveChild(item)) {
            // Abaikan klik tutup selama masih ada child aktif.
            return;
        }

        openMenus.value = openMenus.value.filter(
            (title) => title !== item.title,
        );

        return;
    }

    openMenus.value = [...openMenus.value, item.title];

    // Grup baru saja terbuka → sidebar bisa jadi lebih panjang,
    // pastikan item aktif (kalau ada) tetap kelihatan.
    scrollActiveIntoView();
};

/**
 * React terhadap perubahan URL Inertia, dan langsung dijalankan
 * sekali saat mounted lewat `immediate: true` — supaya grup yang
 * aktif sudah terbuka sejak render pertama, bukan cuma setelah
 * navigasi berikutnya.
 *
 * BUG SEBELUMNYA: watcher ini memakai `currentItems.value` yang
 * merupakan ref kosong dan tidak pernah diisi dari props, jadi
 * sync-nya efektif tidak pernah jalan dan dropdown grup aktif
 * tampak tertutup terus. Sekarang langsung pakai `props.items`.
 */
watch(
    () => page.url,
    () => {
        syncActiveMenus(props.items);
        scrollActiveIntoView();
    },
    { immediate: true },
);
</script>

<template>
    <SidebarGroup class="relative overflow-hidden px-2 py-0">
        <!-- BLOBS BACKGROUND -->
        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <!-- Blob kiri atas -->
            <div
                class="absolute -left-16 top-4 h-32 w-32 rounded-full bg-blue-500/10 blur-3xl transition-opacity duration-500 dark:bg-blue-400/10"
            />

            <!-- Blob kanan -->
            <div
                class="absolute -right-20 top-24 h-40 w-40 rounded-full bg-cyan-500/8 blur-3xl dark:bg-cyan-400/8"
            />

            <!-- Blob bawah -->
            <div
                class="absolute -bottom-16 left-8 h-36 w-36 rounded-full bg-indigo-500/8 blur-3xl dark:bg-indigo-400/8"
            />
        </div>

        <!-- CONTENT -->
        <div ref="rootEl" class="relative z-10">
            <SidebarGroupLabel
                class="mb-2 px-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
            >
                Platform
            </SidebarGroupLabel>

            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <!-- =====================================================
                        MENU DENGAN SUBMENU
                    ====================================================== -->
                    <template v-if="item.items?.length">
                        <SidebarMenuButton
                            type="button"
                            :is-active="hasActiveChild(item)"
                            :tooltip="item.title"
                            :aria-expanded="
                                openMenus.includes(item.title) ||
                                hasActiveChild(item)
                            "
                            :aria-controls="`sidebar-submenu-${item.title
                                .toLowerCase()
                                .replace(/\s+/g, '-')}`"
                            class="transition-all duration-200 hover:bg-blue-500/10 hover:text-blue-700 hover:shadow-[0_0_18px_rgba(59,130,246,0.08)] data-[active=true]:bg-blue-50 data-[active=true]:font-medium data-[active=true]:text-blue-950 dark:hover:bg-blue-400/10 dark:hover:text-blue-300 dark:data-[active=true]:bg-blue-950/40 dark:data-[active=true]:text-blue-200"
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
                                        openMenus.includes(item.title) ||
                                        hasActiveChild(item),
                                }"
                            />
                        </SidebarMenuButton>

                        <!-- =================================================
                            SUBMENU

                            Visibility = openMenus ATAU hasActiveChild.
                            OR dengan hasActiveChild() ini disengaja: grup
                            yang sedang berisi route aktif tidak boleh
                            hilang dari layar, sekalipun openMenus belum
                            (atau belum sempat) mencatatnya. toggleMenu()
                            sendiri sudah menolak menutup grup yang masih
                            aktif, jadi baris ini murni lapisan pengaman.
                        ================================================== -->
                        <SidebarMenuSub
                            :id="`sidebar-submenu-${item.title
                                .toLowerCase()
                                .replace(/\s+/g, '-')}`"
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
                                    class="transition-all duration-200 hover:bg-blue-500/10 hover:text-blue-700 hover:translate-x-0.5 data-[active=true]:border-l-2 data-[active=true]:border-blue-800 data-[active=true]:bg-blue-50 data-[active=true]:font-medium data-[active=true]:text-blue-950 dark:hover:bg-blue-400/10 dark:hover:text-blue-300 dark:data-[active=true]:border-blue-300 dark:data-[active=true]:bg-blue-950/40 dark:data-[active=true]:text-blue-200"
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

                    <!-- =====================================================
                        MENU BIASA
                    ====================================================== -->
                    <SidebarMenuButton
                        v-else-if="item.href"
                        as-child
                        :is-active="isCurrentUrl(item.href)"
                        :tooltip="item.title"
                        class="transition-all duration-200 hover:bg-blue-500/10 hover:text-blue-700 hover:shadow-[0_0_18px_rgba(59,130,246,0.08)] data-[active=true]:bg-blue-50 data-[active=true]:font-medium data-[active=true]:text-blue-950 dark:hover:bg-blue-400/10 dark:hover:text-blue-300 dark:data-[active=true]:bg-blue-950/40 dark:data-[active=true]:text-blue-200"
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
