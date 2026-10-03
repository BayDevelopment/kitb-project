<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowRight,
    ChevronRight,
    Building2,
    CheckCircle2,
    Globe2,
    Home,
    Mail,
    MapPin,
    Phone,
    Quote,
} from "lucide-vue-next";
import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

/* ==========================================================================
   Types
   ========================================================================== */

interface CompanyProfile {
    id: number;
    nama_perusahaan: string | null;
    tentang_kami: string | null;
    latar_belakang: string | null;
    moto: string | null;
    alamat: string | null;
    email: string | null;
    telepon: string | null;
    website: string | null;
    logo: string | null;
    aktif: boolean;
}

interface Props {
    companyProfile: CompanyProfile | null;
}

const props = defineProps<Props>();

/* ==========================================================================
   Reduced Motion
   ========================================================================== */

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const revealed = ref(prefersReducedMotion);

let observer: IntersectionObserver | null = null;

/* ==========================================================================
   Fade In
   ========================================================================== */

onMounted(() => {
    if (prefersReducedMotion) {
        revealed.value = true;
        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    observer?.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.08,
            rootMargin: "0px 0px -40px 0px",
        },
    );

    document.querySelectorAll(".reveal").forEach((element) => {
        observer?.observe(element);
    });
});

onBeforeUnmount(() => {
    observer?.disconnect();
});

/* ==========================================================================
   Company Data
   ========================================================================== */

const company = computed(() => props.companyProfile);

const companyName = computed(
    () =>
        company.value?.nama_perusahaan?.trim() ||
        "PT Kawasan Industri Tanjung Buton",
);

const tentangKami = computed(
    () =>
        company.value?.tentang_kami?.trim() ||
        "Informasi mengenai profil dan kegiatan perusahaan belum tersedia.",
);

const latarBelakang = computed(
    () =>
        company.value?.latar_belakang?.trim() ||
        "Informasi mengenai latar belakang perusahaan belum tersedia.",
);

const moto = computed(
    () => company.value?.moto?.trim() || "Bersama membangun masa depan.",
);

const alamat = computed(() => company.value?.alamat?.trim() || "");

const email = computed(() => company.value?.email?.trim() || "");

const telepon = computed(() => company.value?.telepon?.trim() || "");

const website = computed(() => company.value?.website?.trim() || "");

const isActive = computed(() => Boolean(company.value?.aktif));

/* ==========================================================================
   URL Helpers
   ========================================================================== */

const logoUrl = computed(() => {
    const logo = company.value?.logo?.trim();

    if (!logo) {
        return "";
    }

    if (/^https?:\/\//i.test(logo)) {
        return logo;
    }

    if (logo.startsWith("/storage/")) {
        return logo;
    }

    if (logo.startsWith("/")) {
        return logo;
    }

    return `/storage/${logo}`;
});

const websiteUrl = computed(() => {
    if (!website.value) {
        return "";
    }

    if (/^https?:\/\//i.test(website.value)) {
        return website.value;
    }

    return `https://${website.value}`;
});

const mailUrl = computed(() => {
    if (!email.value) {
        return "";
    }

    return `mailto:${email.value}`;
});

const phoneUrl = computed(() => {
    if (!telepon.value) {
        return "";
    }

    const cleanPhone = telepon.value.replace(/[^\d+]/g, "");

    return `tel:${cleanPhone}`;
});

/* ==========================================================================
   Text Helpers
   ========================================================================== */

const formatParagraphs = (value: string) =>
    value
        .split(/\n+/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean);
</script>

<template>
    <Head>
        <title>Tentang Kami | {{ companyName }} | KITB</title>

        <meta
            name="description"
            :content="`Profil ${companyName}, informasi perusahaan, latar belakang, moto, alamat, dan kontak KITB.`"
        />

        <meta property="og:title" :content="`Tentang Kami | ${companyName}`" />

        <meta
            property="og:description"
            :content="`Kenali ${companyName}, profil perusahaan dan informasi kontak KITB.`"
        />

        <meta property="og:type" content="website" />

        <meta
            property="og:url"
            content="https://tanjungbuton-industrial.co.id/profil-perusahaan/tentang-kami"
        />

        <meta v-if="logoUrl" property="og:image" :content="logoUrl" />
    </Head>

    <div
        class="relative min-h-screen overflow-x-clip bg-slate-50/70 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
    >
        <!-- ==================================================================
             AMBIENT BACKGROUND
        =================================================================== -->

        <div
            class="pointer-events-none absolute inset-x-0 -top-28 h-[820px] overflow-hidden"
            aria-hidden="true"
        >
            <!-- Top fade -->
            <div
                class="absolute inset-x-0 top-0 h-60 bg-gradient-to-b from-blue-100/70 via-blue-50/40 to-transparent dark:from-blue-950/40 dark:via-blue-950/10"
            />

            <!-- Left blob -->
            <div
                class="blob blob-a absolute left-[2%] top-0 size-[26rem] rounded-full bg-gradient-to-br from-blue-400/35 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/25 dark:via-indigo-500/15"
            />

            <!-- Right blob -->
            <div
                class="blob blob-b absolute right-[2%] top-4 size-[22rem] rounded-full bg-gradient-to-tr from-sky-300/35 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/20 dark:via-blue-500/10"
            />

            <!-- Center blob -->
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

            <!-- Bottom fade -->
            <div
                class="absolute inset-x-0 bottom-0 h-52 bg-gradient-to-b from-transparent to-slate-50/90 dark:to-slate-950/90"
            />
        </div>

        <!-- ==================================================================
             MAIN
        =================================================================== -->

        <main
            class="relative z-10 mx-auto w-full max-w-[1440px] px-4 pb-16 pt-24 sm:px-6 sm:pt-28 lg:px-8 lg:pb-20 lg:pt-32"
        >
            <div v-fade-in class="mb-6" style="--d: 0ms">
                <nav
                    aria-label="Breadcrumb"
                    class="flex items-center gap-2 text-sm"
                >
                    <!-- Beranda -->
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Home class="size-4 shrink-0" />
                        <span>Beranda</span>
                    </Link>

                    <!-- Separator -->
                    <ChevronRight class="size-4 shrink-0 text-slate-400" />

                    <!-- Perusahaan -->
                    <Link
                        href="/profil-perusahaan/tentang-kami"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Building2 class="size-4 shrink-0" />
                        <span>Perusahaan</span>
                    </Link>

                    <!-- Separator -->
                    <ChevronRight class="size-4 shrink-0 text-slate-400" />

                    <!-- Current Page -->
                    <span
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                        aria-current="page"
                    >
                        <Building2
                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                        />
                        <span>Tentang Kami</span>
                    </span>
                </nav>
            </div>

            <!-- ==================================================================
                 HERO
            =================================================================== -->

            <section class="mx-auto max-w-5xl text-center">
                <div
                    class="reveal inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-white/75 px-3.5 py-1.5 text-xs font-semibold text-blue-700 shadow-sm shadow-blue-900/5 backdrop-blur-md dark:border-blue-900/60 dark:bg-slate-900/70 dark:text-blue-300"
                    style="--d: 100"
                >
                    <Building2 class="size-3.5" />

                    <span>Profil Perusahaan</span>
                </div>

                <h1
                    class="reveal mt-5 text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white"
                    style="--d: 200"
                >
                    Mengenal

                    <span
                        class="bg-gradient-to-r from-blue-600 via-indigo-600 to-sky-500 bg-clip-text text-transparent"
                    >
                        {{ companyName }}
                    </span>
                </h1>

                <p
                    class="reveal mx-auto mt-5 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8 dark:text-slate-400"
                    style="--d: 300"
                >
                    Mengenal lebih dekat profil, perjalanan, nilai, dan
                    informasi perusahaan yang menjadi bagian dari pengembangan
                    kawasan industri KITB.
                </p>

                <div
                    class="reveal mx-auto mt-8 flex w-fit items-center gap-2 rounded-full border border-slate-200/80 bg-white/70 px-4 py-2 text-xs font-medium shadow-sm backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/70 dark:text-slate-300"
                    style="--d: 400"
                >
                    <span
                        class="size-2 rounded-full"
                        :class="
                            isActive
                                ? 'bg-emerald-500 shadow-sm shadow-emerald-500/50'
                                : 'bg-slate-400'
                        "
                    />

                    <span>
                        {{
                            isActive
                                ? "Perusahaan Aktif"
                                : "Informasi Perusahaan"
                        }}
                    </span>
                </div>
            </section>

            <!-- ==================================================================
                 COMPANY OVERVIEW
            =================================================================== -->

            <section class="reveal mx-auto mt-10 max-w-6xl" style="--d: 480">
                <div
                    class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/85 shadow-xl shadow-slate-900/[0.04] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/85"
                >
                    <div class="grid lg:grid-cols-[320px_minmax(0,1fr)]">
                        <!-- ======================================================
                             COMPANY IDENTITY
                        ======================================================= -->

                        <aside
                            class="relative overflow-hidden border-b border-slate-200/80 bg-slate-50/70 p-6 sm:p-8 lg:border-b-0 lg:border-r dark:border-slate-800 dark:bg-slate-950/40"
                        >
                            <div
                                class="pointer-events-none absolute -right-20 -top-20 size-52 rounded-full bg-blue-100/50 blur-3xl dark:bg-blue-950/30"
                            />

                            <div class="relative">
                                <!-- Logo -->
                                <div
                                    class="flex size-24 items-center justify-center overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white shadow-lg shadow-slate-900/[0.06] dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <img
                                        v-if="logoUrl"
                                        :src="logoUrl"
                                        :alt="`Logo ${companyName}`"
                                        class="size-full object-contain p-3"
                                        loading="eager"
                                        decoding="async"
                                    />

                                    <Building2
                                        v-else
                                        class="size-10 text-blue-500"
                                    />
                                </div>

                                <h2
                                    class="mt-6 text-xl font-bold leading-tight tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ companyName }}
                                </h2>

                                <div
                                    class="mt-3 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300"
                                >
                                    <CheckCircle2 class="size-3.5" />

                                    <span>
                                        {{
                                            isActive
                                                ? "Profil Aktif"
                                                : "Profil Perusahaan"
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="mt-7 h-px bg-slate-200 dark:bg-slate-800"
                                />

                                <p
                                    class="mt-6 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400"
                                >
                                    Informasi Perusahaan
                                </p>

                                <div class="mt-4 space-y-4">
                                    <!-- Address -->
                                    <div
                                        v-if="alamat"
                                        class="flex items-start gap-3"
                                    >
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                        >
                                            <MapPin class="size-4" />
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                            >
                                                Alamat
                                            </p>

                                            <p
                                                class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300"
                                            >
                                                {{ alamat }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div
                                        v-if="email"
                                        class="flex items-start gap-3"
                                    >
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400"
                                        >
                                            <Mail class="size-4" />
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                            >
                                                Email
                                            </p>

                                            <a
                                                :href="mailUrl"
                                                class="mt-1 block break-all text-sm font-medium text-slate-700 transition hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400"
                                            >
                                                {{ email }}
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Phone -->
                                    <div
                                        v-if="telepon"
                                        class="flex items-start gap-3"
                                    >
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                                        >
                                            <Phone class="size-4" />
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                            >
                                                Telepon
                                            </p>

                                            <a
                                                :href="phoneUrl"
                                                class="mt-1 block text-sm font-medium text-slate-700 transition hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400"
                                            >
                                                {{ telepon }}
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Website -->
                                    <div
                                        v-if="website"
                                        class="flex items-start gap-3"
                                    >
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-950/50 dark:text-cyan-400"
                                        >
                                            <Globe2 class="size-4" />
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                            >
                                                Website
                                            </p>

                                            <a
                                                :href="websiteUrl"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="mt-1 block break-all text-sm font-medium text-slate-700 transition hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400"
                                            >
                                                {{ website }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>

                        <!-- ======================================================
                             MAIN PROFILE
                        ======================================================= -->

                        <div class="p-6 sm:p-8 lg:p-10">
                            <!-- Tentang Kami -->
                            <div>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        <Building2 class="size-5" />
                                    </div>

                                    <div>
                                        <p
                                            class="text-[11px] font-semibold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400"
                                        >
                                            Tentang Kami
                                        </p>

                                        <h2
                                            class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                                        >
                                            Siapa Kami
                                        </h2>
                                    </div>
                                </div>

                                <div
                                    class="mt-6 space-y-4 text-sm leading-8 text-slate-600 sm:text-[15px] dark:text-slate-300"
                                >
                                    <p
                                        v-for="(
                                            paragraph, index
                                        ) in formatParagraphs(tentangKami)"
                                        :key="`tentang-${index}`"
                                    >
                                        {{ paragraph }}
                                    </p>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div
                                class="my-9 h-px bg-gradient-to-r from-slate-200 via-slate-200/70 to-transparent dark:from-slate-800 dark:via-slate-800/70"
                            />

                            <!-- Latar Belakang -->
                            <div>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400"
                                    >
                                        <MapPin class="size-5" />
                                    </div>

                                    <div>
                                        <p
                                            class="text-[11px] font-semibold uppercase tracking-[0.16em] text-sky-600 dark:text-sky-400"
                                        >
                                            Perjalanan
                                        </p>

                                        <h2
                                            class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                                        >
                                            Latar Belakang
                                        </h2>
                                    </div>
                                </div>

                                <div
                                    class="mt-6 space-y-4 text-sm leading-8 text-slate-600 sm:text-[15px] dark:text-slate-300"
                                >
                                    <p
                                        v-for="(
                                            paragraph, index
                                        ) in formatParagraphs(latarBelakang)"
                                        :key="`latar-${index}`"
                                    >
                                        {{ paragraph }}
                                    </p>
                                </div>
                            </div>

                            <!-- Moto -->
                            <div
                                class="relative mt-9 overflow-hidden rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50/90 via-indigo-50/70 to-sky-50/80 p-6 sm:p-7 dark:border-blue-900/50 dark:from-blue-950/40 dark:via-indigo-950/30 dark:to-sky-950/30"
                            >
                                <Quote
                                    class="absolute -right-3 -top-4 size-28 rotate-12 text-blue-500/[0.07] dark:text-blue-300/[0.06]"
                                />

                                <div class="relative">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-2xl bg-white/80 text-blue-600 shadow-sm dark:bg-slate-900/70 dark:text-blue-400"
                                    >
                                        <Quote class="size-5" />
                                    </div>

                                    <p
                                        class="mt-5 text-[11px] font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400"
                                    >
                                        Moto Perusahaan
                                    </p>

                                    <blockquote
                                        class="mt-2 text-xl font-semibold leading-8 tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                                    >
                                        “{{ moto }}”
                                    </blockquote>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================================================================
                 CONTACT
            =================================================================== -->

            <section
                v-if="email || telepon || website || alamat"
                class="reveal mx-auto mt-8 max-w-6xl"
                style="--d: 600"
            >
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Address -->
                    <div
                        v-if="alamat"
                        class="group rounded-3xl border border-slate-200/80 bg-white/80 p-5 shadow-sm backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-900/[0.05] dark:border-slate-800 dark:bg-slate-900/75 dark:hover:border-blue-900/70"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                        >
                            <MapPin class="size-5" />
                        </div>

                        <p
                            class="mt-5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400"
                        >
                            Lokasi
                        </p>

                        <p
                            class="mt-2 line-clamp-3 text-sm font-semibold leading-6 text-slate-800 dark:text-slate-200"
                        >
                            {{ alamat }}
                        </p>
                    </div>

                    <!-- Email -->
                    <a
                        v-if="email"
                        :href="mailUrl"
                        class="group rounded-3xl border border-slate-200/80 bg-white/80 p-5 shadow-sm backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:border-sky-200 hover:shadow-lg hover:shadow-sky-900/[0.05] dark:border-slate-800 dark:bg-slate-900/75 dark:hover:border-sky-900/70"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400"
                        >
                            <Mail class="size-5" />
                        </div>

                        <p
                            class="mt-5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400"
                        >
                            Email
                        </p>

                        <p
                            class="mt-2 break-all text-sm font-semibold leading-6 text-slate-800 transition group-hover:text-sky-600 dark:text-slate-200 dark:group-hover:text-sky-400"
                        >
                            {{ email }}
                        </p>
                    </a>

                    <!-- Phone -->
                    <a
                        v-if="telepon"
                        :href="phoneUrl"
                        class="group rounded-3xl border border-slate-200/80 bg-white/80 p-5 shadow-sm backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-900/[0.05] dark:border-slate-800 dark:bg-slate-900/75 dark:hover:border-indigo-900/70"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                        >
                            <Phone class="size-5" />
                        </div>

                        <p
                            class="mt-5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400"
                        >
                            Telepon
                        </p>

                        <p
                            class="mt-2 text-sm font-semibold leading-6 text-slate-800 transition group-hover:text-indigo-600 dark:text-slate-200 dark:group-hover:text-indigo-400"
                        >
                            {{ telepon }}
                        </p>
                    </a>

                    <!-- Website -->
                    <a
                        v-if="website"
                        :href="websiteUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group rounded-3xl border border-slate-200/80 bg-white/80 p-5 shadow-sm backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-lg hover:shadow-cyan-900/[0.05] dark:border-slate-800 dark:bg-slate-900/75 dark:hover:border-cyan-900/70"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600 dark:bg-cyan-950/50 dark:text-cyan-400"
                        >
                            <Globe2 class="size-5" />
                        </div>

                        <p
                            class="mt-5 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400"
                        >
                            Website
                        </p>

                        <p
                            class="mt-2 break-all text-sm font-semibold leading-6 text-slate-800 transition group-hover:text-cyan-600 dark:text-slate-200 dark:group-hover:text-cyan-400"
                        >
                            {{ website }}
                        </p>
                    </a>
                </div>
            </section>

            <!-- ==================================================================
                 EMPTY STATE
            =================================================================== -->

            <section
                v-if="!companyProfile"
                class="reveal mx-auto mt-10 max-w-3xl"
                style="--d: 480"
            >
                <div
                    class="rounded-[2rem] border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center shadow-sm backdrop-blur-md dark:border-slate-700 dark:bg-slate-900/70"
                >
                    <div
                        class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                    >
                        <Building2 class="size-7" />
                    </div>

                    <h2
                        class="mt-5 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        Informasi perusahaan belum tersedia
                    </h2>

                    <p
                        class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500 dark:text-slate-400"
                    >
                        Data profil perusahaan belum dapat ditampilkan saat ini.
                        Silakan kembali beberapa saat lagi.
                    </p>
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
