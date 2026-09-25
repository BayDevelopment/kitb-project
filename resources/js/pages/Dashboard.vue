<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { dashboard } from '@/routes'

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
})
</script>

<template>
    <Head title="Dashboard" />

    <div class="relative min-h-full overflow-hidden">
        <!-- Background blobs -->
        <div
            class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-[#0b1f3a]/8 blur-3xl"
        ></div>

        <div
            class="pointer-events-none absolute -right-40 top-20 h-[28rem] w-[28rem] rounded-full bg-[#12345b]/7 blur-3xl"
        ></div>

        <div
            class="pointer-events-none absolute -bottom-40 left-1/3 h-[30rem] w-[30rem] rounded-full bg-[#0b1f3a]/6 blur-3xl"
        ></div>

        <!-- Content -->
        <div class="relative flex flex-1 flex-col gap-6 p-6">

            <!-- Welcome -->
            <div>
                <p class="text-sm font-medium text-slate-500">
                    PT Kawasan Industri Tanjung Buton
                </p>

                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">
                    Dashboard
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Kelola informasi dan konten website KITB melalui panel administrasi.
                </p>
            </div>

            <!-- Statistics -->
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

                <!-- Total Lahan -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-5 shadow-sm backdrop-blur-sm"
                >
                    <div
                        class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-[#0b1f3a]/6 blur-2xl"
                    ></div>

                    <div class="relative">
                        <p class="text-sm text-slate-500">
                            Total Lahan
                        </p>

                        <p class="mt-2 text-3xl font-semibold text-[#0b1f3a]">
                            6.070
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Hektare kawasan industri
                        </p>
                    </div>
                </div>

                <!-- Zona Kawasan -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-5 shadow-sm backdrop-blur-sm"
                >
                    <div
                        class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-[#0b1f3a]/6 blur-2xl"
                    ></div>

                    <div class="relative">
                        <p class="text-sm text-slate-500">
                            Zona Kawasan
                        </p>

                        <p class="mt-2 text-3xl font-semibold text-[#0b1f3a]">
                            9
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Zona dalam master plan
                        </p>
                    </div>
                </div>

                <!-- Rute -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-5 shadow-sm backdrop-blur-sm"
                >
                    <div
                        class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-[#0b1f3a]/6 blur-2xl"
                    ></div>

                    <div class="relative">
                        <p class="text-sm text-slate-500">
                            Rute Pelayaran
                        </p>

                        <p class="mt-2 text-3xl font-semibold text-[#0b1f3a]">
                            36
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Mil menuju Selat Malaka
                        </p>
                    </div>
                </div>

                <!-- Pembangunan -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-5 shadow-sm backdrop-blur-sm"
                >
                    <div
                        class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-[#0b1f3a]/6 blur-2xl"
                    ></div>

                    <div class="relative">
                        <p class="text-sm text-slate-500">
                            Pembangunan
                        </p>

                        <p class="mt-2 text-3xl font-semibold text-[#0b1f3a]">
                            Tahap 1
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Pengembangan kawasan
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="grid gap-6 lg:grid-cols-3">

                <!-- Aktivitas -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-sm lg:col-span-2"
                >
                    <div
                        class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-[#0b1f3a]/5 blur-3xl"
                    ></div>

                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-slate-900">
                                    Ringkasan Website
                                </h2>

                                <p class="mt-1 text-sm text-slate-500">
                                    Informasi konten yang dikelola melalui admin.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-3 sm:grid-cols-2">

                            <div
                                class="rounded-xl bg-slate-50/80 p-4 ring-1 ring-slate-100"
                            >
                                <p class="text-sm font-medium text-slate-700">
                                    Profil Perusahaan
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Informasi, visi, misi, struktur, dan anak usaha.
                                </p>
                            </div>

                            <div
                                class="rounded-xl bg-slate-50/80 p-4 ring-1 ring-slate-100"
                            >
                                <p class="text-sm font-medium text-slate-700">
                                    Kawasan Industri
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Master plan, zona, lahan, dan pembangunan.
                                </p>
                            </div>

                            <div
                                class="rounded-xl bg-slate-50/80 p-4 ring-1 ring-slate-100"
                            >
                                <p class="text-sm font-medium text-slate-700">
                                    Investasi
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Peluang investasi, kemudahan usaha, dan rute.
                                </p>
                            </div>

                            <div
                                class="rounded-xl bg-slate-50/80 p-4 ring-1 ring-slate-100"
                            >
                                <p class="text-sm font-medium text-slate-700">
                                    Informasi
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Berita, galeri, dan publikasi KITB.
                                </p>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div
                    class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/85 p-6 shadow-sm backdrop-blur-sm"
                >
                    <div
                        class="absolute -bottom-20 -right-20 h-48 w-48 rounded-full bg-[#0b1f3a]/6 blur-3xl"
                    ></div>

                    <div class="relative">
                        <h2 class="text-base font-semibold text-slate-900">
                            Status Sistem
                        </h2>

                        <div class="mt-6 space-y-5">

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">
                                    Website
                                </span>

                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600"
                                >
                                    Aktif
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">
                                    Database
                                </span>

                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600"
                                >
                                    Terhubung
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">
                                    Admin Panel
                                </span>

                                <span
                                    class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600"
                                >
                                    Online
                                </span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</template>
