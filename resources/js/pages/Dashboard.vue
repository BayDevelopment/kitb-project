<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import {
    ArrowRight,
    CalendarCheck,
    CalendarDays,
    CheckCircle2,
    ClipboardList,
    Clock,
    Edit3,
    Eye,
    FileText,
    MapPin,
    RefreshCw,
    Trash2,
    UserRound,
    Users,
    X,
} from "lucide-vue-next";

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

interface Summary {
    kunjungan_total: number;
    kunjungan_bulan_ini: number;
    total_konten: number;
    pengguna: number;
    peluang_investasi: number;
}

interface GroupItem {
    key: string;
    label: string;
    count: number;
    href: string;
}

interface Group {
    title: string;
    items: GroupItem[];
}

interface Zone {
    label: string;
    luas: number;
    warna?: string;
}

const props = defineProps<{
    summary: Summary;
    groups: Group[];
    kunjunganTerbaru: Kunjungan[];
    zones: Zone[];
}>();

/* =========================================================
 * STYLE
 * ======================================================= */

const cardClass =
    "rounded-2xl border border-slate-200/80 bg-white/90 shadow-sm shadow-slate-200/50 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80 dark:shadow-none";

const inputClass =
    "min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500";

const labelClass =
    "mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300";

const secondaryBtnClass =
    "inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800";

const primaryBtnClass =
    "inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto";

const modalBackdropClass =
    "fixed inset-0 z-[90] flex items-start justify-center overflow-y-auto bg-slate-950/50 p-3 backdrop-blur-sm sm:p-6";

const modalCardClass =
    "my-auto flex max-h-[calc(100dvh-1.5rem)] w-full flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-900/10 sm:max-h-[calc(100dvh-3rem)] dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/40";

const closeBtnClass =
    "shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:opacity-50 dark:hover:bg-slate-800 dark:hover:text-slate-200";

/* =========================================================
 * STATE
 * ======================================================= */

const isInitialLoading = ref(true);
const isRequesting = ref(false);

const showDetailModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const selected = ref<Kunjungan | null>(null);

const processingEdit = ref(false);
const processingDelete = ref(false);

const editErrors = ref<Record<string, string>>({});

const editForm = ref({
    nama: "",
    instansi: "",
    jabatan: "",
    email: "",
    telepon: "",
    tanggal_kunjungan: "",
    waktu_mulai: "",
    waktu_selesai: "",
    jumlah_peserta: 1,
    area_lahan: "",
    keperluan: "",
    memerlukan_pendamping: false,
});

const isPageLoading = computed(
    () => isInitialLoading.value || isRequesting.value,
);

const kunjunganTerbaru = computed(() => props.kunjunganTerbaru ?? []);

/* =========================================================
 * COMPUTED
 * ======================================================= */

const pendingCount = computed(
    () =>
        kunjunganTerbaru.value.filter((item) => item?.status === "pending")
            .length,
);

const totalZoneArea = computed(() =>
    (props.zones ?? []).reduce(
        (total, zone) => total + Number(zone?.luas ?? 0),
        0,
    ),
);

/* =========================================================
 * HELPERS
 * ======================================================= */

const formatNumber = (value: number | null | undefined) =>
    new Intl.NumberFormat("id-ID").format(Number(value ?? 0));

const timeOf = (value: string | null | undefined) =>
    value ? value.slice(0, 5) : "-";

const formatDate = (value: string | null | undefined) => {
    if (!value) return "-";

    const date = new Date(`${value.slice(0, 10)}T00:00:00`);

    if (Number.isNaN(date.getTime())) return "-";

    return new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(date);
};

const formatDateTime = (value: string | null | undefined) => {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return "-";

    return new Intl.DateTimeFormat("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(date);
};

const statusMeta: Record<
    Status,
    {
        label: string;
        class: string;
    }
> = {
    pending: {
        label: "Menunggu",
        class: "bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400",
    },
    disetujui: {
        label: "Disetujui",
        class: "bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400",
    },
    ditolak: {
        label: "Ditolak",
        class: "bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400",
    },
    selesai: {
        label: "Selesai",
        class: "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400",
    },
};

const getStatusLabel = (status: Status | undefined) =>
    statusMeta[status ?? "pending"]?.label ?? "-";

const getStatusClass = (status: Status | undefined) =>
    statusMeta[status ?? "pending"]?.class ?? statusMeta.pending.class;

/* =========================================================
 * MODAL DETAIL
 * ======================================================= */

function openDetail(item: Kunjungan) {
    if (!item?.id) return;

    selected.value = item;
    showDetailModal.value = true;
}

function closeDetail() {
    showDetailModal.value = false;
}

/* =========================================================
 * MODAL EDIT
 * ======================================================= */

function openEdit(item: Kunjungan) {
    if (!item?.id) return;

    selected.value = item;

    editForm.value = {
        nama: item.nama ?? "",
        instansi: item.instansi ?? "",
        jabatan: item.jabatan ?? "",
        email: item.email ?? "",
        telepon: item.telepon ?? "",
        tanggal_kunjungan: item.tanggal_kunjungan
            ? item.tanggal_kunjungan.slice(0, 10)
            : "",
        waktu_mulai: item.waktu_mulai?.slice(0, 5) ?? "",
        waktu_selesai: item.waktu_selesai?.slice(0, 5) ?? "",
        jumlah_peserta: Number(item.jumlah_peserta ?? 1),
        area_lahan: item.area_lahan ?? "",
        keperluan: item.keperluan ?? "",
        memerlukan_pendamping: Boolean(item.memerlukan_pendamping),
    };

    editErrors.value = {};
    showDetailModal.value = false;
    showEditModal.value = true;
}

function closeEdit() {
    if (processingEdit.value) return;

    showEditModal.value = false;
}

function submitEdit() {
    const item = selected.value;

    if (!item?.id || processingEdit.value) return;

    processingEdit.value = true;
    editErrors.value = {};

    router.put(`${BASE_URL}/${item.id}`, editForm.value, {
        preserveScroll: true,

        onSuccess: () => {
            showEditModal.value = false;
            selected.value = null;
        },

        onError: (errors) => {
            editErrors.value = errors as Record<string, string>;
        },

        onFinish: () => {
            processingEdit.value = false;
        },
    });
}

/* =========================================================
 * MODAL DELETE
 * ======================================================= */

function openDelete(item: Kunjungan) {
    if (!item?.id) return;

    selected.value = item;
    showDetailModal.value = false;
    showDeleteModal.value = true;
}

function closeDelete() {
    if (processingDelete.value) return;

    showDeleteModal.value = false;
}

function deleteKunjungan() {
    const item = selected.value;

    if (!item?.id || processingDelete.value) return;

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
 * NAVIGATION
 * ======================================================= */

function goToKunjungan() {
    router.visit(BASE_URL);
}

/* =========================================================
 * KEYBOARD / LIFECYCLE
 * ======================================================= */

const onKey = (event: KeyboardEvent) => {
    if (event.key !== "Escape") return;

    if (showDeleteModal.value) {
        closeDelete();
        return;
    }

    if (showEditModal.value) {
        closeEdit();
        return;
    }

    if (showDetailModal.value) {
        closeDetail();
    }
};

const offStart = router.on("start", () => {
    isRequesting.value = true;
});

const offFinish = router.on("finish", () => {
    isRequesting.value = false;
});

onMounted(() => {
    window.addEventListener("keydown", onKey);

    requestAnimationFrame(() => {
        isInitialLoading.value = false;
    });
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", onKey);
    offStart();
    offFinish();
});
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="relative min-h-full overflow-hidden bg-slate-50 transition-colors duration-300 dark:bg-[#07111f]"
        :aria-busy="isPageLoading ? 'true' : 'false'"
    >
        <!-- BACKGROUND -->
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

            <div class="absolute inset-0 opacity-40 dark:opacity-20">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px]"
                ></div>
            </div>
        </div>

        <div
            class="relative z-10 mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <!-- SKELETON -->
            <div v-if="isInitialLoading" class="animate-pulse">
                <div class="mb-6 space-y-2">
                    <div
                        class="h-7 w-48 rounded-lg bg-slate-200 dark:bg-slate-800"
                    ></div>
                    <div
                        class="h-4 w-80 max-w-full rounded bg-slate-200 dark:bg-slate-800"
                    ></div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="item in 4"
                        :key="item"
                        :class="[cardClass, 'h-32']"
                    ></div>
                </div>

                <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_380px]">
                    <div :class="[cardClass, 'h-96']"></div>
                    <div :class="[cardClass, 'h-96']"></div>
                </div>
            </div>

            <template v-else>
                <!-- HEADER -->
                <div class="mb-6">
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <div
                                class="mb-2 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <CheckCircle2 class="size-3.5" />
                                Sistem Administrasi KITB
                            </div>

                            <h1
                                class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                            >
                                Dashboard
                            </h1>

                            <p
                                class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                Ringkasan aktivitas dan pengelolaan konten
                                kawasan industri.
                            </p>
                        </div>

                        <button
                            type="button"
                            :class="secondaryBtnClass"
                            @click="goToKunjungan"
                        >
                            <CalendarCheck class="size-4" />
                            Kelola Kunjungan
                            <ArrowRight class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- SUMMARY -->
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div :class="[cardClass, 'p-5']">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Total Kunjungan
                                </p>

                                <p
                                    class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatNumber(
                                            props.summary?.kunjungan_total,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Semua permohonan
                                </p>
                            </div>

                            <div
                                class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <CalendarCheck class="size-5" />
                            </div>
                        </div>
                    </div>

                    <div :class="[cardClass, 'p-5']">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Kunjungan Bulan Ini
                                </p>

                                <p
                                    class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatNumber(
                                            props.summary?.kunjungan_bulan_ini,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Aktivitas bulan berjalan
                                </p>
                            </div>

                            <div
                                class="flex size-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
                            >
                                <CalendarDays class="size-5" />
                            </div>
                        </div>
                    </div>

                    <div :class="[cardClass, 'p-5']">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Total Konten
                                </p>

                                <p
                                    class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatNumber(
                                            props.summary?.total_konten,
                                        )
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Data seluruh modul
                                </p>
                            </div>

                            <div
                                class="flex size-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400"
                            >
                                <FileText class="size-5" />
                            </div>
                        </div>
                    </div>

                    <div :class="[cardClass, 'p-5']">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Pengguna
                                </p>

                                <p
                                    class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ formatNumber(props.summary?.pengguna) }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Pengguna terdaftar
                                </p>
                            </div>

                            <div
                                class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                            >
                                <Users class="size-5" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MAIN GRID -->
                <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                    <!-- KUNJUNGAN -->
                    <section :class="[cardClass, 'overflow-hidden']">
                        <div
                            class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400"
                                >
                                    <ClipboardList class="size-5" />
                                </div>

                                <div>
                                    <h2
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        Pengajuan Kunjungan Terbaru
                                    </h2>

                                    <p
                                        class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Permohonan yang masih menunggu diproses.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-400"
                                >
                                    {{ pendingCount }} menunggu
                                </span>

                                <button
                                    type="button"
                                    title="Lihat semua"
                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400"
                                    @click="goToKunjungan"
                                >
                                    <ArrowRight class="size-4" />
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="kunjunganTerbaru.length"
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <div
                                v-for="item in kunjunganTerbaru"
                                :key="item?.id"
                                class="group flex flex-col gap-4 p-4 transition hover:bg-blue-50/40 sm:p-5 dark:hover:bg-blue-950/20"
                            >
                                <div
                                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                                >
                                    <div class="flex min-w-0 gap-3">
                                        <div
                                            class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        >
                                            <UserRound class="size-5" />
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="truncate font-semibold text-slate-800 dark:text-slate-100"
                                            >
                                                {{ item?.nama ?? "Tanpa nama" }}
                                            </p>

                                            <p
                                                class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{
                                                    [
                                                        item?.instansi,
                                                        item?.jabatan,
                                                    ]
                                                        .filter(Boolean)
                                                        .join(" · ") || "-"
                                                }}
                                            </p>

                                            <p
                                                class="mt-1 font-mono text-xs text-blue-600 dark:text-blue-400"
                                            >
                                                {{
                                                    item?.nomor_registrasi ??
                                                    "-"
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3 lg:min-w-[480px]"
                                    >
                                        <div>
                                            <p
                                                class="text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                Tanggal
                                            </p>

                                            <p
                                                class="mt-1 font-medium text-slate-700 dark:text-slate-200"
                                            >
                                                {{
                                                    formatDate(
                                                        item?.tanggal_kunjungan,
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                Waktu
                                            </p>

                                            <p
                                                class="mt-1 flex items-center gap-1 font-medium text-slate-700 dark:text-slate-200"
                                            >
                                                <Clock
                                                    class="size-3.5 shrink-0"
                                                />
                                                {{ timeOf(item?.waktu_mulai) }}
                                                -
                                                {{
                                                    timeOf(item?.waktu_selesai)
                                                }}
                                            </p>
                                        </div>

                                        <div>
                                            <p
                                                class="text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                Area
                                            </p>

                                            <p
                                                class="mt-1 flex items-center gap-1 truncate font-medium text-slate-700 dark:text-slate-200"
                                            >
                                                <MapPin
                                                    class="size-3.5 shrink-0"
                                                />
                                                {{ item?.area_lahan ?? "-" }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACTION -->
                                <div
                                    class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-slate-800"
                                >
                                    <span
                                        class="mr-auto inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium"
                                        :class="getStatusClass(item?.status)"
                                    >
                                        {{ getStatusLabel(item?.status) }}
                                    </span>

                                    <button
                                        type="button"
                                        class="inline-flex min-h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                        @click="openDetail(item)"
                                    >
                                        <Eye class="size-3.5" />
                                        Detail
                                    </button>

                                    <button
                                        type="button"
                                        class="inline-flex min-h-9 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-medium text-blue-600 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-400 dark:hover:bg-blue-950/70"
                                        @click="openEdit(item)"
                                    >
                                        <Edit3 class="size-3.5" />
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="inline-flex min-h-9 items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:border-red-900 dark:bg-red-950/40 dark:text-red-400 dark:hover:bg-red-950/70"
                                        @click="openDelete(item)"
                                    >
                                        <Trash2 class="size-3.5" />
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- EMPTY -->
                        <div v-else class="px-6 py-14 text-center">
                            <div
                                class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                            >
                                <CheckCircle2 class="size-7" />
                            </div>

                            <h3
                                class="mt-4 font-semibold text-slate-800 dark:text-slate-200"
                            >
                                Tidak ada pengajuan tertunda
                            </h3>

                            <p
                                class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                Semua permohonan kunjungan sudah diproses atau
                                belum ada pengajuan baru.
                            </p>
                        </div>
                    </section>

                    <!-- QUICK STATS -->
                    <section :class="[cardClass, 'p-5 sm:p-6']">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
                            >
                                <RefreshCw class="size-5" />
                            </div>

                            <div>
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Ringkasan Kawasan
                                </h2>

                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Informasi cepat sistem.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div
                                class="flex items-center justify-between rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60"
                            >
                                <span
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Peluang investasi
                                </span>

                                <span
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    {{
                                        formatNumber(
                                            props.summary?.peluang_investasi,
                                        )
                                    }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60"
                            >
                                <span
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Zona kawasan
                                </span>

                                <span
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ props.zones?.length ?? 0 }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60"
                            >
                                <span
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Luas zona
                                </span>

                                <span
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ formatNumber(totalZoneArea) }} ha
                                </span>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- MODULES -->
                <section class="mt-6">
                    <div class="mb-4">
                        <h2
                            class="text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            Modul Konten
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Akses cepat ke seluruh pengelolaan konten KITB.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div
                            v-for="group in props.groups ?? []"
                            :key="group.title"
                            :class="[cardClass, 'p-5']"
                        >
                            <h3
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ group.title }}
                            </h3>

                            <div class="mt-4 space-y-2">
                                <a
                                    v-for="item in group.items ?? []"
                                    :key="item.key"
                                    :href="item.href"
                                    class="group flex items-center justify-between rounded-xl border border-slate-100 px-3 py-3 transition hover:border-blue-100 hover:bg-blue-50/60 dark:border-slate-800 dark:hover:border-blue-900 dark:hover:bg-blue-950/30"
                                >
                                    <span
                                        class="text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        {{ item.label }}
                                    </span>

                                    <span class="flex items-center gap-2">
                                        <span
                                            class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        >
                                            {{ formatNumber(item.count) }}
                                        </span>

                                        <ArrowRight
                                            class="size-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-blue-500"
                                        />
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            </template>
        </div>

        <!-- =====================================================
             DETAIL MODAL
        ====================================================== -->

        <Transition name="modal">
            <div
                v-if="showDetailModal && selected"
                :class="modalBackdropClass"
                @mousedown.self="closeDetail"
            >
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="dashboard-detail-title"
                    :class="[modalCardClass, 'max-w-3xl']"
                >
                    <div
                        class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                id="dashboard-detail-title"
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Kunjungan Lahan
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ selected.nomor_registrasi }}
                            </p>
                        </div>

                        <button
                            type="button"
                            :class="closeBtnClass"
                            aria-label="Tutup detail"
                            @click="closeDetail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3
                                class="text-xl font-semibold text-slate-900 dark:text-white"
                            >
                                {{ selected.nama }}
                            </h3>

                            <span
                                class="inline-flex rounded-full px-3 py-1.5 text-xs font-medium"
                                :class="getStatusClass(selected.status)"
                            >
                                {{ getStatusLabel(selected.status) }}
                            </span>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Instansi / Jabatan
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                >
                                    {{
                                        [selected.instansi, selected.jabatan]
                                            .filter(Boolean)
                                            .join(" · ") || "-"
                                    }}
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Kontak
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                >
                                    {{ selected.email }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ selected.telepon }}
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Jadwal
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                >
                                    {{ formatDate(selected.tanggal_kunjungan) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ timeOf(selected.waktu_mulai) }}
                                    -
                                    {{ timeOf(selected.waktu_selesai) }}
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Peserta / Area
                                </p>

                                <p
                                    class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                >
                                    {{ selected.jumlah_peserta }}
                                    peserta
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ selected.area_lahan }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5">
                            <p
                                class="mb-2 text-sm font-semibold text-slate-800 dark:text-slate-200"
                            >
                                Keperluan
                            </p>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                            >
                                <p class="whitespace-pre-line">
                                    {{ selected.keperluan || "-" }}
                                </p>
                            </div>
                        </div>

                        <div v-if="selected.catatan_admin" class="mt-5">
                            <p
                                class="mb-2 text-sm font-semibold text-slate-800 dark:text-slate-200"
                            >
                                Catatan Admin
                            </p>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                            >
                                {{ selected.catatan_admin }}
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

                        <button
                            type="button"
                            :class="primaryBtnClass"
                            @click="openEdit(selected)"
                        >
                            <Edit3 class="size-4" />
                            Edit
                        </button>

                        <button
                            type="button"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                            @click="openDelete(selected)"
                        >
                            <Trash2 class="size-4" />
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- =====================================================
             EDIT MODAL
        ====================================================== -->

        <Transition name="modal">
            <div
                v-if="showEditModal && selected"
                :class="modalBackdropClass"
                @mousedown.self="closeEdit"
            >
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="dashboard-edit-title"
                    :class="[modalCardClass, 'max-w-3xl']"
                >
                    <div
                        class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                id="dashboard-edit-title"
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Edit Kunjungan Lahan
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ selected.nomor_registrasi }}
                            </p>
                        </div>

                        <button
                            type="button"
                            :disabled="processingEdit"
                            :class="closeBtnClass"
                            @click="closeEdit"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <form
                        class="flex min-h-0 flex-1 flex-col"
                        @submit.prevent="submitEdit"
                    >
                        <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <!-- NAMA -->
                                <div>
                                    <label
                                        for="dashboard-edit-nama"
                                        :class="labelClass"
                                    >
                                        Nama Pemohon
                                    </label>

                                    <input
                                        id="dashboard-edit-nama"
                                        v-model="editForm.nama"
                                        type="text"
                                        maxlength="255"
                                        :class="[
                                            inputClass,
                                            editErrors.nama && 'border-red-400',
                                        ]"
                                        :disabled="processingEdit"
                                    />

                                    <p
                                        v-if="editErrors.nama"
                                        class="mt-1.5 text-xs text-red-600"
                                    >
                                        {{ editErrors.nama }}
                                    </p>
                                </div>

                                <!-- INSTANSI -->
                                <div>
                                    <label
                                        for="dashboard-edit-instansi"
                                        :class="labelClass"
                                    >
                                        Instansi
                                    </label>

                                    <input
                                        id="dashboard-edit-instansi"
                                        v-model="editForm.instansi"
                                        type="text"
                                        maxlength="255"
                                        :class="inputClass"
                                        :disabled="processingEdit"
                                    />
                                </div>

                                <!-- JABATAN -->
                                <div>
                                    <label
                                        for="dashboard-edit-jabatan"
                                        :class="labelClass"
                                    >
                                        Jabatan
                                    </label>

                                    <input
                                        id="dashboard-edit-jabatan"
                                        v-model="editForm.jabatan"
                                        type="text"
                                        maxlength="255"
                                        :class="inputClass"
                                        :disabled="processingEdit"
                                    />
                                </div>

                                <!-- EMAIL -->
                                <div>
                                    <label
                                        for="dashboard-edit-email"
                                        :class="labelClass"
                                    >
                                        Email
                                    </label>

                                    <input
                                        id="dashboard-edit-email"
                                        v-model="editForm.email"
                                        type="email"
                                        maxlength="255"
                                        :class="inputClass"
                                        :disabled="processingEdit"
                                    />
                                </div>

                                <!-- TELEPON -->
                                <div>
                                    <label
                                        for="dashboard-edit-telepon"
                                        :class="labelClass"
                                    >
                                        Telepon
                                    </label>

                                    <input
                                        id="dashboard-edit-telepon"
                                        v-model="editForm.telepon"
                                        type="text"
                                        maxlength="50"
                                        :class="inputClass"
                                        :disabled="processingEdit"
                                    />
                                </div>

                                <!-- JUMLAH -->
                                <div>
                                    <label
                                        for="dashboard-edit-peserta"
                                        :class="labelClass"
                                    >
                                        Jumlah Peserta
                                    </label>

                                    <input
                                        id="dashboard-edit-peserta"
                                        v-model.number="editForm.jumlah_peserta"
                                        type="number"
                                        min="1"
                                        :class="inputClass"
                                        :disabled="processingEdit"
                                    />
                                </div>

                                <!-- TANGGAL -->
                                <div>
                                    <label
                                        for="dashboard-edit-tanggal"
                                        :class="labelClass"
                                    >
                                        Tanggal Kunjungan
                                    </label>

                                    <input
                                        id="dashboard-edit-tanggal"
                                        v-model="editForm.tanggal_kunjungan"
                                        type="date"
                                        :class="inputClass"
                                        :disabled="processingEdit"
                                    />
                                </div>

                                <!-- AREA -->
                                <div>
                                    <label
                                        for="dashboard-edit-area"
                                        :class="labelClass"
                                    >
                                        Area Lahan
                                    </label>

                                    <input
                                        id="dashboard-edit-area"
                                        v-model="editForm.area_lahan"
                                        type="text"
                                        maxlength="255"
                                        :class="inputClass"
                                        :disabled="processingEdit"
                                    />
                                </div>

                                <!-- WAKTU MULAI -->
                                <div>
                                    <label
                                        for="dashboard-edit-mulai"
                                        :class="labelClass"
                                    >
                                        Waktu Mulai
                                    </label>

                                    <input
                                        id="dashboard-edit-mulai"
                                        v-model="editForm.waktu_mulai"
                                        type="time"
                                        :class="inputClass"
                                        :disabled="processingEdit"
                                    />
                                </div>

                                <!-- WAKTU SELESAI -->
                                <div>
                                    <label
                                        for="dashboard-edit-selesai"
                                        :class="labelClass"
                                    >
                                        Waktu Selesai
                                    </label>

                                    <input
                                        id="dashboard-edit-selesai"
                                        v-model="editForm.waktu_selesai"
                                        type="time"
                                        :class="inputClass"
                                        :disabled="processingEdit"
                                    />
                                </div>

                                <!-- KEPERLUAN -->
                                <div class="sm:col-span-2">
                                    <label
                                        for="dashboard-edit-keperluan"
                                        :class="labelClass"
                                    >
                                        Keperluan
                                    </label>

                                    <textarea
                                        id="dashboard-edit-keperluan"
                                        v-model="editForm.keperluan"
                                        rows="4"
                                        maxlength="5000"
                                        :class="[
                                            inputClass,
                                            'resize-none leading-6',
                                        ]"
                                        :disabled="processingEdit"
                                    ></textarea>
                                </div>

                                <!-- PENDAMPING -->
                                <div class="sm:col-span-2">
                                    <label
                                        class="flex cursor-pointer items-center gap-3"
                                    >
                                        <input
                                            v-model="
                                                editForm.memerlukan_pendamping
                                            "
                                            type="checkbox"
                                            class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                            :disabled="processingEdit"
                                        />

                                        <span
                                            class="text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Memerlukan pendamping
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                :disabled="processingEdit"
                                :class="secondaryBtnClass"
                                @click="closeEdit"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="processingEdit"
                                :class="primaryBtnClass"
                            >
                                <span
                                    v-if="processingEdit"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                {{
                                    processingEdit
                                        ? "Menyimpan..."
                                        : "Simpan Perubahan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- =====================================================
             DELETE MODAL
        ====================================================== -->

        <Transition name="modal">
            <div
                v-if="showDeleteModal && selected"
                :class="modalBackdropClass"
                @mousedown.self="closeDelete"
            >
                <div
                    role="alertdialog"
                    aria-modal="true"
                    aria-labelledby="dashboard-delete-title"
                    class="my-auto w-full max-w-md rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xl shadow-slate-900/10 sm:p-6 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/40"
                >
                    <div
                        class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                    >
                        <Trash2 class="size-5" />
                    </div>

                    <div class="mt-4 text-center">
                        <h2
                            id="dashboard-delete-title"
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
                            >
                                {{ selected.nomor_registrasi }}
                            </span>
                            atas nama
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selected.nama }} </span
                            >? Tindakan ini tidak dapat dibatalkan.
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

        <!-- REQUEST LOADING -->
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
