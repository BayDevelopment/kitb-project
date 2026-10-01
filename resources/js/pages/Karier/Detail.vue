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

interface Props {
    lowongan: Lowongan;
}

const props = defineProps<Props>();

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
        month: "long",
        year: "numeric",
    });
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

/*
|--------------------------------------------------------------------------
| Recruitment Status
|--------------------------------------------------------------------------
*/

const isClosed = computed(() => {
    if (props.lowongan.status !== "published") {
        return true;
    }

    if (!props.lowongan.tanggal_tutup) {
        return false;
    }

    const today = new Date();

    today.setHours(23, 59, 59, 999);

    const closingDate = new Date(props.lowongan.tanggal_tutup);

    closingDate.setHours(23, 59, 59, 999);

    return closingDate < today;
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

/*
|--------------------------------------------------------------------------
| Content Helpers
|--------------------------------------------------------------------------
*/

const hasDescription = computed(() => Boolean(props.lowongan.deskripsi));

const hasResponsibilities = computed(() =>
    Boolean(props.lowongan.tanggung_jawab),
);

const hasQualifications = computed(() => Boolean(props.lowongan.kualifikasi));

const hasBenefits = computed(() => Boolean(props.lowongan.benefit));

const hasContent = computed(
    () =>
        hasDescription.value ||
        hasResponsibilities.value ||
        hasQualifications.value ||
        hasBenefits.value,
);

/*
|--------------------------------------------------------------------------
| Page Metadata
|--------------------------------------------------------------------------
*/

const pageTitle = computed(() => `${props.lowongan.judul} - Karier KITB`);
</script>

<template>
    <Head :title="pageTitle" />

    <div
        class="relative min-h-full overflow-hidden bg-slate-50/70 transition-colors duration-300 dark:bg-slate-950"
    >
        <!-- =========================================================
             DECORATIVE BACKGROUND
        ========================================================== -->
        <div
            class="pointer-events-none absolute inset-x-0 top-0 z-0 h-[620px] overflow-hidden"
            aria-hidden="true"
        >
            <!-- Soft gradient -->
            <div
                class="absolute inset-0 bg-gradient-to-b from-blue-50/90 via-slate-50/70 to-transparent dark:from-blue-950/20 dark:via-slate-950/60 dark:to-transparent"
            />

            <!-- Subtle radial glow -->
            <div
                class="absolute left-1/2 top-0 h-[420px] w-[760px] -translate-x-1/2 rounded-full bg-blue-400/[0.07] blur-3xl dark:bg-blue-500/[0.06]"
            />

            <div
                class="absolute left-[8%] top-[150px] h-64 w-64 rounded-full bg-sky-300/[0.05] blur-3xl dark:bg-sky-500/[0.035]"
            />

            <div
                class="absolute right-[5%] top-[180px] h-72 w-72 rounded-full bg-indigo-300/[0.05] blur-3xl dark:bg-indigo-500/[0.035]"
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
                class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-b from-transparent to-slate-50/95 dark:to-slate-950/95"
            />
        </div>

        <!-- =========================================================
             MAIN
        ========================================================== -->
        <div
            class="relative z-10 mx-auto w-full max-w-[1280px] px-4 pb-12 pt-24 sm:px-6 sm:pb-14 sm:pt-28 lg:px-8 lg:pb-16 lg:pt-32"
        >
            <!-- =====================================================
                 BACK
            ====================================================== -->
            <div class="mb-6">
                <Link
                    href="/karier"
                    preserve-scroll
                    class="group inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                >
                    <span
                        class="flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-white shadow-sm transition-all group-hover:border-blue-200 group-hover:bg-blue-50 dark:border-slate-800 dark:bg-slate-900 dark:group-hover:border-blue-900 dark:group-hover:bg-blue-950/30"
                    >
                        <ArrowLeft class="size-4" />
                    </span>

                    Kembali ke Karier
                </Link>
            </div>

            <!-- =====================================================
                 HERO / JOB HEADER
            ====================================================== -->
            <section
                class="relative mb-7 overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/50 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10"
            >
                <!-- Accent -->
                <div
                    class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-blue-600 via-indigo-500 to-sky-400"
                />

                <div
                    class="relative px-6 py-8 sm:px-8 sm:py-10 lg:px-12 lg:py-12"
                >
                    <div class="max-w-4xl">
                        <!-- Badges -->
                        <div class="mb-5 flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"
                            >
                                <BriefcaseBusiness class="size-3.5" />

                                {{ getTypeLabel(lowongan.tipe_pekerjaan) }}
                            </span>

                            <span
                                v-if="lowongan.unggulan"
                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300"
                            >
                                <Sparkles class="size-3.5" />

                                Posisi Unggulan
                            </span>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold"
                                :class="
                                    isClosed
                                        ? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                        : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                "
                            >
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="
                                        isClosed
                                            ? 'bg-slate-400'
                                            : 'bg-emerald-500'
                                    "
                                />

                                {{ recruitmentStatus.label }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h1
                            class="max-w-4xl text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl lg:text-5xl"
                        >
                            {{ lowongan.judul }}
                        </h1>

                        <!-- Description -->
                        <p
                            class="mt-5 max-w-3xl text-sm leading-7 text-slate-600 dark:text-slate-300 sm:text-base"
                        >
                            Bergabung bersama PT Kawasan Industri Tanjung Buton
                            dan menjadi bagian dari tim yang membangun ekosistem
                            kawasan industri yang berkembang.
                        </p>

                        <!-- Meta -->
                        <div
                            class="mt-7 flex flex-wrap gap-x-6 gap-y-4 border-t border-slate-100 pt-6 dark:border-slate-800"
                        >
                            <!-- Department -->
                            <div
                                v-if="lowongan.departemen"
                                class="flex items-center gap-2.5"
                            >
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    <Building2 class="size-4" />
                                </span>

                                <div>
                                    <p
                                        class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Departemen
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ lowongan.departemen }}
                                    </p>
                                </div>
                            </div>

                            <!-- Location -->
                            <div
                                v-if="lowongan.lokasi"
                                class="flex items-center gap-2.5"
                            >
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    <MapPin class="size-4" />
                                </span>

                                <div>
                                    <p
                                        class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Lokasi
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ lowongan.lokasi }}
                                    </p>
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div
                                v-if="lowongan.tanggal_mulai"
                                class="flex items-center gap-2.5"
                            >
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    <CalendarDays class="size-4" />
                                </span>

                                <div>
                                    <p
                                        class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Mulai Rekrutmen
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ formatDate(lowongan.tanggal_mulai) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Closing Date -->
                            <div
                                v-if="lowongan.tanggal_tutup"
                                class="flex items-center gap-2.5"
                            >
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl"
                                    :class="
                                        isClosed
                                            ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                            : 'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400'
                                    "
                                >
                                    <Clock3 class="size-4" />
                                </span>

                                <div>
                                    <p
                                        class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Batas Lamaran
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold"
                                        :class="
                                            isClosed
                                                ? 'text-slate-600 dark:text-slate-400'
                                                : 'text-blue-700 dark:text-blue-400'
                                        "
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
                 CONTENT GRID
            ====================================================== -->
            <div
                v-if="hasContent"
                class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_340px]"
            >
                <!-- =================================================
                     LEFT CONTENT
                ================================================== -->
                <div class="min-w-0 space-y-6">
                    <!-- Description -->
                    <section
                        v-if="hasDescription"
                        class="rounded-3xl border border-slate-200/80 bg-white/95 p-6 shadow-sm shadow-slate-200/40 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10 sm:p-8"
                    >
                        <div class="mb-6 flex items-start gap-3">
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <BriefcaseBusiness class="size-5" />
                            </span>

                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400"
                                >
                                    About The Role
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    Deskripsi Pekerjaan
                                </h2>
                            </div>
                        </div>

                        <div
                            class="prose prose-slate max-w-none text-sm leading-7 dark:prose-invert"
                            v-html="lowongan.deskripsi"
                        />
                    </section>

                    <!-- Responsibilities -->
                    <section
                        v-if="hasResponsibilities"
                        class="rounded-3xl border border-slate-200/80 bg-white/95 p-6 shadow-sm shadow-slate-200/40 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10 sm:p-8"
                    >
                        <div class="mb-6 flex items-start gap-3">
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
                            >
                                <CheckCircle2 class="size-5" />
                            </span>

                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.16em] text-indigo-600 dark:text-indigo-400"
                                >
                                    What You'll Do
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    Tanggung Jawab
                                </h2>
                            </div>
                        </div>

                        <div
                            class="prose prose-slate max-w-none text-sm leading-7 dark:prose-invert"
                            v-html="lowongan.tanggung_jawab"
                        />
                    </section>

                    <!-- Qualifications -->
                    <section
                        v-if="hasQualifications"
                        class="rounded-3xl border border-slate-200/80 bg-white/95 p-6 shadow-sm shadow-slate-200/40 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10 sm:p-8"
                    >
                        <div class="mb-6 flex items-start gap-3">
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400"
                            >
                                <Users class="size-5" />
                            </span>

                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sky-600 dark:text-sky-400"
                                >
                                    Who We're Looking For
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    Kualifikasi
                                </h2>
                            </div>
                        </div>

                        <div
                            class="prose prose-slate max-w-none text-sm leading-7 dark:prose-invert"
                            v-html="lowongan.kualifikasi"
                        />
                    </section>

                    <!-- Benefits -->
                    <section
                        v-if="hasBenefits"
                        class="rounded-3xl border border-slate-200/80 bg-white/95 p-6 shadow-sm shadow-slate-200/40 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10 sm:p-8"
                    >
                        <div class="mb-6 flex items-start gap-3">
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                            >
                                <Sparkles class="size-5" />
                            </span>

                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-600 dark:text-emerald-400"
                                >
                                    What We Offer
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    Benefit
                                </h2>
                            </div>
                        </div>

                        <div
                            class="prose prose-slate max-w-none text-sm leading-7 dark:prose-invert"
                            v-html="lowongan.benefit"
                        />
                    </section>
                </div>

                <!-- =================================================
                     RIGHT SIDEBAR
                ================================================== -->
                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div
                        class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/40 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10"
                    >
                        <!-- Sidebar Header -->
                        <div
                            class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 px-6 py-7"
                        >
                            <div
                                class="pointer-events-none absolute -right-16 -top-20 size-48 rounded-full bg-white/10 blur-3xl"
                                aria-hidden="true"
                            />

                            <div class="relative">
                                <div
                                    class="mb-3 flex size-10 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/20"
                                >
                                    <BriefcaseBusiness class="size-5" />
                                </div>

                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-100"
                                >
                                    Job Overview
                                </p>

                                <h2 class="mt-1 text-lg font-bold text-white">
                                    Ringkasan Lowongan
                                </h2>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="space-y-5 p-6">
                            <!-- Type -->
                            <div
                                v-if="lowongan.tipe_pekerjaan"
                                class="flex items-start gap-3"
                            >
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    <BriefcaseBusiness class="size-4" />
                                </span>

                                <div class="min-w-0">
                                    <p
                                        class="text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        Tipe Pekerjaan
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{
                                            getTypeLabel(
                                                lowongan.tipe_pekerjaan,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Department -->
                            <div
                                v-if="lowongan.departemen"
                                class="flex items-start gap-3"
                            >
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    <Building2 class="size-4" />
                                </span>

                                <div class="min-w-0">
                                    <p
                                        class="text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        Departemen
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ lowongan.departemen }}
                                    </p>
                                </div>
                            </div>

                            <!-- Location -->
                            <div
                                v-if="lowongan.lokasi"
                                class="flex items-start gap-3"
                            >
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    <MapPin class="size-4" />
                                </span>

                                <div class="min-w-0">
                                    <p
                                        class="text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        Lokasi
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ lowongan.lokasi }}
                                    </p>
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div
                                v-if="lowongan.tanggal_mulai"
                                class="flex items-start gap-3"
                            >
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    <CalendarDays class="size-4" />
                                </span>

                                <div class="min-w-0">
                                    <p
                                        class="text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        Mulai Rekrutmen
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ formatDate(lowongan.tanggal_mulai) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Closing Date -->
                            <div
                                v-if="lowongan.tanggal_tutup"
                                class="flex items-start gap-3"
                            >
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg"
                                    :class="
                                        isClosed
                                            ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                            : 'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400'
                                    "
                                >
                                    <Clock3 class="size-4" />
                                </span>

                                <div class="min-w-0">
                                    <p
                                        class="text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        Batas Lamaran
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold"
                                        :class="
                                            isClosed
                                                ? 'text-slate-600 dark:text-slate-400'
                                                : 'text-blue-700 dark:text-blue-400'
                                        "
                                    >
                                        {{ formatDate(lowongan.tanggal_tutup) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div
                                class="border-t border-slate-100 dark:border-slate-800"
                            />

                            <!-- Status -->
                            <div
                                class="rounded-2xl p-4"
                                :class="
                                    isClosed
                                        ? 'bg-slate-50 dark:bg-slate-800/60'
                                        : 'bg-emerald-50 dark:bg-emerald-950/20'
                                "
                            >
                                <div class="flex items-start gap-3">
                                    <span
                                        class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg"
                                        :class="
                                            isClosed
                                                ? 'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400'
                                                : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400'
                                        "
                                    >
                                        <Clock3 class="size-4" />
                                    </span>

                                    <div>
                                        <p
                                            class="text-sm font-semibold"
                                            :class="
                                                isClosed
                                                    ? 'text-slate-700 dark:text-slate-300'
                                                    : 'text-emerald-800 dark:text-emerald-300'
                                            "
                                        >
                                            {{ recruitmentStatus.label }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs leading-5"
                                            :class="
                                                isClosed
                                                    ? 'text-slate-500 dark:text-slate-400'
                                                    : 'text-emerald-700/80 dark:text-emerald-300/70'
                                            "
                                        >
                                            {{ recruitmentStatus.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- =================================================
                                 APPLY CTA
                            ================================================== -->
                            <Link
                                v-if="!isClosed"
                                :href="`/karier/${lowongan.slug}/lamar`"
                                class="group flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/20 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                            >
                                Lamar Sekarang

                                <ArrowRight
                                    class="size-4 transition-transform duration-200 group-hover:translate-x-0.5"
                                />
                            </Link>

                            <!-- Closed -->
                            <div
                                v-else
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                            >
                                <Clock3 class="size-4" />

                                Lamaran Ditutup
                            </div>

                            <p
                                v-if="!isClosed"
                                class="text-center text-[11px] leading-5 text-slate-400 dark:text-slate-500"
                            >
                                Siapkan CV dan dokumen pendukung sebelum mengisi
                                formulir lamaran.
                            </p>
                        </div>
                    </div>

                    <!-- Back Card -->
                    <Link
                        href="/karier"
                        class="group mt-4 flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/90 px-4 py-3.5 text-sm font-medium text-slate-600 shadow-sm transition-all hover:border-blue-200 hover:bg-blue-50/70 hover:text-blue-600 dark:border-slate-800 dark:bg-slate-900/90 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/20 dark:hover:text-blue-400"
                    >
                        <span class="flex items-center gap-2">
                            <ArrowLeft class="size-4" />

                            Lihat posisi lainnya
                        </span>

                        <ArrowRight
                            class="size-4 transition-transform group-hover:translate-x-0.5"
                        />
                    </Link>
                </aside>
            </div>

            <!-- =====================================================
                 EMPTY CONTENT FALLBACK
            ====================================================== -->
            <section
                v-else
                class="rounded-3xl border border-slate-200/80 bg-white/95 px-6 py-16 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900/95"
            >
                <div
                    class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                >
                    <BriefcaseBusiness class="size-6" />
                </div>

                <h2
                    class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                >
                    Informasi lowongan belum tersedia
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    Detail informasi untuk posisi ini belum tersedia. Silakan
                    kembali ke halaman karier untuk melihat posisi lainnya.
                </p>

                <Link
                    href="/karier"
                    class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition-all hover:bg-blue-700"
                >
                    <ArrowLeft class="size-4" />

                    Kembali ke Karier
                </Link>
            </section>

            <!-- =====================================================
                 FOOTER CTA
            ====================================================== -->
            <section
                class="mt-8 overflow-hidden rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 shadow-lg shadow-blue-600/10 dark:border-blue-900/50"
            >
                <div
                    class="relative px-6 py-8 sm:px-8 sm:py-9 lg:flex lg:items-center lg:justify-between lg:px-10"
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
                            Siap berkembang bersama KITB?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-blue-100 sm:text-base"
                        >
                            Temukan kesempatan untuk menjadi bagian dari
                            perjalanan PT Kawasan Industri Tanjung Buton.
                        </p>
                    </div>

                    <div class="relative mt-5 shrink-0 lg:ml-8 lg:mt-0">
                        <Link
                            href="/karier"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-blue-700 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-blue-50 hover:shadow-md"
                        >
                            Lihat Semua Posisi

                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
