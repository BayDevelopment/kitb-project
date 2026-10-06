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
import { currentLanguage, type LanguageCode } from "@/composables/useLocale";

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
    deskripsi_en: string | null;
    deskripsi_zh: string | null;

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
   LOCALIZATION
============================================================= */

type LocalizedField = "deskripsi";

const getLocalizedValue = (
    item: ProfilKawasan | null | undefined,
    field: LocalizedField,
): string => {
    if (!item) {
        return "";
    }

    const language = currentLanguage.value as LanguageCode;

    if (field === "deskripsi") {
        if (language === "en") {
            return item.deskripsi_en?.trim() || item.deskripsi?.trim() || "";
        }

        if (language === "zh") {
            return item.deskripsi_zh?.trim() || item.deskripsi?.trim() || "";
        }

        return item.deskripsi?.trim() || "";
    }

    return "";
};

const translations = computed(() => {
    const language = currentLanguage.value as LanguageCode;

    const data: Record<
        LanguageCode,
        {
            home: string;
            industrialArea: string;
            profile: string;
            industrialAreaProfile: string;
            exploreDescription: string;
            areas: string;
            playAutoplay: string;
            pauseAutoplay: string;
            previous: string;
            next: string;
            photo: string;
            areaProfile: string;
            areaSize: string;
            location: string;
            established: string;
            hectare: string;
            viewMap: string;
            readDetail: string;
            fullInformation: string;
            exploreKitb: string;
            knowMore: string;
            infrastructure: string;
            facilities: string;
            emptyTitle: string;
            emptyDescription: string;
            backHome: string;
            loading: string;
            slideOf: string;
            view: string;
        }
    > = {
        id: {
            home: "Beranda",
            industrialArea: "Kawasan Industri",
            profile: "Profil Kawasan",
            industrialAreaProfile: "Profil Kawasan Industri",
            exploreDescription:
                "Jelajahi informasi kawasan industri melalui profil dan karakteristik setiap kawasan yang tersedia.",
            areas: "Kawasan",
            playAutoplay: "Putar otomatis slide",
            pauseAutoplay: "Jeda putar otomatis slide",
            previous: "Profil kawasan sebelumnya",
            next: "Profil kawasan berikutnya",
            photo: "Foto",
            areaProfile: "Profil Kawasan",
            areaSize: "Luas Kawasan",
            location: "Lokasi",
            established: "Tahun Berdiri",
            hectare: "Ha",
            viewMap: "Lihat Peta Kawasan",
            readDetail: "Baca Detail",
            fullInformation: "Informasi Lengkap",
            exploreKitb: "Jelajahi KITB",
            knowMore: "Kenali lebih jauh kawasan kami",
            infrastructure: "Infrastruktur",
            facilities: "Fasilitas",
            emptyTitle: "Profil kawasan belum tersedia",
            emptyDescription:
                "Informasi profil kawasan industri belum tersedia atau sedang diperbarui. Silakan kembali lagi nanti.",
            backHome: "Kembali ke Beranda",
            loading: "Memuat profil kawasan",
            slideOf: "dari",
            view: "Lihat",
        },

        en: {
            home: "Home",
            industrialArea: "Industrial Area",
            profile: "Area Profile",
            industrialAreaProfile: "Industrial Area Profile",
            exploreDescription:
                "Explore industrial area information through the profile and characteristics of each available area.",
            areas: "Areas",
            playAutoplay: "Play slides automatically",
            pauseAutoplay: "Pause automatic slides",
            previous: "Previous industrial area profile",
            next: "Next industrial area profile",
            photo: "Photo",
            areaProfile: "Area Profile",
            areaSize: "Area Size",
            location: "Location",
            established: "Established",
            hectare: "Ha",
            viewMap: "View Area Map",
            readDetail: "Read Details",
            fullInformation: "Full Information",
            exploreKitb: "Explore KITB",
            knowMore: "Learn more about our industrial area",
            infrastructure: "Infrastructure",
            facilities: "Facilities",
            emptyTitle: "Area profile is not available",
            emptyDescription:
                "Industrial area profile information is currently unavailable or being updated. Please check back later.",
            backHome: "Back to Home",
            loading: "Loading area profile",
            slideOf: "of",
            view: "View",
        },

        zh: {
            home: "首页",
            industrialArea: "工业园区",
            profile: "园区概况",
            industrialAreaProfile: "工业园区概况",
            exploreDescription:
                "通过园区概况和各园区的主要特点，了解更多工业园区信息。",
            areas: "园区",
            playAutoplay: "播放幻灯片",
            pauseAutoplay: "暂停幻灯片",
            previous: "上一个园区",
            next: "下一个园区",
            photo: "照片",
            areaProfile: "园区概况",
            areaSize: "园区面积",
            location: "位置",
            established: "成立年份",
            hectare: "公顷",
            viewMap: "查看园区地图",
            readDetail: "阅读详情",
            fullInformation: "详细信息",
            exploreKitb: "探索 KITB",
            knowMore: "进一步了解我们的园区",
            infrastructure: "基础设施",
            facilities: "设施",
            emptyTitle: "暂无园区概况",
            emptyDescription:
                "工业园区概况信息暂不可用或正在更新，请稍后再回来查看。",
            backHome: "返回首页",
            loading: "正在加载园区概况",
            slideOf: "共",
            view: "查看",
        },
    };

    return data[language] ?? data.id;
});

/* ============================================================
   SKELETON
============================================================= */

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
        {
            threshold: 0.08,
            rootMargin: "0px 0px -40px 0px",
        },
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
   LANGUAGE CHANGE
============================================================= */

watch(
    () => currentLanguage.value,
    async () => {
        await nextTick();
        setupReveal();
    },
);

/* ============================================================
   KEYBOARD
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
   SWIPE / DRAG
============================================================= */

let pointerStartX: number | null = null;
let pointerStartY: number | null = null;

const handlePointerDown = (event: PointerEvent) => {
    if (!hasMultipleSlides.value) {
        return;
    }

    if (event.pointerType === "mouse" && event.button !== 0) {
        return;
    }

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

/*
 * HTML → plain text.
 * Jeda paragraf tetap dipertahankan.
 */
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

    const locale =
        currentLanguage.value === "zh"
            ? "zh-CN"
            : currentLanguage.value === "en"
              ? "en-US"
              : "id-ID";

    return new Intl.NumberFormat(locale, {
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
    htmlToText(getLocalizedValue(currentKawasan.value, "deskripsi")),
);

const hasDetail = computed(() => currentDeskripsi.value.length > 220);

const petaUrl = computed(() => {
    const slug = currentKawasan.value?.slug;

    return slug
        ? `/kawasan/peta-kawasan?kawasan=${encodeURIComponent(slug)}`
        : "/kawasan/peta-kawasan";
});

/* ============================================================
   SEO
============================================================= */

const seoTitle = computed(() => {
    const language = currentLanguage.value as LanguageCode;

    if (language === "en") {
        return "Industrial Area Profile — Tanjung Buton Industrial Area";
    }

    if (language === "zh") {
        return "工业园区概况 — 丹戎布顿工业园区";
    }

    return "Profil Kawasan — Kawasan Industri Tanjung Buton";
});

const seoDescription = computed(() => {
    const language = currentLanguage.value as LanguageCode;

    if (language === "en") {
        return "Information about the Tanjung Buton Industrial Area, including area size, location, establishment year, and industrial area development.";
    }

    if (language === "zh") {
        return "了解丹戎布顿工业园区，包括园区面积、位置、成立年份以及园区发展信息。";
    }

    return "Informasi Profil Kawasan Industri Tanjung Buton meliputi luas kawasan, lokasi, tahun berdiri, dan informasi pengembangan kawasan.";
});

const seoKeywords = computed(() => {
    const language = currentLanguage.value as LanguageCode;

    if (language === "en") {
        return "Tanjung Buton Industrial Area, KITB, industrial area profile, industrial estate, Riau";
    }

    if (language === "zh") {
        return "丹戎布顿工业园区, KITB, 工业园区概况, 工业园区, 廖内";
    }

    return "Kawasan Industri Tanjung Buton, KITB, profil kawasan industri, kawasan industri Buton, Riau";
});

/* ============================================================
   DETAIL
============================================================= */

const detailRef = ref<HTMLElement | null>(null);
const detailId = "informasi-lengkap";

const scrollToDetail = async () => {
    userPaused.value = true;
    scheduleAutoplay();

    await nextTick();

    detailRef.value?.scrollIntoView({
        behavior: prefersReducedMotion.value ? "auto" : "smooth",
        block: "start",
    });

    detailRef.value?.focus({
        preventScroll: true,
    });
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
        <title>{{ seoTitle }}</title>

        <meta name="description" :content="seoDescription" />

        <meta name="keywords" :content="seoKeywords" />

        <meta property="og:title" :content="seoTitle" />

        <meta property="og:description" :content="seoDescription" />

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
            <!-- Breadcrumb -->
            <div data-reveal class="mb-6" style="--d: 0ms">
                <nav
                    :aria-label="translations.profile"
                    class="flex flex-wrap items-center gap-2 text-sm"
                >
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Home class="size-4 shrink-0" aria-hidden="true" />
                        <span>
                            {{ translations.home }}
                        </span>
                    </Link>

                    <ChevronRight
                        class="size-4 shrink-0 text-slate-400"
                        aria-hidden="true"
                    />

                    <span
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 dark:text-slate-400"
                    >
                        <Landmark class="size-4 shrink-0" aria-hidden="true" />
                        <span>
                            {{ translations.industrialArea }}
                        </span>
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

                        <span>
                            {{ translations.profile }}
                        </span>
                    </span>
                </nav>
            </div>

            <!-- Empty -->
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
                        {{ translations.emptyTitle }}
                    </h1>

                    <p
                        class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ translations.emptyDescription }}
                    </p>

                    <Link
                        href="/"
                        class="mt-7 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600 dark:bg-white dark:text-slate-900 dark:hover:bg-blue-400"
                    >
                        <Home class="size-4" aria-hidden="true" />

                        {{ translations.backHome }}
                    </Link>
                </div>
            </section>

            <!-- Skeleton -->
            <section
                v-else-if="isLoading"
                :aria-label="translations.loading"
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

            <!-- Content -->
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

                                {{ translations.industrialArea }}
                            </div>

                            <h1
                                class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                            >
                                {{ translations.profile }}
                            </h1>

                            <p
                                class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{ translations.exploreDescription }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <div
                                class="inline-flex w-fit items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                            >
                                {{ currentIndex + 1 }}
                                /
                                {{ totalSlides }}
                                {{ translations.areas }}
                            </div>

                            <button
                                v-if="
                                    hasMultipleSlides && !prefersReducedMotion
                                "
                                type="button"
                                :aria-label="
                                    userPaused
                                        ? translations.playAutoplay
                                        : translations.pauseAutoplay
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

                <!-- Carousel -->
                <section
                    data-reveal
                    class="relative touch-pan-y outline-none"
                    style="--d: 120ms"
                    role="region"
                    aria-roledescription="carousel"
                    :aria-label="translations.profile"
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
                                :aria-label="`${currentIndex + 1} ${translations.slideOf} ${totalSlides}`"
                                :aria-live="autoplayEnabled ? 'off' : 'polite'"
                            >
                                <!-- Image -->
                                <div
                                    class="relative min-h-[300px] overflow-hidden bg-slate-100 sm:min-h-[420px] lg:min-h-[520px] dark:bg-slate-800"
                                >
                                    <img
                                        v-if="currentImageUrl"
                                        :src="currentImageUrl"
                                        :alt="`${translations.photo} ${currentKawasan?.judul}`"
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
                                            {{ translations.industrialArea }}
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

                                        {{ translations.areaProfile }}
                                    </div>

                                    <h2
                                        class="mt-5 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                                    >
                                        {{ currentKawasan?.judul }}
                                    </h2>

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

                                                {{ translations.areaSize }}
                                            </dt>

                                            <dd
                                                class="mt-2 text-lg font-bold text-slate-900 dark:text-white"
                                            >
                                                {{ currentLuas ?? "—" }}

                                                <span
                                                    v-if="currentLuas"
                                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                                                >
                                                    {{ translations.hectare }}
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

                                                {{ translations.location }}
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

                                                {{ translations.established }}
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

                                            {{ translations.viewMap }}
                                        </Link>

                                        <button
                                            v-if="hasDetail"
                                            type="button"
                                            :aria-controls="detailId"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:bg-blue-950/30 dark:hover:text-blue-300 dark:focus:ring-offset-slate-900"
                                            @click="scrollToDetail"
                                        >
                                            {{ translations.readDetail }}

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
                            :aria-label="translations.previous"
                            class="absolute left-3 top-1/2 z-20 flex size-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/50 bg-white/90 text-slate-700 shadow-lg backdrop-blur transition hover:scale-105 hover:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 sm:left-5 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-200 dark:hover:bg-slate-900"
                            @click="previousSlide"
                        >
                            <ChevronLeft class="size-5" aria-hidden="true" />
                        </button>

                        <!-- Next -->
                        <button
                            v-if="hasMultipleSlides"
                            type="button"
                            :aria-label="translations.next"
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
                            :aria-label="`${translations.view} ${kawasan.judul}`"
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

                <!-- Informasi Lengkap -->
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
                                        {{ translations.fullInformation }}
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

                                    {{ translations.viewMap }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- CTA -->
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
                                {{ translations.exploreKitb }}
                            </p>

                            <h2
                                class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ translations.knowMore }}
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    currentLanguage === "en"
                                        ? "Explore infrastructure, facilities, and the area map to get a more complete overview."
                                        : currentLanguage === "zh"
                                          ? "查看基础设施、园区设施和园区地图，以获得更完整的园区信息。"
                                          : "Lihat infrastruktur, fasilitas, dan peta kawasan untuk mendapatkan gambaran yang lebih lengkap."
                                }}
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap gap-3">
                            <Link
                                href="/kawasan/infrastruktur"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-600 dark:bg-white dark:text-slate-900 dark:hover:bg-blue-400"
                            >
                                {{ translations.infrastructure }}

                                <ChevronRight
                                    class="size-4"
                                    aria-hidden="true"
                                />
                            </Link>

                            <Link
                                href="/kawasan/fasilitas"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:text-blue-300"
                            >
                                {{ translations.facilities }}

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
