<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowDown,
    ArrowUp,
    Calendar,
    Eye,
    Image as ImageIcon,
    Images,
    Pencil,
    Plus,
    RotateCcw,
    Search,
    Trash2,
    Upload,
    X,
} from "lucide-vue-next";
import { toast } from "vue-sonner";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    currentLanguage,
    localizedValue,
    type LanguageCode,
} from "@/composables/useLocale";

defineOptions({
    layout: AppLayout,
});

/* ==========================================================================
 * INTERFACES
 * ========================================================================== */

interface Galeri {
    id: number;

    // Indonesia
    judul_id: string;
    deskripsi_id: string | null;
    alt_text_id: string | null;

    // English
    judul_en: string | null;
    deskripsi_en: string | null;
    alt_text_en: string | null;

    // Mandarin
    judul_zh: string | null;
    deskripsi_zh: string | null;
    alt_text_zh: string | null;

    // Shared
    slug: string;
    kategori: string | null;
    gambar: string;
    tanggal: string | null;
    status: boolean;
    urutan: number;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData {
    data: Galeri[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
}

interface Props {
    galeris: PaginatedData;

    filters: {
        search: string;
        kategori: string;
        status: string;
    };

    kategoriOptions: string[];
}

const props = defineProps<Props>();

/* ==========================================================================
 * LANGUAGE
 * ========================================================================== */

const languageOptions: Array<{
    code: LanguageCode;
    label: string;
    short: string;
    flag: string;
}> = [
    {
        code: "id",
        label: "Bahasa Indonesia",
        short: "ID",
        flag: "🇮🇩",
    },
    {
        code: "en",
        label: "English",
        short: "EN",
        flag: "🇬🇧",
    },
    {
        code: "zh",
        label: "中文",
        short: "中文",
        flag: "🇨🇳",
    },
];

const activeLanguage = ref<LanguageCode>("id");

/**
 * Adapter supaya localizedValue() dapat bekerja
 * dengan struktur Galeri:
 *
 * id  -> judul / deskripsi
 * en  -> judul_en / deskripsi_en
 * zh  -> judul_zh / deskripsi_zh
 */
const localizedGaleriValue = (
    galeri: Galeri,
    field: "judul" | "deskripsi",
): string => {
    return localizedValue(
        {
            ...galeri,
            judul: galeri.judul_id,
            deskripsi: galeri.deskripsi_id,
        },
        field,
    );
};

const localizedGaleriAltText = (galeri: Galeri): string => {
    const source: Record<string, unknown> = {
        ...galeri,
        alt_text: galeri.alt_text_id,
    };

    return localizedValue(source, "alt_text");
};

const setLanguage = (language: LanguageCode) => {
    activeLanguage.value = language;
    currentLanguage.value = language;
};

const languageLabel = computed(() => {
    const language = languageOptions.find(
        (item) => item.code === activeLanguage.value,
    );

    return language?.label ?? "Bahasa Indonesia";
});

/* ==========================================================================
 * FILTER
 * ========================================================================== */

const search = ref(props.filters.search ?? "");
const kategori = ref(props.filters.kategori || "all");

const status = ref(
    props.filters.status === ""
        ? "all"
        : props.filters.status === "1" || props.filters.status === "true"
          ? "1"
          : props.filters.status === "0" || props.filters.status === "false"
            ? "0"
            : "all",
);

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const applyFilter = () => {
    router.get(
        "/admin/pusat-informasi/galeri",
        {
            search: search.value.trim() || undefined,
            kategori: kategori.value !== "all" ? kategori.value : undefined,
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

watch(kategori, () => {
    applyFilter();
});

watch(status, () => {
    applyFilter();
});

const resetFilter = () => {
    clearTimeout(searchTimeout);

    search.value = "";
    kategori.value = "all";
    status.value = "all";

    router.get(
        "/admin/pusat-informasi/galeri",
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
        kategori.value !== "all" ||
        status.value !== "all"
    );
});

/* ==========================================================================
 * PAGINATION
 * ========================================================================== */

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

/* ==========================================================================
 * MODAL STATE
 * ========================================================================== */

const showModal = ref(false);
const showDetail = ref(false);
const showDelete = ref(false);

const modalMode = ref<"create" | "edit">("create");

const selectedGaleri = ref<Galeri | null>(null);

/* ==========================================================================
 * FORM
 * ========================================================================== */

const emptyForm = () => ({
    judul_id: "",
    deskripsi_id: "",
    alt_text_id: "",

    judul_en: "",
    deskripsi_en: "",
    alt_text_en: "",

    judul_zh: "",
    deskripsi_zh: "",
    alt_text_zh: "",

    slug: "",
    kategori: "",
    tanggal: "",
    status: true,
    urutan: 0,
});

type FormState = ReturnType<typeof emptyForm>;

const form = ref<FormState>(emptyForm());

const gambarFile = ref<File | null>(null);
const gambarPreview = ref<string | null>(null);

const processingForm = ref(false);
const processingDelete = ref(false);

/* ==========================================================================
 * PROCESSING STATE
 * ========================================================================== */

const togglingStatusId = ref<number | null>(null);

const movingId = ref<number | null>(null);

const movingDirection = ref<"up" | "down" | null>(null);

/* ==========================================================================
 * HELPERS
 * ========================================================================== */

const getImageUrl = (gambar: string | null) => {
    if (!gambar) {
        return null;
    }

    if (
        gambar.startsWith("http://") ||
        gambar.startsWith("https://") ||
        gambar.startsWith("/")
    ) {
        return gambar;
    }

    return `/storage/${gambar}`;
};

const truncate = (text: string | null, length = 90) => {
    if (!text) {
        return "-";
    }

    return text.length > length ? `${text.substring(0, length)}...` : text;
};

const formatDate = (value: string | null) => {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "-";
    }

    const locale =
        activeLanguage.value === "en"
            ? "en-US"
            : activeLanguage.value === "zh"
              ? "zh-CN"
              : "id-ID";

    return new Intl.DateTimeFormat(locale, {
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

    const locale =
        activeLanguage.value === "en"
            ? "en-US"
            : activeLanguage.value === "zh"
              ? "zh-CN"
              : "id-ID";

    return new Intl.DateTimeFormat(locale, {
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

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "";
    }

    const pad = (number: number) => String(number).padStart(2, "0");

    return `${date.getFullYear()}-${pad(
        date.getMonth() + 1,
    )}-${pad(date.getDate())}`;
};

const statusLabel = (value: boolean) => {
    if (activeLanguage.value === "en") {
        return value ? "Active" : "Inactive";
    }

    if (activeLanguage.value === "zh") {
        return value ? "启用" : "停用";
    }

    return value ? "Aktif" : "Nonaktif";
};

const statusClass = (value: boolean) => {
    return value
        ? "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400"
        : "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400";
};

const statusDotClass = (value: boolean) => {
    return value ? "bg-emerald-500" : "bg-slate-400";
};

const revokeGambarPreview = () => {
    if (gambarPreview.value?.startsWith("blob:")) {
        URL.revokeObjectURL(gambarPreview.value);
    }

    gambarPreview.value = null;
};

/* ==========================================================================
 * SLUG
 * ========================================================================== */

const slugManuallyEdited = ref(false);

const slugify = (value: string) => {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-");
};

const handleJudulInput = () => {
    if (!slugManuallyEdited.value) {
        form.value.slug = slugify(form.value.judul_id);
    }
};
const handleSlugInput = () => {
    slugManuallyEdited.value = true;
    form.value.slug = slugify(form.value.slug);
};

/* ==========================================================================
 * MODAL ACTIONS
 * ========================================================================== */

const resetFormState = () => {
    showModal.value = false;

    revokeGambarPreview();

    form.value = emptyForm();

    gambarFile.value = null;

    selectedGaleri.value = null;

    slugManuallyEdited.value = false;

    activeLanguage.value = "id";
    currentLanguage.value = "id";
};

const openCreate = () => {
    revokeGambarPreview();

    form.value = emptyForm();

    selectedGaleri.value = null;

    gambarFile.value = null;

    slugManuallyEdited.value = false;

    modalMode.value = "create";

    activeLanguage.value = "id";
    currentLanguage.value = "id";

    showDetail.value = false;
    showDelete.value = false;

    showModal.value = true;
};

const openEdit = (galeri: Galeri) => {
    revokeGambarPreview();

    selectedGaleri.value = galeri;

    form.value = {
        judul_id: galeri.judul_id ?? "",
        deskripsi_id: galeri.deskripsi_id ?? "",
        alt_text_id: galeri.alt_text_id ?? "",

        judul_en: galeri.judul_en ?? "",
        deskripsi_en: galeri.deskripsi_en ?? "",
        alt_text_en: galeri.alt_text_en ?? "",

        judul_zh: galeri.judul_zh ?? "",
        deskripsi_zh: galeri.deskripsi_zh ?? "",
        alt_text_zh: galeri.alt_text_zh ?? "",

        slug: galeri.slug ?? "",
        kategori: galeri.kategori ?? "",
        tanggal: formatDateInput(galeri.tanggal),
        status: Boolean(galeri.status),
        urutan: Number(galeri.urutan ?? 0),
    };

    gambarFile.value = null;

    slugManuallyEdited.value = true;

    modalMode.value = "edit";

    activeLanguage.value = "id";
    currentLanguage.value = "id";

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

const openDetail = (galeri: Galeri) => {
    selectedGaleri.value = galeri;

    showModal.value = false;
    showDelete.value = false;

    showDetail.value = true;
};

const closeDetail = () => {
    showDetail.value = false;
    selectedGaleri.value = null;
};

const openDelete = (galeri: Galeri) => {
    selectedGaleri.value = galeri;

    showModal.value = false;
    showDetail.value = false;

    showDelete.value = true;
};

const closeDelete = () => {
    if (processingDelete.value) {
        return;
    }

    showDelete.value = false;
    selectedGaleri.value = null;
};

/* ==========================================================================
 * IMAGE UPLOAD
 * ========================================================================== */

const handleGambarChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    const file = target.files?.[0] ?? null;

    revokeGambarPreview();

    if (!file) {
        gambarFile.value = null;
        return;
    }

    const allowedTypes = ["image/png", "image/jpeg", "image/webp"];

    const maxSize = 2 * 1024 * 1024;

    if (!allowedTypes.includes(file.type)) {
        gambarFile.value = null;
        target.value = "";

        toast.error("Format gambar harus PNG, JPG, JPEG, atau WEBP.");

        return;
    }

    if (file.size > maxSize) {
        gambarFile.value = null;
        target.value = "";

        toast.error("Ukuran gambar maksimal 2 MB.");

        return;
    }

    gambarFile.value = file;

    gambarPreview.value = URL.createObjectURL(file);
};

/* ==========================================================================
 * SUBMIT
 * ========================================================================== */

const submitForm = () => {
    if (processingForm.value) {
        return;
    }

    const judul = form.value.judul_id.trim();

    if (!judul) {
        activeLanguage.value = "id";

        toast.error("Judul galeri Bahasa Indonesia wajib diisi.");

        return;
    }

    if (!gambarFile.value && modalMode.value === "create") {
        toast.error("Gambar galeri wajib dipilih.");

        return;
    }

    if (gambarFile.value && gambarFile.value.size > 2 * 1024 * 1024) {
        toast.error("Ukuran gambar maksimal 2 MB.");

        return;
    }

    const data = new FormData();

    /*
     * Indonesia
     */
    data.append("judul_id", form.value.judul_id.trim());
    data.append("deskripsi_id", form.value.deskripsi_id.trim());
    data.append(
        "alt_text_id",
        form.value.alt_text_id.trim() || form.value.judul_id.trim(),
    );

    /*
     * English
     */
    data.append("judul_en", form.value.judul_en.trim());
    data.append("deskripsi_en", form.value.deskripsi_en.trim());
    data.append(
        "alt_text_en",
        form.value.alt_text_en.trim() || form.value.judul_en.trim(),
    );

    /*
     * Mandarin
     */
    data.append("judul_zh", form.value.judul_zh.trim());
    data.append("deskripsi_zh", form.value.deskripsi_zh.trim());
    data.append(
        "alt_text_zh",
        form.value.alt_text_zh.trim() || form.value.judul_zh.trim(),
    );

    /*
     * Other fields
     */
    data.append("slug", form.value.slug.trim());

    data.append("kategori", form.value.kategori.trim());

    if (form.value.tanggal) {
        data.append("tanggal", form.value.tanggal);
    }

    data.append("status", form.value.status ? "1" : "0");

    data.append("urutan", String(Math.max(0, Number(form.value.urutan) || 0)));

    if (gambarFile.value) {
        data.append("gambar", gambarFile.value);
    }

    processingForm.value = true;

    if (modalMode.value === "create") {
        router.post("/admin/pusat-informasi/galeri", data, {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                resetFormState();
            },

            onError: (errors) => {
                console.error("Gagal menambahkan galeri:", errors);

                const firstError = Object.values(errors)[0];

                if (firstError) {
                    toast.error(
                        Array.isArray(firstError)
                            ? firstError[0]
                            : String(firstError),
                    );
                }
            },

            onFinish: () => {
                processingForm.value = false;
            },
        });

        return;
    }

    if (!selectedGaleri.value) {
        processingForm.value = false;

        return;
    }

    data.append("_method", "PUT");

    router.post(
        `/admin/pusat-informasi/galeri/${selectedGaleri.value.id}`,
        data,
        {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                resetFormState();
            },

            onError: (errors) => {
                console.error("Gagal memperbarui galeri:", errors);

                const firstError = Object.values(errors)[0];

                if (firstError) {
                    toast.error(
                        Array.isArray(firstError)
                            ? firstError[0]
                            : String(firstError),
                    );
                }
            },

            onFinish: () => {
                processingForm.value = false;
            },
        },
    );
};

/* ==========================================================================
 * DELETE
 * ========================================================================== */

const deleteGaleri = () => {
    if (!selectedGaleri.value || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(`/admin/pusat-informasi/galeri/${selectedGaleri.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDelete.value = false;

            selectedGaleri.value = null;
        },

        onError: (errors) => {
            console.error("Gagal menghapus galeri:", errors);

            const firstError = Object.values(errors)[0];

            if (firstError) {
                toast.error(
                    Array.isArray(firstError)
                        ? firstError[0]
                        : String(firstError),
                );
            }
        },

        onFinish: () => {
            processingDelete.value = false;
        },
    });
};

/* ==========================================================================
 * TOGGLE STATUS
 * ========================================================================== */

const toggleStatus = (galeri: Galeri) => {
    if (togglingStatusId.value !== null) {
        return;
    }

    togglingStatusId.value = galeri.id;

    router.patch(
        `/admin/pusat-informasi/galeri/${galeri.id}/toggle-aktif`,
        {},
        {
            preserveScroll: true,

            onError: (errors) => {
                console.error("Gagal mengubah status galeri:", errors);

                const firstError = Object.values(errors)[0];

                if (firstError) {
                    toast.error(
                        Array.isArray(firstError)
                            ? firstError[0]
                            : String(firstError),
                    );
                }
            },

            onFinish: () => {
                togglingStatusId.value = null;
            },
        },
    );
};

/* ==========================================================================
 * MOVE ORDER
 * ========================================================================== */

const moveGaleri = (galeri: Galeri, direction: "up" | "down") => {
    if (movingId.value !== null) {
        return;
    }

    movingId.value = galeri.id;
    movingDirection.value = direction;

    router.patch(
        `/admin/pusat-informasi/galeri/${galeri.id}/move`,
        {
            direction,
        },
        {
            preserveScroll: true,

            onError: (errors) => {
                console.error("Gagal mengubah urutan galeri:", errors);

                const firstError = Object.values(errors)[0];

                if (firstError) {
                    toast.error(
                        Array.isArray(firstError)
                            ? firstError[0]
                            : String(firstError),
                    );
                }
            },

            onFinish: () => {
                movingId.value = null;
                movingDirection.value = null;
            },
        },
    );
};

/* ==========================================================================
 * CLEANUP
 * ========================================================================== */

onBeforeUnmount(() => {
    clearTimeout(searchTimeout);

    revokeGambarPreview();
});
</script>

<template>
    <div
        class="relative min-h-full overflow-hidden bg-slate-50/50 transition-colors duration-300 dark:bg-slate-950/50"
    >
        <!-- =================================================================
             BACKGROUND DECORATION
        ================================================================== -->

        <div
            class="pointer-events-none absolute inset-x-0 top-0 z-0 h-96 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="blob-shape absolute -left-24 -top-32 size-96 rounded-full bg-gradient-to-br from-blue-400/30 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15"
            ></div>

            <div
                class="blob-shape-delayed absolute -right-20 top-4 size-80 rounded-full bg-gradient-to-tr from-sky-300/30 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10"
            ></div>

            <div
                class="blob-shape-slow absolute left-1/3 -top-40 size-72 rounded-full bg-gradient-to-br from-indigo-300/20 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/10"
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

        <!-- =================================================================
             MAIN
        ================================================================== -->

        <div
            class="relative z-10 mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <!-- =============================================================
                 HEADER
            ============================================================== -->

            <div
                class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <Images class="size-5" />
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
                            Galeri
                        </h1>

                        <p
                            class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Kelola dokumentasi foto dan kegiatan perusahaan.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <!-- PAGE LANGUAGE -->

                    <div
                        class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white/90 p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900/90"
                    >
                        <button
                            v-for="language in languageOptions"
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
                            <span>{{ language.flag }}</span>

                            <span>{{ language.short }}</span>
                        </button>
                    </div>

                    <!-- ADD -->

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                        @click="openCreate"
                    >
                        <Plus class="size-4" />
                        Tambah Galeri
                    </button>
                </div>
            </div>

            <!-- =============================================================
                 ACTIVE LANGUAGE INFO
            ============================================================== -->

            <div
                class="mb-5 flex items-center gap-2 rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-3 text-sm text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/20 dark:text-blue-300"
            >
                <span
                    class="flex size-7 items-center justify-center rounded-lg bg-white/80 dark:bg-slate-900/60"
                >
                    {{
                        languageOptions.find(
                            (item) => item.code === activeLanguage,
                        )?.flag
                    }}
                </span>

                <span>
                    Tampilan bahasa:
                    <strong>{{ languageLabel }}</strong>
                </span>
            </div>

            <!-- =============================================================
                 FILTER CARD
            ============================================================== -->

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
                            Temukan data galeri dengan cepat.
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

                <div
                    class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_180px]"
                >
                    <!-- SEARCH -->

                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari judul, kategori, atau deskripsi..."
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900"
                        />
                    </div>

                    <!-- CATEGORY -->

                    <select
                        v-model="kategori"
                        class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="all">Semua Kategori</option>

                        <option
                            v-for="item in kategoriOptions"
                            :key="item"
                            :value="item"
                        >
                            {{ item }}
                        </option>
                    </select>

                    <!-- STATUS -->

                    <select
                        v-model="status"
                        class="h-11 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="all">Semua Status</option>

                        <option value="1">Aktif</option>

                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- =============================================================
                 TABLE CARD
            ============================================================== -->

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
                            Data Galeri
                        </h2>

                        <p
                            class="mt-0.5 text-xs text-slate-400 dark:text-slate-500"
                        >
                            {{ galeris.total }} data tersedia
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
                                    Galeri
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Kategori
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Tanggal
                                </th>

                                <th
                                    class="px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Urutan
                                </th>

                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="galeri in galeris.data"
                                :key="galeri.id"
                                class="transition-colors duration-200 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                            >
                                <!-- GALERI -->

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:ring-blue-900/40"
                                        >
                                            <img
                                                v-if="galeri.gambar"
                                                :src="
                                                    getImageUrl(galeri.gambar)!
                                                "
                                                :alt="
                                                    localizedGaleriAltText(
                                                        galeri,
                                                    ) ||
                                                    localizedGaleriValue(
                                                        galeri,
                                                        'judul',
                                                    )
                                                "
                                                class="size-full object-cover transition duration-300 hover:scale-105"
                                                loading="lazy"
                                            />

                                            <ImageIcon v-else class="size-6" />
                                        </div>

                                        <div class="min-w-0 max-w-lg">
                                            <p
                                                class="truncate font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{
                                                    localizedGaleriValue(
                                                        galeri,
                                                        "judul",
                                                    )
                                                }}
                                            </p>

                                            <p
                                                class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{
                                                    truncate(
                                                        localizedGaleriValue(
                                                            galeri,
                                                            "deskripsi",
                                                        ),
                                                        100,
                                                    )
                                                }}
                                            </p>

                                            <p
                                                class="mt-1 truncate text-[11px] text-slate-400 dark:text-slate-500"
                                            >
                                                /{{ galeri.slug }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- KATEGORI -->

                                <td class="px-6 py-4">
                                    <span
                                        v-if="galeri.kategori"
                                        class="inline-flex rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        {{ galeri.kategori }}
                                    </span>

                                    <span v-else class="text-slate-400">
                                        -
                                    </span>
                                </td>

                                <!-- DATE -->

                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center gap-2 text-slate-600 dark:text-slate-300"
                                    >
                                        <Calendar
                                            class="size-4 text-slate-400"
                                        />

                                        <span class="whitespace-nowrap">
                                            {{ formatDate(galeri.tanggal) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- STATUS -->

                                <td class="px-6 py-4">
                                    <button
                                        type="button"
                                        :disabled="
                                            togglingStatusId === galeri.id
                                        "
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition hover:opacity-80 disabled:cursor-wait disabled:opacity-60"
                                        :class="statusClass(galeri.status)"
                                        @click="toggleStatus(galeri)"
                                    >
                                        <span
                                            v-if="
                                                togglingStatusId === galeri.id
                                            "
                                            class="size-3 animate-spin rounded-full border border-current/30 border-t-current"
                                        ></span>

                                        <span
                                            v-else
                                            class="size-1.5 rounded-full"
                                            :class="
                                                statusDotClass(galeri.status)
                                            "
                                        ></span>

                                        {{
                                            togglingStatusId === galeri.id
                                                ? "Memproses..."
                                                : statusLabel(galeri.status)
                                        }}
                                    </button>
                                </td>

                                <!-- ORDER -->

                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-center gap-1"
                                    >
                                        <button
                                            type="button"
                                            :disabled="movingId === galeri.id"
                                            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Pindah ke atas"
                                            @click="moveGaleri(galeri, 'up')"
                                        >
                                            <span
                                                v-if="
                                                    movingId === galeri.id &&
                                                    movingDirection === 'up'
                                                "
                                                class="block size-4 animate-spin rounded-full border-2 border-slate-300 border-t-blue-500"
                                            ></span>

                                            <ArrowUp v-else class="size-4" />
                                        </button>

                                        <span
                                            class="min-w-8 text-center text-xs font-semibold text-slate-500 dark:text-slate-400"
                                        >
                                            {{ galeri.urutan }}
                                        </span>

                                        <button
                                            type="button"
                                            :disabled="movingId === galeri.id"
                                            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Pindah ke bawah"
                                            @click="moveGaleri(galeri, 'down')"
                                        >
                                            <span
                                                v-if="
                                                    movingId === galeri.id &&
                                                    movingDirection === 'down'
                                                "
                                                class="block size-4 animate-spin rounded-full border-2 border-slate-300 border-t-blue-500"
                                            ></span>

                                            <ArrowDown v-else class="size-4" />
                                        </button>
                                    </div>
                                </td>

                                <!-- ACTION -->

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-1">
                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Lihat detail"
                                            @click="openDetail(galeri)"
                                        >
                                            <Eye class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                            title="Edit galeri"
                                            @click="openEdit(galeri)"
                                        >
                                            <Pencil class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                            title="Hapus galeri"
                                            @click="openDelete(galeri)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- EMPTY -->

                            <tr v-if="galeris.data.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600"
                                    >
                                        <Images class="size-7" />
                                    </div>

                                    <p
                                        class="mt-4 font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Data galeri tidak ditemukan
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

                <!-- =========================================================
                     PAGINATION
                ========================================================== -->

                <div
                    v-if="galeris.total > 0"
                    class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Menampilkan
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ galeris.from }}
                        </span>
                        -
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ galeris.to }}
                        </span>
                        dari
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ galeris.total }}
                        </span>
                        data
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="(link, index) in galeris.links"
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

        <!-- =================================================================
             CREATE / EDIT MODAL
        ================================================================== -->

        <Transition name="modal">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
                @click.self="closeModal"
            >
                <div
                    class="w-full max-w-4xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-800 dark:bg-slate-900"
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
                                    <Images class="size-4" />
                                </div>

                                <h2
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    {{
                                        modalMode === "create"
                                            ? "Tambah Galeri"
                                            : "Edit Galeri"
                                    }}
                                </h2>
                            </div>

                            <p
                                class="mt-1 pl-11 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Lengkapi informasi galeri dalam tiga bahasa.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-200/70 hover:text-slate-600 dark:hover:bg-slate-800"
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
                        <!-- =================================================
                             LANGUAGE TABS
                        ================================================== -->

                        <div
                            class="mb-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-1.5 dark:border-slate-800 dark:bg-slate-800/40"
                        >
                            <div class="grid grid-cols-3 gap-1">
                                <button
                                    v-for="language in languageOptions"
                                    :key="language.code"
                                    type="button"
                                    class="flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all"
                                    :class="
                                        activeLanguage === language.code
                                            ? 'bg-white text-blue-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:text-blue-400 dark:ring-slate-700'
                                            : 'text-slate-500 hover:bg-white/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-900/60 dark:hover:text-white'
                                    "
                                    @click="setLanguage(language.code)"
                                >
                                    <span>
                                        {{ language.flag }}
                                    </span>

                                    <span>
                                        {{ language.label }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- =================================================
                             LANGUAGE CONTENT
                        ================================================== -->

                        <div
                            class="rounded-2xl border border-blue-100 bg-blue-50/30 p-5 dark:border-blue-900/30 dark:bg-blue-950/10"
                        >
                            <!-- ID -->

                            <div
                                v-if="activeLanguage === 'id'"
                                class="space-y-5"
                            >
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Judul
                                        <span class="text-red-500"> * </span>
                                    </label>

                                    <input
                                        v-model="form.judul_id"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Masukkan judul galeri"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                        @input="handleJudulInput"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Deskripsi
                                    </label>

                                    <textarea
                                        v-model="form.deskripsi_id"
                                        rows="5"
                                        placeholder="Masukkan deskripsi galeri"
                                        class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    ></textarea>
                                </div>
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Alt Text
                                    </label>
                                    <input
                                        v-model="form.alt_text_id"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Deskripsi singkat gambar dalam Bahasa Indonesia"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>

                            <!-- EN -->

                            <div
                                v-else-if="activeLanguage === 'en'"
                                class="space-y-5"
                            >
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Title
                                    </label>

                                    <input
                                        v-model="form.judul_en"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Enter gallery title in English"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Description
                                    </label>

                                    <textarea
                                        v-model="form.deskripsi_en"
                                        rows="5"
                                        placeholder="Enter gallery description in English"
                                        class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    ></textarea>
                                </div>
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Alt Text
                                    </label>
                                    <input
                                        v-model="form.alt_text_en"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Short image description in English"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>

                            <!-- ZH -->

                            <div v-else class="space-y-5">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        标题
                                    </label>

                                    <input
                                        v-model="form.judul_zh"
                                        type="text"
                                        maxlength="255"
                                        placeholder="请输入图库标题"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        描述
                                    </label>

                                    <textarea
                                        v-model="form.deskripsi_zh"
                                        rows="5"
                                        placeholder="请输入图库描述"
                                        class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    ></textarea>
                                </div>
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        图片替代文本
                                    </label>
                                    <input
                                        v-model="form.alt_text_zh"
                                        type="text"
                                        maxlength="255"
                                        placeholder="请输入图片简短描述"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- =================================================
                             GENERAL INFORMATION
                        ================================================== -->

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            <!-- SLUG -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Slug
                                </label>

                                <input
                                    v-model="form.slug"
                                    type="text"
                                    placeholder="slug-galeri"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    @input="handleSlugInput"
                                />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Slug dibuat otomatis dari judul Bahasa
                                    Indonesia.
                                </p>
                            </div>

                            <!-- CATEGORY -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Kategori
                                </label>

                                <input
                                    v-model="form.kategori"
                                    type="text"
                                    maxlength="100"
                                    placeholder="Contoh: Kegiatan, Infrastruktur, Investasi"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                />
                            </div>
                            <!-- DATE -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Tanggal
                                </label>

                                <input
                                    v-model="form.tanggal"
                                    type="date"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                />
                            </div>

                            <!-- ORDER -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Urutan
                                </label>

                                <input
                                    v-model.number="form.urutan"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                />
                            </div>

                            <!-- STATUS -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Status
                                </label>

                                <label
                                    class="flex min-h-12 cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Galeri aktif
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            Tampilkan galeri pada halaman
                                            publik.
                                        </p>
                                    </div>

                                    <input
                                        v-model="form.status"
                                        type="checkbox"
                                        class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700"
                                    />
                                </label>
                            </div>
                        </div>

                        <!-- =================================================
                             IMAGE
                        ================================================== -->

                        <div class="mt-6">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                Gambar Galeri
                                <span
                                    v-if="modalMode === 'create'"
                                    class="text-red-500"
                                >
                                    *
                                </span>
                            </label>

                            <label
                                class="group flex min-h-44 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/70 p-6 text-center transition hover:border-blue-400 hover:bg-blue-50/50 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-blue-700 dark:hover:bg-blue-950/20"
                            >
                                <input
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp"
                                    class="hidden"
                                    @change="handleGambarChange"
                                />

                                <div
                                    class="flex size-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 transition group-hover:scale-105 dark:bg-blue-950/50 dark:text-blue-400"
                                >
                                    <Upload class="size-5" />
                                </div>

                                <p
                                    class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Klik untuk memilih gambar
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    PNG, JPG, JPEG, atau WEBP · Maksimal 2 MB
                                </p>
                            </label>

                            <!-- NEW IMAGE -->

                            <div
                                v-if="gambarPreview"
                                class="mt-4 flex items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50/60 p-3 dark:border-blue-900/40 dark:bg-blue-950/20"
                            >
                                <img
                                    :src="gambarPreview"
                                    alt="Preview gambar baru"
                                    class="h-24 w-36 rounded-xl border border-slate-200 bg-white object-cover dark:border-slate-700"
                                />

                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Preview Gambar Baru
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Gambar baru akan digunakan setelah
                                        disimpan.
                                    </p>
                                </div>
                            </div>

                            <!-- CURRENT IMAGE -->

                            <div
                                v-else-if="
                                    modalMode === 'edit' &&
                                    selectedGaleri?.gambar
                                "
                                class="mt-4 flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800"
                            >
                                <img
                                    :src="getImageUrl(selectedGaleri.gambar)!"
                                    :alt="
                                        localizedGaleriAltText(
                                            selectedGaleri,
                                        ) ||
                                        localizedGaleriValue(
                                            selectedGaleri,
                                            'judul',
                                        )
                                    "
                                    class="h-24 w-36 rounded-xl border border-slate-200 bg-white object-cover dark:border-slate-700"
                                />

                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Gambar Saat Ini
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Pilih file baru jika ingin mengganti
                                        gambar.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- =================================================
                             FOOTER
                        ================================================== -->

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
                                    (modalMode === 'create' && !gambarFile)
                                "
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span
                                    v-if="processingForm"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                {{
                                    processingForm
                                        ? "Menyimpan..."
                                        : modalMode === "create"
                                          ? "Simpan Galeri"
                                          : "Simpan Perubahan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- =================================================================
             DETAIL MODAL
        ================================================================== -->

        <Transition name="modal">
            <div
                v-if="showDetail && selectedGaleri"
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
                                Detail Galeri
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap dokumentasi galeri.
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

                    <!-- DETAIL LANGUAGE -->

                    <div class="px-6 pt-5">
                        <div
                            class="inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-800"
                        >
                            <button
                                v-for="language in languageOptions"
                                :key="language.code"
                                type="button"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
                                :class="
                                    activeLanguage === language.code
                                        ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400'
                                        : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white'
                                "
                                @click="setLanguage(language.code)"
                            >
                                {{ language.flag }}
                                {{ language.short }}
                            </button>
                        </div>
                    </div>

                    <!-- CONTENT -->

                    <div class="max-h-[75vh] space-y-6 overflow-y-auto p-6">
                        <!-- IMAGE -->

                        <div
                            v-if="selectedGaleri.gambar"
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800"
                        >
                            <img
                                :src="getImageUrl(selectedGaleri.gambar)!"
                                :alt="
                                    localizedGaleriAltText(selectedGaleri) ||
                                    localizedGaleriValue(
                                        selectedGaleri,
                                        'judul',
                                    )
                                "
                                class="max-h-[420px] w-full object-contain"
                            />
                        </div>

                        <!-- TITLE -->

                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    v-if="selectedGaleri.kategori"
                                    class="inline-flex rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    {{ selectedGaleri.kategori }}
                                </span>

                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="statusClass(selectedGaleri.status)"
                                >
                                    <span
                                        class="size-1.5 rounded-full"
                                        :class="
                                            statusDotClass(
                                                selectedGaleri.status,
                                            )
                                        "
                                    ></span>

                                    {{ statusLabel(selectedGaleri.status) }}
                                </span>
                            </div>

                            <h3
                                class="mt-3 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{
                                    localizedGaleriValue(
                                        selectedGaleri,
                                        "judul",
                                    )
                                }}
                            </h3>

                            <div
                                class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                <span class="flex items-center gap-1.5">
                                    <Calendar class="size-4" />

                                    {{ formatDate(selectedGaleri.tanggal) }}
                                </span>

                                <span>
                                    Urutan:
                                    <strong
                                        class="font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        {{ selectedGaleri.urutan }}
                                    </strong>
                                </span>
                            </div>
                        </div>

                        <!-- DESCRIPTION -->

                        <div
                            v-if="
                                localizedGaleriValue(
                                    selectedGaleri,
                                    'deskripsi',
                                )
                            "
                            class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5 dark:border-blue-900/40 dark:bg-blue-950/20"
                        >
                            <p
                                class="text-sm leading-7 text-slate-600 dark:text-slate-300"
                            >
                                {{
                                    localizedGaleriValue(
                                        selectedGaleri,
                                        "deskripsi",
                                    )
                                }}
                            </p>
                        </div>

                        <!-- INFORMATION -->

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
                                    {{ selectedGaleri.slug }}
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                >
                                    Alt Text
                                </p>

                                <p
                                    class="mt-2 text-sm text-slate-700 dark:text-slate-200"
                                >
                                    {{
                                        localizedGaleriAltText(
                                            selectedGaleri,
                                        ) || "-"
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- TIMESTAMPS -->

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
                                            selectedGaleri.created_at,
                                        )
                                    }}
                                </p>

                                <p>
                                    Diperbarui:
                                    {{
                                        formatDateTime(
                                            selectedGaleri.updated_at,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- =================================================================
             DELETE MODAL
        ================================================================== -->

        <Transition name="modal">
            <div
                v-if="showDelete && selectedGaleri"
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
                            Hapus Galeri?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus

                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{
                                    localizedGaleriValue(
                                        selectedGaleri,
                                        "judul",
                                    )
                                }}
                            </span>

                            ?

                            <br />

                            Data dan gambar yang terkait akan dihapus.
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
                            @click="deleteGaleri"
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
 * MODAL
 * ========================================================================== */

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
 * BLOBS
 * ========================================================================== */

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

    50% {
        transform: translate3d(20px, 12px, 0) scale(1.05);
    }
}

@keyframes blob-float-delayed {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(-18px, 16px, 0) scale(1.04);
    }
}

@keyframes blob-float-slow {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(12px, 18px, 0) scale(1.06);
    }
}

/* ==========================================================================
 * REDUCED MOTION
 * ========================================================================== */

@media (prefers-reduced-motion: reduce) {
    .blob-shape,
    .blob-shape-delayed,
    .blob-shape-slow {
        animation: none;
    }

    .modal-enter-active,
    .modal-leave-active {
        transition: opacity 0.1s ease;
    }
}
</style>
