<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import DOMPurify from "dompurify";
import {
    Calendar,
    Eye,
    FileText,
    Image as ImageIcon,
    Pencil,
    Plus,
    RotateCcw,
    Search,
    Star,
    Trash2,
    Upload,
    X,
} from "lucide-vue-next";
import { toast } from "vue-sonner";
import AppLayout from "@/layouts/AppLayout.vue";
import RichTextEditor from "@/components/RichTextEditor.vue";

defineOptions({
    layout: AppLayout,
});

/*
|--------------------------------------------------------------------------
| Interfaces
|--------------------------------------------------------------------------
*/

interface Berita {
    id: number;
    judul: string;
    slug: string;
    excerpt: string | null;
    konten: string;
    gambar: string | null;
    kategori: string | null;
    penulis: string | null;
    status: "draft" | "published" | "archived";
    published_at: string | null;
    is_featured: boolean;
    views: number;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData {
    data: Berita[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
}

interface Props {
    beritas: PaginatedData;

    filters: {
        search: string;
        status: string;
        kategori: string;
    };

    categories: string[];
}

const props = defineProps<Props>();

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const search = ref(props.filters.search ?? "");
const status = ref(props.filters.status ?? "all");
const kategori = ref(props.filters.kategori ?? "all");

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const applyFilter = () => {
    router.get(
        "/admin/pusat-informasi/berita",
        {
            search: search.value || undefined,
            status: status.value !== "all" ? status.value : undefined,
            kategori: kategori.value !== "all" ? kategori.value : undefined,
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

watch(kategori, () => {
    applyFilter();
});

const resetFilter = () => {
    clearTimeout(searchTimeout);

    search.value = "";
    status.value = "all";
    kategori.value = "all";

    router.get(
        "/admin/pusat-informasi/berita",
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
        search.value !== "" ||
        status.value !== "all" ||
        kategori.value !== "all"
    );
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

const selectedBerita = ref<Berita | null>(null);

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const emptyForm = () => ({
    judul: "",
    slug: "",
    excerpt: "",
    konten: "",
    kategori: "",
    penulis: "",
    status: "draft" as "draft" | "published" | "archived",
    published_at: "",
    is_featured: false,
});

const form = ref(emptyForm());

const gambarFile = ref<File | null>(null);
const gambarPreview = ref<string | null>(null);

const processingForm = ref(false);
const processingDelete = ref(false);

/*
|--------------------------------------------------------------------------
| Toggle Processing
|--------------------------------------------------------------------------
*/

const togglingStatusId = ref<number | null>(null);
const togglingFeaturedId = ref<number | null>(null);

/*
|--------------------------------------------------------------------------
| Rich Content Helpers
|--------------------------------------------------------------------------
*/

const ALLOWED_TAGS = [
    "p",
    "br",
    "strong",
    "b",
    "em",
    "i",
    "s",
    "del",
    "h2",
    "h3",
    "ul",
    "ol",
    "li",
    "blockquote",
    "hr",
];

const escapeHtml = (text: string) =>
    text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

const hasHtml = (value: string) =>
    /<\/?(p|br|strong|em|b|i|s|del|h[1-6]|ul|ol|li|blockquote|hr|div|span)\b/i.test(
        value,
    );

/*
 * Berita lama mungkin masih berupa plain text.
 * Ubah ke HTML agar paragraf dan line break tetap terlihat.
 */
const toHtml = (value: string | null) => {
    if (!value) {
        return "";
    }

    if (hasHtml(value)) {
        return value;
    }

    return value
        .split(/\n{2,}/)
        .map(
            (paragraph) =>
                `<p>${escapeHtml(paragraph).replace(/\n/g, "<br>")}</p>`,
        )
        .join("");
};

const safeHtml = (value: string | null) => {
    return DOMPurify.sanitize(toHtml(value), {
        ALLOWED_TAGS,
        ALLOWED_ATTR: [],
    });
};

/*
 * Mengubah HTML menjadi teks biasa untuk validasi konten.
 */
const plainText = (value: string) => {
    return value
        .replace(/<[^>]*>/g, " ")
        .replace(/&nbsp;/gi, " ")
        .replace(/&amp;/gi, "&")
        .replace(/&lt;/gi, "<")
        .replace(/&gt;/gi, ">")
        .replace(/\s+/g, " ")
        .trim();
};

const hasKonten = computed(() => {
    return plainText(form.value.konten) !== "";
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const truncate = (text: string | null, length = 100) => {
    if (!text) {
        return "-";
    }

    return text.length > length ? `${text.substring(0, length)}...` : text;
};

const getImageUrl = (gambar: string | null) => {
    if (!gambar) {
        return null;
    }

    return `/storage/${gambar}`;
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
    )}-${pad(date.getDate())}T${pad(
        date.getHours(),
    )}:${pad(date.getMinutes())}`;
};

const statusLabel = (value: Berita["status"]) => {
    switch (value) {
        case "published":
            return "Published";

        case "archived":
            return "Archived";

        default:
            return "Draft";
    }
};

const statusClass = (value: Berita["status"]) => {
    switch (value) {
        case "published":
            return "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400";

        case "archived":
            return "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400";

        default:
            return "bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400";
    }
};

const statusDotClass = (value: Berita["status"]) => {
    switch (value) {
        case "published":
            return "bg-emerald-500";

        case "archived":
            return "bg-slate-400";

        default:
            return "bg-amber-500";
    }
};

const revokeGambarPreview = () => {
    if (gambarPreview.value?.startsWith("blob:")) {
        URL.revokeObjectURL(gambarPreview.value);
    }

    gambarPreview.value = null;
};

/*
|--------------------------------------------------------------------------
| Modal Actions
|--------------------------------------------------------------------------
*/

const resetFormState = () => {
    showModal.value = false;

    revokeGambarPreview();

    form.value = emptyForm();
    gambarFile.value = null;
    selectedBerita.value = null;
};

const openCreate = () => {
    revokeGambarPreview();

    form.value = emptyForm();

    selectedBerita.value = null;
    gambarFile.value = null;

    modalMode.value = "create";

    showDetail.value = false;
    showDelete.value = false;
    showModal.value = true;
};

const openEdit = (berita: Berita) => {
    revokeGambarPreview();

    selectedBerita.value = berita;

    form.value = {
        judul: berita.judul,
        slug: berita.slug,
        excerpt: berita.excerpt || "",
        konten: toHtml(berita.konten),
        kategori: berita.kategori || "",
        penulis: berita.penulis || "",
        status: berita.status,
        published_at: formatDateInput(berita.published_at),
        is_featured: berita.is_featured,
    };

    gambarFile.value = null;

    modalMode.value = "edit";

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

const openDetail = (berita: Berita) => {
    selectedBerita.value = berita;

    showModal.value = false;
    showDelete.value = false;
    showDetail.value = true;
};

const closeDetail = () => {
    showDetail.value = false;
    selectedBerita.value = null;
};

const openDelete = (berita: Berita) => {
    selectedBerita.value = berita;

    showModal.value = false;
    showDetail.value = false;
    showDelete.value = true;
};

const closeDelete = () => {
    if (processingDelete.value) {
        return;
    }

    showDelete.value = false;
    selectedBerita.value = null;
};

/*
|--------------------------------------------------------------------------
| Image Upload
|--------------------------------------------------------------------------
*/

const handleGambarChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    revokeGambarPreview();

    if (!file) {
        gambarFile.value = null;
        return;
    }

    const allowedTypes = ["image/png", "image/jpeg", "image/webp"];

    const maxSize = 1024 * 1024;

    if (!allowedTypes.includes(file.type)) {
        gambarFile.value = null;
        target.value = "";

        toast.error("Format gambar harus PNG, JPG, JPEG, atau WEBP.");

        return;
    }

    if (file.size > maxSize) {
        gambarFile.value = null;
        target.value = "";

        toast.error("Ukuran gambar maksimal 1 MB.");

        return;
    }

    gambarFile.value = file;
    gambarPreview.value = URL.createObjectURL(file);
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

    const judul = form.value.judul.trim();
    const konten = form.value.konten.trim();

    if (!judul) {
        toast.error("Judul berita wajib diisi.");
        return;
    }

    if (!plainText(konten)) {
        toast.error("Konten berita wajib diisi.");
        return;
    }

    /*
     * Hanya berita published yang boleh memiliki
     * published_at dan is_featured.
     */
    if (form.value.status !== "published") {
        form.value.published_at = "";
        form.value.is_featured = false;
    }

    const data = new FormData();

    data.append("judul", judul);

    data.append("excerpt", form.value.excerpt.trim());

    data.append("konten", konten);

    data.append("kategori", form.value.kategori.trim());

    data.append("penulis", form.value.penulis.trim());

    data.append("status", form.value.status);

    if (form.value.published_at) {
        data.append("published_at", form.value.published_at);
    }

    data.append("is_featured", form.value.is_featured ? "1" : "0");

    if (gambarFile.value) {
        data.append("gambar", gambarFile.value);
    }

    processingForm.value = true;

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    if (modalMode.value === "create") {
        router.post("/admin/pusat-informasi/berita", data, {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                /*
                 * Jangan menggunakan closeModal()
                 * karena processingForm masih true
                 * pada saat onSuccess dipanggil.
                 */
                resetFormState();
            },

            onError: (errors) => {
                console.error("Gagal menambahkan berita:", errors);
            },

            onFinish: () => {
                processingForm.value = false;
            },
        });

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    if (!selectedBerita.value) {
        processingForm.value = false;
        return;
    }

    data.append("_method", "PUT");

    router.post(
        `/admin/pusat-informasi/berita/${selectedBerita.value.id}`,
        data,
        {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                resetFormState();
            },

            onError: (errors) => {
                console.error("Gagal memperbarui berita:", errors);
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

const deleteBerita = () => {
    if (!selectedBerita.value || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(`/admin/pusat-informasi/berita/${selectedBerita.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDelete.value = false;
            selectedBerita.value = null;
        },

        onError: (errors) => {
            console.error("Gagal menghapus berita:", errors);
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

const toggleStatus = (berita: Berita) => {
    if (togglingStatusId.value !== null) {
        return;
    }

    togglingStatusId.value = berita.id;

    router.patch(
        `/admin/pusat-informasi/berita/${berita.id}/toggle-status`,
        {},
        {
            preserveScroll: true,

            onError: (errors) => {
                console.error("Gagal mengubah status berita:", errors);
            },

            onFinish: () => {
                togglingStatusId.value = null;
            },
        },
    );
};

/*
|--------------------------------------------------------------------------
| Toggle Featured
|--------------------------------------------------------------------------
*/

const toggleFeatured = (berita: Berita) => {
    if (berita.status !== "published") {
        return;
    }

    if (togglingFeaturedId.value !== null) {
        return;
    }

    togglingFeaturedId.value = berita.id;

    router.patch(
        `/admin/pusat-informasi/berita/${berita.id}/toggle-featured`,
        {},
        {
            preserveScroll: true,

            onError: (errors) => {
                console.error("Gagal mengubah berita unggulan:", errors);
            },

            onFinish: () => {
                togglingFeaturedId.value = null;
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
    revokeGambarPreview();
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
            <!-- HEADER -->
            <div
                class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <FileText class="size-5" />
                    </div>

                    <div class="min-w-0">
                        <h1
                            class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-2xl"
                        >
                            Berita
                        </h1>

                        <p
                            class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                        >
                            Kelola berita dan informasi terbaru perusahaan.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/20 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:ring-offset-2 focus:ring-offset-slate-50 dark:focus:ring-offset-slate-950"
                    @click="openCreate"
                >
                    <Plus class="size-4" />
                    Tambah Berita
                </button>
            </div>

            <!-- =====================================================
                 FILTER
            ====================================================== -->
            <div
                class="mb-5 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm shadow-slate-200/40 backdrop-blur-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
            >
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari judul, kategori, atau penulis..."
                            class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:bg-slate-900"
                        />
                    </div>

                    <select
                        v-model="status"
                        class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="all">Semua Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archived</option>
                    </select>

                    <select
                        v-model="kategori"
                        class="h-10 rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="all">Semua Kategori</option>

                        <option
                            v-for="item in categories"
                            :key="item"
                            :value="item"
                        >
                            {{ item }}
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
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1150px] text-left text-sm">
                        <thead
                            class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <tr>
                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Berita
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Kategori
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Penulis
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Publikasi
                                </th>

                                <th
                                    class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Views
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
                                v-for="berita in beritas.data"
                                :key="berita.id"
                                class="transition-colors duration-200 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                            >
                                <!-- BERITA -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:ring-blue-900/40"
                                        >
                                            <img
                                                v-if="berita.gambar"
                                                :src="
                                                    getImageUrl(berita.gambar)!
                                                "
                                                :alt="berita.judul"
                                                class="size-full object-cover"
                                            />

                                            <ImageIcon v-else class="size-6" />
                                        </div>

                                        <div class="min-w-0 max-w-md">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <p
                                                    class="truncate font-semibold text-slate-900 dark:text-white"
                                                >
                                                    {{ berita.judul }}
                                                </p>

                                                <Star
                                                    v-if="berita.is_featured"
                                                    class="size-4 shrink-0 fill-current text-amber-500"
                                                />
                                            </div>

                                            <p
                                                class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{
                                                    truncate(berita.excerpt, 90)
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- KATEGORI -->
                                <td class="px-6 py-4">
                                    <span
                                        v-if="berita.kategori"
                                        class="inline-flex rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        {{ berita.kategori }}
                                    </span>

                                    <span v-else class="text-slate-400">
                                        -
                                    </span>
                                </td>

                                <!-- PENULIS -->
                                <td class="px-6 py-4">
                                    <span
                                        class="text-slate-600 dark:text-slate-300"
                                    >
                                        {{ berita.penulis || "-" }}
                                    </span>
                                </td>

                                <!-- STATUS -->
                                <td class="px-6 py-4">
                                    <button
                                        type="button"
                                        :disabled="
                                            togglingStatusId === berita.id
                                        "
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition hover:opacity-80 disabled:cursor-wait disabled:opacity-60"
                                        :class="statusClass(berita.status)"
                                        @click="toggleStatus(berita)"
                                    >
                                        <span
                                            v-if="
                                                togglingStatusId === berita.id
                                            "
                                            class="size-3 animate-spin rounded-full border border-current/30 border-t-current"
                                        ></span>

                                        <span
                                            v-else
                                            class="size-1.5 rounded-full"
                                            :class="
                                                statusDotClass(berita.status)
                                            "
                                        ></span>

                                        {{
                                            togglingStatusId === berita.id
                                                ? "Memproses..."
                                                : statusLabel(berita.status)
                                        }}
                                    </button>
                                </td>

                                <!-- PUBLIKASI -->
                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center gap-2 text-slate-600 dark:text-slate-300"
                                    >
                                        <Calendar
                                            class="size-4 text-slate-400"
                                        />

                                        <span class="whitespace-nowrap">
                                            {{
                                                formatDate(berita.published_at)
                                            }}
                                        </span>
                                    </div>
                                </td>

                                <!-- VIEWS -->
                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300"
                                    >
                                        <Eye class="size-4 text-slate-400" />

                                        {{ berita.views }}
                                    </div>
                                </td>

                                <!-- AKSI -->
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-1">
                                        <!-- FEATURED -->
                                        <button
                                            type="button"
                                            :disabled="
                                                berita.status !== 'published' ||
                                                togglingFeaturedId === berita.id
                                            "
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-amber-50 hover:text-amber-600 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                            :class="
                                                berita.is_featured
                                                    ? 'text-amber-500'
                                                    : ''
                                            "
                                            :title="
                                                togglingFeaturedId === berita.id
                                                    ? 'Memproses...'
                                                    : 'Berita unggulan'
                                            "
                                            @click="toggleFeatured(berita)"
                                        >
                                            <span
                                                v-if="
                                                    togglingFeaturedId ===
                                                    berita.id
                                                "
                                                class="block size-4 animate-spin rounded-full border-2 border-slate-300 border-t-amber-500 dark:border-slate-600 dark:border-t-amber-400"
                                            ></span>

                                            <Star
                                                v-else
                                                class="size-4"
                                                :class="
                                                    berita.is_featured
                                                        ? 'fill-current'
                                                        : ''
                                                "
                                            />
                                        </button>

                                        <!-- DETAIL -->
                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Lihat detail"
                                            @click="openDetail(berita)"
                                        >
                                            <Eye class="size-4" />
                                        </button>

                                        <!-- EDIT -->
                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                            title="Edit berita"
                                            @click="openEdit(berita)"
                                        >
                                            <Pencil class="size-4" />
                                        </button>

                                        <!-- DELETE -->
                                        <button
                                            type="button"
                                            class="rounded-xl p-2 text-slate-500 transition-all hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                            title="Hapus berita"
                                            @click="openDelete(berita)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- EMPTY -->
                            <tr v-if="beritas.data.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600"
                                    >
                                        <FileText class="size-7" />
                                    </div>

                                    <p
                                        class="mt-4 font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        Data berita tidak ditemukan
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
                    v-if="beritas.total > 0"
                    class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Menampilkan

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ beritas.from }}
                        </span>

                        -

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ beritas.to }}
                        </span>

                        dari

                        <span
                            class="font-medium text-slate-700 dark:text-slate-200"
                        >
                            {{ beritas.total }}
                        </span>

                        data
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="(link, index) in beritas.links"
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
             MODAL CREATE / EDIT
        ========================================================== -->
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
                                    <FileText class="size-4" />
                                </div>

                                <h2
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    {{
                                        modalMode === "create"
                                            ? "Tambah Berita"
                                            : "Edit Berita"
                                    }}
                                </h2>
                            </div>

                            <p
                                class="mt-1 pl-11 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Lengkapi informasi berita yang akan ditampilkan
                                di website.
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
                        class="max-h-[75vh] overflow-y-auto p-6"
                        @submit.prevent="submitForm"
                    >
                        <div class="grid gap-5 md:grid-cols-2">
                            <!-- JUDUL -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Judul Berita
                                    <span class="text-red-500"> * </span>
                                </label>

                                <input
                                    v-model="form.judul"
                                    type="text"
                                    required
                                    maxlength="255"
                                    placeholder="Contoh: KITB Dorong Pertumbuhan Investasi di Riau"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Slug
                                </label>

                                <div class="relative">
                                    <input
                                        :value="form.slug"
                                        type="text"
                                        readonly
                                        disabled
                                        class="h-11 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 pr-24 text-sm text-slate-500 outline-none dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-400"
                                    />

                                    <span
                                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg bg-slate-200 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-700 dark:text-slate-400"
                                    >
                                        Otomatis
                                    </span>
                                </div>

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Slug dibuat otomatis berdasarkan judul
                                    berita.
                                </p>
                            </div>

                            <!-- KATEGORI -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Kategori
                                </label>

                                <input
                                    v-model="form.kategori"
                                    type="text"
                                    list="kategori-options"
                                    maxlength="100"
                                    placeholder="Contoh: Investasi"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                />

                                <datalist id="kategori-options">
                                    <option
                                        v-for="item in categories"
                                        :key="item"
                                        :value="item"
                                    />
                                </datalist>
                            </div>

                            <!-- PENULIS -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Penulis
                                </label>

                                <input
                                    v-model="form.penulis"
                                    type="text"
                                    maxlength="255"
                                    placeholder="Contoh: Admin KITB"
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
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <option value="draft">Draft</option>

                                    <option value="published">Published</option>

                                    <option value="archived">Archived</option>
                                </select>
                            </div>

                            <!-- PUBLISHED -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Waktu Publikasi
                                </label>

                                <input
                                    v-model="form.published_at"
                                    type="datetime-local"
                                    :disabled="form.status !== 'published'"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Digunakan ketika berita dipublikasikan.
                                </p>
                            </div>

                            <!-- EXCERPT -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Ringkasan
                                </label>

                                <textarea
                                    v-model="form.excerpt"
                                    rows="3"
                                    placeholder="Tuliskan ringkasan singkat berita..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                                ></textarea>
                            </div>

                            <!-- KONTEN -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Konten Berita
                                    <span class="text-red-500"> * </span>
                                </label>

                                <RichTextEditor v-model="form.konten" />

                                <p class="mt-1.5 text-xs text-slate-400">
                                    Blok teks lalu klik tombol di atas untuk
                                    tebal, miring, judul, atau daftar.
                                </p>
                            </div>

                            <!-- IMAGE -->
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                >
                                    Gambar Berita
                                </label>

                                <div
                                    class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 p-4 transition-colors dark:border-slate-700 dark:bg-slate-800/50"
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
                                                    Upload gambar berita
                                                </p>

                                                <p
                                                    class="text-xs text-slate-500 dark:text-slate-400"
                                                >
                                                    PNG, JPG, JPEG, atau WEBP —
                                                    maksimal 1 MB.
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
                                                @change="handleGambarChange"
                                            />
                                        </label>
                                    </div>
                                </div>

                                <!-- NEW IMAGE PREVIEW -->
                                <div
                                    v-if="gambarPreview"
                                    class="mt-3 flex items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50/60 p-3 dark:border-blue-900/40 dark:bg-blue-950/20"
                                >
                                    <img
                                        :src="gambarPreview"
                                        alt="Preview gambar"
                                        class="h-20 w-28 rounded-xl border border-slate-200 bg-white object-cover dark:border-slate-700"
                                    />

                                    <div>
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Preview Gambar Baru
                                        </p>

                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400"
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
                                        selectedBerita?.gambar
                                    "
                                    class="mt-3 flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <img
                                        :src="
                                            getImageUrl(selectedBerita.gambar)!
                                        "
                                        alt="Gambar berita"
                                        class="h-20 w-28 rounded-xl border border-slate-200 bg-white object-cover dark:border-slate-700"
                                    />

                                    <div>
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Gambar Saat Ini
                                        </p>

                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Pilih file baru jika ingin mengganti
                                            gambar.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FEATURED -->
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2 dark:border-slate-700 dark:bg-slate-800/50"
                            >
                                <div class="flex items-start gap-3">
                                    <input
                                        id="is_featured"
                                        v-model="form.is_featured"
                                        type="checkbox"
                                        :disabled="form.status !== 'published'"
                                        class="mt-0.5 size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800"
                                    />

                                    <div>
                                        <label
                                            for="is_featured"
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Jadikan berita unggulan
                                        </label>

                                        <p
                                            class="mt-1 text-xs leading-5 text-slate-400"
                                        >
                                            Hanya berita dengan status published
                                            yang dapat dijadikan unggulan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FOOTER -->
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
                                    !form.judul.trim() ||
                                    !hasKonten
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
                                          ? "Simpan Berita"
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
                v-if="showDetail && selectedBerita"
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
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Berita
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap berita.
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
                        <!-- IMAGE -->
                        <div
                            v-if="selectedBerita.gambar"
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800"
                        >
                            <img
                                :src="getImageUrl(selectedBerita.gambar)!"
                                :alt="selectedBerita.judul"
                                class="max-h-80 w-full object-cover"
                            />
                        </div>

                        <!-- META -->
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    v-if="selectedBerita.kategori"
                                    class="inline-flex rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    {{ selectedBerita.kategori }}
                                </span>

                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="statusClass(selectedBerita.status)"
                                >
                                    <span
                                        class="size-1.5 rounded-full"
                                        :class="
                                            statusDotClass(
                                                selectedBerita.status,
                                            )
                                        "
                                    ></span>

                                    {{ statusLabel(selectedBerita.status) }}
                                </span>

                                <span
                                    v-if="selectedBerita.is_featured"
                                    class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-400"
                                >
                                    <Star class="size-3 fill-current" />

                                    Unggulan
                                </span>
                            </div>

                            <h3
                                class="mt-3 text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ selectedBerita.judul }}
                            </h3>

                            <div
                                class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                <span>
                                    Penulis:

                                    <strong
                                        class="font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{ selectedBerita.penulis || "-" }}
                                    </strong>
                                </span>

                                <span class="flex items-center gap-1.5">
                                    <Calendar class="size-4" />

                                    {{
                                        formatDate(selectedBerita.published_at)
                                    }}
                                </span>

                                <span class="flex items-center gap-1.5">
                                    <Eye class="size-4" />

                                    {{ selectedBerita.views }}
                                    views
                                </span>
                            </div>
                        </div>

                        <!-- EXCERPT -->
                        <div
                            v-if="selectedBerita.excerpt"
                            class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4 dark:border-blue-900/40 dark:bg-blue-950/20"
                        >
                            <p
                                class="text-sm leading-6 text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedBerita.excerpt }}
                            </p>
                        </div>

                        <!-- CONTENT -->
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Konten Berita
                            </p>

                            <div
                                class="rich-content mt-3 text-sm text-slate-600 dark:text-slate-300"
                                v-html="safeHtml(selectedBerita.konten)"
                            ></div>
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
                v-if="showDelete && selectedBerita"
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
                            Hapus Berita?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus

                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedBerita.judul }}
                            </span>

                            ? Data yang sudah dihapus tidak dapat dikembalikan.
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
                            @click="deleteBerita"
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
   Rich Content
   ========================================================================== */

:deep(.rich-content) {
    line-height: 1.75;
    overflow-wrap: anywhere;
}

:deep(.rich-content p) {
    margin: 0.5rem 0;
}

:deep(.rich-content h2) {
    margin: 1.25rem 0 0.75rem;
    font-size: 1.25rem;
    line-height: 1.5rem;
    font-weight: 700;
    color: rgb(15 23 42);
}

:deep(.rich-content h3) {
    margin: 1rem 0 0.5rem;
    font-size: 1.125rem;
    line-height: 1.5rem;
    font-weight: 700;
    color: rgb(15 23 42);
}

:deep(.rich-content ul) {
    list-style-type: disc;
    margin: 0.75rem 0;
    padding-left: 1.5rem;
}

:deep(.rich-content ol) {
    list-style-type: decimal;
    margin: 0.75rem 0;
    padding-left: 1.5rem;
}

:deep(.rich-content li) {
    margin: 0.25rem 0;
    padding-left: 0.25rem;
}

:deep(.rich-content blockquote) {
    margin: 1rem 0;
    border-left: 4px solid rgb(59 130 246);
    padding-left: 1rem;
    font-style: italic;
    color: rgb(71 85 105);
}

:deep(.rich-content hr) {
    margin: 1.25rem 0;
    border: 0;
    border-top: 1px solid rgb(226 232 240);
}

:deep(.rich-content strong),
:deep(.rich-content b) {
    font-weight: 700;
}

:deep(.rich-content em),
:deep(.rich-content i) {
    font-style: italic;
}

:deep(.rich-content s),
:deep(.rich-content del) {
    text-decoration: line-through;
}

/* ==========================================================================
   Rich Content Dark Mode
   ========================================================================== */

:deep(.dark .rich-content h2),
:deep(.dark .rich-content h3) {
    color: rgb(248 250 252);
}

:deep(.dark .rich-content blockquote) {
    border-left-color: rgb(96 165 250);
    color: rgb(148 163 184);
}

:deep(.dark .rich-content hr) {
    border-top-color: rgb(51 65 85);
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
