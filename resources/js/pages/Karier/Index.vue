<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";

import { Head, Link, router } from "@inertiajs/vue3";

import {
    ArrowRight,
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    MapPin,
    Search,
    SlidersHorizontal,
    Users,
    X,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";

import { trans } from "laravel-vue-i18n";

import { currentLanguage } from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

interface Lowongan {
    [key: string]: unknown;

    id: number;

    judul_id: string;

    judul_en: string;

    judul_zh: string;

    slug: string;

    deskripsi_id: string | null;

    deskripsi_en: string | null;

    deskripsi_zh: string | null;

    departemen_id: string | null;

    departemen_en: string | null;

    departemen_zh: string | null;

    tipe_pekerjaan: string | null;

    lokasi_id: string | null;

    lokasi_en: string | null;

    lokasi_zh: string | null;

    tanggal_mulai: string | null;

    tanggal_tutup: string | null;
}

interface LinkItem {
    url: string | null;

    label: string;

    active: boolean;
}

interface Paginator {
    current_page: number;

    from: number | null;

    last_page: number;

    links: LinkItem[];

    next_page_url: string | null;

    prev_page_url: string | null;

    to: number | null;

    total: number;

    data: Lowongan[];
}

interface Props {
    lowongans: Paginator;

    departments: string[];

    types: string[];

    filters: {
        search: string;

        departemen: string;

        tipe: string;
    };
}

const props = defineProps<Props>();

const t = (key: string, params: Record<string, string | number> = {}) =>
    trans(
        `karier.${key}`,

        Object.fromEntries(
            Object.entries(params).map(([name, value]) => [
                name,

                String(value),
            ]),
        ),
    );

type LowonganField = "judul" | "deskripsi" | "departemen" | "lokasi";

/**
 * Mengambil nilai lowongan sesuai bahasa aktif.
 * Prioritas: bahasa aktif, Indonesia, Inggris, lalu Mandarin.
 */
const localizedLowonganValue = (
    lowongan: Lowongan,
    field: LowonganField,
): string => {
    const language = String(currentLanguage.value ?? "id")
        .toLowerCase()
        .replace("-", "_");

    const suffix = language.startsWith("en")
        ? "_en"
        : language.startsWith("zh")
          ? "_zh"
          : "_id";

    const candidates = [
        `${field}${suffix}`,
        `${field}_id`,
        `${field}_en`,
        `${field}_zh`,
    ];

    for (const key of candidates) {
        const value = lowongan[key];
        if (typeof value === "string" && value.trim().length > 0) {
            return value.trim();
        }
    }

    return "";
};

const dateLocale = computed(() => {
    if (currentLanguage.value === "zh") {
        return "zh-CN";
    }

    if (currentLanguage.value === "en") {
        return "en-US";
    }

    return "id-ID";
});

const isLoading = ref(false);

let removeStartListener: (() => void) | undefined;

let removeFinishListener: (() => void) | undefined;

onMounted(() => {
    removeStartListener = router.on("start", (event) => {
        try {
            const pathname = event.detail.visit.url.pathname;

            if (pathname === "/karier") {
                isLoading.value = true;
            }
        } catch {
            isLoading.value = true;
        }
    });

    removeFinishListener = router.on("finish", () => {
        isLoading.value = false;
    });
});

const searchQuery = ref(props.filters.search ?? "");

const selectedDepartment = ref(props.filters.departemen ?? "");

const selectedType = ref(props.filters.tipe ?? "");

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const isSameAsServer = () =>
    searchQuery.value.trim() === (props.filters.search ?? "") &&
    selectedDepartment.value === (props.filters.departemen ?? "") &&
    selectedType.value === (props.filters.tipe ?? "");

const applyFilter = () => {
    if (isSameAsServer()) {
        return;
    }

    router.get(
        "/karier",

        {
            search: searchQuery.value.trim() || undefined,

            departemen: selectedDepartment.value || undefined,

            tipe: selectedType.value || undefined,
        },

        {
            preserveState: true,

            preserveScroll: true,

            replace: true,

            only: ["lowongans", "filters"],
        },
    );
};

watch(searchQuery, () => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        applyFilter();
    }, 400);
});

watch([selectedDepartment, selectedType], () => {
    applyFilter();
});

watch(
    () => props.filters,

    (filters) => {
        clearTimeout(searchTimeout);

        searchQuery.value = filters.search ?? "";

        selectedDepartment.value = filters.departemen ?? "";

        selectedType.value = filters.tipe ?? "";
    },
);

const hasFilter = computed(() =>
    Boolean(
        searchQuery.value.trim() ||
        selectedDepartment.value ||
        selectedType.value,
    ),
);

const clearFilters = () => {
    clearTimeout(searchTimeout);

    searchQuery.value = "";

    selectedDepartment.value = "";

    selectedType.value = "";

    router.get(
        "/karier",

        {},

        {
            preserveState: true,

            preserveScroll: true,

            replace: true,

            only: ["lowongans", "filters"],
        },
    );
};

const showAllJobs = () => {
    if (hasFilter.value) {
        clearFilters();
    }

    window.scrollTo({
        top: 0,

        behavior: "smooth",
    });
};

const goToPage = (url: string | null) => {
    if (!url || isLoading.value) {
        return;
    }

    router.visit(url, {
        preserveState: true,

        preserveScroll: true,

        only: ["lowongans", "filters"],
    });
};

const typeKeys: Record<string, string> = {
    full_time: "types.full_time",

    part_time: "types.part_time",

    contract: "types.contract",

    kontrak: "types.contract",

    internship: "types.internship",

    magang: "types.internship",

    freelance: "types.freelance",

    remote: "types.remote",
};

const normalizeType = (value: string) =>
    value

        .trim()

        .toLowerCase()

        .replace(/[\s-]+/g, "_");

const getTypeLabel = (type: string | null) => {
    if (!type) {
        return t("types.full_time");
    }

    const key = typeKeys[normalizeType(type)];

    return key ? t(key) : type;
};

const getTypeIcon = (type: string | null) => {
    const normalized = type ? normalizeType(type) : "";

    return normalized.includes("intern") || normalized.includes("magang")
        ? Users
        : BriefcaseBusiness;
};

const getJakartaDateKey = (value: Date) =>
    new Intl.DateTimeFormat("en-CA", {
        timeZone: "Asia/Jakarta",
    }).format(value);

const isDeadlineNear = (date: string | null) => {
    if (!date) {
        return false;
    }

    const deadline = new Date(date);

    if (Number.isNaN(deadline.getTime())) {
        return false;
    }

    const today = new Date(`${getJakartaDateKey(new Date())}T00:00:00`);

    const deadlineDate = new Date(`${getJakartaDateKey(deadline)}T00:00:00`);

    const difference = (deadlineDate.getTime() - today.getTime()) / 86_400_000;

    return difference >= 0 && difference <= 7;
};

const stripHtml = (value: string) =>
    value

        .replace(/<[^>]*>/g, " ")

        .replace(/&nbsp;/gi, " ")

        .replace(/&amp;/gi, "&")

        .replace(/\s+/g, " ")

        .trim();

const getDescription = (description: string | null) =>
    stripHtml(description ?? "") || t("index.list.default_description");

const formatDate = (value: string | null) => {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString(dateLocale.value, {
        day: "numeric",

        month: "long",

        year: "numeric",

        timeZone: "Asia/Jakarta",
    });
};

const pageLinks = computed(() =>
    props.lowongans.links.slice(1, -1).map((link) => ({
        ...link,

        text: /^\d+$/.test(link.label.trim()) ? link.label.trim() : "…",
    })),
);

onBeforeUnmount(() => {
    clearTimeout(searchTimeout);

    removeStartListener?.();

    removeFinishListener?.();
});
</script>

<template>
    <Head>
        <title>{{ t("index.seo.title") }}</title>

        <meta name="description" :content="t('index.seo.description')" />

        <meta property="og:title" :content="t('index.seo.title')" />

        <meta property="og:description" :content="t('index.seo.description')" />

        <meta property="og:type" content="website" />

        <meta
            property="og:url"
            content="https://tanjungbuton-industrial.co.id/karier"
        />

        <link
            rel="canonical"
            href="https://tanjungbuton-industrial.co.id/karier"
        />
    </Head>

    <div
        class="relative min-h-screen overflow-x-clip bg-slate-50/70 dark:bg-slate-950"
    >
        <!-- BACKGROUND AMBIENT -->

        <div
            class="pointer-events-none absolute inset-x-0 -top-28 h-[820px] overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute inset-x-0 top-0 h-60 bg-gradient-to-b from-blue-100/70 via-blue-50/40 to-transparent dark:from-blue-950/30 dark:via-blue-950/10"
            />

            <div
                class="blob blob-a absolute left-[2%] top-0 size-[26rem] rounded-full bg-gradient-to-br from-blue-400/35 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15"
            />

            <div
                class="blob blob-b absolute right-[2%] top-4 size-[22rem] rounded-full bg-gradient-to-tr from-sky-300/35 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10"
            />

            <div
                class="blob blob-c absolute left-1/3 top-56 size-72 rounded-full bg-gradient-to-br from-indigo-300/20 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/10"
            />

            <div
                class="absolute inset-0 opacity-[0.18] dark:opacity-[0.08]"
                style="
                    background-image:
                        linear-gradient(
                            rgba(100, 116, 139, 0.11) 1px,

                            transparent 1px
                        ),
                        linear-gradient(
                            90deg,
                            rgba(100, 116, 139, 0.11) 1px,

                            transparent 1px
                        );

                    background-size: 42px 42px;

                    mask-image: linear-gradient(
                        to bottom,

                        black 0%,

                        black 48%,

                        transparent 100%
                    );

                    -webkit-mask-image: linear-gradient(
                        to bottom,

                        black 0%,

                        black 48%,

                        transparent 100%
                    );
                "
            />

            <div
                class="absolute inset-x-0 bottom-0 h-52 bg-gradient-to-b from-transparent to-slate-50/90 dark:to-slate-950/90"
            />
        </div>

        <!-- CONTENT -->

        <div
            class="relative z-10 mx-auto w-full max-w-[1440px] px-4 pb-12 pt-24 sm:px-6 sm:pt-28 lg:px-8 lg:pb-16 lg:pt-32"
        >
            <!-- HERO -->

            <section class="mx-auto max-w-4xl text-center">
                <div
                    class="reveal mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-blue-700 shadow-sm backdrop-blur-sm dark:border-blue-900/60 dark:bg-slate-900/70 dark:text-blue-300"
                    style="--d: 0"
                >
                    <BriefcaseBusiness class="size-3.5" aria-hidden="true" />

                    <span>{{ t("index.hero.badge") }}</span>
                </div>

                <h1
                    class="reveal text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white"
                    style="--d: 100"
                >
                    {{ t("index.hero.title_prefix") }}

                    <span
                        class="bg-gradient-to-r from-blue-600 via-indigo-600 to-sky-500 bg-clip-text text-transparent"
                    >
                        {{ t("index.hero.title_highlight") }}
                    </span>
                </h1>

                <p
                    class="reveal mx-auto mt-5 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8 dark:text-slate-400"
                    style="--d: 200"
                >
                    {{ t("index.hero.description") }}
                </p>

                <div
                    class="reveal mx-auto mt-8 flex w-fit flex-wrap items-center justify-center gap-2 rounded-2xl border border-slate-200/80 bg-white/75 p-1.5 shadow-sm backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/70"
                    style="--d: 300"
                >
                    <div
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300"
                    >
                        <div
                            class="flex size-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                        >
                            <BriefcaseBusiness
                                class="size-3.5"
                                aria-hidden="true"
                            />
                        </div>

                        <span>
                            {{
                                t("index.stats.positions", {
                                    count: lowongans.total,
                                })
                            }}
                        </span>
                    </div>

                    <div
                        class="hidden h-5 w-px bg-slate-200 sm:block dark:bg-slate-700"
                        aria-hidden="true"
                    />

                    <div
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300"
                    >
                        <div
                            class="flex size-7 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400"
                        >
                            <Building2 class="size-3.5" aria-hidden="true" />
                        </div>

                        <span>
                            {{ t("index.stats.environment") }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- SEARCH & FILTER -->

            <section
                class="reveal mx-auto mt-10 max-w-6xl"
                style="--d: 380"
                :aria-label="t('index.filter.region_label')"
            >
                <div
                    class="rounded-3xl border border-slate-200/80 bg-white/90 p-3 shadow-xl backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                >
                    <div
                        class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_180px_auto]"
                    >
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"
                            />

                            <label for="career-search" class="sr-only">
                                {{ t("index.filter.search_label") }}
                            </label>

                            <input
                                id="career-search"
                                v-model="searchQuery"
                                type="search"
                                autocomplete="off"
                                :placeholder="
                                    t('index.filter.search_placeholder')
                                "
                                class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950/60 dark:text-white"
                            />
                        </div>

                        <div class="relative">
                            <SlidersHorizontal
                                class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"
                            />

                            <label for="career-department" class="sr-only">
                                {{ t("index.filter.department_label") }}
                            </label>

                            <select
                                id="career-department"
                                v-model="selectedDepartment"
                                class="h-12 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50/80 pl-11 pr-10 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-200"
                            >
                                <option value="">
                                    {{ t("index.filter.all_departments") }}
                                </option>

                                <option
                                    v-for="department in departments"
                                    :key="department"
                                    :value="department"
                                >
                                    {{ department }}
                                </option>
                            </select>

                            <ChevronDown
                                class="pointer-events-none absolute right-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"
                            />
                        </div>

                        <div class="relative">
                            <BriefcaseBusiness
                                class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"
                            />

                            <label for="career-type" class="sr-only">
                                {{ t("index.filter.type_label") }}
                            </label>

                            <select
                                id="career-type"
                                v-model="selectedType"
                                class="h-12 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50/80 pl-11 pr-10 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-200"
                            >
                                <option value="">
                                    {{ t("index.filter.all_types") }}
                                </option>

                                <option
                                    v-for="type in types"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ getTypeLabel(type) }}
                                </option>
                            </select>

                            <ChevronDown
                                class="pointer-events-none absolute right-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"
                            />
                        </div>

                        <button
                            v-if="hasFilter"
                            type="button"
                            class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                            @click="clearFilters"
                        >
                            <X class="size-4" aria-hidden="true" />

                            <span class="hidden sm:inline">
                                {{ t("index.filter.reset") }}
                            </span>

                            <span class="sr-only">
                                {{ t("index.filter.reset_sr") }}
                            </span>
                        </button>
                    </div>

                    <div
                        v-if="hasFilter"
                        class="mt-3 flex items-center gap-2 px-2 text-xs text-slate-500 dark:text-slate-400"
                        aria-live="polite"
                    >
                        {{
                            t("index.filter.found", {
                                count: lowongans.total,
                            })
                        }}
                    </div>
                </div>
            </section>

            <!-- JOB LIST -->

            <section
                class="reveal mx-auto mt-8 max-w-6xl"
                style="--d: 460"
                :aria-label="t('index.list.label')"
            >
                <!-- LOADING -->

                <div
                    v-if="isLoading"
                    class="grid gap-5 md:grid-cols-2"
                    aria-busy="true"
                >
                    <article
                        v-for="index in 4"
                        :key="`skeleton-${index}`"
                        class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 dark:border-slate-800 dark:bg-slate-900"
                        aria-hidden="true"
                    >
                        <div
                            class="absolute inset-x-0 top-0 h-1 bg-slate-200 dark:bg-slate-800"
                        />

                        <div class="animate-pulse">
                            <div class="flex items-start justify-between gap-4">
                                <div
                                    class="size-12 shrink-0 rounded-2xl bg-slate-200 dark:bg-slate-800"
                                />

                                <div class="flex gap-2">
                                    <div
                                        class="h-6 w-20 rounded-full bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-6 w-24 rounded-full bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                <div
                                    class="h-6 w-3/4 rounded-lg bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-full rounded bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-11/12 rounded bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-2/3 rounded bg-slate-200 dark:bg-slate-800"
                                />
                            </div>

                            <div
                                class="mt-5 space-y-3 border-t border-slate-100 pt-5 dark:border-slate-800"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="size-4 rounded bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-4 w-40 rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>

                                <div class="flex items-center gap-3">
                                    <div
                                        class="size-4 rounded bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-4 w-32 rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>

                                <div class="flex items-center gap-3">
                                    <div
                                        class="size-4 rounded bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-4 w-48 rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>
                            </div>

                            <div
                                class="mt-6 h-11 w-full rounded-2xl bg-slate-200 dark:bg-slate-800"
                            />
                        </div>
                    </article>
                </div>

                <!-- RESULTS -->

                <div
                    v-else-if="lowongans.data.length"
                    class="grid gap-5 md:grid-cols-2"
                >
                    <article
                        v-for="lowongan in lowongans.data"
                        :key="lowongan.id"
                        class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-sky-400 opacity-80"
                            aria-hidden="true"
                        />

                        <div class="p-5 sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div
                                    class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition duration-300 group-hover:scale-105 group-hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-400"
                                >
                                    <component
                                        :is="
                                            getTypeIcon(lowongan.tipe_pekerjaan)
                                        "
                                        class="size-5"
                                        aria-hidden="true"
                                    />
                                </div>

                                <div class="flex flex-wrap justify-end gap-2">
                                    <span
                                        v-if="lowongan.tipe_pekerjaan"
                                        class="rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300"
                                    >
                                        {{
                                            getTypeLabel(
                                                lowongan.tipe_pekerjaan,
                                            )
                                        }}
                                    </span>

                                    <span
                                        v-if="
                                            isDeadlineNear(
                                                lowongan.tanggal_tutup,
                                            )
                                        "
                                        class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300"
                                    >
                                        {{ t("index.list.deadline_soon") }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5">
                                <h2
                                    class="text-xl font-bold tracking-tight text-slate-900 transition group-hover:text-blue-700 dark:text-white dark:group-hover:text-blue-400"
                                >
                                    {{
                                        localizedLowonganValue(
                                            lowongan,
                                            "judul",
                                        ) || "Lowongan"
                                    }}
                                </h2>

                                <p
                                    class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        getDescription(
                                            localizedLowonganValue(
                                                lowongan,
                                                "deskripsi",
                                            ),
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                class="mt-5 grid gap-2.5 border-t border-slate-100 pt-5 dark:border-slate-800"
                            >
                                <div
                                    v-if="
                                        localizedLowonganValue(
                                            lowongan,
                                            'departemen',
                                        )
                                    "
                                    class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <Building2
                                        class="mt-0.5 size-4 shrink-0 text-slate-400"
                                        aria-hidden="true"
                                    />

                                    <span class="min-w-0 break-words">
                                        {{
                                            localizedLowonganValue(
                                                lowongan,
                                                "departemen",
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    v-if="
                                        localizedLowonganValue(
                                            lowongan,
                                            'lokasi',
                                        )
                                    "
                                    class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <MapPin
                                        class="mt-0.5 size-4 shrink-0 text-slate-400"
                                        aria-hidden="true"
                                    />

                                    <span class="min-w-0 break-words">
                                        {{
                                            localizedLowonganValue(
                                                lowongan,
                                                "lokasi",
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    v-if="lowongan.tanggal_tutup"
                                    class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <CalendarDays
                                        class="mt-0.5 size-4 shrink-0 text-slate-400"
                                        aria-hidden="true"
                                    />

                                    <span>
                                        {{
                                            t("index.list.deadline", {
                                                date: formatDate(
                                                    lowongan.tanggal_tutup,
                                                ),
                                            })
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-6">
                                <Link
                                    :href="`/karier/${lowongan.slug}`"
                                    class="group/button inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600 dark:bg-white dark:text-slate-900 dark:hover:bg-blue-400"
                                >
                                    {{ t("index.list.view_detail") }}

                                    <ArrowRight
                                        class="size-4 transition-transform duration-300 group-hover/button:translate-x-1"
                                        aria-hidden="true"
                                    />
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- EMPTY -->

                <div
                    v-else
                    class="rounded-3xl border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900/70"
                    role="status"
                >
                    <div
                        class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                        aria-hidden="true"
                    >
                        <Search class="size-7" />
                    </div>

                    <h3
                        class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                    >
                        {{
                            hasFilter
                                ? t("index.empty.not_found_title")
                                : t("index.empty.title")
                        }}
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{
                            hasFilter
                                ? t("index.empty.not_found_description")
                                : t("index.empty.description")
                        }}
                    </p>

                    <button
                        v-if="hasFilter"
                        type="button"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-600 dark:bg-white dark:text-slate-900"
                        @click="clearFilters"
                    >
                        <X class="size-4" aria-hidden="true" />

                        {{ t("index.empty.reset_filter") }}
                    </button>
                </div>
            </section>

            <!-- PAGINATION -->

            <section
                v-if="lowongans.last_page > 1 && !isLoading"
                class="reveal mx-auto mt-8 max-w-6xl"
                style="--d: 540"
                :aria-label="t('index.pagination.label')"
            >
                <div
                    class="flex flex-col gap-4 rounded-3xl border border-slate-200/80 bg-white/80 p-4 shadow-sm backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-900/70"
                >
                    <p
                        class="text-center text-xs text-slate-500 sm:text-left dark:text-slate-400"
                    >
                        <template
                            v-if="
                                lowongans.from !== null && lowongans.to !== null
                            "
                        >
                            {{ lowongans.from }}–{{ lowongans.to }}

                            {{ t("index.pagination.of") }}

                            {{ lowongans.total }}
                        </template>

                        <template v-else>
                            {{ lowongans.total }}
                        </template>
                    </p>

                    <nav
                        class="flex items-center justify-center gap-1"
                        :aria-label="t('index.pagination.navigation')"
                    >
                        <button
                            type="button"
                            :disabled="!lowongans.prev_page_url || isLoading"
                            class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 disabled:pointer-events-none disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                            :aria-label="t('index.pagination.previous')"
                            @click="goToPage(lowongans.prev_page_url)"
                        >
                            <ChevronLeft class="size-4" aria-hidden="true" />
                        </button>

                        <button
                            v-for="(link, index) in pageLinks"
                            :key="`${link.label}-${index}`"
                            type="button"
                            :disabled="
                                !link.url ||
                                link.label.trim() === '…' ||
                                isLoading
                            "
                            :aria-current="link.active ? 'page' : undefined"
                            :aria-label="
                                link.active
                                    ? `${t('index.pagination.current')} ${link.text}`
                                    : `${t('index.pagination.page')} ${link.text}`
                            "
                            :class="[
                                'inline-flex size-10 items-center justify-center rounded-xl text-sm font-semibold transition',

                                link.active
                                    ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20'
                                    : link.label.trim() === '…'
                                      ? 'cursor-default text-slate-400 dark:text-slate-600'
                                      : 'border border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/40 dark:hover:text-blue-400',
                            ]"
                            @click="goToPage(link.url)"
                        >
                            {{ link.text }}
                        </button>

                        <button
                            type="button"
                            :disabled="!lowongans.next_page_url || isLoading"
                            class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 disabled:pointer-events-none disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                            :aria-label="t('index.pagination.next')"
                            @click="goToPage(lowongans.next_page_url)"
                        >
                            <ChevronRight class="size-4" aria-hidden="true" />
                        </button>
                    </nav>
                </div>
            </section>
        </div>
    </div>
</template>
