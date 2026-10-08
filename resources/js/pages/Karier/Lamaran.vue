<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";

import { Head, Link, useForm } from "@inertiajs/vue3";

import {
    ArrowLeft,
    BriefcaseBusiness,
    CalendarDays,
    CheckCircle2,
    FileText,
    Link2,
    LoaderCircle,
    Mail,
    MapPin,
    Phone,
    Send,
    ShieldCheck,
    Upload,
    User,
    X,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";

import { trans } from "laravel-vue-i18n";

import { currentLanguage, localizedValue } from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

interface Lowongan {
    [key: string]: unknown;

    id: number;

    judul_id: string;
    judul_en: string;
    judul_zh: string;

    slug: string;

    deskripsi_id: string | null;
    deskripsi_en: string | null;
    deskripsi_zh: string | null;

    departemen_id: string | null;
    departemen_en: string | null;
    departemen_zh: string | null;

    tipe_pekerjaan: string | null;

    lokasi_id: string | null;
    lokasi_en: string | null;
    lokasi_zh: string | null;

    tanggal_mulai: string | null;
    tanggal_tutup: string | null;

    status: string;
    unggulan: boolean;
    urutan: number;
    created_at: string | null;
}

const t = (key: string, params: Record<string, string> = {}) =>
    trans(`karier.${key}`, params);
const props = defineProps<{
    lowongan: Lowongan;
}>();

const judul = computed(() => localizedValue(props.lowongan, "judul"));

const form = useForm({
    nama_lengkap: "",
    email: "",
    no_hp: "",
    cv: null as File | null,
    surat_lamaran: null as File | null,
    linkedin: "",
    portfolio: "",
    pesan_id: "",
    pesan_en: "",
    pesan_zh: "",
});

const messageLanguages = [
    { code: "id", label: "Indonesia" },
    { code: "en", label: "English" },
    { code: "zh", label: "中文" },
] as const;

type MessageLanguage = (typeof messageLanguages)[number]["code"];

const messageLanguage = ref<MessageLanguage>("id");

const setMessageLanguage = (language: MessageLanguage) => {
    messageLanguage.value = language;
};

const tips = ["item_1", "item_2", "item_3", "item_4"] as const;

const cvInput = ref<HTMLInputElement | null>(null);
const suratLamaranInput = ref<HTMLInputElement | null>(null);

const cvClientError = ref("");
const suratLamaranClientError = ref("");

const showSuccessModal = ref(false);
const successCloseButton = ref<HTMLButtonElement | null>(null);

const MAX_FILE_SIZE = 1024 * 1024;

const ACCEPTED_EXTENSIONS = [".pdf", ".doc", ".docx"];

const ACCEPT_ATTR =
    ".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document";

const typeLabel = computed(() => {
    const type = props.lowongan.tipe_pekerjaan;

    if (!type) {
        return t("types.default");
    }

    const labels: Record<string, string> = {
        full_time: t("types.full_time"),
        part_time: t("types.part_time"),
        contract: t("types.contract"),
        internship: t("types.internship"),
        freelance: t("types.freelance"),
        remote: t("types.remote"),
    };

    return labels[type] ?? type;
});

const formatDate = (value: string | null): string | null => {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    const locale =
        currentLanguage.value === "zh"
            ? "zh-CN"
            : currentLanguage.value === "en"
              ? "en-US"
              : "id-ID";

    return new Intl.DateTimeFormat(locale, {
        day: "numeric",
        month: "long",
        year: "numeric",
        timeZone: "Asia/Jakarta",
    }).format(date);
};

const formattedClosingDate = computed(() =>
    formatDate(props.lowongan.tanggal_tutup),
);

const formattedStartDate = computed(() =>
    formatDate(props.lowongan.tanggal_mulai),
);

const canonicalUrl = computed(
    () =>
        `https://tanjungbuton-industrial.co.id/karier/${props.lowongan.slug}/lamar`,
);

const ogImage = "https://tanjungbuton-industrial.co.id/logoside.png";

const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${Math.round(bytes / 1024)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const validateFile = (file: File): string | null => {
    const extension = `.${file.name.split(".").pop()?.toLowerCase() ?? ""}`;

    if (!ACCEPTED_EXTENSIONS.includes(extension)) {
        return t("apply.validation.file_type");
    }

    if (file.size > MAX_FILE_SIZE) {
        return t("apply.validation.file_size");
    }

    return null;
};

const handleCvChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    const file = target.files?.[0] ?? null;

    cvClientError.value = "";
    form.clearErrors("cv");

    if (!file) {
        form.cv = null;
        return;
    }

    const error = validateFile(file);

    if (error) {
        cvClientError.value = error;
        form.cv = null;
        target.value = "";
        return;
    }

    form.cv = file;
};

const handleSuratLamaranChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    const file = target.files?.[0] ?? null;

    suratLamaranClientError.value = "";
    form.clearErrors("surat_lamaran");

    if (!file) {
        form.surat_lamaran = null;
        return;
    }

    const error = validateFile(file);

    if (error) {
        suratLamaranClientError.value = error;
        form.surat_lamaran = null;
        target.value = "";
        return;
    }

    form.surat_lamaran = file;
};

const removeCv = () => {
    form.cv = null;
    cvClientError.value = "";
    form.clearErrors("cv");

    if (cvInput.value) {
        cvInput.value.value = "";
    }
};

const removeSuratLamaran = () => {
    form.surat_lamaran = null;
    suratLamaranClientError.value = "";
    form.clearErrors("surat_lamaran");

    if (suratLamaranInput.value) {
        suratLamaranInput.value.value = "";
    }
};

const submit = () => {
    cvClientError.value = "";
    suratLamaranClientError.value = "";

    if (!form.cv) {
        cvClientError.value = t("apply.validation.cv_required");

        cvInput.value?.focus();

        return;
    }

    form.post(`/karier/${props.lowongan.slug}/lamar`, {
        forceFormData: true,
        preserveScroll: true,

        onSuccess: async () => {
            form.clearErrors();
            form.reset();

            messageLanguage.value = "id";

            if (cvInput.value) {
                cvInput.value.value = "";
            }

            if (suratLamaranInput.value) {
                suratLamaranInput.value.value = "";
            }

            showSuccessModal.value = true;

            await nextTick();

            successCloseButton.value?.focus();
        },
    });
};

const closeSuccessModal = () => {
    showSuccessModal.value = false;
};

const handleEscape = (event: KeyboardEvent) => {
    if (event.key === "Escape" && showSuccessModal.value) {
        closeSuccessModal();
    }
};

onMounted(() => {
    window.addEventListener("keydown", handleEscape);
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleEscape);
});
</script>

<template>
    <Head>
        <title>{{ t("apply.seo.title", { title: judul }) }}</title>
        <meta
            name="description"
            :content="t('apply.seo.description', { title: judul })"
        />
        <link rel="canonical" :href="canonicalUrl" />
        <meta
            property="og:title"
            :content="t('apply.seo.title', { title: judul })"
        />
        <meta
            property="og:description"
            :content="t('apply.seo.description', { title: judul })"
        />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:image" :content="ogImage" />
    </Head>

    <main class="lamaran-page min-h-screen bg-slate-50">
        <!-- Header -->
        <section class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <Link
                    :href="`/karier/${lowongan.slug}`"
                    class="group inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-600"
                >
                    <ArrowLeft
                        class="size-4 transition-transform group-hover:-translate-x-0.5"
                    />
                    {{ t("apply.navigation.back") }}
                </Link>

                <div class="mt-7 max-w-4xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-blue-700"
                    >
                        <BriefcaseBusiness class="size-3.5" />
                        {{ t("apply.hero.badge") }}
                    </div>

                    <h1
                        class="mt-4 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl"
                    >
                        {{ judul }}
                    </h1>

                    <p
                        class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 sm:text-base"
                    >
                        {{ t("apply.hero.description") }}
                    </p>

                    <div class="mt-6 flex flex-wrap gap-2.5">
                        <span
                            v-if="lowongan.departemen_id"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600"
                        >
                            <BriefcaseBusiness class="size-4 text-blue-600" />
                            {{ localizedValue(lowongan, "departemen") }}
                        </span>

                        <span
                            v-if="lowongan.lokasi_id"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600"
                        >
                            <MapPin class="size-4 text-blue-600" />
                            {{ localizedValue(lowongan, "lokasi") }}
                        </span>

                        <span
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600"
                        >
                            <CalendarDays class="size-4 text-blue-600" />
                            {{ typeLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <div
                class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-8"
            >
                <!-- FORM -->
                <div
                    class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-5 py-6 sm:px-7">
                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-wider text-blue-600"
                                >
                                    {{ t("apply.form.label") }}
                                </p>
                                <h2
                                    class="mt-1.5 text-xl font-bold tracking-tight text-slate-900"
                                >
                                    {{ t("apply.form.title") }}
                                </h2>
                                <p
                                    class="mt-1.5 text-sm leading-6 text-slate-500"
                                >
                                    {{ t("apply.form.description") }}
                                </p>
                            </div>

                            <div
                                class="inline-flex shrink-0 items-center gap-1.5 text-xs font-medium text-slate-400"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-red-500"
                                />
                                {{ t("apply.form.required") }}
                            </div>
                        </div>
                    </div>

                    <form
                        class="divide-y divide-slate-100"
                        @submit.prevent="submit"
                    >
                        <!-- Personal Information -->
                        <div class="space-y-6 px-5 py-7 sm:px-7">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                                >
                                    <User class="size-4.5" />
                                </div>
                                <div>
                                    <h3
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        {{ t("apply.personal.title") }}
                                    </h3>
                                    <p
                                        class="mt-0.5 text-xs leading-5 text-slate-500"
                                    >
                                        {{ t("apply.personal.description") }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <!-- Nama -->
                                <div class="sm:col-span-2">
                                    <label
                                        for="nama_lengkap"
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                    >
                                        {{ t("apply.personal.full_name") }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <User
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="nama_lengkap"
                                            v-model="form.nama_lengkap"
                                            type="text"
                                            autocomplete="name"
                                            required
                                            :placeholder="
                                                t(
                                                    'apply.personal.full_name_placeholder',
                                                )
                                            "
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                                            :aria-invalid="
                                                !!form.errors.nama_lengkap
                                            "
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.nama_lengkap"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.nama_lengkap }}
                                    </p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label
                                        for="email"
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                    >
                                        {{ t("apply.personal.email") }}
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
                                            autocomplete="email"
                                            required
                                            :placeholder="
                                                t(
                                                    'apply.personal.email_placeholder',
                                                )
                                            "
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
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

                                <!-- Phone -->
                                <div>
                                    <label
                                        for="no_hp"
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                    >
                                        {{ t("apply.personal.phone") }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <Phone
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="no_hp"
                                            v-model="form.no_hp"
                                            type="tel"
                                            autocomplete="tel"
                                            required
                                            :placeholder="
                                                t(
                                                    'apply.personal.phone_placeholder',
                                                )
                                            "
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                                            :aria-invalid="!!form.errors.no_hp"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.no_hp"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.no_hp }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="space-y-6 px-5 py-7 sm:px-7">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                                >
                                    <FileText class="size-4.5" />
                                </div>
                                <div>
                                    <h3
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        {{ t("apply.documents.title") }}
                                    </h3>
                                    <p
                                        class="mt-0.5 text-xs leading-5 text-slate-500"
                                    >
                                        {{ t("apply.documents.description") }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <!-- CV -->
                                <div>
                                    <label
                                        for="cv"
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                    >
                                        {{ t("apply.documents.cv") }}
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="cv"
                                        ref="cvInput"
                                        type="file"
                                        class="sr-only"
                                        :accept="ACCEPT_ATTR"
                                        :aria-describedby="
                                            cvClientError || form.errors.cv
                                                ? 'cv-error'
                                                : undefined
                                        "
                                        @change="handleCvChange"
                                    />

                                    <div
                                        v-if="!form.cv"
                                        class="rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-4 transition hover:border-blue-400 hover:bg-blue-50/40"
                                    >
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-3 text-left"
                                            @click="cvInput?.click()"
                                        >
                                            <span
                                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-white text-blue-600 shadow-sm ring-1 ring-slate-200"
                                            >
                                                <Upload class="size-4.5" />
                                            </span>
                                            <span class="min-w-0">
                                                <span
                                                    class="block text-sm font-semibold text-slate-700"
                                                >
                                                    {{
                                                        t(
                                                            "apply.documents.choose_cv",
                                                        )
                                                    }}
                                                </span>
                                                <span
                                                    class="mt-0.5 block text-xs leading-5 text-slate-400"
                                                >
                                                    {{
                                                        t(
                                                            "apply.documents.max_file",
                                                        )
                                                    }}
                                                </span>
                                            </span>
                                        </button>
                                    </div>

                                    <div
                                        v-else
                                        class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3"
                                    >
                                        <div
                                            class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-white text-emerald-600 shadow-sm ring-1 ring-emerald-100"
                                        >
                                            <CheckCircle2 class="size-5" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="truncate text-sm font-semibold text-slate-700"
                                            >
                                                {{ form.cv.name }}
                                            </p>
                                            <p
                                                class="mt-0.5 text-xs text-slate-400"
                                            >
                                                {{
                                                    formatFileSize(form.cv.size)
                                                }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-slate-400 transition hover:bg-white hover:text-red-500"
                                            :aria-label="
                                                t('apply.documents.remove_cv')
                                            "
                                            @click="removeCv"
                                        >
                                            <X class="size-4" />
                                        </button>
                                    </div>

                                    <p
                                        v-if="cvClientError || form.errors.cv"
                                        id="cv-error"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ cvClientError || form.errors.cv }}
                                    </p>
                                </div>

                                <!-- Cover letter -->
                                <div>
                                    <label
                                        for="surat_lamaran"
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                    >
                                        {{ t("apply.documents.letter") }}
                                        <span
                                            class="font-normal text-slate-400"
                                        >
                                            {{ t("apply.form.optional") }}
                                        </span>
                                    </label>

                                    <input
                                        id="surat_lamaran"
                                        ref="suratLamaranInput"
                                        type="file"
                                        class="sr-only"
                                        :accept="ACCEPT_ATTR"
                                        :aria-describedby="
                                            suratLamaranClientError ||
                                            form.errors.surat_lamaran
                                                ? 'surat-error'
                                                : undefined
                                        "
                                        @change="handleSuratLamaranChange"
                                    />

                                    <div
                                        v-if="!form.surat_lamaran"
                                        class="rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-4 transition hover:border-blue-400 hover:bg-blue-50/40"
                                    >
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-3 text-left"
                                            @click="suratLamaranInput?.click()"
                                        >
                                            <span
                                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-white text-blue-600 shadow-sm ring-1 ring-slate-200"
                                            >
                                                <Upload class="size-4.5" />
                                            </span>
                                            <span class="min-w-0">
                                                <span
                                                    class="block text-sm font-semibold text-slate-700"
                                                >
                                                    {{
                                                        t(
                                                            "apply.documents.choose_letter",
                                                        )
                                                    }}
                                                </span>
                                                <span
                                                    class="mt-0.5 block text-xs leading-5 text-slate-400"
                                                >
                                                    {{
                                                        t(
                                                            "apply.documents.max_file",
                                                        )
                                                    }}
                                                </span>
                                            </span>
                                        </button>
                                    </div>

                                    <div
                                        v-else
                                        class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3"
                                    >
                                        <div
                                            class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-white text-emerald-600 shadow-sm ring-1 ring-emerald-100"
                                        >
                                            <CheckCircle2 class="size-5" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="truncate text-sm font-semibold text-slate-700"
                                            >
                                                {{ form.surat_lamaran.name }}
                                            </p>
                                            <p
                                                class="mt-0.5 text-xs text-slate-400"
                                            >
                                                {{
                                                    formatFileSize(
                                                        form.surat_lamaran.size,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-slate-400 transition hover:bg-white hover:text-red-500"
                                            :aria-label="
                                                t(
                                                    'apply.documents.remove_letter',
                                                )
                                            "
                                            @click="removeSuratLamaran"
                                        >
                                            <X class="size-4" />
                                        </button>
                                    </div>

                                    <p
                                        v-if="
                                            suratLamaranClientError ||
                                            form.errors.surat_lamaran
                                        "
                                        id="surat-error"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{
                                            suratLamaranClientError ||
                                            form.errors.surat_lamaran
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex gap-3 rounded-xl border border-blue-100 bg-blue-50/60 p-4"
                            >
                                <ShieldCheck
                                    class="mt-0.5 size-4.5 shrink-0 text-blue-600"
                                />
                                <p class="text-xs leading-5 text-blue-800">
                                    {{ t("apply.documents.tip") }}
                                </p>
                            </div>
                        </div>

                        <!-- Links -->
                        <div class="space-y-6 px-5 py-7 sm:px-7">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                                >
                                    <Link2 class="size-4.5" />
                                </div>
                                <div>
                                    <h3
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        {{ t("apply.profile.title") }}
                                    </h3>
                                    <p
                                        class="mt-0.5 text-xs leading-5 text-slate-500"
                                    >
                                        {{ t("apply.profile.description") }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <!-- LinkedIn -->
                                <div>
                                    <label
                                        for="linkedin"
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                    >
                                        {{ t("apply.profile.linkedin") }}
                                        <span
                                            class="font-normal text-slate-400"
                                        >
                                            {{ t("apply.form.optional") }}
                                        </span>
                                    </label>
                                    <div class="relative">
                                        <Link2
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="linkedin"
                                            v-model="form.linkedin"
                                            type="url"
                                            autocomplete="url"
                                            :placeholder="
                                                t(
                                                    'apply.profile.linkedin_placeholder',
                                                )
                                            "
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                                            :aria-invalid="
                                                !!form.errors.linkedin
                                            "
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.linkedin"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.linkedin }}
                                    </p>
                                </div>

                                <!-- Portfolio -->
                                <div>
                                    <label
                                        for="portfolio"
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                    >
                                        {{ t("apply.profile.portfolio") }}
                                        <span
                                            class="font-normal text-slate-400"
                                        >
                                            {{ t("apply.form.optional") }}
                                        </span>
                                    </label>
                                    <div class="relative">
                                        <Link2
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="portfolio"
                                            v-model="form.portfolio"
                                            type="url"
                                            autocomplete="url"
                                            :placeholder="
                                                t(
                                                    'apply.profile.portfolio_placeholder',
                                                )
                                            "
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                                            :aria-invalid="
                                                !!form.errors.portfolio
                                            "
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.portfolio"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.portfolio }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="space-y-6 px-5 py-7 sm:px-7">
                            <div>
                                <label
                                    :for="`pesan_${messageLanguage}`"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    {{ t("apply.message.title") }}
                                    <span class="font-normal text-slate-400">
                                        {{ t("apply.form.optional") }}
                                    </span>
                                </label>

                                <div
                                    class="mb-3 flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50 p-1.5"
                                >
                                    <button
                                        v-for="language in messageLanguages"
                                        :key="language.code"
                                        type="button"
                                        class="rounded-lg px-3 py-2 text-xs font-bold transition"
                                        :class="
                                            messageLanguage === language.code
                                                ? 'bg-white text-blue-600 shadow-sm ring-1 ring-slate-200'
                                                : 'text-slate-500 hover:bg-white hover:text-slate-700'
                                        "
                                        @click="
                                            setMessageLanguage(language.code)
                                        "
                                    >
                                        {{ language.label }}
                                    </button>
                                </div>

                                <textarea
                                    v-if="messageLanguage === 'id'"
                                    id="pesan_id"
                                    v-model="form.pesan_id"
                                    rows="5"
                                    maxlength="2000"
                                    :placeholder="
                                        t('apply.message.placeholder_id')
                                    "
                                    class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                                    :aria-invalid="!!form.errors.pesan_id"
                                />
                                <textarea
                                    v-else-if="messageLanguage === 'en'"
                                    id="pesan_en"
                                    v-model="form.pesan_en"
                                    rows="5"
                                    maxlength="2000"
                                    :placeholder="
                                        t('apply.message.placeholder_en')
                                    "
                                    class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                                    :aria-invalid="!!form.errors.pesan_en"
                                />
                                <textarea
                                    v-else
                                    id="pesan_zh"
                                    v-model="form.pesan_zh"
                                    rows="5"
                                    maxlength="2000"
                                    :placeholder="
                                        t('apply.message.placeholder_zh')
                                    "
                                    class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                                    :aria-invalid="!!form.errors.pesan_zh"
                                />

                                <div
                                    class="mt-1.5 flex items-center justify-between gap-4"
                                >
                                    <p
                                        v-if="
                                            form.errors.pesan_id ||
                                            form.errors.pesan_en ||
                                            form.errors.pesan_zh
                                        "
                                        class="text-xs font-medium text-red-600"
                                    >
                                        {{
                                            form.errors.pesan_id ||
                                            form.errors.pesan_en ||
                                            form.errors.pesan_zh
                                        }}
                                    </p>
                                    <span
                                        class="ml-auto text-xs text-slate-400"
                                    >
                                        {{ t("apply.message.max") }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="px-5 py-7 sm:px-7">
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5"
                            >
                                <div
                                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="flex gap-3">
                                        <ShieldCheck
                                            class="mt-0.5 size-5 shrink-0 text-blue-600"
                                        />
                                        <div>
                                            <p
                                                class="text-sm font-semibold text-slate-800"
                                            >
                                                {{
                                                    t(
                                                        "apply.submit.review_title",
                                                    )
                                                }}
                                            </p>
                                            <p
                                                class="mt-0.5 text-xs leading-5 text-slate-500"
                                            >
                                                {{
                                                    t(
                                                        "apply.submit.review_description",
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <LoaderCircle
                                            v-if="form.processing"
                                            class="size-4 animate-spin"
                                        />
                                        <Send v-else class="size-4" />
                                        {{
                                            form.processing
                                                ? t("apply.submit.sending")
                                                : t("apply.submit.button")
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- SIDEBAR -->
                <aside
                    class="min-w-0 space-y-5 lg:sticky lg:top-24 lg:self-start"
                >
                    <!-- Job Summary -->
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div class="border-b border-slate-100 px-5 py-5">
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-blue-600"
                            >
                                {{ t("apply.sidebar.position") }}
                            </p>
                            <h2
                                class="mt-2 text-lg font-bold leading-7 text-slate-900"
                            >
                                {{ judul }}
                            </h2>
                        </div>

                        <div class="space-y-4 px-5 py-5">
                            <div
                                v-if="lowongan.departemen_id"
                                class="flex gap-3"
                            >
                                <BriefcaseBusiness
                                    class="mt-0.5 size-4 shrink-0 text-slate-400"
                                />
                                <div class="min-w-0">
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        {{ t("apply.sidebar.department") }}
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-700"
                                    >
                                        {{
                                            localizedValue(
                                                lowongan,
                                                "departemen",
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="lowongan.lokasi_id" class="flex gap-3">
                                <MapPin
                                    class="mt-0.5 size-4 shrink-0 text-slate-400"
                                />
                                <div class="min-w-0">
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        {{ t("apply.sidebar.location") }}
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-700"
                                    >
                                        {{ localizedValue(lowongan, "lokasi") }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <CalendarDays
                                    class="mt-0.5 size-4 shrink-0 text-slate-400"
                                />
                                <div class="min-w-0">
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        {{ t("apply.sidebar.job_type") }}
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-700"
                                    >
                                        {{ typeLabel }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="formattedStartDate" class="flex gap-3">
                                <CalendarDays
                                    class="mt-0.5 size-4 shrink-0 text-slate-400"
                                />
                                <div class="min-w-0">
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        {{ t("apply.sidebar.start") }}
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-700"
                                    >
                                        {{ formattedStartDate }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="formattedClosingDate" class="flex gap-3">
                                <CalendarDays
                                    class="mt-0.5 size-4 shrink-0 text-slate-400"
                                />
                                <div class="min-w-0">
                                    <p
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        {{ t("apply.sidebar.deadline") }}
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm font-semibold text-slate-700"
                                    >
                                        {{ formattedClosingDate }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="border-t border-slate-100 bg-slate-50 px-5 py-4"
                        >
                            <Link
                                :href="`/karier/${lowongan.slug}`"
                                class="inline-flex items-center gap-1.5 text-sm font-bold text-blue-600 transition hover:text-blue-700"
                            >
                                {{ t("apply.sidebar.view_detail") }}
                                <ArrowLeft class="size-3.5 rotate-180" />
                            </Link>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600"
                            >
                                <CheckCircle2 class="size-4.5" />
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">
                                {{ t("apply.tips.title") }}
                            </h3>
                        </div>

                        <ul class="mt-5 space-y-4">
                            <li
                                v-for="tip in tips"
                                :key="tip"
                                class="flex gap-3 text-sm leading-5 text-slate-600"
                            >
                                <CheckCircle2
                                    class="mt-0.5 size-4 shrink-0 text-emerald-500"
                                />
                                <span>{{ t(`apply.tips.${tip}`) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Privacy -->
                    <div
                        class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5"
                    >
                        <div class="flex gap-3">
                            <ShieldCheck
                                class="mt-0.5 size-5 shrink-0 text-blue-600"
                            />
                            <div>
                                <h3 class="text-sm font-bold text-blue-900">
                                    {{ t("apply.privacy.title") }}
                                </h3>
                                <p
                                    class="mt-1.5 text-xs leading-5 text-blue-800/80"
                                >
                                    {{ t("apply.privacy.description") }}
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    <!-- Success Modal -->
    <Transition name="modal">
        <div
            v-if="showSuccessModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="success-title"
            aria-describedby="success-description"
            @click.self="closeSuccessModal"
        >
            <div
                class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
            >
                <div class="px-6 pb-6 pt-7 text-center sm:px-8">
                    <div
                        class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/70"
                    >
                        <CheckCircle2 class="size-7" />
                    </div>

                    <h2
                        id="success-title"
                        class="mt-6 text-xl font-bold tracking-tight text-slate-900"
                    >
                        {{ t("apply.success.title") }}
                    </h2>

                    <p
                        id="success-description"
                        class="mt-3 text-sm leading-6 text-slate-500"
                    >
                        {{ t("apply.success.description", { title: judul }) }}
                    </p>
                </div>

                <div
                    class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end sm:px-8"
                >
                    <button
                        ref="successCloseButton"
                        type="button"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-500/10"
                        @click="closeSuccessModal"
                    >
                        {{ t("apply.success.close") }}
                    </button>

                    <Link
                        href="/karier"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                    >
                        <BriefcaseBusiness class="size-4" />
                        {{ t("apply.success.other_jobs") }}
                    </Link>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.lamaran-page {
    animation: lamaran-fade-in 0.45s ease-out both;
}

@keyframes lamaran-fade-in {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-enter-active,
.modal-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
    transform: translateY(8px) scale(0.98);
}

@media (prefers-reduced-motion: reduce) {
    .lamaran-page {
        animation: none;
    }

    .modal-enter-active,
    .modal-leave-active {
        transition: none;
    }
}
</style>
