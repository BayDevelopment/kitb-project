<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowDown,
    ArrowUp,
    Building2,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    Eye,
    FileText,
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

defineOptions({
    layout: AppLayout,
});

/**
 * |--------------------------------------------------------------------------
 * | Interfaces
 * |--------------------------------------------------------------------------
 */

interface Infrastruktur {
    id: number;
    nama: string;
    slug: string;
    deskripsi: string | null;
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

interface InfrastrukturPagination {
    data: Infrastruktur[];
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
    slug?: string;
    deskripsi?: string;
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
    infrastrukturs: InfrastrukturPagination | null;
    filters?: {
        search?: string;
    };
}>();

const BASE_URL = "/admin/kawasan/infrastruktur";

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

const selectedInfrastruktur = ref<Infrastruktur | null>(null);

/**
 * |--------------------------------------------------------------------------
 * | Form State
 * |--------------------------------------------------------------------------
 */

const form = ref({
    nama: "",
    slug: "",
    deskripsi: "",
    gambar: null as File | null,
    urutan: "",
    aktif: true,
    remove_gambar: false,
});

const existingImage = ref<string | null>(null);
const previewUrl = ref<string | null>(null);

const processing = ref(false);
const processingDelete = ref(false);
const processingToggleId = ref<number | null>(null);
const processingMoveId = ref<number | null>(null);

const errors = ref<FormErrors>({});

/**
 * |--------------------------------------------------------------------------
 * | Slug (preview saja, backend/model yang menentukan slug final)
 * |--------------------------------------------------------------------------
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
    props.infrastrukturs?.meta?.total ??
    props.infrastrukturs?.total ??
    props.infrastrukturs?.data?.length ??
    0;

const getCurrentPage = (): number =>
    props.infrastrukturs?.meta?.current_page ??
    props.infrastrukturs?.current_page ??
    1;

const getPerPage = (): number =>
    props.infrastrukturs?.meta?.per_page ??
    props.infrastrukturs?.per_page ??
    props.infrastrukturs?.data?.length ??
    10;

const getLastPage = (): number =>
    props.infrastrukturs?.meta?.last_page ??
    props.infrastrukturs?.last_page ??
    1;

const getFrom = (): number =>
    props.infrastrukturs?.meta?.from ?? props.infrastrukturs?.from ?? 0;

const getTo = (): number =>
    props.infrastrukturs?.meta?.to ?? props.infrastrukturs?.to ?? 0;

const getRowNumber = (index: number): number =>
    (getCurrentPage() - 1) * getPerPage() + index + 1;

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
    return props.infrastrukturs?.links ?? [];
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

    form.value = {
        nama: "",
        slug: "",
        deskripsi: "",
        gambar: null,
        urutan: "",
        aktif: true,
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
    selectedInfrastruktur.value = null;

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

const openEdit = (item: Infrastruktur): void => {
    if (processing.value) {
        return;
    }

    modalMode.value = "edit";
    selectedInfrastruktur.value = item;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    errors.value = {};

    isHydratingForm.value = true;

    form.value = {
        nama: toStringValue(item.nama),
        slug: toStringValue(item.slug),
        deskripsi: toStringValue(item.deskripsi),
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
    selectedInfrastruktur.value = null;

    resetForm();
};

const forceCloseForm = (): void => {
    showFormModal.value = false;
    selectedInfrastruktur.value = null;

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
 * Slug tidak dikirim. Controller hanya menerima:
 * nama, deskripsi, gambar, urutan, aktif, remove_gambar (edit).
 */

const submit = (): void => {
    if (processing.value) {
        return;
    }

    errors.value = {};

    const nama = toStringValue(form.value.nama);
    const deskripsi = toStringValue(form.value.deskripsi);
    const urutan = toStringValue(form.value.urutan);

    if (!nama) {
        errors.value.nama = "Nama infrastruktur wajib diisi.";

        return;
    }

    if (!slugify(nama)) {
        errors.value.nama =
            "Nama harus mengandung huruf atau angka agar slug dapat dibuat.";

        return;
    }

    const isEdit =
        modalMode.value === "edit" && selectedInfrastruktur.value !== null;

    const formData = new FormData();

    formData.append("nama", nama);
    formData.append("deskripsi", deskripsi);
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
        ? `${BASE_URL}/${selectedInfrastruktur.value!.id}`
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

            console.error("Gagal menyimpan infrastruktur:", serverErrors);
        },

        onCancel: () => {
            console.warn("Request penyimpanan infrastruktur dibatalkan.");
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

const openDetail = (item: Infrastruktur): void => {
    selectedInfrastruktur.value = item;

    showFormModal.value = false;
    showDeleteModal.value = false;
    showDetailModal.value = true;
};

const closeDetail = (): void => {
    showDetailModal.value = false;
    selectedInfrastruktur.value = null;
};

/**
 * |--------------------------------------------------------------------------
 * | Delete
 * |--------------------------------------------------------------------------
 */

const openDelete = (item: Infrastruktur): void => {
    if (processingDelete.value || processing.value) {
        return;
    }

    selectedInfrastruktur.value = item;

    showFormModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = true;
};

const closeDelete = (): void => {
    if (processingDelete.value) {
        return;
    }

    showDeleteModal.value = false;
    selectedInfrastruktur.value = null;
};

const deleteInfrastruktur = (): void => {
    if (!selectedInfrastruktur.value || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(`${BASE_URL}/${selectedInfrastruktur.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDeleteModal.value = false;
            selectedInfrastruktur.value = null;
        },

        onError: (serverErrors) => {
            console.error("Gagal menghapus infrastruktur:", serverErrors);
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

const toggleAktif = (item: Infrastruktur): void => {
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
                console.error(
                    "Gagal mengubah status infrastruktur:",
                    serverErrors,
                );
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

const moveInfrastruktur = (
    item: Infrastruktur,
    direction: "up" | "down",
): void => {
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
                console.error(
                    "Gagal mengubah urutan infrastruktur:",
                    serverErrors,
                );
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
                                Infrastruktur
                            </h1>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Kelola informasi infrastruktur kawasan
                                perusahaan.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 active:scale-[0.98] sm:w-auto"
                        @click="openCreate"
                    >
                        <Plus class="size-4" />
                        Tambah Infrastruktur
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
                            placeholder="Cari nama atau deskripsi infrastruktur..."
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
                                    Data Infrastruktur
                                </h2>

                                <p
                                    class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Informasi infrastruktur yang tersedia di
                                    kawasan.
                                </p>
                            </div>
                        </div>

                        <span
                            class="self-start rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300 sm:self-auto"
                        >
                            {{ getTotal() }} data
                        </span>
                    </div>

                    <!-- DATA -->

                    <div v-if="(props.infrastrukturs?.data?.length ?? 0) > 0">
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
                                            Infrastruktur
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
                                        v-for="(item, index) in props
                                            .infrastrukturs?.data ?? []"
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

                                        <!-- INFRASTRUKTUR -->

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
                                                        :alt="item.nama"
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
                                                        {{ item.nama }}
                                                    </p>

                                                    <p
                                                        class="mt-0.5 max-w-[260px] truncate text-xs text-blue-600 dark:text-blue-400"
                                                    >
                                                        /{{ item.slug }}
                                                    </p>

                                                    <p
                                                        class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                                    >
                                                        ID #{{ item.id }}
                                                    </p>
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
                                                        item.deskripsi,
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
                                                        moveInfrastruktur(
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
                                                        moveInfrastruktur(
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
                                                    title="Edit infrastruktur"
                                                    aria-label="Edit infrastruktur"
                                                    class="rounded-lg p-2 text-slate-500 transition hover:bg-amber-50 hover:text-amber-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/30 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                                    @click="openEdit(item)"
                                                >
                                                    <Pencil class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Hapus infrastruktur"
                                                    aria-label="Hapus infrastruktur"
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
                                    infrastruktur
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
                                    ? "Infrastruktur tidak ditemukan"
                                    : "Belum ada infrastruktur"
                            }}
                        </p>

                        <p
                            class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{
                                search
                                    ? "Tidak ditemukan data yang sesuai dengan pencarian."
                                    : "Tambahkan data infrastruktur untuk mulai mengelola informasi kawasan."
                            }}
                        </p>

                        <button
                            v-if="!search"
                            type="button"
                            class="mt-5 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />
                            Tambah Infrastruktur
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
                                        ? "Tambah Infrastruktur"
                                        : "Edit Infrastruktur"
                                }}
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    modalMode === "create"
                                        ? "Tambahkan informasi infrastruktur baru."
                                        : "Perbarui informasi infrastruktur."
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
                        @submit.prevent="submit"
                    >
                        <div class="grid gap-5 p-4 sm:p-6 md:grid-cols-2">
                            <!-- NAMA -->

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Nama Infrastruktur
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    v-model="form.nama"
                                    type="text"
                                    maxlength="150"
                                    required
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
                                        Slug dibuat otomatis oleh sistem
                                        berdasarkan nama infrastruktur dan tidak
                                        dapat diubah secara manual. Slug final
                                        dapat berbeda jika sudah digunakan.
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
                                                    ? "Infrastruktur Aktif"
                                                    : "Infrastruktur Nonaktif"
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
                                    maxlength="10000"
                                    placeholder="Tuliskan deskripsi lengkap mengenai infrastruktur..."
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

                            <!-- GAMBAR -->

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Gambar Infrastruktur
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
                                          ? "Simpan Infrastruktur"
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
                v-if="showDetailModal && selectedInfrastruktur"
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
                                Detail Infrastruktur
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap infrastruktur kawasan.
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
                                v-if="getImageUrl(selectedInfrastruktur.gambar)"
                                :src="
                                    getImageUrl(selectedInfrastruktur.gambar)!
                                "
                                :alt="selectedInfrastruktur.nama"
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

                        <div class="mt-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3
                                    class="text-xl font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ selectedInfrastruktur.nama }}
                                </h3>

                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                                    :class="
                                        selectedInfrastruktur.aktif
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                >
                                    {{
                                        selectedInfrastruktur.aktif
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
                                    /{{ selectedInfrastruktur.slug }}
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
                                    {{ selectedInfrastruktur.urutan }}
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
                                    {{ selectedInfrastruktur.deskripsi || "-" }}
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
                            @click="openEdit(selectedInfrastruktur)"
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
                v-if="showDeleteModal && selectedInfrastruktur"
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
                            Hapus Infrastruktur?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedInfrastruktur.nama }}
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
                            class="min-h-11 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closeDelete"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                            @click="deleteInfrastruktur"
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
