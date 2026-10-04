<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import {
    Building2,
    CheckCircle2,
    Clock3,
    Facebook,
    Globe,
    Instagram,
    Linkedin,
    LoaderCircle,
    Mail,
    Map,
    MapPin,
    Phone,
    Save,
    Youtube,
} from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

/* =========================================================
   Types
========================================================= */

interface PengaturanKontak {
    id: number;
    nama_perusahaan: string | null;
    alamat: string | null;
    telepon: string | null;
    whatsapp: string | null;
    email: string | null;
    email_investor: string | null;
    jam_operasional: string | null;
    latitude: number | null;
    longitude: number | null;
    maps_embed_url: string | null;
    facebook: string | null;
    instagram: string | null;
    linkedin: string | null;
    youtube: string | null;
}

interface Props {
    pengaturan: PengaturanKontak;
}

const props = defineProps<Props>();

/* =========================================================
   Page / Flash
========================================================= */

const page = usePage();

const flashSuccess = computed(() => {
    const pageProps = page.props as typeof page.props & {
        flash?: {
            success?: string;
        };
    };

    return pageProps.flash?.success;
});

/* =========================================================
   Loading
========================================================= */

const isPageLoading = ref(true);
const isSaving = ref(false);

/* =========================================================
   Reduced Motion + Reveal
========================================================= */

const prefersReducedMotion = ref(false);

let mediaQuery: MediaQueryList | null = null;
let revealObserver: IntersectionObserver | null = null;

const handleReducedMotionChange = (event: MediaQueryListEvent) => {
    prefersReducedMotion.value = event.matches;

    if (event.matches) {
        revealObserver?.disconnect();
        revealObserver = null;

        document
            .querySelectorAll<HTMLElement>("[data-reveal]")
            .forEach((element) => {
                element.classList.add("is-visible");
            });

        return;
    }

    initializeReveal();
};

const initializeReveal = async () => {
    await nextTick();

    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (!elements.length) {
        return;
    }

    if (
        prefersReducedMotion.value ||
        typeof IntersectionObserver === "undefined"
    ) {
        elements.forEach((element) => {
            element.classList.add("is-visible");
        });

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

    elements.forEach((element) => {
        revealObserver?.observe(element);
    });
};

/* =========================================================
   Form
========================================================= */

const form = useForm({
    nama_perusahaan: props.pengaturan.nama_perusahaan ?? "",

    alamat: props.pengaturan.alamat ?? "",

    telepon: props.pengaturan.telepon ?? "",

    whatsapp: props.pengaturan.whatsapp ?? "",

    email: props.pengaturan.email ?? "",

    email_investor: props.pengaturan.email_investor ?? "",

    jam_operasional: props.pengaturan.jam_operasional ?? "",

    latitude: props.pengaturan.latitude ?? "",

    longitude: props.pengaturan.longitude ?? "",

    maps_embed_url: props.pengaturan.maps_embed_url ?? "",

    facebook: props.pengaturan.facebook ?? "",

    instagram: props.pengaturan.instagram ?? "",

    linkedin: props.pengaturan.linkedin ?? "",

    youtube: props.pengaturan.youtube ?? "",
});

/* =========================================================
   Computed
========================================================= */

const hasErrors = computed(() => {
    return Object.keys(form.errors).length > 0;
});

/* =========================================================
   Submit
========================================================= */

const submit = () => {
    isSaving.value = true;

    form.put("/admin/kontak/pengaturan", {
        preserveScroll: true,

        onFinish: () => {
            isSaving.value = false;
        },
    });
};

/* =========================================================
   Lifecycle
========================================================= */

onMounted(async () => {
    mediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");

    prefersReducedMotion.value = mediaQuery.matches;

    mediaQuery.addEventListener("change", handleReducedMotionChange);

    await new Promise<void>((resolve) => {
        window.setTimeout(resolve, 350);
    });

    isPageLoading.value = false;

    await nextTick();

    await initializeReveal();
});

onBeforeUnmount(() => {
    revealObserver?.disconnect();
    revealObserver = null;

    mediaQuery?.removeEventListener("change", handleReducedMotionChange);
});
</script>

<template>
    <Head title="Pengaturan Kontak" />

    <div
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- =====================================================
             Background Blobs
        ====================================================== -->

        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 overflow-hidden"
        >
            <div
                class="absolute -left-32 -top-32 h-80 w-80 rounded-full bg-blue-500/10 blur-3xl dark:bg-blue-500/10"
            />

            <div
                class="absolute -right-40 top-24 h-96 w-96 rounded-full bg-indigo-500/10 blur-3xl dark:bg-indigo-500/10"
            />

            <div
                class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-sky-500/5 blur-3xl"
            />
        </div>

        <!-- =====================================================
             Skeleton
        ====================================================== -->

        <div
            v-if="isPageLoading"
            class="relative z-10 mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        >
            <div class="animate-pulse space-y-6">
                <div class="h-4 w-48 rounded bg-slate-200 dark:bg-slate-800" />

                <div
                    class="h-10 w-72 rounded-lg bg-slate-200 dark:bg-slate-800"
                />

                <div
                    class="h-4 w-full max-w-xl rounded bg-slate-200 dark:bg-slate-800"
                />

                <div class="grid gap-6 lg:grid-cols-2">
                    <div
                        class="h-[390px] rounded-2xl bg-slate-200 dark:bg-slate-800"
                    />

                    <div
                        class="h-[390px] rounded-2xl bg-slate-200 dark:bg-slate-800"
                    />
                </div>

                <div
                    class="h-[300px] rounded-2xl bg-slate-200 dark:bg-slate-800"
                />
            </div>
        </div>

        <!-- =====================================================
             Content
        ====================================================== -->

        <main
            v-else
            class="relative z-10 mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
        >
            <!-- =================================================
                 Breadcrumb
            ================================================== -->

            <div data-reveal class="reveal mb-6" style="--d: 0ms">
                <nav
                    aria-label="Breadcrumb"
                    class="flex flex-wrap items-center gap-2 text-sm"
                >
                    <a
                        href="/admin"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                    >
                        <Building2 class="size-4 shrink-0" aria-hidden="true" />

                        <span>Dashboard</span>
                    </a>

                    <span
                        class="text-slate-300 dark:text-slate-700"
                        aria-hidden="true"
                    >
                        /
                    </span>

                    <span
                        class="font-medium text-slate-500 dark:text-slate-400"
                    >
                        Kontak
                    </span>

                    <span
                        class="text-slate-300 dark:text-slate-700"
                        aria-hidden="true"
                    >
                        /
                    </span>

                    <span
                        class="font-semibold text-blue-600 dark:text-blue-400"
                    >
                        Pengaturan Kontak
                    </span>
                </nav>
            </div>

            <!-- =================================================
                 Header
            ================================================== -->

            <section data-reveal class="reveal mb-7" style="--d: 60ms">
                <div
                    class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="max-w-3xl">
                        <div
                            class="mb-3 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300"
                        >
                            <Phone class="size-3.5" aria-hidden="true" />

                            Informasi Kontak
                        </div>

                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            Pengaturan Kontak
                        </h1>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Kelola identitas perusahaan, informasi kontak,
                            lokasi, dan media sosial yang digunakan pada halaman
                            publik KITB.
                        </p>
                    </div>

                    <div
                        v-if="flashSuccess"
                        class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300"
                        role="status"
                        aria-live="polite"
                    >
                        <CheckCircle2 class="size-4" aria-hidden="true" />

                        {{ flashSuccess }}
                    </div>
                </div>
            </section>

            <!-- =================================================
                 Form
            ================================================== -->

            <form class="space-y-6" @submit.prevent="submit">
                <!-- =================================================
                     Identitas & Alamat
                ================================================== -->

                <section
                    data-reveal
                    class="reveal overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    style="--d: 100ms"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-5 sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <Building2 class="size-5" aria-hidden="true" />
                            </div>

                            <div>
                                <h2
                                    class="text-base font-semibold text-slate-900 dark:text-white"
                                >
                                    Identitas & Alamat
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Informasi dasar perusahaan dan alamat
                                    kantor.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:p-6">
                        <div>
                            <label
                                for="nama_perusahaan"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Nama Perusahaan
                            </label>

                            <input
                                id="nama_perusahaan"
                                v-model="form.nama_perusahaan"
                                type="text"
                                maxlength="255"
                                autocomplete="organization"
                                placeholder="PT Kawasan Industri Tanjung Buton"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                :aria-invalid="!!form.errors.nama_perusahaan"
                            />

                            <p
                                v-if="form.errors.nama_perusahaan"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.nama_perusahaan }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="alamat"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Alamat
                            </label>

                            <textarea
                                id="alamat"
                                v-model="form.alamat"
                                rows="4"
                                maxlength="5000"
                                autocomplete="street-address"
                                placeholder="Masukkan alamat lengkap perusahaan..."
                                class="w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                :aria-invalid="!!form.errors.alamat"
                            />

                            <p
                                v-if="form.errors.alamat"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.alamat }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- =================================================
                     Kontak
                ================================================== -->

                <section
                    data-reveal
                    class="reveal overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    style="--d: 160ms"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-5 sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
                            >
                                <Phone class="size-5" aria-hidden="true" />
                            </div>

                            <div>
                                <h2
                                    class="text-base font-semibold text-slate-900 dark:text-white"
                                >
                                    Informasi Kontak
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Nomor telepon, WhatsApp, email, dan jam
                                    operasional.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <!-- Telepon -->

                        <div>
                            <label
                                for="telepon"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Telepon
                            </label>

                            <div class="relative">
                                <Phone
                                    class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    aria-hidden="true"
                                />

                                <input
                                    id="telepon"
                                    v-model="form.telepon"
                                    type="tel"
                                    maxlength="30"
                                    autocomplete="tel"
                                    placeholder="+62..."
                                    class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    :aria-invalid="!!form.errors.telepon"
                                />
                            </div>

                            <p
                                v-if="form.errors.telepon"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.telepon }}
                            </p>
                        </div>

                        <!-- WhatsApp -->

                        <div>
                            <label
                                for="whatsapp"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                WhatsApp
                            </label>

                            <div class="relative">
                                <Phone
                                    class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    aria-hidden="true"
                                />

                                <input
                                    id="whatsapp"
                                    v-model="form.whatsapp"
                                    type="tel"
                                    maxlength="30"
                                    autocomplete="tel"
                                    placeholder="+62..."
                                    class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    :aria-invalid="!!form.errors.whatsapp"
                                />
                            </div>

                            <p
                                v-if="form.errors.whatsapp"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.whatsapp }}
                            </p>
                        </div>

                        <!-- Email -->

                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Email Umum
                            </label>

                            <div class="relative">
                                <Mail
                                    class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    aria-hidden="true"
                                />

                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    maxlength="150"
                                    autocomplete="email"
                                    placeholder="info@perusahaan.co.id"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    :aria-invalid="!!form.errors.email"
                                />
                            </div>

                            <p
                                v-if="form.errors.email"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Email Investor -->

                        <div>
                            <label
                                for="email_investor"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Email Investor
                            </label>

                            <div class="relative">
                                <Mail
                                    class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    aria-hidden="true"
                                />

                                <input
                                    id="email_investor"
                                    v-model="form.email_investor"
                                    type="email"
                                    maxlength="150"
                                    autocomplete="email"
                                    placeholder="investor@perusahaan.co.id"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    :aria-invalid="!!form.errors.email_investor"
                                />
                            </div>

                            <p
                                v-if="form.errors.email_investor"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.email_investor }}
                            </p>
                        </div>

                        <!-- Jam -->

                        <div class="sm:col-span-2">
                            <label
                                for="jam_operasional"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Jam Operasional
                            </label>

                            <div class="relative">
                                <Clock3
                                    class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    aria-hidden="true"
                                />

                                <input
                                    id="jam_operasional"
                                    v-model="form.jam_operasional"
                                    type="text"
                                    maxlength="255"
                                    placeholder="Senin - Jumat, 08.00 - 16.00 WIB"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    :aria-invalid="
                                        !!form.errors.jam_operasional
                                    "
                                />
                            </div>

                            <p
                                v-if="form.errors.jam_operasional"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.jam_operasional }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- =================================================
                     Lokasi
                ================================================== -->

                <section
                    data-reveal
                    class="reveal overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    style="--d: 220ms"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-5 sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400"
                            >
                                <Map class="size-5" aria-hidden="true" />
                            </div>

                            <div>
                                <h2
                                    class="text-base font-semibold text-slate-900 dark:text-white"
                                >
                                    Lokasi & Peta
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Koordinat geografis dan peta lokasi
                                    perusahaan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <!-- Latitude -->

                        <div>
                            <label
                                for="latitude"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Latitude
                            </label>

                            <div class="relative">
                                <MapPin
                                    class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    aria-hidden="true"
                                />

                                <input
                                    id="latitude"
                                    v-model="form.latitude"
                                    type="number"
                                    step="0.0000001"
                                    min="-90"
                                    max="90"
                                    placeholder="-0.0000000"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    :aria-invalid="!!form.errors.latitude"
                                />
                            </div>

                            <p
                                v-if="form.errors.latitude"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.latitude }}
                            </p>
                        </div>

                        <!-- Longitude -->

                        <div>
                            <label
                                for="longitude"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Longitude
                            </label>

                            <div class="relative">
                                <MapPin
                                    class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    aria-hidden="true"
                                />

                                <input
                                    id="longitude"
                                    v-model="form.longitude"
                                    type="number"
                                    step="0.0000001"
                                    min="-180"
                                    max="180"
                                    placeholder="100.0000000"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                    :aria-invalid="!!form.errors.longitude"
                                />
                            </div>

                            <p
                                v-if="form.errors.longitude"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.longitude }}
                            </p>
                        </div>

                        <!-- Maps Embed -->

                        <div class="sm:col-span-2">
                            <label
                                for="maps_embed_url"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Google Maps Embed URL
                            </label>

                            <textarea
                                id="maps_embed_url"
                                v-model="form.maps_embed_url"
                                rows="4"
                                maxlength="5000"
                                placeholder="https://www.google.com/maps/embed?..."
                                class="w-full resize-y rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                :aria-invalid="!!form.errors.maps_embed_url"
                            />

                            <div
                                class="mt-2 flex items-start gap-2 text-xs text-slate-500 dark:text-slate-400"
                            >
                                <Map
                                    class="mt-0.5 size-3.5 shrink-0"
                                    aria-hidden="true"
                                />

                                <span>
                                    Gunakan URL <strong>embed</strong>
                                    Google Maps, bukan URL halaman Google Maps
                                    biasa.
                                </span>
                            </div>

                            <p
                                v-if="form.errors.maps_embed_url"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.maps_embed_url }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- =================================================
                     Media Sosial
                ================================================== -->

                <section
                    data-reveal
                    class="reveal overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    style="--d: 280ms"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-5 sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400"
                            >
                                <Globe class="size-5" aria-hidden="true" />
                            </div>

                            <div>
                                <h2
                                    class="text-base font-semibold text-slate-900 dark:text-white"
                                >
                                    Media Sosial
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    URL akun resmi media sosial perusahaan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <!-- Facebook -->

                        <div>
                            <label
                                for="facebook"
                                class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                <Facebook class="size-4" aria-hidden="true" />

                                Facebook
                            </label>

                            <input
                                id="facebook"
                                v-model="form.facebook"
                                type="url"
                                maxlength="255"
                                placeholder="https://facebook.com/..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                :aria-invalid="!!form.errors.facebook"
                            />

                            <p
                                v-if="form.errors.facebook"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.facebook }}
                            </p>
                        </div>

                        <!-- Instagram -->

                        <div>
                            <label
                                for="instagram"
                                class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                <Instagram class="size-4" aria-hidden="true" />

                                Instagram
                            </label>

                            <input
                                id="instagram"
                                v-model="form.instagram"
                                type="url"
                                maxlength="255"
                                placeholder="https://instagram.com/..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                :aria-invalid="!!form.errors.instagram"
                            />

                            <p
                                v-if="form.errors.instagram"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.instagram }}
                            </p>
                        </div>

                        <!-- LinkedIn -->

                        <div>
                            <label
                                for="linkedin"
                                class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                <Linkedin class="size-4" aria-hidden="true" />

                                LinkedIn
                            </label>

                            <input
                                id="linkedin"
                                v-model="form.linkedin"
                                type="url"
                                maxlength="255"
                                placeholder="https://linkedin.com/company/..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                :aria-invalid="!!form.errors.linkedin"
                            />

                            <p
                                v-if="form.errors.linkedin"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.linkedin }}
                            </p>
                        </div>

                        <!-- YouTube -->

                        <div>
                            <label
                                for="youtube"
                                class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                <Youtube class="size-4" aria-hidden="true" />

                                YouTube
                            </label>

                            <input
                                id="youtube"
                                v-model="form.youtube"
                                type="url"
                                maxlength="255"
                                placeholder="https://youtube.com/@..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                :aria-invalid="!!form.errors.youtube"
                            />

                            <p
                                v-if="form.errors.youtube"
                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.youtube }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- =================================================
                     Error Summary
                ================================================== -->

                <div
                    v-if="hasErrors"
                    data-reveal
                    class="reveal rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/30"
                    role="alert"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-red-100 text-sm font-bold text-red-600 dark:bg-red-900/40 dark:text-red-400"
                        >
                            !
                        </div>

                        <div>
                            <p
                                class="text-sm font-semibold text-red-800 dark:text-red-300"
                            >
                                Terdapat kesalahan pada formulir.
                            </p>

                            <p
                                class="mt-1 text-xs leading-5 text-red-700 dark:text-red-400"
                            >
                                Silakan periksa kembali field yang ditandai
                                sebelum menyimpan perubahan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- =================================================
                     Save Bar
                ================================================== -->

                <section
                    data-reveal
                    class="reveal rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900"
                    style="--d: 340ms"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                Simpan pengaturan kontak
                            </p>

                            <p
                                class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                            >
                                Pastikan informasi yang dimasukkan sudah benar
                                sebelum disimpan.
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing || isSaving"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                        >
                            <LoaderCircle
                                v-if="form.processing || isSaving"
                                class="size-4 animate-spin"
                                aria-hidden="true"
                            />

                            <Save v-else class="size-4" aria-hidden="true" />

                            {{
                                form.processing || isSaving
                                    ? "Menyimpan..."
                                    : "Simpan Perubahan"
                            }}
                        </button>
                    </div>
                </section>
            </form>
        </main>
    </div>
</template>

<style scoped>
.reveal {
    opacity: 0;
    transform: translateY(14px);
    transition:
        opacity 0.65s ease,
        transform 0.65s ease;
    transition-delay: var(--d, 0ms);
}

.reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    .reveal {
        opacity: 1;
        transform: none;
        transition: none;
    }
}
</style>
