<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    Building2,
    ChevronRight,
    ExternalLink,
    GitBranch,
    Globe2,
    Users,
} from "lucide-vue-next";
import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

interface AnakUsaha {
    id: number;
    nama: string;
    logo: string | null;
    deskripsi: string | null;
    website: string | null;
    urutan: number;
}

interface Props {
    anakUsahas: AnakUsaha[];
}

const props = defineProps<Props>();

/* ============================================================
   FADE IN
============================================================= */

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const observer = ref<IntersectionObserver | null>(null);

const observeFadeElements = () => {
    if (typeof window === "undefined") return;

    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (prefersReducedMotion) {
        elements.forEach((element) => {
            element.classList.add("is-visible");
        });

        return;
    }

    observer.value?.disconnect();

    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                (entry.target as HTMLElement).classList.add("is-visible");

                observer.value?.unobserve(entry.target);
            });
        },
        {
            threshold: 0.08,
            rootMargin: "0px 0px -40px 0px",
        },
    );

    elements.forEach((element) => {
        observer.value?.observe(element);
    });
};

onMounted(() => {
    requestAnimationFrame(() => {
        observeFadeElements();
    });
});

onBeforeUnmount(() => {
    observer.value?.disconnect();
});

/* ============================================================
   DATA
============================================================= */

const anakUsahaList = computed(() =>
    [...props.anakUsahas].sort((a, b) => a.urutan - b.urutan || a.id - b.id),
);

/* ============================================================
   HELPERS
============================================================= */

const getLogoUrl = (logo: string | null) => {
    if (!logo) return null;

    if (logo.startsWith("http://") || logo.startsWith("https://")) {
        return logo;
    }

    if (logo.startsWith("/storage/")) {
        return logo;
    }

    return `/storage/${logo}`;
};

const truncateDescription = (description: string | null, length = 150) => {
    if (!description) {
        return "Informasi mengenai anak usaha sedang dalam proses pembaruan.";
    }

    if (description.length <= length) {
        return description;
    }

    return `${description.slice(0, length).trim()}...`;
};

const normalizeWebsite = (website: string | null) => {
    if (!website) return null;

    if (website.startsWith("http://") || website.startsWith("https://")) {
        return website;
    }

    return `https://${website}`;
};
</script>

<template>
    <Head>
        <title>Anak Usaha | PT Kawasan Industri Tanjung Buton</title>

        <meta
            name="description"
            content="Informasi anak usaha PT Kawasan Industri Tanjung Buton dan bagian dari ekosistem usaha perusahaan."
        />

        <meta
            name="keywords"
            content="anak usaha KITB, anak perusahaan KITB, PT Kawasan Industri Tanjung Buton"
        />

        <meta
            property="og:title"
            content="Anak Usaha | PT Kawasan Industri Tanjung Buton"
        />

        <meta
            property="og:description"
            content="Mengenal anak usaha dan ekosistem usaha PT Kawasan Industri Tanjung Buton."
        />

        <meta property="og:type" content="website" />

        <meta
            property="og:url"
            content="https://tanjungbuton-industrial.co.id/profil-perusahaan/anak-usaha"
        />

        <meta
            property="og:image"
            content="https://tanjungbuton-industrial.co.id/logoside.png"
        />

        <link
            rel="canonical"
            href="https://tanjungbuton-industrial.co.id/profil-perusahaan/anak-usaha"
        />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- ============================================================
             DECORATIVE BACKGROUND
        ============================================================= -->
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 overflow-hidden"
        >
            <div
                class="absolute -left-40 top-24 h-80 w-80 rounded-full bg-blue-500/10 blur-3xl dark:bg-blue-500/10"
            />

            <div
                class="absolute -right-40 top-[34rem] h-96 w-96 rounded-full bg-slate-400/10 blur-3xl dark:bg-slate-700/10"
            />

            <div
                class="absolute left-1/3 top-[55rem] h-72 w-72 rounded-full bg-blue-400/5 blur-3xl dark:bg-blue-400/5"
            />
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- ========================================================
                 BREADCRUMB
            ========================================================= -->
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

                    <!-- Current Page -->
                    <span
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                        aria-current="page"
                    >
                        <GitBranch
                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                        />

                        <span>Anak Usaha</span>
                    </span>
                </nav>
            </div>

            <!-- ========================================================
                 HERO
            ========================================================= -->
            <section
                data-reveal
                class="relative mx-auto mb-14 max-w-4xl text-center"
                style="--d: 80ms"
            >
                <div
                    class="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-blue-50/80 px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                >
                    <GitBranch class="size-4" />

                    <span>Ekosistem Usaha</span>
                </div>

                <h1
                    class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl dark:text-white"
                >
                    Anak
                    <span class="text-blue-600 dark:text-blue-400">
                        Usaha
                    </span>
                </h1>

                <p
                    class="mx-auto mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg dark:text-slate-400"
                >
                    Mengenal anak usaha yang menjadi bagian dari ekosistem PT
                    Kawasan Industri Tanjung Buton dalam mendukung pengembangan
                    dan pengelolaan kawasan industri.
                </p>
            </section>

            <!-- ========================================================
                 CONTENT HEADER
            ========================================================= -->
            <section id="anak-usaha" aria-labelledby="anak-usaha-title">
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

                            <span> Mitra dan Ekosistem Perusahaan </span>
                        </div>

                        <h2
                            id="anak-usaha-title"
                            class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            Daftar Anak Usaha
                        </h2>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                    >
                        <Building2 class="size-4" />

                        <span>
                            {{ anakUsahaList.length }}
                            {{
                                anakUsahaList.length === 1
                                    ? "Perusahaan"
                                    : "Perusahaan"
                            }}
                        </span>
                    </div>
                </div>

                <!-- ====================================================
                     EMPTY STATE
                ===================================================== -->
                <div
                    v-if="anakUsahaList.length === 0"
                    data-reveal
                    class="rounded-3xl border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70"
                    style="--d: 220ms"
                >
                    <div
                        class="mx-auto mb-5 flex size-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                    >
                        <GitBranch class="size-8" />
                    </div>

                    <h3
                        class="text-lg font-bold text-slate-900 dark:text-white"
                    >
                        Anak usaha belum tersedia
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Informasi anak usaha sedang dalam proses pembaruan.
                    </p>
                </div>

                <!-- ====================================================
                     COMPANY CARDS
                ===================================================== -->
                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(item, index) in anakUsahaList"
                        :key="item.id"
                        data-reveal
                        class="group relative flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm shadow-slate-900/5 backdrop-blur-xl transition-all duration-500 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/10 dark:border-slate-800/80 dark:bg-slate-900/80 dark:hover:border-blue-800"
                        :style="`--d: ${220 + index * 70}ms`"
                    >
                        <!-- Hover Accent -->
                        <div
                            class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-blue-600 to-slate-500 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        />

                        <!-- Number -->
                        <div
                            class="absolute right-5 top-5 flex size-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                        >
                            {{ String(index + 1).padStart(2, "0") }}
                        </div>

                        <!-- Logo -->
                        <div
                            class="mb-6 flex h-40 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50/80 p-6 dark:border-slate-800 dark:bg-slate-950/50"
                        >
                            <img
                                v-if="getLogoUrl(item.logo)"
                                :src="getLogoUrl(item.logo)!"
                                :alt="`Logo ${item.nama}`"
                                class="max-h-28 max-w-[220px] object-contain transition duration-500 group-hover:scale-105"
                                loading="lazy"
                            />

                            <div
                                v-else
                                class="flex size-20 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                            >
                                <Building2 class="size-9" />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex flex-1 flex-col">
                            <h3
                                class="text-xl font-bold text-slate-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                            >
                                {{ item.nama }}
                            </h3>

                            <p
                                class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400"
                            >
                                {{ truncateDescription(item.deskripsi) }}
                            </p>

                            <!-- Website -->
                            <div class="mt-auto pt-6">
                                <a
                                    v-if="normalizeWebsite(item.website)"
                                    :href="normalizeWebsite(item.website)!"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition-all duration-300 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/25 dark:bg-blue-500 dark:hover:bg-blue-400"
                                >
                                    <Globe2 class="size-4" />

                                    <span> Kunjungi Website </span>

                                    <ExternalLink class="size-3.5" />
                                </a>

                                <span
                                    v-else
                                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-400 dark:text-slate-500"
                                >
                                    <Globe2 class="size-4" />

                                    Website belum tersedia
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- ========================================================
                 BOTTOM CTA
            ========================================================= -->
            <section data-reveal class="mt-16" style="--d: 500ms">
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

                                <span> PT Kawasan Industri Tanjung Buton </span>
                            </div>

                            <h2
                                class="text-xl font-bold text-slate-900 sm:text-2xl dark:text-white"
                            >
                                Membangun ekosistem industri yang terintegrasi.
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                            >
                                Anak usaha menjadi bagian dari ekosistem
                                perusahaan dalam mendukung pengembangan kawasan
                                industri secara berkelanjutan.
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
