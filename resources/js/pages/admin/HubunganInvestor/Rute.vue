<script setup lang="ts">
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { router, usePage } from "@inertiajs/vue3";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";
import {
    ArrowDown,
    ArrowUp,
    Check,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    Clock,
    Eye,
    FileText,
    Image as ImageIcon,
    Map,
    MapPin,
    Pencil,
    Plus,
    Route as RouteIcon,
    Search,
    Trash2,
    X,
} from "lucide-vue-next";

/**
 * GET     /hubungan-investor/rute-pelayaran-lokasi
 * POST    /hubungan-investor/rute-pelayaran-lokasi
 * PUT     /hubungan-investor/rute-pelayaran-lokasi/{id}   (via POST + _method)
 * DELETE  /hubungan-investor/rute-pelayaran-lokasi/{id}
 * PATCH   /hubungan-investor/rute-pelayaran-lokasi/{id}/toggle-aktif
 * PATCH   /hubungan-investor/rute-pelayaran-lokasi/{id}/move
 */
const BASE_URL = "/admin/hubungan-investor/rute-pelayaran-lokasi";

const page = usePage();

/* ===================== TYPES ===================== */

type GeometryType = "Point" | "LineString" | "MultiLineString";

interface Geometry {
    type: GeometryType;
    coordinates: unknown;
}

interface Rute {
    id: number;
    nama_rute: string;
    jalur: string;
    jarak: number | string;
    satuan_jarak: string;
    waktu_tempuh: string;
    deskripsi: string | null;
    asal: string | null;
    tujuan: string | null;
    latitude: number | string | null;
    longitude: number | string | null;
    geometry: Geometry | null;
    gambar: string | null;
    urutan: number;
    aktif: boolean;
    created_at: string;
    updated_at?: string;
}

interface PageLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator {
    current_page: number;
    data: Rute[];
    first_page_url: string | null;
    from: number | null;
    last_page: number;
    last_page_url: string | null;
    links: PageLink[];
    next_page_url: string | null;
    path?: string;
    per_page?: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface Filters {
    search?: string;
    aktif?: string | boolean | null;
}

const props = defineProps<{
    rutes: Paginator;
    filters: Filters;
}>();

/* ===================== STYLE ===================== */

const cardClass =
    "rounded-2xl border border-slate-200/80 bg-white/90 shadow-sm shadow-slate-200/50 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80 dark:shadow-none";

const inputClass =
    "min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500";

const inputErrorClass =
    "border-red-400 focus:border-red-500 focus:ring-red-500/10 dark:border-red-500/70";

const errorClass = "mt-1.5 text-xs text-red-600 dark:text-red-400";

const labelClass =
    "mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300";

const thClass =
    "px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400";

const primaryBtnClass =
    "inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto";

const secondaryBtnClass =
    "inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800";

const rowIconBtnClass =
    "rounded-lg p-2 text-slate-400 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-40";

const closeBtnClass =
    "shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:opacity-50 dark:hover:bg-slate-800 dark:hover:text-slate-200";

const modalBackdropClass =
    "fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-950/50 p-3 backdrop-blur-sm sm:p-6";

const modalCardClass =
    "my-auto flex max-h-[calc(100dvh-1.5rem)] w-full flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-900/10 sm:max-h-[calc(100dvh-3rem)] dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/40";

const infoCardClass =
    "rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/50";

/* ===================== TABLE ===================== */

const columns = [
    { label: "No", width: "w-16", align: "text-center" },
    { label: "Rute", width: "min-w-[280px]", align: "text-left" },
    { label: "Jalur", width: "min-w-[250px]", align: "text-left" },
    { label: "Jarak & Waktu", width: "min-w-[180px]", align: "text-left" },
    { label: "Status", width: "w-32", align: "text-left" },
    { label: "Aksi", width: "w-56", align: "text-right" },
];

/* ===================== STATE ===================== */

const isInitialLoading = ref(true);
const isRequesting = ref(false);

const isPageLoading = computed(
    () => isInitialLoading.value || isRequesting.value,
);

const search = ref(props.filters?.search ?? "");

const selectedAktif = ref(
    props.filters?.aktif === true || props.filters?.aktif === "1"
        ? "1"
        : props.filters?.aktif === false || props.filters?.aktif === "0"
          ? "0"
          : "",
);

const items = computed(() => props.rutes?.data ?? []);
const total = computed(() => props.rutes?.total ?? 0);
const lastPage = computed(() => props.rutes?.last_page ?? 1);
const fromRow = computed(() => props.rutes?.from ?? 0);
const toRow = computed(() => props.rutes?.to ?? 0);

const firstPageUrl = computed(() => props.rutes?.first_page_url ?? null);
const lastPageUrl = computed(() => props.rutes?.last_page_url ?? null);
const previousPageUrl = computed(() => props.rutes?.prev_page_url ?? null);
const nextPageUrl = computed(() => props.rutes?.next_page_url ?? null);

const pageLinks = computed(() => (props.rutes?.links ?? []).slice(1, -1));

const isFiltered = computed(
    () => !!(search.value.trim() || selectedAktif.value),
);

/* ===================== MODAL ===================== */

const showFormModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);

const formMode = ref<"create" | "edit">("create");

const selected = ref<Rute | null>(null);

const processingForm = ref(false);
const processingDelete = ref(false);
const processingToggle = ref<number | null>(null);
const processingMove = ref<number | null>(null);

const formErrors = ref<Record<string, string>>({});

/* ===================== FORM ===================== */

const emptyForm = () => ({
    nama_rute: "",
    jalur: "",
    jarak: "" as string | number,
    satuan_jarak: "km",
    waktu_tempuh: "",
    deskripsi: "",
    asal: "",
    tujuan: "",
    latitude: "" as string | number,
    longitude: "" as string | number,
    geometry: "",
    urutan: "" as string | number,
    aktif: true,
    gambar: null as File | null,
});

const form = ref(emptyForm());

const imagePreview = ref<string | null>(null);
const existingImage = ref<string | null>(null);

function resetForm() {
    form.value = emptyForm();
    formErrors.value = {};

    revokeImagePreview();

    imagePreview.value = null;
    existingImage.value = null;
}

function fillForm(item: Rute) {
    form.value = {
        nama_rute: item.nama_rute ?? "",
        jalur: item.jalur ?? "",
        jarak: String(item.jarak ?? ""),
        satuan_jarak: item.satuan_jarak ?? "km",
        waktu_tempuh: item.waktu_tempuh ?? "",
        deskripsi: item.deskripsi ?? "",
        asal: item.asal ?? "",
        tujuan: item.tujuan ?? "",
        latitude:
            item.latitude !== null && item.latitude !== undefined
                ? String(item.latitude)
                : "",
        longitude:
            item.longitude !== null && item.longitude !== undefined
                ? String(item.longitude)
                : "",
        geometry: item.geometry ? JSON.stringify(item.geometry, null, 2) : "",
        urutan: String(item.urutan ?? ""),
        aktif: Boolean(item.aktif),
        gambar: null,
    };

    formErrors.value = {};

    revokeImagePreview();

    imagePreview.value = null;
    existingImage.value = item.gambar ? `/storage/${item.gambar}` : null;
}

function openCreate() {
    selected.value = null;
    formMode.value = "create";

    resetForm();

    showDetailModal.value = false;
    showFormModal.value = true;
}

function openEdit(item: Rute) {
    selected.value = item;
    formMode.value = "edit";

    fillForm(item);

    showDetailModal.value = false;
    showFormModal.value = true;
}

function closeForm() {
    if (processingForm.value) {
        return;
    }

    showFormModal.value = false;
}

/* ===================== IMAGE ===================== */

function revokeImagePreview() {
    if (imagePreview.value?.startsWith("blob:")) {
        URL.revokeObjectURL(imagePreview.value);
    }
}

function handleImage(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    form.value.gambar = file;

    revokeImagePreview();

    imagePreview.value = null;

    if (!file) {
        return;
    }

    if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) {
        formErrors.value.gambar =
            "Format gambar harus JPG, JPEG, PNG, atau WEBP.";

        form.value.gambar = null;
        input.value = "";

        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        formErrors.value.gambar = "Ukuran gambar maksimal 2 MB.";

        form.value.gambar = null;
        input.value = "";

        return;
    }

    delete formErrors.value.gambar;

    imagePreview.value = URL.createObjectURL(file);
}

/* ===================== GEOMETRY ===================== */

function geometryPreview(): string {
    if (!form.value.geometry.trim()) {
        return "";
    }

    try {
        return JSON.stringify(JSON.parse(form.value.geometry), null, 2);
    } catch {
        return form.value.geometry;
    }
}

function validateGeometryClient(): boolean {
    const value = form.value.geometry.trim();

    if (!value) {
        return true;
    }

    try {
        const geometry = JSON.parse(value);

        if (
            !geometry ||
            typeof geometry !== "object" ||
            Array.isArray(geometry)
        ) {
            formErrors.value.geometry = "Geometry harus berupa object GeoJSON.";
            return false;
        }

        if (
            !["Point", "LineString", "MultiLineString"].includes(geometry.type)
        ) {
            formErrors.value.geometry =
                "Tipe geometry hanya Point, LineString, atau MultiLineString.";
            return false;
        }

        if (!("coordinates" in geometry)) {
            formErrors.value.geometry = "Geometry harus memiliki coordinates.";
            return false;
        }

        delete formErrors.value.geometry;

        return true;
    } catch {
        formErrors.value.geometry = "Format GeoJSON tidak valid.";
        return false;
    }
}

/* ===================== SUBMIT FORM ===================== */

function submitForm() {
    if (processingForm.value) {
        return;
    }

    formErrors.value = {};

    if (!validateGeometryClient()) {
        return;
    }

    // v-model pada input type="number" menghasilkan number,
    // jadi semua nilai dipaksa menjadi string sebelum di-trim.
    const str = (value: unknown) => String(value ?? "").trim();

    processingForm.value = true;

    try {
        const formData = new FormData();

        formData.append("nama_rute", str(form.value.nama_rute));
        formData.append("jalur", str(form.value.jalur));
        formData.append("jarak", str(form.value.jarak));
        formData.append("satuan_jarak", str(form.value.satuan_jarak));
        formData.append("waktu_tempuh", str(form.value.waktu_tempuh));

        if (str(form.value.deskripsi)) {
            formData.append("deskripsi", str(form.value.deskripsi));
        }

        if (str(form.value.asal)) {
            formData.append("asal", str(form.value.asal));
        }

        if (str(form.value.tujuan)) {
            formData.append("tujuan", str(form.value.tujuan));
        }

        if (str(form.value.latitude)) {
            formData.append("latitude", str(form.value.latitude));
        }

        if (str(form.value.longitude)) {
            formData.append("longitude", str(form.value.longitude));
        }

        if (str(form.value.geometry)) {
            formData.append("geometry", str(form.value.geometry));
        }

        if (str(form.value.urutan)) {
            formData.append("urutan", str(form.value.urutan));
        }

        formData.append("aktif", form.value.aktif ? "1" : "0");

        if (form.value.gambar instanceof File) {
            formData.append("gambar", form.value.gambar);
        }

        const options = {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                const flash = (page.props as any).flash;

                if (flash?.error) {
                    formErrors.value.general = String(flash.error);
                    return; // modal tetap terbuka
                }

                showFormModal.value = false;
                showDetailModal.value = false;
                selected.value = null;

                resetForm();
            },

            onError: (errors: Record<string, string | string[]>) => {
                formErrors.value = Object.fromEntries(
                    Object.entries(errors).map(([key, value]) => [
                        key,
                        Array.isArray(value) ? value[0] : String(value),
                    ]),
                );
            },

            onFinish: () => {
                processingForm.value = false;
            },
        };

        if (formMode.value === "create") {
            router.post(BASE_URL, formData, options);
            return;
        }

        if (!selected.value) {
            processingForm.value = false;
            return;
        }

        formData.append("_method", "PUT");

        router.post(`${BASE_URL}/${selected.value.id}`, formData, options);
    } catch (error) {
        console.error(error);
        processingForm.value = false;
    }
}

/* ===================== DETAIL ===================== */

function openDetail(item: Rute) {
    selected.value = item;
    showDetailModal.value = true;
}

function closeDetail() {
    showDetailModal.value = false;
}

/* ===================== LEAFLET MAP ===================== */

// Perbaiki ikon marker default yang hilang saat memakai Vite
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const detailMapEl = ref<HTMLElement | null>(null);
let detailMap: L.Map | null = null;

// geometry bisa berupa object atau string JSON (tergantung cast di model)
function parseGeometry(value: unknown): Geometry | null {
    if (!value) return null;

    if (typeof value === "string") {
        try {
            return JSON.parse(value) as Geometry;
        } catch {
            return null;
        }
    }

    return value as Geometry;
}

// decimal dari Laravel bisa berupa string
function toNumber(value: unknown): number | null {
    if (value === null || value === undefined || value === "") return null;

    const n = Number(value);

    return Number.isFinite(n) ? n : null;
}

// Titik awal & akhir garis, format GeoJSON: [longitude, latitude]
function lineEnds(geometry: Geometry | null) {
    if (!geometry) return null;

    const c = geometry.coordinates as any;
    let first: number[] | undefined;
    let last: number[] | undefined;

    if (geometry.type === "LineString" && Array.isArray(c) && c.length > 1) {
        first = c[0];
        last = c[c.length - 1];
    }

    if (geometry.type === "MultiLineString" && Array.isArray(c) && c.length) {
        first = c[0]?.[0];
        last = c[c.length - 1]?.[c[c.length - 1].length - 1];
    }

    if (!first || !last) return null;

    return {
        start: [Number(first[1]), Number(first[0])] as [number, number],
        end: [Number(last[1]), Number(last[0])] as [number, number],
    };
}

const hasMapData = computed(() => {
    const item = selected.value;

    if (!item) return false;

    return (
        !!parseGeometry(item.geometry) ||
        (toNumber(item.latitude) !== null && toNumber(item.longitude) !== null)
    );
});

function destroyDetailMap() {
    if (detailMap) {
        detailMap.remove();
        detailMap = null;
    }
}

// Jika koordinat tertulis [latitude, longitude] (terbalik), tukar otomatis.
// Wilayah Indonesia: latitude -11..6, longitude 95..141.
function fixPair(c: any): any {
    if (!Array.isArray(c)) return c;

    if (typeof c[0] === "number" || typeof c[0] === "string") {
        const a = Number(c[0]);
        const b = Number(c[1]);

        if (a >= -11 && a <= 6 && b >= 95 && b <= 141) {
            return [b, a];
        }

        return [a, b];
    }

    return c.map(fixPair);
}

function normalizeGeometry(g: Geometry | null): Geometry | null {
    if (!g || !g.coordinates) return null;

    return { type: g.type, coordinates: fixPair(g.coordinates) };
}

// Penanda bulat yang digambar dengan CSS (tidak bergantung pada file gambar)
function pinIcon(color: string) {
    return L.divIcon({
        className: "",
        iconSize: [26, 26],
        iconAnchor: [13, 13],
        html: `<span style="display:block;width:26px;height:26px;border-radius:9999px;background:${color};border:4px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.45)"></span>`,
    });
}

function renderDetailMap() {
    destroyDetailMap();

    const item = selected.value;

    if (!item || !detailMapEl.value) return;

    const map = L.map(detailMapEl.value, { scrollWheelZoom: false });

    detailMap = map;

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: "&copy; OpenStreetMap",
    }).addTo(map);

    // Tampilan awal agar layer bisa digambar sebelum fitBounds
    map.setView([-2.5, 118], 5);

    const group = L.featureGroup().addTo(map);
    const geometry = normalizeGeometry(parseGeometry(item.geometry));

    // Garis rute (garis putih di bawah supaya kontras dengan peta)
    if (geometry) {
        try {
            L.geoJSON(geometry as any, {
                style: { color: "#ffffff", weight: 9, opacity: 0.9 },
                pointToLayer: (_f, latlng) =>
                    L.marker(latlng, { icon: pinIcon("#2563eb") }),
            }).addTo(group);

            L.geoJSON(geometry as any, {
                style: { color: "#2563eb", weight: 5, opacity: 1 },
                pointToLayer: (_f, latlng) =>
                    L.marker(latlng, { icon: pinIcon("#2563eb") }),
            }).addTo(group);
        } catch (error) {
            console.error("GeoJSON tidak valid:", error);
        }

        const ends = lineEnds(geometry);

        if (ends) {
            L.marker(ends.start, { icon: pinIcon("#10b981") })
                .addTo(group)
                .bindTooltip(`Awal: ${item.asal || "-"}`, {
                    direction: "top",
                    offset: [0, -12],
                });

            L.marker(ends.end, { icon: pinIcon("#ef4444") })
                .addTo(group)
                .bindTooltip(`Akhir: ${item.tujuan || "-"}`, {
                    direction: "top",
                    offset: [0, -12],
                });
        }
    }

    // Titik lokasi utama dari kolom latitude & longitude
    const lat = toNumber(item.latitude);
    const lng = toNumber(item.longitude);

    if (lat !== null && lng !== null) {
        L.marker([lat, lng], { icon: pinIcon("#f59e0b") })
            .addTo(group)
            .bindTooltip(item.nama_rute, {
                permanent: true,
                direction: "top",
                offset: [0, -14],
            });
    }

    // Tombol untuk kembali fokus ke seluruh rute
    const fit = () => {
        if (!detailMap) return;

        detailMap.invalidateSize();

        const b = group.getBounds();

        if (b.isValid()) {
            detailMap.fitBounds(b, { padding: [40, 40], maxZoom: 14 });
        }
    };

    const FocusControl = L.Control.extend({
        onAdd() {
            const btn = L.DomUtil.create("button", "leaflet-bar");
            btn.type = "button";
            btn.textContent = "Fokus ke rute";
            btn.style.cssText =
                "background:#fff;color:#1e293b;padding:6px 10px;font-size:12px;font-weight:600;cursor:pointer;border:0;border-radius:6px;";
            L.DomEvent.disableClickPropagation(btn);
            L.DomEvent.on(btn, "click", fit);
            return btn;
        },
    });

    new FocusControl({ position: "topright" }).addTo(map);

    // Ukur ulang setelah animasi modal selesai, lalu fokus ke rute
    fit();
    setTimeout(fit, 350);
}

watch(showDetailModal, async (open) => {
    if (open) {
        await nextTick();
        renderDetailMap();
    } else {
        destroyDetailMap();
    }
});

/* ===================== DELETE ===================== */

function openDelete(item: Rute) {
    selected.value = item;
    showDeleteModal.value = true;
}

function closeDelete() {
    if (!processingDelete.value) {
        showDeleteModal.value = false;
    }
}

function deleteRute() {
    const item = selected.value;

    if (!item || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(`${BASE_URL}/${item.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDeleteModal.value = false;
            selected.value = null;
        },

        onFinish: () => {
            processingDelete.value = false;
        },
    });
}

/* ===================== TOGGLE AKTIF ===================== */

function toggleAktif(item: Rute) {
    if (processingToggle.value !== null) {
        return;
    }

    processingToggle.value = item.id;

    router.patch(
        `${BASE_URL}/${item.id}/toggle-aktif`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processingToggle.value = null;
            },
        },
    );
}

/* ===================== MOVE ===================== */

function moveRute(item: Rute, direction: "up" | "down") {
    if (processingMove.value !== null) {
        return;
    }

    processingMove.value = item.id;

    router.patch(
        `${BASE_URL}/${item.id}/move`,
        { direction },
        {
            preserveScroll: true,
            onFinish: () => {
                processingMove.value = null;
            },
        },
    );
}

/* ===================== FILTER ===================== */

let searchTimer: ReturnType<typeof setTimeout> | undefined;

function submitFilter() {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    router.get(
        BASE_URL,
        {
            search: search.value.trim() || undefined,
            aktif: selectedAktif.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ["rutes", "filters"],
        },
    );
}

function submitSearch() {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        submitFilter();
    }, 400);
}

function clearSearchInput() {
    search.value = "";
    submitFilter();
}

function clearFilters() {
    search.value = "";
    selectedAktif.value = "";
    submitFilter();
}

/* ===================== PAGINATION ===================== */

function goToPage(url: string | null) {
    if (!url) {
        return;
    }

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ["rutes", "filters"],
        },
    );
}

const paginationPageLabel = (label: string) =>
    label
        .replace(/<[^>]*>/g, "")
        .replace(/&[a-z#0-9]+;/gi, "")
        .trim();

const navButtonClass = (url: string | null) =>
    url
        ? "text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
        : "cursor-not-allowed text-slate-300 dark:text-slate-600";

/* ===================== FORMAT ===================== */

const formatDate = (value: string | null | undefined) => {
    if (!value) {
        return "-";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", { dateStyle: "medium" }).format(
        date,
    );
};

const formatNumber = (value: number | string) =>
    new Intl.NumberFormat("id-ID", { maximumFractionDigits: 2 }).format(
        Number(value),
    );

const geometryTypeLabel = (geometry: Geometry | string | null) =>
    parseGeometry(geometry)?.type ?? "Tidak ada geometry";

const imageUrl = (path: string | null) => (path ? `/storage/${path}` : null);

/* ===================== LIFECYCLE ===================== */

const offStart = router.on("start", (event) => {
    if (!event.detail.visit?.preserveState) {
        isRequesting.value = true;
    }
});

const offFinish = router.on("finish", () => {
    isRequesting.value = false;
});

const onKey = (event: KeyboardEvent) => {
    if (event.key !== "Escape") {
        return;
    }

    closeForm();
    closeDetail();
    closeDelete();
};

onMounted(() => {
    window.addEventListener("keydown", onKey);

    requestAnimationFrame(() => {
        isInitialLoading.value = false;
    });
});

onBeforeUnmount(() => {
    offStart();
    offFinish();

    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    destroyDetailMap();
    revokeImagePreview();

    window.removeEventListener("keydown", onKey);
});
</script>

<template>
    <div
        class="relative min-h-full overflow-hidden bg-slate-50 transition-colors duration-300 dark:bg-[#07111f]"
        :aria-busy="isPageLoading ? 'true' : 'false'"
    >
        <!-- DECORATIVE BACKGROUND -->
        <div
            class="pointer-events-none absolute inset-0 z-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="blob-shape absolute -left-24 -top-32 h-96 w-96 rounded-full bg-gradient-to-br from-blue-400/30 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/25 dark:via-indigo-500/15 dark:to-transparent"
            ></div>

            <div
                class="blob-shape-delayed absolute -right-20 top-0 h-80 w-80 rounded-full bg-gradient-to-tr from-sky-300/30 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/20 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <div
                class="blob-shape-slow absolute left-[30%] -top-40 h-72 w-72 rounded-full bg-gradient-to-br from-indigo-300/25 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/15 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <div class="absolute inset-0 opacity-40 dark:opacity-20">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px]"
                ></div>
            </div>

            <div
                class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-b from-transparent via-slate-50/40 to-slate-50/90 dark:via-slate-950/40 dark:to-[#07111f]/95"
            ></div>
        </div>

        <!-- CONTENT -->
        <div
            class="relative z-10 mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <!-- SKELETON -->
            <div v-if="isInitialLoading" class="animate-pulse" role="status">
                <span class="sr-only">Memuat data...</span>

                <div class="mb-6 flex items-center gap-3">
                    <div
                        class="size-10 rounded-xl bg-slate-200 dark:bg-slate-800"
                    ></div>

                    <div class="space-y-2">
                        <div
                            class="h-5 w-56 rounded-md bg-slate-200 dark:bg-slate-800"
                        ></div>
                        <div
                            class="h-4 w-80 max-w-full rounded-md bg-slate-200 dark:bg-slate-800"
                        ></div>
                    </div>
                </div>

                <div :class="[cardClass, 'mb-5 p-4 sm:p-5']">
                    <div class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                        <div
                            class="h-11 rounded-xl bg-slate-200 dark:bg-slate-800"
                        ></div>
                        <div
                            class="h-11 rounded-xl bg-slate-200 dark:bg-slate-800"
                        ></div>
                        <div
                            class="h-11 w-32 rounded-xl bg-slate-200 dark:bg-slate-800"
                        ></div>
                    </div>
                </div>

                <div :class="[cardClass, 'space-y-4 p-6']">
                    <div
                        v-for="row in 6"
                        :key="row"
                        class="flex items-center gap-4"
                    >
                        <div
                            class="size-10 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"
                        ></div>

                        <div class="flex-1 space-y-2">
                            <div
                                class="h-4 w-1/3 rounded bg-slate-200 dark:bg-slate-800"
                            ></div>

                            <div
                                class="h-3 w-1/5 rounded bg-slate-200 dark:bg-slate-800"
                            ></div>
                        </div>

                        <div
                            class="h-4 w-24 rounded bg-slate-200 dark:bg-slate-800"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- ACTUAL CONTENT -->
            <template v-else>
                <!-- HEADER -->
                <div
                    class="mb-6 flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex min-w-0 items-start gap-3 sm:items-center">
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-600/10 text-blue-600 shadow-sm dark:bg-blue-400/10 dark:text-blue-400"
                        >
                            <RouteIcon class="size-5" />
                        </div>

                        <div class="min-w-0">
                            <h1
                                class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                            >
                                Rute Pelayaran & Lokasi
                            </h1>

                            <p
                                class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                            >
                                Kelola informasi rute, lokasi, jarak, dan
                                geometry kawasan.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        :class="primaryBtnClass"
                        @click="openCreate"
                    >
                        <Plus class="size-4" />
                        Tambah Rute
                    </button>
                </div>

                <!-- FILTER -->
                <div :class="[cardClass, 'mb-5 p-4 sm:p-5']">
                    <div class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="search"
                                type="text"
                                maxlength="100"
                                aria-label="Cari rute"
                                placeholder="Cari nama rute, jalur, asal, atau tujuan..."
                                :class="[inputClass, 'py-2.5 pl-10 pr-11']"
                                @input="submitSearch"
                                @keydown.enter.prevent="submitFilter"
                            />

                            <button
                                v-if="search"
                                type="button"
                                title="Hapus pencarian"
                                aria-label="Hapus pencarian"
                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:hover:bg-slate-700 dark:hover:text-slate-200"
                                @click="clearSearchInput"
                            >
                                <X class="size-4" />
                            </button>
                        </div>

                        <select
                            v-model="selectedAktif"
                            aria-label="Filter status rute"
                            :class="inputClass"
                            @change="submitFilter"
                        >
                            <option value="">Semua status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak aktif</option>
                        </select>

                        <button
                            v-if="isFiltered"
                            type="button"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="clearFilters"
                        >
                            <X class="size-4" />
                            Reset
                        </button>
                    </div>
                </div>

                <!-- TABLE CARD -->
                <div :class="[cardClass, 'overflow-hidden']">
                    <div
                        class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5 md:flex-row md:items-center md:justify-between dark:border-slate-800"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
                            >
                                <Map class="size-5" />
                            </div>

                            <div class="min-w-0">
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Data Rute
                                </h2>

                                <p
                                    class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    Informasi jalur pelayaran dan lokasi
                                    kawasan.
                                </p>
                            </div>
                        </div>

                        <span
                            class="w-fit rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{ total }} data
                        </span>
                    </div>

                    <!-- DATA -->
                    <div v-if="items.length > 0">
                        <div class="overflow-x-auto overscroll-x-contain">
                            <table
                                class="w-full min-w-[1250px] text-left text-sm"
                            >
                                <thead
                                    class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                                >
                                    <tr>
                                        <th
                                            v-for="col in columns"
                                            :key="col.label"
                                            scope="col"
                                            :class="[
                                                thClass,
                                                col.width,
                                                col.align,
                                            ]"
                                        >
                                            {{ col.label }}
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800"
                                >
                                    <tr
                                        v-for="(item, index) in items"
                                        :key="item.id"
                                        class="group transition-colors duration-200 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                                    >
                                        <!-- NO -->
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex size-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                            >
                                                {{ fromRow + index }}
                                            </span>
                                        </td>

                                        <!-- RUTE -->
                                        <td class="px-6 py-4">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                                >
                                                    <img
                                                        v-if="item.gambar"
                                                        :src="
                                                            imageUrl(
                                                                item.gambar,
                                                            )!
                                                        "
                                                        :alt="item.nama_rute"
                                                        class="size-full object-cover"
                                                    />

                                                    <RouteIcon
                                                        v-else
                                                        class="size-5"
                                                    />
                                                </div>

                                                <div class="min-w-0">
                                                    <p
                                                        class="truncate font-semibold text-slate-800 dark:text-slate-100"
                                                    >
                                                        {{ item.nama_rute }}
                                                    </p>

                                                    <p
                                                        class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400"
                                                    >
                                                        Urutan #{{
                                                            item.urutan
                                                        }}
                                                    </p>

                                                    <p
                                                        class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                                    >
                                                        {{
                                                            geometryTypeLabel(
                                                                item.geometry,
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- JALUR -->
                                        <td class="px-6 py-4">
                                            <p
                                                class="font-medium text-slate-700 dark:text-slate-200"
                                            >
                                                {{ item.jalur }}
                                            </p>

                                            <p
                                                class="mt-1 flex items-center gap-1 text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                <MapPin
                                                    class="size-3.5 shrink-0"
                                                />
                                                <span class="truncate">
                                                    {{
                                                        item.asal ||
                                                        "Lokasi belum diatur"
                                                    }}
                                                    <span v-if="item.tujuan">
                                                        →
                                                        {{ item.tujuan }}
                                                    </span>
                                                </span>
                                            </p>
                                        </td>

                                        <!-- JARAK -->
                                        <td class="px-6 py-4">
                                            <p
                                                class="font-semibold text-slate-800 dark:text-slate-100"
                                            >
                                                {{ formatNumber(item.jarak) }}
                                                {{ item.satuan_jarak }}
                                            </p>

                                            <p
                                                class="mt-1 flex items-center gap-1 text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                <Clock
                                                    class="size-3.5 shrink-0"
                                                />
                                                {{ item.waktu_tempuh }}
                                            </p>
                                        </td>

                                        <!-- STATUS -->
                                        <td class="px-6 py-4">
                                            <button
                                                type="button"
                                                :disabled="
                                                    processingToggle === item.id
                                                "
                                                class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60"
                                                :class="
                                                    item.aktif
                                                        ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-950/60'
                                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700'
                                                "
                                                @click="toggleAktif(item)"
                                            >
                                                <span
                                                    class="size-1.5 rounded-full"
                                                    :class="
                                                        item.aktif
                                                            ? 'bg-emerald-500'
                                                            : 'bg-slate-400'
                                                    "
                                                ></span>

                                                {{
                                                    item.aktif
                                                        ? "Aktif"
                                                        : "Tidak aktif"
                                                }}
                                            </button>
                                        </td>

                                        <!-- AKSI -->
                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-1">
                                                <button
                                                    type="button"
                                                    title="Lihat detail"
                                                    aria-label="Lihat detail rute"
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400',
                                                    ]"
                                                    @click="openDetail(item)"
                                                >
                                                    <Eye class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Edit rute"
                                                    aria-label="Edit rute"
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400',
                                                    ]"
                                                    @click="openEdit(item)"
                                                >
                                                    <Pencil class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Naikkan urutan"
                                                    aria-label="Naikkan urutan"
                                                    :disabled="
                                                        processingMove !== null
                                                    "
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/40 dark:hover:text-indigo-400',
                                                    ]"
                                                    @click="
                                                        moveRute(item, 'up')
                                                    "
                                                >
                                                    <ArrowUp class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Turunkan urutan"
                                                    aria-label="Turunkan urutan"
                                                    :disabled="
                                                        processingMove !== null
                                                    "
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/40 dark:hover:text-indigo-400',
                                                    ]"
                                                    @click="
                                                        moveRute(item, 'down')
                                                    "
                                                >
                                                    <ArrowDown class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Hapus rute"
                                                    aria-label="Hapus rute"
                                                    :class="[
                                                        rowIconBtnClass,
                                                        'hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400',
                                                    ]"
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
                        <nav
                            v-if="lastPage > 1"
                            aria-label="Paginasi"
                            class="flex flex-col gap-4 border-t border-slate-200 px-4 py-4 sm:px-6 md:flex-row md:items-center md:justify-between dark:border-slate-800"
                        >
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Menampilkan
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ fromRow }}
                                </span>
                                -
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ toRow }}
                                </span>
                                dari
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ total }}
                                </span>
                                rute
                            </p>

                            <div
                                class="flex w-full flex-wrap items-center gap-1 sm:w-auto"
                            >
                                <button
                                    type="button"
                                    title="Halaman pertama"
                                    aria-label="Halaman pertama"
                                    :disabled="!firstPageUrl"
                                    class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="navButtonClass(firstPageUrl)"
                                    @click="goToPage(firstPageUrl)"
                                >
                                    <ChevronsLeft class="size-4" />
                                </button>

                                <button
                                    type="button"
                                    title="Halaman sebelumnya"
                                    aria-label="Halaman sebelumnya"
                                    :disabled="!previousPageUrl"
                                    class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="navButtonClass(previousPageUrl)"
                                    @click="goToPage(previousPageUrl)"
                                >
                                    <ChevronLeft class="size-4" />
                                </button>

                                <button
                                    v-for="(link, index) in pageLinks"
                                    :key="`${link.label}-${index}`"
                                    type="button"
                                    :disabled="!link.url || link.active"
                                    :aria-current="
                                        link.active ? 'page' : undefined
                                    "
                                    class="min-w-9 rounded-lg px-3 py-2 text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="
                                        link.active
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : navButtonClass(link.url)
                                    "
                                    @click="goToPage(link.url)"
                                >
                                    {{ paginationPageLabel(link.label) || "…" }}
                                </button>

                                <button
                                    type="button"
                                    title="Halaman berikutnya"
                                    aria-label="Halaman berikutnya"
                                    :disabled="!nextPageUrl"
                                    class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="navButtonClass(nextPageUrl)"
                                    @click="goToPage(nextPageUrl)"
                                >
                                    <ChevronRight class="size-4" />
                                </button>

                                <button
                                    type="button"
                                    title="Halaman terakhir"
                                    aria-label="Halaman terakhir"
                                    :disabled="!lastPageUrl"
                                    class="rounded-lg p-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30"
                                    :class="navButtonClass(lastPageUrl)"
                                    @click="goToPage(lastPageUrl)"
                                >
                                    <ChevronsRight class="size-4" />
                                </button>
                            </div>
                        </nav>
                    </div>

                    <!-- EMPTY -->
                    <div v-else class="px-4 py-16 text-center sm:px-6">
                        <div
                            class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-500 dark:bg-blue-950/30 dark:text-blue-400"
                        >
                            <RouteIcon class="size-7" />
                        </div>

                        <p
                            class="mt-4 font-semibold text-slate-800 dark:text-slate-200"
                        >
                            {{
                                isFiltered
                                    ? "Rute tidak ditemukan"
                                    : "Belum ada data rute"
                            }}
                        </p>

                        <p
                            class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{
                                isFiltered
                                    ? "Tidak ditemukan data yang sesuai dengan filter."
                                    : "Tambahkan data rute pelayaran dan lokasi kawasan."
                            }}
                        </p>

                        <div
                            class="mt-5 flex flex-col justify-center gap-3 sm:flex-row"
                        >
                            <button
                                v-if="isFiltered"
                                type="button"
                                :class="secondaryBtnClass"
                                @click="clearFilters"
                            >
                                <X class="size-4" />
                                Bersihkan Filter
                            </button>

                            <button
                                type="button"
                                :class="primaryBtnClass"
                                @click="openCreate"
                            >
                                <Plus class="size-4" />
                                Tambah Rute
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- FORM MODAL -->
        <Transition name="modal">
            <div
                v-if="showFormModal"
                :class="modalBackdropClass"
                @mousedown.self="closeForm"
            >
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="form-modal-title"
                    :class="[modalCardClass, 'max-w-4xl']"
                >
                    <!-- HEADER -->
                    <div
                        class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex min-w-0 items-start gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <RouteIcon class="size-5" />
                            </div>

                            <div class="min-w-0">
                                <h2
                                    id="form-modal-title"
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    {{
                                        formMode === "create"
                                            ? "Tambah Rute"
                                            : "Edit Rute"
                                    }}
                                </h2>

                                <p
                                    class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        formMode === "create"
                                            ? "Tambahkan informasi rute pelayaran atau lokasi kawasan."
                                            : "Perbarui informasi rute yang dipilih."
                                    }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            aria-label="Tutup modal"
                            :disabled="processingForm"
                            :class="closeBtnClass"
                            @click="closeForm"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <!-- FORM -->
                    <form
                        class="flex min-h-0 flex-1 flex-col"
                        enctype="multipart/form-data"
                        @submit.prevent="submitForm"
                    >
                        <div
                            class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6"
                        >
                            <div class="grid gap-5 lg:grid-cols-2">
                                <!-- NAMA RUTE -->
                                <div>
                                    <label for="nama_rute" :class="labelClass">
                                        Nama rute
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="nama_rute"
                                        v-model="form.nama_rute"
                                        type="text"
                                        maxlength="200"
                                        required
                                        :class="[
                                            inputClass,
                                            formErrors.nama_rute &&
                                                inputErrorClass,
                                        ]"
                                        placeholder="Contoh: Pelabuhan Dumai - KITB"
                                        @input="delete formErrors.nama_rute"
                                    />

                                    <p
                                        v-if="formErrors.nama_rute"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.nama_rute }}
                                    </p>
                                </div>

                                <!-- JALUR -->
                                <div>
                                    <label for="jalur" :class="labelClass">
                                        Jalur
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="jalur"
                                        v-model="form.jalur"
                                        type="text"
                                        maxlength="255"
                                        required
                                        :class="[
                                            inputClass,
                                            formErrors.jalur && inputErrorClass,
                                        ]"
                                        placeholder="Contoh: Selat Malaka → KITB"
                                        @input="delete formErrors.jalur"
                                    />

                                    <p
                                        v-if="formErrors.jalur"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.jalur }}
                                    </p>
                                </div>

                                <!-- ASAL -->
                                <div>
                                    <label for="asal" :class="labelClass">
                                        Asal
                                    </label>

                                    <input
                                        id="asal"
                                        v-model="form.asal"
                                        type="text"
                                        maxlength="200"
                                        :class="[
                                            inputClass,
                                            formErrors.asal && inputErrorClass,
                                        ]"
                                        placeholder="Contoh: Pelabuhan Dumai"
                                    />

                                    <p
                                        v-if="formErrors.asal"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.asal }}
                                    </p>
                                </div>

                                <!-- TUJUAN -->
                                <div>
                                    <label for="tujuan" :class="labelClass">
                                        Tujuan
                                    </label>

                                    <input
                                        id="tujuan"
                                        v-model="form.tujuan"
                                        type="text"
                                        maxlength="200"
                                        :class="[
                                            inputClass,
                                            formErrors.tujuan &&
                                                inputErrorClass,
                                        ]"
                                        placeholder="Contoh: Kawasan KITB"
                                    />

                                    <p
                                        v-if="formErrors.tujuan"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.tujuan }}
                                    </p>
                                </div>

                                <!-- JARAK -->
                                <div>
                                    <label for="jarak" :class="labelClass">
                                        Jarak
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div
                                        class="grid grid-cols-[1fr_120px] gap-2"
                                    >
                                        <input
                                            id="jarak"
                                            v-model="form.jarak"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            required
                                            :class="[
                                                inputClass,
                                                formErrors.jarak &&
                                                    inputErrorClass,
                                            ]"
                                            placeholder="0"
                                        />

                                        <input
                                            v-model="form.satuan_jarak"
                                            type="text"
                                            maxlength="20"
                                            required
                                            :class="[
                                                inputClass,
                                                formErrors.satuan_jarak &&
                                                    inputErrorClass,
                                            ]"
                                            placeholder="km"
                                        />
                                    </div>

                                    <p
                                        v-if="formErrors.jarak"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.jarak }}
                                    </p>
                                </div>

                                <!-- WAKTU -->
                                <div>
                                    <label
                                        for="waktu_tempuh"
                                        :class="labelClass"
                                    >
                                        Waktu tempuh
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="waktu_tempuh"
                                        v-model="form.waktu_tempuh"
                                        type="text"
                                        maxlength="100"
                                        required
                                        :class="[
                                            inputClass,
                                            formErrors.waktu_tempuh &&
                                                inputErrorClass,
                                        ]"
                                        placeholder="Contoh: ± 4 jam"
                                    />

                                    <p
                                        v-if="formErrors.waktu_tempuh"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.waktu_tempuh }}
                                    </p>
                                </div>

                                <!-- LATITUDE -->
                                <div>
                                    <label for="latitude" :class="labelClass">
                                        Latitude
                                    </label>

                                    <input
                                        id="latitude"
                                        v-model="form.latitude"
                                        type="number"
                                        step="0.0000001"
                                        min="-90"
                                        max="90"
                                        :class="[
                                            inputClass,
                                            formErrors.latitude &&
                                                inputErrorClass,
                                        ]"
                                        placeholder="-1.1234567"
                                    />

                                    <p
                                        v-if="formErrors.latitude"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.latitude }}
                                    </p>
                                </div>

                                <!-- LONGITUDE -->
                                <div>
                                    <label for="longitude" :class="labelClass">
                                        Longitude
                                    </label>

                                    <input
                                        id="longitude"
                                        v-model="form.longitude"
                                        type="number"
                                        step="0.0000001"
                                        min="-180"
                                        max="180"
                                        :class="[
                                            inputClass,
                                            formErrors.longitude &&
                                                inputErrorClass,
                                        ]"
                                        placeholder="102.1234567"
                                    />

                                    <p
                                        v-if="formErrors.longitude"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.longitude }}
                                    </p>
                                </div>

                                <!-- URUTAN -->
                                <div>
                                    <label for="urutan" :class="labelClass">
                                        Urutan
                                    </label>

                                    <input
                                        id="urutan"
                                        v-model="form.urutan"
                                        type="number"
                                        min="0"
                                        step="1"
                                        :class="[
                                            inputClass,
                                            formErrors.urutan &&
                                                inputErrorClass,
                                        ]"
                                        placeholder="Kosongkan untuk otomatis"
                                    />

                                    <p
                                        v-if="formErrors.urutan"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.urutan }}
                                    </p>
                                </div>

                                <!-- STATUS -->
                                <div>
                                    <label for="aktif" :class="labelClass">
                                        Status
                                    </label>

                                    <select
                                        id="aktif"
                                        v-model="form.aktif"
                                        :class="inputClass"
                                    >
                                        <option :value="true">Aktif</option>
                                        <option :value="false">
                                            Tidak aktif
                                        </option>
                                    </select>
                                </div>

                                <!-- DESKRIPSI -->
                                <div class="lg:col-span-2">
                                    <label for="deskripsi" :class="labelClass">
                                        Deskripsi
                                    </label>

                                    <textarea
                                        id="deskripsi"
                                        v-model="form.deskripsi"
                                        rows="4"
                                        maxlength="10000"
                                        :class="[
                                            inputClass,
                                            'resize-none leading-6',
                                            formErrors.deskripsi &&
                                                inputErrorClass,
                                        ]"
                                        placeholder="Jelaskan informasi rute..."
                                    ></textarea>

                                    <p
                                        v-if="formErrors.deskripsi"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.deskripsi }}
                                    </p>
                                </div>

                                <!-- GEOMETRY -->
                                <div class="lg:col-span-2">
                                    <div
                                        class="mb-2 flex items-center justify-between gap-3"
                                    >
                                        <label
                                            for="geometry"
                                            :class="labelClass"
                                        >
                                            Geometry GeoJSON
                                        </label>

                                        <span
                                            class="text-xs text-slate-400 dark:text-slate-500"
                                        >
                                            Point / LineString / MultiLineString
                                        </span>
                                    </div>

                                    <textarea
                                        id="geometry"
                                        v-model="form.geometry"
                                        rows="8"
                                        spellcheck="false"
                                        :class="[
                                            inputClass,
                                            'resize-y font-mono text-xs leading-6',
                                            formErrors.geometry &&
                                                inputErrorClass,
                                        ]"
                                        placeholder='{
  "type": "LineString",
  "coordinates": [
    [102.1234567, -1.1234567],
    [102.2345678, -1.2345678]
  ]
}'
                                        @input="delete formErrors.geometry"
                                    ></textarea>

                                    <p
                                        class="mt-1.5 text-xs leading-5 text-slate-400 dark:text-slate-500"
                                    >
                                        Urutan koordinat GeoJSON adalah
                                        <strong
                                            class="font-semibold text-slate-500 dark:text-slate-400"
                                        >
                                            longitude, latitude
                                        </strong>
                                        .
                                    </p>

                                    <p
                                        v-if="formErrors.geometry"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.geometry }}
                                    </p>

                                    <pre
                                        v-if="form.geometry.trim()"
                                        class="mt-3 max-h-48 overflow-auto rounded-xl border border-slate-200 bg-slate-950 p-4 text-xs leading-6 text-slate-200 dark:border-slate-700"
                                        >{{ geometryPreview() }}</pre
                                    >
                                </div>

                                <!-- IMAGE -->
                                <div class="lg:col-span-2">
                                    <label for="gambar" :class="labelClass">
                                        Gambar
                                    </label>

                                    <div
                                        class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/40"
                                    >
                                        <div
                                            class="flex flex-col gap-4 sm:flex-row sm:items-center"
                                        >
                                            <div
                                                class="flex size-28 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700"
                                            >
                                                <img
                                                    v-if="
                                                        imagePreview ||
                                                        existingImage
                                                    "
                                                    :src="
                                                        imagePreview ||
                                                        existingImage ||
                                                        ''
                                                    "
                                                    alt="Preview gambar rute"
                                                    class="size-full object-cover"
                                                />

                                                <ImageIcon
                                                    v-else
                                                    class="size-8 text-slate-300 dark:text-slate-600"
                                                />
                                            </div>

                                            <div class="min-w-0">
                                                <input
                                                    id="gambar"
                                                    type="file"
                                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                                    class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 dark:text-slate-400 dark:file:bg-blue-950/40 dark:file:text-blue-400"
                                                    @change="handleImage"
                                                />

                                                <p
                                                    class="mt-2 text-xs leading-5 text-slate-400 dark:text-slate-500"
                                                >
                                                    JPG, JPEG, PNG, WEBP.
                                                    Maksimal 2 MB.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <p
                                        v-if="formErrors.gambar"
                                        :class="errorClass"
                                    >
                                        {{ formErrors.gambar }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- GENERAL ERROR -->
                        <p
                            v-if="formErrors.general"
                            class="border-t border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600 sm:px-6 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-400"
                        >
                            {{ formErrors.general }}
                        </p>

                        <!-- FOOTER -->
                        <div
                            class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                :disabled="processingForm"
                                :class="secondaryBtnClass"
                                @click="closeForm"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="processingForm"
                                :class="primaryBtnClass"
                            >
                                <span
                                    v-if="processingForm"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                <Check v-else class="size-4" />

                                {{
                                    processingForm
                                        ? "Menyimpan..."
                                        : formMode === "create"
                                          ? "Simpan Rute"
                                          : "Simpan Perubahan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- DETAIL MODAL -->
        <Transition name="modal">
            <div
                v-if="showDetailModal && selected"
                :class="modalBackdropClass"
                @mousedown.self="closeDetail"
            >
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="detail-modal-title"
                    :class="[modalCardClass, 'max-w-4xl']"
                >
                    <div
                        class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2
                                    id="detail-modal-title"
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    Detail Rute
                                </h2>

                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                    :class="
                                        selected.aktif
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                >
                                    {{
                                        selected.aktif ? "Aktif" : "Tidak aktif"
                                    }}
                                </span>
                            </div>

                            <p
                                class="mt-0.5 text-sm leading-5 text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap rute pelayaran dan lokasi.
                            </p>
                        </div>

                        <button
                            type="button"
                            aria-label="Tutup detail"
                            :class="closeBtnClass"
                            @click="closeDetail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <div
                        class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-6"
                    >
                        <div class="grid gap-5 lg:grid-cols-[260px_1fr]">
                            <!-- IMAGE -->
                            <div>
                                <div
                                    class="aspect-[4/3] overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50"
                                >
                                    <img
                                        v-if="selected.gambar"
                                        :src="imageUrl(selected.gambar)!"
                                        :alt="selected.nama_rute"
                                        class="size-full object-cover"
                                    />

                                    <div
                                        v-else
                                        class="flex size-full items-center justify-center text-slate-300 dark:text-slate-600"
                                    >
                                        <RouteIcon class="size-12" />
                                    </div>
                                </div>
                            </div>

                            <!-- INFO -->
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3
                                        class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                                    >
                                        {{ selected.nama_rute }}
                                    </h3>
                                </div>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ selected.jalur }}
                                </p>

                                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                    <div :class="infoCardClass">
                                        <div class="flex items-center gap-2">
                                            <MapPin
                                                class="size-4 text-blue-500"
                                            />
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Asal
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                        >
                                            {{ selected.asal || "-" }}
                                        </p>
                                    </div>

                                    <div :class="infoCardClass">
                                        <div class="flex items-center gap-2">
                                            <MapPin
                                                class="size-4 text-blue-500"
                                            />
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Tujuan
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                        >
                                            {{ selected.tujuan || "-" }}
                                        </p>
                                    </div>

                                    <div :class="infoCardClass">
                                        <div class="flex items-center gap-2">
                                            <RouteIcon
                                                class="size-4 text-blue-500"
                                            />
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Jarak
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                        >
                                            {{ formatNumber(selected.jarak) }}
                                            {{ selected.satuan_jarak }}
                                        </p>
                                    </div>

                                    <div :class="infoCardClass">
                                        <div class="flex items-center gap-2">
                                            <Clock
                                                class="size-4 text-blue-500"
                                            />
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Waktu tempuh
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                        >
                                            {{ selected.waktu_tempuh }}
                                        </p>
                                    </div>

                                    <div :class="infoCardClass">
                                        <div class="flex items-center gap-2">
                                            <Map class="size-4 text-blue-500" />
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Geometry
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                        >
                                            {{
                                                geometryTypeLabel(
                                                    selected.geometry,
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div :class="infoCardClass">
                                        <div class="flex items-center gap-2">
                                            <RouteIcon
                                                class="size-4 text-blue-500"
                                            />
                                            <span
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Urutan
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 font-semibold text-slate-800 dark:text-slate-100"
                                        >
                                            #{{ selected.urutan }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PETA -->
                        <div v-if="hasMapData" class="mt-5">
                            <div class="mb-2 flex items-center gap-2">
                                <Map class="size-4 text-blue-500" />

                                <h4
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Peta Lokasi
                                </h4>
                            </div>

                            <div
                                ref="detailMapEl"
                                class="relative isolate z-0 h-72 w-full overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800"
                            ></div>
                        </div>

                        <!-- COORDINATE -->
                        <div class="mt-5">
                            <div class="mb-2 flex items-center gap-2">
                                <MapPin class="size-4 text-blue-500" />

                                <h4
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Koordinat
                                </h4>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div :class="infoCardClass">
                                    <span
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Latitude
                                    </span>

                                    <p
                                        class="mt-1 font-mono text-sm font-semibold text-blue-600 dark:text-blue-400"
                                    >
                                        {{ selected.latitude ?? "-" }}
                                    </p>
                                </div>

                                <div :class="infoCardClass">
                                    <span
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Longitude
                                    </span>

                                    <p
                                        class="mt-1 font-mono text-sm font-semibold text-blue-600 dark:text-blue-400"
                                    >
                                        {{ selected.longitude ?? "-" }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="mt-5">
                            <div class="mb-2 flex items-center gap-2">
                                <FileText class="size-4 text-blue-500" />

                                <h4
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Deskripsi
                                </h4>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-7 text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300"
                            >
                                <p class="whitespace-pre-line break-words">
                                    {{ selected.deskripsi || "-" }}
                                </p>
                            </div>
                        </div>

                        <!-- GEOJSON -->
                        <div v-if="selected.geometry" class="mt-5">
                            <div class="mb-2 flex items-center gap-2">
                                <Map class="size-4 text-blue-500" />

                                <h4
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    GeoJSON Geometry
                                </h4>
                            </div>

                            <pre
                                class="max-h-72 overflow-auto rounded-2xl border border-slate-800 bg-slate-950 p-4 font-mono text-xs leading-6 text-slate-200"
                                >{{
                                    JSON.stringify(selected.geometry, null, 2)
                                }}</pre
                            >
                        </div>

                        <div
                            class="mt-5 text-xs text-slate-400 dark:text-slate-500"
                        >
                            Dibuat:
                            {{ formatDate(selected.created_at) }}
                            <span class="mx-1">·</span>
                            Diperbarui:
                            {{ formatDate(selected.updated_at) }}
                        </div>
                    </div>

                    <div
                        class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            :class="secondaryBtnClass"
                            @click="closeDetail"
                        >
                            Tutup
                        </button>

                        <button
                            type="button"
                            :class="secondaryBtnClass"
                            @click="openEdit(selected)"
                        >
                            <Pencil class="size-4" />
                            Edit Rute
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- DELETE MODAL -->
        <Transition name="modal">
            <div
                v-if="showDeleteModal && selected"
                :class="modalBackdropClass"
                @mousedown.self="closeDelete"
            >
                <div
                    role="alertdialog"
                    aria-modal="true"
                    aria-labelledby="delete-modal-title"
                    class="my-auto w-full max-w-md rounded-3xl border border-slate-200/80 bg-white p-5 shadow-2xl shadow-slate-900/10 sm:p-6 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/40"
                >
                    <div
                        class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                    >
                        <Trash2 class="size-5" />
                    </div>

                    <div class="mt-4 text-center">
                        <h2
                            id="delete-modal-title"
                            class="text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            Hapus Rute?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus rute
                            <span
                                class="font-semibold text-slate-700 dark:text-slate-200"
                            >
                                {{ selected.nama_rute }}
                            </span>
                            ? Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            :disabled="processingDelete"
                            :class="secondaryBtnClass"
                            @click="closeDelete"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/40 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                            @click="deleteRute"
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

        <!-- PAGE LOADING -->
        <Transition name="loading">
            <div
                v-if="isRequesting"
                class="pointer-events-none fixed inset-0 z-[100] bg-white/30 backdrop-blur-[1px] dark:bg-slate-950/30"
            >
                <div
                    class="absolute left-0 top-0 h-0.5 w-full overflow-hidden bg-blue-100 dark:bg-blue-950"
                >
                    <div
                        class="animate-loading-bar h-full w-1/3 rounded-full bg-blue-600"
                    ></div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.blob-shape {
    animation: blob-float 12s ease-in-out infinite;
}

.blob-shape-delayed {
    animation: blob-float-delayed 15s ease-in-out infinite;
}

.blob-shape-slow {
    animation: blob-float-slow 18s ease-in-out infinite;
}

@keyframes blob-float {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(20px, 15px, 0) scale(1.05);
    }
}

@keyframes blob-float-delayed {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(-18px, 20px, 0) scale(1.08);
    }
}

@keyframes blob-float-slow {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(15px, -18px, 0) scale(1.06);
    }
}

.animate-loading-bar {
    animation: loading-bar 1.2s ease-in-out infinite;
}

@keyframes loading-bar {
    0% {
        transform: translateX(-120%);
    }

    100% {
        transform: translateX(420%);
    }
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}

.modal-enter-active > div,
.modal-leave-active > div {
    transition: transform 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
    transform: translateY(10px) scale(0.98);
}

.loading-enter-active,
.loading-leave-active {
    transition: opacity 0.2s ease;
}

.loading-enter-from,
.loading-leave-to {
    opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
    .blob-shape,
    .blob-shape-delayed,
    .blob-shape-slow,
    .animate-loading-bar {
        animation: none;
    }

    .modal-enter-active,
    .modal-leave-active,
    .modal-enter-active > div,
    .modal-leave-active > div,
    .loading-enter-active,
    .loading-leave-active {
        transition: none;
    }
}
</style>
