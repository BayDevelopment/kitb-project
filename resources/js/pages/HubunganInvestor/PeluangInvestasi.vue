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
    BriefcaseBusiness,
    Building2,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleDollarSign,
    Factory,
    Filter,
    Home,
    LandPlot,
    MapPin,
    Search,
    X,
} from "lucide-vue-next";
import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
   TYPES
========================================================= */

interface PeluangInvestasi {
    id: number;
    judul: string;
    slug: string;
    sektor_industri: string | null;
    deskripsi: string | null;
    luas_lahan: number | string | null;
    satuan_luas: string | null;
    lokasi: string | null;
    status: string;
    nilai_investasi: number | string | null;
    mata_uang: string | null;
    gambar: string | null;
    urutan: number;
    aktif: boolean;
    created_at: string;
}

interface LinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: LinkItem[];
}

/* =========================================================
   PROPS
========================================================= */

const props = defineProps<{
    peluangInvestasis: Paginator<PeluangInvestasi>;
}>();

/* =========================================================
   STATE
========================================================= */

const search = ref("");
const selectedSector = ref("all");
const selectedStatus = ref("all");
const showFilters = ref(false);

const selectedInvestment = ref<PeluangInvestasi | null>(null);

const isLoading = ref(true);
const isNavigating = ref(false);

const searchInput = ref<HTMLInputElement | null>(null);
const modalPanel = ref<HTMLElement | null>(null);

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/* =========================================================
   HELPERS
========================================================= */

const imageUrl = (path: string | null) => {
    if (!path) {
        return "";
    }

    if (
        path.startsWith("http://") ||
        path.startsWith("https://") ||
        path.startsWith("/")
    ) {
        return path;
    }

    return `/storage/${path}`;
};

const formatNumber = (
    value: number | string | null,
    maximumFractionDigits = 2,
) => {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    const number = Number(value);

    if (Number.isNaN(number)) {
        return "-";
    }

    return new Intl.NumberFormat("id-ID", {
        maximumFractionDigits,
    }).format(number);
};

const formatArea = (investment: PeluangInvestasi) => {
    if (
        investment.luas_lahan === null ||
        investment.luas_lahan === undefined ||
        investment.luas_lahan === ""
    ) {
        return "-";
    }

    return `${formatNumber(investment.luas_lahan)} ${
        investment.satuan_luas || "Ha"
    }`;
};

const formatInvestmentValue = (investment: PeluangInvestasi) => {
    if (
        investment.nilai_investasi === null ||
        investment.nilai_investasi === undefined ||
        investment.nilai_investasi === ""
    ) {
        return "Hubungi Kami";
    }

    const value = Number(investment.nilai_investasi);

    if (Number.isNaN(value)) {
        return "Hubungi Kami";
    }

    const currency = investment.mata_uang || "IDR";

    if (currency.toUpperCase() === "IDR") {
        return `Rp ${new Intl.NumberFormat("id-ID", {
            maximumFractionDigits: 0,
        }).format(value)}`;
    }

    return `${currency} ${new Intl.NumberFormat("id-ID", {
        maximumFractionDigits: 2,
    }).format(value)}`;
};

const formatStatus = (status: string) => {
    const labels: Record<string, string> = {
        tersedia: "Tersedia",
        proses: "Dalam Proses",
        terisi: "Terisi",
        tidak_tersedia: "Tidak Tersedia",
    };

    return (
        labels[status.toLowerCase()] ||
        status
            .replace(/_/g, " ")
            .replace(/\b\w/g, (letter) => letter.toUpperCase())
    );
};

const statusClass = (status: string) => {
    switch (status.toLowerCase()) {
        case "tersedia":
            return "text-emerald-700 dark:text-emerald-400";

        case "proses":
            return "text-amber-700 dark:text-amber-400";

        case "terisi":
            return "text-blue-700 dark:text-blue-400";

        case "tidak_tersedia":
            return "text-rose-700 dark:text-rose-400";

        default:
            return "text-slate-700 dark:text-slate-300";
    }
};

const truncate = (value: string | null, length = 150) => {
    if (!value) {
        return "";
    }

    return value.length > length
        ? `${value.substring(0, length).trim()}...`
        : value;
};

/* =========================================================
   FILTER OPTIONS
========================================================= */

const sectors = computed(() => {
    const values = props.peluangInvestasis.data
        .map((item) => item.sektor_industri)
        .filter((value): value is string => Boolean(value));

    return [...new Set(values)].sort((a, b) => a.localeCompare(b));
});

const statuses = computed(() => {
    const values = props.peluangInvestasis.data
        .map((item) => item.status)
        .filter(Boolean);

    return [...new Set(values)];
});

/* =========================================================
   FILTERED DATA
========================================================= */

const filteredInvestments = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    return props.peluangInvestasis.data.filter((investment) => {
        const matchesSearch =
            !keyword ||
            investment.judul.toLowerCase().includes(keyword) ||
            investment.sektor_industri?.toLowerCase().includes(keyword) ||
            investment.lokasi?.toLowerCase().includes(keyword) ||
            investment.deskripsi?.toLowerCase().includes(keyword);

        const matchesSector =
            selectedSector.value === "all" ||
            investment.sektor_industri === selectedSector.value;

        const matchesStatus =
            selectedStatus.value === "all" ||
            investment.status === selectedStatus.value;

        return matchesSearch && matchesSector && matchesStatus;
    });
});

const hasActiveFilter = computed(() => {
    return (
        search.value.trim() !== "" ||
        selectedSector.value !== "all" ||
        selectedStatus.value !== "all"
    );
});

/* =========================================================
   FILTER ACTION
========================================================= */

const resetFilters = () => {
    search.value = "";
    selectedSector.value = "all";
    selectedStatus.value = "all";
};

/* =========================================================
   PAGINATION
========================================================= */

const goToPage = (url: string | null) => {
    if (!url || isNavigating.value) {
        return;
    }

    isNavigating.value = true;
    isLoading.value = true;

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ["peluangInvestasis"],
            onFinish: () => {
                isNavigating.value = false;

                window.requestAnimationFrame(() => {
                    isLoading.value = false;
                });
            },
        },
    );
};

const isPreviousLink = (link: LinkItem, index: number) => {
    if (index === 0) {
        return true;
    }

    return (
        link.label.toLowerCase().includes("previous") ||
        link.label.toLowerCase().includes("prev")
    );
};

const isNextLink = (link: LinkItem, index: number, links: LinkItem[]) => {
    if (index === links.length - 1) {
        return true;
    }

    return link.label.toLowerCase().includes("next");
};

/* =========================================================
   MODAL
========================================================= */

const openModal = async (investment: PeluangInvestasi) => {
    selectedInvestment.value = investment;

    await nextTick();

    document.body.style.overflow = "hidden";

    modalPanel.value?.focus();
};

const closeModal = () => {
    selectedInvestment.value = null;
    document.body.style.overflow = "";
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === "Escape" && selectedInvestment.value) {
        closeModal();
    }
};

/* =========================================================
   REVEAL
========================================================= */

let revealObserver: IntersectionObserver | null = null;

const initializeReveal = () => {
    if (typeof window === "undefined") {
        return;
    }

    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (prefersReducedMotion) {
        elements.forEach((element) => {
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

    elements.forEach((element) => {
        revealObserver?.observe(element);
    });
};

/* =========================================================
   LIFECYCLE
========================================================= */

onMounted(() => {
    window.addEventListener("keydown", handleKeydown);

    if (props.peluangInvestasis) {
        window.requestAnimationFrame(() => {
            isLoading.value = false;

            nextTick(() => {
                initializeReveal();
            });
        });
    } else {
        isLoading.value = false;
    }
});

watch(
    () => props.peluangInvestasis.data,
    async () => {
        isLoading.value = false;

        await nextTick();

        initializeReveal();
    },
);

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeydown);

    revealObserver?.disconnect();

    document.body.style.overflow = "";
});
</script>

<template>
    <Head>
        <title>Peluang Investasi | KITB</title>

        <meta
            name="description"
            content="Temukan berbagai peluang investasi yang tersedia di Kawasan Industri Tanjung Buton."
        />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- Decorative Blob -->
        <div
            class="pointer-events-none absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-900/20"
            aria-hidden="true"
        />

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- =====================================================
                 BREADCRUMB
            ====================================================== -->

            <nav
                aria-label="Breadcrumb"
                class="mb-6"
                data-reveal
                style="--d: 0ms"
            >
                <ol
                    class="flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                >
                    <li class="flex items-center gap-2">
                        <Link
                            href="/"
                            class="inline-flex items-center gap-1.5 transition hover:text-blue-600 dark:hover:text-blue-400"
                        >
                            <Home class="h-4 w-4" aria-hidden="true" />
                            <span>Beranda</span>
                        </Link>
                    </li>

                    <li
                        class="text-slate-300 dark:text-slate-700"
                        aria-hidden="true"
                    >
                        /
                    </li>

                    <li class="flex items-center gap-2">
                        <BriefcaseBusiness class="h-4 w-4" aria-hidden="true" />
                        <span>Hubungan Investor</span>
                    </li>

                    <li
                        class="text-slate-300 dark:text-slate-700"
                        aria-hidden="true"
                    >
                        /
                    </li>

                    <li
                        class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                        aria-current="page"
                    >
                        <BriefcaseBusiness
                            class="h-4 w-4 text-blue-600 dark:text-blue-400"
                            aria-hidden="true"
                        />
                        <span>Peluang Investasi</span>
                    </li>
                </ol>
            </nav>

            <!-- =====================================================
                 HEADING
            ====================================================== -->

            <header class="mb-8 max-w-3xl" data-reveal style="--d: 80ms">
                <div
                    class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400"
                >
                    <BriefcaseBusiness class="h-4 w-4" aria-hidden="true" />
                    Hubungan Investor
                </div>

                <h1
                    class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                >
                    Peluang Investasi
                </h1>

                <p
                    class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    Temukan peluang investasi strategis dan potensi pengembangan
                    usaha di Kawasan Industri Tanjung Buton.
                </p>
            </header>

            <!-- =====================================================
                 SEARCH & FILTER
            ====================================================== -->

            <section
                v-if="!isLoading && props.peluangInvestasis.total > 0"
                class="mb-8 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900"
                data-reveal
                style="--d: 120ms"
            >
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                    <!-- Search -->
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            aria-hidden="true"
                        />

                        <input
                            ref="searchInput"
                            v-model="search"
                            type="search"
                            placeholder="Cari peluang investasi..."
                            aria-label="Cari peluang investasi"
                            class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-10 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-blue-500 dark:focus:bg-slate-950"
                        />

                        <button
                            v-if="search"
                            type="button"
                            aria-label="Hapus pencarian"
                            class="absolute right-3 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="search = ''"
                        >
                            <X class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Filter Toggle -->
                    <button
                        type="button"
                        class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-900"
                        :aria-expanded="showFilters"
                        aria-controls="investment-filters"
                        @click="showFilters = !showFilters"
                    >
                        <Filter class="h-4 w-4" aria-hidden="true" />

                        Filter

                        <span
                            v-if="
                                selectedSector !== 'all' ||
                                selectedStatus !== 'all'
                            "
                            class="flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 text-[10px] font-bold text-white"
                        >
                            {{
                                Number(selectedSector !== "all") +
                                Number(selectedStatus !== "all")
                            }}
                        </span>
                    </button>
                </div>

                <!-- Filter Panel -->
                <div
                    v-if="showFilters"
                    id="investment-filters"
                    class="mt-4 grid gap-4 border-t border-slate-200 pt-4 sm:grid-cols-2 dark:border-slate-800"
                >
                    <div>
                        <label
                            for="sector-filter"
                            class="mb-2 block text-xs font-semibold text-slate-600 dark:text-slate-300"
                        >
                            Sektor Industri
                        </label>

                        <select
                            id="sector-filter"
                            v-model="selectedSector"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                        >
                            <option value="all">Semua Sektor</option>

                            <option
                                v-for="sector in sectors"
                                :key="sector"
                                :value="sector"
                            >
                                {{ sector }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            for="status-filter"
                            class="mb-2 block text-xs font-semibold text-slate-600 dark:text-slate-300"
                        >
                            Status
                        </label>

                        <select
                            id="status-filter"
                            v-model="selectedStatus"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                        >
                            <option value="all">Semua Status</option>

                            <option
                                v-for="status in statuses"
                                :key="status"
                                :value="status"
                            >
                                {{ formatStatus(status) }}
                            </option>
                        </select>
                    </div>

                    <div
                        v-if="hasActiveFilter"
                        class="flex items-end sm:col-span-2"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/30"
                            @click="resetFilters"
                        >
                            <X class="h-4 w-4" aria-hidden="true" />
                            Reset Filter
                        </button>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 RESULT META
            ====================================================== -->

            <div
                v-if="!isLoading && props.peluangInvestasis.total > 0"
                class="mb-5 flex items-center justify-between gap-4"
            >
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Menampilkan
                    <span
                        class="font-semibold text-slate-700 dark:text-slate-200"
                    >
                        {{ filteredInvestments.length }}
                    </span>
                    peluang investasi
                </p>

                <button
                    v-if="hasActiveFilter"
                    type="button"
                    class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    @click="resetFilters"
                >
                    Reset
                </button>
            </div>

            <!-- =====================================================
                 CONTENT
            ====================================================== -->

            <section class="space-y-6" data-reveal style="--d: 140ms">
                <!-- =========================
                     SKELETON
                ========================== -->

                <div
                    v-if="isLoading"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                    aria-label="Memuat data peluang investasi"
                    aria-busy="true"
                >
                    <div
                        v-for="index in 6"
                        :key="`investment-skeleton-${index}`"
                        class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="skeleton-shimmer h-52 bg-slate-200 dark:bg-slate-800"
                        />

                        <div class="space-y-4 p-6">
                            <div
                                class="skeleton-shimmer h-6 w-28 rounded-full bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="space-y-2">
                                <div
                                    class="skeleton-shimmer h-5 w-4/5 rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                                <div
                                    class="skeleton-shimmer h-5 w-3/5 rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                            </div>

                            <div class="space-y-2">
                                <div
                                    class="skeleton-shimmer h-3.5 w-full rounded bg-slate-200 dark:bg-slate-800"
                                />
                                <div
                                    class="skeleton-shimmer h-3.5 w-11/12 rounded bg-slate-200 dark:bg-slate-800"
                                />
                                <div
                                    class="skeleton-shimmer h-3.5 w-3/4 rounded bg-slate-200 dark:bg-slate-800"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <div
                                    class="rounded-2xl border border-slate-200 p-3 dark:border-slate-800"
                                >
                                    <div
                                        class="skeleton-shimmer mb-2 h-3 w-16 rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                    <div
                                        class="skeleton-shimmer h-4 w-20 rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 p-3 dark:border-slate-800"
                                >
                                    <div
                                        class="skeleton-shimmer mb-2 h-3 w-16 rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                    <div
                                        class="skeleton-shimmer h-4 w-20 rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>
                            </div>

                            <div
                                class="skeleton-shimmer h-11 w-full rounded-xl bg-slate-200 dark:bg-slate-800"
                            />
                        </div>
                    </div>
                </div>

                <!-- =========================
                     DATABASE EMPTY
                ========================== -->

                <div
                    v-else-if="props.peluangInvestasis.total === 0"
                    class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <BriefcaseBusiness class="h-8 w-8" aria-hidden="true" />
                    </div>

                    <h2
                        class="mt-5 text-xl font-bold text-slate-900 dark:text-white"
                    >
                        Data Peluang Investasi Belum Tersedia
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Saat ini belum terdapat informasi peluang investasi yang
                        dapat ditampilkan. Silakan kembali lagi nanti untuk
                        mendapatkan informasi terbaru mengenai peluang investasi
                        di KITB.
                    </p>

                    <Link
                        href="/ajukan-kunjungan"
                        class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                    >
                        Ajukan Kunjungan Lahan

                        <ArrowRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>

                <!-- =========================
                     FILTER EMPTY
                ========================== -->

                <div
                    v-else-if="filteredInvestments.length === 0"
                    class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                    >
                        <Search class="h-8 w-8" aria-hidden="true" />
                    </div>

                    <h2
                        class="mt-5 text-xl font-bold text-slate-900 dark:text-white"
                    >
                        Data Tidak Ditemukan
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Tidak ada peluang investasi yang sesuai dengan pencarian
                        atau filter yang Anda pilih.
                    </p>

                    <button
                        v-if="hasActiveFilter"
                        type="button"
                        class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-900"
                        @click="resetFilters"
                    >
                        <X class="h-4 w-4" aria-hidden="true" />
                        Reset Filter
                    </button>
                </div>

                <!-- =========================
                     CARDS
                ========================== -->

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(investment, index) in filteredInvestments"
                        :key="investment.id"
                        data-reveal
                        :style="{
                            '--d': `${180 + index * 60}ms`,
                        }"
                        class="group overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                    >
                        <!-- Image -->
                        <div
                            class="relative aspect-[16/10] overflow-hidden bg-slate-100 dark:bg-slate-800"
                        >
                            <img
                                v-if="investment.gambar"
                                :src="imageUrl(investment.gambar)"
                                :alt="investment.judul"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                loading="lazy"
                            />

                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center"
                            >
                                <Building2
                                    class="h-12 w-12 text-slate-300 dark:text-slate-600"
                                    aria-hidden="true"
                                />
                            </div>

                            <div class="absolute left-4 top-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1.5 text-xs font-semibold shadow-sm backdrop-blur dark:bg-slate-900/95"
                                    :class="statusClass(investment.status)"
                                >
                                    <CheckCircle2
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    />

                                    {{ formatStatus(investment.status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-6">
                            <div
                                class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-blue-600 dark:text-blue-400"
                            >
                                <Factory class="h-4 w-4" aria-hidden="true" />

                                {{ investment.sektor_industri || "Investasi" }}
                            </div>

                            <h2
                                class="mt-3 line-clamp-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ investment.judul }}
                            </h2>

                            <p
                                v-if="investment.deskripsi"
                                class="mt-3 line-clamp-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{ investment.deskripsi }}
                            </p>

                            <div class="mt-5 grid grid-cols-2 gap-3">
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/50"
                                >
                                    <div
                                        class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        <LandPlot
                                            class="h-3.5 w-3.5"
                                            aria-hidden="true"
                                        />

                                        Luas
                                    </div>

                                    <p
                                        class="mt-1 text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ formatArea(investment) }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/50"
                                >
                                    <div
                                        class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        <CircleDollarSign
                                            class="h-3.5 w-3.5"
                                            aria-hidden="true"
                                        />

                                        Investasi
                                    </div>

                                    <p
                                        class="mt-1 truncate text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ formatInvestmentValue(investment) }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="investment.lokasi"
                                class="mt-4 flex items-start gap-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                <MapPin
                                    class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    aria-hidden="true"
                                />

                                <span class="line-clamp-2">
                                    {{ investment.lokasi }}
                                </span>
                            </div>

                            <button
                                type="button"
                                class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                @click="openModal(investment)"
                            >
                                Lihat Detail

                                <ArrowRight
                                    class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                                    aria-hidden="true"
                                />
                            </button>
                        </div>
                    </article>
                </div>
            </section>

            <!-- =====================================================
                 PAGINATION
            ====================================================== -->

            <nav
                v-if="
                    !isLoading &&
                    props.peluangInvestasis.total > 0 &&
                    props.peluangInvestasis.last_page > 1
                "
                class="mt-10 flex flex-wrap items-center justify-center gap-2"
                aria-label="Pagination peluang investasi"
            >
                <template
                    v-for="(link, index) in props.peluangInvestasis.links"
                    :key="`${index}-${link.label}`"
                >
                    <button
                        v-if="link.url"
                        type="button"
                        :disabled="isNavigating"
                        :aria-current="link.active ? 'page' : undefined"
                        :aria-label="
                            isPreviousLink(link, index)
                                ? 'Halaman sebelumnya'
                                : isNextLink(
                                        link,
                                        index,
                                        props.peluangInvestasis.links,
                                    )
                                  ? 'Halaman berikutnya'
                                  : `Halaman ${link.label}`
                        "
                        class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border px-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-slate-950"
                        :class="
                            link.active
                                ? 'border-blue-600 bg-blue-600 text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-700 dark:hover:bg-blue-950/30 dark:hover:text-blue-400'
                        "
                        @click="goToPage(link.url)"
                    >
                        <ChevronLeft
                            v-if="isPreviousLink(link, index)"
                            class="h-4 w-4"
                            aria-hidden="true"
                        />

                        <ChevronRight
                            v-else-if="
                                isNextLink(
                                    link,
                                    index,
                                    props.peluangInvestasis.links,
                                )
                            "
                            class="h-4 w-4"
                            aria-hidden="true"
                        />

                        <span v-else v-html="link.label" />
                    </button>
                </template>
            </nav>

            <!-- =====================================================
                 CTA
            ====================================================== -->

            <section
                v-if="!isLoading && props.peluangInvestasis.total > 0"
                class="mt-14 overflow-hidden rounded-3xl border border-blue-100 bg-blue-50/70 p-6 sm:p-8 dark:border-blue-900/50 dark:bg-blue-950/20"
                data-reveal
                style="--d: 180ms"
            >
                <div
                    class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
                >
                    <div class="max-w-2xl">
                        <div
                            class="flex items-center gap-2 text-sm font-bold text-blue-700 dark:text-blue-400"
                        >
                            <BriefcaseBusiness
                                class="h-5 w-5"
                                aria-hidden="true"
                            />

                            Tertarik Berinvestasi?
                        </div>

                        <h2
                            class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            Mari wujudkan peluang investasi Anda bersama KITB.
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                        >
                            Hubungi tim kami untuk mendapatkan informasi lebih
                            lanjut mengenai ketersediaan lahan, fasilitas, dan
                            proses investasi.
                        </p>
                    </div>

                    <Link
                        href="/kunjungan-lahan"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-blue-950"
                    >
                        Hubungi Kami

                        <ArrowRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>
            </section>
        </div>

        <!-- =========================================================
             DETAIL MODAL
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="selectedInvestment"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4 sm:p-6"
                role="presentation"
                @click.self="closeModal"
            >
                <div
                    class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                    aria-hidden="true"
                />

                <div
                    ref="modalPanel"
                    tabindex="-1"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="investment-modal-title"
                    class="relative my-auto w-full max-w-3xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl outline-none dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Modal Header -->
                    <div
                        class="relative h-56 bg-slate-100 sm:h-72 dark:bg-slate-800"
                    >
                        <img
                            v-if="selectedInvestment.gambar"
                            :src="imageUrl(selectedInvestment.gambar)"
                            :alt="selectedInvestment.judul"
                            class="h-full w-full object-cover"
                        />

                        <div
                            v-else
                            class="flex h-full items-center justify-center"
                        >
                            <Building2
                                class="h-16 w-16 text-slate-300 dark:text-slate-600"
                                aria-hidden="true"
                            />
                        </div>

                        <div
                            class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-slate-950/70 to-transparent"
                            aria-hidden="true"
                        />

                        <button
                            type="button"
                            aria-label="Tutup detail peluang investasi"
                            class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-lg backdrop-blur transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-slate-900/90 dark:text-slate-200 dark:hover:bg-slate-900"
                            @click="closeModal"
                        >
                            <X class="h-5 w-5" aria-hidden="true" />
                        </button>

                        <div class="absolute bottom-5 left-6 right-6">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1.5 text-xs font-semibold shadow-sm dark:bg-slate-900/95"
                                :class="statusClass(selectedInvestment.status)"
                            >
                                <CheckCircle2
                                    class="h-3.5 w-3.5"
                                    aria-hidden="true"
                                />

                                {{ formatStatus(selectedInvestment.status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 sm:p-8">
                        <div
                            class="text-xs font-bold uppercase tracking-[0.14em] text-blue-600 dark:text-blue-400"
                        >
                            {{
                                selectedInvestment.sektor_industri ||
                                "Peluang Investasi"
                            }}
                        </div>

                        <h2
                            id="investment-modal-title"
                            class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            {{ selectedInvestment.judul }}
                        </h2>

                        <!-- Stats -->
                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/50"
                            >
                                <div
                                    class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400"
                                >
                                    <LandPlot
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    />

                                    Luas Lahan
                                </div>

                                <p
                                    class="mt-2 text-base font-bold text-slate-900 dark:text-white"
                                >
                                    {{ formatArea(selectedInvestment) }}
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/50"
                            >
                                <div
                                    class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400"
                                >
                                    <CircleDollarSign
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    />

                                    Nilai Investasi
                                </div>

                                <p
                                    class="mt-2 text-base font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatInvestmentValue(
                                            selectedInvestment,
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/50"
                            >
                                <div
                                    class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400"
                                >
                                    <MapPin
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    />

                                    Lokasi
                                </div>

                                <p
                                    class="mt-2 text-base font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        selectedInvestment.lokasi ||
                                        "Informasi tersedia"
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-7">
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                Tentang Peluang Investasi
                            </h3>

                            <p
                                v-if="selectedInvestment.deskripsi"
                                class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-400"
                            >
                                {{ selectedInvestment.deskripsi }}
                            </p>

                            <p
                                v-else
                                class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400"
                            >
                                Informasi detail mengenai peluang investasi ini
                                dapat diperoleh dengan menghubungi tim KITB.
                            </p>
                        </div>

                        <!-- Actions -->
                        <div
                            class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                        >
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-900"
                                @click="closeModal"
                            >
                                Tutup
                            </button>

                            <Link
                                href="/kunjungan-lahan"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                @click="closeModal"
                            >
                                Ajukan Kunjungan Lahan

                                <ArrowRight
                                    class="h-4 w-4"
                                    aria-hidden="true"
                                />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </main>
</template>

<style scoped>
/* =========================================================
   REVEAL
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
   SKELETON
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
        rgb(255 255 255 / 0.55),
        transparent
    );
    animation: skeleton-shimmer 1.5s infinite;
    content: "";
}

@keyframes skeleton-shimmer {
    100% {
        transform: translateX(100%);
    }
}

/* =========================================================
   MODAL
========================================================= */

.modal-enter-active,
.modal-leave-active {
    transition:
        opacity 250ms ease,
        transform 250ms ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div:last-child,
.modal-leave-to > div:last-child {
    transform: translateY(12px) scale(0.98);
}

/* =========================================================
   REDUCED MOTION
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
    .modal-leave-active {
        transition: none;
    }
}
</style>
