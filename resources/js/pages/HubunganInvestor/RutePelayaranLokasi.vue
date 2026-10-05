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
    CheckCircle2,
    ChevronLeft,
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

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
   Types
   ========================================================= */

interface Geometry {
    type?: "Point" | "LineString" | "MultiLineString" | string;
    coordinates?: unknown;
}

interface Rute {
    id: number;
    nama_rute: string;
    jalur: string;
    jarak: string | number | null;
    satuan_jarak: string;
    waktu_tempuh: string;
    deskripsi: string | null;
    asal: string | null;
    tujuan: string | null;
    latitude: string | number | null;
    longitude: string | number | null;
    geometry: Geometry | null;
    gambar: string | null;
    urutan: number;
    aktif: boolean;
}

/* =========================================================
   Props
   ========================================================= */

const props = defineProps<{
    rutes: Rute[];
}>();

/* =========================================================
   State
   ========================================================= */

const search = ref("");
const selectedRute = ref<Rute | null>(null);

const isLoading = ref(true);
const isNavigating = ref(false);

const modalPanel = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);

let previousActiveElement: HTMLElement | null = null;
let revealObserver: IntersectionObserver | null = null;

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/* =========================================================
   Computed
   ========================================================= */

const filteredRutes = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    if (!keyword) {
        return props.rutes;
    }

    return props.rutes.filter((rute) => {
        const searchable = [
            rute.nama_rute,
            rute.jalur,
            rute.asal,
            rute.tujuan,
            rute.waktu_tempuh,
            rute.deskripsi,
        ]
            .filter(Boolean)
            .join(" ")
            .toLowerCase();

        return searchable.includes(keyword);
    });
});

/* =========================================================
   Helpers
   ========================================================= */

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
        return "Koordinat belum tersedia";
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

/* =========================================================
   Modal
   ========================================================= */

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

/* =========================================================
   Search
   ========================================================= */

const resetSearch = () => {
    search.value = "";
    searchInput.value?.focus();
};

/* =========================================================
   Navigation
   ========================================================= */

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

/* =========================================================
   Reveal animation
   ========================================================= */

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
        .querySelectorAll<HTMLElement>("[data-reveal]")
        .forEach((element) => {
            revealObserver?.observe(element);
        });
};

/* =========================================================
   Lifecycle
   ========================================================= */

onMounted(() => {
    const timer = window.setTimeout(
        () => {
            isLoading.value = false;

            requestAnimationFrame(() => {
                setupRevealObserver();
            });
        },
        prefersReducedMotion ? 0 : 450,
    );

    window.addEventListener("keydown", handleModalKeydown);

    onBeforeUnmount(() => {
        window.clearTimeout(timer);
    });
});

watch(
    () => props.rutes,
    async () => {
        await nextTick();
        setupRevealObserver();
    },
    { deep: true },
);

onBeforeUnmount(() => {
    revealObserver?.disconnect();
    revealObserver = null;

    window.removeEventListener("keydown", handleModalKeydown);

    document.body.classList.remove("overflow-hidden");
});
</script>

<template>
    <Head title="Rute Pelayaran & Lokasi">
        <meta
            name="description"
            content="Informasi rute pelayaran, akses, lokasi, jarak, dan waktu tempuh menuju kawasan industri Tanjung Buton."
        />
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
                aria-label="Breadcrumb"
                class="mb-7 flex flex-wrap items-center gap-x-2 gap-y-2 text-xs text-slate-500 dark:text-slate-400"
                data-reveal
            >
                <Link
                    href="/"
                    class="inline-flex items-center gap-1.5 rounded-lg px-1.5 py-1 transition hover:bg-slate-100 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-slate-900 dark:hover:text-blue-400"
                >
                    <Home class="h-3.5 w-3.5" />
                    <span>Beranda</span>
                </Link>

                <ChevronRight
                    aria-hidden="true"
                    class="h-3.5 w-3.5 text-slate-300 dark:text-slate-700"
                />

                <span class="inline-flex items-center gap-1.5">
                    <BriefcaseBusiness class="h-3.5 w-3.5" />
                    <span>Hubungan Investor</span>
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
                    <span>Rute Pelayaran & Lokasi</span>
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
                    Akses & Lokasi
                </div>

                <h1
                    class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                >
                    Rute Pelayaran & Lokasi
                </h1>

                <p
                    class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    Temukan informasi rute, jarak, waktu tempuh, serta lokasi
                    utama untuk mendukung akses menuju kawasan industri Tanjung
                    Buton.
                </p>
            </header>

            <!-- =====================================================
                 Loading Skeleton
                 ===================================================== -->
            <section
                v-if="isLoading"
                aria-label="Memuat informasi rute"
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
                        Data Rute Belum Tersedia
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Informasi rute pelayaran dan lokasi saat ini belum
                        tersedia. Silakan kembali lagi untuk mendapatkan
                        informasi terbaru.
                    </p>

                    <div class="mt-6">
                        <Link
                            href="/hubungan-investor/peluang-investasi"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                        >
                            Lihat Peluang Investasi
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
                                    Daftar Rute
                                </div>

                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Cari berdasarkan nama rute, jalur, asal,
                                    atau tujuan.
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
                                    aria-label="Cari rute"
                                    placeholder="Cari rute..."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-10 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-blue-500 dark:focus:bg-slate-950"
                                />

                                <button
                                    v-if="search"
                                    type="button"
                                    aria-label="Hapus pencarian"
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
                         ================================================= -->
                    <section
                        v-if="filteredRutes.length === 0"
                        class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
                        data-reveal
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                        >
                            <Search class="h-8 w-8" />
                        </div>

                        <h2
                            class="mt-5 text-xl font-bold text-slate-900 dark:text-white"
                        >
                            Data Tidak Ditemukan
                        </h2>

                        <p
                            class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Tidak ada rute yang sesuai dengan kata kunci
                            pencarian
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                "{{ search }}"
                            </span>
                            .
                        </p>

                        <button
                            type="button"
                            class="mt-6 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:bg-blue-950/30 dark:hover:text-blue-300 dark:focus:ring-offset-slate-900"
                            @click="resetSearch"
                        >
                            <X class="h-4 w-4" />
                            Reset Pencarian
                        </button>
                    </section>

                    <!-- =================================================
                         Route Cards
                         ================================================= -->
                    <section
                        v-else
                        aria-label="Daftar rute pelayaran"
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
                                    :alt="`Ilustrasi ${rute.nama_rute}`"
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
                                    Rute Pelayaran
                                </div>

                                <div
                                    v-if="hasGeometry(rute)"
                                    class="absolute bottom-4 right-4 inline-flex items-center gap-1.5 rounded-full border border-white/60 bg-white/90 px-2.5 py-1.5 text-[10px] font-semibold text-slate-700 shadow-sm backdrop-blur dark:border-slate-700/60 dark:bg-slate-900/90 dark:text-slate-200"
                                >
                                    <Map
                                        class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                                    />
                                    Peta tersedia
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-6">
                                <h2
                                    class="line-clamp-2 text-lg font-bold leading-7 text-slate-900 dark:text-white"
                                >
                                    {{ rute.nama_rute }}
                                </h2>

                                <div
                                    v-if="rute.jalur"
                                    class="mt-2 flex items-start gap-2 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    <RouteIcon
                                        class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    />
                                    <span class="line-clamp-2">
                                        {{ rute.jalur }}
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
                                            Jarak
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
                                            Waktu
                                        </div>

                                        <div
                                            class="mt-1 line-clamp-1 text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ rute.waktu_tempuh }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Origin destination -->
                                <div
                                    v-if="rute.asal || rute.tujuan"
                                    class="mt-4 rounded-2xl border border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900"
                                >
                                    <div
                                        v-if="rute.asal"
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
                                                Asal
                                            </p>
                                            <p
                                                class="mt-0.5 truncate text-xs font-semibold text-slate-700 dark:text-slate-200"
                                            >
                                                {{ rute.asal }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="rute.tujuan"
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
                                                Tujuan
                                            </p>
                                            <p
                                                class="mt-0.5 truncate text-xs font-semibold text-slate-700 dark:text-slate-200"
                                            >
                                                {{ rute.tujuan }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <p
                                    v-if="rute.deskripsi"
                                    class="mt-4 line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    {{ truncate(rute.deskripsi, 120) }}
                                </p>

                                <button
                                    type="button"
                                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:bg-blue-950/30 dark:hover:text-blue-300 dark:focus:ring-offset-slate-900"
                                    @click="openModal(rute)"
                                >
                                    Lihat Detail Rute
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
                                    {{ filteredRutes.length }} rute tersedia
                                </p>

                                <p
                                    class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Gunakan informasi rute sebagai referensi
                                    akses menuju kawasan.
                                </p>
                            </div>
                        </div>

                        <Link
                            href="/ajukan-kunjungan"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950"
                        >
                            Ajukan Kunjungan Lahan
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
                                    Hubungan Investor
                                </div>

                                <h2
                                    class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                                >
                                    Ingin melihat langsung kawasan KITB?
                                </h2>

                                <p
                                    class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                                >
                                    Ajukan kunjungan lahan dan dapatkan
                                    kesempatan untuk melihat lokasi serta
                                    potensi kawasan secara langsung.
                                </p>
                            </div>

                            <Link
                                href="/ajukan-kunjungan"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                            >
                                Ajukan Kunjungan
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
                    aria-label="Tutup dialog"
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
                            :alt="`Ilustrasi ${selectedRute.nama_rute}`"
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
                                Rute Pelayaran
                            </div>

                            <h2
                                :id="`rute-modal-title-${selectedRute.id}`"
                                class="text-xl font-bold text-white sm:text-2xl"
                            >
                                {{ selectedRute.nama_rute }}
                            </h2>
                        </div>

                        <button
                            type="button"
                            aria-label="Tutup detail rute"
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
                            v-if="selectedRute.jalur"
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
                                        Jalur
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold leading-6 text-slate-800 dark:text-slate-200"
                                    >
                                        {{ selectedRute.jalur }}
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
                                    Jarak
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
                                    Waktu Tempuh
                                </div>

                                <p
                                    class="mt-1.5 text-base font-bold text-slate-900 dark:text-white"
                                >
                                    {{ selectedRute.waktu_tempuh }}
                                </p>
                            </div>
                        </div>

                        <!-- Origin destination -->
                        <div
                            v-if="selectedRute.asal || selectedRute.tujuan"
                            class="mt-5 rounded-2xl border border-slate-200 dark:border-slate-800"
                        >
                            <div
                                v-if="selectedRute.asal"
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
                                        Titik Asal
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ selectedRute.asal }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="selectedRute.tujuan"
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
                                        Titik Tujuan
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ selectedRute.tujuan }}
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
                                        Koordinat Lokasi
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
                        <div v-if="selectedRute.deskripsi" class="mt-6">
                            <div
                                class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white"
                            >
                                <FileText
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                />
                                Deskripsi Rute
                            </div>

                            <div
                                class="prose prose-sm max-w-none leading-7 text-slate-600 dark:prose-invert dark:text-slate-300"
                            >
                                <p class="whitespace-pre-line">
                                    {{ selectedRute.deskripsi }}
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
                                    Data geometri tersedia
                                </p>

                                <p
                                    class="mt-1 text-xs leading-5 text-emerald-700/80 dark:text-emerald-300/70"
                                >
                                    Rute ini memiliki data geometri yang dapat
                                    digunakan untuk visualisasi peta.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div
                        class="flex shrink-0 flex-col gap-3 border-t border-slate-200 bg-slate-50/80 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5 dark:border-slate-800 dark:bg-slate-950/70"
                    >
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Membutuhkan informasi lokasi secara langsung?
                        </p>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-950"
                                @click="closeModal"
                            >
                                Tutup
                            </button>

                            <Link
                                href="/hubungan-investor/ajukan-kunjungan"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950"
                                @click="closeModal"
                            >
                                Ajukan Kunjungan
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
