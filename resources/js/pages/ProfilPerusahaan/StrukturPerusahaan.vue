<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { Building2, ChevronRight, Network, Users } from "lucide-vue-next";
import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

interface StrukturPerusahaan {
    id: number;
    nama: string;
    jabatan: string;
    gambar: string | null;
    urutan: number;
}

interface Props {
    strukturPerusahaans: StrukturPerusahaan[];
}

const props = defineProps<Props>();

/* ============================================================
   FADE IN
============================================================= */

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const visibleItems = ref<Set<Element>>(new Set());

let observer: IntersectionObserver | null = null;

const observeFadeElements = () => {
    if (typeof window === "undefined") return;

    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (prefersReducedMotion) {
        elements.forEach((element) => {
            element.classList.add("is-visible");
        });

        return;
    }

    observer?.disconnect();

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    visibleItems.value.add(entry.target);

                    (entry.target as HTMLElement).classList.add("is-visible");

                    observer?.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.08,
            rootMargin: "0px 0px -40px 0px",
        },
    );

    elements.forEach((element) => observer?.observe(element));
};

onMounted(() => {
    requestAnimationFrame(() => {
        observeFadeElements();
    });
});

onBeforeUnmount(() => {
    observer?.disconnect();
});

/* ============================================================
   HELPERS
============================================================= */

const anggota = computed(() =>
    [...props.strukturPerusahaans].sort(
        (a, b) => a.urutan - b.urutan || a.id - b.id,
    ),
);

const getImageUrl = (gambar: string | null) => {
    if (!gambar) return null;

    if (gambar.startsWith("http://") || gambar.startsWith("https://")) {
        return gambar;
    }

    if (gambar.startsWith("/storage/")) {
        return gambar;
    }

    return `/storage/${gambar}`;
};

const initials = (nama: string) => {
    return nama
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((word) => word.charAt(0).toUpperCase())
        .join("");
};
</script>

<template>
    <Head>
        <title>Struktur Perusahaan | PT Kawasan Industri Tanjung Buton</title>

        <meta
            name="description"
            content="Struktur perusahaan PT Kawasan Industri Tanjung Buton dan susunan jajaran organisasi perusahaan."
        />

        <meta
            name="keywords"
            content="struktur perusahaan KITB, struktur organisasi KITB, PT Kawasan Industri Tanjung Buton"
        />

        <meta
            property="og:title"
            content="Struktur Perusahaan | PT Kawasan Industri Tanjung Buton"
        />

        <meta
            property="og:description"
            content="Informasi struktur dan jajaran organisasi PT Kawasan Industri Tanjung Buton."
        />

        <meta property="og:type" content="website" />

        <meta
            property="og:url"
            content="https://tanjungbuton-industrial.co.id/profil-perusahaan/struktur-perusahaan"
        />

        <meta
            property="og:image"
            content="https://tanjungbuton-industrial.co.id/logoside.png"
        />

        <link
            rel="canonical"
            href="https://tanjungbuton-industrial.co.id/profil-perusahaan/struktur-perusahaan"
        />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- ============================================================
             DECORATIVE BLOBS
        ============================================================= -->
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 overflow-hidden"
        >
            <div
                class="absolute -left-40 top-24 h-80 w-80 rounded-full bg-blue-500/10 blur-3xl dark:bg-blue-500/10"
            />

            <div
                class="absolute -right-40 top-[32rem] h-96 w-96 rounded-full bg-slate-400/10 blur-3xl dark:bg-slate-700/10"
            />

            <div
                class="absolute left-1/3 top-[48rem] h-72 w-72 rounded-full bg-blue-400/5 blur-3xl dark:bg-blue-400/5"
            />
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div data-reveal class="mb-6" style="--d: 0ms">
                <nav
                    aria-label="Breadcrumb"
                    class="flex items-center gap-2 text-sm"
                >
                    <!-- Beranda -->
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Home class="size-4 shrink-0" />
                        <span>Beranda</span>
                    </Link>

                    <!-- Separator -->
                    <ChevronRight class="size-4 shrink-0 text-slate-400" />

                    <!-- Perusahaan -->
                    <Link
                        href="/profil-perusahaan/tentang-kami"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Building2 class="size-4 shrink-0" />
                        <span>Perusahaan</span>
                    </Link>

                    <!-- Separator -->
                    <ChevronRight class="size-4 shrink-0 text-slate-400" />

                    <!-- Struktur Perusahaan -->
                    <span
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                        aria-current="page"
                    >
                        <Network
                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                        />
                        <span>Struktur Perusahaan</span>
                    </span>
                </nav>
            </div>

            <!-- ============================================================
                 HERO
            ============================================================= -->
            <section
                data-reveal
                class="relative mx-auto mb-14 max-w-4xl text-center"
                style="--d: 80ms"
            >
                <div
                    class="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-blue-50/80 px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                >
                    <Network class="size-4" />

                    <span>Struktur Perusahaan</span>
                </div>

                <h1
                    class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl dark:text-white"
                >
                    Struktur Organisasi
                    <span class="text-blue-600 dark:text-blue-400"> KITB </span>
                </h1>

                <p
                    class="mx-auto mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg dark:text-slate-400"
                >
                    Mengenal susunan jajaran dan organisasi PT Kawasan Industri
                    Tanjung Buton dalam mendukung pengelolaan kawasan industri
                    yang profesional, terintegrasi, dan berkelanjutan.
                </p>
            </section>

            <!-- ============================================================
                 ORGANIZATION
            ============================================================= -->
            <section
                id="struktur-perusahaan"
                aria-labelledby="struktur-title"
                class="relative"
            >
                <div
                    data-reveal
                    class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                    style="--d: 160ms"
                >
                    <div>
                        <div
                            class="mb-2 flex items-center gap-2 text-sm font-semibold text-blue-600 dark:text-blue-400"
                        >
                            <Users class="size-4" />

                            <span>Jajaran Perusahaan</span>
                        </div>

                        <h2
                            id="struktur-title"
                            class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            Susunan Organisasi
                        </h2>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                    >
                        <Users class="size-4" />

                        <span>
                            {{ anggota.length }}
                            {{ anggota.length === 1 ? "Jabatan" : "Jabatan" }}
                        </span>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="anggota.length === 0"
                    data-reveal
                    class="rounded-3xl border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70"
                    style="--d: 220ms"
                >
                    <div
                        class="mx-auto mb-5 flex size-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                    >
                        <Network class="size-8" />
                    </div>

                    <h3
                        class="text-lg font-bold text-slate-900 dark:text-white"
                    >
                        Struktur perusahaan belum tersedia
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Informasi struktur perusahaan sedang dalam proses
                        pembaruan.
                    </p>
                </div>

                <!-- Organization Cards -->
                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(item, index) in anggota"
                        :key="item.id"
                        data-reveal
                        class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white/90 p-5 shadow-sm shadow-slate-900/5 backdrop-blur-xl transition-all duration-500 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/10 dark:border-slate-800/80 dark:bg-slate-900/80 dark:hover:border-blue-800"
                        :style="`--d: ${220 + index * 70}ms`"
                    >
                        <!-- Card Accent -->
                        <div
                            class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-blue-600 to-slate-500 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        />

                        <!-- Number -->
                        <div
                            class="absolute right-5 top-5 flex size-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                        >
                            {{ String(index + 1).padStart(2, "0") }}
                        </div>

                        <!-- Photo -->
                        <div
                            class="mx-auto mb-5 flex aspect-square w-full max-w-[220px] items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700"
                        >
                            <img
                                v-if="getImageUrl(item.gambar)"
                                :src="getImageUrl(item.gambar)!"
                                :alt="`${item.nama} - ${item.jabatan}`"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                loading="lazy"
                            />

                            <div
                                v-else
                                class="flex h-full w-full flex-col items-center justify-center"
                            >
                                <div
                                    class="flex size-20 items-center justify-center rounded-full bg-white text-xl font-bold text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    {{ initials(item.nama) }}
                                </div>

                                <Users
                                    class="mt-3 size-5 text-slate-400 dark:text-slate-500"
                                />
                            </div>
                        </div>

                        <!-- Information -->
                        <div class="text-center">
                            <h3
                                class="text-lg font-bold text-slate-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                            >
                                {{ item.nama }}
                            </h3>

                            <div
                                class="mx-auto mt-3 inline-flex max-w-full items-center justify-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"
                            >
                                <span class="truncate">
                                    {{ item.jabatan }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- ============================================================
                 BOTTOM INFORMATION
            ============================================================= -->
            <section
                v-if="anggota.length > 0"
                data-reveal
                class="mt-16"
                style="--d: 500ms"
            >
                <div
                    class="relative overflow-hidden rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50 via-white to-slate-50 p-7 shadow-sm dark:border-blue-950/60 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-950 sm:p-10"
                >
                    <div
                        aria-hidden="true"
                        class="absolute -right-20 -top-20 size-56 rounded-full bg-blue-500/10 blur-3xl"
                    />

                    <div
                        class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="max-w-2xl">
                            <div
                                class="mb-3 flex items-center gap-2 text-sm font-semibold text-blue-600 dark:text-blue-400"
                            >
                                <Building2 class="size-4" />

                                <span>PT Kawasan Industri Tanjung Buton</span>
                            </div>

                            <h2
                                class="text-xl font-bold text-slate-900 sm:text-2xl dark:text-white"
                            >
                                Bersama membangun kawasan industri yang
                                terintegrasi.
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                            >
                                Struktur organisasi menjadi bagian penting dalam
                                memastikan setiap fungsi perusahaan berjalan
                                secara terarah dan profesional.
                            </p>
                        </div>

                        <Link
                            href="/profil-perusahaan/tentang-kami"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition-all duration-300 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/25 dark:bg-blue-500 dark:hover:bg-blue-400"
                        >
                            <Building2 class="size-4" />

                            <span>Tentang Kami</span>

                            <ChevronRight class="size-4" />
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>

<style scoped>
[data-reveal] {
    opacity: 0;
    transform: translateY(18px);
    transition:
        opacity 700ms ease,
        transform 700ms cubic-bezier(0.22, 1, 0.36, 1);
    transition-delay: var(--d, 0ms);
}

[data-reveal].is-visible {
    opacity: 1;
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    [data-reveal] {
        opacity: 1;
        transform: none;
        transition: none;
    }
}
</style>
