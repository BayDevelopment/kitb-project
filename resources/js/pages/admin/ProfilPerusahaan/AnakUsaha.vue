<script setup lang="ts">
import {
    ArrowDown,
    ArrowUp,
    Building2,
    Eye,
    ImagePlus,
    Pencil,
    Plus,
    Search,
    Trash2,
    Upload,
    X,
    ExternalLink,
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
    logo: string | null;
    deskripsi: string | null;
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

    if (previewUrl.value?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl.value);
    }
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
        const matchesSearch =
            !keyword ||
            item.nama.toLowerCase().includes(keyword) ||
            (item.deskripsi ?? "").toLowerCase().includes(keyword);

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

const form = ref({
    nama: "",
    logo: null as File | null,
    deskripsi: "",
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
| Form
|--------------------------------------------------------------------------
*/

const formTitle = computed(() =>
    modalMode.value === "create" ? "Tambah Anak Usaha" : "Edit Anak Usaha",
);

const resetForm = () => {
    if (previewUrl.value?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl.value);
    }

    form.value = {
        nama: "",
        logo: null,
        deskripsi: "",
        website: "",
        aktif: true,
    };

    previewUrl.value = null;
};

const openCreate = () => {
    resetForm();

    modalMode.value = "create";

    selectedItem.value = null;

    showFormModal.value = true;
};

const openEdit = (item: AnakUsaha) => {
    if (previewUrl.value?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl.value);
    }

    selectedItem.value = item;

    modalMode.value = "edit";

    form.value = {
        nama: item.nama,
        logo: null,
        deskripsi: item.deskripsi ?? "",
        website: item.website ?? "",
        aktif: item.aktif,
    };

    previewUrl.value = item.logo ? `/storage/${item.logo}` : null;

    showFormModal.value = true;
};

const closeForm = () => {
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

    /*
    |--------------------------------------------------------------------------
    | Maksimal 1 MB
    |--------------------------------------------------------------------------
    */

    const maxSize = 1024 * 1024;

    if (file.size > maxSize) {
        target.value = "";

        form.value.logo = null;

        alert("Ukuran logo maksimal 1 MB.");

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Format
    |--------------------------------------------------------------------------
    */

    const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

    if (!allowedTypes.includes(file.type)) {
        target.value = "";

        form.value.logo = null;

        alert("Format logo harus JPG, JPEG, PNG, atau WEBP.");

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    if (previewUrl.value?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl.value);
    }

    form.value.logo = file;

    previewUrl.value = URL.createObjectURL(file);
};

const getImageUrl = (logo: string | null) => {
    return logo ? `/storage/${logo}` : null;
};

/*
|--------------------------------------------------------------------------
| Store / Update
|--------------------------------------------------------------------------
*/

const submitForm = () => {
    if (processingForm.value) {
        return;
    }

    if (!form.value.nama.trim()) {
        return;
    }

    const formData = new FormData();

    formData.append("nama", form.value.nama.trim());

    formData.append("deskripsi", form.value.deskripsi.trim());

    formData.append("website", form.value.website.trim());

    formData.append("aktif", form.value.aktif ? "1" : "0");

    if (form.value.logo) {
        formData.append("logo", form.value.logo);
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    if (modalMode.value === "edit" && selectedItem.value) {
        formData.append("_method", "PUT");

        router.post(
            `/profil-perusahaan/anak-usaha/${selectedItem.value.id}`,
            formData,
            {
                forceFormData: true,

                preserveScroll: true,

                onStart: () => {
                    processingForm.value = true;
                },

                onSuccess: () => {
                    closeForm();
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
    | TAMBAH
    |--------------------------------------------------------------------------
    */

    router.post("/profil-perusahaan/anak-usaha", formData, {
        forceFormData: true,

        preserveScroll: true,

        onStart: () => {
            processingForm.value = true;
        },

        onSuccess: () => {
            closeForm();
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

    router.delete(`/profil-perusahaan/anak-usaha/${selectedItem.value.id}`, {
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
    });
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
        `/profil-perusahaan/anak-usaha/${item.id}/toggle-aktif`,
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
        `/profil-perusahaan/anak-usaha/${item.id}/move`,
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
        class="relative min-h-full overflow-hidden bg-slate-50 transition-colors duration-300 dark:bg-[#07111f]"
        :aria-busy="isPageLoading"
    >
        <!-- ========================================================= -->
        <!-- BACKGROUND DECORATION -->
        <!-- ========================================================= -->
        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <!-- Light -->
            <div
                class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-[#0b1f3a]/8 blur-3xl transition-opacity duration-500 dark:opacity-0"
            />

            <div
                class="absolute -right-40 top-16 h-[28rem] w-[28rem] rounded-full bg-[#12345b]/8 blur-3xl transition-opacity duration-500 dark:opacity-0"
            />

            <div
                class="absolute -bottom-48 left-1/3 h-[30rem] w-[30rem] rounded-full bg-[#0b1f3a]/6 blur-3xl transition-opacity duration-500 dark:opacity-0"
            />

            <div
                class="absolute left-[7%] top-[42%] h-32 w-32 rounded-full bg-blue-500/5 blur-2xl transition-opacity duration-500 dark:opacity-0"
            />

            <!-- Dark -->
            <div
                class="absolute -left-40 -top-40 h-[32rem] w-[32rem] rounded-full bg-[#163b68]/35 blur-3xl opacity-0 transition-opacity duration-500 dark:opacity-100"
            />

            <div
                class="absolute -right-44 top-0 h-[34rem] w-[34rem] rounded-full bg-blue-600/15 blur-3xl opacity-0 transition-opacity duration-500 dark:opacity-100"
            />

            <div
                class="absolute -bottom-48 left-1/3 h-[34rem] w-[34rem] rounded-full bg-cyan-600/10 blur-3xl opacity-0 transition-opacity duration-500 dark:opacity-100"
            />

            <!-- Decorative circles -->
            <div
                class="absolute left-[7%] top-[45%] h-20 w-20 rounded-full bg-[#163b68]/10 shadow-xl shadow-[#0b1f3a]/10 dark:bg-blue-400/10 dark:shadow-blue-500/10"
            />

            <div
                class="absolute left-[4%] top-[54%] h-4 w-4 rounded-full bg-[#0b1f3a]/20 dark:bg-blue-300/30"
            />

            <div
                class="absolute right-[8%] top-[30%] h-4 w-4 rounded-full bg-[#0b1f3a]/20 dark:bg-blue-300/30"
            />
        </div>

        <!-- ========================================================= -->
        <!-- CONTENT -->
        <!-- ========================================================= -->
        <div
            class="relative mx-auto flex w-full max-w-[1600px] flex-1 flex-col p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <Transition name="page-fade" mode="out-in">
                <!-- ================================================= -->
                <!-- SKELETON -->
                <!-- ================================================= -->
                <div
                    v-if="isPageLoading"
                    key="skeleton"
                    class="relative space-y-5"
                    role="status"
                    aria-label="Memuat data anak usaha"
                >
                    <!-- Header skeleton -->
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

                    <!-- Main card skeleton -->
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <!-- Card header -->
                        <div
                            class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <div class="space-y-2">
                                <div
                                    class="h-5 w-44 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-32 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row">
                                <div
                                    class="h-10 w-full animate-pulse rounded-xl bg-slate-200 dark:bg-slate-800 sm:w-56"
                                />

                                <div
                                    class="h-10 w-full animate-pulse rounded-xl bg-slate-200 dark:bg-slate-800 sm:w-32"
                                />
                            </div>
                        </div>

                        <!-- Table skeleton -->
                        <div class="overflow-x-auto">
                            <div class="min-w-[850px]">
                                <div
                                    class="grid grid-cols-[100px_minmax(280px,1fr)_220px_120px_120px] gap-4 border-b border-slate-200 bg-slate-50/70 px-6 py-3 dark:border-slate-800 dark:bg-slate-950/30"
                                >
                                    <div
                                        class="h-3 w-14 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                    <div
                                        class="h-3 w-24 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                    <div
                                        class="h-3 w-20 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                    <div
                                        class="h-3 w-16 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                    <div
                                        class="ml-auto h-3 w-12 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>

                                <div
                                    v-for="i in 6"
                                    :key="i"
                                    class="grid grid-cols-[100px_minmax(280px,1fr)_220px_120px_120px] items-center gap-4 border-b border-slate-100 px-6 py-5 last:border-b-0 dark:border-slate-800"
                                >
                                    <div
                                        class="size-8 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div class="flex items-center gap-3">
                                        <div
                                            class="size-12 animate-pulse rounded-xl bg-slate-200 dark:bg-slate-800"
                                        />

                                        <div class="space-y-2">
                                            <div
                                                class="h-4 w-40 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                            />
                                            <div
                                                class="h-3 w-28 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        class="h-4 w-32 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-7 w-16 animate-pulse rounded-full bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="ml-auto h-9 w-24 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Pagination skeleton -->
                        <div
                            class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                        >
                            <div
                                class="h-4 w-44 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="flex gap-2">
                                <div
                                    class="h-9 w-20 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                                <div
                                    class="h-9 w-9 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                                <div
                                    class="h-9 w-9 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                                <div
                                    class="h-9 w-20 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                            </div>
                        </div>
                    </div>

                    <span class="sr-only"> Memuat data anak usaha... </span>
                </div>

                <!-- ================================================= -->
                <!-- CONTENT -->
                <!-- ================================================= -->
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
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0b1f3a] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-[#0b1f3a]/15 transition duration-200 hover:-translate-y-0.5 hover:bg-[#163b68] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:bg-white dark:text-[#0b1f3a] dark:hover:bg-slate-100 sm:w-auto"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />
                            Tambah Anak Usaha
                        </button>
                    </div>

                    <!-- MAIN CARD -->
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-900/[0.04] backdrop-blur-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
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
                                <!-- SEARCH -->
                                <div class="relative w-full sm:w-64">
                                    <Search
                                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    />

                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Cari anak usaha..."
                                        class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-9 pr-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-white dark:focus:border-blue-500 dark:focus:bg-slate-800"
                                    />
                                </div>

                                <!-- FILTER -->
                                <select
                                    v-model="statusFilter"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200 dark:focus:border-blue-500 dark:focus:bg-slate-800 sm:w-36"
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
                                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#0b1f3a] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#163b68] dark:bg-white dark:text-[#0b1f3a] dark:hover:bg-slate-100"
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
                                        class="group transition-colors duration-150 hover:bg-blue-50/40 dark:hover:bg-blue-950/10"
                                    >
                                        <!-- URUTAN -->
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

                                        <!-- ANAK USAHA -->
                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 text-slate-400 transition-colors group-hover:border-blue-100 group-hover:bg-white dark:border-slate-700 dark:bg-slate-800 dark:group-hover:border-blue-900/50 dark:group-hover:bg-slate-800"
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
                                                class="inline-flex max-w-[220px] items-center gap-1.5 text-sm font-medium text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
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
                                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-950/60'
                                                        : 'border-slate-200 bg-slate-100 text-slate-500 hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700'
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

                                        <!-- AKSI -->
                                        <td class="px-6 py-4">
                                            <div
                                                class="flex justify-end gap-1 opacity-100 transition-opacity"
                                            >
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
                                class="flex max-w-full items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0"
                            >
                                <button
                                    type="button"
                                    :disabled="!previousPageUrl"
                                    class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
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
                                                ? 'bg-[#0b1f3a] text-white shadow-sm dark:bg-white dark:text-[#0b1f3a]'
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
                                    class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
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
        <!-- MODAL FORM -->
        <!-- ========================================================= -->
        <Transition name="modal-fade">
            <div
                v-if="showFormModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeForm"
            >
                <div
                    class="modal-panel max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- HEADER -->
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
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
                            :disabled="processingForm"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="closeForm"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- FORM -->
                    <form class="space-y-5 p-6" @submit.prevent="submitForm">
                        <!-- NAMA -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Nama Anak Usaha
                            </label>

                            <input
                                v-model="form.nama"
                                type="text"
                                placeholder="Contoh: PT KITB Properti"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <!-- LOGO -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Logo
                            </label>

                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-5 py-7 transition hover:border-blue-400 hover:bg-blue-50/30 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-blue-500 dark:hover:bg-blue-950/20"
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
                                    class="flex size-16 items-center justify-center overflow-hidden rounded-xl bg-white dark:bg-slate-800"
                                >
                                    <img
                                        :src="previewUrl"
                                        alt="Preview logo"
                                        class="size-full object-contain p-1"
                                    />
                                </div>

                                <div>
                                    <p
                                        class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{ form.logo?.name ?? "Logo saat ini" }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Preview logo
                                    </p>
                                </div>
                            </div>
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
                                rows="4"
                                placeholder="Masukkan deskripsi anak usaha..."
                                class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <!-- WEBSITE -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Website
                            </label>

                            <input
                                v-model="form.website"
                                type="url"
                                placeholder="https://www.example.com"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />

                            <p class="mt-1.5 text-xs text-slate-400">
                                Masukkan URL lengkap, contoh
                                https://www.example.com
                            </p>
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
                                    Aktif
                                </p>

                                <p class="text-xs text-slate-400">
                                    Tampilkan anak usaha sebagai data aktif.
                                </p>
                            </div>
                        </label>

                        <!-- BUTTON -->
                        <div
                            class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800"
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
                                :disabled="processingForm || !form.nama.trim()"
                                class="rounded-xl bg-[#0b1f3a] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#163b68] disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-[#0b1f3a] dark:hover:bg-slate-100"
                            >
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
        <!-- MODAL DETAIL -->
        <!-- ========================================================= -->
        <Transition name="modal-fade">
            <div
                v-if="showDetailModal && selectedItem"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeDetail"
            >
                <div
                    class="modal-panel max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
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
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
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

                        <!-- NAMA -->
                        <div class="text-center">
                            <h3
                                class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ selectedItem.nama }}
                            </h3>
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

                        <!-- DESKRIPSI -->
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
                                            : 'text-slate-500'
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
        <!-- MODAL DELETE -->
        <!-- ========================================================= -->
        <Transition name="modal-fade">
            <div
                v-if="showDeleteModal && selectedItem"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeDelete"
            >
                <div
                    class="modal-panel w-full max-w-md rounded-2xl border border-slate-200/80 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
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
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closeDelete"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="deleteItem"
                        >
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
/* =========================================================
   PAGE TRANSITION
   ========================================================= */

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

/* =========================================================
   MODAL TRANSITION
   ========================================================= */

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

/* =========================================================
   ACCESSIBILITY
   ========================================================= */

@media (prefers-reduced-motion: reduce) {
    .page-fade-enter-active,
    .page-fade-leave-active,
    .modal-fade-enter-active,
    .modal-fade-leave-active,
    .modal-panel {
        transition: none;
    }
}

/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 640px) {
    .page-fade-enter-from,
    .page-fade-leave-to {
        transform: none;
    }
}
</style>
