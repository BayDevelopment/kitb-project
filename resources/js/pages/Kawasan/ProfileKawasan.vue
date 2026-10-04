<script setup lang="ts">
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";

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
    Pause,
    Play,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

/* ============================================================
   TYPES & PROPS
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

const kawasans = computed<ProfilKawasan[]>(() => {
    return Array.isArray(props.profilKawasans) ? props.profilKawasans : [];
});

/* ============================================================
   SKELETON
============================================================= */

// Jika data kosong, langsung tampilkan empty state tanpa skeleton
const isLoading = ref(kawasans.value.length > 0);

let skeletonTimer: ReturnType<typeof setTimeout> | null = null;

/* ============================================================
   REDUCED MOTION + REVEAL
============================================================= */

const prefersReducedMotion = ref(false);

let revealObserver: IntersectionObserver | null = null;
let mediaQuery: MediaQueryList | null = null;

const handleMotionChange = (event: MediaQueryListEvent) => {
    prefersReducedMotion.value = event.matches;
    scheduleAutoplay();
};

const setupReveal = () => {
    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (prefersReducedMotion.value) {
        elements.forEach((element) => element.classList.add("is-visible"));
        return;
    }

    revealObserver?.disconnect();

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
        { threshold: 0.08, rootMargin: "0px 0px -40px 0px" },
    );

    elements.forEach((element) => revealObserver?.observe(element));
};

/* ============================================================
   CAROUSEL STATE
============================================================= */

const AUTOPLAY_MS = 6500;

const currentIndex = ref(0);

const isHovering = ref(false);
const isFocusWithin = ref(false);
const isDragging = ref(false);
const userPaused = ref(false);

let autoplayTimer: ReturnType<typeof setTimeout> | null = null;

const totalSlides = computed(() => kawasans.value.length);

const currentKawasan = computed<ProfilKawasan | null>(() => {
    return kawasans.value[currentIndex.value] ?? null;
});

const hasMultipleSlides = computed(() => totalSlides.value > 1);

const autoplayEnabled = computed(() => {
    return (
        !prefersReducedMotion.value &&
        hasMultipleSlides.value &&
        !userPaused.value
    );
});

/* ============================================================
   AUTOPLAY
   (timer di-reset setiap pindah slide, jadi klik manual tidak
   langsung diikuti pindah otomatis)
============================================================= */

const clearAutoplay = () => {
    if (autoplayTimer) {
        clearTimeout(autoplayTimer);
        autoplayTimer = null;
    }
};

function scheduleAutoplay(): void {
    clearAutoplay();

    if (!autoplayEnabled.value) {
        return;
    }

    autoplayTimer = setTimeout(() => {
        const busy =
            isHovering.value || isFocusWithin.value || isDragging.value;

        if (busy) {
            scheduleAutoplay();
            return;
        }

        goToSlide(currentIndex.value + 1);
    }, AUTOPLAY_MS);
}

const toggleAutoplay = () => {
    userPaused.value = !userPaused.value;
    scheduleAutoplay();
};

/* ============================================================
   CAROUSEL ACTIONS
============================================================= */

function goToSlide(index: number): void {
    if (!totalSlides.value) {
        return;
    }

    currentIndex.value = (index + totalSlides.value) % totalSlides.value;

    scheduleAutoplay();
}

const nextSlide = () => goToSlide(currentIndex.value + 1);
const previousSlide = () => goToSlide(currentIndex.value - 1);

watch(totalSlides, (total) => {
    if (currentIndex.value >= total) {
        currentIndex.value = 0;
    }

    scheduleAutoplay();
});

/* ============================================================
   KEYBOARD (hanya saat carousel punya fokus)
============================================================= */

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

const handleFocusIn = () => {
    isFocusWithin.value = true;
};

const handleFocusOut = (event: FocusEvent) => {
    const container = event.currentTarget as HTMLElement | null;
    const next = event.relatedTarget as Node | null;

    if (!container || !next || !container.contains(next)) {
        isFocusWithin.value = false;
    }
};

/* ============================================================
   SWIPE / DRAG (pointer events untuk sentuh + mouse)
============================================================= */

let pointerStartX: number | null = null;
let pointerStartY: number | null = null;

const handlePointerDown = (event: PointerEvent) => {
    if (!hasMultipleSlides.value) {
        return;
    }

    // Abaikan klik mouse selain tombol kiri
    if (event.pointerType === "mouse" && event.button !== 0) {
        return;
    }

    // Jangan mulai swipe dari tombol / link
    if ((event.target as HTMLElement).closest("a, button")) {
        return;
    }

    pointerStartX = event.clientX;
    pointerStartY = event.clientY;
    isDragging.value = true;
};

const finishPointer = (event: PointerEvent, cancelled = false) => {
    if (pointerStartX === null || pointerStartY === null) {
        isDragging.value = false;
        return;
    }

    const dx = event.clientX - pointerStartX;
    const dy = event.clientY - pointerStartY;

    pointerStartX = null;
    pointerStartY = null;
    isDragging.value = false;

    if (cancelled) {
        return;
    }

    // Hanya geser horizontal yang jelas (bukan scroll vertikal)
    if (Math.abs(dx) >= 50 && Math.abs(dx) > Math.abs(dy) * 1.5) {
        if (dx < 0) {
            nextSlide();
        } else {
            previousSlide();
        }
    }
};

const handlePointerUp = (event: PointerEvent) => finishPointer(event);
const handlePointerCancel = (event: PointerEvent) => finishPointer(event, true);

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

    if (gambar.startsWith("storage/")) {
        return `/${gambar}`;
    }

    return `/storage/${gambar}`;
};

// Deskripsi longText bisa berisi HTML: ubah ke teks, jeda paragraf dijaga
const htmlToText = (value: string | null | undefined): string => {
    if (!value) {
        return "";
    }

    return value
        .replace(/<\s*br\s*\/?>/gi, "\n")
        .replace(/<\/(p|div|li|h[1-6])>/gi, "\n\n")
        .replace(/<[^>]*>/g, "")
        .replace(/&nbsp;/g, " ")
        .replace(/[ \t]+/g, " ")
        .replace(/\n{3,}/g, "\n\n")
        .trim();
};

const formatLuas = (
    luas: string | number | null | undefined,
): string | null => {
    if (luas === null || luas === undefined || luas === "") {
        return null;
    }

    const value = Number(luas);

    if (Number.isNaN(value)) {
        return String(luas);
    }

    return new Intl.NumberFormat("id-ID", {
        maximumFractionDigits: 2,
    }).format(value);
};

const currentImageUrl = computed(() =>
    getImageUrl(currentKawasan.value?.gambar ?? null),
);

const currentLuas = computed(() =>
    formatLuas(currentKawasan.value?.luas_kawasan),
);

const currentTahun = computed(() =>
    currentKawasan.value?.tahun_berdiri
        ? String(currentKawasan.value.tahun_berdiri)
        : null,
);

const currentLokasi = computed(
    () => currentKawasan.value?.lokasi?.trim() || null,
);

const currentDeskripsi = computed(() =>
    htmlToText(currentKawasan.value?.deskripsi),
);

// Tombol "Detail" hanya berguna jika deskripsi lebih panjang dari
// ringkasan di dalam slide (sekitar 3 baris).
const hasDetail = computed(() => currentDeskripsi.value.length > 220);

// Slug dikirim agar halaman peta bisa langsung fokus ke kawasan ini.
const petaUrl = computed(() => {
    const slug = currentKawasan.value?.slug;

    return slug
        ? `/kawasan/peta-kawasan?kawasan=${encodeURIComponent(slug)}`
        : "/kawasan/peta-kawasan";
});

/* ============================================================
   DETAIL (scroll ke Informasi Lengkap)
============================================================= */

const detailRef = ref<HTMLElement | null>(null);
const detailId = "informasi-lengkap";

const scrollToDetail = async () => {
    // Pengguna mulai membaca: hentikan autoplay agar isi tidak berganti
    userPaused.value = true;
    scheduleAutoplay();

    await nextTick();

    detailRef.value?.scrollIntoView({
        behavior: prefersReducedMotion.value ? "auto" : "smooth",
        block: "start",
    });

    detailRef.value?.focus({ preventScroll: true });
};

/* ============================================================
   LIFECYCLE
============================================================= */

onMounted(() => {
    mediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
    prefersReducedMotion.value = mediaQuery.matches;
    mediaQuery.addEventListener("change", handleMotionChange);

    setupReveal();

    if (!isLoading.value) {
        scheduleAutoplay();
        return;
    }

    skeletonTimer = setTimeout(async () => {
        isLoading.value = false;

        // Konten carousel baru dirender setelah skeleton hilang,
        // jadi observer reveal dipasang ulang dan autoplay baru dimulai.
        await nextTick();
        setupReveal();
        scheduleAutoplay();
    }, 450);
});

onBeforeUnmount(() => {
    if (skeletonTimer) {
        clearTimeout(skeletonTimer);
    }

    revealObserver?.disconnect();
    mediaQuery?.removeEventListener("change", handleMotionChange);
    clearAutoplay();
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
        <!-- Decorative blobs -->

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
                    class="flex flex-wrap items-center gap-2 text-sm"
                >
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Home class="size-4 shrink-0" aria-hidden="true" />

                        <span>Beranda</span>
                    </Link>

                    <ChevronRight
                        class="size-4 shrink-0 text-slate-400"
                        aria-hidden="true"
                    />

                    <!-- Bukan link: sebelumnya menaut ke halaman ini sendiri -->
                    <span
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 dark:text-slate-400"
                    >
                        <Landmark class="size-4 shrink-0" aria-hidden="true" />

                        <span>Kawasan Industri</span>
                    </span>

                    <ChevronRight
                        class="size-4 shrink-0 text-slate-400"
                        aria-hidden="true"
                    />

                    <span
                        aria-current="page"
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                    >
                        <Landmark
                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                            aria-hidden="true"
                        />

                        <span>Profil Kawasan</span>
                    </span>
                </nav>
            </div>

            <!-- =================================================
                 EMPTY STATE
            ================================================== -->

            <section
                v-if="!kawasans.length"
                data-reveal
                class="py-20 text-center"
                style="--d: 80ms"
            >
                <div class="mx-auto flex max-w-lg flex-col items-center">
                    <div
                        class="flex size-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <Landmark class="size-8" aria-hidden="true" />
                    </div>

                    <h1
                        class="mt-6 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        Profil kawasan belum tersedia
                    </h1>

                    <p
                        class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Informasi profil kawasan industri belum tersedia atau
                        sedang diperbarui. Silakan kembali lagi nanti.
                    </p>

                    <Link
                        href="/"
                        class="mt-7 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600 dark:bg-white dark:text-slate-900 dark:hover:bg-blue-400"
                    >
                        <Home class="size-4" aria-hidden="true" />

                        Kembali ke Beranda
                    </Link>
                </div>
            </section>

            <!-- =================================================
                 SKELETON
            ================================================== -->

            <section
                v-else-if="isLoading"
                aria-label="Memuat profil kawasan"
                aria-busy="true"
            >
                <div class="mb-6 space-y-3">
                    <div
                        class="skeleton-shimmer h-4 w-32 rounded bg-slate-200 dark:bg-slate-800"
                    />

                    <div
                        class="skeleton-shimmer h-9 w-64 rounded-lg bg-slate-200 dark:bg-slate-800"
                    />

                    <div
                        class="skeleton-shimmer h-4 w-full max-w-xl rounded bg-slate-200 dark:bg-slate-800"
                    />
                </div>

                <div
                    class="grid overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm lg:grid-cols-2 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="skeleton-shimmer min-h-[300px] bg-slate-200 sm:min-h-[420px] lg:min-h-[520px] dark:bg-slate-800"
                    />

                    <div class="space-y-5 p-7 sm:p-10 lg:p-12">
                        <div
                            class="skeleton-shimmer h-7 w-36 rounded-full bg-slate-200 dark:bg-slate-800"
                        />

                        <div
                            class="skeleton-shimmer h-9 w-3/4 rounded-lg bg-slate-200 dark:bg-slate-800"
                        />

                        <div class="space-y-2">
                            <div
                                class="skeleton-shimmer h-3.5 w-full rounded bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="skeleton-shimmer h-3.5 w-11/12 rounded bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="skeleton-shimmer h-3.5 w-2/3 rounded bg-slate-200 dark:bg-slate-800"
                            />
                        </div>

                        <div
                            class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3"
                        >
                            <div
                                v-for="index in 3"
                                :key="index"
                                class="skeleton-shimmer h-20 rounded-2xl bg-slate-200 dark:bg-slate-800"
                            />
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <div
                                class="skeleton-shimmer h-11 w-44 rounded-xl bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="skeleton-shimmer h-11 w-32 rounded-xl bg-slate-200 dark:bg-slate-800"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <!-- =================================================
                 CONTENT
            ================================================== -->

            <template v-else>
                <!-- Heading -->

                <section data-reveal class="mb-6" style="--d: 80ms">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <div
                                class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400"
                            >
                                <Globe2 class="size-4" aria-hidden="true" />

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

                        <div class="flex items-center gap-2">
                            <div
                                class="inline-flex w-fit items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                            >
                                {{ currentIndex + 1 }} / {{ totalSlides }}
                                Kawasan
                            </div>

                            <!-- Kontrol jeda autoplay -->
                            <button
                                v-if="
                                    hasMultipleSlides && !prefersReducedMotion
                                "
                                type="button"
                                :aria-label="
                                    userPaused
                                        ? 'Putar otomatis slide'
                                        : 'Jeda putar otomatis slide'
                                "
                                class="flex size-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:border-blue-200 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-blue-300"
                                @click="toggleAutoplay"
                            >
                                <Play
                                    v-if="userPaused"
                                    class="size-4"
                                    aria-hidden="true"
                                />
                                <Pause
                                    v-else
                                    class="size-4"
                                    aria-hidden="true"
                                />
                            </button>
                        </div>
                    </div>
                </section>

                <!-- =================================================
                     CAROUSEL
                ================================================== -->

                <section
                    data-reveal
                    class="relative touch-pan-y outline-none"
                    style="--d: 120ms"
                    role="region"
                    aria-roledescription="carousel"
                    aria-label="Profil kawasan"
                    tabindex="0"
                    @mouseenter="isHovering = true"
                    @mouseleave="isHovering = false"
                    @focusin="handleFocusIn"
                    @focusout="handleFocusOut"
                    @keydown="handleKeydown"
                    @pointerdown="handlePointerDown"
                    @pointerup="handlePointerUp"
                    @pointercancel="handlePointerCancel"
                >
                    <div
                        class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <Transition name="kawasan-slide" mode="out-in">
                            <div
                                :key="currentKawasan?.id"
                                class="grid lg:grid-cols-2"
                                role="group"
                                aria-roledescription="slide"
                                :aria-label="`${currentIndex + 1} dari ${totalSlides}`"
                                :aria-live="autoplayEnabled ? 'off' : 'polite'"
                            >
                                <!-- Image -->

                                <div
                                    class="relative min-h-[300px] overflow-hidden bg-slate-100 sm:min-h-[420px] lg:min-h-[520px] dark:bg-slate-800"
                                >
                                    <img
                                        v-if="currentImageUrl"
                                        :src="currentImageUrl"
                                        :alt="`Foto ${currentKawasan?.judul}`"
                                        class="absolute inset-0 size-full select-none object-cover"
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
                                            <Landmark
                                                class="size-12"
                                                aria-hidden="true"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/50 via-transparent to-transparent"
                                    />

                                    <div class="absolute bottom-5 left-5">
                                        <div
                                            class="rounded-xl border border-white/20 bg-slate-950/40 px-3 py-2 text-xs font-semibold text-white backdrop-blur-md"
                                        >
                                            Kawasan Industri
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
                                        <Landmark
                                            class="size-3.5"
                                            aria-hidden="true"
                                        />

                                        Profil Kawasan
                                    </div>

                                    <h2
                                        class="mt-5 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                                    >
                                        {{ currentKawasan?.judul }}
                                    </h2>

                                    <!-- Ringkasan (versi lengkap ada di "Informasi Lengkap") -->
                                    <p
                                        v-if="currentDeskripsi"
                                        class="mt-5 line-clamp-3 text-sm leading-7 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ currentDeskripsi }}
                                    </p>

                                    <!-- Info -->

                                    <dl
                                        class="mt-7 grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3"
                                    >
                                        <div
                                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60"
                                        >
                                            <dt
                                                class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                <Maximize2
                                                    class="size-4 text-blue-600 dark:text-blue-400"
                                                    aria-hidden="true"
                                                />

                                                Luas Kawasan
                                            </dt>

                                            <dd
                                                class="mt-2 text-lg font-bold text-slate-900 dark:text-white"
                                            >
                                                {{ currentLuas ?? "—" }}

                                                <span
                                                    v-if="currentLuas"
                                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                                                >
                                                    Ha
                                                </span>
                                            </dd>
                                        </div>

                                        <div
                                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60"
                                        >
                                            <dt
                                                class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                <MapPin
                                                    class="size-4 text-blue-600 dark:text-blue-400"
                                                    aria-hidden="true"
                                                />

                                                Lokasi
                                            </dt>

                                            <dd
                                                class="mt-2 line-clamp-2 text-sm font-bold leading-5 text-slate-900 dark:text-white"
                                            >
                                                {{ currentLokasi ?? "—" }}
                                            </dd>
                                        </div>

                                        <div
                                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60"
                                        >
                                            <dt
                                                class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400"
                                            >
                                                <CalendarDays
                                                    class="size-4 text-blue-600 dark:text-blue-400"
                                                    aria-hidden="true"
                                                />

                                                Tahun Berdiri
                                            </dt>

                                            <dd
                                                class="mt-2 text-lg font-bold text-slate-900 dark:text-white"
                                            >
                                                {{ currentTahun ?? "—" }}
                                            </dd>
                                        </div>
                                    </dl>

                                    <!-- Actions -->

                                    <div class="mt-8 flex flex-wrap gap-3">
                                        <Link
                                            :href="petaUrl"
                                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                        >
                                            <MapPin
                                                class="size-4"
                                                aria-hidden="true"
                                            />

                                            Lihat Peta Kawasan
                                        </Link>

                                        <button
                                            v-if="hasDetail"
                                            type="button"
                                            :aria-controls="detailId"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:bg-blue-950/30 dark:hover:text-blue-300 dark:focus:ring-offset-slate-900"
                                            @click="scrollToDetail"
                                        >
                                            Baca Detail

                                            <ChevronRight
                                                class="size-4"
                                                aria-hidden="true"
                                            />
                                        </button>
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
                            @click="previousSlide"
                        >
                            <ChevronLeft class="size-5" aria-hidden="true" />
                        </button>

                        <!-- Next -->

                        <button
                            v-if="hasMultipleSlides"
                            type="button"
                            aria-label="Profil kawasan berikutnya"
                            class="absolute right-3 top-1/2 z-20 flex size-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/50 bg-white/90 text-slate-700 shadow-lg backdrop-blur transition hover:scale-105 hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 sm:right-5 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-200 dark:hover:bg-slate-900"
                            @click="nextSlide"
                        >
                            <ChevronRight class="size-5" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Dots -->

                    <div
                        v-if="hasMultipleSlides"
                        class="mt-5 flex items-center justify-center gap-2"
                    >
                        <button
                            v-for="(kawasan, index) in kawasans"
                            :key="kawasan.id"
                            type="button"
                            :aria-label="`Lihat ${kawasan.judul}`"
                            :aria-current="
                                index === currentIndex ? 'true' : undefined
                            "
                            class="group flex h-6 items-center justify-center focus:outline-none"
                            @click="goToSlide(index)"
                        >
                            <span
                                class="block rounded-full transition-all duration-300 group-focus-visible:ring-2 group-focus-visible:ring-blue-500 group-focus-visible:ring-offset-2"
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
                     INFORMASI LENGKAP
                ================================================== -->

                <section
                    v-if="hasDetail"
                    :id="detailId"
                    ref="detailRef"
                    data-reveal
                    class="mt-10 scroll-mt-24 outline-none"
                    style="--d: 160ms"
                    tabindex="-1"
                    aria-live="polite"
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
                                    <Building2
                                        class="size-5"
                                        aria-hidden="true"
                                    />
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

                            <div
                                class="mt-8 flex flex-wrap gap-3 border-t border-slate-100 pt-6 dark:border-slate-800"
                            >
                                <Link
                                    :href="petaUrl"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                >
                                    <MapPin class="size-4" aria-hidden="true" />

                                    Lihat Peta Kawasan
                                </Link>
                            </div>
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

                                <ChevronRight
                                    class="size-4"
                                    aria-hidden="true"
                                />
                            </Link>

                            <Link
                                href="/kawasan/fasilitas"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:text-blue-300"
                            >
                                Fasilitas

                                <ChevronRight
                                    class="size-4"
                                    aria-hidden="true"
                                />
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

/* ============================================================
   SKELETON SHIMMER
============================================================= */

.skeleton-shimmer {
    position: relative;
    overflow: hidden;
}

.skeleton-shimmer::after {
    position: absolute;
    inset: 0;
    content: "";
    transform: translateX(-100%);
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.55),
        transparent
    );
    animation: skeleton-shimmer 1.35s infinite;
}

@keyframes skeleton-shimmer {
    100% {
        transform: translateX(100%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .skeleton-shimmer::after {
        animation: none;
    }
}
</style>
