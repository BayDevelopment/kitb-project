<script setup lang="ts">
import { computed, type Component } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowUpRight,
    Building,
    Building2,
    BriefcaseBusiness,
    CalendarCheck,
    CalendarDays,
    Construction,
    ExternalLink,
    FileStack,
    Landmark,
    LayoutGrid,
    MapPinned,
    Network,
    Route,
    Target,
    Users,
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

interface GroupItem {
    key: string;
    label: string;
    count: number;
    href: string;
}

interface Group {
    title: string;
    items: GroupItem[];
}

interface Kunjungan {
    id: number;
    nama: string | null;
    instansi: string | null;
    tanggal_kunjungan: string | null;
    jumlah_peserta: number | null;
    created_at: string | null;
}

interface Zone {
    label: string;
    luas: number;
    warna: string;
}

interface Props {
    summary: {
        kunjungan_total: number;
        kunjungan_bulan_ini: number;
        total_konten: number;
        pengguna: number;
        peluang_investasi: number;
    };
    groups: Group[];
    kunjunganTerbaru: Kunjungan[];
    zones: Zone[];
}

const props = defineProps<Props>();

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const iconMap: Record<string, Component> = {
    tentang: Building2,
    visi_misi: Target,
    struktur: Network,
    anak_usaha: Users,
    profil_kawasan: Landmark,
    infrastruktur: Construction,
    fasilitas: Building,
    peta: MapPinned,
    peluang: BriefcaseBusiness,
    ease: Landmark,
    kunjungan: MapPinned,
    rute: Route,
};

const groupAccent = [
    "bg-blue-50 text-blue-600 ring-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:ring-blue-900/40",
    "bg-sky-50 text-sky-600 ring-sky-100 dark:bg-sky-950/40 dark:text-sky-400 dark:ring-sky-900/40",
    "bg-indigo-50 text-indigo-600 ring-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-400 dark:ring-indigo-900/40",
];

const formatNumber = (value: number) => value.toLocaleString("id-ID");

const formatDate = (value: string | null) => {
    if (!value) return "-";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return value;

    return date.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
};

const today = new Date().toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
});

const maxLuas = computed(() =>
    Math.max(1, ...props.zones.map((zone) => zone.luas)),
);

const totalLuas = computed(() =>
    props.zones.reduce((total, zone) => total + zone.luas, 0),
);

const statCards = computed(() => [
    {
        label: "Kunjungan Lahan",
        value: props.summary.kunjungan_total,
        note: `${formatNumber(props.summary.kunjungan_bulan_ini)} permintaan bulan ini`,
        icon: CalendarCheck,
        accent: groupAccent[0],
    },
    {
        label: "Peluang Investasi",
        value: props.summary.peluang_investasi,
        note: "Dipublikasikan di website",
        icon: BriefcaseBusiness,
        accent: groupAccent[1],
    },
    {
        label: "Total Konten",
        value: props.summary.total_konten,
        note: "Seluruh modul website",
        icon: FileStack,
        accent: groupAccent[2],
    },
    {
        label: "Pengguna Admin",
        value: props.summary.pengguna,
        note: "Akun terdaftar",
        icon: Users,
        accent: groupAccent[0],
    },
]);
</script>

<template>
    <Head title="Dashboard" />

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
                        <LayoutGrid class="size-5" />
                    </div>

                    <div class="min-w-0">
                        <h1
                            class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-2xl"
                        >
                            Dashboard
                        </h1>

                        <p
                            class="mt-0.5 flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400"
                        >
                            <CalendarDays class="size-3.5" />
                            {{ today }}
                        </p>
                    </div>
                </div>

                <a
                    href="/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/20 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:ring-offset-2 focus:ring-offset-slate-50 dark:focus:ring-offset-slate-950"
                >
                    <ExternalLink class="size-4" />
                    Lihat Website
                </a>
            </div>

            <!-- STAT CARDS -->
            <div class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="card in statCards"
                    :key="card.label"
                    class="rounded-2xl border border-slate-200/80 bg-white/90 p-5 shadow-sm shadow-slate-200/40 backdrop-blur-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/10"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-slate-400"
                            >
                                {{ card.label }}
                            </p>

                            <p
                                class="mt-2 text-3xl font-semibold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ formatNumber(card.value) }}
                            </p>
                        </div>

                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl ring-1"
                            :class="card.accent"
                        >
                            <component :is="card.icon" class="size-5" />
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                        {{ card.note }}
                    </p>
                </div>
            </div>

            <!-- KUNJUNGAN + ZONA -->
            <div class="mb-5 grid gap-5 xl:grid-cols-3">
                <!-- Kunjungan terbaru -->
                <div
                    class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm shadow-slate-200/50 backdrop-blur-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10 xl:col-span-2"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
                    >
                        <div>
                            <h2
                                class="text-base font-semibold text-slate-900 dark:text-white"
                            >
                                Permintaan Kunjungan Lahan
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Pengajuan terbaru dari form di website.
                            </p>
                        </div>

                        <Link
                            href="/hubungan-investor/kunjungan-lahan"
                            class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                        >
                            Lihat semua
                            <ArrowUpRight class="size-4" />
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead
                                class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <tr>
                                    <th
                                        class="px-6 py-3 font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Pemohon
                                    </th>
                                    <th
                                        class="px-6 py-3 font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Tanggal Kunjungan
                                    </th>
                                    <th
                                        class="px-6 py-3 font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Peserta
                                    </th>
                                    <th
                                        class="px-6 py-3 font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        Diajukan
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr
                                    v-for="item in kunjunganTerbaru"
                                    :key="item.id"
                                    class="transition-colors duration-200 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                                >
                                    <td class="px-6 py-3.5">
                                        <p
                                            class="font-medium text-slate-900 dark:text-white"
                                        >
                                            {{ item.nama || "-" }}
                                        </p>

                                        <p
                                            class="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{ item.instansi || "-" }}
                                        </p>
                                    </td>

                                    <td
                                        class="px-6 py-3.5 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ formatDate(item.tanggal_kunjungan) }}
                                    </td>

                                    <td
                                        class="px-6 py-3.5 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ item.jumlah_peserta ?? "-" }}
                                    </td>

                                    <td
                                        class="px-6 py-3.5 text-slate-500 dark:text-slate-400"
                                    >
                                        {{ formatDate(item.created_at) }}
                                    </td>
                                </tr>

                                <tr v-if="kunjunganTerbaru.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-6 py-14 text-center"
                                    >
                                        <div
                                            class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600"
                                        >
                                            <CalendarCheck class="size-6" />
                                        </div>

                                        <p
                                            class="mt-3 font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            Belum ada permintaan
                                        </p>

                                        <p
                                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            Permintaan dari tombol Atur Jadwal
                                            akan muncul di sini.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Zona kawasan -->
                <div
                    class="rounded-3xl border border-slate-200/80 bg-white/95 p-6 shadow-sm shadow-slate-200/50 backdrop-blur-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10"
                >
                    <h2
                        class="text-base font-semibold text-slate-900 dark:text-white"
                    >
                        Luas Zona Kawasan
                    </h2>

                    <p
                        class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Total {{ formatNumber(totalLuas) }} Ha
                    </p>

                    <div v-if="zones.length" class="mt-5 space-y-3.5">
                        <div v-for="zone in zones" :key="zone.label">
                            <div
                                class="mb-1.5 flex items-center justify-between gap-3 text-sm"
                            >
                                <span
                                    class="flex min-w-0 items-center gap-2 text-slate-700 dark:text-slate-200"
                                >
                                    <span
                                        class="size-2.5 shrink-0 rounded-sm"
                                        :style="{
                                            backgroundColor: zone.warna,
                                        }"
                                    ></span>

                                    <span class="truncate">
                                        {{ zone.label }}
                                    </span>
                                </span>

                                <span
                                    class="shrink-0 font-medium text-slate-900 dark:text-white"
                                >
                                    {{ formatNumber(zone.luas) }} Ha
                                </span>
                            </div>

                            <div
                                class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                            >
                                <div
                                    class="h-full rounded-full"
                                    :style="{
                                        width: `${(zone.luas / maxLuas) * 100}%`,
                                        backgroundColor: zone.warna,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400"
                    >
                        Data zona kawasan belum tersedia.
                    </div>
                </div>
            </div>

            <!-- MODUL KONTEN -->
            <div class="grid gap-5 lg:grid-cols-3">
                <div
                    v-for="(group, index) in groups"
                    :key="group.title"
                    class="rounded-3xl border border-slate-200/80 bg-white/95 p-6 shadow-sm shadow-slate-200/50 backdrop-blur-sm transition-colors duration-300 dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-black/10"
                >
                    <h2
                        class="mb-4 text-base font-semibold text-slate-900 dark:text-white"
                    >
                        {{ group.title }}
                    </h2>

                    <div class="space-y-1.5">
                        <Link
                            v-for="item in group.items"
                            :key="item.key"
                            :href="item.href"
                            class="group flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 transition-colors hover:bg-blue-50 dark:hover:bg-blue-950/30"
                        >
                            <span class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg ring-1"
                                    :class="groupAccent[index % 3]"
                                >
                                    <component
                                        :is="iconMap[item.key] ?? Building2"
                                        class="size-4"
                                    />
                                </span>

                                <span
                                    class="truncate text-sm font-medium text-slate-700 group-hover:text-blue-600 dark:text-slate-200 dark:group-hover:text-blue-400"
                                >
                                    {{ item.label }}
                                </span>
                            </span>

                            <span
                                class="shrink-0 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                            >
                                {{ formatNumber(item.count) }}
                            </span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
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

@media (prefers-reduced-motion: reduce) {
    .blob-shape,
    .blob-shape-delayed,
    .blob-shape-slow {
        animation: none;
    }
}

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
