<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";
import {
    ChevronLeft,
    ChevronRight,
    Expand,
    Home,
    Landmark,
    Map,
    X,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";
import {
    currentLanguage,
    localizedValue,
    type LanguageCode,
} from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

/*
|--------------------------------------------------------------------------
| TYPES
|--------------------------------------------------------------------------
*/

interface PetaKawasan {
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
    aktif: boolean;
}

/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/

const props = defineProps<{
    petaKawasans: PetaKawasan[];
    selectedSlug?: string | null;
}>();

/*
|--------------------------------------------------------------------------
| LANGUAGE
|--------------------------------------------------------------------------
*/

const language = computed<LanguageCode>(() => currentLanguage.value);

const getLocalizedValue = (
    item: PetaKawasan,
    field: "nama" | "deskripsi",
): string => {
    const value = localizedValue(
        item as unknown as Record<string, unknown>,
        field,
    );

    if (value?.trim()) {
        return value;
    }

    if (field === "nama") {
        return item.nama ?? "";
    }

    return item.deskripsi ?? "";
};

/*
|--------------------------------------------------------------------------
| DATA
|--------------------------------------------------------------------------
*/

const petaKawasans = computed<PetaKawasan[]>(() => {
    if (!Array.isArray(props.petaKawasans)) {
        return [];
    }

    return [...props.petaKawasans]
        .filter((item) => item.aktif)
        .sort((a, b) => {
            if (a.urutan !== b.urutan) {
                return a.urutan - b.urutan;
            }

            return a.id - b.id;
        });
});

const selectedId = ref<number | null>(null);

const selected = computed<PetaKawasan | null>(() => {
    return (
        petaKawasans.value.find((item) => item.id === selectedId.value) ?? null
    );
});

const selectedIndex = computed(() => {
    if (!selected.value) {
        return -1;
    }

    return petaKawasans.value.findIndex(
        (item) => item.id === selected.value?.id,
    );
});

/*
|--------------------------------------------------------------------------
| IMAGE
|--------------------------------------------------------------------------
*/

const getImageUrl = (image: string | null): string | null => {
    if (!image) {
        return null;
    }

    if (
        image.startsWith("http://") ||
        image.startsWith("https://") ||
        image.startsWith("/")
    ) {
        return image;
    }

    if (image.startsWith("storage/")) {
        return `/${image}`;
    }

    return `/storage/${image}`;
};

/*
|--------------------------------------------------------------------------
| FULLSCREEN IMAGE
|--------------------------------------------------------------------------
*/

const isImagePreviewOpen = ref(false);

const previewImage = computed<string | null>(() => {
    return selected.value ? getImageUrl(selected.value.gambar) : null;
});

const openImagePreview = (): void => {
    if (!previewImage.value) {
        return;
    }

    isImagePreviewOpen.value = true;
    document.body.style.overflow = "hidden";
};

const closeImagePreview = (): void => {
    isImagePreviewOpen.value = false;
    document.body.style.overflow = "";
};

/*
|--------------------------------------------------------------------------
| SELECTION
|--------------------------------------------------------------------------
*/

const selectPeta = (item: PetaKawasan): void => {
    selectedId.value = item.id;
};

const selectPrevious = (): void => {
    if (!petaKawasans.value.length) {
        return;
    }

    const index = selectedIndex.value;

    if (index <= 0) {
        selectedId.value =
            petaKawasans.value[petaKawasans.value.length - 1]?.id ?? null;

        return;
    }

    selectedId.value = petaKawasans.value[index - 1]?.id ?? null;
};

const selectNext = (): void => {
    if (!petaKawasans.value.length) {
        return;
    }

    const index = selectedIndex.value;

    if (index === -1 || index >= petaKawasans.value.length - 1) {
        selectedId.value = petaKawasans.value[0]?.id ?? null;

        return;
    }

    selectedId.value = petaKawasans.value[index + 1]?.id ?? null;
};

/*
|--------------------------------------------------------------------------
| INITIAL SELECTION
|--------------------------------------------------------------------------
*/

const initializeSelection = (): void => {
    if (!petaKawasans.value.length) {
        selectedId.value = null;
        return;
    }

    if (props.selectedSlug) {
        const target = petaKawasans.value.find(
            (item) => item.slug === props.selectedSlug,
        );

        if (target) {
            selectedId.value = target.id;
            return;
        }
    }

    selectedId.value = petaKawasans.value[0]?.id ?? null;
};

/*
|--------------------------------------------------------------------------
| REVEAL
|--------------------------------------------------------------------------
*/

let revealObserver: IntersectionObserver | null = null;
let prefersReducedMotion = false;

const setupReveal = (): void => {
    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (prefersReducedMotion) {
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
};

/*
|--------------------------------------------------------------------------
| KEYBOARD
|--------------------------------------------------------------------------
*/

const handleKeydown = (event: KeyboardEvent): void => {
    if (event.key === "Escape" && isImagePreviewOpen.value) {
        closeImagePreview();
    }
};

/*
|--------------------------------------------------------------------------
| MOUNT
|--------------------------------------------------------------------------
*/

onMounted(() => {
    prefersReducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;

    initializeSelection();
    setupReveal();

    window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
    revealObserver?.disconnect();

    window.removeEventListener("keydown", handleKeydown);

    document.body.style.overflow = "";
});
</script>

<template>
    <Head>
        <title>
            {{ trans("peta_kawasan.meta_title") }}
        </title>

        <meta
            name="description"
            :content="trans('peta_kawasan.meta_description')"
        />

        <meta name="robots" content="index, follow" />

        <link
            rel="canonical"
            href="https://tanjungbuton-industrial.co.id/kawasan/peta-kawasan"
        />

        <meta property="og:title" :content="trans('peta_kawasan.meta_title')" />

        <meta
            property="og:description"
            :content="trans('peta_kawasan.meta_description')"
        />

        <meta
            property="og:url"
            content="https://tanjungbuton-industrial.co.id/kawasan/peta-kawasan"
        />

        <meta property="og:type" content="website" />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- Decorative background -->
        <div
            aria-hidden="true"
            class="pointer-events-none absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-900/20"
        />

        <div
            aria-hidden="true"
            class="pointer-events-none absolute -right-32 top-[45%] h-96 w-96 rounded-full bg-slate-200/40 blur-3xl dark:bg-slate-800/30"
        />

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav
                data-reveal
                style="--d: 0ms"
                :aria-label="trans('peta_kawasan.breadcrumb_current')"
                class="mb-6 flex flex-wrap items-center gap-2 text-sm"
            >
                <Link
                    href="/"
                    class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                >
                    <Home class="size-4 shrink-0" aria-hidden="true" />

                    <span>
                        {{ trans("peta_kawasan.breadcrumb_home") }}
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
                    <Landmark class="size-4 shrink-0" aria-hidden="true" />

                    <span>
                        {{ trans("peta_kawasan.breadcrumb_kawasan") }}
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
                    <Map
                        class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                        aria-hidden="true"
                    />

                    <span>
                        {{ trans("peta_kawasan.breadcrumb_current") }}
                    </span>
                </span>
            </nav>

            <!-- Heading -->
            <section data-reveal style="--d: 80ms" class="mb-8 max-w-3xl">
                <div
                    class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400"
                >
                    <Map class="size-4" aria-hidden="true" />

                    {{ trans("peta_kawasan.eyebrow") }}
                </div>

                <h1
                    class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                >
                    {{ trans("peta_kawasan.title") }}
                </h1>

                <p
                    class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    {{ trans("peta_kawasan.description") }}
                </p>
            </section>

            <!-- Empty state -->
            <section
                v-if="!petaKawasans.length"
                data-reveal
                style="--d: 140ms"
                class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                >
                    <Map class="size-8" aria-hidden="true" />
                </div>

                <h2
                    class="mt-5 text-xl font-bold text-slate-900 dark:text-white"
                >
                    {{ trans("peta_kawasan.empty_title") }}
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    {{ trans("peta_kawasan.empty_description") }}
                </p>

                <Link
                    href="/kawasan/profil-kawasan"
                    class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                >
                    <Landmark class="size-4" aria-hidden="true" />

                    {{ trans("peta_kawasan.back_profile") }}
                </Link>
            </section>

            <!-- Content -->
            <section
                v-else
                data-reveal
                style="--d: 140ms"
                class="grid gap-6 lg:grid-cols-[340px_1fr]"
            >
                <!-- List -->
                <aside
                    :aria-label="trans('peta_kawasan.map_label')"
                    class="order-2 rounded-3xl border border-slate-200/80 bg-white p-3 shadow-sm lg:order-1 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between px-3 pb-3 pt-1"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                        >
                            {{ trans("peta_kawasan.map_label") }}
                        </p>

                        <span
                            class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                        >
                            {{ petaKawasans.length }}
                        </span>
                    </div>

                    <ul class="space-y-2">
                        <li v-for="item in petaKawasans" :key="item.id">
                            <button
                                type="button"
                                :aria-pressed="selectedId === item.id"
                                :aria-label="
                                    trans('peta_kawasan.aria_open', {
                                        name: getLocalizedValue(item, 'nama'),
                                    })
                                "
                                class="group w-full rounded-2xl border p-4 text-left transition focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="
                                    selectedId === item.id
                                        ? 'border-blue-200 bg-blue-50/70 dark:border-blue-900/60 dark:bg-blue-950/30'
                                        : 'border-slate-200 bg-white hover:border-blue-200 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800/60'
                                "
                                @click="selectPeta(item)"
                            >
                                <span
                                    class="flex items-start justify-between gap-3"
                                >
                                    <span class="min-w-0">
                                        <span
                                            class="block text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                getLocalizedValue(item, "nama")
                                            }}
                                        </span>

                                        <span
                                            v-if="
                                                getLocalizedValue(
                                                    item,
                                                    'deskripsi',
                                                )
                                            "
                                            class="mt-1 block line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                getLocalizedValue(
                                                    item,
                                                    "deskripsi",
                                                )
                                            }}
                                        </span>
                                    </span>

                                    <Map
                                        class="mt-0.5 size-4 shrink-0 transition"
                                        :class="
                                            selectedId === item.id
                                                ? 'text-blue-600 dark:text-blue-400'
                                                : 'text-slate-400 group-hover:text-blue-500'
                                        "
                                        aria-hidden="true"
                                    />
                                </span>
                            </button>
                        </li>
                    </ul>

                    <!-- Navigation -->
                    <div
                        v-if="petaKawasans.length > 1"
                        class="mt-3 grid grid-cols-2 gap-2"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                            @click="selectPrevious"
                        >
                            <ChevronLeft class="size-4" aria-hidden="true" />

                            {{ trans("peta_kawasan.previous") }}
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-3 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @click="selectNext"
                        >
                            {{ trans("peta_kawasan.next") }}

                            <ChevronRight class="size-4" aria-hidden="true" />
                        </button>
                    </div>
                </aside>

                <!-- Main map/image -->
                <article
                    v-if="selected"
                    class="order-1 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm lg:order-2 dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Image -->
                    <div class="relative bg-slate-100 dark:bg-slate-800">
                        <template v-if="getImageUrl(selected.gambar)">
                            <img
                                :src="getImageUrl(selected.gambar) ?? ''"
                                :alt="getLocalizedValue(selected, 'nama')"
                                class="block h-[360px] w-full object-contain sm:h-[500px] lg:h-[620px]"
                                loading="eager"
                            />

                            <button
                                type="button"
                                class="absolute right-4 top-4 inline-flex items-center gap-2 rounded-xl bg-slate-950/75 px-3 py-2.5 text-xs font-semibold text-white backdrop-blur-sm transition hover:bg-slate-950 focus:outline-none focus:ring-2 focus:ring-white"
                                @click="openImagePreview"
                            >
                                <Expand class="size-4" aria-hidden="true" />

                                {{ trans("peta_kawasan.fullscreen") }}
                            </button>
                        </template>

                        <div
                            v-else
                            class="flex h-[360px] flex-col items-center justify-center px-6 text-center sm:h-[500px] lg:h-[620px]"
                        >
                            <div
                                class="flex size-16 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                            >
                                <Map class="size-8" aria-hidden="true" />
                            </div>

                            <h2
                                class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                            >
                                {{ trans("peta_kawasan.image_unavailable") }}
                            </h2>

                            <p
                                class="mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    trans(
                                        "peta_kawasan.image_unavailable_description",
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Information -->
                    <div class="p-5 sm:p-6 lg:p-7">
                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div class="min-w-0">
                                <div
                                    class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400"
                                >
                                    <Map class="size-4" aria-hidden="true" />

                                    {{ trans("peta_kawasan.selected_label") }}
                                </div>

                                <h2
                                    class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ getLocalizedValue(selected, "nama") }}
                                </h2>

                                <p
                                    v-if="
                                        getLocalizedValue(selected, 'deskripsi')
                                    "
                                    class="mt-3 max-w-3xl text-sm leading-7 text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        getLocalizedValue(selected, "deskripsi")
                                    }}
                                </p>
                            </div>

                            <span
                                class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-current"
                                    aria-hidden="true"
                                />

                                {{ trans("peta_kawasan.available") }}
                            </span>
                        </div>

                        <!-- Language indicator -->
                        <div class="mt-5 flex flex-wrap items-center gap-2">
                            <span
                                class="text-xs font-medium text-slate-400 dark:text-slate-500"
                            >
                                {{ trans("peta_kawasan.language") }}:
                            </span>

                            <span
                                class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                            >
                                {{ language }}
                            </span>
                        </div>
                    </div>
                </article>
            </section>

            <!-- Bottom CTA -->
            <section
                v-if="petaKawasans.length"
                data-reveal
                style="--d: 220ms"
                class="mt-8 overflow-hidden rounded-3xl border border-blue-100 bg-blue-50/70 p-6 sm:p-8 dark:border-blue-900/40 dark:bg-blue-950/20"
            >
                <div
                    class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <div
                            class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 dark:text-blue-400"
                        >
                            <Landmark class="size-4" aria-hidden="true" />

                            {{ trans("peta_kawasan.cta_eyebrow") }}
                        </div>

                        <h2
                            class="mt-2 text-xl font-bold text-slate-900 dark:text-white"
                        >
                            {{ trans("peta_kawasan.cta_title") }}
                        </h2>

                        <p
                            class="mt-1 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{ trans("peta_kawasan.cta_description") }}
                        </p>
                    </div>

                    <Link
                        href="/kawasan/profil-kawasan"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950"
                    >
                        {{ trans("peta_kawasan.back_profile") }}

                        <ChevronRight class="size-4" aria-hidden="true" />
                    </Link>
                </div>
            </section>
        </div>

        <!-- Fullscreen image modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isImagePreviewOpen && previewImage"
                class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/95 p-4 backdrop-blur-sm sm:p-8"
                role="dialog"
                aria-modal="true"
                :aria-label="trans('peta_kawasan.image_preview')"
                @click.self="closeImagePreview"
            >
                <button
                    type="button"
                    class="absolute right-4 top-4 z-10 inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white"
                    :aria-label="trans('peta_kawasan.close')"
                    @click="closeImagePreview"
                >
                    <X class="size-6" aria-hidden="true" />
                </button>

                <figure
                    class="flex max-h-full max-w-full flex-col items-center"
                >
                    <img
                        :src="previewImage"
                        :alt="
                            selected
                                ? getLocalizedValue(selected, 'nama')
                                : trans('peta_kawasan.title')
                        "
                        class="max-h-[88vh] max-w-full rounded-2xl object-contain shadow-2xl"
                    />

                    <figcaption
                        v-if="selected"
                        class="mt-4 text-center text-sm font-semibold text-white"
                    >
                        {{ getLocalizedValue(selected, "nama") }}
                    </figcaption>
                </figure>
            </div>
        </Transition>
    </main>
</template>

<style scoped>
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

@media (prefers-reduced-motion: reduce) {
    [data-reveal] {
        opacity: 1;
        transform: none;
        transition: none;
    }
}
</style>
