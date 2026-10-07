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

import { currentLanguage, type LanguageCode } from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
 * TYPES
 * ========================================================= */

interface PeluangInvestasi {
    id: number;

    judul: string;
    judul_en: string | null;
    judul_zh: string | null;

    slug: string;

    sektor_industri: string | null;
    sektor_industri_en: string | null;
    sektor_industri_zh: string | null;

    deskripsi: string | null;
    deskripsi_en: string | null;
    deskripsi_zh: string | null;

    luas_lahan: number | string | null;
    satuan_luas: string | null;

    lokasi: string | null;
    lokasi_en: string | null;
    lokasi_zh: string | null;

    status: string;

    nilai_investasi: number | string | null;
    mata_uang: string | null;

    gambar: string | null;

    urutan: number;
    aktif: boolean;

    created_at?: string;
    updated_at?: string;
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
 * PROPS
 * ========================================================= */

const props = defineProps<{
    peluangInvestasis: Paginator<PeluangInvestasi>;
}>();

/* =========================================================
 * STATE
 * ========================================================= */

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
 * LANGUAGE
 * ========================================================= */

const languageTabs: {
    code: LanguageCode;
    flag: string;
    label: string;
}[] = [
    {
        code: "id",
        flag: "🇮🇩",
        label: "Indonesia",
    },
    {
        code: "en",
        flag: "🇬🇧",
        label: "English",
    },
    {
        code: "zh",
        flag: "🇨🇳",
        label: "中文",
    },
];

type InvestmentLanguageField =
    | "judul"
    | "sektor_industri"
    | "deskripsi"
    | "lokasi";

const fieldKey = (
    field: InvestmentLanguageField,
    language: LanguageCode,
): string => {
    return language === "id" ? field : `${field}_${language}`;
};

/**
 * Mengambil nilai sesuai bahasa aktif.
 *
 * ID:
 *   judul
 *
 * EN:
 *   judul_en
 *
 * ZH:
 *   judul_zh
 *
 * Jika terjemahan tidak tersedia, fallback ke Indonesia.
 */
const getLocalizedValue = (
    investment: PeluangInvestasi,
    field: InvestmentLanguageField,
): string => {
    const record = investment as unknown as Record<string, unknown>;

    const language = currentLanguage.value;

    const localizedField = fieldKey(field, language);

    const localized = String(record[localizedField] ?? "").trim();

    if (localized !== "") {
        return localized;
    }

    if (language !== "id") {
        return String(record[field] ?? "").trim();
    }

    return "";
};

/**
 * Mengambil nilai bahasa tertentu tanpa fallback.
 */
const getRawLanguageValue = (
    investment: PeluangInvestasi,
    field: InvestmentLanguageField,
    language: LanguageCode,
): string => {
    const record = investment as unknown as Record<string, unknown>;

    return String(record[fieldKey(field, language)] ?? "").trim();
};

/**
 * Ganti bahasa tampilan.
 */
const changeLanguage = (language: LanguageCode): void => {
    currentLanguage.value = language;

    // Label sektor berubah sesuai bahasa,
    // sehingga filter sektor lama harus di-reset.
    selectedSector.value = "all";
};

/* =========================================================
 * TRANSLATION
 * ========================================================= */

const translations: Record<
    LanguageCode,
    {
        pageTitle: string;
        pageDescription: string;

        home: string;
        investorRelations: string;
        investmentOpportunities: string;

        searchPlaceholder: string;
        searchAria: string;

        filter: string;
        industrySector: string;
        allSectors: string;
        status: string;
        allStatuses: string;
        resetFilter: string;
        reset: string;

        showing: string;
        investmentData: string;

        noDataTitle: string;
        noDataDescription: string;
        noSearchTitle: string;
        noSearchDescription: string;

        landVisit: string;
        investment: string;
        area: string;
        location: string;
        viewDetail: string;

        opportunity: string;
        investmentOpportunity: string;

        available: string;
        inProgress: string;
        occupied: string;
        unavailable: string;
        closed: string;

        aboutOpportunity: string;
        informationAvailable: string;
        detailFallback: string;

        close: string;
        interested: string;
        interestedTitle: string;
        interestedDescription: string;
        contactUs: string;

        previousPage: string;
        nextPage: string;
        page: string;
        loading: string;
        noImage: string;
    }
> = {
    id: {
        pageTitle: "Peluang Investasi | KITB",
        pageDescription:
            "Temukan berbagai peluang investasi yang tersedia di Kawasan Industri Tanjung Buton.",

        home: "Beranda",
        investorRelations: "Hubungan Investor",
        investmentOpportunities: "Peluang Investasi",

        searchPlaceholder: "Cari peluang investasi...",
        searchAria: "Cari peluang investasi",

        filter: "Filter",
        industrySector: "Sektor Industri",
        allSectors: "Semua Sektor",
        status: "Status",
        allStatuses: "Semua Status",
        resetFilter: "Reset Filter",
        reset: "Reset",

        showing: "Menampilkan",
        investmentData: "peluang investasi",

        noDataTitle: "Data Peluang Investasi Belum Tersedia",
        noDataDescription:
            "Saat ini belum terdapat informasi peluang investasi yang dapat ditampilkan. Silakan kembali lagi nanti untuk mendapatkan informasi terbaru mengenai peluang investasi di KITB.",

        noSearchTitle: "Data Tidak Ditemukan",
        noSearchDescription:
            "Tidak ada peluang investasi yang sesuai dengan pencarian atau filter yang Anda pilih.",

        landVisit: "Ajukan Kunjungan Lahan",
        investment: "Investasi",
        area: "Luas",
        location: "Lokasi",
        viewDetail: "Lihat Detail",

        opportunity: "Investasi",
        investmentOpportunity: "Peluang Investasi",

        available: "Tersedia",
        inProgress: "Dalam Proses",
        occupied: "Terisi",
        unavailable: "Tidak Tersedia",
        closed: "Ditutup",

        aboutOpportunity: "Tentang Peluang Investasi",
        informationAvailable: "Informasi tersedia",
        detailFallback:
            "Informasi detail mengenai peluang investasi ini dapat diperoleh dengan menghubungi tim KITB.",

        close: "Tutup",
        interested: "Tertarik Berinvestasi?",
        interestedTitle: "Mari wujudkan peluang investasi Anda bersama KITB.",
        interestedDescription:
            "Hubungi tim kami untuk mendapatkan informasi lebih lanjut mengenai ketersediaan lahan, fasilitas, dan proses investasi.",
        contactUs: "Hubungi Kami",

        previousPage: "Halaman sebelumnya",
        nextPage: "Halaman berikutnya",
        page: "Halaman",
        loading: "Memuat data peluang investasi...",
        noImage: "Tidak ada gambar",
    },

    en: {
        pageTitle: "Investment Opportunities | KITB",
        pageDescription:
            "Discover strategic investment opportunities available at Tanjung Buton Industrial Estate.",

        home: "Home",
        investorRelations: "Investor Relations",
        investmentOpportunities: "Investment Opportunities",

        searchPlaceholder: "Search investment opportunities...",
        searchAria: "Search investment opportunities",

        filter: "Filter",
        industrySector: "Industry Sector",
        allSectors: "All Sectors",
        status: "Status",
        allStatuses: "All Statuses",
        resetFilter: "Reset Filter",
        reset: "Reset",

        showing: "Showing",
        investmentData: "investment opportunities",

        noDataTitle: "Investment Opportunities Not Available",
        noDataDescription:
            "There are currently no investment opportunities available to display. Please check back later for the latest information from KITB.",

        noSearchTitle: "No Data Found",
        noSearchDescription:
            "No investment opportunities match your search or selected filters.",

        landVisit: "Request Site Visit",
        investment: "Investment",
        area: "Area",
        location: "Location",
        viewDetail: "View Details",

        opportunity: "Investment",
        investmentOpportunity: "Investment Opportunity",

        available: "Available",
        inProgress: "In Progress",
        occupied: "Occupied",
        unavailable: "Unavailable",
        closed: "Closed",

        aboutOpportunity: "About the Investment Opportunity",
        informationAvailable: "Information available",
        detailFallback:
            "Detailed information about this investment opportunity can be obtained by contacting the KITB team.",

        close: "Close",
        interested: "Interested in Investing?",
        interestedTitle:
            "Let's turn your investment opportunity into reality with KITB.",
        interestedDescription:
            "Contact our team for more information about land availability, facilities, and the investment process.",
        contactUs: "Contact Us",

        previousPage: "Previous page",
        nextPage: "Next page",
        page: "Page",
        loading: "Loading investment opportunities...",
        noImage: "No image",
    },

    zh: {
        pageTitle: "投资机会 | KITB",
        pageDescription: "探索丹绒布顿工业园区提供的战略投资机会。",

        home: "首页",
        investorRelations: "投资者关系",
        investmentOpportunities: "投资机会",

        searchPlaceholder: "搜索投资机会...",
        searchAria: "搜索投资机会",

        filter: "筛选",
        industrySector: "产业领域",
        allSectors: "所有领域",
        status: "状态",
        allStatuses: "所有状态",
        resetFilter: "重置筛选",
        reset: "重置",

        showing: "显示",
        investmentData: "项投资机会",

        noDataTitle: "暂无投资机会",
        noDataDescription:
            "目前暂无可显示的投资机会信息。请稍后再次查看，以获取 KITB 的最新投资信息。",

        noSearchTitle: "未找到数据",
        noSearchDescription: "没有符合搜索条件或筛选条件的投资机会。",

        landVisit: "申请土地参观",
        investment: "投资",
        area: "土地面积",
        location: "位置",
        viewDetail: "查看详情",

        opportunity: "投资",
        investmentOpportunity: "投资机会",

        available: "可用",
        inProgress: "处理中",
        occupied: "已入驻",
        unavailable: "不可用",
        closed: "已关闭",

        aboutOpportunity: "投资机会详情",
        informationAvailable: "暂无位置信息",
        detailFallback: "如需了解该投资机会的详细信息，请联系 KITB 团队。",

        close: "关闭",
        interested: "对投资感兴趣？",
        interestedTitle: "与 KITB 一起实现您的投资机会。",
        interestedDescription:
            "联系我们的团队，了解土地供应、设施以及投资流程的更多信息。",
        contactUs: "联系我们",

        previousPage: "上一页",
        nextPage: "下一页",
        page: "第",
        loading: "正在加载投资机会...",
        noImage: "暂无图片",
    },
};

const t = computed(() => translations[currentLanguage.value]);

/* =========================================================
 * IMAGE
 * ========================================================= */

const imageUrl = (path: string | null): string => {
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

/* =========================================================
 * NUMBER FORMATTING
 * ========================================================= */

const formatNumber = (
    value: number | string | null,
    maximumFractionDigits = 2,
): string => {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    const number = Number(value);

    if (Number.isNaN(number)) {
        return "-";
    }

    return new Intl.NumberFormat(
        currentLanguage.value === "zh" ? "zh-CN" : "id-ID",
        {
            maximumFractionDigits,
        },
    ).format(number);
};

const formatArea = (investment: PeluangInvestasi): string => {
    if (
        investment.luas_lahan === null ||
        investment.luas_lahan === undefined ||
        investment.luas_lahan === ""
    ) {
        return "-";
    }

    return `${formatNumber(
        investment.luas_lahan,
    )} ${investment.satuan_luas || "Ha"}`;
};

const formatInvestmentValue = (investment: PeluangInvestasi): string => {
    if (
        investment.nilai_investasi === null ||
        investment.nilai_investasi === undefined ||
        investment.nilai_investasi === ""
    ) {
        return t.value.contactUs;
    }

    const value = Number(investment.nilai_investasi);

    if (Number.isNaN(value)) {
        return t.value.contactUs;
    }

    const currency = (investment.mata_uang || "IDR").toUpperCase();

    if (currency === "IDR") {
        return `Rp ${new Intl.NumberFormat("id-ID", {
            maximumFractionDigits: 0,
        }).format(value)}`;
    }

    return `${currency} ${new Intl.NumberFormat(
        currentLanguage.value === "zh" ? "zh-CN" : "en-US",
        {
            maximumFractionDigits: 2,
        },
    ).format(value)}`;
};

/* =========================================================
 * STATUS
 * ========================================================= */

const formatStatus = (status: string): string => {
    const normalized = status.toLowerCase();

    const labels: Record<LanguageCode, Record<string, string>> = {
        id: {
            tersedia: "Tersedia",
            proses: "Dalam Proses",
            terisi: "Terisi",
            tidak_tersedia: "Tidak Tersedia",
            ditutup: "Ditutup",
        },

        en: {
            tersedia: "Available",
            proses: "In Progress",
            terisi: "Occupied",
            tidak_tersedia: "Unavailable",
            ditutup: "Closed",
        },

        zh: {
            tersedia: "可用",
            proses: "处理中",
            terisi: "已入驻",
            tidak_tersedia: "不可用",
            ditutup: "已关闭",
        },
    };

    return (
        labels[currentLanguage.value][normalized] ?? status.replace(/_/g, " ")
    );
};

const statusClass = (status: string): string => {
    switch (status.toLowerCase()) {
        case "tersedia":
            return "text-emerald-700 dark:text-emerald-400";

        case "proses":
            return "text-amber-700 dark:text-amber-400";

        case "terisi":
            return "text-blue-700 dark:text-blue-400";

        case "tidak_tersedia":
        case "ditutup":
            return "text-rose-700 dark:text-rose-400";

        default:
            return "text-slate-700 dark:text-slate-300";
    }
};

/* =========================================================
 * FILTER OPTIONS
 * ========================================================= */

const sectors = computed(() => {
    const values = props.peluangInvestasis.data
        .map((item) => getLocalizedValue(item, "sektor_industri"))
        .filter((value) => value.trim() !== "");

    return [...new Set(values)].sort((a, b) =>
        a.localeCompare(b, currentLanguage.value),
    );
});

const statuses = computed(() => {
    const values = props.peluangInvestasis.data
        .map((item) => item.status)
        .filter(Boolean);

    return [...new Set(values)];
});

/* =========================================================
 * FILTERED DATA
 * ========================================================= */

const filteredInvestments = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    return props.peluangInvestasis.data.filter((investment) => {
        const searchableValues = [
            investment.judul,
            investment.judul_en,
            investment.judul_zh,

            investment.sektor_industri,
            investment.sektor_industri_en,
            investment.sektor_industri_zh,

            investment.lokasi,
            investment.lokasi_en,
            investment.lokasi_zh,

            investment.deskripsi,
            investment.deskripsi_en,
            investment.deskripsi_zh,
        ]
            .filter(
                (value): value is string =>
                    value !== null && value !== undefined && value !== "",
            )
            .map((value) => value.toLowerCase());

        const matchesSearch =
            keyword === "" ||
            searchableValues.some((value) => value.includes(keyword));

        const matchesSector =
            selectedSector.value === "all" ||
            getLocalizedValue(investment, "sektor_industri") ===
                selectedSector.value;

        const matchesStatus =
            selectedStatus.value === "all" ||
            investment.status === selectedStatus.value;

        return matchesSearch && matchesSector && matchesStatus;
    });
});

const hasActiveFilter = computed(
    () =>
        search.value.trim() !== "" ||
        selectedSector.value !== "all" ||
        selectedStatus.value !== "all",
);

/* =========================================================
 * FILTER ACTION
 * ========================================================= */

const resetFilters = (): void => {
    search.value = "";
    selectedSector.value = "all";
    selectedStatus.value = "all";
};

/* =========================================================
 * PAGINATION
 * ========================================================= */

const goToPage = (url: string | null): void => {
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

const isPreviousLink = (link: LinkItem, index: number): boolean => {
    if (index === 0) {
        return true;
    }

    const label = link.label.toLowerCase();

    return label.includes("previous") || label.includes("prev");
};

const isNextLink = (
    link: LinkItem,
    index: number,
    links: LinkItem[],
): boolean => {
    if (index === links.length - 1) {
        return true;
    }

    return link.label.toLowerCase().includes("next");
};

/* =========================================================
 * MODAL
 * ========================================================= */

const openModal = async (investment: PeluangInvestasi): Promise<void> => {
    selectedInvestment.value = investment;

    await nextTick();

    document.body.style.overflow = "hidden";

    modalPanel.value?.focus();
};

const closeModal = (): void => {
    selectedInvestment.value = null;
    document.body.style.overflow = "";
};

const handleKeydown = (event: KeyboardEvent): void => {
    if (event.key === "Escape" && selectedInvestment.value) {
        closeModal();
    }
};

/* =========================================================
 * REVEAL ANIMATION
 * ========================================================= */

let revealObserver: IntersectionObserver | null = null;

const initializeReveal = (): void => {
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
 * LIFECYCLE
 * ========================================================= */

onMounted(() => {
    window.addEventListener("keydown", handleKeydown);

    window.requestAnimationFrame(() => {
        isLoading.value = false;

        nextTick(() => {
            initializeReveal();
        });
    });
});

watch(
    () => [props.peluangInvestasis.data, currentLanguage.value],
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
        <title>{{ t.pageTitle }}</title>

        <meta name="description" :content="t.pageDescription" />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- Decorative Background -->

        <div
            class="pointer-events-none absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-900/20"
            aria-hidden="true"
        />

        <div
            class="pointer-events-none absolute -right-40 top-[35%] h-96 w-96 rounded-full bg-indigo-200/20 blur-3xl dark:bg-indigo-900/10"
            aria-hidden="true"
        />

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->

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

                            <span>
                                {{ t.home }}
                            </span>
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

                        <span>
                            {{ t.investorRelations }}
                        </span>
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

                        <span>
                            {{ t.investmentOpportunities }}
                        </span>
                    </li>
                </ol>
            </nav>

            <!-- Heading -->

            <header class="mb-8 max-w-3xl" data-reveal style="--d: 80ms">
                <div
                    class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400"
                >
                    <BriefcaseBusiness class="h-4 w-4" aria-hidden="true" />

                    {{ t.investorRelations }}
                </div>

                <h1
                    class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                >
                    {{ t.investmentOpportunities }}
                </h1>

                <p
                    class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    {{ t.pageDescription }}
                </p>
            </header>

            <!-- Language Switcher -->

            <div
                class="mb-6 inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                role="group"
                aria-label="Bahasa tampilan"
            >
                <button
                    v-for="tab in languageTabs"
                    :key="tab.code"
                    type="button"
                    :aria-pressed="currentLanguage === tab.code"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                    :class="
                        currentLanguage === tab.code
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800'
                    "
                    @click="changeLanguage(tab.code)"
                >
                    {{ tab.flag }}
                    {{ tab.code.toUpperCase() }}
                </button>
            </div>

            <!-- Search & Filter -->

            <section
                v-if="!isLoading && props.peluangInvestasis.total > 0"
                class="mb-8 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900"
                data-reveal
                style="--d: 120ms"
            >
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            aria-hidden="true"
                        />

                        <input
                            ref="searchInput"
                            v-model="search"
                            type="search"
                            :placeholder="t.searchPlaceholder"
                            :aria-label="t.searchAria"
                            class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-10 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-blue-500 dark:focus:bg-slate-950"
                        />

                        <button
                            v-if="search"
                            type="button"
                            :aria-label="t.reset"
                            class="absolute right-3 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="search = ''"
                        >
                            <X class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-900"
                        :aria-expanded="showFilters"
                        aria-controls="investment-filters"
                        @click="showFilters = !showFilters"
                    >
                        <Filter class="h-4 w-4" aria-hidden="true" />

                        {{ t.filter }}

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
                            {{ t.industrySector }}
                        </label>

                        <select
                            id="sector-filter"
                            v-model="selectedSector"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                        >
                            <option value="all">
                                {{ t.allSectors }}
                            </option>

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
                            {{ t.status }}
                        </label>

                        <select
                            id="status-filter"
                            v-model="selectedStatus"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                        >
                            <option value="all">
                                {{ t.allStatuses }}
                            </option>

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

                            {{ t.resetFilter }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- Result Meta -->

            <div
                v-if="!isLoading && props.peluangInvestasis.total > 0"
                class="mb-5 flex items-center justify-between gap-4"
            >
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ t.showing }}

                    <span
                        class="font-semibold text-slate-700 dark:text-slate-200"
                    >
                        {{ filteredInvestments.length }}
                    </span>

                    {{ t.investmentData }}
                </p>

                <button
                    v-if="hasActiveFilter"
                    type="button"
                    class="text-sm font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    @click="resetFilters"
                >
                    {{ t.reset }}
                </button>
            </div>

            <!-- Content -->

            <section class="space-y-6" data-reveal style="--d: 140ms">
                <!-- Loading -->

                <div
                    v-if="isLoading"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                    :aria-label="t.loading"
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

                <!-- Database Empty -->

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
                        {{ t.noDataTitle }}
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ t.noDataDescription }}
                    </p>

                    <Link
                        href="/ajukan-kunjungan"
                        class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                    >
                        {{ t.landVisit }}

                        <ArrowRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>

                <!-- Filter Empty -->

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
                        {{ t.noSearchTitle }}
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ t.noSearchDescription }}
                    </p>

                    <button
                        v-if="hasActiveFilter"
                        type="button"
                        class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        @click="resetFilters"
                    >
                        <X class="h-4 w-4" aria-hidden="true" />

                        {{ t.resetFilter }}
                    </button>
                </div>

                <!-- Cards -->

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
                                :alt="getLocalizedValue(investment, 'judul')"
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

                                {{
                                    getLocalizedValue(
                                        investment,
                                        "sektor_industri",
                                    ) || t.opportunity
                                }}
                            </div>

                            <h2
                                class="mt-3 line-clamp-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ getLocalizedValue(investment, "judul") }}
                            </h2>

                            <p
                                v-if="
                                    selectedInvestment &&
                                    getLocalizedValue(
                                        selectedInvestment,
                                        'deskripsi',
                                    )
                                "
                                class="mt-3 line-clamp-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{ getLocalizedValue(investment, "deskripsi") }}
                            </p>

                            <!-- Stats -->

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

                                        {{ t.area }}
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

                                        {{ t.investment }}
                                    </div>

                                    <p
                                        class="mt-1 truncate text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ formatInvestmentValue(investment) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Location -->

                            <div
                                v-if="getLocalizedValue(investment, 'lokasi')"
                                class="mt-4 flex items-start gap-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                <MapPin
                                    class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    aria-hidden="true"
                                />

                                <span class="line-clamp-2">
                                    {{
                                        getLocalizedValue(investment, "lokasi")
                                    }}
                                </span>
                            </div>

                            <!-- Detail Button -->

                            <button
                                type="button"
                                class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                @click="openModal(investment)"
                            >
                                {{ t.viewDetail }}

                                <ArrowRight
                                    class="h-4 w-4 transition-transform group-hover:translate-x-0.5"
                                    aria-hidden="true"
                                />
                            </button>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Pagination -->

            <nav
                v-if="
                    !isLoading &&
                    props.peluangInvestasis.total > 0 &&
                    props.peluangInvestasis.last_page > 1
                "
                class="mt-10 flex flex-wrap items-center justify-center gap-2"
                :aria-label="t.investmentOpportunities"
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
                                ? t.previousPage
                                : isNextLink(
                                        link,
                                        index,
                                        props.peluangInvestasis.links,
                                    )
                                  ? t.nextPage
                                  : `${t.page} ${link.label}`
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

            <!-- CTA -->

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

                            {{ t.interested }}
                        </div>

                        <h2
                            class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            {{ t.interestedTitle }}
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                        >
                            {{ t.interestedDescription }}
                        </p>
                    </div>

                    <Link
                        href="/kunjungan-lahan"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-blue-950"
                    >
                        {{ t.contactUs }}

                        <ArrowRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>
            </section>
        </div>

        <!-- Detail Modal -->

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
                    <!-- Modal Image -->

                    <div
                        class="relative h-56 bg-slate-100 sm:h-72 dark:bg-slate-800"
                    >
                        <img
                            v-if="selectedInvestment.gambar"
                            :src="imageUrl(selectedInvestment.gambar)"
                            :alt="
                                getLocalizedValue(selectedInvestment, 'judul')
                            "
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
                            :aria-label="t.close"
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
                                getLocalizedValue(
                                    selectedInvestment,
                                    "sektor_industri",
                                ) || t.investmentOpportunity
                            }}
                        </div>

                        <h2
                            id="investment-modal-title"
                            class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            {{ getLocalizedValue(selectedInvestment, "judul") }}
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

                                    {{ t.area }}
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

                                    {{ t.investment }}
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

                                    {{ t.location }}
                                </div>

                                <p
                                    class="mt-2 text-base font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        getLocalizedValue(
                                            selectedInvestment,
                                            "lokasi",
                                        ) || t.informationAvailable
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Description -->

                        <div class="mt-7">
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{ t.aboutOpportunity }}
                            </h3>

                            <p
                                v-if="
                                    selectedInvestment &&
                                    getLocalizedValue(
                                        selectedInvestment,
                                        'deskripsi',
                                    )
                                "
                                class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-400"
                            >
                                {{
                                    getLocalizedValue(
                                        selectedInvestment,
                                        "deskripsi",
                                    )
                                }}
                            </p>

                            <p
                                v-else
                                class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400"
                            >
                                {{ t.detailFallback }}
                            </p>
                        </div>

                        <!-- Actions -->

                        <div
                            class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                        >
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                                @click="closeModal"
                            >
                                {{ t.close }}
                            </button>

                            <Link
                                href="/kunjungan-lahan"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                @click="closeModal"
                            >
                                {{ t.landVisit }}

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
/*
|--------------------------------------------------------------------------
| Reveal
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Skeleton
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Modal
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Reduced Motion
|--------------------------------------------------------------------------
*/

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
