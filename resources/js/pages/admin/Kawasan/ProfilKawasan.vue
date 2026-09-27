<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import {
    Building2,
    CalendarDays,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    Eye,
    FileText,
    Image as ImageIcon,
    MapPin,
    Pencil,
    Plus,
    Ruler,
    Search,
    ToggleLeft,
    ToggleRight,
    Trash2,
    X,
} from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

/**
 * |--------------------------------------------------------------------------
 * | Interfaces
 * |--------------------------------------------------------------------------
 */

interface Kawasan {
    id: number;
    judul: string;
    slug: string;
    deskripsi: string | null;
    luas_kawasan: string | number | null;
    lokasi: string | null;
    tahun_berdiri: number | null;
    status: boolean;
    gambar: string | null;
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

interface KawasanPagination {
    data: Kawasan[];
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
    judul?: string;
    slug?: string;
    deskripsi?: string;
    luas_kawasan?: string;
    lokasi?: string;
    tahun_berdiri?: string;
    status?: string;
    gambar?: string;
    remove_gambar?: string;
}

/**
 * |--------------------------------------------------------------------------
 * | Props
 * |--------------------------------------------------------------------------
 */

const props = defineProps<{
    profilKawasans: KawasanPagination | null;
    filters?: {
        search?: string;
    };
}>();

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

const selectedKawasan = ref<Kawasan | null>(null);

/**
 * |--------------------------------------------------------------------------
 * | Form State
 * |--------------------------------------------------------------------------
 */

const form = ref({
    judul: "",
    slug: "",
    deskripsi: "",
    luas_kawasan: "",
    lokasi: "",
    tahun_berdiri: "",
    status: true,
    gambar: null as File | null,
    remove_gambar: false,
});

const existingImage = ref<string | null>(null);
const previewUrl = ref<string | null>(null);

const processing = ref(false);
const processingDelete = ref(false);
const processingToggleId = ref<number | null>(null);

const errors = ref<FormErrors>({});

/**
 * |--------------------------------------------------------------------------
 * | Slug
 * |--------------------------------------------------------------------------
 *
 * Slug sepenuhnya otomatis berdasarkan judul.
 *
 * User tidak dapat mengubah slug secara manual.
 * Backend tetap menjadi sumber kebenaran untuk
 * generate unique slug.
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
    () => form.value.judul,
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
            "/kawasan/profil-kawasan",
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
        "/kawasan/profil-kawasan",
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

const formatArea = (value: string | number | null): string => {
    if (value === null || value === "") {
        return "-";
    }

    return `${value} Ha`;
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

const getTotal = (): number => {
    return (
        props.profilKawasans?.meta?.total ??
        props.profilKawasans?.total ??
        props.profilKawasans?.data?.length ??
        0
    );
};

const getCurrentPage = (): number => {
    return (
        props.profilKawasans?.meta?.current_page ??
        props.profilKawasans?.current_page ??
        1
    );
};

const getPerPage = (): number => {
    return (
        props.profilKawasans?.meta?.per_page ??
        props.profilKawasans?.per_page ??
        props.profilKawasans?.data?.length ??
        10
    );
};

const getLastPage = (): number => {
    return (
        props.profilKawasans?.meta?.last_page ??
        props.profilKawasans?.last_page ??
        1
    );
};

const getFrom = (): number => {
    return props.profilKawasans?.meta?.from ?? props.profilKawasans?.from ?? 0;
};

const getTo = (): number => {
    return props.profilKawasans?.meta?.to ?? props.profilKawasans?.to ?? 0;
};

const getRowNumber = (index: number): number => {
    return (getCurrentPage() - 1) * getPerPage() + index + 1;
};

/**
 * |--------------------------------------------------------------------------
 * | Pagination
 * |--------------------------------------------------------------------------
 */

const getPaginationLinks = (): PaginationLink[] => {
    return props.profilKawasans?.links ?? [];
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
    const links = getPaginationLinks();

    return links.length > 2 ? (links[1]?.url ?? null) : null;
};

const lastPageUrl = (): string | null => {
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

/**
 * |--------------------------------------------------------------------------
 * | Form Reset
 * |--------------------------------------------------------------------------
 */

const resetForm = (): void => {
    isHydratingForm.value = true;

    form.value = {
        judul: "",
        slug: "",
        deskripsi: "",
        luas_kawasan: "",
        lokasi: "",
        tahun_berdiri: "",
        status: true,
        gambar: null,
        remove_gambar: false,
    };

    existingImage.value = null;
    errors.value = {};

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
    selectedKawasan.value = null;

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

const openEdit = (kawasan: Kawasan): void => {
    if (processing.value) {
        return;
    }

    modalMode.value = "edit";
    selectedKawasan.value = kawasan;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    errors.value = {};

    isHydratingForm.value = true;

    form.value = {
        judul: toStringValue(kawasan.judul),

        /*
         * Slug hanya ditampilkan sebagai informasi.
         * Nilainya tidak dapat diedit.
         */
        slug: toStringValue(kawasan.slug),

        deskripsi: toStringValue(kawasan.deskripsi),
        luas_kawasan: toStringValue(kawasan.luas_kawasan),
        lokasi: toStringValue(kawasan.lokasi),
        tahun_berdiri: toStringValue(kawasan.tahun_berdiri),
        status: Boolean(kawasan.status),
        gambar: null,
        remove_gambar: false,
    };

    existingImage.value = kawasan.gambar;

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
    selectedKawasan.value = null;

    resetForm();
};

const forceCloseForm = (): void => {
    showFormModal.value = false;
    selectedKawasan.value = null;

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

    if (!file.type.startsWith("image/")) {
        errors.value.gambar = "File harus berupa gambar.";

        target.value = "";
        form.value.gambar = null;

        return;
    }

    if (file.size > 1024 * 1024) {
        errors.value.gambar = "Ukuran gambar maksimal 1 MB.";

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
 * Slug tidak pernah diambil dari input user.
 *
 * Frontend hanya membuat preview slug dari judul.
 * Backend tetap bertanggung jawab menghasilkan
 * unique slug final.
 * |--------------------------------------------------------------------------
 */

const submit = (): void => {
    if (processing.value) {
        return;
    }

    errors.value = {};

    /**
     * Normalisasi seluruh field.
     */
    const judul = toStringValue(form.value.judul);

    /*
     * Slug selalu dibuat dari judul.
     *
     * Jangan menggunakan form.slug sebagai sumber data.
     */
    const slug = slugify(judul);

    const deskripsi = toStringValue(form.value.deskripsi);

    const luasKawasan = toStringValue(form.value.luas_kawasan);

    const lokasi = toStringValue(form.value.lokasi);

    const tahunBerdiri = toStringValue(form.value.tahun_berdiri);

    /**
     * Validasi frontend.
     */
    if (!judul) {
        errors.value.judul = "Judul kawasan wajib diisi.";

        return;
    }

    /**
     * Pastikan slug berhasil dibuat.
     */
    if (!slug) {
        errors.value.slug = "Slug gagal dibuat dari judul.";

        return;
    }

    /*
     * Sinkronkan preview slug sebelum request.
     */
    form.value.slug = slug;

    /**
     * Tentukan mode.
     */
    const isEdit = modalMode.value === "edit" && selectedKawasan.value !== null;

    /**
     * Pastikan selectedKawasan tersedia
     * ketika mode edit.
     */
    if (isEdit && !selectedKawasan.value) {
        console.error("Data kawasan untuk mode edit tidak tersedia.");

        return;
    }

    /**
     * Build FormData.
     */
    const formData = new FormData();

    formData.append("judul", judul);

    /*
     * Slug dikirim sebagai hasil generate dari judul.
     *
     * Backend tetap boleh mengabaikan nilai ini dan
     * generate ulang untuk memastikan uniqueness.
     */
    formData.append("slug", slug);

    formData.append("deskripsi", deskripsi);

    /**
     * Field number dikirim sebagai string.
     */
    if (luasKawasan !== "") {
        formData.append("luas_kawasan", luasKawasan);
    } else {
        formData.append("luas_kawasan", "");
    }

    formData.append("lokasi", lokasi);

    if (tahunBerdiri !== "") {
        formData.append("tahun_berdiri", tahunBerdiri);
    } else {
        formData.append("tahun_berdiri", "");
    }

    formData.append("status", form.value.status ? "1" : "0");

    /**
     * File baru hanya dikirim jika benar-benar File.
     */
    if (form.value.gambar instanceof File) {
        formData.append("gambar", form.value.gambar);
    }

    /**
     * Update-specific fields.
     */
    if (isEdit) {
        formData.append("remove_gambar", form.value.remove_gambar ? "1" : "0");

        /**
         * Laravel method spoofing.
         */
        formData.append("_method", "PUT");
    }

    /**
     * URL endpoint.
     */
    const url = isEdit
        ? `/kawasan/profil-kawasan/${selectedKawasan.value!.id}`
        : "/kawasan/profil-kawasan";

    /**
     * Lock button sebelum request.
     */
    processing.value = true;

    router.post(url, formData, {
        forceFormData: true,

        preserveScroll: true,

        preserveState: true,

        onStart: () => {
            processing.value = true;
        },

        onSuccess: () => {
            processing.value = false;

            forceCloseForm();

            showDetailModal.value = false;

            showDeleteModal.value = false;

            selectedKawasan.value = null;
        },

        onError: (serverErrors) => {
            processing.value = false;

            errors.value = serverErrors as FormErrors;

            console.error("Gagal menyimpan profil kawasan:", serverErrors);
        },

        onCancel: () => {
            processing.value = false;

            console.warn("Request penyimpanan profil kawasan dibatalkan.");
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

const openDetail = (kawasan: Kawasan): void => {
    selectedKawasan.value = kawasan;

    showFormModal.value = false;
    showDeleteModal.value = false;
    showDetailModal.value = true;
};

const closeDetail = (): void => {
    showDetailModal.value = false;
    selectedKawasan.value = null;
};

/**
 * |--------------------------------------------------------------------------
 * | Delete
 * |--------------------------------------------------------------------------
 */

const openDelete = (kawasan: Kawasan): void => {
    if (processingDelete.value || processing.value) {
        return;
    }

    selectedKawasan.value = kawasan;

    showFormModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = true;
};

const closeDelete = (): void => {
    if (processingDelete.value) {
        return;
    }

    showDeleteModal.value = false;
    selectedKawasan.value = null;
};

const deleteKawasan = (): void => {
    if (!selectedKawasan.value || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(`/kawasan/profil-kawasan/${selectedKawasan.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDeleteModal.value = false;

            selectedKawasan.value = null;
        },

        onError: (serverErrors) => {
            console.error("Gagal menghapus profil kawasan:", serverErrors);
        },

        onCancel: () => {
            processingDelete.value = false;
        },

        onFinish: () => {
            processingDelete.value = false;
        },
    });
};

/**
 * |--------------------------------------------------------------------------
 * | Toggle Status
 * |--------------------------------------------------------------------------
 */

const toggleStatus = (kawasan: Kawasan): void => {
    if (processingToggleId.value !== null) {
        return;
    }

    processingToggleId.value = kawasan.id;

    router.patch(
        `/kawasan/profil-kawasan/${kawasan.id}/toggle-aktif`,
        {},
        {
            preserveScroll: true,

            onError: (serverErrors) => {
                console.error("Gagal mengubah status kawasan:", serverErrors);
            },

            onCancel: () => {
                processingToggleId.value = null;
            },

            onFinish: () => {
                processingToggleId.value = null;
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
        class="relative min-h-full overflow-hidden bg-slate-50/50 p-4 sm:p-6 dark:bg-slate-950/50"
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
                class="blob-shape-slow absolute left-[30%] -top-40 h-72 w-72 rounded-full bg-gradient-to-br from-indigo-300/25 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/15 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <div
                class="blob-shape absolute -bottom-40 right-[20%] h-72 w-72 rounded-full bg-gradient-to-br from-cyan-300/20 via-blue-300/10 to-transparent blur-3xl dark:from-cyan-500/10 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <div class="absolute inset-0 opacity-40 dark:opacity-20">
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

        <div class="relative z-10">
            <!-- =====================================================
                 SKELETON
            ====================================================== -->

            <div v-if="isPageLoading" class="animate-pulse">
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="size-10 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"
                        ></div>

                        <div class="space-y-2">
                            <div
                                class="h-5 w-40 rounded-md bg-slate-200 dark:bg-slate-800"
                            ></div>

                            <div
                                class="h-4 w-64 max-w-full rounded-md bg-slate-200 dark:bg-slate-800"
                            ></div>
                        </div>
                    </div>

                    <div
                        class="h-10 w-full rounded-xl bg-slate-200 dark:bg-slate-800 sm:w-36"
                    ></div>
                </div>

                <div
                    class="mb-5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="h-10 w-full rounded-xl bg-slate-200 dark:bg-slate-800"
                    ></div>
                </div>

                <div
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1050px] text-left text-sm">
                            <thead
                                class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <tr>
                                    <th
                                        v-for="width in [
                                            'w-16',
                                            'w-72',
                                            'w-48',
                                            'w-44',
                                            'w-32',
                                            'w-36',
                                            'w-40',
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
                                        v-for="cell in 5"
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

                    <div
                        class="flex flex-col gap-4 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                    >
                        <div
                            class="h-4 w-48 rounded bg-slate-200 dark:bg-slate-800"
                        ></div>

                        <div class="flex gap-1">
                            <div
                                v-for="item in 5"
                                :key="item"
                                class="size-9 rounded-lg bg-slate-200 dark:bg-slate-800"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =====================================================
                 ACTUAL CONTENT
            ====================================================== -->

            <template v-else>
                <!-- HEADER -->

                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-600/10 text-blue-600 shadow-sm dark:bg-blue-400/10 dark:text-blue-400"
                        >
                            <Building2 class="size-5" />
                        </div>

                        <div>
                            <h1
                                class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                            >
                                Profil Kawasan
                            </h1>

                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Kelola informasi profil kawasan perusahaan.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                        @click="openCreate"
                    >
                        <Plus class="size-4" />
                        Tambah Profil
                    </button>
                </div>

                <!-- SEARCH -->

                <div
                    class="mb-5 rounded-2xl border border-slate-200/80 bg-white/95 p-4 shadow-sm backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95"
                >
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari judul, lokasi, atau deskripsi kawasan..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-10 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                            @input="submitSearch"
                        />

                        <button
                            v-if="search"
                            type="button"
                            title="Hapus pencarian"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600 dark:hover:bg-slate-700"
                            @click="clearSearch"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- TABLE CARD -->

                <div
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 shadow-sm backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95"
                >
                    <div
                        class="flex flex-col gap-3 border-b border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
                            >
                                <FileText class="size-5" />
                            </div>

                            <div>
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Data Profil Kawasan
                                </h2>

                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Informasi kawasan yang dikelola perusahaan.
                                </p>
                            </div>
                        </div>

                        <span
                            class="text-sm text-slate-500 dark:text-slate-400"
                        >
                            {{ getTotal() }} data
                        </span>
                    </div>

                    <!-- DATA -->

                    <div v-if="(props.profilKawasans?.data?.length ?? 0) > 0">
                        <div class="overflow-x-auto">
                            <table
                                class="w-full min-w-[1100px] text-left text-sm"
                            >
                                <thead
                                    class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                                >
                                    <tr>
                                        <th
                                            class="w-16 px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            No.
                                        </th>

                                        <th
                                            class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Profil Kawasan
                                        </th>

                                        <th
                                            class="w-48 px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Lokasi
                                        </th>

                                        <th
                                            class="w-40 px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Luas
                                        </th>

                                        <th
                                            class="w-32 px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Tahun
                                        </th>

                                        <th
                                            class="w-32 px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Status
                                        </th>

                                        <th
                                            class="w-40 px-6 py-4 text-right font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800"
                                >
                                    <tr
                                        v-for="(item, index) in props
                                            .profilKawasans?.data ?? []"
                                        :key="item.id"
                                        class="transition-colors hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                                    >
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex size-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                            >
                                                {{ getRowNumber(index) }}
                                            </span>
                                        </td>

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
                                                        :alt="item.judul"
                                                        class="size-full object-cover"
                                                    />

                                                    <ImageIcon
                                                        v-else
                                                        class="size-6 text-slate-400"
                                                    />
                                                </div>

                                                <div class="min-w-0">
                                                    <p
                                                        class="truncate font-semibold text-slate-800 dark:text-slate-100"
                                                    >
                                                        {{ item.judul }}
                                                    </p>

                                                    <p
                                                        class="mt-0.5 truncate text-xs text-blue-600 dark:text-blue-400"
                                                    >
                                                        /{{ item.slug }}
                                                    </p>

                                                    <p
                                                        class="mt-1 max-w-[360px] truncate text-xs text-slate-400 dark:text-slate-500"
                                                    >
                                                        {{
                                                            truncate(
                                                                item.deskripsi,
                                                                80,
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="flex items-start gap-2">
                                                <MapPin
                                                    class="mt-0.5 size-4 shrink-0 text-slate-400"
                                                />

                                                <span
                                                    class="line-clamp-2 text-slate-600 dark:text-slate-300"
                                                >
                                                    {{ item.lokasi || "-" }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Ruler
                                                    class="size-4 text-slate-400"
                                                />

                                                <span
                                                    class="font-medium text-slate-700 dark:text-slate-200"
                                                >
                                                    {{
                                                        formatArea(
                                                            item.luas_kawasan,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <CalendarDays
                                                    class="size-4 text-slate-400"
                                                />

                                                <span
                                                    class="text-slate-600 dark:text-slate-300"
                                                >
                                                    {{
                                                        item.tahun_berdiri ||
                                                        "-"
                                                    }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <button
                                                type="button"
                                                :disabled="
                                                    processingToggleId ===
                                                    item.id
                                                "
                                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
                                                :class="
                                                    item.status
                                                        ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-950/60'
                                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700'
                                                "
                                                @click="toggleStatus(item)"
                                            >
                                                <span
                                                    v-if="
                                                        processingToggleId ===
                                                        item.id
                                                    "
                                                    class="size-3.5 animate-spin rounded-full border-2 border-current/20 border-t-current"
                                                ></span>

                                                <ToggleRight
                                                    v-else-if="item.status"
                                                    class="size-4"
                                                />

                                                <ToggleLeft
                                                    v-else
                                                    class="size-4"
                                                />

                                                {{
                                                    item.status
                                                        ? "Aktif"
                                                        : "Nonaktif"
                                                }}
                                            </button>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-1">
                                                <button
                                                    type="button"
                                                    title="Lihat detail"
                                                    class="rounded-lg p-2 text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                    @click="openDetail(item)"
                                                >
                                                    <Eye class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Edit profil"
                                                    class="rounded-lg p-2 text-slate-500 transition hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                                    @click="openEdit(item)"
                                                >
                                                    <Pencil class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Hapus profil"
                                                    class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
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
                            class="flex flex-col gap-4 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                        >
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
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
                                profil
                            </p>

                            <div class="flex flex-wrap items-center gap-1">
                                <button
                                    type="button"
                                    title="Halaman pertama"
                                    :disabled="!firstPageUrl()"
                                    class="rounded-lg p-2 transition"
                                    :class="
                                        firstPageUrl()
                                            ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                            : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                    "
                                    @click="goToPage(firstPageUrl())"
                                >
                                    <ChevronsLeft class="size-4" />
                                </button>

                                <button
                                    type="button"
                                    title="Halaman sebelumnya"
                                    :disabled="!previousPageUrl()"
                                    class="rounded-lg p-2 transition"
                                    :class="
                                        previousPageUrl()
                                            ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                            : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
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
                                    :disabled="!link.url"
                                    class="min-w-9 rounded-lg px-3 py-2 text-sm transition"
                                    :class="
                                        link.active
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : link.url
                                              ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                              : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                    "
                                    @click="goToPage(link.url)"
                                >
                                    {{ paginationPageLabel(link.label) || "…" }}
                                </button>

                                <button
                                    type="button"
                                    title="Halaman berikutnya"
                                    :disabled="!nextPageUrl()"
                                    class="rounded-lg p-2 transition"
                                    :class="
                                        nextPageUrl()
                                            ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                            : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                    "
                                    @click="goToPage(nextPageUrl())"
                                >
                                    <ChevronRight class="size-4" />
                                </button>

                                <button
                                    type="button"
                                    title="Halaman terakhir"
                                    :disabled="!lastPageUrl()"
                                    class="rounded-lg p-2 transition"
                                    :class="
                                        lastPageUrl()
                                            ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                            : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                    "
                                    @click="goToPage(lastPageUrl())"
                                >
                                    <ChevronsRight class="size-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- EMPTY -->

                    <div v-else class="px-6 py-16 text-center">
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
                                    ? "Profil kawasan tidak ditemukan"
                                    : "Belum ada profil kawasan"
                            }}
                        </p>

                        <p
                            class="mx-auto mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400"
                        >
                            {{
                                search
                                    ? "Tidak ditemukan data yang sesuai dengan pencarian."
                                    : "Tambahkan informasi profil kawasan untuk mulai mengelola data."
                            }}
                        </p>

                        <button
                            v-if="!search"
                            type="button"
                            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />
                            Tambah Profil
                        </button>

                        <button
                            v-else
                            type="button"
                            class="mt-5 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
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
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeForm"
            >
                <div
                    class="my-auto w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    modalMode === "create"
                                        ? "Tambah Profil Kawasan"
                                        : "Edit Profil Kawasan"
                                }}
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    modalMode === "create"
                                        ? "Tambahkan informasi kawasan baru."
                                        : "Perbarui informasi profil kawasan."
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            :disabled="processing"
                            class="ml-4 shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800"
                            @click="closeForm"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <form
                        class="max-h-[calc(100vh-10rem)] overflow-y-auto"
                        @submit.prevent="submit"
                    >
                        <div class="grid gap-5 p-6 md:grid-cols-2">
                            <!-- JUDUL -->

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Judul Kawasan
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    v-model="form.judul"
                                    type="text"
                                    maxlength="255"
                                    required
                                    placeholder="Contoh: Kawasan Industri Tanjung Buton"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                    :class="{
                                        'border-red-400 focus:border-red-500':
                                            errors.judul,
                                    }"
                                />

                                <p
                                    v-if="errors.judul"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.judul }}
                                </p>
                            </div>

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
                                        aria-describedby="slug-help"
                                        class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 py-2.5 pl-8 pr-4 text-sm text-slate-500 outline-none dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-400"
                                        :class="{
                                            'border-red-400': errors.slug,
                                        }"
                                    />
                                </div>

                                <p
                                    id="slug-help"
                                    class="mt-1.5 flex items-start gap-1.5 text-xs leading-5 text-slate-400 dark:text-slate-500"
                                >
                                    <span
                                        class="mt-1.5 inline-flex size-1.5 shrink-0 rounded-full bg-blue-500"
                                    ></span>

                                    <span>
                                        Slug dibuat secara otomatis berdasarkan
                                        judul kawasan dan tidak dapat diubah
                                        secara manual.
                                    </span>
                                </p>

                                <p
                                    v-if="form.slug"
                                    class="mt-1 text-xs text-blue-500/80 dark:text-blue-400/80"
                                >
                                    URL:
                                    <span class="font-mono">
                                        /{{ form.slug }}
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
                                    class="flex w-full items-center justify-between rounded-xl border px-4 py-2.5 text-sm transition"
                                    :class="
                                        form.status
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-400'
                                            : 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                    @click="form.status = !form.status"
                                >
                                    <span class="flex items-center gap-2">
                                        <ToggleRight
                                            v-if="form.status"
                                            class="size-5"
                                        />

                                        <ToggleLeft v-else class="size-5" />

                                        {{
                                            form.status
                                                ? "Profil Aktif"
                                                : "Profil Nonaktif"
                                        }}
                                    </span>

                                    <span class="text-xs opacity-70">
                                        Klik untuk ubah
                                    </span>
                                </button>
                            </div>

                            <!-- LUAS -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Luas Kawasan
                                </label>

                                <div class="relative">
                                    <Ruler
                                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    />

                                    <input
                                        v-model="form.luas_kawasan"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900"
                                    />

                                    <span
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"
                                    >
                                        Ha
                                    </span>
                                </div>

                                <p
                                    v-if="errors.luas_kawasan"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.luas_kawasan }}
                                </p>
                            </div>

                            <!-- TAHUN -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Tahun Berdiri
                                </label>

                                <div class="relative">
                                    <CalendarDays
                                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    />

                                    <input
                                        v-model="form.tahun_berdiri"
                                        type="number"
                                        min="1800"
                                        max="9999"
                                        placeholder="Contoh: 2020"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                    />
                                </div>

                                <p
                                    v-if="errors.tahun_berdiri"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.tahun_berdiri }}
                                </p>
                            </div>

                            <!-- LOKASI -->

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Lokasi
                                </label>

                                <div class="relative">
                                    <MapPin
                                        class="pointer-events-none absolute left-3 top-3 size-4 text-slate-400"
                                    />

                                    <textarea
                                        v-model="form.lokasi"
                                        rows="3"
                                        maxlength="255"
                                        placeholder="Masukkan lokasi atau alamat kawasan..."
                                        class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                    ></textarea>
                                </div>

                                <p
                                    v-if="errors.lokasi"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.lokasi }}
                                </p>
                            </div>

                            <!-- DESKRIPSI -->

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Deskripsi
                                </label>

                                <textarea
                                    v-model="form.deskripsi"
                                    rows="6"
                                    placeholder="Tuliskan deskripsi lengkap mengenai kawasan..."
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                ></textarea>

                                <p
                                    v-if="errors.deskripsi"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ errors.deskripsi }}
                                </p>
                            </div>

                            <!-- GAMBAR -->

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Gambar Kawasan
                                </label>

                                <div
                                    class="grid gap-4 sm:grid-cols-[180px_1fr]"
                                >
                                    <div
                                        class="flex aspect-video items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800"
                                    >
                                        <img
                                            v-if="
                                                previewUrl ||
                                                getImageUrl(existingImage)
                                            "
                                            :src="
                                                previewUrl ||
                                                getImageUrl(existingImage)!
                                            "
                                            alt="Preview gambar kawasan"
                                            class="size-full object-cover"
                                        />

                                        <div v-else class="text-center">
                                            <ImageIcon
                                                class="mx-auto size-8 text-slate-300 dark:text-slate-600"
                                            />

                                            <p
                                                class="mt-1 text-xs text-slate-400"
                                            >
                                                Belum ada gambar
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="flex min-h-[126px] cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 text-center transition hover:border-blue-400 hover:bg-blue-50/40 dark:border-slate-700 dark:bg-slate-800/60 dark:hover:border-blue-500 dark:hover:bg-blue-950/20"
                                        >
                                            <ImageIcon
                                                class="mb-2 size-7 text-slate-400"
                                            />

                                            <span
                                                class="text-sm font-medium text-slate-700 dark:text-slate-300"
                                            >
                                                Pilih gambar
                                            </span>

                                            <span
                                                class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                JPG, JPEG, PNG atau WEBP · Maks.
                                                1 MB
                                            </span>

                                            <input
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                class="hidden"
                                                @change="handleImageChange"
                                            />
                                        </label>

                                        <div class="mt-2 flex flex-wrap gap-3">
                                            <button
                                                v-if="previewUrl"
                                                type="button"
                                                class="text-xs font-medium text-red-500 hover:text-red-600"
                                                @click="removeSelectedImage"
                                            >
                                                Hapus gambar baru
                                            </button>

                                            <button
                                                v-if="
                                                    modalMode === 'edit' &&
                                                    existingImage &&
                                                    !previewUrl
                                                "
                                                type="button"
                                                class="text-xs font-medium text-red-500 hover:text-red-600"
                                                @click="removeExistingImage"
                                            >
                                                Hapus gambar lama
                                            </button>
                                        </div>

                                        <p
                                            v-if="form.remove_gambar"
                                            class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-950/30 dark:text-amber-400"
                                        >
                                            Gambar lama akan dihapus saat
                                            disimpan.
                                        </p>

                                        <p
                                            v-if="errors.gambar"
                                            class="mt-1.5 text-xs text-red-500"
                                        >
                                            {{ errors.gambar }}
                                        </p>

                                        <p
                                            v-if="errors.remove_gambar"
                                            class="mt-1.5 text-xs text-red-500"
                                        >
                                            {{ errors.remove_gambar }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FOOTER -->

                        <div
                            class="flex flex-col-reverse gap-3 border-t border-slate-200 px-6 py-5 sm:flex-row sm:justify-end dark:border-slate-800"
                        >
                            <button
                                type="button"
                                :disabled="processing"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeForm"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="processing"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span
                                    v-if="processing"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                {{
                                    processing
                                        ? "Menyimpan..."
                                        : modalMode === "create"
                                          ? "Simpan Profil"
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
                v-if="showDetailModal && selectedKawasan"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeDetail"
            >
                <div
                    class="my-auto w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
                    >
                        <div>
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Profil Kawasan
                            </h2>

                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap kawasan.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                            @click="closeDetail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <div class="max-h-[calc(100vh-10rem)] overflow-y-auto p-6">
                        <!-- HERO -->

                        <div
                            class="overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 via-indigo-50/50 to-white dark:border-blue-900/40 dark:from-blue-950/30 dark:via-indigo-950/20 dark:to-slate-900"
                        >
                            <div class="grid md:grid-cols-[240px_1fr]">
                                <div
                                    class="aspect-video overflow-hidden bg-slate-100 md:aspect-auto dark:bg-slate-800"
                                >
                                    <img
                                        v-if="
                                            getImageUrl(selectedKawasan.gambar)
                                        "
                                        :src="
                                            getImageUrl(selectedKawasan.gambar)!
                                        "
                                        :alt="selectedKawasan.judul"
                                        class="size-full object-cover"
                                    />

                                    <div
                                        v-else
                                        class="flex h-full min-h-48 items-center justify-center"
                                    >
                                        <ImageIcon
                                            class="size-12 text-slate-300 dark:text-slate-600"
                                        />
                                    </div>
                                </div>

                                <div class="p-5">
                                    <div
                                        class="flex flex-wrap items-start justify-between gap-3"
                                    >
                                        <div class="min-w-0">
                                            <h3
                                                class="text-xl font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{ selectedKawasan.judul }}
                                            </h3>

                                            <div
                                                class="mt-2 inline-flex max-w-full items-center rounded-lg border border-blue-100 bg-white/70 px-2.5 py-1.5 dark:border-blue-900/40 dark:bg-slate-900/50"
                                            >
                                                <span
                                                    class="truncate font-mono text-xs text-blue-600 dark:text-blue-400"
                                                >
                                                    /{{ selectedKawasan.slug }}
                                                </span>
                                            </div>
                                        </div>

                                        <span
                                            class="inline-flex shrink-0 rounded-full px-3 py-1.5 text-xs font-medium"
                                            :class="
                                                selectedKawasan.status
                                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                    : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                            "
                                        >
                                            {{
                                                selectedKawasan.status
                                                    ? "Aktif"
                                                    : "Nonaktif"
                                            }}
                                        </span>
                                    </div>

                                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                        <div
                                            class="rounded-xl border border-slate-200 bg-white/70 p-3 dark:border-slate-700 dark:bg-slate-900/50"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Ruler
                                                    class="size-4 text-blue-500"
                                                />

                                                <span
                                                    class="text-xs text-slate-500 dark:text-slate-400"
                                                >
                                                    Luas Kawasan
                                                </span>
                                            </div>

                                            <p
                                                class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                            >
                                                {{
                                                    formatArea(
                                                        selectedKawasan.luas_kawasan,
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <div
                                            class="rounded-xl border border-slate-200 bg-white/70 p-3 dark:border-slate-700 dark:bg-slate-900/50"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <CalendarDays
                                                    class="size-4 text-blue-500"
                                                />

                                                <span
                                                    class="text-xs text-slate-500 dark:text-slate-400"
                                                >
                                                    Tahun Berdiri
                                                </span>
                                            </div>

                                            <p
                                                class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                            >
                                                {{
                                                    selectedKawasan.tahun_berdiri ||
                                                    "-"
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SLUG -->

                        <div class="mt-5">
                            <div class="mb-2 flex items-center gap-2">
                                <FileText class="size-4 text-blue-500" />

                                <h4
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Slug
                                </h4>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <code
                                    class="break-all text-sm text-blue-600 dark:text-blue-400"
                                >
                                    /{{ selectedKawasan.slug }}
                                </code>
                            </div>
                        </div>

                        <!-- LOKASI -->

                        <div class="mt-5">
                            <div class="mb-2 flex items-center gap-2">
                                <MapPin class="size-4 text-blue-500" />

                                <h4
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Lokasi
                                </h4>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                            >
                                {{ selectedKawasan.lokasi || "-" }}
                            </div>
                        </div>

                        <!-- DESKRIPSI -->

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
                                    {{ selectedKawasan.deskripsi || "-" }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER -->

                    <div
                        class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closeDetail"
                        >
                            Tutup
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                            @click="openEdit(selectedKawasan)"
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
                v-if="showDeleteModal && selectedKawasan"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeDelete"
            >
                <div
                    class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900"
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
                            Hapus Profil Kawasan?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedKawasan.judul }}
                            </span>
                            ? Data yang sudah dihapus tidak dapat dikembalikan.
                        </p>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closeDelete"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="deleteKawasan"
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
                        class="h-full w-1/3 animate-loading-bar rounded-full bg-blue-600"
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

@media (max-width: 640px) {
    .blob-shape {
        transform: scale(0.75);
    }

    .blob-shape-delayed {
        transform: scale(0.7);
    }

    .blob-shape-slow {
        transform: scale(0.65);
    }
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
