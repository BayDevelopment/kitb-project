<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";

import { dashboard } from "@/routes";

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: "Dashboard",
                href: dashboard(),
            },
        ],
    },
});

const isLoading = ref(true);

onMounted(() => {
    window.setTimeout(() => {
        isLoading.value = false;
    }, 650);
});
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="relative min-h-full overflow-hidden bg-slate-50 transition-colors duration-300 dark:bg-[#07111f]"
    >
        <!-- =====================================================
             BACKGROUND
        ====================================================== -->

        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <!-- Light -->

            <div
                class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-[#0b1f3a]/8 blur-3xl transition-opacity duration-500 dark:opacity-0"
            />

            <div
                class="absolute -right-40 top-20 h-[28rem] w-[28rem] rounded-full bg-[#12345b]/8 blur-3xl transition-opacity duration-500 dark:opacity-0"
            />

            <div
                class="absolute -bottom-40 left-1/3 h-[30rem] w-[30rem] rounded-full bg-[#0b1f3a]/6 blur-3xl transition-opacity duration-500 dark:opacity-0"
            />

            <div
                class="absolute left-[8%] top-[42%] h-32 w-32 rounded-full bg-[#1d4ed8]/5 blur-2xl transition-opacity duration-500 dark:opacity-0"
            />

            <!-- Dark -->

            <div
                class="absolute -left-40 -top-40 h-[32rem] w-[32rem] rounded-full bg-[#163b68]/35 opacity-0 blur-3xl transition-opacity duration-500 dark:opacity-100"
            />

            <div
                class="absolute -right-44 top-0 h-[34rem] w-[34rem] rounded-full bg-[#1d4ed8]/15 opacity-0 blur-3xl transition-opacity duration-500 dark:opacity-100"
            />

            <div
                class="absolute -bottom-48 left-1/3 h-[34rem] w-[34rem] rounded-full bg-[#0e7490]/10 opacity-0 blur-3xl transition-opacity duration-500 dark:opacity-100"
            />

            <div
                class="absolute right-[20%] top-[45%] h-40 w-40 rounded-full bg-[#2563eb]/10 opacity-0 blur-3xl transition-opacity duration-500 dark:opacity-100"
            />

            <!-- Decorative -->

            <div
                class="absolute left-[7%] top-[45%] h-20 w-20 rounded-full bg-[#163b68]/10 shadow-xl shadow-[#0b1f3a]/10 dark:bg-[#60a5fa]/10 dark:shadow-[#2563eb]/10"
            />

            <div
                class="absolute left-[4%] top-[54%] h-4 w-4 rounded-full bg-[#0b1f3a]/20 dark:bg-blue-300/30"
            />

            <div
                class="absolute right-[8%] top-[30%] h-4 w-4 rounded-full bg-[#0b1f3a]/20 dark:bg-blue-300/30"
            />
        </div>

        <!-- =====================================================
             CONTENT
        ====================================================== -->

        <div class="relative flex flex-1 flex-col gap-6 p-4 sm:p-6">
            <Transition name="dashboard-fade" mode="out-in">
                <!-- =================================================
                     SKELETON
                ================================================== -->

                <div
                    v-if="isLoading"
                    key="dashboard-skeleton"
                    class="space-y-6"
                    role="status"
                    aria-live="polite"
                    aria-busy="true"
                >
                    <span class="sr-only"> Memuat dashboard… </span>

                    <!-- Welcome Skeleton -->

                    <section class="space-y-3">
                        <div
                            class="h-4 w-64 animate-pulse rounded-md bg-slate-200 dark:bg-slate-800"
                        />

                        <div
                            class="h-8 w-40 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                        />

                        <div
                            class="h-4 w-full max-w-xl animate-pulse rounded-md bg-slate-200/80 dark:bg-slate-800/80"
                        />
                    </section>

                    <!-- Statistics Skeleton -->

                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div
                            v-for="index in 4"
                            :key="`stat-${index}`"
                            class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/70"
                        >
                            <div
                                class="h-4 w-28 animate-pulse rounded-md bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="mt-4 h-9 w-24 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="mt-3 h-3 w-36 animate-pulse rounded-md bg-slate-200/80 dark:bg-slate-800/80"
                            />
                        </div>
                    </section>

                    <!-- Main Skeleton -->

                    <section class="grid gap-6 lg:grid-cols-3">
                        <!-- Summary -->

                        <div
                            class="rounded-2xl border border-slate-200/80 bg-white/80 p-6 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/70 lg:col-span-2"
                        >
                            <div
                                class="h-5 w-40 animate-pulse rounded-md bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="mt-2 h-4 w-72 max-w-full animate-pulse rounded-md bg-slate-200/80 dark:bg-slate-800/80"
                            />

                            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                <div
                                    v-for="index in 4"
                                    :key="`summary-${index}`"
                                    class="rounded-xl bg-slate-100/80 p-4 dark:bg-slate-800/60"
                                >
                                    <div
                                        class="h-4 w-32 animate-pulse rounded-md bg-slate-200 dark:bg-slate-700"
                                    />

                                    <div
                                        class="mt-3 h-3 w-full animate-pulse rounded-md bg-slate-200/80 dark:bg-slate-700/80"
                                    />

                                    <div
                                        class="mt-2 h-3 w-3/4 animate-pulse rounded-md bg-slate-200/70 dark:bg-slate-700/70"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Status -->

                        <div
                            class="rounded-2xl border border-slate-200/80 bg-white/80 p-6 shadow-sm dark:border-slate-800/80 dark:bg-slate-900/70"
                        >
                            <div
                                class="h-5 w-32 animate-pulse rounded-md bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="mt-7 space-y-6">
                                <div
                                    v-for="index in 3"
                                    :key="`status-${index}`"
                                    class="flex items-center justify-between"
                                >
                                    <div
                                        class="h-4 w-24 animate-pulse rounded-md bg-slate-200 dark:bg-slate-800"
                                    />

                                    <div
                                        class="h-6 w-20 animate-pulse rounded-full bg-slate-200 dark:bg-slate-800"
                                    />
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- =================================================
                     ACTUAL DASHBOARD
                ================================================== -->

                <div v-else key="dashboard-content" class="space-y-6">
                    <!-- Welcome -->

                    <section class="dashboard-item dashboard-item-1">
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            PT Kawasan Industri Tanjung Buton
                        </p>

                        <h1
                            class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl"
                        >
                            Dashboard
                        </h1>

                        <p
                            class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500 dark:text-slate-400"
                        >
                            Kelola informasi dan konten website KITB melalui
                            panel administrasi.
                        </p>
                    </section>

                    <!-- Statistics -->

                    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <!-- Total Lahan -->

                        <div
                            class="dashboard-item dashboard-item-2 group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-5 shadow-sm backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md dark:border-slate-800/80 dark:bg-slate-900/75 dark:hover:border-slate-700 dark:hover:bg-slate-900/90"
                        >
                            <div
                                class="absolute -right-10 -top-10 size-28 rounded-full bg-[#0b1f3a]/6 blur-2xl transition-transform duration-500 group-hover:scale-125 dark:bg-blue-500/10"
                            />

                            <div class="relative">
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Total Lahan
                                </p>

                                <p
                                    class="mt-2 text-3xl font-semibold text-[#0b1f3a] dark:text-blue-100"
                                >
                                    6.070
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Hektare kawasan industri
                                </p>
                            </div>
                        </div>

                        <!-- Zona -->

                        <div
                            class="dashboard-item dashboard-item-3 group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-5 shadow-sm backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md dark:border-slate-800/80 dark:bg-slate-900/75 dark:hover:border-slate-700 dark:hover:bg-slate-900/90"
                        >
                            <div
                                class="absolute -right-10 -top-10 size-28 rounded-full bg-[#0b1f3a]/6 blur-2xl transition-transform duration-500 group-hover:scale-125 dark:bg-blue-500/10"
                            />

                            <div class="relative">
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Zona Kawasan
                                </p>

                                <p
                                    class="mt-2 text-3xl font-semibold text-[#0b1f3a] dark:text-blue-100"
                                >
                                    9
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Zona dalam master plan
                                </p>
                            </div>
                        </div>

                        <!-- Rute -->

                        <div
                            class="dashboard-item dashboard-item-4 group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-5 shadow-sm backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md dark:border-slate-800/80 dark:bg-slate-900/75 dark:hover:border-slate-700 dark:hover:bg-slate-900/90"
                        >
                            <div
                                class="absolute -right-10 -top-10 size-28 rounded-full bg-[#0b1f3a]/6 blur-2xl transition-transform duration-500 group-hover:scale-125 dark:bg-blue-500/10"
                            />

                            <div class="relative">
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Rute Pelayaran
                                </p>

                                <p
                                    class="mt-2 text-3xl font-semibold text-[#0b1f3a] dark:text-blue-100"
                                >
                                    36
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Mil menuju Selat Malaka
                                </p>
                            </div>
                        </div>

                        <!-- Pembangunan -->

                        <div
                            class="dashboard-item dashboard-item-5 group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-5 shadow-sm backdrop-blur-md transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md dark:border-slate-800/80 dark:bg-slate-900/75 dark:hover:border-slate-700 dark:hover:bg-slate-900/90"
                        >
                            <div
                                class="absolute -right-10 -top-10 size-28 rounded-full bg-[#0b1f3a]/6 blur-2xl transition-transform duration-500 group-hover:scale-125 dark:bg-blue-500/10"
                            />

                            <div class="relative">
                                <p
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Pembangunan
                                </p>

                                <p
                                    class="mt-2 text-3xl font-semibold text-[#0b1f3a] dark:text-blue-100"
                                >
                                    Tahap 1
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                                >
                                    Pengembangan kawasan
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Main Content -->

                    <section class="grid gap-6 lg:grid-cols-3">
                        <!-- Ringkasan -->

                        <div
                            class="dashboard-item dashboard-item-6 relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/75 lg:col-span-2"
                        >
                            <div
                                class="absolute -right-24 -top-24 size-64 rounded-full bg-[#0b1f3a]/5 blur-3xl dark:bg-blue-500/10"
                            />

                            <div class="relative">
                                <h2
                                    class="text-base font-semibold text-slate-900 dark:text-white"
                                >
                                    Ringkasan Website
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Informasi konten yang dikelola melalui
                                    admin.
                                </p>

                                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                    <div
                                        class="rounded-xl bg-slate-50/90 p-4 ring-1 ring-slate-100 transition-colors hover:bg-slate-100/80 dark:bg-slate-800/70 dark:ring-slate-700/70 dark:hover:bg-slate-800"
                                    >
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Profil Perusahaan
                                        </p>

                                        <p
                                            class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                                        >
                                            Informasi, visi, misi, struktur, dan
                                            anak usaha.
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-xl bg-slate-50/90 p-4 ring-1 ring-slate-100 transition-colors hover:bg-slate-100/80 dark:bg-slate-800/70 dark:ring-slate-700/70 dark:hover:bg-slate-800"
                                    >
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Kawasan Industri
                                        </p>

                                        <p
                                            class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                                        >
                                            Master plan, zona, lahan, dan
                                            pembangunan.
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-xl bg-slate-50/90 p-4 ring-1 ring-slate-100 transition-colors hover:bg-slate-100/80 dark:bg-slate-800/70 dark:ring-slate-700/70 dark:hover:bg-slate-800"
                                    >
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Investasi
                                        </p>

                                        <p
                                            class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                                        >
                                            Peluang investasi, kemudahan usaha,
                                            dan rute.
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-xl bg-slate-50/90 p-4 ring-1 ring-slate-100 transition-colors hover:bg-slate-100/80 dark:bg-slate-800/70 dark:ring-slate-700/70 dark:hover:bg-slate-800"
                                    >
                                        <p
                                            class="text-sm font-medium text-slate-700 dark:text-slate-200"
                                        >
                                            Informasi
                                        </p>

                                        <p
                                            class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                                        >
                                            Berita, galeri, dan publikasi KITB.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->

                        <div
                            class="dashboard-item dashboard-item-7 relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/75"
                        >
                            <div
                                class="absolute -bottom-20 -right-20 size-48 rounded-full bg-[#0b1f3a]/6 blur-3xl dark:bg-blue-500/10"
                            />

                            <div class="relative">
                                <h2
                                    class="text-base font-semibold text-slate-900 dark:text-white"
                                >
                                    Status Sistem
                                </h2>

                                <div class="mt-6 space-y-5">
                                    <div
                                        class="flex items-center justify-between gap-4"
                                    >
                                        <span
                                            class="text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            Website
                                        </span>

                                        <span
                                            class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400"
                                        >
                                            Aktif
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between gap-4"
                                    >
                                        <span
                                            class="text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            Database
                                        </span>

                                        <span
                                            class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400"
                                        >
                                            Terhubung
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between gap-4"
                                    >
                                        <span
                                            class="text-sm text-slate-500 dark:text-slate-400"
                                        >
                                            Admin Panel
                                        </span>

                                        <span
                                            class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600 dark:bg-blue-500/10 dark:text-blue-400"
                                        >
                                            Online
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
/* =========================================================
   PAGE TRANSITION
========================================================= */

.dashboard-fade-enter-active,
.dashboard-fade-leave-active {
    transition:
        opacity 320ms ease,
        transform 320ms ease;
}

.dashboard-fade-enter-from {
    opacity: 0;
    transform: translateY(8px);
}

.dashboard-fade-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

/* =========================================================
   CONTENT STAGGER
========================================================= */

.dashboard-item {
    opacity: 0;
    animation: dashboard-item-enter 480ms cubic-bezier(0.22, 1, 0.36, 1)
        forwards;
}

.dashboard-item-1 {
    animation-delay: 40ms;
}

.dashboard-item-2 {
    animation-delay: 90ms;
}

.dashboard-item-3 {
    animation-delay: 130ms;
}

.dashboard-item-4 {
    animation-delay: 170ms;
}

.dashboard-item-5 {
    animation-delay: 210ms;
}

.dashboard-item-6 {
    animation-delay: 250ms;
}

.dashboard-item-7 {
    animation-delay: 290ms;
}

@keyframes dashboard-item-enter {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* =========================================================
   REDUCED MOTION
========================================================= */

@media (prefers-reduced-motion: reduce) {
    .dashboard-fade-enter-active,
    .dashboard-fade-leave-active {
        transition: none;
    }

    .dashboard-fade-enter-from,
    .dashboard-fade-leave-to {
        transform: none;
    }

    .dashboard-item {
        opacity: 1;
        animation: none;
    }
}
</style>
