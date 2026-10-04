<script setup lang="ts">
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import {
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    Check,
    CheckCircle2,
    Clock3,
    Copy,
    FileText,
    LoaderCircle,
    Mail,
    MapPin,
    Phone,
    Send,
    ShieldCheck,
    User,
    Users,
} from "lucide-vue-next";
import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

interface JadwalTerisi {
    mulai: string;
    selesai: string;
}

const props = defineProps<{
    jadwal_terisi: JadwalTerisi[];
    nomor_registrasi: string | null;
}>();

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    nama: "",
    instansi: "",
    jabatan: "",
    email: "",
    telepon: "",
    tanggal_kunjungan: "",
    waktu_mulai: "",
    waktu_selesai: "",
    jumlah_peserta: 1 as number | string,
    area_lahan: "",
    keperluan: "",
    memerlukan_pendamping: true,
});

const inputClass =
    "h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950/50 dark:text-white";

const plainInputClass =
    "h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950/50 dark:text-white";

/*
|--------------------------------------------------------------------------
| Tanggal & jadwal terisi
|--------------------------------------------------------------------------
*/

const todayKey = new Intl.DateTimeFormat("en-CA", {
    timeZone: "Asia/Jakarta",
}).format(new Date());

watch(
    () => form.tanggal_kunjungan,
    (tanggal) => {
        if (!tanggal || tanggal < todayKey) {
            return;
        }

        router.get(
            "/ajukan-kunjungan",
            { tanggal },
            {
                only: ["jadwal_terisi"],
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    },
);

const hasOverlap = computed(() => {
    if (!form.waktu_mulai || !form.waktu_selesai) {
        return false;
    }

    return props.jadwal_terisi.some(
        (slot) =>
            slot.mulai < form.waktu_selesai && slot.selesai > form.waktu_mulai,
    );
});

const timeOrderError = computed(() => {
    if (
        form.waktu_mulai &&
        form.waktu_selesai &&
        form.waktu_selesai <= form.waktu_mulai
    ) {
        return "Waktu selesai harus setelah waktu mulai.";
    }

    return "";
});

const formatDateLong = (value: string): string => {
    if (!value) {
        return "";
    }

    const date = new Date(`${value}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(date);
};

/*
|--------------------------------------------------------------------------
| Submit & Success Modal
|--------------------------------------------------------------------------
*/

const showSuccessModal = ref(false);
const submittedNumber = ref("");
const successCloseButton = ref<HTMLButtonElement | null>(null);
const copied = ref(false);

const canSubmit = computed(
    () => !form.processing && !hasOverlap.value && !timeOrderError.value,
);

const submit = () => {
    if (!canSubmit.value) {
        return;
    }

    form.post("/ajukan-kunjungan", {
        preserveScroll: true,

        onSuccess: async () => {
            submittedNumber.value = props.nomor_registrasi ?? "";

            form.reset();
            form.clearErrors();

            showSuccessModal.value = true;

            await nextTick();

            successCloseButton.value?.focus();
        },
    });
};

const closeSuccessModal = () => {
    showSuccessModal.value = false;
    copied.value = false;
};

const copyNumber = async () => {
    if (!submittedNumber.value) {
        return;
    }

    try {
        await navigator.clipboard.writeText(submittedNumber.value);

        copied.value = true;

        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        copied.value = false;
    }
};

const handleEscape = (event: KeyboardEvent) => {
    if (event.key === "Escape" && showSuccessModal.value) {
        closeSuccessModal();
    }
};

/*
|--------------------------------------------------------------------------
| Fade-in on scroll
|--------------------------------------------------------------------------
*/

let revealObserver: IntersectionObserver | null = null;

const setupReveal = () => {
    if (typeof window === "undefined") {
        return;
    }

    const elements = Array.from(
        document.querySelectorAll<HTMLElement>(".reveal"),
    );

    if (!elements.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;

    /*
    |--------------------------------------------------------------------------
    | Reduced motion
    |--------------------------------------------------------------------------
    */

    if (prefersReducedMotion) {
        elements.forEach((element) => {
            element.classList.add("is-visible");
        });

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Bersihkan observer lama
    |--------------------------------------------------------------------------
    */

    revealObserver?.disconnect();

    /*
    |--------------------------------------------------------------------------
    | Intersection Observer
    |--------------------------------------------------------------------------
    */

    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const element = entry.target as HTMLElement;

                element.classList.add("is-visible");

                /*
                |--------------------------------------------------------------------------
                | Stop observe setelah element tampil
                |--------------------------------------------------------------------------
                */

                revealObserver?.unobserve(element);
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
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    window.addEventListener("keydown", handleEscape);

    /*
    |--------------------------------------------------------------------------
    | Tunggu DOM selesai dirender
    |--------------------------------------------------------------------------
    */

    requestAnimationFrame(() => {
        setupReveal();
    });
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleEscape);

    revealObserver?.disconnect();
    revealObserver = null;
});

/*
|--------------------------------------------------------------------------
| SEO
|--------------------------------------------------------------------------
*/

const canonicalUrl = "https://tanjungbuton-industrial.co.id/ajukan-kunjungan";

const ogImage = "https://tanjungbuton-industrial.co.id/logoside.png";
</script>

<template>
    <Head>
        <title>Ajukan Kunjungan Lahan | KITB</title>

        <meta
            name="description"
            content="Ajukan kunjungan ke lahan kawasan industri PT Kawasan Industri Tanjung Buton (KITB)."
        />

        <link rel="canonical" :href="canonicalUrl" />

        <meta property="og:title" content="Ajukan Kunjungan Lahan | KITB" />

        <meta
            property="og:description"
            content="Ajukan kunjungan ke lahan kawasan industri PT Kawasan Industri Tanjung Buton (KITB)."
        />

        <meta property="og:url" :content="canonicalUrl" />

        <meta property="og:image" :content="ogImage" />
    </Head>

    <div
        class="relative min-h-screen overflow-x-clip bg-slate-50/70 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
    >
        <!-- =========================================================
             AMBIENT BACKGROUND
        ========================================================== -->
        <div
            class="pointer-events-none absolute inset-x-0 -top-28 h-[900px] overflow-hidden"
            aria-hidden="true"
        >
            <!-- Top gradient -->
            <div
                class="absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-blue-100/70 via-blue-50/40 to-transparent dark:from-blue-950/30 dark:via-blue-950/10"
            />

            <!-- Left blob -->
            <div
                class="blob blob-a absolute left-[2%] top-0 size-[26rem] rounded-full bg-gradient-to-br from-blue-400/35 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15"
            />

            <!-- Right blob -->
            <div
                class="blob blob-b absolute right-[2%] top-4 size-[22rem] rounded-full bg-gradient-to-tr from-sky-300/35 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10"
            />

            <!-- Center blob -->
            <div
                class="blob blob-c absolute left-1/3 top-56 size-72 rounded-full bg-gradient-to-br from-indigo-300/20 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/10"
            />

            <!-- Technical grid -->
            <div
                class="absolute inset-0 opacity-[0.18] dark:opacity-[0.08]"
                style="
                    background-image:
                        linear-gradient(
                            rgba(100, 116, 139, 0.11) 1px,
                            transparent 1px
                        ),
                        linear-gradient(
                            90deg,
                            rgba(100, 116, 139, 0.11) 1px,
                            transparent 1px
                        );
                    background-size: 42px 42px;
                    mask-image: linear-gradient(
                        to bottom,
                        black 0%,
                        black 55%,
                        transparent 100%
                    );
                    -webkit-mask-image: linear-gradient(
                        to bottom,
                        black 0%,
                        black 55%,
                        transparent 100%
                    );
                "
            />

            <!-- Bottom fade -->
            <div
                class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-b from-transparent to-slate-50/95 dark:to-slate-950/95"
            />
        </div>

        <!-- =========================================================
             HEADER
        ========================================================== -->
        <section
            class="relative z-10 border-b border-slate-200/70 bg-transparent dark:border-slate-800/70"
        >
            <div
                class="mx-auto max-w-6xl px-4 pb-10 pt-24 sm:px-6 sm:pb-12 sm:pt-28 lg:px-8 lg:pb-14 lg:pt-32"
            >
                <div class="reveal max-w-3xl" style="--d: 0">
                    <!-- Badge -->
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-blue-50/80 px-3.5 py-1.5 text-xs font-bold text-blue-700 shadow-sm backdrop-blur-sm dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                    >
                        <MapPin class="size-3.5" />

                        Kunjungan Lahan
                    </div>

                    <!-- Title -->
                    <h1
                        class="mt-5 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl lg:text-[2.75rem] dark:text-white"
                    >
                        Ajukan kunjungan ke kawasan KITB
                    </h1>

                    <!-- Description -->
                    <p
                        class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8 dark:text-slate-400"
                    >
                        Isi jadwal dan data rombongan Anda. Tim kami akan
                        meninjau pengajuan dan menghubungi Anda melalui email
                        atau telepon.
                    </p>

                    <!-- Header line -->
                    <div
                        class="mt-7 flex items-center gap-3"
                        aria-hidden="true"
                    >
                        <div
                            class="h-px w-16 bg-gradient-to-r from-blue-500 to-indigo-500"
                        />

                        <div
                            class="h-px flex-1 bg-gradient-to-r from-slate-200/90 via-slate-200/60 to-transparent dark:from-slate-700/90 dark:via-slate-800/60"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- =========================================================
             CONTENT
        ========================================================== -->
        <section
            class="relative z-10 mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10"
        >
            <div
                class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-8"
            >
                <!-- =================================================
                     FORM
                ================================================== -->
                <div
                    class="reveal min-w-0 overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/90 shadow-xl shadow-slate-900/[0.04] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                    style="--d: 100"
                >
                    <div
                        class="border-b border-slate-100/90 px-5 py-6 sm:px-7 dark:border-slate-800"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400"
                                >
                                    Form Pengajuan
                                </p>

                                <h2
                                    class="mt-1.5 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    Formulir pengajuan
                                </h2>

                                <p
                                    class="mt-1.5 text-sm leading-6 text-slate-500 dark:text-slate-400"
                                >
                                    Kolom bertanda
                                    <span class="text-red-500">*</span>
                                    wajib diisi.
                                </p>
                            </div>

                            <div
                                class="hidden size-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 sm:flex dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <FileText class="size-5" />
                            </div>
                        </div>
                    </div>

                    <form
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                        @submit.prevent="submit"
                    >
                        <!-- =================================================
                             DATA PEMOHON
                        ================================================== -->
                        <div class="space-y-6 px-5 py-7 sm:px-7">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <User class="size-4" />
                                </div>

                                <div>
                                    <h3
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        Data pemohon
                                    </h3>

                                    <p
                                        class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                    >
                                        Kontak yang akan kami hubungi untuk
                                        konfirmasi.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <!-- Nama -->
                                <div class="sm:col-span-2">
                                    <label
                                        for="nama"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Nama lengkap
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <User
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />

                                        <input
                                            id="nama"
                                            v-model="form.nama"
                                            type="text"
                                            required
                                            maxlength="150"
                                            autocomplete="name"
                                            placeholder="Nama lengkap pemohon"
                                            :class="inputClass"
                                            :aria-invalid="!!form.errors.nama"
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.nama"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.nama }}
                                    </p>
                                </div>

                                <!-- Instansi -->
                                <div>
                                    <label
                                        for="instansi"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Instansi / perusahaan
                                        <span
                                            class="font-normal text-slate-400"
                                        >
                                            (opsional)
                                        </span>
                                    </label>

                                    <div class="relative">
                                        <Building2
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />

                                        <input
                                            id="instansi"
                                            v-model="form.instansi"
                                            type="text"
                                            maxlength="200"
                                            autocomplete="organization"
                                            placeholder="Nama instansi"
                                            :class="inputClass"
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.instansi"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.instansi }}
                                    </p>
                                </div>

                                <!-- Jabatan -->
                                <div>
                                    <label
                                        for="jabatan"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Jabatan
                                        <span
                                            class="font-normal text-slate-400"
                                        >
                                            (opsional)
                                        </span>
                                    </label>

                                    <div class="relative">
                                        <BriefcaseBusiness
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />

                                        <input
                                            id="jabatan"
                                            v-model="form.jabatan"
                                            type="text"
                                            maxlength="100"
                                            autocomplete="organization-title"
                                            placeholder="Jabatan di instansi"
                                            :class="inputClass"
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.jabatan"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.jabatan }}
                                    </p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label
                                        for="email"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Email
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <Mail
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />

                                        <input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            required
                                            maxlength="150"
                                            autocomplete="email"
                                            placeholder="nama@email.com"
                                            :class="inputClass"
                                            :aria-invalid="!!form.errors.email"
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.email"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <!-- Telepon -->
                                <div>
                                    <label
                                        for="telepon"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Nomor telepon
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <Phone
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />

                                        <input
                                            id="telepon"
                                            v-model="form.telepon"
                                            type="tel"
                                            required
                                            maxlength="25"
                                            autocomplete="tel"
                                            placeholder="08xxxxxxxxxx"
                                            :class="inputClass"
                                            :aria-invalid="
                                                !!form.errors.telepon
                                            "
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.telepon"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.telepon }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- =================================================
                             JADWAL
                        ================================================== -->
                        <div class="space-y-6 px-5 py-7 sm:px-7">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <CalendarDays class="size-4" />
                                </div>

                                <div>
                                    <h3
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        Jadwal kunjungan
                                    </h3>

                                    <p
                                        class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                    >
                                        Pilih tanggal dulu agar jadwal yang
                                        sudah terisi muncul di samping.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-3">
                                <div>
                                    <label
                                        for="tanggal_kunjungan"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Tanggal
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="tanggal_kunjungan"
                                        v-model="form.tanggal_kunjungan"
                                        type="date"
                                        required
                                        :min="todayKey"
                                        :class="plainInputClass"
                                        :aria-invalid="
                                            !!form.errors.tanggal_kunjungan
                                        "
                                    />

                                    <p
                                        v-if="form.errors.tanggal_kunjungan"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.tanggal_kunjungan }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="waktu_mulai"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Waktu mulai
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="waktu_mulai"
                                        v-model="form.waktu_mulai"
                                        type="time"
                                        required
                                        :class="plainInputClass"
                                        :aria-invalid="
                                            !!form.errors.waktu_mulai
                                        "
                                    />

                                    <p
                                        v-if="form.errors.waktu_mulai"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.waktu_mulai }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="waktu_selesai"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Waktu selesai
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="waktu_selesai"
                                        v-model="form.waktu_selesai"
                                        type="time"
                                        required
                                        :class="plainInputClass"
                                        :aria-invalid="
                                            !!form.errors.waktu_selesai
                                        "
                                    />

                                    <p
                                        v-if="
                                            timeOrderError ||
                                            form.errors.waktu_selesai
                                        "
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{
                                            timeOrderError ||
                                            form.errors.waktu_selesai
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="hasOverlap"
                                class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300"
                                role="alert"
                            >
                                Waktu yang Anda pilih bentrok dengan jadwal yang
                                sudah terisi pada
                                {{ formatDateLong(form.tanggal_kunjungan) }}.
                                Pilih jam lain.
                            </div>
                        </div>

                        <!-- =================================================
                             DETAIL KUNJUNGAN
                        ================================================== -->
                        <div class="space-y-6 px-5 py-7 sm:px-7">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <FileText class="size-4" />
                                </div>

                                <div>
                                    <h3
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        Detail kunjungan
                                    </h3>

                                    <p
                                        class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                    >
                                        Informasi rombongan dan area yang ingin
                                        dilihat.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <!-- Peserta -->
                                <div>
                                    <label
                                        for="jumlah_peserta"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Jumlah peserta
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <Users
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />

                                        <input
                                            id="jumlah_peserta"
                                            v-model.number="form.jumlah_peserta"
                                            type="number"
                                            required
                                            min="1"
                                            max="65535"
                                            inputmode="numeric"
                                            :class="inputClass"
                                            :aria-invalid="
                                                !!form.errors.jumlah_peserta
                                            "
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.jumlah_peserta"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.jumlah_peserta }}
                                    </p>
                                </div>

                                <!-- Area -->
                                <div>
                                    <label
                                        for="area_lahan"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Area lahan yang dikunjungi
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <MapPin
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />

                                        <input
                                            id="area_lahan"
                                            v-model="form.area_lahan"
                                            type="text"
                                            required
                                            maxlength="255"
                                            placeholder="Contoh: Blok A, kawasan pelabuhan"
                                            :class="inputClass"
                                            :aria-invalid="
                                                !!form.errors.area_lahan
                                            "
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.area_lahan"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.area_lahan }}
                                    </p>
                                </div>

                                <!-- Pendamping -->
                                <div class="sm:col-span-2">
                                    <span
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Perlu pendamping dari KITB?
                                        <span class="text-red-500">*</span>
                                    </span>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <button
                                            type="button"
                                            class="flex items-center gap-3 rounded-xl border p-4 text-left text-sm transition focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                                            :class="
                                                form.memerlukan_pendamping
                                                    ? 'border-blue-500 bg-blue-50/60 text-slate-900 dark:border-blue-600 dark:bg-blue-950/30 dark:text-white'
                                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400'
                                            "
                                            :aria-pressed="
                                                form.memerlukan_pendamping
                                            "
                                            @click="
                                                form.memerlukan_pendamping = true
                                            "
                                        >
                                            <CheckCircle2
                                                class="size-5 shrink-0"
                                                :class="
                                                    form.memerlukan_pendamping
                                                        ? 'text-blue-600'
                                                        : 'text-slate-300'
                                                "
                                            />

                                            <span>
                                                <span
                                                    class="block font-semibold"
                                                >
                                                    Ya, saya butuh pendamping
                                                </span>

                                                <span
                                                    class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400"
                                                >
                                                    Petugas KITB menemani selama
                                                    kunjungan.
                                                </span>
                                            </span>
                                        </button>

                                        <button
                                            type="button"
                                            class="flex items-center gap-3 rounded-xl border p-4 text-left text-sm transition focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                                            :class="
                                                !form.memerlukan_pendamping
                                                    ? 'border-blue-500 bg-blue-50/60 text-slate-900 dark:border-blue-600 dark:bg-blue-950/30 dark:text-white'
                                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400'
                                            "
                                            :aria-pressed="
                                                !form.memerlukan_pendamping
                                            "
                                            @click="
                                                form.memerlukan_pendamping = false
                                            "
                                        >
                                            <CheckCircle2
                                                class="size-5 shrink-0"
                                                :class="
                                                    !form.memerlukan_pendamping
                                                        ? 'text-blue-600'
                                                        : 'text-slate-300'
                                                "
                                            />

                                            <span>
                                                <span
                                                    class="block font-semibold"
                                                >
                                                    Tidak perlu
                                                </span>

                                                <span
                                                    class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400"
                                                >
                                                    Kami datang dengan
                                                    pendamping sendiri.
                                                </span>
                                            </span>
                                        </button>
                                    </div>

                                    <p
                                        v-if="form.errors.memerlukan_pendamping"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.memerlukan_pendamping }}
                                    </p>
                                </div>

                                <!-- Keperluan -->
                                <div class="sm:col-span-2">
                                    <label
                                        for="keperluan"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Keperluan kunjungan
                                        <span
                                            class="font-normal text-slate-400"
                                        >
                                            (opsional)
                                        </span>
                                    </label>

                                    <textarea
                                        id="keperluan"
                                        v-model="form.keperluan"
                                        rows="5"
                                        maxlength="5000"
                                        placeholder="Jelaskan tujuan kunjungan, misalnya survei lokasi investasi atau studi lapangan."
                                        class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950/50 dark:text-white"
                                    />

                                    <p
                                        v-if="form.errors.keperluan"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.keperluan }}
                                    </p>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/50 sm:p-5"
                            >
                                <div
                                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="flex gap-3">
                                        <ShieldCheck
                                            class="mt-0.5 size-5 shrink-0 text-blue-600 dark:text-blue-400"
                                        />

                                        <div>
                                            <p
                                                class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                Periksa kembali data Anda
                                            </p>

                                            <p
                                                class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                            >
                                                Pastikan email dan telepon aktif
                                                agar kami bisa mengonfirmasi
                                                jadwal.
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="!canSubmit"
                                        class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <LoaderCircle
                                            v-if="form.processing"
                                            class="size-4 animate-spin"
                                        />

                                        <Send v-else class="size-4" />

                                        {{
                                            form.processing
                                                ? "Mengirim..."
                                                : "Kirim pengajuan"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- =================================================
                     SIDEBAR
                ================================================== -->
                <aside
                    class="min-w-0 space-y-5 lg:sticky lg:top-24 lg:self-start"
                >
                    <!-- Jadwal -->
                    <div
                        class="reveal overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white/90 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 180"
                    >
                        <div
                            class="border-b border-slate-100/90 px-5 py-5 dark:border-slate-800"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <Clock3 class="size-4" />
                                </div>

                                <h3
                                    class="text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    Jadwal yang sudah terisi
                                </h3>
                            </div>
                        </div>

                        <div class="px-5 py-5">
                            <p
                                v-if="!form.tanggal_kunjungan"
                                class="text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                Pilih tanggal kunjungan untuk melihat jam yang
                                tidak tersedia.
                            </p>

                            <template v-else>
                                <p class="text-xs font-medium text-slate-400">
                                    {{ formatDateLong(form.tanggal_kunjungan) }}
                                </p>

                                <p
                                    v-if="!jadwal_terisi.length"
                                    class="mt-2 text-sm font-semibold text-emerald-700 dark:text-emerald-400"
                                >
                                    Belum ada jadwal. Semua jam tersedia.
                                </p>

                                <ul v-else class="mt-3 space-y-2">
                                    <li
                                        v-for="slot in jadwal_terisi"
                                        :key="`${slot.mulai}-${slot.selesai}`"
                                        class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-300"
                                    >
                                        <span>
                                            {{ slot.mulai }} -
                                            {{ slot.selesai }}
                                        </span>

                                        <span
                                            class="text-xs font-normal text-slate-400"
                                        >
                                            Terisi
                                        </span>
                                    </li>
                                </ul>
                            </template>
                        </div>
                    </div>

                    <!-- Alur -->
                    <div
                        class="reveal rounded-[1.5rem] border border-slate-200/80 bg-white/90 p-5 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 240"
                    >
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Setelah pengajuan dikirim
                        </h3>

                        <ol class="mt-4 space-y-4">
                            <li
                                class="flex gap-3 text-sm leading-5 text-slate-600 dark:text-slate-400"
                            >
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"
                                >
                                    1
                                </span>

                                <span>
                                    Anda menerima nomor registrasi untuk
                                    disimpan.
                                </span>
                            </li>

                            <li
                                class="flex gap-3 text-sm leading-5 text-slate-600 dark:text-slate-400"
                            >
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"
                                >
                                    2
                                </span>

                                <span>
                                    Tim KITB meninjau pengajuan dan
                                    mengonfirmasi jadwal.
                                </span>
                            </li>

                            <li
                                class="flex gap-3 text-sm leading-5 text-slate-600 dark:text-slate-400"
                            >
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"
                                >
                                    3
                                </span>

                                <span>
                                    Anda dihubungi melalui email atau telepon
                                    yang diisi.
                                </span>
                            </li>
                        </ol>
                    </div>
                </aside>
            </div>
        </section>
    </div>

    <!-- =============================================================
         SUCCESS MODAL
    ============================================================= -->
    <Transition name="modal">
        <div
            v-if="showSuccessModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="success-title"
            @click.self="closeSuccessModal"
        >
            <div
                class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="px-6 pb-6 pt-7 text-center sm:px-8">
                    <div
                        class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/70 dark:bg-emerald-950/40 dark:ring-emerald-950/20 dark:text-emerald-400"
                    >
                        <CheckCircle2 class="size-7" />
                    </div>

                    <h2
                        id="success-title"
                        class="mt-6 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        Pengajuan terkirim
                    </h2>

                    <p
                        class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Simpan nomor registrasi berikut. Tim KITB akan
                        menghubungi Anda untuk konfirmasi jadwal.
                    </p>

                    <div
                        v-if="submittedNumber"
                        class="mt-5 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/60"
                    >
                        <span
                            class="truncate text-sm font-bold tracking-wide text-slate-800 dark:text-slate-200"
                        >
                            {{ submittedNumber }}
                        </span>

                        <button
                            type="button"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/40"
                            @click="copyNumber"
                        >
                            <Check v-if="copied" class="size-3.5" />

                            <Copy v-else class="size-3.5" />

                            {{ copied ? "Tersalin" : "Salin" }}
                        </button>
                    </div>
                </div>

                <div
                    class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-5 dark:border-slate-800 dark:bg-slate-950/50 sm:px-8"
                >
                    <button
                        ref="successCloseButton"
                        type="button"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                        @click="closeSuccessModal"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
/*
|--------------------------------------------------------------------------
| Fade-in reveal
|--------------------------------------------------------------------------
*/

.reveal {
    opacity: 0;
    transform: translateY(18px);
    transition:
        opacity 700ms ease,
        transform 700ms cubic-bezier(0.22, 1, 0.36, 1);
    transition-delay: calc(var(--d, 0) * 1ms);
    will-change: opacity, transform;
}

.reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/*
|--------------------------------------------------------------------------
| Success modal
|--------------------------------------------------------------------------
*/

.modal-enter-active,
.modal-leave-active {
    transition:
        opacity 220ms ease,
        transform 220ms cubic-bezier(0.22, 1, 0.36, 1);
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
    transform: translateY(10px) scale(0.98);
}

/*
|--------------------------------------------------------------------------
| Reduced motion
|--------------------------------------------------------------------------
*/

@media (prefers-reduced-motion: reduce) {
    .reveal {
        opacity: 1;
        transform: none;
        transition: none;
        will-change: auto;
    }

    .modal-enter-active,
    .modal-leave-active {
        transition: none;
    }

    .modal-enter-from,
    .modal-leave-to {
        opacity: 0;
    }

    .modal-enter-from > div,
    .modal-leave-to > div {
        transform: none;
    }
}
</style>
