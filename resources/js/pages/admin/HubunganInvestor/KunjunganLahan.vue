<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import {
    Ban,
    Building2,
    CalendarCheck,
    Check,
    CheckCheck,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    ClipboardList,
    Clock,
    Eye,
    FileText,
    Mail,
    MapPin,
    Phone,
    Search,
    Trash2,
    UserRound,
    Users,
    X,
} from "lucide-vue-next";

/**
 * Route utama:
 * GET     /hubungan-investor/kunjungan-lahan
 * POST    /hubungan-investor/kunjungan-lahan
 * PUT     /hubungan-investor/kunjungan-lahan/{id}
 * DELETE  /hubungan-investor/kunjungan-lahan/{id}
 * PATCH   /hubungan-investor/kunjungan-lahan/{id}/approve
 * PATCH   /hubungan-investor/kunjungan-lahan/{id}/reject
 * PATCH   /hubungan-investor/kunjungan-lahan/{id}/complete
 * PATCH   /hubungan-investor/kunjungan-lahan/{id}/cancel
 */

const BASE_URL = "/admin/hubungan-investor/kunjungan-lahan";

type Status = "pending" | "disetujui" | "ditolak" | "selesai";

interface Kunjungan {
    id: number;
    nomor_registrasi: string;
    nama: string;
    instansi: string | null;
    jabatan: string | null;
    email: string;
    telepon: string;
    tanggal_kunjungan: string;
    waktu_mulai: string;
    waktu_selesai: string;
    jumlah_peserta: number;
    area_lahan: string;
    keperluan: string | null;
    memerlukan_pendamping: boolean;
    status: Status;
    catatan_admin: string | null;
    disetujui_at: string | null;
    ditolak_at: string | null;
    selesai_at: string | null;
    created_at: string;
    updated_at?: string;
}

interface PageLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator {
    current_page: number;
    data: Kunjungan[];
    first_page_url: string | null;
    from: number | null;
    last_page: number;
    last_page_url: string | null;
    links: PageLink[];
    next_page_url: string | null;
    path?: string;
    per_page?: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface Filters {
    search?: string;
    status?: string;
    tanggal?: string;
}

const props = defineProps<{
    kunjunganLahan: Paginator;
    filters: Filters;
    statuses: Status[];
}>();

/* =========================================================
 * STYLE
 * ======================================================= */

const cardClass =
    "rounded-2xl border border-slate-200/80 bg-white/90 shadow-sm shadow-slate-200/50 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80 dark:shadow-none";

const inputClass =
    "min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500";

const inputErrorClass =
    "border-red-400 focus:border-red-500 focus:ring-red-500/10 dark:border-red-500/70";

const errorClass = "mt-1.5 text-xs text-red-600 dark:text-red-400";

const labelClass =
    "mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300";

const thClass =
    "px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400";

const primaryBtnClass =
    "inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto";

const secondaryBtnClass =
    "inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800";

const rowIconBtnClass =
    "rounded-lg p-2 text-slate-400 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-40";

const closeBtnClass =
    "shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:opacity-50 dark:hover:bg-slate-800 dark:hover:text-slate-200";

const modalBackdropClass =
    "fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-950/50 p-3 backdrop-blur-sm sm:p-6";

const modalCardClass =
    "my-auto flex max-h-[calc(100dvh-1.5rem)] w-full flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-900/10 sm:max-h-[calc(100dvh-3rem)] dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/40";

const infoCardClass =
    "rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50";

/* =========================================================
 * STATE
 * ======================================================= */

const statusOptions: { value: Status; label: string }[] = [
    { value: "pending", label: "Menunggu" },
    { value: "disetujui", label: "Disetujui" },
    { value: "ditolak", label: "Ditolak" },
    { value: "selesai", label: "Selesai" },
];

const columns = [
    { label: "No", width: "w-16", align: "text-center" },
    { label: "Pemohon", width: "min-w-[260px]", align: "text-left" },
    { label: "Jadwal", width: "min-w-[220px]", align: "text-left" },
    { label: "Peserta & Area", width: "min-w-[240px]", align: "text-left" },
    { label: "Status", width: "w-36", align: "text-left" },
    { label: "Aksi", width: "w-52", align: "text-right" },
];

const isInitialLoading = ref(true);
const isRequesting = ref(false);

const isPageLoading = computed(
    () => isInitialLoading.value || isRequesting.value,
);

const search = ref(props.filters?.search ?? "");
const selectedStatus = ref(props.filters?.status ?? "");

const items = computed(() => props.kunjunganLahan?.data ?? []);
const total = computed(() => props.kunjunganLahan?.total ?? 0);
const lastPage = computed(() => props.kunjunganLahan?.last_page ?? 1);
const fromRow = computed(() => props.kunjunganLahan?.from ?? 0);
const toRow = computed(() => props.kunjunganLahan?.to ?? 0);

const firstPageUrl = computed(
    () => props.kunjunganLahan?.first_page_url ?? null,
);

const lastPageUrl = computed(() => props.kunjunganLahan?.last_page_url ?? null);

const previousPageUrl = computed(
    () => props.kunjunganLahan?.prev_page_url ?? null,
);

const nextPageUrl = computed(() => props.kunjunganLahan?.next_page_url ?? null);

const pageLinks = computed(() =>
    (props.kunjunganLahan?.links ?? []).slice(1, -1),
);

const isFiltered = computed(
    () => !!(search.value.trim() || selectedStatus.value),
);

const showDetailModal = ref(false);
const showStatusModal = ref(false);
const showDeleteModal = ref(false);

const selected = ref<Kunjungan | null>(null);

const nextStatus = ref<Status>("disetujui");
const catatan = ref("");
const statusError = ref("");

const processingStatus = ref(false);
const processingDelete = ref(false);

/* =========================================================
 * HELPERS
 * ======================================================= */

const getRowNumber = (index: number) => fromRow.value + index;

const timeOf = (time: string | null | undefined) =>
    time ? time.slice(0, 5) : "-";

const formatDate = (value: string | null) => {
    if (!value) return "-";

    const date = new Date(`${value.slice(0, 10)}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(date);
};

const formatDateTime = (value: string | null) => {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(date);
};

const paginationPageLabel = (label: string) =>
    label
        .replace(/<[^>]*>/g, "")
        .replace(/&[a-z#0-9]+;/gi, "")
        .trim();

const navButtonClass = (url: string | null) =>
    url
        ? "text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
        : "cursor-not-allowed text-slate-300 dark:text-slate-600";

const STATUS_META: Record<Status, { label: string; cls: string }> = {
    pending: {
        label: "Menunggu",
        cls: "bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400",
    },
    disetujui: {
        label: "Disetujui",
        cls: "bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400",
    },
    ditolak: {
        label: "Ditolak",
        cls: "bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400",
    },
    selesai: {
        label: "Selesai",
        cls: "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400",
    },
};

const statusClass = (status: Status) =>
    STATUS_META[status]?.cls ?? STATUS_META.pending.cls;

const getStatusLabel = (item: Kunjungan) =>
    STATUS_META[item.status]?.label ?? item.status;

/* =========================================================
 * STATUS ACTION
 * ======================================================= */

const STATUS_ACTION: Record<
    Exclude<Status, "pending">,
    {
        title: string;
        button: string;
        hint: string;
        btn: string;
    }
> = {
    disetujui: {
        title: "Setujui Kunjungan",
        button: "Ya, Setujui",
        hint: "Catatan untuk pemohon (opsional).",
        btn: "bg-emerald-600 hover:bg-emerald-700 focus-visible:ring-emerald-500/40",
    },
    ditolak: {
        title: "Tolak Kunjungan",
        button: "Ya, Tolak",
        hint: "Jelaskan alasan penolakan (wajib).",
        btn: "bg-red-600 hover:bg-red-700 focus-visible:ring-red-500/40",
    },
    selesai: {
        title: "Tandai Selesai",
        button: "Ya, Selesai",
        hint: "Catatan hasil kunjungan (opsional).",
        btn: "bg-blue-600 hover:bg-blue-700 focus-visible:ring-blue-500/40",
    },
};

const statusAction = computed(() => {
    if (nextStatus.value === "pending") {
        return STATUS_ACTION.disetujui;
    }

    return STATUS_ACTION[nextStatus.value];
});

/* =========================================================
 * DETAIL
 * ======================================================= */

const detailFields = computed(() => {
    const item = selected.value;

    if (!item) return [];

    return [
        {
            icon: ClipboardList,
            label: "Nomor registrasi",
            value: item.nomor_registrasi,
            mono: true,
        },
        {
            icon: UserRound,
            label: "Nama pemohon",
            value: item.nama,
        },
        {
            icon: Building2,
            label: "Instansi / jabatan",
            value:
                [item.instansi, item.jabatan].filter(Boolean).join(" · ") ||
                "-",
        },
        {
            icon: Mail,
            label: "Email",
            value: item.email,
        },
        {
            icon: Phone,
            label: "Telepon",
            value: item.telepon,
        },
        {
            icon: CalendarCheck,
            label: "Tanggal kunjungan",
            value: formatDate(item.tanggal_kunjungan),
        },
        {
            icon: Clock,
            label: "Waktu",
            value: `${timeOf(item.waktu_mulai)} – ${timeOf(item.waktu_selesai)}`,
        },
        {
            icon: Users,
            label: "Jumlah peserta",
            value: `${item.jumlah_peserta} orang`,
        },
        {
            icon: MapPin,
            label: "Area lahan",
            value: item.area_lahan,
        },
        {
            icon: UserRound,
            label: "Pendamping",
            value: item.memerlukan_pendamping
                ? "Diperlukan"
                : "Tidak diperlukan",
        },
        {
            icon: Clock,
            label: "Diajukan",
            value: formatDateTime(item.created_at),
        },
        {
            icon: Clock,
            label: "Diproses",
            value: formatDateTime(
                item.selesai_at ?? item.ditolak_at ?? item.disetujui_at,
            ),
        },
    ];
});

/* =========================================================
 * FILTER
 * ======================================================= */

let searchTimer: ReturnType<typeof setTimeout> | undefined;

function submitFilter() {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    router.get(
        BASE_URL,
        {
            search: search.value.trim() || undefined,
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ["kunjunganLahan", "filters"],
        },
    );
}

function submitSearch() {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        submitFilter();
    }, 400);
}

function clearSearchInput() {
    search.value = "";
    submitFilter();
}

function clearFilters() {
    search.value = "";
    selectedStatus.value = "";
    submitFilter();
}

function goToPage(url: string | null) {
    if (!url) return;

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ["kunjunganLahan", "filters"],
        },
    );
}

/* =========================================================
 * MODAL
 * ======================================================= */

function openDetail(item: Kunjungan) {
    selected.value = item;
    showDetailModal.value = true;
}

function closeDetail() {
    showDetailModal.value = false;
}

function openStatus(item: Kunjungan, status: Status) {
    if (status === "pending") return;

    selected.value = item;
    nextStatus.value = status;
    catatan.value = "";
    statusError.value = "";

    showDetailModal.value = false;
    showStatusModal.value = true;
}

function closeStatus() {
    if (!processingStatus.value) {
        showStatusModal.value = false;
    }
}

function openDelete(item: Kunjungan) {
    selected.value = item;
    showDeleteModal.value = true;
}

function closeDelete() {
    if (!processingDelete.value) {
        showDeleteModal.value = false;
    }
}

/* =========================================================
 * STATUS REQUEST
 * ======================================================= */

function submitStatus() {
    const item = selected.value;

    if (processingStatus.value || !item) {
        return;
    }

    const note = catatan.value.trim();

    if (nextStatus.value === "ditolak" && !note) {
        statusError.value = "Alasan penolakan wajib diisi.";
        return;
    }

    if (note.length > 1000) {
        statusError.value = "Catatan maksimal 1000 karakter.";
        return;
    }

    processingStatus.value = true;
    statusError.value = "";

    let url = "";

    if (nextStatus.value === "disetujui") {
        url = `${BASE_URL}/${item.id}/approve`;
    } else if (nextStatus.value === "ditolak") {
        url = `${BASE_URL}/${item.id}/reject`;
    } else if (nextStatus.value === "selesai") {
        url = `${BASE_URL}/${item.id}/complete`;
    }

    if (!url) {
        processingStatus.value = false;
        statusError.value = "Aksi status tidak valid.";
        return;
    }

    const payload =
        nextStatus.value === "ditolak"
            ? {
                  catatan_admin: note || null,
              }
            : {};

    router.patch(url, payload, {
        preserveScroll: true,

        onSuccess: () => {
            showStatusModal.value = false;
            selected.value = null;
        },

        onError: (errors) => {
            statusError.value =
                errors.catatan_admin || "Status gagal diperbarui. Coba lagi.";
        },

        onFinish: () => {
            processingStatus.value = false;
        },
    });
}

/* =========================================================
 * DELETE
 * ======================================================= */

function deleteKunjungan() {
    const item = selected.value;

    if (processingDelete.value || !item) {
        return;
    }

    processingDelete.value = true;

    router.delete(`${BASE_URL}/${item.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDeleteModal.value = false;
            selected.value = null;
        },

        onFinish: () => {
            processingDelete.value = false;
        },
    });
}

/* =========================================================
 * LIFECYCLE
 * ======================================================= */

const offStart = router.on("start", (event) => {
    if (!event.detail.visit?.preserveState) {
        isRequesting.value = true;
    }
});

const offFinish = router.on("finish", () => {
    isRequesting.value = false;
});

const onKey = (event: KeyboardEvent) => {
    if (event.key !== "Escape") {
        return;
    }

    closeDetail();
    closeStatus();
    closeDelete();
};

onMounted(() => {
    window.addEventListener("keydown", onKey);

    requestAnimationFrame(() => {
        isInitialLoading.value = false;
    });
});

onBeforeUnmount(() => {
    offStart();
    offFinish();

    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    window.removeEventListener("keydown", onKey);
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
            <div v-if="isInitialLoading" class="animate-pulse" role="status">
                <span class="sr-only">Memuat data...</span>
                <div class="mb-6 flex items-center gap-3">
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
                <div :class="[cardClass, 'space-y-4 p-6']">
                    <div
                        v-for="row in 6"
                        :key="row"
                        class="flex items-center gap-4"
                    >
                        <div
                            class="size-10 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"
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

            <!-- ACTUAL CONTENT -->
            <template v-else>
                <!-- HEADER -->
                <div
                    class="mb-6 flex min-w-0 items-start gap-3 sm:items-center"
                >
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-600/10 text-blue-600 shadow-sm dark:bg-blue-400/10 dark:text-blue-400"
                    >
                        <CalendarCheck class="size-5" />
                    </div>
                    <div class="min-w-0">
                        <h1
                            class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                        >
                            Kunjungan Lahan
                        </h1>
                        <p
                            class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                        >
                            Tinjau dan proses permohonan kunjungan ke lahan
                            kawasan.
                        </p>
                    </div>
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
                                maxlength="100"
                                aria-label="Cari kunjungan lahan"
                                placeholder="Cari nomor registrasi, nama, instansi, atau area..."
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
                                v-for="s in statusOptions"
                                :key="s.value"
                                :value="s.value"
                            >
                                {{ s.label }}
                            </option>
                        </select>

                        <button
                            v-if="isFiltered"
                            type="button"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="clearFilters"
                        >
                            <X class="size-4" />
                            Reset
                        </button>
                    </div>
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
                                <ClipboardList class="size-5" />
                            </div>
                            <div class="min-w-0">
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Data Permohonan Kunjungan
                                </h2>
                                <p
                                    class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    Jadwal, jumlah peserta, dan status setiap
                                    permohonan.
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
                                class="w-full min-w-[1150px] text-left text-sm"
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
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex size-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                            >
                                                {{ getRowNumber(index) }}
                                            </span>
                                        </td>

                                        <!-- PEMOHON -->
                                        <td class="px-6 py-4">
                                            <p
                                                class="truncate font-semibold text-slate-800 dark:text-slate-100"
                                            >
                                                {{ item.nama }}
                                            </p>
                                            <p
                                                class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{
                                                    [
                                                        item.instansi,
                                                        item.jabatan,
                                                    ]
                                                        .filter(Boolean)
                                                        .join(" · ") || "-"
                                                }}
                                            </p>
                                            <p
                                                class="mt-1 truncate font-mono text-xs text-blue-600 dark:text-blue-400"
                                            >
                                                {{ item.nomor_registrasi }}
                                            </p>
                                        </td>

                                        <!-- JADWAL -->
                                        <td class="px-6 py-4">
                                            <p
                                                class="font-semibold text-slate-800 dark:text-slate-100"
                                            >
                                                {{
                                                    formatDate(
                                                        item.tanggal_kunjungan,
                                                    )
                                                }}
                                            </p>
                                            <p
                                                class="mt-1 flex items-center gap-1 text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                <Clock
                                                    class="size-3.5 shrink-0"
                                                />
                                                {{ timeOf(item.waktu_mulai) }} –
                                                {{ timeOf(item.waktu_selesai) }}
                                            </p>
                                        </td>

                                        <!-- PESERTA & AREA -->
                                        <td class="px-6 py-4">
                                            <p
                                                class="font-medium text-slate-700 dark:text-slate-200"
                                            >
                                                {{ item.jumlah_peserta }}
                                                peserta
                                            </p>
                                            <p
                                                class="mt-1 flex items-center gap-1 text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                <MapPin
                                                    class="size-3.5 shrink-0"
                                                />
                                                <span class="truncate">{{
                                                    item.area_lahan
                                                }}</span>
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

                                        <!-- AKSI -->
                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-1">
                                                <button
                                                    type="button"
                                                    title="Lihat detail"
                                                    aria-label="Lihat detail kunjungan"
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400',
                                                    ]"
                                                    @click="openDetail(item)"
                                                >
                                                    <Eye class="size-4" />
                                                </button>

                                                <template
                                                    v-if="
                                                        item.status ===
                                                        'pending'
                                                    "
                                                >
                                                    <button
                                                        type="button"
                                                        title="Setujui"
                                                        aria-label="Setujui kunjungan"
                                                        :class="[
                                                            rowIconBtnClass,
                                                            'hover:bg-emerald-50 hover:text-emerald-600 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-400',
                                                        ]"
                                                        @click="
                                                            openStatus(
                                                                item,
                                                                'disetujui',
                                                            )
                                                        "
                                                    >
                                                        <Check class="size-4" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        title="Tolak"
                                                        aria-label="Tolak kunjungan"
                                                        :class="[
                                                            rowIconBtnClass,
                                                            'hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400',
                                                        ]"
                                                        @click="
                                                            openStatus(
                                                                item,
                                                                'ditolak',
                                                            )
                                                        "
                                                    >
                                                        <Ban class="size-4" />
                                                    </button>
                                                </template>

                                                <button
                                                    v-else-if="
                                                        item.status ===
                                                        'disetujui'
                                                    "
                                                    type="button"
                                                    title="Tandai selesai"
                                                    aria-label="Tandai kunjungan selesai"
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-emerald-50 hover:text-emerald-600 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-400',
                                                    ]"
                                                    @click="
                                                        openStatus(
                                                            item,
                                                            'selesai',
                                                        )
                                                    "
                                                >
                                                    <CheckCheck
                                                        class="size-4"
                                                    />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Hapus kunjungan"
                                                    aria-label="Hapus kunjungan"
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
                                permohonan
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
                            <CalendarCheck class="size-7" />
                        </div>
                        <p
                            class="mt-4 font-semibold text-slate-800 dark:text-slate-200"
                        >
                            {{
                                isFiltered
                                    ? "Permohonan tidak ditemukan"
                                    : "Belum ada permohonan kunjungan"
                            }}
                        </p>
                        <p
                            class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{
                                isFiltered
                                    ? "Tidak ditemukan data yang sesuai dengan filter."
                                    : "Permohonan yang dikirim lewat formulir publik akan muncul di sini."
                            }}
                        </p>
                        <button
                            v-if="isFiltered"
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

        <!-- DETAIL MODAL -->
        <Transition name="modal">
            <div
                v-if="showDetailModal && selected"
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
                        class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                id="detail-modal-title"
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Kunjungan Lahan
                            </h2>
                            <p
                                class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap permohonan kunjungan.
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
                        <div class="flex flex-wrap items-center gap-2">
                            <h3
                                class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ selected.nama }}
                            </h3>
                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                :class="statusClass(selected.status)"
                            >
                                {{ getStatusLabel(selected) }}
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
                                        >{{ field.label }}</span
                                    >
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
                                    Keperluan
                                </h4>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                            >
                                <p class="whitespace-pre-line break-words">
                                    {{ selected.keperluan || "-" }}
                                </p>
                            </div>
                        </div>

                        <div v-if="selected.catatan_admin" class="mt-5">
                            <div class="mb-2 flex items-center gap-2">
                                <ClipboardList class="size-4 text-blue-500" />
                                <h4
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Catatan admin
                                </h4>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                            >
                                <p class="whitespace-pre-line break-words">
                                    {{ selected.catatan_admin }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            :class="secondaryBtnClass"
                            @click="closeDetail"
                        >
                            Tutup
                        </button>
                        <template v-if="selected.status === 'pending'">
                            <button
                                type="button"
                                :class="secondaryBtnClass"
                                @click="openStatus(selected, 'ditolak')"
                            >
                                <Ban class="size-4" />
                                &nbsp;Tolak
                            </button>
                            <button
                                type="button"
                                :class="primaryBtnClass"
                                @click="openStatus(selected, 'disetujui')"
                            >
                                <Check class="size-4" />
                                Setujui
                            </button>
                        </template>
                        <button
                            v-else-if="selected.status === 'disetujui'"
                            type="button"
                            :class="primaryBtnClass"
                            @click="openStatus(selected, 'selesai')"
                        >
                            <CheckCheck class="size-4" />
                            Tandai Selesai
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- STATUS MODAL -->
        <Transition name="modal">
            <div
                v-if="showStatusModal && selected"
                :class="modalBackdropClass"
                @mousedown.self="closeStatus"
            >
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="status-modal-title"
                    :class="[modalCardClass, 'max-w-lg']"
                >
                    <div
                        class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                id="status-modal-title"
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                {{ statusAction.title }}
                            </h2>
                            <p
                                class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                            >
                                {{ selected.nama }} ·
                                {{ selected.nomor_registrasi }}
                            </p>
                        </div>
                        <button
                            type="button"
                            aria-label="Tutup modal"
                            :disabled="processingStatus"
                            :class="closeBtnClass"
                            @click="closeStatus"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <form
                        class="flex min-h-0 flex-1 flex-col"
                        novalidate
                        @submit.prevent="submitStatus"
                    >
                        <div
                            class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6"
                        >
                            <label for="f-catatan" :class="labelClass">
                                Catatan admin
                                <span
                                    v-if="nextStatus === 'ditolak'"
                                    class="text-red-500"
                                    >*</span
                                >
                            </label>
                            <textarea
                                id="f-catatan"
                                v-model="catatan"
                                rows="4"
                                maxlength="1000"
                                :placeholder="statusAction.hint"
                                :class="[
                                    inputClass,
                                    'resize-none leading-6',
                                    statusError && inputErrorClass,
                                ]"
                                @input="statusError = ''"
                            ></textarea>
                            <p
                                class="mt-1.5 text-xs text-slate-400 dark:text-slate-500"
                            >
                                Sisa {{ 1000 - catatan.length }} karakter
                            </p>
                            <p
                                v-if="statusError"
                                :class="errorClass"
                                role="alert"
                            >
                                {{ statusError }}
                            </p>
                        </div>

                        <div
                            class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                :disabled="processingStatus"
                                :class="secondaryBtnClass"
                                @click="closeStatus"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="processingStatus"
                                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-white shadow-sm transition focus:outline-none focus-visible:ring-2 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                                :class="statusAction.btn"
                            >
                                <span
                                    v-if="processingStatus"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>
                                {{
                                    processingStatus
                                        ? "Menyimpan..."
                                        : statusAction.button
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- DELETE MODAL -->
        <Transition name="modal">
            <div
                v-if="showDeleteModal && selected"
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
                            Hapus Permohonan?
                        </h2>
                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus permohonan
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                                >{{ selected.nomor_registrasi }}</span
                            >
                            atas nama {{ selected.nama }}? Tindakan ini tidak
                            dapat dibatalkan.
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
                            @click="deleteKunjungan"
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
                v-if="isRequesting"
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
