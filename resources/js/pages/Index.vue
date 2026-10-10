<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";
import {
    ArrowRight,
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    ChevronDown,
    ChevronRight,
    Clock3,
    ExternalLink,
    Factory,
    Globe2,
    Handshake,
    MapPin,
    MoveRight,
    Navigation,
    Quote,
    Ship,
    Sparkles,
    TrendingUp,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";
import { visitUrl } from "@/data/publicNavigation";
import { currentLanguage } from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
 * TYPES
 * ========================================================= */

interface CompanyProfile {
    id?: number;
    nama?: string | null;
    nama_perusahaan?: string | null;
    judul?: string | null;
    deskripsi?: string | null;
    tentang?: string | null;
    logo?: string | null;
    logo_path?: string | null;
    index?: number | null;
}

interface SambutanDirektur {
    id?: number;
    nama_direktur?: string | null;
    jabatan_direktur?: string | null;
    sambutan_direktur?: string | null;
    foto_direktur?: string | null;
    status?: string | boolean | number | null;
    index?: number | null;
}

interface Misi {
    id?: number;
    judul?: string | null;
    title?: string | null;
    deskripsi?: string | null;
    desc?: string | null;
    isi?: string | null;
    urutan?: number | null;
    index?: number | null;
}

interface Visi {
    id?: number;
    judul?: string | null;
    title?: string | null;
    visi?: string | null;
    deskripsi?: string | null;
    misis?: Misi[];
}

interface AnakUsaha {
    id: number;
    nama?: string | null;
    nama_perusahaan?: string | null;
    judul?: string | null;
    deskripsi?: string | null;
    logo?: string | null;
    logo_path?: string | null;
    gambar?: string | null;
    gambar_path?: string | null;
    website?: string | null;
    aktif?: boolean | number | string | null;
}

interface MitraPerusahaan {
    id: number;
    nama_perusahaan?: string | null;
    slug?: string | null;
    logo?: string | null;
    website?: string | null;
    aktif?: boolean | number | string | null;
    urutan?: number | null;
}

interface ProfilKawasan {
    id: number;
    judul?: string | null;
    slug?: string | null;
    deskripsi?: string | null;
    luas_kawasan?: number | string | null;
    lokasi?: string | null;
    tahun_berdiri?: number | string | null;
    status?: string | boolean | number | null;
    gambar?: string | null;
    gambar_path?: string | null;
}

interface PetaKawasan {
    id: number;
    judul?: string | null;
    gambar?: string | null;
    gambar_path?: string | null;
    keterangan?: string | null;
    deskripsi?: string | null;
    aktif?: boolean | number | string | null;
}

interface PeluangInvestasi {
    id: number;
    judul?: string | null;
    nama?: string | null;
    sektor?: string | null;
    deskripsi?: string | null;
    luas_lahan?: number | string | null;
    status?: string | boolean | number | null;
    gambar?: string | null;
    gambar_path?: string | null;
    urutan?: number | null;
}

interface Rute {
    id: number;
    nama?: string | null;
    judul?: string | null;
    asal?: string | null;
    tujuan?: string | null;
    jalur?: string | null;
    rute?: string | null;
    jarak?: string | number | null;
    distance?: string | number | null;
    waktu_tempuh?: string | number | null;
    waktu?: string | number | null;
    deskripsi?: string | null;
}

interface Berita {
    id: number;
    judul?: string | null;
    title?: string | null;
    slug?: string | null;
    excerpt?: string | null;
    ringkasan?: string | null;
    deskripsi?: string | null;
    gambar?: string | null;
    gambar_path?: string | null;
    thumbnail?: string | null;
    published_at?: string | null;
    created_at?: string | null;
    is_featured?: boolean | number | string | null;
}

interface Lowongan {
    id: number;
    judul?: string | null;
    slug?: string | null;
    departemen?: string | null;
    lokasi?: string | null;
    tipe_pekerjaan?: string | null;
    deskripsi?: string | null;
    tanggal_mulai?: string | null;
    tanggal_tutup?: string | null;
    status?: string | null;
    unggulan?: boolean | number | string | null;
}

interface StatItem {
    label: string;
    target: number;
    decimals: number;
    suffix?: string;
}

interface LandStat {
    label: string;
    target: number;
    decimals: number;
    unit: string;
}

/* =========================================================
 * PROPS
 * ========================================================= */

const props = withDefaults(
    defineProps<{
        companyProfile?: CompanyProfile | null;
        sambutanDirektur?: SambutanDirektur | null;
        visi?: Visi | null;
        anakUsahas?: AnakUsaha[];
        mitraPerusahaans?: MitraPerusahaan[];
        profilKawasan?: ProfilKawasan | null;
        petaKawasan?: PetaKawasan | null;
        peluangInvestasi?: PeluangInvestasi[];
        rutes?: Rute[];
        beritas?: Berita[];
        lowongans?: Lowongan[];
    }>(),
    {
        companyProfile: null,
        sambutanDirektur: null,
        visi: null,
        anakUsahas: () => [],
        mitraPerusahaans: () => [],
        profilKawasan: null,
        petaKawasan: null,
        peluangInvestasi: () => [],
        rutes: () => [],
        beritas: () => [],
        lowongans: () => [],
    },
);

/* =========================================================
 * HELPERS
 * ========================================================= */

function firstValue<T extends Record<string, any>>(
    object: T | null | undefined,
    keys: string[],
    fallback = "",
): string {
    if (!object) {
        return fallback;
    }

    for (const key of keys) {
        const value = object[key];

        if (
            value !== null &&
            value !== undefined &&
            String(value).trim() !== ""
        ) {
            return String(value);
        }
    }

    return fallback;
}

/**
 * Mengambil field sesuai bahasa aktif dengan fallback ke Bahasa Indonesia.
 * Mendukung struktur field seperti judul_id/judul_en/judul_zh maupun
 * field dasar berbahasa Indonesia seperti nama_perusahaan.
 */
function localizedFirstValue<T extends object>(
    object: T | null | undefined,
    keys: string[],
    fallback = "",
): string {
    if (!object) {
        return fallback;
    }

    const record = object as unknown as Record<string, unknown>;
    const language = currentLanguage.value;
    const suffix = language === "en" ? "en" : language === "zh" ? "zh" : "id";

    for (const key of keys) {
        const candidates = [`${key}_${suffix}`, key];

        for (const candidate of candidates) {
            const value = record[candidate];

            if (
                value !== null &&
                value !== undefined &&
                String(value).trim() !== ""
            ) {
                return String(value);
            }
        }
    }

    return fallback;
}

function currentIntlLocale(): string {
    const language = currentLanguage.value;

    if (language === "en") return "en-US";
    if (language === "zh") return "zh-CN";

    return "id-ID";
}

function numericValue(value: unknown, fallback = 0): number {
    if (typeof value === "number" && Number.isFinite(value)) {
        return value;
    }

    if (typeof value === "string") {
        const normalized = value.replace(/\s/g, "").replace(/,/g, "");
        const number = Number(normalized);

        return Number.isFinite(number) ? number : fallback;
    }

    return fallback;
}

function formatNumber(
    value: number | string | null | undefined,
    decimals = 0,
): string {
    const number = numericValue(value);

    return new Intl.NumberFormat(currentIntlLocale(), {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(number);
}

function truncate(value: string | null | undefined, length = 150): string {
    if (!value) {
        return "";
    }

    const text = String(value).trim();

    if (text.length <= length) {
        return text;
    }

    return `${text.slice(0, length).trimEnd()}…`;
}

function formatDate(value: string | null | undefined): string {
    if (!value) {
        return "";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat(currentIntlLocale(), {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(date);
}

function storageUrl(path: string | null | undefined): string {
    if (!path) {
        return "";
    }

    const value = String(path).trim();

    if (!value) {
        return "";
    }

    if (
        /^https?:\/\//i.test(value) ||
        value.startsWith("data:") ||
        value.startsWith("blob:")
    ) {
        return value;
    }

    if (value.startsWith("/storage/")) {
        return value;
    }

    if (value.startsWith("/")) {
        return value;
    }

    return `/storage/${value.replace(/^\/+/, "")}`;
}

function normalizeWebsite(website: string | null | undefined): string {
    if (!website) {
        return "";
    }

    const value = website.trim();

    if (!value) {
        return "";
    }

    if (/^https?:\/\//i.test(value)) {
        return value;
    }

    return `https://${value}`;
}

function hasValue(value: unknown): boolean {
    if (value === null || value === undefined) {
        return false;
    }

    if (typeof value === "string") {
        return value.trim() !== "";
    }

    const number = Number(value);

    return Number.isFinite(number) && number > 0;
}

function jobTypeLabel(value: string | null | undefined): string {
    if (!value) {
        return trans("home.career.position_available");
    }

    const normalized = value.trim().toLowerCase().replace(/[ -]/g, "_");
    const labels: Record<string, string> = {
        full_time:
            currentLanguage.value === "id"
                ? "Penuh waktu"
                : currentLanguage.value === "zh"
                  ? "全职"
                  : "Full Time",
        part_time:
            currentLanguage.value === "id"
                ? "Paruh waktu"
                : currentLanguage.value === "zh"
                  ? "兼职"
                  : "Part Time",
        contract:
            currentLanguage.value === "id"
                ? "Kontrak"
                : currentLanguage.value === "zh"
                  ? "合同制"
                  : "Contract",
        internship:
            currentLanguage.value === "id"
                ? "Magang"
                : currentLanguage.value === "zh"
                  ? "实习"
                  : "Internship",
        freelance:
            currentLanguage.value === "id"
                ? "Pekerja lepas"
                : currentLanguage.value === "zh"
                  ? "自由职业"
                  : "Freelance",
        remote:
            currentLanguage.value === "id"
                ? "Jarak jauh"
                : currentLanguage.value === "zh"
                  ? "远程办公"
                  : "Remote",
        hybrid:
            currentLanguage.value === "id"
                ? "Hybrid"
                : currentLanguage.value === "zh"
                  ? "混合办公"
                  : "Hybrid",
    };

    return labels[normalized] ?? value;
}

/* =========================================================
 * REDUCED MOTION
 * ========================================================= */

const prefersReducedMotion = ref(false);

function updateReducedMotion(): void {
    if (typeof window === "undefined") {
        return;
    }

    prefersReducedMotion.value = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;
}

/* =========================================================
 * FADE IN DIRECTIVE
 * ========================================================= */

const fadeObservers = new WeakMap<Element, IntersectionObserver>();

const vFadeIn = {
    mounted(element: HTMLElement) {
        element.classList.add("fade-in");

        if (
            prefersReducedMotion.value ||
            typeof window === "undefined" ||
            !("IntersectionObserver" in window)
        ) {
            element.classList.add("fade-in-visible");
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        element.classList.add("fade-in-visible");

                        observer.unobserve(element);
                    }
                });
            },
            {
                threshold: 0.08,
                rootMargin: "0px 0px -40px 0px",
            },
        );

        fadeObservers.set(element, observer);
        observer.observe(element);
    },

    unmounted(element: HTMLElement) {
        fadeObservers.get(element)?.disconnect();
        fadeObservers.delete(element);
    },
};

/* =========================================================
 * COUNT UP DIRECTIVE
 * ========================================================= */

interface CountUpOptions {
    target: number;
    decimals?: number;
    delay?: number;
}

const countUpCleanup = new WeakMap<Element, () => void>();

const vCountUp = {
    mounted(element: HTMLElement, binding: { value: CountUpOptions }) {
        const target = numericValue(binding.value?.target);

        const decimals = numericValue(binding.value?.decimals);

        const delay = numericValue(binding.value?.delay);

        let frame = 0;
        let timeout = 0;
        let observer: IntersectionObserver | null = null;
        let started = false;

        const render = (value: number) => {
            element.textContent = formatNumber(value, decimals);
        };

        const cleanup = () => {
            if (frame) {
                cancelAnimationFrame(frame);
            }

            if (timeout) {
                window.clearTimeout(timeout);
            }

            observer?.disconnect();
        };

        countUpCleanup.set(element, cleanup);

        const start = () => {
            if (started) {
                return;
            }

            started = true;

            if (prefersReducedMotion.value) {
                render(target);
                return;
            }

            const duration = 1100;
            const startTime = performance.now();

            const animate = (currentTime: number) => {
                const elapsed = currentTime - startTime;

                const progress = Math.min(elapsed / duration, 1);

                const eased = 1 - Math.pow(1 - progress, 3);

                render(target * eased);

                if (progress < 1) {
                    frame = requestAnimationFrame(animate);
                } else {
                    render(target);
                }
            };

            timeout = window.setTimeout(() => {
                frame = requestAnimationFrame(animate);
            }, delay);
        };

        if (
            typeof window === "undefined" ||
            !("IntersectionObserver" in window)
        ) {
            start();
            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    start();
                    observer?.disconnect();
                }
            },
            {
                threshold: 0.25,
            },
        );

        observer.observe(element);
    },

    unmounted(element: HTMLElement) {
        countUpCleanup.get(element)?.();
        countUpCleanup.delete(element);
    },
};

/* =========================================================
 * COMPANY
 * ========================================================= */

const companyName = computed(() =>
    localizedFirstValue(
        props.companyProfile,
        ["nama_perusahaan", "nama", "judul"],
        "PT Kawasan Industri Tanjung Buton",
    ),
);

const companyDescription = computed(() =>
    localizedFirstValue(
        props.companyProfile,
        ["deskripsi", "tentang"],
        currentLanguage.value === "id"
            ? `${trans("home.company.description")} ${trans("home.company.description_continuation")}`
            : trans("home.company.description"),
    ),
);

/* =========================================================
 * SAMBUTAN DIREKTUR
 * ========================================================= */

const isMounted = ref(false);
const sambutanExpanded = ref(false);

const direkturName = computed(() =>
    localizedFirstValue(props.sambutanDirektur, ["nama_direktur"]),
);

const direkturPosition = computed(() =>
    localizedFirstValue(props.sambutanDirektur, ["jabatan_direktur"]),
);

const direkturPhoto = computed(() =>
    storageUrl(firstValue(props.sambutanDirektur, ["foto_direktur"])),
);

const direkturRaw = computed(() =>
    localizedFirstValue(props.sambutanDirektur, ["sambutan_direktur"]),
);

function stripTags(value: string): string {
    if (typeof document === "undefined") {
        return value.replace(/<[^>]*>/g, " ");
    }

    const element = document.createElement("div");

    element.innerHTML = value;

    return (element.textContent || element.innerText || "")
        .replace(/\s+/g, " ")
        .trim();
}

function plainTextToHtml(value: string): string {
    return value
        .split(/\n{2,}/)
        .map((paragraph) => {
            const text = paragraph.trim().replace(/\n/g, "<br>");

            return text ? `<p>${text}</p>` : "";
        })
        .filter(Boolean)
        .join("");
}

function cleanRichNode(node: Node, documentRef: Document): Node | null {
    if (node.nodeType === Node.TEXT_NODE) {
        return documentRef.createTextNode(node.textContent || "");
    }

    if (node.nodeType !== Node.ELEMENT_NODE) {
        return null;
    }

    const source = node as HTMLElement;

    const allowedTags = new Set([
        "P",
        "BR",
        "STRONG",
        "B",
        "EM",
        "I",
        "U",
        "UL",
        "OL",
        "LI",
        "BLOCKQUOTE",
        "A",
        "H2",
        "H3",
        "H4",
    ]);

    const tag = source.tagName.toUpperCase();

    if (!allowedTags.has(tag)) {
        const fragment = documentRef.createDocumentFragment();

        Array.from(source.childNodes).forEach((child) => {
            const cleaned = cleanRichNode(child, documentRef);

            if (cleaned) {
                fragment.appendChild(cleaned);
            }
        });

        return fragment;
    }

    const target = documentRef.createElement(tag.toLowerCase());

    if (tag === "A") {
        const href = source.getAttribute("href") || "";

        if (
            /^https?:\/\//i.test(href) ||
            href.startsWith("/") ||
            href.startsWith("#")
        ) {
            target.setAttribute("href", href);

            if (/^https?:\/\//i.test(href)) {
                target.setAttribute("target", "_blank");

                target.setAttribute("rel", "noopener noreferrer");
            }
        }
    }

    Array.from(source.childNodes).forEach((child) => {
        const cleaned = cleanRichNode(child, documentRef);

        if (cleaned) {
            target.appendChild(cleaned);
        }
    });

    return target;
}

function sanitizeRichText(value: string): string {
    if (!value) {
        return "";
    }

    if (typeof DOMParser === "undefined") {
        return plainTextToHtml(stripTags(value));
    }

    const parser = new DOMParser();

    const parsed = parser.parseFromString(value, "text/html");

    const container = parsed.createElement("div");

    Array.from(parsed.body.childNodes).forEach((node) => {
        const cleaned = cleanRichNode(node, parsed);

        if (cleaned) {
            container.appendChild(cleaned);
        }
    });

    return container.innerHTML;
}

const direkturHtml = computed(() => {
    if (!direkturRaw.value) {
        return "";
    }

    const raw = direkturRaw.value.trim();

    if (!raw.includes("<")) {
        return plainTextToHtml(raw);
    }

    return sanitizeRichText(raw);
});

const sambutanIsLong = computed(() => {
    return stripTags(direkturRaw.value).length > 700;
});

const hasSambutanDirektur = computed(() => {
    return Boolean(
        props.sambutanDirektur &&
        (direkturName.value ||
            direkturPosition.value ||
            direkturRaw.value ||
            direkturPhoto.value),
    );
});

/* =========================================================
 * VISI & MISI
 * ========================================================= */

const visionText = computed(() =>
    localizedFirstValue(props.visi, ["visi", "deskripsi", "judul", "title"]),
);

const missions = computed<Misi[]>(() => props.visi?.misis ?? []);

const hasVisionMission = computed(
    () =>
        Boolean(props.visi) &&
        Boolean(visionText.value || missions.value.length),
);

/* =========================================================
 * PROFIL KAWASAN
 * ========================================================= */

const kawasanName = computed(() =>
    localizedFirstValue(
        props.profilKawasan,
        ["judul"],
        trans("home.area.eyebrow"),
    ),
);

const kawasanDescription = computed(() =>
    localizedFirstValue(props.profilKawasan, ["deskripsi"]),
);

const kawasanArea = computed(() =>
    numericValue(props.profilKawasan?.luas_kawasan),
);

const kawasanAreaDecimals = computed(() => {
    const value = props.profilKawasan?.luas_kawasan;

    if (typeof value === "string" && value.includes(".")) {
        return Math.min(value.split(".")[1]?.length ?? 0, 2);
    }

    return Number.isInteger(numericValue(value)) ? 0 : 2;
});

const kawasanLocation = computed(() =>
    localizedFirstValue(props.profilKawasan, ["lokasi"]),
);

const kawasanYear = computed(() =>
    firstValue(props.profilKawasan, ["tahun_berdiri"]),
);

const kawasanImage = computed(() =>
    storageUrl(firstValue(props.profilKawasan, ["gambar_path", "gambar"])),
);

/* =========================================================
 * MASTER PLAN
 * ========================================================= */

const mapTitle = computed(() =>
    localizedFirstValue(
        props.petaKawasan,
        ["judul"],
        trans("home.area.master_plan"),
    ),
);

const mapImage = computed(() =>
    storageUrl(firstValue(props.petaKawasan, ["gambar_path", "gambar"])),
);

const mapDescription = computed(() =>
    localizedFirstValue(props.petaKawasan, ["deskripsi", "keterangan"]),
);

/* =========================================================
 * DEVELOPMENT STAGES
 * ========================================================= */

const developmentStages = computed(() => {
    const translations = {
        id: [
            {
                number: "01",
                title: "Kawasan Industri",
                desc: "Kavling industri, fasilitas pendukung, perdagangan dan jasa, area perkantoran, serta fasilitas penunjang dalam satu tata kawasan terpadu.",
            },
            {
                number: "02",
                title: "Kawasan Pelabuhan",
                desc: "Area migas, CPO, curah kering, kontainer dan pergudangan, serta fasilitas galangan kapal untuk mendukung arus logistik ekspor-impor.",
            },
            {
                number: "03",
                title: "Pelabuhan Tanjung Buton",
                desc: "Infrastruktur pelabuhan yang mendukung konektivitas kawasan dengan jaringan pelayaran dan jalur logistik regional.",
            },
        ],
        en: [
            {
                number: "01",
                title: "Industrial Estate",
                desc: "Industrial plots, supporting facilities, trade and services, office areas, and complementary facilities within an integrated estate layout.",
            },
            {
                number: "02",
                title: "Port Area",
                desc: "Oil and gas, CPO, dry bulk, container and warehousing areas, as well as shipyard facilities supporting export and import logistics.",
            },
            {
                number: "03",
                title: "Tanjung Buton Port",
                desc: "Port infrastructure connecting the estate to shipping networks and regional logistics routes.",
            },
        ],
        zh: [
            {
                number: "01",
                title: "工业园区",
                desc: "通过一体化园区规划整合工业地块、配套设施、贸易与服务、办公区域及其他辅助设施。",
            },
            {
                number: "02",
                title: "港口区域",
                desc: "涵盖油气、棕榈油（CPO）、干散货、集装箱、仓储及船舶修造设施，支持进出口物流。",
            },
            {
                number: "03",
                title: "丹戎布通港口",
                desc: "通过港口基础设施连接园区、航运网络及区域物流路线。",
            },
        ],
    } as const;

    return translations[currentLanguage.value] ?? translations.id;
});

/* =========================================================
 * STATS
 * ========================================================= */

const stats = computed<StatItem[]>(() => {
    const result: StatItem[] = [];

    if (kawasanArea.value > 0) {
        result.push({
            label: trans("home.area.area_size"),
            target: kawasanArea.value,
            decimals: kawasanAreaDecimals.value,
            suffix: "Ha",
        });
    }

    if (props.anakUsahas.length) {
        result.push({
            label: trans("home.subsidiaries.eyebrow"),
            target: props.anakUsahas.length,
            decimals: 0,
        });
    }

    if (props.rutes.length) {
        result.push({
            label: trans("home.location.shipping_routes"),
            target: props.rutes.length,
            decimals: 0,
        });
    }

    if (props.beritas.length) {
        result.push({
            label: trans("home.news.eyebrow"),
            target: props.beritas.length,
            decimals: 0,
        });
    }

    return result.slice(0, 4);
});

const landStats = computed<LandStat[]>(() => {
    if (kawasanArea.value <= 0) {
        return [];
    }

    return [
        {
            label: trans("home.area.area_size"),
            target: kawasanArea.value,
            decimals: kawasanAreaDecimals.value,
            unit: "Ha",
        },
    ];
});

/* =========================================================
 * RUTE
 * ========================================================= */

const displayedRoutes = computed(() => props.rutes.slice(0, 6));

const hasLocationSection = computed(
    () => Boolean(kawasanLocation.value) || displayedRoutes.value.length > 0,
);

function routePath(route: Rute): string {
    const direct = localizedFirstValue(route, ["rute", "jalur"]);

    if (direct) {
        return direct;
    }

    const asal = localizedFirstValue(route, ["asal"]);

    const tujuan = localizedFirstValue(route, ["tujuan"]);

    if (asal && tujuan) {
        return `${asal} → ${tujuan}`;
    }

    return "";
}

function routeName(route: Rute, index: number): string {
    return localizedFirstValue(
        route,
        ["nama", "judul"],
        `${trans("home.location.route")} ${String(index + 1).padStart(2, "0")}`,
    );
}

/* =========================================================
 * MITRA PERUSAHAAN
 * ========================================================= */

const displayedMitraPerusahaans = computed(() =>
    [...props.mitraPerusahaans]
        .filter((item) => {
            if (item.aktif === undefined || item.aktif === null) {
                return true;
            }

            return (
                item.aktif === true || item.aktif === 1 || item.aktif === "1"
            );
        })
        .sort((a, b) => numericValue(a.urutan) - numericValue(b.urutan)),
);

const hasMitraPerusahaan = computed(
    () => displayedMitraPerusahaans.value.length > 0,
);

const mitraCarousel = ref<HTMLElement | null>(null);

const mitraAutoplayPaused = ref(false);

let mitraAutoplayTimer: number | null = null;

function mitraPerusahaanName(mitra: MitraPerusahaan): string {
    return localizedFirstValue(
        mitra,
        ["nama_perusahaan", "slug"],
        trans("home.partners.title"),
    );
}

function mitraPerusahaanImage(mitra: MitraPerusahaan): string {
    return storageUrl(mitra.logo);
}

function scrollMitra(direction: "prev" | "next"): void {
    const container = mitraCarousel.value;

    if (!container) {
        return;
    }

    const card = container.querySelector<HTMLElement>("[data-mitra-card]");

    if (!card) {
        return;
    }

    const gap = Number.parseFloat(getComputedStyle(container).gap) || 0;

    const amount = card.getBoundingClientRect().width + gap;

    const maxScroll = container.scrollWidth - container.clientWidth;

    let nextLeft =
        container.scrollLeft + (direction === "next" ? amount : -amount);

    if (direction === "next" && nextLeft >= maxScroll - 4) {
        nextLeft = 0;
    }

    if (direction === "prev" && nextLeft <= 4) {
        nextLeft = maxScroll;
    }

    container.scrollTo({
        left: Math.max(0, nextLeft),
        behavior: prefersReducedMotion.value ? "auto" : "smooth",
    });
}

function stopMitraAutoplay(): void {
    if (mitraAutoplayTimer !== null) {
        window.clearInterval(mitraAutoplayTimer);

        mitraAutoplayTimer = null;
    }
}

function startMitraAutoplay(): void {
    stopMitraAutoplay();

    if (
        prefersReducedMotion.value ||
        displayedMitraPerusahaans.value.length <= 1
    ) {
        return;
    }

    mitraAutoplayTimer = window.setInterval(() => {
        if (!mitraAutoplayPaused.value) {
            scrollMitra("next");
        }
    }, 4500);
}

function pauseMitraAutoplay(): void {
    mitraAutoplayPaused.value = true;
}

function resumeMitraAutoplay(): void {
    mitraAutoplayPaused.value = false;
}

/* =========================================================
 * INVESTASI
 * ========================================================= */

function investmentTitle(item: PeluangInvestasi): string {
    return localizedFirstValue(
        item,
        ["judul", "nama"],
        trans("home.investment.title"),
    );
}

function investmentDescription(item: PeluangInvestasi): string {
    return localizedFirstValue(item, ["deskripsi"]);
}

function investmentImage(item: PeluangInvestasi): string {
    return storageUrl(firstValue(item, ["gambar_path", "gambar"]));
}

/* =========================================================
 * BERITA
 * ========================================================= */

function beritaTitle(item: Berita): string {
    return localizedFirstValue(
        item,
        ["judul", "title"],
        trans("home.news.title"),
    );
}

function beritaExcerpt(item: Berita): string {
    return localizedFirstValue(item, ["excerpt", "ringkasan", "deskripsi"]);
}

function beritaImage(item: Berita): string {
    return storageUrl(firstValue(item, ["gambar_path", "gambar", "thumbnail"]));
}

/* =========================================================
 * ANAK USAHA
 * ========================================================= */

function anakUsahaName(item: AnakUsaha): string {
    return localizedFirstValue(
        item,
        ["nama_perusahaan", "nama", "judul"],
        trans("home.subsidiaries.title"),
    );
}

function anakUsahaImage(item: AnakUsaha): string {
    return storageUrl(
        firstValue(item, ["logo_path", "logo", "gambar_path", "gambar"]),
    );
}

/* =========================================================
 * LIFECYCLE
 * ========================================================= */

let mediaQueryList: MediaQueryList | null = null;

function handleMotionChange(event: MediaQueryListEvent): void {
    prefersReducedMotion.value = event.matches;

    if (event.matches) {
        stopMitraAutoplay();
    } else if (isMounted.value) {
        startMitraAutoplay();
    }
}

onMounted(() => {
    isMounted.value = true;

    updateReducedMotion();

    if (typeof window !== "undefined") {
        mediaQueryList = window.matchMedia("(prefers-reduced-motion: reduce)");

        mediaQueryList.addEventListener("change", handleMotionChange);
    }

    startMitraAutoplay();
});

onBeforeUnmount(() => {
    stopMitraAutoplay();

    if (mediaQueryList) {
        mediaQueryList.removeEventListener("change", handleMotionChange);
    }
});
</script>
<template>
    <Head :title="companyName" />

    <div
        class="kitb-landing relative min-h-screen overflow-x-clip bg-kitb-sand-50 text-kitb-ink-900 antialiased"
    >
        <!-- =====================================================
             HERO
             ===================================================== -->

        <section
            id="top"
            class="hero-blob-field relative z-0 overflow-visible border-b border-black/5 pb-20 pt-32 sm:pb-24 sm:pt-40 md:pb-36 md:pt-48 dark:border-white/10"
        >
            <div
                class="blob blob-organic-1 drift-a"
                aria-hidden="true"
                style="
                    width: 620px;
                    height: 620px;
                    top: -300px;
                    right: -220px;
                    background: radial-gradient(
                        circle at 35% 30%,
                        #8fb5e8 0%,
                        #4c82c8 35%,
                        #2e6fbf 55%,
                        #163a70 86%,
                        transparent 100%
                    );
                    opacity: 0.72;
                "
            />

            <div
                class="blob blob-organic-2 drift-b"
                aria-hidden="true"
                style="
                    width: 340px;
                    height: 340px;
                    right: 120px;
                    bottom: -120px;
                    background: radial-gradient(
                        circle at 60% 40%,
                        #2f66b0 0%,
                        #1f4c91 42%,
                        #0b1f3f 82%,
                        transparent 100%
                    );
                    opacity: 0.68;
                "
            />

            <div class="relative z-10 mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div class="max-w-3xl" v-fade-in>
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full bg-kitb-green-700/10 px-3.5 py-1.5 text-[12px] font-medium text-kitb-green-800 sm:mb-8 sm:text-[13px]"
                    >
                        <span
                            class="h-1.5 w-1.5 shrink-0 rounded-full bg-kitb-teal-500"
                        />

                        {{ trans("home.hero.badge") }}
                    </div>

                    <h1
                        class="mb-6 max-w-4xl font-display font-bold leading-[1.06] tracking-[-0.025em] text-kitb-green-900 sm:mb-7"
                        style="font-size: clamp(2.25rem, 5.8vw, 5rem)"
                    >
                        {{ trans("home.hero.title") }}
                    </h1>

                    <p
                        class="mb-8 max-w-2xl text-[16px] leading-[1.8] text-kitb-ink-900/70 sm:mb-10 md:text-[18px]"
                    >
                        {{ companyDescription }}
                    </p>

                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-4"
                    >
                        <a
                            :href="visitUrl"
                            class="btn-primary inline-flex w-full items-center justify-center gap-2 rounded-full px-7 py-3.5 text-[15px] font-medium text-white sm:w-auto"
                        >
                            {{ trans("home.hero.submit_visit") }}

                            <ArrowRight class="h-4 w-4" />
                        </a>

                        <a
                            href="#kawasan"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-black/15 px-7 py-3.5 text-[15px] font-medium text-kitb-ink-900 transition-all duration-200 hover:-translate-y-0.5 hover:border-kitb-green-700/30 hover:bg-white/60 sm:w-auto"
                        >
                            {{ trans("home.hero.view_area") }}

                            <ChevronRight class="h-4 w-4" />
                        </a>
                    </div>
                </div>

                <div
                    v-if="stats.length"
                    v-fade-in
                    class="mt-16 grid max-w-5xl grid-cols-2 gap-x-6 gap-y-8 border-t border-black/10 pt-10 md:mt-24 md:grid-cols-4 md:gap-x-8"
                >
                    <div v-for="(s, i) in stats" :key="s.label" class="group">
                        <div
                            class="stat-number text-2xl text-kitb-green-800 transition-transform duration-300 group-hover:-translate-y-1 sm:text-3xl md:text-4xl"
                        >
                            <span
                                v-count-up="{
                                    target: s.target,
                                    decimals: s.decimals,
                                    delay: i * 120,
                                }"
                            >
                                0
                            </span>

                            <span
                                v-if="s.suffix"
                                class="ml-0.5 align-top text-base font-semibold sm:text-lg"
                            >
                                {{ s.suffix }}
                            </span>
                        </div>

                        <div
                            class="mt-1.5 max-w-[180px] text-[12px] leading-relaxed text-kitb-ink-900/55 sm:text-[13px]"
                        >
                            {{ s.label }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             TENTANG KAMI
             ===================================================== -->

        <section
            id="tentang-kami"
            class="relative overflow-hidden py-16 sm:py-24 lg:py-28"
        >
            <div
                class="pointer-events-none absolute -left-32 top-20 h-72 w-72 rounded-full bg-kitb-teal-100/50 blur-3xl"
                aria-hidden="true"
            />

            <div
                class="pointer-events-none absolute -right-32 bottom-0 h-80 w-80 rounded-full bg-slate-200/60 blur-3xl"
                aria-hidden="true"
            />

            <div class="relative mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(320px,0.72fr)] lg:items-center lg:gap-16"
                >
                    <div class="min-w-0">
                        <div class="mb-5 flex items-center gap-3">
                            <span class="h-px w-10 bg-kitb-teal-500" />

                            <p
                                class="text-[12px] font-semibold uppercase tracking-[0.16em] text-kitb-teal-600"
                            >
                                {{ trans("home.company.eyebrow") }}
                            </p>
                        </div>

                        <h2
                            class="max-w-3xl font-display font-bold leading-[1.08] tracking-[-0.025em] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 4vw, 3.2rem)"
                        >
                            {{ trans("home.company.title") }}
                        </h2>

                        <div
                            class="mt-6 max-w-2xl space-y-5 text-[15px] leading-[1.9] text-kitb-ink-900/70 sm:text-base"
                        >
                            <p v-if="companyDescription">
                                {{ truncate(companyDescription, 360) }}
                            </p>

                            <p>
                                {{ trans("home.company.description") }}

                                <span
                                    class="font-display font-semibold text-kitb-green-800"
                                >
                                    {{ trans("home.company.smart_green") }}
                                </span>

                                {{
                                    trans(
                                        "home.company.description_continuation",
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            v-if="companyName"
                            class="mt-8 flex items-center gap-3"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-kitb-teal-50 text-kitb-teal-600"
                            >
                                <Building2 class="h-5 w-5" />
                            </div>

                            <div class="min-w-0">
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    {{ trans("home.company.company_label") }}
                                </p>

                                <p
                                    class="mt-0.5 font-display font-semibold text-slate-800"
                                >
                                    {{ companyName }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-9">
                            <Link
                                href="/profil-perusahaan/tentang-kami"
                                class="group inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-800 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-kitb-teal-300 hover:text-kitb-teal-700 hover:shadow-md"
                            >
                                {{ trans("home.company.learn_more") }}

                                <ArrowRight
                                    class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                                />
                            </Link>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        <div
                            class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                        >
                            <div
                                class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-kitb-teal-50 text-kitb-teal-600"
                            >
                                <Sparkles class="h-5 w-5" />
                            </div>

                            <h3
                                class="font-display text-lg font-semibold text-slate-900"
                            >
                                {{ trans("home.company.smart_title") }}
                            </h3>

                            <p class="mt-2 text-sm leading-7 text-slate-500">
                                {{ trans("home.company.smart_description") }}
                            </p>
                        </div>

                        <div
                            class="rounded-3xl border border-slate-200/80 bg-slate-900 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1"
                        >
                            <div
                                class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-kitb-teal-300"
                            >
                                <Globe2 class="h-5 w-5" />
                            </div>

                            <h3
                                class="font-display text-lg font-semibold text-white"
                            >
                                {{ trans("home.company.green_title") }}
                            </h3>

                            <p class="mt-2 text-sm leading-7 text-white/60">
                                {{ trans("home.company.green_description") }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             SAMBUTAN DIREKTUR
             ===================================================== -->

        <section
            v-if="hasSambutanDirektur"
            id="sambutan-direktur"
            class="relative overflow-hidden bg-slate-50 py-16 sm:py-24 md:py-28"
        >
            <div
                class="pointer-events-none absolute -left-40 top-10 h-80 w-80 rounded-full bg-kitb-teal-100/60 blur-3xl"
                aria-hidden="true"
            />

            <div
                class="pointer-events-none absolute -right-40 bottom-0 h-96 w-96 rounded-full bg-kitb-navy-100/60 blur-3xl"
                aria-hidden="true"
            />

            <div class="relative mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="grid gap-10 lg:gap-16"
                    :class="
                        direkturPhoto
                            ? 'lg:grid-cols-[minmax(280px,0.62fr)_minmax(0,1.38fr)] lg:items-start'
                            : ''
                    "
                >
                    <figure
                        v-if="direkturPhoto"
                        class="relative mx-auto w-full max-w-[22rem] sm:max-w-sm lg:sticky lg:top-28 lg:mx-0 lg:max-w-md"
                    >
                        <div
                            class="pointer-events-none absolute -left-8 -top-8 h-32 w-32 rounded-full bg-kitb-teal-300/25 blur-2xl"
                            aria-hidden="true"
                        />

                        <div
                            class="pointer-events-none absolute -bottom-10 -right-8 h-40 w-40 rounded-full bg-kitb-navy-300/25 blur-3xl"
                            aria-hidden="true"
                        />

                        <div
                            class="relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white p-2 shadow-xl shadow-kitb-navy-900/10 sm:rounded-[2rem]"
                        >
                            <div
                                class="overflow-hidden rounded-[1.4rem] bg-slate-100 sm:rounded-[1.6rem]"
                            >
                                <img
                                    :src="direkturPhoto"
                                    :alt="
                                        direkturName
                                            ? trans(
                                                  'home.director.photo_alt_named',
                                                  { name: direkturName },
                                              )
                                            : trans('home.director.photo_alt')
                                    "
                                    class="aspect-[4/5] h-auto w-full object-cover object-top transition-transform duration-700 hover:scale-[1.025]"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </div>
                        </div>

                        <figcaption
                            v-if="direkturName || direkturPosition"
                            class="relative mt-5 text-center lg:hidden"
                        >
                            <p
                                v-if="direkturName"
                                class="font-display text-lg font-bold text-slate-900"
                            >
                                {{ direkturName }}
                            </p>

                            <p
                                v-if="direkturPosition"
                                class="mt-0.5 text-sm font-medium text-kitb-teal-600"
                            >
                                {{ direkturPosition }}
                            </p>
                        </figcaption>
                    </figure>

                    <div class="relative min-w-0">
                        <div class="mb-5 flex items-center gap-3">
                            <span class="h-px w-10 bg-kitb-teal-500" />

                            <p
                                class="text-[12px] font-semibold uppercase tracking-[0.16em] text-kitb-teal-600"
                            >
                                {{ trans("home.director.eyebrow") }}
                            </p>
                        </div>

                        <div
                            class="relative rounded-[1.5rem] border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-sm sm:rounded-[2rem] sm:p-9 md:p-10"
                        >
                            <Quote
                                class="pointer-events-none absolute right-5 top-5 h-10 w-10 text-kitb-navy-900/[0.07] sm:right-8 sm:top-7 sm:h-14 sm:w-14"
                                aria-hidden="true"
                            />

                            <div class="relative">
                                <div
                                    class="rich-content"
                                    :class="
                                        sambutanIsLong && !sambutanExpanded
                                            ? 'rich-content-collapsed'
                                            : ''
                                    "
                                    v-html="direkturHtml"
                                />

                                <div
                                    v-if="sambutanIsLong && !sambutanExpanded"
                                    class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-white via-white/85 to-transparent"
                                    aria-hidden="true"
                                />
                            </div>

                            <button
                                v-if="sambutanIsLong"
                                type="button"
                                class="mt-4 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-kitb-green-700 transition-all duration-200 hover:border-kitb-green-700/30 hover:bg-kitb-green-700 hover:text-white"
                                :aria-expanded="sambutanExpanded"
                                @click="sambutanExpanded = !sambutanExpanded"
                            >
                                {{
                                    sambutanExpanded
                                        ? trans("home.director.show_less")
                                        : trans("home.director.read_more")
                                }}

                                <ChevronDown
                                    class="h-4 w-4 transition-transform duration-300"
                                    :class="
                                        sambutanExpanded ? 'rotate-180' : ''
                                    "
                                />
                            </button>

                            <div
                                v-if="direkturName || direkturPosition"
                                class="mt-8 border-t border-slate-200 pt-6"
                            >
                                <p
                                    v-if="direkturName"
                                    class="font-display text-lg font-bold text-slate-900 sm:text-xl"
                                >
                                    {{ direkturName }}
                                </p>

                                <p
                                    v-if="direkturPosition"
                                    class="mt-1 text-sm font-medium text-kitb-teal-600"
                                >
                                    {{ direkturPosition }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             VISI MISI
             ===================================================== -->

        <section
            v-if="hasVisionMission"
            id="visi-misi"
            class="relative overflow-hidden bg-kitb-navy-900 py-16 sm:py-24 md:py-32"
        >
            <div
                class="blob blob-organic-3 drift-a"
                aria-hidden="true"
                style="
                    width: 520px;
                    height: 520px;
                    bottom: -220px;
                    right: -160px;
                    background: radial-gradient(
                        circle at 40% 40%,
                        #2e6fbf,
                        transparent 70%
                    );
                    opacity: 0.45;
                "
            />

            <div
                class="blob blob-organic-1"
                aria-hidden="true"
                style="
                    width: 260px;
                    height: 260px;
                    top: 60px;
                    left: -100px;
                    background: #7fa8e0;
                    opacity: 0.08;
                "
            />

            <div class="relative mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div class="mb-12 max-w-3xl md:mb-16" v-fade-in>
                    <p
                        class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-300"
                    >
                        {{ trans("home.vision_mission.vision") }}
                    </p>

                    <h2
                        v-if="visionText"
                        class="font-display font-bold leading-[1.12] tracking-[-0.02em] text-white"
                        style="font-size: clamp(1.85rem, 3.8vw, 3rem)"
                    >
                        {{ visionText }}
                    </h2>
                </div>

                <div v-if="missions.length">
                    <p
                        class="mb-8 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-300"
                    >
                        {{ trans("home.vision_mission.mission") }}
                    </p>

                    <div
                        class="grid gap-x-10 gap-y-8 md:grid-cols-2 md:gap-y-10"
                    >
                        <div
                            v-for="(m, i) in missions"
                            :key="m.id ?? i"
                            v-fade-in
                            :style="{
                                transitionDelay: `${i * 90}ms`,
                            }"
                            class="relative border-white/10 pb-8 md:pb-0"
                            :class="
                                i < missions.length - 1
                                    ? 'border-b md:border-b-0'
                                    : ''
                            "
                        >
                            <div class="mb-5 flex items-center gap-3">
                                <span
                                    class="font-display text-sm font-semibold text-kitb-teal-300"
                                >
                                    {{ String(i + 1).padStart(2, "0") }}
                                </span>

                                <span class="h-px w-10 bg-white/15" />
                            </div>

                            <h3
                                class="mb-2.5 font-display text-lg font-semibold text-white sm:text-xl"
                            >
                                {{
                                    localizedFirstValue(
                                        m,
                                        ["judul", "title"],
                                        trans(
                                            "home.vision_mission.mission_fallback",
                                            {
                                                number: String(i + 1).padStart(
                                                    2,
                                                    "0",
                                                ),
                                            },
                                        ),
                                    )
                                }}
                            </h3>

                            <p
                                v-if="
                                    localizedFirstValue(m, [
                                        'deskripsi',
                                        'desc',
                                        'isi',
                                    ])
                                "
                                class="text-[15px] leading-[1.8] text-white/60"
                            >
                                {{
                                    localizedFirstValue(m, [
                                        "deskripsi",
                                        "desc",
                                        "isi",
                                    ])
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             LOKASI & RUTE
             ===================================================== -->

        <section
            v-if="hasLocationSection"
            id="lokasi"
            class="relative overflow-hidden py-16 sm:py-24 md:py-32"
        >
            <div class="mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div class="grid items-start gap-10 md:grid-cols-12 md:gap-12">
                    <div class="md:col-span-5" v-fade-in>
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            {{ trans("home.location.eyebrow") }}
                        </p>

                        <h2
                            class="mb-6 font-display font-bold leading-[1.08] tracking-[-0.02em] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 3.4vw, 2.8rem)"
                        >
                            {{ trans("home.location.title") }}
                        </h2>

                        <p
                            class="mb-8 text-[16px] leading-[1.8] text-kitb-ink-900/70"
                        >
                            {{ trans("home.location.description") }}
                        </p>

                        <div class="space-y-4">
                            <div
                                v-if="kawasanLocation"
                                class="flex items-start gap-3.5"
                            >
                                <div
                                    class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-kitb-teal-500/10 text-kitb-teal-600"
                                >
                                    <Navigation class="h-4 w-4" />
                                </div>

                                <div>
                                    <div
                                        class="mb-0.5 text-[13px] font-medium text-kitb-ink-900/45"
                                    >
                                        {{ trans("home.location.location") }}
                                    </div>

                                    <p class="text-[15px] text-kitb-ink-900/70">
                                        {{ kawasanLocation }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="kawasanYear"
                                class="flex items-start gap-3.5"
                            >
                                <div
                                    class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-kitb-teal-500/10 text-kitb-teal-600"
                                >
                                    <CalendarDays class="h-4 w-4" />
                                </div>

                                <div>
                                    <div
                                        class="mb-0.5 text-[13px] font-medium text-kitb-ink-900/45"
                                    >
                                        {{ trans("home.location.established") }}
                                    </div>

                                    <p class="text-[15px] text-kitb-ink-900/70">
                                        {{ kawasanYear }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3.5">
                                <div
                                    class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-kitb-teal-500/10 text-kitb-teal-600"
                                >
                                    <Ship class="h-4 w-4" />
                                </div>

                                <div>
                                    <div
                                        class="mb-0.5 text-[13px] font-medium text-kitb-ink-900/45"
                                    >
                                        {{
                                            trans(
                                                "home.location.maritime_connectivity",
                                            )
                                        }}
                                    </div>

                                    <p class="text-[15px] text-kitb-ink-900/70">
                                        {{
                                            trans(
                                                "home.location.maritime_description",
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="displayedRoutes.length"
                        v-fade-in
                        class="min-w-0 md:col-span-6 md:col-start-7"
                        style="transition-delay: 120ms"
                    >
                        <div
                            class="overflow-hidden rounded-2xl border border-black/5 bg-kitb-sand-100 shadow-sm"
                        >
                            <div class="border-b border-black/5 px-5 py-4">
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <div>
                                        <div
                                            class="text-[11px] font-semibold uppercase tracking-[0.12em] text-kitb-teal-600"
                                        >
                                            {{
                                                trans(
                                                    "home.location.shipping_routes",
                                                )
                                            }}
                                        </div>

                                        <div
                                            class="mt-1 font-display text-lg font-semibold text-kitb-green-900"
                                        >
                                            {{
                                                trans(
                                                    "home.location.main_access",
                                                )
                                            }}
                                        </div>
                                    </div>

                                    <Ship
                                        class="h-5 w-5 shrink-0 text-kitb-green-700"
                                    />
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[620px] text-[14px]">
                                    <thead>
                                        <tr class="bg-kitb-green-700 text-left">
                                            <th
                                                scope="col"
                                                class="px-4 py-3.5 text-[12px] font-medium text-white sm:px-5"
                                            >
                                                {{
                                                    trans("home.location.route")
                                                }}
                                            </th>

                                            <th
                                                scope="col"
                                                class="px-4 py-3.5 text-[12px] font-medium text-white sm:px-5"
                                            >
                                                {{
                                                    trans("home.location.path")
                                                }}
                                            </th>

                                            <th
                                                scope="col"
                                                class="px-4 py-3.5 text-[12px] font-medium text-white sm:px-5"
                                            >
                                                {{
                                                    trans(
                                                        "home.location.distance",
                                                    )
                                                }}
                                            </th>

                                            <th
                                                scope="col"
                                                class="px-4 py-3.5 text-[12px] font-medium text-white sm:px-5"
                                            >
                                                {{
                                                    trans("home.location.time")
                                                }}
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr
                                            v-for="(
                                                r, index
                                            ) in displayedRoutes"
                                            :key="r.id"
                                            class="route-row border-b border-black/5 last:border-0"
                                        >
                                            <td
                                                class="px-4 py-4 font-medium text-kitb-green-800 sm:px-5"
                                            >
                                                {{ routeName(r, index) }}
                                            </td>

                                            <td
                                                class="px-4 py-4 text-kitb-ink-900/70 sm:px-5"
                                            >
                                                {{ routePath(r) || "—" }}
                                            </td>

                                            <td
                                                class="whitespace-nowrap px-4 py-4 text-kitb-ink-900/70 sm:px-5"
                                            >
                                                {{
                                                    firstValue(
                                                        r,
                                                        ["jarak", "distance"],
                                                        "—",
                                                    )
                                                }}
                                            </td>

                                            <td
                                                class="whitespace-nowrap px-4 py-4 text-kitb-ink-900/70 sm:px-5"
                                            >
                                                {{
                                                    firstValue(
                                                        r,
                                                        [
                                                            "waktu_tempuh",
                                                            "waktu",
                                                        ],
                                                        "—",
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                class="flex flex-col gap-3 border-t border-black/5 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <p
                                    class="text-[12px] leading-relaxed text-kitb-ink-900/45"
                                >
                                    {{ trans("home.location.route_note") }}
                                </p>

                                <Link
                                    href="/hubungan-investor/rute-pelayaran-lokasi"
                                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full border border-kitb-green-700/15 bg-white px-4 py-2.5 text-[12px] font-semibold text-kitb-green-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-kitb-green-700/25 hover:bg-kitb-green-700 hover:text-white"
                                >
                                    {{ trans("home.location.view_all_routes") }}

                                    <ArrowRight class="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             PROFIL KAWASAN
             ===================================================== -->

        <section
            v-if="props.profilKawasan"
            id="kawasan"
            class="relative overflow-hidden bg-kitb-sand-100 py-16 sm:py-24 md:py-32"
        >
            <div
                class="blob blob-organic-2 drift-b"
                aria-hidden="true"
                style="
                    width: 460px;
                    height: 460px;
                    top: -140px;
                    right: -180px;
                    background: radial-gradient(
                        circle at 40% 40%,
                        #1f4c91,
                        transparent 70%
                    );
                    opacity: 0.18;
                "
            />

            <div class="relative mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-16">
                    <div v-fade-in class="order-2 lg:order-1 lg:col-span-5">
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            {{ trans("home.area.eyebrow") }}
                        </p>

                        <h2
                            class="mb-5 font-display font-bold leading-[1.08] tracking-[-0.02em] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 3.5vw, 2.8rem)"
                        >
                            {{ kawasanName }}
                        </h2>

                        <p
                            v-if="kawasanDescription"
                            class="mb-8 text-[15.5px] leading-[1.85] text-kitb-ink-900/65"
                        >
                            {{ kawasanDescription }}
                        </p>

                        <div
                            v-if="kawasanArea > 0 || kawasanYear"
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4"
                        >
                            <div
                                v-if="kawasanArea > 0"
                                class="rounded-2xl border border-black/5 bg-white/65 p-4 sm:p-5"
                            >
                                <div
                                    class="mb-1 text-[11px] font-semibold uppercase tracking-[0.1em] text-kitb-ink-900/40"
                                >
                                    {{ trans("home.area.area_size") }}
                                </div>

                                <div
                                    class="font-display text-2xl font-bold text-kitb-green-800 sm:text-3xl"
                                >
                                    {{
                                        formatNumber(
                                            kawasanArea,
                                            kawasanAreaDecimals,
                                        )
                                    }}

                                    <span
                                        class="text-sm font-medium text-kitb-ink-900/45"
                                    >
                                        Ha
                                    </span>
                                </div>
                            </div>

                            <div
                                v-if="kawasanYear"
                                class="rounded-2xl border border-black/5 bg-white/65 p-4 sm:p-5"
                            >
                                <div
                                    class="mb-1 text-[11px] font-semibold uppercase tracking-[0.1em] text-kitb-ink-900/40"
                                >
                                    {{ trans("home.area.established") }}
                                </div>

                                <div
                                    class="font-display text-2xl font-bold text-kitb-green-800 sm:text-3xl"
                                >
                                    {{ kawasanYear }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="kawasanImage"
                        v-fade-in
                        class="order-1 lg:order-2 lg:col-span-7"
                    >
                        <div
                            class="group relative overflow-hidden rounded-[1.75rem] border border-black/5 bg-white shadow-xl shadow-kitb-navy-900/10"
                        >
                            <img
                                :src="kawasanImage"
                                :alt="kawasanName"
                                class="h-auto max-h-[520px] w-full object-cover transition-transform duration-700 group-hover:scale-[1.025]"
                                loading="lazy"
                                decoding="async"
                            />

                            <div
                                v-if="kawasanLocation"
                                class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-kitb-navy-900/70 to-transparent p-5 pt-16 sm:p-7 sm:pt-20"
                            >
                                <div
                                    class="flex items-center gap-2 text-sm font-medium text-white"
                                >
                                    <MapPin class="h-4 w-4 shrink-0" />

                                    {{ kawasanLocation }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="landStats.length"
                    v-fade-in
                    class="mt-16 border-t border-black/10 pt-12 md:mt-20 md:pt-16"
                >
                    <div
                        v-for="(land, i) in landStats"
                        :key="land.label"
                        class="max-w-sm"
                    >
                        <div
                            class="stat-number mb-2 text-4xl text-kitb-green-800 md:text-5xl"
                        >
                            <span
                                v-count-up="{
                                    target: land.target,
                                    decimals: land.decimals,
                                    delay: i * 120,
                                }"
                            >
                                0
                            </span>

                            <span class="ml-1 align-top text-xl">
                                {{ land.unit }}
                            </span>
                        </div>

                        <div
                            class="max-w-xs text-[14px] leading-relaxed text-kitb-ink-900/55"
                        >
                            {{ land.label }}
                        </div>
                    </div>
                </div>

                <div
                    class="mt-16 border-t border-black/10 pt-12 md:mt-20 md:pt-16"
                >
                    <div class="mb-10 max-w-2xl" v-fade-in>
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            {{ trans("home.area.development") }}
                        </p>

                        <h3
                            class="font-display font-bold leading-[1.1] text-kitb-green-900"
                            style="font-size: clamp(1.6rem, 3vw, 2.4rem)"
                        >
                            {{ trans("home.area.development_title") }}
                        </h3>
                    </div>

                    <div class="grid gap-5 md:grid-cols-3 md:gap-6">
                        <div
                            v-for="(stage, i) in developmentStages"
                            :key="stage.number"
                            v-fade-in
                            :style="{
                                transitionDelay: `${i * 100}ms`,
                            }"
                            class="group relative overflow-hidden rounded-2xl border border-black/5 bg-white/65 p-6 transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-xl hover:shadow-kitb-navy-900/5"
                        >
                            <div class="mb-7 flex items-center justify-between">
                                <span
                                    class="font-display text-3xl font-bold text-kitb-green-700/20"
                                >
                                    {{ stage.number }}
                                </span>

                                <MoveRight
                                    class="h-5 w-5 text-kitb-teal-500 transition-transform duration-300 group-hover:translate-x-1"
                                />
                            </div>

                            <h4
                                class="mb-2.5 font-display text-lg font-semibold text-kitb-green-900"
                            >
                                {{ stage.title }}
                            </h4>

                            <p
                                class="text-[14px] leading-[1.75] text-kitb-ink-900/60"
                            >
                                {{ stage.desc }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="props.petaKawasan && mapImage"
                    class="mt-16 border-t border-black/10 pt-12 md:mt-20 md:pt-16"
                >
                    <div
                        v-fade-in
                        class="grid items-start gap-8 lg:grid-cols-12 lg:gap-12"
                    >
                        <div class="lg:col-span-4">
                            <p
                                class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                            >
                                {{ trans("home.area.master_plan") }}
                            </p>

                            <h3
                                class="mb-5 font-display font-bold leading-[1.1] text-kitb-green-900"
                                style="font-size: clamp(1.55rem, 2.8vw, 2.3rem)"
                            >
                                {{ mapTitle }}
                            </h3>

                            <p
                                v-if="mapDescription"
                                class="mb-7 text-[15px] leading-[1.8] text-kitb-ink-900/65"
                            >
                                {{ mapDescription }}
                            </p>

                            <div
                                v-if="kawasanArea > 0"
                                class="rounded-2xl bg-kitb-navy-900 p-5 text-white"
                            >
                                <div
                                    class="mb-1 text-[11px] font-medium uppercase tracking-[0.1em] text-white/45"
                                >
                                    {{ trans("home.area.total_area") }}
                                </div>

                                <div class="font-display text-3xl font-bold">
                                    {{
                                        formatNumber(
                                            kawasanArea,
                                            kawasanAreaDecimals,
                                        )
                                    }}

                                    <span
                                        class="text-lg font-medium text-white/55"
                                    >
                                        Ha
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="min-w-0 lg:col-span-8">
                            <div
                                class="group overflow-hidden rounded-2xl border border-black/5 bg-white shadow-lg shadow-kitb-navy-900/5"
                            >
                                <a
                                    :href="mapImage"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :aria-label="
                                        trans('home.area.map_full_size', {
                                            title: mapTitle,
                                        })
                                    "
                                    class="relative block"
                                >
                                    <img
                                        :src="mapImage"
                                        :alt="mapTitle"
                                        class="h-auto max-h-[650px] w-full object-contain transition-transform duration-700 group-hover:scale-[1.01]"
                                        loading="lazy"
                                        decoding="async"
                                    />

                                    <div
                                        class="pointer-events-none absolute inset-0 hidden items-end justify-center bg-gradient-to-t from-kitb-navy-900/35 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 md:flex"
                                    >
                                        <span
                                            class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-medium text-kitb-navy-900 shadow-lg"
                                        >
                                            <ExternalLink class="h-3.5 w-3.5" />

                                            {{
                                                trans(
                                                    "home.area.open_full_size",
                                                )
                                            }}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <p
                                class="mt-3 text-[12.5px] leading-relaxed text-kitb-ink-900/45"
                            >
                                {{ trans("home.area.map_note") }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             ANAK USAHA
             ===================================================== -->

        <section
            v-if="props.anakUsahas.length"
            id="anak-usaha"
            class="relative overflow-hidden py-16 sm:py-24 md:py-32"
        >
            <div class="mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="mb-12 flex flex-col justify-between gap-6 md:mb-16 md:flex-row md:items-end"
                >
                    <div class="max-w-2xl">
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            {{ trans("home.subsidiaries.eyebrow") }}
                        </p>

                        <h2
                            class="font-display font-bold leading-[1.08] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 3.5vw, 2.8rem)"
                        >
                            {{ trans("home.subsidiaries.title") }}
                        </h2>
                    </div>

                    <Building2
                        class="hidden h-10 w-10 text-kitb-green-700/20 md:block"
                    />
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(anak, index) in props.anakUsahas"
                        :key="anak.id"
                        v-fade-in
                        :style="{
                            transitionDelay: `${index * 80}ms`,
                        }"
                        class="group rounded-2xl border border-black/5 bg-white/60 p-5 transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-xl hover:shadow-kitb-navy-900/5 sm:p-6"
                    >
                        <div
                            class="mb-6 flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl border border-black/5 bg-kitb-sand-100"
                        >
                            <img
                                v-if="anakUsahaImage(anak)"
                                :src="anakUsahaImage(anak)"
                                :alt="anakUsahaName(anak)"
                                class="h-full w-full object-contain p-2"
                                loading="lazy"
                                decoding="async"
                            />

                            <Building2
                                v-else
                                class="h-7 w-7 text-kitb-green-700/40"
                            />
                        </div>

                        <h3
                            class="mb-2 font-display text-lg font-semibold text-kitb-green-900"
                        >
                            {{ anakUsahaName(anak) }}
                        </h3>

                        <p
                            v-if="localizedFirstValue(anak, ['deskripsi'])"
                            class="mb-5 text-[14px] leading-[1.75] text-kitb-ink-900/60"
                        >
                            {{
                                truncate(
                                    localizedFirstValue(anak, ["deskripsi"]),
                                    130,
                                )
                            }}
                        </p>

                        <a
                            v-if="anak.website"
                            :href="normalizeWebsite(anak.website)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 text-[13px] font-semibold text-kitb-green-700 transition-colors hover:text-kitb-navy-900"
                        >
                            {{ trans("home.subsidiaries.visit_website") }}

                            <ExternalLink class="h-3.5 w-3.5" />
                        </a>
                    </article>
                </div>
            </div>
        </section>

        <!-- =====================================================
             MITRA PERUSAHAAN
             ===================================================== -->

        <section
            v-if="hasMitraPerusahaan"
            id="mitra-perusahaan"
            class="relative overflow-hidden bg-white py-16 sm:py-24 md:py-32"
        >
            <div
                class="pointer-events-none absolute -left-40 top-20 h-80 w-80 rounded-full bg-kitb-teal-100/50 blur-3xl"
                aria-hidden="true"
            />

            <div
                class="pointer-events-none absolute -right-40 bottom-0 h-96 w-96 rounded-full bg-kitb-navy-100/40 blur-3xl"
                aria-hidden="true"
            />

            <div class="relative mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="mb-10 flex flex-col gap-6 md:mb-14 md:flex-row md:items-end md:justify-between"
                >
                    <div class="max-w-2xl">
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            {{ trans("home.partners.eyebrow") }}
                        </p>

                        <h2
                            class="font-display font-bold leading-[1.08] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 3.5vw, 2.8rem)"
                        >
                            {{ trans("home.partners.title") }}
                        </h2>

                        <p
                            class="mt-5 max-w-xl text-[15px] leading-[1.8] text-kitb-ink-900/60"
                        >
                            {{ trans("home.partners.description") }}
                        </p>
                    </div>

                    <div
                        v-if="displayedMitraPerusahaans.length > 1"
                        class="flex shrink-0 items-center gap-2"
                    >
                        <button
                            type="button"
                            :aria-label="trans('home.partners.previous')"
                            class="group flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-kitb-green-700 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-kitb-teal-300 hover:bg-kitb-green-700 hover:text-white hover:shadow-md focus:outline-none focus:ring-2 focus:ring-kitb-teal-500/40 focus:ring-offset-2"
                            @click="scrollMitra('prev')"
                            @mouseenter="pauseMitraAutoplay"
                            @mouseleave="resumeMitraAutoplay"
                        >
                            <ChevronRight
                                class="h-5 w-5 rotate-180 transition-transform duration-300 group-hover:-translate-x-0.5"
                            />
                        </button>

                        <button
                            type="button"
                            :aria-label="trans('home.partners.next')"
                            class="group flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-kitb-green-700 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-kitb-teal-300 hover:bg-kitb-green-700 hover:text-white hover:shadow-md focus:outline-none focus:ring-2 focus:ring-kitb-teal-500/40 focus:ring-offset-2"
                            @click="scrollMitra('next')"
                            @mouseenter="pauseMitraAutoplay"
                            @mouseleave="resumeMitraAutoplay"
                        >
                            <ChevronRight
                                class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-0.5"
                            />
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <div
                        ref="mitraCarousel"
                        class="mitra-carousel flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth pb-5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden sm:gap-6"
                        tabindex="0"
                        :aria-label="trans('home.partners.list')"
                        @mouseenter="pauseMitraAutoplay"
                        @mouseleave="resumeMitraAutoplay"
                        @focusin="pauseMitraAutoplay"
                        @focusout="resumeMitraAutoplay"
                    >
                        <article
                            v-for="(mitra, index) in displayedMitraPerusahaans"
                            :key="mitra.id"
                            data-mitra-card
                            v-fade-in
                            :style="{
                                transitionDelay: `${index * 70}ms`,
                            }"
                            class="group relative w-[82%] shrink-0 snap-start overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-kitb-teal-200 hover:shadow-xl hover:shadow-kitb-navy-900/5 sm:w-[calc(50%-12px)] sm:p-6 lg:w-[calc(33.333%-16px)] xl:w-[calc(25%-18px)]"
                        >
                            <div
                                class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-kitb-teal-100/50 opacity-0 blur-2xl transition-opacity duration-300 group-hover:opacity-100"
                                aria-hidden="true"
                            />

                            <div
                                class="relative mb-5 flex h-24 items-center justify-center overflow-hidden rounded-2xl border border-slate-100 bg-slate-50/80 p-4 transition-colors duration-300 group-hover:bg-kitb-teal-50/40"
                            >
                                <img
                                    v-if="mitraPerusahaanImage(mitra)"
                                    :src="mitraPerusahaanImage(mitra)"
                                    :alt="mitraPerusahaanName(mitra)"
                                    class="h-full w-full object-contain transition-transform duration-500 group-hover:scale-105"
                                    loading="lazy"
                                    decoding="async"
                                />

                                <Handshake
                                    v-else
                                    class="h-9 w-9 text-kitb-green-700/30"
                                    aria-hidden="true"
                                />
                            </div>

                            <div class="relative min-w-0">
                                <h3
                                    class="line-clamp-2 font-display text-base font-semibold leading-[1.4] text-kitb-green-900 transition-colors duration-300 group-hover:text-kitb-navy-900 sm:text-lg"
                                >
                                    {{ mitraPerusahaanName(mitra) }}
                                </h3>

                                <a
                                    v-if="mitra.website"
                                    :href="normalizeWebsite(mitra.website)"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-4 inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-kitb-green-700 transition-colors hover:text-kitb-navy-900 focus:outline-none focus:ring-2 focus:ring-kitb-teal-500/40 focus:ring-offset-2"
                                    @click.stop
                                >
                                    {{ trans("home.partners.visit_website") }}

                                    <ExternalLink class="h-3.5 w-3.5" />
                                </a>
                            </div>
                        </article>
                    </div>

                    <div
                        v-if="displayedMitraPerusahaans.length > 1"
                        class="mt-2 flex items-center gap-3"
                        aria-hidden="true"
                    >
                        <div class="h-px flex-1 bg-slate-100" />

                        <span
                            class="h-1.5 w-1.5 rounded-full bg-kitb-teal-500/60"
                        />

                        <div class="h-px flex-1 bg-slate-100" />
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             INVESTASI
             ===================================================== -->

        <section
            v-if="props.peluangInvestasi.length"
            id="investasi"
            class="relative overflow-hidden bg-kitb-navy-900 py-16 sm:py-24 md:py-32"
        >
            <div
                class="blob blob-organic-3 drift-a"
                aria-hidden="true"
                style="
                    width: 520px;
                    height: 520px;
                    top: -280px;
                    left: -180px;
                    background: radial-gradient(
                        circle at 60% 50%,
                        #2e6fbf,
                        transparent 70%
                    );
                    opacity: 0.28;
                "
            />

            <div class="relative mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="mb-12 flex flex-col justify-between gap-6 md:mb-16 md:flex-row md:items-end"
                >
                    <div class="max-w-2xl">
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-300"
                        >
                            {{ trans("home.investment.eyebrow") }}
                        </p>

                        <h2
                            class="font-display font-bold leading-[1.08] text-white"
                            style="font-size: clamp(1.8rem, 3.5vw, 2.8rem)"
                        >
                            {{ trans("home.investment.title") }}
                        </h2>

                        <p
                            class="mt-5 max-w-xl text-[15px] leading-[1.8] text-white/55"
                        >
                            {{ trans("home.investment.description") }}
                        </p>
                    </div>

                    <TrendingUp
                        class="hidden h-10 w-10 text-kitb-teal-300/30 md:block"
                    />
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(investment, index) in props.peluangInvestasi"
                        :key="investment.id"
                        v-fade-in
                        :style="{
                            transitionDelay: `${index * 80}ms`,
                        }"
                        class="group overflow-hidden rounded-2xl border border-white/10 bg-white/[0.055] transition-all duration-300 hover:-translate-y-1 hover:bg-white/[0.08]"
                    >
                        <div
                            v-if="investmentImage(investment)"
                            class="aspect-[16/9] overflow-hidden"
                        >
                            <img
                                :src="investmentImage(investment)"
                                :alt="investmentTitle(investment)"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                loading="lazy"
                                decoding="async"
                            />
                        </div>

                        <div class="p-5 sm:p-6">
                            <div
                                v-if="
                                    localizedFirstValue(investment, ['sektor'])
                                "
                                class="mb-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kitb-teal-300"
                            >
                                {{
                                    localizedFirstValue(investment, ["sektor"])
                                }}
                            </div>

                            <h3
                                class="mb-3 font-display text-lg font-semibold text-white"
                            >
                                {{ investmentTitle(investment) }}
                            </h3>

                            <p
                                v-if="investmentDescription(investment)"
                                class="mb-5 text-[14px] leading-[1.75] text-white/55"
                            >
                                {{
                                    truncate(
                                        investmentDescription(investment),
                                        130,
                                    )
                                }}
                            </p>

                            <div
                                v-if="hasValue(investment.luas_lahan)"
                                class="flex items-center justify-between border-t border-white/10 pt-4"
                            >
                                <span class="text-[12px] text-white/40">
                                    {{ trans("home.investment.land_size") }}
                                </span>

                                <span
                                    class="font-display font-semibold text-white"
                                >
                                    {{ formatNumber(investment.luas_lahan) }}
                                    Ha
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- =====================================================
             BERITA
             ===================================================== -->

        <section
            v-if="props.beritas.length"
            id="berita"
            class="relative overflow-hidden py-16 sm:py-24 md:py-32"
        >
            <div class="mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="mb-12 flex flex-col justify-between gap-6 md:mb-14 md:flex-row md:items-end"
                >
                    <div class="max-w-2xl">
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            {{ trans("home.news.eyebrow") }}
                        </p>

                        <h2
                            class="font-display font-bold leading-[1.08] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 3.5vw, 2.8rem)"
                        >
                            {{ trans("home.news.title") }}
                        </h2>

                        <p
                            class="mt-5 max-w-xl text-[15px] leading-[1.8] text-kitb-ink-900/60"
                        >
                            {{ trans("home.news.description") }}
                        </p>
                    </div>

                    <Link
                        href="/berita"
                        class="hidden shrink-0 items-center gap-2 text-sm font-semibold text-kitb-green-700 transition-colors hover:text-kitb-navy-900 md:inline-flex"
                    >
                        {{ trans("home.news.view_all") }}

                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(berita, index) in props.beritas"
                        :key="berita.id"
                        v-fade-in
                        :style="{
                            transitionDelay: `${index * 100}ms`,
                        }"
                        class="group overflow-hidden rounded-2xl border border-black/5 bg-white/65 transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-xl hover:shadow-kitb-navy-900/5"
                    >
                        <Link
                            :href="
                                berita.slug
                                    ? `/berita/${berita.slug}`
                                    : '/berita'
                            "
                            class="block h-full"
                        >
                            <div
                                v-if="beritaImage(berita)"
                                class="aspect-[16/9] overflow-hidden bg-kitb-sand-100"
                            >
                                <img
                                    :src="beritaImage(berita)"
                                    :alt="beritaTitle(berita)"
                                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.04]"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </div>

                            <div class="p-5 sm:p-6">
                                <div
                                    class="mb-3 flex items-center gap-2 text-[12px] text-kitb-ink-900/45"
                                >
                                    <CalendarDays class="h-3.5 w-3.5" />

                                    {{
                                        formatDate(
                                            berita.published_at ??
                                                berita.created_at,
                                        )
                                    }}
                                </div>

                                <h3
                                    class="mb-3 font-display text-lg font-semibold leading-[1.35] text-kitb-green-900 transition-colors group-hover:text-kitb-navy-900 sm:text-xl"
                                >
                                    {{ beritaTitle(berita) }}
                                </h3>

                                <p
                                    v-if="beritaExcerpt(berita)"
                                    class="text-[14px] leading-[1.75] text-kitb-ink-900/60"
                                >
                                    {{ truncate(beritaExcerpt(berita), 135) }}
                                </p>

                                <div
                                    class="mt-5 inline-flex items-center gap-2 text-[13px] font-semibold text-kitb-green-700 transition-colors group-hover:text-kitb-navy-900"
                                >
                                    {{ trans("home.news.read_more") }}

                                    <ArrowRight
                                        class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1"
                                    />
                                </div>
                            </div>
                        </Link>
                    </article>
                </div>

                <div class="mt-8 flex justify-center md:hidden">
                    <Link
                        href="/berita"
                        class="inline-flex items-center gap-2 rounded-full border border-black/10 bg-white px-5 py-3 text-sm font-semibold text-kitb-green-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-kitb-green-700/20 hover:shadow-md"
                    >
                        {{ trans("home.news.view_all") }}

                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- =====================================================
             KARIER
             ===================================================== -->

        <section
            v-if="props.lowongans.length"
            id="karier"
            class="relative overflow-hidden bg-kitb-sand-100 py-16 sm:py-24 md:py-32"
        >
            <div
                class="blob blob-organic-1 drift-a"
                aria-hidden="true"
                style="
                    width: 420px;
                    height: 420px;
                    right: -180px;
                    bottom: -220px;
                    background: radial-gradient(
                        circle at 40% 40%,
                        #4c82c8,
                        transparent 70%
                    );
                    opacity: 0.18;
                "
            />

            <div class="relative mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="mb-12 flex flex-col justify-between gap-6 md:mb-14 md:flex-row md:items-end"
                >
                    <div class="max-w-2xl">
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            {{ trans("home.career.eyebrow") }}
                        </p>

                        <h2
                            class="font-display font-bold leading-[1.08] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 3.5vw, 2.8rem)"
                        >
                            {{ trans("home.career.title") }}
                        </h2>

                        <p
                            class="mt-5 max-w-xl text-[15px] leading-[1.8] text-kitb-ink-900/60"
                        >
                            {{ trans("home.career.description") }}
                        </p>
                    </div>

                    <Link
                        href="/karier"
                        class="inline-flex shrink-0 items-center gap-2 text-sm font-semibold text-kitb-green-700 transition-colors hover:text-kitb-navy-900"
                    >
                        {{ trans("home.career.view_all") }}

                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div class="grid gap-4">
                    <Link
                        v-for="(job, index) in props.lowongans"
                        :key="job.id"
                        :href="job.slug ? `/karier/${job.slug}` : '/karier'"
                        v-fade-in
                        :style="{
                            transitionDelay: `${index * 80}ms`,
                        }"
                        class="group grid gap-4 rounded-2xl border border-black/5 bg-white/70 p-5 transition-all duration-300 hover:-translate-y-0.5 hover:bg-white hover:shadow-xl hover:shadow-kitb-navy-900/5 sm:p-6 md:grid-cols-[1fr_auto] md:items-center md:gap-5"
                    >
                        <div class="min-w-0">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span
                                    v-if="job.unggulan"
                                    class="inline-flex items-center gap-1 rounded-full bg-kitb-amber-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-kitb-amber-700"
                                >
                                    {{ trans("home.career.featured") }}
                                </span>

                                <span
                                    class="rounded-full bg-kitb-green-700/10 px-2.5 py-1 text-[10px] font-medium text-kitb-green-800"
                                >
                                    {{
                                        jobTypeLabel(
                                            localizedFirstValue(job, [
                                                "tipe_pekerjaan",
                                            ]),
                                        )
                                    }}
                                </span>
                            </div>

                            <h3
                                class="mb-2 font-display text-lg font-semibold text-kitb-green-900 transition-colors group-hover:text-kitb-navy-900 sm:text-xl"
                            >
                                {{
                                    localizedFirstValue(
                                        job,
                                        ["judul"],
                                        trans("home.career.position_available"),
                                    )
                                }}
                            </h3>

                            <div
                                v-if="
                                    localizedFirstValue(job, ['departemen']) ||
                                    localizedFirstValue(job, ['lokasi']) ||
                                    job.tanggal_tutup
                                "
                                class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[12.5px] text-kitb-ink-900/50"
                            >
                                <span
                                    v-if="
                                        localizedFirstValue(job, ['departemen'])
                                    "
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <BriefcaseBusiness class="h-3.5 w-3.5" />

                                    {{
                                        localizedFirstValue(job, ["departemen"])
                                    }}
                                </span>

                                <span
                                    v-if="localizedFirstValue(job, ['lokasi'])"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <MapPin class="h-3.5 w-3.5" />

                                    {{ localizedFirstValue(job, ["lokasi"]) }}
                                </span>

                                <span
                                    v-if="job.tanggal_tutup"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <Clock3 class="h-3.5 w-3.5" />

                                    {{
                                        trans("home.career.closing", {
                                            date: formatDate(job.tanggal_tutup),
                                        })
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="hidden h-10 w-10 items-center justify-center rounded-full border border-black/10 text-kitb-green-700 transition-all duration-300 group-hover:translate-x-1 group-hover:bg-kitb-green-700 group-hover:text-white md:flex"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- =====================================================
             FINAL CTA
             ===================================================== -->

        <section class="relative py-14 sm:py-20 md:py-28">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="relative overflow-hidden rounded-[1.75rem] bg-kitb-green-700 px-6 py-12 text-center sm:px-8 md:px-16 md:py-20"
                >
                    <div
                        class="blob blob-organic-1 drift-a"
                        aria-hidden="true"
                        style="
                            width: 300px;
                            height: 300px;
                            top: -100px;
                            left: -80px;
                            background: #2e6fbf;
                            opacity: 0.25;
                        "
                    />

                    <div
                        class="blob blob-organic-3 drift-b"
                        aria-hidden="true"
                        style="
                            width: 260px;
                            height: 260px;
                            right: -60px;
                            bottom: -100px;
                            background: #c8963e;
                            opacity: 0.15;
                        "
                    />

                    <div class="relative z-10">
                        <div
                            class="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-kitb-teal-300"
                        >
                            <Factory class="h-6 w-6" />
                        </div>

                        <p
                            class="mb-5 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-300"
                        >
                            {{ trans("home.cta.eyebrow") }}
                        </p>

                        <h2
                            class="mx-auto max-w-3xl font-display font-bold leading-[1.12] text-white"
                            style="font-size: clamp(1.7rem, 3.8vw, 3rem)"
                        >
                            {{ trans("home.cta.title") }}
                        </h2>

                        <p
                            class="mx-auto mt-5 max-w-xl text-[14px] leading-[1.8] text-white/60 sm:text-[15px]"
                        >
                            {{ trans("home.cta.description") }}
                        </p>

                        <div class="mt-8 sm:mt-10">
                            <a
                                :href="visitUrl"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-white px-8 py-3.5 text-[15px] font-medium text-[#163a70] shadow-lg shadow-black/10 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white/95 sm:w-auto"
                            >
                                {{ trans("home.cta.start_discussion") }}

                                <ArrowRight class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* =========================================================
   GLOBAL
   ========================================================= */

:global(html) {
    scroll-behavior: smooth;
    scroll-padding-top: 5.5rem;
}

.kitb-landing {
    font-family: "Poppins", ui-sans-serif, system-ui, sans-serif;
    overflow-x: clip;
}

.kitb-landing * {
    font-family: inherit;
}

.kitb-landing a:focus-visible,
.kitb-landing button:focus-visible {
    outline: 2px solid #2e6fbf;
    outline-offset: 3px;
}

/* =========================================================
   BLOB
   ========================================================= */

.blob {
    position: absolute;
    pointer-events: none;
    max-width: 90vw;
    max-height: 90vw;
    will-change: transform;
    transform-origin: center;
    z-index: 0;
}

.blob-organic-1 {
    border-radius: 42% 58% 65% 35% / 45% 40% 60% 55%;
}

.blob-organic-2 {
    border-radius: 58% 42% 35% 65% / 55% 60% 40% 45%;
}

.blob-organic-3 {
    border-radius: 38% 62% 63% 37% / 41% 44% 56% 59%;
}

@keyframes driftA {
    0%,
    100% {
        transform: translate3d(0, 0, 0) rotate(0deg) scale(1);
    }
    50% {
        transform: translate3d(18px, -24px, 0) rotate(6deg) scale(1.025);
    }
}

@keyframes driftB {
    0%,
    100% {
        transform: translate3d(0, 0, 0) rotate(0deg) scale(1);
    }
    50% {
        transform: translate3d(-22px, 20px, 0) rotate(-5deg) scale(1.03);
    }
}

.drift-a {
    animation: driftA 16s ease-in-out infinite;
}

.drift-b {
    animation: driftB 19s ease-in-out infinite;
}

.hero-blob-field {
    background: transparent;
}

.stat-number {
    font-family: "Poppins", sans-serif;
    font-weight: 700;
    letter-spacing: -0.025em;
}

/* =========================================================
   RICH CONTENT (sambutan direktur dari rich text editor)
   ---------------------------------------------------------
   Memakai :deep() karena elemen dirender lewat v-html dan
   tidak membawa atribut scoped.
   ========================================================= */

.rich-content {
    max-width: 68ch;
    font-size: 15.5px;
    line-height: 1.9;
    color: #475569;
    overflow-wrap: anywhere;
    word-break: normal;
}

.rich-content-collapsed {
    max-height: 22rem;
    overflow: hidden;
}

/* Child langsung dari konten v-html */
.rich-content > :deep(*:first-child) {
    margin-top: 0;
}

.rich-content > :deep(*:last-child) {
    margin-bottom: 0;
}

/* Paragraf & blok */
/* Paragraf: tanpa margin, supaya jarak persis seperti yang diketik di editor
   (Enter sekali = baris berikutnya, Enter dua kali = satu baris kosong) */
.rich-content :deep(p) {
    margin: 0;
}

/* Baris kosong dari editor (<p></p> / <p><br></p>) = tinggi satu baris */
.rich-content :deep(p:empty) {
    min-height: 1.9em;
}

.rich-content :deep(ul),
.rich-content :deep(ol),
.rich-content :deep(blockquote),
.rich-content :deep(figure),
.rich-content :deep(table) {
    margin: 0.6em 0 1em;
}

.rich-content :deep(strong),
.rich-content :deep(b) {
    font-weight: 600;
    color: #0f172a;
}

.rich-content :deep(em),
.rich-content :deep(i) {
    font-style: italic;
}

.rich-content :deep(u) {
    text-underline-offset: 3px;
}

.rich-content :deep(mark) {
    background: rgb(46 111 191 / 0.14);
    color: inherit;
    padding: 0 0.2em;
    border-radius: 0.25em;
}

.rich-content :deep(a) {
    color: #2e6fbf;
    text-decoration: underline;
    text-underline-offset: 3px;
    transition: color 0.2s ease;
}

.rich-content :deep(a:hover) {
    color: #163a70;
}

.rich-content :deep(h1),
.rich-content :deep(h2),
.rich-content :deep(h3),
.rich-content :deep(h4),
.rich-content :deep(h5),
.rich-content :deep(h6) {
    margin: 1.6em 0 0.6em;
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: -0.01em;
    color: #0f172a;
}

.rich-content :deep(h1),
.rich-content :deep(h2) {
    font-size: 1.3em;
}

.rich-content :deep(h3) {
    font-size: 1.15em;
}

.rich-content :deep(h4),
.rich-content :deep(h5),
.rich-content :deep(h6) {
    font-size: 1.02em;
}

.rich-content :deep(ul),
.rich-content :deep(ol) {
    padding-left: 1.4em;
}

.rich-content :deep(ul) {
    list-style: disc;
}

.rich-content :deep(ol) {
    list-style: decimal;
}

.rich-content :deep(li) {
    margin: 0.35em 0;
    padding-left: 0.2em;
}

.rich-content :deep(li::marker) {
    color: #2e6fbf;
}

.rich-content :deep(li > p) {
    margin: 0;
}

.rich-content :deep(blockquote) {
    border-left: 3px solid #2e6fbf;
    background: rgb(22 58 112 / 0.04);
    padding: 0.9em 1.2em;
    border-radius: 0 0.75rem 0.75rem 0;
    font-style: italic;
    color: #334155;
}

.rich-content :deep(hr) {
    margin: 2em 0;
    border: 0;
    border-top: 1px solid #e2e8f0;
}

.rich-content :deep(img) {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 1.4em auto;
    border-radius: 1rem;
}

.rich-content :deep(figcaption) {
    margin-top: 0.5em;
    font-size: 0.85em;
    text-align: center;
    color: #64748b;
}

.rich-content :deep(table) {
    display: block;
    width: 100%;
    overflow-x: auto;
    border-collapse: collapse;
    font-size: 0.92em;
}

.rich-content :deep(th),
.rich-content :deep(td) {
    border: 1px solid #e2e8f0;
    padding: 0.55em 0.8em;
    text-align: left;
    vertical-align: top;
}

.rich-content :deep(th) {
    background: #f1f5f9;
    font-weight: 600;
    color: #0f172a;
}

@media (min-width: 640px) {
    .rich-content {
        font-size: 16px;
    }

    .rich-content-collapsed {
        max-height: 26rem;
    }
}

@media (min-width: 768px) {
    .rich-content {
        font-size: 17px;
    }
}

/* =========================================================
   ROUTE TABLE
   ========================================================= */

.route-row {
    transition: background 0.2s ease;
}

.route-row:nth-child(odd) {
    background: rgba(22, 58, 112, 0.045);
}

.route-row:hover {
    background: rgba(22, 58, 112, 0.08);
}

:global(.dark) .route-row:nth-child(odd) {
    background: rgb(255 255 255 / 0.04);
}

/* =========================================================
   PRIMARY BUTTON
   ========================================================= */

.btn-primary {
    background: #163a70;
    transition:
        background 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.btn-primary:hover {
    background: #1f4c91;
    transform: translateY(-1px);
    box-shadow: 0 12px 28px rgb(22 58 112 / 0.18);
}

/* =========================================================
   FADE-IN
   ========================================================= */

.fade-in {
    opacity: 0;
    transform: translateY(28px);
    transition:
        opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1),
        transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-in-visible {
    opacity: 1;
    transform: translateY(0);
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1024px) {
    .blob-organic-1 {
        width: 520px !important;
        height: 520px !important;
        top: -260px !important;
        right: -210px !important;
    }

    .blob-organic-2 {
        width: 300px !important;
        height: 300px !important;
        right: 70px !important;
    }
}

@media (max-width: 640px) {
    :global(html) {
        scroll-padding-top: 4.5rem;
    }

    .blob-organic-1 {
        width: 390px !important;
        height: 390px !important;
        top: -210px !important;
        right: -190px !important;
        opacity: 0.62 !important;
    }

    .blob-organic-2 {
        width: 240px !important;
        height: 240px !important;
        right: -55px !important;
        bottom: -90px !important;
        opacity: 0.55 !important;
    }

    .fade-in {
        transform: translateY(18px);
    }
}

@media (prefers-reduced-motion: reduce) {
    :global(html) {
        scroll-behavior: auto;
    }

    .drift-a,
    .drift-b {
        animation: none;
    }

    .fade-in {
        opacity: 1;
        transform: none;
        transition: none;
    }

    .btn-primary:hover {
        transform: none;
    }
}
</style>
