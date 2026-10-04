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

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
   Types
   ========================================================= */

interface Galeri {
    id: number;
    judul: string;
    slug: string;
    deskripsi: string | null;
    kategori: string | null;
    gambar: string;
    alt_text: string | null;
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

/* =========================================================
   State
   ========================================================= */

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

/* =========================================================
   Motion
   ========================================================= */

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/* =========================================================
   Image Helper
   ========================================================= */

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

/* =========================================================
   Date Helper
   ========================================================= */

const formatDate = (date: string | null): string => {
    if (!date) {
        return "";
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return "";
    }

    return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    }).format(parsed);
};

/* =========================================================
   Text Helper
   ========================================================= */

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

/* =========================================================
   Search / Filter
   ========================================================= */

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

/* =========================================================
   Modal / Lightbox
   ========================================================= */

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

/* =========================================================
   Reveal
   ========================================================= */

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

/* =========================================================
   Loading
   ========================================================= */

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

/* =========================================================
   Computed
   ========================================================= */

const hasData = computed(() => {
    return props.galeris.total > 0;
});

const filteredEmpty = computed(() => {
    return hasData.value && props.galeris.data.length === 0;
});

/* =========================================================
   Lifecycle
   ========================================================= */

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
        <title>Galeri | KITB</title>

        <meta
            name="description"
            content="Galeri foto dan dokumentasi kegiatan PT Kawasan Industri Tanjung Buton."
        />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- =====================================================
             Decorative Background
             ===================================================== -->

        <div
            aria-hidden="true"
            class="pointer-events-none absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-900/20"
        />

        <div
            aria-hidden="true"
            class="pointer-events-none absolute -right-40 top-[32rem] h-96 w-96 rounded-full bg-slate-200/40 blur-3xl dark:bg-slate-800/20"
        />

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- =================================================
                 Breadcrumb
                 ================================================= -->

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

                    <span>Beranda</span>
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

                    <span>Galeri</span>
                </span>
            </nav>

            <!-- =================================================
                 Header
                 ================================================= -->

            <header class="mb-8 max-w-3xl" data-reveal style="--d: 80ms">
                <p
                    class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400"
                >
                    Pusat Informasi
                </p>

                <h1
                    class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                >
                    Galeri
                </h1>

                <p
                    class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    Dokumentasi kegiatan, aktivitas, dan berbagai momen yang
                    menggambarkan perkembangan Kawasan Industri Tanjung Buton.
                </p>
            </header>

            <!-- =================================================
                 Search & Filter
                 ================================================= -->

            <section
                class="mb-8 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900"
                aria-label="Pencarian galeri"
                data-reveal
                style="--d: 140ms"
            >
                <div class="flex flex-col gap-3 lg:flex-row">
                    <!-- Search -->
                    <div class="relative min-w-0 flex-1">
                        <label for="search-galeri" class="sr-only">
                            Cari galeri
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
                            placeholder="Cari judul, kategori, atau deskripsi..."
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                            @input="handleSearchInput"
                        />
                    </div>

                    <!-- Category -->
                    <div class="w-full lg:w-56">
                        <label for="kategori-galeri" class="sr-only">
                            Filter kategori galeri
                        </label>

                        <select
                            id="kategori-galeri"
                            v-model="selectedKategori"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                            @change="applyFilters"
                        >
                            <option value="">Semua Kategori</option>

                            <option
                                v-for="kategori in props.kategoris"
                                :key="kategori"
                                :value="kategori"
                            >
                                {{ kategori }}
                            </option>
                        </select>
                    </div>

                    <!-- Reset -->
                    <button
                        v-if="hasFilters"
                        type="button"
                        class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="clearFilters"
                    >
                        <X class="h-4 w-4" aria-hidden="true" />

                        Reset
                    </button>
                </div>
            </section>

            <!-- =================================================
                 Loading Skeleton
                 ================================================= -->

            <section
                v-if="isLoading"
                aria-label="Memuat galeri"
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

            <!-- =================================================
                 Database Empty
                 ================================================= -->

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
                    Data Galeri Belum Tersedia
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    Belum ada dokumentasi galeri yang dapat ditampilkan saat
                    ini. Silakan kembali lagi untuk melihat dokumentasi terbaru
                    KITB.
                </p>
            </section>

            <!-- =================================================
                 Filter Empty
                 ================================================= -->

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
                    Data Tidak Ditemukan
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    Tidak ada galeri yang sesuai dengan pencarian atau kategori
                    yang dipilih.
                </p>

                <button
                    type="button"
                    class="mt-6 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                    @click="clearFilters"
                >
                    Tampilkan Semua Galeri
                </button>
            </section>

            <!-- =================================================
                 Gallery Grid
                 ================================================= -->

            <section v-else aria-label="Daftar galeri">
                <div
                    class="grid auto-rows-[220px] grid-cols-2 gap-3 sm:auto-rows-[230px] sm:gap-5 lg:grid-cols-3"
                >
                    <article
                        v-for="(galeri, index) in props.galeris.data"
                        :key="galeri.id"
                        class="group relative min-h-0 overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-100 shadow-sm dark:border-slate-800 dark:bg-slate-900"
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
                            :aria-label="`Lihat galeri ${galeri.judul}`"
                            @click="openModal(galeri)"
                        >
                            <!-- Image -->
                            <template v-if="imageUrl(galeri)">
                                <img
                                    :src="imageUrl(galeri) ?? undefined"
                                    :alt="galeri.alt_text || galeri.judul"
                                    class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                                    loading="lazy"
                                    @error="handleImageError"
                                />

                                <!-- Image fallback -->
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
                                class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/10 to-transparent opacity-80 transition-opacity duration-300 group-hover:opacity-100"
                                aria-hidden="true"
                            />

                            <!-- Top category -->
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

                            <!-- Bottom content -->
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
                                    {{ galeri.judul }}
                                </h2>

                                <p
                                    v-if="galeri.deskripsi"
                                    class="mt-1.5 hidden line-clamp-2 text-xs leading-5 text-white/70 sm:block"
                                >
                                    {{ truncate(galeri.deskripsi, 100) }}
                                </p>

                                <span
                                    class="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold text-white/80 transition group-hover:text-white sm:text-xs"
                                >
                                    Lihat detail

                                    <ChevronRightSmall
                                        class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5"
                                        aria-hidden="true"
                                    />
                                </span>
                            </div>
                        </button>
                    </article>
                </div>

                <!-- =================================================
                     Pagination
                     ================================================= -->

                <nav
                    v-if="props.galeris.last_page > 1"
                    class="mt-10 flex flex-wrap items-center justify-center gap-2"
                    aria-label="Pagination galeri"
                    data-reveal
                >
                    <Link
                        v-if="props.galeris.prev_page_url"
                        :href="props.galeris.prev_page_url"
                        preserve-scroll
                        preserve-state
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-sm font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-400"
                        aria-label="Halaman sebelumnya"
                    >
                        <ChevronLeft class="h-4 w-4" aria-hidden="true" />

                        <span class="hidden sm:inline"> Sebelumnya </span>
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
                        aria-label="Halaman berikutnya"
                    >
                        <span class="hidden sm:inline"> Berikutnya </span>

                        <ChevronRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </nav>
            </section>
        </div>

        <!-- =====================================================
             Gallery Lightbox / Detail Modal
             ===================================================== -->

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
                    <!-- Backdrop -->
                    <div
                        class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"
                        aria-hidden="true"
                    />

                    <!-- Modal -->
                    <article
                        ref="modalPanel"
                        tabindex="-1"
                        class="relative flex max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-white/10 bg-slate-950 shadow-2xl outline-none"
                    >
                        <!-- Close -->
                        <button
                            type="button"
                            class="absolute right-3 top-3 z-20 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-slate-950/70 text-white backdrop-blur-md transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-blue-500/40"
                            aria-label="Tutup galeri"
                            @click="closeModal"
                        >
                            <X class="h-5 w-5" aria-hidden="true" />
                        </button>

                        <!-- Content -->
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
                                            selectedGaleri.alt_text ||
                                            selectedGaleri.judul
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
                                    <!-- Category -->
                                    <div v-if="selectedGaleri.kategori">
                                        <span
                                            class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                        >
                                            {{ selectedGaleri.kategori }}
                                        </span>
                                    </div>

                                    <!-- Title -->
                                    <h2
                                        :id="`galeri-title-${selectedGaleri.id}`"
                                        class="mt-4 text-xl font-bold leading-7 text-slate-900 dark:text-white"
                                    >
                                        {{ selectedGaleri.judul }}
                                    </h2>

                                    <!-- Date -->
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

                                    <!-- Divider -->
                                    <div
                                        class="my-5 h-px bg-slate-200 dark:bg-slate-800"
                                    />

                                    <!-- Description -->
                                    <div v-if="selectedGaleri.deskripsi">
                                        <p
                                            class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                                        >
                                            Deskripsi
                                        </p>

                                        <p
                                            class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300"
                                        >
                                            {{ selectedGaleri.deskripsi }}
                                        </p>
                                    </div>

                                    <div
                                        v-else
                                        class="rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                    >
                                        Tidak ada deskripsi untuk dokumentasi
                                        ini.
                                    </div>

                                    <!-- Alt text -->
                                    <div
                                        v-if="selectedGaleri.alt_text"
                                        class="mt-6"
                                    >
                                        <p
                                            class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                                        >
                                            Keterangan Gambar
                                        </p>

                                        <p
                                            class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            {{ selectedGaleri.alt_text }}
                                        </p>
                                    </div>

                                    <!-- Close -->
                                    <button
                                        type="button"
                                        class="mt-7 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                                        @click="closeModal"
                                    >
                                        Tutup Galeri

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
/* =========================================================
   Skeleton
   ========================================================= */

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

/* =========================================================
   Reveal
   ========================================================= */

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

/* =========================================================
   Modal
   ========================================================= */

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

/* =========================================================
   Reduced Motion
   ========================================================= */

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
