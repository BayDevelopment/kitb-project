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
    ChevronLeft,
    ChevronRight,
    CircleCheck,
    FileCheck,
    FileText,
    Handshake,
    Home,
    Info,
    Search,
    Truck,
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

/*
|--------------------------------------------------------------------------
| TYPES
|--------------------------------------------------------------------------
*/

interface EaseOfDoingBusiness {
    id: number;

    judul: string;
    judul_en: string | null;
    judul_zh: string | null;

    slug: string;

    ringkasan: string | null;
    ringkasan_en: string | null;
    ringkasan_zh: string | null;

    deskripsi: string | null;
    deskripsi_en: string | null;
    deskripsi_zh: string | null;

    ikon: string | null;

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

const props = defineProps<{
    easeOfDoingBusinesses: Paginator<EaseOfDoingBusiness>;
}>();

/*
|--------------------------------------------------------------------------
| TRANSLATIONS
|--------------------------------------------------------------------------
*/

const translations = {
    id: {
        pageTitle: "Ease of Doing Business | KITB",
        meta: "Informasi kemudahan berusaha dan berbagai layanan pendukung investasi di Kawasan Industri Tanjung Buton.",

        home: "Beranda",
        investor: "Hubungan Investor",

        heading: "Ease of Doing Business",

        intro: "Kenali berbagai kemudahan, layanan, dan dukungan yang tersedia untuk membantu investor menjalankan kegiatan usaha di Kawasan Industri Tanjung Buton.",

        search: "Cari informasi kemudahan berusaha...",
        searchAria: "Cari informasi kemudahan berusaha",
        clear: "Hapus pencarian",

        loading: "Memuat informasi Ease of Doing Business",

        unavailableTitle: "Data Belum Tersedia",

        unavailable:
            "Informasi mengenai Ease of Doing Business saat ini belum tersedia. Silakan kembali lagi nanti untuk mendapatkan informasi terbaru dari KITB.",

        viewInvestment: "Lihat Peluang Investasi",

        notFound: "Data Tidak Ditemukan",

        notFoundText:
            "Tidak ada informasi yang sesuai dengan kata pencarian Anda.",

        reset: "Reset Pencarian",

        more: "Lihat Selengkapnya",

        ready: "Siap Berinvestasi di KITB?",

        readyTitle: "Kami siap membantu perjalanan investasi Anda.",

        readyText:
            "Dapatkan informasi lebih lanjut mengenai peluang investasi, fasilitas kawasan, dan proses kunjungan lahan di KITB.",

        investment: "Peluang Investasi",

        detail: "Informasi Detail",

        noDetail: "Informasi detail belum tersedia.",

        close: "Tutup",

        pagination: "Navigasi halaman Ease of Doing Business",
    },

    en: {
        pageTitle: "Ease of Doing Business | KITB",

        meta: "Information about ease of doing business and investment support services in Tanjung Buton Industrial Estate.",

        home: "Home",

        investor: "Investor Relations",

        heading: "Ease of Doing Business",

        intro: "Discover various facilities, services, and support available to help investors operate their businesses in Tanjung Buton Industrial Estate.",

        search: "Search ease of doing business information...",

        searchAria: "Search ease of doing business information",

        clear: "Clear search",

        loading: "Loading Ease of Doing Business information",

        unavailableTitle: "Data Not Available",

        unavailable:
            "Ease of Doing Business information is currently unavailable. Please come back later for the latest information from KITB.",

        viewInvestment: "View Investment Opportunities",

        notFound: "No Data Found",

        notFoundText: "No information matches your search keyword.",

        reset: "Reset Search",

        more: "View Details",

        ready: "Ready to Invest in KITB?",

        readyTitle: "We are ready to support your investment journey.",

        readyText:
            "Get more information about investment opportunities, estate facilities, and land visit procedures at KITB.",

        investment: "Investment Opportunities",

        detail: "Detailed Information",

        noDetail: "Detailed information is not available yet.",

        close: "Close",

        pagination: "Ease of Doing Business page navigation",
    },

    zh: {
        pageTitle: "营商便利度 | KITB",

        meta: "了解丹戎布顿工业园区的营商便利政策及投资配套服务。",

        home: "首页",

        investor: "投资者关系",

        heading: "营商便利度",

        intro: "了解丹戎布顿工业园区为投资者开展经营活动提供的各项便利、服务与支持。",

        search: "搜索营商便利度信息...",

        searchAria: "搜索营商便利度信息",

        clear: "清除搜索",

        loading: "正在加载营商便利度信息",

        unavailableTitle: "暂无数据",

        unavailable:
            "目前暂无营商便利度相关信息，请稍后返回查看 KITB 的最新信息。",

        viewInvestment: "查看投资机会",

        notFound: "未找到数据",

        notFoundText: "没有找到符合搜索关键词的信息。",

        reset: "重置搜索",

        more: "查看详情",

        ready: "准备投资 KITB？",

        readyTitle: "我们将为您的投资之旅提供支持。",

        readyText: "了解更多投资机会、园区设施以及 KITB 土地参观流程。",

        investment: "投资机会",

        detail: "详细信息",

        noDetail: "暂无详细信息。",

        close: "关闭",

        pagination: "营商便利度页面导航",
    },
} as const;

/*
|--------------------------------------------------------------------------
| ACTIVE LANGUAGE
|--------------------------------------------------------------------------
|
| currentLanguage berasal dari navbar/shared locale.
| Tidak ada language switcher lokal di halaman ini.
|
*/

const t = computed(() => {
    const language: LanguageCode = currentLanguage.value;

    return translations[language];
});

/*
|--------------------------------------------------------------------------
| LOCALIZED DATABASE VALUE
|--------------------------------------------------------------------------
|
| useLocale tetap menggunakan Record<string, unknown>.
| Wrapper ini menjaga EaseOfDoingBusiness tetap strongly typed
| tanpa harus melakukan "as Record<string, unknown>" di template.
|
*/

const localized = (
    object: EaseOfDoingBusiness | null | undefined,
    field: string,
): string => {
    return localizedValue(
        object as Record<string, unknown> | null | undefined,
        field,
    );
};

/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const search = ref("");

const selectedBusiness = ref<EaseOfDoingBusiness | null>(null);

const isLoading = ref(true);

const isNavigating = ref(false);

const modalPanel = ref<HTMLElement | null>(null);

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

const filteredBusinesses = computed(() => {
    const keyword = search.value.trim().toLocaleLowerCase();

    if (!keyword) {
        return props.easeOfDoingBusinesses.data;
    }

    return props.easeOfDoingBusinesses.data.filter((item) => {
        const judul = localized(item, "judul").toLocaleLowerCase();

        const ringkasan = localized(item, "ringkasan").toLocaleLowerCase();

        const deskripsi = localized(item, "deskripsi").toLocaleLowerCase();

        return (
            judul.includes(keyword) ||
            ringkasan.includes(keyword) ||
            deskripsi.includes(keyword)
        );
    });
});

/*
|--------------------------------------------------------------------------
| ICON
|--------------------------------------------------------------------------
*/

const iconMap: Record<string, typeof Info> = {
    info: Info,

    file: FileText,
    filetext: FileText,

    filecheck: FileCheck,
    filecheck2: FileCheck,

    building: Building2,
    building2: Building2,

    business: BriefcaseBusiness,

    handshake: Handshake,

    truck: Truck,

    check: CircleCheck,
};

const getIcon = (icon: string | null) => {
    if (!icon) {
        return Info;
    }

    return iconMap[icon.toLowerCase()] ?? Info;
};

/*
|--------------------------------------------------------------------------
| TEXT HELPERS
|--------------------------------------------------------------------------
*/

const truncate = (value: string | null | undefined, length = 160): string => {
    if (!value) {
        return "";
    }

    return value.length > length
        ? `${value.substring(0, length).trim()}...`
        : value;
};

/*
|--------------------------------------------------------------------------
| MODAL
|--------------------------------------------------------------------------
*/

const openModal = async (business: EaseOfDoingBusiness): Promise<void> => {
    selectedBusiness.value = business;

    await nextTick();

    document.body.style.overflow = "hidden";

    modalPanel.value?.focus();
};

const closeModal = (): void => {
    selectedBusiness.value = null;

    document.body.style.overflow = "";
};

const handleKeydown = (event: KeyboardEvent): void => {
    if (event.key === "Escape" && selectedBusiness.value) {
        closeModal();
    }
};

/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/

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
            only: ["easeOfDoingBusinesses"],

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

    return (
        label.includes("previous") ||
        label.includes("prev") ||
        label.includes("&laquo;")
    );
};

const isNextLink = (
    link: LinkItem,
    index: number,
    links: LinkItem[],
): boolean => {
    if (index === links.length - 1) {
        return true;
    }

    const label = link.label.toLowerCase();

    return label.includes("next") || label.includes("&raquo;");
};

/*
|--------------------------------------------------------------------------
| REVEAL ANIMATION
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| LANGUAGE SYNC
|--------------------------------------------------------------------------
|
| Bahasa dikontrol navbar melalui currentLanguage.
| Keyword pencarian direset ketika bahasa berubah agar pencarian
| tidak tertinggal dari bahasa sebelumnya.
|
*/

watch(currentLanguage, () => {
    search.value = "";
});

/*
|--------------------------------------------------------------------------
| LIFECYCLE
|--------------------------------------------------------------------------
*/

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
    () => props.easeOfDoingBusinesses.data,
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

        <meta name="description" :content="t.meta" />
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
                    <li>
                        <Link
                            href="/"
                            class="inline-flex items-center gap-1.5 transition hover:text-blue-600 dark:hover:text-blue-400"
                        >
                            <Home class="h-4 w-4" aria-hidden="true" />

                            <span>{{ t.home }}</span>
                        </Link>
                    </li>

                    <li
                        aria-hidden="true"
                        class="text-slate-300 dark:text-slate-700"
                    >
                        /
                    </li>

                    <li class="inline-flex items-center gap-1.5">
                        <BriefcaseBusiness class="h-4 w-4" aria-hidden="true" />

                        <span>{{ t.investor }}</span>
                    </li>

                    <li
                        aria-hidden="true"
                        class="text-slate-300 dark:text-slate-700"
                    >
                        /
                    </li>

                    <li
                        aria-current="page"
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                    >
                        <BriefcaseBusiness
                            class="h-4 w-4 text-blue-600 dark:text-blue-400"
                            aria-hidden="true"
                        />

                        <span>{{ t.heading }}</span>
                    </li>
                </ol>
            </nav>

            <!-- Header -->
            <header class="mb-8 max-w-3xl" data-reveal style="--d: 80ms">
                <div
                    class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400"
                >
                    <BriefcaseBusiness class="h-4 w-4" aria-hidden="true" />

                    {{ t.investor }}
                </div>

                <h1
                    class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                >
                    {{ t.heading }}
                </h1>

                <p
                    class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    {{ t.intro }}
                </p>
            </header>

            <!-- Search -->
            <section
                v-if="!isLoading && props.easeOfDoingBusinesses.total > 0"
                class="mb-8 rounded-3xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900"
                data-reveal
                style="--d: 120ms"
            >
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        aria-hidden="true"
                    />

                    <input
                        v-model="search"
                        type="search"
                        :placeholder="t.search"
                        :aria-label="t.searchAria"
                        class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-10 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-blue-500"
                    />

                    <button
                        v-if="search"
                        type="button"
                        :aria-label="t.clear"
                        class="absolute right-3 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                        @click="search = ''"
                    >
                        <X class="h-4 w-4" aria-hidden="true" />
                    </button>
                </div>
            </section>

            <!-- Content -->
            <section data-reveal style="--d: 140ms">
                <!-- Skeleton -->
                <div
                    v-if="isLoading"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                    :aria-label="t.loading"
                    aria-busy="true"
                >
                    <div
                        v-for="index in 6"
                        :key="`eodb-skeleton-${index}`"
                        class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="skeleton-shimmer flex h-14 w-14 rounded-2xl bg-slate-200 dark:bg-slate-800"
                        />

                        <div class="mt-5 space-y-3">
                            <div
                                class="skeleton-shimmer h-5 w-4/5 rounded-lg bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="skeleton-shimmer h-4 w-full rounded bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="skeleton-shimmer h-4 w-11/12 rounded bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="skeleton-shimmer h-4 w-3/4 rounded bg-slate-200 dark:bg-slate-800"
                            />
                        </div>

                        <div
                            class="mt-6 skeleton-shimmer h-10 w-36 rounded-xl bg-slate-200 dark:bg-slate-800"
                        />
                    </div>
                </div>

                <!-- Database Empty -->
                <div
                    v-else-if="props.easeOfDoingBusinesses.total === 0"
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
                        {{ t.unavailableTitle }}
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ t.unavailable }}
                    </p>

                    <Link
                        href="/hubungan-investor/peluang-investasi"
                        class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                    >
                        {{ t.viewInvestment }}

                        <ArrowRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>

                <!-- Search Empty -->
                <div
                    v-else-if="filteredBusinesses.length === 0"
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
                        {{ t.notFound }}
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ t.notFoundText }}
                    </p>

                    <button
                        type="button"
                        class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-900"
                        @click="search = ''"
                    >
                        <X class="h-4 w-4" aria-hidden="true" />

                        {{ t.reset }}
                    </button>
                </div>

                <!-- Cards -->
                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(business, index) in filteredBusinesses"
                        :key="business.id"
                        data-reveal
                        :style="{
                            '--d': `${180 + index * 60}ms`,
                        }"
                        class="group rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                    >
                        <!-- Icon -->
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition duration-300 group-hover:bg-blue-600 group-hover:text-white dark:bg-blue-950/40 dark:text-blue-400 dark:group-hover:bg-blue-600 dark:group-hover:text-white"
                        >
                            <component
                                :is="getIcon(business.ikon)"
                                class="h-7 w-7"
                                aria-hidden="true"
                            />
                        </div>

                        <!-- Content -->
                        <h2
                            class="mt-5 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            {{ localized(business, "judul") }}
                        </h2>

                        <p
                            v-if="localized(business, 'ringkasan')"
                            class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{ truncate(localized(business, "ringkasan")) }}
                        </p>

                        <p
                            v-else-if="localized(business, 'deskripsi')"
                            class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{ truncate(localized(business, "deskripsi")) }}
                        </p>

                        <button
                            type="button"
                            class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-blue-600 transition hover:gap-3 dark:text-blue-400"
                            @click="openModal(business)"
                        >
                            {{ t.more }}

                            <ArrowRight class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </article>
                </div>
            </section>

            <!-- Pagination -->
            <nav
                v-if="
                    !isLoading &&
                    props.easeOfDoingBusinesses.total > 0 &&
                    props.easeOfDoingBusinesses.last_page > 1
                "
                class="mt-10 flex flex-wrap items-center justify-center gap-2"
                :aria-label="t.pagination"
            >
                <template
                    v-for="(link, index) in props.easeOfDoingBusinesses.links"
                    :key="`${index}-${link.label}`"
                >
                    <button
                        v-if="link.url"
                        type="button"
                        :disabled="isNavigating"
                        :aria-current="link.active ? 'page' : undefined"
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
                                    props.easeOfDoingBusinesses.links,
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
                v-if="!isLoading && props.easeOfDoingBusinesses.total > 0"
                class="mt-14 overflow-hidden rounded-3xl border border-blue-100 bg-blue-50/70 p-6 sm:p-8 dark:border-blue-900/50 dark:bg-blue-950/20"
                data-reveal
                style="--d: 200ms"
            >
                <div
                    class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
                >
                    <div class="max-w-2xl">
                        <div
                            class="flex items-center gap-2 text-sm font-bold text-blue-700 dark:text-blue-400"
                        >
                            <CircleCheck class="h-5 w-5" aria-hidden="true" />

                            {{ t.ready }}
                        </div>

                        <h2
                            class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            {{ t.readyTitle }}
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                        >
                            {{ t.readyText }}
                        </p>
                    </div>

                    <Link
                        href="/hubungan-investor/peluang-investasi"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-blue-950"
                    >
                        {{ t.investment }}

                        <ArrowRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>
            </section>
        </div>

        <!-- Detail Modal -->
        <Transition name="modal">
            <div
                v-if="selectedBusiness"
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
                    aria-labelledby="eodb-modal-title"
                    class="relative my-auto w-full max-w-2xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl outline-none dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Modal Header -->
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-200 p-6 sm:p-7 dark:border-slate-800"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <component
                                    :is="getIcon(selectedBusiness.ikon)"
                                    class="h-6 w-6"
                                    aria-hidden="true"
                                />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.14em] text-blue-600 dark:text-blue-400"
                                >
                                    {{ t.heading }}
                                </p>

                                <h2
                                    id="eodb-modal-title"
                                    class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                                >
                                    {{ localized(selectedBusiness, "judul") }}
                                </h2>
                            </div>
                        </div>

                        <button
                            type="button"
                            :aria-label="t.close"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="closeModal"
                        >
                            <X class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="max-h-[65vh] overflow-y-auto p-6 sm:p-7">
                        <div
                            v-if="localized(selectedBusiness, 'ringkasan')"
                            class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4 dark:border-blue-900/50 dark:bg-blue-950/20"
                        >
                            <p
                                class="text-sm font-medium leading-6 text-blue-900 dark:text-blue-200"
                            >
                                {{ localized(selectedBusiness, "ringkasan") }}
                            </p>
                        </div>

                        <div
                            v-if="localized(selectedBusiness, 'deskripsi')"
                            class="mt-6"
                        >
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{ t.detail }}
                            </h3>

                            <div
                                class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-400"
                            >
                                {{ localized(selectedBusiness, "deskripsi") }}
                            </div>
                        </div>

                        <div
                            v-if="
                                !localized(selectedBusiness, 'ringkasan') &&
                                !localized(selectedBusiness, 'deskripsi')
                            "
                            class="py-8 text-center"
                        >
                            <Info
                                class="mx-auto h-8 w-8 text-slate-300 dark:text-slate-600"
                                aria-hidden="true"
                            />

                            <p
                                class="mt-3 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ t.noDetail }}
                            </p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div
                        class="flex flex-col-reverse gap-3 border-t border-slate-200 p-6 sm:flex-row sm:justify-end dark:border-slate-800"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 dark:focus:ring-offset-slate-900"
                            @click="closeModal"
                        >
                            {{ t.close }}
                        </button>

                        <Link
                            href="/hubungan-investor/peluang-investasi"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                            @click="closeModal"
                        >
                            {{ t.viewInvestment }}

                            <ArrowRight class="h-4 w-4" aria-hidden="true" />
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>
    </main>
</template>

<style scoped>
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
