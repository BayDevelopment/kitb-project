<script setup lang="ts">
import {
    ArrowDown,
    ArrowLeft,
    ArrowUp,
    Building2,
    Check,
    ChevronLeft,
    ChevronRight,
    ExternalLink,
    Eye,
    ImagePlus,
    LoaderCircle,
    Pencil,
    Plus,
    Search,
    Trash2,
    Upload,
    X,
} from "lucide-vue-next";
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

/* -------------------------------------------------------------------------- */
/* Types                                                                      */
/* -------------------------------------------------------------------------- */

interface MitraPerusahaan {
    id: number;
    nama_perusahaan: string;
    nama_perusahaan_en: string | null;
    nama_perusahaan_zh: string | null;
    slug: string;
    logo: string | null;
    website: string | null;
    aktif: boolean;
    urutan: number;
    created_at?: string;
    updated_at?: string;
}

interface LinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: LinkItem[];
}

interface Props {
    mitraPerusahaans: Paginator<MitraPerusahaan>;
}

const props = defineProps<Props>();

/* -------------------------------------------------------------------------- */
/* Page Loading                                                               */
/* -------------------------------------------------------------------------- */

const isPageLoading = ref(true);

let initialLoadingTimer: ReturnType<typeof setTimeout> | null = null;
let removeRouterStartListener: (() => void) | null = null;
let removeRouterFinishListener: (() => void) | null = null;

/* -------------------------------------------------------------------------- */
/* State                                                                      */
/* -------------------------------------------------------------------------- */

const search = ref("");
const statusFilter = ref<"all" | "active" | "inactive">("all");
const activeLanguage = ref<"id" | "en" | "zh">("id");

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);

const selectedMitra = ref<MitraPerusahaan | null>(null);

const isSubmitting = ref(false);
const isDeleting = ref(false);
const processingId = ref<number | null>(null);
const processingMoveId = ref<number | null>(null);

const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);
const objectUrl = ref<string | null>(null);

/* -------------------------------------------------------------------------- */
/* Form                                                                       */
/* -------------------------------------------------------------------------- */

const form = useForm({
    nama_perusahaan: "",
    nama_perusahaan_en: "",
    nama_perusahaan_zh: "",
    website: "",
    logo: null as File | null,
    aktif: true,
});

/* -------------------------------------------------------------------------- */
/* Computed                                                                   */
/* -------------------------------------------------------------------------- */

const displayedData = computed(() => {
    let data = props.mitraPerusahaans.data ?? [];

    const keyword = search.value.trim().toLowerCase();

    if (keyword) {
        data = data.filter((item) => {
            return (
                item.nama_perusahaan.toLowerCase().includes(keyword) ||
                (item.nama_perusahaan_en ?? "")
                    .toLowerCase()
                    .includes(keyword) ||
                (item.nama_perusahaan_zh ?? "")
                    .toLowerCase()
                    .includes(keyword) ||
                item.slug.toLowerCase().includes(keyword) ||
                (item.website ?? "").toLowerCase().includes(keyword)
            );
        });
    }

    if (statusFilter.value === "active") {
        data = data.filter((item) => item.aktif);
    }

    if (statusFilter.value === "inactive") {
        data = data.filter((item) => !item.aktif);
    }

    return data;
});

const activeCount = computed(() => {
    return props.mitraPerusahaans.data.filter((item) => item.aktif).length;
});

const inactiveCount = computed(() => {
    return props.mitraPerusahaans.data.filter((item) => !item.aktif).length;
});

const hasSearchOrFilter = computed(() => {
    return search.value.trim().length > 0 || statusFilter.value !== "all";
});

const existingLogoUrl = computed(() => {
    const logo = selectedMitra.value?.logo;

    if (!logo) {
        return null;
    }

    if (
        logo.startsWith("http://") ||
        logo.startsWith("https://") ||
        logo.startsWith("/")
    ) {
        return logo;
    }

    return `/storage/${logo}`;
});

const displayedLogo = computed(() => {
    return previewUrl.value || existingLogoUrl.value;
});

const hasExistingLogo = computed(() => {
    return !!existingLogoUrl.value;
});

const modalTitle = computed(() => {
    return showEditModal.value
        ? "Edit Mitra Perusahaan"
        : "Tambah Mitra Perusahaan";
});

/* -------------------------------------------------------------------------- */
/* Helpers                                                                    */
/* -------------------------------------------------------------------------- */

const resetForm = () => {
    form.reset();
    form.clearErrors();

    form.nama_perusahaan = "";
    form.nama_perusahaan_en = "";
    form.nama_perusahaan_zh = "";
    activeLanguage.value = "id";
    form.website = "";
    form.logo = null;
    form.aktif = true;
};

const revokeObjectUrl = () => {
    if (objectUrl.value) {
        URL.revokeObjectURL(objectUrl.value);
        objectUrl.value = null;
    }
};

const resetPreview = () => {
    revokeObjectUrl();
    previewUrl.value = null;

    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

const closeAllModals = () => {
    if (isSubmitting.value || isDeleting.value) {
        return;
    }

    showCreateModal.value = false;
    showEditModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = false;

    selectedMitra.value = null;

    resetPreview();
    resetForm();
};

/**
 * Menutup modal form setelah request berhasil.
 *
 * Jangan menggunakan closeAllModals() di onSuccess karena
 * isSubmitting masih true pada saat callback tersebut dipanggil.
 */
const closeFormModalAfterSuccess = () => {
    showCreateModal.value = false;
    showEditModal.value = false;

    selectedMitra.value = null;

    resetPreview();
    resetForm();
};

const openCreateModal = () => {
    selectedMitra.value = null;

    resetPreview();
    resetForm();

    showCreateModal.value = true;
};

const openEditModal = (mitra: MitraPerusahaan) => {
    selectedMitra.value = mitra;

    resetPreview();

    form.clearErrors();

    form.nama_perusahaan = mitra.nama_perusahaan;
    form.nama_perusahaan_en = mitra.nama_perusahaan_en ?? "";
    form.nama_perusahaan_zh = mitra.nama_perusahaan_zh ?? "";
    activeLanguage.value = "id";
    form.website = mitra.website ?? "";
    form.logo = null;
    form.aktif = mitra.aktif;

    showEditModal.value = true;
};

const openDetailModal = (mitra: MitraPerusahaan) => {
    selectedMitra.value = mitra;
    showDetailModal.value = true;
};

const openDeleteModal = (mitra: MitraPerusahaan) => {
    selectedMitra.value = mitra;
    showDeleteModal.value = true;
};

const clearSearch = () => {
    search.value = "";
    statusFilter.value = "all";
};

/* -------------------------------------------------------------------------- */
/* Logo                                                                       */
/* -------------------------------------------------------------------------- */

const handleFile = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    if (!file) {
        return;
    }

    const maxSize = 1024 * 1024;

    if (file.size > maxSize) {
        target.value = "";
        form.logo = null;

        form.setError("logo", "Ukuran logo maksimal 1 MB.");

        return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

    if (!allowedTypes.includes(file.type)) {
        target.value = "";
        form.logo = null;

        form.setError("logo", "Format logo harus JPG, JPEG, PNG, atau WEBP.");

        return;
    }

    form.clearErrors("logo");

    revokeObjectUrl();

    form.logo = file;

    objectUrl.value = URL.createObjectURL(file);
    previewUrl.value = objectUrl.value;
};

const removeNewLogo = () => {
    form.logo = null;

    resetPreview();

    form.clearErrors("logo");
};

/* -------------------------------------------------------------------------- */
/* Submit                                                                     */
/* -------------------------------------------------------------------------- */

const submitForm = () => {
    if (isSubmitting.value) {
        return;
    }

    if (!form.nama_perusahaan.trim()) {
        form.setError("nama_perusahaan", "Nama perusahaan wajib diisi.");

        return;
    }

    const formData = new FormData();

    formData.append("nama_perusahaan", form.nama_perusahaan.trim());
    formData.append("nama_perusahaan_en", form.nama_perusahaan_en.trim());
    formData.append("nama_perusahaan_zh", form.nama_perusahaan_zh.trim());

    formData.append("website", form.website.trim());

    formData.append("aktif", form.aktif ? "1" : "0");

    if (form.logo instanceof File) {
        formData.append("logo", form.logo);
    }

    /**
     * EDIT
     */
    if (showEditModal.value && selectedMitra.value) {
        formData.append("_method", "PUT");

        router.post(
            `/admin/profil-perusahaan/mitra-perusahaan/${selectedMitra.value.id}`,
            formData,
            {
                forceFormData: true,
                preserveScroll: true,

                onStart: () => {
                    isSubmitting.value = true;
                },

                onError: (errors) => {
                    console.error(
                        "Gagal memperbarui mitra perusahaan:",
                        errors,
                    );
                },

                onSuccess: () => {
                    /**
                     * Tutup modal hanya jika request benar-benar sukses.
                     * Jangan menggunakan closeAllModals() di sini karena
                     * isSubmitting masih bernilai true.
                     */
                    closeFormModalAfterSuccess();
                },

                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );

        return;
    }

    /**
     * CREATE
     */
    router.post("/admin/profil-perusahaan/mitra-perusahaan", formData, {
        forceFormData: true,
        preserveScroll: true,

        onStart: () => {
            isSubmitting.value = true;
        },

        onError: (errors) => {
            console.error("Gagal menambahkan mitra perusahaan:", errors);
        },

        onSuccess: () => {
            /**
             * Modal hanya ditutup ketika backend mengembalikan
             * response sukses.
             */
            closeFormModalAfterSuccess();
        },

        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
/* -------------------------------------------------------------------------- */
/* Delete                                                                     */
/* -------------------------------------------------------------------------- */

const deleteMitra = () => {
    if (!selectedMitra.value || isDeleting.value) {
        return;
    }

    isDeleting.value = true;

    router.delete(
        `/admin/profil-perusahaan/mitra-perusahaan/${selectedMitra.value.id}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                showDeleteModal.value = false;
                selectedMitra.value = null;
            },

            onError: (errors) => {
                console.error("Gagal menghapus mitra perusahaan:", errors);
            },

            onFinish: () => {
                isDeleting.value = false;
            },
        },
    );
};

/* -------------------------------------------------------------------------- */
/* Toggle Aktif                                                               */
/* -------------------------------------------------------------------------- */

const toggleAktif = (mitra: MitraPerusahaan) => {
    if (processingId.value !== null) {
        return;
    }

    processingId.value = mitra.id;

    router.patch(
        `/admin/profil-perusahaan/mitra-perusahaan/${mitra.id}/toggle-aktif`,
        {},
        {
            preserveScroll: true,

            onError: (errors) => {
                console.error(
                    "Gagal mengubah status mitra perusahaan:",
                    errors,
                );
            },

            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

/* -------------------------------------------------------------------------- */
/* Move                                                                       */
/* -------------------------------------------------------------------------- */

const moveMitra = (mitra: MitraPerusahaan, direction: "up" | "down") => {
    if (processingMoveId.value !== null) {
        return;
    }

    processingMoveId.value = mitra.id;

    router.patch(
        `/admin/profil-perusahaan/mitra-perusahaan/${mitra.id}/move`,
        {
            direction,
        },
        {
            preserveScroll: true,

            onError: (errors) => {
                console.error(
                    "Gagal mengubah urutan mitra perusahaan:",
                    errors,
                );
            },

            onFinish: () => {
                processingMoveId.value = null;
            },
        },
    );
};

/* -------------------------------------------------------------------------- */
/* Pagination                                                                 */
/* -------------------------------------------------------------------------- */

const visitPage = (url: string | null) => {
    if (!url || isPageLoading.value) {
        return;
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
};

/* -------------------------------------------------------------------------- */
/* Website                                                                    */
/* -------------------------------------------------------------------------- */

const normalizeWebsite = (website: string | null) => {
    if (!website) {
        return "";
    }

    if (website.startsWith("http://") || website.startsWith("https://")) {
        return website;
    }

    return `https://${website}`;
};

/* -------------------------------------------------------------------------- */
/* Loading                                                                    */
/* -------------------------------------------------------------------------- */

onMounted(async () => {
    await nextTick();

    removeRouterStartListener = router.on("start", (event) => {
        if (!event.detail.visit.preserveState) {
            isPageLoading.value = true;
        }
    });

    removeRouterFinishListener = router.on("finish", () => {
        isPageLoading.value = false;
    });

    initialLoadingTimer = setTimeout(() => {
        isPageLoading.value = false;
    }, 500);
});

onBeforeUnmount(() => {
    removeRouterStartListener?.();
    removeRouterFinishListener?.();

    if (initialLoadingTimer) {
        clearTimeout(initialLoadingTimer);
    }

    revokeObjectUrl();
});
</script>

<template>
    <Head title="Mitra Perusahaan" />

    <div
        class="relative min-h-full overflow-hidden bg-slate-50 transition-colors duration-300 dark:bg-[#07111f]"
    >
        <!-- =============================================================== -->
        <!-- DECORATIVE BACKGROUND -->
        <!-- =============================================================== -->

        <div
            class="pointer-events-none absolute -left-24 -top-24 z-0 size-72 rounded-full bg-gradient-to-br from-blue-400/25 to-indigo-500/15 blur-3xl dark:from-blue-500/15 dark:to-indigo-600/10"
            aria-hidden="true"
        />

        <div
            class="pointer-events-none absolute -right-28 top-40 z-0 size-80 rounded-full bg-gradient-to-br from-sky-400/20 to-blue-500/10 blur-3xl dark:from-sky-500/10 dark:to-blue-600/10"
            aria-hidden="true"
        />

        <div
            class="pointer-events-none absolute -bottom-40 left-1/3 z-0 size-96 rounded-full bg-gradient-to-br from-indigo-400/10 to-cyan-400/10 blur-3xl dark:from-indigo-500/10 dark:to-cyan-500/5"
            aria-hidden="true"
        />

        <!-- GRID -->

        <div
            class="pointer-events-none absolute inset-0 z-0 opacity-[0.35] dark:opacity-[0.08]"
            aria-hidden="true"
        >
            <div
                class="absolute inset-0"
                style="
                    background-image:
                        linear-gradient(
                            rgba(100, 116, 139, 0.08) 1px,
                            transparent 1px
                        ),
                        linear-gradient(
                            90deg,
                            rgba(100, 116, 139, 0.08) 1px,
                            transparent 1px
                        );
                    background-size: 32px 32px;
                    mask-image: linear-gradient(
                        to bottom,
                        black,
                        transparent 75%
                    );
                "
            />
        </div>

        <div class="relative z-10">
            <Transition name="page-fade" mode="out-in">
                <!-- ======================================================= -->
                <!-- SKELETON -->
                <!-- ======================================================= -->

                <div
                    v-if="isPageLoading"
                    key="skeleton"
                    class="mx-auto w-full max-w-[1250px] animate-pulse space-y-5 p-4 sm:p-5 lg:p-6 xl:p-8"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="size-11 rounded-2xl bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="space-y-2">
                                <div
                                    class="h-5 w-52 rounded-lg bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-80 max-w-[70vw] rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                            </div>
                        </div>

                        <div
                            class="h-10 w-full rounded-xl bg-slate-200 sm:w-36 dark:bg-slate-800"
                        />
                    </div>

                    <div
                        class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="border-b border-slate-200 p-5 dark:border-slate-800"
                        >
                            <div class="flex flex-col gap-4 sm:flex-row">
                                <div
                                    class="h-11 flex-1 rounded-xl bg-slate-100 dark:bg-slate-800"
                                />

                                <div
                                    class="h-11 w-full rounded-xl bg-slate-100 sm:w-36 dark:bg-slate-800"
                                />
                            </div>
                        </div>

                        <div class="space-y-4 p-5">
                            <div
                                v-for="i in 5"
                                :key="i"
                                class="h-20 rounded-2xl bg-slate-100 dark:bg-slate-800"
                            />
                        </div>
                    </div>
                </div>

                <!-- ======================================================= -->
                <!-- CONTENT -->
                <!-- ======================================================= -->

                <div
                    v-else
                    key="content"
                    class="mx-auto w-full max-w-[1250px] space-y-5 p-4 sm:p-5 lg:p-6 xl:p-8"
                >
                    <!-- HEADER -->

                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-sm shadow-blue-500/20"
                            >
                                <Building2 class="size-5" />
                            </div>

                            <div class="min-w-0">
                                <h1
                                    class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                                >
                                    Mitra Perusahaan
                                </h1>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Kelola daftar perusahaan yang menjadi mitra
                                    KITB.
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-center"
                        >
                            <button
                                type="button"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:focus-visible:ring-offset-slate-950"
                                @click="router.visit('/dashboard')"
                            >
                                <ArrowLeft class="size-4" />
                                Kembali
                            </button>

                            <button
                                type="button"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-500/10 transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 sm:w-auto dark:focus-visible:ring-offset-slate-950"
                                @click="openCreateModal"
                            >
                                <Plus class="size-4" />
                                Tambah Mitra
                            </button>
                        </div>
                    </div>

                    <!-- STATS -->

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80"
                        >
                            <p class="text-xs font-medium text-slate-400">
                                Total Data
                            </p>

                            <p
                                class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white"
                            >
                                {{ props.mitraPerusahaans.total }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-emerald-200/70 bg-emerald-50/70 p-4 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20"
                        >
                            <p
                                class="text-xs font-medium text-emerald-600 dark:text-emerald-400"
                            >
                                Aktif
                            </p>

                            <p
                                class="mt-1 text-2xl font-semibold text-emerald-700 dark:text-emerald-300"
                            >
                                {{ activeCount }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80"
                        >
                            <p class="text-xs font-medium text-slate-400">
                                Tidak Aktif
                            </p>

                            <p
                                class="mt-1 text-2xl font-semibold text-slate-700 dark:text-slate-300"
                            >
                                {{ inactiveCount }}
                            </p>
                        </div>
                    </div>

                    <!-- MAIN CARD -->

                    <div
                        class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/50 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
                    >
                        <!-- CARD HEADER -->

                        <div
                            class="border-b border-slate-200 px-5 py-5 sm:px-6 dark:border-slate-800"
                        >
                            <div
                                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div>
                                    <h2
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        Daftar Mitra Perusahaan
                                    </h2>

                                    <p
                                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Atur logo, website, status, dan urutan
                                        mitra perusahaan.
                                    </p>
                                </div>

                                <!-- SEARCH -->

                                <div
                                    class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto"
                                >
                                    <div class="relative">
                                        <Search
                                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />

                                        <input
                                            v-model="search"
                                            type="search"
                                            placeholder="Cari perusahaan..."
                                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 sm:w-64 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                        />
                                    </div>

                                    <select
                                        v-model="statusFilter"
                                        class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-600 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                                    >
                                        <option value="all">
                                            Semua Status
                                        </option>

                                        <option value="active">Aktif</option>

                                        <option value="inactive">
                                            Tidak Aktif
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- TABLE -->

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[900px] text-left">
                                <thead
                                    class="border-b border-slate-200 bg-slate-50/70 dark:border-slate-800 dark:bg-slate-800/30"
                                >
                                    <tr>
                                        <th
                                            class="w-20 px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400"
                                        >
                                            Urutan
                                        </th>

                                        <th
                                            class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400"
                                        >
                                            Perusahaan
                                        </th>

                                        <th
                                            class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400"
                                        >
                                            Website
                                        </th>

                                        <th
                                            class="w-32 px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400"
                                        >
                                            Status
                                        </th>

                                        <th
                                            class="w-40 px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800"
                                >
                                    <tr
                                        v-for="mitra in displayedData"
                                        :key="mitra.id"
                                        class="group transition hover:bg-slate-50/70 dark:hover:bg-slate-800/30"
                                    >
                                        <!-- ORDER -->

                                        <td class="px-5 py-4">
                                            <div
                                                class="flex items-center gap-1"
                                            >
                                                <span
                                                    class="flex size-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                                >
                                                    {{ mitra.urutan }}
                                                </span>

                                                <div class="flex flex-col">
                                                    <button
                                                        type="button"
                                                        title="Naik"
                                                        :disabled="
                                                            processingMoveId !==
                                                            null
                                                        "
                                                        class="rounded-md p-1 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-blue-950/30 dark:hover:text-blue-400"
                                                        @click="
                                                            moveMitra(
                                                                mitra,
                                                                'up',
                                                            )
                                                        "
                                                    >
                                                        <ArrowUp
                                                            class="size-3.5"
                                                        />
                                                    </button>

                                                    <button
                                                        type="button"
                                                        title="Turun"
                                                        :disabled="
                                                            processingMoveId !==
                                                            null
                                                        "
                                                        class="rounded-md p-1 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-blue-950/30 dark:hover:text-blue-400"
                                                        @click="
                                                            moveMitra(
                                                                mitra,
                                                                'down',
                                                            )
                                                        "
                                                    >
                                                        <ArrowDown
                                                            class="size-3.5"
                                                        />
                                                    </button>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- COMPANY -->

                                        <td class="px-5 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
                                                >
                                                    <img
                                                        v-if="mitra.logo"
                                                        :src="
                                                            mitra.logo.startsWith(
                                                                'http',
                                                            ) ||
                                                            mitra.logo.startsWith(
                                                                '/',
                                                            )
                                                                ? mitra.logo
                                                                : `/storage/${mitra.logo}`
                                                        "
                                                        :alt="`Logo ${mitra.nama_perusahaan}`"
                                                        class="size-full object-contain p-1.5"
                                                    />

                                                    <Building2
                                                        v-else
                                                        class="size-5 text-slate-300 dark:text-slate-600"
                                                    />
                                                </div>

                                                <div class="min-w-0">
                                                    <p
                                                        class="truncate text-sm font-semibold text-slate-800 dark:text-slate-200"
                                                    >
                                                        {{
                                                            mitra.nama_perusahaan
                                                        }}
                                                    </p>

                                                    <p
                                                        class="mt-0.5 truncate text-xs text-slate-400"
                                                    >
                                                        /{{ mitra.slug }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- WEBSITE -->

                                        <td class="px-5 py-4">
                                            <a
                                                v-if="mitra.website"
                                                :href="
                                                    normalizeWebsite(
                                                        mitra.website,
                                                    )
                                                "
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex max-w-[260px] items-center gap-1.5 truncate text-sm text-blue-600 transition hover:text-blue-700 hover:underline dark:text-blue-400"
                                            >
                                                <span class="truncate">
                                                    {{ mitra.website }}
                                                </span>

                                                <ExternalLink
                                                    class="size-3.5 shrink-0"
                                                />
                                            </a>

                                            <span
                                                v-else
                                                class="text-sm text-slate-400"
                                            >
                                                —
                                            </span>
                                        </td>

                                        <!-- STATUS -->

                                        <td class="px-5 py-4">
                                            <button
                                                type="button"
                                                role="switch"
                                                :aria-checked="mitra.aktif"
                                                :disabled="
                                                    processingId === mitra.id
                                                "
                                                class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                                                :class="
                                                    mitra.aktif
                                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                                "
                                                @click="toggleAktif(mitra)"
                                            >
                                                <span
                                                    class="size-1.5 rounded-full"
                                                    :class="
                                                        mitra.aktif
                                                            ? 'bg-emerald-500'
                                                            : 'bg-slate-400'
                                                    "
                                                />

                                                {{
                                                    mitra.aktif
                                                        ? "Aktif"
                                                        : "Tidak Aktif"
                                                }}
                                            </button>
                                        </td>

                                        <!-- ACTION -->

                                        <td class="px-5 py-4">
                                            <div
                                                class="flex items-center justify-end gap-1"
                                            >
                                                <button
                                                    type="button"
                                                    title="Lihat detail"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/30 dark:hover:text-blue-400"
                                                    @click="
                                                        openDetailModal(mitra)
                                                    "
                                                >
                                                    <Eye class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Edit"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/30 dark:hover:text-amber-400"
                                                    @click="
                                                        openEditModal(mitra)
                                                    "
                                                >
                                                    <Pencil class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Hapus"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30 dark:hover:text-red-400"
                                                    @click="
                                                        openDeleteModal(mitra)
                                                    "
                                                >
                                                    <Trash2 class="size-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- EMPTY -->

                                    <tr v-if="displayedData.length === 0">
                                        <td
                                            colspan="5"
                                            class="px-5 py-16 text-center"
                                        >
                                            <div
                                                class="mx-auto flex max-w-sm flex-col items-center"
                                            >
                                                <div
                                                    class="flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                                                >
                                                    <Building2 class="size-6" />
                                                </div>

                                                <h3
                                                    class="mt-4 text-sm font-semibold text-slate-800 dark:text-slate-200"
                                                >
                                                    {{
                                                        hasSearchOrFilter
                                                            ? "Data tidak ditemukan"
                                                            : "Belum ada mitra perusahaan"
                                                    }}
                                                </h3>

                                                <p
                                                    class="mt-1 text-xs leading-5 text-slate-400"
                                                >
                                                    {{
                                                        hasSearchOrFilter
                                                            ? "Coba ubah kata pencarian atau filter status."
                                                            : "Tambahkan perusahaan mitra pertama untuk ditampilkan di sini."
                                                    }}
                                                </p>

                                                <button
                                                    v-if="hasSearchOrFilter"
                                                    type="button"
                                                    class="mt-4 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                                    @click="clearSearch"
                                                >
                                                    Bersihkan filter
                                                </button>

                                                <button
                                                    v-else
                                                    type="button"
                                                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-700"
                                                    @click="openCreateModal"
                                                >
                                                    <Plus class="size-3.5" />
                                                    Tambah Mitra
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- PAGINATION -->

                        <div
                            v-if="props.mitraPerusahaans.last_page > 1"
                            class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                        >
                            <p class="text-xs text-slate-400">
                                Menampilkan
                                <span
                                    class="font-semibold text-slate-600 dark:text-slate-300"
                                >
                                    {{ props.mitraPerusahaans.from ?? 0 }}
                                </span>
                                –
                                <span
                                    class="font-semibold text-slate-600 dark:text-slate-300"
                                >
                                    {{ props.mitraPerusahaans.to ?? 0 }}
                                </span>
                                dari
                                <span
                                    class="font-semibold text-slate-600 dark:text-slate-300"
                                >
                                    {{ props.mitraPerusahaans.total }}
                                </span>
                                data
                            </p>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    :disabled="
                                        !props.mitraPerusahaans.links[0]?.url
                                    "
                                    class="rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                    @click="
                                        visitPage(
                                            props.mitraPerusahaans.links[0]
                                                ?.url ?? null,
                                        )
                                    "
                                >
                                    <ChevronLeft class="size-4" />
                                </button>

                                <template
                                    v-for="(
                                        link, index
                                    ) in props.mitraPerusahaans.links.slice(
                                        1,
                                        -1,
                                    )"
                                    :key="index"
                                >
                                    <button
                                        v-if="link.url && link.label !== '...'"
                                        type="button"
                                        class="min-w-9 rounded-lg border px-2.5 py-2 text-xs font-medium transition"
                                        :class="
                                            link.active
                                                ? 'border-blue-600 bg-blue-600 text-white'
                                                : 'border-slate-200 text-slate-500 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800'
                                        "
                                        @click="visitPage(link.url)"
                                    >
                                        {{ link.label }}
                                    </button>

                                    <span
                                        v-else
                                        class="px-1 text-xs text-slate-400"
                                    >
                                        {{ link.label }}
                                    </span>
                                </template>

                                <button
                                    type="button"
                                    :disabled="
                                        !props.mitraPerusahaans.links[
                                            props.mitraPerusahaans.links
                                                .length - 1
                                        ]?.url
                                    "
                                    class="rounded-lg border border-slate-200 p-2 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                    @click="
                                        visitPage(
                                            props.mitraPerusahaans.links[
                                                props.mitraPerusahaans.links
                                                    .length - 1
                                            ]?.url ?? null,
                                        )
                                    "
                                >
                                    <ChevronRight class="size-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ================================================================= -->
        <!-- CREATE / EDIT MODAL -->
        <!-- ================================================================= -->

        <Transition name="modal-fade">
            <div
                v-if="showCreateModal || showEditModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                :aria-label="modalTitle"
                @keydown.esc="closeAllModals"
            >
                <div
                    class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"
                    @click="closeAllModals"
                />

                <div
                    class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-700 dark:bg-slate-900"
                >
                    <!-- MODAL HEADER -->

                    <div
                        class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur-xl sm:px-6 dark:border-slate-800 dark:bg-slate-900/95"
                    >
                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ modalTitle }}
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-400">
                                {{
                                    showEditModal
                                        ? "Perbarui informasi mitra perusahaan."
                                        : "Tambahkan perusahaan yang menjadi mitra KITB."
                                }}
                            </p>
                        </div>

                        <button
                            type="button"
                            :disabled="isSubmitting"
                            class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:opacity-50 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="closeAllModals"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- FORM -->

                    <form
                        class="space-y-6 p-5 sm:p-6"
                        @submit.prevent="submitForm"
                    >
                        <!-- COMPANY NAME: MULTILINGUAL -->

                        <div class="space-y-3">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Nama Perusahaan
                                    <span class="text-red-500">*</span>
                                </label>
                                <p class="text-xs leading-5 text-slate-400">
                                    Isi nama perusahaan dalam Bahasa Indonesia,
                                    English, dan 中文. Bahasa Indonesia wajib
                                    diisi.
                                </p>
                            </div>

                            <div
                                class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-slate-50/80 p-1.5 dark:border-slate-700 dark:bg-slate-800/50"
                            >
                                <button
                                    type="button"
                                    class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition sm:flex-none"
                                    :class="
                                        activeLanguage === 'id'
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : 'text-slate-500 hover:bg-white hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white'
                                    "
                                    @click="activeLanguage = 'id'"
                                >
                                    Indonesia
                                    <span
                                        v-if="form.errors.nama_perusahaan"
                                        class="ml-1 text-red-300"
                                        >•</span
                                    >
                                </button>
                                <button
                                    type="button"
                                    class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition sm:flex-none"
                                    :class="
                                        activeLanguage === 'en'
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : 'text-slate-500 hover:bg-white hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white'
                                    "
                                    @click="activeLanguage = 'en'"
                                >
                                    English
                                    <span
                                        v-if="form.errors.nama_perusahaan_en"
                                        class="ml-1 text-red-500"
                                        >•</span
                                    >
                                </button>
                                <button
                                    type="button"
                                    class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition sm:flex-none"
                                    :class="
                                        activeLanguage === 'zh'
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : 'text-slate-500 hover:bg-white hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white'
                                    "
                                    @click="activeLanguage = 'zh'"
                                >
                                    中文
                                    <span
                                        v-if="form.errors.nama_perusahaan_zh"
                                        class="ml-1 text-red-500"
                                        >•</span
                                    >
                                </button>
                            </div>

                            <div v-if="activeLanguage === 'id'">
                                <label
                                    for="nama_perusahaan"
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Nama Perusahaan (Indonesia)
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="nama_perusahaan"
                                    v-model="form.nama_perusahaan"
                                    type="text"
                                    autocomplete="organization"
                                    placeholder="Contoh: PT Kawasan Industri Tanjung Buton"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500/10':
                                            form.errors.nama_perusahaan,
                                    }"
                                />
                                <p
                                    v-if="form.errors.nama_perusahaan"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ form.errors.nama_perusahaan }}
                                </p>
                            </div>

                            <div v-else-if="activeLanguage === 'en'">
                                <label
                                    for="nama_perusahaan_en"
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Company Name (English)
                                </label>
                                <input
                                    id="nama_perusahaan_en"
                                    v-model="form.nama_perusahaan_en"
                                    type="text"
                                    autocomplete="organization"
                                    placeholder="Enter company name in English"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500/10':
                                            form.errors.nama_perusahaan_en,
                                    }"
                                />
                                <p
                                    v-if="form.errors.nama_perusahaan_en"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ form.errors.nama_perusahaan_en }}
                                </p>
                                <p v-else class="mt-1.5 text-xs text-slate-400">
                                    Optional. If empty, the public page can fall
                                    back to Indonesian.
                                </p>
                            </div>

                            <div v-else>
                                <label
                                    for="nama_perusahaan_zh"
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    公司名称（中文）
                                </label>
                                <input
                                    id="nama_perusahaan_zh"
                                    v-model="form.nama_perusahaan_zh"
                                    type="text"
                                    autocomplete="organization"
                                    placeholder="请输入中文公司名称"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500/10':
                                            form.errors.nama_perusahaan_zh,
                                    }"
                                />
                                <p
                                    v-if="form.errors.nama_perusahaan_zh"
                                    class="mt-1.5 text-xs text-red-500"
                                >
                                    {{ form.errors.nama_perusahaan_zh }}
                                </p>
                                <p v-else class="mt-1.5 text-xs text-slate-400">
                                    选填。未填写时，公开页面可回退显示印度尼西亚语名称。
                                </p>
                            </div>
                        </div>

                        <!-- WEBSITE -->

                        <div>
                            <label
                                for="website"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Website
                            </label>

                            <div class="relative">
                                <ExternalLink
                                    class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    id="website"
                                    v-model="form.website"
                                    type="text"
                                    inputmode="url"
                                    autocomplete="url"
                                    placeholder="https://contoh.co.id"
                                    class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500/10':
                                            form.errors.website,
                                    }"
                                />
                            </div>

                            <p
                                v-if="form.errors.website"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ form.errors.website }}
                            </p>

                            <p v-else class="mt-1.5 text-xs text-slate-400">
                                URL website resmi perusahaan.
                            </p>
                        </div>

                        <!-- LOGO -->

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-800/40"
                        >
                            <div class="mb-4 flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <ImagePlus class="size-4" />
                                </div>

                                <div>
                                    <h3
                                        class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        Logo Perusahaan
                                    </h3>

                                    <p
                                        class="mt-0.5 text-xs leading-5 text-slate-400"
                                    >
                                        Gunakan logo dengan latar transparan
                                        jika tersedia.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex flex-col gap-4 sm:flex-row sm:items-center"
                            >
                                <div
                                    class="relative flex size-28 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <img
                                        v-if="displayedLogo"
                                        :src="displayedLogo"
                                        :alt="
                                            form.nama_perusahaan
                                                ? `Logo ${form.nama_perusahaan}`
                                                : 'Logo perusahaan'
                                        "
                                        class="size-full object-contain p-3"
                                    />

                                    <Building2
                                        v-else
                                        class="size-8 text-slate-300 dark:text-slate-600"
                                    />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <label
                                        class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-3 text-xs font-semibold text-slate-600 transition hover:border-blue-400 hover:bg-blue-50/30 hover:text-blue-600 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:bg-blue-950/20"
                                    >
                                        <Upload class="size-4" />

                                        <span>
                                            {{
                                                previewUrl
                                                    ? "Ganti Logo"
                                                    : "Pilih Logo"
                                            }}
                                        </span>

                                        <input
                                            ref="fileInput"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="handleFile"
                                        />
                                    </label>

                                    <p
                                        class="mt-2 text-center text-[10px] leading-4 text-slate-400"
                                    >
                                        JPG, JPEG, PNG, WEBP · Maksimal 1 MB
                                    </p>

                                    <p
                                        v-if="form.errors.logo"
                                        class="mt-2 text-center text-xs text-red-500"
                                    >
                                        {{ form.errors.logo }}
                                    </p>

                                    <button
                                        v-if="previewUrl"
                                        type="button"
                                        class="mt-2 w-full text-center text-[11px] font-medium text-red-500 transition hover:text-red-600"
                                        @click="removeNewLogo"
                                    >
                                        Batalkan logo baru
                                    </button>

                                    <p
                                        v-else-if="hasExistingLogo"
                                        class="mt-2 text-center text-[10px] text-slate-400"
                                    >
                                        Pilih logo baru untuk mengganti logo
                                        saat ini.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- STATUS -->

                        <div
                            class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/40"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                >
                                    Tampilkan di halaman publik
                                </p>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    Mitra aktif dapat ditampilkan pada halaman
                                    publik.
                                </p>
                            </div>

                            <button
                                type="button"
                                role="switch"
                                :aria-checked="form.aktif"
                                class="relative h-6 w-11 shrink-0 rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
                                :class="
                                    form.aktif
                                        ? 'bg-blue-600'
                                        : 'bg-slate-300 dark:bg-slate-700'
                                "
                                @click="form.aktif = !form.aktif"
                            >
                                <span
                                    class="absolute top-0.5 size-5 rounded-full bg-white shadow-sm transition"
                                    :class="
                                        form.aktif ? 'left-[22px]' : 'left-0.5'
                                    "
                                />
                            </button>
                        </div>

                        <!-- BUTTON -->

                        <div
                            class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end sm:gap-3 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                :disabled="isSubmitting"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeAllModals"
                            >
                                <X class="size-4" />
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="
                                    isSubmitting || !form.nama_perusahaan.trim()
                                "
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-500/10 transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus-visible:ring-offset-slate-950"
                            >
                                <LoaderCircle
                                    v-if="isSubmitting"
                                    class="size-4 animate-spin"
                                />

                                <Check v-else class="size-4" />

                                {{
                                    isSubmitting
                                        ? "Menyimpan..."
                                        : showEditModal
                                          ? "Simpan Perubahan"
                                          : "Tambah Mitra"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- ================================================================= -->
        <!-- DETAIL MODAL -->
        <!-- ================================================================= -->

        <Transition name="modal-fade">
            <div
                v-if="showDetailModal && selectedMitra"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-label="Detail Mitra Perusahaan"
                @keydown.esc="closeAllModals"
            >
                <div
                    class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"
                    @click="closeAllModals"
                />

                <div
                    class="relative z-10 w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-700 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800"
                    >
                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Mitra
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-400">
                                Informasi perusahaan mitra.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="closeAllModals"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <div class="space-y-5 p-5 sm:p-6">
                        <div
                            class="flex flex-col items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-700 dark:bg-slate-800/40"
                        >
                            <div
                                class="flex size-32 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
                            >
                                <img
                                    v-if="existingLogoUrl"
                                    :src="existingLogoUrl"
                                    :alt="`Logo ${selectedMitra.nama_perusahaan}`"
                                    class="size-full object-contain p-4"
                                />

                                <Building2
                                    v-else
                                    class="size-10 text-slate-300 dark:text-slate-600"
                                />
                            </div>

                            <div class="text-center">
                                <h3
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ selectedMitra.nama_perusahaan }}
                                </h3>
                                <p
                                    v-if="selectedMitra.nama_perusahaan_en"
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ selectedMitra.nama_perusahaan_en }}
                                </p>
                                <p
                                    v-if="selectedMitra.nama_perusahaan_zh"
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ selectedMitra.nama_perusahaan_zh }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Slug:
                                    {{ selectedMitra.slug }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"
                            >
                                <p
                                    class="text-[11px] font-medium uppercase tracking-wider text-slate-400"
                                >
                                    Urutan
                                </p>

                                <p
                                    class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    {{ selectedMitra.urutan }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"
                            >
                                <p
                                    class="text-[11px] font-medium uppercase tracking-wider text-slate-400"
                                >
                                    Status
                                </p>

                                <p
                                    class="mt-1 text-sm font-semibold"
                                    :class="
                                        selectedMitra.aktif
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-slate-500'
                                    "
                                >
                                    {{
                                        selectedMitra.aktif
                                            ? "Aktif"
                                            : "Tidak Aktif"
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"
                        >
                            <p
                                class="text-[11px] font-medium uppercase tracking-wider text-slate-400"
                            >
                                Website
                            </p>

                            <a
                                v-if="selectedMitra.website"
                                :href="normalizeWebsite(selectedMitra.website)"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                            >
                                {{ selectedMitra.website }}

                                <ExternalLink class="size-3.5" />
                            </a>

                            <p v-else class="mt-1 text-sm text-slate-400">
                                Belum tersedia
                            </p>
                        </div>

                        <div
                            class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800"
                        >
                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeAllModals"
                            >
                                <X class="size-4" />
                                Tutup
                            </button>

                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                                @click="
                                    showDetailModal = false;
                                    openEditModal(selectedMitra!);
                                "
                            >
                                <Pencil class="size-4" />
                                Edit Mitra
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ================================================================= -->
        <!-- DELETE MODAL -->
        <!-- ================================================================= -->

        <Transition name="modal-fade">
            <div
                v-if="showDeleteModal && selectedMitra"
                class="fixed inset-0 z-[60] flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-label="Hapus Mitra Perusahaan"
                @keydown.esc="!isDeleting && closeAllModals()"
            >
                <div
                    class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                    @click="!isDeleting && closeAllModals()"
                />

                <div
                    class="relative z-10 w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-950/20 dark:border-slate-700 dark:bg-slate-900"
                >
                    <div class="flex gap-4">
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                        >
                            <Trash2 class="size-5" />
                        </div>

                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Hapus Mitra Perusahaan?
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                Data
                                <strong
                                    class="font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    {{ selectedMitra.nama_perusahaan }}
                                </strong>
                                akan dihapus secara permanen. Logo yang
                                tersimpan juga akan dihapus.
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            :disabled="isDeleting"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closeAllModals"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="isDeleting"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="deleteMitra"
                        >
                            <LoaderCircle
                                v-if="isDeleting"
                                class="size-4 animate-spin"
                            />

                            <Trash2 v-else class="size-4" />

                            {{ isDeleting ? "Menghapus..." : "Ya, Hapus" }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.page-fade-enter-active,
.page-fade-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.page-fade-enter-from,
.page-fade-leave-to {
    opacity: 0;
    transform: translateY(6px);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-enter-from > div:last-child,
.modal-fade-leave-to > div:last-child {
    transform: translateY(8px) scale(0.98);
}

.modal-fade-enter-active > div:last-child,
.modal-fade-leave-active > div:last-child {
    transition: transform 0.2s ease;
}

@media (prefers-reduced-motion: reduce) {
    .page-fade-enter-active,
    .page-fade-leave-active,
    .modal-fade-enter-active,
    .modal-fade-leave-active {
        transition: none;
    }

    .modal-fade-enter-active > div:last-child,
    .modal-fade-leave-active > div:last-child {
        transition: none;
    }
}
</style>
