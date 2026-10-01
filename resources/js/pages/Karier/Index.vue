<script setup lang="ts">
import { computed, ref, type Component } from "vue";
import { Head, Link } from "@inertiajs/vue3";
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
    Sparkles,
    Users,
    X,
} from "lucide-vue-next";

/*
|--------------------------------------------------------------------------
| Interfaces
|--------------------------------------------------------------------------
*/

interface Lowongan {
    id: number;
    judul: string;
    slug: string;
    departemen: string | null;
    lokasi: string | null;
    tipe_pekerjaan: string | null;
    deskripsi: string | null;
    tanggung_jawab: string | null;
    kualifikasi: string | null;
    benefit: string | null;
    tanggal_mulai: string | null;
    tanggal_tutup: string | null;
    status: string;
    unggulan: boolean;
    urutan: number;
    created_at: string | null;
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
    lowongans: Paginator<Lowongan>;
}

const props = defineProps<Props>();

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const search = ref("");
const selectedType = ref("");
const selectedDepartment = ref("");

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const formatDate = (value: string | null) => {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
};

const formatDateLong = (value: string | null) => {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};

const truncate = (value: string | null, length = 150) => {
    if (!value) {
        return "Informasi lowongan pekerjaan tersedia pada halaman detail.";
    }

    const clean = value
        .replace(/<[^>]*>/g, " ")
        .replace(/\s+/g, " ")
        .trim();

    if (clean.length <= length) {
        return clean;
    }

    return `${clean.slice(0, length).trim()}...`;
};

const getTypeLabel = (value: string | null) => {
    if (!value) return "Tipe pekerjaan";

    const labels: Record<string, string> = {
        full_time: "Full Time",
        part_time: "Part Time",
        contract: "Kontrak",
        internship: "Magang",
        freelance: "Freelance",
        remote: "Remote",
    };

    return labels[value] ?? value;
};

const getTypeIcon = (value: string | null): Component => {
    if (value === "internship") {
        return Users;
    }

    return BriefcaseBusiness;
};

/*
|--------------------------------------------------------------------------
| Filter Options
|--------------------------------------------------------------------------
*/

const typeOptions = computed(() => {
    const values = props.lowongans.data
        .map((item) => item.tipe_pekerjaan)
        .filter((value): value is string => Boolean(value));

    return [...new Set(values)];
});

const departmentOptions = computed(() => {
    const values = props.lowongans.data
        .map((item) => item.departemen)
        .filter((value): value is string => Boolean(value));

    return [...new Set(values)];
});

/*
|--------------------------------------------------------------------------
| Client-side filtering
|--------------------------------------------------------------------------
|
| Pagination tetap berasal dari server.
| Filtering di bawah ini hanya berlaku pada data halaman yang sedang
| ditampilkan. Untuk dataset besar, filter sebaiknya dipindahkan ke
| controller menggunakan query parameter.
|--------------------------------------------------------------------------
*/

const filteredLowongans = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    return props.lowongans.data.filter((item) => {
        const matchesSearch =
            !keyword ||
            item.judul.toLowerCase().includes(keyword) ||
            item.departemen?.toLowerCase().includes(keyword) ||
            item.lokasi?.toLowerCase().includes(keyword) ||
            item.tipe_pekerjaan?.toLowerCase().includes(keyword);

        const matchesType =
            !selectedType.value || item.tipe_pekerjaan === selectedType.value;

        const matchesDepartment =
            !selectedDepartment.value ||
            item.departemen === selectedDepartment.value;

        return matchesSearch && matchesType && matchesDepartment;
    });
});

const hasFilters = computed(
    () =>
        Boolean(search.value) ||
        Boolean(selectedType.value) ||
        Boolean(selectedDepartment.value),
);

const resetFilters = () => {
    search.value = "";
    selectedType.value = "";
    selectedDepartment.value = "";
};

const availableCount = computed(() => props.lowongans.total);

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const visiblePaginationLinks = computed(() =>
    props.lowongans.links.filter((link) => {
        if (
            link.label.includes("Previous") ||
            link.label.includes("Next") ||
            link.label.includes("pagination.previous") ||
            link.label.includes("pagination.next")
        ) {
            return false;
        }

        return true;
    }),
);

const previousLink = computed(() => props.lowongans.links[0] ?? null);

const nextLink = computed(
    () => props.lowongans.links[props.lowongans.links.length - 1] ?? null,
);
</script>

<template>
    <Head title="Karier" />

    <div
        class="relative min-h-full overflow-hidden bg-slate-50/50 transition-colors duration-300 dark:bg-slate-950/50"
    >
        <!-- =========================================================
             DECORATIVE BACKGROUND
        ========================================================== -->

        <div
            class="pointer-events-none absolute inset-x-0 top-0 z-0 h-[560px] overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="blob-shape absolute -left-32 -top-36 size-[30rem] rounded-full bg-gradient-to-br from-blue-400/25 via-indigo-400/15 to-transparent blur-3xl dark:from-blue-500/15 dark:via-indigo-500/10 dark:to-transparent"
            />

            <div
                class="blob-shape-delayed absolute -right-28 top-8 size-[25rem] rounded-full bg-gradient-to-tr from-sky-300/25 via-blue-400/15 to-transparent blur-3xl dark:from-sky-500/12 dark:via-blue-500/10 dark:to-transparent"
            />

            <div
                class="blob-shape-slow absolute left-[38%] -top-48 size-[23rem] rounded-full bg-gradient-to-br from-indigo-300/15 via-blue-300/10 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/5 dark:to-transparent"
            />

            <div class="absolute inset-0 opacity-40 dark:opacity-15">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px]"
                />
            </div>

            <div
                class="absolute inset-x-0 bottom-0 h-52 bg-gradient-to-b from-transparent to-slate-50/95 dark:to-slate-950/95"
            />
        </div>

        <!-- =========================================================
             MAIN CONTENT
        ========================================================== -->

        <main
            class="relative z-10 mx-auto w-full max-w-[1440px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10"
        >
            <!-- =====================================================
                 HERO
            ====================================================== -->

            <section
                class="relative mb-8 overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-sm shadow-slate-200/50 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
            >
                <div
                    class="absolute inset-y-0 right-0 hidden w-1/2 bg-gradient-to-l from-blue-50/80 to-transparent dark:from-blue-950/20 lg:block"
                    aria-hidden="true"
                />

                <div
                    class="relative px-6 py-8 sm:px-8 sm:py-10 lg:px-12 lg:py-12"
                >
                    <div class="max-w-3xl">
                        <!-- Eyebrow -->

                        <div
                            class="mb-4 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3.5 py-1.5 text-xs font-semibold text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300"
                        >
                            <Sparkles class="size-3.5" />
                            Bergabung bersama KITB
                        </div>

                        <!-- Heading -->

                        <h1
                            class="text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl lg:text-5xl"
                        >
                            Bangun karier dan
                            <span
                                class="bg-gradient-to-r from-blue-600 via-indigo-600 to-sky-500 bg-clip-text text-transparent"
                            >
                                masa depan bersama kami.
                            </span>
                        </h1>

                        <p
                            class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300 sm:text-base"
                        >
                            Temukan peluang karier di PT Kawasan Industri
                            Tanjung Buton. Jelajahi posisi yang tersedia dan
                            temukan kesempatan untuk berkembang bersama tim
                            kami.
                        </p>

                        <!-- Mini stats -->

                        <div
                            class="mt-7 flex flex-wrap items-center gap-x-6 gap-y-3"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="flex size-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                >
                                    <BriefcaseBusiness class="size-4" />
                                </span>

                                <div>
                                    <p
                                        class="text-lg font-bold leading-none text-slate-900 dark:text-white"
                                    >
                                        {{ availableCount }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Posisi tersedia
                                    </p>
                                </div>
                            </div>

                            <div
                                class="hidden h-8 w-px bg-slate-200 sm:block dark:bg-slate-700"
                            />

                            <div class="flex items-center gap-2">
                                <span
                                    class="flex size-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400"
                                >
                                    <Building2 class="size-4" />
                                </span>

                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        KITB
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        PT Kawasan Industri Tanjung Buton
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 SEARCH & FILTER
            ====================================================== -->

            <section class="mb-7">
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white/95 p-4 shadow-sm shadow-slate-200/40 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10 sm:p-5"
                >
                    <div
                        class="flex flex-col gap-3 lg:flex-row lg:items-center"
                    >
                        <!-- Search -->

                        <div class="relative min-w-0 flex-1">
                            <Search
                                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="search"
                                type="search"
                                placeholder="Cari posisi, departemen, lokasi..."
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-10 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/70 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-blue-500 dark:focus:bg-slate-800"
                            />

                            <button
                                v-if="search"
                                type="button"
                                class="absolute right-3 top-1/2 flex size-6 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-slate-700 dark:hover:text-slate-200"
                                aria-label="Hapus pencarian"
                                @click="search = ''"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>

                        <!-- Department -->

                        <div class="relative lg:w-56">
                            <Building2
                                class="pointer-events-none absolute left-3.5 top-1/2 z-10 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <select
                                v-model="selectedDepartment"
                                class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-9 text-sm text-slate-700 outline-none transition-all focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-200 dark:focus:border-blue-500 dark:focus:bg-slate-800"
                            >
                                <option value="">Semua Departemen</option>

                                <option
                                    v-for="department in departmentOptions"
                                    :key="department"
                                    :value="department"
                                >
                                    {{ department }}
                                </option>
                            </select>
                        </div>

                        <!-- Type -->

                        <div class="relative lg:w-52">
                            <BriefcaseBusiness
                                class="pointer-events-none absolute left-3.5 top-1/2 z-10 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <select
                                v-model="selectedType"
                                class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-9 text-sm text-slate-700 outline-none transition-all focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-200 dark:focus:border-blue-500 dark:focus:bg-slate-800"
                            >
                                <option value="">Semua Tipe</option>

                                <option
                                    v-for="type in typeOptions"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ getTypeLabel(type) }}
                                </option>
                            </select>
                        </div>

                        <!-- Reset -->

                        <button
                            v-if="hasFilters"
                            type="button"
                            class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-medium text-slate-600 transition-all hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                            @click="resetFilters"
                        >
                            <X class="size-4" />
                            Reset
                        </button>
                    </div>

                    <div
                        class="mt-3 flex flex-col gap-1 text-xs text-slate-500 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p>
                            Menampilkan
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ filteredLowongans.length }}
                            </span>
                            lowongan pada halaman ini.
                        </p>

                        <p v-if="props.lowongans.total">
                            Total {{ props.lowongans.total }} posisi tersedia
                        </p>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 JOB LIST
            ====================================================== -->

            <section>
                <div
                    class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400"
                        >
                            Career Opportunities
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl"
                        >
                            Posisi yang tersedia
                        </h2>
                    </div>

                    <p
                        v-if="props.lowongans.total"
                        class="text-sm text-slate-500 dark:text-slate-400"
                    >
                        {{ props.lowongans.from }}–{{ props.lowongans.to }} dari
                        {{ props.lowongans.total }} lowongan
                    </p>
                </div>

                <!-- Cards -->

                <div
                    v-if="filteredLowongans.length"
                    class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="lowongan in filteredLowongans"
                        :key="lowongan.id"
                        class="group relative flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/40 transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-100/50 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10 dark:hover:border-blue-900 dark:hover:shadow-blue-950/20"
                    >
                        <!-- Top accent -->

                        <div
                            class="h-1 w-full bg-gradient-to-r from-blue-600 via-indigo-500 to-sky-400 opacity-80 transition-opacity group-hover:opacity-100"
                        />

                        <div class="flex flex-1 flex-col p-5 sm:p-6">
                            <!-- Badges -->

                            <div
                                class="mb-4 flex min-h-7 items-start justify-between gap-3"
                            >
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"
                                    >
                                        <component
                                            :is="
                                                getTypeIcon(
                                                    lowongan.tipe_pekerjaan,
                                                )
                                            "
                                            class="size-3"
                                        />
                                        {{
                                            getTypeLabel(
                                                lowongan.tipe_pekerjaan,
                                            )
                                        }}
                                    </span>

                                    <span
                                        v-if="lowongan.unggulan"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300"
                                    >
                                        <Sparkles class="size-3" />
                                        Unggulan
                                    </span>
                                </div>
                            </div>

                            <!-- Title -->

                            <h3
                                class="line-clamp-2 text-lg font-bold leading-7 tracking-tight text-slate-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                            >
                                {{ lowongan.judul }}
                            </h3>

                            <!-- Meta -->

                            <div
                                class="mt-4 space-y-2.5 border-b border-slate-100 pb-5 dark:border-slate-800"
                            >
                                <div
                                    v-if="lowongan.departemen"
                                    class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <Building2
                                        class="size-4 shrink-0 text-slate-400"
                                    />

                                    <span class="truncate">
                                        {{ lowongan.departemen }}
                                    </span>
                                </div>

                                <div
                                    v-if="lowongan.lokasi"
                                    class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <MapPin
                                        class="size-4 shrink-0 text-slate-400"
                                    />

                                    <span class="truncate">
                                        {{ lowongan.lokasi }}
                                    </span>
                                </div>

                                <div
                                    v-if="lowongan.tanggal_tutup"
                                    class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <Clock3
                                        class="size-4 shrink-0 text-slate-400"
                                    />

                                    <span>
                                        Ditutup
                                        {{ formatDate(lowongan.tanggal_tutup) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Description -->

                            <p
                                class="mt-5 line-clamp-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{ truncate(lowongan.deskripsi) }}
                            </p>

                            <!-- Footer -->

                            <div
                                class="mt-auto flex items-center justify-between gap-3 pt-6"
                            >
                                <div
                                    v-if="lowongan.tanggal_mulai"
                                    class="flex min-w-0 items-center gap-2 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    <CalendarDays class="size-3.5 shrink-0" />

                                    <span class="truncate">
                                        Mulai
                                        {{ formatDate(lowongan.tanggal_mulai) }}
                                    </span>
                                </div>

                                <span v-else />

                                <Link
                                    :href="`/karier/${lowongan.slug}`"
                                    class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2.5 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-600 hover:shadow-md hover:shadow-blue-600/20 dark:bg-white dark:text-slate-900 dark:hover:bg-blue-500 dark:hover:text-white"
                                >
                                    Lihat Detail
                                    <ArrowRight
                                        class="size-3.5 transition-transform duration-200 group-hover:translate-x-0.5"
                                    />
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Empty State -->

                <div
                    v-else
                    class="rounded-3xl border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900/70"
                >
                    <div
                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                    >
                        <Search class="size-6" />
                    </div>

                    <h3
                        class="mt-4 text-base font-semibold text-slate-800 dark:text-slate-200"
                    >
                        {{
                            hasFilters
                                ? "Lowongan tidak ditemukan"
                                : "Belum ada lowongan tersedia"
                        }}
                    </h3>

                    <p
                        class="mx-auto mt-1.5 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{
                            hasFilters
                                ? "Coba ubah kata kunci atau filter yang digunakan untuk menemukan posisi lainnya."
                                : "Saat ini belum terdapat posisi yang sedang dibuka. Silakan kembali lagi untuk melihat peluang karier terbaru."
                        }}
                    </p>

                    <button
                        v-if="hasFilters"
                        type="button"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition-all hover:bg-blue-700 hover:shadow-md"
                        @click="resetFilters"
                    >
                        <X class="size-4" />
                        Reset Filter
                    </button>
                </div>
            </section>

            <!-- =====================================================
                 PAGINATION
            ====================================================== -->

            <nav
                v-if="props.lowongans.last_page > 1 && filteredLowongans.length"
                class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                aria-label="Pagination"
            >
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Halaman
                    <span
                        class="font-semibold text-slate-700 dark:text-slate-200"
                    >
                        {{ props.lowongans.current_page }}
                    </span>
                    dari {{ props.lowongans.last_page }}
                </p>

                <div class="flex items-center gap-1.5">
                    <!-- Previous -->

                    <Link
                        v-if="previousLink?.url"
                        :href="previousLink.url"
                        preserve-scroll
                        preserve-state
                        class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-400"
                        aria-label="Halaman sebelumnya"
                    >
                        <ChevronLeft class="size-4" />
                    </Link>

                    <span
                        v-else
                        class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-300 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-600"
                    >
                        <ChevronLeft class="size-4" />
                    </span>

                    <!-- Number -->

                    <template
                        v-for="link in visiblePaginationLinks"
                        :key="link.label"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            preserve-state
                            class="inline-flex size-10 items-center justify-center rounded-xl border text-sm font-medium transition-all"
                            :class="
                                link.active
                                    ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-600/20'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-400'
                            "
                            v-html="link.label"
                        />
                    </template>

                    <!-- Next -->

                    <Link
                        v-if="nextLink?.url"
                        :href="nextLink.url"
                        preserve-scroll
                        preserve-state
                        class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-400"
                        aria-label="Halaman berikutnya"
                    >
                        <ChevronRight class="size-4" />
                    </Link>

                    <span
                        v-else
                        class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-300 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-600"
                    >
                        <ChevronRight class="size-4" />
                    </span>
                </div>
            </nav>

            <!-- =====================================================
                 BOTTOM CTA
            ====================================================== -->

            <section
                class="mt-10 overflow-hidden rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 shadow-lg shadow-blue-600/10 dark:border-blue-900/50"
            >
                <div
                    class="relative px-6 py-8 sm:px-8 sm:py-10 lg:flex lg:items-center lg:justify-between lg:px-10"
                >
                    <div
                        class="pointer-events-none absolute -right-20 -top-24 size-64 rounded-full bg-white/10 blur-3xl"
                        aria-hidden="true"
                    />

                    <div class="relative max-w-2xl">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-100"
                        >
                            Grow With Us
                        </p>

                        <h2
                            class="mt-2 text-xl font-bold tracking-tight text-white sm:text-2xl"
                        >
                            Tidak menemukan posisi yang sesuai?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-blue-100 sm:text-base"
                        >
                            Pantau halaman karier KITB secara berkala untuk
                            mendapatkan informasi mengenai peluang pekerjaan
                            terbaru.
                        </p>
                    </div>

                    <div class="relative mt-6 flex shrink-0 lg:ml-8 lg:mt-0">
                        <a
                            href="#"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-blue-700 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-blue-50 hover:shadow-md"
                        >
                            Ikuti Informasi KITB
                            <ArrowRight class="size-4" />
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<style scoped>
.blob-shape {
    animation: blob-float 12s ease-in-out infinite;
    transform-origin: center;
    will-change: transform;
}

.blob-shape-delayed {
    animation: blob-float-delayed 15s ease-in-out infinite;
    transform-origin: center;
    will-change: transform;
}

.blob-shape-slow {
    animation: blob-float-slow 18s ease-in-out infinite;
    transform-origin: center;
    will-change: transform;
}

@keyframes blob-float {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    33% {
        transform: translate3d(25px, 15px, 0) scale(1.05);
    }

    66% {
        transform: translate3d(-15px, 30px, 0) scale(0.96);
    }
}

@keyframes blob-float-delayed {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    40% {
        transform: translate3d(-30px, 20px, 0) scale(1.08);
    }

    75% {
        transform: translate3d(15px, -15px, 0) scale(0.95);
    }
}

@keyframes blob-float-slow {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(0, 35px, 0) scale(1.1);
    }
}

@media (prefers-reduced-motion: reduce) {
    .blob-shape,
    .blob-shape-delayed,
    .blob-shape-slow {
        animation: none;
    }
}

@media (max-width: 640px) {
    .blob-shape {
        left: -10rem;
        top: -8rem;
        width: 20rem;
        height: 20rem;
    }

    .blob-shape-delayed {
        right: -8rem;
        width: 17rem;
        height: 17rem;
    }

    .blob-shape-slow {
        left: 35%;
        width: 15rem;
        height: 15rem;
    }
}
</style>
