<script setup lang="ts">
import {
    ArrowDown,
    ArrowUp,
    Eye,
    ImagePlus,
    Pencil,
    Plus,
    Search,
    Trash2,
    Upload,
    Users,
    X,
} from "lucide-vue-next";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

interface Struktur {
    id: number;

    // Indonesia
    nama: string;
    jabatan: string;

    // English
    nama_en: string | null;
    jabatan_en: string | null;

    // Chinese
    nama_zh: string | null;
    jabatan_zh: string | null;

    gambar: string | null;
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

interface StrukturPagination {
    data: Struktur[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

interface Props {
    struktur: StrukturPagination;
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

const filteredStruktur = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    return [...props.struktur.data].filter((item) => {
        const matchesSearch =
            !keyword ||
            item.nama.toLowerCase().includes(keyword) ||
            item.jabatan.toLowerCase().includes(keyword) ||
            (item.nama_en ?? "").toLowerCase().includes(keyword) ||
            (item.jabatan_en ?? "").toLowerCase().includes(keyword) ||
            (item.nama_zh ?? "").toLowerCase().includes(keyword) ||
            (item.jabatan_zh ?? "").toLowerCase().includes(keyword);

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
    return props.struktur.links[0]?.url ?? null;
});

const nextPageUrl = computed(() => {
    return props.struktur.links[props.struktur.links.length - 1]?.url ?? null;
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

const selectedItem = ref<Struktur | null>(null);

const form = ref({
    // Indonesia
    nama: "",
    jabatan: "",

    // English
    nama_en: "",
    jabatan_en: "",

    // Chinese
    nama_zh: "",
    jabatan_zh: "",

    gambar: null as File | null,
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
    modalMode.value === "create"
        ? "Tambah Struktur Perusahaan"
        : "Edit Struktur Perusahaan",
);

const resetForm = () => {
    if (previewUrl.value?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl.value);
    }

    form.value = {
        nama: "",
        jabatan: "",

        nama_en: "",
        jabatan_en: "",

        nama_zh: "",
        jabatan_zh: "",

        gambar: null,
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

const openEdit = (item: Struktur) => {
    if (previewUrl.value?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl.value);
    }

    selectedItem.value = item;
    modalMode.value = "edit";

    form.value = {
        // Indonesia
        nama: item.nama,
        jabatan: item.jabatan,

        // English
        nama_en: item.nama_en ?? "",
        jabatan_en: item.jabatan_en ?? "",

        // Chinese
        nama_zh: item.nama_zh ?? "",
        jabatan_zh: item.jabatan_zh ?? "",

        gambar: null,
        aktif: item.aktif,
    };

    previewUrl.value = item.gambar ? `/storage/${item.gambar}` : null;

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

const openDetail = (item: Struktur) => {
    selectedItem.value = item;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
    selectedItem.value = null;
};

const openDelete = (item: Struktur) => {
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
        form.value.gambar = null;

        alert("Ukuran gambar maksimal 1 MB.");

        return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

    if (!allowedTypes.includes(file.type)) {
        target.value = "";
        form.value.gambar = null;

        alert("Format gambar harus JPG, JPEG, PNG, atau WEBP.");

        return;
    }

    if (previewUrl.value?.startsWith("blob:")) {
        URL.revokeObjectURL(previewUrl.value);
    }

    form.value.gambar = file;
    previewUrl.value = URL.createObjectURL(file);
};

const getImageUrl = (gambar: string | null) => {
    return gambar ? `/storage/${gambar}` : null;
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

    // Bahasa Indonesia wajib
    if (!form.value.nama.trim()) {
        return;
    }

    if (!form.value.jabatan.trim()) {
        return;
    }

    const formData = new FormData();

    /*
    |--------------------------------------------------------------------------
    | Indonesia
    |--------------------------------------------------------------------------
    */

    formData.append("nama", form.value.nama.trim());

    formData.append("jabatan", form.value.jabatan.trim());

    /*
    |--------------------------------------------------------------------------
    | English
    |--------------------------------------------------------------------------
    */

    formData.append("nama_en", form.value.nama_en.trim());

    formData.append("jabatan_en", form.value.jabatan_en.trim());

    /*
    |--------------------------------------------------------------------------
    | Chinese
    |--------------------------------------------------------------------------
    */

    formData.append("nama_zh", form.value.nama_zh.trim());

    formData.append("jabatan_zh", form.value.jabatan_zh.trim());

    /*
    |--------------------------------------------------------------------------
    | Other
    |--------------------------------------------------------------------------
    */

    formData.append("aktif", form.value.aktif ? "1" : "0");

    if (form.value.gambar) {
        formData.append("gambar", form.value.gambar);
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    if (modalMode.value === "edit" && selectedItem.value) {
        formData.append("_method", "PUT");

        router.post(
            `/admin/profil-perusahaan/struktur-perusahaan/${selectedItem.value.id}`,
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
                    console.error(
                        "Gagal memperbarui struktur perusahaan:",
                        errors,
                    );
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

    router.post("/admin/profil-perusahaan/struktur-perusahaan", formData, {
        forceFormData: true,
        preserveScroll: true,

        onStart: () => {
            processingForm.value = true;
        },

        onSuccess: () => {
            closeForm();
        },

        onError: (errors) => {
            console.error("Gagal menambahkan struktur perusahaan:", errors);
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
        `/admin/profil-perusahaan/struktur-perusahaan/${selectedItem.value.id}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                showDeleteModal.value = false;
                selectedItem.value = null;
            },

            onError: (errors) => {
                console.error("Gagal menghapus struktur perusahaan:", errors);
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

const toggleAktif = (item: Struktur) => {
    if (processingToggleId.value !== null) {
        return;
    }

    processingToggleId.value = item.id;

    router.patch(
        `/admin/profil-perusahaan/struktur-perusahaan/${item.id}/toggle-aktif`,
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

const moveItem = (item: Struktur, direction: "up" | "down") => {
    if (movingId.value !== null) {
        return;
    }

    if (direction === "up" && item.urutan <= 1) {
        return;
    }

    if (direction === "down" && item.urutan >= props.struktur.total) {
        return;
    }

    movingId.value = item.id;

    router.patch(
        `/admin/profil-perusahaan/struktur-perusahaan/${item.id}/move`,
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
    >
        <!-- ===================================================== -->
        <!-- DECORATIVE BACKGROUND -->
        <!-- ===================================================== -->

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
                <!-- ===================================================== -->
                <!-- SKELETON -->
                <!-- ===================================================== -->

                <div
                    v-if="isPageLoading"
                    key="skeleton"
                    class="mx-auto w-full max-w-[1600px] animate-pulse space-y-5 p-4 sm:p-5 lg:p-6 xl:p-8"
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
                                    class="h-5 w-48 rounded-lg bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-72 max-w-[70vw] rounded-lg bg-slate-200 dark:bg-slate-800"
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
                            class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 sm:px-6 dark:border-slate-800"
                        >
                            <div class="space-y-2">
                                <div
                                    class="h-5 w-44 rounded-lg bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-4 w-64 max-w-full rounded-lg bg-slate-200 dark:bg-slate-800"
                                />
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row">
                                <div
                                    class="h-10 w-full rounded-xl bg-slate-200 sm:w-56 dark:bg-slate-800"
                                />

                                <div
                                    class="h-10 w-full rounded-xl bg-slate-200 sm:w-32 dark:bg-slate-800"
                                />
                            </div>
                        </div>

                        <div class="space-y-4 p-5 sm:p-6">
                            <div
                                v-for="i in 6"
                                :key="i"
                                class="flex items-center gap-4"
                            >
                                <div
                                    class="size-12 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"
                                />

                                <div class="min-w-0 flex-1 space-y-2">
                                    <div
                                        class="h-4 w-48 max-w-full rounded-lg bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-3 w-32 rounded-lg bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>

                                <div
                                    class="hidden h-8 w-20 rounded-full bg-slate-200 sm:block dark:bg-slate-800"
                                />

                                <div
                                    class="hidden h-8 w-28 rounded-lg bg-slate-200 sm:block dark:bg-slate-800"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- CONTENT -->
                <!-- ===================================================== -->

                <div
                    v-else
                    key="content"
                    class="mx-auto w-full max-w-[1600px] space-y-5 p-4 sm:p-5 lg:p-6 xl:p-8"
                >
                    <!-- HEADER -->

                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-sm shadow-blue-500/20"
                            >
                                <Users class="size-5" />
                            </div>

                            <div class="min-w-0">
                                <h1
                                    class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                                >
                                    Struktur Perusahaan
                                </h1>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Kelola struktur organisasi dan jabatan
                                    perusahaan.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-500/10 transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto dark:focus-visible:ring-offset-slate-950"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />
                            Tambah Struktur
                        </button>
                    </div>

                    <!-- MAIN CARD -->

                    <div
                        class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/50 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
                    >
                        <!-- CARD HEADER -->

                        <div
                            class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800"
                        >
                            <div class="min-w-0">
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Daftar Struktur
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ props.struktur.total }}
                                    data struktur perusahaan.
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
                                        placeholder="Cari nama/jabatan..."
                                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                    />
                                </div>

                                <!-- STATUS -->

                                <select
                                    v-model="statusFilter"
                                    class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                >
                                    <option value="all">Semua Status</option>

                                    <option value="active">Aktif</option>

                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <!-- EMPTY -->

                        <div
                            v-if="filteredStruktur.length === 0"
                            class="px-5 py-16 text-center sm:px-6"
                        >
                            <div
                                class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800"
                            >
                                <Users class="size-5" />
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
                                        : "Tambahkan struktur perusahaan untuk mulai mengisi halaman ini."
                                }}
                            </p>
                        </div>

                        <!-- TABLE -->

                        <div v-else class="overflow-x-auto">
                            <table class="w-full min-w-[850px] text-left">
                                <thead
                                    class="border-b border-slate-200 bg-slate-50/70 dark:border-slate-800 dark:bg-slate-950/30"
                                >
                                    <tr>
                                        <th
                                            class="whitespace-nowrap px-6 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Urutan
                                        </th>

                                        <th
                                            class="whitespace-nowrap px-6 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Nama
                                        </th>

                                        <th
                                            class="whitespace-nowrap px-6 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Jabatan
                                        </th>

                                        <th
                                            class="whitespace-nowrap px-6 py-3.5 text-xs font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Status
                                        </th>

                                        <th
                                            class="whitespace-nowrap px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800"
                                >
                                    <tr
                                        v-for="item in filteredStruktur"
                                        :key="item.id"
                                        class="transition-colors hover:bg-blue-50/40 dark:hover:bg-blue-950/10"
                                    >
                                        <!-- URUTAN -->

                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-1"
                                            >
                                                <span
                                                    class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
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
                                                        class="rounded-lg p-1 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-blue-950/40"
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
                                                                props.struktur
                                                                    .total ||
                                                            movingId !== null
                                                        "
                                                        class="rounded-lg p-1 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-blue-950/40"
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

                                        <!-- NAMA -->

                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800"
                                                >
                                                    <img
                                                        v-if="
                                                            getImageUrl(
                                                                item.gambar,
                                                            )
                                                        "
                                                        :src="
                                                            getImageUrl(
                                                                item.gambar,
                                                            )!
                                                        "
                                                        :alt="item.nama"
                                                        class="size-full object-cover"
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
                                                </div>
                                            </div>
                                        </td>

                                        <!-- JABATAN -->

                                        <td class="px-6 py-4">
                                            <div class="space-y-0.5 text-sm">
                                                <p
                                                    class="text-slate-600 dark:text-slate-300"
                                                >
                                                    {{ item.jabatan }}
                                                </p>

                                                <p
                                                    v-if="item.jabatan_en"
                                                    class="text-xs text-slate-400"
                                                >
                                                    {{ item.jabatan_en }}
                                                </p>
                                            </div>
                                        </td>

                                        <!-- STATUS -->

                                        <td class="px-6 py-4">
                                            <button
                                                type="button"
                                                :disabled="
                                                    processingToggleId ===
                                                    item.id
                                                "
                                                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-50"
                                                :class="
                                                    item.aktif
                                                        ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                        : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400'
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
                                            <div class="flex justify-end gap-1">
                                                <button
                                                    type="button"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                    title="Lihat detail"
                                                    @click="openDetail(item)"
                                                >
                                                    <Eye class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                                    title="Edit"
                                                    @click="openEdit(item)"
                                                >
                                                    <Pencil class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                                    title="Hapus"
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
                            v-if="props.struktur.last_page > 1"
                            class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                        >
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Menampilkan

                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ props.struktur.from ?? 0 }}
                                </span>

                                -

                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ props.struktur.to ?? 0 }}
                                </span>

                                dari

                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ props.struktur.total }}
                                </span>

                                data
                            </p>

                            <div
                                class="flex max-w-full items-center gap-1.5 overflow-x-auto pb-1"
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
                                    ) in props.struktur.links.slice(1, -1)"
                                    :key="index"
                                >
                                    <button
                                        v-if="link.url"
                                        type="button"
                                        class="min-w-9 shrink-0 rounded-lg px-3 py-2 text-sm font-medium transition"
                                        :class="
                                            link.active
                                                ? 'bg-blue-600 text-white shadow-sm'
                                                : 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
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

            <!-- ========================================================= -->
            <!-- MODAL FORM -->
            <!-- ========================================================= -->

            <div
                v-if="showFormModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeForm"
            >
                <div
                    class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- HEADER -->

                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                {{ formTitle }}
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Lengkapi informasi struktur perusahaan dalam
                                tiga bahasa.
                            </p>
                        </div>

                        <button
                            type="button"
                            :disabled="processingForm"
                            class="shrink-0 rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800"
                            @click="closeForm"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- FORM -->

                    <form
                        class="space-y-6 p-5 sm:p-6"
                        @submit.prevent="submitForm"
                    >
                        <!-- ================================================= -->
                        <!-- INDONESIA -->
                        <!-- ================================================= -->

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-800/40"
                        >
                            <div class="mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg" aria-hidden="true">
                                        🇮🇩
                                    </span>

                                    <div>
                                        <h3
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            Bahasa Indonesia
                                        </h3>

                                        <p class="text-xs text-slate-400">
                                            Bahasa utama
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <!-- NAMA ID -->

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Nama
                                        <span class="text-red-500"> * </span>
                                    </label>

                                    <input
                                        v-model="form.nama"
                                        type="text"
                                        placeholder="Contoh: Budi Santoso"
                                        required
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>

                                <!-- JABATAN ID -->

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Jabatan
                                        <span class="text-red-500"> * </span>
                                    </label>

                                    <input
                                        v-model="form.jabatan"
                                        type="text"
                                        placeholder="Contoh: Direktur Utama"
                                        required
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- ================================================= -->
                        <!-- ENGLISH -->
                        <!-- ================================================= -->

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-800/40"
                        >
                            <div class="mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg" aria-hidden="true">
                                        🇬🇧
                                    </span>

                                    <div>
                                        <h3
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            English
                                        </h3>

                                        <p class="text-xs text-slate-400">
                                            Bahasa Inggris
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <!-- NAMA EN -->

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Name
                                    </label>

                                    <input
                                        v-model="form.nama_en"
                                        type="text"
                                        placeholder="Example: Budi Santoso"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>

                                <!-- JABATAN EN -->

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Position
                                    </label>

                                    <input
                                        v-model="form.jabatan_en"
                                        type="text"
                                        placeholder="Example: President Director"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- ================================================= -->
                        <!-- CHINESE -->
                        <!-- ================================================= -->

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-800/40"
                        >
                            <div class="mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg" aria-hidden="true">
                                        🇨🇳
                                    </span>

                                    <div>
                                        <h3
                                            class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            中文
                                        </h3>

                                        <p class="text-xs text-slate-400">
                                            中文语言
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <!-- NAMA ZH -->

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        姓名
                                    </label>

                                    <input
                                        v-model="form.nama_zh"
                                        type="text"
                                        placeholder="例如：Budi Santoso"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>

                                <!-- JABATAN ZH -->

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        职位
                                    </label>

                                    <input
                                        v-model="form.jabatan_zh"
                                        type="text"
                                        placeholder="例如：总裁"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- ================================================= -->
                        <!-- GAMBAR -->
                        <!-- ================================================= -->

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Foto
                            </label>

                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-5 py-8 transition hover:border-blue-400 hover:bg-blue-50/30 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-blue-500"
                            >
                                <Upload class="size-6 text-slate-400" />

                                <span
                                    class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-300"
                                >
                                    Klik untuk memilih gambar
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
                                class="mt-4 flex items-center gap-4 rounded-2xl border border-slate-200 p-3 dark:border-slate-700"
                            >
                                <img
                                    :src="previewUrl"
                                    alt="Preview"
                                    class="size-16 shrink-0 rounded-xl object-cover"
                                />

                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{
                                            form.gambar?.name ??
                                            "Gambar saat ini"
                                        }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        Preview gambar
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ================================================= -->
                        <!-- STATUS -->
                        <!-- ================================================= -->

                        <label
                            class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800/50"
                        >
                            <input
                                v-model="form.aktif"
                                type="checkbox"
                                class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />

                            <div>
                                <p
                                    class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                >
                                    Aktif
                                </p>

                                <p class="text-xs text-slate-400">
                                    Tampilkan data sebagai struktur aktif.
                                </p>
                            </div>
                        </label>

                        <!-- BUTTON -->

                        <div
                            class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end sm:gap-3 dark:border-slate-800"
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
                                :disabled="
                                    processingForm ||
                                    !form.nama.trim() ||
                                    !form.jabatan.trim()
                                "
                                class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
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

            <!-- ========================================================= -->
            <!-- MODAL DETAIL -->
            <!-- ========================================================= -->

            <div
                v-if="showDetailModal && selectedItem"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeDetail"
            >
                <div
                    class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Struktur
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap anggota struktur perusahaan.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="shrink-0 rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-800"
                            @click="closeDetail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <div class="space-y-5 p-5 sm:p-6">
                        <!-- FOTO -->

                        <div class="flex justify-center">
                            <div
                                class="flex size-28 items-center justify-center overflow-hidden rounded-3xl bg-slate-100 text-slate-400 shadow-inner dark:bg-slate-800"
                            >
                                <img
                                    v-if="getImageUrl(selectedItem.gambar)"
                                    :src="getImageUrl(selectedItem.gambar)!"
                                    :alt="selectedItem.nama"
                                    class="size-full object-cover"
                                />

                                <ImagePlus v-else class="size-8" />
                            </div>
                        </div>

                        <!-- INDONESIA -->

                        <div class="text-center">
                            <p
                                class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-400"
                            >
                                🇮🇩 Indonesia
                            </p>

                            <h3
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                {{ selectedItem.nama }}
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ selectedItem.jabatan }}
                            </p>
                        </div>

                        <!-- TRANSLATIONS -->

                        <div class="grid gap-3 sm:grid-cols-2">
                            <!-- ENGLISH -->

                            <div
                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"
                            >
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                >
                                    🇬🇧 English
                                </p>

                                <p
                                    class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    {{
                                        selectedItem.nama_en ||
                                        selectedItem.nama
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        selectedItem.jabatan_en ||
                                        selectedItem.jabatan
                                    }}
                                </p>
                            </div>

                            <!-- CHINESE -->

                            <div
                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"
                            >
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                >
                                    🇨🇳 中文
                                </p>

                                <p
                                    class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    {{
                                        selectedItem.nama_zh ||
                                        selectedItem.nama_en ||
                                        selectedItem.nama
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        selectedItem.jabatan_zh ||
                                        selectedItem.jabatan_en ||
                                        selectedItem.jabatan
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- META -->

                        <div class="grid grid-cols-2 gap-3">
                            <div
                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"
                            >
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
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
                                class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60"
                            >
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
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

            <!-- ========================================================= -->
            <!-- MODAL DELETE -->
            <!-- ========================================================= -->

            <div
                v-if="showDeleteModal && selectedItem"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeDelete"
            >
                <div
                    class="w-full max-w-md rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xl sm:p-6 dark:border-slate-800 dark:bg-slate-900"
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
                            Hapus Struktur Perusahaan?
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
                            class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-left dark:border-slate-700 dark:bg-slate-800"
                        >
                            <p
                                class="text-sm font-medium text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedItem.jabatan }}
                            </p>

                            <p
                                v-if="selectedItem.jabatan_en"
                                class="mt-1 text-xs text-slate-400"
                            >
                                {{ selectedItem.jabatan_en }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3"
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
                            class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="deleteItem"
                        >
                            {{
                                processingDelete ? "Menghapus..." : "Ya, Hapus"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
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

@media (prefers-reduced-motion: reduce) {
    .page-fade-enter-active,
    .page-fade-leave-active {
        transition: none;
    }
}
</style>
