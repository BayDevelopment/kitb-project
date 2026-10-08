<script setup lang="ts">
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";

import { Head, Link, router } from "@inertiajs/vue3";

import {
    ArrowRight,
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    Eye,
    FileText,
    Home,
    Image as ImageIcon,
    Newspaper,
    Search,
    UserRound,
    X,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";

import {
    currentLanguage,
    localizedValue,
    type LanguageCode,
} from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface Berita {
    id: number;

    // Bahasa Indonesia
    judul_id: string;
    excerpt_id: string | null;
    konten_id: string | null;

    // English
    judul_en: string | null;
    excerpt_en: string | null;
    konten_en: string | null;

    // Chinese
    judul_zh: string | null;
    excerpt_zh: string | null;
    konten_zh: string | null;

    // Common data
    slug: string;
    gambar: string | null;
    kategori: string | null;
    penulis: string | null;
    status: "draft" | "published" | "archived";
    published_at: string | null;
    is_featured: boolean;
    views: number;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface Props {
    beritas: Paginator<Berita>;
    kategoris: string[];
    filters: {
        search: string;
        kategori: string;
    };
}

const props = defineProps<Props>();

// ---------------------------------------------------------------------------
// Language
// ---------------------------------------------------------------------------

const languageOptions: Array<{
    code: LanguageCode;
    label: string;
    short: string;
    flag: string;
}> = [
    { code: "id", label: "Bahasa Indonesia", short: "ID", flag: "🇮🇩" },
    { code: "en", label: "English", short: "EN", flag: "🇬🇧" },
    { code: "zh", label: "中文", short: "中文", flag: "🇨🇳" },
];

// ---------------------------------------------------------------------------
// Database localization adapter
//
// localizedValue() reads:   judul / judul_en / judul_zh
// Database columns:         judul_id / judul_en / judul_zh
// Indonesian is used as the base (fallback) field.
// ---------------------------------------------------------------------------

const localizedBeritaValue = (
    berita: Berita,
    field: "judul" | "excerpt" | "konten",
): string => {
    const source: Record<string, unknown> = {
        ...berita,
        judul: berita.judul_id,
        excerpt: berita.excerpt_id,
        konten: berita.konten_id,
    };

    return localizedValue(source, field);
};

// ---------------------------------------------------------------------------
// UI translation
// ---------------------------------------------------------------------------

const ui = computed(() => {
    const translations = {
        id: {
            home: "Beranda",
            informationCenter: "Pusat Informasi",
            news: "Berita",
            description:
                "Informasi terbaru mengenai kegiatan, perkembangan, investasi, dan aktivitas PT Kawasan Industri Tanjung Buton.",

            searchLabel: "Cari berita",
            searchPlaceholder: "Cari judul, kategori, atau isi berita...",

            categoryFilter: "Filter kategori berita",
            allCategories: "Semua Kategori",
            reset: "Reset",

            language: "Bahasa",
            newsCount: "berita",

            loading: "Memuat berita...",

            emptyTitle: "Belum Ada Berita",
            emptyDescription:
                "Belum ada berita yang dapat ditampilkan saat ini. Silakan kembali lagi untuk mendapatkan informasi terbaru dari KITB.",

            notFoundTitle: "Berita Tidak Ditemukan",
            notFoundDescription:
                "Tidak ada berita yang sesuai dengan pencarian atau kategori yang dipilih.",

            showAll: "Tampilkan Semua Berita",

            featured: "Berita Unggulan",
            readMore: "Baca Selengkapnya",

            previous: "Sebelumnya",
            next: "Berikutnya",

            previousAria: "Halaman sebelumnya",
            nextAria: "Halaman berikutnya",

            paginationAria: "Pagination berita",
            newsListAria: "Daftar berita",
            searchSectionAria: "Pencarian berita",
            loadingAria: "Memuat berita",

            readAria: (title: string) => `Baca ${title}`,
            detailAria: (title: string) => `Lihat detail berita ${title}`,

            close: "Tutup",
            fullArticle: "Baca Berita Selengkapnya",

            summary: "Ringkasan",
            content: "Isi Berita",

            views: "dilihat",

            modalCloseAria: "Tutup detail berita",
        },

        en: {
            home: "Home",
            informationCenter: "Information Center",
            news: "News",
            description:
                "Latest information about the activities, developments, investments, and operations of PT Kawasan Industri Tanjung Buton.",

            searchLabel: "Search news",
            searchPlaceholder: "Search title, category, or news content...",

            categoryFilter: "Filter news category",
            allCategories: "All Categories",
            reset: "Reset",

            language: "Language",
            newsCount: "news",

            loading: "Loading news...",

            emptyTitle: "No News Available",
            emptyDescription:
                "There is no news available at the moment. Please come back for the latest information from KITB.",

            notFoundTitle: "News Not Found",
            notFoundDescription:
                "No news matches the selected search or category.",

            showAll: "Show All News",

            featured: "Featured News",
            readMore: "Read More",

            previous: "Previous",
            next: "Next",

            previousAria: "Previous page",
            nextAria: "Next page",

            paginationAria: "News pagination",
            newsListAria: "News list",
            searchSectionAria: "News search",
            loadingAria: "Loading news",

            readAria: (title: string) => `Read ${title}`,
            detailAria: (title: string) => `View news details ${title}`,

            close: "Close",
            fullArticle: "Read Full Article",

            summary: "Summary",
            content: "News Content",

            views: "views",

            modalCloseAria: "Close news details",
        },

        zh: {
            home: "首页",
            informationCenter: "信息中心",
            news: "新闻",
            description:
                "PT Kawasan Industri Tanjung Buton 的最新活动、发展、投资及运营信息。",

            searchLabel: "搜索新闻",
            searchPlaceholder: "搜索标题、分类或新闻内容...",

            categoryFilter: "筛选新闻分类",
            allCategories: "全部分类",
            reset: "重置",

            language: "语言",
            newsCount: "条新闻",

            loading: "正在加载新闻...",

            emptyTitle: "暂无新闻",
            emptyDescription:
                "目前暂无可显示的新闻。请稍后返回查看 KITB 的最新信息。",

            notFoundTitle: "未找到新闻",
            notFoundDescription: "没有符合搜索条件或所选分类的新闻。",

            showAll: "显示全部新闻",

            featured: "精选新闻",
            readMore: "阅读更多",

            previous: "上一页",
            next: "下一页",

            previousAria: "上一页",
            nextAria: "下一页",

            paginationAria: "新闻分页",
            newsListAria: "新闻列表",
            searchSectionAria: "新闻搜索",
            loadingAria: "正在加载新闻",

            readAria: (title: string) => `阅读 ${title}`,
            detailAria: (title: string) => `查看新闻详情 ${title}`,

            close: "关闭",
            fullArticle: "阅读完整新闻",

            summary: "摘要",
            content: "新闻内容",

            views: "次浏览",

            modalCloseAria: "关闭新闻详情",
        },
    } as const;

    return translations[currentLanguage.value];
});

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------

const search = ref(props.filters?.search ?? "");
const selectedKategori = ref(props.filters?.kategori ?? "");

const isNavigating = ref(false);
const revealReady = ref(false);
const isMounted = ref(false);

const selectedBerita = ref<Berita | null>(null);
const showModal = ref(false);
const modalPanel = ref<HTMLElement | null>(null);
const previouslyFocusedElement = ref<HTMLElement | null>(null);

let revealObserver: IntersectionObserver | null = null;
let revealFallbackTimer: ReturnType<typeof setTimeout> | null = null;
let searchTimer: ReturnType<typeof setTimeout> | null = null;
let removeStartListener: (() => void) | null = null;
let removeFinishListener: (() => void) | null = null;

// ---------------------------------------------------------------------------
// Motion
// ---------------------------------------------------------------------------

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

// ---------------------------------------------------------------------------
// Image helper
// ---------------------------------------------------------------------------

const imageUrl = (berita: Berita): string | null => {
    if (!berita.gambar) {
        return null;
    }

    if (
        berita.gambar.startsWith("http://") ||
        berita.gambar.startsWith("https://") ||
        berita.gambar.startsWith("/")
    ) {
        return berita.gambar;
    }

    return `/storage/${berita.gambar}`;
};

const handleImageError = (event: Event) => {
    const image = event.target as HTMLImageElement;

    image.style.display = "none";

    const fallback = image.parentElement?.querySelector<HTMLElement>(
        "[data-image-fallback]",
    );

    fallback?.classList.remove("hidden");
    fallback?.classList.add("flex");
};

// ---------------------------------------------------------------------------
// Date helper
// ---------------------------------------------------------------------------

const formatDate = (date: string | null): string => {
    if (!date) {
        return "";
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return "";
    }

    const locale =
        currentLanguage.value === "en"
            ? "en-US"
            : currentLanguage.value === "zh"
              ? "zh-CN"
              : "id-ID";

    return new Intl.DateTimeFormat(locale, {
        day: "2-digit",
        month: "long",
        year: "numeric",
    }).format(parsed);
};

// ---------------------------------------------------------------------------
// Text helper
// ---------------------------------------------------------------------------

const stripHtml = (text: string | null): string => {
    if (!text) {
        return "";
    }

    return text
        .replace(/<[^>]*>/g, "")
        .replace(/&nbsp;/gi, " ")
        .replace(/&amp;/gi, "&")
        .replace(/&quot;/gi, '"')
        .replace(/&#039;|&#39;/gi, "'")
        .replace(/\s+/g, " ")
        .trim();
};

const truncate = (text: string | null, length = 150): string => {
    const clean = stripHtml(text);

    if (!clean) {
        return "";
    }

    if (clean.length <= length) {
        return clean;
    }

    return `${clean.slice(0, length).trim()}…`;
};

// ---------------------------------------------------------------------------
// Search / filter
// ---------------------------------------------------------------------------

const hasFilters = computed(() => {
    return Boolean(search.value.trim() || selectedKategori.value);
});

const applyFilters = () => {
    const params: Record<string, string> = {};

    const cleanSearch = search.value.trim();

    if (cleanSearch) {
        params.search = cleanSearch;
    }

    if (selectedKategori.value) {
        params.kategori = selectedKategori.value;
    }

    router.get("/berita", params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const handleSearchInput = () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        applyFilters();
    }, 450);
};

const clearFilters = () => {
    search.value = "";
    selectedKategori.value = "";

    applyFilters();
};

// ---------------------------------------------------------------------------
// Data state
// ---------------------------------------------------------------------------

const databaseEmpty = computed(() => {
    return !hasFilters.value && props.beritas.total === 0;
});

const filteredEmpty = computed(() => {
    return hasFilters.value && props.beritas.total === 0;
});

// ---------------------------------------------------------------------------
// Modal
// ---------------------------------------------------------------------------

const openModal = async (berita: Berita) => {
    previouslyFocusedElement.value =
        document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;

    selectedBerita.value = berita;
    showModal.value = true;

    document.body.classList.add("overflow-hidden");

    await nextTick();

    modalPanel.value?.focus();
};

const closeModal = (restoreFocus = true) => {
    showModal.value = false;

    const target = previouslyFocusedElement.value;

    selectedBerita.value = null;
    previouslyFocusedElement.value = null;

    document.body.classList.remove("overflow-hidden");

    if (restoreFocus && target) {
        nextTick(() => {
            target.focus();
        });
    }
};

const getFocusableElements = (): HTMLElement[] => {
    if (!modalPanel.value) {
        return [];
    }

    return Array.from(
        modalPanel.value.querySelectorAll<HTMLElement>(
            'button:not([disabled]), a[href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
        ),
    ).filter((element) => {
        return (
            !element.hasAttribute("disabled") && element.offsetParent !== null
        );
    });
};

const handleModalKeydown = (event: KeyboardEvent) => {
    if (!showModal.value) {
        return;
    }

    if (event.key === "Escape") {
        event.preventDefault();
        closeModal();
        return;
    }

    if (event.key !== "Tab") {
        return;
    }

    const focusable = getFocusableElements();

    if (!focusable.length) {
        event.preventDefault();
        modalPanel.value?.focus();
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};

// ---------------------------------------------------------------------------
// Reveal
// ---------------------------------------------------------------------------

const revealAll = () => {
    document
        .querySelectorAll<HTMLElement>("[data-reveal]:not(.is-visible)")
        .forEach((element) => {
            element.classList.add("is-visible");
        });
};

const setupRevealObserver = () => {
    if (prefersReducedMotion || !("IntersectionObserver" in window)) {
        revealAll();
        return;
    }

    revealObserver?.disconnect();

    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add("is-visible");

                revealObserver?.unobserve(entry.target);
            });
        },
        {
            threshold: 0.08,
            rootMargin: "0px 0px -40px 0px",
        },
    );

    document
        .querySelectorAll<HTMLElement>("[data-reveal]:not(.is-visible)")
        .forEach((element) => {
            revealObserver?.observe(element);
        });
};

// ---------------------------------------------------------------------------
// Lifecycle
// ---------------------------------------------------------------------------

onMounted(() => {
    isMounted.value = true;
    revealReady.value = true;

    nextTick(setupRevealObserver);

    revealFallbackTimer = setTimeout(revealAll, 1500);

    document.addEventListener("keydown", handleModalKeydown);

    removeStartListener = router.on("start", (event) => {
        if (event.detail.visit.url.pathname === "/berita") {
            isNavigating.value = true;
        }
    });

    removeFinishListener = router.on("finish", () => {
        isNavigating.value = false;
    });
});

onBeforeUnmount(() => {
    revealObserver?.disconnect();

    if (revealFallbackTimer) {
        clearTimeout(revealFallbackTimer);
    }

    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    removeStartListener?.();
    removeFinishListener?.();

    document.removeEventListener("keydown", handleModalKeydown);

    document.body.classList.remove("overflow-hidden");
});

watch(
    () => [props.beritas.data, isNavigating.value],
    () => {
        nextTick(setupRevealObserver);
    },
    {
        flush: "post",
    },
);
</script>

<template>
    <Head>
        <title>{{ ui.news }} | KITB</title>

        <meta name="description" :content="ui.description" />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
        :class="{ 'reveal-ready': revealReady }"
    >
        <!-- Decorative Background -->
        <div
            aria-hidden="true"
            class="pointer-events-none absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-900/20"
        />

        <div
            aria-hidden="true"
            class="pointer-events-none absolute -right-40 top-[32rem] h-96 w-96 rounded-full bg-slate-200/40 blur-3xl dark:bg-slate-800/20"
        />

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav
                aria-label="Breadcrumb"
                class="mb-7 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400"
                data-reveal
            >
                <Link
                    href="/"
                    class="inline-flex items-center gap-1.5 rounded-lg px-1.5 py-1 transition hover:bg-white hover:text-blue-600 dark:hover:bg-slate-900 dark:hover:text-blue-400"
                >
                    <Home class="h-3.5 w-3.5" aria-hidden="true" />

                    <span>{{ ui.home }}</span>
                </Link>

                <ChevronRight
                    class="h-3.5 w-3.5 shrink-0 text-slate-400"
                    aria-hidden="true"
                />

                <span
                    class="inline-flex items-center gap-1.5 font-semibold text-blue-600 dark:text-blue-400"
                    aria-current="page"
                >
                    <Newspaper class="h-3.5 w-3.5" aria-hidden="true" />

                    <span>{{ ui.news }}</span>
                </span>
            </nav>

            <!-- Header -->
            <header class="mb-8 max-w-3xl" data-reveal style="--d: 80ms">
                <p
                    class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400"
                >
                    {{ ui.informationCenter }}
                </p>

                <h1
                    class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                >
                    {{ ui.news }}
                </h1>

                <p
                    class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    {{ ui.description }}
                </p>
            </header>

            <!-- Search & Filter -->
            <section
                class="mb-8 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900"
                :aria-label="ui.searchSectionAria"
                data-reveal
                style="--d: 140ms"
            >
                <h2 class="sr-only">
                    {{ ui.searchSectionAria }}
                </h2>

                <div class="flex flex-col gap-3 lg:flex-row">
                    <div class="relative min-w-0 flex-1">
                        <label for="search-berita" class="sr-only">
                            {{ ui.searchLabel }}
                        </label>

                        <Search
                            class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            aria-hidden="true"
                        />

                        <input
                            id="search-berita"
                            v-model="search"
                            type="search"
                            autocomplete="off"
                            :placeholder="ui.searchPlaceholder"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                            @input="handleSearchInput"
                        />
                    </div>

                    <div class="w-full lg:w-56">
                        <label for="kategori-berita" class="sr-only">
                            {{ ui.categoryFilter }}
                        </label>

                        <select
                            id="kategori-berita"
                            v-model="selectedKategori"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                            @change="applyFilters"
                        >
                            <option value="">
                                {{ ui.allCategories }}
                            </option>

                            <option
                                v-for="kategori in props.kategoris"
                                :key="kategori"
                                :value="kategori"
                            >
                                {{ kategori }}
                            </option>
                        </select>
                    </div>

                    <button
                        v-if="hasFilters"
                        type="button"
                        class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" aria-hidden="true" />

                        {{ ui.reset }}
                    </button>
                </div>

                <div
                    v-if="isNavigating"
                    class="mt-3 flex items-center gap-2 text-xs font-medium text-blue-600 dark:text-blue-400"
                    aria-live="polite"
                >
                    <span
                        class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-blue-200 border-t-blue-600 dark:border-blue-900 dark:border-t-blue-400"
                        aria-hidden="true"
                    />

                    {{ ui.loading }}
                </div>
            </section>

            <!-- Language -->
            <section
                class="mb-8 flex flex-wrap items-center gap-2 rounded-3xl border border-slate-200/80 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                data-reveal
                style="--d: 180ms"
            >
                <h2 class="sr-only">
                    {{ ui.language }}
                </h2>

                <span
                    class="px-2 text-xs font-semibold text-slate-500 dark:text-slate-400"
                >
                    {{ ui.language }}:
                </span>

                <button
                    v-for="language in languageOptions"
                    :key="language.code"
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-xl border px-3.5 py-2 text-xs font-semibold transition"
                    :class="
                        currentLanguage === language.code
                            ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-400'
                    "
                    :aria-pressed="currentLanguage === language.code"
                    :title="language.label"
                    @click="currentLanguage = language.code"
                >
                    <span aria-hidden="true">
                        {{ language.flag }}
                    </span>

                    {{ language.short }}
                </button>

                <span class="ml-auto hidden text-xs text-slate-400 sm:inline">
                    {{ props.beritas.total }}
                    {{ ui.newsCount }}
                </span>
            </section>

            <!-- Loading -->
            <section
                v-if="isNavigating"
                :aria-label="ui.loadingAria"
                aria-busy="true"
                class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
            >
                <h2 class="sr-only">
                    {{ ui.loadingAria }}
                </h2>

                <article
                    v-for="index in 6"
                    :key="index"
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="skeleton-shimmer h-52 bg-slate-200 dark:bg-slate-800"
                    />

                    <div class="space-y-3 p-5">
                        <div
                            class="skeleton-shimmer h-3 w-24 rounded-full bg-slate-200 dark:bg-slate-800"
                        />

                        <div
                            class="skeleton-shimmer h-5 w-full rounded-full bg-slate-200 dark:bg-slate-800"
                        />

                        <div
                            class="skeleton-shimmer h-4 w-4/5 rounded-full bg-slate-200 dark:bg-slate-800"
                        />

                        <div
                            class="skeleton-shimmer h-3 w-32 rounded-full bg-slate-200 dark:bg-slate-800"
                        />
                    </div>
                </article>
            </section>

            <!-- Database Empty -->
            <section
                v-else-if="databaseEmpty"
                class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
                data-reveal
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                >
                    <Newspaper class="h-7 w-7" aria-hidden="true" />
                </div>

                <h2
                    class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                >
                    {{ ui.emptyTitle }}
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    {{ ui.emptyDescription }}
                </p>
            </section>

            <!-- Filter Empty -->
            <section
                v-else-if="filteredEmpty"
                class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
                data-reveal
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                >
                    <Search class="h-7 w-7" aria-hidden="true" />
                </div>

                <h2
                    class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                >
                    {{ ui.notFoundTitle }}
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    {{ ui.notFoundDescription }}
                </p>

                <button
                    type="button"
                    class="mt-6 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                    @click="clearFilters"
                >
                    {{ ui.showAll }}
                </button>
            </section>

            <!-- News Grid -->
            <section v-else :aria-label="ui.newsListAria">
                <h2 class="sr-only">
                    {{ ui.newsListAria }}
                </h2>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(berita, index) in props.beritas.data"
                        :key="berita.id"
                        class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:hover:shadow-black/20"
                        data-reveal
                        :style="{
                            '--d': `${Math.min(index * 55, 400)}ms`,
                        }"
                    >
                        <!-- Image -->
                        <button
                            type="button"
                            class="relative block aspect-[16/10] w-full overflow-hidden bg-slate-100 text-left focus:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-blue-500/40 dark:bg-slate-800"
                            :aria-label="
                                ui.detailAria(
                                    localizedBeritaValue(berita, 'judul'),
                                )
                            "
                            @click="openModal(berita)"
                        >
                            <template v-if="imageUrl(berita)">
                                <img
                                    :src="imageUrl(berita) ?? undefined"
                                    :alt="localizedBeritaValue(berita, 'judul')"
                                    class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                                    loading="lazy"
                                    @error="handleImageError"
                                />

                                <div
                                    data-image-fallback
                                    class="absolute inset-0 hidden items-center justify-center bg-slate-100 dark:bg-slate-800"
                                >
                                    <ImageIcon
                                        class="h-10 w-10 text-slate-400"
                                        aria-hidden="true"
                                    />
                                </div>
                            </template>

                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-slate-100 dark:bg-slate-800"
                            >
                                <div
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    <Newspaper
                                        class="h-7 w-7"
                                        aria-hidden="true"
                                    />
                                </div>
                            </div>

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent opacity-70 transition-opacity duration-300 group-hover:opacity-100"
                                aria-hidden="true"
                            />

                            <span
                                v-if="berita.is_featured"
                                class="absolute left-3 top-3 inline-flex items-center rounded-full border border-white/20 bg-blue-600/90 px-2.5 py-1 text-[10px] font-bold text-white shadow-sm backdrop-blur-md sm:left-4 sm:top-4 sm:text-[11px]"
                            >
                                {{ ui.featured }}
                            </span>

                            <span
                                v-if="berita.kategori"
                                class="absolute bottom-3 left-3 inline-flex max-w-[calc(100%-1.5rem)] items-center rounded-full border border-white/20 bg-slate-950/65 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur-md sm:bottom-4 sm:left-4 sm:px-3 sm:text-[11px]"
                            >
                                {{ berita.kategori }}
                            </span>
                        </button>

                        <!-- Content -->
                        <div class="flex flex-1 flex-col p-5">
                            <!-- Meta -->
                            <div
                                class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-[10px] font-medium text-slate-400 sm:text-xs dark:text-slate-500"
                            >
                                <span
                                    v-if="berita.published_at"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <CalendarDays
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    />

                                    {{ formatDate(berita.published_at) }}
                                </span>

                                <span
                                    v-if="berita.views > 0"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <Eye
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    />

                                    {{ berita.views }}
                                </span>
                            </div>

                            <!-- Title -->
                            <button
                                type="button"
                                class="mt-3 text-left focus:outline-none"
                                :aria-label="
                                    ui.readAria(
                                        localizedBeritaValue(berita, 'judul'),
                                    )
                                "
                                @click="openModal(berita)"
                            >
                                <h3
                                    class="line-clamp-2 text-base font-bold leading-6 text-slate-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                                >
                                    {{ localizedBeritaValue(berita, "judul") }}
                                </h3>
                            </button>

                            <!-- Excerpt -->
                            <p
                                v-if="localizedBeritaValue(berita, 'excerpt')"
                                class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    truncate(
                                        localizedBeritaValue(berita, "excerpt"),
                                        150,
                                    )
                                }}
                            </p>

                            <!-- Author -->
                            <div
                                v-if="berita.penulis"
                                class="mt-4 flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500"
                            >
                                <UserRound
                                    class="h-3.5 w-3.5"
                                    aria-hidden="true"
                                />

                                <span>
                                    {{ berita.penulis }}
                                </span>
                            </div>

                            <!-- Action -->
                            <div class="mt-auto pt-5">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 transition-colors hover:text-blue-700 focus:outline-none focus-visible:rounded-md focus-visible:ring-4 focus-visible:ring-blue-500/20 dark:text-blue-400 dark:hover:text-blue-300"
                                    @click="openModal(berita)"
                                >
                                    {{ ui.readMore }}

                                    <ArrowRight
                                        class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5"
                                        aria-hidden="true"
                                    />
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Pagination -->
                <nav
                    v-if="props.beritas.last_page > 1"
                    class="mt-10 flex flex-wrap items-center justify-center gap-2"
                    :aria-label="ui.paginationAria"
                    data-reveal
                >
                    <Link
                        v-if="props.beritas.prev_page_url"
                        :href="props.beritas.prev_page_url"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-sm font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-400"
                        :aria-label="ui.previousAria"
                    >
                        <ChevronLeft class="h-4 w-4" aria-hidden="true" />

                        <span class="hidden sm:inline">
                            {{ ui.previous }}
                        </span>
                    </Link>

                    <template
                        v-for="(link, index) in props.beritas.links.slice(
                            1,
                            -1,
                        )"
                        :key="`${index}-${link.label}`"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            preserve-state
                            class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border px-3 text-sm font-semibold transition focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                            :class="
                                link.active
                                    ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:text-blue-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-400'
                            "
                            :aria-current="link.active ? 'page' : undefined"
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="inline-flex h-10 min-w-10 items-center justify-center px-2 text-sm text-slate-400"
                            v-html="link.label"
                        />
                    </template>

                    <Link
                        v-if="props.beritas.next_page_url"
                        :href="props.beritas.next_page_url"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-sm font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-400"
                        :aria-label="ui.nextAria"
                    >
                        <span class="hidden sm:inline">
                            {{ ui.next }}
                        </span>

                        <ChevronRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </nav>
            </section>
        </div>

        <!-- Detail Modal -->
        <Teleport v-if="isMounted" to="body">
            <Transition name="modal">
                <div
                    v-if="showModal && selectedBerita"
                    class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="`berita-title-${selectedBerita.id}`"
                    @click.self="closeModal()"
                >
                    <div
                        class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"
                        aria-hidden="true"
                    />

                    <article
                        ref="modalPanel"
                        tabindex="-1"
                        class="relative flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl outline-none dark:bg-slate-900"
                    >
                        <!-- Close -->
                        <button
                            type="button"
                            class="absolute right-3 top-3 z-20 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-slate-950/70 text-white backdrop-blur-md transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/40"
                            :aria-label="ui.modalCloseAria"
                            @click="closeModal()"
                        >
                            <X class="h-5 w-5" aria-hidden="true" />
                        </button>

                        <div class="flex min-h-0 flex-col lg:flex-row">
                            <!-- Image -->
                            <div
                                class="relative flex min-h-[240px] flex-1 items-center justify-center overflow-hidden bg-slate-950 lg:min-h-[580px]"
                            >
                                <template v-if="imageUrl(selectedBerita)">
                                    <img
                                        :src="
                                            imageUrl(selectedBerita) ??
                                            undefined
                                        "
                                        :alt="
                                            localizedBeritaValue(
                                                selectedBerita,
                                                'judul',
                                            )
                                        "
                                        class="max-h-[52vh] w-full object-contain sm:max-h-[58vh] lg:max-h-[78vh]"
                                    />
                                </template>

                                <div
                                    v-else
                                    class="flex min-h-[240px] w-full items-center justify-center text-slate-500"
                                >
                                    <div
                                        class="flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-900 text-blue-400"
                                    >
                                        <Newspaper
                                            class="h-10 w-10"
                                            aria-hidden="true"
                                        />
                                    </div>
                                </div>

                                <span
                                    v-if="selectedBerita.is_featured"
                                    class="absolute left-4 top-4 inline-flex items-center rounded-full border border-white/20 bg-blue-600/90 px-3 py-1.5 text-[11px] font-bold text-white shadow-lg backdrop-blur-md"
                                >
                                    {{ ui.featured }}
                                </span>
                            </div>

                            <!-- Information -->
                            <div
                                class="w-full overflow-y-auto bg-white dark:bg-slate-900 lg:max-w-md"
                            >
                                <div class="p-5 sm:p-7">
                                    <div v-if="selectedBerita.kategori">
                                        <span
                                            class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                        >
                                            {{ selectedBerita.kategori }}
                                        </span>
                                    </div>

                                    <h2
                                        :id="`berita-title-${selectedBerita.id}`"
                                        class="mt-4 text-xl font-bold leading-7 text-slate-900 dark:text-white"
                                    >
                                        {{
                                            localizedBeritaValue(
                                                selectedBerita,
                                                "judul",
                                            )
                                        }}
                                    </h2>

                                    <div
                                        class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        <span
                                            v-if="selectedBerita.published_at"
                                            class="inline-flex items-center gap-2"
                                        >
                                            <CalendarDays
                                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                                aria-hidden="true"
                                            />

                                            {{
                                                formatDate(
                                                    selectedBerita.published_at,
                                                )
                                            }}
                                        </span>

                                        <span
                                            v-if="selectedBerita.penulis"
                                            class="inline-flex items-center gap-2"
                                        >
                                            <UserRound
                                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                                aria-hidden="true"
                                            />

                                            {{ selectedBerita.penulis }}
                                        </span>

                                        <span
                                            v-if="selectedBerita.views > 0"
                                            class="inline-flex items-center gap-2"
                                        >
                                            <Eye
                                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                                aria-hidden="true"
                                            />

                                            {{ selectedBerita.views }}
                                            {{ ui.views }}
                                        </span>
                                    </div>

                                    <div
                                        class="my-5 h-px bg-slate-200 dark:bg-slate-800"
                                    />

                                    <!-- Excerpt -->
                                    <div
                                        v-if="
                                            localizedBeritaValue(
                                                selectedBerita,
                                                'excerpt',
                                            )
                                        "
                                    >
                                        <p
                                            class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                                        >
                                            {{ ui.summary }}
                                        </p>

                                        <p
                                            class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300"
                                        >
                                            {{
                                                stripHtml(
                                                    localizedBeritaValue(
                                                        selectedBerita,
                                                        "excerpt",
                                                    ),
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <!-- Content Preview -->
                                    <div
                                        v-if="
                                            localizedBeritaValue(
                                                selectedBerita,
                                                'konten',
                                            )
                                        "
                                        class="mt-6 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800"
                                    >
                                        <div
                                            class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                                        >
                                            <FileText
                                                class="h-4 w-4"
                                                aria-hidden="true"
                                            />

                                            {{ ui.content }}
                                        </div>

                                        <p
                                            class="mt-3 line-clamp-5 text-sm leading-7 text-slate-600 dark:text-slate-300"
                                        >
                                            {{
                                                truncate(
                                                    localizedBeritaValue(
                                                        selectedBerita,
                                                        "konten",
                                                    ),
                                                    500,
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-7 flex flex-col gap-3">
                                        <Link
                                            :href="`/berita/${selectedBerita.slug}`"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                                            @click="closeModal(false)"
                                        >
                                            {{ ui.fullArticle }}

                                            <ArrowRight
                                                class="h-4 w-4"
                                                aria-hidden="true"
                                            />
                                        </Link>

                                        <button
                                            type="button"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                                            @click="closeModal()"
                                        >
                                            {{ ui.close }}

                                            <X
                                                class="h-4 w-4"
                                                aria-hidden="true"
                                            />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </Transition>
        </Teleport>
    </main>
</template>

<style scoped>
/* Skeleton */

.skeleton-shimmer {
    position: relative;
    overflow: hidden;
}

.skeleton-shimmer::after {
    position: absolute;
    inset: 0;
    transform: translateX(-100%);
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.45),
        transparent
    );
    content: "";
    animation: shimmer 1.5s infinite;
}

:global(.dark) .skeleton-shimmer::after {
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.06),
        transparent
    );
}

@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}

/* Reveal */

.reveal-ready [data-reveal]:not(.is-visible) {
    opacity: 0;
    transform: translateY(16px);
}

[data-reveal] {
    transition:
        opacity 700ms ease,
        transform 700ms ease;
    transition-delay: var(--d, 0ms);
}

[data-reveal].is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Modal */

.modal-enter-active,
.modal-leave-active {
    transition: opacity 220ms ease;
}

.modal-enter-active article,
.modal-leave-active article {
    transition:
        opacity 220ms ease,
        transform 220ms ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from article,
.modal-leave-to article {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
}

/* Reduced motion */

@media (prefers-reduced-motion: reduce) {
    [data-reveal],
    .reveal-ready [data-reveal]:not(.is-visible) {
        opacity: 1;
        transform: none;
        transition: none;
    }

    .skeleton-shimmer::after {
        animation: none;
    }

    .modal-enter-active,
    .modal-leave-active,
    .modal-enter-active article,
    .modal-leave-active article {
        transition: none;
    }
}
</style>
