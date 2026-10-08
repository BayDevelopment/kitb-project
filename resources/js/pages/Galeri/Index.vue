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
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    ChevronRight as ChevronRightSmall,
    Expand,
    GalleryHorizontalEnd,
    Home,
    Image as ImageIcon,
    Images,
    Search,
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

interface Galeri {
    id: number;

    judul_id: string;

    judul_en: string | null;

    judul_zh: string | null;

    slug: string;

    deskripsi_id: string | null;

    deskripsi_en: string | null;

    deskripsi_zh: string | null;

    kategori: string | null;

    gambar: string;

    alt_text_id: string | null;

    alt_text_en: string | null;

    alt_text_zh: string | null;

    tanggal: string | null;

    status: boolean;

    urutan: number;

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
    galeris: Paginator<Galeri>;

    kategoris: string[];

    filters: {
        search: string;

        kategori: string;
    };
}

const props = defineProps<Props>();

const languageOptions: Array<{
    code: LanguageCode;

    label: string;

    short: string;

    flag: string;
}> = [
    {
        code: "id",

        label: "Bahasa Indonesia",

        short: "ID",

        flag: "🇮🇩",
    },

    {
        code: "en",

        label: "English",

        short: "EN",

        flag: "🇬🇧",
    },

    {
        code: "zh",

        label: "中文",

        short: "中文",

        flag: "🇨🇳",
    },
];

const ui = computed(() => {
    const translations: Record<
        LanguageCode,
        {
            home: string;

            informationCenter: string;

            title: string;

            description: string;

            searchLabel: string;

            searchPlaceholder: string;

            categoryFilter: string;

            allCategories: string;

            reset: string;

            loading: string;

            noDataTitle: string;

            noDataDescription: string;

            noResultTitle: string;

            noResultDescription: string;

            showAll: string;

            viewDetail: string;

            close: string;

            descriptionLabel: string;

            noDescription: string;

            imageCaption: string;

            previous: string;

            next: string;

            galleryAria: string;

            closeGallery: string;
        }
    > = {
        id: {
            home: "Beranda",

            informationCenter: "Pusat Informasi",

            title: "Galeri",

            description:
                "Dokumentasi kegiatan, aktivitas, dan berbagai momen yang menggambarkan perkembangan Kawasan Industri Tanjung Buton.",

            searchLabel: "Cari galeri",

            searchPlaceholder: "Cari judul, kategori, atau deskripsi...",

            categoryFilter: "Filter kategori galeri",

            allCategories: "Semua Kategori",

            reset: "Reset",

            loading: "Memuat galeri",

            noDataTitle: "Data Galeri Belum Tersedia",

            noDataDescription:
                "Belum ada dokumentasi galeri yang dapat ditampilkan saat ini. Silakan kembali lagi untuk melihat dokumentasi terbaru KITB.",

            noResultTitle: "Data Tidak Ditemukan",

            noResultDescription:
                "Tidak ada galeri yang sesuai dengan pencarian atau kategori yang dipilih.",

            showAll: "Tampilkan Semua Galeri",

            viewDetail: "Lihat detail",

            close: "Tutup",

            descriptionLabel: "Deskripsi",

            noDescription: "Tidak ada deskripsi untuk dokumentasi ini.",

            imageCaption: "Keterangan Gambar",

            previous: "Sebelumnya",

            next: "Berikutnya",

            galleryAria: "Daftar galeri",

            closeGallery: "Tutup galeri",
        },

        en: {
            home: "Home",

            informationCenter: "Information Center",

            title: "Gallery",

            description:
                "Documentation of activities, events, and various moments that showcase the development of Tanjung Buton Industrial Estate.",

            searchLabel: "Search gallery",

            searchPlaceholder: "Search title, category, or description...",

            categoryFilter: "Filter gallery category",

            allCategories: "All Categories",

            reset: "Reset",

            loading: "Loading gallery",

            noDataTitle: "Gallery Data Not Available",

            noDataDescription:
                "There is currently no gallery documentation available. Please come back later to see the latest KITB documentation.",

            noResultTitle: "No Data Found",

            noResultDescription:
                "No gallery matches the selected search or category.",

            showAll: "Show All Galleries",

            viewDetail: "View detail",

            close: "Close",

            descriptionLabel: "Description",

            noDescription:
                "There is no description available for this documentation.",

            imageCaption: "Image Caption",

            previous: "Previous",

            next: "Next",

            galleryAria: "Gallery list",

            closeGallery: "Close gallery",
        },

        zh: {
            home: "首页",

            informationCenter: "信息中心",

            title: "图库",

            description:
                "展示丹绒布顿工业园区发展过程中的活动、项目及重要时刻。",

            searchLabel: "搜索图库",

            searchPlaceholder: "搜索标题、类别或描述...",

            categoryFilter: "图库类别筛选",

            allCategories: "全部类别",

            reset: "重置",

            loading: "正在加载图库",

            noDataTitle: "暂无图库数据",

            noDataDescription:
                "目前暂无可显示的图库资料，请稍后回来查看 KITB 的最新资料。",

            noResultTitle: "未找到数据",

            noResultDescription: "没有符合当前搜索条件或类别的图库资料。",

            showAll: "显示全部图库",

            viewDetail: "查看详情",

            close: "关闭",

            descriptionLabel: "描述",

            noDescription: "暂无该资料的描述。",

            imageCaption: "图片说明",

            previous: "上一页",

            next: "下一页",

            galleryAria: "图库列表",

            closeGallery: "关闭图库",
        },
    };

    return translations[currentLanguage.value];
});

const localizedGaleriValue = (
    galeri: Galeri,

    field: "judul" | "deskripsi",
): string => {
    const source: Record<string, unknown> = {
        ...galeri,

        // Base Indonesian field.

        judul: galeri.judul_id,

        deskripsi: galeri.deskripsi_id,
    };

    return localizedValue(source, field);
};

const localizedGaleriAltText = (galeri: Galeri): string => {
    const source: Record<string, unknown> = {
        ...galeri,

        alt_text: galeri.alt_text_id,
    };

    return localizedValue(source, "alt_text");
};

const galleryTitle = (galeri: Galeri): string => {
    return localizedGaleriValue(galeri, "judul");
};

const galleryDescription = (galeri: Galeri): string => {
    return localizedGaleriValue(galeri, "deskripsi");
};

const search = ref(props.filters?.search ?? "");

const selectedKategori = ref(props.filters?.kategori ?? "");

const isLoading = ref(true);

const isNavigating = ref(false);

const selectedGaleri = ref<Galeri | null>(null);

const showModal = ref(false);

const modalPanel = ref<HTMLElement | null>(null);

const previouslyFocusedElement = ref<HTMLElement | null>(null);

let revealObserver: IntersectionObserver | null = null;

let loadingTimer: ReturnType<typeof setTimeout> | null = null;

let searchTimer: ReturnType<typeof setTimeout> | null = null;

const changeLanguage = (language: LanguageCode) => {
    currentLanguage.value = language;
};

const imageUrl = (galeri: Galeri): string | null => {
    if (!galeri.gambar) {
        return null;
    }

    if (
        galeri.gambar.startsWith("http://") ||
        galeri.gambar.startsWith("https://") ||
        galeri.gambar.startsWith("/")
    ) {
        return galeri.gambar;
    }

    return `/storage/${galeri.gambar}`;
};

const handleImageError = (event: Event) => {
    const image = event.target as HTMLImageElement;

    image.style.display = "none";

    const fallback = image.parentElement?.querySelector<HTMLElement>(
        "[data-image-fallback]",
    );

    fallback?.classList.remove("hidden");
};

const formatDate = (date: string | null): string => {
    if (!date) {
        return "";
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return "";
    }

    const localeMap: Record<LanguageCode, string> = {
        id: "id-ID",

        en: "en-US",

        zh: "zh-CN",
    };

    return new Intl.DateTimeFormat(localeMap[currentLanguage.value], {
        day: "2-digit",

        month: "long",

        year: "numeric",
    }).format(parsed);
};

const truncate = (text: string | null, length = 150): string => {
    if (!text) {
        return "";
    }

    const clean = text

        .replace(/<[^>]*>/g, "")

        .replace(/\s+/g, " ")

        .trim();

    if (clean.length <= length) {
        return clean;
    }

    return `${clean.slice(0, length).trim()}…`;
};

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

    isNavigating.value = true;

    router.get("/galeri", params, {
        preserveState: true,

        preserveScroll: true,

        replace: true,

        onFinish: () => {
            isNavigating.value = false;
        },
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

const openModal = async (galeri: Galeri) => {
    previouslyFocusedElement.value =
        document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;

    selectedGaleri.value = galeri;

    showModal.value = true;

    document.body.classList.add("overflow-hidden");

    await nextTick();

    modalPanel.value?.focus();
};

const closeModal = () => {
    showModal.value = false;

    const target = previouslyFocusedElement.value;

    selectedGaleri.value = null;

    previouslyFocusedElement.value = null;

    document.body.classList.remove("overflow-hidden");

    if (target) {
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
    );
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

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const setupRevealObserver = () => {
    if (prefersReducedMotion) {
        document

            .querySelectorAll<HTMLElement>("[data-reveal]")

            .forEach((element) => {
                element.classList.add("is-visible");
            });

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

        .querySelectorAll<HTMLElement>("[data-reveal]")

        .forEach((element) => {
            revealObserver?.observe(element);
        });
};

const startInitialLoading = () => {
    if (prefersReducedMotion) {
        isLoading.value = false;

        return;
    }

    loadingTimer = setTimeout(() => {
        isLoading.value = false;

        nextTick(() => {
            setupRevealObserver();
        });
    }, 550);
};

const hasData = computed(() => {
    return props.galeris.total > 0;
});

const filteredEmpty = computed(() => {
    return hasData.value && props.galeris.data.length === 0;
});

onMounted(() => {
    startInitialLoading();

    document.addEventListener("keydown", handleModalKeydown);

    if (prefersReducedMotion) {
        nextTick(() => {
            setupRevealObserver();
        });
    }
});

onBeforeUnmount(() => {
    revealObserver?.disconnect();

    if (loadingTimer) {
        clearTimeout(loadingTimer);
    }

    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    document.removeEventListener("keydown", handleModalKeydown);

    document.body.classList.remove("overflow-hidden");
});

watch(
    () => props.galeris.data,

    async () => {
        await nextTick();

        if (!isLoading.value) {
            setupRevealObserver();
        }
    },
);
</script>

<template>
    <Head>
        <title>{{ ui.title }} | KITB</title>

        <meta name="description" :content="ui.description" />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/70 dark:bg-slate-950"
    >
        <!-- Background decoration -->

        <div
            aria-hidden="true"
            class="pointer-events-none absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-900/20"
        />

        <div
            aria-hidden="true"
            class="pointer-events-none absolute -right-40 top-[32rem] h-96 w-96 rounded-full bg-indigo-200/30 blur-3xl dark:bg-indigo-900/20"
        />

        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 opacity-[0.035] dark:opacity-[0.025]"
            style="
                background-image:
                    linear-gradient(#2563eb 1px, transparent 1px),
                    linear-gradient(90deg, #2563eb 1px, transparent 1px);

                background-size: 40px 40px;
            "
        />

        <div
            class="relative mx-auto max-w-[1600px] px-4 py-8 sm:px-6 lg:px-8 xl:px-10"
        >
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

                <ChevronRightSmall
                    class="h-3.5 w-3.5 shrink-0 text-slate-400"
                    aria-hidden="true"
                />

                <span
                    class="inline-flex items-center gap-1.5 font-semibold text-blue-600 dark:text-blue-400"
                    aria-current="page"
                >
                    <Images class="h-3.5 w-3.5" aria-hidden="true" />

                    <span>{{ ui.title }}</span>
                </span>
            </nav>

            <!-- Header -->

            <header
                class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between"
                data-reveal
                style="--d: 80ms"
            >
                <div class="max-w-3xl">
                    <p
                        class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400"
                    >
                        {{ ui.informationCenter }}
                    </p>

                    <div class="flex items-center gap-3">
                        <div
                            class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20 sm:flex"
                        >
                            <Images class="h-6 w-6" aria-hidden="true" />
                        </div>

                        <div>
                            <h1
                                class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                            >
                                {{ ui.title }}
                            </h1>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{ ui.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Language -->

                <div
                    class="inline-flex w-fit items-center gap-1 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    aria-label="Language"
                >
                    <button
                        v-for="language in languageOptions"
                        :key="language.code"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-bold transition focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                        :class="
                            currentLanguage === language.code
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'
                        "
                        :aria-pressed="currentLanguage === language.code"
                        @click="changeLanguage(language.code)"
                    >
                        <span>{{ language.flag }}</span>

                        <span>{{ language.short }}</span>
                    </button>
                </div>
            </header>

            <!-- Search & Filter -->

            <section
                class="mb-8 rounded-3xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur-xl sm:p-5 dark:border-slate-800 dark:bg-slate-900/90"
                aria-label="Gallery filters"
                data-reveal
                style="--d: 140ms"
            >
                <div class="flex flex-col gap-3 lg:flex-row">
                    <div class="relative min-w-0 flex-1">
                        <label for="search-galeri" class="sr-only">
                            {{ ui.searchLabel }}
                        </label>

                        <Search
                            class="pointer-events-none absolute left-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-slate-400"
                            aria-hidden="true"
                        />

                        <input
                            id="search-galeri"
                            v-model="search"
                            type="search"
                            autocomplete="off"
                            :placeholder="ui.searchPlaceholder"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                            @input="handleSearchInput"
                        />
                    </div>

                    <div class="w-full lg:w-56">
                        <label for="kategori-galeri" class="sr-only">
                            {{ ui.categoryFilter }}
                        </label>

                        <select
                            id="kategori-galeri"
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
            </section>

            <!-- Skeleton -->

            <section
                v-if="isLoading"
                :aria-label="ui.loading"
                aria-busy="true"
                class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-3"
            >
                <article
                    v-for="index in 9"
                    :key="index"
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    :class="index % 5 === 0 ? 'sm:row-span-2' : ''"
                >
                    <div
                        class="skeleton-shimmer h-48 bg-slate-200 sm:h-56 lg:h-64 dark:bg-slate-800"
                        :class="
                            index % 5 === 0 ? 'sm:h-full sm:min-h-[465px]' : ''
                        "
                    />

                    <div class="space-y-3 p-4">
                        <div
                            class="skeleton-shimmer h-3 w-20 rounded-full bg-slate-200 dark:bg-slate-800"
                        />

                        <div
                            class="skeleton-shimmer h-4 w-4/5 rounded-full bg-slate-200 dark:bg-slate-800"
                        />
                    </div>
                </article>
            </section>

            <!-- Database empty -->

            <section
                v-else-if="!hasData"
                class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
                data-reveal
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                >
                    <GalleryHorizontalEnd class="h-7 w-7" aria-hidden="true" />
                </div>

                <h2
                    class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                >
                    {{ ui.noDataTitle }}
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    {{ ui.noDataDescription }}
                </p>
            </section>

            <!-- Filter empty -->

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
                    {{ ui.noResultTitle }}
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    {{ ui.noResultDescription }}
                </p>

                <button
                    type="button"
                    class="mt-6 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                    @click="clearFilters"
                >
                    {{ ui.showAll }}
                </button>
            </section>

            <!-- Gallery -->

            <section v-else :aria-label="ui.galleryAria">
                <div
                    class="grid auto-rows-[220px] grid-cols-2 gap-3 sm:auto-rows-[230px] sm:gap-5 lg:grid-cols-3"
                >
                    <article
                        v-for="(galeri, index) in props.galeris.data"
                        :key="galeri.id"
                        class="group relative min-h-0 overflow-hidden rounded-3xl border border-slate-200/80 bg-slate-100 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                        :class="[
                            index % 5 === 0 ? 'row-span-2' : '',

                            index % 7 === 3 ? 'sm:col-span-2' : '',
                        ]"
                        data-reveal
                        :style="{
                            '--d': `${Math.min(index * 55, 400)}ms`,
                        }"
                    >
                        <button
                            type="button"
                            class="relative block h-full w-full text-left focus:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-blue-500/40"
                            :aria-label="`${ui.viewDetail}: ${galleryTitle(galeri)}`"
                            @click="openModal(galeri)"
                        >
                            <!-- Image -->

                            <template v-if="imageUrl(galeri)">
                                <img
                                    :src="imageUrl(galeri) ?? undefined"
                                    :alt="
                                        localizedGaleriAltText(galeri) ||
                                        galleryTitle(galeri)
                                    "
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
                                class="flex h-full items-center justify-center bg-slate-100 dark:bg-slate-800"
                            >
                                <div
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    <Images
                                        class="h-7 w-7"
                                        aria-hidden="true"
                                    />
                                </div>
                            </div>

                            <!-- Gradient -->

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/10 to-transparent opacity-80 transition-opacity duration-300 group-hover:opacity-100"
                                aria-hidden="true"
                            />

                            <!-- Category -->

                            <div
                                v-if="galeri.kategori"
                                class="absolute left-3 top-3 sm:left-4 sm:top-4"
                            >
                                <span
                                    class="inline-flex max-w-[calc(100vw-3rem)] items-center rounded-full border border-white/20 bg-slate-950/60 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur-md sm:px-3 sm:text-[11px]"
                                >
                                    {{ galeri.kategori }}
                                </span>
                            </div>

                            <!-- Expand -->

                            <span
                                class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-xl border border-white/20 bg-slate-950/50 text-white opacity-0 backdrop-blur-md transition duration-300 group-hover:opacity-100 sm:right-4 sm:top-4"
                                aria-hidden="true"
                            >
                                <Expand class="h-4 w-4" />
                            </span>

                            <!-- Content -->

                            <div
                                class="absolute inset-x-0 bottom-0 p-3.5 sm:p-5"
                            >
                                <div
                                    v-if="galeri.tanggal"
                                    class="mb-1.5 flex items-center gap-1.5 text-[10px] font-medium text-white/70 sm:text-xs"
                                >
                                    <CalendarDays
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    />

                                    {{ formatDate(galeri.tanggal) }}
                                </div>

                                <h2
                                    class="line-clamp-2 text-sm font-bold leading-5 text-white sm:text-base sm:leading-6"
                                >
                                    {{ galleryTitle(galeri) }}
                                </h2>

                                <p
                                    v-if="galleryDescription(galeri)"
                                    class="mt-1.5 hidden line-clamp-2 text-xs leading-5 text-white/70 sm:block"
                                >
                                    {{
                                        truncate(
                                            galleryDescription(galeri),

                                            100,
                                        )
                                    }}
                                </p>

                                <span
                                    class="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold text-white/80 transition group-hover:text-white sm:text-xs"
                                >
                                    {{ ui.viewDetail }}

                                    <ChevronRightSmall
                                        class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5"
                                        aria-hidden="true"
                                    />
                                </span>
                            </div>
                        </button>
                    </article>
                </div>

                <!-- Pagination -->

                <nav
                    v-if="props.galeris.last_page > 1"
                    class="mt-10 flex flex-wrap items-center justify-center gap-2"
                    aria-label="Gallery pagination"
                    data-reveal
                >
                    <Link
                        v-if="props.galeris.prev_page_url"
                        :href="props.galeris.prev_page_url"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-sm font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-400"
                        :aria-label="ui.previous"
                    >
                        <ChevronLeft class="h-4 w-4" aria-hidden="true" />

                        <span class="hidden sm:inline">
                            {{ ui.previous }}
                        </span>
                    </Link>

                    <template
                        v-for="link in props.galeris.links.slice(1, -1)"
                        :key="link.label"
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
                        v-if="props.galeris.next_page_url"
                        :href="props.galeris.next_page_url"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-sm font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-400"
                        :aria-label="ui.next"
                    >
                        <span class="hidden sm:inline">
                            {{ ui.next }}
                        </span>

                        <ChevronRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </nav>
            </section>
        </div>

        <!-- Lightbox -->

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showModal && selectedGaleri"
                    class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="`galeri-title-${selectedGaleri.id}`"
                    @click.self="closeModal"
                >
                    <div
                        class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"
                        aria-hidden="true"
                    />

                    <article
                        ref="modalPanel"
                        tabindex="-1"
                        class="relative flex max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-white/10 bg-slate-950 shadow-2xl outline-none"
                    >
                        <!-- Close -->

                        <button
                            type="button"
                            class="absolute right-3 top-3 z-20 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-slate-950/70 text-white backdrop-blur-md transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/40"
                            :aria-label="ui.closeGallery"
                            @click="closeModal"
                        >
                            <X class="h-5 w-5" aria-hidden="true" />
                        </button>

                        <div class="flex min-h-0 flex-col lg:flex-row">
                            <!-- Image -->

                            <div
                                class="relative flex min-h-[260px] flex-1 items-center justify-center overflow-hidden bg-black lg:min-h-[650px]"
                            >
                                <template v-if="imageUrl(selectedGaleri)">
                                    <img
                                        :src="
                                            imageUrl(selectedGaleri) ??
                                            undefined
                                        "
                                        :alt="
                                            localizedGaleriAltText(
                                                selectedGaleri,
                                            ) || galleryTitle(selectedGaleri)
                                        "
                                        class="max-h-[62vh] w-full object-contain sm:max-h-[68vh] lg:max-h-[82vh]"
                                    />
                                </template>

                                <div
                                    v-else
                                    class="flex h-full min-h-[260px] w-full items-center justify-center text-slate-500"
                                >
                                    <Images
                                        class="h-16 w-16"
                                        aria-hidden="true"
                                    />
                                </div>
                            </div>

                            <!-- Information -->

                            <div
                                class="w-full overflow-y-auto bg-white dark:bg-slate-900 lg:max-w-sm"
                            >
                                <div class="p-5 sm:p-6">
                                    <div v-if="selectedGaleri.kategori">
                                        <span
                                            class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                        >
                                            {{ selectedGaleri.kategori }}
                                        </span>
                                    </div>

                                    <h2
                                        :id="`galeri-title-${selectedGaleri.id}`"
                                        class="mt-4 text-xl font-bold leading-7 text-slate-900 dark:text-white"
                                    >
                                        {{ galleryTitle(selectedGaleri) }}
                                    </h2>

                                    <div
                                        v-if="selectedGaleri.tanggal"
                                        class="mt-4 inline-flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        <CalendarDays
                                            class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                            aria-hidden="true"
                                        />

                                        {{ formatDate(selectedGaleri.tanggal) }}
                                    </div>

                                    <div
                                        class="my-5 h-px bg-slate-200 dark:bg-slate-800"
                                    />

                                    <!-- Description -->

                                    <div
                                        v-if="
                                            galleryDescription(selectedGaleri)
                                        "
                                    >
                                        <p
                                            class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                                        >
                                            {{ ui.descriptionLabel }}
                                        </p>

                                        <p
                                            class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300"
                                        >
                                            {{
                                                galleryDescription(
                                                    selectedGaleri,
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        v-else
                                        class="rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                    >
                                        {{ ui.noDescription }}
                                    </div>

                                    <!-- Alt -->

                                    <div
                                        v-if="
                                            localizedGaleriAltText(
                                                selectedGaleri,
                                            )
                                        "
                                        class="mt-6"
                                    >
                                        <p
                                            class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                                        >
                                            {{ ui.imageCaption }}
                                        </p>

                                        <p
                                            class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                localizedGaleriAltText(
                                                    selectedGaleri,
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        class="mt-7 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                                        @click="closeModal"
                                    >
                                        {{ ui.close }}

                                        <X class="h-4 w-4" aria-hidden="true" />
                                    </button>
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

.dark .skeleton-shimmer::after {
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

[data-reveal] {
    opacity: 0;

    transform: translateY(16px);

    transition:
        opacity 700ms ease,
        transform 700ms ease;

    transition-delay: var(--d, 0ms);
}

[data-reveal].is-visible {
    opacity: 1;

    transform: translateY(0);
}

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

@media (prefers-reduced-motion: reduce) {
    [data-reveal] {
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
