<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";
import {
    Building2,
    ChevronRight,
    Home,
    Image as ImageIcon,
    Maximize2,
    X,
} from "lucide-vue-next";

import { localizedValue } from "@/composables/useLocale";
import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
 * Props
 * ========================================================= */

interface Fasilitas {
    id: number;

    // Bahasa Indonesia
    nama: string;
    deskripsi: string | null;

    // Bahasa Inggris
    nama_en: string | null;
    deskripsi_en: string | null;

    // Bahasa Mandarin
    nama_zh: string | null;
    deskripsi_zh: string | null;

    // Data lainnya
    slug: string;
    gambar: string | null;
    urutan: number;
    aktif: boolean;
}

const props = defineProps<{
    fasilitas: Fasilitas[];
}>();

/* =========================================================
 * Data
 * ========================================================= */

const fasilitas = computed<Fasilitas[]>(() => {
    return Array.isArray(props.fasilitas) ? props.fasilitas : [];
});

/* =========================================================
 * Skeleton
 * ========================================================= */

const isMounted = ref(false);
const isLoading = ref(fasilitas.value.length > 0);

let skeletonTimer: ReturnType<typeof setTimeout> | null = null;

const finishLoading = () => {
    if (skeletonTimer) {
        clearTimeout(skeletonTimer);
    }

    skeletonTimer = setTimeout(async () => {
        isLoading.value = false;

        await setupRevealObserver();
    }, 450);
};

/* =========================================================
 * Reveal animation
 * ========================================================= */

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const revealObserver = ref<IntersectionObserver | null>(null);

const setupRevealObserver = async () => {
    if (typeof window === "undefined") {
        return;
    }

    await nextTick();

    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (prefersReducedMotion) {
        elements.forEach((element) => {
            element.classList.add("is-visible");
        });

        return;
    }

    revealObserver.value?.disconnect();

    revealObserver.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add("is-visible");

                revealObserver.value?.unobserve(entry.target);
            });
        },
        {
            threshold: 0.08,
            rootMargin: "0px 0px -40px 0px",
        },
    );

    elements.forEach((element) => {
        revealObserver.value?.observe(element);
    });
};

/* =========================================================
 * Accessibility helpers
 * ========================================================= */

const focusableSelector = [
    "a[href]",
    "area[href]",
    "button:not([disabled])",
    "input:not([disabled])",
    "select:not([disabled])",
    "textarea:not([disabled])",
    "iframe",
    "object",
    "embed",
    "[contenteditable]",
    "[tabindex]:not([tabindex='-1'])",
].join(",");

const getFocusableElements = (container: HTMLElement | null): HTMLElement[] => {
    if (!container) {
        return [];
    }

    return Array.from(
        container.querySelectorAll<HTMLElement>(focusableSelector),
    ).filter((element) => {
        const style = window.getComputedStyle(element);

        return (
            style.display !== "none" &&
            style.visibility !== "hidden" &&
            !element.hasAttribute("aria-hidden")
        );
    });
};

const trapFocus = (event: KeyboardEvent, container: HTMLElement | null) => {
    if (event.key !== "Tab" || !container) {
        return;
    }

    const focusableElements = getFocusableElements(container);

    if (focusableElements.length === 0) {
        event.preventDefault();
        container.focus();
        return;
    }

    const first = focusableElements[0];
    const last = focusableElements[focusableElements.length - 1];
    const active = document.activeElement;

    if (!container.contains(active)) {
        event.preventDefault();
        first.focus();
        return;
    }

    if (event.shiftKey && (active === first || active === container)) {
        event.preventDefault();
        last.focus();
        return;
    }

    if (!event.shiftKey && active === last) {
        event.preventDefault();
        first.focus();
    }
};

/* =========================================================
 * Detail Modal
 * ========================================================= */

const selectedFasilitas = ref<Fasilitas | null>(null);
const isModalOpen = ref(false);

const modalRef = ref<HTMLElement | null>(null);
const modalCloseButtonRef = ref<HTMLButtonElement | null>(null);

const previouslyFocusedElement = ref<HTMLElement | null>(null);

let clearSelectedTimer: ReturnType<typeof setTimeout> | null = null;

const modalTitleId = "fasilitas-modal-title";
const modalDescriptionId = "fasilitas-modal-description";

const openModal = (item: Fasilitas) => {
    if (clearSelectedTimer) {
        clearTimeout(clearSelectedTimer);
        clearSelectedTimer = null;
    }

    previouslyFocusedElement.value =
        document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;

    selectedFasilitas.value = item;
    isModalOpen.value = true;

    document.body.style.overflow = "hidden";

    nextTick(() => {
        modalCloseButtonRef.value?.focus();
    });
};

const closeModal = () => {
    isImagePreviewOpen.value = false;
    isModalOpen.value = false;

    document.body.style.overflow = "";

    clearSelectedTimer = setTimeout(() => {
        selectedFasilitas.value = null;
        clearSelectedTimer = null;
    }, 200);

    nextTick(() => {
        previouslyFocusedElement.value?.focus();
        previouslyFocusedElement.value = null;
    });
};

/* =========================================================
 * Full Image Preview
 * ========================================================= */

const isImagePreviewOpen = ref(false);

const imagePreviewRef = ref<HTMLElement | null>(null);
const imagePreviewCloseButtonRef = ref<HTMLButtonElement | null>(null);

const previouslyFocusedImageElement = ref<HTMLElement | null>(null);

const imagePreviewTitleId = "fasilitas-image-preview-title";

const openImagePreview = () => {
    if (!selectedImage.value) {
        return;
    }

    previouslyFocusedImageElement.value =
        document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;

    isImagePreviewOpen.value = true;

    nextTick(() => {
        imagePreviewCloseButtonRef.value?.focus();
    });
};

const closeImagePreview = () => {
    isImagePreviewOpen.value = false;

    nextTick(() => {
        previouslyFocusedImageElement.value?.focus();
        previouslyFocusedImageElement.value = null;
    });
};

/* =========================================================
 * Keyboard
 * ========================================================= */

const handleGlobalKeydown = (event: KeyboardEvent) => {
    // Image preview berada di lapisan paling atas.
    if (isImagePreviewOpen.value) {
        if (event.key === "Escape") {
            event.preventDefault();
            closeImagePreview();
            return;
        }

        trapFocus(event, imagePreviewRef.value);
        return;
    }

    if (isModalOpen.value) {
        if (event.key === "Escape") {
            event.preventDefault();
            closeModal();
            return;
        }

        trapFocus(event, modalRef.value);
    }
};

/* =========================================================
 * Helpers
 * ========================================================= */

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

/**
 * Mengambil value berdasarkan bahasa aktif.
 *
 * ID -> nama / deskripsi
 * EN -> nama_en / deskripsi_en
 * ZH -> nama_zh / deskripsi_zh
 *
 * localizedValue melakukan fallback ke bahasa Indonesia
 * apabila terjemahan EN / ZH kosong.
 *
 * Adapter cast dilakukan hanya di sini karena localizedValue
 * menggunakan Record<string, unknown>.
 */
const getLocalizedValue = (
    item: Fasilitas,
    field: "nama" | "deskripsi",
): string => {
    return localizedValue(item as unknown as Record<string, unknown>, field);
};

const stripHtml = (text: string): string => {
    return text
        .replace(/<[^>]*>/g, " ")
        .replace(/\s+/g, " ")
        .trim();
};

const truncateText = (text: string | null, maxLength = 180): string => {
    if (!text) {
        return trans("facilities.description_fallback");
    }

    const clean = stripHtml(text);

    if (!clean) {
        return trans("facilities.description_fallback");
    }

    if (clean.length <= maxLength) {
        return clean;
    }

    return `${clean.slice(0, maxLength).trim()}…`;
};

/* =========================================================
 * Selected Data
 * ========================================================= */

const selectedImage = computed(() => {
    return selectedFasilitas.value
        ? getImageUrl(selectedFasilitas.value.gambar)
        : null;
});

const selectedDescription = computed(() => {
    const item = selectedFasilitas.value;

    if (!item) {
        return trans("facilities.description_fallback");
    }

    const text = stripHtml(getLocalizedValue(item, "deskripsi"));

    return text || trans("facilities.description_fallback");
});

const selectedLocalizedName = computed(() => {
    const item = selectedFasilitas.value;

    if (!item) {
        return trans("facilities.fallback_name");
    }

    return getLocalizedValue(item, "nama") || trans("facilities.fallback_name");
});

const selectedNumber = computed(() => {
    if (!selectedFasilitas.value) {
        return "00";
    }

    return String(selectedFasilitas.value.urutan || 0).padStart(2, "0");
});

/* =========================================================
 * Lifecycle
 * ========================================================= */

onMounted(async () => {
    isMounted.value = true;

    document.addEventListener("keydown", handleGlobalKeydown);

    await setupRevealObserver();

    if (isLoading.value) {
        finishLoading();
    }
});

onBeforeUnmount(() => {
    revealObserver.value?.disconnect();

    if (skeletonTimer) {
        clearTimeout(skeletonTimer);
        skeletonTimer = null;
    }

    if (clearSelectedTimer) {
        clearTimeout(clearSelectedTimer);
        clearSelectedTimer = null;
    }

    document.removeEventListener("keydown", handleGlobalKeydown);

    document.body.style.overflow = "";
});
</script>

<template>
    <Head>
        <title>{{ trans("facilities.meta_title") }}</title>

        <meta
            name="description"
            :content="trans('facilities.meta_description')"
        />

        <meta name="robots" content="index, follow" />

        <link
            rel="canonical"
            href="https://tanjungbuton-industrial.co.id/kawasan/fasilitas"
        />

        <meta property="og:title" :content="trans('facilities.meta_title')" />

        <meta
            property="og:description"
            :content="trans('facilities.meta_description')"
        />

        <meta
            property="og:url"
            content="https://tanjungbuton-industrial.co.id/kawasan/fasilitas"
        />

        <meta property="og:type" content="website" />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 text-slate-900 dark:bg-slate-950 dark:text-slate-100"
    >
        <!-- Decorative Background -->
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
            <!-- Breadcrumb -->
            <div data-reveal class="reveal mb-6" style="--d: 0ms">
                <nav
                    :aria-label="trans('navigation.breadcrumb')"
                    class="flex flex-wrap items-center gap-2 text-sm"
                >
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Home class="size-4 shrink-0" aria-hidden="true" />

                        <span>
                            {{ trans("navigation.home") }}
                        </span>
                    </Link>

                    <ChevronRight
                        class="size-4 shrink-0 text-slate-400"
                        aria-hidden="true"
                    />

                    <Link
                        href="/kawasan/profil-kawasan"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Building2 class="size-4 shrink-0" aria-hidden="true" />

                        <span>
                            {{ trans("navigation.industrial_area") }}
                        </span>
                    </Link>

                    <ChevronRight
                        class="size-4 shrink-0 text-slate-400"
                        aria-hidden="true"
                    />

                    <span
                        aria-current="page"
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                    >
                        <Building2
                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                            aria-hidden="true"
                        />

                        <span>
                            {{ trans("navigation.facilities") }}
                        </span>
                    </span>
                </nav>
            </div>

            <!-- Hero -->
            <section
                data-reveal
                class="reveal mb-10 max-w-3xl"
                style="--d: 80ms"
            >
                <div
                    class="mb-4 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-white/80 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-blue-700 shadow-sm backdrop-blur dark:border-blue-900/60 dark:bg-slate-900/70 dark:text-blue-300"
                >
                    <span
                        class="size-1.5 rounded-full bg-blue-600 dark:bg-blue-400"
                    />

                    {{ trans("facilities.hero_badge") }}
                </div>

                <h1
                    class="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl lg:text-5xl dark:text-white"
                >
                    {{ trans("facilities.hero_title") }}

                    <span class="block text-blue-600 dark:text-blue-400">
                        {{ trans("facilities.hero_title_highlight") }}
                    </span>
                </h1>

                <p
                    class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base dark:text-slate-400"
                >
                    {{ trans("facilities.hero_description") }}
                </p>
            </section>

            <!-- Skeleton -->
            <section
                v-if="isLoading"
                :aria-label="trans('facilities.loading')"
                aria-busy="true"
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="index in 6"
                    :key="index"
                    class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="skeleton-shimmer aspect-[16/10] bg-slate-200 dark:bg-slate-800"
                    />

                    <div class="space-y-4 p-6">
                        <div
                            class="skeleton-shimmer h-5 w-2/3 rounded-lg bg-slate-200 dark:bg-slate-800"
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
                            class="skeleton-shimmer h-4 w-28 rounded bg-slate-200 dark:bg-slate-800"
                        />
                    </div>
                </div>
            </section>

            <!-- Empty State -->
            <section
                v-else-if="!fasilitas.length"
                data-reveal
                class="reveal rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
                style="--d: 140ms"
            >
                <div class="mx-auto max-w-xl text-center">
                    <div
                        class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                    >
                        <Building2 class="size-8" aria-hidden="true" />
                    </div>

                    <h2
                        class="mt-5 text-xl font-bold text-slate-900 dark:text-white"
                    >
                        {{ trans("facilities.empty_title") }}
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ trans("facilities.empty_description") }}
                    </p>

                    <Link
                        href="/kawasan/profil-kawasan"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700"
                    >
                        <Building2 class="size-4" aria-hidden="true" />

                        {{ trans("facilities.area_profile") }}
                    </Link>
                </div>
            </section>

            <!-- Facility Grid -->
            <section v-else data-reveal class="reveal" style="--d: 140ms">
                <div
                    class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <div
                            class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400"
                        >
                            <span
                                class="size-2 rounded-full bg-blue-600 dark:bg-blue-400"
                            />

                            {{ trans("facilities.available") }}
                        </div>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            {{
                                trans("facilities.count", {
                                    count: String(fasilitas.length),
                                })
                            }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(item, index) in fasilitas"
                        :key="item.id"
                        data-reveal
                        class="reveal group overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-500 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-900"
                        :style="{
                            '--d': `${180 + index * 60}ms`,
                        }"
                    >
                        <!-- Image -->
                        <div
                            class="relative aspect-[16/10] overflow-hidden bg-slate-100 dark:bg-slate-800"
                        >
                            <img
                                v-if="getImageUrl(item.gambar)"
                                :src="getImageUrl(item.gambar) ?? ''"
                                :alt="getLocalizedValue(item, 'nama')"
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                            />

                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 via-blue-50 to-slate-100 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800"
                            >
                                <div
                                    class="flex size-16 items-center justify-center rounded-2xl bg-white/80 text-blue-500 shadow-sm dark:bg-slate-900/80 dark:text-blue-400"
                                >
                                    <ImageIcon
                                        class="size-7"
                                        aria-hidden="true"
                                    />
                                </div>
                            </div>

                            <div
                                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-transparent"
                            />

                            <div
                                class="absolute left-4 top-4 rounded-full border border-white/20 bg-slate-950/45 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur-md"
                            >
                                {{ trans("facilities.badge") }}
                            </div>

                            <div
                                class="absolute bottom-4 right-4 flex size-10 items-center justify-center rounded-xl border border-white/20 bg-slate-950/45 text-white backdrop-blur-md"
                            >
                                <span class="font-mono text-xs font-semibold">
                                    {{ String(index + 1).padStart(2, "0") }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <h2
                                class="text-lg font-bold tracking-tight text-slate-900 transition-colors group-hover:text-blue-700 dark:text-white dark:group-hover:text-blue-400"
                            >
                                {{ getLocalizedValue(item, "nama") }}
                            </h2>

                            <p
                                class="mt-3 line-clamp-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    truncateText(
                                        getLocalizedValue(item, "deskripsi"),
                                    )
                                }}
                            </p>

                            <div
                                class="mt-6 flex items-center justify-between gap-3"
                            >
                                <span
                                    class="text-xs font-medium text-slate-400 dark:text-slate-500"
                                >
                                    {{ trans("facilities.card_label") }}
                                </span>

                                <button
                                    type="button"
                                    :aria-label="
                                        trans('facilities.view_detail_aria', {
                                            name: getLocalizedValue(
                                                item,
                                                'nama',
                                            ),
                                        })
                                    "
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:-translate-y-0.5 hover:bg-blue-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white dark:focus:ring-offset-slate-900"
                                    @click="openModal(item)"
                                >
                                    {{ trans("facilities.view_detail") }}

                                    <ChevronRight
                                        class="size-4 transition-transform group-hover:translate-x-0.5"
                                        aria-hidden="true"
                                    />
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- CTA -->
            <section
                v-if="!isLoading && fasilitas.length"
                data-reveal
                class="reveal relative mt-10 overflow-hidden rounded-[2rem] bg-slate-950 px-6 py-10 text-white shadow-xl sm:px-10 lg:px-12"
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

                            {{ trans("facilities.cta_badge") }}
                        </div>

                        <h2
                            class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl"
                        >
                            {{ trans("facilities.cta_title") }}
                        </h2>

                        <p
                            class="mt-3 text-sm leading-6 text-slate-300 sm:text-base"
                        >
                            {{ trans("facilities.cta_description") }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row lg:shrink-0">
                        <Link
                            href="/kawasan/profil-kawasan"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:-translate-y-0.5 hover:bg-slate-100"
                        >
                            <Building2 class="size-4" aria-hidden="true" />

                            {{ trans("facilities.area_profile") }}
                        </Link>

                        <Link
                            href="/kawasan/infrastruktur"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/15"
                        >
                            {{ trans("facilities.infrastructure") }}

                            <ChevronRight class="size-4" aria-hidden="true" />
                        </Link>
                    </div>
                </div>
            </section>
        </div>

        <!-- Detail Modal -->
        <Teleport v-if="isMounted" to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isModalOpen && selectedFasilitas"
                    class="fixed inset-0 z-[90] flex items-center justify-center overflow-y-auto bg-slate-950/70 p-4 backdrop-blur-sm sm:p-6"
                    role="presentation"
                    @mousedown.self="closeModal"
                >
                    <div
                        ref="modalRef"
                        class="relative my-auto w-full max-w-4xl overflow-hidden rounded-[2rem] border border-white/10 bg-white shadow-2xl outline-none dark:border-slate-700 dark:bg-slate-900"
                        role="dialog"
                        aria-modal="true"
                        :aria-labelledby="modalTitleId"
                        :aria-describedby="modalDescriptionId"
                        tabindex="-1"
                    >
                        <!-- Accent -->
                        <div
                            aria-hidden="true"
                            class="absolute inset-x-0 top-0 z-20 h-1 bg-gradient-to-r from-blue-600 via-sky-500 to-indigo-500"
                        />

                        <!-- Close -->
                        <button
                            ref="modalCloseButtonRef"
                            type="button"
                            :aria-label="trans('facilities.close_detail')"
                            class="absolute right-4 top-4 z-30 flex size-11 items-center justify-center rounded-xl border border-white/20 bg-slate-950/50 text-white backdrop-blur-md transition hover:bg-slate-950/70 focus:outline-none focus:ring-2 focus:ring-white/70 sm:right-6 sm:top-6"
                            @click="closeModal"
                        >
                            <X class="size-5" aria-hidden="true" />
                        </button>

                        <div
                            class="grid max-h-[90vh] overflow-y-auto lg:grid-cols-12 lg:overflow-hidden"
                        >
                            <!-- Modal Image -->
                            <div
                                class="relative min-h-[260px] bg-slate-100 lg:col-span-6 lg:min-h-[580px] dark:bg-slate-800"
                            >
                                <img
                                    v-if="selectedImage"
                                    :src="selectedImage"
                                    :alt="
                                        trans('facilities.photo_alt', {
                                            name: selectedLocalizedName,
                                        })
                                    "
                                    class="h-full min-h-[260px] w-full object-cover lg:min-h-[580px]"
                                />

                                <div
                                    v-else
                                    class="flex h-full min-h-[260px] items-center justify-center bg-gradient-to-br from-slate-100 via-blue-50 to-slate-100 lg:min-h-[580px] dark:from-slate-800 dark:via-slate-900 dark:to-slate-800"
                                >
                                    <div
                                        class="flex size-20 items-center justify-center rounded-3xl bg-white text-blue-500 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                    >
                                        <ImageIcon
                                            class="size-9"
                                            aria-hidden="true"
                                        />
                                    </div>
                                </div>

                                <div
                                    class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent"
                                />

                                <div
                                    class="absolute bottom-5 left-5 right-5 flex items-end justify-between gap-4"
                                >
                                    <div>
                                        <p
                                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-200"
                                        >
                                            {{ trans("facilities.card_label") }}
                                        </p>

                                        <p
                                            class="mt-1 font-mono text-sm font-semibold text-white"
                                        >
                                            {{ selectedNumber }}
                                        </p>
                                    </div>

                                    <button
                                        v-if="selectedImage"
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-slate-950/40 px-3.5 py-2 text-xs font-semibold text-white backdrop-blur-md transition hover:bg-slate-950/60 focus:outline-none focus:ring-2 focus:ring-white/70"
                                        @click="openImagePreview"
                                    >
                                        <Maximize2
                                            class="size-4"
                                            aria-hidden="true"
                                        />

                                        {{ trans("facilities.enlarge") }}
                                    </button>
                                </div>
                            </div>

                            <!-- Modal Content -->
                            <div
                                class="flex flex-col p-6 sm:p-8 lg:col-span-6 lg:p-10"
                            >
                                <div class="flex-1">
                                    <div
                                        class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                                    >
                                        <span
                                            class="size-1.5 rounded-full bg-blue-600 dark:bg-blue-400"
                                        />

                                        {{ trans("facilities.hero_badge") }}
                                    </div>

                                    <h2
                                        :id="modalTitleId"
                                        class="mt-5 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl dark:text-white"
                                    >
                                        {{ selectedLocalizedName }}
                                    </h2>

                                    <div
                                        class="mt-6 h-px bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div class="mt-6">
                                        <p
                                            class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500"
                                        >
                                            {{
                                                trans(
                                                    "facilities.description_label",
                                                )
                                            }}
                                        </p>

                                        <p
                                            :id="modalDescriptionId"
                                            class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600 sm:text-base dark:text-slate-400"
                                        >
                                            {{ selectedDescription }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="mt-8 border-t border-slate-200 pt-6 dark:border-slate-800"
                                >
                                    <div
                                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div>
                                            <p
                                                class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                                            >
                                                {{
                                                    trans(
                                                        "facilities.area_label",
                                                    )
                                                }}
                                            </p>

                                            <p
                                                class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-300"
                                            >
                                                {{
                                                    trans(
                                                        "facilities.area_name",
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-white dark:text-slate-900 dark:hover:bg-blue-50"
                                            @click="closeModal"
                                        >
                                            {{
                                                trans("facilities.close_detail")
                                            }}

                                            <X
                                                class="size-4"
                                                aria-hidden="true"
                                            />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Full Image Preview -->
        <Teleport v-if="isMounted" to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isImagePreviewOpen && selectedImage"
                    ref="imagePreviewRef"
                    class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/95 p-4 outline-none backdrop-blur-md sm:p-8"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="imagePreviewTitleId"
                    tabindex="-1"
                    @mousedown.self="closeImagePreview"
                >
                    <h2 :id="imagePreviewTitleId" class="sr-only">
                        {{ trans("facilities.image_preview") }}
                        {{ selectedLocalizedName }}
                    </h2>

                    <button
                        ref="imagePreviewCloseButtonRef"
                        type="button"
                        :aria-label="trans('facilities.close_image_preview')"
                        class="absolute right-4 top-4 z-10 flex size-11 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-white backdrop-blur-md transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/70 sm:right-7 sm:top-7"
                        @click="closeImagePreview"
                    >
                        <X class="size-5" aria-hidden="true" />
                    </button>

                    <div
                        class="relative max-h-[90vh] max-w-6xl overflow-hidden rounded-2xl bg-slate-900 shadow-2xl"
                    >
                        <img
                            :src="selectedImage"
                            :alt="
                                trans('facilities.image_preview_alt', {
                                    name: selectedLocalizedName,
                                })
                            "
                            class="max-h-[90vh] max-w-full object-contain"
                        />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </main>
</template>

<style scoped>
.reveal {
    opacity: 0;
    transform: translateY(18px);
    transition:
        opacity 700ms ease,
        transform 700ms cubic-bezier(0.22, 1, 0.36, 1);
    transition-delay: var(--d, 0ms);
}

.reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

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
    .reveal {
        opacity: 1;
        transform: none;
        transition: none;
    }

    .skeleton-shimmer::after {
        animation: none;
    }
}
</style>
