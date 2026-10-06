<script setup lang="ts">
import {
    ArrowDown,
    ArrowUp,
    Building2,
    Check,
    Eye,
    ExternalLink,
    ImagePlus,
    Pencil,
    Plus,
    Search,
    Trash2,
    Upload,
    X,
} from "lucide-vue-next";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

/*
|--------------------------------------------------------------------------
| Interface
|--------------------------------------------------------------------------
*/

interface AnakUsaha {
    id: number;

    nama: string;
    nama_en: string | null;
    nama_zh: string | null;

    logo: string | null;

    deskripsi: string | null;
    deskripsi_en: string | null;
    deskripsi_zh: string | null;

    website: string | null;

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

interface AnakUsahaPagination {
    data: AnakUsaha[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

interface Props {
    anakUsaha: AnakUsahaPagination;
}

const props = defineProps<Props>();

/*
|--------------------------------------------------------------------------
| Page Loading
|--------------------------------------------------------------------------
*/

const isPageLoading = ref(true);

let initialLoadingTimer: ReturnType<typeof setTimeout> | null = null;

let removeRouterStartListener: (() => void) | null = null;
let removeRouterFinishListener: (() => void) | null = null;

onMounted(() => {
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

    revokePreview();
});

/*
|--------------------------------------------------------------------------
| Search & Filter
|--------------------------------------------------------------------------
*/

const search = ref("");

const statusFilter = ref<"all" | "active" | "inactive">("all");

const filteredAnakUsaha = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    return [...props.anakUsaha.data].filter((item) => {
        const searchableText = [
            item.nama,
            item.nama_en ?? "",
            item.nama_zh ?? "",
            item.deskripsi ?? "",
            item.deskripsi_en ?? "",
            item.deskripsi_zh ?? "",
        ]
            .join(" ")
            .toLowerCase();

        const matchesSearch = !keyword || searchableText.includes(keyword);

        const matchesStatus =
            statusFilter.value === "all" ||
            (statusFilter.value === "active" && item.aktif) ||
            (statusFilter.value === "inactive" && !item.aktif);

        return matchesSearch && matchesStatus;
    });
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

const previousPageUrl = computed(() => {
    return props.anakUsaha.links[0]?.url ?? null;
});

const nextPageUrl = computed(() => {
    return props.anakUsaha.links[props.anakUsaha.links.length - 1]?.url ?? null;
});

/*
|--------------------------------------------------------------------------
| Modal
|--------------------------------------------------------------------------
*/

const showFormModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);

const modalMode = ref<"create" | "edit">("create");

const selectedItem = ref<AnakUsaha | null>(null);

/*
|--------------------------------------------------------------------------
| Language Tab
|--------------------------------------------------------------------------
*/

const activeLanguage = ref<"id" | "en" | "zh">("id");

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = ref({
    nama: "",
    nama_en: "",
    nama_zh: "",

    logo: null as File | null,

    deskripsi: "",
    deskripsi_en: "",
    deskripsi_zh: "",

    website: "",
    aktif: true,
});

const previewUrl = ref<string | null>(null);

const processingForm = ref(false);
const processingDelete = ref(false);
const processingToggleId = ref<number | null>(null);
const movingId = ref<number | null>(null);

/*
|--------------------------------------------------------------------------
| Form Computed
|--------------------------------------------------------------------------
*/

const formTitle = computed(() =>
    modalMode.value === "create" ? "Tambah Anak Usaha" : "Edit Anak Usaha",
);

const languageLabel = computed(() => {
    switch (activeLanguage.value) {
        case "en":
            return "English";

        case "zh":
            return "中文";

        default:
            return "Indonesia";
    }
});

/*
|--------------------------------------------------------------------------
| Form Reset
|--------------------------------------------------------------------------
*/

const revokePreview = () => {
    if (previewUrl.value?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl.value);
    }
};

const resetForm = () => {
    revokePreview();

    form.value = {
        nama: "",
        nama_en: "",
        nama_zh: "",

        logo: null,

        deskripsi: "",
        deskripsi_en: "",
        deskripsi_zh: "",

        website: "",
        aktif: true,
    };

    previewUrl.value = null;
    activeLanguage.value = "id";
};

const openCreate = () => {
    resetForm();

    modalMode.value = "create";
    selectedItem.value = null;

    showFormModal.value = true;
};

const openEdit = (item: AnakUsaha) => {
    revokePreview();

    selectedItem.value = item;

    modalMode.value = "edit";

    form.value = {
        nama: item.nama,
        nama_en: item.nama_en ?? "",
        nama_zh: item.nama_zh ?? "",

        logo: null,

        deskripsi: item.deskripsi ?? "",
        deskripsi_en: item.deskripsi_en ?? "",
        deskripsi_zh: item.deskripsi_zh ?? "",

        website: item.website ?? "",
        aktif: item.aktif,
    };

    previewUrl.value = item.logo ? `/storage/${item.logo}` : null;

    activeLanguage.value = "id";

    showFormModal.value = true;
};

const closeForm = () => {
    if (processingForm.value) {
        return;
    }

    showFormModal.value = false;

    resetForm();

    selectedItem.value = null;
};

const openDetail = (item: AnakUsaha) => {
    selectedItem.value = item;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
    selectedItem.value = null;
};

const openDelete = (item: AnakUsaha) => {
    selectedItem.value = item;
    showDeleteModal.value = true;
};

const closeDelete = () => {
    if (processingDelete.value) {
        return;
    }

    showDeleteModal.value = false;
    selectedItem.value = null;
};

/*
|--------------------------------------------------------------------------
| Image
|--------------------------------------------------------------------------
*/

const handleFile = (event: Event) => {
    const target = event.target as HTMLInputElement;

    const file = target.files?.[0] ?? null;

    if (!file) {
        return;
    }

    const maxSize = 1024 * 1024;

    if (file.size > maxSize) {
        target.value = "";
        form.value.logo = null;

        alert("Ukuran logo maksimal 1 MB.");

        return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

    if (!allowedTypes.includes(file.type)) {
        target.value = "";
        form.value.logo = null;

        alert("Format logo harus JPG, JPEG, PNG, atau WEBP.");

        return;
    }

    revokePreview();

    form.value.logo = file;

    previewUrl.value = URL.createObjectURL(file);
};

const getImageUrl = (logo: string | null) => {
    return logo ? `/storage/${logo}` : null;
};

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const isFormValid = computed(() => {
    return form.value.nama.trim().length > 0;
});

/*
|--------------------------------------------------------------------------
| Store / Update
|--------------------------------------------------------------------------
*/

const submitForm = () => {
    if (processingForm.value || !isFormValid.value) {
        return;
    }

    const formData = new FormData();

    formData.append("nama", form.value.nama.trim());

    formData.append("nama_en", form.value.nama_en.trim());

    formData.append("nama_zh", form.value.nama_zh.trim());

    formData.append("deskripsi", form.value.deskripsi.trim());

    formData.append("deskripsi_en", form.value.deskripsi_en.trim());

    formData.append("deskripsi_zh", form.value.deskripsi_zh.trim());

    formData.append("website", form.value.website.trim());

    formData.append("aktif", form.value.aktif ? "1" : "0");

    if (form.value.logo) {
        formData.append("logo", form.value.logo);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    if (modalMode.value === "edit" && selectedItem.value) {
        formData.append("_method", "PUT");

        router.post(
            `/admin/profil-perusahaan/anak-usaha/${selectedItem.value.id}`,
            formData,
            {
                forceFormData: true,
                preserveScroll: true,

                onStart: () => {
                    processingForm.value = true;
                },

                onSuccess: () => {
                    showFormModal.value = false;

                    resetForm();

                    selectedItem.value = null;
                },

                onError: (errors) => {
                    console.error("Gagal memperbarui anak usaha:", errors);
                },

                onFinish: () => {
                    processingForm.value = false;
                },
            },
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    router.post("/admin/profil-perusahaan/anak-usaha", formData, {
        forceFormData: true,
        preserveScroll: true,

        onStart: () => {
            processingForm.value = true;
        },

        onSuccess: () => {
            showFormModal.value = false;

            resetForm();

            selectedItem.value = null;
        },

        onError: (errors) => {
            console.error("Gagal menambahkan anak usaha:", errors);
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

const deleteItem = () => {
    if (!selectedItem.value || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(
        `/admin/profil-perusahaan/anak-usaha/${selectedItem.value.id}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                showDeleteModal.value = false;
                selectedItem.value = null;
            },

            onError: (errors) => {
                console.error("Gagal menghapus anak usaha:", errors);
            },

            onFinish: () => {
                processingDelete.value = false;
            },
        },
    );
};

/*
|--------------------------------------------------------------------------
| Toggle Status
|--------------------------------------------------------------------------
*/

const toggleAktif = (item: AnakUsaha) => {
    if (processingToggleId.value !== null) {
        return;
    }

    processingToggleId.value = item.id;

    router.patch(
        `/admin/profil-perusahaan/anak-usaha/${item.id}/toggle-aktif`,
        {},
        {
            preserveScroll: true,

            onError: (errors) => {
                console.error("Gagal mengubah status:", errors);
            },

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

const moveItem = (item: AnakUsaha, direction: "up" | "down") => {
    if (movingId.value !== null) {
        return;
    }

    if (direction === "up" && item.urutan <= 1) {
        return;
    }

    if (direction === "down" && item.urutan >= props.anakUsaha.total) {
        return;
    }

    movingId.value = item.id;

    router.patch(
        `/admin/profil-perusahaan/anak-usaha/${item.id}/move`,
        {
            direction,
        },
        {
            preserveScroll: true,

            onError: (errors) => {
                console.error("Gagal mengubah urutan:", errors);
            },

            onFinish: () => {
                movingId.value = null;
            },
        },
    );
};
</script>

<template>
    <div
        class="relative min-h-full overflow-hidden bg-slate-50/50 transition-colors duration-300 dark:bg-slate-950/50"
        :aria-busy="isPageLoading"
    >
        <!-- ========================================================= -->
        <!-- BACKGROUND -->
        <!-- ========================================================= -->

        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="blob-shape absolute -left-40 -top-40 h-[32rem] w-[32rem] rounded-full bg-blue-500/[0.055] blur-3xl transition-opacity duration-500 dark:opacity-0"
            />

            <div
                class="blob-shape-delayed absolute -right-48 top-8 h-[34rem] w-[34rem] rounded-full bg-indigo-500/[0.05] blur-3xl transition-opacity duration-500 dark:opacity-0"
            />

            <div
                class="blob-shape-slow absolute -bottom-52 left-1/3 h-[34rem] w-[34rem] rounded-full bg-sky-500/[0.045] blur-3xl transition-opacity duration-500 dark:opacity-0"
            />

            <div
                class="blob-shape absolute -left-12 top-[42%] h-40 w-40 rounded-full bg-blue-500/[0.035] blur-3xl dark:opacity-0"
            />

            <div
                class="blob-shape-delayed absolute -right-16 top-[58%] h-48 w-48 rounded-full bg-slate-400/[0.04] blur-3xl dark:opacity-0"
            />

            <div
                class="blob-shape absolute -left-44 -top-44 h-[34rem] w-[34rem] rounded-full bg-blue-600/[0.10] opacity-0 blur-3xl dark:opacity-100"
            />

            <div
                class="blob-shape-delayed absolute -right-48 -top-12 h-[36rem] w-[36rem] rounded-full bg-indigo-500/[0.075] opacity-0 blur-3xl dark:opacity-100"
            />

            <div
                class="blob-shape-slow absolute -bottom-56 left-1/3 h-[36rem] w-[36rem] rounded-full bg-sky-500/[0.06] opacity-0 blur-3xl dark:opacity-100"
            />

            <div
                class="absolute inset-0 bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px] opacity-40 dark:opacity-20"
            />

            <div
                class="absolute left-[7%] top-[45%] h-20 w-20 rounded-full border border-blue-500/[0.07] bg-blue-500/[0.045] shadow-xl shadow-blue-500/[0.05] dark:border-blue-400/[0.08] dark:bg-blue-400/[0.06]"
            />

            <div
                class="absolute left-[4%] top-[54%] h-4 w-4 rounded-full bg-blue-500/[0.12] dark:bg-blue-300/[0.22]"
            />

            <div
                class="absolute right-[8%] top-[30%] h-4 w-4 rounded-full bg-indigo-500/[0.12] dark:bg-indigo-300/[0.22]"
            />

            <div
                class="absolute bottom-[18%] right-[13%] h-6 w-6 rounded-full border border-indigo-500/[0.07] bg-indigo-500/[0.035] dark:border-blue-400/[0.08] dark:bg-blue-400/[0.06]"
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
            <Transition name="page-fade" mode="out-in">
                <!-- ===================================================== -->
                <!-- SKELETON -->
                <!-- ===================================================== -->

                <div
                    v-if="isPageLoading"
                    key="skeleton"
                    class="space-y-5"
                    role="status"
                    aria-label="Memuat data anak usaha"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="size-11 animate-pulse rounded-2xl bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="space-y-2">
                                <div
                                    class="h-5 w-44 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-64 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                            </div>
                        </div>

                        <div
                            class="h-11 w-full animate-pulse rounded-xl bg-slate-200 dark:bg-slate-800 sm:w-40"
                        />
                    </div>

                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="h-16 animate-pulse border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950"
                        />

                        <div
                            v-for="i in 6"
                            :key="i"
                            class="flex items-center gap-4 border-b border-slate-100 px-6 py-5 dark:border-slate-800"
                        >
                            <div
                                class="size-12 animate-pulse rounded-xl bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="flex-1 space-y-2">
                                <div
                                    class="h-4 w-48 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-3 w-72 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                />
                            </div>

                            <div
                                class="h-8 w-20 animate-pulse rounded-full bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="h-9 w-24 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                            />
                        </div>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- CONTENT -->
                <!-- ===================================================== -->

                <div v-else key="content" class="relative space-y-5">
                    <!-- HEADER -->

                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <Building2 class="size-5" />
                            </div>

                            <div class="min-w-0">
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400"
                                >
                                    Profil Perusahaan
                                </p>

                                <h1
                                    class="mt-0.5 text-xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-2xl"
                                >
                                    Anak Usaha
                                </h1>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Kelola informasi anak usaha perusahaan.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 sm:w-auto"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />
                            Tambah Anak Usaha
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
                                        Daftar Anak Usaha
                                    </h2>

                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        {{ props.anakUsaha.total }}
                                    </span>
                                </div>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Kelola, urutkan, dan perbarui informasi anak
                                    usaha.
                                </p>
                            </div>

                            <div
                                class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto"
                            >
                                <div class="relative w-full sm:w-64">
                                    <Search
                                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    />

                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Cari anak usaha..."
                                        aria-label="Cari anak usaha"
                                        class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-9 pr-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800"
                                    />
                                </div>

                                <select
                                    v-model="statusFilter"
                                    aria-label="Filter status"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200 sm:w-36"
                                >
                                    <option value="all">Semua Status</option>

                                    <option value="active">Aktif</option>

                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <!-- EMPTY -->

                        <div
                            v-if="filteredAnakUsaha.length === 0"
                            class="px-6 py-20 text-center"
                        >
                            <div
                                class="mx-auto flex size-14 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800"
                            >
                                <Building2 class="size-6" />
                            </div>

                            <h3
                                class="mt-4 font-semibold text-slate-900 dark:text-white"
                            >
                                Belum ada data
                            </h3>

                            <p
                                class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    search || statusFilter !== "all"
                                        ? "Data yang sesuai dengan pencarian tidak ditemukan."
                                        : "Tambahkan anak usaha untuk mulai mengisi halaman ini."
                                }}
                            </p>

                            <button
                                v-if="!search && statusFilter === 'all'"
                                type="button"
                                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                                @click="openCreate"
                            >
                                <Plus class="size-4" />
                                Tambah Anak Usaha
                            </button>
                        </div>

                        <!-- TABLE -->

                        <div v-else class="overflow-x-auto">
                            <table class="w-full min-w-[950px] text-left">
                                <thead
                                    class="border-b border-slate-200/80 bg-slate-50/70 dark:border-slate-800 dark:bg-slate-950/30"
                                >
                                    <tr>
                                        <th
                                            class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                                        >
                                            Urutan
                                        </th>

                                        <th
                                            class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                                        >
                                            Anak Usaha
                                        </th>

                                        <th
                                            class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                                        >
                                            Website
                                        </th>

                                        <th
                                            class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                                        >
                                            Status
                                        </th>

                                        <th
                                            class="px-6 py-3.5 text-right text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800"
                                >
                                    <tr
                                        v-for="item in filteredAnakUsaha"
                                        :key="item.id"
                                        class="group transition-colors hover:bg-blue-50/40 dark:hover:bg-blue-950/10"
                                    >
                                        <!-- ORDER -->

                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-1.5"
                                            >
                                                <span
                                                    class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                                >
                                                    {{ item.urutan }}
                                                </span>

                                                <div class="flex flex-col">
                                                    <button
                                                        type="button"
                                                        title="Naikkan urutan"
                                                        :disabled="
                                                            item.urutan <= 1 ||
                                                            movingId !== null
                                                        "
                                                        class="rounded-md p-1 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                        @click="
                                                            moveItem(item, 'up')
                                                        "
                                                    >
                                                        <ArrowUp
                                                            class="size-3.5"
                                                        />
                                                    </button>

                                                    <button
                                                        type="button"
                                                        title="Turunkan urutan"
                                                        :disabled="
                                                            item.urutan >=
                                                                props.anakUsaha
                                                                    .total ||
                                                            movingId !== null
                                                        "
                                                        class="rounded-md p-1 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                        @click="
                                                            moveItem(
                                                                item,
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

                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 text-slate-400 group-hover:border-blue-100 group-hover:bg-white dark:border-slate-700 dark:bg-slate-800"
                                                >
                                                    <img
                                                        v-if="
                                                            getImageUrl(
                                                                item.logo,
                                                            )
                                                        "
                                                        :src="
                                                            getImageUrl(
                                                                item.logo,
                                                            )!
                                                        "
                                                        :alt="item.nama"
                                                        class="size-full object-contain p-1.5"
                                                    />

                                                    <ImagePlus
                                                        v-else
                                                        class="size-5"
                                                    />
                                                </div>

                                                <div class="min-w-0">
                                                    <p
                                                        class="font-medium text-slate-800 dark:text-slate-200"
                                                    >
                                                        {{ item.nama }}
                                                    </p>

                                                    <p
                                                        v-if="item.nama_en"
                                                        class="mt-0.5 text-xs text-slate-400"
                                                    >
                                                        {{ item.nama_en }}
                                                    </p>

                                                    <p
                                                        v-if="item.deskripsi"
                                                        class="mt-1 max-w-md truncate text-xs text-slate-400"
                                                    >
                                                        {{ item.deskripsi }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- WEBSITE -->

                                        <td class="px-6 py-4">
                                            <a
                                                v-if="item.website"
                                                :href="item.website"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex max-w-[220px] items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                            >
                                                <span class="truncate">
                                                    {{ item.website }}
                                                </span>

                                                <ExternalLink
                                                    class="size-3.5 shrink-0"
                                                />
                                            </a>

                                            <span
                                                v-else
                                                class="text-sm text-slate-400"
                                            >
                                                -
                                            </span>
                                        </td>

                                        <!-- STATUS -->

                                        <td class="px-6 py-4">
                                            <button
                                                type="button"
                                                :disabled="
                                                    processingToggleId ===
                                                    item.id
                                                "
                                                class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold transition"
                                                :class="
                                                    item.aktif
                                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                        : 'border-slate-200 bg-slate-100 text-slate-500 hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400'
                                                "
                                                @click="toggleAktif(item)"
                                            >
                                                {{
                                                    item.aktif
                                                        ? "Aktif"
                                                        : "Nonaktif"
                                                }}
                                            </button>
                                        </td>

                                        <!-- ACTION -->

                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-1">
                                                <button
                                                    type="button"
                                                    title="Lihat detail"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                    @click="openDetail(item)"
                                                >
                                                    <Eye class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Edit"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                                    @click="openEdit(item)"
                                                >
                                                    <Pencil class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Hapus"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
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

                        <div
                            v-if="props.anakUsaha.last_page > 1"
                            class="flex flex-col gap-4 border-t border-slate-200/80 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                        >
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Menampilkan
                                <span
                                    class="font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    {{ props.anakUsaha.from ?? 0 }}
                                </span>
                                -
                                <span
                                    class="font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    {{ props.anakUsaha.to ?? 0 }}
                                </span>
                                dari
                                <span
                                    class="font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    {{ props.anakUsaha.total }}
                                </span>
                                data
                            </p>

                            <div
                                class="flex max-w-full items-center gap-1.5 overflow-x-auto pb-1"
                            >
                                <button
                                    type="button"
                                    :disabled="!previousPageUrl"
                                    class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                    @click="goToPage(previousPageUrl)"
                                >
                                    Previous
                                </button>

                                <template
                                    v-for="(
                                        link, index
                                    ) in props.anakUsaha.links.slice(1, -1)"
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
                                    class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                    @click="goToPage(nextPageUrl)"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- ========================================================= -->
        <!-- FORM MODAL -->
        <!-- ========================================================= -->

        <Transition name="modal-fade">
            <div
                v-if="showFormModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
                @click.self="closeForm"
            >
                <div
                    class="modal-panel max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- HEADER -->

                    <div
                        class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-slate-50/95 px-6 py-4 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95"
                    >
                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ formTitle }}
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Lengkapi informasi anak usaha.
                            </p>
                        </div>

                        <button
                            type="button"
                            title="Tutup"
                            :disabled="processingForm"
                            class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:opacity-50 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="closeForm"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <form class="space-y-6 p-6" @submit.prevent="submitForm">
                        <!-- ================================================= -->
                        <!-- LANGUAGE TABS -->
                        <!-- ================================================= -->

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-1.5 dark:border-slate-700 dark:bg-slate-800/50"
                        >
                            <div class="grid grid-cols-3 gap-1">
                                <button
                                    type="button"
                                    class="rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                                    :class="
                                        activeLanguage === 'id'
                                            ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-700 dark:text-blue-400'
                                            : 'text-slate-500 hover:bg-white/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700/50 dark:hover:text-slate-200'
                                    "
                                    @click="activeLanguage = 'id'"
                                >
                                    🇮🇩 Indonesia
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                                    :class="
                                        activeLanguage === 'en'
                                            ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-700 dark:text-blue-400'
                                            : 'text-slate-500 hover:bg-white/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700/50 dark:hover:text-slate-200'
                                    "
                                    @click="activeLanguage = 'en'"
                                >
                                    🇬🇧 English
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                                    :class="
                                        activeLanguage === 'zh'
                                            ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-700 dark:text-blue-400'
                                            : 'text-slate-500 hover:bg-white/70 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700/50 dark:hover:text-slate-200'
                                    "
                                    @click="activeLanguage = 'zh'"
                                >
                                    🇨🇳 中文
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
                                <Check class="size-4" />
                            </div>

                            <div>
                                <p
                                    class="text-sm font-semibold text-blue-800 dark:text-blue-300"
                                >
                                    {{ languageLabel }}
                                </p>

                                <p
                                    class="mt-0.5 text-xs leading-5 text-blue-600/80 dark:text-blue-400/80"
                                >
                                    Isi konten anak usaha untuk bahasa yang
                                    sedang dipilih.
                                </p>
                            </div>
                        </div>

                        <!-- ================================================= -->
                        <!-- INDONESIA -->
                        <!-- ================================================= -->

                        <template v-if="activeLanguage === 'id'">
                            <!-- NAMA -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Nama Anak Usaha
                                    <span class="text-red-500"> * </span>
                                </label>

                                <input
                                    v-model="form.nama"
                                    type="text"
                                    required
                                    maxlength="255"
                                    placeholder="Contoh: PT KITB Properti"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:border-blue-500 dark:focus:bg-slate-800"
                                />
                            </div>

                            <!-- DESKRIPSI -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Deskripsi
                                </label>

                                <textarea
                                    v-model="form.deskripsi"
                                    rows="5"
                                    placeholder="Masukkan deskripsi anak usaha..."
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800"
                                />
                            </div>
                        </template>

                        <!-- ================================================= -->
                        <!-- ENGLISH -->
                        <!-- ================================================= -->

                        <template v-else-if="activeLanguage === 'en'">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Subsidiary Name
                                </label>

                                <input
                                    v-model="form.nama_en"
                                    type="text"
                                    maxlength="255"
                                    placeholder="Example: KITB Property Ltd."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Description
                                </label>

                                <textarea
                                    v-model="form.deskripsi_en"
                                    rows="5"
                                    placeholder="Enter the subsidiary description..."
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800"
                                />
                            </div>
                        </template>

                        <!-- ================================================= -->
                        <!-- CHINESE -->
                        <!-- ================================================= -->

                        <template v-else>
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    子公司名称
                                </label>

                                <input
                                    v-model="form.nama_zh"
                                    type="text"
                                    maxlength="255"
                                    placeholder="例如：KITB 房地产有限公司"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    描述
                                </label>

                                <textarea
                                    v-model="form.deskripsi_zh"
                                    rows="5"
                                    placeholder="请输入子公司的描述..."
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800"
                                />
                            </div>
                        </template>

                        <!-- ================================================= -->
                        <!-- LOGO -->
                        <!-- ================================================= -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Logo
                            </label>

                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-5 py-7 transition hover:border-blue-400 hover:bg-blue-50/30 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-blue-500 dark:hover:bg-blue-950/20"
                            >
                                <Upload class="size-6 text-slate-400" />

                                <span
                                    class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-300"
                                >
                                    Klik untuk memilih logo
                                </span>

                                <span class="mt-1 text-xs text-slate-400">
                                    JPG, JPEG, PNG, WEBP maksimal 1 MB
                                </span>

                                <input
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                    @change="handleFile"
                                />
                            </label>

                            <div
                                v-if="previewUrl"
                                class="mt-4 flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50/50 p-3 dark:border-slate-700 dark:bg-slate-800/40"
                            >
                                <div
                                    class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white dark:bg-slate-800"
                                >
                                    <img
                                        :src="previewUrl"
                                        alt="Preview logo"
                                        class="size-full object-contain p-1"
                                    />
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{ form.logo?.name ?? "Logo saat ini" }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Preview logo
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ================================================= -->
                        <!-- WEBSITE -->
                        <!-- ================================================= -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Website
                            </label>

                            <input
                                v-model="form.website"
                                type="url"
                                maxlength="255"
                                placeholder="https://www.example.com"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:bg-slate-800"
                            />

                            <p class="mt-1.5 text-xs text-slate-400">
                                Masukkan URL lengkap, contoh
                                https://www.example.com
                            </p>
                        </div>

                        <!-- ================================================= -->
                        <!-- STATUS -->
                        <!-- ================================================= -->

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
                                    Aktif
                                </p>

                                <p class="text-xs text-slate-400">
                                    Tampilkan anak usaha sebagai data aktif.
                                </p>
                            </div>
                        </label>

                        <!-- ================================================= -->
                        <!-- BUTTON -->
                        <!-- ================================================= -->

                        <div
                            class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 dark:border-slate-800 sm:flex-row sm:justify-end"
                        >
                            <button
                                type="button"
                                :disabled="processingForm"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeForm"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="processingForm || !isFormValid"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span
                                    v-if="processingForm"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                />

                                {{
                                    processingForm
                                        ? "Menyimpan..."
                                        : modalMode === "create"
                                          ? "Simpan"
                                          : "Perbarui"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- ========================================================= -->
        <!-- DETAIL MODAL -->
        <!-- ========================================================= -->

        <Transition name="modal-fade">
            <div
                v-if="showDetailModal && selectedItem"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
                @click.self="closeDetail"
            >
                <div
                    class="modal-panel max-h-[90vh] w-full max-w-md overflow-y-auto rounded-3xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-6 py-4 dark:border-slate-800 dark:bg-slate-950/30"
                    >
                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Anak Usaha
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap anak usaha perusahaan.
                            </p>
                        </div>

                        <button
                            type="button"
                            title="Tutup"
                            class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="closeDetail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <div class="space-y-5 p-6">
                        <!-- LOGO -->

                        <div class="flex justify-center">
                            <div
                                class="flex size-32 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800"
                            >
                                <img
                                    v-if="getImageUrl(selectedItem.logo)"
                                    :src="getImageUrl(selectedItem.logo)!"
                                    :alt="selectedItem.nama"
                                    class="size-full object-contain p-3"
                                />

                                <ImagePlus v-else class="size-8" />
                            </div>
                        </div>

                        <!-- NAME -->

                        <div class="text-center">
                            <h3
                                class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ selectedItem.nama }}
                            </h3>

                            <p
                                v-if="selectedItem.nama_en"
                                class="mt-1 text-sm text-slate-400"
                            >
                                {{ selectedItem.nama_en }}
                            </p>

                            <p
                                v-if="selectedItem.nama_zh"
                                class="mt-1 text-sm text-slate-400"
                            >
                                {{ selectedItem.nama_zh }}
                            </p>
                        </div>

                        <!-- WEBSITE -->

                        <div
                            v-if="selectedItem.website"
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400"
                            >
                                Website
                            </p>

                            <a
                                :href="selectedItem.website"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-2 inline-flex items-center gap-2 break-all text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                            >
                                {{ selectedItem.website }}

                                <ExternalLink class="size-4 shrink-0" />
                            </a>
                        </div>

                        <!-- DESCRIPTION ID -->

                        <div
                            v-if="selectedItem.deskripsi"
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400"
                            >
                                Deskripsi
                            </p>

                            <p
                                class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedItem.deskripsi }}
                            </p>
                        </div>

                        <!-- DESCRIPTION EN -->

                        <div
                            v-if="selectedItem.deskripsi_en"
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400"
                            >
                                Description
                            </p>

                            <p
                                class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedItem.deskripsi_en }}
                            </p>
                        </div>

                        <!-- DESCRIPTION ZH -->

                        <div
                            v-if="selectedItem.deskripsi_zh"
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400"
                            >
                                描述
                            </p>

                            <p
                                class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedItem.deskripsi_zh }}
                            </p>
                        </div>

                        <!-- INFO -->

                        <div class="grid grid-cols-2 gap-3">
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Urutan
                                </p>

                                <p
                                    class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Ke-{{ selectedItem.urutan }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Status
                                </p>

                                <p
                                    class="mt-2 text-sm font-semibold"
                                    :class="
                                        selectedItem.aktif
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-slate-500 dark:text-slate-400'
                                    "
                                >
                                    {{
                                        selectedItem.aktif
                                            ? "Aktif"
                                            : "Nonaktif"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ========================================================= -->
        <!-- DELETE MODAL -->
        <!-- ========================================================= -->

        <Transition name="modal-fade">
            <div
                v-if="showDeleteModal && selectedItem"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
                @click.self="closeDelete"
            >
                <div
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
                            Hapus Anak Usaha?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus data
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedItem.nama }}
                            </span>
                            ? Data yang sudah dihapus tidak dapat dikembalikan.
                        </p>

                        <div
                            class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-left dark:border-slate-700 dark:bg-slate-800"
                        >
                            <p
                                class="text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                Urutan ke-{{ selectedItem.urutan }}
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
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
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
    </div>
</template>

<style scoped>
.page-fade-enter-active,
.page-fade-leave-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s ease;
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

.modal-fade-enter-from .modal-panel,
.modal-fade-leave-to .modal-panel {
    transform: translateY(8px) scale(0.985);
}

.modal-panel {
    transition: transform 0.2s ease;
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
    .page-fade-enter-active,
    .page-fade-leave-active,
    .modal-fade-enter-active,
    .modal-fade-leave-active,
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

    .page-fade-enter-from,
    .page-fade-leave-to {
        transform: none;
    }
}
</style>
