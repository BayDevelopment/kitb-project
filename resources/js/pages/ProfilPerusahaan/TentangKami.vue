<script setup lang="ts">
import { computed } from "vue";
import { Head } from "@inertiajs/vue3";
import { Building2, Globe, Mail, MapPin, Phone, Quote } from "lucide-vue-next";
import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

interface Profile {
    nama_perusahaan: string;
    tentang_kami: string | null;
    moto: string | null;
    alamat: string | null;
    email: string | null;
    telepon: string | null;
    website: string | null;
    logo_url: string | null;
}

const props = defineProps<{
    profile: Profile | null;
}>();

/**
 * |--------------------------------------------------------------------------
 * | Konten
 * |--------------------------------------------------------------------------
 *
 * Teks dipecah per paragraf (dipisahkan baris kosong).
 * Dirender sebagai teks biasa, bukan HTML.
 */

const paragraphs = computed(() => {
    const text = props.profile?.tentang_kami?.trim();

    if (!text) {
        return [];
    }

    return text
        .split(/\r?\n\s*\r?\n/)
        .map((item) => item.trim())
        .filter(Boolean);
});

const leadParagraph = computed(() => paragraphs.value[0] ?? null);
const otherParagraphs = computed(() => paragraphs.value.slice(1));

const websiteHref = computed(() => {
    const website = props.profile?.website?.trim();

    if (!website) {
        return null;
    }

    return /^https?:\/\//i.test(website) ? website : `https://${website}`;
});

const websiteLabel = computed(() =>
    (props.profile?.website ?? "")
        .replace(/^https?:\/\//i, "")
        .replace(/\/$/, ""),
);

const phoneHref = computed(() => {
    const phone = props.profile?.telepon?.replace(/[^\d+]/g, "");

    return phone ? `tel:${phone}` : null;
});

const contacts = computed(() => {
    const profile = props.profile;

    if (!profile) {
        return [];
    }

    return [
        {
            key: "alamat",
            label: "Alamat",
            value: profile.alamat,
            href: null as string | null,
            icon: MapPin,
        },
        {
            key: "email",
            label: "Email",
            value: profile.email,
            href: profile.email ? `mailto:${profile.email}` : null,
            icon: Mail,
        },
        {
            key: "telepon",
            label: "Telepon",
            value: profile.telepon,
            href: phoneHref.value,
            icon: Phone,
        },
        {
            key: "website",
            label: "Website",
            value: websiteLabel.value,
            href: websiteHref.value,
            icon: Globe,
        },
    ].filter((item) => item.value && String(item.value).trim() !== "");
});

/**
 * |--------------------------------------------------------------------------
 * | SEO
 * |--------------------------------------------------------------------------
 */

const metaDescription = computed(() => {
    const text = (props.profile?.tentang_kami ?? "")
        .replace(/\s+/g, " ")
        .trim();

    if (!text) {
        return "Kenali profil PT Kawasan Industri Tanjung Buton (KITB).";
    }

    return text.length > 155 ? `${text.slice(0, 155)}...` : text;
});

const canonicalUrl =
    "https://tanjungbuton-industrial.co.id/profil-perusahaan/tentang-kami";

const ogImage = computed(
    () =>
        props.profile?.logo_url ??
        "https://tanjungbuton-industrial.co.id/logoside.png",
);
</script>

<template>
    <Head>
        <title>Tentang Kami | KITB</title>
        <meta name="description" :content="metaDescription" />
        <link rel="canonical" :href="canonicalUrl" />
        <meta property="og:title" content="Tentang Kami | KITB" />
        <meta property="og:description" :content="metaDescription" />
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:image" :content="ogImage" />
    </Head>

    <div
        class="relative min-h-screen overflow-x-clip bg-slate-50/70 dark:bg-slate-950"
    >
        <!-- =========================================================
             BLOBS
        ========================================================== -->
        <div
            class="pointer-events-none absolute inset-x-0 -top-28 h-[760px] overflow-hidden"
            aria-hidden="true"
        >
            <!-- Pita warna di bagian paling atas (di belakang navbar) -->
            <div
                class="absolute inset-x-0 top-0 h-60 bg-gradient-to-b from-blue-100/70 via-blue-50/40 to-transparent dark:from-blue-950/30 dark:via-blue-950/10"
            />

            <div
                class="blob blob-a absolute left-[2%] top-0 size-[26rem] rounded-full bg-gradient-to-br from-blue-400/35 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15"
            />

            <div
                class="blob blob-b absolute right-[2%] top-4 size-[22rem] rounded-full bg-gradient-to-tr from-sky-300/35 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10"
            />

            <div
                class="blob blob-c absolute left-1/3 top-56 size-72 rounded-full bg-gradient-to-br from-indigo-300/20 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/10"
            />

            <div
                class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-b from-transparent to-slate-50/90 dark:to-slate-950/90"
            />
        </div>

        <!-- =========================================================
             CONTENT
        ========================================================== -->
        <div
            class="relative z-10 mx-auto w-full max-w-6xl px-4 pb-16 pt-28 sm:px-6 sm:pt-32 lg:px-8 lg:pb-24 lg:pt-36"
        >
            <!-- HERO -->
            <header class="max-w-3xl">
                <div class="reveal flex items-center gap-4" style="--d: 0">
                    <div
                        class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <img
                            v-if="profile?.logo_url"
                            :src="profile.logo_url"
                            :alt="`Logo ${profile.nama_perusahaan}`"
                            class="size-full object-contain p-2"
                        />

                        <Building2
                            v-else
                            class="size-7 text-blue-600 dark:text-blue-400"
                        />
                    </div>

                    <p
                        v-if="profile"
                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        {{ profile.nama_perusahaan }}
                    </p>
                </div>

                <h1
                    class="reveal mt-6 text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white"
                    style="--d: 120"
                >
                    Tentang Kami
                </h1>

                <p
                    v-if="profile?.moto"
                    class="reveal mt-5 flex max-w-2xl gap-3 text-base leading-7 text-slate-600 sm:text-lg dark:text-slate-300"
                    style="--d: 240"
                >
                    <Quote
                        class="mt-1 size-5 shrink-0 text-blue-500 dark:text-blue-400"
                        aria-hidden="true"
                    />

                    <span>{{ profile.moto }}</span>
                </p>
            </header>

            <!-- BODY -->
            <div
                v-if="profile"
                class="mt-12 grid gap-6 lg:mt-14 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-8"
            >
                <!-- Narasi -->
                <article
                    class="reveal min-w-0 rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm shadow-slate-200/50 backdrop-blur-sm sm:p-10 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
                    style="--d: 360"
                >
                    <template v-if="leadParagraph">
                        <p
                            class="max-w-[68ch] text-lg font-medium leading-8 text-slate-800 dark:text-slate-100"
                        >
                            {{ leadParagraph }}
                        </p>

                        <div
                            v-if="otherParagraphs.length"
                            class="mt-6 max-w-[68ch] space-y-5"
                        >
                            <p
                                v-for="(paragraph, index) in otherParagraphs"
                                :key="index"
                                class="whitespace-pre-line text-base leading-8 text-slate-600 dark:text-slate-300"
                            >
                                {{ paragraph }}
                            </p>
                        </div>
                    </template>

                    <p
                        v-else
                        class="text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Informasi tentang perusahaan belum tersedia.
                    </p>
                </article>

                <!-- Kontak -->
                <aside
                    v-if="contacts.length"
                    class="reveal min-w-0 lg:sticky lg:top-28 lg:self-start"
                    style="--d: 480"
                >
                    <div
                        class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm shadow-slate-200/50 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
                    >
                        <h2
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Hubungi kami
                        </h2>

                        <ul class="mt-5 space-y-5">
                            <li
                                v-for="item in contacts"
                                :key="item.key"
                                class="flex gap-3"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                >
                                    <component :is="item.icon" class="size-4" />
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="text-xs font-medium text-slate-400 dark:text-slate-500"
                                    >
                                        {{ item.label }}
                                    </p>

                                    <a
                                        v-if="item.href"
                                        :href="item.href"
                                        :target="
                                            item.key === 'website'
                                                ? '_blank'
                                                : undefined
                                        "
                                        :rel="
                                            item.key === 'website'
                                                ? 'noopener noreferrer'
                                                : undefined
                                        "
                                        class="mt-0.5 block break-words text-sm font-semibold text-slate-700 transition hover:text-blue-600 focus:outline-none focus-visible:underline dark:text-slate-200 dark:hover:text-blue-400"
                                    >
                                        {{ item.value }}
                                    </a>

                                    <p
                                        v-else
                                        class="mt-0.5 whitespace-pre-line break-words text-sm font-semibold leading-6 text-slate-700 dark:text-slate-200"
                                    >
                                        {{ item.value }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>

            <!-- EMPTY -->
            <div
                v-else
                class="reveal mt-12 rounded-3xl border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900/70"
                style="--d: 300"
            >
                <div
                    class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                >
                    <Building2 class="size-6" />
                </div>

                <h2
                    class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                >
                    Profil perusahaan belum tersedia
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    Informasi sedang disiapkan. Silakan kembali lagi nanti.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ==========================================================================
   Fade-in saat halaman dimuat (bertahap lewat --d dalam milidetik)
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
   Aksesibilitas
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
}
</style>
