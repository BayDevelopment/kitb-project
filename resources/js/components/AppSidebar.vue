<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import {
    BookOpen,
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

/**
 * =========================================================
 * AUTH / VERIFICATION STATE
 * =========================================================
 * `auth.user` selalu ada begitu user login (di-share lewat
 * HandleInertiaRequests), jadi hanya field opsional yang perlu
 * di-guard dengan `?.`.
 */
interface AuthUser {
    name?: string;
    email?: string;
    email_verified_at?: string | null;
}

const page = usePage<{ auth?: { user?: AuthUser } }>();

const isEmailVerified = computed(
    () => !!page.props.auth?.user?.email_verified_at,
);

/**
 * =========================================================
 * MAIN NAVIGATION
 * =========================================================
 */
const mainNavItems: NavItem[] = [
    {
        title: "Dashboard",
        href: dashboard(),
        icon: LayoutGrid,
    },

    // =====================================================
    // PROFIL PERUSAHAAN
    // =====================================================
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

    // =====================================================
    // KAWASAN
    // =====================================================
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
];

/**
 * =========================================================
 * FOOTER NAVIGATION
 * =========================================================
 * TODO: ganti ke repo & docs milik proyek ini sebelum rilis —
 * link di bawah masih menunjuk ke starter kit resmi Laravel.
 */
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

/**
 * =========================================================
 * ACTIVE-GROUP DETECTION
 * =========================================================
 * Dipakai supaya grup dropdown yang memuat rute aktif tetap
 * terbuka (tidak collapse) saat navigasi. NavMain menerima
 * daftar title grup yang harus default terbuka lewat prop
 * `default-open-groups`.
 *
 * PENTING: state buka/tutup collapsible-nya sendiri berada di
 * dalam NavMain.vue. Prop di bawah ini hanya memberi tahu grup
 * mana yang harus dianggap "terbuka secara default" — NavMain
 * perlu membaca prop ini (mis. lewat Collapsible `:default-open`
 * atau `v-model:open` per grup) agar perilakunya benar-benar
 * berlaku. Kirimkan isi NavMain.vue jika ingin kami sambungkan
 * langsung.
 */
function resolveHref(href: NavItem["href"]): string {
    return typeof href === "string" ? href : (href?.url ?? "");
}

function isActiveHref(href: string, currentUrl: string): boolean {
    if (!href) return false;
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

/**
 * =========================================================
 * NAV SKELETON CONFIG
 * =========================================================
 * Dibangun otomatis dari `mainNavItems` supaya skeleton tetap
 * sinkron kalau menu berubah, tanpa perlu tulis ulang markup.
 */
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
    <Sidebar collapsible="icon" variant="inset">
        <!-- =====================================================
             HEADER
             `sidebar-blobs` = dekorasi gradient blur di belakang
             logo, murni visual (aria-hidden, tidak ganggu layout).
        ====================================================== -->
        <SidebarHeader class="relative overflow-hidden">
            <div class="sidebar-blobs" aria-hidden="true">
                <span class="sidebar-blob sidebar-blob--one" />
                <span class="sidebar-blob sidebar-blob--two" />
            </div>

            <SidebarMenu class="relative">
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <!-- =====================================================
             MAIN NAVIGATION
             Hanya bagian ini yang dapat melakukan scrolling.
        ====================================================== -->
        <SidebarContent class="flex min-h-0 flex-1 flex-col overflow-y-auto">
            <Transition name="nav-fade" mode="out-in">
                <!-- =================================================
                     NAVIGATION SKELETON
                     Dibangun dari `navSkeletonGroups`, bukan markup
                     statis, jadi otomatis ikut berubah kalau
                     `mainNavItems` berubah.
                ================================================== -->
                <div
                    v-if="!isEmailVerified"
                    key="navigation-skeleton"
                    class="space-y-2 px-2 py-2"
                    role="status"
                    aria-live="polite"
                    aria-busy="true"
                >
                    <span class="sr-only">Memuat navigasi…</span>

                    <!-- Dashboard (item tanpa children) -->
                    <div class="flex h-9 items-center gap-3 rounded-lg px-3">
                        <div
                            class="size-4 shrink-0 animate-pulse rounded-md bg-muted"
                        />
                        <div
                            class="h-4 w-24 animate-pulse rounded-md bg-muted"
                        />
                    </div>

                    <template
                        v-for="group in navSkeletonGroups.filter(
                            (g) => g.hasChildren,
                        )"
                        :key="group.title"
                    >
                        <!-- Group header -->
                        <div
                            class="mt-3 flex h-9 items-center gap-3 rounded-lg px-3"
                        >
                            <div
                                class="size-4 shrink-0 animate-pulse rounded-md bg-muted"
                            />
                            <div
                                class="h-4 animate-pulse rounded-md bg-muted"
                                :style="{ width: group.titleWidth }"
                            />
                        </div>

                        <!-- Group children -->
                        <div class="space-y-2 pl-5">
                            <div
                                v-for="(child, index) in group.children"
                                :key="index"
                                class="h-8 animate-pulse rounded-lg bg-muted/70"
                                :style="{ width: child.width }"
                            />
                        </div>
                    </template>
                </div>

                <!-- =================================================
                     ACTUAL NAVIGATION
                ================================================== -->
                <NavMain
                    v-else
                    key="navigation"
                    :items="mainNavItems"
                    :default-open-groups="activeGroupTitles"
                />
            </Transition>
        </SidebarContent>

        <!-- =====================================================
             FOOTER
             Footer tidak ikut scroll.
        ====================================================== -->
        <SidebarFooter>
            <Transition name="nav-fade" mode="out-in">
                <!-- =================================================
                     FOOTER SKELETON
                ================================================== -->
                <div
                    v-if="!isEmailVerified"
                    key="footer-skeleton"
                    class="space-y-2 px-2 pb-2"
                    aria-hidden="true"
                >
                    <!-- Repository -->
                    <div class="flex h-8 items-center gap-3 px-3">
                        <div class="size-4 animate-pulse rounded-md bg-muted" />
                        <div
                            class="h-3.5 w-20 animate-pulse rounded-md bg-muted"
                        />
                    </div>

                    <!-- Documentation -->
                    <div class="flex h-8 items-center gap-3 px-3">
                        <div class="size-4 animate-pulse rounded-md bg-muted" />
                        <div
                            class="h-3.5 w-24 animate-pulse rounded-md bg-muted"
                        />
                    </div>

                    <!-- User -->
                    <div
                        class="mt-2 flex h-11 items-center gap-3 rounded-lg border border-border/50 px-3"
                    >
                        <div
                            class="size-7 animate-pulse rounded-full bg-muted"
                        />

                        <div class="flex-1 space-y-1.5">
                            <div
                                class="h-3 w-20 animate-pulse rounded-md bg-muted"
                            />
                            <div
                                class="h-2.5 w-28 animate-pulse rounded-md bg-muted/70"
                            />
                        </div>
                    </div>
                </div>

                <!-- =================================================
                     ACTUAL FOOTER
                ================================================== -->
                <div v-else key="footer-navigation">
                    <NavFooter :items="footerNavItems" />

                    <!-- Logout sudah tersedia lewat dropdown NavUser,
                         jadi tombol "Keluar" terpisah di sini dihapus
                         supaya tidak dobel. -->
                    <NavUser />
                </div>
            </Transition>
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>

<style scoped>
.nav-fade-enter-active {
    animation: nav-fade-in 0.55s ease-out;
}

.nav-fade-leave-active {
    animation: nav-fade-out 0.2s ease-in;
}

@keyframes nav-fade-in {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes nav-fade-out {
    from {
        opacity: 1;
    }

    to {
        opacity: 0;
    }
}

/* =========================================================
   DECORATIVE BLOBS
   Blur gradien halus di belakang header, memakai CSS var tema
   sidebar (--sidebar-primary / --sidebar-accent) yang sudah
   disediakan oleh komponen ui/sidebar, jadi otomatis mengikuti
   light/dark mode tanpa warna hardcode.
========================================================= */
.sidebar-blobs {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
}

.sidebar-blob {
    position: absolute;
    border-radius: 9999px;
    filter: blur(28px);
    animation: sidebar-blob-drift 12s ease-in-out infinite;

    /* Light mode: latar terang, blob perlu redup + multiply
       supaya tidak jadi "noda" putih pucat di atas background. */
    opacity: 0.22;
    mix-blend-mode: multiply;
}

.sidebar-blob--one {
    top: -2.5rem;
    left: -2rem;
    width: 6rem;
    height: 6rem;
    background: radial-gradient(
        circle at 30% 30%,
        var(--sidebar-primary) 0%,
        transparent 70%
    );
}

.sidebar-blob--two {
    top: -1.5rem;
    right: -2.5rem;
    width: 5rem;
    height: 5rem;
    background: radial-gradient(
        circle at 60% 40%,
        var(--sidebar-accent) 0%,
        transparent 70%
    );
    animation-delay: -6s;
}

/* Dark mode (termasuk "system" saat resolve ke dark): latar
   gelap butuh blend "screen" + opacity lebih tinggi supaya blob
   tetap kelihatan sebagai cahaya lembut, bukan hilang ditelan
   background gelap. Asumsi: mode gelap di-toggle lewat class
   `.dark` di <html> (konvensi default starter kit Laravel/shadcn).
   Kalau proyekmu pakai mekanisme lain, kasih tahu saya. */
:global(.dark) .sidebar-blob {
    opacity: 0.4;
    mix-blend-mode: screen;
}

@keyframes sidebar-blob-drift {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(6px, 8px) scale(1.08);
    }
}

@media (prefers-reduced-motion: reduce) {
    .nav-fade-enter-active,
    .nav-fade-leave-active,
    .sidebar-blob {
        animation: none;
    }
}
</style>
