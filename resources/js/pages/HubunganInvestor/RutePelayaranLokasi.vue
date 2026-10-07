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
    Anchor,
    ArrowRight,
    Building2,
    BriefcaseBusiness,
    CheckCircle2,
    ChevronRight,
    Clock3,
    FileText,
    Home,
    Map,
    MapPin,
    Navigation,
    Route as RouteIcon,
    Search,
    Ship,
    X,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";

import { currentLanguage, localizedValue } from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

/**
 * ============================================================
 * TYPES
 * ============================================================
 */

interface Geometry {
    type?: "Point" | "LineString" | "MultiLineString" | string;
    coordinates?: unknown;
}

interface Rute {
    id: number;

    // Bahasa Indonesia
    nama_rute: string;
    jalur: string;
    deskripsi: string | null;

    // Bahasa Inggris
    nama_rute_en: string | null;
    jalur_en: string | null;
    deskripsi_en: string | null;

    // Bahasa Mandarin
    nama_rute_zh: string | null;
    jalur_zh: string | null;
    deskripsi_zh: string | null;

    // Data teknis
    jarak: string | number | null;
    satuan_jarak: string;

    // Waktu tempuh - ID / EN / ZH
    waktu_tempuh: string;
    waktu_tempuh_en: string | null;
    waktu_tempuh_zh: string | null;

    // Asal - ID / EN / ZH
    asal: string | null;
    asal_en: string | null;
    asal_zh: string | null;

    // Tujuan - ID / EN / ZH
    tujuan: string | null;
    tujuan_en: string | null;
    tujuan_zh: string | null;

    // Lokasi
    latitude: string | number | null;
    longitude: string | number | null;

    // GeoJSON
    geometry: Geometry | null;

    // Gambar
    gambar: string | null;

    // Pengaturan
    urutan: number;
    aktif: boolean;
}

const props = defineProps<{
    rutes: Rute[];
}>();

/**
 * ============================================================
 * LINKS
 * ============================================================
 *
 * Satu sumber URL agar tidak tidak konsisten.
 * Sesuaikan dengan route yang benar di web.php.
 */

const VISIT_URL = "/ajukan-kunjungan";
const INVESTMENT_URL = "/hubungan-investor/peluang-investasi";

/**
 * ============================================================
 * LOCALIZATION
 * ============================================================
 */

const localized = (object: Rute | null | undefined, field: string): string => {
    return localizedValue(
        object as Record<string, unknown> | null | undefined,
        field,
    );
};

/**
 * ============================================================
 * TRANSLATIONS
 * ============================================================
 */

const translations = {
    id: {
        pageTitle: "Rute Pelayaran & Lokasi | KITB",

        meta: "Informasi rute pelayaran, akses, lokasi, jarak, dan waktu tempuh menuju Kawasan Industri Tanjung Buton.",

        home: "Beranda",
        investor: "Hubungan Investor",
        page: "Rute Pelayaran & Lokasi",

        badge: "Akses & Lokasi",

        heading: "Rute Pelayaran & Lokasi",

        intro: "Temukan informasi rute, jarak, waktu tempuh, serta lokasi utama untuk mendukung akses menuju Kawasan Industri Tanjung Buton.",

        loading: "Memuat informasi rute",

        unavailableTitle: "Data Rute Belum Tersedia",

        unavailableDescription:
            "Informasi rute pelayaran dan lokasi saat ini belum tersedia. Silakan kembali lagi untuk mendapatkan informasi terbaru.",

        investment: "Lihat Peluang Investasi",

        listTitle: "Daftar Rute",

        searchDescription:
            "Cari berdasarkan nama rute, jalur, asal, atau tujuan.",

        searchPlaceholder: "Cari rute...",

        searchAria: "Cari rute",

        clearSearch: "Hapus pencarian",

        searchEmptyTitle: "Data Tidak Ditemukan",

        searchEmptyDescription:
            "Tidak ada rute yang sesuai dengan kata kunci pencarian. Coba kata kunci lain atau reset pencarian.",

        searchKeyword: "Pencarian:",

        resetSearch: "Reset Pencarian",

        route: "Rute Pelayaran",

        mapAvailable: "Peta tersedia",

        distance: "Jarak",

        duration: "Waktu Tempuh",

        origin: "Asal",

        destination: "Tujuan",

        detail: "Lihat Detail Rute",

        available: "rute tersedia",

        reference:
            "Gunakan informasi rute sebagai referensi akses menuju kawasan.",

        visit: "Ajukan Kunjungan Lahan",

        investorLabel: "Hubungan Investor",

        ctaTitle: "Ingin melihat langsung kawasan KITB?",

        ctaDescription:
            "Ajukan kunjungan lahan dan dapatkan kesempatan untuk melihat lokasi serta potensi kawasan secara langsung.",

        ctaButton: "Ajukan Kunjungan",

        close: "Tutup",

        detailRoute: "Detail Rute",

        path: "Jalur",

        originPoint: "Titik Asal",

        destinationPoint: "Titik Tujuan",

        coordinates: "Koordinat Lokasi",

        unavailableCoordinates: "Koordinat belum tersedia",

        description: "Deskripsi Rute",

        geometryAvailable: "Data geometri tersedia",

        geometryDescription:
            "Rute ini memiliki data geometri yang dapat digunakan untuk visualisasi peta.",

        needLocation: "Membutuhkan informasi lokasi secara langsung?",
    },

    en: {
        pageTitle: "Shipping Routes & Location | KITB",

        meta: "Information about shipping routes, access, locations, distances, and travel times to Tanjung Buton Industrial Estate.",

        home: "Home",
        investor: "Investor Relations",
        page: "Shipping Routes & Location",

        badge: "Access & Location",

        heading: "Shipping Routes & Location",

        intro: "Find information about routes, distances, travel times, and key locations supporting access to Tanjung Buton Industrial Estate.",

        loading: "Loading route information",

        unavailableTitle: "Route Data Unavailable",

        unavailableDescription:
            "Shipping route and location information is currently unavailable. Please check back later for the latest information.",

        investment: "View Investment Opportunities",

        listTitle: "Route List",

        searchDescription:
            "Search by route name, path, origin, or destination.",

        searchPlaceholder: "Search routes...",

        searchAria: "Search routes",

        clearSearch: "Clear search",

        searchEmptyTitle: "No Data Found",

        searchEmptyDescription:
            "No routes match the search keyword. Try a different keyword or reset the search.",

        searchKeyword: "Search:",

        resetSearch: "Reset Search",

        route: "Shipping Route",

        mapAvailable: "Map available",

        distance: "Distance",

        duration: "Travel Time",

        origin: "Origin",

        destination: "Destination",

        detail: "View Route Details",

        available: "routes available",

        reference:
            "Use route information as a reference for accessing the area.",

        visit: "Request Site Visit",

        investorLabel: "Investor Relations",

        ctaTitle: "Would you like to see KITB directly?",

        ctaDescription:
            "Request a site visit to explore the location and potential of the industrial estate firsthand.",

        ctaButton: "Request a Visit",

        close: "Close",

        detailRoute: "Route Details",

        path: "Path",

        originPoint: "Origin Point",

        destinationPoint: "Destination Point",

        coordinates: "Location Coordinates",

        unavailableCoordinates: "Coordinates are not available",

        description: "Route Description",

        geometryAvailable: "Geometry data available",

        geometryDescription:
            "This route contains geometry data that can be used for map visualization.",

        needLocation: "Need detailed location information?",
    },

    zh: {
        pageTitle: "航运路线与位置 | KITB",

        meta: "提供前往丹戎布顿工业园区的航运路线、交通、位置、距离和行程时间信息。",

        home: "首页",
        investor: "投资者关系",
        page: "航运路线与位置",

        badge: "交通与位置",

        heading: "航运路线与位置",

        intro: "了解航运路线、距离、行程时间以及支持前往丹戎布顿工业园区的重要位置。",

        loading: "正在加载路线信息",

        unavailableTitle: "暂无路线数据",

        unavailableDescription:
            "目前暂无航运路线和位置信息，请稍后再回来查看最新信息。",

        investment: "查看投资机会",

        listTitle: "路线列表",

        searchDescription: "可按路线名称、航线、起点或终点进行搜索。",

        searchPlaceholder: "搜索路线...",

        searchAria: "搜索路线",

        clearSearch: "清除搜索",

        searchEmptyTitle: "未找到数据",

        searchEmptyDescription:
            "没有符合搜索关键词的路线。请尝试其他关键词或重置搜索。",

        searchKeyword: "搜索：",

        resetSearch: "重置搜索",

        route: "航运路线",

        mapAvailable: "地图可用",

        distance: "距离",

        duration: "行程时间",

        origin: "起点",

        destination: "终点",

        detail: "查看路线详情",

        available: "条路线可用",

        reference: "路线信息可作为前往园区的交通参考。",

        visit: "申请园区参观",

        investorLabel: "投资者关系",

        ctaTitle: "希望亲自了解 KITB 园区？",

        ctaDescription: "申请园区参观，亲自了解园区位置及其发展潜力。",

        ctaButton: "申请参观",

        close: "关闭",

        detailRoute: "路线详情",

        path: "航线",

        originPoint: "起点位置",

        destinationPoint: "终点位置",

        coordinates: "位置坐标",

        unavailableCoordinates: "暂无坐标信息",

        description: "路线描述",

        geometryAvailable: "已有几何数据",

        geometryDescription: "该路线包含可用于地图可视化的几何数据。",

        needLocation: "需要了解详细位置信息？",
    },
} as const;

const t = computed(() => translations[currentLanguage.value]);

/**
 * ============================================================
 * STATE
 * ============================================================
 */

const search = ref("");

const selectedRute = ref<Rute | null>(null);

const isLoading = ref(true);

const isNavigating = ref(false);

const modalPanel = ref<HTMLElement | null>(null);

const searchInput = ref<HTMLInputElement | null>(null);

let previousActiveElement: HTMLElement | null = null;

let revealObserver: IntersectionObserver | null = null;

let loadingTimer: number | undefined;

/**
 * ============================================================
 * ACCESSIBILITY / MOTION
 * ============================================================
 */

const prefersReducedMotion =
    typeof window !== "undefined" &&
    typeof window.matchMedia === "function" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/**
 * ============================================================
 * FILTER
 * ============================================================
 */

const filteredRutes = computed(() => {
    const keyword = search.value.trim().toLocaleLowerCase();

    if (!keyword) {
        return props.rutes;
    }

    return props.rutes.filter((rute) => {
        const searchable = [
            rute.nama_rute,
            rute.nama_rute_en,
            rute.nama_rute_zh,

            rute.jalur,
            rute.jalur_en,
            rute.jalur_zh,

            rute.deskripsi,
            rute.deskripsi_en,
            rute.deskripsi_zh,

            rute.asal,
            rute.asal_en,
            rute.asal_zh,

            rute.tujuan,
            rute.tujuan_en,
            rute.tujuan_zh,

            rute.waktu_tempuh,
            rute.waktu_tempuh_en,
            rute.waktu_tempuh_zh,
        ]
            .filter((value) => value !== null && value !== undefined)
            .map((value) => String(value).trim())
            .filter(Boolean)
            .join(" ")
            .toLocaleLowerCase();

        return searchable.includes(keyword);
    });
});

/**
 * ============================================================
 * HELPERS
 * ============================================================
 */

const truncate = (value: string | null | undefined, length = 150): string => {
    if (!value) {
        return "";
    }

    if (value.length <= length) {
        return value;
    }

    return `${value.slice(0, length).trim()}…`;
};

const formatDistance = (rute: Rute): string => {
    if (rute.jarak === null || rute.jarak === undefined) {
        return "-";
    }

    return `${rute.jarak} ${rute.satuan_jarak || "km"}`;
};

const getRouteImage = (gambar: string | null): string | null => {
    if (!gambar) {
        return null;
    }

    if (
        gambar.startsWith("http://") ||
        gambar.startsWith("https://") ||
        gambar.startsWith("/")
    ) {
        return gambar;
    }

    return `/storage/${gambar}`;
};

const getCoordinates = (rute: Rute): string => {
    if (rute.latitude == null || rute.longitude == null) {
        return t.value.unavailableCoordinates;
    }

    return `${rute.latitude}, ${rute.longitude}`;
};

const hasGeometry = (rute: Rute): boolean => {
    return Boolean(
        rute.geometry &&
        typeof rute.geometry === "object" &&
        rute.geometry.type &&
        rute.geometry.coordinates,
    );
};

/**
 * ============================================================
 * MODAL
 * ============================================================
 */

const openModal = async (rute: Rute) => {
    previousActiveElement =
        document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;

    selectedRute.value = rute;

    document.body.classList.add("overflow-hidden");

    await nextTick();

    modalPanel.value?.focus();
};

const closeModal = async () => {
    selectedRute.value = null;

    document.body.classList.remove("overflow-hidden");

    await nextTick();

    previousActiveElement?.focus();

    previousActiveElement = null;
};

const handleModalKeydown = (event: KeyboardEvent) => {
    if (!selectedRute.value) {
        return;
    }

    if (event.key === "Escape") {
        event.preventDefault();

        closeModal();

        return;
    }

    if (event.key !== "Tab" || !modalPanel.value) {
        return;
    }

    const focusable = modalPanel.value.querySelectorAll<HTMLElement>(
        'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])',
    );

    if (!focusable.length) {
        event.preventDefault();

        modalPanel.value.focus();

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

/**
 * ============================================================
 * SEARCH
 * ============================================================
 */

const resetSearch = () => {
    search.value = "";

    searchInput.value?.focus();
};

/**
 * Ketika Navbar mengganti bahasa, pencarian lama dihapus
 * karena bahasa konten yang dicari ikut berubah.
 */
watch(currentLanguage, () => {
    search.value = "";
});

/**
 * ============================================================
 * NAVIGATION
 * ============================================================
 */

const navigate = (href: string) => {
    if (isNavigating.value) {
        return;
    }

    isNavigating.value = true;

    router.visit(href, {
        preserveScroll: true,

        onFinish: () => {
            isNavigating.value = false;
        },
    });
};

/**
 * ============================================================
 * REVEAL ANIMATION
 * ============================================================
 */

const setupRevealObserver = () => {
    if (typeof window === "undefined") {
        return;
    }

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
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");

                    revealObserver?.unobserve(entry.target);
                }
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

/**
 * ============================================================
 * LIFECYCLE
 * ============================================================
 */

onMounted(() => {
    loadingTimer = window.setTimeout(
        () => {
            isLoading.value = false;

            requestAnimationFrame(() => {
                setupRevealObserver();
            });
        },
        prefersReducedMotion ? 0 : 450,
    );

    window.addEventListener("keydown", handleModalKeydown);
});

/**
 * Data dari server berubah.
 */
watch(
    () => props.rutes,
    async () => {
        await nextTick();

        setupRevealObserver();
    },
    {
        deep: true,
    },
);

/**
 * Hasil filter berubah: elemen baru yang muncul
 * (card atau pesan kosong) perlu diobservasi ulang.
 */
watch(filteredRutes, async () => {
    await nextTick();

    setupRevealObserver();
});

onBeforeUnmount(() => {
    window.clearTimeout(loadingTimer);

    revealObserver?.disconnect();

    revealObserver = null;

    window.removeEventListener("keydown", handleModalKeydown);

    document.body.classList.remove("overflow-hidden");
});
</script>

<template>
    <Head>
        <title>{{ t.pageTitle }}</title>
        <meta head-key="description" name="description" :content="t.meta" />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- Decorative blob -->
        <div
            aria-hidden="true"
            class="pointer-events-none absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-900/20"
        />

        <div
            aria-hidden="true"
            class="pointer-events-none absolute -right-40 top-[38rem] h-96 w-96 rounded-full bg-slate-200/40 blur-3xl dark:bg-slate-800/20"
        />

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- =====================================================
                 Breadcrumb
                 ===================================================== -->
            <nav
                :aria-label="t.page"
                class="mb-7 flex flex-wrap items-center gap-x-2 gap-y-2 text-xs text-slate-500 dark:text-slate-400"
                data-reveal
            >
                <Link
                    href="/"
                    class="inline-flex items-center gap-1.5 rounded-lg px-1.5 py-1 transition hover:bg-slate-100 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-slate-900 dark:hover:text-blue-400"
                >
                    <Home class="h-3.5 w-3.5" />
                    <span>{{ t.home }}</span>
                </Link>

                <ChevronRight
                    aria-hidden="true"
                    class="h-3.5 w-3.5 text-slate-300 dark:text-slate-700"
                />

                <span class="inline-flex items-center gap-1.5">
                    <BriefcaseBusiness class="h-3.5 w-3.5" />
                    <span>{{ t.investor }}</span>
                </span>

                <ChevronRight
                    aria-hidden="true"
                    class="h-3.5 w-3.5 text-slate-300 dark:text-slate-700"
                />

                <span
                    aria-current="page"
                    class="inline-flex items-center gap-1.5 font-semibold text-blue-600 dark:text-blue-400"
                >
                    <RouteIcon class="h-3.5 w-3.5" />
                    <span>{{ t.page }}</span>
                </span>
            </nav>

            <!-- =====================================================
                 Header
                 ===================================================== -->
            <header class="mb-8 max-w-3xl" data-reveal style="--d: 80ms">
                <div
                    class="mb-3 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300"
                >
                    <Navigation class="h-3.5 w-3.5" />
                    {{ t.badge }}
                </div>

                <h1
                    class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                >
                    {{ t.heading }}
                </h1>

                <p
                    class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    {{ t.intro }}
                </p>
            </header>

            <!-- =====================================================
                 Loading Skeleton
                 ===================================================== -->
            <section
                v-if="isLoading"
                :aria-label="t.loading"
                class="grid gap-5 md:grid-cols-2 lg:grid-cols-3"
            >
                <article
                    v-for="index in 6"
                    :key="index"
                    class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="skeleton-shimmer h-44 w-full bg-slate-200 dark:bg-slate-800"
                    />

                    <div class="space-y-4 p-6">
                        <div
                            class="skeleton-shimmer h-5 w-3/4 rounded-lg bg-slate-200 dark:bg-slate-800"
                        />

                        <div class="space-y-2">
                            <div
                                class="skeleton-shimmer h-3 w-full rounded bg-slate-200 dark:bg-slate-800"
                            />
                            <div
                                class="skeleton-shimmer h-3 w-5/6 rounded bg-slate-200 dark:bg-slate-800"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div
                                class="skeleton-shimmer h-14 rounded-xl bg-slate-200 dark:bg-slate-800"
                            />
                            <div
                                class="skeleton-shimmer h-14 rounded-xl bg-slate-200 dark:bg-slate-800"
                            />
                        </div>

                        <div
                            class="skeleton-shimmer h-10 w-full rounded-xl bg-slate-200 dark:bg-slate-800"
                        />
                    </div>
                </article>
            </section>

            <template v-else>
                <!-- =================================================
                     Database Empty
                     ================================================= -->
                <section
                    v-if="props.rutes.length === 0"
                    class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
                    data-reveal
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <RouteIcon class="h-8 w-8" />
                    </div>

                    <h2
                        class="mt-5 text-xl font-bold text-slate-900 dark:text-white"
                    >
                        {{ t.unavailableTitle }}
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ t.unavailableDescription }}
                    </p>

                    <div class="mt-6">
                        <Link
                            :href="INVESTMENT_URL"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                        >
                            {{ t.investment }}
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>
                </section>

                <template v-else>
                    <!-- =================================================
                         Search
                         ================================================= -->
                    <section
                        class="mb-7 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900"
                        data-reveal
                        style="--d: 120ms"
                    >
                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <div
                                    class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white"
                                >
                                    <Map
                                        class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                    />
                                    {{ t.listTitle }}
                                </div>

                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ t.searchDescription }}
                                </p>
                            </div>

                            <div class="relative w-full sm:max-w-sm">
                                <Search
                                    aria-hidden="true"
                                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    ref="searchInput"
                                    v-model="search"
                                    type="search"
                                    :aria-label="t.searchAria"
                                    :placeholder="t.searchPlaceholder"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-10 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-blue-500 dark:focus:bg-slate-950"
                                />

                                <button
                                    v-if="search"
                                    type="button"
                                    :aria-label="t.clearSearch"
                                    class="absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-200 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                    @click="resetSearch"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- =================================================
                         Search Empty
                         (tanpa data-reveal agar selalu langsung tampil)
                         ================================================= -->
                    <section
                        v-if="filteredRutes.length === 0"
                        role="status"
                        aria-live="polite"
                        class="empty-fade rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400"
                        >
                            <Search class="h-8 w-8" />
                        </div>

                        <h2
                            class="mt-5 text-xl font-bold text-slate-900 dark:text-white"
                        >
                            {{ t.searchEmptyTitle }}
                        </h2>

                        <p
                            class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{ t.searchEmptyDescription }}
                        </p>

                        <div
                            v-if="search"
                            class="mx-auto mt-4 inline-flex max-w-full items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                        >
                            <Search
                                class="h-3.5 w-3.5 shrink-0 text-slate-400"
                            />
                            <span>{{ t.searchKeyword }}</span>
                            <span
                                class="max-w-[240px] truncate font-semibold text-slate-900 dark:text-white"
                                :title="search"
                            >
                                "{{ search }}"
                            </span>
                        </div>

                        <button
                            type="button"
                            class="mt-6 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:bg-blue-950/30 dark:hover:text-blue-300 dark:focus:ring-offset-slate-900"
                            @click="resetSearch"
                        >
                            <X class="h-4 w-4" />
                            {{ t.resetSearch }}
                        </button>
                    </section>

                    <!-- =================================================
                         Route Cards
                         ================================================= -->
                    <section
                        v-else
                        :aria-label="t.listTitle"
                        class="grid gap-5 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="(rute, index) in filteredRutes"
                            :key="rute.id"
                            data-reveal
                            :style="{
                                '--d': `${160 + index * 70}ms`,
                            }"
                            class="group overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:hover:shadow-black/20"
                        >
                            <!-- Image -->
                            <div
                                class="relative h-44 overflow-hidden bg-gradient-to-br from-blue-50 via-slate-100 to-slate-200 dark:from-blue-950/40 dark:via-slate-900 dark:to-slate-800"
                            >
                                <img
                                    v-if="getRouteImage(rute.gambar)"
                                    :src="getRouteImage(rute.gambar)!"
                                    :alt="
                                        localized(rute, 'nama_rute') || t.route
                                    "
                                    loading="lazy"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                />

                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center"
                                >
                                    <div
                                        class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/80 text-blue-600 shadow-sm backdrop-blur dark:bg-slate-900/80 dark:text-blue-400"
                                    >
                                        <Ship class="h-8 w-8" />
                                    </div>
                                </div>

                                <div
                                    class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full border border-white/60 bg-white/90 px-3 py-1.5 text-[11px] font-semibold text-slate-700 shadow-sm backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/90 dark:text-slate-200"
                                >
                                    <Anchor
                                        class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                                    />
                                    {{ t.route }}
                                </div>

                                <div
                                    v-if="hasGeometry(rute)"
                                    class="absolute bottom-4 right-4 inline-flex items-center gap-1.5 rounded-full border border-white/60 bg-white/90 px-2.5 py-1.5 text-[10px] font-semibold text-slate-700 shadow-sm backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/90 dark:text-slate-200"
                                >
                                    <Map
                                        class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                                    />
                                    {{ t.mapAvailable }}
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-6">
                                <h2
                                    class="line-clamp-2 text-lg font-bold leading-7 text-slate-900 dark:text-white"
                                >
                                    {{ localized(rute, "nama_rute") }}
                                </h2>

                                <div
                                    v-if="localized(rute, 'jalur')"
                                    class="mt-2 flex items-start gap-2 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    <RouteIcon
                                        class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    />
                                    <span class="line-clamp-2">
                                        {{ localized(rute, "jalur") }}
                                    </span>
                                </div>

                                <!-- Route meta -->
                                <div class="mt-5 grid grid-cols-2 gap-3">
                                    <div
                                        class="rounded-2xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950"
                                    >
                                        <div
                                            class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"
                                        >
                                            <MapPin class="h-3.5 w-3.5" />
                                            {{ t.distance }}
                                        </div>

                                        <div
                                            class="mt-1 text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ formatDistance(rute) }}
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950"
                                    >
                                        <div
                                            class="flex items-center gap-1.5 text-[11px] font-medium text-slate-400"
                                        >
                                            <Clock3 class="h-3.5 w-3.5" />
                                            {{ t.duration }}
                                        </div>

                                        <div
                                            class="mt-1 line-clamp-1 text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                localized(rute, "waktu_tempuh")
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Origin destination -->
                                <div
                                    v-if="
                                        localized(rute, 'asal') ||
                                        localized(rute, 'tujuan')
                                    "
                                    class="mt-4 rounded-2xl border border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900"
                                >
                                    <div
                                        v-if="localized(rute, 'asal')"
                                        class="flex items-start gap-3 border-b border-slate-100 px-4 py-3 dark:border-slate-800"
                                    >
                                        <div
                                            class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        >
                                            <Navigation class="h-3.5 w-3.5" />
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"
                                            >
                                                {{ t.origin }}
                                            </p>
                                            <p
                                                class="mt-0.5 truncate text-xs font-semibold text-slate-700 dark:text-slate-200"
                                            >
                                                {{ localized(rute, "asal") }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="localized(rute, 'tujuan')"
                                        class="flex items-start gap-3 px-4 py-3"
                                    >
                                        <div
                                            class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                        >
                                            <MapPin class="h-3.5 w-3.5" />
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"
                                            >
                                                {{ t.destination }}
                                            </p>
                                            <p
                                                class="mt-0.5 truncate text-xs font-semibold text-slate-700 dark:text-slate-200"
                                            >
                                                {{ localized(rute, "tujuan") }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <p
                                    v-if="localized(rute, 'deskripsi')"
                                    class="mt-4 line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        truncate(
                                            localized(rute, "deskripsi"),
                                            120,
                                        )
                                    }}
                                </p>

                                <button
                                    type="button"
                                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:bg-blue-950/30 dark:hover:text-blue-300 dark:focus:ring-offset-slate-900"
                                    @click="openModal(rute)"
                                >
                                    {{ t.detail }}
                                    <ArrowRight class="h-4 w-4" />
                                </button>
                            </div>
                        </article>
                    </section>

                    <!-- =================================================
                         Summary
                         ================================================= -->
                    <div
                        v-if="filteredRutes.length > 0"
                        class="mt-8 flex flex-col gap-3 rounded-2xl border border-blue-100 bg-blue-50/60 px-5 py-4 text-sm sm:flex-row sm:items-center sm:justify-between dark:border-blue-900/40 dark:bg-blue-950/20"
                        data-reveal
                        style="--d: 300ms"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400"
                            >
                                <CheckCircle2 class="h-4 w-4" />
                            </div>

                            <div>
                                <p
                                    class="font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    {{ filteredRutes.length }} {{ t.available }}
                                </p>

                                <p
                                    class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ t.reference }}
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="VISIT_URL"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950"
                        >
                            {{ t.visit }}
                            <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>

                    <!-- =================================================
                         Bottom CTA
                         ================================================= -->
                    <section
                        class="relative mt-12 overflow-hidden rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9 dark:border-slate-800 dark:bg-slate-900"
                        data-reveal
                        style="--d: 360ms"
                    >
                        <div
                            aria-hidden="true"
                            class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-blue-100/60 blur-3xl dark:bg-blue-900/20"
                        />

                        <div
                            class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <div class="max-w-2xl">
                                <div
                                    class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.14em] text-blue-600 dark:text-blue-400"
                                >
                                    <Building2 class="h-4 w-4" />
                                    {{ t.investorLabel }}
                                </div>

                                <h2
                                    class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                                >
                                    {{ t.ctaTitle }}
                                </h2>

                                <p
                                    class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                                >
                                    {{ t.ctaDescription }}
                                </p>
                            </div>

                            <Link
                                :href="VISIT_URL"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                            >
                                {{ t.ctaButton }}
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </section>
                </template>
            </template>
        </div>

        <!-- =========================================================
             Detail Modal
             ========================================================= -->
        <Transition name="modal">
            <div
                v-if="selectedRute"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="`rute-modal-title-${selectedRute.id}`"
            >
                <button
                    type="button"
                    :aria-label="t.close"
                    class="absolute inset-0 cursor-default bg-slate-950/60 backdrop-blur-sm"
                    @click="closeModal"
                />

                <section
                    ref="modalPanel"
                    tabindex="-1"
                    class="relative z-10 flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl outline-none dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Modal header image -->
                    <div
                        class="relative h-48 shrink-0 overflow-hidden bg-gradient-to-br from-blue-50 via-slate-100 to-slate-200 sm:h-56 dark:from-blue-950/40 dark:via-slate-900 dark:to-slate-800"
                    >
                        <img
                            v-if="getRouteImage(selectedRute.gambar)"
                            :src="getRouteImage(selectedRute.gambar)!"
                            :alt="
                                localized(selectedRute, 'nama_rute') || t.route
                            "
                            class="h-full w-full object-cover"
                        />

                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center"
                        >
                            <div
                                class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/80 text-blue-600 shadow-sm backdrop-blur dark:bg-slate-900/80 dark:text-blue-400"
                            >
                                <Ship class="h-10 w-10" />
                            </div>
                        </div>

                        <div
                            class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/70 to-transparent p-5 sm:p-6"
                        >
                            <div
                                class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:bg-slate-900/90 dark:text-slate-200"
                            >
                                <Anchor
                                    class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                                />
                                {{ t.route }}
                            </div>

                            <h2
                                :id="`rute-modal-title-${selectedRute.id}`"
                                class="text-xl font-bold text-white sm:text-2xl"
                            >
                                {{ localized(selectedRute, "nama_rute") }}
                            </h2>
                        </div>

                        <button
                            type="button"
                            :aria-label="t.close"
                            class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/20 bg-slate-950/40 text-white backdrop-blur transition hover:bg-slate-950/60 focus:outline-none focus:ring-2 focus:ring-white/80"
                            @click="closeModal"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Modal body -->
                    <div class="overflow-y-auto p-5 sm:p-7">
                        <!-- Jalur -->
                        <div
                            v-if="localized(selectedRute, 'jalur')"
                            class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4 dark:border-blue-900/40 dark:bg-blue-950/20"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400"
                                >
                                    <RouteIcon class="h-4 w-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400"
                                    >
                                        {{ t.path }}
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold leading-6 text-slate-800 dark:text-slate-200"
                                    >
                                        {{ localized(selectedRute, "jalur") }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <div
                                    class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    <MapPin
                                        class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                    />
                                    {{ t.distance }}
                                </div>

                                <p
                                    class="mt-1.5 text-base font-bold text-slate-900 dark:text-white"
                                >
                                    {{ formatDistance(selectedRute) }}
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <div
                                    class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    <Clock3
                                        class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                    />
                                    {{ t.duration }}
                                </div>

                                <p
                                    class="mt-1.5 text-base font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        localized(selectedRute, "waktu_tempuh")
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Origin destination -->
                        <div
                            v-if="
                                localized(selectedRute, 'asal') ||
                                localized(selectedRute, 'tujuan')
                            "
                            class="mt-5 rounded-2xl border border-slate-200 dark:border-slate-800"
                        >
                            <div
                                v-if="localized(selectedRute, 'asal')"
                                class="flex items-start gap-3 border-b border-slate-200 p-4 dark:border-slate-800"
                            >
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    <Navigation class="h-4 w-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        {{ t.originPoint }}
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ localized(selectedRute, "asal") }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="localized(selectedRute, 'tujuan')"
                                class="flex items-start gap-3 p-4"
                            >
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <MapPin class="h-4 w-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        {{ t.destinationPoint }}
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ localized(selectedRute, "tujuan") }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Coordinates -->
                        <div
                            v-if="
                                selectedRute.latitude != null &&
                                selectedRute.longitude != null
                            "
                            class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    <MapPin class="h-4 w-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        {{ t.coordinates }}
                                    </p>

                                    <p
                                        class="mt-1 font-mono text-xs text-slate-700 dark:text-slate-300"
                                    >
                                        {{ getCoordinates(selectedRute) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div
                            v-if="localized(selectedRute, 'deskripsi')"
                            class="mt-6"
                        >
                            <div
                                class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white"
                            >
                                <FileText
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                />
                                {{ t.description }}
                            </div>

                            <div
                                class="prose prose-sm max-w-none leading-7 text-slate-600 dark:prose-invert dark:text-slate-300"
                            >
                                <p class="whitespace-pre-line">
                                    {{ localized(selectedRute, "deskripsi") }}
                                </p>
                            </div>
                        </div>

                        <!-- Geometry status -->
                        <div
                            v-if="hasGeometry(selectedRute)"
                            class="mt-6 flex items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20"
                        >
                            <CheckCircle2
                                class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                            />

                            <div>
                                <p
                                    class="text-sm font-semibold text-emerald-800 dark:text-emerald-300"
                                >
                                    {{ t.geometryAvailable }}
                                </p>

                                <p
                                    class="mt-1 text-xs leading-5 text-emerald-700/80 dark:text-emerald-300/70"
                                >
                                    {{ t.geometryDescription }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div
                        class="flex shrink-0 flex-col gap-3 border-t border-slate-200 bg-slate-50/80 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5 dark:border-slate-800 dark:bg-slate-950/70"
                    >
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ t.needLocation }}
                        </p>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-950"
                                @click="closeModal"
                            >
                                {{ t.close }}
                            </button>

                            <Link
                                :href="VISIT_URL"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950"
                                @click="closeModal"
                            >
                                {{ t.visit }}
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </section>
            </div>
        </Transition>
    </main>
</template>

<style scoped>
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
   Empty search result (selalu tampil, tanpa observer)
   ========================================================= */

.empty-fade {
    animation: emptyFade 300ms ease both;
}

@keyframes emptyFade {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

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
        rgba(255, 255, 255, 0.55),
        transparent
    );
    animation: shimmer 1.5s infinite;
    content: "";
}

@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}

/* =========================================================
   Modal
   ========================================================= */

.modal-enter-active,
.modal-leave-active {
    transition:
        opacity 220ms ease,
        transform 220ms ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from section,
.modal-leave-to section {
    transform: translateY(12px) scale(0.98);
}

.modal-enter-active section,
.modal-leave-active section {
    transition: transform 220ms ease;
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

    .empty-fade {
        animation: none;
    }

    .skeleton-shimmer::after {
        animation: none;
    }

    .modal-enter-active,
    .modal-leave-active,
    .modal-enter-active section,
    .modal-leave-active section {
        transition: none;
    }

    .group,
    .group img {
        transition: none !important;
    }
}
</style>
