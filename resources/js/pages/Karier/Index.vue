<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    ArrowRight,
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    Clock3,
    MapPin,
    Search,
    SlidersHorizontal,
    Users,
    X,
} from "lucide-vue-next";
import PublicLayout from "@/layouts/PublicLayout.vue";

interface Lowongan {
    id: number;
    judul: string;
    slug: string;
    deskripsi: string | null;
    departemen: string | null;
    tipe_pekerjaan: string | null;
    lokasi: string | null;
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

defineOptions({
    layout: PublicLayout,
});

/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const isLoading = ref(false);

let removeStartListener: (() => void) | undefined;
let removeFinishListener: (() => void) | undefined;

onMounted(() => {
    removeStartListener = router.on("start", () => {
        isLoading.value = true;
    });

    removeFinishListener = router.on("finish", () => {
        isLoading.value = false;
    });
});

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const searchQuery = ref(props.filters.search ?? "");
const selectedDepartment = ref(props.filters.departemen ?? "");
const selectedType = ref(props.filters.tipe ?? "");

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const applyFilter = () => {
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

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const typeLabels: Record<string, string> = {
    full_time: "Full Time",
    part_time: "Part Time",
    contract: "Contract",
    kontrak: "Kontrak",
    internship: "Internship",
    magang: "Magang",
    freelance: "Freelance",
    remote: "Remote",
};

const normalizeType = (value: string) =>
    value
        .trim()
        .toLowerCase()
        .replace(/[\s-]+/g, "_");

const getTypeLabel = (type: string | null) => {
    if (!type) return "Full Time";

    return typeLabels[normalizeType(type)] ?? type;
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
    if (!date) return false;

    const deadline = new Date(date);

    if (Number.isNaN(deadline.getTime())) {
        return false;
    }

    const todayKey = getJakartaDateKey(new Date());
    const deadlineKey = getJakartaDateKey(deadline);

    const today = new Date(`${todayKey}T00:00:00`);
    const deadlineDate = new Date(`${deadlineKey}T00:00:00`);

    const difference = (deadlineDate.getTime() - today.getTime()) / 86_400_000;

    return difference >= 0 && difference <= 7;
};

const getDescription = (description: string | null) =>
    description?.trim() ||
    "Temukan kesempatan untuk berkembang dan berkontribusi bersama KITB.";

const formatDate = (value: string | null) => {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        timeZone: "Asia/Jakarta",
    });
};

const pageLinks = computed(() => props.lowongans.links.slice(1, -1));

/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
    clearTimeout(searchTimeout);

    removeStartListener?.();
    removeFinishListener?.();
});
</script>

<template>
    <Head>
        <title>Karier | KITB</title>

        <meta
            name="description"
            content="Temukan peluang karier dan bergabung bersama PT Kawasan Industri Tanjung Buton."
        />

        <meta property="og:title" content="Karier | KITB" />

        <meta
            property="og:description"
            content="Temukan peluang karier dan bergabung bersama PT Kawasan Industri Tanjung Buton."
        />

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
        <!-- =========================================================
             BACKGROUND AMBIENT
        ========================================================== -->

        <div
            class="pointer-events-none absolute inset-x-0 -top-28 h-[820px] overflow-hidden"
            aria-hidden="true"
        >
            <!-- Top fade -->

            <div
                class="absolute inset-x-0 top-0 h-60 bg-gradient-to-b from-blue-100/70 via-blue-50/40 to-transparent dark:from-blue-950/30 dark:via-blue-950/10"
            />

            <!-- Blob kiri -->

            <div
                class="blob blob-a absolute left-[2%] top-0 size-[26rem] rounded-full bg-gradient-to-br from-blue-400/35 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15"
            />

            <!-- Blob kanan -->

            <div
                class="blob blob-b absolute right-[2%] top-4 size-[22rem] rounded-full bg-gradient-to-tr from-sky-300/35 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10"
            />

            <!-- Blob tengah -->

            <div
                class="blob blob-c absolute left-1/3 top-56 size-72 rounded-full bg-gradient-to-br from-indigo-300/20 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/10"
            />

            <!-- Grid -->

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

            <!-- Fade ke background -->

            <div
                class="absolute inset-x-0 bottom-0 h-52 bg-gradient-to-b from-transparent to-slate-50/90 dark:to-slate-950/90"
            />
        </div>

        <!-- =========================================================
             CONTENT
        ========================================================== -->

        <div
            class="relative z-10 mx-auto w-full max-w-[1440px] px-4 pb-12 pt-24 sm:px-6 sm:pt-28 lg:px-8 lg:pb-16 lg:pt-32"
        >
            <!-- =====================================================
                 HERO
            ====================================================== -->

            <section class="mx-auto max-w-4xl text-center">
                <!-- Badge -->

                <div
                    class="reveal mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-blue-700 shadow-sm shadow-blue-900/5 backdrop-blur-sm dark:border-blue-900/60 dark:bg-slate-900/70 dark:text-blue-300"
                    style="--d: 0"
                >
                    <BriefcaseBusiness class="size-3.5" aria-hidden="true" />

                    <span>Peluang Karier di KITB</span>
                </div>

                <!-- Heading -->

                <h1
                    class="reveal text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white"
                    style="--d: 100"
                >
                    Bangun Masa Depan

                    <span
                        class="bg-gradient-to-r from-blue-600 via-indigo-600 to-sky-500 bg-clip-text text-transparent"
                    >
                        Bersama KITB
                    </span>
                </h1>

                <!-- Description -->

                <p
                    class="reveal mx-auto mt-5 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8 dark:text-slate-400"
                    style="--d: 200"
                >
                    Temukan peluang untuk berkembang, berkolaborasi, dan
                    memberikan kontribusi nyata dalam membangun kawasan industri
                    yang berkelanjutan.
                </p>

                <!-- Stats -->

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

                        <span> {{ lowongans.total }} Posisi Tersedia </span>
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

                        <span>Lingkungan Profesional</span>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 SEARCH & FILTER
            ====================================================== -->

            <section
                class="reveal mx-auto mt-10 max-w-6xl"
                style="--d: 380"
                aria-label="Pencarian dan filter lowongan"
            >
                <div
                    class="rounded-3xl border border-slate-200/80 bg-white/90 p-3 shadow-xl shadow-slate-900/[0.04] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
                >
                    <div
                        class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_180px_auto]"
                    >
                        <!-- Search -->

                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"
                            />

                            <label for="career-search" class="sr-only">
                                Cari lowongan
                            </label>

                            <input
                                id="career-search"
                                v-model="searchQuery"
                                type="search"
                                autocomplete="off"
                                placeholder="Cari posisi, departemen, atau lokasi..."
                                class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950/60 dark:text-white dark:focus:border-blue-500 dark:focus:bg-slate-950"
                            />
                        </div>

                        <!-- Department -->

                        <div class="relative">
                            <SlidersHorizontal
                                class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"
                            />

                            <label for="career-department" class="sr-only">
                                Filter departemen
                            </label>

                            <select
                                id="career-department"
                                v-model="selectedDepartment"
                                class="h-12 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50/80 pl-11 pr-8 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-200 dark:focus:border-blue-500 dark:focus:bg-slate-950"
                            >
                                <option value="">Semua Departemen</option>

                                <option
                                    v-for="department in departments"
                                    :key="department"
                                    :value="department"
                                >
                                    {{ department }}
                                </option>
                            </select>
                        </div>

                        <!-- Type -->

                        <div class="relative">
                            <BriefcaseBusiness
                                class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden="true"
                            />

                            <label for="career-type" class="sr-only">
                                Filter tipe pekerjaan
                            </label>

                            <select
                                id="career-type"
                                v-model="selectedType"
                                class="h-12 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50/80 pl-11 pr-8 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-200 dark:focus:border-blue-500 dark:focus:bg-slate-950"
                            >
                                <option value="">Semua Tipe</option>

                                <option
                                    v-for="type in types"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ getTypeLabel(type) }}
                                </option>
                            </select>
                        </div>

                        <!-- Reset -->

                        <button
                            v-if="hasFilter"
                            type="button"
                            class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-4 focus:ring-red-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-red-900/60 dark:hover:bg-red-950/30 dark:hover:text-red-400"
                            @click="clearFilters"
                        >
                            <X class="size-4" aria-hidden="true" />

                            <span class="hidden sm:inline"> Reset </span>

                            <span class="sr-only"> pencarian dan filter </span>
                        </button>
                    </div>

                    <div
                        v-if="hasFilter"
                        class="mt-3 flex items-center gap-2 px-2 text-xs text-slate-500 dark:text-slate-400"
                        aria-live="polite"
                    >
                        <span>
                            Ditemukan

                            <strong
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ lowongans.total }}
                            </strong>

                            posisi.
                        </span>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 JOB LIST
            ====================================================== -->

            <section
                class="reveal mx-auto mt-8 max-w-6xl"
                style="--d: 460"
                aria-label="Daftar lowongan pekerjaan"
            >
                <!-- =================================================
                     SKELETON
                ================================================== -->

                <div
                    v-if="isLoading"
                    class="grid gap-5 md:grid-cols-2"
                    aria-live="polite"
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
                            <!-- Header -->

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

                            <!-- Title -->

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

                            <!-- Meta -->

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

                            <!-- Button -->

                            <div
                                class="mt-6 h-11 w-full rounded-2xl bg-slate-200 dark:bg-slate-800"
                            />
                        </div>
                    </article>
                </div>

                <!-- =================================================
                     DATA
                ================================================== -->

                <div
                    v-else-if="lowongans.data.length"
                    class="grid gap-5 md:grid-cols-2"
                >
                    <article
                        v-for="lowongan in lowongans.data"
                        :key="lowongan.id"
                        class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/[0.07] dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-900/70"
                    >
                        <!-- Top accent -->

                        <div
                            class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-sky-400 opacity-80"
                            aria-hidden="true"
                        />

                        <div class="p-5 sm:p-6">
                            <!-- Header -->

                            <div class="flex items-start justify-between gap-4">
                                <div
                                    class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition duration-300 group-hover:scale-105 group-hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-400 dark:group-hover:bg-blue-950"
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
                                        Segera Ditutup
                                    </span>
                                </div>
                            </div>

                            <!-- Title -->

                            <div class="mt-5">
                                <h2
                                    class="text-xl font-bold tracking-tight text-slate-900 transition group-hover:text-blue-700 dark:text-white dark:group-hover:text-blue-400"
                                >
                                    {{ lowongan.judul }}
                                </h2>

                                <p
                                    class="mt-2 line-clamp-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                                >
                                    {{ getDescription(lowongan.deskripsi) }}
                                </p>
                            </div>

                            <!-- Meta -->

                            <div
                                class="mt-5 grid gap-2.5 border-t border-slate-100 pt-5 dark:border-slate-800"
                            >
                                <div
                                    v-if="lowongan.departemen"
                                    class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <Building2
                                        class="mt-0.5 size-4 shrink-0 text-slate-400"
                                        aria-hidden="true"
                                    />

                                    <span class="min-w-0 break-words">
                                        {{ lowongan.departemen }}
                                    </span>
                                </div>

                                <div
                                    v-if="lowongan.lokasi"
                                    class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <MapPin
                                        class="mt-0.5 size-4 shrink-0 text-slate-400"
                                        aria-hidden="true"
                                    />

                                    <span class="min-w-0 break-words">
                                        {{ lowongan.lokasi }}
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
                                        Batas lamaran:
                                        {{ formatDate(lowongan.tanggal_tutup) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action -->

                            <div class="mt-6">
                                <Link
                                    :href="`/karier/${lowongan.slug}`"
                                    class="group/button inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:bg-white dark:text-slate-900 dark:hover:bg-blue-400"
                                >
                                    Lihat Detail Posisi

                                    <ArrowRight
                                        class="size-4 transition-transform duration-300 group-hover/button:translate-x-1"
                                        aria-hidden="true"
                                    />
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- =================================================
                     EMPTY STATE
                ================================================== -->

                <div
                    v-else
                    class="rounded-3xl border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900/70"
                    role="status"
                    aria-live="polite"
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
                                ? "Posisi tidak ditemukan"
                                : "Belum ada lowongan"
                        }}
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{
                            hasFilter
                                ? "Belum ada posisi yang sesuai dengan pencarian atau filter yang kamu pilih."
                                : "Saat ini belum ada posisi yang dibuka. Silakan cek kembali nanti."
                        }}
                    </p>

                    <button
                        v-if="hasFilter"
                        type="button"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:bg-white dark:text-slate-900"
                        @click="clearFilters"
                    >
                        <X class="size-4" aria-hidden="true" />

                        Reset Filter
                    </button>
                </div>
            </section>

            <!-- =====================================================
                 PAGINATION
            ====================================================== -->

            <section
                v-if="lowongans.last_page > 1 && !isLoading"
                class="reveal mx-auto mt-8 max-w-6xl"
                style="--d: 540"
                aria-label="Navigasi halaman lowongan"
            >
                <div
                    class="flex flex-col gap-4 rounded-3xl border border-slate-200/80 bg-white/80 p-4 shadow-sm backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-900/70"
                >
                    <div
                        class="text-center text-xs text-slate-500 sm:text-left dark:text-slate-400"
                    >
                        Menampilkan

                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ lowongans.from ?? 0 }}
                        </span>

                        -

                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ lowongans.to ?? 0 }}
                        </span>

                        dari

                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ lowongans.total }}
                        </span>

                        posisi
                    </div>

                    <div class="flex items-center justify-center gap-1.5">
                        <!-- Previous -->

                        <Link
                            v-if="lowongans.prev_page_url"
                            :href="lowongans.prev_page_url"
                            preserve-scroll
                            aria-label="Halaman sebelumnya"
                            class="flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-400"
                        >
                            <ChevronLeft class="size-4" aria-hidden="true" />
                        </Link>

                        <span
                            v-else
                            class="flex size-9 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-300 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-700"
                            aria-hidden="true"
                        >
                            <ChevronLeft class="size-4" />
                        </span>

                        <!-- Pages -->

                        <template
                            v-for="(link, index) in pageLinks"
                            :key="`${link.label}-${index}`"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                :aria-current="link.active ? 'page' : undefined"
                                :aria-label="
                                    link.active
                                        ? `Halaman ${link.label}, saat ini`
                                        : `Halaman ${link.label}`
                                "
                                class="flex size-9 items-center justify-center rounded-xl border text-xs font-semibold transition focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                                :class="
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-600/20'
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-400'
                                "
                                v-html="link.label"
                            />

                            <span
                                v-else
                                class="flex size-9 items-center justify-center text-xs text-slate-400"
                                aria-hidden="true"
                                v-html="link.label"
                            />
                        </template>

                        <!-- Next -->

                        <Link
                            v-if="lowongans.next_page_url"
                            :href="lowongans.next_page_url"
                            preserve-scroll
                            aria-label="Halaman berikutnya"
                            class="flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-400"
                        >
                            <ChevronRight class="size-4" aria-hidden="true" />
                        </Link>

                        <span
                            v-else
                            class="flex size-9 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-300 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-700"
                            aria-hidden="true"
                        >
                            <ChevronRight class="size-4" />
                        </span>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 BOTTOM CTA
            ====================================================== -->

            <section class="reveal mx-auto mt-12 max-w-6xl" style="--d: 620">
                <div
                    class="relative overflow-hidden rounded-[2rem] border border-blue-200/70 bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 px-6 py-10 text-center shadow-xl shadow-blue-900/10 sm:px-10 lg:py-12 dark:border-blue-800/50"
                >
                    <div
                        class="pointer-events-none absolute -right-20 -top-20 size-64 rounded-full border border-white/10"
                        aria-hidden="true"
                    />

                    <div
                        class="pointer-events-none absolute -bottom-32 -left-20 size-72 rounded-full border border-white/10"
                        aria-hidden="true"
                    />

                    <div class="relative">
                        <div
                            class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20"
                            aria-hidden="true"
                        >
                            <Clock3 class="size-5" />
                        </div>

                        <h2
                            class="mt-5 text-2xl font-bold tracking-tight text-white sm:text-3xl"
                        >
                            Belum menemukan posisi yang sesuai?
                        </h2>

                        <p
                            class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-blue-100 sm:text-base"
                        >
                            Pantau halaman karier KITB secara berkala untuk
                            mendapatkan informasi mengenai kesempatan dan posisi
                            terbaru.
                        </p>

                        <a
                            href="/karier"
                            class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-blue-700 shadow-lg shadow-blue-950/10 transition hover:-translate-y-0.5 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-white/40"
                        >
                            Lihat Semua Lowongan

                            <ArrowRight class="size-4" aria-hidden="true" />
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
/* ==========================================================================
   Fade In
   ========================================================================== */

.reveal {
    opacity: 0;
    transform: translateY(14px);
    animation: reveal 0.7s cubic-bezier(0.2, 0.7, 0.2, 1) forwards;
    animation-delay: calc(var(--d, 0) * 1ms);
}

@keyframes reveal {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ==========================================================================
   Blobs
   ========================================================================== */

.blob {
    will-change: transform;
    transform-origin: center;
}

.blob-a {
    animation: blob-a 14s ease-in-out infinite;
}

.blob-b {
    animation: blob-b 17s ease-in-out infinite;
}

.blob-c {
    animation: blob-c 20s ease-in-out infinite;
}

@keyframes blob-a {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    33% {
        transform: translate3d(28px, 18px, 0) scale(1.05);
    }

    66% {
        transform: translate3d(-16px, 32px, 0) scale(0.96);
    }
}

@keyframes blob-b {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    40% {
        transform: translate3d(-32px, 22px, 0) scale(1.08);
    }

    75% {
        transform: translate3d(16px, -16px, 0) scale(0.95);
    }
}

@keyframes blob-c {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(0, 36px, 0) scale(1.1);
    }
}

/* ==========================================================================
   Accessibility
   ========================================================================== */

@media (prefers-reduced-motion: reduce) {
    .reveal {
        opacity: 1;
        transform: none;
        animation: none;
    }

    .blob-a,
    .blob-b,
    .blob-c {
        animation: none;
    }

    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        transition-duration: 0.01ms !important;
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
    }
}

/* ==========================================================================
   Mobile
   ========================================================================== */

@media (max-width: 640px) {
    .blob-a {
        left: -9rem;
        width: 20rem;
        height: 20rem;
    }

    .blob-b {
        right: -8rem;
        width: 17rem;
        height: 17rem;
    }

    .blob-c {
        left: 25%;
        width: 18rem;
        height: 18rem;
    }
}
</style>
