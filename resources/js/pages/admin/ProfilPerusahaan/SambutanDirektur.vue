<script setup lang="ts">
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
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

interface SambutanDirektur {
    id: number;
    nama_direktur: string;
    jabatan_direktur: string | null;
    sambutan_direktur: string;
    foto_direktur: string | null;
    status: boolean;
    created_at?: string;
    updated_at?: string;
}

interface Props {
    sambutanDirektur: SambutanDirektur | null;
}

const props = defineProps<Props>();

/*
|--------------------------------------------------------------------------
| Page Loading
|--------------------------------------------------------------------------
*/

const isPageLoading = ref(true);

let initialLoadingTimer: ReturnType<typeof setTimeout> | null = null;
let removeRouterStartListener: (() => void) | null = null;
let removeRouterFinishListener: (() => void) | null = null;

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const fileInput = ref<HTMLInputElement | null>(null);
const editorRef = ref<HTMLDivElement | null>(null);

const previewUrl = ref<string | null>(null);
const objectUrl = ref<string | null>(null);

const DASHBOARD_URL = "/dashboard";

const goToDashboard = () => {
    router.visit(DASHBOARD_URL);
};

const isSubmitting = ref(false);
const isProcessingFoto = ref(false);

const MAX_FOTO_SIZE = 1024 * 1024; // 1 MB

const form = useForm({
    nama_direktur: props.sambutanDirektur?.nama_direktur ?? "",

    jabatan_direktur: props.sambutanDirektur?.jabatan_direktur ?? "Direktur",

    sambutan_direktur: props.sambutanDirektur?.sambutan_direktur ?? "",

    foto_direktur: null as File | null,

    status: props.sambutanDirektur?.status ?? false,

    remove_foto_direktur: false,
});

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const existingFotoUrl = computed(() => {
    const foto = props.sambutanDirektur?.foto_direktur;

    if (!foto) {
        return null;
    }

    if (
        foto.startsWith("http://") ||
        foto.startsWith("https://") ||
        foto.startsWith("/")
    ) {
        return foto;
    }

    return `/storage/${foto}`;
});

const displayedFoto = computed(() => {
    return previewUrl.value || existingFotoUrl.value;
});

const hasExistingFoto = computed(() => {
    return !!existingFotoUrl.value;
});

const hasSambutanContent = computed(() => {
    const text = form.sambutan_direktur
        .replace(/<br\s*\/?>/gi, "")
        .replace(/<[^>]*>/g, "")
        .replace(/&nbsp;/gi, " ")
        .trim();

    return text.length > 0;
});

/*
|--------------------------------------------------------------------------
| Editor
|--------------------------------------------------------------------------
*/

/*
| Editor berada di dalam blok v-else (muncul setelah skeleton hilang),
| jadi isinya harus diisi saat elemen benar-benar sudah dirender.
| Watch ini berjalan setiap kali editor muncul/dibuat ulang.
*/

watch(
    editorRef,
    (el) => {
        if (el) {
            el.innerHTML = form.sambutan_direktur;
        }
    },
    { flush: "post" },
);

const syncEditor = () => {
    if (!editorRef.value) {
        return;
    }

    form.sambutan_direktur = editorRef.value.innerHTML;
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
    if (event.key === "Enter") {
        window.setTimeout(() => {
            syncEditor();
        }, 0);
    }
};

const handleEditorPaste = (event: ClipboardEvent) => {
    event.preventDefault();

    const text = event.clipboardData?.getData("text/plain") ?? "";

    const escape = (s: string) =>
        s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

    const html = text
        .replace(/\r\n/g, "\n")
        .split(/\n+/)
        .map((line) => line.trim())
        .filter(Boolean)
        .map((line) => `<p>${escape(line)}</p>`)
        .join("");

    document.execCommand("insertHTML", false, html);

    syncEditor();
};

/*
|--------------------------------------------------------------------------
| File Upload
|--------------------------------------------------------------------------
*/

const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

/*
| Kompres & konversi gambar di browser.
| Foto dari kamera HP biasanya 2-8 MB (atau HEIC di iPhone),
| jadi diperkecil dulu sebelum dicek batas ukuran.
*/

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
    const needsConversion = !allowedTypes.includes(file.type);

    // Sudah kecil dan formatnya valid → tidak perlu diproses.
    if (!needsConversion && file.size <= MAX_FOTO_SIZE) {
        return file;
    }

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

            if (!ctx) {
                return file;
            }

            // Latar putih agar PNG transparan tidak menjadi hitam di JPEG.
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            const blob = await canvasToBlob(canvas, "image/jpeg", quality);

            if (blob && blob.size <= MAX_FOTO_SIZE) {
                return new File(
                    [blob],
                    file.name.replace(/\.\w+$/, "") + ".jpg",
                    { type: "image/jpeg" },
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

    if (!selected) {
        return;
    }

    isProcessingFoto.value = true;

    const file = await compressImage(selected);

    isProcessingFoto.value = false;

    if (file.size > MAX_FOTO_SIZE) {
        target.value = "";
        form.foto_direktur = null;

        form.setError("foto_direktur", "Ukuran foto maksimal 1 MB.");

        return;
    }

    if (!allowedTypes.includes(file.type)) {
        target.value = "";
        form.foto_direktur = null;

        form.setError(
            "foto_direktur",
            "Format foto harus JPG, JPEG, PNG, atau WEBP.",
        );

        return;
    }

    form.clearErrors("foto_direktur");

    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
    }

    form.foto_direktur = file;

    objectUrl.value = URL.createObjectURL(file);

    previewUrl.value = objectUrl.value;

    /*
    | Foto baru otomatis membatalkan flag hapus foto.
    */

    form.remove_foto_direktur = false;
};

const removePreview = () => {
    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);

        objectUrl.value = null;
    }

    previewUrl.value = null;

    form.foto_direktur = null;

    if (fileInput.value) {
        fileInput.value.value = "";
    }

    form.clearErrors("foto_direktur");
};

const removeExistingFoto = () => {
    if (previewUrl.value) {
        removePreview();
    }

    form.remove_foto_direktur = true;
};

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submitForm = () => {
    if (isSubmitting.value || isProcessingFoto.value) {
        return;
    }

    syncEditor();

    form.clearErrors();

    let hasError = false;

    if (!form.nama_direktur.trim()) {
        form.setError("nama_direktur", "Nama direktur wajib diisi.");
        hasError = true;
    }

    if (!form.jabatan_direktur.trim()) {
        form.setError("jabatan_direktur", "Jabatan wajib diisi.");
        hasError = true;
    }

    if (!hasSambutanContent.value) {
        form.setError("sambutan_direktur", "Isi sambutan wajib diisi.");
        hasError = true;
    }

    if (hasError) {
        return;
    }

    const formData = new FormData();

    formData.append("nama_direktur", form.nama_direktur.trim());

    formData.append("jabatan_direktur", form.jabatan_direktur.trim());

    formData.append("sambutan_direktur", form.sambutan_direktur);

    formData.append("status", form.status ? "1" : "0");

    formData.append(
        "remove_foto_direktur",
        form.remove_foto_direktur ? "1" : "0",
    );

    if (form.foto_direktur) {
        formData.append("foto_direktur", form.foto_direktur);
    }

    formData.append("_method", "PUT");

    router.post("/admin/profil-perusahaan/sambutan", formData, {
        forceFormData: true,
        preserveScroll: true,

        onStart: () => {
            isSubmitting.value = true;
        },

        onError: (errors) => {
            console.error("Gagal memperbarui sambutan direktur:", errors);

            // Tampilkan error dari server pada field terkait.
            Object.entries(errors).forEach(([key, message]) => {
                form.setError(key as keyof typeof form.data, message as string);
            });
        },

        onSuccess: () => {
            /*
            | Reset file baru setelah berhasil.
            */

            if (objectUrl.value) {
                URL.revokeObjectURL(objectUrl.value);

                objectUrl.value = null;
            }

            previewUrl.value = null;
            form.foto_direktur = null;
            form.remove_foto_direktur = false;

            if (fileInput.value) {
                fileInput.value.value = "";
            }
        },

        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

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

    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
    }
});
</script>

<template>
    <div
        class="relative min-h-full overflow-hidden bg-slate-50 transition-colors duration-300 dark:bg-[#07111f]"
    >
        <!-- ===================================================== -->
        <!-- DECORATIVE BACKGROUND -->
        <!-- ===================================================== -->

        <div
            class="pointer-events-none absolute -left-24 -top-24 z-0 size-72 rounded-full bg-gradient-to-br from-blue-400/25 to-indigo-500/15 blur-3xl dark:from-blue-500/15 dark:to-indigo-600/10"
            aria-hidden="true"
        />

        <div
            class="pointer-events-none absolute -right-28 top-40 z-0 size-80 rounded-full bg-gradient-to-br from-sky-400/20 to-blue-500/10 blur-3xl dark:from-sky-500/10 dark:to-blue-600/10"
            aria-hidden="true"
        />

        <div
            class="pointer-events-none absolute -bottom-40 left-1/3 z-0 size-96 rounded-full bg-gradient-to-br from-indigo-400/10 to-cyan-400/10 blur-3xl dark:from-indigo-500/10 dark:to-cyan-500/5"
            aria-hidden="true"
        />

        <!-- GRID -->

        <div
            class="pointer-events-none absolute inset-0 z-0 opacity-[0.35] dark:opacity-[0.08]"
            aria-hidden="true"
        >
            <div
                class="absolute inset-0"
                style="
                    background-image:
                        linear-gradient(
                            rgba(100, 116, 139, 0.08) 1px,
                            transparent 1px
                        ),
                        linear-gradient(
                            90deg,
                            rgba(100, 116, 139, 0.08) 1px,
                            transparent 1px
                        );
                    background-size: 32px 32px;
                    mask-image: linear-gradient(
                        to bottom,
                        black,
                        transparent 75%
                    );
                "
            />
        </div>

        <div class="relative z-10">
            <Transition name="page-fade" mode="out-in">
                <!-- ===================================================== -->
                <!-- SKELETON -->
                <!-- ===================================================== -->

                <div
                    v-if="isPageLoading"
                    key="skeleton"
                    class="mx-auto w-full max-w-[1200px] animate-pulse space-y-5 p-4 sm:p-5 lg:p-6 xl:p-8"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="size-11 rounded-2xl bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="space-y-2">
                                <div
                                    class="h-5 w-48 rounded-lg bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-72 max-w-[70vw] rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                            </div>
                        </div>

                        <div
                            class="h-10 w-full rounded-xl bg-slate-200 sm:w-28 dark:bg-slate-800"
                        />
                    </div>

                    <div
                        class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-slate-200 px-5 py-5 dark:border-slate-800"
                        >
                            <div
                                class="h-5 w-48 rounded-lg bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="mt-2 h-4 w-72 max-w-full rounded-lg bg-slate-200 dark:bg-slate-800"
                            />
                        </div>

                        <div
                            class="grid gap-8 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_260px]"
                        >
                            <div class="space-y-5">
                                <div class="grid gap-5 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <div
                                            class="h-4 w-28 rounded bg-slate-200 dark:bg-slate-800"
                                        />

                                        <div
                                            class="h-11 rounded-xl bg-slate-100 dark:bg-slate-800"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <div
                                            class="h-4 w-20 rounded bg-slate-200 dark:bg-slate-800"
                                        />

                                        <div
                                            class="h-11 rounded-xl bg-slate-100 dark:bg-slate-800"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <div
                                        class="h-4 w-28 rounded bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-64 rounded-xl bg-slate-100 dark:bg-slate-800"
                                    />
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div
                                    class="h-4 w-28 rounded bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="mx-auto size-24 rounded-2xl bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-12 rounded-xl bg-slate-100 dark:bg-slate-800"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- CONTENT -->
                <!-- ===================================================== -->

                <div
                    v-else
                    key="content"
                    class="mx-auto w-full max-w-[1200px] space-y-5 p-4 sm:p-5 lg:p-6 xl:p-8"
                >
                    <!-- HEADER -->

                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-sm shadow-blue-500/20"
                            >
                                <UserRound class="size-5" />
                            </div>

                            <div class="min-w-0">
                                <h1
                                    class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                                >
                                    Sambutan Direktur
                                </h1>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Kelola informasi sambutan dan foto Direktur
                                    perusahaan.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:focus-visible:ring-offset-slate-950"
                            @click="goToDashboard"
                        >
                            <ArrowLeft class="size-4" />
                            Kembali
                        </button>
                    </div>

                    <!-- MAIN CARD -->

                    <div
                        class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/50 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
                    >
                        <!-- CARD HEADER -->

                        <div
                            class="border-b border-slate-200 px-5 py-5 sm:px-6 dark:border-slate-800"
                        >
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Informasi Sambutan
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Konten ini bersifat tunggal dan digunakan pada
                                halaman profil perusahaan.
                            </p>
                        </div>

                        <!-- FORM -->

                        <form
                            class="grid gap-8 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_260px]"
                            @submit.prevent="submitForm"
                        >
                            <!-- ===================================================== -->
                            <!-- LEFT -->
                            <!-- ===================================================== -->

                            <div class="min-w-0 space-y-5">
                                <!-- NAMA + JABATAN -->

                                <div class="grid gap-5 sm:grid-cols-2">
                                    <!-- NAMA -->

                                    <div>
                                        <label
                                            for="nama_direktur"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Nama Direktur
                                            <span class="text-red-500">
                                                *
                                            </span>
                                        </label>

                                        <input
                                            id="nama_direktur"
                                            v-model="form.nama_direktur"
                                            type="text"
                                            autocomplete="name"
                                            placeholder="Contoh: Budi Santoso"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                            :class="{
                                                'border-red-300 focus:border-red-500 focus:ring-red-500/10':
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

                                    <!-- JABATAN -->

                                    <div>
                                        <label
                                            for="jabatan_direktur"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Jabatan
                                            <span class="text-red-500">
                                                *
                                            </span>
                                        </label>

                                        <input
                                            id="jabatan_direktur"
                                            v-model="form.jabatan_direktur"
                                            type="text"
                                            placeholder="Contoh: Direktur Utama"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                            :class="{
                                                'border-red-300 focus:border-red-500 focus:ring-red-500/10':
                                                    form.errors
                                                        .jabatan_direktur,
                                            }"
                                        />

                                        <p
                                            v-if="form.errors.jabatan_direktur"
                                            class="mt-1.5 text-xs text-red-500"
                                        >
                                            {{ form.errors.jabatan_direktur }}
                                        </p>
                                    </div>
                                </div>

                                <!-- SAMBUTAN -->

                                <div>
                                    <label
                                        for="sambutan-editor"
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Isi Sambutan
                                        <span class="text-red-500"> * </span>
                                    </label>

                                    <!-- TOOLBAR -->

                                    <div
                                        class="flex flex-wrap items-center gap-1 rounded-t-xl border border-b-0 border-slate-200 bg-slate-50 p-2 dark:border-slate-700 dark:bg-slate-800/70"
                                    >
                                        <button
                                            type="button"
                                            title="Paragraf"
                                            class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-white hover:text-blue-600 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-blue-400"
                                            @mousedown.prevent
                                            @click="formatParagraph"
                                        >
                                            ¶ Paragraf
                                        </button>

                                        <div
                                            class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"
                                        />

                                        <button
                                            type="button"
                                            title="Tebal"
                                            aria-label="Tebal"
                                            class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-blue-400"
                                            @mousedown.prevent
                                            @click="execCommand('bold')"
                                        >
                                            <Bold class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            title="Miring"
                                            aria-label="Miring"
                                            class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-blue-400"
                                            @mousedown.prevent
                                            @click="execCommand('italic')"
                                        >
                                            <Italic class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            title="Garis bawah"
                                            aria-label="Garis bawah"
                                            class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-blue-400"
                                            @mousedown.prevent
                                            @click="execCommand('underline')"
                                        >
                                            <Underline class="size-4" />
                                        </button>

                                        <div
                                            class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"
                                        />

                                        <button
                                            type="button"
                                            title="Bullet"
                                            aria-label="Bullet list"
                                            class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-blue-400"
                                            @mousedown.prevent
                                            @click="
                                                execCommand(
                                                    'insertUnorderedList',
                                                )
                                            "
                                        >
                                            <List class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            title="Numbering"
                                            aria-label="Numbered list"
                                            class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-blue-400"
                                            @mousedown.prevent
                                            @click="
                                                execCommand('insertOrderedList')
                                            "
                                        >
                                            <ListOrdered class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            title="Quote"
                                            aria-label="Quote"
                                            class="rounded-lg p-2 text-slate-700 transition hover:bg-white hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-blue-400"
                                            @mousedown.prevent
                                            @click="formatQuote"
                                        >
                                            <Quote class="size-4" />
                                        </button>

                                        <div
                                            class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"
                                        />

                                        <button
                                            type="button"
                                            title="Hapus format"
                                            class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-500 transition hover:bg-white hover:text-red-500 dark:text-slate-400 dark:hover:bg-slate-700"
                                            @mousedown.prevent
                                            @click="clearFormatting"
                                        >
                                            Bersihkan
                                        </button>
                                    </div>

                                    <!-- EDITOR -->

                                    <div
                                        id="sambutan-editor"
                                        ref="editorRef"
                                        contenteditable="true"
                                        role="textbox"
                                        aria-multiline="true"
                                        aria-label="Isi Sambutan Direktur"
                                        data-placeholder="Tulis sambutan Direktur di sini..."
                                        class="min-h-[260px] w-full rounded-b-xl border border-slate-200 bg-white px-4 py-4 text-sm leading-7 text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500/10':
                                                form.errors.sambutan_direktur,
                                        }"
                                        @input="syncEditor"
                                        @keydown="handleEditorKeydown"
                                        @paste="handleEditorPaste"
                                    />

                                    <div
                                        class="mt-1.5 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <p
                                            v-if="form.errors.sambutan_direktur"
                                            class="text-xs text-red-500"
                                        >
                                            {{ form.errors.sambutan_direktur }}
                                        </p>

                                        <p
                                            v-else
                                            class="text-xs text-slate-400"
                                        >
                                            Mendukung
                                            <strong> tebal </strong>,
                                            <em> miring </em>, underline,
                                            paragraf, dan daftar.
                                        </p>

                                        <span class="text-xs text-slate-400">
                                            HTML rich text
                                        </span>
                                    </div>
                                </div>

                                <!-- STATUS -->

                                <div
                                    class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/40"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Tampilkan di halaman publik
                                        </p>

                                        <p
                                            class="mt-0.5 text-xs text-slate-400"
                                        >
                                            Aktifkan agar sambutan dapat
                                            ditampilkan kepada pengunjung.
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
                                                form.status
                                                    ? 'left-[22px]'
                                                    : 'left-0.5'
                                            "
                                        />
                                    </button>
                                </div>
                            </div>

                            <!-- ===================================================== -->
                            <!-- RIGHT : FOTO -->
                            <!-- ===================================================== -->

                            <div class="min-w-0">
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-800/40"
                                >
                                    <!-- TITLE -->

                                    <div class="mb-4">
                                        <div class="flex items-center gap-2">
                                            <ImagePlus
                                                class="size-4 text-blue-600"
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
                                            Foto portrait untuk ditampilkan pada
                                            halaman publik.
                                        </p>
                                    </div>

                                    <!-- PHOTO -->

                                    <div class="flex justify-center">
                                        <div
                                            class="relative size-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
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
                                                    class="size-8"
                                                    stroke-width="1.5"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- UPLOAD -->

                                    <div class="mt-4">
                                        <label
                                            class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white px-3 py-3 text-xs font-medium text-slate-600 transition hover:border-blue-400 hover:bg-blue-50/30 hover:text-blue-600 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:bg-blue-950/20"
                                            :class="{
                                                'pointer-events-none opacity-60':
                                                    isProcessingFoto,
                                            }"
                                        >
                                            <Upload class="size-4" />

                                            <span>
                                                {{
                                                    isProcessingFoto
                                                        ? "Memproses foto..."
                                                        : previewUrl
                                                          ? "Ganti Foto"
                                                          : "Pilih Foto"
                                                }}
                                            </span>

                                            <input
                                                ref="fileInput"
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                class="hidden"
                                                @change="handleFile"
                                            />
                                        </label>

                                        <p
                                            class="mt-2 text-center text-[10px] leading-4 text-slate-400"
                                        >
                                            JPG, JPEG, PNG, WEBP · Maksimal 1 MB
                                            (foto besar dikompres otomatis)
                                        </p>

                                        <p
                                            v-if="form.errors.foto_direktur"
                                            class="mt-2 text-center text-xs text-red-500"
                                        >
                                            {{ form.errors.foto_direktur }}
                                        </p>

                                        <!-- REMOVE NEW -->

                                        <button
                                            v-if="previewUrl"
                                            type="button"
                                            class="mt-2 w-full text-center text-[11px] font-medium text-red-500 transition hover:text-red-600"
                                            @click="removePreview"
                                        >
                                            Batalkan foto baru
                                        </button>

                                        <!-- REMOVE EXISTING -->

                                        <button
                                            v-else-if="
                                                hasExistingFoto &&
                                                !form.remove_foto_direktur
                                            "
                                            type="button"
                                            class="mt-2 w-full text-center text-[11px] font-medium text-red-500 transition hover:text-red-600"
                                            @click="removeExistingFoto"
                                        >
                                            Hapus foto saat ini
                                        </button>

                                        <div
                                            v-if="form.remove_foto_direktur"
                                            class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-center text-[11px] leading-4 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-400"
                                        >
                                            Foto akan dihapus setelah perubahan
                                            disimpan.
                                        </div>
                                    </div>

                                    <!-- REMOVE BG INFO -->

                                    <div
                                        class="mt-4 rounded-xl border border-blue-100 bg-blue-50/70 px-3 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/20"
                                    >
                                        <p
                                            class="text-[11px] font-medium text-blue-700 dark:text-blue-400"
                                        >
                                            Background removal
                                        </p>

                                        <p
                                            class="mt-0.5 text-[10px] leading-4 text-blue-600/80 dark:text-blue-400/70"
                                        >
                                            Foto akan mencoba diproses otomatis
                                            tanpa background. Fitur ini
                                            menggunakan kuota layanan. Jika
                                            kuota atau layanan tidak tersedia,
                                            foto tetap disimpan dengan
                                            background asli.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ===================================================== -->
                            <!-- BUTTON -->
                            <!-- ===================================================== -->

                            <div
                                class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:col-span-2 sm:flex-row sm:justify-end sm:gap-3 dark:border-slate-800"
                            >
                                <button
                                    type="button"
                                    :disabled="isSubmitting"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                    @click="goToDashboard"
                                >
                                    <X class="size-4" />
                                    Batal
                                </button>

                                <button
                                    type="submit"
                                    :disabled="isSubmitting || isProcessingFoto"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-500/10 transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus-visible:ring-offset-slate-950"
                                >
                                    <Save
                                        class="size-4"
                                        :class="{
                                            'animate-pulse': isSubmitting,
                                        }"
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
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.page-fade-enter-active,
.page-fade-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.page-fade-enter-from,
.page-fade-leave-to {
    opacity: 0;
    transform: translateY(6px);
}

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

@media (prefers-reduced-motion: reduce) {
    .page-fade-enter-active,
    .page-fade-leave-active {
        transition: none;
    }
}
</style>
