<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowRight,
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    Clock3,
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
    tipe: string | null;
    lokasi: string | null;
    tanggal_buka: string | null;
    tanggal_tutup: string | null;
    is_active: boolean;
}

interface LinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator {
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: LinkItem[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface Props {
    lowongans: Paginator & {
        data: Lowongan[];
    };
}

const props = defineProps<Props>();

defineOptions({
    layout: PublicLayout,
});

const searchQuery = ref("");
const selectedDepartment = ref("");
const selectedType = ref("");

const departments = computed(() => {
    return [
        ...new Set(
            props.lowongans.data
                .map((item) => item.departemen)
                .filter((item): item is string => Boolean(item)),
        ),
    ].sort();
});

const types = computed(() => {
    return [
        ...new Set(
            props.lowongans.data
                .map((item) => item.tipe)
                .filter((item): item is string => Boolean(item)),
        ),
    ].sort();
});

const filteredLowongans = computed(() => {
    const keyword = searchQuery.value.trim().toLowerCase();

    return props.lowongans.data.filter((item) => {
        const matchesSearch =
            !keyword ||
            item.judul.toLowerCase().includes(keyword) ||
            item.departemen?.toLowerCase().includes(keyword) ||
            item.lokasi?.toLowerCase().includes(keyword);

        const matchesDepartment =
            !selectedDepartment.value ||
            item.departemen === selectedDepartment.value;

        const matchesType =
            !selectedType.value || item.tipe === selectedType.value;

        return matchesSearch && matchesDepartment && matchesType;
    });
});

const hasFilter = computed(() => {
    return Boolean(
        searchQuery.value || selectedDepartment.value || selectedType.value,
    );
});

const clearFilters = () => {
    searchQuery.value = "";
    selectedDepartment.value = "";
    selectedType.value = "";
};

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
    });
};

const getTypeLabel = (type: string | null) => {
    if (!type) return "Full Time";

    const normalized = type.toLowerCase();

    if (normalized.includes("intern")) {
        return "Internship";
    }

    if (normalized.includes("kontrak")) {
        return "Kontrak";
    }

    if (normalized.includes("part")) {
        return "Part Time";
    }

    return type;
};

const getTypeIcon = (type: string | null) => {
    if (type?.toLowerCase().includes("intern")) {
        return Users;
    }

    return BriefcaseBusiness;
};

const getDescription = (description: string | null) => {
    if (!description) {
        return "Temukan kesempatan untuk berkembang dan berkontribusi bersama KITB.";
    }

    const plainText = description
        .replace(/<[^>]*>/g, " ")
        .replace(/\s+/g, " ")
        .trim();

    if (!plainText) {
        return "Temukan kesempatan untuk berkembang dan berkontribusi bersama KITB.";
    }

    return plainText.length > 150
        ? `${plainText.substring(0, 150)}...`
        : plainText;
};

const isDeadlineNear = (date: string | null) => {
    if (!date) return false;

    const deadline = new Date(date);
    const now = new Date();

    const difference =
        (deadline.getTime() - now.getTime()) / (1000 * 60 * 60 * 24);

    return difference >= 0 && difference <= 7;
};

const previousLink = computed(() => {
    return props.lowongans.links.find((link) =>
        link.label.includes("Previous"),
    );
});

const nextLink = computed(() => {
    return props.lowongans.links.find((link) => link.label.includes("Next"));
});

const pageLinks = computed(() => {
    return props.lowongans.links.filter((link) => {
        return !link.label.includes("Previous") && !link.label.includes("Next");
    });
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
    </Head>

    <div
        class="relative min-h-screen overflow-hidden bg-slate-50/70 dark:bg-slate-950"
    >
        <!-- =========================================================
             BACKGROUND AMBIENT
        ========================================================== -->
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-[620px] overflow-hidden"
            aria-hidden="true"
        >
            <!-- Soft top gradient -->
            <div
                class="absolute inset-0 bg-gradient-to-b from-blue-50/90 via-slate-50/70 to-slate-50/0 dark:from-blue-950/20 dark:via-slate-950/50 dark:to-transparent"
            />

            <!-- Subtle radial glow -->
            <div
                class="absolute left-1/2 top-0 h-[420px] w-[760px] -translate-x-1/2 rounded-full bg-blue-400/[0.08] blur-3xl dark:bg-blue-500/[0.07]"
            />

            <div
                class="absolute left-[8%] top-[150px] h-64 w-64 rounded-full bg-sky-300/[0.07] blur-3xl dark:bg-sky-500/[0.04]"
            />

            <div
                class="absolute right-[5%] top-[180px] h-72 w-72 rounded-full bg-indigo-300/[0.06] blur-3xl dark:bg-indigo-500/[0.04]"
            />

            <!-- Technical grid -->
            <div
                class="absolute inset-0 opacity-[0.28] dark:opacity-[0.12]"
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
                        black 55%,
                        transparent 100%
                    );
                    -webkit-mask-image: linear-gradient(
                        to bottom,
                        black 0%,
                        black 55%,
                        transparent 100%
                    );
                "
            />

            <!-- Bottom fade -->
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
                <div
                    class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-blue-700 shadow-sm shadow-blue-900/5 backdrop-blur-sm dark:border-blue-900/60 dark:bg-slate-900/70 dark:text-blue-300"
                >
                    <BriefcaseBusiness class="size-3.5" />
                    <span>Peluang Karier di KITB</span>
                </div>

                <h1
                    class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white"
                >
                    Bangun Masa Depan
                    <span
                        class="bg-gradient-to-r from-blue-600 via-indigo-600 to-sky-500 bg-clip-text text-transparent"
                    >
                        Bersama KITB
                    </span>
                </h1>

                <p
                    class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8 dark:text-slate-400"
                >
                    Temukan peluang untuk berkembang, berkolaborasi, dan
                    memberikan kontribusi nyata dalam membangun kawasan industri
                    yang berkelanjutan.
                </p>

                <!-- Small stats -->
                <div
                    class="mx-auto mt-8 flex w-fit flex-wrap items-center justify-center gap-2 rounded-2xl border border-slate-200/80 bg-white/75 p-1.5 shadow-sm backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/70"
                >
                    <div
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300"
                    >
                        <div
                            class="flex size-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                        >
                            <BriefcaseBusiness class="size-3.5" />
                        </div>

                        <span>
                            {{ props.lowongans.total }} Posisi Tersedia
                        </span>
                    </div>

                    <div
                        class="hidden h-5 w-px bg-slate-200 sm:block dark:bg-slate-700"
                    />

                    <div
                        class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300"
                    >
                        <div
                            class="flex size-7 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400"
                        >
                            <Building2 class="size-3.5" />
                        </div>

                        <span> Lingkungan Profesional </span>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 SEARCH & FILTER
            ====================================================== -->
            <section class="mx-auto mt-10 max-w-6xl">
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
                            />

                            <input
                                v-model="searchQuery"
                                type="search"
                                placeholder="Cari posisi, departemen, atau lokasi..."
                                class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50/80 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950/60 dark:text-white dark:focus:border-blue-500 dark:focus:bg-slate-950"
                            />
                        </div>

                        <!-- Department -->
                        <div class="relative">
                            <SlidersHorizontal
                                class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <select
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
                            />

                            <select
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

                        <!-- Clear -->
                        <button
                            v-if="hasFilter"
                            type="button"
                            class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-red-900/60 dark:hover:bg-red-950/30 dark:hover:text-red-400"
                            @click="clearFilters"
                        >
                            <X class="size-4" />
                            <span class="hidden sm:inline"> Reset </span>
                        </button>
                    </div>

                    <div
                        v-if="hasFilter"
                        class="mt-3 flex items-center gap-2 px-2 text-xs text-slate-500 dark:text-slate-400"
                    >
                        <span>
                            Menampilkan
                            <strong
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ filteredLowongans.length }}
                            </strong>
                            dari
                            <strong
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ props.lowongans.data.length }}
                            </strong>
                            posisi pada halaman ini.
                        </span>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 JOB LIST
            ====================================================== -->
            <section class="mx-auto mt-8 max-w-6xl">
                <div
                    v-if="filteredLowongans.length"
                    class="grid gap-5 md:grid-cols-2"
                >
                    <article
                        v-for="lowongan in filteredLowongans"
                        :key="lowongan.id"
                        class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-900/[0.07] dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-900/70"
                    >
                        <!-- Top accent -->
                        <div
                            class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-sky-400 opacity-80"
                        />

                        <div class="p-5 sm:p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between gap-4">
                                <div
                                    class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition duration-300 group-hover:scale-105 group-hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-400 dark:group-hover:bg-blue-950"
                                >
                                    <component
                                        :is="getTypeIcon(lowongan.tipe)"
                                        class="size-5"
                                    />
                                </div>

                                <div class="flex flex-wrap justify-end gap-2">
                                    <span
                                        v-if="lowongan.tipe"
                                        class="rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300"
                                    >
                                        {{ getTypeLabel(lowongan.tipe) }}
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
                                    class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <Building2
                                        class="size-4 shrink-0 text-slate-400"
                                    />

                                    <span>
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

                                    <span>
                                        {{ lowongan.lokasi }}
                                    </span>
                                </div>

                                <div
                                    v-if="lowongan.tanggal_tutup"
                                    class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <CalendarDays
                                        class="size-4 shrink-0 text-slate-400"
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
                >
                    <div
                        class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                    >
                        <Search class="size-7" />
                    </div>

                    <h3
                        class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                    >
                        Posisi tidak ditemukan
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Belum ada posisi yang sesuai dengan pencarian atau
                        filter yang kamu pilih.
                    </p>

                    <button
                        v-if="hasFilter"
                        type="button"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-600 dark:bg-white dark:text-slate-900"
                        @click="clearFilters"
                    >
                        <X class="size-4" />
                        Reset Filter
                    </button>
                </div>
            </section>

            <!-- =====================================================
                 PAGINATION
            ====================================================== -->
            <section
                v-if="props.lowongans.last_page > 1"
                class="mx-auto mt-8 max-w-6xl"
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
                            {{ props.lowongans.from ?? 0 }}
                        </span>
                        -
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ props.lowongans.to ?? 0 }}
                        </span>
                        dari
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ props.lowongans.total }}
                        </span>
                        posisi
                    </div>

                    <div class="flex items-center justify-center gap-1.5">
                        <Link
                            v-if="previousLink?.url"
                            :href="previousLink.url"
                            preserve-scroll
                            class="flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-400"
                        >
                            <ChevronLeft class="size-4" />
                        </Link>

                        <span
                            v-else
                            class="flex size-9 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-300 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-700"
                        >
                            <ChevronLeft class="size-4" />
                        </span>

                        <template
                            v-for="(link, index) in pageLinks"
                            :key="`${link.label}-${index}`"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                class="flex size-9 items-center justify-center rounded-xl border text-xs font-semibold transition"
                                :class="
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-600/20'
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-400'
                                "
                                v-html="link.label"
                            />
                        </template>

                        <Link
                            v-if="nextLink?.url"
                            :href="nextLink.url"
                            preserve-scroll
                            class="flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-400"
                        >
                            <ChevronRight class="size-4" />
                        </Link>

                        <span
                            v-else
                            class="flex size-9 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-300 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-700"
                        >
                            <ChevronRight class="size-4" />
                        </span>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 BOTTOM CTA
            ====================================================== -->
            <section class="mx-auto mt-12 max-w-6xl">
                <div
                    class="relative overflow-hidden rounded-[2rem] border border-blue-200/70 bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 px-6 py-10 text-center shadow-xl shadow-blue-900/10 sm:px-10 lg:py-12 dark:border-blue-800/50"
                >
                    <!-- Subtle decorative circles -->
                    <div
                        class="pointer-events-none absolute -right-20 -top-20 size-64 rounded-full border border-white/10"
                    />

                    <div
                        class="pointer-events-none absolute -bottom-32 -left-20 size-72 rounded-full border border-white/10"
                    />

                    <div class="relative">
                        <div
                            class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20"
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
                            href="#"
                            class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-blue-700 shadow-lg shadow-blue-950/10 transition hover:-translate-y-0.5 hover:bg-blue-50"
                        >
                            Ikuti Informasi KITB

                            <ArrowRight class="size-4" />
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
