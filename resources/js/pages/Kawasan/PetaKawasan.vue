<script setup lang="ts">
import "leaflet/dist/leaflet.css";

import type {
    GeoJSON as LeafletGeoJSON,
    Map as LeafletMap,
    Marker,
} from "leaflet";

import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";

import { Head, Link } from "@inertiajs/vue3";

import {
    CalendarDays,
    ChevronRight,
    Home,
    Landmark,
    Maximize2,
    MapPin,
    Navigation,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";

defineOptions({
    layout: PublicLayout,
});

/* ============================================================
   TYPES & PROPS
============================================================= */

interface KawasanPeta {
    id: number;
    judul: string;
    slug: string;
    lokasi: string | null;
    luas_kawasan: string | number | null;
    tahun_berdiri: number | null;
    gambar: string | null;
    latitude: string | number | null;
    longitude: string | number | null;
    // GeoJSON (Polygon / MultiPolygon / Feature) — opsional
    batas_kawasan: Record<string, unknown> | null;
}

interface Point extends Omit<KawasanPeta, "latitude" | "longitude"> {
    latitude: number;
    longitude: number;
}

const props = defineProps<{
    kawasans: KawasanPeta[];
    selectedSlug?: string | null;
}>();

/* ============================================================
   DATA
============================================================= */

// Hanya kawasan dengan koordinat valid yang bisa ditampilkan di peta
const points = computed<Point[]>(() => {
    const list = Array.isArray(props.kawasans) ? props.kawasans : [];

    return list
        .map((item) => ({
            ...item,
            latitude: Number(item.latitude),
            longitude: Number(item.longitude),
        }))
        .filter(
            (item) =>
                item.latitude !== null &&
                Number.isFinite(item.latitude) &&
                Number.isFinite(item.longitude) &&
                Math.abs(item.latitude) <= 90 &&
                Math.abs(item.longitude) <= 180,
        ) as Point[];
});

const selectedId = ref<number | null>(null);

const selected = computed<Point | null>(() => {
    return points.value.find((item) => item.id === selectedId.value) ?? null;
});

/* ============================================================
   HELPERS
============================================================= */

const formatLuas = (luas: string | number | null): string | null => {
    if (luas === null || luas === undefined || luas === "") {
        return null;
    }

    const value = Number(luas);

    if (Number.isNaN(value)) {
        return String(luas);
    }

    return new Intl.NumberFormat("id-ID", {
        maximumFractionDigits: 2,
    }).format(value);
};

const formatCoordinate = (item: Point): string => {
    return `${item.latitude.toFixed(5)}, ${item.longitude.toFixed(5)}`;
};

const directionsUrl = (item: Point): string => {
    return `https://www.google.com/maps/dir/?api=1&destination=${item.latitude},${item.longitude}`;
};

/* ============================================================
   LEAFLET
============================================================= */

const mapElement = ref<HTMLElement | null>(null);
const isMapReady = ref(false);
const mapError = ref(false);

// Daftar kawasan tampil setelah peta siap (atau jika peta gagal dimuat)
const isListReady = computed(() => isMapReady.value || mapError.value);

let L: typeof import("leaflet") | null = null;
let map: LeafletMap | null = null;

const markers = new Map<number, Marker>();
const boundaries = new Map<number, LeafletGeoJSON>();

let prefersReducedMotion = false;

const pinHtml = (active: boolean): string => {
    return `
        <span class="kitb-pin${active ? " is-active" : ""}">
            <svg viewBox="0 0 24 24" width="100%" height="100%" aria-hidden="true">
                <path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12Z"
                      fill="currentColor" stroke="white" stroke-width="1.5"/>
                <circle cx="12" cy="10" r="2.6" fill="white"/>
            </svg>
        </span>
    `;
};

const createIcon = (active: boolean) => {
    return L!.divIcon({
        className: "kitb-pin-wrapper",
        html: pinHtml(active),
        iconSize: [38, 38],
        iconAnchor: [19, 36],
    });
};

const boundaryStyle = (active: boolean) => ({
    color: active ? "#2563eb" : "#64748b",
    weight: active ? 3 : 2,
    fillColor: active ? "#3b82f6" : "#94a3b8",
    fillOpacity: active ? 0.22 : 0.12,
});

const refreshStyles = () => {
    markers.forEach((marker, id) => {
        const active = id === selectedId.value;

        marker.setIcon(createIcon(active));
        marker.setZIndexOffset(active ? 1000 : 0);
    });

    boundaries.forEach((layer, id) => {
        layer.setStyle(boundaryStyle(id === selectedId.value));
    });
};

const selectKawasan = (item: Point, fly = true) => {
    selectedId.value = item.id;

    refreshStyles();

    if (!map || !fly) {
        return;
    }

    const boundary = boundaries.get(item.id);
    const animate = !prefersReducedMotion;

    if (boundary) {
        map.flyToBounds(boundary.getBounds(), {
            padding: [48, 48],
            animate,
            duration: 1,
        });

        return;
    }

    map.flyTo([item.latitude, item.longitude], Math.max(map.getZoom(), 14), {
        animate,
        duration: 1,
    });
};

const initMap = async () => {
    if (!mapElement.value || !points.value.length) {
        return;
    }

    // Import dinamis agar aman untuk SSR (Leaflet butuh `window`)
    const leaflet = await import("leaflet");

    L = (leaflet as any).default ?? leaflet;

    if (!L || !mapElement.value) {
        return;
    }

    map = L.map(mapElement.value, {
        zoomControl: true,
        scrollWheelZoom: false, // aktif setelah peta diklik, agar scroll halaman tidak "terjebak"
        attributionControl: true,
    });

    const streets = L.tileLayer(
        "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            maxZoom: 19,
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        },
    ).addTo(map);

    const satellite = L.tileLayer(
        "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
        {
            maxZoom: 19,
            attribution: "Tiles &copy; Esri",
        },
    );

    L.control
        .layers({ Peta: streets, Satelit: satellite }, undefined, {
            position: "topright",
        })
        .addTo(map);

    map.on("click", () => map?.scrollWheelZoom.enable());
    map.on("mouseout", () => map?.scrollWheelZoom.disable());

    points.value.forEach((item) => {
        // Batas kawasan (GeoJSON), jika ada
        if (item.batas_kawasan) {
            try {
                const layer = L!
                    .geoJSON(item.batas_kawasan as any, {
                        style: () => boundaryStyle(false),
                    })
                    .addTo(map!);

                boundaries.set(item.id, layer);
            } catch {
                // GeoJSON tidak valid: abaikan, marker tetap tampil
            }
        }

        const marker = L!
            .marker([item.latitude, item.longitude], {
                icon: createIcon(false),
                title: item.judul,
                alt: item.judul,
                keyboard: true,
            })
            .addTo(map!);

        marker.bindTooltip(item.judul, {
            direction: "top",
            offset: [0, -34],
        });

        marker.on("click", () => selectKawasan(item));

        markers.set(item.id, marker);
    });

    // Tampilan awal
    const target = props.selectedSlug
        ? points.value.find((item) => item.slug === props.selectedSlug)
        : null;

    if (points.value.length === 1) {
        const only = points.value[0];

        map.setView([only.latitude, only.longitude], 14);
        selectKawasan(only, false);
    } else {
        map.fitBounds(
            L.latLngBounds(
                points.value.map(
                    (item) =>
                        [item.latitude, item.longitude] as [number, number],
                ),
            ),
            { padding: [48, 48] },
        );
    }

    if (target) {
        selectKawasan(target);
    }

    isMapReady.value = true;

    await nextTick();
    map.invalidateSize();
};

/* ============================================================
   REVEAL / FADE IN
============================================================= */

let revealObserver: IntersectionObserver | null = null;

const setupReveal = () => {
    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (prefersReducedMotion) {
        elements.forEach((element) => element.classList.add("is-visible"));
        return;
    }

    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add("is-visible");
                revealObserver?.unobserve(entry.target);
            });
        },
        { threshold: 0.08, rootMargin: "0px 0px -40px 0px" },
    );

    elements.forEach((element) => revealObserver?.observe(element));
};

onMounted(() => {
    prefersReducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;

    setupReveal();

    initMap().catch(() => {
        mapError.value = true;
    });
});

onBeforeUnmount(() => {
    revealObserver?.disconnect();

    map?.remove();
    map = null;

    markers.clear();
    boundaries.clear();
});
</script>

<template>
    <Head>
        <title>Peta Kawasan — Kawasan Industri Tanjung Buton</title>

        <meta
            name="description"
            content="Peta lokasi Kawasan Industri Tanjung Buton (KITB) lengkap dengan titik koordinat dan batas kawasan."
        />

        <meta name="robots" content="index, follow" />

        <link
            rel="canonical"
            href="https://tanjungbuton-industrial.co.id/kawasan/peta-kawasan"
        />

        <meta property="og:title" content="Peta Kawasan | KITB" />
        <meta
            property="og:description"
            content="Peta lokasi Kawasan Industri Tanjung Buton (KITB)."
        />
        <meta
            property="og:url"
            content="https://tanjungbuton-industrial.co.id/kawasan/peta-kawasan"
        />
        <meta property="og:type" content="website" />
    </Head>

    <main
        class="relative min-h-screen overflow-hidden bg-slate-50/50 dark:bg-slate-950"
    >
        <div
            aria-hidden="true"
            class="pointer-events-none absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-900/20"
        />

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->

            <nav
                data-reveal
                style="--d: 0ms"
                aria-label="Breadcrumb"
                class="mb-6 flex flex-wrap items-center gap-2 text-sm"
            >
                <Link
                    href="/"
                    class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                >
                    <Home class="size-4 shrink-0" aria-hidden="true" />
                    <span>Beranda</span>
                </Link>

                <ChevronRight
                    class="size-4 shrink-0 text-slate-400"
                    aria-hidden="true"
                />

                <Link
                    href="/kawasan/profil-kawasan"
                    class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                >
                    <Landmark class="size-4 shrink-0" aria-hidden="true" />
                    <span>Profil Kawasan</span>
                </Link>

                <ChevronRight
                    class="size-4 shrink-0 text-slate-400"
                    aria-hidden="true"
                />

                <span
                    aria-current="page"
                    class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                >
                    <MapPin
                        class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                        aria-hidden="true"
                    />
                    <span>Peta Kawasan</span>
                </span>
            </nav>

            <!-- Heading -->

            <section data-reveal style="--d: 80ms" class="mb-8 max-w-3xl">
                <div
                    class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400"
                >
                    <MapPin class="size-4" aria-hidden="true" />
                    Peta Interaktif
                </div>

                <h1
                    class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                >
                    Peta Kawasan
                </h1>

                <p
                    class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    Temukan lokasi kawasan industri melalui peta interaktif.
                    Pilih kawasan pada daftar atau klik penanda di peta untuk
                    melihat informasinya.
                </p>
            </section>

            <!-- Empty state -->

            <section
                v-if="!points.length"
                data-reveal
                style="--d: 140ms"
                class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12 dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                >
                    <MapPin class="size-8" aria-hidden="true" />
                </div>

                <h2
                    class="mt-5 text-xl font-bold text-slate-900 dark:text-white"
                >
                    Data peta belum tersedia
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    Titik koordinat kawasan belum tersedia atau sedang
                    diperbarui. Silakan kembali lagi nanti.
                </p>

                <Link
                    href="/kawasan/profil-kawasan"
                    class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    <Landmark class="size-4" aria-hidden="true" />
                    Lihat Profil Kawasan
                </Link>
            </section>

            <!-- Map + list -->

            <section
                v-else
                data-reveal
                style="--d: 140ms"
                class="grid gap-6 lg:grid-cols-[360px_1fr]"
            >
                <!-- Daftar kawasan -->

                <aside
                    v-if="!isListReady"
                    aria-label="Memuat daftar kawasan"
                    aria-busy="true"
                    class="order-2 rounded-3xl border border-slate-200/80 bg-white p-3 shadow-sm lg:order-1 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="skeleton-shimmer mx-3 mb-3 mt-2 h-3 w-20 rounded bg-slate-200 dark:bg-slate-800"
                    />

                    <div class="space-y-2">
                        <div
                            v-for="index in 3"
                            :key="index"
                            class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800"
                        >
                            <div
                                class="skeleton-shimmer h-4 w-2/3 rounded bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="skeleton-shimmer mt-3 h-3 w-full rounded bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="skeleton-shimmer mt-2 h-3 w-1/2 rounded bg-slate-200 dark:bg-slate-800"
                            />
                        </div>
                    </div>
                </aside>

                <aside
                    v-else
                    aria-label="Daftar kawasan"
                    class="fade-in order-2 max-h-[640px] overflow-y-auto rounded-3xl border border-slate-200/80 bg-white p-3 shadow-sm lg:order-1 dark:border-slate-800 dark:bg-slate-900"
                >
                    <p
                        class="px-3 pb-2 pt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500"
                    >
                        {{ points.length }} Kawasan
                    </p>

                    <ul class="space-y-2">
                        <li v-for="item in points" :key="item.id">
                            <button
                                type="button"
                                :aria-pressed="selectedId === item.id"
                                class="w-full rounded-2xl border p-4 text-left transition focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="
                                    selectedId === item.id
                                        ? 'border-blue-200 bg-blue-50/70 dark:border-blue-900/60 dark:bg-blue-950/30'
                                        : 'border-slate-200 bg-white hover:border-blue-200 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800/60'
                                "
                                @click="selectKawasan(item)"
                            >
                                <span
                                    class="flex items-start justify-between gap-3"
                                >
                                    <span>
                                        <span
                                            class="block text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ item.judul }}
                                        </span>

                                        <span
                                            v-if="item.lokasi"
                                            class="mt-1 line-clamp-2 block text-xs leading-5 text-slate-500 dark:text-slate-400"
                                        >
                                            {{ item.lokasi }}
                                        </span>
                                    </span>

                                    <MapPin
                                        class="mt-0.5 size-4 shrink-0"
                                        :class="
                                            selectedId === item.id
                                                ? 'text-blue-600 dark:text-blue-400'
                                                : 'text-slate-400'
                                        "
                                        aria-hidden="true"
                                    />
                                </span>
                            </button>

                            <!-- Detail kawasan terpilih -->

                            <div
                                v-if="selectedId === item.id"
                                class="mx-1 mt-2 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60"
                            >
                                <dl class="space-y-3 text-sm">
                                    <div
                                        v-if="formatLuas(item.luas_kawasan)"
                                        class="flex items-center gap-3"
                                    >
                                        <Maximize2
                                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                            aria-hidden="true"
                                        />
                                        <div>
                                            <dt
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Luas Kawasan
                                            </dt>
                                            <dd
                                                class="font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{
                                                    formatLuas(
                                                        item.luas_kawasan,
                                                    )
                                                }}
                                                Ha
                                            </dd>
                                        </div>
                                    </div>

                                    <div
                                        v-if="item.tahun_berdiri"
                                        class="flex items-center gap-3"
                                    >
                                        <CalendarDays
                                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                            aria-hidden="true"
                                        />
                                        <div>
                                            <dt
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Tahun Berdiri
                                            </dt>
                                            <dd
                                                class="font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{ item.tahun_berdiri }}
                                            </dd>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <MapPin
                                            class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                            aria-hidden="true"
                                        />
                                        <div>
                                            <dt
                                                class="text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Koordinat
                                            </dt>
                                            <dd
                                                class="font-mono text-xs font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{ formatCoordinate(item) }}
                                            </dd>
                                        </div>
                                    </div>
                                </dl>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a
                                        :href="directionsUrl(item)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                    >
                                        <Navigation
                                            class="size-4"
                                            aria-hidden="true"
                                        />
                                        Petunjuk Arah
                                        <span class="sr-only">
                                            (buka di tab baru)
                                        </span>
                                    </a>

                                    <Link
                                        href="/kawasan/profil-kawasan"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:ring-offset-slate-900"
                                    >
                                        Profil
                                        <ChevronRight
                                            class="size-4"
                                            aria-hidden="true"
                                        />
                                    </Link>
                                </div>
                            </div>
                        </li>
                    </ul>
                </aside>

                <!-- Peta -->

                <div
                    class="relative isolate z-0 order-1 overflow-hidden rounded-3xl border border-slate-200/80 bg-slate-100 shadow-sm lg:order-2 dark:border-slate-800 dark:bg-slate-800"
                >
                    <div
                        ref="mapElement"
                        class="h-[420px] w-full sm:h-[520px] lg:h-[640px]"
                        role="application"
                        aria-label="Peta lokasi kawasan industri"
                    />

                    <Transition
                        leave-active-class="transition-opacity duration-500"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div
                            v-if="!isMapReady"
                            role="status"
                            class="absolute inset-0 z-[500] overflow-hidden bg-slate-100 dark:bg-slate-800"
                        >
                            <template v-if="!mapError">
                                <div
                                    class="skeleton-shimmer absolute inset-0 bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="absolute inset-0 flex flex-col items-center justify-center gap-3"
                                >
                                    <div
                                        class="flex size-14 animate-pulse items-center justify-center rounded-2xl bg-white/80 text-blue-500 shadow-sm dark:bg-slate-900/80 dark:text-blue-400"
                                    >
                                        <MapPin
                                            class="size-7"
                                            aria-hidden="true"
                                        />
                                    </div>

                                    <p
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Memuat peta…
                                    </p>
                                </div>
                            </template>

                            <div
                                v-else
                                class="flex h-full flex-col items-center justify-center gap-2 p-6 text-center"
                            >
                                <MapPin
                                    class="size-8 text-slate-400"
                                    aria-hidden="true"
                                />

                                <p
                                    class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Peta gagal dimuat
                                </p>

                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Periksa koneksi internet Anda, lalu muat
                                    ulang halaman.
                                </p>
                            </div>
                        </div>
                    </Transition>
                </div>
            </section>
        </div>
    </main>
</template>

<!-- Tidak scoped: elemen penanda dibuat oleh Leaflet, bukan oleh Vue -->
<style>
.kitb-pin-wrapper {
    background: transparent;
    border: 0;
}

.kitb-pin {
    display: block;
    width: 38px;
    height: 38px;
    color: #475569;
    filter: drop-shadow(0 4px 6px rgb(15 23 42 / 0.35));
    transition:
        transform 200ms ease,
        color 200ms ease;
    transform-origin: 50% 90%;
}

.kitb-pin.is-active {
    color: #2563eb;
    transform: scale(1.2);
}

.leaflet-container {
    font-family: inherit;
}

@media (prefers-reduced-motion: reduce) {
    .kitb-pin {
        transition: none;
    }
}
</style>

<style scoped>
/* ============================================================
   REVEAL
============================================================= */

[data-reveal] {
    opacity: 0;
    transform: translateY(16px);
    transition:
        opacity 700ms ease,
        transform 700ms ease;
    transition-delay: var(--d, 0ms);
}

[data-reveal].is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* ============================================================
   FADE IN (daftar kawasan setelah skeleton)
============================================================= */

.fade-in {
    animation: fade-in 450ms ease both;
}

@keyframes fade-in {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@media (prefers-reduced-motion: reduce) {
    [data-reveal] {
        opacity: 1;
        transform: none;
        transition: none;
    }

    .fade-in {
        animation: none;
    }
}

/* ============================================================
   SKELETON SHIMMER
============================================================= */

.skeleton-shimmer {
    position: relative;
    overflow: hidden;
}

.skeleton-shimmer::after {
    position: absolute;
    inset: 0;
    content: "";
    transform: translateX(-100%);
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.55),
        transparent
    );
    animation: skeleton-shimmer 1.35s infinite;
}

@keyframes skeleton-shimmer {
    100% {
        transform: translateX(100%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .skeleton-shimmer::after {
        animation: none;
    }
}
</style>
