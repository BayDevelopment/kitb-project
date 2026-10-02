<script setup lang="ts">
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowRight,
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    ChevronRight,
    ExternalLink,
    Factory,
    Globe2,
    MapPin,
    MoveRight,
    Navigation,
    Ship,
    Sparkles,
    TrendingUp,
    Clock3,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";
import { visitUrl } from "@/data/publicNavigation";

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
   TYPES
   ========================================================= */

interface CompanyProfile {
    id?: number;
    nama?: string;
    nama_perusahaan?: string;
    judul?: string;
    deskripsi?: string;
    tentang?: string;
    logo?: string;
    logo_path?: string;
    [key: string]: unknown;
}

interface Misi {
    id?: number;
    judul?: string;
    title?: string;
    deskripsi?: string;
    desc?: string;
    isi?: string;
    urutan?: number;
    [key: string]: unknown;
}

interface Visi {
    id?: number;
    judul?: string;
    title?: string;
    visi?: string;
    deskripsi?: string;
    misis?: Misi[];
    [key: string]: unknown;
}

interface AnakUsaha {
    id: number;
    nama?: string;
    nama_perusahaan?: string;
    judul?: string;
    deskripsi?: string;
    logo?: string;
    logo_path?: string;
    gambar?: string;
    gambar_path?: string;
    website?: string;
    aktif?: boolean;
    [key: string]: unknown;
}

interface ProfilKawasan {
    id?: number;
    judul?: string;
    slug?: string;
    deskripsi?: string;
    luas_kawasan?: number | string;
    lokasi?: string;
    tahun_berdiri?: number | string;
    status?: boolean;
    gambar?: string;
    gambar_path?: string;
    [key: string]: unknown;
}

interface PetaKawasan {
    id?: number;
    judul?: string;
    gambar?: string;
    gambar_path?: string;
    keterangan?: string;
    deskripsi?: string;
    aktif?: boolean;
    [key: string]: unknown;
}

interface PeluangInvestasi {
    id: number;
    judul?: string;
    nama?: string;
    sektor?: string;
    deskripsi?: string;
    luas_lahan?: number | string;
    status?: string;
    gambar?: string;
    gambar_path?: string;
    urutan?: number;
    [key: string]: unknown;
}

interface Rute {
    id: number;
    nama?: string;
    judul?: string;
    asal?: string;
    tujuan?: string;
    jalur?: string;
    rute?: string;
    jarak?: number | string;
    distance?: string;
    waktu_tempuh?: string;
    waktu?: string;
    deskripsi?: string;
    [key: string]: unknown;
}

interface Berita {
    id: number;
    judul?: string;
    title?: string;
    slug?: string;
    excerpt?: string;
    ringkasan?: string;
    deskripsi?: string;
    gambar?: string;
    gambar_path?: string;
    thumbnail?: string;
    published_at?: string | null;
    created_at?: string;
    is_featured?: boolean;
    [key: string]: unknown;
}

interface Lowongan {
    id: number;
    judul?: string;
    slug?: string;
    departemen?: string;
    lokasi?: string;
    tipe_pekerjaan?: string;
    deskripsi?: string;
    tanggal_mulai?: string | null;
    tanggal_tutup?: string | null;
    status?: string;
    unggulan?: boolean;
    [key: string]: unknown;
}

/* =========================================================
   PROPS
   ========================================================= */

const props = withDefaults(
    defineProps<{
        companyProfile?: CompanyProfile | null;
        visi?: Visi | null;
        anakUsahas?: AnakUsaha[];
        profilKawasan?: ProfilKawasan | null;
        petaKawasan?: PetaKawasan | null;
        peluangInvestasi?: PeluangInvestasi[];
        rutes?: Rute[];
        beritas?: Berita[];
        lowongans?: Lowongan[];
    }>(),
    {
        companyProfile: null,
        visi: null,
        anakUsahas: () => [],
        profilKawasan: null,
        petaKawasan: null,
        peluangInvestasi: () => [],
        rutes: () => [],
        beritas: () => [],
        lowongans: () => [],
    },
);

/* =========================================================
   MOTION
   ========================================================= */

const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

type FadeElement = HTMLElement & {
    __fadeObserver?: IntersectionObserver;
};

const vFadeIn = {
    mounted(el: FadeElement) {
        if (prefersReducedMotion) {
            el.classList.add("fade-in-visible");
            return;
        }

        el.classList.add("fade-in");

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    el.classList.add("fade-in-visible");
                    observer.unobserve(el);
                });
            },
            {
                threshold: 0.12,
                rootMargin: "0px 0px -50px 0px",
            },
        );

        observer.observe(el);
        el.__fadeObserver = observer;
    },

    unmounted(el: FadeElement) {
        el.__fadeObserver?.disconnect();
        delete el.__fadeObserver;
    },
};

/* =========================================================
   COUNT UP
   ========================================================= */

type CountElement = HTMLElement & {
    __countObserver?: IntersectionObserver;
    __countTimeout?: ReturnType<typeof setTimeout>;
    __countFrame?: number;
};

function formatId(num: number, decimals = 0) {
    return num.toLocaleString("id-ID", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

const vCountUp = {
    mounted(el: CountElement, binding: { value?: Record<string, unknown> }) {
        const {
            target,
            decimals = 0,
            duration = 1400,
            delay = 0,
        } = binding.value || {};

        const numericTarget =
            typeof target === "number" ? target : Number(target ?? 0);

        const decimalPlaces = Number(decimals);

        el.textContent = formatId(0, decimalPlaces);

        if (prefersReducedMotion || Number.isNaN(numericTarget)) {
            el.textContent = formatId(
                Number.isNaN(numericTarget) ? 0 : numericTarget,
                decimalPlaces,
            );

            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    observer.unobserve(el);

                    el.__countTimeout = setTimeout(() => {
                        const start = performance.now();

                        const tick = (now: number) => {
                            const progress = Math.min(
                                (now - start) / Number(duration),
                                1,
                            );

                            const eased = 1 - Math.pow(1 - progress, 3);

                            el.textContent = formatId(
                                numericTarget * eased,
                                decimalPlaces,
                            );

                            if (progress < 1) {
                                el.__countFrame = requestAnimationFrame(tick);
                            } else {
                                el.textContent = formatId(
                                    numericTarget,
                                    decimalPlaces,
                                );
                            }
                        };

                        el.__countFrame = requestAnimationFrame(tick);
                    }, Number(delay));
                });
            },
            {
                threshold: 0.4,
                rootMargin: "0px 0px -40px 0px",
            },
        );

        observer.observe(el);
        el.__countObserver = observer;
    },

    unmounted(el: CountElement) {
        el.__countObserver?.disconnect();

        if (el.__countTimeout) {
            clearTimeout(el.__countTimeout);
        }

        if (el.__countFrame) {
            cancelAnimationFrame(el.__countFrame);
        }

        delete el.__countObserver;
        delete el.__countTimeout;
        delete el.__countFrame;
    },
};

/* =========================================================
   HELPERS
   ========================================================= */

function firstValue(
    object: Record<string, unknown> | null | undefined,
    keys: string[],
    fallback = "",
): string {
    if (!object) return fallback;

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

function numericValue(
    object: Record<string, unknown> | null | undefined,
    keys: string[],
    fallback = 0,
): number {
    if (!object) return fallback;

    for (const key of keys) {
        const raw = object[key];

        if (raw === null || raw === undefined || String(raw).trim() === "") {
            continue;
        }

        const value = Number(raw);

        if (!Number.isNaN(value)) {
            return value;
        }
    }

    return fallback;
}

function imageUrl(
    object: Record<string, unknown> | null | undefined,
    keys: string[],
): string {
    return firstValue(object, keys, "");
}

function truncate(text: string, length = 150) {
    if (!text) return "";

    return text.length > length ? `${text.substring(0, length).trim()}…` : text;
}

function formatDate(date?: string | null) {
    if (!date) return "";

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return date;
    }

    return parsed.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function formatNumber(value: number | string | undefined, decimals = 0) {
    const number = Number(value ?? 0);

    if (Number.isNaN(number)) return "0";

    return number.toLocaleString("id-ID", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

function jobTypeLabel(type?: string) {
    if (!type) return "Posisi tersedia";

    const labels: Record<string, string> = {
        full_time: "Full Time",
        part_time: "Part Time",
        contract: "Kontrak",
        internship: "Magang",
        freelance: "Freelance",
    };

    return labels[type] ?? type;
}

/* =========================================================
   COMPANY
   ========================================================= */

const companyName = computed(() =>
    firstValue(
        props.companyProfile,
        ["nama_perusahaan", "nama", "judul"],
        "PT Kawasan Industri Tanjung Buton",
    ),
);

const companyDescription = computed(() =>
    firstValue(
        props.companyProfile,
        ["deskripsi", "tentang"],
        "PT Kawasan Industri Tanjung Buton mengintegrasikan kawasan industri, pelabuhan, logistik, dan hilirisasi komoditas dalam satu ekosistem pertumbuhan yang terintegrasi.",
    ),
);

/* =========================================================
   VISION & MISSION
   ========================================================= */

const visionText = computed(() =>
    firstValue(props.visi, ["visi", "deskripsi", "judul", "title"]),
);

const missions = computed<Misi[]>(() => props.visi?.misis ?? []);

const hasVisionMission = computed(
    () =>
        Boolean(props.visi) &&
        Boolean(visionText.value || missions.value.length),
);

/* =========================================================
   AREA
   ========================================================= */

const kawasanName = computed(() =>
    firstValue(
        props.profilKawasan,
        ["judul"],
        "Kawasan Industri Tanjung Buton",
    ),
);

const kawasanDescription = computed(() =>
    firstValue(props.profilKawasan, ["deskripsi"]),
);

const kawasanArea = computed(() =>
    numericValue(props.profilKawasan, ["luas_kawasan"]),
);

const kawasanAreaDecimals = computed(() =>
    Number.isInteger(kawasanArea.value) ? 0 : 1,
);

const kawasanLocation = computed(() =>
    firstValue(props.profilKawasan, ["lokasi"]),
);

const kawasanYear = computed(() =>
    firstValue(props.profilKawasan, ["tahun_berdiri"]),
);

const kawasanImage = computed(() =>
    imageUrl(props.profilKawasan, ["gambar_path", "gambar"]),
);

/* =========================================================
   MAP
   ========================================================= */

const mapTitle = computed(() =>
    firstValue(
        props.petaKawasan,
        ["judul"],
        "Peta kawasan & tahapan pengembangan",
    ),
);

const mapImage = computed(() =>
    imageUrl(props.petaKawasan, ["gambar_path", "gambar"]),
);

const mapDescription = computed(() =>
    firstValue(props.petaKawasan, ["keterangan", "deskripsi"]),
);

/* =========================================================
   HERO STATS
   ========================================================= */

const stats = computed(() => {
    const items: Array<{
        target: number;
        decimals: number;
        suffix: string;
        label: string;
    }> = [];

    const anakUsahas = props.anakUsahas ?? [];
    const rutes = props.rutes ?? [];
    const beritas = props.beritas ?? [];

    if (kawasanArea.value > 0) {
        items.push({
            target: kawasanArea.value,
            decimals: kawasanAreaDecimals.value,
            suffix: "Ha",
            label: "Luas kawasan pengembangan",
        });
    }

    if (anakUsahas.length > 0) {
        items.push({
            target: anakUsahas.length,
            decimals: 0,
            suffix: "",
            label: "Entitas dalam ekosistem KITB",
        });
    }

    if (rutes.length > 0) {
        items.push({
            target: rutes.length,
            decimals: 0,
            suffix: "",
            label: "Rute pelayaran aktif",
        });
    }

    if (beritas.length > 0) {
        items.push({
            target: beritas.length,
            decimals: 0,
            suffix: "",
            label: "Informasi terbaru tersedia",
        });
    }

    return items.slice(0, 4);
});

/* =========================================================
   LAND DEVELOPMENT
   ========================================================= */

const landStats = computed(() => {
    if (kawasanArea.value <= 0) {
        return [];
    }

    return [
        {
            target: kawasanArea.value,
            decimals: kawasanAreaDecimals.value,
            unit: "Ha",
            label: "Total wilayah pengembangan KITB",
        },
    ];
});

/* =========================================================
   DEVELOPMENT DATA
   ========================================================= */

const developmentStages = [
    {
        number: "01",
        title: "Kawasan industri",
        desc: "Kavling industri, fasilitas pendukung, perdagangan & jasa, area perkantoran, serta fasilitas penunjang dalam satu estate layout terpadu.",
    },
    {
        number: "02",
        title: "Kawasan pelabuhan",
        desc: "Area migas, CPO, dry bulk, kontainer & pergudangan, serta fasilitas galangan kapal untuk mendukung arus logistik ekspor-impor.",
    },
    {
        number: "03",
        title: "Pelabuhan Tanjung Buton",
        desc: "Infrastruktur pelabuhan yang mendukung konektivitas kawasan dengan jaringan pelayaran dan jalur logistik regional.",
    },
];

/* =========================================================
   ROUTES
   ========================================================= */

const displayedRoutes = computed<Rute[]>(() => (props.rutes ?? []).slice(0, 6));

const hasLocationSection = computed(
    () => Boolean(kawasanLocation.value) || displayedRoutes.value.length > 0,
);

function routePath(r: Rute) {
    return firstValue(
        r,
        ["jalur", "rute", "deskripsi"],
        [firstValue(r, ["asal"]), firstValue(r, ["tujuan"])]
            .filter(Boolean)
            .join(" – "),
    );
}

function routeName(r: Rute, index: number) {
    return firstValue(r, ["nama", "judul"], `Rute ${index + 1}`);
}

/* =========================================================
   INVESTMENT
   ========================================================= */

function investmentTitle(investment: PeluangInvestasi) {
    return firstValue(investment, ["judul", "nama"], "Peluang investasi");
}

function investmentImage(investment: PeluangInvestasi) {
    return imageUrl(investment, ["gambar_path", "gambar"]);
}

function investmentDescription(investment: PeluangInvestasi) {
    return firstValue(investment, ["deskripsi"]);
}

/* =========================================================
   NEWS
   ========================================================= */

const latestNews = computed<Berita[]>(() => props.beritas ?? []);

function beritaTitle(berita: Berita) {
    return firstValue(berita, ["judul", "title"], "Berita KITB");
}

function beritaImage(berita: Berita) {
    return imageUrl(berita, ["gambar_path", "gambar", "thumbnail"]);
}

/* =========================================================
   SUBSIDIARIES
   ========================================================= */

function anakUsahaName(anak: AnakUsaha) {
    return firstValue(
        anak,
        ["nama_perusahaan", "nama", "judul"],
        "Anak Usaha KITB",
    );
}

function anakUsahaImage(anak: AnakUsaha) {
    return imageUrl(anak, ["logo_path", "logo", "gambar_path", "gambar"]);
}
</script>

<template>
    <Head :title="`${companyName} — Beyond Industry, Towards the Future`" />

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

                        Badan Usaha Milik Daerah · Kabupaten Siak
                    </div>

                    <h1
                        class="mb-6 max-w-4xl font-display font-bold leading-[1.06] tracking-[-0.025em] text-kitb-green-900 sm:mb-7"
                        style="font-size: clamp(2.25rem, 5.8vw, 5rem)"
                    >
                        Kawasan industri yang berdiri tepat di bibir
                        <span class="text-kitb-navy-900"> Selat Malaka. </span>
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
                            Ajukan kunjungan lahan
                            <ArrowRight class="h-4 w-4" />
                        </a>

                        <a
                            href="#kawasan"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-black/15 px-7 py-3.5 text-[15px] font-medium text-kitb-ink-900 transition-all duration-200 hover:-translate-y-0.5 hover:border-kitb-green-700/30 hover:bg-white/60 sm:w-auto"
                        >
                            Lihat kawasan
                            <ChevronRight class="h-4 w-4" />
                        </a>
                    </div>
                </div>

                <!-- Hero statistics -->
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

        <!-- =========================================================
     TENTANG KAMI
========================================================= -->
        <section
            id="tentang-kami"
            class="relative overflow-hidden bg-slate-50 py-20 sm:py-24 lg:py-28"
        >
            <!-- Decorative blobs -->
            <div
                class="pointer-events-none absolute -left-32 top-20 h-72 w-72 rounded-full bg-kitb-teal-100/50 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -right-32 bottom-0 h-80 w-80 rounded-full bg-slate-200/60 blur-3xl"
            />

            <div class="relative mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(320px,0.72fr)] lg:items-center lg:gap-16"
                >
                    <!-- =====================================================
                 LEFT — COMPANY PROFILE
            ====================================================== -->
                    <div>
                        <div class="mb-6 flex items-center gap-3">
                            <span class="h-px w-10 bg-kitb-teal-500" />

                            <p
                                class="text-[12px] font-semibold uppercase tracking-[0.16em] text-kitb-teal-600"
                            >
                                Tentang Kami
                            </p>
                        </div>

                        <h2
                            class="max-w-3xl font-display font-bold leading-[1.08] tracking-[-0.025em] text-slate-900"
                            style="font-size: clamp(2rem, 4vw, 3.4rem)"
                        >
                            Bukan sekadar penyedia lahan.
                        </h2>

                        <!-- Company Profile dari database -->
                        <p
                            v-if="companyDescription"
                            class="mt-6 max-w-2xl text-[15px] leading-[1.9] text-slate-600 sm:text-base"
                        >
                            {{ truncate(companyDescription, 360) }}
                        </p>

                        <!-- Nama perusahaan -->
                        <div
                            v-if="companyName"
                            class="mt-8 flex items-center gap-3"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-kitb-teal-50 text-kitb-teal-600"
                            >
                                <Building2 class="h-5 w-5" />
                            </div>

                            <div>
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Perusahaan
                                </p>

                                <p
                                    class="mt-0.5 font-display font-semibold text-slate-800"
                                >
                                    {{ companyName }}
                                </p>
                            </div>
                        </div>

                        <!-- Selengkapnya -->
                        <div class="mt-9">
                            <Link
                                href="/profil-perusahaan/tentang-kami"
                                class="group inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-800 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-kitb-teal-300 hover:text-kitb-teal-700 hover:shadow-md"
                            >
                                Selengkapnya

                                <ArrowRight
                                    class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                                />
                            </Link>
                        </div>
                    </div>

                    <!-- =====================================================
                 RIGHT — SUPPORTING CARDS
            ====================================================== -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        <!-- Smart Industrial Park -->
                        <div
                            v-fade-in
                            class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                        >
                            <div
                                class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-kitb-teal-50 text-kitb-teal-600"
                            >
                                <Factory class="h-5 w-5" />
                            </div>

                            <h3
                                class="font-display text-lg font-semibold text-slate-900"
                            >
                                Smart Industrial Park
                            </h3>

                            <p class="mt-2 text-sm leading-7 text-slate-500">
                                Kawasan industri yang dikembangkan dengan
                                pendekatan terintegrasi, efisien, dan
                                berorientasi pada kebutuhan industri masa depan.
                            </p>
                        </div>

                        <!-- Green Industrial Estate -->
                        <div
                            v-fade-in
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
                                Green Industrial Estate
                            </h3>

                            <p class="mt-2 text-sm leading-7 text-white/55">
                                Mendorong pertumbuhan industri yang selaras
                                dengan keberlanjutan lingkungan dan ekosistem
                                kawasan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             TENTANG
             ===================================================== -->

        <section
            id="tentang"
            class="relative overflow-hidden py-16 sm:py-24 md:py-32"
        >
            <div
                class="blob blob-organic-2 drift-b"
                aria-hidden="true"
                style="
                    width: 420px;
                    height: 420px;
                    top: -60px;
                    left: -200px;
                    background: radial-gradient(
                        circle at 40% 40%,
                        #7fa8e0,
                        transparent 70%
                    );
                    opacity: 0.35;
                "
            />

            <div
                class="relative mx-auto grid max-w-7xl grid-cols-1 gap-10 px-5 sm:px-6 md:grid-cols-12 md:gap-8 md:px-10"
            >
                <div class="md:col-span-4" v-fade-in>
                    <p
                        class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                    >
                        Tentang KITB
                    </p>

                    <h2
                        class="font-display font-bold leading-[1.08] tracking-[-0.02em] text-kitb-green-900"
                        style="font-size: clamp(1.8rem, 3.4vw, 2.8rem)"
                    >
                        Bukan sekadar penyedia lahan.
                    </h2>
                </div>

                <div
                    v-fade-in
                    class="space-y-6 text-[16px] leading-[1.85] text-kitb-ink-900/72 sm:text-[16.5px] md:col-span-7 md:col-start-6"
                >
                    <p>
                        {{ companyDescription }}
                    </p>

                    <p>
                        Mengusung moto
                        <span
                            class="font-display font-semibold text-kitb-green-800"
                        >
                            “Beyond Industry, Towards the Future,”
                        </span>
                        KITB dikembangkan sebagai ekosistem
                        <strong class="font-semibold text-kitb-green-800">
                            Smart &amp; Green Industrial Estate
                        </strong>
                        yang mendorong hilirisasi bernilai tambah tinggi,
                        efisiensi operasional, dan pertumbuhan ekonomi daerah.
                    </p>

                    <div class="grid grid-cols-1 gap-5 pt-4 sm:grid-cols-2">
                        <div
                            class="rounded-2xl border border-kitb-teal-500/15 bg-white/55 p-5 transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-lg hover:shadow-kitb-navy-900/5"
                        >
                            <div
                                class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-kitb-teal-500/10 text-kitb-teal-600"
                            >
                                <Sparkles class="h-5 w-5" />
                            </div>

                            <div
                                class="mb-1 font-display text-lg font-semibold text-kitb-green-800"
                            >
                                Smart Industrial Park
                            </div>

                            <div
                                class="text-[14px] leading-relaxed text-kitb-ink-900/55"
                            >
                                Digitalisasi dan otomasi untuk efisiensi
                                operasional tenant.
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-kitb-amber-500/15 bg-white/55 p-5 transition-all duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-lg hover:shadow-kitb-navy-900/5"
                        >
                            <div
                                class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-kitb-amber-500/10 text-kitb-amber-600"
                            >
                                <Globe2 class="h-5 w-5" />
                            </div>

                            <div
                                class="mb-1 font-display text-lg font-semibold text-kitb-green-800"
                            >
                                Green Industrial Estate
                            </div>

                            <div
                                class="text-[14px] leading-relaxed text-kitb-ink-900/55"
                            >
                                Efisiensi sumber daya dan pengelolaan kawasan
                                yang berorientasi keberlanjutan.
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
                        Visi
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
                        Misi
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
                                    firstValue(
                                        m,
                                        ["judul", "title"],
                                        `Misi ${i + 1}`,
                                    )
                                }}
                            </h3>

                            <p
                                v-if="
                                    firstValue(m, ['deskripsi', 'desc', 'isi'])
                                "
                                class="text-[15px] leading-[1.8] text-white/60"
                            >
                                {{
                                    firstValue(m, ["deskripsi", "desc", "isi"])
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
                    <!-- Location information -->
                    <div class="md:col-span-5" v-fade-in>
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            Keunggulan geografis
                        </p>

                        <h2
                            class="mb-6 font-display font-bold leading-[1.08] tracking-[-0.02em] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 3.4vw, 2.8rem)"
                        >
                            Menghadap langsung jalur pelayaran strategis.
                        </h2>

                        <p
                            class="mb-8 text-[16px] leading-[1.8] text-kitb-ink-900/70"
                        >
                            Tanjung Buton berada di kawasan yang memberikan
                            akses strategis terhadap jaringan perdagangan dan
                            logistik regional melalui Selat Malaka.
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
                                        Lokasi
                                    </div>

                                    <p class="text-[15px] text-kitb-ink-900/68">
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
                                        Tahun berdiri
                                    </div>

                                    <p class="text-[15px] text-kitb-ink-900/68">
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
                                        Konektivitas maritim
                                    </div>

                                    <p class="text-[15px] text-kitb-ink-900/68">
                                        Terhubung dengan jaringan pelayaran
                                        regional melalui kawasan pelabuhan
                                        Tanjung Buton.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Routes -->
                    <div
                        v-if="displayedRoutes.length"
                        class="min-w-0 md:col-span-6 md:col-start-7"
                        v-fade-in
                        style="transition-delay: 120ms"
                    >
                        <div
                            class="overflow-hidden rounded-2xl border border-black/5 bg-kitb-sand-100 shadow-sm"
                        >
                            <!-- Header -->
                            <div class="border-b border-black/5 px-5 py-4">
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <div>
                                        <div
                                            class="text-[11px] font-semibold uppercase tracking-[0.12em] text-kitb-teal-600"
                                        >
                                            Rute pelayaran
                                        </div>

                                        <div
                                            class="mt-1 font-display text-lg font-semibold text-kitb-green-900"
                                        >
                                            Akses menuju jalur utama
                                        </div>

                                        <p
                                            class="mt-1 text-[12px] text-kitb-ink-900/45"
                                        >
                                            Menampilkan hingga 6 rute terbaru.
                                        </p>
                                    </div>

                                    <Ship
                                        class="h-5 w-5 shrink-0 text-kitb-green-700"
                                    />
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[620px] text-[14px]">
                                    <thead>
                                        <tr class="bg-kitb-green-700 text-left">
                                            <th
                                                scope="col"
                                                class="px-4 py-3.5 text-[12px] font-medium text-white sm:px-5"
                                            >
                                                Rute
                                            </th>

                                            <th
                                                scope="col"
                                                class="px-4 py-3.5 text-[12px] font-medium text-white sm:px-5"
                                            >
                                                Jalur
                                            </th>

                                            <th
                                                scope="col"
                                                class="px-4 py-3.5 text-[12px] font-medium text-white sm:px-5"
                                            >
                                                Jarak
                                            </th>

                                            <th
                                                scope="col"
                                                class="px-4 py-3.5 text-[12px] font-medium text-white sm:px-5"
                                            >
                                                Waktu
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

                            <!-- Footer -->
                            <div
                                class="flex flex-col gap-3 border-t border-black/5 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <p
                                    class="text-[12px] leading-relaxed text-kitb-ink-900/45"
                                >
                                    Data menampilkan maksimal 6 rute pelayaran
                                    terbaru.
                                </p>

                                <Link
                                    href="/rute-pelayaran-lokasi"
                                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full border border-kitb-green-700/15 bg-white px-4 py-2.5 text-[12px] font-semibold text-kitb-green-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-kitb-green-700/25 hover:bg-kitb-green-700 hover:text-white"
                                >
                                    Lihat semua rute
                                    <ArrowRight class="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </div>

                        <p
                            class="mt-4 text-[12.5px] leading-relaxed text-kitb-ink-900/45"
                        >
                            Informasi rute ditampilkan berdasarkan data rute
                            aktif yang tersedia pada sistem KITB.
                        </p>
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
                            Profil kawasan
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
                                    Luas kawasan
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
                                    Tahun berdiri
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
                            class="group relative overflow-hidden rounded-[1.75rem] border border-black/5 bg-white shadow-xl shadow-kitb-navy-900/8"
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
                                    <MapPin class="h-4 w-4" />
                                    {{ kawasanLocation }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Land statistics -->
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

                <!-- Development -->
                <div
                    class="mt-16 border-t border-black/10 pt-12 md:mt-20 md:pt-16"
                >
                    <div class="mb-10 max-w-2xl" v-fade-in>
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            Pengembangan kawasan
                        </p>

                        <h3
                            class="font-display font-bold leading-[1.1] text-kitb-green-900"
                            style="font-size: clamp(1.6rem, 3vw, 2.4rem)"
                        >
                            Infrastruktur dibangun sebagai satu ekosistem.
                        </h3>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
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

                <!-- Master plan -->
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
                                Master plan KITB
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
                                    Total luas kawasan
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
                                    :aria-label="`Buka ${mapTitle} dalam ukuran penuh`"
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
                                        class="pointer-events-none absolute inset-0 flex items-end justify-center bg-gradient-to-t from-kitb-navy-900/35 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                    >
                                        <span
                                            class="mb-5 inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-medium text-kitb-navy-900 shadow-lg"
                                        >
                                            <ExternalLink class="h-3.5 w-3.5" />
                                            Buka ukuran penuh
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <p
                                class="mt-3 text-[12.5px] leading-relaxed text-kitb-ink-900/45"
                            >
                                Ketuk atau klik peta untuk melihat gambar dalam
                                ukuran penuh.
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
            v-if="(props.anakUsahas ?? []).length"
            id="anak-usaha"
            class="relative overflow-hidden py-16 sm:py-24 md:py-32"
        >
            <div class="mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    class="mb-12 flex flex-col justify-between gap-6 md:mb-16 md:flex-row md:items-end"
                    v-fade-in
                >
                    <div class="max-w-2xl">
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            Anak usaha
                        </p>

                        <h2
                            class="font-display font-bold leading-[1.08] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 3.5vw, 2.8rem)"
                        >
                            Bagian dari ekosistem KITB.
                        </h2>
                    </div>

                    <Building2
                        class="hidden h-10 w-10 text-kitb-green-700/20 md:block"
                    />
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(anak, index) in anakUsahas"
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
                            v-if="anak.deskripsi"
                            class="mb-5 text-[14px] leading-[1.75] text-kitb-ink-900/60"
                        >
                            {{ truncate(anak.deskripsi, 130) }}
                        </p>

                        <a
                            v-if="anak.website"
                            :href="anak.website"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 text-[13px] font-semibold text-kitb-green-700 transition-colors hover:text-kitb-navy-900"
                        >
                            Kunjungi website
                            <ExternalLink class="h-3.5 w-3.5" />
                        </a>
                    </article>
                </div>
            </div>
        </section>

        <!-- =====================================================
             INVESTASI
             ===================================================== -->

        <section
            v-if="(props.peluangInvestasi ?? []).length"
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
                    class="mb-12 flex flex-col justify-between gap-6 md:mb-16 md:flex-row md:items-end"
                    v-fade-in
                >
                    <div class="max-w-2xl">
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-300"
                        >
                            Peluang investasi
                        </p>

                        <h2
                            class="font-display font-bold leading-[1.08] text-white"
                            style="font-size: clamp(1.8rem, 3.5vw, 2.8rem)"
                        >
                            Ruang untuk tumbuh bersama.
                        </h2>

                        <p
                            class="mt-5 max-w-xl text-[15px] leading-[1.8] text-white/55"
                        >
                            Temukan peluang investasi yang tersedia di dalam
                            ekosistem kawasan industri dan maritim KITB.
                        </p>
                    </div>

                    <TrendingUp
                        class="hidden h-10 w-10 text-kitb-teal-300/30 md:block"
                    />
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="(investment, index) in peluangInvestasi"
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
                                v-if="investment.sektor"
                                class="mb-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-kitb-teal-300"
                            >
                                {{ investment.sektor }}
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
                                v-if="
                                    investment.luas_lahan !== null &&
                                    investment.luas_lahan !== undefined &&
                                    investment.luas_lahan !== ''
                                "
                                class="flex items-center justify-between border-t border-white/10 pt-4"
                            >
                                <span class="text-[12px] text-white/40">
                                    Luas lahan
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
            v-if="(props.beritas ?? []).length"
            id="berita"
            class="relative overflow-hidden py-16 sm:py-24 md:py-32"
        >
            <div class="mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    class="mb-12 flex flex-col justify-between gap-6 md:mb-14 md:flex-row md:items-end"
                    v-fade-in
                >
                    <div class="max-w-2xl">
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            Pusat informasi
                        </p>

                        <h2
                            class="font-display font-bold leading-[1.08] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 3.5vw, 2.8rem)"
                        >
                            Berita terbaru dari KITB.
                        </h2>

                        <p
                            class="mt-5 max-w-xl text-[15px] leading-[1.8] text-kitb-ink-900/60"
                        >
                            Ikuti informasi dan perkembangan terbaru seputar
                            Kawasan Industri Tanjung Buton.
                        </p>
                    </div>

                    <Link
                        href="/berita"
                        class="inline-flex shrink-0 items-center gap-2 text-sm font-semibold text-kitb-green-700 transition-colors hover:text-kitb-navy-900"
                    >
                        Lihat semua berita
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <article
                        v-for="(berita, index) in beritas"
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
                            class="block"
                        >
                            <div
                                v-if="
                                    imageUrl(berita, [
                                        'gambar',
                                        'gambar_path',
                                        'thumbnail',
                                    ])
                                "
                                class="aspect-[16/9] overflow-hidden bg-kitb-sand-100"
                            >
                                <img
                                    :src="
                                        imageUrl(berita, [
                                            'gambar',
                                            'gambar_path',
                                            'thumbnail',
                                        ])
                                    "
                                    :alt="
                                        firstValue(
                                            berita,
                                            ['judul', 'title'],
                                            'Berita KITB',
                                        )
                                    "
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
                                    {{
                                        firstValue(
                                            berita,
                                            ["judul", "title"],
                                            "Berita KITB",
                                        )
                                    }}
                                </h3>

                                <p
                                    v-if="
                                        firstValue(berita, [
                                            'excerpt',
                                            'ringkasan',
                                            'deskripsi',
                                        ])
                                    "
                                    class="text-[14px] leading-[1.75] text-kitb-ink-900/60"
                                >
                                    {{
                                        truncate(
                                            firstValue(berita, [
                                                "excerpt",
                                                "ringkasan",
                                                "deskripsi",
                                            ]),
                                            135,
                                        )
                                    }}
                                </p>

                                <div
                                    class="mt-5 inline-flex items-center gap-2 text-[13px] font-semibold text-kitb-green-700 transition-colors group-hover:text-kitb-navy-900"
                                >
                                    Baca selengkapnya
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
                        Lihat semua berita
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- =====================================================
             KARIER
             ===================================================== -->

        <section
            v-if="(props.lowongans ?? []).length"
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
                    class="mb-12 flex flex-col justify-between gap-6 md:mb-14 md:flex-row md:items-end"
                    v-fade-in
                >
                    <div class="max-w-2xl">
                        <p
                            class="mb-4 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-600"
                        >
                            Karier
                        </p>

                        <h2
                            class="font-display font-bold leading-[1.08] text-kitb-green-900"
                            style="font-size: clamp(1.8rem, 3.5vw, 2.8rem)"
                        >
                            Bangun masa depan bersama KITB.
                        </h2>

                        <p
                            class="mt-5 max-w-xl text-[15px] leading-[1.8] text-kitb-ink-900/60"
                        >
                            Bergabung dengan tim yang sedang membangun ekosistem
                            industri dan maritim masa depan.
                        </p>
                    </div>

                    <Link
                        href="/karier"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-kitb-green-700 transition-colors hover:text-kitb-navy-900"
                    >
                        Lihat semua lowongan
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div class="grid gap-4">
                    <Link
                        v-for="(job, index) in lowongans"
                        :key="job.id"
                        :href="job.slug ? `/karier/${job.slug}` : '/karier'"
                        v-fade-in
                        :style="{
                            transitionDelay: `${index * 80}ms`,
                        }"
                        class="group grid gap-5 rounded-2xl border border-black/5 bg-white/70 p-5 transition-all duration-300 hover:-translate-y-0.5 hover:bg-white hover:shadow-xl hover:shadow-kitb-navy-900/5 sm:p-6 md:grid-cols-[1fr_auto] md:items-center"
                    >
                        <div class="min-w-0">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span
                                    v-if="job.unggulan"
                                    class="inline-flex items-center gap-1 rounded-full bg-kitb-amber-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-kitb-amber-700"
                                >
                                    Unggulan
                                </span>

                                <span
                                    class="rounded-full bg-kitb-green-700/10 px-2.5 py-1 text-[10px] font-medium text-kitb-green-800"
                                >
                                    {{ jobTypeLabel(job.tipe_pekerjaan) }}
                                </span>
                            </div>

                            <h3
                                class="mb-2 font-display text-lg font-semibold text-kitb-green-900 transition-colors group-hover:text-kitb-navy-900 sm:text-xl"
                            >
                                {{
                                    firstValue(
                                        job,
                                        ["judul"],
                                        "Posisi tersedia",
                                    )
                                }}
                            </h3>

                            <div
                                v-if="
                                    job.departemen ||
                                    job.lokasi ||
                                    job.tanggal_tutup
                                "
                                class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[12.5px] text-kitb-ink-900/50"
                            >
                                <span
                                    v-if="job.departemen"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <BriefcaseBusiness class="h-3.5 w-3.5" />
                                    {{ job.departemen }}
                                </span>

                                <span
                                    v-if="job.lokasi"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <MapPin class="h-3.5 w-3.5" />
                                    {{ job.lokasi }}
                                </span>

                                <span
                                    v-if="job.tanggal_tutup"
                                    class="inline-flex items-center gap-1.5"
                                >
                                    <Clock3 class="h-3.5 w-3.5" />
                                    Tutup
                                    {{ formatDate(job.tanggal_tutup) }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-black/10 text-kitb-green-700 transition-all duration-300 group-hover:translate-x-1 group-hover:bg-kitb-green-700 group-hover:text-white"
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
                            class="relative mb-5 text-[13px] font-semibold uppercase tracking-[0.14em] text-kitb-teal-300"
                        >
                            Naik ke panggung industri global
                        </p>

                        <h2
                            class="relative mx-auto max-w-3xl font-display font-bold leading-[1.12] text-white"
                            style="font-size: clamp(1.7rem, 3.8vw, 3rem)"
                        >
                            Bersama para mitra dan investor, kami siap membangun
                            masa depan industri Indonesia.
                        </h2>

                        <p
                            class="mx-auto mt-5 max-w-xl text-[14px] leading-[1.8] text-white/60 sm:text-[15px]"
                        >
                            Mari diskusikan kebutuhan lahan, peluang investasi,
                            dan potensi kolaborasi di Kawasan Industri Tanjung
                            Buton.
                        </p>

                        <div class="relative mt-8 sm:mt-10">
                            <a
                                :href="visitUrl"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-white px-8 py-3.5 text-[15px] font-medium text-[#163a70] shadow-lg shadow-black/10 transition-all duration-200 hover:-translate-y-0.5 hover:bg-white/95 sm:w-auto"
                            >
                                Mulai diskusi investasi
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

.kitb-landing input,
.kitb-landing textarea {
    font-size: 16px;
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

/* =========================================================
   BLOB ANIMATION
   ========================================================= */

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

/* =========================================================
   HERO
   ========================================================= */

.hero-blob-field {
    background: transparent;
}

/* =========================================================
   STAT NUMBER
   ========================================================= */

.stat-number {
    font-family: "Poppins", sans-serif;
    font-weight: 700;
    letter-spacing: -0.025em;
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
   TABLET
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

/* =========================================================
   MOBILE
   ========================================================= */

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

/* =========================================================
   REDUCED MOTION
   ========================================================= */

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
