<script setup lang="ts">
import {
    computed,
    nextTick,
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
    Award,
    BadgeCheck,
    Banknote,
    Building2,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    ClipboardCheck,
    Clock,
    Eye,
    FileCheck,
    FileText,
    Globe,
    Handshake,
    Hash,
    Headset,
    Landmark,
    Layers,
    Pencil,
    Plus,
    Rocket,
    Scale,
    Search,
    ShieldCheck,
    Stamp,
    ToggleLeft,
    ToggleRight,
    Trash2,
    TrendingUp,
    Users,
    Wallet,
    Workflow,
    X,
    Zap,
} from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

/* -------------------------------------------------------------------------
 * Interfaces
 * ---------------------------------------------------------------------- */

interface KemudahanBerusaha {
    id: number;
    judul: string;
    slug: string;
    ringkasan: string | null;
    deskripsi: string | null;
    ikon: string | null;
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

interface KemudahanBerusahaPagination {
    data: KemudahanBerusaha[];
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
    ringkasan?: string;
    deskripsi?: string;
    ikon?: string;
    aktif?: string;
}

interface IconOption {
    key: string;
    label: string;
    component: Component;
}

type FormState = {
    judul: string;
    slug: string;
    ringkasan: string;
    deskripsi: string;
    ikon: string;
    aktif: boolean;
};

/* -------------------------------------------------------------------------
 * Props & constants
 * ---------------------------------------------------------------------- */

const props = defineProps<{
    kemudahanBerusaha: KemudahanBerusahaPagination | null;
    filters?: {
        search?: string;
    };
}>();

/**
 * IMPORTANT:
 * Harus sama dengan route Laravel.
 */
const BASE_URL = "/hubungan-investor/ease-of-doing-business";

const iconOptions: IconOption[] = [
    { key: "file-text", label: "Dokumen", component: FileText },
    { key: "file-check", label: "Dokumen Sah", component: FileCheck },
    { key: "clipboard-check", label: "Persyaratan", component: ClipboardCheck },
    { key: "stamp", label: "Perizinan", component: Stamp },
    { key: "building-2", label: "Gedung", component: Building2 },
    { key: "landmark", label: "Pemerintah", component: Landmark },
    { key: "scale", label: "Hukum", component: Scale },
    { key: "handshake", label: "Kemitraan", component: Handshake },
    { key: "shield-check", label: "Jaminan", component: ShieldCheck },
    { key: "badge-check", label: "Terverifikasi", component: BadgeCheck },
    { key: "clock", label: "Waktu", component: Clock },
    { key: "zap", label: "Cepat", component: Zap },
    { key: "workflow", label: "Alur", component: Workflow },
    { key: "layers", label: "Lapisan", component: Layers },
    { key: "banknote", label: "Biaya", component: Banknote },
    { key: "wallet", label: "Insentif", component: Wallet },
    { key: "trending-up", label: "Pertumbuhan", component: TrendingUp },
    { key: "users", label: "Tenaga Kerja", component: Users },
    { key: "headset", label: "Layanan", component: Headset },
    { key: "globe", label: "Global", component: Globe },
    { key: "rocket", label: "Percepatan", component: Rocket },
    { key: "award", label: "Penghargaan", component: Award },
];

const iconMap: Record<string, Component> = Object.fromEntries(
    iconOptions.map((option) => [option.key, option.component]),
);

/* -------------------------------------------------------------------------
 * Shared classes
 * ---------------------------------------------------------------------- */

const inputClass =
    "min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900";

const inputErrorClass =
    "border-red-400 focus:border-red-500 dark:border-red-500";

const labelClass =
    "mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300";

const errorClass = "mt-1.5 text-xs text-red-500";

const cardClass =
    "rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/40 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20";

const modalBackdropClass =
    "fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/55 p-3 sm:p-4";

const modalCardClass =
    "my-auto flex max-h-[calc(100dvh-1.5rem)] w-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/40";

const modalHeaderClass =
    "flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800";

const modalFooterClass =
    "flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-slate-800";

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
    {
        label: "Kemudahan Berusaha",
        width: "w-[440px]",
        align: "text-left",
    },
    { label: "Urutan", width: "w-40", align: "text-center" },
    { label: "Aktif", width: "w-44", align: "text-center" },
    { label: "Aksi", width: "w-44", align: "text-right" },
];

/* -------------------------------------------------------------------------
 * Data
 * ---------------------------------------------------------------------- */

const items = computed<KemudahanBerusaha[]>(
    () => props.kemudahanBerusaha?.data ?? [],
);

const total = computed(
    () =>
        props.kemudahanBerusaha?.meta?.total ??
        props.kemudahanBerusaha?.total ??
        items.value.length,
);

const currentPage = computed(
    () =>
        props.kemudahanBerusaha?.meta?.current_page ??
        props.kemudahanBerusaha?.current_page ??
        1,
);

const perPage = computed(
    () =>
        props.kemudahanBerusaha?.meta?.per_page ??
        props.kemudahanBerusaha?.per_page ??
        10,
);

const lastPage = computed(
    () =>
        props.kemudahanBerusaha?.meta?.last_page ??
        props.kemudahanBerusaha?.last_page ??
        1,
);

const fromRow = computed(
    () =>
        props.kemudahanBerusaha?.meta?.from ??
        props.kemudahanBerusaha?.from ??
        0,
);

const toRow = computed(
    () => props.kemudahanBerusaha?.meta?.to ?? props.kemudahanBerusaha?.to ?? 0,
);

const getRowNumber = (index: number): number =>
    (currentPage.value - 1) * perPage.value + index + 1;

const isFiltered = computed(() => search.value.trim().length > 0);

const isFirstItem = (index: number): boolean => getRowNumber(index) <= 1;

const isLastItem = (index: number): boolean =>
    getRowNumber(index) >= total.value;

/* -------------------------------------------------------------------------
 * Loading
 * ---------------------------------------------------------------------- */

const isPageLoading = ref(false);

let removeRouterStartListener: (() => void) | null = null;
let removeRouterFinishListener: (() => void) | null = null;

/* -------------------------------------------------------------------------
 * Helpers
 * ---------------------------------------------------------------------- */

const toStringValue = (value: unknown): string => {
    if (value === null || value === undefined) {
        return "";
    }

    if (typeof value === "string") {
        return value.trim();
    }

    if (typeof value === "number" || typeof value === "boolean") {
        return String(value).trim();
    }

    return "";
};

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

const resolveIcon = (key: string | null | undefined): Component =>
    iconMap[toStringValue(key)] ?? FileText;

const getIconLabel = (key: string | null | undefined): string =>
    iconOptions.find((option) => option.key === toStringValue(key))?.label ??
    "Belum dipilih";

/* -------------------------------------------------------------------------
 * Search
 * ---------------------------------------------------------------------- */

const search = ref(props.filters?.search ?? "");

let searchTimer: ReturnType<typeof setTimeout> | null = null;

const clearSearchTimer = (): void => {
    if (searchTimer !== null) {
        clearTimeout(searchTimer);
        searchTimer = null;
    }
};

const visitList = (): void => {
    const query = search.value.trim();

    router.get(
        BASE_URL,
        query
            ? {
                  search: query,
              }
            : {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const submitSearch = (): void => {
    clearSearchTimer();

    searchTimer = setTimeout(() => {
        visitList();
        searchTimer = null;
    }, 350);
};

const submitFilter = (): void => {
    clearSearchTimer();
    visitList();
};

const clearSearchInput = (): void => {
    clearSearchTimer();

    search.value = "";

    visitList();
};

watch(
    () => props.filters?.search,
    (value) => {
        const incoming = value ?? "";

        if (incoming !== search.value) {
            search.value = incoming;
        }
    },
);

/* -------------------------------------------------------------------------
 * Pagination
 * ---------------------------------------------------------------------- */

const paginationLinks = computed<PaginationLink[]>(
    () =>
        props.kemudahanBerusaha?.meta?.links ??
        props.kemudahanBerusaha?.links ??
        [],
);

const pageLinks = computed(() => paginationLinks.value.slice(1, -1));

const paginationPageLabel = (label: string): string =>
    label
        .replace(/&laquo;|&raquo;/g, "")
        .replace(/previous|next/gi, "")
        .trim();

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

const firstPageUrl = computed<string | null>(() => {
    if (currentPage.value <= 1) {
        return null;
    }

    return paginationLinks.value[1]?.url ?? null;
});

const lastPageUrl = computed<string | null>(() => {
    if (currentPage.value >= lastPage.value) {
        return null;
    }

    return paginationLinks.value[paginationLinks.value.length - 2]?.url ?? null;
});

const previousPageUrl = computed<string | null>(
    () => paginationLinks.value[0]?.url ?? null,
);

const nextPageUrl = computed<string | null>(
    () => paginationLinks.value[paginationLinks.value.length - 1]?.url ?? null,
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

const selectedItem = ref<KemudahanBerusaha | null>(null);

const emptyForm = (): FormState => ({
    judul: "",
    slug: "",
    ringkasan: "",
    deskripsi: "",
    ikon: "",
    aktif: true,
});

const form = ref<FormState>(emptyForm());

const processing = ref(false);
const processingDelete = ref(false);
const processingToggleId = ref<number | null>(null);
const processingMoveId = ref<number | null>(null);

const errors = ref<FormErrors>({});

const isHydratingForm = ref(false);

watch(
    () => form.value.judul,
    (value) => {
        if (isHydratingForm.value || modalMode.value === "edit") {
            return;
        }

        form.value.slug = slugify(value);
    },
);

const hydrateForm = async (data: FormState): Promise<void> => {
    isHydratingForm.value = true;

    form.value = data;

    await nextTick();

    isHydratingForm.value = false;
};

const resetForm = (): void => {
    isHydratingForm.value = true;

    form.value = emptyForm();

    isHydratingForm.value = false;

    errors.value = {};
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
    if (processing.value || processingDelete.value) {
        return;
    }

    modalMode.value = "create";
    selectedItem.value = null;

    resetForm();
    closeAllModals();

    showFormModal.value = true;
};

const openEdit = (item: KemudahanBerusaha): void => {
    if (processing.value || processingDelete.value) {
        return;
    }

    modalMode.value = "edit";
    selectedItem.value = item;
    errors.value = {};

    void hydrateForm({
        judul: toStringValue(item.judul),
        slug: toStringValue(item.slug),
        ringkasan: toStringValue(item.ringkasan),
        deskripsi: toStringValue(item.deskripsi),
        ikon: toStringValue(item.ikon),
        aktif: Boolean(item.aktif),
    });

    closeAllModals();

    showFormModal.value = true;
};

const closeForm = (): void => {
    if (processing.value) {
        return;
    }

    showFormModal.value = false;
    selectedItem.value = null;

    resetForm();
};

const forceCloseForm = (): void => {
    showFormModal.value = false;
    selectedItem.value = null;

    resetForm();
};

const selectIcon = (key: string): void => {
    form.value.ikon = form.value.ikon === key ? "" : key;

    errors.value.ikon = undefined;
};

/* -------------------------------------------------------------------------
 * Submit
 * ---------------------------------------------------------------------- */

const submit = (): void => {
    if (processing.value) {
        return;
    }

    errors.value = {};

    const judul = toStringValue(form.value.judul);

    if (!judul) {
        errors.value.judul = "Judul wajib diisi.";

        return;
    }

    if (!slugify(judul)) {
        errors.value.judul =
            "Judul harus mengandung huruf atau angka agar slug dapat dibuat.";

        return;
    }

    const isEdit = modalMode.value === "edit" && selectedItem.value !== null;

    const id = selectedItem.value?.id;

    if (isEdit && !id) {
        return;
    }

    const url = isEdit && id ? `${BASE_URL}/${id}` : BASE_URL;

    const payload = {
        judul,
        ringkasan: toStringValue(form.value.ringkasan) || null,
        deskripsi: toStringValue(form.value.deskripsi) || null,
        ikon: toStringValue(form.value.ikon) || null,
        aktif: form.value.aktif,
    };

    processing.value = true;

    const options = {
        preserveScroll: true,

        onSuccess: () => {
            forceCloseForm();
        },

        onError: (serverErrors: Record<string, string>) => {
            errors.value = serverErrors as FormErrors;

            showFormModal.value = true;
        },

        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEdit) {
        router.put(url, payload, options);
    } else {
        router.post(url, payload, options);
    }
};

/* -------------------------------------------------------------------------
 * Detail
 * ---------------------------------------------------------------------- */

const openDetail = (item: KemudahanBerusaha): void => {
    selectedItem.value = item;

    closeAllModals();

    showDetailModal.value = true;
};

const closeDetail = (): void => {
    showDetailModal.value = false;
    selectedItem.value = null;
};

interface DetailField {
    label: string;
    value: string;
    icon: Component;
    mono?: boolean;
}

const detailFields = computed<DetailField[]>(() => {
    const item = selectedItem.value;

    if (!item) {
        return [];
    }

    return [
        {
            label: "Slug",
            value: `/${toStringValue(item.slug)}`,
            icon: FileText,
            mono: true,
        },
        {
            label: "Urutan",
            value: String(item.urutan),
            icon: Hash,
        },
        {
            label: "Ikon",
            value: getIconLabel(item.ikon),
            icon: resolveIcon(item.ikon),
        },
    ];
});

/* -------------------------------------------------------------------------
 * Delete
 * ---------------------------------------------------------------------- */

const openDelete = (item: KemudahanBerusaha): void => {
    if (processingDelete.value || processing.value) {
        return;
    }

    selectedItem.value = item;

    closeAllModals();

    showDeleteModal.value = true;
};

const closeDelete = (): void => {
    if (processingDelete.value) {
        return;
    }

    showDeleteModal.value = false;
    selectedItem.value = null;
};

const deleteItem = (): void => {
    const item = selectedItem.value;

    if (!item || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(`${BASE_URL}/${item.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDeleteModal.value = false;
            selectedItem.value = null;
        },

        onError: (serverErrors) => {
            console.error("Gagal menghapus kemudahan berusaha:", serverErrors);
        },

        onFinish: () => {
            processingDelete.value = false;
        },
    });
};

/* -------------------------------------------------------------------------
 * Toggle & ordering
 * ---------------------------------------------------------------------- */

const toggleAktif = (item: KemudahanBerusaha): void => {
    if (
        processingToggleId.value !== null ||
        processing.value ||
        processingDelete.value
    ) {
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
                console.error("Gagal mengubah status aktif:", serverErrors);
            },

            onFinish: () => {
                processingToggleId.value = null;
            },
        },
    );
};

const moveItem = (item: KemudahanBerusaha, direction: "up" | "down"): void => {
    if (
        processingMoveId.value !== null ||
        processing.value ||
        processingDelete.value ||
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
 * Modal / keyboard
 * ---------------------------------------------------------------------- */

const isAnyModalOpen = computed(
    () => showFormModal.value || showDetailModal.value || showDeleteModal.value,
);

const handleKeydown = (event: KeyboardEvent): void => {
    if (event.key !== "Escape" || !isAnyModalOpen.value) {
        return;
    }

    if (showDeleteModal.value) {
        closeDelete();
    } else if (showFormModal.value) {
        closeForm();
    } else if (showDetailModal.value) {
        closeDetail();
    }
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
            />

            <div
                class="blob-shape-delayed absolute -right-20 top-0 h-80 w-80 rounded-full bg-gradient-to-tr from-sky-300/30 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/20 dark:via-blue-500/10 dark:to-transparent"
            />

            <div
                class="blob-shape-slow absolute left-[30%] -top-40 h-72 w-72 rounded-full bg-gradient-to-br from-indigo-300/25 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/15 dark:via-blue-500/10 dark:to-transparent"
            />

            <div
                class="blob-shape absolute -bottom-40 right-[20%] h-72 w-72 rounded-full bg-gradient-to-br from-cyan-300/20 via-blue-300/10 to-transparent blur-3xl dark:from-cyan-500/10 dark:via-blue-500/10 dark:to-transparent"
            />

            <div class="absolute inset-0 opacity-40 dark:opacity-20">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px]"
                />
            </div>

            <div
                class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent via-slate-50/40 to-slate-50/90 dark:via-slate-950/40 dark:to-[#07111f]/95"
            />
        </div>

        <!-- CONTENT -->
        <div
            class="relative z-10 mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <!-- SKELETON -->
            <div v-if="isPageLoading" class="animate-pulse" role="status">
                <span class="sr-only"> Memuat data... </span>

                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="size-10 rounded-xl bg-slate-200 dark:bg-slate-800"
                        />

                        <div class="space-y-2">
                            <div
                                class="h-5 w-48 rounded-md bg-slate-200 dark:bg-slate-800"
                            />
                            <div
                                class="h-4 w-72 max-w-full rounded-md bg-slate-200 dark:bg-slate-800"
                            />
                        </div>
                    </div>

                    <div
                        class="h-11 w-full rounded-xl bg-slate-200 sm:w-48 dark:bg-slate-800"
                    />
                </div>

                <div :class="[cardClass, 'mb-5 p-4 sm:p-5']">
                    <div
                        class="h-11 rounded-xl bg-slate-200 dark:bg-slate-800"
                    />
                </div>

                <div :class="[cardClass, 'overflow-hidden']">
                    <div class="space-y-4 p-6">
                        <div
                            v-for="row in 6"
                            :key="row"
                            class="flex items-center gap-4"
                        >
                            <div
                                class="size-12 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="flex-1 space-y-2">
                                <div
                                    class="h-4 w-1/3 rounded bg-slate-200 dark:bg-slate-800"
                                />
                                <div
                                    class="h-3 w-1/2 rounded bg-slate-200 dark:bg-slate-800"
                                />
                            </div>

                            <div
                                class="h-4 w-24 rounded bg-slate-200 dark:bg-slate-800"
                            />
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
                            <Stamp class="size-5" />
                        </div>

                        <div class="min-w-0">
                            <h1
                                class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                            >
                                Kemudahan Berusaha
                            </h1>

                            <p
                                class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                            >
                                Kelola informasi kemudahan berusaha yang
                                ditampilkan kepada calon investor.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        :disabled="processing || processingDelete"
                        :class="[
                            primaryBtnClass,
                            'hover:shadow-md active:scale-[0.99]',
                        ]"
                        @click="openCreate"
                    >
                        <Plus class="size-4" />
                        Tambah Kemudahan Berusaha
                    </button>
                </div>

                <!-- FILTER -->
                <div :class="[cardClass, 'mb-5 p-4 sm:p-5']">
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="text"
                            aria-label="Cari kemudahan berusaha"
                            placeholder="Cari judul atau ringkasan..."
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

                    <p
                        v-if="isFiltered"
                        class="mt-3 text-xs text-slate-500 dark:text-slate-400"
                    >
                        Pengaturan urutan dinonaktifkan saat pencarian aktif.
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
                                <Stamp class="size-5" />
                            </div>

                            <div class="min-w-0">
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Data Kemudahan Berusaha
                                </h2>

                                <p
                                    class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    Daftar layanan dan kemudahan bagi investor.
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
                                class="w-full min-w-[980px] text-left text-sm"
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

                                        <!-- JUDUL -->
                                        <td class="px-6 py-4">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="flex size-12 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-400"
                                                >
                                                    <component
                                                        :is="
                                                            resolveIcon(
                                                                item.ikon,
                                                            )
                                                        "
                                                        class="size-6"
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
                                                        /
                                                        {{ item.slug }}
                                                    </p>

                                                    <p
                                                        v-if="item.ringkasan"
                                                        class="mt-1 line-clamp-2 max-w-md text-xs leading-5 text-slate-500 dark:text-slate-400"
                                                    >
                                                        {{ item.ringkasan }}
                                                    </p>
                                                </div>
                                            </div>
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
                                                        isFiltered ||
                                                        isFirstItem(index)
                                                    "
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400',
                                                    ]"
                                                    @click="
                                                        moveItem(item, 'up')
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
                                                        isFiltered ||
                                                        isLastItem(index)
                                                    "
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400',
                                                    ]"
                                                    @click="
                                                        moveItem(item, 'down')
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
                                                />

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
                                                    title="Edit"
                                                    aria-label="Edit kemudahan berusaha"
                                                    :disabled="
                                                        processing ||
                                                        processingDelete
                                                    "
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
                                                    title="Hapus"
                                                    aria-label="Hapus kemudahan berusaha"
                                                    :disabled="
                                                        processing ||
                                                        processingDelete
                                                    "
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
                                >
                                    {{ fromRow }}
                                </span>
                                -
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ toRow }}
                                </span>
                                dari
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ total }}
                                </span>
                                data
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
                            <Stamp class="size-7" />
                        </div>

                        <p
                            class="mt-4 font-semibold text-slate-800 dark:text-slate-200"
                        >
                            {{
                                search
                                    ? "Data tidak ditemukan"
                                    : "Belum ada kemudahan berusaha"
                            }}
                        </p>

                        <p
                            class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{
                                search
                                    ? "Tidak ada data yang cocok dengan kata kunci pencarian."
                                    : "Tambahkan data untuk mulai menampilkan kemudahan berusaha kepada calon investor."
                            }}
                        </p>

                        <button
                            v-if="!search"
                            type="button"
                            :class="[primaryBtnClass, 'mt-5']"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />
                            Tambah Kemudahan Berusaha
                        </button>

                        <button
                            v-else
                            type="button"
                            :class="[
                                secondaryBtnClass,
                                'mt-5 inline-flex items-center justify-center gap-2',
                            ]"
                            @click="clearSearchInput"
                        >
                            <X class="size-4" />
                            Hapus Pencarian
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
                    :class="[modalCardClass, 'max-w-3xl']"
                >
                    <div :class="modalHeaderClass">
                        <div class="min-w-0">
                            <h2
                                id="form-modal-title"
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    modalMode === "create"
                                        ? "Tambah Kemudahan Berusaha"
                                        : "Edit Kemudahan Berusaha"
                                }}
                            </h2>

                            <p
                                class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    modalMode === "create"
                                        ? "Tambahkan informasi kemudahan berusaha baru."
                                        : "Perbarui informasi kemudahan berusaha."
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
                        class="flex min-h-0 flex-1 flex-col"
                        novalidate
                        @submit.prevent="submit"
                    >
                        <div
                            class="grid min-h-0 flex-1 gap-5 overflow-y-auto overscroll-contain p-4 sm:p-6"
                        >
                            <!-- JUDUL -->
                            <div>
                                <label for="f-judul" :class="labelClass">
                                    Judul
                                    <span class="text-red-500"> * </span>
                                </label>

                                <input
                                    id="f-judul"
                                    v-model="form.judul"
                                    type="text"
                                    maxlength="255"
                                    required
                                    autocomplete="off"
                                    placeholder="Contoh: Perizinan Terpadu Satu Pintu"
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
                            <div>
                                <label for="f-slug" :class="labelClass">
                                    Slug
                                </label>

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
                                    />

                                    <span>
                                        {{
                                            modalMode === "edit"
                                                ? "Slug tidak berubah saat edit agar tautan yang sudah dibagikan tetap berfungsi."
                                                : "Slug dibuat otomatis oleh sistem berdasarkan judul. Slug final dapat berbeda jika sudah digunakan."
                                        }}
                                    </span>
                                </p>

                                <p v-if="errors.slug" :class="errorClass">
                                    {{ errors.slug }}
                                </p>
                            </div>

                            <!-- IKON -->
                            <div>
                                <span :class="labelClass"> Ikon </span>

                                <div
                                    role="radiogroup"
                                    aria-label="Pilih ikon"
                                    class="grid grid-cols-4 gap-2 sm:grid-cols-6 lg:grid-cols-8"
                                >
                                    <button
                                        v-for="option in iconOptions"
                                        :key="option.key"
                                        type="button"
                                        role="radio"
                                        :aria-checked="form.ikon === option.key"
                                        :title="option.label"
                                        :aria-label="option.label"
                                        class="flex min-h-16 flex-col items-center justify-center gap-1 rounded-xl border px-1 py-2 text-[11px] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40"
                                        :class="
                                            form.ikon === option.key
                                                ? 'border-blue-500 bg-blue-50 text-blue-700 dark:border-blue-500 dark:bg-blue-950/40 dark:text-blue-300'
                                                : 'border-slate-200 bg-slate-50 text-slate-500 hover:border-blue-300 hover:bg-blue-50/60 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:border-blue-700'
                                        "
                                        @click="selectIcon(option.key)"
                                    >
                                        <component
                                            :is="option.component"
                                            class="size-5"
                                        />

                                        <span
                                            class="w-full truncate text-center"
                                        >
                                            {{ option.label }}
                                        </span>
                                    </button>
                                </div>

                                <p
                                    class="mt-2 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    {{
                                        form.ikon
                                            ? `Terpilih: ${getIconLabel(form.ikon)}. Klik lagi untuk membatalkan.`
                                            : "Belum ada ikon dipilih. Ikon dokumen dipakai sebagai bawaan."
                                    }}
                                </p>

                                <p v-if="errors.ikon" :class="errorClass">
                                    {{ errors.ikon }}
                                </p>
                            </div>

                            <!-- RINGKASAN -->
                            <div>
                                <label for="f-ringkasan" :class="labelClass">
                                    Ringkasan
                                </label>

                                <textarea
                                    id="f-ringkasan"
                                    v-model="form.ringkasan"
                                    rows="3"
                                    maxlength="1000"
                                    placeholder="Penjelasan singkat yang tampil pada kartu di halaman publik..."
                                    :class="[
                                        inputClass,
                                        'resize-none leading-6',
                                        errors.ringkasan && inputErrorClass,
                                    ]"
                                />

                                <p
                                    class="mt-1 text-right text-xs text-slate-400 dark:text-slate-500"
                                >
                                    {{ form.ringkasan.length }}/1000
                                </p>

                                <p v-if="errors.ringkasan" :class="errorClass">
                                    {{ errors.ringkasan }}
                                </p>
                            </div>

                            <!-- DESKRIPSI -->
                            <div>
                                <label for="f-deskripsi" :class="labelClass">
                                    Deskripsi
                                </label>

                                <textarea
                                    id="f-deskripsi"
                                    v-model="form.deskripsi"
                                    rows="8"
                                    maxlength="20000"
                                    placeholder="Tuliskan penjelasan lengkap mengenai kemudahan berusaha ini..."
                                    :class="[
                                        inputClass,
                                        'resize-y leading-6',
                                        errors.deskripsi && inputErrorClass,
                                    ]"
                                />

                                <p v-if="errors.deskripsi" :class="errorClass">
                                    {{ errors.deskripsi }}
                                </p>
                            </div>

                            <!-- AKTIF -->
                            <div>
                                <span :class="labelClass">
                                    Status Publikasi
                                </span>

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
                                    >
                                        Klik untuk ubah
                                    </span>
                                </button>

                                <p v-if="errors.aktif" :class="errorClass">
                                    {{ errors.aktif }}
                                </p>
                            </div>
                        </div>

                        <!-- FOOTER -->
                        <div :class="modalFooterClass">
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
                                />

                                {{
                                    processing
                                        ? "Menyimpan..."
                                        : modalMode === "create"
                                          ? "Simpan"
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
                v-if="showDetailModal && selectedItem"
                :class="modalBackdropClass"
                @mousedown.self="closeDetail"
            >
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="detail-modal-title"
                    :class="[modalCardClass, 'max-w-2xl']"
                >
                    <div :class="modalHeaderClass">
                        <div class="min-w-0">
                            <h2
                                id="detail-modal-title"
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Kemudahan Berusaha
                            </h2>

                            <p
                                class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap kemudahan berusaha.
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
                        class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6"
                    >
                        <div class="flex flex-wrap items-center gap-3">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <component
                                    :is="resolveIcon(selectedItem.ikon)"
                                    class="size-6"
                                />
                            </div>

                            <h3
                                class="min-w-0 break-words text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ selectedItem.judul }}
                            </h3>

                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                :class="
                                    selectedItem.aktif
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                "
                            >
                                {{ selectedItem.aktif ? "Aktif" : "Nonaktif" }}
                            </span>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
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
                            <h4
                                class="mb-2 text-sm font-semibold text-slate-800 dark:text-slate-200"
                            >
                                Ringkasan
                            </h4>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                            >
                                <p class="whitespace-pre-line break-words">
                                    {{ selectedItem.ringkasan || "-" }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5">
                            <h4
                                class="mb-2 text-sm font-semibold text-slate-800 dark:text-slate-200"
                            >
                                Deskripsi
                            </h4>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                            >
                                <p class="whitespace-pre-line break-words">
                                    {{ selectedItem.deskripsi || "-" }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div :class="modalFooterClass">
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
                            @click="openEdit(selectedItem)"
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
                v-if="showDeleteModal && selectedItem"
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
                            Hapus Kemudahan Berusaha?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedItem.judul }}
                            </span>
                            ? Data akan dihapus dari daftar kemudahan berusaha.
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
                            @click="deleteItem"
                        >
                            <span
                                v-if="processingDelete"
                                class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            />

                            {{
                                processingDelete ? "Menghapus..." : "Ya, Hapus"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- PAGE LOADING -->
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
                    />
                </div>
            </div>
        </Transition>
    </div>
</template>
