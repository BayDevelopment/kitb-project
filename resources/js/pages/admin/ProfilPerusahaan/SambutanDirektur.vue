<script setup lang="ts">
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { router, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Bold,
    ImagePlus,
    Italic,
    List,
    ListOrdered,
    Quote,
    Save,
    Underline,
    Upload,
    UserRound,
    X,
} from "lucide-vue-next";
import { toast } from "vue-sonner";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({ layout: AppLayout });

type LanguageCode = "id" | "en" | "zh";
type SambutanType = "direktur" | "bupati";

interface SambutanData {
    id?: number;
    nama_direktur?: string;
    nama_direktur_en?: string | null;
    nama_direktur_zh?: string | null;
    jabatan_direktur?: string | null;
    jabatan_direktur_en?: string | null;
    jabatan_direktur_zh?: string | null;
    sambutan_direktur?: string;
    sambutan_direktur_en?: string | null;
    sambutan_direktur_zh?: string | null;
    foto_direktur?: string | null;

    nama_bupati?: string;
    nama_bupati_en?: string | null;
    nama_bupati_zh?: string | null;
    jabatan_bupati?: string | null;
    jabatan_bupati_en?: string | null;
    jabatan_bupati_zh?: string | null;
    sambutan_bupati?: string;
    sambutan_bupati_en?: string | null;
    sambutan_bupati_zh?: string | null;
    foto_bupati?: string | null;

    status?: boolean;
}

interface Props {
    sambutanDirektur: SambutanData | null;
    sambutanBupati?: SambutanData | null;
}

const props = defineProps<Props>();

const languageOptions = [
    { code: "id" as const, label: "Bahasa Indonesia", flag: "🇮🇩" },
    { code: "en" as const, label: "English", flag: "🇬🇧" },
    { code: "zh" as const, label: "Mandarin", flag: "🇨🇳" },
];

const typeOptions = [
    { code: "direktur" as const, label: "Sambutan Direktur" },
    { code: "bupati" as const, label: "Sambutan Bupati" },
];

const activeType = ref<SambutanType>("direktur");
const activeLanguage = ref<LanguageCode>("id");
const isPageLoading = ref(true);
const isSubmitting = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const editorRef = ref<HTMLDivElement | null>(null);
const previewUrl = ref<string | null>(null);
const objectUrl = ref<string | null>(null);

const MAX_FOTO_SIZE = 1024 * 1024;
const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

const form = useForm<Record<string, any>>({
    // Direktur
    nama_direktur: props.sambutanDirektur?.nama_direktur ?? "",
    nama_direktur_en: props.sambutanDirektur?.nama_direktur_en ?? "",
    nama_direktur_zh: props.sambutanDirektur?.nama_direktur_zh ?? "",

    jabatan_direktur: props.sambutanDirektur?.jabatan_direktur ?? "Direktur",
    jabatan_direktur_en: props.sambutanDirektur?.jabatan_direktur_en ?? "",
    jabatan_direktur_zh: props.sambutanDirektur?.jabatan_direktur_zh ?? "",

    sambutan_direktur: props.sambutanDirektur?.sambutan_direktur ?? "",
    sambutan_direktur_en: props.sambutanDirektur?.sambutan_direktur_en ?? "",
    sambutan_direktur_zh: props.sambutanDirektur?.sambutan_direktur_zh ?? "",

    foto_direktur: null as File | null,
    remove_foto_direktur: false,

    // Bupati
    nama_bupati: props.sambutanBupati?.nama_bupati ?? "",
    nama_bupati_en: props.sambutanBupati?.nama_bupati_en ?? "",
    nama_bupati_zh: props.sambutanBupati?.nama_bupati_zh ?? "",

    jabatan_bupati: props.sambutanBupati?.jabatan_bupati ?? "Bupati",
    jabatan_bupati_en: props.sambutanBupati?.jabatan_bupati_en ?? "",
    jabatan_bupati_zh: props.sambutanBupati?.jabatan_bupati_zh ?? "",

    sambutan_bupati: props.sambutanBupati?.sambutan_bupati ?? "",
    sambutan_bupati_en: props.sambutanBupati?.sambutan_bupati_en ?? "",
    sambutan_bupati_zh: props.sambutanBupati?.sambutan_bupati_zh ?? "",

    foto_bupati: null as File | null,
    remove_foto_bupati: false,

    // Status dipakai bersama
    status: props.sambutanDirektur?.status ?? false,
});

const currentRecord = computed(() =>
    activeType.value === "direktur"
        ? props.sambutanDirektur
        : props.sambutanBupati,
);

const currentTitle = computed(() =>
    activeType.value === "direktur" ? "Direktur" : "Bupati",
);

const languageLabel = computed(
    () =>
        languageOptions.find((item) => item.code === activeLanguage.value)
            ?.label ?? "Bahasa Indonesia",
);

/**
 * Membentuk nama field sesuai konvensi database:
 * nama_direktur, nama_direktur_en, nama_bupati_zh, dan seterusnya.
 */
const field = (base: string, language?: LanguageCode): string => {
    // Status di kedua controller menggunakan nama field yang sama: status
    if (base === "status") {
        return "status";
    }

    // Field hapus foto mengikuti nama masing-masing tabel
    if (base === "remove_foto") {
        return `remove_foto_${activeType.value}`;
    }

    const suffix = language && language !== "id" ? `_${language}` : "";

    return `${base}_${activeType.value}${suffix}`;
};

const getValue = (key: string): any => {
    return form[key] ?? "";
};

const setValue = (key: string, value: any) => {
    form[key] = value;
};

const currentSambutan = computed(
    () => getValue(field("sambutan", activeLanguage.value)) as string,
);

const currentSambutanError = computed(
    () =>
        form.errors[field("sambutan", activeLanguage.value)] as
            | string
            | undefined,
);

const existingFotoUrl = computed(() => {
    const foto = currentRecord.value?.[field("foto") as keyof SambutanData];

    if (typeof foto !== "string" || !foto) return null;

    if (
        foto.startsWith("http://") ||
        foto.startsWith("https://") ||
        foto.startsWith("/")
    ) {
        return foto;
    }

    return `/storage/${foto}`;
});

const displayedFoto = computed(
    () =>
        previewUrl.value ||
        (getValue(field("remove_foto")) ? null : existingFotoUrl.value),
);

const goToDashboard = () => router.visit("/dashboard");

const syncEditor = () => {
    if (!editorRef.value) return;

    setValue(
        field("sambutan", activeLanguage.value),
        editorRef.value.innerHTML,
    );
};

const setLanguage = async (language: LanguageCode) => {
    syncEditor();
    activeLanguage.value = language;

    await nextTick();

    if (editorRef.value) {
        editorRef.value.innerHTML = currentSambutan.value || "";
    }
};

const setType = async (type: SambutanType) => {
    syncEditor();

    activeType.value = type;
    activeLanguage.value = "id";

    setValue(
        "status",
        type === "direktur"
            ? (props.sambutanDirektur?.status ?? false)
            : (props.sambutanBupati?.status ?? false),
    );

    clearPhotoPreview();

    await nextTick();

    if (editorRef.value) {
        editorRef.value.innerHTML = currentSambutan.value || "";
    }
};

watch(
    [activeType, activeLanguage],
    async () => {
        await nextTick();

        if (editorRef.value) {
            editorRef.value.innerHTML = currentSambutan.value || "";
        }
    },
    { flush: "post" },
);

const execCommand = (
    command:
        | "bold"
        | "italic"
        | "underline"
        | "insertUnorderedList"
        | "insertOrderedList",
) => {
    editorRef.value?.focus();
    document.execCommand(command, false);
    syncEditor();
};

const formatParagraph = () => {
    editorRef.value?.focus();
    document.execCommand("formatBlock", false, "p");
    syncEditor();
};

const formatQuote = () => {
    editorRef.value?.focus();
    document.execCommand("formatBlock", false, "blockquote");
    syncEditor();
};

const clearFormatting = () => {
    editorRef.value?.focus();
    document.execCommand("removeFormat", false);
    syncEditor();
};

const handleEditorKeydown = (event: KeyboardEvent) => {
    if (event.key === "Enter") {
        window.setTimeout(syncEditor, 0);
    }
};

const handleEditorPaste = (event: ClipboardEvent) => {
    event.preventDefault();

    const text = event.clipboardData?.getData("text/plain") ?? "";

    const escapeHtml = (value: string) =>
        value
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

    const html = text
        .replace(/\r\n/g, "\n")
        .split(/\n+/)
        .map((line) => line.trim())
        .filter(Boolean)
        .map((line) => `<p>${escapeHtml(line)}</p>`)
        .join("");

    document.execCommand("insertHTML", false, html);
    syncEditor();
};

const clearPhotoPreview = () => {
    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
        objectUrl.value = null;
    }

    previewUrl.value = null;

    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

const handleFile = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) return;

    const photoField = field("foto");
    const removeField = field("remove_foto");

    if (!allowedTypes.includes(file.type)) {
        target.value = "";
        toast.error("Format foto harus JPG, JPEG, PNG, atau WEBP.");
        form.setError(photoField, "Format foto tidak didukung.");
        return;
    }

    if (file.size > MAX_FOTO_SIZE) {
        target.value = "";
        toast.error("Ukuran foto maksimal 1 MB.");
        form.setError(photoField, "Ukuran foto maksimal 1 MB.");
        return;
    }

    form.clearErrors(photoField);
    clearPhotoPreview();

    setValue(photoField, file);
    setValue(removeField, false);

    objectUrl.value = URL.createObjectURL(file);
    previewUrl.value = objectUrl.value;
};

const removePreview = () => {
    clearPhotoPreview();
    setValue(field("foto"), null);
    form.clearErrors(field("foto"));
};

const removeExistingFoto = () => {
    clearPhotoPreview();
    setValue(field("foto"), null);
    setValue(field("remove_foto"), true);
};

const plainText = (html: string) =>
    html
        .replace(/<br\s*\/?>/gi, "")
        .replace(/<[^>]*>/g, "")
        .replace(/&nbsp;/gi, " ")
        .trim();

const submitForm = () => {
    if (isSubmitting.value) return;

    syncEditor();
    form.clearErrors();

    const nameField = field("nama", "id");
    const messageField = field("sambutan", "id");

    if (!String(getValue(nameField)).trim()) {
        form.setError(
            nameField,
            `Nama ${currentTitle.value.toLowerCase()} wajib diisi.`,
        );
        activeLanguage.value = "id";
        toast.error(`Nama ${currentTitle.value.toLowerCase()} wajib diisi.`);
        return;
    }

    if (!plainText(String(getValue(messageField)))) {
        form.setError(
            messageField,
            "Isi sambutan Bahasa Indonesia wajib diisi.",
        );
        activeLanguage.value = "id";
        toast.error("Isi sambutan Bahasa Indonesia wajib diisi.");
        return;
    }

    const data = new FormData();

    for (const base of ["nama", "jabatan", "sambutan"]) {
        for (const language of ["id", "en", "zh"] as LanguageCode[]) {
            const key = field(base, language);
            const value = getValue(key);

            data.append(key, value == null ? "" : String(value));
        }
    }

    data.append("status", getValue("status") ? "1" : "0");

    data.append(
        field("remove_foto"),
        getValue(field("remove_foto")) ? "1" : "0",
    );

    const photo = getValue(field("foto"));

    if (photo instanceof File) {
        data.append(field("foto"), photo);
    }

    data.append("_method", "PUT");

    // Direktur memakai endpoint yang sudah ada.
    // Direktur dan Bupati menggunakan endpoint masing-masing.
    const endpoint =
        activeType.value === "direktur"
            ? "/admin/profil-perusahaan/sambutan"
            : "/admin/profil-perusahaan/sambutan/bupati";

    router.post(endpoint, data, {
        forceFormData: true,
        preserveScroll: true,

        onStart: () => {
            isSubmitting.value = true;
        },

        onError: (errors) => {
            console.error("Gagal menyimpan sambutan:", errors);

            const firstError = Object.values(errors)[0];

            if (firstError) {
                toast.error(String(firstError));
            }

            Object.entries(errors).forEach(([key, message]) => {
                form.setError(key, String(message));
            });
        },

        onSuccess: () => {
            clearPhotoPreview();
            setValue(field("foto"), null);
            setValue(field("remove_foto"), false);

            toast.success(`Sambutan ${currentTitle.value} berhasil disimpan.`);
        },

        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

let initialLoadingTimer: ReturnType<typeof setTimeout> | null = null;
let removeRouterStartListener: (() => void) | null = null;
let removeRouterFinishListener: (() => void) | null = null;

onMounted(async () => {
    await nextTick();

    removeRouterStartListener = router.on("start", (event) => {
        if (!event.detail.visit.preserveState) {
            isPageLoading.value = true;
        }
    });

    removeRouterFinishListener = router.on("finish", () => {
        isPageLoading.value = false;
    });

    initialLoadingTimer = setTimeout(() => {
        isPageLoading.value = false;
    }, 500);
});

onBeforeUnmount(() => {
    removeRouterStartListener?.();
    removeRouterFinishListener?.();

    if (initialLoadingTimer) {
        clearTimeout(initialLoadingTimer);
    }

    clearPhotoPreview();
});
</script>

<template>
    <div
        class="relative min-h-full overflow-hidden bg-slate-50/50 transition-colors duration-300 dark:bg-slate-950/50"
    >
        <!-- Decorative background -->
        <div
            class="pointer-events-none absolute inset-x-0 top-0 z-0 h-96 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -left-24 -top-32 size-96 rounded-full bg-gradient-to-br from-blue-400/30 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15"
            />
            <div
                class="absolute -right-20 top-4 size-80 rounded-full bg-gradient-to-tr from-sky-300/30 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10"
            />
            <div
                class="absolute inset-0 bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px] opacity-40 dark:opacity-20"
            />
            <div
                class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent to-slate-50/90 dark:to-slate-950/90"
            />
        </div>

        <div
            class="relative z-10 mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <!-- Header -->
            <div
                class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <UserRound class="size-5" />
                    </div>

                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400"
                        >
                            Profil Perusahaan
                        </p>
                        <h1
                            class="mt-0.5 text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl"
                        >
                            Pengaturan Sambutan
                        </h1>
                        <p
                            class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Kelola sambutan Direktur dan Bupati dalam tiga
                            bahasa.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white/90 px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900/90 dark:text-slate-300 dark:hover:bg-slate-800"
                    @click="goToDashboard"
                >
                    <ArrowLeft class="size-4" />
                    Kembali
                </button>
            </div>

            <!-- Main tabs -->
            <div
                class="mb-5 rounded-2xl border border-slate-200 bg-white/90 p-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900/90"
            >
                <div class="grid grid-cols-2 gap-1">
                    <button
                        v-for="item in typeOptions"
                        :key="item.code"
                        type="button"
                        class="flex items-center justify-center gap-2 rounded-xl px-3 py-3 text-sm font-semibold transition-all"
                        :class="
                            activeType === item.code
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'
                        "
                        @click="setType(item.code)"
                    >
                        <UserRound class="size-4" />
                        {{ item.label }}
                    </button>
                </div>
            </div>

            <!-- Current language information -->
            <div
                class="mb-5 flex items-center gap-2 rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-3 text-sm text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/20 dark:text-blue-300"
            >
                <span
                    class="flex size-7 items-center justify-center rounded-lg bg-white/80 dark:bg-slate-900/60"
                >
                    {{
                        languageOptions.find(
                            (item) => item.code === activeLanguage,
                        )?.flag
                    }}
                </span>
                <span>
                    Mengedit:
                    <strong>{{ currentTitle }}</strong>
                    ·
                    <strong>{{ languageLabel }}</strong>
                </span>
            </div>

            <!-- Loading -->
            <div v-if="isPageLoading" class="animate-pulse space-y-5">
                <div
                    class="h-20 rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
                />
                <div
                    class="h-[500px] rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
                />
            </div>

            <div
                v-else
                class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/50 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10"
            >
                <div
                    class="border-b border-slate-100 px-5 py-5 dark:border-slate-800 sm:px-6"
                >
                    <h2
                        class="text-sm font-semibold text-slate-800 dark:text-slate-100"
                    >
                        Informasi Sambutan {{ currentTitle }}
                    </h2>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                        Isi nama, jabatan, dan sambutan sesuai bahasa. Foto dan
                        status publikasi berlaku untuk semua bahasa.
                    </p>
                </div>

                <form
                    class="grid gap-8 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_300px]"
                    @submit.prevent="submitForm"
                >
                    <div class="min-w-0 space-y-6">
                        <!-- Language tabs -->
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-1.5 dark:border-slate-800 dark:bg-slate-800/40"
                        >
                            <div class="grid grid-cols-3 gap-1">
                                <button
                                    v-for="language in languageOptions"
                                    :key="language.code"
                                    type="button"
                                    class="flex items-center justify-center gap-2 rounded-xl px-2 py-2.5 text-sm font-semibold transition-all"
                                    :class="
                                        activeLanguage === language.code
                                            ? 'bg-white text-blue-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:text-blue-400 dark:ring-slate-700'
                                            : 'text-slate-500 hover:bg-white/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-900/60 dark:hover:text-white'
                                    "
                                    @click="setLanguage(language.code)"
                                >
                                    <span>{{ language.flag }}</span>
                                    <span>{{ language.label }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Name and position -->
                        <div
                            class="space-y-5 rounded-2xl border border-blue-100 bg-blue-50/30 p-5 dark:border-blue-900/30 dark:bg-blue-950/10"
                        >
                            <div>
                                <label
                                    :for="field('nama', activeLanguage)"
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    {{
                                        activeLanguage === "id"
                                            ? `Nama ${currentTitle}`
                                            : activeLanguage === "en"
                                              ? currentTitle === "Direktur"
                                                  ? "Director's Name"
                                                  : "Regent's Name"
                                              : currentTitle === "Direktur"
                                                ? "董事姓名"
                                                : "县长姓名"
                                    }}
                                    <span
                                        v-if="activeLanguage === 'id'"
                                        class="text-red-500"
                                        >*</span
                                    >
                                </label>

                                <input
                                    :id="field('nama', activeLanguage)"
                                    :value="
                                        getValue(field('nama', activeLanguage))
                                    "
                                    type="text"
                                    maxlength="255"
                                    :placeholder="`Masukkan nama ${currentTitle}`"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    :class="{
                                        'border-red-300':
                                            form.errors[
                                                field('nama', activeLanguage)
                                            ],
                                    }"
                                    @input="
                                        setValue(
                                            field('nama', activeLanguage),
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        )
                                    "
                                />
                                <p
                                    v-if="
                                        form.errors[
                                            field('nama', activeLanguage)
                                        ]
                                    "
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{
                                        form.errors[
                                            field("nama", activeLanguage)
                                        ]
                                    }}
                                </p>
                            </div>

                            <div>
                                <label
                                    :for="field('jabatan', activeLanguage)"
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    {{
                                        activeLanguage === "id"
                                            ? "Jabatan"
                                            : activeLanguage === "en"
                                              ? "Position"
                                              : "职位"
                                    }}
                                </label>
                                <input
                                    :id="field('jabatan', activeLanguage)"
                                    :value="
                                        getValue(
                                            field('jabatan', activeLanguage),
                                        )
                                    "
                                    type="text"
                                    maxlength="255"
                                    :placeholder="
                                        activeLanguage === 'id'
                                            ? `Contoh: ${currentTitle}`
                                            : activeLanguage === 'en'
                                              ? 'Enter position'
                                              : '请输入职位'
                                    "
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    @input="
                                        setValue(
                                            field('jabatan', activeLanguage),
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        )
                                    "
                                />
                                <p
                                    v-if="
                                        form.errors[
                                            field('jabatan', activeLanguage)
                                        ]
                                    "
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{
                                        form.errors[
                                            field("jabatan", activeLanguage)
                                        ]
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Rich text editor -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{
                                    activeLanguage === "id"
                                        ? "Isi Sambutan"
                                        : activeLanguage === "en"
                                          ? "Message"
                                          : "致辞内容"
                                }}
                                <span
                                    v-if="activeLanguage === 'id'"
                                    class="text-red-500"
                                    >*</span
                                >
                            </label>

                            <div
                                class="flex flex-wrap items-center gap-1 rounded-t-xl border border-b-0 border-slate-200 bg-slate-50 p-2 dark:border-slate-700 dark:bg-slate-800/70"
                            >
                                <button
                                    type="button"
                                    class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-white hover:text-blue-600 dark:text-slate-300 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="formatParagraph"
                                >
                                    ¶ Paragraf
                                </button>
                                <span
                                    class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"
                                />
                                <button
                                    type="button"
                                    title="Tebal"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="execCommand('bold')"
                                >
                                    <Bold class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    title="Miring"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="execCommand('italic')"
                                >
                                    <Italic class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    title="Garis bawah"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="execCommand('underline')"
                                >
                                    <Underline class="size-4" />
                                </button>
                                <span
                                    class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"
                                />
                                <button
                                    type="button"
                                    title="Bullet list"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="execCommand('insertUnorderedList')"
                                >
                                    <List class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    title="Numbered list"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="execCommand('insertOrderedList')"
                                >
                                    <ListOrdered class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    title="Kutipan"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="formatQuote"
                                >
                                    <Quote class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-500 transition hover:bg-white hover:text-red-500 dark:text-slate-400 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="clearFormatting"
                                >
                                    Bersihkan format
                                </button>
                            </div>

                            <div
                                ref="editorRef"
                                contenteditable="true"
                                role="textbox"
                                aria-multiline="true"
                                :aria-label="languageLabel"
                                :data-placeholder="
                                    activeLanguage === 'id'
                                        ? `Tulis sambutan ${currentTitle} dalam Bahasa Indonesia...`
                                        : activeLanguage === 'en'
                                          ? `Write the ${currentTitle.toLowerCase()}'s message in English...`
                                          : '请在此填写致辞内容...'
                                "
                                class="min-h-[280px] w-full rounded-b-xl border border-slate-200 bg-white px-4 py-4 text-sm leading-7 text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                :class="{
                                    'border-red-300': currentSambutanError,
                                }"
                                @input="syncEditor"
                                @keydown="handleEditorKeydown"
                                @paste="handleEditorPaste"
                            />
                            <div
                                class="mt-1.5 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <p
                                    v-if="currentSambutanError"
                                    class="text-xs text-red-500"
                                >
                                    {{ currentSambutanError }}
                                </p>
                                <p v-else class="text-xs text-slate-400">
                                    Mendukung teks tebal, miring, garis bawah,
                                    paragraf, dan daftar.
                                </p>
                                <span class="text-xs text-slate-400">
                                    HTML rich text
                                </span>
                            </div>
                        </div>

                        <!-- Publication status -->
                        <div
                            class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-4 dark:border-slate-800 dark:bg-slate-800/40"
                        >
                            <div>
                                <p
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-100"
                                >
                                    Tampilkan di halaman publik
                                </p>
                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Aktifkan agar sambutan dapat dilihat
                                    pengunjung.
                                </p>
                            </div>
                            <button
                                type="button"
                                role="switch"
                                :aria-checked="
                                    Boolean(getValue(field('status')))
                                "
                                class="relative h-6 w-11 shrink-0 rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
                                :class="
                                    getValue(field('status'))
                                        ? 'bg-blue-600'
                                        : 'bg-slate-300 dark:bg-slate-700'
                                "
                                @click="
                                    setValue(
                                        field('status'),
                                        !getValue(field('status')),
                                    )
                                "
                            >
                                <span
                                    class="absolute top-1 size-4 rounded-full bg-white shadow-sm transition-all"
                                    :class="
                                        getValue(field('status'))
                                            ? 'left-6'
                                            : 'left-1'
                                    "
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Photo and save -->
                    <aside class="min-w-0 space-y-5">
                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div class="mb-4">
                                <h3
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-100"
                                >
                                    Foto {{ currentTitle }}
                                </h3>
                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    JPG, PNG, atau WEBP. Maksimal 1 MB.
                                </p>
                            </div>

                            <div
                                class="relative flex min-h-[220px] items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50"
                            >
                                <img
                                    v-if="displayedFoto"
                                    :src="displayedFoto"
                                    :alt="`Foto ${currentTitle}`"
                                    class="h-64 w-full object-contain p-3"
                                />
                                <div
                                    v-else
                                    class="flex flex-col items-center gap-3 px-4 py-8 text-center"
                                >
                                    <div
                                        class="flex size-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        <ImagePlus class="size-7" />
                                    </div>
                                    <p
                                        class="text-sm font-medium text-slate-600 dark:text-slate-300"
                                    >
                                        Belum ada foto
                                    </p>
                                </div>

                                <button
                                    v-if="previewUrl"
                                    type="button"
                                    class="absolute right-3 top-3 flex size-8 items-center justify-center rounded-lg bg-red-500 text-white shadow transition hover:bg-red-600"
                                    title="Batalkan foto baru"
                                    @click="removePreview"
                                >
                                    <X class="size-4" />
                                </button>
                                <button
                                    v-else-if="
                                        existingFotoUrl &&
                                        !getValue(field('remove_foto'))
                                    "
                                    type="button"
                                    class="absolute right-3 top-3 flex size-8 items-center justify-center rounded-lg bg-red-500 text-white shadow transition hover:bg-red-600"
                                    title="Hapus foto"
                                    @click="removeExistingFoto"
                                >
                                    <X class="size-4" />
                                </button>
                            </div>

                            <p
                                v-if="form.errors[field('foto')]"
                                class="mt-2 text-xs text-red-500"
                            >
                                {{ form.errors[field("foto")] }}
                            </p>

                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="handleFile"
                            />

                            <button
                                type="button"
                                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                @click="fileInput?.click()"
                            >
                                <Upload class="size-4" />
                                {{
                                    displayedFoto ? "Ganti Foto" : "Unggah Foto"
                                }}
                            </button>
                        </div>

                        <div
                            class="rounded-2xl border border-blue-100 bg-blue-50/50 p-4 dark:border-blue-900/30 dark:bg-blue-950/10"
                        >
                            <p
                                class="text-sm font-semibold text-slate-800 dark:text-slate-100"
                            >
                                Siap menyimpan?
                            </p>
                            <p
                                class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                            >
                                Pastikan nama dan isi sambutan Bahasa Indonesia
                                sudah diisi sebelum menyimpan.
                            </p>

                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <Save class="size-4" />
                                {{
                                    isSubmitting
                                        ? "Menyimpan..."
                                        : `Simpan Sambutan ${currentTitle}`
                                }}
                            </button>
                        </div>
                    </aside>
                </form>
            </div>
        </div>
    </div>
</template>
