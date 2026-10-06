<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";
import {
    Building2,
    ChevronRight,
    ExternalLink,
    GitBranch,
    Globe2,
    Home,
    Users,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";
import { currentLanguage } from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

/* ============================================================
 * TYPES
 * ============================================================= */

interface AnakUsaha {
    id: number;

    nama: string;
    nama_en: string | null;
    nama_zh: string | null;

    logo: string | null;

    deskripsi: string | null;
    deskripsi_en: string | null;
    deskripsi_zh: string | null;

    website: string | null;
    urutan: number;
}

interface Props {
    anakUsahas: AnakUsaha[];
}

const props = defineProps<Props>();

/* ============================================================
 * TRANSLATION HELPER
 * ============================================================= */

const t = (key: string, replacements?: Record<string, string>): string => {
    return trans(key, replacements);
};

/* ============================================================
 * LOCALIZED DATABASE VALUE
 * ============================================================= */

const getLocalizedValue = (
    idValue: string | null | undefined,
    enValue: string | null | undefined,
    zhValue: string | null | undefined,
): string => {
    switch (currentLanguage.value) {
        case "en":
            return enValue?.trim() || idValue?.trim() || "";

        case "zh":
            return zhValue?.trim() || idValue?.trim() || "";

        default:
            return idValue?.trim() || "";
    }
};

/* ============================================================
 * SEO
 * ============================================================= */

const seo = computed(() => {
    switch (currentLanguage.value) {
        case "en":
            return {
                title: "Subsidiaries | PT Kawasan Industri Tanjung Buton",
                description:
                    "Information about the subsidiaries of PT Kawasan Industri Tanjung Buton and their role in supporting the company's business ecosystem.",
                keywords:
                    "KITB subsidiaries, KITB subsidiary companies, PT Kawasan Industri Tanjung Buton",
                ogDescription:
                    "Learn about the subsidiaries and business ecosystem of PT Kawasan Industri Tanjung Buton.",
            };

        case "zh":
            return {
                title: "子公司 | PT Kawasan Industri Tanjung Buton",
                description:
                    "了解 PT Kawasan Industri Tanjung Buton 的子公司及其在支持公司业务生态系统发展中的作用。",
                keywords:
                    "KITB 子公司, KITB 企业, PT Kawasan Industri Tanjung Buton",
                ogDescription:
                    "了解 PT Kawasan Industri Tanjung Buton 的子公司及其业务生态系统。",
            };

        default:
            return {
                title: "Anak Usaha | PT Kawasan Industri Tanjung Buton",
                description:
                    "Informasi anak usaha PT Kawasan Industri Tanjung Buton dan bagian dari ekosistem usaha perusahaan.",
                keywords:
                    "anak usaha KITB, anak perusahaan KITB, PT Kawasan Industri Tanjung Buton",
                ogDescription:
                    "Mengenal anak usaha dan ekosistem usaha PT Kawasan Industri Tanjung Buton.",
            };
    }
});

/* ============================================================
 * FADE IN
 * ============================================================= */

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
 * DATA
 * ============================================================= */

const anakUsahaList = computed(() =>
    [...props.anakUsahas].sort((a, b) => a.urutan - b.urutan || a.id - b.id),
);

/* ============================================================
 * HELPERS
 * ============================================================= */

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

const getName = (item: AnakUsaha) => {
    return getLocalizedValue(item.nama, item.nama_en, item.nama_zh);
};

const getDescription = (item: AnakUsaha) => {
    return getLocalizedValue(
        item.deskripsi,
        item.deskripsi_en,
        item.deskripsi_zh,
    );
};

const truncateDescription = (description: string | null, length = 150) => {
    if (!description?.trim()) {
        return t("anak-usaha.fallback");
    }

    const cleanDescription = description.trim();

    if (cleanDescription.length <= length) {
        return cleanDescription;
    }

    return `${cleanDescription.slice(0, length).trim()}...`;
};

const normalizeWebsite = (website: string | null) => {
    if (!website?.trim()) return null;

    const value = website.trim();

    if (value.startsWith("http://") || value.startsWith("https://")) {
        return value;
    }

    return `https://${value}`;
};
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>

        <meta name="description" :content="seo.description" />

        <meta name="keywords" :content="seo.keywords" />

        <meta property="og:title" :content="seo.title" />

        <meta property="og:description" :content="seo.ogDescription" />

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
                    class="flex flex-wrap items-center gap-2 text-sm"
                >
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Home class="size-4 shrink-0" />

                        <span>
                            {{ t("home.home") }}
                        </span>
                    </Link>

                    <ChevronRight class="size-4 shrink-0 text-slate-400" />

                    <Link
                        href="/profil-perusahaan/tentang-kami"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Building2 class="size-4 shrink-0" />

                        <span>
                            {{ t("struktur.title") }}
                        </span>
                    </Link>

                    <ChevronRight class="size-4 shrink-0 text-slate-400" />

                    <span
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                        aria-current="page"
                    >
                        <GitBranch
                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                        />

                        <span>
                            {{ t("anak-usaha.title") }}
                        </span>
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

                    <span>
                        {{ t("anak-usaha.company") }}
                    </span>
                </div>

                <h1
                    class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl dark:text-white"
                >
                    {{ t("anak-usaha.title") }}
                </h1>

                <p
                    class="mx-auto mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg dark:text-slate-400"
                >
                    {{ t("anak-usaha.subtitle") }}
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

                            <span>
                                {{ t("anak-usaha.subsidiary") }}
                            </span>
                        </div>

                        <h2
                            id="anak-usaha-title"
                            class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            {{ t("anak-usaha.title") }}
                        </h2>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                    >
                        <Building2 class="size-4" />

                        <span>
                            {{ anakUsahaList.length }}
                            {{ t("anak-usaha.company") }}
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
                        {{ t("anak-usaha.empty") }}
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ t("anak-usaha.subtitle") }}
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
                                :alt="`Logo ${getName(item)}`"
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
                                {{ getName(item) }}
                            </h3>

                            <p
                                class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400"
                            >
                                {{ truncateDescription(getDescription(item)) }}
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

                                    <span>
                                        {{ t("anak-usaha.visit_website") }}
                                    </span>

                                    <ExternalLink class="size-3.5" />
                                </a>

                                <span
                                    v-else
                                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-400 dark:text-slate-500"
                                >
                                    <Globe2 class="size-4" />

                                    <span>
                                        {{ t("anak-usaha.website") }}
                                    </span>
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
                                {{ t("anak-usaha.subtitle") }}
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                            >
                                {{ t("anak-usaha.description") }}
                            </p>
                        </div>

                        <Link
                            href="/profil-perusahaan/tentang-kami"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition-all duration-300 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/25 dark:bg-blue-500 dark:hover:bg-blue-400"
                        >
                            <Building2 class="size-4" />

                            <span>
                                {{ t("anak-usaha.company") }}
                            </span>

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
