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
import { trans } from "laravel-vue-i18n";
import { currentLanguage, localizedValue } from "@/composables/useLocale";

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
   Translations
========================================================================== */

// Teks antarmuka dibaca dari lang/{id,en,zh_CN}/karier.php lewat laravel-vue-i18n.
const t = (key: string, params: Record<string, string | number> = {}) =>
    trans(`karier.${key}`, params);

/* ==========================================================================
   Helpers
========================================================================== */

const dateLocale = computed(() =>
    currentLanguage.value === "zh"
        ? "zh-CN"
        : currentLanguage.value === "en"
          ? "en-US"
          : "id-ID",
);

const formatDate = (value: string | null) => {
    if (!value) return "-";

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

const typeLabel = computed(() => {
    const value = props.lowongan.tipe_pekerjaan;

    if (!value) return t("detail.meta.job_type_fallback");

    const key = typeKeys[normalizeType(value)];

    return key ? t(key) : value;
});

/* ==========================================================================
   Recruitment Status
========================================================================== */

const jakartaDateKey = (value: Date) =>
    new Intl.DateTimeFormat("en-CA", {
        timeZone: "Asia/Jakarta",
    }).format(value);

const isClosed = computed(() => {
    if (props.lowongan.status !== "published") {
        return true;
    }

    if (!props.lowongan.tanggal_tutup) {
        return false;
    }

    const closingDate = new Date(props.lowongan.tanggal_tutup);

    if (Number.isNaN(closingDate.getTime())) {
        return false;
    }

    // Batas lamaran bersifat inklusif: masih terbuka sampai akhir hari tutup.
    return jakartaDateKey(closingDate) < jakartaDateKey(new Date());
});

const recruitmentStatus = computed(() => {
    if (props.lowongan.status !== "published") {
        return {
            label: t("detail.status.unavailable"),
            description: t("detail.status.unavailable_description"),
        };
    }

    if (isClosed.value) {
        return {
            label: t("detail.status.closed"),
            description: t("detail.status.closed_description"),
        };
    }

    return {
        label: t("detail.status.open"),
        description: t("detail.status.open_description"),
    };
});

/* ==========================================================================
   Display data
========================================================================== */

// Kartu metadata di bagian hero.
const heroMeta = computed(() => [
    {
        key: "department",
        label: t("detail.meta.department"),
        icon: Building2,
        value: localizedValue(props.lowongan, "departemen") || "-",
        truncate: true,
    },
    {
        key: "location",
        label: t("detail.meta.location"),
        icon: MapPin,
        value: localizedValue(props.lowongan, "lokasi") || "-",
        truncate: true,
    },
    {
        key: "start",
        label: t("detail.meta.start"),
        icon: CalendarDays,
        value: formatDate(props.lowongan.tanggal_mulai),
        truncate: false,
    },
    {
        key: "deadline",
        label: t("detail.meta.deadline"),
        icon: Clock3,
        value: formatDate(props.lowongan.tanggal_tutup),
        truncate: false,
    },
]);

// Baris ringkasan di sidebar.
const summaryRows = computed(() => [
    {
        key: "type",
        label: t("detail.summary.job_type"),
        icon: BriefcaseBusiness,
        value: typeLabel.value,
    },
    {
        key: "department",
        label: t("detail.meta.department"),
        icon: Building2,
        value: localizedValue(props.lowongan, "departemen") || "-",
    },
    {
        key: "location",
        label: t("detail.meta.location"),
        icon: MapPin,
        value: localizedValue(props.lowongan, "lokasi") || "-",
    },
    {
        key: "start",
        label: t("detail.summary.start_registration"),
        icon: CalendarDays,
        value: formatDate(props.lowongan.tanggal_mulai),
    },
    {
        key: "deadline",
        label: t("detail.summary.deadline_registration"),
        icon: Clock3,
        value: formatDate(props.lowongan.tanggal_tutup),
    },
]);

// Class Tailwind ditulis utuh agar tidak dibuang oleh purge.
const contentSections = computed(() =>
    [
        {
            key: "description",
            eyebrow: t("detail.sections.about.eyebrow"),
            title: t("detail.sections.about.title"),
            icon: BriefcaseBusiness,
            iconClass:
                "bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400",
            eyebrowClass: "text-blue-600 dark:text-blue-400",
            html: localizedValue(props.lowongan, "deskripsi"),
            delay: 160,
        },
        {
            key: "responsibilities",
            eyebrow: t("detail.sections.responsibilities.eyebrow"),
            title: t("detail.sections.responsibilities.title"),
            icon: CheckCircle2,
            iconClass:
                "bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400",
            eyebrowClass: "text-indigo-600 dark:text-indigo-400",
            html: localizedValue(props.lowongan, "tanggung_jawab"),
            delay: 220,
        },
        {
            key: "qualifications",
            eyebrow: t("detail.sections.qualifications.eyebrow"),
            title: t("detail.sections.qualifications.title"),
            icon: Users,
            iconClass:
                "bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400",
            eyebrowClass: "text-sky-600 dark:text-sky-400",
            html: localizedValue(props.lowongan, "kualifikasi"),
            delay: 280,
        },
        {
            key: "benefits",
            eyebrow: t("detail.sections.benefits.eyebrow"),
            title: t("detail.sections.benefits.title"),
            icon: Sparkles,
            iconClass:
                "bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400",
            eyebrowClass: "text-emerald-600 dark:text-emerald-400",
            html: localizedValue(props.lowongan, "benefit"),
            delay: 340,
        },
    ].filter((section) => Boolean(section.html?.trim())),
);

const hasContent = computed(() => contentSections.value.length > 0);

const proseClass =
    "prose prose-slate max-w-none text-sm leading-8 prose-headings:text-slate-900 prose-p:text-slate-600 prose-strong:text-slate-900 prose-li:text-slate-600 dark:prose-invert dark:prose-p:text-slate-300 dark:prose-strong:text-white dark:prose-li:text-slate-300";

/* ==========================================================================
   SEO
========================================================================== */

const judul = computed(() => localizedValue(props.lowongan, "judul"));

const pageTitle = computed(() => t("detail.seo.title", { title: judul.value }));

const canonicalUrl = computed(
    () => `https://tanjungbuton-industrial.co.id/karier/${props.lowongan.slug}`,
);

const ogImage = "https://tanjungbuton-industrial.co.id/logoside.png";
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>

        <meta
            name="description"
            :content="t('detail.seo.description', { title: judul })"
        />

        <meta name="robots" content="index, follow" />
        <link rel="canonical" :href="canonicalUrl" />

        <meta property="og:title" :content="pageTitle" />
        <meta
            property="og:description"
            :content="t('detail.seo.og_description', { title: judul })"
        />
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:image" :content="ogImage" />
    </Head>

    <div
        class="relative min-h-screen overflow-x-clip bg-slate-50/70 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
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
        <main
            class="relative z-10 mx-auto w-full max-w-[1440px] px-4 pb-12 pt-24 sm:px-6 sm:pt-28 lg:px-8 lg:pb-16 lg:pt-32"
        >
            <!-- Back -->
            <div class="reveal mb-6" style="--d: 0">
                <Link
                    href="/karier"
                    class="group inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-white/80 px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm backdrop-blur-sm transition-all duration-300 hover:-translate-x-0.5 hover:border-blue-300 hover:bg-white hover:shadow-md focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:border-blue-900/60 dark:bg-slate-900/70 dark:text-blue-300 dark:hover:border-blue-800 dark:hover:bg-slate-900"
                >
                    <ArrowLeft
                        class="size-4 transition-transform duration-300 group-hover:-translate-x-0.5"
                        aria-hidden="true"
                    />

                    {{ t("detail.navigation.back") }}
                </Link>
            </div>

            <!-- HERO -->
            <section
                class="reveal relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-xl shadow-slate-900/[0.05] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                style="--d: 80"
            >
                <div
                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-sky-400"
                    aria-hidden="true"
                />

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
                            <BriefcaseBusiness
                                class="size-3.5"
                                aria-hidden="true"
                            />

                            {{ typeLabel }}
                        </span>

                        <span
                            v-if="lowongan.unggulan"
                            class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3.5 py-1.5 text-xs font-semibold text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300"
                        >
                            <Sparkles class="size-3.5" aria-hidden="true" />

                            {{ t("detail.badges.featured") }}
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
                                aria-hidden="true"
                            />

                            {{ recruitmentStatus.label }}
                        </span>
                    </div>

                    <!-- Heading -->
                    <div class="mt-6 max-w-4xl">
                        <h1
                            class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl dark:text-white"
                        >
                            {{ judul }}
                        </h1>

                        <p
                            class="mt-5 text-sm leading-7 text-slate-600 sm:text-base sm:leading-8 dark:text-slate-400"
                        >
                            {{ t("detail.hero.prefix") }}
                            <strong
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ t("detail.hero.company") }}
                            </strong>
                            {{ t("detail.hero.suffix") }}
                        </p>

                        <!-- Apply (mobile/tablet; di desktop ada di sidebar) -->
                        <div class="mt-6 lg:hidden">
                            <Link
                                v-if="!isClosed"
                                :href="`/karier/${lowongan.slug}/lamar`"
                                class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition duration-300 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                            >
                                {{ t("detail.apply.now") }}

                                <ArrowRight
                                    class="size-4 transition-transform duration-300 group-hover:translate-x-1"
                                    aria-hidden="true"
                                />
                            </Link>
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div
                            v-for="item in heroMeta"
                            :key="item.key"
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-slate-800 dark:bg-slate-950/50 dark:hover:border-blue-900/60 dark:hover:bg-blue-950/20"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    <component
                                        :is="item.icon"
                                        class="size-5"
                                        aria-hidden="true"
                                    />
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                                    >
                                        {{ item.label }}
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        :class="item.truncate ? 'truncate' : ''"
                                        :title="
                                            item.truncate
                                                ? item.value
                                                : undefined
                                        "
                                    >
                                        {{ item.value }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- BODY -->
            <div
                class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_340px]"
            >
                <!-- Main -->
                <div class="min-w-0 space-y-6">
                    <!--
                        Catatan keamanan: konten di bawah dirender dengan v-html.
                        Pastikan HTML sudah disanitasi di server (mis. mews/purifier)
                        atau dengan DOMPurify sebelum disimpan / dikirim ke sini.
                    -->
                    <section
                        v-for="section in contentSections"
                        :key="section.key"
                        class="reveal rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-6 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl sm:p-8 dark:border-slate-800 dark:bg-slate-900/90"
                        :style="{ '--d': section.delay }"
                    >
                        <div class="mb-6 flex items-center gap-4">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-2xl"
                                :class="section.iconClass"
                            >
                                <component
                                    :is="section.icon"
                                    class="size-6"
                                    aria-hidden="true"
                                />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em]"
                                    :class="section.eyebrowClass"
                                >
                                    {{ section.eyebrow }}
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{ section.title }}
                                </h2>
                            </div>
                        </div>

                        <div :class="proseClass" v-html="section.html" />
                    </section>

                    <!-- Empty -->
                    <section
                        v-if="!hasContent"
                        class="reveal rounded-[1.75rem] border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900/70"
                        style="--d: 160"
                    >
                        <div
                            class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                            aria-hidden="true"
                        >
                            <BriefcaseBusiness class="size-7" />
                        </div>

                        <h2
                            class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                        >
                            {{ t("detail.empty.title") }}
                        </h2>

                        <p
                            class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{ t("detail.empty.description") }}
                        </p>
                    </section>
                </div>

                <!-- SIDEBAR -->
                <aside class="lg:sticky lg:top-28">
                    <div
                        class="reveal overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/90 shadow-xl shadow-slate-900/[0.05] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 220"
                    >
                        <!-- Header -->
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
                                    aria-hidden="true"
                                >
                                    <BriefcaseBusiness class="size-5" />
                                </div>

                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-100"
                                >
                                    {{ t("detail.summary.eyebrow") }}
                                </p>

                                <h2 class="mt-2 text-xl font-bold">
                                    {{ t("detail.summary.title") }}
                                </h2>

                                <p class="mt-2 text-sm leading-6 text-blue-100">
                                    {{ t("detail.summary.description") }}
                                </p>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6">
                            <!-- Summary -->
                            <div class="space-y-4">
                                <div
                                    v-for="(row, index) in summaryRows"
                                    :key="row.key"
                                    class="flex items-start gap-3"
                                    :class="
                                        index < summaryRows.length - 1
                                            ? 'border-b border-slate-100 pb-4 dark:border-slate-800'
                                            : ''
                                    "
                                >
                                    <component
                                        :is="row.icon"
                                        class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                        aria-hidden="true"
                                    />

                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-400">
                                            {{ row.label }}
                                        </p>

                                        <p
                                            class="mt-1 break-words text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ row.value }}
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
                                        aria-hidden="true"
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
                                    {{ t("detail.apply.now") }}

                                    <ArrowRight
                                        class="size-4 transition-transform duration-300 group-hover:translate-x-1"
                                        aria-hidden="true"
                                    />
                                </Link>

                                <div
                                    v-else
                                    class="flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-5 py-3.5 text-sm font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400"
                                    role="status"
                                >
                                    <Clock3 class="size-4" aria-hidden="true" />

                                    {{ t("detail.apply.closed") }}
                                </div>
                            </div>

                            <!-- Note -->
                            <div
                                class="mt-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-950/60"
                            >
                                <p
                                    class="text-xs leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    {{ t("detail.apply.note") }}
                                </p>
                            </div>

                            <!-- Other positions -->
                            <Link
                                href="/karier"
                                class="group mt-5 flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3.5 transition duration-300 hover:border-blue-200 hover:bg-blue-50/50 focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-900/60 dark:hover:bg-blue-950/20"
                            >
                                <div>
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        {{ t("detail.explore.eyebrow") }}
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ t("detail.explore.label") }}
                                    </p>
                                </div>

                                <ArrowRight
                                    class="size-4 text-slate-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-blue-600 dark:group-hover:text-blue-400"
                                    aria-hidden="true"
                                />
                            </Link>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- BOTTOM CTA -->
            <section
                class="reveal relative mt-10 overflow-hidden rounded-[2rem] border border-blue-200/70 bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 px-6 py-10 text-center shadow-xl shadow-blue-900/10 sm:px-10 lg:py-12 dark:border-blue-800/50"
                style="--d: 420"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-20 size-64 rounded-full border border-white/10"
                    aria-hidden="true"
                />

                <div
                    class="pointer-events-none absolute -bottom-32 -left-20 size-72 rounded-full border border-white/10"
                    aria-hidden="true"
                />

                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_75%_20%,rgba(255,255,255,0.10),transparent_32%)]"
                    aria-hidden="true"
                />

                <div class="relative">
                    <div
                        class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20"
                        aria-hidden="true"
                    >
                        <Sparkles class="size-5" />
                    </div>

                    <p
                        class="mt-5 text-xs font-semibold uppercase tracking-[0.2em] text-blue-100"
                    >
                        {{ t("detail.cta.eyebrow") }}
                    </p>

                    <h2
                        class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl"
                    >
                        {{ t("detail.cta.title") }}
                    </h2>

                    <p
                        class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-blue-100 sm:text-base"
                    >
                        {{ t("detail.cta.description") }}
                    </p>

                    <Link
                        href="/karier"
                        class="group mt-6 inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-blue-700 shadow-lg shadow-blue-950/10 transition duration-300 hover:-translate-y-0.5 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-white/40"
                    >
                        {{ t("detail.cta.button") }}

                        <ArrowRight
                            class="size-4 transition-transform duration-300 group-hover:translate-x-1"
                            aria-hidden="true"
                        />
                    </Link>
                </div>
            </section>
        </main>
    </div>
</template>

<style scoped>
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

/* Rich text */
:deep(.prose ul),
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
