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

interface SambutanDirektur {
    id: number;
    nama_direktur: string;
    nama_direktur_en: string | null;
    nama_direktur_zh: string | null;
    jabatan_direktur: string | null;
    jabatan_direktur_en: string | null;
    jabatan_direktur_zh: string | null;
    sambutan_direktur: string;
    sambutan_direktur_en: string | null;
    sambutan_direktur_zh: string | null;
    foto_direktur: string | null;
    status: boolean;
    created_at?: string;
    updated_at?: string;
}

interface Props {
    sambutanDirektur: SambutanDirektur | null;
}

const props = defineProps<Props>();

const languageOptions: Array<{
    code: LanguageCode;
    label: string;
    short: string;
    flag: string;
}> = [
    { code: "id", label: "Bahasa Indonesia", short: "ID", flag: "🇮🇩" },
    { code: "en", label: "English", short: "EN", flag: "🇬🇧" },
    { code: "zh", label: "中文", short: "中文", flag: "🇨🇳" },
];

const activeLanguage = ref<LanguageCode>("id");
const isPageLoading = ref(true);
const isSubmitting = ref(false);
const isProcessingFoto = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const editorRef = ref<HTMLDivElement | null>(null);
const previewUrl = ref<string | null>(null);
const objectUrl = ref<string | null>(null);
const MAX_FOTO_SIZE = 1024 * 1024;
const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

let initialLoadingTimer: ReturnType<typeof setTimeout> | null = null;
let removeRouterStartListener: (() => void) | null = null;
let removeRouterFinishListener: (() => void) | null = null;

const form = useForm({
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
    status: props.sambutanDirektur?.status ?? false,
    remove_foto_direktur: false,
});

const existingFotoUrl = computed(() => {
    const foto = props.sambutanDirektur?.foto_direktur;
    if (!foto) return null;
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
        (form.remove_foto_direktur ? null : existingFotoUrl.value),
);

const languageLabel = computed(
    () =>
        languageOptions.find((item) => item.code === activeLanguage.value)
            ?.label ?? "Bahasa Indonesia",
);

const currentSambutan = computed(() => {
    if (activeLanguage.value === "en") return form.sambutan_direktur_en;
    if (activeLanguage.value === "zh") return form.sambutan_direktur_zh;
    return form.sambutan_direktur;
});

const currentSambutanError = computed(() => {
    if (activeLanguage.value === "en") return form.errors.sambutan_direktur_en;
    if (activeLanguage.value === "zh") return form.errors.sambutan_direktur_zh;
    return form.errors.sambutan_direktur;
});

const goToDashboard = () => router.visit("/dashboard");

const setLanguage = async (language: LanguageCode) => {
    syncEditor();
    activeLanguage.value = language;
    await nextTick();
    if (editorRef.value) editorRef.value.innerHTML = currentSambutan.value;
};

watch(
    editorRef,
    (el) => {
        if (el) el.innerHTML = currentSambutan.value;
    },
    { flush: "post" },
);

watch(activeLanguage, async () => {
    await nextTick();
    if (editorRef.value) editorRef.value.innerHTML = currentSambutan.value;
});

const syncEditor = () => {
    if (!editorRef.value) return;
    const value = editorRef.value.innerHTML;
    if (activeLanguage.value === "en") form.sambutan_direktur_en = value;
    else if (activeLanguage.value === "zh") form.sambutan_direktur_zh = value;
    else form.sambutan_direktur = value;
};

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
    if (event.key === "Enter") window.setTimeout(syncEditor, 0);
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

const loadImage = (file: File): Promise<HTMLImageElement> =>
    new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve(img);
        };
        img.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error("Gagal membaca gambar."));
        };
        img.src = url;
    });

const canvasToBlob = (
    canvas: HTMLCanvasElement,
    type: string,
    quality: number,
): Promise<Blob | null> =>
    new Promise((resolve) => canvas.toBlob(resolve, type, quality));

const compressImage = async (file: File): Promise<File> => {
    if (allowedTypes.includes(file.type) && file.size <= MAX_FOTO_SIZE)
        return file;
    try {
        const img = await loadImage(file);
        let maxWidth = 1200;
        let quality = 0.85;
        for (let attempt = 0; attempt < 5; attempt++) {
            const scale = Math.min(1, maxWidth / img.width);
            const canvas = document.createElement("canvas");
            canvas.width = Math.round(img.width * scale);
            canvas.height = Math.round(img.height * scale);
            const ctx = canvas.getContext("2d");
            if (!ctx) return file;
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            const blob = await canvasToBlob(canvas, "image/jpeg", quality);
            if (blob && blob.size <= MAX_FOTO_SIZE) {
                return new File(
                    [blob],
                    file.name.replace(/\.\w+$/, "") + ".jpg",
                    {
                        type: "image/jpeg",
                    },
                );
            }
            maxWidth = Math.round(maxWidth * 0.8);
            quality = Math.max(0.6, quality - 0.08);
        }
        return file;
    } catch {
        return file;
    }
};

const handleFile = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const selected = target.files?.[0] ?? null;
    if (!selected) return;

    isProcessingFoto.value = true;
    const file = await compressImage(selected);
    isProcessingFoto.value = false;

    if (file.size > MAX_FOTO_SIZE) {
        target.value = "";
        form.foto_direktur = null;
        form.setError("foto_direktur", "Ukuran foto maksimal 1 MB.");
        toast.error("Ukuran foto maksimal 1 MB.");
        return;
    }

    if (!allowedTypes.includes(file.type)) {
        target.value = "";
        form.foto_direktur = null;
        form.setError(
            "foto_direktur",
            "Format foto harus JPG, JPEG, PNG, atau WEBP.",
        );
        toast.error("Format foto harus JPG, JPEG, PNG, atau WEBP.");
        return;
    }

    form.clearErrors("foto_direktur");
    if (objectUrl.value) URL.revokeObjectURL(objectUrl.value);
    form.foto_direktur = file;
    objectUrl.value = URL.createObjectURL(file);
    previewUrl.value = objectUrl.value;
    form.remove_foto_direktur = false;
};

const removePreview = () => {
    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
        objectUrl.value = null;
    }
    previewUrl.value = null;
    form.foto_direktur = null;
    if (fileInput.value) fileInput.value.value = "";
    form.clearErrors("foto_direktur");
};

const removeExistingFoto = () => {
    if (previewUrl.value) removePreview();
    form.remove_foto_direktur = true;
};

const plainText = (html: string) =>
    html
        .replace(/<br\s*\/?>/gi, "")
        .replace(/<[^>]*>/g, "")
        .replace(/&nbsp;/gi, " ")
        .trim();

const submitForm = () => {
    if (isSubmitting.value || isProcessingFoto.value) return;
    syncEditor();
    form.clearErrors();

    if (!form.nama_direktur.trim()) {
        form.setError("nama_direktur", "Nama direktur wajib diisi.");
        activeLanguage.value = "id";
        toast.error("Nama direktur Bahasa Indonesia wajib diisi.");
        return;
    }

    if (!plainText(form.sambutan_direktur)) {
        form.setError(
            "sambutan_direktur",
            "Isi sambutan Bahasa Indonesia wajib diisi.",
        );
        activeLanguage.value = "id";
        toast.error("Isi sambutan Bahasa Indonesia wajib diisi.");
        return;
    }

    const data = new FormData();
    data.append("nama_direktur", form.nama_direktur.trim());
    data.append("nama_direktur_en", form.nama_direktur_en.trim());
    data.append("nama_direktur_zh", form.nama_direktur_zh.trim());
    data.append("jabatan_direktur", form.jabatan_direktur.trim());
    data.append("jabatan_direktur_en", form.jabatan_direktur_en.trim());
    data.append("jabatan_direktur_zh", form.jabatan_direktur_zh.trim());
    data.append("sambutan_direktur", form.sambutan_direktur);
    data.append("sambutan_direktur_en", form.sambutan_direktur_en);
    data.append("sambutan_direktur_zh", form.sambutan_direktur_zh);
    data.append("status", form.status ? "1" : "0");
    data.append("remove_foto_direktur", form.remove_foto_direktur ? "1" : "0");
    if (form.foto_direktur) data.append("foto_direktur", form.foto_direktur);
    data.append("_method", "PUT");

    router.post("/admin/profil-perusahaan/sambutan", data, {
        forceFormData: true,
        preserveScroll: true,
        onStart: () => {
            isSubmitting.value = true;
        },
        onError: (errors) => {
            console.error("Gagal memperbarui sambutan direktur:", errors);
            const firstError = Object.values(errors)[0];
            if (firstError)
                toast.error(
                    Array.isArray(firstError)
                        ? firstError[0]
                        : String(firstError),
                );
            Object.entries(errors).forEach(([key, message]) => {
                form.setError(key as keyof typeof form.data, message as string);
            });
        },
        onSuccess: () => {
            if (objectUrl.value) {
                URL.revokeObjectURL(objectUrl.value);
                objectUrl.value = null;
            }
            previewUrl.value = null;
            form.foto_direktur = null;
            form.remove_foto_direktur = false;
            if (fileInput.value) fileInput.value.value = "";
            toast.success("Sambutan Direktur berhasil disimpan.");
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

onMounted(async () => {
    await nextTick();
    removeRouterStartListener = router.on("start", (event) => {
        if (!event.detail.visit.preserveState) isPageLoading.value = true;
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
    if (initialLoadingTimer) clearTimeout(initialLoadingTimer);
    if (objectUrl.value) URL.revokeObjectURL(objectUrl.value);
});
</script>

<template>
    <div
        class="relative min-h-full overflow-hidden bg-slate-50/50 transition-colors duration-300 dark:bg-slate-950/50"
    >
        <!-- Decorative background, matching the Galeri admin page -->
        <div
            class="pointer-events-none absolute inset-x-0 top-0 z-0 h-96 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -left-24 -top-32 size-96 rounded-full bg-gradient-to-br from-blue-400/30 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15"
            ></div>
            <div
                class="absolute -right-20 top-4 size-80 rounded-full bg-gradient-to-tr from-sky-300/30 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10"
            ></div>
            <div
                class="absolute left-1/3 -top-40 size-72 rounded-full bg-gradient-to-br from-indigo-300/20 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/10"
            ></div>
            <div class="absolute inset-0 opacity-40 dark:opacity-20">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px]"
                ></div>
            </div>
            <div
                class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent to-slate-50/90 dark:to-slate-950/90"
            ></div>
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
                            Sambutan Direktur
                        </h1>
                        <p
                            class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Kelola sambutan Direktur dalam tiga bahasa.
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

            <!-- Language info -->
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
                <span
                    >Bahasa yang sedang diedit:
                    <strong>{{ languageLabel }}</strong></span
                >
            </div>

            <!-- Loading skeleton -->
            <div v-if="isPageLoading" class="animate-pulse space-y-5">
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mb-5 h-5 w-48 rounded-lg bg-slate-200 dark:bg-slate-800"
                    ></div>
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
                        <div class="space-y-5">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div
                                    class="h-12 rounded-xl bg-slate-100 dark:bg-slate-800"
                                ></div>
                                <div
                                    class="h-12 rounded-xl bg-slate-100 dark:bg-slate-800"
                                ></div>
                            </div>
                            <div
                                class="h-64 rounded-xl bg-slate-100 dark:bg-slate-800"
                            ></div>
                        </div>
                        <div
                            class="h-72 rounded-2xl bg-slate-100 dark:bg-slate-800"
                        ></div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/50 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10"
            >
                <div
                    class="border-b border-slate-100 px-5 py-5 sm:px-6 dark:border-slate-800"
                >
                    <h2
                        class="text-sm font-semibold text-slate-800 dark:text-slate-100"
                    >
                        Informasi Sambutan
                    </h2>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                        Isi konten Indonesia, Inggris, dan Mandarin secara
                        terpisah. Foto dan status berlaku untuk semua bahasa.
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
                                    class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all"
                                    :class="
                                        activeLanguage === language.code
                                            ? 'bg-white text-blue-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:text-blue-400 dark:ring-slate-700'
                                            : 'text-slate-500 hover:bg-white/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-900/60 dark:hover:text-white'
                                    "
                                    @click="setLanguage(language.code)"
                                >
                                    <span>{{ language.flag }}</span
                                    ><span>{{ language.label }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Language-specific fields -->
                        <div
                            class="rounded-2xl border border-blue-100 bg-blue-50/30 p-5 dark:border-blue-900/30 dark:bg-blue-950/10"
                        >
                            <div
                                v-if="activeLanguage === 'id'"
                                class="space-y-5"
                            >
                                <div>
                                    <label
                                        for="nama_direktur"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        >Nama Direktur
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        id="nama_direktur"
                                        v-model="form.nama_direktur"
                                        type="text"
                                        maxlength="255"
                                        autocomplete="name"
                                        placeholder="Masukkan nama Direktur"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                        :class="{
                                            'border-red-300':
                                                form.errors.nama_direktur,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.nama_direktur"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ form.errors.nama_direktur }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        for="jabatan_direktur"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        >Jabatan Direktur</label
                                    >
                                    <input
                                        id="jabatan_direktur"
                                        v-model="form.jabatan_direktur"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Contoh: Direktur Utama"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <p
                                        v-if="form.errors.jabatan_direktur"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ form.errors.jabatan_direktur }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-else-if="activeLanguage === 'en'"
                                class="space-y-5"
                            >
                                <div>
                                    <label
                                        for="nama_direktur_en"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        >Director's Name</label
                                    >
                                    <input
                                        id="nama_direktur_en"
                                        v-model="form.nama_direktur_en"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Enter director's name"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <p
                                        v-if="form.errors.nama_direktur_en"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ form.errors.nama_direktur_en }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        for="jabatan_direktur_en"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        >Director's Position</label
                                    >
                                    <input
                                        id="jabatan_direktur_en"
                                        v-model="form.jabatan_direktur_en"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Example: President Director"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <p
                                        v-if="form.errors.jabatan_direktur_en"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ form.errors.jabatan_direktur_en }}
                                    </p>
                                </div>
                            </div>

                            <div v-else class="space-y-5">
                                <div>
                                    <label
                                        for="nama_direktur_zh"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        >董事姓名</label
                                    >
                                    <input
                                        id="nama_direktur_zh"
                                        v-model="form.nama_direktur_zh"
                                        type="text"
                                        maxlength="255"
                                        placeholder="请输入董事姓名"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <p
                                        v-if="form.errors.nama_direktur_zh"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ form.errors.nama_direktur_zh }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        for="jabatan_direktur_zh"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        >董事职位</label
                                    >
                                    <input
                                        id="jabatan_direktur_zh"
                                        v-model="form.jabatan_direktur_zh"
                                        type="text"
                                        maxlength="255"
                                        placeholder="例如：董事总经理"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <p
                                        v-if="form.errors.jabatan_direktur_zh"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ form.errors.jabatan_direktur_zh }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Rich text editor -->
                        <div>
                            <label
                                for="sambutan-editor"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{
                                    activeLanguage === "id"
                                        ? "Isi Sambutan"
                                        : activeLanguage === "en"
                                          ? "Director's Message"
                                          : "董事致辞"
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
                                    title="Paragraf"
                                    class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-white hover:text-blue-600 dark:text-slate-300 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="formatParagraph"
                                >
                                    ¶ Paragraf
                                </button>
                                <span
                                    class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"
                                ></span>
                                <button
                                    type="button"
                                    title="Tebal"
                                    aria-label="Tebal"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="execCommand('bold')"
                                >
                                    <Bold class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    title="Miring"
                                    aria-label="Miring"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="execCommand('italic')"
                                >
                                    <Italic class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    title="Garis bawah"
                                    aria-label="Garis bawah"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="execCommand('underline')"
                                >
                                    <Underline class="size-4" />
                                </button>
                                <span
                                    class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"
                                ></span>
                                <button
                                    type="button"
                                    title="Bullet list"
                                    aria-label="Bullet list"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="execCommand('insertUnorderedList')"
                                >
                                    <List class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    title="Numbered list"
                                    aria-label="Numbered list"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="execCommand('insertOrderedList')"
                                >
                                    <ListOrdered class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    title="Kutipan"
                                    aria-label="Kutipan"
                                    class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @mousedown.prevent
                                    @click="formatQuote"
                                >
                                    <Quote class="size-4" />
                                </button>
                                <span
                                    class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"
                                ></span>
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
                                id="sambutan-editor"
                                ref="editorRef"
                                contenteditable="true"
                                role="textbox"
                                aria-multiline="true"
                                :aria-label="languageLabel"
                                :data-placeholder="
                                    activeLanguage === 'id'
                                        ? 'Tulis sambutan Direktur dalam Bahasa Indonesia...'
                                        : activeLanguage === 'en'
                                          ? 'Write the director’s message in English...'
                                          : '请在此填写董事致辞...'
                                "
                                class="min-h-[280px] w-full rounded-b-xl border border-slate-200 bg-white px-4 py-4 text-sm leading-7 text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                :class="{
                                    'border-red-300': currentSambutanError,
                                }"
                                @input="syncEditor"
                                @keydown="handleEditorKeydown"
                                @paste="handleEditorPaste"
                            ></div>
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
                                <span class="text-xs text-slate-400"
                                    >HTML rich text</span
                                >
                            </div>
                        </div>

                        <!-- Status -->
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
                                :aria-checked="form.status"
                                class="relative h-6 w-11 shrink-0 rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
                                :class="
                                    form.status
                                        ? 'bg-blue-600'
                                        : 'bg-slate-300 dark:bg-slate-700'
                                "
                                @click="form.status = !form.status"
                            >
                                <span
                                    class="absolute top-0.5 size-5 rounded-full bg-white shadow-sm transition"
                                    :class="
                                        form.status ? 'left-[22px]' : 'left-0.5'
                                    "
                                ></span>
                            </button>
                        </div>
                    </div>

                    <!-- Photo panel -->
                    <aside class="min-w-0">
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                        >
                            <div class="mb-4">
                                <div class="flex items-center gap-2">
                                    <ImagePlus
                                        class="size-4 text-blue-600 dark:text-blue-400"
                                    />
                                    <h3
                                        class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        Foto Direktur
                                    </h3>
                                </div>
                                <p
                                    class="mt-1 text-xs leading-5 text-slate-400"
                                >
                                    Foto yang sama digunakan untuk ketiga
                                    bahasa.
                                </p>
                            </div>

                            <div class="flex justify-center">
                                <div
                                    class="relative size-36 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <img
                                        v-if="displayedFoto"
                                        :src="displayedFoto"
                                        alt="Foto Direktur"
                                        class="size-full object-cover object-top"
                                    />
                                    <div
                                        v-else
                                        class="flex size-full items-center justify-center text-slate-300 dark:text-slate-600"
                                    >
                                        <UserRound
                                            class="size-10"
                                            stroke-width="1.5"
                                        />
                                    </div>
                                </div>
                            </div>

                            <label
                                class="mt-4 flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white px-3 py-3 text-xs font-semibold text-slate-600 transition hover:border-blue-400 hover:bg-blue-50/30 hover:text-blue-600 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500"
                            >
                                <Upload class="size-4" />
                                <span>{{
                                    isProcessingFoto
                                        ? "Memproses foto..."
                                        : previewUrl
                                          ? "Ganti Foto"
                                          : "Pilih Foto"
                                }}</span>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                    :disabled="isProcessingFoto"
                                    @change="handleFile"
                                />
                            </label>
                            <p
                                class="mt-2 text-center text-[10px] leading-4 text-slate-400"
                            >
                                JPG, JPEG, PNG, WEBP · Maksimal 1 MB. Foto besar
                                dikompres otomatis.
                            </p>
                            <p
                                v-if="form.errors.foto_direktur"
                                class="mt-2 text-center text-xs text-red-500"
                            >
                                {{ form.errors.foto_direktur }}
                            </p>

                            <button
                                v-if="previewUrl"
                                type="button"
                                class="mt-3 w-full text-center text-xs font-semibold text-red-500 transition hover:text-red-600"
                                @click="removePreview"
                            >
                                Batalkan foto baru
                            </button>
                            <button
                                v-else-if="
                                    existingFotoUrl &&
                                    !form.remove_foto_direktur
                                "
                                type="button"
                                class="mt-3 w-full text-center text-xs font-semibold text-red-500 transition hover:text-red-600"
                                @click="removeExistingFoto"
                            >
                                Hapus foto saat ini
                            </button>
                            <div
                                v-if="form.remove_foto_direktur"
                                class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-center text-[11px] leading-4 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-400"
                            >
                                Foto akan dihapus setelah perubahan disimpan.
                            </div>

                            <div
                                class="mt-5 rounded-xl border border-blue-100 bg-blue-50/70 px-3 py-3 dark:border-blue-900/40 dark:bg-blue-950/20"
                            >
                                <p
                                    class="text-xs font-semibold text-blue-700 dark:text-blue-400"
                                >
                                    Catatan gambar
                                </p>
                                <p
                                    class="mt-1 text-[11px] leading-5 text-blue-600/80 dark:text-blue-400/70"
                                >
                                    Gunakan foto portrait dengan pencahayaan
                                    yang baik agar tampil optimal pada halaman
                                    profil perusahaan.
                                </p>
                            </div>
                        </div>
                    </aside>

                    <!-- Footer buttons -->
                    <div
                        class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end sm:gap-3 lg:col-span-2 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            :disabled="isSubmitting"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="goToDashboard"
                        >
                            <X class="size-4" /> Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting || isProcessingFoto"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Save
                                class="size-4"
                                :class="{ 'animate-pulse': isSubmitting }"
                            />
                            {{
                                isSubmitting
                                    ? "Menyimpan..."
                                    : "Simpan Perubahan"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
[contenteditable="true"]:empty::before {
    content: attr(data-placeholder);
    color: rgb(148 163 184);
    pointer-events: none;
}
.dark [contenteditable="true"]:empty::before {
    color: rgb(100 116 139);
}
[contenteditable="true"] p {
    margin: 0 0 0.75rem;
}
[contenteditable="true"] p:last-child {
    margin-bottom: 0;
}
[contenteditable="true"] ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}
[contenteditable="true"] ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}
[contenteditable="true"] blockquote {
    margin: 0.75rem 0;
    border-left: 3px solid rgb(59 130 246 / 0.5);
    padding-left: 1rem;
    color: rgb(100 116 139);
}
</style>
