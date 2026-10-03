<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    ArrowRight,
    Building2,
    CheckCircle2,
    ChevronRight,
    Compass,
    Eye,
    Home,
    Leaf,
    Target,
    TrendingUp,
} from "lucide-vue-next";
import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

/* ==========================================================================
   Types
========================================================================== */

interface Misi {
    id?: number;
    judul?: string;
    title?: string;
    deskripsi?: string;
    desc?: string;
    isi?: string;
    urutan?: number;
    [key: string]: unknown;
}

interface Visi {
    id?: number;
    judul?: string;
    title?: string;
    visi?: string;
    deskripsi?: string;
    misis?: Misi[];
    [key: string]: unknown;
}

interface Props {
    visi?: Visi | null;
}

const props = withDefaults(defineProps<Props>(), {
    visi: null,
});

/* ==========================================================================
   Loading / Skeleton
========================================================================== */

const isLoading = ref(true);

let loadingFrame = 0;
let removeStart: (() => void) | undefined;
let removeFinish: (() => void) | undefined;

onMounted(() => {
    removeStart = router.on("start", () => {
        isLoading.value = true;
    });

    removeFinish = router.on("finish", () => {
        cancelAnimationFrame(loadingFrame);

        loadingFrame = requestAnimationFrame(() => {
            isLoading.value = false;
        });
    });

    loadingFrame = requestAnimationFrame(() => {
        isLoading.value = false;
    });
});

/* ==========================================================================
   Helpers
========================================================================== */

function firstValue(
    object: Record<string, unknown> | null | undefined,
    keys: string[],
    fallback = "",
): string {
    if (!object) {
        return fallback;
    }

    for (const key of keys) {
        const value = object[key];

        if (
            value !== null &&
            value !== undefined &&
            String(value).trim() !== ""
        ) {
            return String(value);
        }
    }

    return fallback;
}

function misiTitle(misi: Misi, index: number): string {
    return firstValue(
        misi,
        ["judul", "title"],
        `Misi ${String(index + 1).padStart(2, "0")}`,
    );
}

function misiDescription(misi: Misi): string {
    return firstValue(misi, ["deskripsi", "desc", "isi"]);
}

/* ==========================================================================
   Content State
========================================================================== */

const visionTitle = computed(() =>
    firstValue(props.visi, ["judul", "title"], "Visi"),
);

const visionText = computed(() =>
    firstValue(props.visi, ["visi", "deskripsi"]),
);

const missions = computed<Misi[]>(() =>
    (props.visi?.misis ?? []).filter((misi) => Boolean(misiDescription(misi))),
);

const hasVision = computed(() => Boolean(visionText.value));

const hasMissions = computed(() => missions.value.length > 0);

const hasContent = computed(() => hasVision.value || hasMissions.value);

/* ==========================================================================
   SEO
========================================================================== */

const pageTitle = computed(
    () => `${visionTitle.value} & Misi - PT Kawasan Industri Tanjung Buton`,
);

const pageDescription = computed(
    () =>
        "Visi, misi, dan arah strategis PT Kawasan Industri Tanjung Buton (KITB) dalam membangun kawasan industri yang terintegrasi dan berkelanjutan.",
);

/* ==========================================================================
   Reveal Animation
========================================================================== */

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

let observer: IntersectionObserver | null = null;

const vFadeIn = {
    mounted(el: HTMLElement) {
        if (prefersReducedMotion) {
            el.classList.add("reveal-visible");
            return;
        }

        el.classList.add("reveal");

        if (!observer) {
            observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        entry.target.classList.add("reveal-visible");

                        observer?.unobserve(entry.target);
                    });
                },
                {
                    threshold: 0.08,
                    rootMargin: "0px 0px -40px 0px",
                },
            );
        }

        observer.observe(el);
    },
};

/* ==========================================================================
   Cleanup
========================================================================== */

onBeforeUnmount(() => {
    removeStart?.();
    removeFinish?.();

    cancelAnimationFrame(loadingFrame);

    observer?.disconnect();
    observer = null;
});
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>

        <meta name="description" :content="pageDescription" />

        <meta name="robots" content="index, follow" />

        <meta property="og:title" :content="pageTitle" />

        <meta property="og:description" :content="pageDescription" />

        <meta property="og:type" content="website" />

        <meta
            property="og:url"
            content="https://tanjungbuton-industrial.co.id/profil-perusahaan/visi-misi"
        />
    </Head>

    <div
        class="relative min-h-screen overflow-x-clip bg-slate-50/70 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
    >
        <!-- ================================================================
             AMBIENT BACKGROUND
        ================================================================= -->

        <div
            class="pointer-events-none absolute inset-x-0 -top-28 h-[900px] overflow-hidden"
            aria-hidden="true"
        >
            <!-- Top fade -->
            <div
                class="absolute inset-x-0 top-0 h-64 bg-gradient-to-b from-blue-100/75 via-blue-50/40 to-transparent dark:from-blue-950/35 dark:via-blue-950/10"
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
                class="absolute inset-x-0 bottom-0 h-56 bg-gradient-to-b from-transparent to-slate-50/95 dark:to-slate-950/95"
            />
        </div>

        <!-- ================================================================
             MAIN
        ================================================================= -->

        <main
            class="relative z-10 mx-auto w-full max-w-[1440px] px-4 pb-14 pt-24 sm:px-6 sm:pt-28 lg:px-8 lg:pb-20 lg:pt-32"
        >
            <!-- ============================================================
                 BREADCRUMB
            ============================================================= -->
            <!-- ============================================================
     BREADCRUMB
============================================================= -->
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
                    >
                        <Target
                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                        />
                        <span>Visi &amp; Misi</span>
                    </span>
                </nav>
            </div>

            <!-- ============================================================
                 HERO
            ============================================================= -->

            <section
                v-fade-in
                class="relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-xl shadow-slate-900/[0.05] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                style="--d: 80ms"
            >
                <!-- Accent -->
                <div
                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-sky-400"
                />

                <!-- Glow -->
                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute -right-28 -top-28 size-80 rounded-full bg-blue-400/10 blur-3xl dark:bg-blue-500/10"
                />

                <div
                    aria-hidden="true"
                    class="pointer-events-none absolute -bottom-32 -left-24 size-72 rounded-full bg-indigo-400/10 blur-3xl dark:bg-indigo-500/10"
                />

                <div class="relative p-6 sm:p-8 lg:p-10">
                    <div class="max-w-4xl">
                        <!-- Label -->
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3.5 py-1.5 text-xs font-semibold text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                        >
                            <Compass class="size-3.5" />
                            Profil Perusahaan
                        </div>

                        <!-- Heading -->
                        <h1
                            class="mt-5 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl dark:text-white"
                        >
                            Arah yang jelas untuk tumbuh bersama.
                        </h1>

                        <p
                            class="mt-5 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8 dark:text-slate-400"
                        >
                            Visi dan misi menjadi landasan KITB dalam membangun
                            kawasan industri yang terintegrasi, berkelanjutan,
                            dan memberikan nilai jangka panjang bagi seluruh
                            pemangku kepentingan.
                        </p>
                    </div>

                    <!-- Hero mini stats -->
                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <!-- Arah -->
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/50"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    <Eye class="size-5" />
                                </div>

                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                                    >
                                        Arah
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        Visi Perusahaan
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Fokus -->
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/50"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm dark:bg-slate-900 dark:text-indigo-400"
                                >
                                    <Target class="size-5" />
                                </div>

                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                                    >
                                        Fokus
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        Misi Strategis
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Prinsip -->
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/50"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm dark:bg-slate-900 dark:text-emerald-400"
                                >
                                    <Leaf class="size-5" />
                                </div>

                                <div>
                                    <p
                                        class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                                    >
                                        Prinsip
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        Berkelanjutan
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 SKELETON
            ============================================================= -->

            <div
                v-if="isLoading"
                class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_340px]"
            >
                <div class="space-y-6">
                    <!-- Vision skeleton -->
                    <section
                        class="rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-6 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl sm:p-8 dark:border-slate-800 dark:bg-slate-900/90"
                    >
                        <div class="animate-pulse">
                            <div class="flex items-center gap-4">
                                <div
                                    class="size-12 rounded-2xl bg-slate-200 dark:bg-slate-800"
                                />

                                <div class="space-y-2">
                                    <div
                                        class="h-3 w-24 rounded-full bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-5 w-40 rounded-full bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>
                            </div>

                            <div class="mt-7 space-y-3">
                                <div
                                    class="h-4 w-full rounded-full bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-[92%] rounded-full bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-[78%] rounded-full bg-slate-200 dark:bg-slate-800"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Mission skeleton -->
                    <section
                        class="rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-6 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl sm:p-8 dark:border-slate-800 dark:bg-slate-900/90"
                    >
                        <div class="animate-pulse">
                            <div class="flex items-center gap-4">
                                <div
                                    class="size-12 rounded-2xl bg-slate-200 dark:bg-slate-800"
                                />

                                <div class="space-y-2">
                                    <div
                                        class="h-3 w-28 rounded-full bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-5 w-36 rounded-full bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>
                            </div>

                            <div class="mt-7 grid gap-4 sm:grid-cols-2">
                                <div
                                    v-for="item in 4"
                                    :key="item"
                                    class="rounded-2xl border border-slate-200 p-5 dark:border-slate-800"
                                >
                                    <div
                                        class="size-9 rounded-xl bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="mt-4 h-4 w-28 rounded-full bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div class="mt-3 space-y-2">
                                        <div
                                            class="h-3 w-full rounded-full bg-slate-200 dark:bg-slate-800"
                                        />

                                        <div
                                            class="h-3 w-[80%] rounded-full bg-slate-200 dark:bg-slate-800"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Sidebar skeleton -->
                <aside class="lg:sticky lg:top-28">
                    <div
                        class="overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/90 shadow-xl shadow-slate-900/[0.05] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                    >
                        <div
                            class="animate-pulse bg-slate-200 p-6 dark:bg-slate-800"
                        >
                            <div
                                class="size-11 rounded-2xl bg-slate-300 dark:bg-slate-700"
                            />

                            <div
                                class="mt-4 h-3 w-24 rounded-full bg-slate-300 dark:bg-slate-700"
                            />

                            <div
                                class="mt-3 h-6 w-40 rounded-full bg-slate-300 dark:bg-slate-700"
                            />
                        </div>

                        <div class="space-y-5 p-6">
                            <div
                                v-for="item in 4"
                                :key="item"
                                class="flex gap-3"
                            >
                                <div
                                    class="size-8 rounded-xl bg-slate-200 dark:bg-slate-800"
                                />

                                <div class="flex-1 space-y-2">
                                    <div
                                        class="h-3 w-20 rounded-full bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-4 w-32 rounded-full bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- ============================================================
                 ACTUAL CONTENT
            ============================================================= -->

            <div
                v-else
                class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_340px]"
            >
                <!-- ========================================================
                     MAIN CONTENT
                ========================================================= -->

                <div class="space-y-6">
                    <!-- VISION -->
                    <section
                        v-if="hasVision"
                        v-fade-in
                        class="rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-6 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl sm:p-8 dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 140ms"
                    >
                        <div class="mb-7 flex items-center gap-4">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                            >
                                <Eye class="size-6" />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400"
                                >
                                    Arah Perusahaan
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{ visionTitle }}
                                </h2>
                            </div>
                        </div>

                        <div
                            class="relative overflow-hidden rounded-[1.5rem] border border-blue-100 bg-gradient-to-br from-blue-50 via-white to-indigo-50 p-6 sm:p-8 dark:border-blue-900/50 dark:from-blue-950/40 dark:via-slate-900 dark:to-indigo-950/30"
                        >
                            <div
                                aria-hidden="true"
                                class="absolute -right-20 -top-20 size-52 rounded-full bg-blue-400/10 blur-3xl"
                            />

                            <div class="relative flex gap-4">
                                <div
                                    class="hidden shrink-0 text-blue-300 sm:block dark:text-blue-800"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="size-10"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M9.588 2.588A6.75 6.75 0 0 0 2.25 9.34v.41c0 2.11.98 4.1 2.65 5.39l1.37 1.05a2.25 2.25 0 0 1 .88 1.79V20.25h4.5v-2.27a6.75 6.75 0 0 0-2.63-5.37l-1.37-1.05a2.25 2.25 0 0 1-.9-1.8v-.41a2.25 2.25 0 0 1 4.5 0v.9h4.5v-.9a6.75 6.75 0 0 0-6.162-6.75Zm7.5 0A6.75 6.75 0 0 0 10 9.34v.41c0 2.11.98 4.1 2.65 5.39l1.37 1.05a2.25 2.25 0 0 1 .88 1.79V20.25h4.5v-2.27a6.75 6.75 0 0 0-2.63-5.37l-1.37-1.05a2.25 2.25 0 0 1-.9-1.8v-.41a2.25 2.25 0 0 1 4.5 0v.9h4.5v-.9a6.75 6.75 0 0 0-6.412-6.75Z"
                                        />
                                    </svg>
                                </div>

                                <p
                                    class="text-lg font-semibold leading-9 tracking-tight text-slate-800 sm:text-xl dark:text-slate-100"
                                >
                                    {{ visionText }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- MISSIONS -->
                    <section
                        v-if="hasMissions"
                        v-fade-in
                        class="rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-6 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl sm:p-8 dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 220ms"
                    >
                        <div class="mb-7 flex items-center gap-4">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                            >
                                <Target class="size-6" />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400"
                                >
                                    Fokus Strategis
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    Misi Perusahaan
                                </h2>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <article
                                v-for="(misi, index) in missions"
                                :key="misi.id ?? index"
                                class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-50/80 p-5 transition-all duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:bg-indigo-50/40 hover:shadow-lg hover:shadow-indigo-900/[0.05] dark:border-slate-800 dark:bg-slate-950/50 dark:hover:border-indigo-900/60 dark:hover:bg-indigo-950/20"
                            >
                                <div
                                    class="absolute right-0 top-0 h-24 w-24 rounded-full bg-indigo-400/5 blur-2xl transition duration-300 group-hover:bg-indigo-400/10"
                                />

                                <div class="relative">
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <div
                                            class="flex size-10 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm dark:bg-slate-900 dark:text-indigo-400"
                                        >
                                            <CheckCircle2 class="size-5" />
                                        </div>

                                        <span
                                            class="text-xs font-bold tracking-[0.15em] text-slate-300 dark:text-slate-700"
                                        >
                                            {{
                                                String(index + 1).padStart(
                                                    2,
                                                    "0",
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <h3
                                        class="mt-5 text-base font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ misiTitle(misi, index) }}
                                    </h3>

                                    <p
                                        class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400"
                                    >
                                        {{ misiDescription(misi) }}
                                    </p>
                                </div>
                            </article>
                        </div>
                    </section>

                    <!-- NILAI -->
                    <section
                        v-fade-in
                        class="rounded-[1.75rem] border border-slate-200/80 bg-white/90 p-6 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl sm:p-8 dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 300ms"
                    >
                        <div class="mb-7 flex items-center gap-4">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400"
                            >
                                <TrendingUp class="size-6" />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400"
                                >
                                    Prinsip Pengembangan
                                </p>

                                <h2
                                    class="mt-1 text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    Nilai &amp; Arah KITB
                                </h2>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Berkelanjutan -->
                            <div
                                class="group rounded-2xl border border-emerald-100 bg-emerald-50/60 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-900/[0.05] dark:border-emerald-900/50 dark:bg-emerald-950/20"
                            >
                                <div
                                    class="flex size-10 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm dark:bg-slate-900 dark:text-emerald-400"
                                >
                                    <Leaf class="size-5" />
                                </div>

                                <h3
                                    class="mt-5 text-base font-bold text-slate-900 dark:text-white"
                                >
                                    Berkelanjutan
                                </h3>

                                <p
                                    class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-400"
                                >
                                    Mendorong pengembangan kawasan yang
                                    memperhatikan keberlanjutan lingkungan,
                                    sosial, dan ekonomi dalam jangka panjang.
                                </p>
                            </div>

                            <!-- Terintegrasi -->
                            <div
                                class="group rounded-2xl border border-blue-100 bg-blue-50/60 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-900/[0.05] dark:border-blue-900/50 dark:bg-blue-950/20"
                            >
                                <div
                                    class="flex size-10 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                >
                                    <Building2 class="size-5" />
                                </div>

                                <h3
                                    class="mt-5 text-base font-bold text-slate-900 dark:text-white"
                                >
                                    Terintegrasi
                                </h3>

                                <p
                                    class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-400"
                                >
                                    Menghubungkan infrastruktur, fasilitas,
                                    layanan, dan kebutuhan industri dalam
                                    ekosistem kawasan yang saling mendukung.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- EMPTY -->
                    <section
                        v-if="!hasContent"
                        v-fade-in
                        class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900/70"
                        style="--d: 160ms"
                    >
                        <div
                            class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                        >
                            <Compass class="size-7" />
                        </div>

                        <h2
                            class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Informasi belum tersedia
                        </h2>

                        <p
                            class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Informasi visi dan misi perusahaan belum tersedia.
                            Silakan kembali lagi untuk melihat informasi
                            terbaru.
                        </p>
                    </section>
                </div>

                <!-- ========================================================
                     SIDEBAR
                ========================================================= -->

                <aside v-fade-in class="lg:sticky lg:top-28" style="--d: 220ms">
                    <div
                        class="overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/90 shadow-xl shadow-slate-900/[0.05] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                    >
                        <!-- Sidebar header -->
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
                                    <Compass class="size-5" />
                                </div>

                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-100"
                                >
                                    Company Direction
                                </p>

                                <h2 class="mt-2 text-xl font-bold">
                                    Visi &amp; Misi
                                </h2>

                                <p class="mt-2 text-sm leading-6 text-blue-100">
                                    Landasan strategis dalam pengembangan
                                    kawasan industri Tanjung Buton.
                                </p>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6">
                            <div class="space-y-4">
                                <!-- Arah -->
                                <div
                                    class="flex items-start gap-3 border-b border-slate-100 pb-4 dark:border-slate-800"
                                >
                                    <Eye
                                        class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    />

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            Arah
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Visi Perusahaan
                                        </p>
                                    </div>
                                </div>

                                <!-- Fokus -->
                                <div
                                    class="flex items-start gap-3 border-b border-slate-100 pb-4 dark:border-slate-800"
                                >
                                    <Target
                                        class="mt-0.5 size-4 shrink-0 text-indigo-600 dark:text-indigo-400"
                                    />

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            Fokus
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ missions.length }}
                                            Misi Strategis
                                        </p>
                                    </div>
                                </div>

                                <!-- Prinsip -->
                                <div
                                    class="flex items-start gap-3 border-b border-slate-100 pb-4 dark:border-slate-800"
                                >
                                    <Leaf
                                        class="mt-0.5 size-4 shrink-0 text-emerald-600 dark:text-emerald-400"
                                    />

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            Prinsip
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Berkelanjutan
                                        </p>
                                    </div>
                                </div>

                                <!-- Pendekatan -->
                                <div class="flex items-start gap-3">
                                    <Building2
                                        class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    />

                                    <div>
                                        <p class="text-xs text-slate-400">
                                            Pendekatan
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Terintegrasi
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- About link -->
                            <Link
                                href="/profil-perusahaan/tentang-kami"
                                class="group mt-6 flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/50 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-900/60 dark:hover:bg-blue-950/20"
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
                                        Tentang Kami
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

            <!-- ============================================================
                 CTA
            ============================================================= -->

            <section
                v-fade-in
                class="relative mt-10 overflow-hidden rounded-[2rem] border border-blue-200/70 bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 px-6 py-10 text-center shadow-xl shadow-blue-900/10 sm:px-10 lg:py-12 dark:border-blue-800/50"
                style="--d: 380ms"
            >
                <!-- CTA circles -->
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
                        <Compass class="size-5" />
                    </div>

                    <p
                        class="mt-5 text-xs font-semibold uppercase tracking-[0.2em] text-blue-100"
                    >
                        Kenali KITB
                    </p>

                    <h2
                        class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl"
                    >
                        Kenali lebih dekat perjalanan KITB.
                    </h2>

                    <p
                        class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-blue-100 sm:text-base"
                    >
                        Pelajari profil, perjalanan, dan komitmen PT Kawasan
                        Industri Tanjung Buton dalam membangun kawasan industri
                        yang terintegrasi dan berkelanjutan.
                    </p>

                    <Link
                        href="/profil-perusahaan/tentang-kami"
                        class="group mt-6 inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-blue-700 shadow-lg shadow-blue-950/10 transition duration-300 hover:-translate-y-0.5 hover:bg-blue-50"
                    >
                        Tentang Kami

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
   Reveal
========================================================================== */

.reveal {
    opacity: 0;
    transform: translateY(14px);
    transition:
        opacity 700ms cubic-bezier(0.2, 0.7, 0.2, 1),
        transform 700ms cubic-bezier(0.2, 0.7, 0.2, 1);
    transition-delay: var(--d, 0ms);
}

.reveal-visible {
    opacity: 1;
    transform: translateY(0);
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
        transition: none;
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
