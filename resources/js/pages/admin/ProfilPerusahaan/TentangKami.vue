<script setup lang="ts">
import { computed, ref, watch, onBeforeUnmount } from "vue";
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
} from "lucide-vue-next";

import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

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

const props = defineProps<{
    profiles: PaginatedData;
    filters: {
        search: string;
        status: string;
    };
}>();

const search = ref(props.filters.search);
const status = ref(props.filters.status);

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

const hasFilter = computed(() => {
    return search.value !== "" || status.value !== "";
});

const showModal = ref(false);
const showDetail = ref(false);
const showDelete = ref(false);

const modalMode = ref<"create" | "edit">("create");

const selectedProfile = ref<CompanyProfile | null>(null);

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

const truncate = (text: string | null, length = 100) => {
    if (!text) return "-";

    return text.length > length ? `${text.substring(0, length)}...` : text;
};

const openCreate = () => {
    form.value = emptyForm();
    selectedProfile.value = null;
    modalMode.value = "create";
    logoFile.value = null;
    logoPreview.value = null;
    showModal.value = true;
};

const logoFile = ref<File | null>(null);
const logoPreview = ref<string | null>(null);

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
    }

    if (!target.files || !target.files[0]) {
        logoFile.value = null;
        logoPreview.value = null;
        return;
    }

    const file = target.files[0];

    if (!["image/png", "image/jpeg", "image/webp"].includes(file.type)) {
        logoFile.value = null;
        logoPreview.value = null;
        target.value = "";
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        logoFile.value = null;
        logoPreview.value = null;
        target.value = "";
        return;
    }

    logoFile.value = file;
    logoPreview.value = URL.createObjectURL(file);
};

const openEdit = (profile: CompanyProfile) => {
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

    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
    }

    logoPreview.value = null;
    modalMode.value = "edit";
    showModal.value = true;
};

const openDetail = (profile: CompanyProfile) => {
    selectedProfile.value = profile;
    showDetail.value = true;
};

const openDelete = (profile: CompanyProfile) => {
    selectedProfile.value = profile;
    showDelete.value = true;
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

const resetFormState = () => {
    showModal.value = false;
    selectedProfile.value = null;
    form.value = emptyForm();
    logoFile.value = null;

    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
    }

    logoPreview.value = null;
};

const submitForm = () => {
    const data = new FormData();

    data.append("nama_perusahaan", form.value.nama_perusahaan.trim());
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

    if (modalMode.value === "create") {
        router.post("/profil-perusahaan/tentang-kami", data, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                resetFormState();
            },
            onError: (errors) => {
                console.error("Gagal menambahkan profil:", errors);
            },
        });

        return;
    }

    if (!selectedProfile.value) {
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
                resetFormState();
            },
            onError: (errors) => {
                console.error("Gagal memperbarui profil:", errors);
            },
        },
    );
};

const deleteProfile = () => {
    if (!selectedProfile.value) return;

    router.delete(
        `/profil-perusahaan/tentang-kami/${selectedProfile.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                showDelete.value = false;
                selectedProfile.value = null;
            },
        },
    );
};

onBeforeUnmount(() => {
    clearTimeout(searchTimeout);

    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
    }
});
</script>

<template>
    <div class="min-h-full bg-slate-50/50 p-6 dark:bg-slate-950/50">
        <!-- ========================================================= -->
        <!-- HEADER -->
        <!-- ========================================================= -->

        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-xl bg-blue-600/10 text-blue-600 dark:bg-blue-400/10 dark:text-blue-400"
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

            <!-- TAMBAH -->
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                @click="openCreate"
            >
                <Plus class="size-4" />
                Tambah Profil
            </button>
        </div>

        <!-- ========================================================= -->
        <!-- FILTER -->
        <!-- ========================================================= -->

        <div
            class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <!-- SEARCH -->
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

                <!-- STATUS -->
                <select
                    v-model="status"
                    class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                >
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>

                <!-- RESET -->
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

        <!-- ========================================================= -->
        <!-- TABLE -->
        <!-- ========================================================= -->

        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <!-- HEADER TABLE -->
                    <thead
                        class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                    >
                        <tr>
                            <th class="px-6 py-4 font-semibold">Perusahaan</th>

                            <th class="px-6 py-4 font-semibold">
                                Tentang Kami
                            </th>

                            <th class="px-6 py-4 font-semibold">Moto</th>

                            <th class="px-6 py-4 font-semibold">Status</th>

                            <th class="px-6 py-4 text-right font-semibold">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <!-- BODY -->
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <tr
                            v-for="profile in profiles.data"
                            :key="profile.id"
                            class="transition-colors hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                        >
                            <!-- PERUSAHAAN -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        <img
                                            v-if="profile.logo"
                                            :src="`/storage/${profile.logo}`"
                                            :alt="profile.nama_perusahaan"
                                            class="size-full object-contain"
                                        />

                                        <Building2 v-else class="size-5" />
                                    </div>

                                    <div>
                                        <p
                                            class="font-medium text-slate-900 dark:text-white"
                                        >
                                            {{ profile.nama_perusahaan }}
                                        </p>

                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{ profile.email || "-" }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- TENTANG -->
                            <td
                                class="max-w-md px-6 py-4 text-slate-600 dark:text-slate-300"
                            >
                                {{ truncate(profile.tentang_kami) }}
                            </td>

                            <!-- MOTO -->
                            <td class="px-6 py-4">
                                <span
                                    class="text-slate-600 dark:text-slate-300"
                                >
                                    {{ profile.moto || "-" }}
                                </span>
                            </td>

                            <!-- STATUS -->
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="
                                        profile.aktif
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                >
                                    {{ profile.aktif ? "Aktif" : "Nonaktif" }}
                                </span>
                            </td>

                            <!-- AKSI -->
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-1">
                                    <!-- LIHAT -->
                                    <button
                                        type="button"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                        title="Lihat"
                                        @click="openDetail(profile)"
                                    >
                                        <Eye class="size-4" />
                                    </button>

                                    <!-- EDIT -->
                                    <button
                                        type="button"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                        title="Edit"
                                        @click="openEdit(profile)"
                                    >
                                        <Pencil class="size-4" />
                                    </button>

                                    <!-- HAPUS -->
                                    <button
                                        type="button"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                        title="Hapus"
                                        @click="openDelete(profile)"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- EMPTY -->
                        <tr v-if="profiles.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center">
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

            <!-- ===================================================== -->
            <!-- PAGINATION -->
            <!-- ===================================================== -->

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

                <div class="flex items-center gap-1">
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

    <!-- ============================================================= -->
    <!-- MODAL TAMBAH / EDIT -->
    <!-- ============================================================= -->

    <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="showModal = false"
    >
        <div
            class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
        >
            <!-- HEADER -->
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

                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Lengkapi informasi profil perusahaan.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                    @click="showModal = false"
                >
                    <X class="size-5" />
                </button>
            </div>

            <!-- FORM -->
            <form
                class="max-h-[75vh] overflow-y-auto p-6"
                @submit.prevent="submitForm"
            >
                <div class="grid gap-5 md:grid-cols-2">
                    <!-- NAMA PERUSAHAAN -->
                    <div class="md:col-span-2">
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Nama Perusahaan
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            v-model="form.nama_perusahaan"
                            type="text"
                            required
                            placeholder="Contoh: PT Kawasan Industri Tanjung Buton"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                        />

                        <p class="mt-1.5 text-xs text-slate-400">
                            Masukkan nama resmi perusahaan yang akan ditampilkan
                            di website.
                        </p>
                    </div>

                    <!-- MOTO -->
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
                            Isi dengan slogan atau moto resmi perusahaan.
                        </p>
                    </div>

                    <!-- EMAIL -->
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
                            Gunakan email resmi yang dapat dihubungi oleh calon
                            investor atau masyarakat.
                        </p>
                    </div>

                    <!-- TELEPON -->
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
                            Masukkan nomor telepon atau nomor layanan resmi
                            perusahaan.
                        </p>
                    </div>

                    <!-- WEBSITE -->
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

                    <!-- ALAMAT -->
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
                        />

                        <p class="mt-1.5 text-xs text-slate-400">
                            Tuliskan alamat lengkap kantor atau lokasi
                            perusahaan.
                        </p>
                    </div>

                    <!-- TENTANG KAMI -->
                    <div class="md:col-span-2">
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Tentang Kami
                        </label>

                        <textarea
                            v-model="form.tentang_kami"
                            rows="6"
                            placeholder="Contoh: PT Kawasan Industri Tanjung Buton (KITB) merupakan perusahaan pengelola kawasan industri yang dikembangkan untuk mendukung pertumbuhan industri, hilirisasi komoditas, serta aktivitas logistik dan perdagangan..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                        />

                        <p class="mt-1.5 text-xs text-slate-400">
                            Jelaskan secara singkat siapa perusahaan ini,
                            bidangnya, dan apa yang dilakukan.
                        </p>
                    </div>

                    <!-- LATAR BELAKANG -->
                    <div class="md:col-span-2">
                        <label
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Latar Belakang
                        </label>

                        <textarea
                            v-model="form.latar_belakang"
                            rows="6"
                            placeholder="Contoh: Kawasan Industri Tanjung Buton dikembangkan dengan mempertimbangkan posisi strategis wilayah yang berada di jalur pelayaran menuju Selat Malaka serta potensi sumber daya dan komoditas unggulan Provinsi Riau..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                        />

                        <p class="mt-1.5 text-xs text-slate-400">
                            Jelaskan alasan, sejarah singkat, atau dasar
                            pengembangan perusahaan/kawasan.
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200"
                        >
                            Logo Perusahaan
                        </label>

                        <input
                            type="file"
                            accept="image/png,image/jpeg"
                            @change="handleLogoChange"
                            class="block w-full cursor-pointer rounded-lg border border-slate-200 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:border-r file:border-slate-200 file:bg-slate-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:file:border-slate-700 dark:file:bg-slate-800 dark:file:text-slate-200"
                        />

                        <p
                            class="mt-1.5 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Upload logo perusahaan dalam format PNG, JPG, atau
                            WEBP. Maksimal 2 MB.
                        </p>

                        <!-- Preview logo baru -->
                        <div
                            v-if="logoPreview"
                            class="mt-3 flex items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900"
                        >
                            <img
                                :src="logoPreview"
                                alt="Preview logo"
                                class="h-16 w-16 rounded-lg border border-slate-200 bg-white object-contain p-2 dark:border-slate-700"
                            />

                            <div>
                                <p
                                    class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                >
                                    Preview Logo
                                </p>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Logo ini akan digunakan sebagai logo
                                    perusahaan.
                                </p>
                            </div>
                        </div>

                        <!-- Logo lama ketika edit -->
                        <div
                            v-else-if="
                                modalMode === 'edit' && selectedProfile?.logo
                            "
                            class="mt-3 flex items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900"
                        >
                            <img
                                :src="`/storage/${selectedProfile.logo}`"
                                alt="Logo perusahaan"
                                class="h-16 w-16 rounded-lg border border-slate-200 bg-white object-contain p-2 dark:border-slate-700"
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
                                    Pilih file baru jika ingin mengganti logo.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- STATUS -->
                    <div
                        class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50"
                    >
                        <div class="flex items-start gap-3">
                            <input
                                id="aktif"
                                v-model="form.aktif"
                                type="checkbox"
                                class="mt-0.5 size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
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
                                    Jika dicentang, profil dapat ditampilkan dan
                                    digunakan pada bagian website yang
                                    membutuhkan informasi perusahaan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div
                    class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800"
                >
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="showModal = false"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                    >
                        {{
                            modalMode === "create"
                                ? "Simpan Profil"
                                : "Simpan Perubahan"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL DETAIL -->
    <!-- ============================================================= -->

    <div
        v-if="showDetail && selectedProfile"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="showDetail = false"
    >
        <div
            class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
        >
            <!-- HEADER -->
            <div
                class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        Detail Profil Perusahaan
                    </h2>

                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Informasi lengkap perusahaan.
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                    @click="showDetail = false"
                >
                    <X class="size-5" />
                </button>
            </div>

            <!-- CONTENT -->
            <div class="max-h-[70vh] space-y-5 overflow-y-auto p-6">
                <!-- NAMA -->
                <div>
                    <p
                        class="text-xs font-medium uppercase tracking-wide text-slate-400"
                    >
                        Nama Perusahaan
                    </p>

                    <p
                        class="mt-1 text-base font-semibold text-slate-900 dark:text-white"
                    >
                        {{ selectedProfile.nama_perusahaan }}
                    </p>
                </div>

                <!-- TENTANG -->
                <div>
                    <p
                        class="text-xs font-medium uppercase tracking-wide text-slate-400"
                    >
                        Tentang Kami
                    </p>

                    <p
                        class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                    >
                        {{ selectedProfile.tentang_kami || "-" }}
                    </p>
                </div>

                <!-- LATAR -->
                <div>
                    <p
                        class="text-xs font-medium uppercase tracking-wide text-slate-400"
                    >
                        Latar Belakang
                    </p>

                    <p
                        class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300"
                    >
                        {{ selectedProfile.latar_belakang || "-" }}
                    </p>
                </div>

                <!-- DETAIL -->
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-slate-400"
                        >
                            Moto
                        </p>

                        <p class="mt-1 text-sm">
                            {{ selectedProfile.moto || "-" }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-slate-400"
                        >
                            Email
                        </p>

                        <p class="mt-1 text-sm">
                            {{ selectedProfile.email || "-" }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-slate-400"
                        >
                            Telepon
                        </p>

                        <p class="mt-1 text-sm">
                            {{ selectedProfile.telepon || "-" }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-medium uppercase tracking-wide text-slate-400"
                        >
                            Website
                        </p>

                        <p class="mt-1 text-sm">
                            {{ selectedProfile.website || "-" }}
                        </p>
                    </div>
                </div>

                <!-- ALAMAT -->
                <div>
                    <p
                        class="text-xs font-medium uppercase tracking-wide text-slate-400"
                    >
                        Alamat
                    </p>

                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                        {{ selectedProfile.alamat || "-" }}
                    </p>
                </div>

                <!-- STATUS -->
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
                        {{ selectedProfile.aktif ? "Aktif" : "Nonaktif" }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL HAPUS -->
    <!-- ============================================================= -->

    <div
        v-if="showDelete && selectedProfile"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="showDelete = false"
    >
        <div
            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900"
        >
            <!-- ICON -->
            <div
                class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
            >
                <Trash2 class="size-5" />
            </div>

            <!-- TEXT -->
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

            <!-- BUTTON -->
            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    @click="showDelete = false"
                >
                    Batal
                </button>

                <button
                    type="button"
                    class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700"
                    @click="deleteProfile"
                >
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</template>
