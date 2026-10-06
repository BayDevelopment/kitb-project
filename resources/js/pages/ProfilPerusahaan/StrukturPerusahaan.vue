<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";
import { Building2, ChevronRight, Home, Network, Users } from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";
import { currentLanguage, type LanguageCode } from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

interface StrukturPerusahaan {
    id: number;

    nama: string;
    nama_en: string | null;
    nama_zh: string | null;

    jabatan: string;
    jabatan_en: string | null;
    jabatan_zh: string | null;

    gambar: string | null;
    urutan: number;
    aktif?: boolean;
}

interface Props {
    strukturPerusahaans: StrukturPerusahaan[];
}

const props = defineProps<Props>();

/*
|--------------------------------------------------------------------------
| Translation Helper
|--------------------------------------------------------------------------
*/

const t = (key: string, replacements?: Record<string, string>): string => {
    return trans(key, replacements);
};

/*
|--------------------------------------------------------------------------
| Localized Content
|--------------------------------------------------------------------------
*/

const getLocalizedValue = (
    idValue: string | null | undefined,
    enValue: string | null | undefined,
    zhValue: string | null | undefined,
): string => {
    const lang: LanguageCode = currentLanguage.value;

    if (lang === "en") {
        return enValue?.trim() || idValue?.trim() || zhValue?.trim() || "";
    }

    if (lang === "zh") {
        return zhValue?.trim() || idValue?.trim() || enValue?.trim() || "";
    }

    return idValue?.trim() || enValue?.trim() || zhValue?.trim() || "";
};

const localizedName = (item: StrukturPerusahaan): string => {
    return getLocalizedValue(item.nama, item.nama_en, item.nama_zh);
};

const localizedPosition = (item: StrukturPerusahaan): string => {
    return getLocalizedValue(item.jabatan, item.jabatan_en, item.jabatan_zh);
};

/*
|--------------------------------------------------------------------------
| Language Reactivity
|--------------------------------------------------------------------------
|
| currentLanguage adalah ref sehingga perubahan bahasa akan
| membuat data localized ikut berubah.
|
| MutationObserver digunakan untuk memastikan bagian SEO/trans()
| ikut dihitung ulang ketika atribut lang pada <html> berubah.
|
*/

const languageVersion = ref(0);

let languageObserver: MutationObserver | null = null;

const setupLanguageObserver = (): void => {
    if (typeof document === "undefined") {
        return;
    }

    languageObserver = new MutationObserver(() => {
        languageVersion.value++;
    });

    languageObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ["lang"],
    });
};

/*
|--------------------------------------------------------------------------
| SEO
|--------------------------------------------------------------------------
*/

const seoTitle = computed(() => {
    void languageVersion.value;

    return t("struktur.title");
});

const seoDescription = computed(() => {
    void languageVersion.value;

    return t("struktur.subtitle");
});

/*
|--------------------------------------------------------------------------
| SEO Keywords
|--------------------------------------------------------------------------
|
| File struktur.php saat ini belum memiliki key "keywords".
| Karena itu kita tidak memanggil struktur.keywords agar tidak
| muncul literal "#struktur.keywords".
|
*/

const seoKeywords = computed(() => {
    void languageVersion.value;

    return [
        t("struktur.title"),
        t("struktur.subtitle"),
        "KITB",
        "PT Kawasan Industri Tanjung Buton",
        "Kawasan Industri Tanjung Buton",
    ].join(", ");
});

const canonicalUrl =
    "https://tanjungbuton-industrial.co.id/profil-perusahaan/struktur-perusahaan";

const ogImage = "https://tanjungbuton-industrial.co.id/logoside.png";

/*
|--------------------------------------------------------------------------
| Organization Data
|--------------------------------------------------------------------------
*/

const anggota = computed<StrukturPerusahaan[]>(() => {
    return [...(props.strukturPerusahaans ?? [])]
        .filter((item) => item.aktif !== false)
        .sort(
            (a, b) =>
                Number(a.urutan) - Number(b.urutan) ||
                Number(a.id) - Number(b.id),
        );
});

/*
|--------------------------------------------------------------------------
| Image Helpers
|--------------------------------------------------------------------------
*/

const getImageUrl = (gambar: string | null | undefined): string | null => {
    if (!gambar?.trim()) {
        return null;
    }

    const value = gambar.trim();

    if (value.startsWith("http://") || value.startsWith("https://")) {
        return value;
    }

    if (value.startsWith("/storage/")) {
        return value;
    }

    if (value.startsWith("storage/")) {
        return `/${value}`;
    }

    return `/storage/${value}`;
};

const initials = (nama: string): string => {
    const value = nama.trim();

    if (!value) {
        return "KT";
    }

    return value
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word.charAt(0).toUpperCase())
        .join("");
};

/*
|--------------------------------------------------------------------------
| Fade In / Intersection Observer
|--------------------------------------------------------------------------
*/

const prefersReducedMotion = ref(false);

let observer: IntersectionObserver | null = null;
let mediaQuery: MediaQueryList | null = null;

const updateReducedMotion = (): void => {
    if (typeof window === "undefined") {
        return;
    }

    mediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");

    prefersReducedMotion.value = mediaQuery.matches;
};

const handleMotionChange = (event: MediaQueryListEvent): void => {
    prefersReducedMotion.value = event.matches;

    if (event.matches) {
        document
            .querySelectorAll<HTMLElement>("[data-reveal]")
            .forEach((element) => {
                element.classList.add("is-visible");
            });
    }
};

const markVisible = (element: Element): void => {
    (element as HTMLElement).classList.add("is-visible");
};

const observeFadeElements = (): void => {
    if (typeof window === "undefined") {
        return;
    }

    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (prefersReducedMotion.value) {
        elements.forEach(markVisible);
        return;
    }

    observer?.disconnect();

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                markVisible(entry.target);
                observer?.unobserve(entry.target);
            });
        },
        {
            threshold: 0.08,
            rootMargin: "0px 0px -40px 0px",
        },
    );

    elements.forEach((element) => {
        observer?.observe(element);
    });
};

const refreshRevealObserver = async (): Promise<void> => {
    await nextTick();

    if (typeof window === "undefined") {
        return;
    }

    window.requestAnimationFrame(() => {
        observeFadeElements();
    });
};

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(async () => {
    setupLanguageObserver();

    updateReducedMotion();

    if (mediaQuery && typeof mediaQuery.addEventListener === "function") {
        mediaQuery.addEventListener("change", handleMotionChange);
    }

    await refreshRevealObserver();
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;

    languageObserver?.disconnect();
    languageObserver = null;

    if (mediaQuery && typeof mediaQuery.removeEventListener === "function") {
        mediaQuery.removeEventListener("change", handleMotionChange);
    }

    mediaQuery = null;
});
</script>

<template>
    <Head>
        <title>{{ seoTitle }} | PT Kawasan Industri Tanjung Buton</title>

        <meta name="description" :content="seoDescription" />

        <meta name="keywords" :content="seoKeywords" />

        <meta
            property="og:title"
            :content="`${seoTitle} | PT Kawasan Industri Tanjung Buton`"
        />

        <meta property="og:description" :content="seoDescription" />

        <meta property="og:type" content="website" />

        <meta property="og:url" :content="canonicalUrl" />

        <meta property="og:image" :content="ogImage" />

        <meta
            property="og:site_name"
            content="PT Kawasan Industri Tanjung Buton"
        />

        <meta name="twitter:card" content="summary_large_image" />

        <meta
            name="twitter:title"
            :content="`${seoTitle} | PT Kawasan Industri Tanjung Buton`"
        />

        <meta name="twitter:description" :content="seoDescription" />

        <meta name="twitter:image" :content="ogImage" />

        <link rel="canonical" :href="canonicalUrl" />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- Decorative Background -->
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
            <!-- Breadcrumb -->
            <div data-reveal class="mb-6" style="--d: 0ms">
                <nav
                    :aria-label="t('struktur.title')"
                    class="flex flex-wrap items-center gap-2 text-sm"
                >
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition-colors hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Home class="size-4 shrink-0" aria-hidden="true" />

                        <span>
                            {{ t("struktur.indonesia") }}
                        </span>
                    </Link>

                    <ChevronRight
                        class="size-4 shrink-0 text-slate-400"
                        aria-hidden="true"
                    />

                    <Link
                        href="/profil-perusahaan/tentang-kami"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition-colors hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Building2 class="size-4 shrink-0" aria-hidden="true" />

                        <span>
                            {{ t("struktur.title") }}
                        </span>
                    </Link>

                    <ChevronRight
                        class="size-4 shrink-0 text-slate-400"
                        aria-hidden="true"
                    />

                    <span
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                        aria-current="page"
                    >
                        <Network
                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                            aria-hidden="true"
                        />

                        <span>
                            {{ t("struktur.title") }}
                        </span>
                    </span>
                </nav>
            </div>

            <!-- Hero -->
            <section
                data-reveal
                class="relative mx-auto mb-14 max-w-4xl text-center"
                style="--d: 80ms"
            >
                <div
                    class="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-blue-50/80 px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm backdrop-blur dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                >
                    <Network class="size-4" aria-hidden="true" />

                    <span>
                        {{ t("struktur.board") }}
                    </span>
                </div>

                <h1
                    class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl dark:text-white"
                >
                    {{ t("struktur.title") }}

                    <span class="text-blue-600 dark:text-blue-400"> KITB </span>
                </h1>

                <p
                    class="mx-auto mt-5 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg dark:text-slate-400"
                >
                    {{ t("struktur.subtitle") }}
                </p>
            </section>

            <!-- Organization -->
            <section
                id="struktur-perusahaan"
                aria-labelledby="struktur-title"
                class="relative"
            >
                <!-- Section Header -->
                <div
                    data-reveal
                    class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                    style="--d: 160ms"
                >
                    <div>
                        <div
                            class="mb-2 flex items-center gap-2 text-sm font-semibold text-blue-600 dark:text-blue-400"
                        >
                            <Users class="size-4" aria-hidden="true" />

                            <span>
                                {{ t("struktur.board") }}
                            </span>
                        </div>

                        <h2
                            id="struktur-title"
                            class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            {{ t("struktur.title") }}
                        </h2>
                    </div>

                    <div
                        v-if="anggota.length > 0"
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                    >
                        <Users class="size-4" aria-hidden="true" />

                        <span>
                            {{ anggota.length }}
                            {{ t("struktur.position") }}
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
                        <Network class="size-8" aria-hidden="true" />
                    </div>

                    <h3
                        class="text-lg font-bold text-slate-900 dark:text-white"
                    >
                        {{ t("struktur.empty") }}
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ t("struktur.subtitle") }}
                    </p>
                </div>

                <!-- Organization Cards -->
                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(item, index) in anggota"
                        :key="item.id"
                        data-reveal
                        class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white/90 p-5 shadow-sm shadow-slate-900/5 backdrop-blur-xl transition-all duration-500 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/10 dark:border-slate-800/80 dark:bg-slate-900/80 dark:hover:border-blue-800"
                        :style="{
                            '--d': `${Math.min(220 + index * 70, 850)}ms`,
                        }"
                    >
                        <!-- Accent -->
                        <div
                            aria-hidden="true"
                            class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-blue-600 to-slate-500 opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        />

                        <!-- Number -->
                        <div
                            class="absolute right-5 top-5 flex size-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                            aria-hidden="true"
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
                                :alt="`${localizedName(item)} - ${localizedPosition(item)}`"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                loading="lazy"
                                decoding="async"
                            />

                            <div
                                v-else
                                class="flex h-full w-full flex-col items-center justify-center"
                            >
                                <div
                                    class="flex size-20 items-center justify-center rounded-full bg-white text-xl font-bold text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                    aria-hidden="true"
                                >
                                    {{ initials(localizedName(item)) }}
                                </div>

                                <Users
                                    class="mt-3 size-5 text-slate-400 dark:text-slate-500"
                                    aria-hidden="true"
                                />
                            </div>
                        </div>

                        <!-- Information -->
                        <div class="text-center">
                            <h3
                                class="break-words text-lg font-bold text-slate-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                            >
                                {{ localizedName(item) }}
                            </h3>

                            <div
                                class="mx-auto mt-3 inline-flex max-w-full items-center justify-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"
                            >
                                <span
                                    class="max-w-full truncate"
                                    :title="localizedPosition(item)"
                                >
                                    {{ localizedPosition(item) }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Bottom Information -->
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
                                <Building2 class="size-4" aria-hidden="true" />

                                <span> PT Kawasan Industri Tanjung Buton </span>
                            </div>

                            <h2
                                class="text-xl font-bold text-slate-900 sm:text-2xl dark:text-white"
                            >
                                {{ t("struktur.subtitle") }}
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                            >
                                {{ t("struktur.board") }}
                                —
                                {{ anggota.length }}
                                {{ t("struktur.position") }}.
                            </p>
                        </div>

                        <Link
                            href="/profil-perusahaan/tentang-kami"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition-all duration-300 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-400 dark:focus:ring-offset-slate-950"
                        >
                            <Building2 class="size-4" aria-hidden="true" />

                            <span>
                                {{ t("struktur.name") }}
                            </span>

                            <ChevronRight class="size-4" aria-hidden="true" />
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
