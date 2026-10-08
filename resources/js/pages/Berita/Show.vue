<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowLeft,
    CalendarDays,
    Check,
    ChevronRight,
    Eye,
    Home,
    Link2,
    Newspaper,
    UserRound,
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

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface Berita {
    id: number;

    // Bahasa Indonesia
    judul_id: string;
    excerpt_id: string | null;
    konten_id: string;

    // English
    judul_en: string | null;
    excerpt_en: string | null;
    konten_en: string | null;

    // Chinese
    judul_zh: string | null;
    excerpt_zh: string | null;
    konten_zh: string | null;

    // Common data
    slug: string;
    gambar: string | null;
    kategori: string | null;
    penulis: string | null;
    status: "draft" | "published" | "archived";
    published_at: string | null;
    is_featured: boolean;
    views: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    berita: Berita;
    terkait?: Berita[];
}

const props = withDefaults(defineProps<Props>(), {
    terkait: () => [],
});

// ---------------------------------------------------------------------------
// Language
// ---------------------------------------------------------------------------

const languageOptions: Array<{
    code: LanguageCode;
    label: string;
}> = [
    { code: "id", label: "Indonesia" },
    { code: "en", label: "English" },
    { code: "zh", label: "中文" },
];

// ---------------------------------------------------------------------------
// UI translation
// ---------------------------------------------------------------------------

const ui = computed(() => {
    const translations = {
        id: {
            home: "Beranda",
            news: "Berita",
            language: "Bahasa",
            chooseLanguage: "Pilih bahasa berita",
            featured: "Berita Pilihan",
            views: "dilihat",
            summary: "Ringkasan",
            content: "Isi Berita",
            contentUnavailable: "Isi berita belum tersedia.",
            share: "Bagikan berita ini",
            copy: "Salin tautan",
            copied: "Tautan disalin",
            whatsapp: "WhatsApp",
            back: "Kembali ke daftar berita",
            related: "Berita lainnya",
        },

        en: {
            home: "Home",
            news: "News",
            language: "Language",
            chooseLanguage: "Choose news language",
            featured: "Featured News",
            views: "views",
            summary: "Summary",
            content: "Article Content",
            contentUnavailable: "News content is not available.",
            share: "Share this news",
            copy: "Copy link",
            copied: "Link copied",
            whatsapp: "WhatsApp",
            back: "Back to news list",
            related: "Other News",
        },

        zh: {
            home: "首页",
            news: "新闻",
            language: "语言",
            chooseLanguage: "选择新闻语言",
            featured: "精选新闻",
            views: "次浏览",
            summary: "摘要",
            content: "新闻内容",
            contentUnavailable: "新闻内容暂不可用。",
            share: "分享这则新闻",
            copy: "复制链接",
            copied: "链接已复制",
            whatsapp: "WhatsApp",
            back: "返回新闻列表",
            related: "其他新闻",
        },
    } as const;

    return translations[currentLanguage.value];
});

// ---------------------------------------------------------------------------
// Localized news value
//
// localizedValue() reads:   field / field_en / field_zh
// Database columns:         judul_id / excerpt_id / konten_id (+ _en, _zh)
// Indonesian is used as the base (fallback) field.
// ---------------------------------------------------------------------------

const localizedBeritaValue = (
    berita: Berita,
    field: "judul" | "excerpt" | "konten",
): string => {
    const source: Record<string, unknown> = {
        ...berita,
        judul: berita.judul_id,
        excerpt: berita.excerpt_id,
        konten: berita.konten_id,
    };

    return localizedValue(source, field);
};

const currentTitle = computed(() =>
    localizedBeritaValue(props.berita, "judul"),
);

const currentExcerpt = computed(() =>
    localizedBeritaValue(props.berita, "excerpt"),
);

const currentContent = computed(() =>
    localizedBeritaValue(props.berita, "konten"),
);

// ---------------------------------------------------------------------------
// Image
// ---------------------------------------------------------------------------

const imageFailed = ref(false);

const resolveImage = (gambar: string | null): string | null => {
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

const heroImage = computed(() => {
    return imageFailed.value ? null : resolveImage(props.berita.gambar);
});

// ---------------------------------------------------------------------------
// Date
// ---------------------------------------------------------------------------

const formatDate = (date: string | null): string => {
    if (!date) {
        return "";
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return "";
    }

    const locale =
        currentLanguage.value === "en"
            ? "en-US"
            : currentLanguage.value === "zh"
              ? "zh-CN"
              : "id-ID";

    return new Intl.DateTimeFormat(locale, {
        day: "2-digit",
        month: "long",
        year: "numeric",
    }).format(parsed);
};

// ---------------------------------------------------------------------------
// HTML helpers
// ---------------------------------------------------------------------------

const stripHtml = (text: string | null): string => {
    if (!text) {
        return "";
    }

    return text
        .replace(/<[^>]*>/g, "")
        .replace(/&nbsp;/gi, " ")
        .replace(/&amp;/gi, "&")
        .replace(/&quot;/gi, '"')
        .replace(/&#039;|&#39;/gi, "'")
        .replace(/\s+/g, " ")
        .trim();
};

const metaDescription = computed(() => {
    const source = currentExcerpt.value || currentContent.value;

    const clean = stripHtml(source);

    return clean.length > 160 ? `${clean.slice(0, 160).trim()}…` : clean;
});

// ---------------------------------------------------------------------------
// Article content
// ---------------------------------------------------------------------------

const kontenHtml = computed(() => {
    const konten = currentContent.value?.trim();

    if (!konten) {
        return "";
    }

    // Already HTML (from a rich text editor): render as is.
    if (/<\/?[a-z][\s\S]*>/i.test(konten)) {
        return konten;
    }

    // Plain text: convert to paragraphs.
    return konten
        .split(/\n{2,}/)
        .map((paragraph) => {
            const escaped = paragraph
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/\n/g, "<br />");

            return `<p>${escaped}</p>`;
        })
        .join("");
});

// ---------------------------------------------------------------------------
// Share
// ---------------------------------------------------------------------------

const copied = ref(false);

let copiedTimer: ReturnType<typeof setTimeout> | null = null;

const currentUrl = (): string => {
    return typeof window !== "undefined" ? window.location.href : "";
};

const whatsappUrl = computed(() => {
    const text = `${currentTitle.value} - ${currentUrl()}`;

    return `https://wa.me/?text=${encodeURIComponent(text)}`;
});

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(currentUrl());

        copied.value = true;

        if (copiedTimer) {
            clearTimeout(copiedTimer);
        }

        copiedTimer = setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        // Clipboard is not available.
    }
};

// Reset per-article state when navigating to another article
// (Inertia may reuse this component instance).
watch(
    () => props.berita.id,
    () => {
        imageFailed.value = false;
        copied.value = false;
    },
);

onBeforeUnmount(() => {
    if (copiedTimer) {
        clearTimeout(copiedTimer);
    }
});
</script>

<template>
    <Head>
        <title>{{ currentTitle }} | {{ ui.news }} KITB</title>

        <meta name="description" :content="metaDescription" />

        <meta property="og:title" :content="currentTitle" />

        <meta property="og:description" :content="metaDescription" />

        <meta property="og:type" content="article" />

        <meta
            v-if="resolveImage(berita.gambar)"
            property="og:image"
            :content="resolveImage(berita.gambar) ?? ''"
        />
    </Head>

    <main class="relative min-h-screen bg-slate-50/50 dark:bg-slate-950">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav
                aria-label="Breadcrumb"
                class="mb-7 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400"
            >
                <Link
                    href="/"
                    class="inline-flex items-center gap-1.5 rounded-lg px-1.5 py-1 transition hover:bg-white hover:text-blue-600 dark:hover:bg-slate-900 dark:hover:text-blue-400"
                >
                    <Home class="h-3.5 w-3.5" aria-hidden="true" />

                    <span>{{ ui.home }}</span>
                </Link>

                <ChevronRight
                    class="h-3.5 w-3.5 shrink-0 text-slate-400"
                    aria-hidden="true"
                />

                <Link
                    href="/berita"
                    class="inline-flex items-center gap-1.5 rounded-lg px-1.5 py-1 transition hover:bg-white hover:text-blue-600 dark:hover:bg-slate-900 dark:hover:text-blue-400"
                >
                    <Newspaper class="h-3.5 w-3.5" aria-hidden="true" />

                    <span>{{ ui.news }}</span>
                </Link>

                <ChevronRight
                    class="h-3.5 w-3.5 shrink-0 text-slate-400"
                    aria-hidden="true"
                />

                <span
                    class="line-clamp-1 max-w-[16rem] font-semibold text-blue-600 dark:text-blue-400"
                    aria-current="page"
                >
                    {{ currentTitle }}
                </span>
            </nav>

            <!-- Language Selector -->
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white/80 p-2 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/80"
            >
                <div class="flex items-center gap-2 px-2">
                    <span
                        class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                    >
                        {{ ui.language }}
                    </span>
                </div>

                <div
                    class="flex items-center gap-1 rounded-xl bg-slate-100 p-1 dark:bg-slate-800"
                    role="group"
                    :aria-label="ui.chooseLanguage"
                >
                    <button
                        v-for="language in languageOptions"
                        :key="language.code"
                        type="button"
                        :aria-pressed="currentLanguage === language.code"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-500/20"
                        :class="
                            currentLanguage === language.code
                                ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-700 dark:text-blue-300'
                                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'
                        "
                        @click="currentLanguage = language.code"
                    >
                        {{ language.label }}
                    </button>
                </div>
            </div>

            <article>
                <!-- Article Header -->
                <header class="mb-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            v-if="berita.kategori"
                            class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                        >
                            {{ berita.kategori }}
                        </span>

                        <span
                            v-if="berita.is_featured"
                            class="inline-flex items-center rounded-full bg-blue-600 px-3 py-1 text-[11px] font-bold text-white"
                        >
                            {{ ui.featured }}
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-balance text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                    >
                        {{ currentTitle }}
                    </h1>

                    <div
                        class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-500 dark:text-slate-400"
                    >
                        <span
                            v-if="berita.published_at"
                            class="inline-flex items-center gap-2"
                        >
                            <CalendarDays
                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                aria-hidden="true"
                            />

                            <time :datetime="berita.published_at">
                                {{ formatDate(berita.published_at) }}
                            </time>
                        </span>

                        <span
                            v-if="berita.penulis"
                            class="inline-flex items-center gap-2"
                        >
                            <UserRound
                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                aria-hidden="true"
                            />

                            {{ berita.penulis }}
                        </span>

                        <span
                            v-if="berita.views > 0"
                            class="inline-flex items-center gap-2"
                        >
                            <Eye
                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                aria-hidden="true"
                            />

                            {{ berita.views }} {{ ui.views }}
                        </span>
                    </div>
                </header>

                <!-- Hero Image -->
                <figure
                    v-if="heroImage"
                    class="mb-8 overflow-hidden rounded-3xl border border-slate-200/80 bg-slate-100 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <img
                        :src="heroImage"
                        :alt="currentTitle"
                        class="max-h-[34rem] w-full object-cover"
                        @error="imageFailed = true"
                    />
                </figure>

                <!-- Article Body -->
                <div
                    class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
                >
                    <template v-if="currentExcerpt">
                        <h2 class="sr-only">
                            {{ ui.summary }}
                        </h2>

                        <p
                            class="mb-6 border-l-4 border-blue-600 pl-4 text-base font-medium leading-7 text-slate-700 dark:border-blue-400 dark:text-slate-200"
                        >
                            {{ stripHtml(currentExcerpt) }}
                        </p>
                    </template>

                    <h2 class="sr-only">
                        {{ ui.content }}
                    </h2>

                    <div
                        v-if="kontenHtml"
                        class="berita-content"
                        v-html="kontenHtml"
                    />

                    <p
                        v-else
                        class="text-sm text-slate-500 dark:text-slate-400"
                    >
                        {{ ui.contentUnavailable }}
                    </p>

                    <!-- Share -->
                    <div
                        class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800"
                    >
                        <span
                            class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ ui.share }}
                        </span>

                        <button
                            type="button"
                            class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 transition hover:border-blue-200 hover:text-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-400"
                            @click="copyLink"
                        >
                            <Check
                                v-if="copied"
                                class="h-4 w-4 text-emerald-600"
                                aria-hidden="true"
                            />

                            <Link2 v-else class="h-4 w-4" aria-hidden="true" />

                            <span aria-live="polite">
                                {{ copied ? ui.copied : ui.copy }}
                            </span>
                        </button>

                        <a
                            :href="whatsappUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-10 items-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/20"
                        >
                            {{ ui.whatsapp }}
                        </a>
                    </div>
                </div>
            </article>

            <!-- Back -->
            <div class="mt-8">
                <Link
                    href="/berita"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-blue-200 hover:text-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-800 dark:hover:text-blue-400"
                >
                    <ArrowLeft class="h-4 w-4" aria-hidden="true" />

                    {{ ui.back }}
                </Link>
            </div>

            <!-- Related News -->
            <section
                v-if="terkait.length"
                class="mt-12"
                :aria-label="ui.related"
            >
                <h2
                    class="mb-4 text-lg font-bold text-slate-900 dark:text-white"
                >
                    {{ ui.related }}
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="item in terkait"
                        :key="item.id"
                        :href="`/berita/${item.slug}`"
                        class="group overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="aspect-[16/10] bg-slate-100 dark:bg-slate-800"
                        >
                            <img
                                v-if="resolveImage(item.gambar)"
                                :src="resolveImage(item.gambar) ?? undefined"
                                :alt="localizedBeritaValue(item, 'judul')"
                                loading="lazy"
                                class="h-full w-full object-cover"
                            />

                            <div
                                v-else
                                class="flex h-full items-center justify-center text-slate-400"
                            >
                                <Newspaper class="h-8 w-8" aria-hidden="true" />
                            </div>
                        </div>

                        <div class="p-4">
                            <h3
                                class="line-clamp-2 text-sm font-bold leading-6 text-slate-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                            >
                                {{ localizedBeritaValue(item, "judul") }}
                            </h3>

                            <p
                                v-if="item.published_at"
                                class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                            >
                                {{ formatDate(item.published_at) }}
                            </p>
                        </div>
                    </Link>
                </div>
            </section>
        </div>
    </main>
</template>

<style scoped>
.berita-content {
    font-size: 1rem;
    line-height: 1.85;
    color: rgb(51 65 85);
    overflow-wrap: anywhere;
}

.dark .berita-content {
    color: rgb(203 213 225);
}

.berita-content :deep(p) {
    margin: 0 0 1.15em;
}

.berita-content :deep(h2),
.berita-content :deep(h3),
.berita-content :deep(h4) {
    margin: 1.8em 0 0.6em;
    font-weight: 700;
    line-height: 1.35;
    color: rgb(15 23 42);
}

.dark .berita-content :deep(h2),
.dark .berita-content :deep(h3),
.dark .berita-content :deep(h4) {
    color: #fff;
}

.berita-content :deep(h2) {
    font-size: 1.4rem;
}

.berita-content :deep(h3) {
    font-size: 1.2rem;
}

.berita-content :deep(h4) {
    font-size: 1.05rem;
}

.berita-content :deep(a) {
    color: rgb(37 99 235);
    text-decoration: underline;
    text-underline-offset: 3px;
}

.dark .berita-content :deep(a) {
    color: rgb(96 165 250);
}

.berita-content :deep(ul),
.berita-content :deep(ol) {
    margin: 0 0 1.15em;
    padding-left: 1.5rem;
}

.berita-content :deep(ul) {
    list-style: disc;
}

.berita-content :deep(ol) {
    list-style: decimal;
}

.berita-content :deep(li) {
    margin-bottom: 0.4em;
}

.berita-content :deep(blockquote) {
    margin: 1.5em 0;
    padding: 0.25em 0 0.25em 1.1rem;
    border-left: 4px solid rgb(37 99 235);
    color: rgb(71 85 105);
    font-style: italic;
}

.dark .berita-content :deep(blockquote) {
    color: rgb(148 163 184);
}

.berita-content :deep(img) {
    max-width: 100%;
    height: auto;
    margin: 1.5em auto;
    border-radius: 1rem;
}

.berita-content :deep(table) {
    display: block;
    width: 100%;
    margin: 1.5em 0;
    overflow-x: auto;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.berita-content :deep(th),
.berita-content :deep(td) {
    padding: 0.55rem 0.75rem;
    border: 1px solid rgb(226 232 240);
    text-align: left;
}

.dark .berita-content :deep(th),
.dark .berita-content :deep(td) {
    border-color: rgb(51 65 85);
}

.berita-content :deep(iframe),
.berita-content :deep(video) {
    max-width: 100%;
    border-radius: 1rem;
}

.berita-content > :deep(:last-child) {
    margin-bottom: 0;
}
</style>
