<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    ArrowDown,
    ArrowUp,
    Check,
    ChevronLeft,
    ChevronRight,
    Edit,
    Eye,
    FileText,
    MapPin,
    Plus,
    Route as RouteIcon,
    Search,
    Trash2,
    X,
} from "lucide-vue-next";
import {
    currentLanguage,
    localizedValue,
    type LanguageCode,
} from "@/composables/useLocale";

/**
 * Layout dipasang sekali lewat defineOptions (persistent layout).
 * Jangan membungkus template dengan <AppLayout> lagi.
 *
 * Jika layout default sudah diset global di app.ts, hapus import
 * AppLayout dan blok defineOptions ini.
 */
defineOptions({
    layout: AppLayout,
});

/**
 * ============================================================
 * ENDPOINT
 * ============================================================
 *
 * GET     /admin/hubungan-investor/rute-pelayaran-lokasi
 * POST    /admin/hubungan-investor/rute-pelayaran-lokasi
 * PUT     /admin/hubungan-investor/rute-pelayaran-lokasi/{id}
 * DELETE  /admin/hubungan-investor/rute-pelayaran-lokasi/{id}
 * PATCH   /admin/hubungan-investor/rute-pelayaran-lokasi/{id}/toggle-aktif
 * PATCH   /admin/hubungan-investor/rute-pelayaran-lokasi/{id}/move
 *
 * Untuk upload gambar saat update: POST + _method=PUT
 */
const BASE_URL = "/admin/hubungan-investor/rute-pelayaran-lokasi";

/**
 * ============================================================
 * TYPES
 * ============================================================
 */

interface Rute {
    id: number;

    // Bahasa Indonesia
    nama_rute: string;
    jalur: string;
    deskripsi: string | null;

    // Bahasa Inggris
    nama_rute_en: string | null;
    jalur_en: string | null;
    deskripsi_en: string | null;

    // Bahasa Mandarin
    nama_rute_zh: string | null;
    jalur_zh: string | null;
    deskripsi_zh: string | null;

    // Data teknis
    jarak: string | number;
    satuan_jarak: string;
    waktu_tempuh: string;

    // Asal & tujuan
    asal: string | null;
    tujuan: string | null;

    // Lokasi
    latitude: string | number | null;
    longitude: string | number | null;

    // GeoJSON
    geometry: Record<string, unknown> | null;

    // Gambar
    gambar: string | null;

    // Pengaturan
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

/**
 * ============================================================
 * PROPS
 * ============================================================
 */

const props = defineProps<{
    rutes: PaginatedRutes;
    filters: Filters;
}>();

/**
 * ============================================================
 * LOCALIZATION
 * ============================================================
 */

const localized = (object: Rute | null | undefined, field: string): string => {
    return localizedValue(
        object as Record<string, unknown> | null | undefined,
        field,
    );
};

/**
 * ============================================================
 * UI TRANSLATIONS
 * ============================================================
 */

const t = computed(() => {
    const language: LanguageCode = currentLanguage.value;

    return {
        id: {
            title: "Rute Pelayaran",
            subtitle:
                "Kelola informasi rute pelayaran dan konektivitas logistik.",
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
            noResult: "Tidak ada data yang sesuai dengan filter.",
            resetFilter: "Reset Filter",

            create: "Tambah Rute",
            edit: "Edit Rute",
            detail: "Detail Rute",
            delete: "Hapus Rute",

            close: "Tutup",
            save: "Simpan",
            update: "Perbarui",
            cancel: "Batal",

            indonesia: "Bahasa Indonesia",
            english: "English",
            chinese: "中文",

            name: "Nama Rute",
            description: "Deskripsi",
            origin: "Asal",
            destination: "Tujuan",
            image: "Gambar",
            order: "Urutan",
            activeStatus: "Status Aktif",

            confirmDelete: "Yakin ingin menghapus rute ini?",

            moveUp: "Naikkan urutan",
            moveDown: "Turunkan urutan",
        },

        en: {
            title: "Shipping Routes",
            subtitle:
                "Manage shipping route and logistics connectivity information.",
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
            noResult: "No data matches the current filter.",
            resetFilter: "Reset Filter",

            create: "Add Route",
            edit: "Edit Route",
            detail: "Route Detail",
            delete: "Delete Route",

            close: "Close",
            save: "Save",
            update: "Update",
            cancel: "Cancel",

            indonesia: "Bahasa Indonesia",
            english: "English",
            chinese: "中文",

            name: "Route Name",
            description: "Description",
            origin: "Origin",
            destination: "Destination",
            image: "Image",
            order: "Order",
            activeStatus: "Active Status",

            confirmDelete: "Are you sure you want to delete this route?",

            moveUp: "Move up",
            moveDown: "Move down",
        },

        zh: {
            title: "航运路线",
            subtitle: "管理航运路线和物流连接信息。",
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
            noResult: "没有符合当前筛选条件的数据。",
            resetFilter: "重置筛选",

            create: "添加路线",
            edit: "编辑路线",
            detail: "路线详情",
            delete: "删除路线",

            close: "关闭",
            save: "保存",
            update: "更新",
            cancel: "取消",

            indonesia: "Bahasa Indonesia",
            english: "English",
            chinese: "中文",

            name: "路线名称",
            description: "描述",
            origin: "起点",
            destination: "终点",
            image: "图片",
            order: "排序",
            activeStatus: "状态",

            confirmDelete: "确定要删除此路线吗？",

            moveUp: "上移",
            moveDown: "下移",
        },
    }[language];
});

/**
 * ============================================================
 * SEARCH & FILTER
 * ============================================================
 */

const search = ref(props.filters.search ?? "");

const status = ref(
    props.filters.aktif === true || props.filters.aktif === "1"
        ? "1"
        : props.filters.aktif === false || props.filters.aktif === "0"
          ? "0"
          : "",
);

/**
 * Navbar adalah satu-satunya language switcher.
 * Saat bahasa berubah, search di-reset.
 */
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

/**
 * ============================================================
 * MODAL STATE
 * ============================================================
 */

const showForm = ref(false);
const showDetail = ref(false);
const showDelete = ref(false);

const editing = ref<Rute | null>(null);
const selected = ref<Rute | null>(null);

/**
 * ============================================================
 * FORM
 * ============================================================
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

const resetForm = () => {
    form.reset();
    form.clearErrors();

    form.satuan_jarak = "km";
    form.aktif = true;

    form.urutan = props.rutes.total + 1;

    form.gambar = null;
};

const closeForm = () => {
    showForm.value = false;
    editing.value = null;
};

/**
 * ============================================================
 * CREATE
 * ============================================================
 */

const openCreate = () => {
    editing.value = null;

    resetForm();

    showForm.value = true;
};

/**
 * ============================================================
 * EDIT
 * ============================================================
 */

const openEdit = (rute: Rute) => {
    editing.value = rute;

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

    form.urutan = rute.urutan;
    form.aktif = rute.aktif;

    showForm.value = true;
};

/**
 * ============================================================
 * SUBMIT
 * ============================================================
 *
 * Update + upload file: POST /url/{id} dengan _method=PUT,
 * dimasukkan lewat transform().
 */

const submit = () => {
    if (form.processing) {
        return;
    }

    /**
     * CREATE
     */
    if (!editing.value) {
        form.transform((data) => ({
            ...data,
            _method: undefined,
        })).post(BASE_URL, {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                closeForm();
                resetForm();
            },
        });

        return;
    }

    /**
     * UPDATE
     */
    const id = editing.value.id;

    form.transform((data) => ({
        ...data,
        _method: "PUT",
    })).post(`${BASE_URL}/${id}`, {
        forceFormData: true,
        preserveScroll: true,

        onSuccess: () => {
            closeForm();
            resetForm();
        },
    });
};

/**
 * ============================================================
 * DETAIL
 * ============================================================
 */

const openDetail = (rute: Rute) => {
    selected.value = rute;
    showDetail.value = true;
};

/**
 * ============================================================
 * DELETE
 * ============================================================
 */

const askDelete = (rute: Rute) => {
    selected.value = rute;
    showDelete.value = true;
};

const destroy = () => {
    if (!selected.value) {
        return;
    }

    router.delete(`${BASE_URL}/${selected.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDelete.value = false;
            selected.value = null;
        },
    });
};

/**
 * ============================================================
 * TOGGLE ACTIVE
 * ============================================================
 */

const toggleAktif = (rute: Rute) => {
    router.patch(
        `${BASE_URL}/${rute.id}/toggle-aktif`,
        {},
        {
            preserveScroll: true,
        },
    );
};

/**
 * ============================================================
 * MOVE ORDER
 * ============================================================
 */

const move = (rute: Rute, direction: "up" | "down") => {
    router.patch(
        `${BASE_URL}/${rute.id}/move`,
        {
            direction,
        },
        {
            preserveScroll: true,
        },
    );
};

/**
 * ============================================================
 * HELPERS
 * ============================================================
 */

const imageUrl = (path: string | null): string => {
    return path ? `/storage/${path}` : "";
};

const formatDistance = (rute: Rute): string => {
    return `${rute.jarak} ${rute.satuan_jarak || "km"}`;
};

const currentPage = computed(() => props.rutes.current_page);

const perPage = computed(() => props.rutes.per_page || 10);

const rowNumber = (index: number): number =>
    (currentPage.value - 1) * perPage.value + index + 1;
</script>

<template>
    <Head :title="t.title" />

    <!-- Satu root element. Layout dari defineOptions, bukan <AppLayout>. -->
    <div>
        <div
            class="relative min-h-screen overflow-hidden bg-slate-50 dark:bg-slate-950"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-60 dark:opacity-30"
                style="
                    background-image:
                        linear-gradient(
                            rgba(59, 130, 246, 0.06) 1px,
                            transparent 1px
                        ),
                        linear-gradient(
                            90deg,
                            rgba(59, 130, 246, 0.06) 1px,
                            transparent 1px
                        );
                    background-size: 40px 40px;
                "
            />

            <div
                class="relative mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8"
            >
                <!-- Header -->
                <div
                    class="rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-xl shadow-slate-200/40 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20"
                >
                    <div
                        class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20"
                            >
                                <RouteIcon class="h-7 w-7" />
                            </div>

                            <div>
                                <h1
                                    class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ t.title }}
                                </h1>
                                <p
                                    class="mt-1 max-w-2xl text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ t.subtitle }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700"
                            @click="openCreate"
                        >
                            <Plus class="h-4 w-4" />
                            {{ t.add }}
                        </button>
                    </div>
                </div>

                <!-- Filter -->
                <div
                    class="rounded-3xl border border-slate-200/80 bg-white/90 p-4 shadow-lg shadow-slate-200/30 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90"
                >
                    <div class="grid gap-3 md:grid-cols-[1fr_220px]">
                        <div class="relative">
                            <Search
                                class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="search"
                                type="search"
                                :placeholder="t.search"
                                class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            />
                        </div>

                        <select
                            v-model="status"
                            class="h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        >
                            <option value="">{{ t.all }}</option>
                            <option value="1">{{ t.active }}</option>
                            <option value="0">{{ t.inactive }}</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div
                    class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-xl shadow-slate-200/30 dark:border-slate-800 dark:bg-slate-900/95"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1050px] text-left">
                            <thead
                                class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-950/60"
                            >
                                <tr
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    <th class="px-6 py-4">#</th>
                                    <th class="px-6 py-4">{{ t.route }}</th>
                                    <th class="px-6 py-4">{{ t.path }}</th>
                                    <th class="px-6 py-4">{{ t.distance }}</th>
                                    <th class="px-6 py-4">{{ t.duration }}</th>
                                    <th class="px-6 py-4">{{ t.status }}</th>
                                    <th class="px-6 py-4 text-right">
                                        {{ t.action }}
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="(rute, index) in props.rutes.data"
                                    :key="rute.id"
                                    class="group transition hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                                >
                                    <td class="px-6 py-5 align-top">
                                        <span
                                            class="font-semibold text-slate-500 dark:text-slate-400"
                                        >
                                            {{ rowNumber(index) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 align-top">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="h-11 w-11 shrink-0 overflow-hidden rounded-xl bg-blue-50 dark:bg-blue-950/40"
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
                                                    class="h-full w-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="flex h-full w-full items-center justify-center text-blue-500"
                                                >
                                                    <RouteIcon
                                                        class="h-5 w-5"
                                                    />
                                                </div>
                                            </div>

                                            <div class="min-w-0">
                                                <button
                                                    type="button"
                                                    class="text-left font-semibold text-slate-900 transition hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
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
                                                    class="mt-1 line-clamp-2 max-w-xs text-xs text-slate-500 dark:text-slate-400"
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

                                    <td class="px-6 py-5 align-top">
                                        <div
                                            class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300"
                                        >
                                            <MapPin
                                                class="h-4 w-4 shrink-0 text-blue-500"
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

                                    <td class="px-6 py-5 align-top">
                                        <span
                                            class="font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ formatDistance(rute) }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-5 align-top text-sm text-slate-600 dark:text-slate-300"
                                    >
                                        {{ rute.waktu_tempuh || "-" }}
                                    </td>

                                    <td class="px-6 py-5 align-top">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold transition"
                                            :class="
                                                rute.aktif
                                                    ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400'
                                            "
                                            @click="toggleAktif(rute)"
                                        >
                                            <span
                                                class="h-1.5 w-1.5 rounded-full"
                                                :class="
                                                    rute.aktif
                                                        ? 'bg-emerald-500'
                                                        : 'bg-slate-400'
                                                "
                                            />
                                            {{
                                                rute.aktif
                                                    ? t.active
                                                    : t.inactive
                                            }}
                                        </button>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex justify-end gap-1.5">
                                            <button
                                                type="button"
                                                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800"
                                                :title="t.detail"
                                                @click="openDetail(rute)"
                                            >
                                                <Eye class="h-4 w-4" />
                                            </button>

                                            <button
                                                type="button"
                                                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-blue-600 disabled:opacity-30 dark:hover:bg-slate-800"
                                                :title="t.moveUp"
                                                :disabled="rute.urutan <= 1"
                                                @click="move(rute, 'up')"
                                            >
                                                <ArrowUp class="h-4 w-4" />
                                            </button>

                                            <button
                                                type="button"
                                                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-blue-600 disabled:opacity-30 dark:hover:bg-slate-800"
                                                :title="t.moveDown"
                                                :disabled="
                                                    rute.urutan >=
                                                    props.rutes.total
                                                "
                                                @click="move(rute, 'down')"
                                            >
                                                <ArrowDown class="h-4 w-4" />
                                            </button>

                                            <button
                                                type="button"
                                                class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800"
                                                :title="t.edit"
                                                @click="openEdit(rute)"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </button>

                                            <button
                                                type="button"
                                                class="rounded-xl p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30"
                                                :title="t.delete"
                                                @click="askDelete(rute)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="props.rutes.data.length === 0">
                                    <td
                                        colspan="7"
                                        class="px-6 py-16 text-center"
                                    >
                                        <div
                                            class="mx-auto flex max-w-sm flex-col items-center"
                                        >
                                            <div
                                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800"
                                            >
                                                <RouteIcon class="h-7 w-7" />
                                            </div>
                                            <p
                                                class="mt-4 font-semibold text-slate-700 dark:text-slate-200"
                                            >
                                                {{
                                                    hasActiveFilter
                                                        ? t.noResult
                                                        : t.noData
                                                }}
                                            </p>

                                            <button
                                                v-if="hasActiveFilter"
                                                type="button"
                                                class="mt-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                                @click="resetFilter"
                                            >
                                                <X class="h-4 w-4" />
                                                {{ t.resetFilter }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.rutes.last_page > 1"
                        class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                    >
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ props.rutes.from }}–{{ props.rutes.to }} /
                            {{ props.rutes.total }}
                        </p>

                        <div class="flex items-center gap-1">
                            <a
                                v-for="link in props.rutes.links"
                                :key="link.label"
                                :href="link.url || undefined"
                                class="flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-xs font-semibold transition"
                                :class="
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                                "
                                @click.prevent="
                                    link.url &&
                                    router.visit(link.url, {
                                        preserveScroll: true,
                                    })
                                "
                            >
                                <ChevronLeft
                                    v-if="link.label.includes('Previous')"
                                    class="h-4 w-4"
                                />
                                <ChevronRight
                                    v-else-if="link.label.includes('Next')"
                                    class="h-4 w-4"
                                />
                                <span v-else v-html="link.label" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <Teleport to="body">
            <div
                v-if="showForm"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
                @click.self="closeForm"
            >
                <div
                    class="max-h-[92vh] w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-2xl dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-800"
                    >
                        <div>
                            <h2
                                class="text-lg font-bold text-slate-900 dark:text-white"
                            >
                                {{ editing ? t.edit : t.create }}
                            </h2>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                {{ t.title }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                            @click="closeForm"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form
                        class="max-h-[calc(92vh-88px)] overflow-y-auto p-6"
                        @submit.prevent="submit"
                    >
                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Indonesia -->
                            <div class="md:col-span-2">
                                <div
                                    class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    <FileText class="h-4 w-4 text-blue-500" />
                                    {{ t.indonesia }}
                                </div>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <label class="space-y-2">
                                        <span
                                            class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                            >{{ t.name }} *</span
                                        >
                                        <input
                                            v-model="form.nama_rute"
                                            type="text"
                                            class="field"
                                        />
                                        <span
                                            v-if="form.errors.nama_rute"
                                            class="error"
                                            >{{ form.errors.nama_rute }}</span
                                        >
                                    </label>

                                    <label class="space-y-2">
                                        <span
                                            class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                            >{{ t.path }} *</span
                                        >
                                        <input
                                            v-model="form.jalur"
                                            type="text"
                                            class="field"
                                        />
                                        <span
                                            v-if="form.errors.jalur"
                                            class="error"
                                            >{{ form.errors.jalur }}</span
                                        >
                                    </label>

                                    <label class="space-y-2 md:col-span-2">
                                        <span
                                            class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                            >{{ t.description }}</span
                                        >
                                        <textarea
                                            v-model="form.deskripsi"
                                            rows="3"
                                            class="field resize-none"
                                        />
                                    </label>
                                </div>
                            </div>

                            <!-- English -->
                            <div class="md:col-span-2">
                                <div
                                    class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    <FileText class="h-4 w-4 text-blue-500" />
                                    {{ t.english }}
                                </div>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <label class="space-y-2">
                                        <span
                                            class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                            >{{ t.name }}</span
                                        >
                                        <input
                                            v-model="form.nama_rute_en"
                                            type="text"
                                            class="field"
                                        />
                                    </label>

                                    <label class="space-y-2">
                                        <span
                                            class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                            >{{ t.path }}</span
                                        >
                                        <input
                                            v-model="form.jalur_en"
                                            type="text"
                                            class="field"
                                        />
                                    </label>

                                    <label class="space-y-2 md:col-span-2">
                                        <span
                                            class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                            >{{ t.description }}</span
                                        >
                                        <textarea
                                            v-model="form.deskripsi_en"
                                            rows="3"
                                            class="field resize-none"
                                        />
                                    </label>
                                </div>
                            </div>

                            <!-- Mandarin -->
                            <div class="md:col-span-2">
                                <div
                                    class="mb-3 flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    <FileText class="h-4 w-4 text-blue-500" />
                                    {{ t.chinese }}
                                </div>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <label class="space-y-2">
                                        <span
                                            class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                            >{{ t.name }}</span
                                        >
                                        <input
                                            v-model="form.nama_rute_zh"
                                            type="text"
                                            class="field"
                                        />
                                    </label>

                                    <label class="space-y-2">
                                        <span
                                            class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                            >{{ t.path }}</span
                                        >
                                        <input
                                            v-model="form.jalur_zh"
                                            type="text"
                                            class="field"
                                        />
                                    </label>

                                    <label class="space-y-2 md:col-span-2">
                                        <span
                                            class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                            >{{ t.description }}</span
                                        >
                                        <textarea
                                            v-model="form.deskripsi_zh"
                                            rows="3"
                                            class="field resize-none"
                                        />
                                    </label>
                                </div>
                            </div>

                            <!-- Distance -->
                            <label class="space-y-2">
                                <span
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                    >{{ t.distance }} *</span
                                >
                                <div class="grid grid-cols-[1fr_110px] gap-2">
                                    <input
                                        v-model="form.jarak"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="field"
                                    />
                                    <input
                                        v-model="form.satuan_jarak"
                                        type="text"
                                        class="field"
                                    />
                                </div>
                                <span v-if="form.errors.jarak" class="error">{{
                                    form.errors.jarak
                                }}</span>
                            </label>

                            <label class="space-y-2">
                                <span
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                    >{{ t.duration }} *</span
                                >
                                <input
                                    v-model="form.waktu_tempuh"
                                    type="text"
                                    class="field"
                                />
                                <span
                                    v-if="form.errors.waktu_tempuh"
                                    class="error"
                                    >{{ form.errors.waktu_tempuh }}</span
                                >
                            </label>

                            <label class="space-y-2">
                                <span
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                    >{{ t.origin }}</span
                                >
                                <input
                                    v-model="form.asal"
                                    type="text"
                                    class="field"
                                />
                            </label>

                            <label class="space-y-2">
                                <span
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                    >{{ t.destination }}</span
                                >
                                <input
                                    v-model="form.tujuan"
                                    type="text"
                                    class="field"
                                />
                            </label>

                            <label class="space-y-2">
                                <span
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                    >Latitude</span
                                >
                                <input
                                    v-model="form.latitude"
                                    type="number"
                                    step="0.0000001"
                                    class="field"
                                />
                            </label>

                            <label class="space-y-2">
                                <span
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                    >Longitude</span
                                >
                                <input
                                    v-model="form.longitude"
                                    type="number"
                                    step="0.0000001"
                                    class="field"
                                />
                            </label>

                            <label class="space-y-2 md:col-span-2">
                                <span
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                    >GeoJSON</span
                                >
                                <textarea
                                    v-model="form.geometry"
                                    rows="4"
                                    class="field resize-none font-mono text-xs"
                                    placeholder='{"type":"LineString","coordinates":[[106.0,-6.0],[106.1,-6.1]]}'
                                />
                                <span
                                    v-if="form.errors.geometry"
                                    class="error"
                                    >{{ form.errors.geometry }}</span
                                >
                            </label>

                            <label class="space-y-2">
                                <span
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                    >{{ t.image }}</span
                                >
                                <input
                                    type="file"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50 p-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300"
                                    @change="
                                        form.gambar =
                                            ($event.target as HTMLInputElement)
                                                .files?.[0] ?? null
                                    "
                                />
                                <span v-if="form.errors.gambar" class="error">{{
                                    form.errors.gambar
                                }}</span>
                            </label>

                            <label class="space-y-2">
                                <span
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                                    >{{ t.order }}</span
                                >
                                <input
                                    v-model="form.urutan"
                                    type="number"
                                    min="0"
                                    class="field"
                                />
                                <span v-if="form.errors.urutan" class="error">{{
                                    form.errors.urutan
                                }}</span>
                            </label>

                            <label
                                class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2 dark:border-slate-700 dark:bg-slate-950"
                            >
                                <input
                                    v-model="form.aktif"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span
                                    class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >{{ t.activeStatus }}</span
                                >
                            </label>
                        </div>

                        <div
                            class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                class="rounded-2xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeForm"
                            >
                                {{ t.cancel }}
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                            >
                                <Check class="h-4 w-4" />
                                {{ editing ? t.update : t.save }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Detail Modal -->
        <Teleport to="body">
            <div
                v-if="showDetail && selected"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
                @click.self="showDetail = false"
            >
                <div
                    class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <RouteIcon class="h-6 w-6" />
                            </div>

                            <div>
                                <h2
                                    class="text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    {{ localized(selected, "nama_rute") }}
                                </h2>
                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ localized(selected, "jalur") }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                            @click="showDetail = false"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div
                        v-if="selected.gambar"
                        class="mt-6 overflow-hidden rounded-2xl"
                    >
                        <img
                            :src="imageUrl(selected.gambar)"
                            :alt="localized(selected, 'nama_rute')"
                            class="max-h-80 w-full object-cover"
                        />
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="detail-card">
                            <span>{{ t.order }}</span>
                            <strong>{{ selected.urutan }}</strong>
                        </div>
                        <div class="detail-card">
                            <span>{{ t.status }}</span>
                            <strong>{{
                                selected.aktif ? t.active : t.inactive
                            }}</strong>
                        </div>
                        <div class="detail-card">
                            <span>{{ t.distance }}</span>
                            <strong>{{ formatDistance(selected) }}</strong>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="detail-card">
                            <span>{{ t.origin }}</span>
                            <strong>{{ selected.asal || "-" }}</strong>
                        </div>
                        <div class="detail-card">
                            <span>{{ t.destination }}</span>
                            <strong>{{ selected.tujuan || "-" }}</strong>
                        </div>
                        <div class="detail-card">
                            <span>{{ t.duration }}</span>
                            <strong>{{ selected.waktu_tempuh || "-" }}</strong>
                        </div>
                        <div class="detail-card">
                            <span>Latitude / Longitude</span>
                            <strong>
                                {{ selected.latitude ?? "-" }},
                                {{ selected.longitude ?? "-" }}
                            </strong>
                        </div>
                    </div>

                    <div
                        class="mt-4 rounded-2xl border border-slate-200 p-4 dark:border-slate-800"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            {{ t.description }}
                        </p>
                        <p
                            class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                        >
                            {{ localized(selected, "deskripsi") || "-" }}
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            type="button"
                            class="rounded-2xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200"
                            @click="showDetail = false"
                        >
                            {{ t.close }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Delete Modal -->
        <Teleport to="body">
            <div
                v-if="showDelete && selected"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
                @click.self="showDelete = false"
            >
                <div
                    class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl dark:bg-slate-900"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/30"
                    >
                        <Trash2 class="h-5 w-5" />
                    </div>
                    <h2
                        class="mt-4 text-lg font-bold text-slate-900 dark:text-white"
                    >
                        {{ t.delete }}
                    </h2>
                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{ t.confirmDelete }}
                        <strong class="text-slate-700 dark:text-slate-200">
                            {{ localized(selected, "nama_rute") }}
                        </strong>
                    </p>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-2xl px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="showDelete = false"
                        >
                            {{ t.cancel }}
                        </button>
                        <button
                            type="button"
                            class="rounded-2xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700"
                            @click="destroy"
                        >
                            {{ t.delete }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
.field {
    width: 100%;
    border-radius: 1rem;
    border: 1px solid rgb(226 232 240);
    background: rgb(248 250 252);
    padding: 0.7rem 0.9rem;
    font-size: 0.875rem;
    outline: none;
    transition: 150ms ease;
}

.field:focus {
    border-color: rgb(59 130 246);
    box-shadow: 0 0 0 4px rgb(59 130 246 / 0.1);
}

.error {
    display: block;
    font-size: 0.75rem;
    color: rgb(220 38 38);
}

.detail-card {
    border-radius: 1rem;
    border: 1px solid rgb(226 232 240);
    padding: 1rem;
}

.detail-card span {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgb(148 163 184);
}

.detail-card strong {
    display: block;
    margin-top: 0.35rem;
    font-size: 0.875rem;
    color: rgb(51 65 85);
}

:global(.dark) .field {
    border-color: rgb(51 65 85);
    background: rgb(2 6 23);
    color: white;
}

:global(.dark) .detail-card {
    border-color: rgb(51 65 85);
}

:global(.dark) .detail-card strong {
    color: rgb(226 232 240);
}
</style>
