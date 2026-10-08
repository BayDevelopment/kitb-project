<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import {
    BriefcaseBusiness,
    Calendar,
    CheckCircle2,
    Clock3,
    Download,
    Eye,
    FileText,
    Mail,
    MapPin,
    Pencil,
    Phone,
    RotateCcw,
    Search,
    Trash2,
    UserRound,
    UserRoundCheck,
    X,
    Linkedin,
    ExternalLink,
} from "lucide-vue-next";
import { toast } from "vue-sonner";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

/*
|--------------------------------------------------------------------------
| Interfaces
|--------------------------------------------------------------------------
*/

interface Lowongan {
    id: number;
    judul_id: string;
    judul_en: string | null;
    judul_zh: string | null;
}

interface Lamaran {
    id: number;
    lowongan_id: number;
    nama_lengkap: string;
    email: string;
    no_hp: string;
    linkedin: string | null;
    portfolio: string | null;
    pesan_id: string | null;
    pesan_en: string | null;
    pesan_zh: string | null;
    /** Fallback untuk data lama yang masih memakai kolom pesan. */
    pesan?: string | null;
    status: string;
    submitted_at: string | null;
    created_at: string;
    updated_at: string;

    has_cv?: boolean;
    has_surat_lamaran?: boolean;

    lowongan?: Lowongan | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData {
    data: Lamaran[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
}

interface FilterProps {
    search: string;
    status: string | null;
    lowongan_id: number | string | null;
}

interface StatusOption {
    value: string;
    label: string;
}

interface Stats {
    total: number;
    by_status: Record<string, number>;
}

interface Props {
    lamarans: PaginatedData;
    lowongans: Lowongan[];
    statuses: StatusOption[];
    stats: Stats;
    filters: FilterProps;
}

const props = defineProps<Props>();

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const search = ref(props.filters.search ?? "");

const status = ref(props.filters.status || "all");

const lowonganId = ref(
    props.filters.lowongan_id ? String(props.filters.lowongan_id) : "all",
);

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const applyFilter = () => {
    router.get(
        "/admin/recruitment/lamaran",
        {
            search: search.value.trim() || undefined,
            status: status.value !== "all" ? status.value : undefined,
            lowongan_id:
                lowonganId.value !== "all" ? lowonganId.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(search, () => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        applyFilter();
    }, 400);
});

watch(status, () => {
    applyFilter();
});

watch(lowonganId, () => {
    applyFilter();
});

const resetFilter = () => {
    clearTimeout(searchTimeout);

    search.value = "";
    status.value = "all";
    lowonganId.value = "all";

    router.get(
        "/admin/recruitment/lamaran",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const hasFilter = computed(() => {
    return (
        search.value.trim() !== "" ||
        status.value !== "all" ||
        lowonganId.value !== "all"
    );
});

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const goToPage = (url: string | null) => {
    if (!url) {
        return;
    }

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

/*
|--------------------------------------------------------------------------
| Modal State
|--------------------------------------------------------------------------
*/

const showDetail = ref(false);
const showEdit = ref(false);
const showDelete = ref(false);

const selectedLamaran = ref<Lamaran | null>(null);

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const emptyForm = () => ({
    lowongan_id: "",
    nama_lengkap: "",
    email: "",
    no_hp: "",
    linkedin: "",
    portfolio: "",
    pesan_id: "",
    pesan_en: "",
    pesan_zh: "",
    status: "",
    submitted_at: "",
    cv: null as File | null,
    surat_lamaran: null as File | null,
});

const form = ref(emptyForm());

const messageLanguages = [
    { code: "id", label: "Indonesia" },
    { code: "en", label: "English" },
    { code: "zh", label: "中文" },
] as const;

const messageLanguage = ref<"id" | "en" | "zh">("id");

const setMessageLanguage = (language: "id" | "en" | "zh") => {
    messageLanguage.value = language;
};

const processingForm = ref(false);
const processingDelete = ref(false);

/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const isLoading = ref(false);

let loadingStartedAt = 0;
let loadingTimer: ReturnType<typeof setTimeout> | undefined;

const stopLoading = () => {
    const elapsed = Date.now() - loadingStartedAt;
    const remaining = Math.max(0, 500 - elapsed);

    clearTimeout(loadingTimer);

    loadingTimer = setTimeout(() => {
        requestAnimationFrame(() => {
            isLoading.value = false;
        });
    }, remaining);
};

const removeRouterListeners = [
    router.on("start", (event) => {
        if (!event.detail.visit.preserveState) {
            loadingStartedAt = Date.now();
            isLoading.value = true;
        }
    }),

    router.on("finish", (event) => {
        if (!event.detail.visit.preserveState) {
            stopLoading();
        }
    }),
];

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const truncate = (text: string | null, length = 100) => {
    if (!text) {
        return "-";
    }

    const cleanText = text.replace(/<[^>]*>/g, "").trim();

    return cleanText.length > length
        ? `${cleanText.substring(0, length)}...`
        : cleanText;
};

const formatDate = (value: string | null) => {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(date);
};

const formatDateTime = (value: string | null) => {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(date);
};

const formatDateTimeInput = (value: string | null) => {
    if (!value) {
        return "";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "";
    }

    const pad = (number: number) => String(number).padStart(2, "0");

    return [
        `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(
            date.getDate(),
        )}`,
        `${pad(date.getHours())}:${pad(date.getMinutes())}`,
    ].join("T");
};

const statusLabel = (value: string) => {
    const found = props.statuses.find(
        (statusOption) => statusOption.value === value,
    );

    if (found) {
        return found.label;
    }

    return value
        .replace(/[_-]/g, " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
};

const statusClass = (value: string) => {
    const normalized = value.toLowerCase();

    if (
        [
            "approved",
            "accepted",
            "hired",
            "diterima",
            "lulus",
            "shortlisted",
        ].includes(normalized)
    ) {
        return "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400";
    }

    if (["rejected", "ditolak", "failed", "gagal"].includes(normalized)) {
        return "bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400";
    }

    if (
        ["interview", "review", "reviewing", "diproses", "processing"].includes(
            normalized,
        )
    ) {
        return "bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400";
    }

    if (["submitted", "pending", "menunggu"].includes(normalized)) {
        return "bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400";
    }

    return "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300";
};

const statusDotClass = (value: string) => {
    const normalized = value.toLowerCase();

    if (
        [
            "approved",
            "accepted",
            "hired",
            "diterima",
            "lulus",
            "shortlisted",
        ].includes(normalized)
    ) {
        return "bg-emerald-500";
    }

    if (["rejected", "ditolak", "failed", "gagal"].includes(normalized)) {
        return "bg-red-500";
    }

    if (
        ["interview", "review", "reviewing", "diproses", "processing"].includes(
            normalized,
        )
    ) {
        return "bg-blue-500";
    }

    if (["submitted", "pending", "menunggu"].includes(normalized)) {
        return "bg-amber-500";
    }

    return "bg-slate-400";
};

const getFirstError = (errors: Record<string, string | string[]>) => {
    const firstError = Object.values(errors)[0];

    if (!firstError) {
        return null;
    }

    return Array.isArray(firstError) ? firstError[0] : String(firstError);
};

const lowonganTitle = (lamaran: Lamaran): string => {
    const lowongan =
        lamaran.lowongan ??
        props.lowongans.find((item) => item.id === lamaran.lowongan_id);

    return (
        lowongan?.judul_id ||
        lowongan?.judul_en ||
        lowongan?.judul_zh ||
        "Lowongan tidak ditemukan"
    );
};

const getStat = (value: string) => {
    return props.stats.by_status?.[value] ?? 0;
};

/*
|--------------------------------------------------------------------------
| Modal Actions
|--------------------------------------------------------------------------
*/

const resetFormState = () => {
    showEdit.value = false;

    form.value = emptyForm();

    selectedLamaran.value = null;
};

const getInitialMessageLanguage = (lamaran: Lamaran): "id" | "en" | "zh" => {
    if (lamaran.pesan_id?.trim() || lamaran.pesan?.trim()) {
        return "id";
    }

    if (lamaran.pesan_en?.trim()) {
        return "en";
    }

    if (lamaran.pesan_zh?.trim()) {
        return "zh";
    }

    return "id";
};

const openDetail = (lamaran: Lamaran) => {
    selectedLamaran.value = lamaran;
    messageLanguage.value = getInitialMessageLanguage(lamaran);

    showEdit.value = false;
    showDelete.value = false;

    showDetail.value = true;
};

const closeDetail = () => {
    showDetail.value = false;
    selectedLamaran.value = null;
};

const openEdit = (lamaran: Lamaran) => {
    selectedLamaran.value = lamaran;

    // Muat semua pesan ke form sekaligus. Jika data lama masih memakai
    // kolom `pesan`, masukkan ke Bahasa Indonesia sebagai fallback.
    form.value = {
        lowongan_id: String(lamaran.lowongan_id),
        nama_lengkap: lamaran.nama_lengkap ?? "",
        email: lamaran.email ?? "",
        no_hp: lamaran.no_hp ?? "",
        linkedin: lamaran.linkedin ?? "",
        portfolio: lamaran.portfolio ?? "",
        pesan_id: lamaran.pesan_id ?? lamaran.pesan ?? "",
        pesan_en: lamaran.pesan_en ?? "",
        pesan_zh: lamaran.pesan_zh ?? "",
        status: lamaran.status ?? "submitted",
        submitted_at: formatDateTimeInput(lamaran.submitted_at),
        cv: null,
        surat_lamaran: null,
    };

    // Buka tab bahasa yang memang memiliki isi agar modal tidak tampak kosong.
    messageLanguage.value = getInitialMessageLanguage({
        ...lamaran,
        pesan_id: form.value.pesan_id || null,
        pesan_en: form.value.pesan_en || null,
        pesan_zh: form.value.pesan_zh || null,
    });

    showDetail.value = false;
    showDelete.value = false;

    showEdit.value = true;
};

const closeEdit = () => {
    if (processingForm.value) {
        return;
    }

    resetFormState();
};

const openDelete = (lamaran: Lamaran) => {
    selectedLamaran.value = lamaran;

    showDetail.value = false;
    showEdit.value = false;

    showDelete.value = true;
};

const closeDelete = () => {
    if (processingDelete.value) {
        return;
    }

    showDelete.value = false;
    selectedLamaran.value = null;
};

/*
|--------------------------------------------------------------------------
| File Input
|--------------------------------------------------------------------------
*/

const handleCvChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    form.value.cv = target.files?.[0] ?? null;
};

const handleSuratChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    form.value.surat_lamaran = target.files?.[0] ?? null;
};

/*
|--------------------------------------------------------------------------
| Submit Edit
|--------------------------------------------------------------------------
*/

const submitForm = () => {
    const lamaran = selectedLamaran.value;

    if (processingForm.value || !lamaran) {
        return;
    }

    const nama = form.value.nama_lengkap.trim();
    const email = form.value.email.trim();
    const noHp = form.value.no_hp.trim();

    if (nama.length < 3) {
        toast.error("Nama lengkap minimal 3 karakter.");
        return;
    }

    if (!form.value.lowongan_id) {
        toast.error("Lowongan wajib dipilih.");
        return;
    }

    if (!email) {
        toast.error("Email pelamar wajib diisi.");
        return;
    }

    if (!noHp) {
        toast.error("Nomor HP pelamar wajib diisi.");
        return;
    }

    if (!form.value.status) {
        toast.error("Status lamaran wajib dipilih.");
        return;
    }

    const data = {
        _method: "PUT",
        lowongan_id: Number(form.value.lowongan_id),
        nama_lengkap: nama,
        email,
        no_hp: noHp,
        linkedin: form.value.linkedin.trim() || null,
        portfolio: form.value.portfolio.trim() || null,
        pesan_id: form.value.pesan_id.trim() || null,
        pesan_en: form.value.pesan_en.trim() || null,
        pesan_zh: form.value.pesan_zh.trim() || null,
        status: form.value.status,
        submitted_at: form.value.submitted_at || null,
        cv: form.value.cv,
        surat_lamaran: form.value.surat_lamaran,
    };

    processingForm.value = true;

    router.post(`/admin/recruitment/lamaran/${lamaran.id}`, data, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            resetFormState();
            toast.success("Data lamaran berhasil diperbarui.");
        },
        onError: (errors) => {
            const message = getFirstError(errors);
            toast.error(message ?? "Data lamaran gagal diperbarui.");
        },
        onFinish: () => {
            processingForm.value = false;
        },
    });
};

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteLamaran = () => {
    const lamaran = selectedLamaran.value;

    if (!lamaran || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(`/admin/recruitment/lamaran/${lamaran.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDelete.value = false;
            selectedLamaran.value = null;
            toast.success("Data lamaran berhasil dihapus.");
        },
        onError: (errors) => {
            const message = getFirstError(errors);
            toast.error(message ?? "Lamaran gagal dihapus.");
        },
        onFinish: () => {
            processingDelete.value = false;
        },
    });
};

/*
|--------------------------------------------------------------------------
| Download
|--------------------------------------------------------------------------
*/

const downloadCv = (lamaran: Lamaran) => {
    if (!lamaran.has_cv) {
        toast.error("CV tidak tersedia.");
        return;
    }

    window.open(
        `/admin/recruitment/lamaran/${lamaran.id}/cv`,
        "_blank",
        "noopener,noreferrer",
    );
};

const downloadSurat = (lamaran: Lamaran) => {
    if (!lamaran.has_surat_lamaran) {
        toast.error("Surat lamaran tidak tersedia.");
        return;
    }

    window.open(
        `/admin/recruitment/lamaran/${lamaran.id}/surat`,
        "_blank",
        "noopener,noreferrer",
    );
};

/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
    clearTimeout(searchTimeout);
    clearTimeout(loadingTimer);

    removeRouterListeners.forEach((remove) => remove());
});
</script>

<template>
    <Head title="Lamaran" />

    <div
        class="relative min-h-full overflow-hidden bg-slate-50/50 transition-colors duration-300 dark:bg-slate-950/50"
    >
        <!-- =========================================================
             BACKGROUND
        ========================================================== -->

        <div
            class="pointer-events-none absolute inset-x-0 top-0 z-0 h-96 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="blob-shape absolute -left-24 -top-32 size-96 rounded-full bg-gradient-to-br from-blue-400/30 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15 dark:to-transparent"
            ></div>

            <div
                class="blob-shape-delayed absolute -right-20 top-4 size-80 rounded-full bg-gradient-to-tr from-sky-300/30 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <div
                class="blob-shape-slow absolute left-1/3 -top-40 size-72 rounded-full bg-gradient-to-br from-indigo-300/20 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <div class="absolute inset-0 opacity-40 dark:opacity-20">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px]"
                ></div>
            </div>

            <div
                class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent to-slate-50/90 dark:to-slate-950/90"
            ></div>
        </div>

        <!-- =========================================================
             MAIN
        ========================================================== -->

        <div
            class="relative z-10 mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <!-- =====================================================
                 HEADER
            ====================================================== -->

            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <UserRoundCheck class="size-5" />
                    </div>

                    <div class="min-w-0">
                        <h1
                            class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-2xl"
                        >
                            Lamaran
                        </h1>

                        <p
                            class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Kelola dan pantau seluruh lamaran yang masuk ke
                            KITB.
                        </p>
                    </div>
                </div>
            </div>

            <!-- =====================================================
                 STATS
            ====================================================== -->

            <div class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <!-- TOTAL -->

                <div
                    class="rounded-2xl border border-slate-200/80 bg-white/90 p-5 shadow-sm shadow-slate-200/40 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Total Lamaran
                            </p>

                            <p
                                class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ stats.total }}
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Seluruh lamaran masuk
                            </p>
                        </div>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                        >
                            <UserRoundCheck class="size-5" />
                        </div>
                    </div>
                </div>

                <!-- DYNAMIC STATUS -->

                <div
                    v-for="statusOption in statuses.slice(0, 3)"
                    :key="statusOption.value"
                    class="rounded-2xl border border-slate-200/80 bg-white/90 p-5 shadow-sm shadow-slate-200/40 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                {{ statusOption.label }}
                            </p>

                            <p
                                class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ getStat(statusOption.value) }}
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Status lamaran
                            </p>
                        </div>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                        >
                            <FileText class="size-5" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- =====================================================
                 FILTER
            ====================================================== -->

            <div
                class="mb-5 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm shadow-slate-200/40 backdrop-blur-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
            >
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama atau email pelamar..."
                            class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900"
                        />
                    </div>

                    <select
                        v-model="lowonganId"
                        class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="all">Semua Lowongan</option>

                        <option
                            v-for="lowongan in lowongans"
                            :key="lowongan.id"
                            :value="String(lowongan.id)"
                        >
                            {{
                                lowongan.judul_id ||
                                lowongan.judul_en ||
                                lowongan.judul_zh
                            }}
                        </option>
                    </select>

                    <select
                        v-model="status"
                        class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="all">Semua Status</option>

                        <option
                            v-for="option in statuses"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>

                    <button
                        v-if="hasFilter"
                        type="button"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-medium text-slate-600 transition-all hover:bg-slate-100 hover:text-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                        @click="resetFilter"
                    >
                        <RotateCcw class="size-4" />
                        Reset
                    </button>
                </div>
            </div>

            <!-- =====================================================
                 TABLE
            ====================================================== -->

            <div
                class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/50 backdrop-blur-sm transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10"
            >
                <!-- LOADING SKELETON -->

                <div v-if="isLoading" class="animate-pulse overflow-hidden">
                    <div
                        class="hidden border-b border-slate-200 bg-slate-50/80 px-6 py-4 md:grid md:grid-cols-[2fr_1.4fr_1.3fr_1fr_1fr_120px] md:gap-6 dark:border-slate-800 dark:bg-slate-800/50"
                    >
                        <div
                            v-for="index in 6"
                            :key="index"
                            class="h-4 rounded-lg bg-slate-200 dark:bg-slate-700"
                        ></div>
                    </div>

                    <div
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <div v-for="index in 7" :key="index" class="p-5">
                            <div class="flex items-start gap-3">
                                <div
                                    class="size-11 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"
                                ></div>

                                <div class="min-w-0 flex-1 space-y-2">
                                    <div
                                        class="h-4 w-1/2 rounded bg-slate-200 dark:bg-slate-800"
                                    ></div>

                                    <div
                                        class="h-3 w-1/3 rounded bg-slate-200 dark:bg-slate-800"
                                    ></div>

                                    <div
                                        class="h-3 w-2/3 rounded bg-slate-200 dark:bg-slate-800"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE -->

                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead
                            class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <tr>
                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Pelamar
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Lowongan
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Kontak
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Dokumen
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Dikirim
                                </th>

                                <th
                                    class="px-6 py-4 text-right font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="lamaran in lamarans.data"
                                :key="lamaran.id"
                                class="transition-colors duration-200 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                            >
                                <!-- PELAMAR -->

                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-0.5 flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:ring-blue-900/40"
                                        >
                                            <UserRound class="size-5" />
                                        </div>

                                        <div class="min-w-0 max-w-xs">
                                            <p
                                                class="truncate font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{ lamaran.nama_lengkap }}
                                            </p>

                                            <p
                                                class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{ lamaran.email }}
                                            </p>

                                            <p
                                                class="mt-1 text-[11px] text-slate-400 dark:text-slate-500"
                                            >
                                                ID #{{ lamaran.id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- LOWONGAN -->

                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-2">
                                        <BriefcaseBusiness
                                            class="mt-0.5 size-4 shrink-0 text-slate-400"
                                        />

                                        <div class="min-w-0 max-w-xs">
                                            <p
                                                class="truncate font-medium text-slate-700 dark:text-slate-200"
                                            >
                                                {{ lowonganTitle(lamaran) }}
                                            </p>

                                            <p
                                                class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                Lowongan #{{
                                                    lamaran.lowongan_id
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- KONTAK -->

                                <td class="px-6 py-4">
                                    <div
                                        class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        <div class="flex items-center gap-2">
                                            <Mail
                                                class="size-3.5 text-slate-400"
                                            />

                                            <span class="truncate">
                                                {{ lamaran.email }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <Phone
                                                class="size-3.5 text-slate-400"
                                            />

                                            <span>
                                                {{ lamaran.no_hp }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- DOKUMEN -->

                                <td class="px-6 py-4">
                                    <div
                                        class="flex flex-col items-start gap-2"
                                    >
                                        <button
                                            v-if="lamaran.has_cv"
                                            type="button"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-950/50"
                                            @click="downloadCv(lamaran)"
                                        >
                                            <FileText class="size-3.5" />
                                            CV
                                            <Download class="size-3" />
                                        </button>

                                        <span
                                            v-else
                                            class="text-xs text-slate-400"
                                        >
                                            CV tidak tersedia
                                        </span>

                                        <button
                                            v-if="lamaran.has_surat_lamaran"
                                            type="button"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-600 transition hover:bg-blue-100 dark:bg-blue-950/30 dark:text-blue-400 dark:hover:bg-blue-950/50"
                                            @click="downloadSurat(lamaran)"
                                        >
                                            <FileText class="size-3.5" />
                                            Surat
                                            <Download class="size-3" />
                                        </button>
                                    </div>
                                </td>

                                <!-- STATUS -->

                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="statusClass(lamaran.status)"
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="
                                                statusDotClass(lamaran.status)
                                            "
                                        ></span>

                                        {{ statusLabel(lamaran.status) }}
                                    </span>
                                </td>

                                <!-- DATE -->

                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        <Calendar
                                            class="size-3.5 text-slate-400"
                                        />

                                        {{
                                            formatDateTime(lamaran.submitted_at)
                                        }}
                                    </div>
                                </td>

                                <!-- ACTION -->

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-1">
                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Lihat detail"
                                            @click="openDetail(lamaran)"
                                        >
                                            <Eye class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                            title="Edit lamaran"
                                            @click="openEdit(lamaran)"
                                        >
                                            <Pencil class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                            title="Hapus lamaran"
                                            @click="openDelete(lamaran)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- EMPTY -->

                            <tr v-if="lamarans.data.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600"
                                    >
                                        <UserRoundCheck class="size-7" />
                                    </div>

                                    <p
                                        class="mt-4 font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Data lamaran tidak ditemukan
                                    </p>

                                    <p
                                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Coba ubah kata pencarian atau filter.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- =================================================
                     PAGINATION
                ================================================== -->

                <div
                    v-if="!isLoading && lamarans.total > 0"
                    class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Menampilkan

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ lamarans.from }}
                        </span>

                        -

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ lamarans.to }}
                        </span>

                        dari

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ lamarans.total }}
                        </span>

                        data
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="(link, index) in lamarans.links"
                            :key="index"
                            type="button"
                            :disabled="!link.url"
                            class="min-w-9 rounded-xl px-3 py-2 text-sm transition-all"
                            :class="
                                link.active
                                    ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20'
                                    : link.url
                                      ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                      : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                            "
                            @click="goToPage(link.url)"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             DETAIL MODAL
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showDetail && selectedLamaran"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
                @click.self="closeDetail"
            >
                <div
                    class="w-full max-w-3xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- HEADER -->

                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40"
                    >
                        <div>
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                >
                                    <UserRoundCheck class="size-4" />
                                </div>

                                <h2
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    Detail Lamaran
                                </h2>
                            </div>

                            <p
                                class="mt-1 pl-11 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap kandidat dan dokumen lamaran.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-200/70 hover:text-slate-600 dark:hover:bg-slate-800"
                            @click="closeDetail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- CONTENT -->

                    <div class="max-h-[78vh] space-y-6 overflow-y-auto p-6">
                        <!-- PROFILE -->

                        <div
                            class="flex flex-col gap-4 rounded-2xl border border-blue-100 bg-blue-50/60 p-5 sm:flex-row sm:items-center dark:border-blue-900/40 dark:bg-blue-950/20"
                        >
                            <div
                                class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                            >
                                <UserRound class="size-7" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3
                                        class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                    >
                                        {{ selectedLamaran.nama_lengkap }}
                                    </h3>

                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="
                                            statusClass(selectedLamaran.status)
                                        "
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="
                                                statusDotClass(
                                                    selectedLamaran.status,
                                                )
                                            "
                                        ></span>

                                        {{
                                            statusLabel(selectedLamaran.status)
                                        }}
                                    </span>
                                </div>

                                <p
                                    class="mt-1 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    <BriefcaseBusiness class="size-4" />

                                    {{ lowonganTitle(selectedLamaran) }}
                                </p>
                            </div>
                        </div>

                        <!-- CONTACT -->

                        <div>
                            <h4
                                class="mb-3 text-sm font-semibold text-slate-800 dark:text-slate-200"
                            >
                                Informasi Kontak
                            </h4>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                                >
                                    <div
                                        class="flex items-center gap-2 text-slate-400"
                                    >
                                        <Mail class="size-4" />

                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide"
                                        >
                                            Email
                                        </span>
                                    </div>

                                    <p
                                        class="mt-2 break-all text-sm font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{ selectedLamaran.email }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                                >
                                    <div
                                        class="flex items-center gap-2 text-slate-400"
                                    >
                                        <Phone class="size-4" />

                                        <span
                                            class="text-xs font-semibold uppercase tracking-wide"
                                        >
                                            Nomor HP
                                        </span>
                                    </div>

                                    <p
                                        class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{ selectedLamaran.no_hp }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- LINKS -->

                        <div
                            v-if="
                                selectedLamaran.linkedin ||
                                selectedLamaran.portfolio
                            "
                        >
                            <h4
                                class="mb-3 text-sm font-semibold text-slate-800 dark:text-slate-200"
                            >
                                Profil & Portofolio
                            </h4>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <a
                                    v-if="selectedLamaran.linkedin"
                                    :href="selectedLamaran.linkedin"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-blue-300 hover:bg-blue-50/50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-800 dark:hover:bg-blue-950/20"
                                >
                                    <div
                                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        <Linkedin class="size-5" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            LinkedIn
                                        </p>

                                        <p
                                            class="mt-0.5 truncate text-xs text-slate-400"
                                        >
                                            {{ selectedLamaran.linkedin }}
                                        </p>
                                    </div>

                                    <ExternalLink
                                        class="size-4 text-slate-400 transition group-hover:text-blue-500"
                                    />
                                </a>

                                <a
                                    v-if="selectedLamaran.portfolio"
                                    :href="selectedLamaran.portfolio"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-blue-300 hover:bg-blue-50/50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-800 dark:hover:bg-blue-950/20"
                                >
                                    <div
                                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
                                    >
                                        <ExternalLink class="size-5" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Portfolio
                                        </p>

                                        <p
                                            class="mt-0.5 truncate text-xs text-slate-400"
                                        >
                                            {{ selectedLamaran.portfolio }}
                                        </p>
                                    </div>

                                    <ExternalLink
                                        class="size-4 text-slate-400 transition group-hover:text-blue-500"
                                    />
                                </a>
                            </div>
                        </div>

                        <!-- DOCUMENTS -->

                        <div>
                            <h4
                                class="mb-3 text-sm font-semibold text-slate-800 dark:text-slate-200"
                            >
                                Dokumen Lamaran
                            </h4>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <button
                                    type="button"
                                    :disabled="!selectedLamaran.has_cv"
                                    class="flex items-center gap-3 rounded-2xl border p-4 text-left transition disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="
                                        selectedLamaran.has_cv
                                            ? 'border-red-100 bg-red-50/60 hover:bg-red-50 dark:border-red-900/30 dark:bg-red-950/20 dark:hover:bg-red-950/30'
                                            : 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50'
                                    "
                                    @click="downloadCv(selectedLamaran)"
                                >
                                    <div
                                        class="flex size-10 items-center justify-center rounded-xl bg-white text-red-600 shadow-sm dark:bg-slate-900 dark:text-red-400"
                                    >
                                        <FileText class="size-5" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Curriculum Vitae
                                        </p>

                                        <p
                                            class="mt-0.5 text-xs text-slate-400"
                                        >
                                            {{
                                                selectedLamaran.has_cv
                                                    ? "Download CV"
                                                    : "Tidak tersedia"
                                            }}
                                        </p>
                                    </div>

                                    <Download
                                        v-if="selectedLamaran.has_cv"
                                        class="size-4 text-red-500"
                                    />
                                </button>

                                <button
                                    type="button"
                                    :disabled="
                                        !selectedLamaran.has_surat_lamaran
                                    "
                                    class="flex items-center gap-3 rounded-2xl border p-4 text-left transition disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="
                                        selectedLamaran.has_surat_lamaran
                                            ? 'border-blue-100 bg-blue-50/60 hover:bg-blue-50 dark:border-blue-900/30 dark:bg-blue-950/20 dark:hover:bg-blue-950/30'
                                            : 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50'
                                    "
                                    @click="downloadSurat(selectedLamaran)"
                                >
                                    <div
                                        class="flex size-10 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                                    >
                                        <FileText class="size-5" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Surat Lamaran
                                        </p>

                                        <p
                                            class="mt-0.5 text-xs text-slate-400"
                                        >
                                            {{
                                                selectedLamaran.has_surat_lamaran
                                                    ? "Download surat"
                                                    : "Tidak tersedia"
                                            }}
                                        </p>
                                    </div>

                                    <Download
                                        v-if="selectedLamaran.has_surat_lamaran"
                                        class="size-4 text-blue-500"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- MESSAGE -->

                        <div
                            v-if="
                                selectedLamaran.pesan_id ||
                                selectedLamaran.pesan ||
                                selectedLamaran.pesan_en ||
                                selectedLamaran.pesan_zh
                            "
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <div class="flex items-center gap-2">
                                <FileText
                                    class="size-4 text-blue-600 dark:text-blue-400"
                                />

                                <h4
                                    class="font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Pesan Pelamar
                                </h4>
                            </div>

                            <div
                                class="mt-4 flex flex-wrap gap-2 border-b border-slate-200 pb-3 dark:border-slate-700"
                            >
                                <button
                                    v-for="language in messageLanguages"
                                    :key="language.code"
                                    type="button"
                                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                                    :class="
                                        messageLanguage === language.code
                                            ? 'bg-blue-600 text-white'
                                            : 'text-slate-500 hover:bg-slate-200 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200'
                                    "
                                    @click="setMessageLanguage(language.code)"
                                >
                                    {{ language.label }}
                                </button>
                            </div>

                            <p
                                v-if="
                                    messageLanguage === 'id' &&
                                    (selectedLamaran.pesan_id ||
                                        selectedLamaran.pesan)
                                "
                                class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    selectedLamaran.pesan_id ||
                                    selectedLamaran.pesan
                                }}
                            </p>

                            <p
                                v-else-if="
                                    messageLanguage === 'en' &&
                                    selectedLamaran.pesan_en
                                "
                                class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedLamaran.pesan_en }}
                            </p>

                            <p
                                v-else-if="
                                    messageLanguage === 'zh' &&
                                    selectedLamaran.pesan_zh
                                "
                                class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedLamaran.pesan_zh }}
                            </p>

                            <p
                                v-else
                                class="mt-4 text-sm italic text-slate-400 dark:text-slate-500"
                            >
                                Pesan dalam bahasa ini tidak tersedia.
                            </p>
                        </div>

                        <!-- META -->

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                >
                                    Dikirim
                                </p>

                                <p
                                    class="mt-2 text-sm text-slate-700 dark:text-slate-200"
                                >
                                    {{
                                        formatDateTime(
                                            selectedLamaran.submitted_at,
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                >
                                    Diperbarui
                                </p>

                                <p
                                    class="mt-2 text-sm text-slate-700 dark:text-slate-200"
                                >
                                    {{
                                        formatDateTime(
                                            selectedLamaran.updated_at,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- =========================================================
             EDIT MODAL
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showEdit && selectedLamaran"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
                @click.self="closeEdit"
            >
                <div
                    class="w-full max-w-3xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- HEADER -->

                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40"
                    >
                        <div>
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                >
                                    <Pencil class="size-4" />
                                </div>

                                <h2
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    Edit Lamaran
                                </h2>
                            </div>

                            <p
                                class="mt-1 pl-11 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Perbarui informasi pelamar dan status proses
                                rekrutmen.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-200/70 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800"
                            :disabled="processingForm"
                            @click="closeEdit"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- FORM -->

                    <form
                        class="max-h-[78vh] overflow-y-auto p-6"
                        @submit.prevent="submitForm"
                    >
                        <div class="grid gap-5 md:grid-cols-2">
                            <!-- LOWONGAN -->

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Lowongan
                                    <span class="text-red-500"> * </span>
                                </label>

                                <select
                                    v-model="form.lowongan_id"
                                    required
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900"
                                >
                                    <option value="" disabled>
                                        Pilih lowongan
                                    </option>

                                    <option
                                        v-for="lowongan in lowongans"
                                        :key="lowongan.id"
                                        :value="String(lowongan.id)"
                                    >
                                        {{
                                            lowongan.judul_id ||
                                            lowongan.judul_en ||
                                            lowongan.judul_zh
                                        }}
                                    </option>
                                </select>
                            </div>

                            <!-- NAMA -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Nama Lengkap
                                    <span class="text-red-500"> * </span>
                                </label>

                                <input
                                    v-model="form.nama_lengkap"
                                    type="text"
                                    required
                                    maxlength="255"
                                    autocomplete="name"
                                    placeholder="Nama lengkap pelamar"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />
                            </div>

                            <!-- EMAIL -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Email
                                    <span class="text-red-500"> * </span>
                                </label>

                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    maxlength="255"
                                    autocomplete="email"
                                    placeholder="nama@email.com"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />
                            </div>

                            <!-- PHONE -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Nomor HP
                                    <span class="text-red-500"> * </span>
                                </label>

                                <input
                                    v-model="form.no_hp"
                                    type="text"
                                    required
                                    maxlength="30"
                                    autocomplete="tel"
                                    placeholder="08xxxxxxxxxx"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />
                            </div>

                            <!-- STATUS -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Status
                                    <span class="text-red-500"> * </span>
                                </label>

                                <select
                                    v-model="form.status"
                                    required
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <option
                                        v-for="option in statuses"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- SUBMITTED -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Waktu Pengajuan
                                </label>

                                <input
                                    v-model="form.submitted_at"
                                    type="datetime-local"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900"
                                />
                            </div>

                            <!-- LINKEDIN -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    LinkedIn
                                </label>

                                <input
                                    v-model="form.linkedin"
                                    type="url"
                                    maxlength="255"
                                    placeholder="https://linkedin.com/in/..."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />
                            </div>

                            <!-- PORTFOLIO -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Portfolio
                                </label>

                                <input
                                    v-model="form.portfolio"
                                    type="url"
                                    maxlength="255"
                                    placeholder="https://..."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />
                            </div>

                            <!-- CV -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Ganti CV
                                </label>

                                <input
                                    type="file"
                                    accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-600 file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2.5 file:text-sm file:font-medium file:text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:file:bg-slate-700 dark:file:text-slate-200"
                                    @change="handleCvChange"
                                />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    PDF, DOC, DOCX · maksimal 1 MB.
                                </p>

                                <p
                                    v-if="form.cv"
                                    class="mt-2 text-xs font-medium text-blue-600 dark:text-blue-400"
                                >
                                    File:
                                    {{ form.cv.name }}
                                </p>
                            </div>

                            <!-- SURAT -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Ganti Surat Lamaran
                                </label>

                                <input
                                    type="file"
                                    accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-600 file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2.5 file:text-sm file:font-medium file:text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:file:bg-slate-700 dark:file:text-slate-200"
                                    @change="handleSuratChange"
                                />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    PDF, DOC, DOCX · maksimal 1 MB.
                                </p>

                                <p
                                    v-if="form.surat_lamaran"
                                    class="mt-2 text-xs font-medium text-blue-600 dark:text-blue-400"
                                >
                                    File:
                                    {{ form.surat_lamaran.name }}
                                </p>
                            </div>

                            <!-- PESAN 3 BAHASA -->

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Pesan Pelamar
                                </label>

                                <div
                                    class="mb-3 flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-800"
                                >
                                    <button
                                        v-for="language in messageLanguages"
                                        :key="language.code"
                                        type="button"
                                        class="rounded-t-xl border-b-2 px-4 py-2 text-xs font-semibold transition"
                                        :class="
                                            messageLanguage === language.code
                                                ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                                                : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'
                                        "
                                        @click="
                                            setMessageLanguage(language.code)
                                        "
                                    >
                                        {{ language.label }}
                                    </button>
                                </div>

                                <textarea
                                    v-if="messageLanguage === 'id'"
                                    v-model="form.pesan_id"
                                    rows="5"
                                    maxlength="2000"
                                    placeholder="Pesan atau catatan dari pelamar dalam Bahasa Indonesia..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                ></textarea>

                                <textarea
                                    v-else-if="messageLanguage === 'en'"
                                    v-model="form.pesan_en"
                                    rows="5"
                                    maxlength="2000"
                                    placeholder="Applicant message or note in English..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                ></textarea>

                                <textarea
                                    v-else
                                    v-model="form.pesan_zh"
                                    rows="5"
                                    maxlength="2000"
                                    placeholder="申请人的留言或备注（中文）..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                ></textarea>
                            </div>
                        </div>

                        <!-- FOOTER -->

                        <div
                            class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800"
                        >
                            <button
                                type="button"
                                :disabled="processingForm"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeEdit"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="
                                    processingForm || !form.nama_lengkap.trim()
                                "
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span
                                    v-if="processingForm"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                {{
                                    processingForm
                                        ? "Menyimpan..."
                                        : "Simpan Perubahan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- =========================================================
             DELETE
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showDelete && selectedLamaran"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
                @click.self="closeDelete"
            >
                <div
                    class="w-full max-w-md rounded-3xl border border-white/10 bg-white p-6 shadow-2xl shadow-slate-950/20 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                    >
                        <Trash2 class="size-6" />
                    </div>

                    <div class="mt-5 text-center">
                        <h2
                            class="text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            Hapus Lamaran?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus lamaran dari

                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedLamaran.nama_lengkap }}
                            </span>

                            ?

                            <br />

                            Data dan dokumen lamaran akan dihapus secara
                            permanen.
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
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-red-600/20 transition hover:bg-red-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60"
                            @click="deleteLamaran"
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
    </div>
</template>

<style scoped>
/* ==========================================================================
   Modal Transition
   ========================================================================== */

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
    transform: translateY(8px) scale(0.985);
}

/* ==========================================================================
   Decorative Blobs
   ========================================================================== */

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

/* ==========================================================================
   Blob Animations
   ========================================================================== */

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

/* ==========================================================================
   Accessibility
   ========================================================================== */

@media (prefers-reduced-motion: reduce) {
    .blob-shape,
    .blob-shape-delayed,
    .blob-shape-slow,
    .modal-enter-active,
    .modal-leave-active {
        animation: none;
        transition: none;
    }
}

/* ==========================================================================
   Mobile
   ========================================================================== */

@media (max-width: 640px) {
    .blob-shape {
        left: -10rem;
        top: -8rem;
        width: 20rem;
        height: 20rem;
    }

    .blob-shape-delayed {
        right: -8rem;
        width: 17rem;
        height: 17rem;
    }

    .blob-shape-slow {
        left: 35%;
        width: 15rem;
        height: 15rem;
    }
}
</style>
