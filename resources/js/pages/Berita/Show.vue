<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from "vue";
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

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
 * Types
 * ========================================================= */

interface Berita {
    id: number;
    judul: string;
    slug: string;
    excerpt: string | null;
    konten: string | null;
    gambar: string | null;
    kategori: string | null;
    penulis: string | null;
    status: string;
    published_at: string | null;
    is_featured: boolean;
    views: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    berita: Berita;
    // Opsional: kirim dari controller bila ingin menampilkan berita terkait
    terkait?: Berita[];
}

const props = withDefaults(defineProps<Props>(), {
    terkait: () => [],
});

/* =========================================================
 * Helpers
 * ========================================================= */

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

const formatDate = (date: string | null): string => {
    if (!date) {
        return "";
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return "";
    }

    return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    }).format(parsed);
};

const stripHtml = (text: string | null): string => {
    if (!text) {
        return "";
    }

    return text
        .replace(/<[^>]*>/g, "")
        .replace(/&nbsp;/gi, " ")
        .replace(/&amp;/gi, "&")
        .replace(/&quot;/gi, '"')
        .replace(/&#039;/gi, "'")
        .replace(/\s+/g, " ")
        .trim();
};

const metaDescription = computed(() => {
    const source = props.berita.excerpt || props.berita.konten;
    const clean = stripHtml(source);

    return clean.length > 160 ? `${clean.slice(0, 160).trim()}…` : clean;
});

/**
 * Konten bisa berupa HTML (dari rich text editor) atau teks biasa.
 * Teks biasa diubah menjadi paragraf agar enter tetap terbaca.
 */
const kontenHtml = computed(() => {
    const konten = props.berita.konten?.trim();

    if (!konten) {
        return "";
    }

    if (/<\/?[a-z][\s\S]*>/i.test(konten)) {
        return konten;
    }

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

/* =========================================================
 * Share
 * ========================================================= */

const copied = ref(false);
let copiedTimer: ReturnType<typeof setTimeout> | null = null;

const currentUrl = (): string => {
    return typeof window !== "undefined" ? window.location.href : "";
};

const whatsappUrl = computed(() => {
    const text = `${props.berita.judul} - ${currentUrl()}`;

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
        // Clipboard tidak tersedia (mis. koneksi non-HTTPS); abaikan.
    }
};

onBeforeUnmount(() => {
    if (copiedTimer) {
        clearTimeout(copiedTimer);
    }
});
</script>

<template>
    <Head>
        <title>{{ berita.judul }} | Berita KITB</title>

        <meta name="description" :content="metaDescription" />
        <meta property="og:title" :content="berita.judul" />
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
                    <span>Beranda</span>
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
                    <span>Berita</span>
                </Link>

                <ChevronRight
                    class="h-3.5 w-3.5 shrink-0 text-slate-400"
                    aria-hidden="true"
                />

                <span
                    class="line-clamp-1 max-w-[16rem] font-semibold text-blue-600 dark:text-blue-400"
                    aria-current="page"
                >
                    {{ berita.judul }}
                </span>
            </nav>

            <article>
                <!-- Header -->

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
                            Berita Pilihan
                        </span>
                    </div>

                    <h1
                        class="mt-4 text-balance text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                    >
                        {{ berita.judul }}
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
                            {{ berita.views }} dilihat
                        </span>
                    </div>
                </header>

                <!-- Image -->

                <figure
                    v-if="heroImage"
                    class="mb-8 overflow-hidden rounded-3xl border border-slate-200/80 bg-slate-100 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <img
                        :src="heroImage"
                        :alt="berita.judul"
                        class="max-h-[34rem] w-full object-cover"
                        @error="imageFailed = true"
                    />
                </figure>

                <!-- Body -->

                <div
                    class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
                >
                    <p
                        v-if="berita.excerpt"
                        class="mb-6 border-l-4 border-blue-600 pl-4 text-base font-medium leading-7 text-slate-700 dark:border-blue-400 dark:text-slate-200"
                    >
                        {{ stripHtml(berita.excerpt) }}
                    </p>

                    <!-- Konten dari admin (rich text). Pastikan disanitasi di sisi server. -->
                    <div
                        v-if="kontenHtml"
                        class="berita-content"
                        v-html="kontenHtml"
                    />

                    <p
                        v-else
                        class="text-sm text-slate-500 dark:text-slate-400"
                    >
                        Isi berita belum tersedia.
                    </p>

                    <!-- Share -->

                    <div
                        class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-6 dark:border-slate-800"
                    >
                        <span
                            class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            Bagikan berita ini
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
                                {{ copied ? "Tautan disalin" : "Salin tautan" }}
                            </span>
                        </button>

                        <a
                            :href="whatsappUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-10 items-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/20"
                        >
                            WhatsApp
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
                    Kembali ke daftar berita
                </Link>
            </div>

            <!-- Related (opsional, tampil bila controller mengirim `terkait`) -->

            <section v-if="terkait.length" class="mt-12" aria-label="Berita lainnya">
                <h2
                    class="mb-4 text-lg font-bold text-slate-900 dark:text-white"
                >
                    Berita lainnya
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
                                :alt="item.judul"
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
                                {{ item.judul }}
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
/* =========================================================
 * Konten berita (hasil rich text / v-html)
 * Memakai :deep karena elemen dibuat oleh v-html.
 * ========================================================= */

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

.berita-content :deep(> :last-child) {
    margin-bottom: 0;
}
</style>
