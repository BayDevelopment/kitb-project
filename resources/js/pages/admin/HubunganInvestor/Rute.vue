<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    ArrowDown,
    ArrowUp,
    Check,
    ChevronLeft,
    ChevronRight,
    Eye,
    FileText,
    ImagePlus,
    MapPin,
    Pencil,
    Plus,
    Route as RouteIcon,
    Search,
    Trash2,
    Upload,
    X,
} from "lucide-vue-next";
import {
    currentLanguage,
    localizedValue,
    type LanguageCode,
} from "@/composables/useLocale";

defineOptions({
    layout: AppLayout,
});

/*
|--------------------------------------------------------------------------
| Endpoint
|--------------------------------------------------------------------------
| Update dengan upload file: POST + _method=PUT
*/

const BASE_URL = "/admin/hubungan-investor/rute-pelayaran-lokasi";

const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
const ALLOWED_IMAGE_TYPES = ["image/jpeg", "image/png", "image/webp"];

/*
|--------------------------------------------------------------------------
| Interface
|--------------------------------------------------------------------------
*/

interface Rute {
    id: number;

    nama_rute: string;
    jalur: string;
    deskripsi: string | null;

    nama_rute_en: string | null;
    jalur_en: string | null;
    deskripsi_en: string | null;

    nama_rute_zh: string | null;
    jalur_zh: string | null;
    deskripsi_zh: string | null;

    jarak: string | number;
    satuan_jarak: string;
    waktu_tempuh: string;

    asal: string | null;
    tujuan: string | null;

    latitude: string | number | null;
    longitude: string | number | null;

    geometry: Record<string, unknown> | null;

    gambar: string | null;

    urutan: number;
    aktif: boolean;

    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedRutes {
    data: Rute[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
}

interface Filters {
    search?: string;
    aktif?: string | boolean | null;
}

type LocalizedKey =
    | "nama_rute"
    | "nama_rute_en"
    | "nama_rute_zh"
    | "jalur"
    | "jalur_en"
    | "jalur_zh"
    | "deskripsi"
    | "deskripsi_en"
    | "deskripsi_zh";

type LocalizedBase = "nama_rute" | "jalur" | "deskripsi";

const props = defineProps<{
    rutes: PaginatedRutes;
    filters: Filters;
}>();

/*
|--------------------------------------------------------------------------
| Localization
|--------------------------------------------------------------------------
*/

const localized = (object: Rute | null | undefined, field: string): string => {
    return localizedValue(
        object as Record<string, unknown> | null | undefined,
        field,
    );
};

const t = computed(() => {
    const language: LanguageCode = currentLanguage.value;

    return {
        id: {
            section: "Hubungan Investor",
            title: "Rute Pelayaran",
            subtitle:
                "Kelola informasi rute pelayaran dan konektivitas logistik.",
            listTitle: "Daftar Rute",
            listSubtitle: "Kelola, urutkan, dan perbarui informasi rute.",
            add: "Tambah Rute",
            search: "Cari rute, jalur, asal, atau tujuan...",
            all: "Semua Status",
            active: "Aktif",
            inactive: "Nonaktif",

            route: "Rute",
            path: "Jalur",
            distance: "Jarak",
            duration: "Waktu Tempuh",
            status: "Status",
            action: "Aksi",

            noData: "Belum ada data rute.",
            noDataHint: "Tambahkan rute untuk mulai mengisi halaman ini.",
            noResult: "Tidak ada data yang sesuai dengan filter.",
            resetFilter: "Reset Filter",

            create: "Tambah Rute",
            edit: "Edit Rute",
            detail: "Detail Rute",
            delete: "Hapus Rute",
            detailDesc: "Informasi lengkap rute pelayaran.",
            formCreateDesc: "Tambahkan informasi rute pelayaran baru.",
            formEditDesc: "Perbarui informasi rute pelayaran.",

            close: "Tutup",
            save: "Simpan",
            saving: "Menyimpan...",
            update: "Perbarui",
            cancel: "Batal",

            indonesia: "Indonesia",
            english: "English",
            chinese: "中文",
            langNotice:
                "Isi informasi sesuai bahasa yang dipilih. Bahasa Indonesia menjadi bahasa utama.",

            name: "Nama Rute",
            description: "Deskripsi",
            origin: "Asal",
            destination: "Tujuan",
            originPlaceholder: "Pelabuhan asal",
            destinationPlaceholder: "Pelabuhan tujuan",
            durationPlaceholder: "Contoh: 2 jam 30 menit",
            techTitle: "Informasi Rute",
            techDesc: "Data teknis dan konektivitas rute.",

            image: "Gambar",
            chooseImage: "Klik untuk memilih gambar",
            imageHint: "JPG, JPEG, PNG, WEBP maksimal 2 MB",
            currentImage: "Gambar saat ini",
            imagePreview: "Preview gambar",
            imageTooLarge: "Ukuran gambar maksimal 2 MB.",
            imageInvalid: "Format gambar harus JPG, JPEG, PNG, atau WEBP.",
            imageKeep:
                "Gambar saat ini akan dipertahankan jika tidak memilih gambar baru.",

            order: "Urutan",
            activeStatus: "Aktif",
            activeHint: "Tampilkan rute pada halaman publik.",

            confirmTitle: "Hapus Rute?",
            confirmDelete: "Yakin ingin menghapus rute",
            confirmWarning: "Data yang sudah dihapus tidak dapat dikembalikan.",
            confirmYes: "Ya, Hapus",
            deleting: "Menghapus...",

            moveUp: "Naikkan urutan",
            moveDown: "Turunkan urutan",
        },

        en: {
            section: "Investor Relations",
            title: "Shipping Routes",
            subtitle:
                "Manage shipping route and logistics connectivity information.",
            listTitle: "Route List",
            listSubtitle: "Manage, reorder, and update route information.",
            add: "Add Route",
            search: "Search route, path, origin, or destination...",
            all: "All Status",
            active: "Active",
            inactive: "Inactive",

            route: "Route",
            path: "Path",
            distance: "Distance",
            duration: "Travel Time",
            status: "Status",
            action: "Actions",

            noData: "No route data yet.",
            noDataHint: "Add a route to start filling this page.",
            noResult: "No data matches the current filter.",
            resetFilter: "Reset Filter",

            create: "Add Route",
            edit: "Edit Route",
            detail: "Route Detail",
            delete: "Delete Route",
            detailDesc: "Complete shipping route information.",
            formCreateDesc: "Add new shipping route information.",
            formEditDesc: "Update shipping route information.",

            close: "Close",
            save: "Save",
            saving: "Saving...",
            update: "Update",
            cancel: "Cancel",

            indonesia: "Indonesia",
            english: "English",
            chinese: "中文",
            langNotice:
                "Fill in the content for the selected language. Indonesian is the primary language.",

            name: "Route Name",
            description: "Description",
            origin: "Origin",
            destination: "Destination",
            originPlaceholder: "Departure port",
            destinationPlaceholder: "Arrival port",
            durationPlaceholder: "Example: 2 hours 30 minutes",
            techTitle: "Route Information",
            techDesc: "Technical and connectivity data.",

            image: "Image",
            chooseImage: "Click to choose an image",
            imageHint: "JPG, JPEG, PNG, WEBP up to 2 MB",
            currentImage: "Current image",
            imagePreview: "Image preview",
            imageTooLarge: "Image size must not exceed 2 MB.",
            imageInvalid: "Image must be JPG, JPEG, PNG, or WEBP.",
            imageKeep:
                "The current image is kept if you do not choose a new one.",

            order: "Order",
            activeStatus: "Active",
            activeHint: "Show this route on the public page.",

            confirmTitle: "Delete Route?",
            confirmDelete: "Are you sure you want to delete route",
            confirmWarning: "Deleted data cannot be restored.",
            confirmYes: "Yes, Delete",
            deleting: "Deleting...",

            moveUp: "Move up",
            moveDown: "Move down",
        },

        zh: {
            section: "投资者关系",
            title: "航运路线",
            subtitle: "管理航运路线和物流连接信息。",
            listTitle: "路线列表",
            listSubtitle: "管理、排序并更新路线信息。",
            add: "添加路线",
            search: "搜索路线、航线、起点或终点...",
            all: "全部状态",
            active: "启用",
            inactive: "停用",

            route: "路线",
            path: "航线",
            distance: "距离",
            duration: "航行时间",
            status: "状态",
            action: "操作",

            noData: "暂无路线数据。",
            noDataHint: "添加路线以开始填充此页面。",
            noResult: "没有符合当前筛选条件的数据。",
            resetFilter: "重置筛选",

            create: "添加路线",
            edit: "编辑路线",
            detail: "路线详情",
            delete: "删除路线",
            detailDesc: "航运路线完整信息。",
            formCreateDesc: "添加新的航运路线信息。",
            formEditDesc: "更新航运路线信息。",

            close: "关闭",
            save: "保存",
            saving: "保存中...",
            update: "更新",
            cancel: "取消",

            indonesia: "Indonesia",
            english: "English",
            chinese: "中文",
            langNotice: "请按所选语言填写内容。印尼语为主要语言。",

            name: "路线名称",
            description: "描述",
            origin: "起点",
            destination: "终点",
            originPlaceholder: "出发港口",
            destinationPlaceholder: "到达港口",
            durationPlaceholder: "例如：2 小时 30 分钟",
            techTitle: "路线信息",
            techDesc: "路线技术与连接数据。",

            image: "图片",
            chooseImage: "点击选择图片",
            imageHint: "JPG、JPEG、PNG、WEBP，最大 2 MB",
            currentImage: "当前图片",
            imagePreview: "图片预览",
            imageTooLarge: "图片大小不能超过 2 MB。",
            imageInvalid: "图片格式必须为 JPG、JPEG、PNG 或 WEBP。",
            imageKeep: "如果不选择新图片，将保留当前图片。",

            order: "排序",
            activeStatus: "启用",
            activeHint: "在公开页面显示此路线。",

            confirmTitle: "删除路线？",
            confirmDelete: "确定要删除路线",
            confirmWarning: "删除后的数据无法恢复。",
            confirmYes: "确认删除",
            deleting: "删除中...",

            moveUp: "上移",
            moveDown: "下移",
        },
    }[language];
});

/*
|--------------------------------------------------------------------------
| Shared Class Names
|--------------------------------------------------------------------------
*/

const inputClass =
    "h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800";

const textareaClass =
    "w-full resize-none rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800";

const labelClass =
    "mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300";

const errorClass = "mt-1.5 block text-xs text-red-600 dark:text-red-400";

const infoCardClass =
    "rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60";

const infoLabelClass =
    "text-xs font-semibold uppercase tracking-[0.12em] text-slate-400";

const tabClass = (active: boolean) =>
    [
        "rounded-xl px-3 py-2.5 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30",
        active
            ? "bg-white text-blue-600 shadow-sm dark:bg-slate-700 dark:text-blue-400"
            : "text-slate-500 hover:bg-white/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700/50 dark:hover:text-slate-200",
    ].join(" ");

/*
|--------------------------------------------------------------------------
| Search & Filter
|--------------------------------------------------------------------------
*/

const search = ref(props.filters.search ?? "");

const status = ref(
    props.filters.aktif === true || props.filters.aktif === "1"
        ? "1"
        : props.filters.aktif === false || props.filters.aktif === "0"
          ? "0"
          : "",
);

/** Navbar adalah satu-satunya language switcher; search di-reset saat bahasa berubah. */
watch(currentLanguage, () => {
    search.value = "";
});

let searchTimer: ReturnType<typeof setTimeout> | undefined;

watch([search, status], () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        router.get(
            BASE_URL,
            {
                search: search.value || undefined,
                aktif: status.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 350);
});

const hasActiveFilter = computed(() => Boolean(search.value || status.value));

const resetFilter = () => {
    search.value = "";
    status.value = "";
};

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const goToPage = (url: string | null) => {
    if (!url) {
        return;
    }

    router.get(url, {}, { preserveState: true, preserveScroll: true });
};

const previousPageUrl = computed(() => props.rutes.links[0]?.url ?? null);

const nextPageUrl = computed(
    () => props.rutes.links[props.rutes.links.length - 1]?.url ?? null,
);

/*
|--------------------------------------------------------------------------
| Modal State
|--------------------------------------------------------------------------
*/

const showForm = ref(false);
const showDetail = ref(false);
const showDelete = ref(false);

const editing = ref<Rute | null>(null);
const selected = ref<Rute | null>(null);

const activeLanguage = ref<LanguageCode>("id");

const processingDelete = ref(false);
const processingToggleId = ref<number | null>(null);
const movingId = ref<number | null>(null);

const languageTabs = computed(() => [
    { code: "id" as const, flag: "🇮🇩", label: t.value.indonesia },
    { code: "en" as const, flag: "🇬🇧", label: t.value.english },
    { code: "zh" as const, flag: "🇨🇳", label: t.value.chinese },
]);

const fieldKey = (base: LocalizedBase): LocalizedKey =>
    (activeLanguage.value === "id"
        ? base
        : `${base}_${activeLanguage.value}`) as LocalizedKey;

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    nama_rute: "",
    jalur: "",
    deskripsi: "",

    nama_rute_en: "",
    jalur_en: "",
    deskripsi_en: "",

    nama_rute_zh: "",
    jalur_zh: "",
    deskripsi_zh: "",

    jarak: "",
    satuan_jarak: "km",
    waktu_tempuh: "",

    asal: "",
    tujuan: "",

    latitude: "",
    longitude: "",

    geometry: "",

    gambar: null as File | null,

    urutan: 0,
    aktif: true,
});

const previewUrl = ref<string | null>(null);

const revokePreview = () => {
    if (previewUrl.value?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl.value);
    }
};

const resetForm = () => {
    revokePreview();

    form.reset();
    form.clearErrors();

    form.satuan_jarak = "km";
    form.aktif = true;
    form.urutan = props.rutes.total + 1;
    form.gambar = null;

    previewUrl.value = null;
};

const hideForm = () => {
    showForm.value = false;
    editing.value = null;
    activeLanguage.value = "id";

    revokePreview();
    previewUrl.value = null;
};

const closeForm = () => {
    if (form.processing) {
        return;
    }

    hideForm();
};

const openCreate = () => {
    editing.value = null;
    activeLanguage.value = "id";

    resetForm();

    showForm.value = true;
};

const openEdit = (rute: Rute) => {
    revokePreview();

    editing.value = rute;
    activeLanguage.value = "id";

    form.clearErrors();

    form.nama_rute = rute.nama_rute ?? "";
    form.jalur = rute.jalur ?? "";
    form.deskripsi = rute.deskripsi ?? "";

    form.nama_rute_en = rute.nama_rute_en ?? "";
    form.jalur_en = rute.jalur_en ?? "";
    form.deskripsi_en = rute.deskripsi_en ?? "";

    form.nama_rute_zh = rute.nama_rute_zh ?? "";
    form.jalur_zh = rute.jalur_zh ?? "";
    form.deskripsi_zh = rute.deskripsi_zh ?? "";

    form.jarak = String(rute.jarak ?? "");
    form.satuan_jarak = rute.satuan_jarak ?? "km";
    form.waktu_tempuh = rute.waktu_tempuh ?? "";

    form.asal = rute.asal ?? "";
    form.tujuan = rute.tujuan ?? "";

    form.latitude = rute.latitude === null ? "" : String(rute.latitude);
    form.longitude = rute.longitude === null ? "" : String(rute.longitude);

    form.geometry = rute.geometry ? JSON.stringify(rute.geometry) : "";

    form.gambar = null;
    previewUrl.value = rute.gambar ? imageUrl(rute.gambar) : null;

    form.urutan = rute.urutan;
    form.aktif = rute.aktif;

    showForm.value = true;
};

/*
|--------------------------------------------------------------------------
| Image
|--------------------------------------------------------------------------
*/

const imageUrl = (path: string | null): string => {
    return path ? `/storage/${path}` : "";
};

const handleFile = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    if (!file) {
        return;
    }

    form.clearErrors("gambar");

    if (file.size > MAX_IMAGE_SIZE) {
        target.value = "";
        form.setError("gambar", t.value.imageTooLarge);
        return;
    }

    if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
        target.value = "";
        form.setError("gambar", t.value.imageInvalid);
        return;
    }

    revokePreview();

    form.gambar = file;
    previewUrl.value = URL.createObjectURL(file);
};

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const isFormValid = computed(
    () =>
        form.nama_rute.trim().length > 0 &&
        form.jalur.trim().length > 0 &&
        String(form.jarak).trim().length > 0 &&
        form.waktu_tempuh.trim().length > 0,
);

/*
|--------------------------------------------------------------------------
| Store / Update
|--------------------------------------------------------------------------
| Update + upload file: POST /url/{id} dengan _method=PUT.
*/

const submit = () => {
    if (form.processing || !isFormValid.value) {
        return;
    }

    if (!editing.value) {
        form.post(BASE_URL, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                hideForm();
                resetForm();
            },
        });

        return;
    }

    const id = editing.value.id;

    form.transform((data) => ({
        ...data,
        _method: "PUT",
    })).post(`${BASE_URL}/${id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            hideForm();
            resetForm();
        },
    });
};

/*
|--------------------------------------------------------------------------
| Detail
|--------------------------------------------------------------------------
*/

const openDetail = (rute: Rute) => {
    selected.value = rute;
    showDetail.value = true;
};

const closeDetail = () => {
    showDetail.value = false;
    selected.value = null;
};

const detailDescriptions = computed(() => {
    if (!selected.value) {
        return [];
    }

    return [
        {
            code: "id",
            flag: "🇮🇩",
            label: t.value.indonesia,
            text: selected.value.deskripsi,
        },
        {
            code: "en",
            flag: "🇬🇧",
            label: t.value.english,
            text: selected.value.deskripsi_en,
        },
        {
            code: "zh",
            flag: "🇨🇳",
            label: t.value.chinese,
            text: selected.value.deskripsi_zh,
        },
    ].filter((item) => item.text);
});

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const askDelete = (rute: Rute) => {
    selected.value = rute;
    showDelete.value = true;
};

const closeDelete = () => {
    if (processingDelete.value) {
        return;
    }

    showDelete.value = false;
    selected.value = null;
};

const destroy = () => {
    if (!selected.value || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(`${BASE_URL}/${selected.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDelete.value = false;
            selected.value = null;
        },

        onFinish: () => {
            processingDelete.value = false;
        },
    });
};

/*
|--------------------------------------------------------------------------
| Toggle Status
|--------------------------------------------------------------------------
*/

const toggleAktif = (rute: Rute) => {
    if (processingToggleId.value !== null) {
        return;
    }

    processingToggleId.value = rute.id;

    router.patch(
        `${BASE_URL}/${rute.id}/toggle-aktif`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processingToggleId.value = null;
            },
        },
    );
};

/*
|--------------------------------------------------------------------------
| Move Order
|--------------------------------------------------------------------------
*/

const move = (rute: Rute, direction: "up" | "down") => {
    if (movingId.value !== null) {
        return;
    }

    movingId.value = rute.id;

    router.patch(
        `${BASE_URL}/${rute.id}/move`,
        { direction },
        {
            preserveScroll: true,
            onFinish: () => {
                movingId.value = null;
            },
        },
    );
};

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const formatDistance = (rute: Rute): string => {
    return `${rute.jarak} ${rute.satuan_jarak || "km"}`;
};

/*
|--------------------------------------------------------------------------
| Keyboard (Esc menutup modal teratas)
|--------------------------------------------------------------------------
*/

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key !== "Escape") {
        return;
    }

    if (showDelete.value) {
        closeDelete();
    } else if (showDetail.value) {
        closeDetail();
    } else if (showForm.value) {
        closeForm();
    }
};

onMounted(() => {
    window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeydown);

    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    revokePreview();
});
</script>

<template>
    <Head :title="t.title" />

    <div
        class="relative min-h-full overflow-hidden bg-slate-50/50 transition-colors duration-300 dark:bg-slate-950/50"
    >
        <!-- ========================================================= -->
        <!-- BACKGROUND -->
        <!-- ========================================================= -->

        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="blob-shape absolute -left-40 -top-40 h-[32rem] w-[32rem] rounded-full bg-blue-500/[0.055] blur-3xl dark:bg-blue-600/[0.10]"
            />

            <div
                class="blob-shape-delayed absolute -right-48 top-8 h-[34rem] w-[34rem] rounded-full bg-indigo-500/[0.05] blur-3xl dark:bg-indigo-500/[0.075]"
            />

            <div
                class="blob-shape-slow absolute -bottom-52 left-1/3 h-[34rem] w-[34rem] rounded-full bg-sky-500/[0.045] blur-3xl dark:bg-sky-500/[0.06]"
            />

            <div
                class="absolute inset-0 bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px] opacity-40 dark:opacity-20"
            />

            <div
                class="absolute left-[7%] top-[45%] h-20 w-20 rounded-full border border-blue-500/[0.07] bg-blue-500/[0.045] shadow-xl shadow-blue-500/[0.05] dark:border-blue-400/[0.08] dark:bg-blue-400/[0.06]"
            />

            <div
                class="absolute right-[8%] top-[30%] h-4 w-4 rounded-full bg-indigo-500/[0.12] dark:bg-indigo-300/[0.22]"
            />

            <div
                class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent to-slate-50/90 dark:to-slate-950/90"
            />
        </div>

        <!-- ========================================================= -->
        <!-- CONTENT -->
        <!-- ========================================================= -->

        <div
            class="relative mx-auto flex w-full max-w-[1600px] flex-1 flex-col p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <div class="relative space-y-5">
                <!-- HEADER -->

                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-400"
                        >
                            <RouteIcon class="size-5" />
                        </div>

                        <div class="min-w-0">
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400"
                            >
                                {{ t.section }}
                            </p>

                            <h1
                                class="mt-0.5 text-xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-2xl"
                            >
                                {{ t.title }}
                            </h1>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ t.subtitle }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 sm:w-auto"
                        @click="openCreate"
                    >
                        <Plus class="size-4" />
                        {{ t.add }}
                    </button>
                </div>

                <!-- MAIN CARD -->

                <div
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-900/[0.04] backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/90"
                >
                    <!-- CARD HEADER -->

                    <div
                        class="flex flex-col gap-4 border-b border-slate-200/80 px-5 py-5 dark:border-slate-800 sm:px-6 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <h2
                                    class="font-semibold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ t.listTitle }}
                                </h2>

                                <span
                                    class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    {{ props.rutes.total }}
                                </span>
                            </div>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ t.listSubtitle }}
                            </p>
                        </div>

                        <div
                            class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto"
                        >
                            <div class="relative w-full sm:w-72">
                                <Search
                                    class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="search"
                                    type="search"
                                    :placeholder="t.search"
                                    :aria-label="t.search"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-9 pr-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800"
                                />
                            </div>

                            <select
                                v-model="status"
                                :aria-label="t.status"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200 sm:w-40"
                            >
                                <option value="">{{ t.all }}</option>
                                <option value="1">{{ t.active }}</option>
                                <option value="0">{{ t.inactive }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- EMPTY -->

                    <div
                        v-if="props.rutes.data.length === 0"
                        class="px-6 py-20 text-center"
                    >
                        <div
                            class="mx-auto flex size-14 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800"
                        >
                            <RouteIcon class="size-6" />
                        </div>

                        <h3
                            class="mt-4 font-semibold text-slate-900 dark:text-white"
                        >
                            {{ hasActiveFilter ? t.noResult : t.noData }}
                        </h3>

                        <p
                            v-if="!hasActiveFilter"
                            class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{ t.noDataHint }}
                        </p>

                        <button
                            v-if="hasActiveFilter"
                            type="button"
                            class="mt-5 inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="resetFilter"
                        >
                            <X class="size-4" />
                            {{ t.resetFilter }}
                        </button>

                        <button
                            v-else
                            type="button"
                            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />
                            {{ t.add }}
                        </button>
                    </div>

                    <!-- TABLE -->

                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[1000px] text-left">
                            <thead
                                class="border-b border-slate-200/80 bg-slate-50/70 dark:border-slate-800 dark:bg-slate-950/30"
                            >
                                <tr
                                    class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    <th class="px-6 py-3.5">{{ t.order }}</th>
                                    <th class="px-6 py-3.5">{{ t.route }}</th>
                                    <th class="px-6 py-3.5">{{ t.path }}</th>
                                    <th class="px-6 py-3.5">
                                        {{ t.distance }}
                                    </th>
                                    <th class="px-6 py-3.5">
                                        {{ t.duration }}
                                    </th>
                                    <th class="px-6 py-3.5">{{ t.status }}</th>
                                    <th class="px-6 py-3.5 text-right">
                                        {{ t.action }}
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="rute in props.rutes.data"
                                    :key="rute.id"
                                    class="group transition-colors hover:bg-blue-50/40 dark:hover:bg-blue-950/10"
                                >
                                    <!-- ORDER -->

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                            >
                                                {{ rute.urutan }}
                                            </span>

                                            <div class="flex flex-col">
                                                <button
                                                    type="button"
                                                    :title="t.moveUp"
                                                    :disabled="
                                                        rute.urutan <= 1 ||
                                                        movingId !== null
                                                    "
                                                    class="rounded-md p-1 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                    @click="move(rute, 'up')"
                                                >
                                                    <ArrowUp class="size-3.5" />
                                                </button>

                                                <button
                                                    type="button"
                                                    :title="t.moveDown"
                                                    :disabled="
                                                        rute.urutan >=
                                                            props.rutes.total ||
                                                        movingId !== null
                                                    "
                                                    class="rounded-md p-1 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                    @click="move(rute, 'down')"
                                                >
                                                    <ArrowDown
                                                        class="size-3.5"
                                                    />
                                                </button>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- ROUTE -->

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 text-slate-400 group-hover:border-blue-100 group-hover:bg-white dark:border-slate-700 dark:bg-slate-800"
                                            >
                                                <img
                                                    v-if="rute.gambar"
                                                    :src="imageUrl(rute.gambar)"
                                                    :alt="
                                                        localized(
                                                            rute,
                                                            'nama_rute',
                                                        )
                                                    "
                                                    class="size-full object-cover"
                                                />

                                                <ImagePlus
                                                    v-else
                                                    class="size-5"
                                                />
                                            </div>

                                            <div class="min-w-0">
                                                <button
                                                    type="button"
                                                    class="text-left font-medium text-slate-800 transition hover:text-blue-600 dark:text-slate-200 dark:hover:text-blue-400"
                                                    @click="openDetail(rute)"
                                                >
                                                    {{
                                                        localized(
                                                            rute,
                                                            "nama_rute",
                                                        ) || "-"
                                                    }}
                                                </button>

                                                <p
                                                    class="mt-1 line-clamp-2 max-w-xs text-xs text-slate-400"
                                                >
                                                    {{
                                                        localized(
                                                            rute,
                                                            "deskripsi",
                                                        ) || "-"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- PATH -->

                                    <td class="px-6 py-4">
                                        <div
                                            class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300"
                                        >
                                            <MapPin
                                                class="size-4 shrink-0 text-blue-500"
                                            />

                                            <span
                                                class="max-w-[230px] truncate"
                                            >
                                                {{
                                                    localized(rute, "jalur") ||
                                                    "-"
                                                }}
                                            </span>
                                        </div>

                                        <p
                                            v-if="rute.asal || rute.tujuan"
                                            class="mt-1 text-xs text-slate-400"
                                        >
                                            {{ rute.asal || "-" }} →
                                            {{ rute.tujuan || "-" }}
                                        </p>
                                    </td>

                                    <!-- DISTANCE -->

                                    <td class="px-6 py-4">
                                        <span
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ formatDistance(rute) }}
                                        </span>
                                    </td>

                                    <!-- DURATION -->

                                    <td
                                        class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300"
                                    >
                                        {{ rute.waktu_tempuh || "-" }}
                                    </td>

                                    <!-- STATUS -->

                                    <td class="px-6 py-4">
                                        <button
                                            type="button"
                                            :disabled="
                                                processingToggleId === rute.id
                                            "
                                            class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-60"
                                            :class="
                                                rute.aktif
                                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                    : 'border-slate-200 bg-slate-100 text-slate-500 hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400'
                                            "
                                            @click="toggleAktif(rute)"
                                        >
                                            {{
                                                rute.aktif
                                                    ? t.active
                                                    : t.inactive
                                            }}
                                        </button>
                                    </td>

                                    <!-- ACTION -->

                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-1">
                                            <button
                                                type="button"
                                                :title="t.detail"
                                                class="rounded-lg p-2 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                @click="openDetail(rute)"
                                            >
                                                <Eye class="size-4" />
                                            </button>

                                            <button
                                                type="button"
                                                :title="t.edit"
                                                class="rounded-lg p-2 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                                @click="openEdit(rute)"
                                            >
                                                <Pencil class="size-4" />
                                            </button>

                                            <button
                                                type="button"
                                                :title="t.delete"
                                                class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                                @click="askDelete(rute)"
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
                        v-if="props.rutes.last_page > 1"
                        class="flex flex-col gap-4 border-t border-slate-200/80 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                    >
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ props.rutes.from ?? 0 }}
                            </span>
                            -
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ props.rutes.to ?? 0 }}
                            </span>
                            /
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ props.rutes.total }}
                            </span>
                        </p>

                        <div
                            class="flex max-w-full items-center gap-1.5 overflow-x-auto pb-1"
                        >
                            <button
                                type="button"
                                :disabled="!previousPageUrl"
                                class="shrink-0 rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="goToPage(previousPageUrl)"
                            >
                                <ChevronLeft class="size-4" />
                            </button>

                            <template
                                v-for="(link, index) in props.rutes.links.slice(
                                    1,
                                    -1,
                                )"
                                :key="index"
                            >
                                <button
                                    v-if="link.url"
                                    type="button"
                                    class="min-w-9 shrink-0 rounded-lg px-3 py-2 text-sm font-medium transition"
                                    :class="
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                                    "
                                    @click="goToPage(link.url)"
                                    v-html="link.label"
                                />

                                <span
                                    v-else
                                    class="shrink-0 px-2 text-sm text-slate-400"
                                    v-html="link.label"
                                />
                            </template>

                            <button
                                type="button"
                                :disabled="!nextPageUrl"
                                class="shrink-0 rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="goToPage(nextPageUrl)"
                            >
                                <ChevronRight class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- FORM MODAL -->
        <!-- ========================================================= -->

        <div
            v-if="showForm"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
            @mousedown.self="closeForm"
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="route-form-title"
                class="modal-panel max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-3xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- HEADER -->

                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-slate-50/95 px-6 py-4 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-400"
                        >
                            <RouteIcon class="size-5" />
                        </div>

                        <div class="min-w-0">
                            <h2
                                id="route-form-title"
                                class="truncate font-semibold text-slate-900 dark:text-white"
                            >
                                {{ editing ? t.edit : t.create }}
                            </h2>

                            <p
                                class="mt-0.5 truncate text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    editing ? t.formEditDesc : t.formCreateDesc
                                }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        :title="t.close"
                        :disabled="form.processing"
                        class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:opacity-50 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                        @click="closeForm"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <form class="space-y-6 p-6" @submit.prevent="submit">
                    <!-- LANGUAGE TABS -->

                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-50/70 p-1.5 dark:border-slate-700 dark:bg-slate-800/50"
                    >
                        <div class="grid grid-cols-3 gap-1" role="tablist">
                            <button
                                v-for="tab in languageTabs"
                                :key="tab.code"
                                type="button"
                                role="tab"
                                :aria-selected="activeLanguage === tab.code"
                                :class="tabClass(activeLanguage === tab.code)"
                                @click="activeLanguage = tab.code"
                            >
                                {{ tab.flag }} {{ tab.label }}
                            </button>
                        </div>
                    </div>

                    <!-- LANGUAGE NOTICE -->

                    <div
                        class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50/70 p-3.5 dark:border-blue-900/40 dark:bg-blue-950/20"
                    >
                        <div
                            class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400"
                        >
                            <FileText class="size-4" />
                        </div>

                        <p
                            class="text-xs leading-5 text-blue-700/90 dark:text-blue-300/90"
                        >
                            {{ t.langNotice }}
                        </p>
                    </div>

                    <!-- LOCALIZED FIELDS -->

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label :class="labelClass">
                                {{ t.name }}
                                <span
                                    v-if="activeLanguage === 'id'"
                                    class="text-red-500"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                v-model="form[fieldKey('nama_rute')]"
                                type="text"
                                maxlength="200"
                                :placeholder="`${t.name}...`"
                                :class="inputClass"
                            />

                            <span
                                v-if="form.errors[fieldKey('nama_rute')]"
                                :class="errorClass"
                            >
                                {{ form.errors[fieldKey("nama_rute")] }}
                            </span>
                        </div>

                        <div>
                            <label :class="labelClass">
                                {{ t.path }}
                                <span
                                    v-if="activeLanguage === 'id'"
                                    class="text-red-500"
                                >
                                    *
                                </span>
                            </label>

                            <input
                                v-model="form[fieldKey('jalur')]"
                                type="text"
                                maxlength="255"
                                :placeholder="`${t.path}...`"
                                :class="inputClass"
                            />

                            <span
                                v-if="form.errors[fieldKey('jalur')]"
                                :class="errorClass"
                            >
                                {{ form.errors[fieldKey("jalur")] }}
                            </span>
                        </div>

                        <div class="md:col-span-2">
                            <label :class="labelClass">
                                {{ t.description }}
                            </label>

                            <textarea
                                v-model="form[fieldKey('deskripsi')]"
                                rows="5"
                                :placeholder="`${t.description}...`"
                                :class="textareaClass"
                            />

                            <span
                                v-if="form.errors[fieldKey('deskripsi')]"
                                :class="errorClass"
                            >
                                {{ form.errors[fieldKey("deskripsi")] }}
                            </span>
                        </div>
                    </div>

                    <!-- ROUTE INFORMATION -->

                    <section
                        class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5 dark:border-slate-700 dark:bg-slate-800/30"
                    >
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                            >
                                <MapPin class="size-4" />
                            </div>

                            <div>
                                <h3
                                    class="text-sm font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ t.techTitle }}
                                </h3>

                                <p class="text-xs text-slate-400">
                                    {{ t.techDesc }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label :class="labelClass">
                                    {{ t.distance }}
                                    <span class="text-red-500">*</span>
                                </label>

                                <div class="grid grid-cols-[1fr_96px] gap-2">
                                    <input
                                        v-model="form.jarak"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0"
                                        :class="inputClass"
                                    />

                                    <input
                                        v-model="form.satuan_jarak"
                                        type="text"
                                        maxlength="20"
                                        placeholder="km"
                                        :class="inputClass"
                                    />
                                </div>

                                <span
                                    v-if="
                                        form.errors.jarak ||
                                        form.errors.satuan_jarak
                                    "
                                    :class="errorClass"
                                >
                                    {{
                                        form.errors.jarak ||
                                        form.errors.satuan_jarak
                                    }}
                                </span>
                            </div>

                            <div>
                                <label :class="labelClass">
                                    {{ t.duration }}
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    v-model="form.waktu_tempuh"
                                    type="text"
                                    maxlength="100"
                                    :placeholder="t.durationPlaceholder"
                                    :class="inputClass"
                                />

                                <span
                                    v-if="form.errors.waktu_tempuh"
                                    :class="errorClass"
                                >
                                    {{ form.errors.waktu_tempuh }}
                                </span>
                            </div>

                            <div>
                                <label :class="labelClass">
                                    {{ t.origin }}
                                </label>

                                <input
                                    v-model="form.asal"
                                    type="text"
                                    maxlength="200"
                                    :placeholder="t.originPlaceholder"
                                    :class="inputClass"
                                />
                            </div>

                            <div>
                                <label :class="labelClass">
                                    {{ t.destination }}
                                </label>

                                <input
                                    v-model="form.tujuan"
                                    type="text"
                                    maxlength="200"
                                    :placeholder="t.destinationPlaceholder"
                                    :class="inputClass"
                                />
                            </div>

                            <div>
                                <label :class="labelClass"> Latitude </label>

                                <input
                                    v-model="form.latitude"
                                    type="number"
                                    step="0.0000001"
                                    placeholder="-6.0000000"
                                    :class="inputClass"
                                />

                                <span
                                    v-if="form.errors.latitude"
                                    :class="errorClass"
                                >
                                    {{ form.errors.latitude }}
                                </span>
                            </div>

                            <div>
                                <label :class="labelClass"> Longitude </label>

                                <input
                                    v-model="form.longitude"
                                    type="number"
                                    step="0.0000001"
                                    placeholder="106.0000000"
                                    :class="inputClass"
                                />

                                <span
                                    v-if="form.errors.longitude"
                                    :class="errorClass"
                                >
                                    {{ form.errors.longitude }}
                                </span>
                            </div>

                            <div class="md:col-span-2">
                                <label :class="labelClass"> GeoJSON </label>

                                <textarea
                                    v-model="form.geometry"
                                    rows="4"
                                    placeholder='{"type":"LineString","coordinates":[[106.0,-6.0],[106.1,-6.1]]}'
                                    :class="[
                                        textareaClass,
                                        'font-mono text-xs leading-5',
                                    ]"
                                />

                                <span
                                    v-if="form.errors.geometry"
                                    :class="errorClass"
                                >
                                    {{ form.errors.geometry }}
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- IMAGE -->

                    <div>
                        <label :class="labelClass">{{ t.image }}</label>

                        <label
                            class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-5 py-7 transition hover:border-blue-400 hover:bg-blue-50/30 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-blue-500 dark:hover:bg-blue-950/20"
                        >
                            <Upload class="size-6 text-slate-400" />

                            <span
                                class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-300"
                            >
                                {{ t.chooseImage }}
                            </span>

                            <span class="mt-1 text-xs text-slate-400">
                                {{ t.imageHint }}
                            </span>

                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="handleFile"
                            />
                        </label>

                        <span v-if="form.errors.gambar" :class="errorClass">
                            {{ form.errors.gambar }}
                        </span>

                        <div
                            v-if="previewUrl"
                            class="mt-4 flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50/50 p-3 dark:border-slate-700 dark:bg-slate-800/40"
                        >
                            <div
                                class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white dark:bg-slate-800"
                            >
                                <img
                                    :src="previewUrl"
                                    :alt="t.imagePreview"
                                    class="size-full object-cover"
                                />
                            </div>

                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ form.gambar?.name ?? t.currentImage }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{
                                        form.gambar
                                            ? t.imagePreview
                                            : t.imageKeep
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ORDER -->

                    <div>
                        <label :class="labelClass">{{ t.order }}</label>

                        <input
                            v-model="form.urutan"
                            type="number"
                            min="0"
                            :class="inputClass"
                        />

                        <span v-if="form.errors.urutan" :class="errorClass">
                            {{ form.errors.urutan }}
                        </span>
                    </div>

                    <!-- STATUS -->

                    <label
                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800/40 dark:hover:bg-slate-800"
                    >
                        <input
                            v-model="form.aktif"
                            type="checkbox"
                            class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800"
                        />

                        <div>
                            <p
                                class="text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ t.activeStatus }}
                            </p>

                            <p class="text-xs text-slate-400">
                                {{ t.activeHint }}
                            </p>
                        </div>
                    </label>

                    <!-- BUTTONS -->

                    <div
                        class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 dark:border-slate-800 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            :disabled="form.processing"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closeForm"
                        >
                            {{ t.cancel }}
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing || !isFormValid"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span
                                v-if="form.processing"
                                class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            />

                            <Check v-else class="size-4" />

                            {{
                                form.processing
                                    ? t.saving
                                    : editing
                                      ? t.update
                                      : t.save
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- ========================================================= -->
        <!-- DETAIL MODAL -->
        <!-- ========================================================= -->

        <div
            v-if="showDetail && selected"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
            @mousedown.self="closeDetail"
        >
            <div
                role="dialog"
                aria-modal="true"
                class="modal-panel max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-slate-50/95 px-6 py-4 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95"
                >
                    <div>
                        <h2
                            class="font-semibold text-slate-900 dark:text-white"
                        >
                            {{ t.detail }}
                        </h2>

                        <p
                            class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                        >
                            {{ t.detailDesc }}
                        </p>
                    </div>

                    <button
                        type="button"
                        :title="t.close"
                        class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                        @click="closeDetail"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <div class="space-y-5 p-6">
                    <!-- IMAGE -->

                    <div
                        class="flex h-44 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800"
                    >
                        <img
                            v-if="selected.gambar"
                            :src="imageUrl(selected.gambar)"
                            :alt="localized(selected, 'nama_rute')"
                            class="size-full object-cover"
                        />

                        <ImagePlus v-else class="size-8" />
                    </div>

                    <!-- NAME -->

                    <div class="text-center">
                        <h3
                            class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white"
                        >
                            {{ localized(selected, "nama_rute") }}
                        </h3>

                        <p
                            class="mt-1 flex items-center justify-center gap-1.5 text-sm text-slate-400"
                        >
                            <MapPin class="size-3.5" />
                            {{ localized(selected, "jalur") || "-" }}
                        </p>
                    </div>

                    <!-- INFO -->

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div :class="infoCardClass">
                            <p :class="infoLabelClass">
                                {{ t.order }}
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selected.urutan }}
                            </p>
                        </div>

                        <div :class="infoCardClass">
                            <p :class="infoLabelClass">
                                {{ t.status }}
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold"
                                :class="
                                    selected.aktif
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-slate-500 dark:text-slate-400'
                                "
                            >
                                {{ selected.aktif ? t.active : t.inactive }}
                            </p>
                        </div>

                        <div
                            :class="[infoCardClass, 'col-span-2 sm:col-span-1']"
                        >
                            <p :class="infoLabelClass">
                                {{ t.distance }}
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ formatDistance(selected) }}
                            </p>
                        </div>

                        <div :class="infoCardClass">
                            <p :class="infoLabelClass">
                                {{ t.origin }}
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selected.asal || "-" }}
                            </p>
                        </div>

                        <div :class="infoCardClass">
                            <p :class="infoLabelClass">
                                {{ t.destination }}
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selected.tujuan || "-" }}
                            </p>
                        </div>

                        <div
                            :class="[infoCardClass, 'col-span-2 sm:col-span-1']"
                        >
                            <p :class="infoLabelClass">
                                {{ t.duration }}
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selected.waktu_tempuh || "-" }}
                            </p>
                        </div>
                    </div>

                    <div :class="infoCardClass">
                        <p :class="infoLabelClass">Latitude / Longitude</p>

                        <p
                            class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ selected.latitude ?? "-" }},
                            {{ selected.longitude ?? "-" }}
                        </p>
                    </div>

                    <!-- DESCRIPTIONS -->

                    <div
                        v-for="item in detailDescriptions"
                        :key="item.code"
                        :class="infoCardClass"
                    >
                        <p :class="infoLabelClass">
                            {{ item.flag }} {{ t.description }} ·
                            {{ item.label }}
                        </p>

                        <p
                            class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                        >
                            {{ item.text }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- ========================================================= -->
        <!-- DELETE MODAL -->
        <!-- ========================================================= -->

        <div
            v-if="showDelete && selected"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
            @mousedown.self="closeDelete"
        >
            <div
                role="alertdialog"
                aria-modal="true"
                class="modal-panel w-full max-w-md rounded-3xl border border-slate-200/80 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                >
                    <Trash2 class="size-5" />
                </div>

                <div class="mt-4 text-center">
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        {{ t.confirmTitle }}
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ t.confirmDelete }}
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ localized(selected, "nama_rute") }}
                        </span>
                        ? {{ t.confirmWarning }}
                    </p>

                    <div
                        class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-left dark:border-slate-700 dark:bg-slate-800"
                    >
                        <p
                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ t.order }}: {{ selected.urutan }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
                >
                    <button
                        type="button"
                        :disabled="processingDelete"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="closeDelete"
                    >
                        {{ t.cancel }}
                    </button>

                    <button
                        type="button"
                        :disabled="processingDelete"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="destroy"
                    >
                        <span
                            v-if="processingDelete"
                            class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                        />

                        {{ processingDelete ? t.deleting : t.confirmYes }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-panel {
    scrollbar-width: thin;
    scrollbar-color: rgb(203 213 225) transparent;
}

.modal-panel::-webkit-scrollbar {
    width: 8px;
}

.modal-panel::-webkit-scrollbar-track {
    background: transparent;
}

.modal-panel::-webkit-scrollbar-thumb {
    border-radius: 9999px;
    background: rgb(203 213 225);
}

:global(.dark) .modal-panel {
    scrollbar-color: rgb(71 85 105) transparent;
}

:global(.dark) .modal-panel::-webkit-scrollbar-thumb {
    background: rgb(71 85 105);
}

.blob-shape {
    animation: blob-float 12s ease-in-out infinite;
    transform-origin: center;
    will-change: transform;
}

.blob-shape-delayed {
    animation: blob-float-delayed 15s ease-in-out infinite;
    transform-origin: center;
    will-change: transform;
}

.blob-shape-slow {
    animation: blob-float-slow 18s ease-in-out infinite;
    transform-origin: center;
    will-change: transform;
}

@keyframes blob-float {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    33% {
        transform: translate3d(25px, 15px, 0) scale(1.05);
    }

    66% {
        transform: translate3d(-15px, 30px, 0) scale(0.96);
    }
}

@keyframes blob-float-delayed {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    40% {
        transform: translate3d(-30px, 20px, 0) scale(1.08);
    }

    75% {
        transform: translate3d(15px, -15px, 0) scale(0.95);
    }
}

@keyframes blob-float-slow {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(0, 35px, 0) scale(1.1);
    }
}

@media (prefers-reduced-motion: reduce) {
    .blob-shape,
    .blob-shape-delayed,
    .blob-shape-slow,
    .modal-panel {
        animation: none;
        transition: none;
    }
}

@media (max-width: 640px) {
    .blob-shape {
        width: 24rem;
        height: 24rem;
    }

    .blob-shape-delayed {
        width: 26rem;
        height: 26rem;
    }

    .blob-shape-slow {
        width: 25rem;
        height: 25rem;
    }
}
</style>
