<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";

import { Head, Link } from "@inertiajs/vue3";

import {
    Building2,
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    Globe2,
    Home,
    Landmark,
    MapPin,
    Maximize2,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

/* ============================================================
   TYPES
============================================================= */

interface ProfilKawasan {
    id: number;
    judul: string;
    slug: string;
    deskripsi: string | null;
    luas_kawasan: string | number | null;
    lokasi: string | null;
    tahun_berdiri: number | null;
    gambar: string | null;
}

const props = defineProps<{
    profilKawasans: ProfilKawasan[];
}>();

/* ============================================================
   REVEAL / FADE IN
============================================================= */

const prefersReducedMotion = ref(false);

let revealObserver: IntersectionObserver | null = null;
let mediaQuery: MediaQueryList | null = null;

const handleMotionChange = (event: MediaQueryListEvent) => {
    prefersReducedMotion.value = event.matches;
};

onMounted(() => {
    if (typeof window === "undefined") {
        return;
    }

    mediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");

    prefersReducedMotion.value = mediaQuery.matches;

    mediaQuery.addEventListener("change", handleMotionChange);

    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (prefersReducedMotion.value) {
        elements.forEach((element) => {
            element.classList.add("is-visible");
        });

        return;
    }

    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add("is-visible");

                revealObserver?.unobserve(entry.target);
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
});

onBeforeUnmount(() => {
    revealObserver?.disconnect();

    mediaQuery?.removeEventListener("change", handleMotionChange);

    stopAutoplay();
});

/* ============================================================
   CAROUSEL
============================================================= */

const currentIndex = ref(0);
const isPaused = ref(false);
const isDragging = ref(false);

let autoplayTimer: ReturnType<typeof setInterval> | null = null;

const totalSlides = computed(() => {
    return props.profilKawasans.length;
});

const currentKawasan = computed(() => {
    return props.profilKawasans[currentIndex.value] ?? null;
});

const hasMultipleSlides = computed(() => {
    return totalSlides.value > 1;
});

/* ============================================================
   CAROUSEL ACTIONS
============================================================= */

const goToSlide = (index: number) => {
    if (!totalSlides.value) {
        return;
    }

    currentIndex.value = (index + totalSlides.value) % totalSlides.value;
};

const nextSlide = () => {
    goToSlide(currentIndex.value + 1);
};

const previousSlide = () => {
    goToSlide(currentIndex.value - 1);
};

const startAutoplay = () => {
    if (
        prefersReducedMotion.value ||
        !hasMultipleSlides.value ||
        autoplayTimer
    ) {
        return;
    }

    autoplayTimer = setInterval(() => {
        if (!isPaused.value && !isDragging.value) {
            nextSlide();
        }
    }, 6500);
};

const stopAutoplay = () => {
    if (autoplayTimer) {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
    }
};

const pauseAutoplay = () => {
    isPaused.value = true;
};

const resumeAutoplay = () => {
    isPaused.value = false;
};

const handleKeydown = (event: KeyboardEvent) => {
    if (!hasMultipleSlides.value) {
        return;
    }

    if (event.key === "ArrowLeft") {
        event.preventDefault();
        previousSlide();
    }

    if (event.key === "ArrowRight") {
        event.preventDefault();
        nextSlide();
    }
};

/* ============================================================
   TOUCH / SWIPE
============================================================= */

const touchStartX = ref<number | null>(null);
const touchCurrentX = ref<number | null>(null);

const handleTouchStart = (event: TouchEvent) => {
    if (!hasMultipleSlides.value) {
        return;
    }

    touchStartX.value = event.touches[0]?.clientX ?? null;
    touchCurrentX.value = touchStartX.value;
    isDragging.value = true;
};

const handleTouchMove = (event: TouchEvent) => {
    if (!hasMultipleSlides.value || touchStartX.value === null) {
        return;
    }

    touchCurrentX.value = event.touches[0]?.clientX ?? touchCurrentX.value;
};

const handleTouchEnd = () => {
    if (touchStartX.value === null || touchCurrentX.value === null) {
        isDragging.value = false;
        return;
    }

    const distance = touchCurrentX.value - touchStartX.value;

    const threshold = 50;

    if (Math.abs(distance) >= threshold) {
        if (distance < 0) {
            nextSlide();
        } else {
            previousSlide();
        }
    }

    touchStartX.value = null;
    touchCurrentX.value = null;
    isDragging.value = false;
};

/* ============================================================
   MOUSE DRAG
============================================================= */

const mouseStartX = ref<number | null>(null);

const handleMouseDown = (event: MouseEvent) => {
    if (!hasMultipleSlides.value) {
        return;
    }

    mouseStartX.value = event.clientX;
    isDragging.value = true;
};

const handleMouseUp = (event: MouseEvent) => {
    if (mouseStartX.value === null) {
        isDragging.value = false;
        return;
    }

    const distance = event.clientX - mouseStartX.value;

    if (Math.abs(distance) >= 60) {
        if (distance < 0) {
            nextSlide();
        } else {
            previousSlide();
        }
    }

    mouseStartX.value = null;
    isDragging.value = false;
};

const handleMouseLeave = () => {
    resumeAutoplay();
    mouseStartX.value = null;
    isDragging.value = false;
};

/* ============================================================
   HELPERS
============================================================= */

const getImageUrl = (gambar: string | null): string | null => {
    if (!gambar) {
        return null;
    }

    if (
        gambar.startsWith("http://") ||
        gambar.startsWith("https://") ||
        gambar.startsWith("/")
    ) {
        return gambar;
    }

    return `/storage/${gambar}`;
};

const formatLuas = (luas: string | number | null): string => {
    if (luas === null || luas === undefined || luas === "") {
        return "—";
    }

    const value = Number(luas);

    if (Number.isNaN(value)) {
        return String(luas);
    }

    return new Intl.NumberFormat("id-ID", {
        maximumFractionDigits: 2,
    }).format(value);
};

const formatTahun = (tahun: number | null): string => {
    return tahun ? String(tahun) : "—";
};

const formatLokasi = (lokasi: string | null): string => {
    return lokasi?.trim() || "—";
};

const currentImageUrl = computed(() => {
    return getImageUrl(currentKawasan.value?.gambar ?? null);
});

const currentLuas = computed(() => {
    return formatLuas(currentKawasan.value?.luas_kawasan ?? null);
});

const currentTahun = computed(() => {
    return formatTahun(currentKawasan.value?.tahun_berdiri ?? null);
});

const currentLokasi = computed(() => {
    return formatLokasi(currentKawasan.value?.lokasi ?? null);
});

const currentDeskripsi = computed(() => {
    return currentKawasan.value?.deskripsi?.trim() || "";
});

/* ============================================================
   LIFECYCLE
============================================================= */

onMounted(() => {
    window.addEventListener("keydown", handleKeydown);

    startAutoplay();
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
    <Head>
        <title>Profil Kawasan — Kawasan Industri Tanjung Buton</title>

        <meta
            name="description"
            content="Informasi Profil Kawasan Industri Tanjung Buton meliputi luas kawasan, lokasi, tahun berdiri, dan informasi pengembangan kawasan."
        />

        <meta
            name="keywords"
            content="Kawasan Industri Tanjung Buton, KITB, profil kawasan industri, kawasan industri Buton, Riau"
        />

        <meta
            property="og:title"
            content="Profil Kawasan — Kawasan Industri Tanjung Buton"
        />

        <meta
            property="og:description"
            content="Informasi Profil Kawasan Industri Tanjung Buton."
        />

        <meta property="og:type" content="website" />

        <meta
            property="og:url"
            content="https://tanjungbuton-industrial.co.id/kawasan/profil-kawasan"
        />

        <link
            rel="canonical"
            href="https://tanjungbuton-industrial.co.id/kawasan/profil-kawasan"
        />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- ====================================================
             DECORATIVE BLOBS
        ===================================================== -->

        <div
            aria-hidden="true"
            class="pointer-events-none absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-900/20"
        />

        <div
            aria-hidden="true"
            class="pointer-events-none absolute -right-32 top-[32rem] h-96 w-96 rounded-full bg-slate-200/60 blur-3xl dark:bg-slate-800/30"
        />

        <div
            aria-hidden="true"
            class="pointer-events-none absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-blue-100/30 blur-3xl dark:bg-blue-950/20"
        />

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- =================================================
                 BREADCRUMB
            ================================================== -->

            <div data-reveal class="mb-6" style="--d: 0ms">
                <nav
                    aria-label="Breadcrumb"
                    class="flex items-center gap-2 text-sm"
                >
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Home class="size-4 shrink-0" />

                        <span>Beranda</span>
                    </Link>

                    <ChevronRight class="size-4 shrink-0 text-slate-400" />

                    <Link
                        href="/kawasan/profil-kawasan"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Landmark class="size-4 shrink-0" />

                        <span>Kawasan Industri</span>
                    </Link>

                    <ChevronRight class="size-4 shrink-0 text-slate-400" />

                    <span
                        aria-current="page"
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                    >
                        <Landmark
                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                        />

                        <span>Profil Kawasan</span>
                    </span>
                </nav>
            </div>

            <!-- =================================================
                 EMPTY STATE
            ================================================== -->

            <section
                v-if="!profilKawasans.length"
                data-reveal
                class="py-20 text-center"
                style="--d: 80ms"
            >
                <div class="mx-auto flex max-w-lg flex-col items-center">
                    <div
                        class="flex size-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <Landmark class="size-8" />
                    </div>

                    <h1
                        class="mt-6 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        Profil kawasan belum tersedia
                    </h1>

                    <p
                        class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Informasi profil kawasan industri saat ini belum
                        tersedia untuk ditampilkan.
                    </p>

                    <Link
                        href="/"
                        class="mt-7 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600 dark:bg-white dark:text-slate-900 dark:hover:bg-blue-400"
                    >
                        <Home class="size-4" />

                        Kembali ke Beranda
                    </Link>
                </div>
            </section>

            <!-- =================================================
                 CONTENT
            ================================================== -->

            <template v-else>
                <!-- =================================================
                     SECTION HEADING
                ================================================== -->

                <section data-reveal class="mb-6" style="--d: 80ms">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <div
                                class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400"
                            >
                                <Globe2 class="size-4" />

                                Kawasan Industri
                            </div>

                            <h1
                                class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                            >
                                Profil Kawasan
                            </h1>

                            <p
                                class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                Jelajahi informasi kawasan industri melalui
                                profil dan karakteristik setiap kawasan yang
                                tersedia.
                            </p>
                        </div>

                        <div
                            class="inline-flex w-fit items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            {{ currentIndex + 1 }}
                            /
                            {{ totalSlides }}
                            Kawasan
                        </div>
                    </div>
                </section>

                <!-- =================================================
                     CAROUSEL
                ================================================== -->

                <section
                    data-reveal
                    class="relative"
                    style="--d: 120ms"
                    @mouseenter="pauseAutoplay"
                    @touchstart.passive="handleTouchStart"
                    @touchmove.passive="handleTouchMove"
                    @touchend="handleTouchEnd"
                    @mousedown="handleMouseDown"
                    @mouseup="handleMouseUp"
                    @mouseleave="handleMouseLeave"
                >
                    <div
                        class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <!-- Slide -->

                        <Transition name="kawasan-slide" mode="out-in">
                            <div
                                :key="currentKawasan?.id"
                                class="grid select-none lg:grid-cols-[1fr_1fr]"
                            >
                                <!-- Image -->

                                <div
                                    class="relative min-h-[300px] overflow-hidden bg-slate-100 sm:min-h-[420px] lg:min-h-[520px] dark:bg-slate-800"
                                >
                                    <img
                                        v-if="currentImageUrl"
                                        :src="currentImageUrl"
                                        :alt="currentKawasan?.judul"
                                        class="absolute inset-0 size-full object-cover"
                                        loading="eager"
                                        decoding="async"
                                        draggable="false"
                                    />

                                    <div
                                        v-else
                                        class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-blue-50 via-slate-100 to-slate-200 dark:from-blue-950/40 dark:via-slate-800 dark:to-slate-900"
                                    >
                                        <div
                                            class="flex size-24 items-center justify-center rounded-3xl bg-white/80 text-blue-600 shadow-lg backdrop-blur dark:bg-slate-900/70 dark:text-blue-400"
                                        >
                                            <Landmark class="size-12" />
                                        </div>
                                    </div>

                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-slate-950/50 via-transparent to-transparent"
                                    />

                                    <!-- Image Label -->

                                    <div
                                        class="absolute bottom-5 left-5 right-5 flex items-end justify-between gap-4"
                                    >
                                        <div
                                            class="rounded-xl border border-white/20 bg-slate-950/40 px-3 py-2 text-xs font-semibold text-white backdrop-blur-md"
                                        >
                                            Kawasan Industri
                                        </div>

                                        <div
                                            class="rounded-xl border border-white/20 bg-slate-950/40 px-3 py-2 text-xs font-medium text-white backdrop-blur-md"
                                        >
                                            {{ currentIndex + 1 }}
                                            /
                                            {{ totalSlides }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Content -->

                                <div
                                    class="flex flex-col justify-center p-7 sm:p-10 lg:p-12"
                                >
                                    <div
                                        class="inline-flex w-fit items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                                    >
                                        <Landmark class="size-3.5" />

                                        Profil Kawasan
                                    </div>

                                    <h2
                                        class="mt-5 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                                    >
                                        {{ currentKawasan?.judul }}
                                    </h2>

                                    <p
                                        v-if="currentDeskripsi"
                                        class="mt-5 line-clamp-5 text-sm leading-7 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ currentDeskripsi }}
                                    </p>

                                    <!-- Info -->

                                    <div
                                        class="mt-7 grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3"
                                    >
                                        <!-- Luas -->

                                        <div
                                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60"
                                        >
                                            <div
                                                class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                <Maximize2
                                                    class="size-4 text-blue-600 dark:text-blue-400"
                                                />

                                                Luas Kawasan
                                            </div>

                                            <p
                                                class="mt-2 text-lg font-bold text-slate-900 dark:text-white"
                                            >
                                                {{ currentLuas }}

                                                <span
                                                    v-if="
                                                        currentKawasan?.luas_kawasan !==
                                                        null
                                                    "
                                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                                                >
                                                    Ha
                                                </span>
                                            </p>
                                        </div>

                                        <!-- Lokasi -->

                                        <div
                                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60"
                                        >
                                            <div
                                                class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                <MapPin
                                                    class="size-4 text-blue-600 dark:text-blue-400"
                                                />

                                                Lokasi
                                            </div>

                                            <p
                                                class="mt-2 line-clamp-2 text-sm font-bold leading-5 text-slate-900 dark:text-white"
                                            >
                                                {{ currentLokasi }}
                                            </p>
                                        </div>

                                        <!-- Tahun -->

                                        <div
                                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60"
                                        >
                                            <div
                                                class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                <CalendarDays
                                                    class="size-4 text-blue-600 dark:text-blue-400"
                                                />

                                                Tahun Berdiri
                                            </div>

                                            <p
                                                class="mt-2 text-lg font-bold text-slate-900 dark:text-white"
                                            >
                                                {{ currentTahun }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Actions -->

                                    <div class="mt-8 flex flex-wrap gap-3">
                                        <Link
                                            href="/kawasan/peta-kawasan"
                                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                        >
                                            <MapPin class="size-4" />

                                            Lihat Peta Kawasan
                                        </Link>

                                        <a
                                            href="#informasi-lengkap"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:bg-blue-950/30 dark:hover:text-blue-300"
                                        >
                                            Detail

                                            <ChevronRight class="size-4" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </Transition>

                        <!-- Previous -->

                        <button
                            v-if="hasMultipleSlides"
                            type="button"
                            aria-label="Profil kawasan sebelumnya"
                            class="absolute left-3 top-1/2 z-20 flex size-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/50 bg-white/90 text-slate-700 shadow-lg backdrop-blur transition hover:scale-105 hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 sm:left-5 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-200 dark:hover:bg-slate-900"
                            @click.stop="previousSlide"
                        >
                            <ChevronLeft class="size-5" />
                        </button>

                        <!-- Next -->

                        <button
                            v-if="hasMultipleSlides"
                            type="button"
                            aria-label="Profil kawasan berikutnya"
                            class="absolute right-3 top-1/2 z-20 flex size-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/50 bg-white/90 text-slate-700 shadow-lg backdrop-blur transition hover:scale-105 hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 sm:right-5 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-200 dark:hover:bg-slate-900"
                            @click.stop="nextSlide"
                        >
                            <ChevronRight class="size-5" />
                        </button>
                    </div>

                    <!-- =================================================
                         DOTS
                    ================================================== -->

                    <div
                        v-if="hasMultipleSlides"
                        class="mt-5 flex items-center justify-center gap-2"
                    >
                        <button
                            v-for="(kawasan, index) in profilKawasans"
                            :key="kawasan.id"
                            type="button"
                            :aria-label="`Lihat ${kawasan.judul}`"
                            :aria-current="
                                index === currentIndex ? 'true' : undefined
                            "
                            class="group flex h-6 items-center justify-center"
                            @click="goToSlide(index)"
                        >
                            <span
                                class="block rounded-full transition-all duration-300"
                                :class="
                                    index === currentIndex
                                        ? 'h-2 w-8 bg-blue-600 dark:bg-blue-400'
                                        : 'size-2 bg-slate-300 group-hover:bg-slate-400 dark:bg-slate-700 dark:group-hover:bg-slate-600'
                                "
                            />
                        </button>
                    </div>
                </section>

                <!-- =================================================
                     FULL DESCRIPTION
                ================================================== -->

                <section
                    v-if="currentDeskripsi"
                    id="informasi-lengkap"
                    data-reveal
                    class="mt-10"
                    style="--d: 160ms"
                >
                    <div
                        class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-slate-100 px-6 py-5 sm:px-8 dark:border-slate-800"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <Building2 class="size-5" />
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400"
                                    >
                                        Informasi Lengkap
                                    </p>

                                    <h2
                                        class="text-xl font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ currentKawasan?.judul }}
                                    </h2>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-7 sm:px-8 sm:py-9">
                            <p
                                class="whitespace-pre-line text-[15px] leading-8 text-slate-600 dark:text-slate-300"
                            >
                                {{ currentDeskripsi }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- =================================================
                     CTA
                ================================================== -->

                <section
                    data-reveal
                    class="mt-10 overflow-hidden rounded-3xl border border-blue-100 bg-blue-50/70 dark:border-blue-900/50 dark:bg-blue-950/20"
                    style="--d: 200ms"
                >
                    <div
                        class="flex flex-col gap-6 p-7 sm:p-9 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div class="max-w-2xl">
                            <p
                                class="text-sm font-semibold text-blue-600 dark:text-blue-400"
                            >
                                Jelajahi KITB
                            </p>

                            <h2
                                class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                Kenali lebih jauh kawasan kami
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                Lihat infrastruktur, fasilitas, dan peta kawasan
                                untuk mendapatkan gambaran yang lebih lengkap.
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap gap-3">
                            <Link
                                href="/kawasan/infrastruktur"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600 dark:bg-white dark:text-slate-900 dark:hover:bg-blue-400"
                            >
                                Infrastruktur

                                <ChevronRight class="size-4" />
                            </Link>

                            <Link
                                href="/kawasan/fasilitas"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:text-blue-300"
                            >
                                Fasilitas

                                <ChevronRight class="size-4" />
                            </Link>
                        </div>
                    </div>
                </section>
            </template>
        </div>
    </main>
</template>

<style scoped>
/* ============================================================
   REVEAL
============================================================= */

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

/* ============================================================
   CAROUSEL TRANSITION
============================================================= */

.kawasan-slide-enter-active,
.kawasan-slide-leave-active {
    transition:
        opacity 450ms ease,
        transform 450ms ease;
}

.kawasan-slide-enter-from {
    opacity: 0;
    transform: translateX(28px);
}

.kawasan-slide-leave-to {
    opacity: 0;
    transform: translateX(-28px);
}

/* ============================================================
   REDUCED MOTION
============================================================= */

@media (prefers-reduced-motion: reduce) {
    [data-reveal] {
        opacity: 1;
        transform: none;
        transition: none;
    }

    .kawasan-slide-enter-active,
    .kawasan-slide-leave-active {
        transition: none;
    }
}
</style>
