<script setup lang="ts">
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowLeft,
    ArrowRight,
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    CheckCircle2,
    Clock3,
    MapPin,
    Sparkles,
    Users,
} from "lucide-vue-next";
import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

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

interface Props {
    lowongan: Lowongan;
}

const props = defineProps<Props>();

/* ==========================================================================
   Helpers
========================================================================== */

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

const getTypeLabel = (value: string | null) => {
    if (!value) return "Tipe pekerjaan";

    const normalized = normalizeType(value);

    return typeLabels[normalized] ?? value;
};

/* ==========================================================================
   Recruitment Status
========================================================================== */

const isClosed = computed(() => {
    if (props.lowongan.status !== "published") {
        return true;
    }

    if (!props.lowongan.tanggal_tutup) {
        return false;
    }

    const now = new Date();

    const todayKey = new Intl.DateTimeFormat("en-CA", {
        timeZone: "Asia/Jakarta",
    }).format(now);

    const closingDate = new Date(props.lowongan.tanggal_tutup);

    if (Number.isNaN(closingDate.getTime())) {
        return false;
    }

    const closingKey = new Intl.DateTimeFormat("en-CA", {
        timeZone: "Asia/Jakarta",
    }).format(closingDate);

    return closingKey < todayKey;
});

const recruitmentStatus = computed(() => {
    if (props.lowongan.status !== "published") {
        return {
            label: "Tidak tersedia",
            description: "Lowongan ini belum tersedia untuk pelamar.",
        };
    }

    if (isClosed.value) {
        return {
            label: "Lamaran ditutup",
            description: "Periode pendaftaran untuk posisi ini telah berakhir.",
        };
    }

    return {
        label: "Lamaran dibuka",
        description: "Posisi ini sedang menerima lamaran.",
    };
});

/* ==========================================================================
   Content State
========================================================================== */

const hasDescription = computed(() =>
    Boolean(props.lowongan.deskripsi?.trim()),
);

const hasResponsibilities = computed(() =>
    Boolean(props.lowongan.tanggung_jawab?.trim()),
);

const hasQualifications = computed(() =>
    Boolean(props.lowongan.kualifikasi?.trim()),
);

const hasBenefits = computed(() => Boolean(props.lowongan.benefit?.trim()));

const hasContent = computed(
    () =>
        hasDescription.value ||
        hasResponsibilities.value ||
        hasQualifications.value ||
        hasBenefits.value,
);

const pageTitle = computed(() => `${props.lowongan.judul} - Karier KITB`);
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>

        <meta
            name="description"
            :content="`Informasi lowongan ${lowongan.judul} di PT Kawasan Industri Tanjung Buton (KITB).`"
        />

        <meta name="robots" content="index, follow" />

        <meta property="og:title" :content="pageTitle" />

        <meta
            property="og:description"
            :content="`Lihat detail lowongan ${lowongan.judul} dan peluang berkarier bersama KITB.`"
        />

        <meta property="og:type" content="website" />

        <meta
            property="og:url"
            :content="`https://tanjungbuton-industrial.co.id/karier/${lowongan.slug}`"
        />
    </Head>

    <div
        class="relative min-h-screen overflow-x-clip bg-slate-50/70 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
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
        <main
            class="relative z-10 mx-auto w-full max-w-[1440px] px-4 pb-12 pt-24 sm:px-6 sm:pt-28 lg:px-8 lg:pb-16 lg:pt-32"
        >
            <!-- Back -->
            <div class="reveal mb-6" style="--d: 0">
                <Link
                    href="/karier"
                    class="group inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-white/80 px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm backdrop-blur-sm transition-all duration-300 hover:-translate-x-0.5 hover:border-blue-300 hover:bg-white hover:shadow-md dark:border-blue-900/60 dark:bg-slate-900/70 dark:text-blue-300 dark:hover:border-blue-800 dark:hover:bg-slate-900"
                >
                    <ArrowLeft
                        class="size-4 transition-transform duration-300 group-hover:-translate-x-0.5"
                    />

                    Kembali ke Karier
                </Link>
            </div>

            <!-- =====================================================
                 HERO
            ====================================================== -->
            <section
                class="reveal relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-xl shadow-slate-900/[0.05] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                style="--d: 80"
            >
                <!-- Accent -->
                <div
                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-sky-400"
                />

                <!-- Internal glow -->
                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute -right-28 -top-28 size-72 rounded-full bg-blue-400/10 blur-3xl dark:bg-blue-500/10"
                />

                <div class="relative p-6 sm:p-8 lg:p-10">
                    <!-- Badges -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3.5 py-1.5 text-xs font-semibold text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                        >
                            <BriefcaseBusiness class="size-3.5" />

                            {{ getTypeLabel(lowongan.tipe_pekerjaan) }}
                        </span>

                        <span
                            v-if="lowongan.unggulan"
                            class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3.5 py-1.5 text-xs font-semibold text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300"
                        >
                            <Sparkles class="size-3.5" />

                            Posisi Unggulan
                        </span>

                        <span
                            class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-xs font-semibold"
                            :class="
                                !isClosed
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300'
                                    : 'border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-300'
                            "
                        >
                            <span
                                class="size-1.5 rounded-full"
                                :class="
                                    !isClosed
                                        ? 'animate-pulse bg-emerald-500'
                                        : 'bg-slate-400'
                                "
                            />

                            {{ recruitmentStatus.label }}
                        </span>
                    </div>

                    <!-- Heading -->
                    <div class="mt-6 max-w-4xl">
                        <h1
                            class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl dark:text-white"
                        >
                            {{ lowongan.judul }}
                        </h1>

                        <p
                            class="mt-5 text-sm leading-7 text-slate-600 sm:text-base sm:leading-8 dark:text-slate-400"
                        >
                            Bergabung bersama
                            <strong
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                PT Kawasan Industri Tanjung Buton (KITB)
                            </strong>
                            untuk berkembang, berkolaborasi, dan memberikan
                            kontribusi nyata dalam pengembangan kawasan industri
                            yang berkelanjutan.
                        </p>
                    </div>

                    <!-- Metadata -->
                    <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Department -->
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-slate-800 dark:bg-slate-950/50 dark:hover:border-blue-900/60 dark:hover:bg-blue-950/20"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    <Building2 class="size-5" />
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                                    >
                                        Departemen
                                    </p>

                                    <p
                                        class="mt-1 truncate text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ lowongan.departemen || "-" }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-slate-800 dark:bg-slate-950/50 dark:hover:border-blue-900/60 dark:hover:bg-blue-950/20"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    <MapPin class="size-5" />
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                                    >
                                        Lokasi
                                    </p>

                                    <p
                                        class="mt-1 truncate text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ lowongan.lokasi || "-" }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Start -->
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-slate-800 dark:bg-slate-950/50 dark:hover:border-blue-900/60 dark:hover:bg-blue-950/20"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    <CalendarDays class="size-5" />
                                </div>

                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                                    >
                                        Mulai
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ formatDate(lowongan.tanggal_mulai) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Deadline -->
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-slate-800 dark:bg-slate-950/50 dark:hover:border-blue-900/60 dark:hover:bg-blue-950/20"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    <Clock3 class="size-5" />
                                </div>

                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                                    >
                                        Batas Lamaran
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ formatDate(lowongan.tanggal_tutup) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 CONTENT
            ====================================================== -->
            <div
                class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_340px]"
            >
                <!-- Main -->
                <div class="space-y-6">
                    <!-- Description -->
                    <section
                        v-if="hasDescription"
                        class="reveal rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-6 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl sm:p-8 dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 160"
                    >
                        <div class="mb-6 flex items-center gap-4">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                            >
                                <BriefcaseBusiness class="size-6" />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400"
                                >
                                    Tentang Posisi
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    Deskripsi Pekerjaan
                                </h2>
                            </div>
                        </div>

                        <div
                            class="prose prose-slate max-w-none text-sm leading-8 prose-headings:text-slate-900 prose-p:text-slate-600 prose-strong:text-slate-900 prose-li:text-slate-600 dark:prose-invert dark:prose-p:text-slate-300 dark:prose-strong:text-white dark:prose-li:text-slate-300"
                            v-html="lowongan.deskripsi"
                        />
                    </section>

                    <!-- Responsibilities -->
                    <section
                        v-if="hasResponsibilities"
                        class="reveal rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-6 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl sm:p-8 dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 220"
                    >
                        <div class="mb-6 flex items-center gap-4">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                            >
                                <CheckCircle2 class="size-6" />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400"
                                >
                                    Peran & Tanggung Jawab
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    Tanggung Jawab
                                </h2>
                            </div>
                        </div>

                        <div
                            class="prose prose-slate max-w-none text-sm leading-8 prose-headings:text-slate-900 prose-p:text-slate-600 prose-strong:text-slate-900 prose-li:text-slate-600 dark:prose-invert dark:prose-p:text-slate-300 dark:prose-strong:text-white dark:prose-li:text-slate-300"
                            v-html="lowongan.tanggung_jawab"
                        />
                    </section>

                    <!-- Qualifications -->
                    <section
                        v-if="hasQualifications"
                        class="reveal rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-6 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl sm:p-8 dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 280"
                    >
                        <div class="mb-6 flex items-center gap-4">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400"
                            >
                                <Users class="size-6" />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-600 dark:text-sky-400"
                                >
                                    Persyaratan
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    Kualifikasi
                                </h2>
                            </div>
                        </div>

                        <div
                            class="prose prose-slate max-w-none text-sm leading-8 prose-headings:text-slate-900 prose-p:text-slate-600 prose-strong:text-slate-900 prose-li:text-slate-600 dark:prose-invert dark:prose-p:text-slate-300 dark:prose-strong:text-white dark:prose-li:text-slate-300"
                            v-html="lowongan.kualifikasi"
                        />
                    </section>

                    <!-- Benefits -->
                    <section
                        v-if="hasBenefits"
                        class="reveal rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-6 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl sm:p-8 dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 340"
                    >
                        <div class="mb-6 flex items-center gap-4">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                            >
                                <Sparkles class="size-6" />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400"
                                >
                                    Apa yang Anda Dapatkan
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    Benefit
                                </h2>
                            </div>
                        </div>

                        <div
                            class="prose prose-slate max-w-none text-sm leading-8 prose-headings:text-slate-900 prose-p:text-slate-600 prose-strong:text-slate-900 prose-li:text-slate-600 dark:prose-invert dark:prose-p:text-slate-300 dark:prose-strong:text-white dark:prose-li:text-slate-300"
                            v-html="lowongan.benefit"
                        />
                    </section>

                    <!-- Empty -->
                    <section
                        v-if="!hasContent"
                        class="reveal rounded-[1.75rem] border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900/70"
                        style="--d: 160"
                    >
                        <div
                            class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                        >
                            <BriefcaseBusiness class="size-7" />
                        </div>

                        <h2
                            class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Informasi belum tersedia
                        </h2>

                        <p
                            class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Detail untuk posisi ini belum dilengkapi. Silakan
                            kembali ke halaman karier untuk melihat posisi
                            lainnya.
                        </p>
                    </section>
                </div>

                <!-- =================================================
                     SIDEBAR
                ================================================== -->
                <aside class="lg:sticky lg:top-28">
                    <div
                        class="reveal overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/90 shadow-xl shadow-slate-900/[0.05] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 220"
                    >
                        <!-- Sidebar Header -->
                        <div
                            class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 p-6 text-white"
                        >
                            <div
                                aria-hidden="true"
                                class="absolute -right-16 -top-20 size-52 rounded-full bg-white/10 blur-3xl"
                            />

                            <div
                                aria-hidden="true"
                                class="absolute -bottom-20 -left-16 size-52 rounded-full bg-sky-300/10 blur-3xl"
                            />

                            <div class="relative">
                                <div
                                    class="mb-4 flex size-11 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20"
                                >
                                    <BriefcaseBusiness class="size-5" />
                                </div>

                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-100"
                                >
                                    Job Overview
                                </p>

                                <h2 class="mt-2 text-xl font-bold">
                                    Ringkasan Lowongan
                                </h2>

                                <p class="mt-2 text-sm leading-6 text-blue-100">
                                    Informasi penting mengenai posisi yang
                                    sedang Anda lihat.
                                </p>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6">
                            <!-- Summary -->
                            <div class="space-y-4">
                                <div
                                    class="flex items-start gap-3 border-b border-slate-100 pb-4 dark:border-slate-800"
                                >
                                    <BriefcaseBusiness
                                        class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    />

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            Tipe Pekerjaan
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{
                                                getTypeLabel(
                                                    lowongan.tipe_pekerjaan,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="flex items-start gap-3 border-b border-slate-100 pb-4 dark:border-slate-800"
                                >
                                    <Building2
                                        class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    />

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            Departemen
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ lowongan.departemen || "-" }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="flex items-start gap-3 border-b border-slate-100 pb-4 dark:border-slate-800"
                                >
                                    <MapPin
                                        class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    />

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            Lokasi
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ lowongan.lokasi || "-" }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="flex items-start gap-3 border-b border-slate-100 pb-4 dark:border-slate-800"
                                >
                                    <CalendarDays
                                        class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    />

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            Mulai Pendaftaran
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{
                                                formatDate(
                                                    lowongan.tanggal_mulai,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <Clock3
                                        class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    />

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            Batas Pendaftaran
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{
                                                formatDate(
                                                    lowongan.tanggal_tutup,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Recruitment status -->
                            <div
                                class="mt-6 rounded-2xl border p-4"
                                :class="
                                    !isClosed
                                        ? 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-900/60 dark:bg-emerald-950/25'
                                        : 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/50'
                                "
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex size-8 shrink-0 items-center justify-center rounded-xl"
                                        :class="
                                            !isClosed
                                                ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400'
                                                : 'bg-slate-200 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                        "
                                    >
                                        <CheckCircle2
                                            v-if="!isClosed"
                                            class="size-4"
                                        />

                                        <Clock3 v-else class="size-4" />
                                    </div>

                                    <div>
                                        <p
                                            class="text-sm font-semibold"
                                            :class="
                                                !isClosed
                                                    ? 'text-emerald-800 dark:text-emerald-300'
                                                    : 'text-slate-700 dark:text-slate-300'
                                            "
                                        >
                                            {{ recruitmentStatus.label }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            {{ recruitmentStatus.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Apply -->
                            <div class="mt-5">
                                <Link
                                    v-if="!isClosed"
                                    :href="`/karier/${lowongan.slug}/lamar`"
                                    class="group flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition duration-300 hover:-translate-y-0.5 hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl hover:shadow-blue-600/25 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                                >
                                    Lamar Sekarang

                                    <ArrowRight
                                        class="size-4 transition-transform duration-300 group-hover:translate-x-1"
                                    />
                                </Link>

                                <div
                                    v-else
                                    class="flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-5 py-3.5 text-sm font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    <Clock3 class="size-4" />

                                    Lamaran Ditutup
                                </div>
                            </div>

                            <!-- Note -->
                            <div
                                class="mt-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-950/60"
                            >
                                <p
                                    class="text-xs leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    Pastikan CV dan dokumen pendukung yang
                                    diperlukan telah disiapkan sebelum
                                    mengajukan lamaran.
                                </p>
                            </div>

                            <!-- Other positions -->
                            <Link
                                href="/karier"
                                class="group mt-5 flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3.5 transition duration-300 hover:border-blue-200 hover:bg-blue-50/50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-900/60 dark:hover:bg-blue-950/20"
                            >
                                <div>
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        Eksplorasi
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        Lihat posisi lainnya
                                    </p>
                                </div>

                                <ArrowRight
                                    class="size-4 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-blue-600 dark:group-hover:text-blue-400"
                                />
                            </Link>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- =====================================================
                 BOTTOM CTA
            ====================================================== -->
            <section
                class="reveal relative mt-10 overflow-hidden rounded-[2rem] border border-blue-200/70 bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 px-6 py-10 text-center shadow-xl shadow-blue-900/10 sm:px-10 lg:py-12 dark:border-blue-800/50"
                style="--d: 420"
            >
                <!-- CTA ambient -->
                <div
                    class="pointer-events-none absolute -right-20 -top-20 size-64 rounded-full border border-white/10"
                />

                <div
                    class="pointer-events-none absolute -bottom-32 -left-20 size-72 rounded-full border border-white/10"
                />

                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_75%_20%,rgba(255,255,255,0.10),transparent_32%)]"
                />

                <div class="relative">
                    <div
                        class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20"
                    >
                        <Sparkles class="size-5" />
                    </div>

                    <p
                        class="mt-5 text-xs font-semibold uppercase tracking-[0.2em] text-blue-100"
                    >
                        Grow With Us
                    </p>

                    <h2
                        class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl"
                    >
                        Siap berkembang bersama KITB?
                    </h2>

                    <p
                        class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-blue-100 sm:text-base"
                    >
                        Temukan peluang karier lainnya dan jadilah bagian dari
                        perjalanan pengembangan kawasan industri Tanjung Buton.
                    </p>

                    <Link
                        href="/karier"
                        class="group mt-6 inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-blue-700 shadow-lg shadow-blue-950/10 transition duration-300 hover:-translate-y-0.5 hover:bg-blue-50"
                    >
                        Lihat Semua Lowongan

                        <ArrowRight
                            class="size-4 transition-transform duration-300 group-hover:translate-x-1"
                        />
                    </Link>
                </div>
            </section>
        </main>
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
   Rich Text
========================================================================== */

:deep(.prose ul) {
    padding-left: 1.4rem;
}

:deep(.prose ol) {
    padding-left: 1.4rem;
}

:deep(.prose li::marker) {
    color: rgb(59 130 246);
}

:deep(.prose a) {
    color: rgb(37 99 235);
    font-weight: 600;
    text-decoration: none;
}

:deep(.prose a:hover) {
    text-decoration: underline;
}

:deep(.prose img) {
    max-width: 100%;
    height: auto;
    border-radius: 1rem;
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
