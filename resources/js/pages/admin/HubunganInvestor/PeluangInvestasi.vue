<script setup lang="ts">
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
    type Component,
} from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowDown,
    ArrowUp,
    BriefcaseBusiness,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    Eye,
    FileText,
    Hash,
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

/* -------------------------------------------------------------------------
 * Interfaces
 * ---------------------------------------------------------------------- */

interface PeluangInvestasi {
    id: number;
    judul: string;
    slug: string;
    sektor_industri: string | null;
    deskripsi: string | null;
    luas_lahan: string | number | null;
    satuan_luas: string;
    lokasi: string | null;
    status: string;
    status_label?: string;
    nilai_investasi: string | number | null;
    mata_uang: string;
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
    links?: PaginationLink[];
}

interface PeluangInvestasiPagination {
    data: PeluangInvestasi[];
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
    sektor_industri?: string;
    deskripsi?: string;
    luas_lahan?: string;
    satuan_luas?: string;
    lokasi?: string;
    status?: string;
    nilai_investasi?: string;
    mata_uang?: string;
    gambar?: string;
    aktif?: string;
}

interface StatusOption {
    value: string;
    label: string;
}

/* -------------------------------------------------------------------------
 * Props & constants
 * ---------------------------------------------------------------------- */

const props = defineProps<{
    peluangInvestasi: PeluangInvestasiPagination | null;
    filters?: {
        search?: string;
        status?: string;
    };
    statuses?: Record<string, string> | string[];
}>();

const BASE_URL = "/hubungan-investor/peluang-investasi";
const MAX_IMAGE_SIZE = 1024 * 1024; // 1 MB
const ALLOWED_IMAGE_TYPES = ["image/jpeg", "image/png", "image/webp"];

const defaultStatuses: Record<string, string> = {
    tersedia: "Tersedia",
    proses: "Dalam Proses",
    terisi: "Terisi",
    ditutup: "Ditutup",
};

/* Shared class names (menghindari duplikasi di template) */
const inputClass =
    "min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900";
const inputErrorClass = "border-red-400 focus:border-red-500";
const smallInputClass =
    "min-h-11 w-24 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-center text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900";
const labelClass =
    "mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300";
const errorClass = "mt-1.5 text-xs text-red-500";
const cardClass =
    "rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/40 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20";
const modalBackdropClass =
    "fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-950/55 p-3 backdrop-blur-sm sm:p-4";
const modalCardClass =
    "my-auto w-full overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-2xl shadow-slate-900/10 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/40";
const closeBtnClass =
    "shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800 dark:hover:text-slate-200";
const secondaryBtnClass =
    "min-h-11 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800";
const primaryBtnClass =
    "inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto";
const rowIconBtnClass =
    "rounded-lg p-2 text-slate-500 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-30 dark:text-slate-400";
const thClass =
    "px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400";
const infoCardClass =
    "rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-800/50";

const columns = [
    { label: "No.", width: "w-16", align: "text-center" },
    { label: "Peluang Investasi", width: "w-[340px]", align: "text-left" },
    { label: "Sektor", width: "w-48", align: "text-left" },
    { label: "Lahan", width: "w-48", align: "text-left" },
    { label: "Nilai Investasi", width: "w-52", align: "text-left" },
    { label: "Status", width: "w-44", align: "text-left" },
    { label: "Urutan", width: "w-40", align: "text-center" },
    { label: "Aktif", width: "w-44", align: "text-center" },
    { label: "Aksi", width: "w-44", align: "text-right" },
];

/* -------------------------------------------------------------------------
 * Data accessors
 * ---------------------------------------------------------------------- */

const items = computed<PeluangInvestasi[]>(
    () => props.peluangInvestasi?.data ?? [],
);

const total = computed(
    () =>
        props.peluangInvestasi?.meta?.total ??
        props.peluangInvestasi?.total ??
        items.value.length,
);

const currentPage = computed(
    () =>
        props.peluangInvestasi?.meta?.current_page ??
        props.peluangInvestasi?.current_page ??
        1,
);

const perPage = computed(
    () =>
        props.peluangInvestasi?.meta?.per_page ??
        props.peluangInvestasi?.per_page ??
        (items.value.length || 10),
);

const lastPage = computed(
    () =>
        props.peluangInvestasi?.meta?.last_page ??
        props.peluangInvestasi?.last_page ??
        1,
);

const fromRow = computed(
    () =>
        props.peluangInvestasi?.meta?.from ?? props.peluangInvestasi?.from ?? 0,
);

const toRow = computed(
    () => props.peluangInvestasi?.meta?.to ?? props.peluangInvestasi?.to ?? 0,
);

const getRowNumber = (index: number): number =>
    (currentPage.value - 1) * perPage.value + index + 1;

/* Pengurutan hanya valid jika daftar tidak sedang difilter */
const isFiltered = computed(() =>
    Boolean(props.filters?.search || props.filters?.status),
);

const isFirstItem = (index: number): boolean => getRowNumber(index) <= 1;
const isLastItem = (index: number): boolean =>
    getRowNumber(index) >= total.value;

/* -------------------------------------------------------------------------
 * Page loading
 * ---------------------------------------------------------------------- */

const isPageLoading = ref(false);

let removeRouterStartListener: (() => void) | null = null;
let removeRouterFinishListener: (() => void) | null = null;

/* -------------------------------------------------------------------------
 * Status helpers
 * ---------------------------------------------------------------------- */

const normalizeStatusValue = (value: unknown): string => {
    if (typeof value === "string") return value;
    if (typeof value === "number") return String(value);

    if (value && typeof value === "object") {
        const obj = value as Record<string, unknown>;

        for (const key of ["value", "key", "status", "label"]) {
            if (typeof obj[key] === "string") {
                return obj[key] as string;
            }
        }
    }

    return "";
};

const normalizeStatusLabel = (label: unknown, fallback: string): string => {
    if (typeof label === "string" && label.trim()) {
        return label;
    }

    const normalized = fallback.trim();

    return (
        defaultStatuses[normalized] ||
        normalized.replace(/_/g, " ") ||
        "Tidak diketahui"
    );
};

const statusOptions = computed<StatusOption[]>(() => {
    if (Array.isArray(props.statuses)) {
        return props.statuses
            .map((status): StatusOption | null => {
                const value = normalizeStatusValue(status);

                if (!value) return null;

                const label =
                    status && typeof status === "object"
                        ? (status as { label?: unknown }).label
                        : undefined;

                return { value, label: normalizeStatusLabel(label, value) };
            })
            .filter((s): s is StatusOption => s !== null);
    }

    if (props.statuses && typeof props.statuses === "object") {
        return Object.entries(props.statuses).map(([value, label]) => ({
            value,
            label: normalizeStatusLabel(label, value),
        }));
    }

    return Object.entries(defaultStatuses).map(([value, label]) => ({
        value,
        label,
    }));
});

const getStatusLabel = (item: PeluangInvestasi): string => {
    if (item.status_label) return item.status_label;

    const value = normalizeStatusValue(item.status);

    if (!value) return "-";

    return (
        statusOptions.value.find((s) => s.value === value)?.label ??
        defaultStatuses[value] ??
        value.replace(/_/g, " ")
    );
};

const statusClass = (status: string): string => {
    switch (status) {
        case "tersedia":
            return "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400";
        case "proses":
            return "bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400";
        case "terisi":
            return "bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400";
        default:
            return "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400";
    }
};

/* -------------------------------------------------------------------------
 * Generic helpers
 * ---------------------------------------------------------------------- */

const toStringValue = (value: string | number | null | undefined): string =>
    value === null || value === undefined ? "" : String(value).trim();

const slugify = (value: string): string =>
    String(value ?? "")
        .normalize("NFKD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-")
        .replace(/^-+|-+$/g, "");

const getImageUrl = (image: string | null): string | null => {
    if (!image) return null;

    if (/^(https?:)?\/\//.test(image) || image.startsWith("/")) {
        return image;
    }

    return `/storage/${image}`;
};

const formatNumber = (value: string | number | null | undefined): string => {
    if (value === null || value === undefined || value === "") return "-";

    const number = Number(value);

    if (!Number.isFinite(number)) return String(value);

    return new Intl.NumberFormat("id-ID", { maximumFractionDigits: 2 }).format(
        number,
    );
};

const formatInvestment = (
    value: string | number | null | undefined,
    currency: string,
): string => {
    const formatted = formatNumber(value);

    return formatted === "-" ? "-" : `${currency} ${formatted}`;
};

/* -------------------------------------------------------------------------
 * Search & filter
 * ---------------------------------------------------------------------- */

const search = ref(props.filters?.search ?? "");
const selectedStatus = ref(props.filters?.status ?? "");

let searchTimer: ReturnType<typeof setTimeout> | null = null;

const clearSearchTimer = (): void => {
    if (searchTimer) {
        clearTimeout(searchTimer);
        searchTimer = null;
    }
};

const visitList = (): void => {
    router.get(
        BASE_URL,
        {
            search: search.value.trim() || undefined,
            status: selectedStatus.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const submitSearch = (): void => {
    clearSearchTimer();
    searchTimer = setTimeout(visitList, 350);
};

const submitFilter = (): void => {
    clearSearchTimer();
    visitList();
};

const clearSearchInput = (): void => {
    search.value = "";
    submitFilter();
};

const clearFilters = (): void => {
    clearSearchTimer();
    search.value = "";
    selectedStatus.value = "";
    visitList();
};

/* Sinkronkan input dengan server (mis. tombol back browser) */
watch(
    () => props.filters,
    (filters) => {
        search.value = filters?.search ?? "";
        selectedStatus.value = filters?.status ?? "";
    },
);

/* -------------------------------------------------------------------------
 * Pagination
 * ---------------------------------------------------------------------- */

const paginationLinks = computed<PaginationLink[]>(
    () =>
        props.peluangInvestasi?.meta?.links ??
        props.peluangInvestasi?.links ??
        [],
);

const pageLinks = computed(() => paginationLinks.value.slice(1, -1));

const paginationPageLabel = (label: string): string =>
    label
        .replace(/&laquo;|&raquo;/g, "")
        .replace(/previous|next/gi, "")
        .trim();

const goToPage = (url: string | null): void => {
    if (!url) return;

    router.get(
        url,
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const firstPageUrl = computed<string | null>(() => {
    if (currentPage.value <= 1) return null;

    return paginationLinks.value.length > 2
        ? (paginationLinks.value[1]?.url ?? null)
        : null;
});

const lastPageUrl = computed<string | null>(() => {
    if (currentPage.value >= lastPage.value) return null;

    return paginationLinks.value.length > 2
        ? (paginationLinks.value[paginationLinks.value.length - 2]?.url ?? null)
        : null;
});

const previousPageUrl = computed<string | null>(
    () => paginationLinks.value[0]?.url ?? null,
);

const nextPageUrl = computed<string | null>(() =>
    paginationLinks.value.length > 0
        ? (paginationLinks.value[paginationLinks.value.length - 1]?.url ?? null)
        : null,
);

const navButtonClass = (url: string | null): string =>
    url
        ? "text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
        : "cursor-not-allowed text-slate-300 dark:text-slate-700";

/* -------------------------------------------------------------------------
 * Modal & form state
 * ---------------------------------------------------------------------- */

const showFormModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);

const modalMode = ref<"create" | "edit">("create");
const selectedPeluangInvestasi = ref<PeluangInvestasi | null>(null);

const emptyForm = () => ({
    judul: "",
    slug: "",
    sektor_industri: "",
    deskripsi: "",
    luas_lahan: "",
    satuan_luas: "Ha",
    lokasi: "",
    status: "tersedia",
    nilai_investasi: "",
    mata_uang: "IDR",
    gambar: null as File | null,
    aktif: true,
});

const form = ref(emptyForm());

const existingImage = ref<string | null>(null);
const removedExistingImage = ref(false);
const previewUrl = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const processing = ref(false);
const processingDelete = ref(false);
const processingToggleId = ref<number | null>(null);
const processingMoveId = ref<number | null>(null);

const errors = ref<FormErrors>({});

const displayImage = computed(
    () => previewUrl.value || getImageUrl(existingImage.value),
);

const isHydratingForm = ref(false);

watch(
    () => form.value.judul,
    (value) => {
        if (isHydratingForm.value) return;

        form.value.slug = slugify(value);
    },
);

const revokePreview = (): void => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
};

const hydrateForm = (data: ReturnType<typeof emptyForm>): void => {
    isHydratingForm.value = true;
    form.value = data;

    requestAnimationFrame(() => {
        isHydratingForm.value = false;
    });
};

const resetForm = (): void => {
    hydrateForm(emptyForm());

    existingImage.value = null;
    removedExistingImage.value = false;
    errors.value = {};

    revokePreview();

    if (fileInput.value) fileInput.value.value = "";
};

const closeAllModals = (): void => {
    showFormModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = false;
};

/* -------------------------------------------------------------------------
 * Create / Edit
 * ---------------------------------------------------------------------- */

const openCreate = (): void => {
    if (processing.value) return;

    modalMode.value = "create";
    selectedPeluangInvestasi.value = null;

    resetForm();
    closeAllModals();
    showFormModal.value = true;
};

const openEdit = (item: PeluangInvestasi): void => {
    if (processing.value) return;

    modalMode.value = "edit";
    selectedPeluangInvestasi.value = item;

    resetForm();

    hydrateForm({
        judul: toStringValue(item.judul),
        slug: toStringValue(item.slug),
        sektor_industri: toStringValue(item.sektor_industri),
        deskripsi: toStringValue(item.deskripsi),
        luas_lahan: toStringValue(item.luas_lahan),
        satuan_luas: toStringValue(item.satuan_luas) || "Ha",
        lokasi: toStringValue(item.lokasi),
        status: toStringValue(item.status) || "tersedia",
        nilai_investasi: toStringValue(item.nilai_investasi),
        mata_uang: toStringValue(item.mata_uang) || "IDR",
        gambar: null,
        aktif: Boolean(item.aktif),
    });

    existingImage.value = item.gambar;

    closeAllModals();
    showFormModal.value = true;
};

const closeForm = (): void => {
    if (processing.value) return;

    showFormModal.value = false;
    selectedPeluangInvestasi.value = null;

    resetForm();
};

const forceCloseForm = (): void => {
    showFormModal.value = false;
    selectedPeluangInvestasi.value = null;

    resetForm();
};

/* -------------------------------------------------------------------------
 * Image upload
 * ---------------------------------------------------------------------- */

const handleImageChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    errors.value.gambar = undefined;

    if (!file) {
        form.value.gambar = null;
        revokePreview();

        return;
    }

    if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
        errors.value.gambar =
            "Gambar harus berformat JPG, JPEG, PNG, atau WEBP.";
        target.value = "";
        form.value.gambar = null;

        return;
    }

    if (file.size > MAX_IMAGE_SIZE) {
        errors.value.gambar = "Ukuran gambar maksimal 1 MB.";
        target.value = "";
        form.value.gambar = null;

        return;
    }

    form.value.gambar = file;

    revokePreview();
    previewUrl.value = URL.createObjectURL(file);
};

const removeSelectedImage = (): void => {
    form.value.gambar = null;
    revokePreview();

    if (fileInput.value) fileInput.value.value = "";
};

const removeExistingImage = (): void => {
    existingImage.value = null;
    removedExistingImage.value = true;
};

/* -------------------------------------------------------------------------
 * Submit
 * ---------------------------------------------------------------------- */

const submit = (): void => {
    if (processing.value) return;

    errors.value = {};

    const judul = toStringValue(form.value.judul);
    const luasLahan = toStringValue(form.value.luas_lahan);
    const nilaiInvestasi = toStringValue(form.value.nilai_investasi);
    const satuanLuas = toStringValue(form.value.satuan_luas) || "Ha";
    const mataUang = toStringValue(form.value.mata_uang).toUpperCase() || "IDR";
    const status = toStringValue(form.value.status) || "tersedia";

    if (!judul) {
        errors.value.judul = "Judul peluang investasi wajib diisi.";

        return;
    }

    if (!slugify(judul)) {
        errors.value.judul =
            "Judul harus mengandung huruf atau angka agar slug dapat dibuat.";

        return;
    }

    if (
        luasLahan &&
        (!Number.isFinite(Number(luasLahan)) || Number(luasLahan) < 0)
    ) {
        errors.value.luas_lahan =
            "Luas lahan harus berupa angka tidak negatif.";

        return;
    }

    if (
        nilaiInvestasi &&
        (!Number.isFinite(Number(nilaiInvestasi)) || Number(nilaiInvestasi) < 0)
    ) {
        errors.value.nilai_investasi =
            "Nilai investasi harus berupa angka tidak negatif.";

        return;
    }

    const isEdit =
        modalMode.value === "edit" && selectedPeluangInvestasi.value !== null;

    const formData = new FormData();

    formData.append("judul", judul);
    formData.append(
        "sektor_industri",
        toStringValue(form.value.sektor_industri),
    );
    formData.append("deskripsi", toStringValue(form.value.deskripsi));
    formData.append("luas_lahan", luasLahan);
    formData.append("satuan_luas", satuanLuas);
    formData.append("lokasi", toStringValue(form.value.lokasi));
    formData.append("status", status);
    formData.append("nilai_investasi", nilaiInvestasi);
    formData.append("mata_uang", mataUang);
    formData.append("aktif", form.value.aktif ? "1" : "0");

    if (form.value.gambar instanceof File) {
        formData.append("gambar", form.value.gambar);
    }

    if (isEdit) {
        formData.append("_method", "PUT");

        // Backend harus membaca flag ini untuk menghapus gambar lama.
        if (removedExistingImage.value && !form.value.gambar) {
            formData.append("hapus_gambar", "1");
        }
    }

    const url = isEdit
        ? `${BASE_URL}/${selectedPeluangInvestasi.value!.id}`
        : BASE_URL;

    processing.value = true;

    router.post(url, formData, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,

        onSuccess: () => {
            forceCloseForm();
            closeAllModals();
        },

        onError: (serverErrors) => {
            errors.value = serverErrors as FormErrors;
        },

        onFinish: () => {
            processing.value = false;
        },
    });
};

/* -------------------------------------------------------------------------
 * Detail
 * ---------------------------------------------------------------------- */

const openDetail = (item: PeluangInvestasi): void => {
    selectedPeluangInvestasi.value = item;

    closeAllModals();
    showDetailModal.value = true;
};

const closeDetail = (): void => {
    showDetailModal.value = false;
    selectedPeluangInvestasi.value = null;
};

interface DetailField {
    label: string;
    value: string;
    icon: Component;
    mono?: boolean;
}

const detailFields = computed<DetailField[]>(() => {
    const item = selectedPeluangInvestasi.value;

    if (!item) return [];

    return [
        { label: "Slug", value: `/ ${item.slug}`, icon: FileText, mono: true },
        {
            label: "Sektor Industri",
            value: item.sektor_industri || "-",
            icon: BriefcaseBusiness,
        },
        { label: "Lokasi", value: item.lokasi || "-", icon: MapPin },
        { label: "Urutan", value: String(item.urutan), icon: Hash },
        {
            label: "Luas Lahan",
            value: `${formatNumber(item.luas_lahan)} ${item.satuan_luas || "Ha"}`,
            icon: Ruler,
        },
        {
            label: "Nilai Investasi",
            value: formatInvestment(item.nilai_investasi, item.mata_uang),
            icon: BriefcaseBusiness,
        },
    ];
});

/* -------------------------------------------------------------------------
 * Delete
 * ---------------------------------------------------------------------- */

const openDelete = (item: PeluangInvestasi): void => {
    if (processingDelete.value || processing.value) return;

    selectedPeluangInvestasi.value = item;

    closeAllModals();
    showDeleteModal.value = true;
};

const closeDelete = (): void => {
    if (processingDelete.value) return;

    showDeleteModal.value = false;
    selectedPeluangInvestasi.value = null;
};

const deletePeluangInvestasi = (): void => {
    if (!selectedPeluangInvestasi.value || processingDelete.value) return;

    processingDelete.value = true;

    router.delete(`${BASE_URL}/${selectedPeluangInvestasi.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDeleteModal.value = false;
            selectedPeluangInvestasi.value = null;
        },

        onError: (serverErrors) => {
            console.error("Gagal menghapus peluang investasi:", serverErrors);
        },

        onFinish: () => {
            processingDelete.value = false;
        },
    });
};

/* -------------------------------------------------------------------------
 * Toggle aktif & urutan
 * ---------------------------------------------------------------------- */

const toggleAktif = (item: PeluangInvestasi): void => {
    if (processingToggleId.value !== null) return;

    processingToggleId.value = item.id;

    router.patch(
        `${BASE_URL}/${item.id}/toggle-aktif`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onError: (serverErrors) => {
                console.error("Gagal mengubah status aktif:", serverErrors);
            },
            onFinish: () => {
                processingToggleId.value = null;
            },
        },
    );
};

const movePeluangInvestasi = (
    item: PeluangInvestasi,
    direction: "up" | "down",
): void => {
    if (
        processingMoveId.value !== null ||
        processing.value ||
        isFiltered.value
    ) {
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
                console.error("Gagal mengubah urutan:", serverErrors);
            },
            onFinish: () => {
                processingMoveId.value = null;
            },
        },
    );
};

/* -------------------------------------------------------------------------
 * Keyboard (Esc) & body scroll lock
 * ---------------------------------------------------------------------- */

const isAnyModalOpen = computed(
    () => showFormModal.value || showDetailModal.value || showDeleteModal.value,
);

const handleKeydown = (event: KeyboardEvent): void => {
    if (event.key !== "Escape") return;

    if (showDeleteModal.value) closeDelete();
    else if (showFormModal.value) closeForm();
    else if (showDetailModal.value) closeDetail();
};

watch(isAnyModalOpen, (open) => {
    document.body.style.overflow = open ? "hidden" : "";
});

/* -------------------------------------------------------------------------
 * Lifecycle
 * ---------------------------------------------------------------------- */

onMounted(() => {
    removeRouterStartListener = router.on("start", (event) => {
        const visit = event.detail.visit;

        // Skeleton hanya untuk navigasi GET penuh (bukan aksi form/toggle)
        if (visit.method === "get" && !visit.preserveState) {
            isPageLoading.value = true;
        }
    });

    removeRouterFinishListener = router.on("finish", () => {
        isPageLoading.value = false;
    });

    window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
    removeRouterStartListener?.();
    removeRouterFinishListener?.();

    window.removeEventListener("keydown", handleKeydown);
    document.body.style.overflow = "";

    clearSearchTimer();
    revokePreview();
});
</script>

<template>
    <div
        class="relative min-h-full overflow-hidden bg-slate-50 transition-colors duration-300 dark:bg-[#07111f]"
        :aria-busy="isPageLoading ? 'true' : 'false'"
    >
        <!-- DECORATIVE BACKGROUND -->
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
                class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent via-slate-50/40 to-slate-50/90 dark:via-slate-950/40 dark:to-[#07111f]/95"
            ></div>
        </div>

        <!-- CONTENT -->
        <div
            class="relative z-10 mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <!-- SKELETON -->
            <div v-if="isPageLoading" class="animate-pulse" role="status">
                <span class="sr-only">Memuat data...</span>

                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="size-10 rounded-xl bg-slate-200 dark:bg-slate-800"
                        ></div>
                        <div class="space-y-2">
                            <div
                                class="h-5 w-48 rounded-md bg-slate-200 dark:bg-slate-800"
                            ></div>
                            <div
                                class="h-4 w-72 max-w-full rounded-md bg-slate-200 dark:bg-slate-800"
                            ></div>
                        </div>
                    </div>
                    <div
                        class="h-11 w-full rounded-xl bg-slate-200 sm:w-48 dark:bg-slate-800"
                    ></div>
                </div>

                <div :class="[cardClass, 'mb-5 p-4 sm:p-5']">
                    <div class="grid gap-3 md:grid-cols-[1fr_220px]">
                        <div
                            class="h-11 rounded-xl bg-slate-200 dark:bg-slate-800"
                        ></div>
                        <div
                            class="h-11 rounded-xl bg-slate-200 dark:bg-slate-800"
                        ></div>
                    </div>
                </div>

                <div :class="[cardClass, 'overflow-hidden']">
                    <div class="space-y-4 p-6">
                        <div
                            v-for="row in 6"
                            :key="row"
                            class="flex items-center gap-4"
                        >
                            <div
                                class="size-14 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"
                            ></div>
                            <div class="flex-1 space-y-2">
                                <div
                                    class="h-4 w-1/3 rounded bg-slate-200 dark:bg-slate-800"
                                ></div>
                                <div
                                    class="h-3 w-1/5 rounded bg-slate-200 dark:bg-slate-800"
                                ></div>
                            </div>
                            <div
                                class="h-4 w-24 rounded bg-slate-200 dark:bg-slate-800"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTUAL CONTENT -->
            <template v-else>
                <!-- HEADER -->
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex min-w-0 items-start gap-3 sm:items-center">
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-600/10 text-blue-600 shadow-sm dark:bg-blue-400/10 dark:text-blue-400"
                        >
                            <BriefcaseBusiness class="size-5" />
                        </div>

                        <div class="min-w-0">
                            <h1
                                class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                            >
                                Peluang Investasi
                            </h1>
                            <p
                                class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                            >
                                Kelola informasi peluang investasi yang tersedia
                                di kawasan.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        :class="[
                            primaryBtnClass,
                            'hover:shadow-md active:scale-[0.99]',
                        ]"
                        @click="openCreate"
                    >
                        <Plus class="size-4" />
                        Tambah Peluang Investasi
                    </button>
                </div>

                <!-- FILTER -->
                <div :class="[cardClass, 'mb-5 p-4 sm:p-5']">
                    <div class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="search"
                                type="text"
                                aria-label="Cari peluang investasi"
                                placeholder="Cari judul, sektor industri, atau lokasi..."
                                :class="[inputClass, 'py-2.5 pl-10 pr-11']"
                                @input="submitSearch"
                                @keydown.enter.prevent="submitFilter"
                            />

                            <button
                                v-if="search"
                                type="button"
                                title="Hapus pencarian"
                                aria-label="Hapus pencarian"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:hover:bg-slate-700 dark:hover:text-slate-200"
                                @click="clearSearchInput"
                            >
                                <X class="size-4" />
                            </button>
                        </div>

                        <select
                            v-model="selectedStatus"
                            aria-label="Filter status"
                            :class="inputClass"
                            @change="submitFilter"
                        >
                            <option value="">Semua status</option>
                            <option
                                v-for="status in statusOptions"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.label }}
                            </option>
                        </select>

                        <button
                            v-if="search || selectedStatus"
                            type="button"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="clearFilters"
                        >
                            <X class="size-4" />
                            Reset
                        </button>
                    </div>

                    <p
                        v-if="isFiltered"
                        class="mt-3 text-xs text-slate-500 dark:text-slate-400"
                    >
                        Pengaturan urutan dinonaktifkan saat filter aktif.
                    </p>
                </div>

                <!-- TABLE CARD -->
                <div :class="[cardClass, 'overflow-hidden']">
                    <div
                        class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5 md:flex-row md:items-center md:justify-between dark:border-slate-800"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
                            >
                                <BriefcaseBusiness class="size-5" />
                            </div>

                            <div class="min-w-0">
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Data Peluang Investasi
                                </h2>
                                <p
                                    class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    Informasi peluang investasi dan ketersediaan
                                    lahan.
                                </p>
                            </div>
                        </div>

                        <span
                            class="w-fit rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{ total }} data
                        </span>
                    </div>

                    <!-- DATA -->
                    <div v-if="items.length > 0">
                        <div class="overflow-x-auto overscroll-x-contain">
                            <table
                                class="w-full min-w-[1550px] text-left text-sm"
                            >
                                <thead
                                    class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                                >
                                    <tr>
                                        <th
                                            v-for="col in columns"
                                            :key="col.label"
                                            scope="col"
                                            :class="[
                                                thClass,
                                                col.width,
                                                col.align,
                                            ]"
                                        >
                                            {{ col.label }}
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800"
                                >
                                    <tr
                                        v-for="(item, index) in items"
                                        :key="item.id"
                                        class="group transition-colors duration-200 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                                    >
                                        <!-- NO -->
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex size-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                            >
                                                {{ getRowNumber(index) }}
                                            </span>
                                        </td>

                                        <!-- PELUANG -->
                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-sm dark:border-slate-700 dark:bg-slate-800"
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
                                                        loading="lazy"
                                                        class="size-full object-cover transition duration-300 group-hover:scale-105"
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
                                                        / {{ item.slug }}
                                                    </p>
                                                    <p
                                                        class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                                    >
                                                        ID #{{ item.id }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- SEKTOR -->
                                        <td class="px-6 py-4">
                                            <p
                                                class="font-medium text-slate-700 dark:text-slate-200"
                                            >
                                                {{
                                                    item.sektor_industri || "-"
                                                }}
                                            </p>
                                            <p
                                                v-if="item.lokasi"
                                                class="mt-1 flex items-center gap-1 text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                <MapPin
                                                    class="size-3.5 shrink-0"
                                                />
                                                {{ item.lokasi }}
                                            </p>
                                        </td>

                                        <!-- LAHAN -->
                                        <td class="px-6 py-4">
                                            <p
                                                class="font-semibold text-slate-800 dark:text-slate-100"
                                            >
                                                {{
                                                    formatNumber(
                                                        item.luas_lahan,
                                                    )
                                                }}
                                                {{ item.satuan_luas || "Ha" }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                Luas lahan
                                            </p>
                                        </td>

                                        <!-- NILAI -->
                                        <td class="px-6 py-4">
                                            <p
                                                class="font-semibold text-slate-800 dark:text-slate-100"
                                            >
                                                {{
                                                    formatInvestment(
                                                        item.nilai_investasi,
                                                        item.mata_uang,
                                                    )
                                                }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                Nilai investasi
                                            </p>
                                        </td>

                                        <!-- STATUS -->
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium"
                                                :class="
                                                    statusClass(item.status)
                                                "
                                            >
                                                {{ getStatusLabel(item) }}
                                            </span>
                                        </td>

                                        <!-- URUTAN -->
                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center justify-center gap-1"
                                            >
                                                <button
                                                    type="button"
                                                    title="Pindah ke atas"
                                                    aria-label="Pindah peluang investasi ke atas"
                                                    :disabled="
                                                        processingMoveId !==
                                                            null ||
                                                        isFiltered ||
                                                        isFirstItem(index)
                                                    "
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400',
                                                    ]"
                                                    @click="
                                                        movePeluangInvestasi(
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
                                                    aria-label="Pindah peluang investasi ke bawah"
                                                    :disabled="
                                                        processingMoveId !==
                                                            null ||
                                                        isFiltered ||
                                                        isLastItem(index)
                                                    "
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400',
                                                    ]"
                                                    @click="
                                                        movePeluangInvestasi(
                                                            item,
                                                            'down',
                                                        )
                                                    "
                                                >
                                                    <ArrowDown class="size-4" />
                                                </button>
                                            </div>
                                        </td>

                                        <!-- AKTIF -->
                                        <td class="px-6 py-4 text-center">
                                            <button
                                                type="button"
                                                :disabled="
                                                    processingToggleId ===
                                                    item.id
                                                "
                                                :aria-pressed="item.aktif"
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
                                                    aria-label="Lihat detail peluang investasi"
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400',
                                                    ]"
                                                    @click="openDetail(item)"
                                                >
                                                    <Eye class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Edit peluang investasi"
                                                    aria-label="Edit peluang investasi"
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400',
                                                    ]"
                                                    @click="openEdit(item)"
                                                >
                                                    <Pencil class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Hapus peluang investasi"
                                                    aria-label="Hapus peluang investasi"
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400',
                                                    ]"
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
                        <nav
                            v-if="lastPage > 1"
                            aria-label="Paginasi"
                            class="flex flex-col gap-4 border-t border-slate-200 px-4 py-4 sm:px-6 md:flex-row md:items-center md:justify-between dark:border-slate-800"
                        >
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Menampilkan
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                    >{{ fromRow }}</span
                                >
                                -
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                    >{{ toRow }}</span
                                >
                                dari
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                    >{{ total }}</span
                                >
                                peluang investasi
                            </p>

                            <div
                                class="flex w-full flex-wrap items-center gap-1 sm:w-auto"
                            >
                                <button
                                    type="button"
                                    title="Halaman pertama"
                                    aria-label="Halaman pertama"
                                    :disabled="!firstPageUrl"
                                    class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="navButtonClass(firstPageUrl)"
                                    @click="goToPage(firstPageUrl)"
                                >
                                    <ChevronsLeft class="size-4" />
                                </button>

                                <button
                                    type="button"
                                    title="Halaman sebelumnya"
                                    aria-label="Halaman sebelumnya"
                                    :disabled="!previousPageUrl"
                                    class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="navButtonClass(previousPageUrl)"
                                    @click="goToPage(previousPageUrl)"
                                >
                                    <ChevronLeft class="size-4" />
                                </button>

                                <button
                                    v-for="(link, index) in pageLinks"
                                    :key="`${link.label}-${index}`"
                                    type="button"
                                    :disabled="!link.url || link.active"
                                    :aria-current="
                                        link.active ? 'page' : undefined
                                    "
                                    class="min-w-9 rounded-lg px-3 py-2 text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="
                                        link.active
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : navButtonClass(link.url)
                                    "
                                    @click="goToPage(link.url)"
                                >
                                    {{ paginationPageLabel(link.label) || "…" }}
                                </button>

                                <button
                                    type="button"
                                    title="Halaman berikutnya"
                                    aria-label="Halaman berikutnya"
                                    :disabled="!nextPageUrl"
                                    class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="navButtonClass(nextPageUrl)"
                                    @click="goToPage(nextPageUrl)"
                                >
                                    <ChevronRight class="size-4" />
                                </button>

                                <button
                                    type="button"
                                    title="Halaman terakhir"
                                    aria-label="Halaman terakhir"
                                    :disabled="!lastPageUrl"
                                    class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="navButtonClass(lastPageUrl)"
                                    @click="goToPage(lastPageUrl)"
                                >
                                    <ChevronsRight class="size-4" />
                                </button>
                            </div>
                        </nav>
                    </div>

                    <!-- EMPTY -->
                    <div v-else class="px-4 py-16 text-center sm:px-6">
                        <div
                            class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-500 dark:bg-blue-950/30 dark:text-blue-400"
                        >
                            <BriefcaseBusiness class="size-7" />
                        </div>

                        <p
                            class="mt-4 font-semibold text-slate-800 dark:text-slate-200"
                        >
                            {{
                                search || selectedStatus
                                    ? "Peluang investasi tidak ditemukan"
                                    : "Belum ada peluang investasi"
                            }}
                        </p>

                        <p
                            class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{
                                search || selectedStatus
                                    ? "Tidak ditemukan data yang sesuai dengan filter."
                                    : "Tambahkan data peluang investasi untuk mulai mengelola informasi investasi kawasan."
                            }}
                        </p>

                        <button
                            v-if="!search && !selectedStatus"
                            type="button"
                            :class="[primaryBtnClass, 'mt-5']"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />
                            Tambah Peluang Investasi
                        </button>

                        <button
                            v-else
                            type="button"
                            :class="[
                                secondaryBtnClass,
                                'mt-5 inline-flex items-center justify-center gap-2',
                            ]"
                            @click="clearFilters"
                        >
                            <X class="size-4" />
                            Bersihkan Filter
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- CREATE / EDIT MODAL -->
        <Transition name="modal">
            <div
                v-if="showFormModal"
                :class="modalBackdropClass"
                @mousedown.self="closeForm"
            >
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="form-modal-title"
                    :class="[modalCardClass, 'max-w-4xl']"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                id="form-modal-title"
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    modalMode === "create"
                                        ? "Tambah Peluang Investasi"
                                        : "Edit Peluang Investasi"
                                }}
                            </h2>
                            <p
                                class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    modalMode === "create"
                                        ? "Tambahkan informasi peluang investasi baru."
                                        : "Perbarui informasi peluang investasi."
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            aria-label="Tutup modal"
                            :disabled="processing"
                            :class="closeBtnClass"
                            @click="closeForm"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <form
                        class="max-h-[calc(100vh-7rem)] overflow-y-auto sm:max-h-[calc(100vh-8rem)]"
                        novalidate
                        @submit.prevent="submit"
                    >
                        <div class="grid gap-5 p-4 sm:p-6 md:grid-cols-2">
                            <!-- JUDUL -->
                            <div class="md:col-span-2">
                                <label for="f-judul" :class="labelClass">
                                    Judul Peluang Investasi
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="f-judul"
                                    v-model="form.judul"
                                    type="text"
                                    maxlength="255"
                                    required
                                    placeholder="Contoh: Lahan Industri Tahap 1"
                                    :class="[
                                        inputClass,
                                        errors.judul && inputErrorClass,
                                    ]"
                                />
                                <p v-if="errors.judul" :class="errorClass">
                                    {{ errors.judul }}
                                </p>
                            </div>

                            <!-- SLUG -->
                            <div class="md:col-span-2">
                                <label for="f-slug" :class="labelClass"
                                    >Slug</label
                                >
                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"
                                    >
                                        /
                                    </span>
                                    <input
                                        id="f-slug"
                                        v-model="form.slug"
                                        type="text"
                                        readonly
                                        disabled
                                        tabindex="-1"
                                        placeholder="slug-otomatis"
                                        class="min-h-11 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 py-2.5 pl-8 pr-4 text-sm text-slate-500 outline-none dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-400"
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
                                        berdasarkan judul. Slug final dapat
                                        berbeda jika sudah digunakan.
                                    </span>
                                </p>
                            </div>

                            <!-- SEKTOR -->
                            <div>
                                <label for="f-sektor" :class="labelClass"
                                    >Sektor Industri</label
                                >
                                <input
                                    id="f-sektor"
                                    v-model="form.sektor_industri"
                                    type="text"
                                    maxlength="255"
                                    placeholder="Contoh: Industri Hilirisasi"
                                    :class="[
                                        inputClass,
                                        errors.sektor_industri &&
                                            inputErrorClass,
                                    ]"
                                />
                                <p
                                    v-if="errors.sektor_industri"
                                    :class="errorClass"
                                >
                                    {{ errors.sektor_industri }}
                                </p>
                            </div>

                            <!-- STATUS -->
                            <div>
                                <label for="f-status" :class="labelClass"
                                    >Status Investasi</label
                                >
                                <select
                                    id="f-status"
                                    v-model="form.status"
                                    :class="[
                                        inputClass,
                                        errors.status && inputErrorClass,
                                    ]"
                                >
                                    <option
                                        v-for="status in statusOptions"
                                        :key="status.value"
                                        :value="status.value"
                                    >
                                        {{ status.label }}
                                    </option>
                                </select>
                                <p v-if="errors.status" :class="errorClass">
                                    {{ errors.status }}
                                </p>
                            </div>

                            <!-- LUAS -->
                            <div>
                                <label for="f-luas" :class="labelClass"
                                    >Luas Lahan</label
                                >
                                <div class="flex gap-2">
                                    <input
                                        id="f-luas"
                                        v-model="form.luas_lahan"
                                        type="number"
                                        inputmode="decimal"
                                        min="0"
                                        step="0.01"
                                        placeholder="0"
                                        :class="[
                                            inputClass,
                                            'min-w-0 flex-1',
                                            errors.luas_lahan &&
                                                inputErrorClass,
                                        ]"
                                    />
                                    <input
                                        v-model="form.satuan_luas"
                                        type="text"
                                        maxlength="20"
                                        placeholder="Ha"
                                        aria-label="Satuan luas"
                                        :class="[
                                            smallInputClass,
                                            errors.satuan_luas &&
                                                inputErrorClass,
                                        ]"
                                    />
                                </div>
                                <p v-if="errors.luas_lahan" :class="errorClass">
                                    {{ errors.luas_lahan }}
                                </p>
                                <p
                                    v-if="errors.satuan_luas"
                                    :class="errorClass"
                                >
                                    {{ errors.satuan_luas }}
                                </p>
                            </div>

                            <!-- NILAI INVESTASI -->
                            <div>
                                <label for="f-nilai" :class="labelClass"
                                    >Nilai Investasi</label
                                >
                                <div class="flex gap-2">
                                    <input
                                        id="f-nilai"
                                        v-model="form.nilai_investasi"
                                        type="number"
                                        inputmode="decimal"
                                        min="0"
                                        step="0.01"
                                        placeholder="0"
                                        :class="[
                                            inputClass,
                                            'min-w-0 flex-1',
                                            errors.nilai_investasi &&
                                                inputErrorClass,
                                        ]"
                                    />
                                    <input
                                        v-model="form.mata_uang"
                                        type="text"
                                        maxlength="10"
                                        placeholder="IDR"
                                        aria-label="Mata uang"
                                        :class="[
                                            smallInputClass,
                                            'uppercase',
                                            errors.mata_uang && inputErrorClass,
                                        ]"
                                    />
                                </div>
                                <p
                                    v-if="errors.nilai_investasi"
                                    :class="errorClass"
                                >
                                    {{ errors.nilai_investasi }}
                                </p>
                                <p v-if="errors.mata_uang" :class="errorClass">
                                    {{ errors.mata_uang }}
                                </p>
                            </div>

                            <!-- LOKASI -->
                            <div class="md:col-span-2">
                                <label for="f-lokasi" :class="labelClass"
                                    >Lokasi</label
                                >
                                <input
                                    id="f-lokasi"
                                    v-model="form.lokasi"
                                    type="text"
                                    maxlength="255"
                                    placeholder="Contoh: Zona Industri Utama KITB"
                                    :class="[
                                        inputClass,
                                        errors.lokasi && inputErrorClass,
                                    ]"
                                />
                                <p v-if="errors.lokasi" :class="errorClass">
                                    {{ errors.lokasi }}
                                </p>
                            </div>

                            <!-- AKTIF -->
                            <div>
                                <span :class="labelClass"
                                    >Status Publikasi</span
                                >
                                <button
                                    type="button"
                                    role="switch"
                                    :aria-checked="form.aktif"
                                    class="flex min-h-11 w-full items-center justify-between gap-3 rounded-xl border px-4 py-2.5 text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="
                                        form.aktif
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-400'
                                            : 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                    @click="form.aktif = !form.aktif"
                                >
                                    <span class="flex items-center gap-2">
                                        <ToggleRight
                                            v-if="form.aktif"
                                            class="size-5 shrink-0"
                                        />
                                        <ToggleLeft
                                            v-else
                                            class="size-5 shrink-0"
                                        />
                                        {{
                                            form.aktif
                                                ? "Data Aktif"
                                                : "Data Nonaktif"
                                        }}
                                    </span>
                                    <span
                                        class="hidden text-xs opacity-70 sm:inline"
                                        >Klik untuk ubah</span
                                    >
                                </button>
                                <p v-if="errors.aktif" :class="errorClass">
                                    {{ errors.aktif }}
                                </p>
                            </div>

                            <!-- DESKRIPSI -->
                            <div class="md:col-span-2">
                                <label for="f-deskripsi" :class="labelClass"
                                    >Deskripsi</label
                                >
                                <textarea
                                    id="f-deskripsi"
                                    v-model="form.deskripsi"
                                    rows="6"
                                    maxlength="10000"
                                    placeholder="Tuliskan informasi lengkap mengenai peluang investasi..."
                                    :class="[
                                        inputClass,
                                        'resize-none leading-6',
                                        errors.deskripsi && inputErrorClass,
                                    ]"
                                ></textarea>
                                <p v-if="errors.deskripsi" :class="errorClass">
                                    {{ errors.deskripsi }}
                                </p>
                            </div>

                            <!-- GAMBAR -->
                            <div class="md:col-span-2">
                                <span :class="labelClass"
                                    >Gambar Peluang Investasi</span
                                >

                                <div
                                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-3 sm:p-4 dark:border-slate-700 dark:bg-slate-800/50"
                                >
                                    <div v-if="displayImage" class="mb-4">
                                        <div
                                            class="relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
                                        >
                                            <img
                                                :src="displayImage"
                                                alt="Preview gambar"
                                                class="max-h-64 w-full object-cover sm:max-h-80"
                                            />

                                            <button
                                                type="button"
                                                :title="
                                                    previewUrl
                                                        ? 'Hapus gambar baru'
                                                        : 'Hapus gambar'
                                                "
                                                :aria-label="
                                                    previewUrl
                                                        ? 'Hapus gambar baru'
                                                        : 'Hapus gambar'
                                                "
                                                class="absolute right-3 top-3 rounded-lg bg-red-600 p-2 text-white shadow-lg transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400/60"
                                                @click="
                                                    previewUrl
                                                        ? removeSelectedImage()
                                                        : removeExistingImage()
                                                "
                                            >
                                                <Trash2 class="size-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <label
                                        class="flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-7 text-center transition hover:border-blue-400 hover:bg-blue-50/50 focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-blue-600 dark:hover:bg-blue-950/20"
                                    >
                                        <ImageIcon
                                            class="size-8 text-slate-400"
                                        />
                                        <span
                                            class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            {{
                                                displayImage
                                                    ? "Ganti gambar"
                                                    : "Pilih gambar"
                                            }}
                                        </span>
                                        <span
                                            class="mt-1 max-w-sm text-xs leading-5 text-slate-400 dark:text-slate-500"
                                        >
                                            JPG, JPEG, PNG, WEBP — maksimal 1 MB
                                        </span>
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="sr-only"
                                            @change="handleImageChange"
                                        />
                                    </label>
                                </div>

                                <p v-if="errors.gambar" :class="errorClass">
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
                                :class="secondaryBtnClass"
                                @click="closeForm"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="processing"
                                :class="primaryBtnClass"
                            >
                                <span
                                    v-if="processing"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>
                                {{
                                    processing
                                        ? "Menyimpan..."
                                        : modalMode === "create"
                                          ? "Simpan Peluang Investasi"
                                          : "Simpan Perubahan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- DETAIL MODAL -->
        <Transition name="modal">
            <div
                v-if="showDetailModal && selectedPeluangInvestasi"
                :class="modalBackdropClass"
                @mousedown.self="closeDetail"
            >
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="detail-modal-title"
                    :class="[modalCardClass, 'max-w-3xl']"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                id="detail-modal-title"
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Peluang Investasi
                            </h2>
                            <p
                                class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap peluang investasi kawasan.
                            </p>
                        </div>

                        <button
                            type="button"
                            aria-label="Tutup detail"
                            :class="closeBtnClass"
                            @click="closeDetail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <div
                        class="max-h-[calc(100vh-8rem)] overflow-y-auto p-4 sm:p-6"
                    >
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                        >
                            <img
                                v-if="
                                    getImageUrl(selectedPeluangInvestasi.gambar)
                                "
                                :src="
                                    getImageUrl(
                                        selectedPeluangInvestasi.gambar,
                                    )!
                                "
                                :alt="selectedPeluangInvestasi.judul"
                                class="max-h-80 w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-52 items-center justify-center sm:h-56"
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

                        <div class="mt-5 flex flex-wrap items-center gap-2">
                            <h3
                                class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ selectedPeluangInvestasi.judul }}
                            </h3>

                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                :class="
                                    statusClass(selectedPeluangInvestasi.status)
                                "
                            >
                                {{ getStatusLabel(selectedPeluangInvestasi) }}
                            </span>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                                :class="
                                    selectedPeluangInvestasi.aktif
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                "
                            >
                                {{
                                    selectedPeluangInvestasi.aktif
                                        ? "Aktif"
                                        : "Nonaktif"
                                }}
                            </span>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="field in detailFields"
                                :key="field.label"
                                :class="infoCardClass"
                            >
                                <div class="flex items-center gap-2">
                                    <component
                                        :is="field.icon"
                                        class="size-4 text-blue-500"
                                    />
                                    <span
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ field.label }}
                                    </span>
                                </div>
                                <p
                                    class="mt-1 break-words font-semibold text-slate-800 dark:text-slate-100"
                                    :class="
                                        field.mono &&
                                        'break-all font-mono text-sm font-medium text-blue-600 dark:text-blue-400'
                                    "
                                >
                                    {{ field.value }}
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
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                            >
                                <p class="whitespace-pre-line break-words">
                                    {{
                                        selectedPeluangInvestasi.deskripsi ||
                                        "-"
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
                            :class="secondaryBtnClass"
                            @click="closeDetail"
                        >
                            Tutup
                        </button>
                        <button
                            type="button"
                            :class="primaryBtnClass"
                            @click="openEdit(selectedPeluangInvestasi)"
                        >
                            <Pencil class="size-4" />
                            Edit
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- DELETE MODAL -->
        <Transition name="modal">
            <div
                v-if="showDeleteModal && selectedPeluangInvestasi"
                :class="modalBackdropClass"
                @mousedown.self="closeDelete"
            >
                <div
                    role="alertdialog"
                    aria-modal="true"
                    aria-labelledby="delete-modal-title"
                    class="my-auto w-full max-w-md rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xl shadow-slate-900/10 sm:p-6 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/40"
                >
                    <div
                        class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                    >
                        <Trash2 class="size-5" />
                    </div>

                    <div class="mt-4 text-center">
                        <h2
                            id="delete-modal-title"
                            class="text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            Hapus Peluang Investasi?
                        </h2>
                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedPeluangInvestasi.judul }}
                            </span>
                            ? Data akan dihapus dari daftar peluang investasi.
                        </p>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            :disabled="processingDelete"
                            :class="secondaryBtnClass"
                            @click="closeDelete"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                            @click="deletePeluangInvestasi"
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

        <!-- PAGE LOADING BAR -->
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
    transition: opacity 0.2s ease;
}

.modal-enter-active > div,
.modal-leave-active > div {
    transition: transform 0.2s ease;
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
    .modal-enter-active > div,
    .modal-leave-active > div,
    .loading-enter-active,
    .loading-leave-active {
        transition: none;
    }
}
</style>
