<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
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
    Languages,
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
    tentang_kami_en: string | null;
    tentang_kami_zh: string | null;

    latar_belakang: string | null;
    latar_belakang_en: string | null;
    latar_belakang_zh: string | null;

    moto: string | null;
    moto_en: string | null;
    moto_zh: string | null;

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
        "/admin/profil-perusahaan/tentang-kami",
        {
            search: search.value.trim() || undefined,
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
        "/admin/profil-perusahaan/tentang-kami",
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

interface CompanyProfileForm {
    nama_perusahaan: string;

    tentang_kami: string;
    tentang_kami_en: string;
    tentang_kami_zh: string;

    latar_belakang: string;
    latar_belakang_en: string;
    latar_belakang_zh: string;

    moto: string;
    moto_en: string;
    moto_zh: string;

    alamat: string;
    email: string;
    telepon: string;
    website: string;

    aktif: boolean;
}

const emptyForm = (): CompanyProfileForm => ({
    nama_perusahaan: "",

    tentang_kami: "",
    tentang_kami_en: "",
    tentang_kami_zh: "",

    latar_belakang: "",
    latar_belakang_en: "",
    latar_belakang_zh: "",

    moto: "",
    moto_en: "",
    moto_zh: "",

    alamat: "",
    email: "",
    telepon: "",
    website: "",

    aktif: true,
});

const form = ref<CompanyProfileForm>(emptyForm());

const logoFile = ref<File | null>(null);
const logoPreview = ref<string | null>(null);

const processingForm = ref(false);
const processingDelete = ref(false);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const truncate = (text: string | null | undefined, length = 100): string => {
    if (!text?.trim()) {
        return "-";
    }

    const value = text.trim();

    return value.length > length ? `${value.substring(0, length)}...` : value;
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

const getLogoUrl = (logo: string | null | undefined): string | null => {
    if (!logo?.trim()) {
        return null;
    }

    const value = logo.trim();

    if (/^https?:\/\//i.test(value)) {
        return value;
    }

    if (value.startsWith("/storage/")) {
        return value;
    }

    if (value.startsWith("/")) {
        return value;
    }

    return `/storage/${value}`;
};

const revokeLogoPreview = () => {
    if (logoPreview.value?.startsWith("blob:")) {
        URL.revokeObjectURL(logoPreview.value);
    }

    logoPreview.value = null;
};

const firstValidationError = (
    errors: Record<string, string | string[]>,
): string => {
    const first = Object.values(errors)[0];

    if (Array.isArray(first)) {
        return first[0] ?? "Terjadi kesalahan validasi.";
    }

    return first || "Terjadi kesalahan validasi.";
};

const hasTranslation = (value: string | null | undefined): boolean => {
    return Boolean(value?.trim());
};

const translationCount = (
    id: string | null | undefined,
    en: string | null | undefined,
    zh: string | null | undefined,
): number => {
    return [id, en, zh].filter(hasTranslation).length;
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
    logoFile.value = null;

    modalMode.value = "create";

    showDetail.value = false;
    showDelete.value = false;
    showModal.value = true;
};

const openEdit = (profile: CompanyProfile) => {
    revokeLogoPreview();

    selectedProfile.value = profile;

    form.value = {
        nama_perusahaan: profile.nama_perusahaan,

        tentang_kami: profile.tentang_kami ?? "",
        tentang_kami_en: profile.tentang_kami_en ?? "",
        tentang_kami_zh: profile.tentang_kami_zh ?? "",

        latar_belakang: profile.latar_belakang ?? "",
        latar_belakang_en: profile.latar_belakang_en ?? "",
        latar_belakang_zh: profile.latar_belakang_zh ?? "",

        moto: profile.moto ?? "",
        moto_en: profile.moto_en ?? "",
        moto_zh: profile.moto_zh ?? "",

        alamat: profile.alamat ?? "",
        email: profile.email ?? "",
        telepon: profile.telepon ?? "",
        website: profile.website ?? "",

        aktif: Boolean(profile.aktif),
    };

    logoFile.value = null;

    modalMode.value = "edit";

    showDetail.value = false;
    showDelete.value = false;
    showModal.value = true;
};

const closeModal = () => {
    if (processingForm.value) {
        return;
    }

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
    if (processingDelete.value) {
        return;
    }

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

        toast.error("Format logo harus PNG, JPG, JPEG, atau WEBP.");

        return;
    }

    if (file.size > maxSize) {
        logoFile.value = null;
        target.value = "";

        toast.error("Ukuran logo maksimal 2 MB.");

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
    if (processingForm.value) {
        return;
    }

    const namaPerusahaan = form.value.nama_perusahaan.trim();

    if (!namaPerusahaan) {
        toast.error("Nama perusahaan wajib diisi.");
        return;
    }

    const data = new FormData();

    /*
    |--------------------------------------------------------------------------
    | Identitas
    |--------------------------------------------------------------------------
    */

    data.append("nama_perusahaan", namaPerusahaan);

    /*
    |--------------------------------------------------------------------------
    | Tentang Kami
    |--------------------------------------------------------------------------
    */

    data.append("tentang_kami", form.value.tentang_kami.trim());

    data.append("tentang_kami_en", form.value.tentang_kami_en.trim());

    data.append("tentang_kami_zh", form.value.tentang_kami_zh.trim());

    /*
    |--------------------------------------------------------------------------
    | Latar Belakang
    |--------------------------------------------------------------------------
    */

    data.append("latar_belakang", form.value.latar_belakang.trim());

    data.append("latar_belakang_en", form.value.latar_belakang_en.trim());

    data.append("latar_belakang_zh", form.value.latar_belakang_zh.trim());

    /*
    |--------------------------------------------------------------------------
    | Moto
    |--------------------------------------------------------------------------
    */

    data.append("moto", form.value.moto.trim());

    data.append("moto_en", form.value.moto_en.trim());

    data.append("moto_zh", form.value.moto_zh.trim());

    /*
    |--------------------------------------------------------------------------
    | Kontak
    |--------------------------------------------------------------------------
    */

    data.append("alamat", form.value.alamat.trim());

    data.append("email", form.value.email.trim());

    data.append("telepon", form.value.telepon.trim());

    data.append("website", normalizeWebsite(form.value.website));

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    data.append("aktif", form.value.aktif ? "1" : "0");

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    */

    if (logoFile.value) {
        data.append("logo", logoFile.value);
    }

    processingForm.value = true;

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    if (modalMode.value === "create") {
        router.post("/admin/profil-perusahaan/tentang-kami", data, {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                closeModal();

                toast.success("Profil perusahaan berhasil ditambahkan.");
            },

            onError: (errors) => {
                toast.error(firstValidationError(errors));

                console.error("Gagal menambahkan profil:", errors);
            },

            onFinish: () => {
                processingForm.value = false;
            },
        });

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if (!selectedProfile.value) {
        processingForm.value = false;

        toast.error("Data profil tidak ditemukan.");

        return;
    }

    data.append("_method", "PUT");

    router.post(
        `/admin/profil-perusahaan/tentang-kami/${selectedProfile.value.id}`,
        data,
        {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                closeModal();

                toast.success("Profil perusahaan berhasil diperbarui.");
            },

            onError: (errors) => {
                toast.error(firstValidationError(errors));

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
        `/admin/profil-perusahaan/tentang-kami/${selectedProfile.value.id}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                showDelete.value = false;

                selectedProfile.value = null;

                toast.success("Profil perusahaan berhasil dihapus.");
            },

            onError: (errors) => {
                toast.error(firstValidationError(errors));

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
        class="relative min-h-full overflow-hidden bg-slate-50/50 transition-colors duration-300 dark:bg-slate-950/50"
    >
        <!-- =========================================================
             DECORATIVE BACKGROUND
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
             MAIN CONTENT
        ========================================================== -->
        <div
            class="relative z-10 mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <!-- HEADER -->
            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <Building2 class="size-5" />
                    </div>

                    <div class="min-w-0">
                        <h1
                            class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-2xl"
                        >
                            Tentang Kami
                        </h1>

                        <p
                            class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Kelola informasi profil perusahaan dalam Bahasa
                            Indonesia, English, dan 中文.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/20 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:ring-offset-2 focus:ring-offset-slate-50 dark:focus:ring-offset-slate-950"
                    @click="openCreate"
                >
                    <Plus class="size-4" />
                    Tambah Profil
                </button>
            </div>

            <!-- FILTER -->
            <div
                class="mb-5 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm shadow-slate-200/40 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
            >
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama, moto, konten, atau email..."
                            class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900"
                        />
                    </div>

                    <select
                        v-model="status"
                        class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
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

            <!-- TABLE -->
            <div
                class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/50 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1050px] text-left text-sm">
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
                                    Bahasa
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

                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="profile in profiles.data"
                                :key="profile.id"
                                class="transition-colors duration-200 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                            >
                                <!-- Perusahaan -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:ring-blue-900/40"
                                        >
                                            <img
                                                v-if="profile.logo"
                                                :src="getLogoUrl(profile.logo)!"
                                                :alt="profile.nama_perusahaan"
                                                class="size-full object-contain p-1.5"
                                            />

                                            <Building2 v-else class="size-5" />
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="truncate font-semibold text-slate-900 dark:text-white"
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
                                <td class="max-w-md px-6 py-4">
                                    <p
                                        class="line-clamp-3 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ truncate(profile.tentang_kami) }}
                                    </p>
                                </td>

                                <!-- Moto -->
                                <td class="px-6 py-4">
                                    <span
                                        class="line-clamp-2 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ profile.moto || "-" }}
                                    </span>
                                </td>

                                <!-- Bahasa -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span
                                            class="rounded-md px-2 py-1 text-[10px] font-semibold"
                                            :class="
                                                hasTranslation(
                                                    profile.tentang_kami,
                                                )
                                                    ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400'
                                                    : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600'
                                            "
                                        >
                                            ID
                                        </span>

                                        <span
                                            class="rounded-md px-2 py-1 text-[10px] font-semibold"
                                            :class="
                                                hasTranslation(
                                                    profile.tentang_kami_en,
                                                )
                                                    ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400'
                                                    : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600'
                                            "
                                        >
                                            EN
                                        </span>

                                        <span
                                            class="rounded-md px-2 py-1 text-[10px] font-semibold"
                                            :class="
                                                hasTranslation(
                                                    profile.tentang_kami_zh,
                                                )
                                                    ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400'
                                                    : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600'
                                            "
                                        >
                                            中文
                                        </span>
                                    </div>

                                    <p
                                        class="mt-1.5 text-[11px] text-slate-400"
                                    >
                                        {{
                                            translationCount(
                                                profile.tentang_kami,
                                                profile.tentang_kami_en,
                                                profile.tentang_kami_zh,
                                            )
                                        }}/3 terisi
                                    </p>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="
                                            profile.aktif
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                        "
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="
                                                profile.aktif
                                                    ? 'bg-emerald-500'
                                                    : 'bg-slate-400'
                                            "
                                        ></span>

                                        {{
                                            profile.aktif ? "Aktif" : "Nonaktif"
                                        }}
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-1">
                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Lihat detail"
                                            @click="openDetail(profile)"
                                        >
                                            <Eye class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                            title="Edit profil"
                                            @click="openEdit(profile)"
                                        >
                                            <Pencil class="size-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
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
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600"
                                    >
                                        <Building2 class="size-7" />
                                    </div>

                                    <p
                                        class="mt-4 font-semibold text-slate-700 dark:text-slate-300"
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

                <!-- Pagination -->
                <div
                    v-if="profiles.total > 0"
                    class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
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
             MODAL TAMBAH / EDIT
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
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40"
                    >
                        <div>
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                >
                                    <Building2 class="size-4" />
                                </div>

                                <h2
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    {{
                                        modalMode === "create"
                                            ? "Tambah Profil Perusahaan"
                                            : "Edit Profil Perusahaan"
                                    }}
                                </h2>
                            </div>

                            <p
                                class="mt-1 pl-11 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Lengkapi informasi perusahaan dan terjemahan
                                konten.
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

                    <!-- Form -->
                    <form
                        class="max-h-[78vh] overflow-y-auto p-6"
                        @submit.prevent="submitForm"
                    >
                        <div class="space-y-7">
                            <!-- =================================================
                                 IDENTITAS
                            ================================================== -->
                            <section>
                                <div class="mb-4 flex items-center gap-3">
                                    <div
                                        class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        <Building2 class="size-4" />
                                    </div>

                                    <div>
                                        <h3
                                            class="text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Identitas Perusahaan
                                        </h3>

                                        <p class="text-xs text-slate-400">
                                            Informasi utama perusahaan.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <!-- Nama -->
                                    <div class="md:col-span-2">
                                        <label
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Nama Perusahaan
                                            <span
                                                class="ml-0.5 font-bold text-red-500"
                                                aria-hidden="true"
                                            >
                                                *
                                            </span>
                                        </label>

                                        <input
                                            v-model="form.nama_perusahaan"
                                            type="text"
                                            required
                                            maxlength="255"
                                            placeholder="Contoh: PT Kawasan Industri Tanjung Buton"
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        />

                                        <p
                                            class="mt-1.5 text-xs text-slate-400"
                                        >
                                            Nama resmi perusahaan yang
                                            ditampilkan di website.
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <!-- =================================================
                                 MOTO
                            ================================================== -->
                            <section
                                class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5 dark:border-slate-800 dark:bg-slate-800/30"
                            >
                                <div
                                    class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <Languages
                                                class="size-4 text-blue-600 dark:text-blue-400"
                                            />

                                            <h3
                                                class="text-sm font-semibold text-slate-900 dark:text-white"
                                            >
                                                Moto Perusahaan
                                            </h3>
                                        </div>

                                        <p
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Isi moto dalam tiga bahasa agar
                                            tampil sesuai bahasa website.
                                        </p>
                                    </div>

                                    <span
                                        class="inline-flex w-fit items-center rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        3 Bahasa
                                    </span>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-3">
                                    <!-- ID -->
                                    <div>
                                        <label
                                            class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            <span
                                                class="rounded-md bg-blue-100 px-1.5 py-0.5 text-[10px] text-blue-700 dark:bg-blue-950/50 dark:text-blue-400"
                                            >
                                                ID
                                            </span>

                                            Bahasa Indonesia
                                        </label>

                                        <input
                                            v-model="form.moto"
                                            type="text"
                                            maxlength="255"
                                            placeholder="Menghubungkan Industri, Logistik, dan Peluang Investasi."
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                        />
                                    </div>

                                    <!-- EN -->
                                    <div>
                                        <label
                                            class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            <span
                                                class="rounded-md bg-indigo-100 px-1.5 py-0.5 text-[10px] text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400"
                                            >
                                                EN
                                            </span>

                                            English
                                        </label>

                                        <input
                                            v-model="form.moto_en"
                                            type="text"
                                            maxlength="255"
                                            placeholder="Connecting Industry, Logistics, and Investment Opportunities."
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                        />
                                    </div>

                                    <!-- ZH -->
                                    <div>
                                        <label
                                            class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            <span
                                                class="rounded-md bg-sky-100 px-1.5 py-0.5 text-[10px] text-sky-700 dark:bg-sky-950/50 dark:text-sky-400"
                                            >
                                                中文
                                            </span>

                                            Chinese
                                        </label>

                                        <input
                                            v-model="form.moto_zh"
                                            type="text"
                                            maxlength="255"
                                            placeholder="连接产业、物流与投资机遇。"
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                        />
                                    </div>
                                </div>
                            </section>

                            <!-- =================================================
                                 TENTANG KAMI
                            ================================================== -->
                            <section
                                class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5 dark:border-slate-800 dark:bg-slate-800/30"
                            >
                                <div
                                    class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <Languages
                                                class="size-4 text-blue-600 dark:text-blue-400"
                                            />

                                            <h3
                                                class="text-sm font-semibold text-slate-900 dark:text-white"
                                            >
                                                Tentang Kami
                                            </h3>
                                        </div>

                                        <p
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Jelaskan siapa perusahaan, bidang
                                            usaha, dan aktivitas utama
                                            perusahaan.
                                        </p>
                                    </div>

                                    <span
                                        class="inline-flex w-fit items-center rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        3 Bahasa
                                    </span>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-3">
                                    <!-- ID -->
                                    <div>
                                        <label
                                            class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            <span
                                                class="rounded-md bg-blue-100 px-1.5 py-0.5 text-[10px] text-blue-700 dark:bg-blue-950/50 dark:text-blue-400"
                                            >
                                                ID
                                            </span>

                                            Bahasa Indonesia
                                        </label>

                                        <textarea
                                            v-model="form.tentang_kami"
                                            rows="8"
                                            maxlength="10000"
                                            placeholder="PT Kawasan Industri Tanjung Buton (KITB) merupakan perusahaan pengelola kawasan industri..."
                                            class="w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                        ></textarea>

                                        <p
                                            class="mt-1.5 text-[11px] text-slate-400"
                                        >
                                            Versi utama / fallback website.
                                        </p>
                                    </div>

                                    <!-- EN -->
                                    <div>
                                        <label
                                            class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            <span
                                                class="rounded-md bg-indigo-100 px-1.5 py-0.5 text-[10px] text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400"
                                            >
                                                EN
                                            </span>

                                            English
                                        </label>

                                        <textarea
                                            v-model="form.tentang_kami_en"
                                            rows="8"
                                            maxlength="10000"
                                            placeholder="PT Kawasan Industri Tanjung Buton (KITB) is an industrial estate management company..."
                                            class="w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                        ></textarea>

                                        <p
                                            class="mt-1.5 text-[11px] text-slate-400"
                                        >
                                            Digunakan ketika website menggunakan
                                            English.
                                        </p>
                                    </div>

                                    <!-- ZH -->
                                    <div>
                                        <label
                                            class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            <span
                                                class="rounded-md bg-sky-100 px-1.5 py-0.5 text-[10px] text-sky-700 dark:bg-sky-950/50 dark:text-sky-400"
                                            >
                                                中文
                                            </span>

                                            Chinese
                                        </label>

                                        <textarea
                                            v-model="form.tentang_kami_zh"
                                            rows="8"
                                            maxlength="10000"
                                            placeholder="PT Kawasan Industri Tanjung Buton（KITB）是一家工业园区管理公司..."
                                            class="w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                        ></textarea>

                                        <p
                                            class="mt-1.5 text-[11px] text-slate-400"
                                        >
                                            Digunakan ketika website menggunakan
                                            中文.
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="mt-4 flex items-start gap-2 rounded-xl border border-amber-100 bg-amber-50/70 px-3.5 py-3 dark:border-amber-900/30 dark:bg-amber-950/20"
                                >
                                    <Languages
                                        class="mt-0.5 size-4 shrink-0 text-amber-600 dark:text-amber-400"
                                    />

                                    <p
                                        class="text-xs leading-5 text-amber-700 dark:text-amber-400"
                                    >
                                        Jika versi English atau 中文
                                        dikosongkan, website otomatis
                                        menggunakan versi Bahasa Indonesia.
                                    </p>
                                </div>
                            </section>

                            <!-- =================================================
                                 LATAR BELAKANG
                            ================================================== -->
                            <section
                                class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5 dark:border-slate-800 dark:bg-slate-800/30"
                            >
                                <div
                                    class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <Languages
                                                class="size-4 text-blue-600 dark:text-blue-400"
                                            />

                                            <h3
                                                class="text-sm font-semibold text-slate-900 dark:text-white"
                                            >
                                                Latar Belakang
                                            </h3>
                                        </div>

                                        <p
                                            class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            Jelaskan sejarah, alasan, konteks,
                                            atau dasar pengembangan perusahaan /
                                            kawasan.
                                        </p>
                                    </div>

                                    <span
                                        class="inline-flex w-fit items-center rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        3 Bahasa
                                    </span>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-3">
                                    <!-- ID -->
                                    <div>
                                        <label
                                            class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            <span
                                                class="rounded-md bg-blue-100 px-1.5 py-0.5 text-[10px] text-blue-700 dark:bg-blue-950/50 dark:text-blue-400"
                                            >
                                                ID
                                            </span>

                                            Bahasa Indonesia
                                        </label>

                                        <textarea
                                            v-model="form.latar_belakang"
                                            rows="8"
                                            maxlength="10000"
                                            placeholder="Kawasan Industri Tanjung Buton dikembangkan dengan mempertimbangkan posisi strategis wilayah..."
                                            class="w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                        ></textarea>
                                    </div>

                                    <!-- EN -->
                                    <div>
                                        <label
                                            class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            <span
                                                class="rounded-md bg-indigo-100 px-1.5 py-0.5 text-[10px] text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400"
                                            >
                                                EN
                                            </span>

                                            English
                                        </label>

                                        <textarea
                                            v-model="form.latar_belakang_en"
                                            rows="8"
                                            maxlength="10000"
                                            placeholder="The Tanjung Buton Industrial Estate was developed by considering the strategic position of the region..."
                                            class="w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                        ></textarea>
                                    </div>

                                    <!-- ZH -->
                                    <div>
                                        <label
                                            class="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            <span
                                                class="rounded-md bg-sky-100 px-1.5 py-0.5 text-[10px] text-sky-700 dark:bg-sky-950/50 dark:text-sky-400"
                                            >
                                                中文
                                            </span>

                                            Chinese
                                        </label>

                                        <textarea
                                            v-model="form.latar_belakang_zh"
                                            rows="8"
                                            maxlength="10000"
                                            placeholder="丹绒布东工业园区的开发充分考虑了该地区的战略地理位置..."
                                            class="w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                                        ></textarea>
                                    </div>
                                </div>

                                <p class="mt-4 text-xs text-slate-400">
                                    Versi English dan 中文 bersifat opsional.
                                    Jika kosong, website menggunakan versi
                                    Indonesia.
                                </p>
                            </section>

                            <!-- =================================================
                                 KONTAK
                            ================================================== -->
                            <section>
                                <div class="mb-4 flex items-center gap-3">
                                    <div
                                        class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        <Building2 class="size-4" />
                                    </div>

                                    <div>
                                        <h3
                                            class="text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Kontak & Lokasi
                                        </h3>

                                        <p class="text-xs text-slate-400">
                                            Informasi yang dapat digunakan
                                            pengunjung untuk menghubungi
                                            perusahaan.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
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
                                            maxlength="255"
                                            autocomplete="email"
                                            placeholder="Contoh: info@kitb.co.id"
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        />
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
                                            maxlength="50"
                                            autocomplete="tel"
                                            placeholder="Contoh: +62 761 123 456"
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        />
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
                                            maxlength="255"
                                            placeholder="https://tanjungbuton-industrial.co.id"
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        />
                                    </div>

                                    <!-- Alamat -->
                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Alamat Perusahaan
                                        </label>

                                        <textarea
                                            v-model="form.alamat"
                                            rows="3"
                                            maxlength="500"
                                            placeholder="Kampung Mengkapan, Kecamatan Sungai Apit, Kabupaten Siak, Provinsi Riau."
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                        ></textarea>
                                    </div>
                                </div>
                            </section>

                            <!-- =================================================
                                 LOGO
                            ================================================== -->
                            <section>
                                <div class="mb-4 flex items-center gap-3">
                                    <div
                                        class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        <Upload class="size-4" />
                                    </div>

                                    <div>
                                        <h3
                                            class="text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Logo Perusahaan
                                        </h3>

                                        <p class="text-xs text-slate-400">
                                            Gunakan logo dengan kualitas yang
                                            baik.
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-800/50"
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
                                    Pada mode edit, logo lama tetap digunakan
                                    jika tidak memilih file baru.
                                </p>

                                <!-- New Preview -->
                                <div
                                    v-if="logoPreview"
                                    class="mt-3 flex items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50/60 p-3 dark:border-blue-900/40 dark:bg-blue-950/20"
                                >
                                    <img
                                        :src="logoPreview"
                                        alt="Preview logo baru"
                                        class="size-16 rounded-xl border border-slate-200 bg-white object-contain p-2 dark:border-slate-700"
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
                                    class="mt-3 flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <img
                                        :src="getLogoUrl(selectedProfile.logo)!"
                                        alt="Logo perusahaan"
                                        class="size-16 rounded-xl border border-slate-200 bg-white object-contain p-2 dark:border-slate-700"
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
                            </section>

                            <!-- =================================================
                                 STATUS
                            ================================================== -->
                            <section
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50"
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
                                            Profil aktif dapat digunakan pada
                                            halaman publik website.
                                        </p>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- Footer -->
                        <div
                            class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800"
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
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-md"
                @click.self="closeDetail"
            >
                <div
                    class="w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Header -->
                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40"
                    >
                        <div>
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                >
                                    <Eye class="size-4" />
                                </div>

                                <h2
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    Detail Profil Perusahaan
                                </h2>
                            </div>

                            <p
                                class="mt-1 pl-11 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap perusahaan dan konten
                                multilingual.
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

                    <!-- Content -->
                    <div class="max-h-[75vh] space-y-6 overflow-y-auto p-6">
                        <!-- Identity -->
                        <div
                            class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <div
                                class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700"
                            >
                                <img
                                    v-if="selectedProfile.logo"
                                    :src="getLogoUrl(selectedProfile.logo)!"
                                    :alt="selectedProfile.nama_perusahaan"
                                    class="size-full object-contain p-2"
                                />

                                <Building2
                                    v-else
                                    class="size-8 text-blue-500"
                                />
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3
                                        class="text-lg font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ selectedProfile.nama_perusahaan }}
                                    </h3>

                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="
                                            selectedProfile.aktif
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                        "
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="
                                                selectedProfile.aktif
                                                    ? 'bg-emerald-500'
                                                    : 'bg-slate-400'
                                            "
                                        ></span>

                                        {{
                                            selectedProfile.aktif
                                                ? "Aktif"
                                                : "Nonaktif"
                                        }}
                                    </span>
                                </div>

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

                        <!-- Moto -->
                        <section>
                            <div class="mb-3 flex items-center gap-2">
                                <Languages
                                    class="size-4 text-blue-600 dark:text-blue-400"
                                />

                                <h3
                                    class="text-sm font-semibold text-slate-900 dark:text-white"
                                >
                                    Moto Perusahaan
                                </h3>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-3">
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <span
                                        class="rounded-md bg-blue-100 px-1.5 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        ID
                                    </span>

                                    <p
                                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ selectedProfile.moto || "-" }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <span
                                        class="rounded-md bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400"
                                    >
                                        EN
                                    </span>

                                    <p
                                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ selectedProfile.moto_en || "-" }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <span
                                        class="rounded-md bg-sky-100 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700 dark:bg-sky-950/50 dark:text-sky-400"
                                    >
                                        中文
                                    </span>

                                    <p
                                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ selectedProfile.moto_zh || "-" }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Tentang -->
                        <section>
                            <div class="mb-3 flex items-center gap-2">
                                <Languages
                                    class="size-4 text-blue-600 dark:text-blue-400"
                                />

                                <h3
                                    class="text-sm font-semibold text-slate-900 dark:text-white"
                                >
                                    Tentang Kami
                                </h3>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-3">
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <span
                                        class="rounded-md bg-blue-100 px-1.5 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        ID
                                    </span>

                                    <p
                                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{
                                            selectedProfile.tentang_kami || "-"
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <span
                                        class="rounded-md bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400"
                                    >
                                        EN
                                    </span>

                                    <p
                                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{
                                            selectedProfile.tentang_kami_en ||
                                            "-"
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <span
                                        class="rounded-md bg-sky-100 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700 dark:bg-sky-950/50 dark:text-sky-400"
                                    >
                                        中文
                                    </span>

                                    <p
                                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{
                                            selectedProfile.tentang_kami_zh ||
                                            "-"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Latar Belakang -->
                        <section>
                            <div class="mb-3 flex items-center gap-2">
                                <Languages
                                    class="size-4 text-blue-600 dark:text-blue-400"
                                />

                                <h3
                                    class="text-sm font-semibold text-slate-900 dark:text-white"
                                >
                                    Latar Belakang
                                </h3>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-3">
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <span
                                        class="rounded-md bg-blue-100 px-1.5 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        ID
                                    </span>

                                    <p
                                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{
                                            selectedProfile.latar_belakang ||
                                            "-"
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <span
                                        class="rounded-md bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400"
                                    >
                                        EN
                                    </span>

                                    <p
                                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{
                                            selectedProfile.latar_belakang_en ||
                                            "-"
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <span
                                        class="rounded-md bg-sky-100 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700 dark:bg-sky-950/50 dark:text-sky-400"
                                    >
                                        中文
                                    </span>

                                    <p
                                        class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{
                                            selectedProfile.latar_belakang_zh ||
                                            "-"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Contact -->
                        <section>
                            <div class="mb-3 flex items-center gap-2">
                                <Building2
                                    class="size-4 text-blue-600 dark:text-blue-400"
                                />

                                <h3
                                    class="text-sm font-semibold text-slate-900 dark:text-white"
                                >
                                    Kontak & Informasi
                                </h3>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Email
                                    </p>

                                    <p
                                        class="mt-2 break-all text-sm text-slate-700 dark:text-slate-200"
                                    >
                                        {{ selectedProfile.email || "-" }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Telepon
                                    </p>

                                    <p
                                        class="mt-2 text-sm text-slate-700 dark:text-slate-200"
                                    >
                                        {{ selectedProfile.telepon || "-" }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
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
                                        class="mt-2 inline-flex items-center gap-1.5 break-all text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        {{ selectedProfile.website }}

                                        <ExternalLink
                                            class="size-3.5 shrink-0"
                                        />
                                    </a>

                                    <p
                                        v-else
                                        class="mt-2 text-sm text-slate-700 dark:text-slate-200"
                                    >
                                        -
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Alamat
                                    </p>

                                    <p
                                        class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ selectedProfile.alamat || "-" }}
                                    </p>
                                </div>
                            </div>
                        </section>
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
                            ?

                            <br />

                            Data yang sudah dihapus tidak dapat dikembalikan.
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
/* ==========================================================================
   Page / Modal Transition
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
