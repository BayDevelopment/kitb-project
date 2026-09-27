<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import {
    Building2,
    Search,
    RotateCcw,
    Pencil,
    Eye,
    Plus,
    X,
    Trash2,
    Upload,
    ExternalLink,
} from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

/*
|--------------------------------------------------------------------------
| Interfaces
|--------------------------------------------------------------------------
*/

interface CompanyProfile {
    id: number;
    nama_perusahaan: string;
    tentang_kami: string | null;
    latar_belakang: string | null;
    moto: string | null;
    alamat: string | null;
    email: string | null;
    telepon: string | null;
    website: string | null;
    logo: string | null;
    aktif: boolean;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData {
    data: CompanyProfile[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
}

interface Props {
    profiles: PaginatedData;
    filters: {
        search: string;
        status: string;
    };
}

const props = defineProps<Props>();

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const search = ref(props.filters.search ?? "");
const status = ref(props.filters.status ?? "");

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const applyFilter = () => {
    router.get(
        "/profil-perusahaan/tentang-kami",
        {
            search: search.value || undefined,
            status: status.value || undefined,
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
    status.value = "";

    router.get(
        "/profil-perusahaan/tentang-kami",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const hasFilter = computed(() => {
    return search.value !== "" || status.value !== "";
});

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const goToPage = (url: string | null) => {
    if (!url) return;

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

const showModal = ref(false);
const showDetail = ref(false);
const showDelete = ref(false);

const modalMode = ref<"create" | "edit">("create");

const selectedProfile = ref<CompanyProfile | null>(null);

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const emptyForm = () => ({
    nama_perusahaan: "",
    tentang_kami: "",
    latar_belakang: "",
    moto: "",
    alamat: "",
    email: "",
    telepon: "",
    website: "",
    logo: "",
    aktif: true,
});

const form = ref(emptyForm());

const logoFile = ref<File | null>(null);
const logoPreview = ref<string | null>(null);

const processingForm = ref(false);
const processingDelete = ref(false);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const truncate = (text: string | null, length = 100) => {
    if (!text) return "-";

    return text.length > length ? `${text.substring(0, length)}...` : text;
};

const normalizeWebsite = (website: string): string => {
    const value = website.trim();

    if (!value) {
        return "";
    }

    if (/^https?:\/\//i.test(value)) {
        return value;
    }

    return `https://${value}`;
};

const getLogoUrl = (logo: string | null) => {
    if (!logo) return null;

    return `/storage/${logo}`;
};

const revokeLogoPreview = () => {
    if (logoPreview.value?.startsWith("blob:")) {
        URL.revokeObjectURL(logoPreview.value);
    }

    logoPreview.value = null;
};

/*
|--------------------------------------------------------------------------
| Modal Actions
|--------------------------------------------------------------------------
*/

const openCreate = () => {
    revokeLogoPreview();

    form.value = emptyForm();
    selectedProfile.value = null;
    modalMode.value = "create";
    logoFile.value = null;

    showDetail.value = false;
    showDelete.value = false;
    showModal.value = true;
};

const openEdit = (profile: CompanyProfile) => {
    revokeLogoPreview();

    selectedProfile.value = profile;

    form.value = {
        nama_perusahaan: profile.nama_perusahaan,
        tentang_kami: profile.tentang_kami || "",
        latar_belakang: profile.latar_belakang || "",
        moto: profile.moto || "",
        alamat: profile.alamat || "",
        email: profile.email || "",
        telepon: profile.telepon || "",
        website: profile.website || "",
        logo: profile.logo || "",
        aktif: profile.aktif,
    };

    logoFile.value = null;
    modalMode.value = "edit";

    showDetail.value = false;
    showDelete.value = false;
    showModal.value = true;
};

const closeModal = () => {
    if (processingForm.value) return;

    showModal.value = false;

    revokeLogoPreview();

    form.value = emptyForm();
    logoFile.value = null;
    selectedProfile.value = null;
};

const openDetail = (profile: CompanyProfile) => {
    selectedProfile.value = profile;

    showModal.value = false;
    showDelete.value = false;
    showDetail.value = true;
};

const closeDetail = () => {
    showDetail.value = false;
    selectedProfile.value = null;
};

const openDelete = (profile: CompanyProfile) => {
    selectedProfile.value = profile;

    showModal.value = false;
    showDetail.value = false;
    showDelete.value = true;
};

const closeDelete = () => {
    if (processingDelete.value) return;

    showDelete.value = false;
    selectedProfile.value = null;
};

/*
|--------------------------------------------------------------------------
| Logo Upload
|--------------------------------------------------------------------------
*/

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    revokeLogoPreview();

    if (!file) {
        logoFile.value = null;
        return;
    }

    const allowedTypes = ["image/png", "image/jpeg", "image/webp"];

    const maxSize = 2 * 1024 * 1024;

    if (!allowedTypes.includes(file.type)) {
        logoFile.value = null;
        target.value = "";

        window.alert("Format logo harus PNG, JPG, JPEG, atau WEBP.");

        return;
    }

    if (file.size > maxSize) {
        logoFile.value = null;
        target.value = "";

        window.alert("Ukuran logo maksimal 2 MB.");

        return;
    }

    logoFile.value = file;
    logoPreview.value = URL.createObjectURL(file);
};

/*
|--------------------------------------------------------------------------
| Submit Form
|--------------------------------------------------------------------------
*/

const submitForm = () => {
    if (processingForm.value) return;

    const namaPerusahaan = form.value.nama_perusahaan.trim();

    if (!namaPerusahaan) {
        return;
    }

    const data = new FormData();

    data.append("nama_perusahaan", namaPerusahaan);
    data.append("tentang_kami", form.value.tentang_kami.trim());
    data.append("latar_belakang", form.value.latar_belakang.trim());
    data.append("moto", form.value.moto.trim());
    data.append("alamat", form.value.alamat.trim());
    data.append("email", form.value.email.trim());
    data.append("telepon", form.value.telepon.trim());
    data.append("website", normalizeWebsite(form.value.website));
    data.append("aktif", form.value.aktif ? "1" : "0");

    if (logoFile.value) {
        data.append("logo", logoFile.value);
    }

    processingForm.value = true;

    if (modalMode.value === "create") {
        router.post("/profil-perusahaan/tentang-kami", data, {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                closeModal();
            },

            onError: (errors) => {
                console.error("Gagal menambahkan profil:", errors);
            },

            onFinish: () => {
                processingForm.value = false;
            },
        });

        return;
    }

    if (!selectedProfile.value) {
        processingForm.value = false;
        return;
    }

    data.append("_method", "PUT");

    router.post(
        `/profil-perusahaan/tentang-kami/${selectedProfile.value.id}`,
        data,
        {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                closeModal();
            },

            onError: (errors) => {
                console.error("Gagal memperbarui profil:", errors);
            },

            onFinish: () => {
                processingForm.value = false;
            },
        },
    );
};

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteProfile = () => {
    if (!selectedProfile.value || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(
        `/profil-perusahaan/tentang-kami/${selectedProfile.value.id}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                showDelete.value = false;
                selectedProfile.value = null;
            },

            onError: (errors) => {
                console.error("Gagal menghapus profil:", errors);
            },

            onFinish: () => {
                processingDelete.value = false;
            },
        },
    );
};

/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
    clearTimeout(searchTimeout);
    revokeLogoPreview();
});
</script>

<template>
    <div
        class="relative min-h-full overflow-hidden bg-slate-50/50 p-4 sm:p-6 dark:bg-slate-950/50"
    >
        <!-- =========================================================
             DECORATIVE BACKGROUND
        ========================================================== -->

        <div
            class="pointer-events-none absolute inset-x-0 top-0 z-0 h-80 overflow-hidden"
            aria-hidden="true"
        >
            <!-- Blob kiri -->
            <div
                class="blob-shape absolute -left-24 -top-32 size-96 rounded-full bg-gradient-to-br from-blue-400/30 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15 dark:to-transparent"
            ></div>

            <!-- Blob kanan -->
            <div
                class="blob-shape-delayed absolute -right-20 top-4 size-80 rounded-full bg-gradient-to-tr from-sky-300/30 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <!-- Blob tengah -->
            <div
                class="blob-shape-slow absolute left-1/3 -top-40 size-72 rounded-full bg-gradient-to-br from-indigo-300/20 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <!-- Grid halus -->
            <div class="absolute inset-0 opacity-40 dark:opacity-20">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px]"
                ></div>
            </div>

            <!-- Fade bawah -->
            <div
                class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-b from-transparent to-slate-50/80 dark:to-slate-950/80"
            ></div>
        </div>

        <!-- =========================================================
             MAIN CONTENT
        ========================================================== -->

        <div class="relative z-10">
            <!-- =====================================================
                 HEADER
            ====================================================== -->

            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-600/10 text-blue-600 shadow-sm dark:bg-blue-400/10 dark:text-blue-400"
                    >
                        <Building2 class="size-5" />
                    </div>

                    <div>
                        <h1
                            class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                        >
                            Tentang Kami
                        </h1>

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Kelola informasi profil perusahaan.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                    @click="openCreate"
                >
                    <Plus class="size-4" />
                    Tambah Profil
                </button>
            </div>

            <!-- =====================================================
                 FILTER
            ====================================================== -->

            <div
                class="mb-5 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/90"
            >
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <!-- Search -->
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama perusahaan, moto, atau email..."
                            class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900"
                        />
                    </div>

                    <!-- Status -->
                    <select
                        v-model="status"
                        class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">Semua Status</option>

                        <option value="1">Aktif</option>

                        <option value="0">Nonaktif</option>
                    </select>

                    <!-- Reset -->
                    <button
                        v-if="hasFilter"
                        type="button"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
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
                class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 shadow-sm backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <!-- Table Header -->
                        <thead
                            class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <tr>
                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Perusahaan
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Tentang Kami
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Moto
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 text-right font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="profile in profiles.data"
                                :key="profile.id"
                                class="transition-colors hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                            >
                                <!-- Perusahaan -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:ring-blue-900/40"
                                        >
                                            <img
                                                v-if="profile.logo"
                                                :src="getLogoUrl(profile.logo)!"
                                                :alt="profile.nama_perusahaan"
                                                class="size-full object-contain p-1"
                                            />

                                            <Building2 v-else class="size-5" />
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="truncate font-medium text-slate-900 dark:text-white"
                                            >
                                                {{ profile.nama_perusahaan }}
                                            </p>

                                            <p
                                                class="truncate text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{ profile.email || "-" }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tentang -->
                                <td
                                    class="max-w-md px-6 py-4 text-slate-600 dark:text-slate-300"
                                >
                                    {{ truncate(profile.tentang_kami) }}
                                </td>

                                <!-- Moto -->
                                <td class="px-6 py-4">
                                    <span
                                        class="text-slate-600 dark:text-slate-300"
                                    >
                                        {{ profile.moto || "-" }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="
                                            profile.aktif
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                        "
                                    >
                                        {{
                                            profile.aktif ? "Aktif" : "Nonaktif"
                                        }}
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-1">
                                        <!-- Detail -->
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Lihat detail"
                                            @click="openDetail(profile)"
                                        >
                                            <Eye class="size-4" />
                                        </button>

                                        <!-- Edit -->
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                            title="Edit profil"
                                            @click="openEdit(profile)"
                                        >
                                            <Pencil class="size-4" />
                                        </button>

                                        <!-- Delete -->
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                            title="Hapus profil"
                                            @click="openDelete(profile)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty -->
                            <tr v-if="profiles.data.length === 0">
                                <td colspan="5" class="px-6 py-14 text-center">
                                    <Building2
                                        class="mx-auto mb-3 size-10 text-slate-300 dark:text-slate-700"
                                    />

                                    <p
                                        class="font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        Data tidak ditemukan
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
                    v-if="profiles.total > 0"
                    class="flex flex-col gap-4 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Menampilkan

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ profiles.from }}
                        </span>

                        -

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ profiles.to }}
                        </span>

                        dari

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ profiles.total }}
                        </span>

                        data
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="(link, index) in profiles.links"
                            :key="index"
                            type="button"
                            :disabled="!link.url"
                            class="min-w-9 rounded-lg px-3 py-2 text-sm transition"
                            :class="
                                link.active
                                    ? 'bg-blue-600 text-white shadow-sm'
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
             MODAL TAMBAH / EDIT
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeModal"
            >
                <div
                    class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
                    >
                        <div>
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    modalMode === "create"
                                        ? "Tambah Profil Perusahaan"
                                        : "Edit Profil Perusahaan"
                                }}
                            </h2>

                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Lengkapi informasi profil perusahaan.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                            :disabled="processingForm"
                            @click="closeModal"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Form -->
                    <form
                        class="max-h-[75vh] overflow-y-auto p-6"
                        @submit.prevent="submitForm"
                    >
                        <div class="grid gap-5 md:grid-cols-2">
                            <!-- Nama -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Nama Perusahaan
                                    <span class="text-red-500"> * </span>
                                </label>

                                <input
                                    v-model="form.nama_perusahaan"
                                    type="text"
                                    required
                                    placeholder="Contoh: PT Kawasan Industri Tanjung Buton"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Masukkan nama resmi perusahaan yang akan
                                    ditampilkan di website.
                                </p>
                            </div>

                            <!-- Moto -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Moto Perusahaan
                                </label>

                                <input
                                    v-model="form.moto"
                                    type="text"
                                    placeholder="Contoh: Menghubungkan Industri, Logistik, dan Peluang Investasi."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Isi dengan slogan atau moto resmi
                                    perusahaan.
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Email Perusahaan
                                </label>

                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Contoh: info@kitb.co.id"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Gunakan email resmi yang dapat dihubungi.
                                </p>
                            </div>

                            <!-- Telepon -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Nomor Telepon
                                </label>

                                <input
                                    v-model="form.telepon"
                                    type="text"
                                    placeholder="Contoh: +62 761 123 456"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Nomor telepon atau layanan resmi perusahaan.
                                </p>
                            </div>

                            <!-- Website -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Website Perusahaan
                                </label>

                                <input
                                    v-model="form.website"
                                    type="url"
                                    inputmode="url"
                                    autocomplete="url"
                                    placeholder="https://kitb.co.id"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Masukkan alamat website resmi perusahaan.
                                </p>
                            </div>

                            <!-- Alamat -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Alamat Perusahaan
                                </label>

                                <textarea
                                    v-model="form.alamat"
                                    rows="3"
                                    placeholder="Contoh: Kampung Mengkapan, Kecamatan Sungai Apit, Kabupaten Siak, Provinsi Riau."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                ></textarea>

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Tuliskan alamat lengkap kantor atau lokasi
                                    perusahaan.
                                </p>
                            </div>

                            <!-- Tentang -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Tentang Kami
                                </label>

                                <textarea
                                    v-model="form.tentang_kami"
                                    rows="6"
                                    placeholder="Contoh: PT Kawasan Industri Tanjung Buton (KITB) merupakan perusahaan pengelola kawasan industri..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                ></textarea>

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Jelaskan secara singkat siapa perusahaan
                                    ini, bidangnya, dan apa yang dilakukan.
                                </p>
                            </div>

                            <!-- Latar Belakang -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Latar Belakang
                                </label>

                                <textarea
                                    v-model="form.latar_belakang"
                                    rows="6"
                                    placeholder="Contoh: Kawasan Industri Tanjung Buton dikembangkan dengan mempertimbangkan posisi strategis wilayah..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                ></textarea>

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Jelaskan alasan, sejarah singkat, atau dasar
                                    pengembangan perusahaan/kawasan.
                                </p>
                            </div>

                            <!-- Logo -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Logo Perusahaan
                                </label>

                                <div
                                    class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 transition dark:border-slate-700 dark:bg-slate-800/50"
                                >
                                    <div
                                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700"
                                            >
                                                <Upload class="size-5" />
                                            </div>

                                            <div>
                                                <p
                                                    class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                                >
                                                    Upload logo perusahaan
                                                </p>

                                                <p
                                                    class="text-xs text-slate-500 dark:text-slate-400"
                                                >
                                                    PNG, JPG, JPEG, atau WEBP —
                                                    maksimal 2 MB.
                                                </p>
                                            </div>
                                        </div>

                                        <label
                                            class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                                        >
                                            Pilih File

                                            <input
                                                type="file"
                                                accept="image/png,image/jpeg,image/webp"
                                                class="hidden"
                                                @change="handleLogoChange"
                                            />
                                        </label>
                                    </div>
                                </div>

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Pilih file baru jika ingin mengganti logo.
                                </p>

                                <!-- New Preview -->
                                <div
                                    v-if="logoPreview"
                                    class="mt-3 flex items-center gap-4 rounded-xl border border-blue-100 bg-blue-50/60 p-3 dark:border-blue-900/40 dark:bg-blue-950/20"
                                >
                                    <img
                                        :src="logoPreview"
                                        alt="Preview logo"
                                        class="size-16 rounded-lg border border-slate-200 bg-white object-contain p-2 dark:border-slate-700"
                                    />

                                    <div class="min-w-0">
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Preview Logo Baru
                                        </p>

                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Logo baru akan digunakan setelah
                                            disimpan.
                                        </p>
                                    </div>
                                </div>

                                <!-- Existing Logo -->
                                <div
                                    v-else-if="
                                        modalMode === 'edit' &&
                                        selectedProfile?.logo
                                    "
                                    class="mt-3 flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <img
                                        :src="getLogoUrl(selectedProfile.logo)!"
                                        alt="Logo perusahaan"
                                        class="size-16 rounded-lg border border-slate-200 bg-white object-contain p-2 dark:border-slate-700"
                                    />

                                    <div>
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Logo Saat Ini
                                        </p>

                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Pilih file baru jika ingin mengganti
                                            logo.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div
                                class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50"
                            >
                                <div class="flex items-start gap-3">
                                    <input
                                        id="aktif"
                                        v-model="form.aktif"
                                        type="checkbox"
                                        class="mt-0.5 size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800"
                                    />

                                    <div>
                                        <label
                                            for="aktif"
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Tampilkan profil sebagai aktif
                                        </label>

                                        <p
                                            class="mt-1 text-xs leading-5 text-slate-400"
                                        >
                                            Jika dicentang, profil dapat
                                            ditampilkan dan digunakan pada
                                            bagian website yang membutuhkan
                                            informasi perusahaan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800"
                        >
                            <button
                                type="button"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                :disabled="processingForm"
                                @click="closeModal"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="
                                    processingForm ||
                                    !form.nama_perusahaan.trim()
                                "
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span
                                    v-if="processingForm"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                {{
                                    processingForm
                                        ? "Menyimpan..."
                                        : modalMode === "create"
                                          ? "Simpan Profil"
                                          : "Simpan Perubahan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- =========================================================
             MODAL DETAIL
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showDetail && selectedProfile"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeDetail"
            >
                <div
                    class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
                    >
                        <div>
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Profil Perusahaan
                            </h2>

                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap perusahaan.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                            @click="closeDetail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="max-h-[70vh] space-y-6 overflow-y-auto p-6">
                        <!-- Identity -->
                        <div
                            class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <div
                                class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700"
                            >
                                <img
                                    v-if="selectedProfile.logo"
                                    :src="getLogoUrl(selectedProfile.logo)!"
                                    :alt="selectedProfile.nama_perusahaan"
                                    class="size-full object-contain p-2"
                                />

                                <Building2
                                    v-else
                                    class="size-7 text-blue-500"
                                />
                            </div>

                            <div class="min-w-0">
                                <h3
                                    class="truncate text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ selectedProfile.nama_perusahaan }}
                                </h3>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        selectedProfile.email ||
                                        "Tidak ada email"
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Tentang -->
                        <div>
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-slate-400"
                            >
                                Tentang Kami
                            </p>

                            <p
                                class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedProfile.tentang_kami || "-" }}
                            </p>
                        </div>

                        <!-- Latar -->
                        <div>
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-slate-400"
                            >
                                Latar Belakang
                            </p>

                            <p
                                class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedProfile.latar_belakang || "-" }}
                            </p>
                        </div>

                        <!-- Detail -->
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                >
                                    Moto
                                </p>

                                <p
                                    class="mt-1 text-sm text-slate-700 dark:text-slate-200"
                                >
                                    {{ selectedProfile.moto || "-" }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                >
                                    Email
                                </p>

                                <p
                                    class="mt-1 break-all text-sm text-slate-700 dark:text-slate-200"
                                >
                                    {{ selectedProfile.email || "-" }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                >
                                    Telepon
                                </p>

                                <p
                                    class="mt-1 text-sm text-slate-700 dark:text-slate-200"
                                >
                                    {{ selectedProfile.telepon || "-" }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-slate-400"
                                >
                                    Website
                                </p>

                                <a
                                    v-if="selectedProfile.website"
                                    :href="
                                        normalizeWebsite(
                                            selectedProfile.website,
                                        )
                                    "
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-1 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    {{ selectedProfile.website }}

                                    <ExternalLink class="size-3.5" />
                                </a>

                                <p
                                    v-else
                                    class="mt-1 text-sm text-slate-700 dark:text-slate-200"
                                >
                                    -
                                </p>
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div>
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-slate-400"
                            >
                                Alamat
                            </p>

                            <p
                                class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedProfile.alamat || "-" }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-slate-400"
                            >
                                Status
                            </p>

                            <span
                                class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="
                                    selectedProfile.aktif
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                "
                            >
                                {{
                                    selectedProfile.aktif ? "Aktif" : "Nonaktif"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- =========================================================
             MODAL DELETE
        ========================================================== -->

        <Transition name="modal">
            <div
                v-if="showDelete && selectedProfile"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeDelete"
            >
                <div
                    class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900"
                >
                    <!-- Icon -->
                    <div
                        class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                    >
                        <Trash2 class="size-5" />
                    </div>

                    <!-- Text -->
                    <div class="mt-4 text-center">
                        <h2
                            class="text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            Hapus Profil?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus

                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedProfile.nama_perusahaan }}
                            </span>

                            ? Data yang sudah dihapus tidak dapat dikembalikan.
                        </p>
                    </div>

                    <!-- Buttons -->
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
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="deleteProfile"
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
/*
|--------------------------------------------------------------------------
| Page / Modal Transition
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Decorative Blobs
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Blob Animations
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Accessibility
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

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
