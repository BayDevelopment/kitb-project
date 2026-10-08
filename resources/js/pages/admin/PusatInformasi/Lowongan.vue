<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowDown,
    ArrowUp,
    BriefcaseBusiness,
    Calendar,
    CheckCircle2,
    Clock3,
    Eye,
    MapPin,
    Pencil,
    Plus,
    RotateCcw,
    Search,
    Star,
    Trash2,
    UserRound,
    X,
} from "lucide-vue-next";
import { toast } from "vue-sonner";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

type LanguageCode = "id" | "en" | "zh";
type Status = "draft" | "published" | "closed";

interface Lowongan {
    id: number;
    slug: string;

    judul_id: string;
    judul_en: string;
    judul_zh: string;

    departemen_id: string | null;
    departemen_en: string | null;
    departemen_zh: string | null;

    lokasi_id: string | null;
    lokasi_en: string | null;
    lokasi_zh: string | null;

    tipe_pekerjaan: string | null;

    deskripsi_id: string | null;
    deskripsi_en: string | null;
    deskripsi_zh: string | null;

    tanggung_jawab_id: string | null;
    tanggung_jawab_en: string | null;
    tanggung_jawab_zh: string | null;

    kualifikasi_id: string | null;
    kualifikasi_en: string | null;
    kualifikasi_zh: string | null;

    benefit_id: string | null;
    benefit_en: string | null;
    benefit_zh: string | null;

    tanggal_mulai: string | null;
    tanggal_tutup: string | null;
    status: Status;
    unggulan: boolean;
    urutan: number;
    url?: string;
    is_expired?: boolean;
    is_open?: boolean;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData {
    data: Lowongan[];
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
    status: string;
}

interface StatusOption {
    value: string;
    label: string;
}

interface Props {
    lowongans: PaginatedData;
    filters: FilterProps;
    statusOptions: StatusOption[];
}

interface LowonganForm {
    judul_id: string;
    judul_en: string;
    judul_zh: string;

    departemen_id: string;
    departemen_en: string;
    departemen_zh: string;

    lokasi_id: string;
    lokasi_en: string;
    lokasi_zh: string;

    tipe_pekerjaan: string;

    deskripsi_id: string;
    deskripsi_en: string;
    deskripsi_zh: string;

    tanggung_jawab_id: string;
    tanggung_jawab_en: string;
    tanggung_jawab_zh: string;

    kualifikasi_id: string;
    kualifikasi_en: string;
    kualifikasi_zh: string;

    benefit_id: string;
    benefit_en: string;
    benefit_zh: string;

    tanggal_mulai: string;
    tanggal_tutup: string;
    status: Status;
    unggulan: boolean;
    urutan: number;
}

const props = defineProps<Props>();

const languages: Array<{
    code: LanguageCode;
    label: string;
    short: string;
}> = [
    { code: "id", label: "Indonesia", short: "ID" },
    { code: "en", label: "English", short: "EN" },
    { code: "zh", label: "中文", short: "ZH" },
];

const activeLanguage = ref<LanguageCode>("id");
const formLanguage = ref<LanguageCode>("id");
const detailLanguage = ref<LanguageCode>("id");

const setLanguage = (language: LanguageCode) => {
    activeLanguage.value = language;
};

const search = ref(props.filters.search ?? "");

const status = ref(
    props.filters.status &&
        ["draft", "published", "closed"].includes(props.filters.status)
        ? props.filters.status
        : "all",
);

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const applyFilter = () => {
    router.get(
        "/admin/pusat-informasi/lowongan",
        {
            search: search.value.trim() || undefined,
            status: status.value !== "all" ? status.value : undefined,
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

const resetFilter = () => {
    clearTimeout(searchTimeout);

    search.value = "";
    status.value = "all";

    router.get(
        "/admin/pusat-informasi/lowongan",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const hasFilter = computed(() => {
    return search.value.trim() !== "" || status.value !== "all";
});

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

const showModal = ref(false);
const showDetail = ref(false);
const showDelete = ref(false);

const modalMode = ref<"create" | "edit">("create");
const selectedLowongan = ref<Lowongan | null>(null);

const emptyForm = (): LowonganForm => ({
    judul_id: "",
    judul_en: "",
    judul_zh: "",

    departemen_id: "",
    departemen_en: "",
    departemen_zh: "",

    lokasi_id: "",
    lokasi_en: "",
    lokasi_zh: "",

    tipe_pekerjaan: "",

    deskripsi_id: "",
    deskripsi_en: "",
    deskripsi_zh: "",

    tanggung_jawab_id: "",
    tanggung_jawab_en: "",
    tanggung_jawab_zh: "",

    kualifikasi_id: "",
    kualifikasi_en: "",
    kualifikasi_zh: "",

    benefit_id: "",
    benefit_en: "",
    benefit_zh: "",

    tanggal_mulai: "",
    tanggal_tutup: "",
    status: "draft",
    unggulan: false,
    urutan: 1,
});

const form = ref<LowonganForm>(emptyForm());

const processingForm = ref(false);
const processingDelete = ref(false);

const togglingStatusId = ref<number | null>(null);
const togglingFeaturedId = ref<number | null>(null);

const movingId = ref<number | null>(null);
const movingDirection = ref<"up" | "down" | null>(null);

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

const formatDateInput = (value: string | null) => {
    if (!value) {
        return "";
    }

    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return value;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "";
    }

    const pad = (number: number) => String(number).padStart(2, "0");

    return `${date.getFullYear()}-${pad(
        date.getMonth() + 1,
    )}-${pad(date.getDate())}`;
};

const statusLabel = (statusValue: Status) => {
    return {
        draft: "Draft",
        published: "Published",
        closed: "Closed",
    }[statusValue];
};

const statusClass = (statusValue: Status) => {
    if (statusValue === "published") {
        return "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400";
    }

    if (statusValue === "closed") {
        return "bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400";
    }

    return "bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400";
};

const statusDotClass = (statusValue: Status) => {
    if (statusValue === "published") {
        return "bg-emerald-500";
    }

    if (statusValue === "closed") {
        return "bg-red-500";
    }

    return "bg-amber-500";
};

const typeLabel = (value: string | null) => {
    if (!value) {
        return "-";
    }

    const labels: Record<string, string> = {
        full_time: "Full Time",
        part_time: "Part Time",
        contract: "Contract",
        internship: "Internship",
        freelance: "Freelance",
    };

    return labels[value] ?? value;
};

const getFirstError = (errors: Record<string, string | string[]>) => {
    const firstError = Object.values(errors)[0];

    if (!firstError) {
        return null;
    }

    return Array.isArray(firstError) ? firstError[0] : String(firstError);
};

const localizedLowonganValue = (
    lowongan: Lowongan,
    field: "judul" | "departemen" | "lokasi" | "deskripsi",
): string => {
    const suffix =
        activeLanguage.value === "en"
            ? "_en"
            : activeLanguage.value === "zh"
              ? "_zh"
              : "_id";

    return String(lowongan[`${field}${suffix}` as keyof Lowongan] ?? "");
};

const languageLabel = computed(() => {
    return (
        languages.find((item) => item.code === activeLanguage.value)?.label ??
        "Indonesia"
    );
});

const currentLanguageLabel = computed(() => {
    return (
        languages.find((item) => item.code === formLanguage.value)?.label ??
        "Indonesia"
    );
});

const resetFormState = () => {
    showModal.value = false;
    form.value = emptyForm();
    selectedLowongan.value = null;
    formLanguage.value = "id";
};

const openCreate = () => {
    form.value = emptyForm();
    selectedLowongan.value = null;
    modalMode.value = "create";
    formLanguage.value = "id";
    detailLanguage.value = "id";
    showDetail.value = false;
    showDelete.value = false;
    showModal.value = true;
};

const openEdit = (lowongan: Lowongan) => {
    selectedLowongan.value = lowongan;

    form.value = {
        judul_id: lowongan.judul_id || "",
        judul_en: lowongan.judul_en || "",
        judul_zh: lowongan.judul_zh || "",

        departemen_id: lowongan.departemen_id || "",
        departemen_en: lowongan.departemen_en || "",
        departemen_zh: lowongan.departemen_zh || "",

        lokasi_id: lowongan.lokasi_id || "",
        lokasi_en: lowongan.lokasi_en || "",
        lokasi_zh: lowongan.lokasi_zh || "",

        tipe_pekerjaan: lowongan.tipe_pekerjaan || "",

        deskripsi_id: lowongan.deskripsi_id || "",
        deskripsi_en: lowongan.deskripsi_en || "",
        deskripsi_zh: lowongan.deskripsi_zh || "",

        tanggung_jawab_id: lowongan.tanggung_jawab_id || "",
        tanggung_jawab_en: lowongan.tanggung_jawab_en || "",
        tanggung_jawab_zh: lowongan.tanggung_jawab_zh || "",

        kualifikasi_id: lowongan.kualifikasi_id || "",
        kualifikasi_en: lowongan.kualifikasi_en || "",
        kualifikasi_zh: lowongan.kualifikasi_zh || "",

        benefit_id: lowongan.benefit_id || "",
        benefit_en: lowongan.benefit_en || "",
        benefit_zh: lowongan.benefit_zh || "",

        tanggal_mulai: formatDateInput(lowongan.tanggal_mulai),
        tanggal_tutup: formatDateInput(lowongan.tanggal_tutup),
        status: lowongan.status,
        unggulan: lowongan.unggulan,
        urutan: lowongan.urutan,
    };

    modalMode.value = "edit";
    formLanguage.value = "id";
    detailLanguage.value = "id";
    showDetail.value = false;
    showDelete.value = false;
    showModal.value = true;
};

const closeModal = () => {
    if (processingForm.value) {
        return;
    }

    resetFormState();
};

const openDetail = (lowongan: Lowongan) => {
    selectedLowongan.value = lowongan;
    detailLanguage.value = "id";
    showModal.value = false;
    showDelete.value = false;
    showDetail.value = true;
};

const closeDetail = () => {
    showDetail.value = false;
    selectedLowongan.value = null;
};

const openDelete = (lowongan: Lowongan) => {
    selectedLowongan.value = lowongan;
    showModal.value = false;
    showDetail.value = false;
    showDelete.value = true;
};

const closeDelete = () => {
    if (processingDelete.value) {
        return;
    }

    showDelete.value = false;
    selectedLowongan.value = null;
};

const submitForm = () => {
    if (processingForm.value) {
        return;
    }

    if (!form.value.judul_id.trim()) {
        formLanguage.value = "id";
        toast.error("Judul lowongan Bahasa Indonesia wajib diisi.");
        return;
    }

    if (form.value.judul_id.trim().length < 3) {
        formLanguage.value = "id";
        toast.error("Judul lowongan Bahasa Indonesia minimal 3 karakter.");
        return;
    }

    if (!form.value.judul_en.trim()) {
        formLanguage.value = "en";
        toast.error("Judul lowongan Bahasa Inggris wajib diisi.");
        return;
    }

    if (!form.value.judul_zh.trim()) {
        formLanguage.value = "zh";
        toast.error("Judul lowongan Bahasa Mandarin wajib diisi.");
        return;
    }

    if (
        form.value.tanggal_mulai &&
        form.value.tanggal_tutup &&
        form.value.tanggal_tutup < form.value.tanggal_mulai
    ) {
        toast.error("Tanggal tutup harus sama atau setelah tanggal mulai.");
        return;
    }

    const parsedUrutan = Number(form.value.urutan);
    const urutan =
        Number.isFinite(parsedUrutan) && parsedUrutan >= 1
            ? Math.floor(parsedUrutan)
            : 1;

    const trimOrNull = (value: string) => value.trim() || null;

    const data = {
        judul_id: form.value.judul_id.trim(),
        judul_en: form.value.judul_en.trim(),
        judul_zh: form.value.judul_zh.trim(),

        departemen_id: trimOrNull(form.value.departemen_id),
        departemen_en: trimOrNull(form.value.departemen_en),
        departemen_zh: trimOrNull(form.value.departemen_zh),

        lokasi_id: trimOrNull(form.value.lokasi_id),
        lokasi_en: trimOrNull(form.value.lokasi_en),
        lokasi_zh: trimOrNull(form.value.lokasi_zh),

        tipe_pekerjaan: trimOrNull(form.value.tipe_pekerjaan),

        deskripsi_id: trimOrNull(form.value.deskripsi_id),
        deskripsi_en: trimOrNull(form.value.deskripsi_en),
        deskripsi_zh: trimOrNull(form.value.deskripsi_zh),

        tanggung_jawab_id: trimOrNull(form.value.tanggung_jawab_id),
        tanggung_jawab_en: trimOrNull(form.value.tanggung_jawab_en),
        tanggung_jawab_zh: trimOrNull(form.value.tanggung_jawab_zh),

        kualifikasi_id: trimOrNull(form.value.kualifikasi_id),
        kualifikasi_en: trimOrNull(form.value.kualifikasi_en),
        kualifikasi_zh: trimOrNull(form.value.kualifikasi_zh),

        benefit_id: trimOrNull(form.value.benefit_id),
        benefit_en: trimOrNull(form.value.benefit_en),
        benefit_zh: trimOrNull(form.value.benefit_zh),

        tanggal_mulai: form.value.tanggal_mulai || null,
        tanggal_tutup: form.value.tanggal_tutup || null,
        status: form.value.status,
        unggulan: form.value.unggulan,
        urutan,
    };

    processingForm.value = true;

    if (modalMode.value === "create") {
        router.post("/admin/pusat-informasi/lowongan", data, {
            preserveScroll: true,
            onSuccess: () => {
                resetFormState();
            },
            onError: (errors) => {
                console.error("Gagal menambahkan lowongan:", errors);

                const message = getFirstError(errors);

                if (message) {
                    toast.error(message);
                }
            },
            onFinish: () => {
                processingForm.value = false;
            },
        });

        return;
    }

    if (!selectedLowongan.value) {
        processingForm.value = false;
        return;
    }

    router.put(
        `/admin/pusat-informasi/lowongan/${selectedLowongan.value.id}`,
        data,
        {
            preserveScroll: true,
            onSuccess: () => {
                resetFormState();
            },
            onError: (errors) => {
                console.error("Gagal memperbarui lowongan:", errors);

                const message = getFirstError(errors);

                if (message) {
                    toast.error(message);
                }
            },
            onFinish: () => {
                processingForm.value = false;
            },
        },
    );
};

const deleteLowongan = () => {
    if (!selectedLowongan.value || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(
        `/admin/pusat-informasi/lowongan/${selectedLowongan.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                showDelete.value = false;
                selectedLowongan.value = null;
            },
            onError: (errors) => {
                console.error("Gagal menghapus lowongan:", errors);

                const message = getFirstError(errors);

                if (message) {
                    toast.error(message);
                }
            },
            onFinish: () => {
                processingDelete.value = false;
            },
        },
    );
};

const toggleStatus = (lowongan: Lowongan) => {
    if (togglingStatusId.value !== null) {
        return;
    }

    const nextStatus = lowongan.status === "published" ? "closed" : "published";

    togglingStatusId.value = lowongan.id;

    router.patch(
        `/admin/pusat-informasi/lowongan/${lowongan.id}/toggle-status`,
        {
            status: nextStatus,
        },
        {
            preserveScroll: true,
            onError: (errors) => {
                console.error("Gagal mengubah status lowongan:", errors);

                const message = getFirstError(errors);

                if (message) {
                    toast.error(message);
                }
            },
            onFinish: () => {
                togglingStatusId.value = null;
            },
        },
    );
};

const toggleFeatured = (lowongan: Lowongan) => {
    if (togglingFeaturedId.value !== null) {
        return;
    }

    togglingFeaturedId.value = lowongan.id;

    router.patch(
        `/admin/pusat-informasi/lowongan/${lowongan.id}/toggle-featured`,
        {},
        {
            preserveScroll: true,
            onError: (errors) => {
                console.error("Gagal mengubah status unggulan:", errors);

                const message = getFirstError(errors);

                if (message) {
                    toast.error(message);
                }
            },
            onFinish: () => {
                togglingFeaturedId.value = null;
            },
        },
    );
};

const moveLowongan = (lowongan: Lowongan, direction: "up" | "down") => {
    if (movingId.value !== null) {
        return;
    }

    movingId.value = lowongan.id;
    movingDirection.value = direction;

    router.patch(
        `/admin/pusat-informasi/lowongan/${lowongan.id}/move`,
        {
            direction,
        },
        {
            preserveScroll: true,
            onError: (errors) => {
                console.error("Gagal mengubah urutan lowongan:", errors);

                const message = getFirstError(errors);

                if (message) {
                    toast.error(message);
                }
            },
            onFinish: () => {
                movingId.value = null;
                movingDirection.value = null;
            },
        },
    );
};

onBeforeUnmount(() => {
    clearTimeout(searchTimeout);
});
</script>

<template>
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
                class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <BriefcaseBusiness class="size-5" />
                    </div>

                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400"
                        >
                            Pusat Informasi
                        </p>

                        <h1
                            class="mt-0.5 text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl"
                        >
                            Lowongan Kerja
                        </h1>

                        <p
                            class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Kelola informasi dan publikasi lowongan pekerjaan
                            perusahaan.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div
                        class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white/90 p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900/90"
                    >
                        <button
                            v-for="language in languages"
                            :key="language.code"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold transition-all"
                            :class="
                                activeLanguage === language.code
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'
                            "
                            @click="setLanguage(language.code)"
                        >
                            <span>{{ language.short }}</span>
                        </button>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                        @click="openCreate"
                    >
                        <Plus class="size-4" />
                        Tambah Lowongan
                    </button>
                </div>
            </div>

            <div
                class="mb-5 flex items-center gap-2 rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-3 text-sm text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/20 dark:text-blue-300"
            >
                <span
                    class="flex size-7 items-center justify-center rounded-lg bg-white/80 font-semibold text-xs dark:bg-slate-900/60"
                >
                    {{ activeLanguage.toUpperCase() }}
                </span>

                <span>
                    Tampilan bahasa:
                    <strong>{{ languageLabel }}</strong>
                </span>
            </div>

            <!-- =====================================================
                 FILTER
            ====================================================== -->

            <div
                class="mb-5 rounded-3xl border border-slate-200/80 bg-white/90 p-4 shadow-sm shadow-slate-200/40 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
            >
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h2
                            class="text-sm font-semibold text-slate-800 dark:text-slate-100"
                        >
                            Pencarian & Filter
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-slate-400 dark:text-slate-500"
                        >
                            Temukan data lowongan dengan cepat.
                        </p>
                    </div>

                    <button
                        v-if="hasFilter"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white"
                        @click="resetFilter"
                    >
                        <RotateCcw class="size-3.5" />
                        Reset
                    </button>
                </div>

                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px]">
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari judul, departemen, lokasi, atau tipe pekerjaan..."
                            class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900"
                        />
                    </div>

                    <select
                        v-model="status"
                        class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="all">Semua Status</option>

                        <option
                            v-for="option in statusOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- =====================================================
                 TABLE
            ====================================================== -->

            <div
                class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/50 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800"
                >
                    <div>
                        <h2
                            class="text-sm font-semibold text-slate-800 dark:text-slate-100"
                        >
                            Data Lowongan
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-slate-400 dark:text-slate-500"
                        >
                            {{ lowongans.total }} data tersedia
                        </p>
                    </div>

                    <div
                        class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                    >
                        {{ activeLanguage.toUpperCase() }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead
                            class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <tr>
                                <th
                                    class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Lowongan
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Departemen
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Lokasi
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Periode
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Urutan
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
                                v-for="lowongan in lowongans.data"
                                :key="lowongan.id"
                                class="transition-colors duration-200 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                            >
                                <!-- LOWONGAN -->

                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-0.5 flex size-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:ring-blue-900/40"
                                        >
                                            <BriefcaseBusiness class="size-5" />
                                        </div>

                                        <div class="min-w-0 max-w-md">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <p
                                                    class="truncate font-semibold text-slate-900 dark:text-white"
                                                >
                                                    {{
                                                        localizedLowonganValue(
                                                            lowongan,
                                                            "judul",
                                                        )
                                                    }}
                                                </p>

                                                <span
                                                    v-if="lowongan.unggulan"
                                                    class="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400"
                                                >
                                                    <Star
                                                        class="size-3 fill-current"
                                                    />
                                                    Unggulan
                                                </span>
                                            </div>

                                            <p
                                                class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{
                                                    truncate(
                                                        localizedLowonganValue(
                                                            lowongan,
                                                            "deskripsi",
                                                        ),
                                                        90,
                                                    )
                                                }}
                                            </p>

                                            <div
                                                class="mt-1.5 flex items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500"
                                            >
                                                <span>
                                                    {{
                                                        typeLabel(
                                                            lowongan.tipe_pekerjaan,
                                                        )
                                                    }}
                                                </span>

                                                <span>•</span>

                                                <span>
                                                    /{{ lowongan.slug }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- DEPARTEMEN -->

                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center gap-2 text-slate-600 dark:text-slate-300"
                                    >
                                        <UserRound
                                            class="size-4 text-slate-400"
                                        />

                                        <span>
                                            {{
                                                localizedLowonganValue(
                                                    lowongan,
                                                    "departemen",
                                                ) || "-"
                                            }}
                                        </span>
                                    </div>
                                </td>

                                <!-- LOKASI -->

                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center gap-2 text-slate-600 dark:text-slate-300"
                                    >
                                        <MapPin class="size-4 text-slate-400" />

                                        <span>
                                            {{
                                                localizedLowonganValue(
                                                    lowongan,
                                                    "lokasi",
                                                ) || "-"
                                            }}
                                        </span>
                                    </div>
                                </td>

                                <!-- PERIODE -->

                                <td class="px-6 py-4">
                                    <div
                                        class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        <div class="flex items-center gap-2">
                                            <Calendar
                                                class="size-3.5 text-slate-400"
                                            />

                                            <span>
                                                {{
                                                    formatDate(
                                                        lowongan.tanggal_mulai,
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <Clock3
                                                class="size-3.5 text-slate-400"
                                            />

                                            <span>
                                                Tutup:
                                                {{
                                                    formatDate(
                                                        lowongan.tanggal_tutup,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- STATUS -->

                                <td class="px-6 py-4">
                                    <div
                                        class="flex flex-col items-start gap-2"
                                    >
                                        <button
                                            type="button"
                                            :disabled="
                                                togglingStatusId === lowongan.id
                                            "
                                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition hover:opacity-80 disabled:cursor-wait disabled:opacity-60"
                                            :class="
                                                statusClass(lowongan.status)
                                            "
                                            @click="toggleStatus(lowongan)"
                                        >
                                            <span
                                                v-if="
                                                    togglingStatusId ===
                                                    lowongan.id
                                                "
                                                class="size-3 animate-spin rounded-full border border-current/30 border-t-current"
                                            ></span>

                                            <span
                                                v-else
                                                class="size-1.5 rounded-full"
                                                :class="
                                                    statusDotClass(
                                                        lowongan.status,
                                                    )
                                                "
                                            ></span>

                                            {{
                                                togglingStatusId === lowongan.id
                                                    ? "Memproses..."
                                                    : statusLabel(
                                                          lowongan.status,
                                                      )
                                            }}
                                        </button>

                                        <button
                                            type="button"
                                            :disabled="
                                                togglingFeaturedId ===
                                                lowongan.id
                                            "
                                            class="inline-flex items-center gap-1.5 text-xs font-medium transition disabled:cursor-wait disabled:opacity-50"
                                            :class="
                                                lowongan.unggulan
                                                    ? 'text-amber-600 dark:text-amber-400'
                                                    : 'text-slate-400 hover:text-amber-600 dark:hover:text-amber-400'
                                            "
                                            @click="toggleFeatured(lowongan)"
                                        >
                                            <span
                                                v-if="
                                                    togglingFeaturedId ===
                                                    lowongan.id
                                                "
                                                class="size-3 animate-spin rounded-full border border-current/30 border-t-current"
                                            ></span>

                                            <Star
                                                v-else
                                                class="size-3.5"
                                                :class="{
                                                    'fill-current':
                                                        lowongan.unggulan,
                                                }"
                                            />

                                            {{
                                                lowongan.unggulan
                                                    ? "Unggulan"
                                                    : "Jadikan unggulan"
                                            }}
                                        </button>
                                    </div>
                                </td>

                                <!-- URUTAN -->

                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-center gap-1"
                                    >
                                        <button
                                            type="button"
                                            :disabled="movingId === lowongan.id"
                                            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Pindah ke atas"
                                            @click="
                                                moveLowongan(lowongan, 'up')
                                            "
                                        >
                                            <span
                                                v-if="
                                                    movingId === lowongan.id &&
                                                    movingDirection === 'up'
                                                "
                                                class="block size-4 animate-spin rounded-full border-2 border-slate-300 border-t-blue-500"
                                            ></span>

                                            <ArrowUp v-else class="size-4" />
                                        </button>

                                        <span
                                            class="min-w-8 text-center text-xs font-semibold text-slate-500 dark:text-slate-400"
                                        >
                                            {{ lowongan.urutan }}
                                        </span>

                                        <button
                                            type="button"
                                            :disabled="movingId === lowongan.id"
                                            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Pindah ke bawah"
                                            @click="
                                                moveLowongan(lowongan, 'down')
                                            "
                                        >
                                            <span
                                                v-if="
                                                    movingId === lowongan.id &&
                                                    movingDirection === 'down'
                                                "
                                                class="block size-4 animate-spin rounded-full border-2 border-slate-300 border-t-blue-500"
                                            ></span>

                                            <ArrowDown v-else class="size-4" />
                                        </button>
                                    </div>
                                </td>

                                <!-- AKSI -->

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-1">
                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Lihat detail"
                                            @click="openDetail(lowongan)"
                                        >
                                            <Eye class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                            title="Edit lowongan"
                                            @click="openEdit(lowongan)"
                                        >
                                            <Pencil class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                            title="Hapus lowongan"
                                            @click="openDelete(lowongan)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- EMPTY -->

                            <tr v-if="lowongans.data.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600"
                                    >
                                        <BriefcaseBusiness class="size-7" />
                                    </div>

                                    <p
                                        class="mt-4 font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Data lowongan tidak ditemukan
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
                    v-if="lowongans.total > 0"
                    class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Menampilkan

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ lowongans.from }}
                        </span>

                        -

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ lowongans.to }}
                        </span>

                        dari

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ lowongans.total }}
                        </span>

                        data
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="(link, index) in lowongans.links"
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
                        ></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             MODAL CREATE / EDIT
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
                @click.self="closeModal"
            >
                <div
                    class="w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-800 dark:bg-slate-900"
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
                                    <BriefcaseBusiness class="size-4" />
                                </div>

                                <h2
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    {{
                                        modalMode === "create"
                                            ? "Tambah Lowongan"
                                            : "Edit Lowongan"
                                    }}
                                </h2>
                            </div>

                            <p
                                class="mt-1 pl-11 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Lengkapi informasi lowongan dalam tiga bahasa.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-200/70 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800"
                            :disabled="processingForm"
                            @click="closeModal"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- FORM -->

                    <form
                        class="max-h-[78vh] overflow-y-auto p-6"
                        @submit.prevent="submitForm"
                    >
                        <div
                            class="mb-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-1.5 dark:border-slate-800 dark:bg-slate-800/40"
                        >
                            <div class="grid grid-cols-3 gap-1">
                                <button
                                    v-for="language in languages"
                                    :key="language.code"
                                    type="button"
                                    class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all"
                                    :class="
                                        formLanguage === language.code
                                            ? 'bg-white text-blue-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:text-blue-400 dark:ring-slate-700'
                                            : 'text-slate-500 hover:bg-white/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-900/60 dark:hover:text-white'
                                    "
                                    @click="formLanguage = language.code"
                                >
                                    <span>{{ language.short }}</span>
                                    <span>{{ language.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div
                            class="mb-6 rounded-2xl border border-blue-100 bg-blue-50/60 p-4 dark:border-blue-900/40 dark:bg-blue-950/20"
                        >
                            <p
                                class="text-sm font-medium text-blue-800 dark:text-blue-300"
                            >
                                Konten Bahasa {{ currentLanguageLabel }}
                            </p>
                            <p
                                class="mt-1 text-xs leading-5 text-blue-700/80 dark:text-blue-400/80"
                            >
                                Isi judul, departemen, lokasi, deskripsi,
                                tanggung jawab, kualifikasi, dan benefit untuk
                                bahasa yang dipilih.
                            </p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <!-- JUDUL -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Judul Lowongan
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    v-if="formLanguage === 'id'"
                                    v-model="form.judul_id"
                                    type="text"
                                    required
                                    minlength="3"
                                    maxlength="255"
                                    autocomplete="off"
                                    placeholder="Contoh: Staff Administrasi"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />

                                <input
                                    v-else-if="formLanguage === 'en'"
                                    v-model="form.judul_en"
                                    type="text"
                                    required
                                    minlength="3"
                                    maxlength="255"
                                    autocomplete="off"
                                    placeholder="Example: Administrative Staff"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />

                                <input
                                    v-else
                                    v-model="form.judul_zh"
                                    type="text"
                                    required
                                    minlength="1"
                                    maxlength="255"
                                    autocomplete="off"
                                    placeholder="例如：行政人员"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Judul dalam ketiga bahasa wajib diisi.
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
                                    <input
                                        :value="
                                            selectedLowongan?.slug ||
                                            'slug-dibuat-otomatis'
                                        "
                                        type="text"
                                        readonly
                                        disabled
                                        tabindex="-1"
                                        autocomplete="off"
                                        aria-readonly="true"
                                        aria-disabled="true"
                                        class="h-11 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 pr-16 text-sm text-slate-500 outline-none dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-400"
                                    />

                                    <span
                                        class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded-lg bg-slate-200 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-700 dark:text-slate-400"
                                    >
                                        Auto
                                    </span>
                                </div>

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Slug dibuat otomatis oleh server berdasarkan
                                    judul Bahasa Indonesia dan tidak dikirim
                                    dari frontend.
                                </p>
                            </div>

                            <!-- DEPARTEMEN -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Departemen
                                </label>

                                <input
                                    v-if="formLanguage === 'id'"
                                    v-model="form.departemen_id"
                                    type="text"
                                    maxlength="150"
                                    placeholder="Contoh: Human Resources"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />

                                <input
                                    v-else-if="formLanguage === 'en'"
                                    v-model="form.departemen_en"
                                    type="text"
                                    maxlength="150"
                                    placeholder="Example: Human Resources"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />

                                <input
                                    v-else
                                    v-model="form.departemen_zh"
                                    type="text"
                                    maxlength="150"
                                    placeholder="例如：人力资源部"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>

                            <!-- LOKASI -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Lokasi
                                </label>

                                <input
                                    v-if="formLanguage === 'id'"
                                    v-model="form.lokasi_id"
                                    type="text"
                                    maxlength="150"
                                    placeholder="Contoh: Siak, Riau"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />

                                <input
                                    v-else-if="formLanguage === 'en'"
                                    v-model="form.lokasi_en"
                                    type="text"
                                    maxlength="150"
                                    placeholder="Example: Siak, Riau"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />

                                <input
                                    v-else
                                    v-model="form.lokasi_zh"
                                    type="text"
                                    maxlength="150"
                                    placeholder="例如：廖内省锡亚克"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>

                            <!-- TIPE -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Tipe Pekerjaan
                                </label>

                                <select
                                    v-model="form.tipe_pekerjaan"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <option value="">
                                        Pilih tipe pekerjaan
                                    </option>
                                    <option value="full_time">Full Time</option>
                                    <option value="part_time">Part Time</option>
                                    <option value="contract">Contract</option>
                                    <option value="internship">
                                        Internship
                                    </option>
                                    <option value="freelance">Freelance</option>
                                </select>
                            </div>

                            <!-- STATUS -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Status
                                </label>

                                <select
                                    v-model="form.status"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>

                            <!-- DATES -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Tanggal Mulai
                                </label>
                                <input
                                    v-model="form.tanggal_mulai"
                                    type="date"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Tanggal Tutup
                                </label>
                                <input
                                    v-model="form.tanggal_tutup"
                                    type="date"
                                    :min="form.tanggal_mulai || undefined"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>

                            <!-- URUTAN -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Urutan
                                </label>
                                <input
                                    v-model.number="form.urutan"
                                    type="number"
                                    min="1"
                                    max="4294967295"
                                    step="1"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>

                            <!-- FEATURED -->
                            <div
                                class="flex items-center rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50"
                            >
                                <label
                                    class="flex cursor-pointer items-center gap-3"
                                >
                                    <input
                                        v-model="form.unggulan"
                                        type="checkbox"
                                        class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Jadikan Lowongan Unggulan
                                        </p>
                                        <p
                                            class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Tandai lowongan untuk ditampilkan
                                            sebagai konten unggulan.
                                        </p>
                                    </div>
                                </label>
                            </div>

                            <!-- DESKRIPSI -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Deskripsi
                                </label>
                                <textarea
                                    v-if="formLanguage === 'id'"
                                    v-model="form.deskripsi_id"
                                    rows="5"
                                    maxlength="50000"
                                    placeholder="Tuliskan gambaran umum mengenai posisi yang dibutuhkan..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                                <textarea
                                    v-else-if="formLanguage === 'en'"
                                    v-model="form.deskripsi_en"
                                    rows="5"
                                    maxlength="50000"
                                    placeholder="Describe the position and its main purpose..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                                <textarea
                                    v-else
                                    v-model="form.deskripsi_zh"
                                    rows="5"
                                    maxlength="50000"
                                    placeholder="请填写职位概述..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                            </div>

                            <!-- TANGGUNG JAWAB -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Tanggung Jawab
                                </label>
                                <textarea
                                    v-if="formLanguage === 'id'"
                                    v-model="form.tanggung_jawab_id"
                                    rows="6"
                                    maxlength="50000"
                                    placeholder="Tuliskan tanggung jawab dan tugas utama posisi..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                                <textarea
                                    v-else-if="formLanguage === 'en'"
                                    v-model="form.tanggung_jawab_en"
                                    rows="6"
                                    maxlength="50000"
                                    placeholder="Describe the main responsibilities and duties..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                                <textarea
                                    v-else
                                    v-model="form.tanggung_jawab_zh"
                                    rows="6"
                                    maxlength="50000"
                                    placeholder="请填写主要职责和工作内容..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                                <p class="mt-1.5 text-xs text-slate-400">
                                    Kamu dapat menuliskan setiap poin dalam
                                    baris baru.
                                </p>
                            </div>

                            <!-- KUALIFIKASI -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Kualifikasi
                                </label>
                                <textarea
                                    v-if="formLanguage === 'id'"
                                    v-model="form.kualifikasi_id"
                                    rows="6"
                                    maxlength="50000"
                                    placeholder="Tuliskan pendidikan, pengalaman, kemampuan, dan persyaratan kandidat..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                                <textarea
                                    v-else-if="formLanguage === 'en'"
                                    v-model="form.kualifikasi_en"
                                    rows="6"
                                    maxlength="50000"
                                    placeholder="Describe education, experience, skills, and candidate requirements..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                                <textarea
                                    v-else
                                    v-model="form.kualifikasi_zh"
                                    rows="6"
                                    maxlength="50000"
                                    placeholder="请填写学历、经验、技能和任职要求..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                            </div>

                            <!-- BENEFIT -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Benefit
                                </label>
                                <textarea
                                    v-if="formLanguage === 'id'"
                                    v-model="form.benefit_id"
                                    rows="5"
                                    maxlength="50000"
                                    placeholder="Tuliskan benefit atau fasilitas yang ditawarkan perusahaan..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                                <textarea
                                    v-else-if="formLanguage === 'en'"
                                    v-model="form.benefit_en"
                                    rows="5"
                                    maxlength="50000"
                                    placeholder="Describe the benefits or facilities offered by the company..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                                <textarea
                                    v-else
                                    v-model="form.benefit_zh"
                                    rows="5"
                                    maxlength="50000"
                                    placeholder="请填写公司提供的福利和设施..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                ></textarea>
                            </div>
                        </div>
                        <!-- FOOTER -->

                        <div
                            class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800"
                        >
                            <button
                                type="button"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                :disabled="processingForm"
                                @click="closeModal"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="
                                    processingForm ||
                                    !form.judul_id.trim() ||
                                    !form.judul_en.trim() ||
                                    !form.judul_zh.trim()
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
                                        : modalMode === "create"
                                          ? "Simpan Lowongan"
                                          : "Simpan Perubahan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- =========================================================
             DETAIL
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showDetail && selectedLowongan"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
                @click.self="closeDetail"
            >
                <div
                    class="w-full max-w-4xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- HEADER -->

                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40"
                    >
                        <div>
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Lowongan
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap lowongan pekerjaan.
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

                    <div class="max-h-[75vh] space-y-6 overflow-y-auto p-6">
                        <div
                            class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-800"
                        >
                            <button
                                v-for="language in languages"
                                :key="language.code"
                                type="button"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                                :class="
                                    detailLanguage === language.code
                                        ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400'
                                        : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white'
                                "
                                @click="detailLanguage = language.code"
                            >
                                {{ language.short }}
                            </button>
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="
                                        statusClass(selectedLowongan.status)
                                    "
                                >
                                    <span
                                        class="size-1.5 rounded-full"
                                        :class="
                                            statusDotClass(
                                                selectedLowongan.status,
                                            )
                                        "
                                    ></span>
                                    {{ statusLabel(selectedLowongan.status) }}
                                </span>

                                <span
                                    v-if="selectedLowongan.unggulan"
                                    class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-400"
                                >
                                    <Star class="size-3.5 fill-current" />
                                    Unggulan
                                </span>

                                <span
                                    v-if="selectedLowongan.tipe_pekerjaan"
                                    class="inline-flex rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    {{
                                        typeLabel(
                                            selectedLowongan.tipe_pekerjaan,
                                        )
                                    }}
                                </span>
                            </div>

                            <h3
                                class="mt-4 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{
                                    detailLanguage === "id"
                                        ? selectedLowongan.judul_id
                                        : detailLanguage === "en"
                                          ? selectedLowongan.judul_en
                                          : selectedLowongan.judul_zh
                                }}
                            </h3>

                            <div
                                class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                <span class="flex items-center gap-1.5">
                                    <UserRound class="size-4" />
                                    {{
                                        detailLanguage === "id"
                                            ? selectedLowongan.departemen_id ||
                                              "Departemen tidak ditentukan"
                                            : detailLanguage === "en"
                                              ? selectedLowongan.departemen_en ||
                                                "Department not specified"
                                              : selectedLowongan.departemen_zh ||
                                                "未指定部门"
                                    }}
                                </span>

                                <span class="flex items-center gap-1.5">
                                    <MapPin class="size-4" />
                                    {{
                                        detailLanguage === "id"
                                            ? selectedLowongan.lokasi_id ||
                                              "Lokasi tidak ditentukan"
                                            : detailLanguage === "en"
                                              ? selectedLowongan.lokasi_en ||
                                                "Location not specified"
                                              : selectedLowongan.lokasi_zh ||
                                                "未指定地点"
                                    }}
                                </span>
                            </div>

                            <div
                                class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                <span class="flex items-center gap-1.5">
                                    <Calendar class="size-4" />
                                    Mulai:
                                    {{
                                        formatDate(
                                            selectedLowongan.tanggal_mulai,
                                        )
                                    }}
                                </span>

                                <span class="flex items-center gap-1.5">
                                    <Clock3 class="size-4" />
                                    Tutup:
                                    {{
                                        formatDate(
                                            selectedLowongan.tanggal_tutup,
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="
                                (detailLanguage === 'id' &&
                                    selectedLowongan.deskripsi_id) ||
                                (detailLanguage === 'en' &&
                                    selectedLowongan.deskripsi_en) ||
                                (detailLanguage === 'zh' &&
                                    selectedLowongan.deskripsi_zh)
                            "
                            class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5 dark:border-blue-900/40 dark:bg-blue-950/20"
                        >
                            <div class="flex items-center gap-2">
                                <BriefcaseBusiness
                                    class="size-4 text-blue-600 dark:text-blue-400"
                                />
                                <h4
                                    class="font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Deskripsi
                                </h4>
                            </div>

                            <p
                                class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    detailLanguage === "id"
                                        ? selectedLowongan.deskripsi_id
                                        : detailLanguage === "en"
                                          ? selectedLowongan.deskripsi_en
                                          : selectedLowongan.deskripsi_zh
                                }}
                            </p>
                        </div>

                        <div
                            v-if="
                                (detailLanguage === 'id' &&
                                    selectedLowongan.tanggung_jawab_id) ||
                                (detailLanguage === 'en' &&
                                    selectedLowongan.tanggung_jawab_en) ||
                                (detailLanguage === 'zh' &&
                                    selectedLowongan.tanggung_jawab_zh)
                            "
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <div class="flex items-center gap-2">
                                <CheckCircle2
                                    class="size-4 text-blue-600 dark:text-blue-400"
                                />
                                <h4
                                    class="font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Tanggung Jawab
                                </h4>
                            </div>

                            <p
                                class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    detailLanguage === "id"
                                        ? selectedLowongan.tanggung_jawab_id
                                        : detailLanguage === "en"
                                          ? selectedLowongan.tanggung_jawab_en
                                          : selectedLowongan.tanggung_jawab_zh
                                }}
                            </p>
                        </div>

                        <div
                            v-if="
                                (detailLanguage === 'id' &&
                                    selectedLowongan.kualifikasi_id) ||
                                (detailLanguage === 'en' &&
                                    selectedLowongan.kualifikasi_en) ||
                                (detailLanguage === 'zh' &&
                                    selectedLowongan.kualifikasi_zh)
                            "
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <div class="flex items-center gap-2">
                                <UserRound
                                    class="size-4 text-blue-600 dark:text-blue-400"
                                />
                                <h4
                                    class="font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Kualifikasi
                                </h4>
                            </div>

                            <p
                                class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    detailLanguage === "id"
                                        ? selectedLowongan.kualifikasi_id
                                        : detailLanguage === "en"
                                          ? selectedLowongan.kualifikasi_en
                                          : selectedLowongan.kualifikasi_zh
                                }}
                            </p>
                        </div>

                        <div
                            v-if="
                                (detailLanguage === 'id' &&
                                    selectedLowongan.benefit_id) ||
                                (detailLanguage === 'en' &&
                                    selectedLowongan.benefit_en) ||
                                (detailLanguage === 'zh' &&
                                    selectedLowongan.benefit_zh)
                            "
                            class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-5 dark:border-emerald-900/30 dark:bg-emerald-950/20"
                        >
                            <div class="flex items-center gap-2">
                                <CheckCircle2
                                    class="size-4 text-emerald-600 dark:text-emerald-400"
                                />
                                <h4
                                    class="font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Benefit
                                </h4>
                            </div>

                            <p
                                class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    detailLanguage === "id"
                                        ? selectedLowongan.benefit_id
                                        : detailLanguage === "en"
                                          ? selectedLowongan.benefit_en
                                          : selectedLowongan.benefit_zh
                                }}
                            </p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                >
                                    Slug
                                </p>
                                <p
                                    class="mt-2 break-all text-sm text-slate-700 dark:text-slate-200"
                                >
                                    {{ selectedLowongan.slug }}
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                >
                                    Urutan
                                </p>
                                <p
                                    class="mt-2 text-sm text-slate-700 dark:text-slate-200"
                                >
                                    {{ selectedLowongan.urutan }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="border-t border-slate-200 pt-5 dark:border-slate-800"
                        >
                            <div
                                class="grid gap-3 text-xs text-slate-400 sm:grid-cols-2"
                            >
                                <p>
                                    Dibuat:
                                    {{
                                        formatDateTime(
                                            selectedLowongan.created_at,
                                        )
                                    }}
                                </p>
                                <p>
                                    Diperbarui:
                                    {{
                                        formatDateTime(
                                            selectedLowongan.updated_at,
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
             DELETE
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showDelete && selectedLowongan"
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
                            Hapus Lowongan?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus

                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedLowongan.judul_id }}
                            </span>

                            ?

                            <br />

                            Data lowongan ini akan dihapus secara permanen.
                        </p>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closeDelete"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-red-600/20 transition hover:bg-red-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60"
                            @click="deleteLowongan"
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
