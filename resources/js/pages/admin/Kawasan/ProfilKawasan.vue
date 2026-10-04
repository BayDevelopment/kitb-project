<script setup lang="ts">
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { Head, router } from "@inertiajs/vue3";
import {
    Building2,
    CalendarDays,
    Check,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    Eye,
    FileText,
    Image as ImageIcon,
    LoaderCircle,
    MapPin,
    Pencil,
    Plus,
    Ruler,
    Search,
    ToggleLeft,
    ToggleRight,
    Trash2,
    X,
} from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

/* =========================================================
   TYPES
   ========================================================= */

interface Kawasan {
    id: number;
    judul: string;
    slug: string;
    deskripsi: string | null;
    luas_kawasan: number | string | null;
    lokasi: string | null;
    latitude: number | string | null;
    longitude: number | string | null;
    batas_kawasan: GeoJsonObject | null;
    tahun_berdiri: number | string | null;
    status: boolean | number;
    gambar: string | null;
    created_at: string | null;
    updated_at: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface KawasanPagination {
    data: Kawasan[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface GeoJsonObject {
    type?: string;
    [key: string]: unknown;
}

interface Props {
    profilKawasans: KawasanPagination;
    filters?: {
        search?: string;
    };
}

/**
 * Input type="number" + v-model membuat Vue mengubah nilainya
 * menjadi number saat user mengetik. Karena itu field numerik
 * bertipe string | number, dan selalu dinormalisasi lewat
 * normalizeString() sebelum memakai .trim().
 */
interface FormState {
    judul: string;
    slug: string;
    deskripsi: string;
    luas_kawasan: string | number;
    lokasi: string;
    latitude: string | number;
    longitude: string | number;
    batas_kawasan: string;
    tahun_berdiri: string | number;
    status: boolean;
}

interface FormErrors {
    [key: string]: string;
}

const props = defineProps<Props>();

/* =========================================================
   PAGE LOADING
   ========================================================= */

const pageLoading = ref(false);

let removeStartListener: (() => void) | null = null;
let removeFinishListener: (() => void) | null = null;
let pageLoadingTimer: number | null = null;

onMounted(() => {
    removeStartListener = router.on("start", (event) => {
        if (
            event.detail.visit?.preserveState ||
            isSubmitting.value ||
            isDeleting.value
        ) {
            return;
        }

        pageLoading.value = true;

        if (pageLoadingTimer !== null) {
            window.clearTimeout(pageLoadingTimer);
            pageLoadingTimer = null;
        }
    });

    removeFinishListener = router.on("finish", () => {
        if (pageLoadingTimer !== null) {
            window.clearTimeout(pageLoadingTimer);
        }

        pageLoadingTimer = window.setTimeout(() => {
            pageLoading.value = false;
            pageLoadingTimer = null;
        }, 300);
    });
});

/* =========================================================
   SEARCH
   ========================================================= */

const search = ref(props.filters?.search ?? "");
let searchTimer: number | null = null;

watch(search, (value) => {
    if (searchTimer !== null) {
        window.clearTimeout(searchTimer);
    }

    searchTimer = window.setTimeout(() => {
        router.get(
            "/admin/kawasan/profil-kawasan",
            {
                search: value.trim() || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 350);
});

/* =========================================================
   MODAL STATE
   ========================================================= */

const showFormModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);

const editingId = ref<number | null>(null);
const selectedKawasan = ref<Kawasan | null>(null);
const deleteTarget = ref<Kawasan | null>(null);

const formModalRef = ref<HTMLElement | null>(null);
const detailModalRef = ref<HTMLElement | null>(null);
const deleteModalRef = ref<HTMLElement | null>(null);

const lastFocusedElement = ref<HTMLElement | null>(null);

/* =========================================================
   FORM
   ========================================================= */

const form = ref<FormState>({
    judul: "",
    slug: "",
    deskripsi: "",
    luas_kawasan: "",
    lokasi: "",
    latitude: "",
    longitude: "",
    batas_kawasan: "",
    tahun_berdiri: "",
    status: true,
});

const errors = ref<FormErrors>({});
const isSubmitting = ref(false);
const isDeleting = ref(false);

/* =========================================================
   IMAGE
   ========================================================= */

const existingGambar = ref<string | null>(null);
const gambarFile = ref<File | null>(null);
const gambarPreview = ref<string | null>(null);
const removeGambar = ref(false);

const gambarInputRef = ref<HTMLInputElement | null>(null);

const imageError = ref("");

const hasImage = computed(
    () => !!gambarPreview.value || !!existingGambar.value,
);

const imageUrl = computed(() => {
    if (gambarPreview.value) {
        return gambarPreview.value;
    }

    if (existingGambar.value) {
        return getImageUrl(existingGambar.value);
    }

    return null;
});

/* =========================================================
   COMPUTED
   ========================================================= */

const isEditing = computed(() => editingId.value !== null);

const modalTitle = computed(() =>
    isEditing.value ? "Edit Profil Kawasan" : "Tambah Profil Kawasan",
);

const pagination = computed(() => props.profilKawasans);

const currentPage = computed(() => props.profilKawasans.current_page);

const totalPages = computed(() => props.profilKawasans.last_page);

const hasPreviousPage = computed(() => !!props.profilKawasans.prev_page_url);

const hasNextPage = computed(() => !!props.profilKawasans.next_page_url);

/* =========================================================
   HELPERS
   ========================================================= */

function normalizeString(value: unknown): string {
    return value === null || value === undefined ? "" : String(value);
}

function slugify(value: string): string {
    return value
        .toString()
        .normalize("NFKD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-")
        .replace(/^-|-$/g, "");
}

function truncate(value: unknown, length = 100): string {
    const text = normalizeString(value);

    if (text.length <= length) {
        return text;
    }

    return `${text.slice(0, length).trim()}...`;
}

function formatNumber(value: unknown): string {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    const number = Number(value);

    if (!Number.isFinite(number)) {
        return "-";
    }

    return new Intl.NumberFormat("id-ID", {
        maximumFractionDigits: 2,
    }).format(number);
}

function formatCoordinate(value: unknown): string {
    if (value === null || value === undefined || value === "") {
        return "-";
    }

    const number = Number(value);

    if (!Number.isFinite(number)) {
        return "-";
    }

    return number.toFixed(7);
}

function formatDate(value: string | null): string {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    }).format(date);
}

function getImageUrl(path: string | null): string {
    if (!path) {
        return "";
    }

    if (
        path.startsWith("http://") ||
        path.startsWith("https://") ||
        path.startsWith("/")
    ) {
        return path;
    }

    return `/storage/${path}`;
}

function revokePreview() {
    if (gambarPreview.value) {
        URL.revokeObjectURL(gambarPreview.value);
        gambarPreview.value = null;
    }
}

function getGeoJsonText(value: GeoJsonObject | null): string {
    if (!value) {
        return "";
    }

    try {
        return JSON.stringify(value, null, 2);
    } catch {
        return "";
    }
}

function parseGeoJson(value: string): GeoJsonObject | null {
    const text = value.trim();

    if (!text) {
        return null;
    }

    try {
        const parsed = JSON.parse(text);

        if (!parsed || typeof parsed !== "object" || Array.isArray(parsed)) {
            throw new Error("GeoJSON harus berupa object.");
        }

        if (typeof parsed.type !== "string" || parsed.type.trim() === "") {
            throw new Error("GeoJSON harus memiliki property type.");
        }

        return parsed as GeoJsonObject;
    } catch {
        errors.value.batas_kawasan =
            "Format GeoJSON tidak valid. Pastikan JSON dan property type benar.";

        return null;
    }
}

function isValidLatitude(value: string): boolean {
    if (value.trim() === "") {
        return true;
    }

    const number = Number(value);

    return Number.isFinite(number) && number >= -90 && number <= 90;
}

function isValidLongitude(value: string): boolean {
    if (value.trim() === "") {
        return true;
    }

    const number = Number(value);

    return Number.isFinite(number) && number >= -180 && number <= 180;
}

/* =========================================================
   FORM RESET
   ========================================================= */

function resetForm() {
    revokePreview();

    form.value = {
        judul: "",
        slug: "",
        deskripsi: "",
        luas_kawasan: "",
        lokasi: "",
        latitude: "",
        longitude: "",
        batas_kawasan: "",
        tahun_berdiri: "",
        status: true,
    };

    errors.value = {};

    existingGambar.value = null;
    gambarFile.value = null;
    removeGambar.value = false;
    imageError.value = "";

    if (gambarInputRef.value) {
        gambarInputRef.value.value = "";
    }

    editingId.value = null;
}

/* =========================================================
   ACCESSIBILITY / FOCUS
   ========================================================= */

function rememberFocus() {
    lastFocusedElement.value =
        document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;
}

function restoreFocus() {
    nextTick(() => {
        lastFocusedElement.value?.focus();
        lastFocusedElement.value = null;
    });
}

function getFocusableElements(container: HTMLElement | null): HTMLElement[] {
    if (!container) {
        return [];
    }

    return Array.from(
        container.querySelectorAll<HTMLElement>(
            [
                "a[href]",
                "button:not([disabled])",
                "input:not([disabled])",
                "textarea:not([disabled])",
                "select:not([disabled])",
                "[tabindex]:not([tabindex='-1'])",
            ].join(","),
        ),
    ).filter(
        (element) =>
            !element.hasAttribute("aria-hidden") &&
            element.offsetParent !== null,
    );
}

function trapFocus(event: KeyboardEvent, container: HTMLElement | null) {
    if (event.key !== "Tab") {
        return;
    }

    const focusable = getFocusableElements(container);

    if (!focusable.length) {
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

function handleFormKeydown(event: KeyboardEvent) {
    event.stopPropagation();

    if (event.key === "Escape") {
        if (!isSubmitting.value) {
            closeForm();
        }

        return;
    }

    trapFocus(event, formModalRef.value);
}

function handleDetailKeydown(event: KeyboardEvent) {
    event.stopPropagation();

    if (event.key === "Escape") {
        closeDetail();
        return;
    }

    trapFocus(event, detailModalRef.value);
}

function handleDeleteKeydown(event: KeyboardEvent) {
    event.stopPropagation();

    if (event.key === "Escape") {
        if (!isDeleting.value) {
            closeDelete();
        }

        return;
    }

    trapFocus(event, deleteModalRef.value);
}

/* =========================================================
   BODY SCROLL LOCK
   ========================================================= */

let previousBodyOverflow = "";

watch(
    [showFormModal, showDetailModal, showDeleteModal],
    ([formOpen, detailOpen, deleteOpen]) => {
        const modalOpen = formOpen || detailOpen || deleteOpen;

        if (modalOpen) {
            if (previousBodyOverflow === "") {
                previousBodyOverflow = document.body.style.overflow;
            }

            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = previousBodyOverflow;
            previousBodyOverflow = "";
        }
    },
);

/* =========================================================
   CREATE / EDIT
   ========================================================= */

function openCreate() {
    rememberFocus();

    resetForm();

    showDetailModal.value = false;
    showDeleteModal.value = false;
    showFormModal.value = true;

    nextTick(() => {
        formModalRef.value
            ?.querySelector<HTMLElement>(
                "input:not([disabled]), textarea, select, button",
            )
            ?.focus();
    });
}

function openEdit(kawasan: Kawasan) {
    rememberFocus();

    revokePreview();

    editingId.value = kawasan.id;

    form.value = {
        judul: normalizeString(kawasan.judul),

        // Slug selalu dibuat otomatis dari judul.
        slug: slugify(normalizeString(kawasan.judul)),

        deskripsi: normalizeString(kawasan.deskripsi),

        luas_kawasan:
            kawasan.luas_kawasan !== null && kawasan.luas_kawasan !== undefined
                ? String(kawasan.luas_kawasan)
                : "",

        lokasi: normalizeString(kawasan.lokasi),

        latitude:
            kawasan.latitude !== null && kawasan.latitude !== undefined
                ? String(kawasan.latitude)
                : "",

        longitude:
            kawasan.longitude !== null && kawasan.longitude !== undefined
                ? String(kawasan.longitude)
                : "",

        batas_kawasan: getGeoJsonText(kawasan.batas_kawasan),

        // Pastikan integer dari Laravel dinormalisasi menjadi string.
        tahun_berdiri:
            kawasan.tahun_berdiri !== null &&
            kawasan.tahun_berdiri !== undefined
                ? String(kawasan.tahun_berdiri)
                : "",

        status: Boolean(kawasan.status),
    };

    existingGambar.value = kawasan.gambar ?? null;
    gambarFile.value = null;
    removeGambar.value = false;
    imageError.value = "";
    errors.value = {};

    if (gambarInputRef.value) {
        gambarInputRef.value.value = "";
    }

    showDetailModal.value = false;
    showDeleteModal.value = false;
    showFormModal.value = true;

    nextTick(() => {
        formModalRef.value
            ?.querySelector<HTMLElement>(
                "input:not([disabled]), textarea, select, button",
            )
            ?.focus();
    });
}

function closeForm() {
    if (isSubmitting.value) {
        return;
    }

    showFormModal.value = false;

    window.setTimeout(() => {
        if (!showFormModal.value) {
            resetForm();
            restoreFocus();
        }
    }, 200);
}

/* =========================================================
   AUTOMATIC SLUG
   ========================================================= */

watch(
    () => form.value.judul,
    (value) => {
        form.value.slug = slugify(value);
    },
);

/* =========================================================
   IMAGE HANDLING
   ========================================================= */

function handleImageChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    imageError.value = "";

    if (!file) {
        return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

    if (!allowedTypes.includes(file.type)) {
        imageError.value = "Format gambar harus JPG, JPEG, PNG, atau WEBP.";

        target.value = "";
        return;
    }

    if (file.size > 1024 * 1024) {
        imageError.value = "Ukuran gambar maksimal 1 MB.";

        target.value = "";
        return;
    }

    revokePreview();

    gambarFile.value = file;
    gambarPreview.value = URL.createObjectURL(file);

    // Ada file baru, jadi jangan hapus gambar lama.
    removeGambar.value = false;
}

function removeSelectedImage() {
    /*
     * Jika yang dihapus adalah file baru,
     * cukup batalkan file tersebut dan pertahankan
     * gambar lama ketika mode edit.
     */
    if (gambarFile.value) {
        gambarFile.value = null;
        revokePreview();

        removeGambar.value = false;
        imageError.value = "";

        if (gambarInputRef.value) {
            gambarInputRef.value.value = "";
        }

        return;
    }

    /*
     * Jika tidak ada file baru dan memang ada gambar lama,
     * tandai gambar lama untuk dihapus.
     */
    if (existingGambar.value) {
        removeGambar.value = true;
    }
}

function restoreExistingImage() {
    removeGambar.value = false;
}

/* =========================================================
   VALIDATION
   ========================================================= */

function validateForm(): boolean {
    errors.value = {};
    imageError.value = "";

    /*
     * Field numerik (input type="number") bisa berisi number,
     * bukan string, sehingga wajib dinormalisasi lebih dulu.
     */
    const judul = normalizeString(form.value.judul).trim();
    const slug = normalizeString(form.value.slug).trim();
    const luas = normalizeString(form.value.luas_kawasan).trim();
    const latitude = normalizeString(form.value.latitude).trim();
    const longitude = normalizeString(form.value.longitude).trim();
    const tahun = normalizeString(form.value.tahun_berdiri).trim();

    if (!judul) {
        errors.value.judul = "Judul kawasan wajib diisi.";
    } else if (judul.length > 255) {
        errors.value.judul = "Judul maksimal 255 karakter.";
    }

    if (!slug) {
        errors.value.slug = "Slug tidak dapat dibuat dari judul.";
    }

    if (luas !== "") {
        const luasNumber = Number(luas);

        if (!Number.isFinite(luasNumber) || luasNumber < 0) {
            errors.value.luas_kawasan =
                "Luas kawasan harus berupa angka positif.";
        }
    }

    if (!isValidLatitude(latitude)) {
        errors.value.latitude =
            "Latitude harus berada di antara -90 sampai 90.";
    }

    if (!isValidLongitude(longitude)) {
        errors.value.longitude =
            "Longitude harus berada di antara -180 sampai 180.";
    }

    if (tahun !== "") {
        const tahunNumber = Number(tahun);
        const currentYear = new Date().getFullYear();

        if (
            !Number.isInteger(tahunNumber) ||
            tahunNumber < 1800 ||
            tahunNumber > currentYear
        ) {
            errors.value.tahun_berdiri = `Tahun berdiri harus berupa tahun valid antara 1800-${currentYear}.`;
        }
    }

    if (normalizeString(form.value.batas_kawasan).trim() !== "") {
        const geoJson = parseGeoJson(form.value.batas_kawasan);

        if (!geoJson) {
            return false;
        }
    }

    if (gambarFile.value && gambarFile.value.size > 1024 * 1024) {
        imageError.value = "Ukuran gambar maksimal 1 MB.";
        return false;
    }

    return Object.keys(errors.value).length === 0;
}

/* =========================================================
   SUBMIT
   ========================================================= */

function submitForm() {
    if (isSubmitting.value) {
        return;
    }

    if (!validateForm()) {
        nextTick(() => {
            formModalRef.value
                ?.querySelector<HTMLElement>('[aria-invalid="true"]')
                ?.focus();
        });

        return;
    }

    isSubmitting.value = true;

    const data = new FormData();

    // Normalisasi field numerik agar aman dipanggil .trim().
    const luas = normalizeString(form.value.luas_kawasan).trim();
    const latitude = normalizeString(form.value.latitude).trim();
    const longitude = normalizeString(form.value.longitude).trim();
    const tahun = normalizeString(form.value.tahun_berdiri).trim();
    const lokasi = normalizeString(form.value.lokasi).trim();
    const batasKawasan = normalizeString(form.value.batas_kawasan).trim();

    data.append("judul", normalizeString(form.value.judul).trim());
    data.append("slug", normalizeString(form.value.slug).trim());

    data.append("deskripsi", normalizeString(form.value.deskripsi).trim());

    if (luas !== "") {
        data.append("luas_kawasan", String(Number(luas)));
    }

    if (lokasi !== "") {
        data.append("lokasi", lokasi);
    }

    if (latitude !== "") {
        data.append("latitude", String(Number(latitude)));
    }

    if (longitude !== "") {
        data.append("longitude", String(Number(longitude)));
    }

    if (batasKawasan !== "") {
        const geoJson = parseGeoJson(batasKawasan);

        if (geoJson) {
            data.append("batas_kawasan", JSON.stringify(geoJson));
        }
    } else {
        data.append("batas_kawasan", "");
    }

    if (tahun !== "") {
        data.append("tahun_berdiri", String(Number(tahun)));
    }

    data.append("status", form.value.status ? "1" : "0");

    if (gambarFile.value) {
        data.append("gambar", gambarFile.value);
    }

    if (removeGambar.value) {
        data.append("remove_gambar", "1");
    }

    const url = isEditing.value
        ? `/admin/kawasan/profil-kawasan/${editingId.value}`
        : "/admin/kawasan/profil-kawasan";

    if (isEditing.value) {
        data.append("_method", "PUT");
    }

    router.post(url, data, {
        forceFormData: true,
        preserveScroll: true,

        onError: (serverErrors) => {
            errors.value = Object.fromEntries(
                Object.entries(serverErrors).map(([key, value]) => [
                    key,
                    Array.isArray(value) ? String(value[0]) : String(value),
                ]),
            );
        },

        onSuccess: () => {
            showFormModal.value = false;
            resetForm();
        },

        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

/* =========================================================
   DETAIL
   ========================================================= */

function openDetail(kawasan: Kawasan) {
    rememberFocus();

    selectedKawasan.value = kawasan;
    showFormModal.value = false;
    showDeleteModal.value = false;
    showDetailModal.value = true;

    nextTick(() => {
        detailModalRef.value
            ?.querySelector<HTMLElement>(
                "button, [tabindex]:not([tabindex='-1'])",
            )
            ?.focus();
    });
}

function closeDetail() {
    showDetailModal.value = false;

    window.setTimeout(() => {
        if (!showDetailModal.value) {
            selectedKawasan.value = null;
            restoreFocus();
        }
    }, 200);
}

/* =========================================================
   DELETE
   ========================================================= */

function openDelete(kawasan: Kawasan) {
    rememberFocus();

    deleteTarget.value = kawasan;

    showFormModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = true;

    nextTick(() => {
        deleteModalRef.value?.querySelector<HTMLElement>("button")?.focus();
    });
}

function closeDelete() {
    if (isDeleting.value) {
        return;
    }

    showDeleteModal.value = false;

    window.setTimeout(() => {
        if (!showDeleteModal.value) {
            deleteTarget.value = null;
            restoreFocus();
        }
    }, 200);
}

function confirmDelete() {
    if (!deleteTarget.value || isDeleting.value) {
        return;
    }

    isDeleting.value = true;

    router.delete(`/admin/kawasan/profil-kawasan/${deleteTarget.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDeleteModal.value = false;
            deleteTarget.value = null;
        },

        onFinish: () => {
            isDeleting.value = false;
        },
    });
}

/* =========================================================
   TOGGLE STATUS
   ========================================================= */

function toggleStatus(kawasan: Kawasan) {
    if (isSubmitting.value || isDeleting.value) {
        return;
    }

    router.patch(
        `/admin/kawasan/profil-kawasan/${kawasan.id}/toggle-aktif`,
        {},
        {
            preserveScroll: true,
        },
    );
}

/* =========================================================
   PAGINATION
   ========================================================= */

function goToPage(url: string | null) {
    if (!url || pageLoading.value) {
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
}

/* =========================================================
   GLOBAL ESCAPE
   ========================================================= */

function handleGlobalKeydown(event: KeyboardEvent) {
    if (event.key !== "Escape") {
        return;
    }

    if (showFormModal.value && !isSubmitting.value) {
        closeForm();
        return;
    }

    if (showDetailModal.value) {
        closeDetail();
        return;
    }

    if (showDeleteModal.value && !isDeleting.value) {
        closeDelete();
    }
}

/* =========================================================
   LIFECYCLE
   ========================================================= */

onMounted(() => {
    window.addEventListener("keydown", handleGlobalKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleGlobalKeydown);

    removeStartListener?.();
    removeFinishListener?.();

    if (searchTimer !== null) {
        window.clearTimeout(searchTimer);
    }

    if (pageLoadingTimer !== null) {
        window.clearTimeout(pageLoadingTimer);
    }

    revokePreview();

    document.body.style.overflow = previousBodyOverflow;
});
</script>

<template>
    <Head title="Profil Kawasan" />

    <div
        class="relative min-h-full overflow-x-clip bg-slate-50/50 dark:bg-slate-950"
    >
        <!-- Background -->
        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"
            />
            <div
                class="absolute -right-24 top-1/3 h-80 w-80 rounded-full bg-slate-500/10 blur-3xl"
            />
        </div>

        <div class="relative mx-auto max-w-[1600px] p-4 sm:p-6 lg:p-8">
            <!-- Breadcrumb -->
            <nav
                class="mb-6 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                aria-label="Breadcrumb"
            >
                <Building2 class="h-4 w-4 shrink-0" />

                <span>Admin</span>

                <ChevronRight class="h-4 w-4" />

                <span>Kawasan</span>

                <ChevronRight class="h-4 w-4" />

                <span class="font-medium text-slate-700 dark:text-slate-200">
                    Profil Kawasan
                </span>
            </nav>

            <!-- Header -->
            <div
                class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
            >
                <div class="min-w-0">
                    <div
                        class="mb-2 inline-flex items-center gap-2 rounded-full border border-blue-200/70 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300"
                    >
                        <Building2 class="h-3.5 w-3.5" />
                        Kawasan
                    </div>

                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl"
                    >
                        Profil Kawasan
                    </h1>

                    <p
                        class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        Kelola informasi utama kawasan industri, koordinat
                        lokasi, batas kawasan, dan status publikasi.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 dark:focus:ring-offset-slate-950"
                    @click="openCreate"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Profil
                </button>
            </div>

            <!-- Search -->
            <div
                class="mb-6 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/90"
            >
                <div class="relative max-w-xl">
                    <Search
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />

                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari profil kawasan..."
                        aria-label="Cari profil kawasan"
                        class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:bg-slate-950"
                    />
                </div>
            </div>

            <!-- Skeleton -->
            <div
                v-if="pageLoading"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="animate-pulse">
                    <div
                        class="hidden border-b border-slate-200 p-4 dark:border-slate-800 md:grid md:grid-cols-7 md:gap-4"
                    >
                        <div
                            v-for="n in 7"
                            :key="n"
                            class="h-4 rounded bg-slate-200 dark:bg-slate-800"
                        />
                    </div>

                    <div
                        v-for="n in 5"
                        :key="n"
                        class="border-b border-slate-200 p-4 last:border-0 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="h-14 w-14 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="flex-1 space-y-2">
                                <div
                                    class="h-4 w-1/3 rounded bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-3 w-2/3 rounded bg-slate-200 dark:bg-slate-800"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div
                v-else
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div v-if="profilKawasans.data.length" class="overflow-x-auto">
                    <table
                        class="w-full min-w-[1050px] border-collapse text-left"
                    >
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-950/60"
                            >
                                <th
                                    class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Kawasan
                                </th>

                                <th
                                    class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Luas
                                </th>

                                <th
                                    class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Lokasi
                                </th>

                                <th
                                    class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Koordinat
                                </th>

                                <th
                                    class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Tahun
                                </th>

                                <th
                                    class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="kawasan in profilKawasans.data"
                                :key="kawasan.id"
                                class="transition hover:bg-slate-50/70 dark:hover:bg-slate-800/40"
                            >
                                <!-- Kawasan -->
                                <td class="px-5 py-4">
                                    <div
                                        class="flex min-w-0 items-center gap-3"
                                    >
                                        <div
                                            class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800"
                                        >
                                            <img
                                                v-if="kawasan.gambar"
                                                :src="
                                                    getImageUrl(kawasan.gambar)
                                                "
                                                :alt="kawasan.judul"
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            />

                                            <ImageIcon
                                                v-else
                                                class="h-6 w-6 text-slate-400"
                                            />
                                        </div>

                                        <div class="min-w-0">
                                            <div
                                                class="truncate font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{ kawasan.judul }}
                                            </div>

                                            <div
                                                class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                /{{ kawasan.slug }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Luas -->
                                <td class="px-5 py-4">
                                    <div
                                        class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300"
                                    >
                                        <Ruler class="h-4 w-4 text-slate-400" />

                                        <span>
                                            {{
                                                kawasan.luas_kawasan !== null &&
                                                kawasan.luas_kawasan !==
                                                    undefined
                                                    ? `${formatNumber(
                                                          kawasan.luas_kawasan,
                                                      )} ha`
                                                    : "-"
                                            }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Lokasi -->
                                <td class="max-w-[220px] px-5 py-4">
                                    <div
                                        class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-300"
                                    >
                                        <MapPin
                                            class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
                                        />

                                        <span class="line-clamp-2">
                                            {{ kawasan.lokasi || "-" }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Koordinat -->
                                <td class="px-5 py-4">
                                    <div
                                        v-if="
                                            kawasan.latitude !== null &&
                                            kawasan.longitude !== null
                                        "
                                        class="space-y-1 text-xs text-slate-600 dark:text-slate-400"
                                    >
                                        <div>
                                            Lat:
                                            {{
                                                formatCoordinate(
                                                    kawasan.latitude,
                                                )
                                            }}
                                        </div>

                                        <div>
                                            Lng:
                                            {{
                                                formatCoordinate(
                                                    kawasan.longitude,
                                                )
                                            }}
                                        </div>
                                    </div>

                                    <span v-else class="text-sm text-slate-400">
                                        -
                                    </span>
                                </td>

                                <!-- Tahun -->
                                <td class="px-5 py-4">
                                    <div
                                        class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300"
                                    >
                                        <CalendarDays
                                            class="h-4 w-4 text-slate-400"
                                        />

                                        <span>
                                            {{ kawasan.tahun_berdiri ?? "-" }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-4">
                                    <button
                                        type="button"
                                        role="switch"
                                        :aria-checked="Boolean(kawasan.status)"
                                        :aria-label="`Ubah status ${kawasan.judul}`"
                                        class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                        :class="
                                            Boolean(kawasan.status)
                                                ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-950/60'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700'
                                        "
                                        @click="toggleStatus(kawasan)"
                                    >
                                        <ToggleRight
                                            v-if="Boolean(kawasan.status)"
                                            class="h-4 w-4"
                                        />

                                        <ToggleLeft v-else class="h-4 w-4" />

                                        {{
                                            Boolean(kawasan.status)
                                                ? "Aktif"
                                                : "Nonaktif"
                                        }}
                                    </button>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-1">
                                        <button
                                            type="button"
                                            title="Lihat detail"
                                            aria-label="Lihat detail"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-400 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            @click="openDetail(kawasan)"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </button>

                                        <button
                                            type="button"
                                            title="Edit"
                                            aria-label="Edit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-amber-50 hover:text-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:text-slate-400 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                            @click="openEdit(kawasan)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>

                                        <button
                                            type="button"
                                            title="Hapus"
                                            aria-label="Hapus"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 dark:text-slate-400 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                            @click="openDelete(kawasan)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty -->
                <div
                    v-else
                    class="flex min-h-[360px] flex-col items-center justify-center px-6 py-12 text-center"
                >
                    <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"
                    >
                        <Building2 class="h-8 w-8 text-slate-400" />
                    </div>

                    <h3
                        class="text-base font-semibold text-slate-900 dark:text-white"
                    >
                        Belum ada profil kawasan
                    </h3>

                    <p
                        class="mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                    >
                        {{
                            search
                                ? "Tidak ditemukan profil kawasan yang sesuai dengan pencarian."
                                : "Silakan tambahkan profil kawasan untuk mulai mengelola informasi kawasan."
                        }}
                    </p>

                    <button
                        v-if="!search"
                        type="button"
                        class="mt-5 inline-flex min-h-10 items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:bg-white dark:text-slate-900"
                        @click="openCreate"
                    >
                        <Plus class="h-4 w-4" />
                        Tambah Profil
                    </button>
                </div>

                <!-- Pagination -->
                <div
                    v-if="profilKawasans.data.length"
                    class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Menampilkan
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ profilKawasans.from ?? 0 }}
                        </span>
                        -
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ profilKawasans.to ?? 0 }}
                        </span>
                        dari
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ profilKawasans.total }}
                        </span>
                        data
                    </p>

                    <div
                        class="flex items-center gap-1"
                        aria-label="Pagination"
                    >
                        <button
                            type="button"
                            aria-label="Halaman pertama"
                            :disabled="!hasPreviousPage"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                            @click="goToPage(profilKawasans.first_page_url)"
                        >
                            <ChevronsLeft class="h-4 w-4" />
                        </button>

                        <button
                            type="button"
                            aria-label="Halaman sebelumnya"
                            :disabled="!hasPreviousPage"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                            @click="goToPage(profilKawasans.prev_page_url)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>

                        <template
                            v-for="(link, index) in profilKawasans.links"
                            :key="`${link.label}-${index}`"
                        >
                            <button
                                v-if="
                                    link.url &&
                                    !link.label.includes('Previous') &&
                                    !link.label.includes('Next')
                                "
                                type="button"
                                :aria-current="link.active ? 'page' : undefined"
                                class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition"
                                :class="
                                    link.active
                                        ? 'border-slate-900 bg-slate-900 text-white dark:border-white dark:bg-white dark:text-slate-900'
                                        : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800'
                                "
                                @click="goToPage(link.url)"
                            >
                                {{ link.label }}
                            </button>
                        </template>

                        <button
                            type="button"
                            aria-label="Halaman berikutnya"
                            :disabled="!hasNextPage"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                            @click="goToPage(profilKawasans.next_page_url)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </button>

                        <button
                            type="button"
                            aria-label="Halaman terakhir"
                            :disabled="!hasNextPage"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                            @click="goToPage(profilKawasans.last_page_url)"
                        >
                            <ChevronsRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- =====================================================
             FORM MODAL
        ====================================================== -->

        <Transition name="modal">
            <div
                v-if="showFormModal"
                class="fixed inset-0 z-[80] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="'profil-kawasan-form-title'"
                @keydown="handleFormKeydown"
            >
                <div
                    class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                    aria-hidden="true"
                    @click="closeForm"
                />

                <section
                    ref="formModalRef"
                    class="relative flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
                >
                    <!-- Header -->
                    <div
                        class="flex shrink-0 items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800 sm:px-6"
                    >
                        <div class="min-w-0">
                            <h2
                                id="profil-kawasan-form-title"
                                class="text-lg font-bold text-slate-900 dark:text-white"
                            >
                                {{ modalTitle }}
                            </h2>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Lengkapi informasi profil kawasan dengan data
                                yang akurat.
                            </p>
                        </div>

                        <button
                            type="button"
                            aria-label="Tutup modal"
                            :disabled="isSubmitting"
                            class="ml-4 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800 dark:hover:text-white"
                            @click="closeForm"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body -->
                    <form
                        class="min-h-0 overflow-y-auto"
                        @submit.prevent="submitForm"
                    >
                        <div class="space-y-6 p-5 sm:p-6">
                            <!-- Basic -->
                            <div>
                                <div class="mb-4 flex items-center gap-2">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        <Building2 class="h-4 w-4" />
                                    </div>

                                    <div>
                                        <h3
                                            class="text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Informasi Utama
                                        </h3>

                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Data dasar profil kawasan.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <!-- Judul -->
                                    <div class="md:col-span-2">
                                        <label
                                            for="judul"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Judul Kawasan
                                            <span class="text-red-500">
                                                *
                                            </span>
                                        </label>

                                        <input
                                            id="judul"
                                            v-model="form.judul"
                                            type="text"
                                            maxlength="255"
                                            autocomplete="off"
                                            :aria-invalid="!!errors.judul"
                                            class="h-11 w-full rounded-xl border bg-white px-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-2 dark:bg-slate-950 dark:text-white"
                                            :class="
                                                errors.judul
                                                    ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
                                                    : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-700'
                                            "
                                            placeholder="Contoh: Kawasan Industri Tanjung Buton"
                                        />

                                        <p
                                            v-if="errors.judul"
                                            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
                                        >
                                            {{ errors.judul }}
                                        </p>
                                    </div>

                                    <!-- Slug -->
                                    <div>
                                        <label
                                            for="slug"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Slug
                                        </label>

                                        <input
                                            id="slug"
                                            :value="form.slug"
                                            type="text"
                                            readonly
                                            disabled
                                            aria-describedby="slug-help"
                                            class="h-11 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3.5 text-sm text-slate-500 outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400"
                                        />

                                        <p
                                            id="slug-help"
                                            class="mt-1.5 text-xs text-slate-400"
                                        >
                                            Slug dibuat otomatis dari judul.
                                        </p>
                                    </div>

                                    <!-- Lokasi -->
                                    <div>
                                        <label
                                            for="lokasi"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Lokasi
                                        </label>

                                        <input
                                            id="lokasi"
                                            v-model="form.lokasi"
                                            type="text"
                                            maxlength="255"
                                            :aria-invalid="!!errors.lokasi"
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                            placeholder="Contoh: Kabupaten Siak, Riau"
                                        />

                                        <p
                                            v-if="errors.lokasi"
                                            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
                                        >
                                            {{ errors.lokasi }}
                                        </p>
                                    </div>

                                    <!-- Deskripsi -->
                                    <div class="md:col-span-2">
                                        <label
                                            for="deskripsi"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Deskripsi
                                        </label>

                                        <textarea
                                            id="deskripsi"
                                            v-model="form.deskripsi"
                                            rows="5"
                                            :aria-invalid="!!errors.deskripsi"
                                            class="w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                            placeholder="Masukkan deskripsi kawasan..."
                                        />

                                        <p
                                            v-if="errors.deskripsi"
                                            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
                                        >
                                            {{ errors.deskripsi }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- GIS -->
                            <div
                                class="border-t border-slate-200 pt-6 dark:border-slate-800"
                            >
                                <div class="mb-4 flex items-center gap-2">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                                    >
                                        <MapPin class="h-4 w-4" />
                                    </div>

                                    <div>
                                        <h3
                                            class="text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Data GIS
                                        </h3>

                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Koordinat titik dan batas kawasan.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <!-- Latitude -->
                                    <div>
                                        <label
                                            for="latitude"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Latitude
                                        </label>

                                        <input
                                            id="latitude"
                                            v-model="form.latitude"
                                            type="number"
                                            step="any"
                                            inputmode="decimal"
                                            :aria-invalid="!!errors.latitude"
                                            class="h-11 w-full rounded-xl border bg-white px-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-2 dark:bg-slate-950 dark:text-white"
                                            :class="
                                                errors.latitude
                                                    ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
                                                    : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-700'
                                            "
                                            placeholder="-0.1234567"
                                        />

                                        <p
                                            v-if="errors.latitude"
                                            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
                                        >
                                            {{ errors.latitude }}
                                        </p>
                                    </div>

                                    <!-- Longitude -->
                                    <div>
                                        <label
                                            for="longitude"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Longitude
                                        </label>

                                        <input
                                            id="longitude"
                                            v-model="form.longitude"
                                            type="number"
                                            step="any"
                                            inputmode="decimal"
                                            :aria-invalid="!!errors.longitude"
                                            class="h-11 w-full rounded-xl border bg-white px-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-2 dark:bg-slate-950 dark:text-white"
                                            :class="
                                                errors.longitude
                                                    ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
                                                    : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-700'
                                            "
                                            placeholder="101.1234567"
                                        />

                                        <p
                                            v-if="errors.longitude"
                                            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
                                        >
                                            {{ errors.longitude }}
                                        </p>
                                    </div>

                                    <!-- GeoJSON -->
                                    <div class="md:col-span-2">
                                        <label
                                            for="batas_kawasan"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Batas Kawasan
                                            <span
                                                class="ml-1 text-xs font-normal text-slate-400"
                                            >
                                                (GeoJSON opsional)
                                            </span>
                                        </label>

                                        <textarea
                                            id="batas_kawasan"
                                            v-model="form.batas_kawasan"
                                            rows="8"
                                            spellcheck="false"
                                            :aria-invalid="
                                                !!errors.batas_kawasan
                                            "
                                            class="w-full resize-y rounded-xl border bg-slate-50 px-3.5 py-3 font-mono text-xs leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:ring-2 dark:bg-slate-950 dark:text-slate-200"
                                            :class="
                                                errors.batas_kawasan
                                                    ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
                                                    : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-700'
                                            "
                                            placeholder='{"type":"Polygon","coordinates":[...]}'
                                        />

                                        <p
                                            v-if="errors.batas_kawasan"
                                            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
                                        >
                                            {{ errors.batas_kawasan }}
                                        </p>

                                        <p
                                            v-else
                                            class="mt-1.5 text-xs text-slate-400"
                                        >
                                            Masukkan object GeoJSON dalam format
                                            JSON valid.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional -->
                            <div
                                class="border-t border-slate-200 pt-6 dark:border-slate-800"
                            >
                                <div class="mb-4 flex items-center gap-2">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400"
                                    >
                                        <FileText class="h-4 w-4" />
                                    </div>

                                    <div>
                                        <h3
                                            class="text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            Informasi Tambahan
                                        </h3>

                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Luas, tahun berdiri, gambar, dan
                                            status.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <!-- Luas -->
                                    <div>
                                        <label
                                            for="luas_kawasan"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Luas Kawasan
                                            <span
                                                class="text-xs font-normal text-slate-400"
                                            >
                                                (ha)
                                            </span>
                                        </label>

                                        <div class="relative">
                                            <Ruler
                                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                            />

                                            <input
                                                id="luas_kawasan"
                                                v-model="form.luas_kawasan"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                inputmode="decimal"
                                                :aria-invalid="
                                                    !!errors.luas_kawasan
                                                "
                                                class="h-11 w-full rounded-xl border bg-white pl-10 pr-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-2 dark:bg-slate-950 dark:text-white"
                                                :class="
                                                    errors.luas_kawasan
                                                        ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
                                                        : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-700'
                                                "
                                                placeholder="1000.00"
                                            />
                                        </div>

                                        <p
                                            v-if="errors.luas_kawasan"
                                            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
                                        >
                                            {{ errors.luas_kawasan }}
                                        </p>
                                    </div>

                                    <!-- Tahun -->
                                    <div>
                                        <label
                                            for="tahun_berdiri"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Tahun Berdiri
                                        </label>

                                        <div class="relative">
                                            <CalendarDays
                                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                            />

                                            <input
                                                id="tahun_berdiri"
                                                v-model="form.tahun_berdiri"
                                                type="number"
                                                min="1800"
                                                :max="new Date().getFullYear()"
                                                step="1"
                                                inputmode="numeric"
                                                :aria-invalid="
                                                    !!errors.tahun_berdiri
                                                "
                                                class="h-11 w-full rounded-xl border bg-white pl-10 pr-3.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-2 dark:bg-slate-950 dark:text-white"
                                                :class="
                                                    errors.tahun_berdiri
                                                        ? 'border-red-400 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500'
                                                        : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500/20 dark:border-slate-700'
                                                "
                                                placeholder="2026"
                                            />
                                        </div>

                                        <p
                                            v-if="errors.tahun_berdiri"
                                            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
                                        >
                                            {{ errors.tahun_berdiri }}
                                        </p>
                                    </div>

                                    <!-- Image -->
                                    <div class="md:col-span-2">
                                        <label
                                            for="gambar"
                                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                                        >
                                            Gambar Kawasan
                                        </label>

                                        <input
                                            id="gambar"
                                            ref="gambarInputRef"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="sr-only"
                                            @change="handleImageChange"
                                        />

                                        <div
                                            class="overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-950"
                                        >
                                            <div
                                                v-if="imageUrl"
                                                class="relative"
                                            >
                                                <img
                                                    :src="imageUrl"
                                                    alt="Preview gambar kawasan"
                                                    class="h-64 w-full object-cover sm:h-72"
                                                />

                                                <div
                                                    class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-3 bg-gradient-to-t from-black/70 to-transparent px-4 pb-4 pt-10"
                                                >
                                                    <label
                                                        for="gambar"
                                                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-white/95 px-3 py-2 text-xs font-semibold text-slate-900 shadow-sm transition hover:bg-white"
                                                    >
                                                        <ImageIcon
                                                            class="h-4 w-4"
                                                        />
                                                        Ganti Gambar
                                                    </label>

                                                    <button
                                                        v-if="!removeGambar"
                                                        type="button"
                                                        class="inline-flex items-center gap-2 rounded-lg bg-red-500/90 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-500"
                                                        @click="
                                                            removeSelectedImage
                                                        "
                                                    >
                                                        <Trash2
                                                            class="h-4 w-4"
                                                        />
                                                        Hapus
                                                    </button>

                                                    <button
                                                        v-else
                                                        type="button"
                                                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-500/90 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500"
                                                        @click="
                                                            restoreExistingImage
                                                        "
                                                    >
                                                        <Check
                                                            class="h-4 w-4"
                                                        />
                                                        Pertahankan
                                                    </button>
                                                </div>

                                                <div
                                                    v-if="removeGambar"
                                                    class="absolute left-3 top-3 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow"
                                                >
                                                    Gambar akan dihapus
                                                </div>
                                            </div>

                                            <label
                                                v-else
                                                for="gambar"
                                                class="flex min-h-48 cursor-pointer flex-col items-center justify-center px-6 py-10 text-center transition hover:bg-white dark:hover:bg-slate-900"
                                            >
                                                <div
                                                    class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm dark:bg-slate-800"
                                                >
                                                    <ImageIcon
                                                        class="h-6 w-6"
                                                    />
                                                </div>

                                                <span
                                                    class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                                                >
                                                    Pilih gambar
                                                </span>

                                                <span
                                                    class="mt-1 text-xs text-slate-400"
                                                >
                                                    JPG, JPEG, PNG, WEBP · Maks.
                                                    1 MB
                                                </span>
                                            </label>
                                        </div>

                                        <p
                                            v-if="imageError"
                                            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
                                        >
                                            {{ imageError }}
                                        </p>
                                    </div>

                                    <!-- Status -->
                                    <div class="md:col-span-2">
                                        <div
                                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-950"
                                        >
                                            <div>
                                                <p
                                                    class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                                >
                                                    Status Publikasi
                                                </p>

                                                <p
                                                    class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                                >
                                                    Tentukan apakah profil
                                                    kawasan aktif ditampilkan.
                                                </p>
                                            </div>

                                            <button
                                                type="button"
                                                role="switch"
                                                :aria-checked="form.status"
                                                :aria-label="
                                                    form.status
                                                        ? 'Nonaktifkan profil kawasan'
                                                        : 'Aktifkan profil kawasan'
                                                "
                                                class="relative inline-flex h-7 w-12 shrink-0 rounded-full transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950"
                                                :class="
                                                    form.status
                                                        ? 'bg-emerald-500'
                                                        : 'bg-slate-300 dark:bg-slate-700'
                                                "
                                                @click="
                                                    form.status = !form.status
                                                "
                                            >
                                                <span
                                                    class="inline-block h-5 w-5 translate-y-1 rounded-full bg-white shadow transition"
                                                    :class="
                                                        form.status
                                                            ? 'translate-x-6'
                                                            : 'translate-x-1'
                                                    "
                                                />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="sticky bottom-0 flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:justify-end sm:px-6 dark:border-slate-800 dark:bg-slate-900/95"
                        >
                            <button
                                type="button"
                                :disabled="isSubmitting"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeForm"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                :aria-busy="isSubmitting"
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100"
                            >
                                <LoaderCircle
                                    v-if="isSubmitting"
                                    class="h-4 w-4 animate-spin"
                                />

                                <Check v-else class="h-4 w-4" />

                                {{
                                    isSubmitting
                                        ? "Menyimpan..."
                                        : isEditing
                                          ? "Simpan Perubahan"
                                          : "Simpan Profil"
                                }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </Transition>

        <!-- =====================================================
             DETAIL MODAL
        ====================================================== -->

        <Transition name="modal">
            <div
                v-if="showDetailModal && selectedKawasan"
                class="fixed inset-0 z-[80] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="profil-kawasan-detail-title"
                @keydown="handleDetailKeydown"
            >
                <div
                    class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                    aria-hidden="true"
                    @click="closeDetail"
                />

                <section
                    ref="detailModalRef"
                    class="relative flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
                >
                    <div
                        class="flex shrink-0 items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800 sm:px-6"
                    >
                        <div>
                            <h2
                                id="profil-kawasan-detail-title"
                                class="text-lg font-bold text-slate-900 dark:text-white"
                            >
                                Detail Profil Kawasan
                            </h2>

                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap kawasan.
                            </p>
                        </div>

                        <button
                            type="button"
                            aria-label="Tutup detail"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:bg-slate-800 dark:hover:text-white"
                            @click="closeDetail"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="min-h-0 overflow-y-auto p-5 sm:p-6">
                        <div class="space-y-6">
                            <!-- Image -->
                            <div
                                v-if="selectedKawasan.gambar"
                                class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700"
                            >
                                <img
                                    :src="getImageUrl(selectedKawasan.gambar)"
                                    :alt="selectedKawasan.judul"
                                    class="max-h-[380px] w-full object-cover"
                                />
                            </div>

                            <!-- Title -->
                            <div>
                                <div
                                    class="mb-2 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="
                                        Boolean(selectedKawasan.status)
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-current"
                                    />

                                    {{
                                        Boolean(selectedKawasan.status)
                                            ? "Aktif"
                                            : "Nonaktif"
                                    }}
                                </div>

                                <h3
                                    class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ selectedKawasan.judul }}
                                </h3>

                                <p
                                    class="mt-1 font-mono text-xs text-slate-400"
                                >
                                    /{{ selectedKawasan.slug }}
                                </p>
                            </div>

                            <!-- Info grid -->
                            <div
                                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                            >
                                <div
                                    class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <div
                                        class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        <Ruler class="h-4 w-4" />
                                        Luas
                                    </div>

                                    <p
                                        class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{
                                            selectedKawasan.luas_kawasan !==
                                                null &&
                                            selectedKawasan.luas_kawasan !==
                                                undefined
                                                ? `${formatNumber(
                                                      selectedKawasan.luas_kawasan,
                                                  )} ha`
                                                : "-"
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <div
                                        class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        <CalendarDays class="h-4 w-4" />
                                        Tahun Berdiri
                                    </div>

                                    <p
                                        class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{
                                            selectedKawasan.tahun_berdiri ?? "-"
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <div
                                        class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        <MapPin class="h-4 w-4" />
                                        Lokasi
                                    </div>

                                    <p
                                        class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ selectedKawasan.lokasi || "-" }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <div
                                        class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Latitude
                                    </div>

                                    <p
                                        class="font-mono text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{
                                            formatCoordinate(
                                                selectedKawasan.latitude,
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <div
                                        class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Longitude
                                    </div>

                                    <p
                                        class="font-mono text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{
                                            formatCoordinate(
                                                selectedKawasan.longitude,
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <div
                                        class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                                    >
                                        Dibuat
                                    </div>

                                    <p
                                        class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{
                                            formatDate(
                                                selectedKawasan.created_at,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <div class="mb-2 flex items-center gap-2">
                                    <FileText class="h-4 w-4 text-slate-400" />

                                    <h4
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        Deskripsi
                                    </h4>
                                </div>

                                <div
                                    class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950"
                                >
                                    <p
                                        class="whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                                    >
                                        {{
                                            selectedKawasan.deskripsi ||
                                            "Belum ada deskripsi."
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- GeoJSON -->
                            <div v-if="selectedKawasan.batas_kawasan">
                                <div class="mb-2 flex items-center gap-2">
                                    <MapPin class="h-4 w-4 text-slate-400" />

                                    <h4
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        Batas Kawasan · GeoJSON
                                    </h4>
                                </div>

                                <pre
                                    class="max-h-80 overflow-auto rounded-xl border border-slate-200 bg-slate-950 p-4 font-mono text-xs leading-6 text-slate-300 dark:border-slate-700"
                                    >{{
                                        JSON.stringify(
                                            selectedKawasan.batas_kawasan,
                                            null,
                                            2,
                                        )
                                    }}</pre
                                >
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900 sm:px-6"
                    >
                        <button
                            type="button"
                            class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:bg-white dark:text-slate-900"
                            @click="closeDetail"
                        >
                            Tutup
                        </button>
                    </div>
                </section>
            </div>
        </Transition>

        <!-- =====================================================
             DELETE MODAL
        ====================================================== -->

        <Transition name="modal">
            <div
                v-if="showDeleteModal && deleteTarget"
                class="fixed inset-0 z-[90] flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-labelledby="delete-kawasan-title"
                @keydown="handleDeleteKeydown"
            >
                <div
                    class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"
                    aria-hidden="true"
                    @click="closeDelete"
                />

                <section
                    ref="deleteModalRef"
                    class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
                >
                    <div class="p-6">
                        <div
                            class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                        >
                            <Trash2 class="h-6 w-6" />
                        </div>

                        <div class="text-center">
                            <h2
                                id="delete-kawasan-title"
                                class="text-lg font-bold text-slate-900 dark:text-white"
                            >
                                Hapus Profil Kawasan?
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                Anda akan menghapus profil
                                <span
                                    class="font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    "{{ deleteTarget.judul }}"
                                </span>
                                secara permanen. Tindakan ini tidak dapat
                                dibatalkan.
                            </p>
                        </div>

                        <div
                            class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center"
                        >
                            <button
                                type="button"
                                :disabled="isDeleting"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeDelete"
                            >
                                Batal
                            </button>

                            <button
                                type="button"
                                :disabled="isDeleting"
                                :aria-busy="isDeleting"
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-slate-900"
                                @click="confirmDelete"
                            >
                                <LoaderCircle
                                    v-if="isDeleting"
                                    class="h-4 w-4 animate-spin"
                                />

                                <Trash2 v-else class="h-4 w-4" />

                                {{ isDeleting ? "Menghapus..." : "Ya, Hapus" }}
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
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

.modal-enter-from > section,
.modal-leave-to > section {
    transform: translateY(10px) scale(0.985);
}

@media (prefers-reduced-motion: reduce) {
    .modal-enter-active,
    .modal-leave-active {
        transition: opacity 0.01ms linear;
    }

    .modal-enter-from > section,
    .modal-leave-to > section {
        transform: none;
    }
}
</style>
