<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowDown,
    ArrowUp,
    Building2,
    Check,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    Eye,
    FileText,
    Globe,
    Image as ImageIcon,
    Pencil,
    Plus,
    Search,
    ToggleLeft,
    ToggleRight,
    Trash2,
    X,
} from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    currentLanguage,
    localizedValue,
    type LanguageCode,
} from "@/composables/useLocale";

defineOptions({
    layout: AppLayout,
});

/**
 * |--------------------------------------------------------------------------
 * | Interfaces
 * |--------------------------------------------------------------------------
 */

interface Fasilitas {
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
    created_at?: string;
    updated_at?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
}

interface FasilitasPagination {
    data: Fasilitas[];
    links?: PaginationLink[];
    meta?: PaginationMeta;
    current_page?: number;
    from?: number | null;
    last_page?: number;
    per_page?: number;
    to?: number | null;
    total?: number;
}

interface FormErrors {
    nama?: string;
    nama_en?: string;
    nama_zh?: string;
    slug?: string;
    deskripsi?: string;
    deskripsi_en?: string;
    deskripsi_zh?: string;
    gambar?: string;
    urutan?: string;
    aktif?: string;
    remove_gambar?: string;
}

/**
 * |--------------------------------------------------------------------------
 * | Props
 * |--------------------------------------------------------------------------
 */

const props = defineProps<{
    fasilitas: FasilitasPagination | null;
    filters?: {
        search?: string;
    };
}>();

const BASE_URL = "/admin/kawasan/fasilitas";

/**
 * |--------------------------------------------------------------------------
 * | Language
 * |--------------------------------------------------------------------------
 *
 * currentLanguage (useLocale) = bahasa tampilan tabel.
 * activeLanguage / detailLanguage = tab bahasa lokal di modal form / detail,
 * supaya mengedit tidak mengubah bahasa tampilan global.
 */

const languageTabs: { code: LanguageCode; flag: string; label: string }[] = [
    { code: "id", flag: "🇮🇩", label: "Indonesia" },
    { code: "en", flag: "🇬🇧", label: "English" },
    { code: "zh", flag: "🇨🇳", label: "中文" },
];

const activeLanguage = ref<LanguageCode>("id");
const detailLanguage = ref<LanguageCode>("id");

const languageLabel = computed(
    () =>
        languageTabs.find((tab) => tab.code === activeLanguage.value)?.label ??
        "Indonesia",
);

const fieldKey = (field: "nama" | "deskripsi", lang: LanguageCode): string =>
    lang === "id" ? field : `${field}_${lang}`;

const hasTranslation = (item: Fasilitas, lang: LanguageCode): boolean => {
    const record = item as unknown as Record<string, unknown>;

    return (
        String(record[fieldKey("nama", lang)] ?? "").trim() !== "" ||
        String(record[fieldKey("deskripsi", lang)] ?? "").trim() !== ""
    );
};

/**
 * |--------------------------------------------------------------------------
 * | Page Loading
 * |--------------------------------------------------------------------------
 */

const isPageLoading = ref(true);

let removeRouterStartListener: (() => void) | null = null;
let removeRouterFinishListener: (() => void) | null = null;

onMounted(() => {
    removeRouterStartListener = router.on("start", (event) => {
        if (!event.detail.visit.preserveState) {
            isPageLoading.value = true;
        }
    });

    removeRouterFinishListener = router.on("finish", () => {
        isPageLoading.value = false;
    });

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            isPageLoading.value = false;
        });
    });
});

/**
 * |--------------------------------------------------------------------------
 * | Modal State
 * |--------------------------------------------------------------------------
 */

const showFormModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);

const modalMode = ref<"create" | "edit">("create");

const selectedFasilitas = ref<Fasilitas | null>(null);

/**
 * |--------------------------------------------------------------------------
 * | Form State
 * |--------------------------------------------------------------------------
 */

const emptyForm = () => ({
    nama: "",
    nama_en: "",
    nama_zh: "",
    slug: "",
    deskripsi: "",
    deskripsi_en: "",
    deskripsi_zh: "",
    gambar: null as File | null,
    urutan: "",
    aktif: true,
    remove_gambar: false,
});

const form = ref(emptyForm());

const existingImage = ref<string | null>(null);
const previewUrl = ref<string | null>(null);

const processing = ref(false);
const processingDelete = ref(false);
const processingToggleId = ref<number | null>(null);
const processingMoveId = ref<number | null>(null);

const errors = ref<FormErrors>({});

const hasLanguageError = (lang: LanguageCode): boolean => {
    const record = errors.value as Record<string, string | undefined>;

    return Boolean(
        record[fieldKey("nama", lang)] || record[fieldKey("deskripsi", lang)],
    );
};

const focusFirstErrorLanguage = (): void => {
    const firstWithError = languageTabs.find((tab) =>
        hasLanguageError(tab.code),
    );

    if (firstWithError) {
        activeLanguage.value = firstWithError.code;
    }
};

/**
 * |--------------------------------------------------------------------------
 * | Slug (preview saja, backend/model yang menentukan slug final)
 * |--------------------------------------------------------------------------
 *
 * Slug selalu dibuat dari nama Bahasa Indonesia.
 */

const isHydratingForm = ref(false);

const slugify = (value: string): string => {
    return String(value ?? "")
        .normalize("NFKD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-")
        .replace(/^-+|-+$/g, "");
};

watch(
    () => form.value.nama,
    (value) => {
        if (isHydratingForm.value) {
            return;
        }

        form.value.slug = slugify(value);
    },
);

/**
 * |--------------------------------------------------------------------------
 * | Search
 * |--------------------------------------------------------------------------
 */

const search = ref(props.filters?.search ?? "");

let searchTimer: ReturnType<typeof setTimeout> | null = null;

const submitSearch = (): void => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        router.get(
            BASE_URL,
            {
                search: search.value.trim() || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 350);
};

const clearSearch = (): void => {
    if (searchTimer) {
        clearTimeout(searchTimer);
        searchTimer = null;
    }

    search.value = "";

    router.get(
        BASE_URL,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

/**
 * |--------------------------------------------------------------------------
 * | Helpers
 * |--------------------------------------------------------------------------
 */

const toStringValue = (value: string | number | null | undefined): string => {
    if (value === null || value === undefined) {
        return "";
    }

    return String(value).trim();
};

const truncate = (text: string | null, length = 150): string => {
    if (!text) {
        return "-";
    }

    return text.length > length ? `${text.substring(0, length)}...` : text;
};

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

    return `/storage/${image}`;
};

const getTotal = (): number =>
    props.fasilitas?.meta?.total ??
    props.fasilitas?.total ??
    props.fasilitas?.data?.length ??
    0;

const getCurrentPage = (): number =>
    props.fasilitas?.meta?.current_page ?? props.fasilitas?.current_page ?? 1;

const getPerPage = (): number =>
    props.fasilitas?.meta?.per_page ??
    props.fasilitas?.per_page ??
    props.fasilitas?.data?.length ??
    10;

const getLastPage = (): number =>
    props.fasilitas?.meta?.last_page ?? props.fasilitas?.last_page ?? 1;

const getFrom = (): number =>
    props.fasilitas?.meta?.from ?? props.fasilitas?.from ?? 0;

const getTo = (): number =>
    props.fasilitas?.meta?.to ?? props.fasilitas?.to ?? 0;

const getRowNumber = (index: number): number =>
    (getCurrentPage() - 1) * getPerPage() + index + 1;

/**
 * Nama/deskripsi sesuai bahasa tampilan (useLocale), fallback ke Indonesia.
 */
const displayName = (item: Fasilitas): string =>
    localizedValue(item as unknown as Record<string, unknown>, "nama");

const displayDescription = (item: Fasilitas): string =>
    localizedValue(item as unknown as Record<string, unknown>, "deskripsi");

/**
 * Nama/deskripsi untuk tab bahasa tertentu (tanpa fallback) di modal detail.
 */
const getDetailValue = (
    item: Fasilitas,
    field: "nama" | "deskripsi",
): string => {
    const record = item as unknown as Record<string, unknown>;
    const value = record[fieldKey(field, detailLanguage.value)];

    return toStringValue(value as string | null | undefined);
};

/**
 * Posisi global (lintas halaman) untuk menonaktifkan tombol naik/turun.
 * Controller menukar urutan dengan tetangga terdekat, sehingga hanya
 * item pertama (paling atas) dan terakhir (paling bawah) yang tidak bisa pindah.
 */
const isFirstItem = (index: number): boolean => getRowNumber(index) <= 1;

const isLastItem = (index: number): boolean =>
    getRowNumber(index) >= getTotal();

/**
 * |--------------------------------------------------------------------------
 * | Pagination
 * |--------------------------------------------------------------------------
 */

const getPaginationLinks = (): PaginationLink[] => {
    return props.fasilitas?.links ?? [];
};

const paginationPageLabel = (label: string): string => {
    return label
        .replace(/&laquo;/g, "")
        .replace(/&raquo;/g, "")
        .replace(/Previous/gi, "")
        .replace(/Next/gi, "")
        .trim();
};

const goToPage = (url: string | null): void => {
    if (!url) {
        return;
    }

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const firstPageUrl = (): string | null => {
    if (getCurrentPage() <= 1) {
        return null;
    }

    const links = getPaginationLinks();

    return links.length > 2 ? (links[1]?.url ?? null) : null;
};

const lastPageUrl = (): string | null => {
    if (getCurrentPage() >= getLastPage()) {
        return null;
    }

    const links = getPaginationLinks();

    return links.length > 2 ? (links[links.length - 2]?.url ?? null) : null;
};

const previousPageUrl = (): string | null => {
    return getPaginationLinks()[0]?.url ?? null;
};

const nextPageUrl = (): string | null => {
    const links = getPaginationLinks();

    return links.length > 0 ? (links[links.length - 1]?.url ?? null) : null;
};

const navButtonClass = (url: string | null): string =>
    url
        ? "text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
        : "cursor-not-allowed text-slate-300 dark:text-slate-700";

/**
 * |--------------------------------------------------------------------------
 * | Form Reset
 * |--------------------------------------------------------------------------
 */

const resetForm = (): void => {
    isHydratingForm.value = true;

    form.value = emptyForm();

    existingImage.value = null;
    errors.value = {};
    activeLanguage.value = "id";

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    requestAnimationFrame(() => {
        isHydratingForm.value = false;
    });
};

/**
 * |--------------------------------------------------------------------------
 * | Create
 * |--------------------------------------------------------------------------
 */

const openCreate = (): void => {
    if (processing.value) {
        return;
    }

    modalMode.value = "create";
    selectedFasilitas.value = null;

    resetForm();

    showDetailModal.value = false;
    showDeleteModal.value = false;
    showFormModal.value = true;
};

/**
 * |--------------------------------------------------------------------------
 * | Edit
 * |--------------------------------------------------------------------------
 */

const openEdit = (item: Fasilitas): void => {
    if (processing.value) {
        return;
    }

    modalMode.value = "edit";
    selectedFasilitas.value = item;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    errors.value = {};
    activeLanguage.value = "id";

    isHydratingForm.value = true;

    form.value = {
        nama: toStringValue(item.nama),
        nama_en: toStringValue(item.nama_en),
        nama_zh: toStringValue(item.nama_zh),
        slug: toStringValue(item.slug),
        deskripsi: toStringValue(item.deskripsi),
        deskripsi_en: toStringValue(item.deskripsi_en),
        deskripsi_zh: toStringValue(item.deskripsi_zh),
        gambar: null,
        urutan: toStringValue(item.urutan),
        aktif: Boolean(item.aktif),
        remove_gambar: false,
    };

    existingImage.value = item.gambar;

    requestAnimationFrame(() => {
        isHydratingForm.value = false;
    });

    showDetailModal.value = false;
    showDeleteModal.value = false;
    showFormModal.value = true;
};

/**
 * |--------------------------------------------------------------------------
 * | Close Form
 * |--------------------------------------------------------------------------
 */

const closeForm = (): void => {
    if (processing.value) {
        return;
    }

    showFormModal.value = false;
    selectedFasilitas.value = null;

    resetForm();
};

const forceCloseForm = (): void => {
    showFormModal.value = false;
    selectedFasilitas.value = null;

    resetForm();
};

/**
 * |--------------------------------------------------------------------------
 * | Image Upload
 * |--------------------------------------------------------------------------
 */

const handleImageChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;

    const file = target.files?.[0] ?? null;

    errors.value.gambar = undefined;
    form.value.remove_gambar = false;

    if (!file) {
        form.value.gambar = null;

        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = null;
        }

        return;
    }

    if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) {
        errors.value.gambar =
            "Gambar harus berformat JPG, JPEG, PNG, atau WEBP.";

        target.value = "";
        form.value.gambar = null;

        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        errors.value.gambar = "Ukuran gambar maksimal 2 MB.";

        target.value = "";
        form.value.gambar = null;

        return;
    }

    form.value.gambar = file;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = URL.createObjectURL(file);
};

const removeSelectedImage = (): void => {
    form.value.gambar = null;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
};

const removeExistingImage = (): void => {
    existingImage.value = null;
    form.value.remove_gambar = true;
};

/**
 * |--------------------------------------------------------------------------
 * | Submit
 * |--------------------------------------------------------------------------
 *
 * Slug tidak dikirim. Controller menerima:
 * nama, nama_en, nama_zh, deskripsi, deskripsi_en, deskripsi_zh,
 * gambar, urutan, aktif, remove_gambar (edit).
 */

const submit = (): void => {
    if (processing.value) {
        return;
    }

    errors.value = {};

    const nama = toStringValue(form.value.nama);
    const namaEn = toStringValue(form.value.nama_en);
    const namaZh = toStringValue(form.value.nama_zh);

    const deskripsi = toStringValue(form.value.deskripsi);
    const deskripsiEn = toStringValue(form.value.deskripsi_en);
    const deskripsiZh = toStringValue(form.value.deskripsi_zh);

    const urutan = toStringValue(form.value.urutan);

    if (!nama) {
        errors.value.nama = "Nama fasilitas (Indonesia) wajib diisi.";
        activeLanguage.value = "id";

        return;
    }

    if (!slugify(nama)) {
        errors.value.nama =
            "Nama harus mengandung huruf atau angka agar slug dapat dibuat.";
        activeLanguage.value = "id";

        return;
    }

    const isEdit =
        modalMode.value === "edit" && selectedFasilitas.value !== null;

    const formData = new FormData();

    formData.append("nama", nama);
    formData.append("nama_en", namaEn);
    formData.append("nama_zh", namaZh);
    formData.append("deskripsi", deskripsi);
    formData.append("deskripsi_en", deskripsiEn);
    formData.append("deskripsi_zh", deskripsiZh);
    formData.append("urutan", urutan || "0");
    formData.append("aktif", form.value.aktif ? "1" : "0");

    if (form.value.gambar instanceof File) {
        formData.append("gambar", form.value.gambar);
    }

    if (isEdit) {
        formData.append("remove_gambar", form.value.remove_gambar ? "1" : "0");
        formData.append("_method", "PUT");
    }

    const url = isEdit
        ? `${BASE_URL}/${selectedFasilitas.value!.id}`
        : BASE_URL;

    processing.value = true;

    router.post(url, formData, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,

        onSuccess: () => {
            forceCloseForm();

            showDetailModal.value = false;
            showDeleteModal.value = false;
        },

        onError: (serverErrors) => {
            errors.value = serverErrors as FormErrors;

            focusFirstErrorLanguage();

            console.error("Gagal menyimpan fasilitas:", serverErrors);
        },

        onCancel: () => {
            console.warn("Request penyimpanan fasilitas dibatalkan.");
        },

        onFinish: () => {
            processing.value = false;
        },
    });
};

/**
 * |--------------------------------------------------------------------------
 * | Detail
 * |--------------------------------------------------------------------------
 */

const openDetail = (item: Fasilitas): void => {
    selectedFasilitas.value = item;
    detailLanguage.value = "id";

    showFormModal.value = false;
    showDeleteModal.value = false;
    showDetailModal.value = true;
};

const closeDetail = (): void => {
    showDetailModal.value = false;
    selectedFasilitas.value = null;
};

/**
 * |--------------------------------------------------------------------------
 * | Delete
 * |--------------------------------------------------------------------------
 */

const openDelete = (item: Fasilitas): void => {
    if (processingDelete.value || processing.value) {
        return;
    }

    selectedFasilitas.value = item;

    showFormModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = true;
};

const closeDelete = (): void => {
    if (processingDelete.value) {
        return;
    }

    showDeleteModal.value = false;
    selectedFasilitas.value = null;
};

const deleteFasilitas = (): void => {
    if (!selectedFasilitas.value || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(`${BASE_URL}/${selectedFasilitas.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDeleteModal.value = false;
            selectedFasilitas.value = null;
        },

        onError: (serverErrors) => {
            console.error("Gagal menghapus fasilitas:", serverErrors);
        },

        onFinish: () => {
            processingDelete.value = false;
        },
    });
};

/**
 * |--------------------------------------------------------------------------
 * | Toggle Aktif
 * |--------------------------------------------------------------------------
 */

const toggleAktif = (item: Fasilitas): void => {
    if (processingToggleId.value !== null) {
        return;
    }

    processingToggleId.value = item.id;

    router.patch(
        `${BASE_URL}/${item.id}/toggle-aktif`,
        {},
        {
            preserveScroll: true,
            preserveState: true,

            onError: (serverErrors) => {
                console.error("Gagal mengubah status fasilitas:", serverErrors);
            },

            onFinish: () => {
                processingToggleId.value = null;
            },
        },
    );
};

/**
 * |--------------------------------------------------------------------------
 * | Move Up / Down
 * |--------------------------------------------------------------------------
 */

const moveFasilitas = (item: Fasilitas, direction: "up" | "down"): void => {
    if (processingMoveId.value !== null || processing.value) {
        return;
    }

    processingMoveId.value = item.id;

    router.patch(
        `${BASE_URL}/${item.id}/move-${direction}`,
        {},
        {
            preserveScroll: true,
            preserveState: true,

            onError: (serverErrors) => {
                console.error("Gagal mengubah urutan fasilitas:", serverErrors);
            },

            onFinish: () => {
                processingMoveId.value = null;
            },
        },
    );
};

/**
 * |--------------------------------------------------------------------------
 * | Cleanup
 * |--------------------------------------------------------------------------
 */

onBeforeUnmount(() => {
    removeRouterStartListener?.();
    removeRouterFinishListener?.();

    if (searchTimer) {
        clearTimeout(searchTimer);
        searchTimer = null;
    }

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
});
</script>

<template>
    <div
        class="relative min-h-full overflow-hidden bg-slate-50 transition-colors duration-300 dark:bg-[#07111f]"
        :aria-busy="isPageLoading ? 'true' : 'false'"
    >
        <!-- =========================================================
             DECORATIVE BACKGROUND
        ========================================================== -->

        <div
            class="pointer-events-none absolute inset-0 z-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="blob-shape absolute -left-24 -top-32 h-96 w-96 rounded-full bg-gradient-to-br from-blue-400/30 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/25 dark:via-indigo-500/15 dark:to-transparent"
            ></div>

            <div
                class="blob-shape-delayed absolute -right-20 top-0 h-80 w-80 rounded-full bg-gradient-to-tr from-sky-300/30 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/20 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <div
                class="blob-shape-slow absolute -top-40 left-[30%] h-72 w-72 rounded-full bg-gradient-to-br from-indigo-300/25 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/15 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <div
                class="blob-shape absolute -bottom-40 right-[20%] h-72 w-72 rounded-full bg-gradient-to-br from-cyan-300/20 via-blue-300/10 to-transparent blur-3xl dark:from-cyan-500/10 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <div class="absolute inset-0 opacity-40 dark:opacity-15">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px]"
                ></div>
            </div>

            <div
                class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent via-slate-50/40 to-slate-50/90 dark:via-slate-950/40 dark:to-slate-950/90"
            ></div>
        </div>

        <!-- =========================================================
             CONTENT
        ========================================================== -->

        <div
            class="relative z-10 mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <!-- SKELETON -->

            <div v-if="isPageLoading" class="animate-pulse">
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div
                            class="size-10 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"
                        ></div>

                        <div class="min-w-0 space-y-2">
                            <div
                                class="h-5 w-40 max-w-full rounded-md bg-slate-200 dark:bg-slate-800"
                            ></div>

                            <div
                                class="h-4 w-64 max-w-full rounded-md bg-slate-200 dark:bg-slate-800"
                            ></div>
                        </div>
                    </div>

                    <div
                        class="h-10 w-full rounded-xl bg-slate-200 sm:w-40 dark:bg-slate-800"
                    ></div>
                </div>

                <div
                    class="mb-5 rounded-3xl border border-slate-200/80 bg-white/90 p-4 shadow-sm shadow-slate-200/40 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20"
                >
                    <div
                        class="h-10 w-full rounded-xl bg-slate-200 dark:bg-slate-800"
                    ></div>
                </div>

                <div
                    class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/40 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1200px] text-left text-sm">
                            <thead
                                class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <tr>
                                    <th
                                        v-for="width in [
                                            'w-10',
                                            'w-40',
                                            'w-52',
                                            'w-20',
                                            'w-20',
                                            'w-24',
                                        ]"
                                        :key="width"
                                        class="px-6 py-4"
                                    >
                                        <div
                                            class="h-4 rounded bg-slate-200 dark:bg-slate-700"
                                            :class="width"
                                        ></div>
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr v-for="row in 6" :key="row">
                                    <td class="px-6 py-5">
                                        <div
                                            class="mx-auto size-8 rounded-lg bg-slate-200 dark:bg-slate-800"
                                        ></div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex gap-3">
                                            <div
                                                class="size-14 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"
                                            ></div>

                                            <div class="space-y-2">
                                                <div
                                                    class="h-4 w-44 rounded bg-slate-200 dark:bg-slate-800"
                                                ></div>

                                                <div
                                                    class="h-3 w-32 rounded bg-slate-200 dark:bg-slate-800"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td
                                        v-for="cell in 4"
                                        :key="cell"
                                        class="px-6 py-5"
                                    >
                                        <div
                                            class="h-4 w-24 rounded bg-slate-200 dark:bg-slate-800"
                                        ></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ACTUAL CONTENT -->

            <template v-else>
                <!-- HEADER -->

                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-600/10 text-blue-600 shadow-sm dark:bg-blue-400/10 dark:text-blue-400"
                        >
                            <Building2 class="size-5" />
                        </div>

                        <div class="min-w-0">
                            <h1
                                class="truncate text-xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-2xl"
                            >
                                Fasilitas
                            </h1>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Kelola informasi fasilitas kawasan perusahaan.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 active:scale-[0.98] sm:w-auto"
                        @click="openCreate"
                    >
                        <Plus class="size-4" />
                        Tambah Fasilitas
                    </button>
                </div>

                <!-- SEARCH -->

                <div
                    class="mb-5 rounded-3xl border border-slate-200/80 bg-white/95 p-4 shadow-sm shadow-slate-200/40 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20 sm:p-5"
                >
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari nama atau deskripsi fasilitas (ID / EN / 中文)..."
                            class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-10 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                            @input="submitSearch"
                        />

                        <button
                            v-if="search"
                            type="button"
                            title="Hapus pencarian"
                            aria-label="Hapus pencarian"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:hover:bg-slate-700"
                            @click="clearSearch"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- TABLE CARD -->

                <div
                    class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/40 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20"
                >
                    <div
                        class="flex flex-col gap-4 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
                            >
                                <FileText class="size-5" />
                            </div>

                            <div class="min-w-0">
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Data Fasilitas
                                </h2>

                                <p
                                    class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Informasi fasilitas yang tersedia di
                                    kawasan.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <!-- LANGUAGE VIEW (useLocale) -->

                            <div
                                class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-100 p-1 dark:border-slate-700 dark:bg-slate-800"
                                role="group"
                                aria-label="Bahasa tampilan tabel"
                            >
                                <Globe
                                    class="ml-1.5 size-3.5 text-slate-400"
                                    aria-hidden="true"
                                />

                                <button
                                    v-for="tab in languageTabs"
                                    :key="tab.code"
                                    type="button"
                                    :aria-pressed="currentLanguage === tab.code"
                                    class="rounded-lg px-2.5 py-1 text-xs font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="
                                        currentLanguage === tab.code
                                            ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400'
                                            : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                                    "
                                    @click="currentLanguage = tab.code"
                                >
                                    {{ tab.code.toUpperCase() }}
                                </button>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                            >
                                {{ getTotal() }} data
                            </span>
                        </div>
                    </div>

                    <!-- DATA -->

                    <div v-if="(props.fasilitas?.data?.length ?? 0) > 0">
                        <div class="overflow-x-auto overscroll-x-contain">
                            <table
                                class="w-full min-w-[1200px] text-left text-sm"
                            >
                                <thead
                                    class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                                >
                                    <tr>
                                        <th
                                            class="w-16 whitespace-nowrap px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            No.
                                        </th>

                                        <th
                                            class="whitespace-nowrap px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Fasilitas
                                        </th>

                                        <th
                                            class="w-[420px] px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Deskripsi
                                        </th>

                                        <th
                                            class="w-40 whitespace-nowrap px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Urutan
                                        </th>

                                        <th
                                            class="w-32 whitespace-nowrap px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Status
                                        </th>

                                        <th
                                            class="w-48 whitespace-nowrap px-6 py-4 text-right font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800"
                                >
                                    <tr
                                        v-for="(item, index) in props.fasilitas
                                            ?.data ?? []"
                                        :key="item.id"
                                        class="transition-colors duration-200 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                                    >
                                        <!-- NO -->

                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex size-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                            >
                                                {{ getRowNumber(index) }}
                                            </span>
                                        </td>

                                        <!-- FASILITAS -->

                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800"
                                                >
                                                    <img
                                                        v-if="
                                                            getImageUrl(
                                                                item.gambar,
                                                            )
                                                        "
                                                        :src="
                                                            getImageUrl(
                                                                item.gambar,
                                                            )!
                                                        "
                                                        :alt="displayName(item)"
                                                        loading="lazy"
                                                        class="size-full object-cover"
                                                    />

                                                    <ImageIcon
                                                        v-else
                                                        class="size-6 text-slate-400"
                                                    />
                                                </div>

                                                <div class="min-w-0">
                                                    <p
                                                        class="max-w-[260px] truncate font-semibold text-slate-800 dark:text-slate-100"
                                                    >
                                                        {{ displayName(item) }}
                                                    </p>

                                                    <p
                                                        class="mt-0.5 max-w-[260px] truncate text-xs text-blue-600 dark:text-blue-400"
                                                    >
                                                        /{{ item.slug }}
                                                    </p>

                                                    <div
                                                        class="mt-1 flex items-center gap-1.5"
                                                    >
                                                        <span
                                                            class="text-xs text-slate-400 dark:text-slate-500"
                                                        >
                                                            ID #{{ item.id }}
                                                        </span>

                                                        <span
                                                            v-for="tab in languageTabs"
                                                            :key="tab.code"
                                                            :title="
                                                                hasTranslation(
                                                                    item,
                                                                    tab.code,
                                                                )
                                                                    ? `${tab.label}: terisi`
                                                                    : `${tab.label}: belum diisi`
                                                            "
                                                            class="rounded px-1.5 py-0.5 text-[10px] font-semibold"
                                                            :class="
                                                                hasTranslation(
                                                                    item,
                                                                    tab.code,
                                                                )
                                                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                                    : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500'
                                                            "
                                                        >
                                                            {{
                                                                tab.code.toUpperCase()
                                                            }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- DESKRIPSI -->

                                        <td class="px-6 py-4">
                                            <p
                                                class="line-clamp-3 max-w-[420px] text-sm leading-6 text-slate-600 dark:text-slate-300"
                                            >
                                                {{
                                                    truncate(
                                                        displayDescription(
                                                            item,
                                                        ),
                                                        150,
                                                    )
                                                }}
                                            </p>
                                        </td>

                                        <!-- URUTAN -->

                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center justify-center gap-1"
                                            >
                                                <button
                                                    type="button"
                                                    title="Pindah ke atas"
                                                    aria-label="Pindah ke atas"
                                                    :disabled="
                                                        processingMoveId !==
                                                            null ||
                                                        isFirstItem(index)
                                                    "
                                                    class="rounded-lg p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-30 dark:text-slate-400 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                    @click="
                                                        moveFasilitas(
                                                            item,
                                                            'up',
                                                        )
                                                    "
                                                >
                                                    <ArrowUp class="size-4" />
                                                </button>

                                                <span
                                                    class="inline-flex min-w-9 items-center justify-center rounded-lg bg-slate-100 px-2 py-1.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                                >
                                                    {{ item.urutan }}
                                                </span>

                                                <button
                                                    type="button"
                                                    title="Pindah ke bawah"
                                                    aria-label="Pindah ke bawah"
                                                    :disabled="
                                                        processingMoveId !==
                                                            null ||
                                                        isLastItem(index)
                                                    "
                                                    class="rounded-lg p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-30 dark:text-slate-400 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                    @click="
                                                        moveFasilitas(
                                                            item,
                                                            'down',
                                                        )
                                                    "
                                                >
                                                    <ArrowDown class="size-4" />
                                                </button>
                                            </div>
                                        </td>

                                        <!-- STATUS -->

                                        <td class="px-6 py-4 text-center">
                                            <button
                                                type="button"
                                                :disabled="
                                                    processingToggleId ===
                                                    item.id
                                                "
                                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50"
                                                :class="
                                                    item.aktif
                                                        ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-950/60'
                                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700'
                                                "
                                                @click="toggleAktif(item)"
                                            >
                                                <span
                                                    v-if="
                                                        processingToggleId ===
                                                        item.id
                                                    "
                                                    class="size-3.5 animate-spin rounded-full border-2 border-current/20 border-t-current"
                                                ></span>

                                                <ToggleRight
                                                    v-else-if="item.aktif"
                                                    class="size-4"
                                                />

                                                <ToggleLeft
                                                    v-else
                                                    class="size-4"
                                                />

                                                {{
                                                    item.aktif
                                                        ? "Aktif"
                                                        : "Nonaktif"
                                                }}
                                            </button>
                                        </td>

                                        <!-- AKSI -->

                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-1">
                                                <button
                                                    type="button"
                                                    title="Lihat detail"
                                                    aria-label="Lihat detail"
                                                    class="rounded-lg p-2 text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                    @click="openDetail(item)"
                                                >
                                                    <Eye class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Edit fasilitas"
                                                    aria-label="Edit fasilitas"
                                                    class="rounded-lg p-2 text-slate-500 transition hover:bg-amber-50 hover:text-amber-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/30 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                                    @click="openEdit(item)"
                                                >
                                                    <Pencil class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Hapus fasilitas"
                                                    aria-label="Hapus fasilitas"
                                                    class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                                    @click="openDelete(item)"
                                                >
                                                    <Trash2 class="size-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- PAGINATION -->

                        <div
                            v-if="getLastPage() > 1"
                            class="flex flex-col gap-4 border-t border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                        >
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <p
                                    class="text-center text-sm text-slate-500 sm:text-left dark:text-slate-400"
                                >
                                    Menampilkan
                                    <span
                                        class="font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{ getFrom() }}
                                    </span>
                                    -
                                    <span
                                        class="font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{ getTo() }}
                                    </span>
                                    dari
                                    <span
                                        class="font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{ getTotal() }}
                                    </span>
                                    fasilitas
                                </p>

                                <div
                                    class="flex flex-wrap items-center justify-center gap-1 sm:justify-end"
                                >
                                    <button
                                        type="button"
                                        title="Halaman pertama"
                                        aria-label="Halaman pertama"
                                        :disabled="!firstPageUrl()"
                                        class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                        :class="navButtonClass(firstPageUrl())"
                                        @click="goToPage(firstPageUrl())"
                                    >
                                        <ChevronsLeft class="size-4" />
                                    </button>

                                    <button
                                        type="button"
                                        title="Halaman sebelumnya"
                                        aria-label="Halaman sebelumnya"
                                        :disabled="!previousPageUrl()"
                                        class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                        :class="
                                            navButtonClass(previousPageUrl())
                                        "
                                        @click="goToPage(previousPageUrl())"
                                    >
                                        <ChevronLeft class="size-4" />
                                    </button>

                                    <button
                                        v-for="(
                                            link, index
                                        ) in getPaginationLinks().slice(1, -1)"
                                        :key="`${link.label}-${index}`"
                                        type="button"
                                        :disabled="!link.url || link.active"
                                        class="min-w-9 rounded-lg px-3 py-2 text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                        :class="
                                            link.active
                                                ? 'bg-blue-600 text-white shadow-sm'
                                                : navButtonClass(link.url)
                                        "
                                        @click="goToPage(link.url)"
                                    >
                                        {{
                                            paginationPageLabel(link.label) ||
                                            "…"
                                        }}
                                    </button>

                                    <button
                                        type="button"
                                        title="Halaman berikutnya"
                                        aria-label="Halaman berikutnya"
                                        :disabled="!nextPageUrl()"
                                        class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                        :class="navButtonClass(nextPageUrl())"
                                        @click="goToPage(nextPageUrl())"
                                    >
                                        <ChevronRight class="size-4" />
                                    </button>

                                    <button
                                        type="button"
                                        title="Halaman terakhir"
                                        aria-label="Halaman terakhir"
                                        :disabled="!lastPageUrl()"
                                        class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                        :class="navButtonClass(lastPageUrl())"
                                        @click="goToPage(lastPageUrl())"
                                    >
                                        <ChevronsRight class="size-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- EMPTY -->

                    <div v-else class="px-4 py-16 text-center sm:px-6">
                        <div
                            class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-500 dark:bg-blue-950/30 dark:text-blue-400"
                        >
                            <Building2 class="size-7" />
                        </div>

                        <p
                            class="mt-4 font-semibold text-slate-800 dark:text-slate-200"
                        >
                            {{
                                search
                                    ? "Fasilitas tidak ditemukan"
                                    : "Belum ada fasilitas"
                            }}
                        </p>

                        <p
                            class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{
                                search
                                    ? "Tidak ditemukan data yang sesuai dengan pencarian."
                                    : "Tambahkan data fasilitas untuk mulai mengelola informasi kawasan."
                            }}
                        </p>

                        <button
                            v-if="!search"
                            type="button"
                            class="mt-5 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />
                            Tambah Fasilitas
                        </button>

                        <button
                            v-else
                            type="button"
                            class="mt-5 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="clearSearch"
                        >
                            <X class="size-4" />
                            Bersihkan Pencarian
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- =========================================================
             CREATE / EDIT MODAL
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showFormModal"
                class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-950/50 p-3 backdrop-blur-sm sm:p-4"
                @click.self="closeForm"
            >
                <div
                    class="my-auto w-full max-w-3xl overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                class="text-base font-semibold text-slate-900 sm:text-lg dark:text-white"
                            >
                                {{
                                    modalMode === "create"
                                        ? "Tambah Fasilitas"
                                        : "Edit Fasilitas"
                                }}
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    modalMode === "create"
                                        ? "Tambahkan informasi fasilitas baru."
                                        : "Perbarui informasi fasilitas."
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            :disabled="processing"
                            aria-label="Tutup modal"
                            class="shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800"
                            @click="closeForm"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <form
                        class="max-h-[calc(100dvh-8rem)] overflow-y-auto"
                        novalidate
                        @submit.prevent="submit"
                    >
                        <div class="grid gap-5 p-4 sm:p-6 md:grid-cols-2">
                            <!-- LANGUAGE TABS -->

                            <div class="md:col-span-2">
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50/70 p-1.5 dark:border-slate-700 dark:bg-slate-800/50"
                                >
                                    <div
                                        class="grid grid-cols-3 gap-1"
                                        role="tablist"
                                    >
                                        <button
                                            v-for="tab in languageTabs"
                                            :key="tab.code"
                                            type="button"
                                            role="tab"
                                            :aria-selected="
                                                activeLanguage === tab.code
                                            "
                                            class="relative rounded-xl px-3 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                            :class="
                                                activeLanguage === tab.code
                                                    ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-700 dark:text-blue-400'
                                                    : 'text-slate-500 hover:bg-white/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700/50 dark:hover:text-slate-200'
                                            "
                                            @click="activeLanguage = tab.code"
                                        >
                                            {{ tab.flag }} {{ tab.label }}

                                            <span
                                                v-if="
                                                    hasLanguageError(tab.code)
                                                "
                                                class="absolute right-2 top-2 size-2 rounded-full bg-red-500"
                                                aria-label="Ada kesalahan pada bahasa ini"
                                            ></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- LANGUAGE NOTICE -->

                            <div class="md:col-span-2">
                                <div
                                    class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50/70 p-3.5 dark:border-blue-900/40 dark:bg-blue-950/20"
                                >
                                    <div
                                        class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400"
                                    >
                                        <Check class="size-4" />
                                    </div>

                                    <div>
                                        <p
                                            class="text-sm font-semibold text-blue-800 dark:text-blue-300"
                                        >
                                            {{ languageLabel }}
                                        </p>

                                        <p
                                            class="mt-0.5 text-xs leading-5 text-blue-600/80 dark:text-blue-400/80"
                                        >
                                            Isi nama dan deskripsi untuk bahasa
                                            yang sedang dipilih. Jika bahasa
                                            lain dikosongkan, tampilan akan
                                            memakai Bahasa Indonesia.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ================= INDONESIA ================= -->

                            <template v-if="activeLanguage === 'id'">
                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Nama Fasilitas
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        v-model="form.nama"
                                        type="text"
                                        maxlength="150"
                                        placeholder="Contoh: Jalan Utama Kawasan"
                                        class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        :class="{
                                            'border-red-400 focus:border-red-500':
                                                errors.nama,
                                        }"
                                    />

                                    <p
                                        v-if="errors.nama"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ errors.nama }}
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Deskripsi
                                    </label>

                                    <textarea
                                        v-model="form.deskripsi"
                                        rows="6"
                                        maxlength="10000"
                                        placeholder="Tuliskan deskripsi lengkap mengenai fasilitas..."
                                        class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        :class="{
                                            'border-red-400 focus:border-red-500':
                                                errors.deskripsi,
                                        }"
                                    ></textarea>

                                    <p
                                        v-if="errors.deskripsi"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ errors.deskripsi }}
                                    </p>
                                </div>
                            </template>

                            <!-- ================= ENGLISH ================= -->

                            <template v-else-if="activeLanguage === 'en'">
                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Facility Name
                                    </label>

                                    <input
                                        v-model="form.nama_en"
                                        type="text"
                                        maxlength="150"
                                        placeholder="Example: Main Estate Road"
                                        class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        :class="{
                                            'border-red-400 focus:border-red-500':
                                                errors.nama_en,
                                        }"
                                    />

                                    <p
                                        v-if="errors.nama_en"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ errors.nama_en }}
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Description
                                    </label>

                                    <textarea
                                        v-model="form.deskripsi_en"
                                        rows="6"
                                        maxlength="10000"
                                        placeholder="Write a complete description of the facility..."
                                        class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        :class="{
                                            'border-red-400 focus:border-red-500':
                                                errors.deskripsi_en,
                                        }"
                                    ></textarea>

                                    <p
                                        v-if="errors.deskripsi_en"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ errors.deskripsi_en }}
                                    </p>
                                </div>
                            </template>

                            <!-- ================= CHINESE ================= -->

                            <template v-else>
                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        设施名称
                                    </label>

                                    <input
                                        v-model="form.nama_zh"
                                        type="text"
                                        maxlength="150"
                                        placeholder="例如：园区主干道"
                                        class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        :class="{
                                            'border-red-400 focus:border-red-500':
                                                errors.nama_zh,
                                        }"
                                    />

                                    <p
                                        v-if="errors.nama_zh"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ errors.nama_zh }}
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        描述
                                    </label>

                                    <textarea
                                        v-model="form.deskripsi_zh"
                                        rows="6"
                                        maxlength="10000"
                                        placeholder="请填写设施的完整描述..."
                                        class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        :class="{
                                            'border-red-400 focus:border-red-500':
                                                errors.deskripsi_zh,
                                        }"
                                    ></textarea>

                                    <p
                                        v-if="errors.deskripsi_zh"
                                        class="mt-1.5 text-xs text-red-500"
                                    >
                                        {{ errors.deskripsi_zh }}
                                    </p>
                                </div>
                            </template>

                            <!-- SLUG -->

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Slug
                                </label>

                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"
                                    >
                                        /
                                    </span>

                                    <input
                                        v-model="form.slug"
                                        type="text"
                                        readonly
                                        disabled
                                        tabindex="-1"
                                        placeholder="slug-otomatis"
                                        class="min-h-11 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 py-2.5 pl-8 pr-4 text-sm text-slate-500 outline-none dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-400"
                                        :class="{
                                            'border-red-400': errors.slug,
                                        }"
                                    />
                                </div>

                                <p
                                    class="mt-1.5 flex items-start gap-1.5 text-xs leading-5 text-slate-400 dark:text-slate-500"
                                >
                                    <span
                                        class="mt-1.5 inline-flex size-1.5 shrink-0 rounded-full bg-blue-500"
                                    ></span>

                                    <span>
                                        Slug dibuat otomatis oleh sistem dari
                                        nama Bahasa Indonesia dan tidak dapat
                                        diubah secara manual. Slug final dapat
                                        berbeda jika sudah digunakan.
                                    </span>
                                </p>

                                <p
                                    v-if="errors.slug"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.slug }}
                                </p>
                            </div>

                            <!-- STATUS -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Status
                                </label>

                                <button
                                    type="button"
                                    class="flex min-h-11 w-full items-center justify-between gap-3 rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="
                                        form.aktif
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-400'
                                            : 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                    @click="form.aktif = !form.aktif"
                                >
                                    <span
                                        class="flex min-w-0 items-center gap-2"
                                    >
                                        <ToggleRight
                                            v-if="form.aktif"
                                            class="size-5 shrink-0"
                                        />

                                        <ToggleLeft
                                            v-else
                                            class="size-5 shrink-0"
                                        />

                                        <span class="truncate">
                                            {{
                                                form.aktif
                                                    ? "Fasilitas Aktif"
                                                    : "Fasilitas Nonaktif"
                                            }}
                                        </span>
                                    </span>

                                    <span
                                        class="hidden shrink-0 text-xs opacity-70 sm:inline"
                                    >
                                        Klik untuk ubah
                                    </span>
                                </button>
                            </div>

                            <!-- URUTAN -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Urutan
                                </label>

                                <input
                                    v-model="form.urutan"
                                    type="number"
                                    min="0"
                                    step="1"
                                    placeholder="0"
                                    class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                    :class="{
                                        'border-red-400 focus:border-red-500':
                                            errors.urutan,
                                    }"
                                />

                                <p
                                    class="mt-1.5 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Kosongkan atau gunakan 0 untuk mengikuti
                                    urutan otomatis.
                                </p>

                                <p
                                    v-if="errors.urutan"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.urutan }}
                                </p>
                            </div>

                            <!-- GAMBAR -->

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Gambar Fasilitas
                                </label>

                                <div
                                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-3 sm:p-4 dark:border-slate-700 dark:bg-slate-800/50"
                                >
                                    <div
                                        v-if="previewUrl || existingImage"
                                        class="mb-4"
                                    >
                                        <div
                                            class="relative overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900"
                                        >
                                            <img
                                                :src="
                                                    previewUrl ||
                                                    getImageUrl(existingImage)!
                                                "
                                                alt="Preview gambar"
                                                class="max-h-64 w-full object-cover"
                                            />

                                            <div
                                                class="absolute right-3 top-3 flex gap-2"
                                            >
                                                <button
                                                    v-if="previewUrl"
                                                    type="button"
                                                    title="Hapus gambar baru"
                                                    aria-label="Hapus gambar baru"
                                                    class="rounded-lg bg-red-600 p-2 text-white shadow-lg transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50"
                                                    @click="removeSelectedImage"
                                                >
                                                    <Trash2 class="size-4" />
                                                </button>

                                                <button
                                                    v-else
                                                    type="button"
                                                    title="Hapus gambar"
                                                    aria-label="Hapus gambar"
                                                    class="rounded-lg bg-red-600 p-2 text-white shadow-lg transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50"
                                                    @click="removeExistingImage"
                                                >
                                                    <Trash2 class="size-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <p
                                        v-if="
                                            form.remove_gambar &&
                                            !previewUrl &&
                                            !existingImage
                                        "
                                        class="mb-3 text-xs text-red-500"
                                    >
                                        Gambar lama akan dihapus saat disimpan.
                                    </p>

                                    <label
                                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-7 text-center transition hover:border-blue-400 hover:bg-blue-50/50 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-blue-600 dark:hover:bg-blue-950/20 sm:px-5 sm:py-8"
                                    >
                                        <ImageIcon
                                            class="size-8 text-slate-400"
                                        />

                                        <span
                                            class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Pilih gambar
                                        </span>

                                        <span
                                            class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                        >
                                            JPG, JPEG, PNG, WEBP — maksimal 2 MB
                                        </span>

                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="handleImageChange"
                                        />
                                    </label>
                                </div>

                                <p
                                    v-if="errors.gambar"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.gambar }}
                                </p>
                            </div>
                        </div>

                        <!-- FOOTER -->

                        <div
                            class="flex flex-col-reverse gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                :disabled="processing"
                                class="min-h-11 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeForm"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="processing"
                                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                            >
                                <span
                                    v-if="processing"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                {{
                                    processing
                                        ? "Menyimpan..."
                                        : modalMode === "create"
                                          ? "Simpan Fasilitas"
                                          : "Simpan Perubahan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- =========================================================
             DETAIL MODAL
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showDetailModal && selectedFasilitas"
                class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-950/50 p-3 backdrop-blur-sm sm:p-4"
                @click.self="closeDetail"
            >
                <div
                    class="my-auto w-full max-w-2xl overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                class="text-base font-semibold text-slate-900 sm:text-lg dark:text-white"
                            >
                                Detail Fasilitas
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap fasilitas kawasan.
                            </p>
                        </div>

                        <button
                            type="button"
                            aria-label="Tutup detail"
                            class="shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:hover:bg-slate-800"
                            @click="closeDetail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <div
                        class="max-h-[calc(100dvh-10rem)] overflow-y-auto p-4 sm:p-6"
                    >
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800"
                        >
                            <img
                                v-if="getImageUrl(selectedFasilitas.gambar)"
                                :src="getImageUrl(selectedFasilitas.gambar)!"
                                :alt="displayName(selectedFasilitas)"
                                class="max-h-80 w-full object-cover"
                            />

                            <div
                                v-else
                                class="flex h-56 items-center justify-center"
                            >
                                <div class="text-center">
                                    <ImageIcon
                                        class="mx-auto size-10 text-slate-400"
                                    />

                                    <p class="mt-2 text-sm text-slate-400">
                                        Tidak ada gambar
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- LANGUAGE TABS -->

                        <div
                            class="mt-5 inline-flex rounded-xl border border-slate-200 bg-slate-100 p-1 dark:border-slate-700 dark:bg-slate-800"
                            role="tablist"
                        >
                            <button
                                v-for="tab in languageTabs"
                                :key="tab.code"
                                type="button"
                                role="tab"
                                :aria-selected="detailLanguage === tab.code"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                :class="
                                    detailLanguage === tab.code
                                        ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400'
                                        : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                                "
                                @click="detailLanguage = tab.code"
                            >
                                {{ tab.flag }} {{ tab.label }}
                            </button>
                        </div>

                        <div class="mt-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3
                                    class="text-xl font-semibold text-slate-900 dark:text-white"
                                >
                                    {{
                                        getDetailValue(
                                            selectedFasilitas,
                                            "nama",
                                        ) || "-"
                                    }}
                                </h3>

                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                                    :class="
                                        selectedFasilitas.aktif
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                >
                                    {{
                                        selectedFasilitas.aktif
                                            ? "Aktif"
                                            : "Nonaktif"
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div
                                class="rounded-xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/50"
                            >
                                <div class="flex items-center gap-2">
                                    <FileText class="size-4 text-blue-500" />

                                    <span
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Slug
                                    </span>
                                </div>

                                <p
                                    class="mt-1 break-all font-mono text-sm font-medium text-blue-600 dark:text-blue-400"
                                >
                                    /{{ selectedFasilitas.slug }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/50"
                            >
                                <div class="flex items-center gap-2">
                                    <ArrowUp class="size-4 text-blue-500" />

                                    <span
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Urutan
                                    </span>
                                </div>

                                <p
                                    class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                >
                                    {{ selectedFasilitas.urutan }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5">
                            <div class="mb-2 flex items-center gap-2">
                                <FileText class="size-4 text-blue-500" />

                                <h4
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Deskripsi
                                </h4>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                            >
                                <p class="whitespace-pre-line">
                                    {{
                                        getDetailValue(
                                            selectedFasilitas,
                                            "deskripsi",
                                        ) || "-"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            class="min-h-11 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 sm:w-auto dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closeDetail"
                        >
                            Tutup
                        </button>

                        <button
                            type="button"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 sm:w-auto"
                            @click="openEdit(selectedFasilitas)"
                        >
                            <Pencil class="size-4" />
                            Edit
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- =========================================================
             DELETE MODAL
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showDeleteModal && selectedFasilitas"
                class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-950/50 p-3 backdrop-blur-sm sm:p-4"
                @click.self="closeDelete"
            >
                <div
                    class="w-full max-w-md rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-900 sm:p-6"
                >
                    <div
                        class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                    >
                        <Trash2 class="size-5" />
                    </div>

                    <div class="mt-4 text-center">
                        <h2
                            class="text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            Hapus Fasilitas?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedFasilitas.nama }}
                            </span>
                            ? Seluruh terjemahan (ID, EN, 中文) akan ikut
                            terhapus dan tidak dapat dikembalikan.
                        </p>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="min-h-11 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closeDelete"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                            @click="deleteFasilitas"
                        >
                            <span
                                v-if="processingDelete"
                                class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            ></span>

                            {{
                                processingDelete ? "Menghapus..." : "Ya, Hapus"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- =========================================================
             PAGE LOADING
        ========================================================== -->

        <Transition name="loading">
            <div
                v-if="isPageLoading"
                class="pointer-events-none fixed inset-0 z-[100] bg-white/30 backdrop-blur-[1px] dark:bg-slate-950/30"
            >
                <div
                    class="absolute left-0 top-0 h-0.5 w-full overflow-hidden bg-blue-100 dark:bg-blue-950"
                >
                    <div
                        class="animate-loading-bar h-full w-1/3 rounded-full bg-blue-600"
                    ></div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.blob-shape {
    animation: blob-float 12s ease-in-out infinite;
}

.blob-shape-delayed {
    animation: blob-float-delayed 15s ease-in-out infinite;
}

.blob-shape-slow {
    animation: blob-float-slow 18s ease-in-out infinite;
}

@keyframes blob-float {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(20px, 15px, 0) scale(1.05);
    }
}

@keyframes blob-float-delayed {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(-18px, 20px, 0) scale(1.08);
    }
}

@keyframes blob-float-slow {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(15px, -18px, 0) scale(1.06);
    }
}

.animate-loading-bar {
    animation: loading-bar 1.2s ease-in-out infinite;
}

@keyframes loading-bar {
    0% {
        transform: translateX(-120%);
    }

    100% {
        transform: translateX(420%);
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
    transform: translateY(10px) scale(0.98);
}

.loading-enter-active,
.loading-leave-active {
    transition: opacity 0.2s ease;
}

.loading-enter-from,
.loading-leave-to {
    opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
    .blob-shape,
    .blob-shape-delayed,
    .blob-shape-slow,
    .animate-loading-bar {
        animation: none;
    }

    .modal-enter-active,
    .modal-leave-active,
    .loading-enter-active,
    .loading-leave-active {
        transition: none;
    }
}
</style>
