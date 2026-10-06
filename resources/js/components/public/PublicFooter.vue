<script setup lang="ts">
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";
import {
    ArrowUpRight,
    Facebook,
    Instagram,
    Linkedin,
    Mail,
    MapPin,
    Youtube,
} from "lucide-vue-next";
import { kontakLink, navGroups, visitUrl } from "@/data/publicNavigation";

/**
 * =========================================================
 * Types
 * =========================================================
 */

interface PengaturanKontak {
    nama_perusahaan: string | null;
    alamat: string | null;
    telepon: string | null;
    whatsapp: string | null;
    email: string | null;
    email_investor: string | null;
    jam_operasional: string | null;
    latitude: number | null;
    longitude: number | null;
    maps_embed_url: string | null;

    // Social media
    facebook: string | null;
    instagram: string | null;
    linkedin: string | null;
    youtube: string | null;
}

interface SharedPageProps {
    pengaturanKontak?: PengaturanKontak | null;
}

/**
 * =========================================================
 * Inertia Shared Props
 * =========================================================
 */

const page = usePage();

const sharedProps = computed(
    () => page.props as typeof page.props & SharedPageProps,
);

const pengaturan = computed(() => sharedProps.value.pengaturanKontak ?? null);

/**
 * =========================================================
 * Contact
 * =========================================================
 */

const emailHref = computed(() => {
    const email = pengaturan.value?.email?.trim();

    return email ? `mailto:${email}` : "#";
});

/**
 * =========================================================
 * Social Media
 * =========================================================
 */

type SocialType = "facebook" | "instagram" | "linkedin" | "youtube";

const socialIcons: Record<SocialType, typeof Facebook> = {
    facebook: Facebook,
    instagram: Instagram,
    linkedin: Linkedin,
    youtube: Youtube,
};

const socialLinks = computed(() => {
    const data = pengaturan.value;

    if (!data) {
        return [];
    }

    const links: Array<{
        type: SocialType;
        label: string;
        href: string;
    }> = [];

    if (data.facebook?.trim()) {
        links.push({
            type: "facebook",
            label: "Facebook",
            href: data.facebook.trim(),
        });
    }

    if (data.instagram?.trim()) {
        links.push({
            type: "instagram",
            label: "Instagram",
            href: data.instagram.trim(),
        });
    }

    if (data.linkedin?.trim()) {
        links.push({
            type: "linkedin",
            label: "LinkedIn",
            href: data.linkedin.trim(),
        });
    }

    if (data.youtube?.trim()) {
        links.push({
            type: "youtube",
            label: "YouTube",
            href: data.youtube.trim(),
        });
    }

    return links;
});
</script>

<template>
    <footer id="kontak" class="bg-kitb-navy-900 text-white">
        <div
            class="mx-auto w-full max-w-[1440px] px-5 py-14 sm:px-6 md:py-16 lg:px-8 lg:py-20"
        >
            <!-- Main Footer -->
            <div class="grid gap-12 sm:gap-14 lg:grid-cols-12 lg:gap-8">
                <!-- Brand -->
                <div class="lg:col-span-4">
                    <Link
                        href="/"
                        :aria-label="trans('footer.home_aria')"
                        class="inline-flex items-center gap-3"
                    >
                        <div class="flex shrink-0 items-center gap-2">
                            <img
                                src="/images/siak-kabupaten.png"
                                alt="Kabupaten Siak"
                                class="h-9 w-auto object-contain sm:h-10"
                            />

                            <div
                                class="h-9 w-px bg-white/15 sm:h-10"
                                aria-hidden="true"
                            />

                            <img
                                src="/images/kitb-logo.png"
                                alt="KITB"
                                class="h-10 w-auto object-contain sm:h-11"
                            />
                        </div>

                        <div class="min-w-0">
                            <p
                                class="truncate text-base font-semibold leading-none tracking-tight sm:text-[17px]"
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

                    <p class="mt-6 max-w-lg text-sm leading-7 text-white/65">
                        {{
                            pengaturan?.nama_perusahaan ||
                            "PT. Kawasan Industri Tanjung Buton"
                        }}
                        {{ trans("footer.company_description") }}
                    </p>

                    <!-- Contact Information -->
                    <div class="mt-6 space-y-3.5">
                        <!-- Address -->
                        <div
                            v-if="pengaturan?.alamat"
                            class="flex items-start gap-3 text-sm text-white/65"
                        >
                            <MapPin
                                class="mt-0.5 size-4 shrink-0 text-kitb-teal-300"
                                aria-hidden="true"
                            />

                            <span class="min-w-0 leading-6">
                                {{ pengaturan.alamat }}
                            </span>
                        </div>

                        <!-- Email -->
                        <a
                            v-if="pengaturan?.email"
                            :href="emailHref"
                            class="flex min-w-0 items-center gap-3 text-sm text-white/65 transition-colors duration-200 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-kitb-navy-900"
                        >
                            <Mail
                                class="size-4 shrink-0 text-kitb-teal-300"
                                aria-hidden="true"
                            />

                            <span class="truncate">
                                {{ pengaturan.email }}
                            </span>
                        </a>
                    </div>
                </div>

                <!-- Navigation -->
                <div
                    class="grid gap-10 sm:grid-cols-2 lg:col-span-5 lg:grid-cols-2 lg:gap-x-8 lg:gap-y-10"
                >
                    <div
                        v-for="group in navGroups.slice(0, 2)"
                        :key="group.translationKey ?? group.label"
                    >
                        <h3
                            class="text-sm font-semibold tracking-tight text-white"
                        >
                            {{ trans(group.translationKey ?? group.label) }}
                        </h3>

                        <ul class="mt-4 space-y-2.5">
                            <li
                                v-for="item in group.items"
                                :key="item.translationKey ?? item.label"
                            >
                                <Link
                                    :href="item.href"
                                    class="group inline-flex max-w-full items-center gap-1.5 text-sm text-white/55 transition-colors duration-200 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-kitb-navy-900"
                                >
                                    <span class="truncate">
                                        {{
                                            trans(
                                                item.translationKey ??
                                                    item.label,
                                            )
                                        }}
                                    </span>

                                    <span
                                        v-if="item.badge"
                                        class="shrink-0 rounded-full bg-white/10 px-1.5 py-0.5 text-[9px] font-semibold text-kitb-teal-300 transition-colors group-hover:bg-kitb-teal-300/10"
                                    >
                                        {{
                                            item.badgeTranslationKey
                                                ? trans(
                                                      item.badgeTranslationKey,
                                                  )
                                                : item.badge
                                        }}
                                    </span>
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div
                        v-for="group in navGroups.slice(2)"
                        :key="group.translationKey ?? group.label"
                    >
                        <h3
                            class="text-sm font-semibold tracking-tight text-white"
                        >
                            {{ trans(group.translationKey ?? group.label) }}
                        </h3>

                        <ul class="mt-4 space-y-2.5">
                            <li
                                v-for="item in group.items"
                                :key="item.translationKey ?? item.label"
                            >
                                <Link
                                    :href="item.href"
                                    class="group inline-flex max-w-full items-center gap-1.5 text-sm text-white/55 transition-colors duration-200 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-kitb-navy-900"
                                >
                                    <span class="truncate">
                                        {{
                                            trans(
                                                item.translationKey ??
                                                    item.label,
                                            )
                                        }}
                                    </span>

                                    <span
                                        v-if="item.badge"
                                        class="shrink-0 rounded-full bg-white/10 px-1.5 py-0.5 text-[9px] font-semibold text-kitb-teal-300 transition-colors group-hover:bg-kitb-teal-300/10"
                                    >
                                        {{
                                            item.badgeTranslationKey
                                                ? trans(
                                                      item.badgeTranslationKey,
                                                  )
                                                : item.badge
                                        }}
                                    </span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Contact / CTA -->
                <div class="lg:col-span-3">
                    <h3 class="text-sm font-semibold tracking-tight text-white">
                        {{ trans("footer.contact_us") }}
                    </h3>

                    <p class="mt-4 max-w-sm text-sm leading-6 text-white/55">
                        {{ trans("footer.contact_description") }}
                    </p>

                    <!-- CTA -->
                    <div class="mt-5">
                        <Link
                            :href="visitUrl"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-kitb-navy-900 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-kitb-sand-50 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-kitb-navy-900 sm:w-auto"
                        >
                            <span>
                                {{ trans("navigation.submit_visit") }}
                            </span>

                            <ArrowUpRight class="size-4" aria-hidden="true" />
                        </Link>
                    </div>

                    <!-- Contact Link -->
                    <div class="mt-4">
                        <Link
                            :href="kontakLink.href"
                            class="inline-flex items-center gap-2 rounded-lg px-1 py-1 text-sm font-medium text-white/65 transition-colors duration-200 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-kitb-navy-900"
                        >
                            <span>
                                {{
                                    trans(
                                        kontakLink.translationKey ??
                                            kontakLink.label,
                                    )
                                }}
                            </span>

                            <ArrowUpRight class="size-3.5" aria-hidden="true" />
                        </Link>
                    </div>

                    <!-- Social Media -->
                    <div v-if="socialLinks.length" class="mt-8">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-white/40"
                        >
                            {{ trans("footer.follow_us") }}
                        </p>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <a
                                v-for="social in socialLinks"
                                :key="social.type"
                                :href="social.href"
                                :aria-label="social.label"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex size-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white/65 transition-all duration-200 hover:-translate-y-0.5 hover:border-white/20 hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-kitb-navy-900"
                            >
                                <component
                                    :is="socialIcons[social.type]"
                                    class="size-4"
                                    aria-hidden="true"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom -->
            <div
                class="mt-12 flex flex-col gap-5 border-t border-white/10 pt-6 sm:mt-14 lg:mt-16 lg:flex-row lg:items-center lg:justify-between"
            >
                <p class="text-xs leading-5 text-white/40">
                    © {{ new Date().getFullYear() }}
                    {{
                        pengaturan?.nama_perusahaan ||
                        "PT. Kawasan Industri Tanjung Buton"
                    }}.
                    {{ trans("footer.regional_owned_enterprise") }}
                </p>

                <div
                    class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-white/40"
                >
                    <Link
                        href="/kebijakan-privasi"
                        class="transition-colors duration-200 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-kitb-navy-900"
                    >
                        {{ trans("footer.privacy_policy") }}
                    </Link>

                    <Link
                        href="/pengaduan"
                        class="transition-colors duration-200 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-kitb-navy-900"
                    >
                        {{ trans("footer.complaints") }}
                    </Link>

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
