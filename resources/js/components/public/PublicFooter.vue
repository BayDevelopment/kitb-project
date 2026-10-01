<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import {
    ArrowUpRight,
    Instagram,
    Mail,
    MapPin,
    Music2,
    Youtube,
} from "lucide-vue-next";
import {
    kontakLink,
    navGroups,
    socials,
    visitUrl,
} from "@/data/publicNavigation";

const socialIcons = {
    instagram: Instagram,
    tiktok: Music2,
    youtube: Youtube,
};
</script>

<template>
    <footer id="kontak" class="bg-kitb-navy-900 text-white">
        <div
            class="mx-auto w-full max-w-[1440px] px-5 py-14 sm:px-6 lg:px-8 lg:py-16"
        >
            <!-- Main Footer -->
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-8">
                <!-- Brand -->
                <div class="lg:col-span-4">
                    <Link href="/" class="inline-flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <img
                                src="/images/siak-kabupaten.png"
                                alt="Kabupaten Siak"
                                class="h-9 w-auto object-contain sm:h-10"
                            />

                            <div
                                class="h-9 w-px bg-white/15"
                                aria-hidden="true"
                            />

                            <img
                                src="/images/kitb-logo.png"
                                alt="KITB"
                                class="h-10 w-auto object-contain sm:h-11"
                            />
                        </div>

                        <div>
                            <p
                                class="text-base font-semibold leading-none tracking-tight"
                            >
                                Tanjung Buton
                            </p>

                            <p
                                class="mt-1 text-[9px] font-medium uppercase tracking-[0.16em] text-kitb-teal-300"
                            >
                                Industrial Estate
                            </p>
                        </div>
                    </Link>

                    <p class="mt-6 max-w-md text-sm leading-7 text-white/65">
                        PT. Kawasan Industri Tanjung Buton merupakan kawasan
                        industri strategis di Kabupaten Siak, Provinsi Riau,
                        yang dikembangkan untuk mendukung pertumbuhan industri
                        dan investasi berkelanjutan.
                    </p>

                    <div class="mt-6 space-y-3">
                        <div
                            class="flex items-start gap-3 text-sm text-white/65"
                        >
                            <MapPin
                                class="mt-0.5 size-4 shrink-0 text-kitb-teal-300"
                            />

                            <span class="leading-6">
                                Kampung Mengkapan &amp; Kampung Sungai Rawa,
                                Kecamatan Sungai Apit, Kabupaten Siak, Provinsi
                                Riau
                            </span>
                        </div>

                        <a
                            href="mailto:info@tanjungbuton-industrial.co.id"
                            class="flex items-center gap-3 text-sm text-white/65 transition-colors hover:text-white"
                        >
                            <Mail class="size-4 shrink-0 text-kitb-teal-300" />

                            <span> info@tanjungbuton-industrial.co.id </span>
                        </a>
                    </div>
                </div>

                <!-- Navigation -->
                <div
                    class="grid gap-8 sm:grid-cols-2 lg:col-span-5 lg:grid-cols-2"
                >
                    <div
                        v-for="group in navGroups.slice(0, 2)"
                        :key="group.label"
                    >
                        <h3 class="text-sm font-semibold text-white">
                            {{ group.label }}
                        </h3>

                        <ul class="mt-4 space-y-2.5">
                            <li v-for="item in group.items" :key="item.label">
                                <Link
                                    :href="item.href"
                                    class="inline-flex items-center gap-1.5 text-sm text-white/55 transition-colors hover:text-white"
                                >
                                    <span>
                                        {{ item.label }}
                                    </span>

                                    <span
                                        v-if="item.badge"
                                        class="rounded-full bg-white/10 px-1.5 py-0.5 text-[9px] font-semibold text-kitb-teal-300"
                                    >
                                        {{ item.badge }}
                                    </span>
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div v-for="group in navGroups.slice(2)" :key="group.label">
                        <h3 class="text-sm font-semibold text-white">
                            {{ group.label }}
                        </h3>

                        <ul class="mt-4 space-y-2.5">
                            <li v-for="item in group.items" :key="item.label">
                                <Link
                                    :href="item.href"
                                    class="inline-flex items-center gap-1.5 text-sm text-white/55 transition-colors hover:text-white"
                                >
                                    <span>
                                        {{ item.label }}
                                    </span>

                                    <span
                                        v-if="item.badge"
                                        class="rounded-full bg-white/10 px-1.5 py-0.5 text-[9px] font-semibold text-kitb-teal-300"
                                    >
                                        {{ item.badge }}
                                    </span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Contact / CTA -->
                <div class="lg:col-span-3">
                    <h3 class="text-sm font-semibold text-white">
                        Kontak Kami
                    </h3>

                    <p class="mt-4 text-sm leading-6 text-white/55">
                        Hubungi tim KITB untuk informasi kawasan, peluang
                        investasi, maupun kunjungan lahan.
                    </p>

                    <!-- CTA -->
                    <div class="mt-5">
                        <Link
                            :href="visitUrl"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-kitb-navy-900 transition-all duration-200 hover:-translate-y-0.5 hover:bg-kitb-sand-50 sm:w-auto"
                        >
                            <span>Ajukan Kunjungan</span>
                            <ArrowUpRight class="size-4" />
                        </Link>
                    </div>

                    <!-- Contact Link -->
                    <div class="mt-4">
                        <a
                            :href="kontakLink.href"
                            class="inline-flex items-center gap-2 rounded-lg px-1 py-1 text-sm font-medium text-white/65 transition-colors hover:text-white"
                        >
                            <span>{{ kontakLink.label }}</span>
                            <ArrowUpRight class="size-3.5" />
                        </a>
                    </div>

                    <!-- Social -->
                    <div class="mt-8">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-white/40"
                        >
                            Ikuti Kami
                        </p>

                        <div class="mt-3 flex items-center gap-2">
                            <a
                                v-for="social in socials"
                                :key="social.type"
                                :href="social.href"
                                :aria-label="social.label"
                                class="flex size-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white/65 transition-all duration-200 hover:-translate-y-0.5 hover:border-white/20 hover:bg-white/10 hover:text-white"
                                :target="
                                    social.href === '#' ? undefined : '_blank'
                                "
                                :rel="
                                    social.href === '#'
                                        ? undefined
                                        : 'noopener noreferrer'
                                "
                            >
                                <component
                                    :is="socialIcons[social.type]"
                                    class="size-4"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom -->
            <div
                class="mt-12 flex flex-col gap-4 border-t border-white/10 pt-6 lg:flex-row lg:items-center lg:justify-between"
            >
                <p class="text-xs leading-5 text-white/40">
                    © 2026 PT. Kawasan Industri Tanjung Buton. Badan Usaha Milik
                    Daerah, Kabupaten Siak.
                </p>

                <div
                    class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-white/40"
                >
                    <a
                        href="/kebijakan-privasi"
                        class="transition-colors hover:text-white"
                    >
                        Kebijakan Privasi
                    </a>

                    <a
                        href="/pengaduan"
                        class="transition-colors hover:text-white"
                    >
                        Pengaduan
                    </a>

                    <span
                        class="hidden h-3.5 w-px bg-white/15 sm:block"
                        aria-hidden="true"
                    />

                    <span class="text-white/30">
                        Beyond Industry, Towards the Future.
                    </span>
                </div>
            </div>
        </div>
    </footer>
</template>
