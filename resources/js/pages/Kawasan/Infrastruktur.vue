<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";

import { Head, Link } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";

import {
    Building2,
    ChevronLeft,
    ChevronRight,
    Construction,
    Home,
    Map,
    MapPin,
    Maximize2,
    ShieldCheck,
    X,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";
import { localizedValue } from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
   Props
   ========================================================= */

interface Infrastruktur {
    id: number;

    nama: string;
    nama_en: string | null;
    nama_zh: string | null;

    slug: string;

    deskripsi: string | null;
    deskripsi_en: string | null;
    deskripsi_zh: string | null;

    gambar: string | null;
    urutan: number;
}

const props = defineProps<{
    infrastrukturnya?: Infrastruktur[];
    infrastrukturs: Infrastruktur[];
}>();

/* =========================================================
   Normalisasi props
   ========================================================= */

const infrastrukturs = computed<Infrastruktur[]>(() => {
    return Array.isArray(props.infrastrukturs)
        ? props.infrastrukturs
        : Array.isArray(props.infrastrukturnya)
          ? props.infrastrukturnya
          : [];
});

/* =========================================================
   Reveal animation
   ========================================================= */

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const revealObserver = ref<IntersectionObserver | null>(null);

onMounted(() => {
    if (prefersReducedMotion) {
        document
            .querySelectorAll<HTMLElement>("[data-reveal]")
            .forEach((element) => {
                element.classList.add("is-visible");
            });

        return;
    }

    revealObserver.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    revealObserver.value?.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.08,
            rootMargin: "0px 0px -40px 0px",
        },
    );

    document
        .querySelectorAll<HTMLElement>("[data-reveal]")
        .forEach((element) => {
            revealObserver.value?.observe(element);
        });
});

onBeforeUnmount(() => {
    revealObserver.value?.disconnect();
});

/* =========================================================
   Carousel
   ========================================================= */

const currentIndex = ref(0);
const isPaused = ref(false);
const isDragging = ref(false);

let autoplayTimer: ReturnType<typeof setInterval> | null = null;

const totalSlides = computed(() => infrastrukturs.value.length);

const currentInfrastruktur = computed(
    () => infrastrukturs.value[currentIndex.value] ?? null,
);

const hasMultipleSlides = computed(() => totalSlides.value > 1);

/* =========================================================
   Navigation
   ========================================================= */

const goToSlide = (index: number) => {
    if (!totalSlides.value) return;

    if (index < 0) {
        currentIndex.value = totalSlides.value - 1;
        return;
    }

    if (index >= totalSlides.value) {
        currentIndex.value = 0;
        return;
    }

    currentIndex.value = index;
};

const nextSlide = () => {
    if (!hasMultipleSlides.value) return;

    goToSlide(currentIndex.value + 1);
    restartAutoplay();
};

const previousSlide = () => {
    if (!hasMultipleSlides.value) return;

    goToSlide(currentIndex.value - 1);
    restartAutoplay();
};

/* =========================================================
   Autoplay
   ========================================================= */

const startAutoplay = () => {
    if (prefersReducedMotion || !hasMultipleSlides.value || isPaused.value) {
        return;
    }

    if (autoplayTimer) {
        clearInterval(autoplayTimer);
    }

    autoplayTimer = setInterval(() => {
        if (!isPaused.value) {
            goToSlide(currentIndex.value + 1);
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
    stopAutoplay();
};

const resumeAutoplay = () => {
    isPaused.value = false;
    startAutoplay();
};

const restartAutoplay = () => {
    stopAutoplay();
    startAutoplay();
};

onMounted(() => {
    startAutoplay();
});

onBeforeUnmount(() => {
    stopAutoplay();
});

/* =========================================================
   Keyboard navigation
   ========================================================= */

const handleKeydown = (event: KeyboardEvent) => {
    if (!hasMultipleSlides.value) return;

    if (event.key === "ArrowLeft") {
        event.preventDefault();
        previousSlide();
    }

    if (event.key === "ArrowRight") {
        event.preventDefault();
        nextSlide();
    }
};

/* =========================================================
   Touch / Mouse drag
   ========================================================= */

const dragStartX = ref<number | null>(null);
const dragCurrentX = ref<number | null>(null);

const handlePointerDown = (event: PointerEvent) => {
    if (!hasMultipleSlides.value) return;

    dragStartX.value = event.clientX;
    dragCurrentX.value = event.clientX;
    isDragging.value = true;

    pauseAutoplay();
};

const handlePointerMove = (event: PointerEvent) => {
    if (!isDragging.value) return;

    dragCurrentX.value = event.clientX;
};

const handlePointerUp = () => {
    if (!isDragging.value) return;

    const start = dragStartX.value;
    const current = dragCurrentX.value;

    isDragging.value = false;
    dragStartX.value = null;
    dragCurrentX.value = null;

    if (start === null || current === null) {
        resumeAutoplay();
        return;
    }

    const distance = current - start;

    if (Math.abs(distance) > 55) {
        if (distance > 0) {
            goToSlide(currentIndex.value - 1);
        } else {
            goToSlide(currentIndex.value + 1);
        }
    }

    resumeAutoplay();
};

const handlePointerCancel = () => {
    isDragging.value = false;
    dragStartX.value = null;
    dragCurrentX.value = null;

    resumeAutoplay();
};

/* =========================================================
   Helpers
   ========================================================= */

const getImageUrl = (gambar: string | null): string | null => {
    if (!gambar) return null;

    if (
        gambar.startsWith("http://") ||
        gambar.startsWith("https://") ||
        gambar.startsWith("/")
    ) {
        return gambar;
    }

    return `/storage/${gambar}`;
};

/**
 * Nama & deskripsi mengikuti bahasa aktif (useLocale),
 * fallback otomatis ke Bahasa Indonesia jika terjemahan kosong.
 */
const getLocalizedName = (item: Infrastruktur | null): string => {
    if (!item) {
        return trans("infrastruktur.public.image_alt_fallback");
    }

    return (
        localizedValue(item as unknown as Record<string, unknown>, "nama") ||
        trans("infrastruktur.public.image_alt_fallback")
    );
};

const getLocalizedDescription = (item: Infrastruktur | null): string => {
    if (!item) {
        return trans("infrastruktur.public.description_fallback");
    }

    return (
        localizedValue(
            item as unknown as Record<string, unknown>,
            "deskripsi",
        ).trim() || trans("infrastruktur.public.description_fallback")
    );
};

const truncateText = (text: string | null, maxLength = 220): string => {
    if (!text) {
        return trans("infrastruktur.public.description_fallback");
    }

    const clean = text.replace(/\s+/g, " ").trim();

    if (clean.length <= maxLength) {
        return clean;
    }

    return `${clean.slice(0, maxLength).trim()}…`;
};

const currentImage = computed(() =>
    currentInfrastruktur.value
        ? getImageUrl(currentInfrastruktur.value.gambar)
        : null,
);

const currentName = computed(() =>
    getLocalizedName(currentInfrastruktur.value),
);

const currentDescription = computed(() =>
    truncateText(getLocalizedDescription(currentInfrastruktur.value)),
);

const currentNumber = computed(() => {
    if (!totalSlides.value) return "00";

    return String(currentIndex.value + 1).padStart(2, "0");
});

const totalNumber = computed(() => {
    return String(totalSlides.value).padStart(2, "0");
});

/* =========================================================
   Full image preview
   ========================================================= */

const isImagePreviewOpen = ref(false);

const openImagePreview = () => {
    if (!currentImage.value) return;

    isImagePreviewOpen.value = true;
    pauseAutoplay();
};

const closeImagePreview = () => {
    isImagePreviewOpen.value = false;
    resumeAutoplay();
};

const handlePreviewKeydown = (event: KeyboardEvent) => {
    if (event.key === "Escape") {
        closeImagePreview();
    }
};
</script>

<template>
    <Head>
        <title>{{ trans("infrastruktur.title") }} | KITB</title>

        <meta
            name="description"
            :content="trans('infrastruktur.public.hero_description')"
        />

        <meta name="robots" content="index, follow" />

        <link
            rel="canonical"
            href="https://tanjungbuton-industrial.co.id/kawasan/infrastruktur"
        />

        <meta
            property="og:title"
            :content="`${trans('infrastruktur.title')} | KITB`"
        />

        <meta
            property="og:description"
            :content="trans('infrastruktur.public.hero_description')"
        />

        <meta
            property="og:url"
            content="https://tanjungbuton-industrial.co.id/kawasan/infrastruktur"
        />

        <meta property="og:type" content="website" />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 text-slate-900 dark:bg-slate-950 dark:text-slate-100"
    >
        <!-- =====================================================
             Decorative background
        ====================================================== -->

        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 overflow-hidden"
        >
            <div
                class="absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-500/10 blur-3xl dark:bg-blue-500/10"
            />

            <div
                class="absolute right-[-8rem] top-[28rem] h-96 w-96 rounded-full bg-sky-400/10 blur-3xl dark:bg-sky-500/10"
            />

            <div
                class="absolute bottom-[-10rem] left-[35%] h-96 w-96 rounded-full bg-indigo-500/5 blur-3xl dark:bg-indigo-500/10"
            />

            <div
                class="absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-blue-50/80 to-transparent dark:from-blue-950/20 dark:to-transparent"
            />
        </div>

        <div
            class="relative mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12"
        >
            <!-- =================================================
                 Breadcrumb
            ================================================== -->

            <div data-reveal class="mb-6" style="--d: 0ms">
                <nav
                    :aria-label="
                        trans('infrastruktur.public.breadcrumb_current')
                    "
                    class="flex flex-wrap items-center gap-2 text-sm"
                >
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Home class="size-4 shrink-0" />

                        <span>
                            {{ trans("infrastruktur.public.breadcrumb_home") }}
                        </span>
                    </Link>

                    <ChevronRight class="size-4 shrink-0 text-slate-400" />

                    <Link
                        href="/kawasan/profil-kawasan"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Building2 class="size-4 shrink-0" />

                        <span>
                            {{ trans("infrastruktur.public.breadcrumb_area") }}
                        </span>
                    </Link>

                    <ChevronRight class="size-4 shrink-0 text-slate-400" />

                    <span
                        aria-current="page"
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                    >
                        <Construction
                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                        />

                        <span>
                            {{
                                trans("infrastruktur.public.breadcrumb_current")
                            }}
                        </span>
                    </span>
                </nav>
            </div>

            <!-- =================================================
                 Hero
            ================================================== -->

            <section data-reveal class="mb-8 max-w-3xl" style="--d: 80ms">
                <div
                    class="mb-4 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-white/80 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-blue-700 shadow-sm backdrop-blur dark:border-blue-900/60 dark:bg-slate-900/70 dark:text-blue-300"
                >
                    <span
                        class="size-1.5 rounded-full bg-blue-600 dark:bg-blue-400"
                    />

                    {{ trans("infrastruktur.public.hero_badge") }}
                </div>

                <h1
                    class="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl lg:text-5xl dark:text-white"
                >
                    {{ trans("infrastruktur.public.hero_title") }}

                    <span class="block text-blue-600 dark:text-blue-400">
                        {{ trans("infrastruktur.public.hero_title_highlight") }}
                    </span>
                </h1>

                <p
                    class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base dark:text-slate-400"
                >
                    {{ trans("infrastruktur.public.hero_description") }}
                </p>
            </section>

            <!-- =================================================
                 Empty State
            ================================================== -->

            <section
                v-if="!infrastrukturs.length"
                data-reveal
                class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
                style="--d: 140ms"
            >
                <div class="mx-auto max-w-xl text-center">
                    <div
                        class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                    >
                        <Construction class="size-8" />
                    </div>

                    <h2
                        class="mt-5 text-xl font-bold text-slate-900 dark:text-white"
                    >
                        {{ trans("infrastruktur.public.empty_title") }}
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ trans("infrastruktur.public.empty_description") }}
                    </p>

                    <Link
                        href="/kawasan/profil-kawasan"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700"
                    >
                        <Building2 class="size-4" />

                        {{ trans("infrastruktur.public.view_area_profile") }}
                    </Link>
                </div>
            </section>

            <!-- =================================================
                 Carousel
            ================================================== -->

            <section v-else data-reveal class="relative" style="--d: 140ms">
                <!-- Carousel header -->

                <div
                    class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <div
                            class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
                        >
                            <span
                                class="size-2 rounded-full bg-blue-600 dark:bg-blue-400"
                            />

                            {{ trans("infrastruktur.public.section_label") }}
                        </div>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            {{ totalSlides }}
                            {{ trans("infrastruktur.public.available") }}
                        </p>
                    </div>

                    <div
                        v-if="hasMultipleSlides"
                        class="flex items-center gap-2 self-start sm:self-auto"
                    >
                        <span
                            class="font-mono text-sm font-semibold text-slate-700 dark:text-slate-300"
                        >
                            {{ currentNumber }}
                        </span>

                        <span class="text-slate-400"> / </span>

                        <span
                            class="font-mono text-sm text-slate-500 dark:text-slate-500"
                        >
                            {{ totalNumber }}
                        </span>
                    </div>
                </div>

                <!-- Main carousel -->

                <div
                    class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20"
                    tabindex="0"
                    :aria-label="trans('infrastruktur.public.carousel_label')"
                    @keydown="handleKeydown"
                    @mouseenter="pauseAutoplay"
                    @mouseleave="resumeAutoplay"
                    @pointerdown="handlePointerDown"
                    @pointermove="handlePointerMove"
                    @pointerup="handlePointerUp"
                    @pointercancel="handlePointerCancel"
                >
                    <!-- Accent line -->

                    <div
                        aria-hidden="true"
                        class="absolute inset-x-0 top-0 z-20 h-1 bg-gradient-to-r from-blue-600 via-sky-500 to-indigo-500"
                    />

                    <Transition
                        mode="out-in"
                        enter-active-class="transition duration-500 ease-out"
                        enter-from-class="translate-x-4 opacity-0"
                        enter-to-class="translate-x-0 opacity-100"
                        leave-active-class="transition duration-300 ease-in absolute inset-0"
                        leave-from-class="translate-x-0 opacity-100"
                        leave-to-class="-translate-x-4 opacity-0"
                    >
                        <div
                            :key="currentInfrastruktur?.id"
                            class="grid min-h-[520px] grid-cols-1 lg:grid-cols-12"
                        >
                            <!-- Image -->

                            <div
                                class="relative min-h-[280px] overflow-hidden bg-slate-100 sm:min-h-[360px] lg:col-span-7 lg:min-h-[560px] dark:bg-slate-800"
                            >
                                <img
                                    v-if="currentImage"
                                    :src="currentImage"
                                    :alt="currentName"
                                    class="absolute inset-0 size-full select-none object-cover"
                                    draggable="false"
                                />

                                <div
                                    v-else
                                    class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-slate-100 via-blue-50 to-slate-200 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800"
                                >
                                    <div
                                        class="flex flex-col items-center text-center"
                                    >
                                        <div
                                            class="flex size-20 items-center justify-center rounded-3xl bg-white/80 text-blue-600 shadow-lg backdrop-blur dark:bg-slate-900/70 dark:text-blue-400"
                                        >
                                            <Construction class="size-10" />
                                        </div>

                                        <span
                                            class="mt-4 text-sm font-medium text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                trans(
                                                    "infrastruktur.public.image_alt_fallback",
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Image overlay -->

                                <div
                                    aria-hidden="true"
                                    class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent"
                                />

                                <!-- Image label -->

                                <div
                                    class="absolute left-5 top-5 sm:left-7 sm:top-7"
                                >
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-slate-950/45 px-3 py-1.5 text-xs font-semibold text-white shadow-lg backdrop-blur-md"
                                    >
                                        <Construction class="size-3.5" />

                                        {{
                                            trans(
                                                "infrastruktur.public.image_label",
                                            )
                                        }}
                                    </span>
                                </div>

                                <!-- Image bottom -->

                                <div
                                    class="absolute inset-x-5 bottom-5 flex items-end justify-between gap-4 sm:inset-x-7 sm:bottom-7"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="text-xs font-medium uppercase tracking-[0.14em] text-white/65"
                                        >
                                            {{
                                                trans(
                                                    "infrastruktur.public.supporting_facility",
                                                )
                                            }}
                                        </p>

                                        <p
                                            class="mt-1 truncate text-lg font-bold text-white sm:text-xl"
                                        >
                                            {{ currentName }}
                                        </p>
                                    </div>

                                    <button
                                        v-if="currentImage"
                                        type="button"
                                        class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-white/20 bg-slate-950/40 text-white backdrop-blur-md transition hover:bg-white hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-white/80"
                                        :aria-label="
                                            trans(
                                                'infrastruktur.public.view_larger_image',
                                            )
                                        "
                                        @click.stop="openImagePreview"
                                        @pointerdown.stop
                                    >
                                        <Maximize2 class="size-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Content -->

                            <div
                                class="relative flex flex-col justify-between p-6 sm:p-8 lg:col-span-5 lg:p-10 xl:p-12"
                            >
                                <div>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex size-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                        >
                                            <Construction class="size-5" />
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs font-semibold uppercase tracking-[0.15em] text-blue-600 dark:text-blue-400"
                                            >
                                                {{
                                                    trans(
                                                        "infrastruktur.public.section_label",
                                                    )
                                                }}
                                            </p>

                                            <p
                                                class="mt-0.5 text-xs text-slate-400"
                                            >
                                                KITB
                                            </p>
                                        </div>
                                    </div>

                                    <h2
                                        class="mt-6 text-2xl font-bold leading-tight tracking-tight text-slate-950 sm:text-3xl dark:text-white"
                                    >
                                        {{ currentName }}
                                    </h2>

                                    <div
                                        class="mt-5 h-px w-16 bg-blue-600 dark:bg-blue-400"
                                    />

                                    <p
                                        class="mt-5 text-sm leading-7 text-slate-600 sm:text-base dark:text-slate-400"
                                    >
                                        {{ currentDescription }}
                                    </p>
                                </div>

                                <!-- Info -->

                                <div class="mt-8">
                                    <div
                                        class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/50"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                            >
                                                <MapPin class="size-4" />
                                            </div>

                                            <div>
                                                <p
                                                    class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                                                >
                                                    {{
                                                        trans(
                                                            "infrastruktur.public.area",
                                                        )
                                                    }}
                                                </p>

                                                <p
                                                    class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200"
                                                >
                                                    {{
                                                        trans(
                                                            "infrastruktur.public.area_name",
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-4 flex flex-col gap-3 sm:flex-row"
                                    >
                                        <Link
                                            href="/kawasan/fasilitas"
                                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:bg-blue-950/40 dark:hover:text-blue-300"
                                            @pointerdown.stop
                                        >
                                            <Building2 class="size-4" />

                                            {{
                                                trans(
                                                    "infrastruktur.public.facility",
                                                )
                                            }}
                                        </Link>

                                        <Link
                                            href="/kawasan/peta-kawasan"
                                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400"
                                            @pointerdown.stop
                                        >
                                            <Map class="size-4" />

                                            {{
                                                trans(
                                                    "infrastruktur.public.area_map",
                                                )
                                            }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>

                    <!-- Previous -->

                    <button
                        v-if="hasMultipleSlides"
                        type="button"
                        class="absolute left-3 top-1/2 z-30 flex size-11 -translate-y-1/2 items-center justify-center rounded-2xl border border-white/30 bg-slate-950/55 text-white shadow-xl backdrop-blur-md transition hover:scale-105 hover:bg-slate-950/75 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:left-5"
                        :aria-label="trans('infrastruktur.public.previous')"
                        @click.stop="previousSlide"
                        @pointerdown.stop
                    >
                        <ChevronLeft class="size-5" />
                    </button>

                    <!-- Next -->

                    <button
                        v-if="hasMultipleSlides"
                        type="button"
                        class="absolute right-3 top-1/2 z-30 flex size-11 -translate-y-1/2 items-center justify-center rounded-2xl border border-white/30 bg-slate-950/55 text-white shadow-xl backdrop-blur-md transition hover:scale-105 hover:bg-slate-950/75 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:right-5"
                        :aria-label="trans('infrastruktur.public.next')"
                        @click.stop="nextSlide"
                        @pointerdown.stop
                    >
                        <ChevronRight class="size-5" />
                    </button>

                    <!-- Dots -->

                    <div
                        v-if="hasMultipleSlides"
                        class="absolute bottom-4 left-1/2 z-30 flex -translate-x-1/2 items-center gap-1.5 rounded-full border border-white/20 bg-slate-950/45 px-3 py-2 shadow-lg backdrop-blur-md sm:bottom-5"
                        role="tablist"
                        :aria-label="trans('infrastruktur.public.navigation')"
                        @pointerdown.stop
                    >
                        <button
                            v-for="(infrastruktur, index) in infrastrukturs"
                            :key="infrastruktur.id"
                            type="button"
                            class="h-1.5 rounded-full transition-all duration-300"
                            :class="
                                index === currentIndex
                                    ? 'w-7 bg-white'
                                    : 'w-1.5 bg-white/45 hover:bg-white/75'
                            "
                            :aria-label="
                                trans('infrastruktur.public.show_slide', {
                                    name: getLocalizedName(infrastruktur),
                                })
                            "
                            :aria-selected="index === currentIndex"
                            role="tab"
                            @click.stop="
                                goToSlide(index);
                                restartAutoplay();
                            "
                        />
                    </div>
                </div>
            </section>

            <!-- =================================================
                 Supporting section
            ================================================== -->

            <section
                v-if="infrastrukturs.length"
                data-reveal
                class="mt-10 grid gap-5 md:grid-cols-3"
                style="--d: 220ms"
            >
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                    >
                        <Construction class="size-5" />
                    </div>

                    <h3 class="mt-5 font-bold text-slate-900 dark:text-white">
                        {{ trans("infrastruktur.public.integrated_title") }}
                    </h3>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{
                            trans("infrastruktur.public.integrated_description")
                        }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                    >
                        <ShieldCheck class="size-5" />
                    </div>

                    <h3 class="mt-5 font-bold text-slate-900 dark:text-white">
                        {{ trans("infrastruktur.public.industry_title") }}
                    </h3>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ trans("infrastruktur.public.industry_description") }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400"
                    >
                        <MapPin class="size-5" />
                    </div>

                    <h3 class="mt-5 font-bold text-slate-900 dark:text-white">
                        {{ trans("infrastruktur.public.connected_title") }}
                    </h3>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{
                            trans("infrastruktur.public.connected_description")
                        }}
                    </p>
                </div>
            </section>

            <!-- =================================================
                 CTA
            ================================================== -->

            <section
                v-if="infrastrukturs.length"
                data-reveal
                class="relative mt-10 overflow-hidden rounded-[2rem] bg-slate-950 px-6 py-10 text-white shadow-xl sm:px-10 lg:px-12"
                style="--d: 300ms"
            >
                <div
                    aria-hidden="true"
                    class="absolute -right-20 -top-28 size-72 rounded-full bg-blue-500/20 blur-3xl"
                />

                <div
                    aria-hidden="true"
                    class="absolute -bottom-32 left-1/3 size-80 rounded-full bg-sky-500/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-7 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="max-w-2xl">
                        <div
                            class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-blue-300"
                        >
                            <span class="size-1.5 rounded-full bg-blue-400" />

                            {{ trans("infrastruktur.public.explore_label") }}
                        </div>

                        <h2
                            class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl"
                        >
                            {{ trans("infrastruktur.public.explore_title") }}
                        </h2>

                        <p
                            class="mt-3 text-sm leading-6 text-slate-300 sm:text-base"
                        >
                            {{
                                trans(
                                    "infrastruktur.public.explore_description",
                                )
                            }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row lg:shrink-0">
                        <Link
                            href="/kawasan/profil-kawasan"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:-translate-y-0.5 hover:bg-slate-100"
                        >
                            <Building2 class="size-4" />

                            {{
                                trans("infrastruktur.public.view_area_profile")
                            }}
                        </Link>

                        <Link
                            href="/kawasan/peta-kawasan"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/15"
                        >
                            <Map class="size-4" />

                            {{ trans("infrastruktur.public.area_map") }}
                        </Link>
                    </div>
                </div>
            </section>
        </div>

        <!-- =====================================================
             Image Preview Modal
        ====================================================== -->

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isImagePreviewOpen && currentImage"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/90 p-4 backdrop-blur-sm sm:p-8"
                role="dialog"
                aria-modal="true"
                :aria-label="trans('infrastruktur.public.image_preview')"
                tabindex="0"
                @keydown="handlePreviewKeydown"
                @click="closeImagePreview"
            >
                <button
                    type="button"
                    class="absolute right-4 top-4 z-10 flex size-11 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-white backdrop-blur-md transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/70 sm:right-7 sm:top-7"
                    :aria-label="trans('infrastruktur.public.close_preview')"
                    @click.stop="closeImagePreview"
                >
                    <X class="size-5" />
                </button>

                <div
                    class="relative max-h-[90vh] max-w-6xl overflow-hidden rounded-2xl bg-slate-900 shadow-2xl"
                    @click.stop
                >
                    <img
                        :src="currentImage"
                        :alt="currentName"
                        class="max-h-[90vh] max-w-full object-contain"
                    />

                    <div
                        class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 to-transparent px-5 pb-5 pt-12"
                    >
                        <p
                            class="text-sm font-semibold text-white sm:text-base"
                        >
                            {{ currentName }}
                        </p>
                    </div>
                </div>
            </div>
        </Transition>
    </main>
</template>

<style scoped>
[data-reveal] {
    opacity: 0;
    transform: translateY(18px);

    transition:
        opacity 700ms cubic-bezier(0.22, 1, 0.36, 1),
        transform 700ms cubic-bezier(0.22, 1, 0.36, 1);

    transition-delay: var(--d, 0ms);
}

[data-reveal].is-visible {
    opacity: 1;
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    [data-reveal] {
        opacity: 1;
        transform: none;
        transition: none;
    }
}

@media (max-width: 640px) {
    [data-reveal] {
        transition-duration: 500ms;
    }
}
</style>
