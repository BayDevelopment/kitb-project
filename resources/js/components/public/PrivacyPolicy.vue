<script setup lang="ts">
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";
import {
    ArrowLeft,
    ShieldCheck,
    LockKeyhole,
    Database,
    Cookie,
    Share2,
    UserRoundCheck,
    ExternalLink,
    Mail,
} from "lucide-vue-next";

const sections = computed(() => [
    {
        number: "01",
        icon: Database,
        title: trans("legal.privacy.sections.collection.title"),
        content: trans("legal.privacy.sections.collection.content"),
    },
    {
        number: "02",
        icon: UserRoundCheck,
        title: trans("legal.privacy.sections.usage.title"),
        content: trans("legal.privacy.sections.usage.content"),
    },
    {
        number: "03",
        icon: LockKeyhole,
        title: trans("legal.privacy.sections.protection.title"),
        content: trans("legal.privacy.sections.protection.content"),
    },
    {
        number: "04",
        icon: Cookie,
        title: trans("legal.privacy.sections.cookies.title"),
        content: trans("legal.privacy.sections.cookies.content"),
    },
    {
        number: "05",
        icon: Share2,
        title: trans("legal.privacy.sections.sharing.title"),
        content: trans("legal.privacy.sections.sharing.content"),
    },
    {
        number: "06",
        icon: ExternalLink,
        title: trans("legal.privacy.sections.external_links.title"),
        content: trans("legal.privacy.sections.external_links.content"),
    },
    {
        number: "07",
        icon: UserRoundCheck,
        title: trans("legal.privacy.sections.user_rights.title"),
        content: trans("legal.privacy.sections.user_rights.content"),
    },
    {
        number: "08",
        icon: ShieldCheck,
        title: trans("legal.privacy.sections.changes.title"),
        content: trans("legal.privacy.sections.changes.content"),
    },
]);
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
    >
        <!-- Hero -->
        <section
            class="relative overflow-hidden border-b border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900"
        >
            <!-- Decorative background -->
            <div
                class="pointer-events-none absolute -right-32 -top-32 h-80 w-80 rounded-full bg-kitb-teal-300/10 blur-3xl"
            />

            <div
                class="pointer-events-none absolute -left-32 bottom-0 h-72 w-72 rounded-full bg-kitb-sand-200/20 blur-3xl"
            />

            <div
                class="relative mx-auto w-full max-w-[1200px] px-5 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20"
            >
                <!-- Back -->
                <Link
                    href="/"
                    class="group inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition-colors duration-200 hover:text-kitb-teal-600 dark:text-white/50 dark:hover:text-kitb-teal-300"
                >
                    <ArrowLeft
                        class="size-4 transition-transform duration-200 group-hover:-translate-x-1"
                    />

                    <span>
                        {{ trans("legal.back_home") }}
                    </span>
                </Link>

                <div class="mt-10 max-w-3xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-kitb-teal-200 bg-kitb-teal-50 px-3.5 py-1.5 text-xs font-semibold text-kitb-teal-700 dark:border-kitb-teal-300/20 dark:bg-kitb-teal-300/10 dark:text-kitb-teal-300"
                    >
                        <ShieldCheck class="size-4" />

                        <span>
                            {{ trans("legal.privacy.badge") }}
                        </span>
                    </div>

                    <h1
                        class="mt-5 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl lg:text-5xl dark:text-white"
                    >
                        {{ trans("legal.privacy.title") }}
                    </h1>

                    <p
                        class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8 dark:text-white/60"
                    >
                        {{ trans("legal.privacy.description") }}
                    </p>

                    <div
                        class="mt-6 inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-white/50"
                    >
                        {{ trans("legal.privacy.updated") }}
                    </div>
                </div>
            </div>
        </section>

        <!-- Content -->
        <main
            class="mx-auto w-full max-w-[1200px] px-5 py-10 sm:px-6 sm:py-14 lg:px-8 lg:py-16"
        >
            <div
                class="grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)] lg:gap-12"
            >
                <!-- Sidebar -->
                <aside class="hidden lg:block">
                    <div class="sticky top-8">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-white/35"
                        >
                            {{ trans("legal.privacy.contents") }}
                        </p>

                        <nav class="mt-4 space-y-1.5">
                            <a
                                v-for="section in sections"
                                :key="section.number"
                                :href="`#privacy-section-${section.number}`"
                                class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-500 transition-colors hover:bg-white hover:text-kitb-teal-600 dark:text-white/50 dark:hover:bg-white/5 dark:hover:text-kitb-teal-300"
                            >
                                <span
                                    class="font-mono text-[11px] text-slate-300 group-hover:text-kitb-teal-500 dark:text-white/20"
                                >
                                    {{ section.number }}
                                </span>

                                <span class="truncate">
                                    {{ section.title }}
                                </span>
                            </a>
                        </nav>
                    </div>
                </aside>

                <!-- Article -->
                <article
                    class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900"
                >
                    <div
                        class="border-b border-slate-200 px-5 py-6 sm:px-8 dark:border-white/10"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-kitb-teal-50 text-kitb-teal-600 dark:bg-kitb-teal-300/10 dark:text-kitb-teal-300"
                            >
                                <ShieldCheck class="size-5" />
                            </div>

                            <div>
                                <h2
                                    class="text-lg font-semibold text-slate-900 dark:text-white"
                                >
                                    {{
                                        trans(
                                            "legal.privacy.introduction_title",
                                        )
                                    }}
                                </h2>

                                <p
                                    class="mt-1 text-sm leading-6 text-slate-500 dark:text-white/50"
                                >
                                    {{ trans("legal.privacy.introduction") }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-200 dark:divide-white/10">
                        <section
                            v-for="section in sections"
                            :id="`privacy-section-${section.number}`"
                            :key="section.number"
                            class="scroll-mt-8 px-5 py-7 sm:px-8 sm:py-9"
                        >
                            <div class="flex gap-4">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-white/5 dark:text-white/60"
                                >
                                    <component
                                        :is="section.icon"
                                        class="size-4.5"
                                    />
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="font-mono text-[11px] font-medium text-kitb-teal-500 dark:text-kitb-teal-300"
                                        >
                                            {{ section.number }}
                                        </span>

                                        <h3
                                            class="text-base font-semibold text-slate-900 sm:text-lg dark:text-white"
                                        >
                                            {{ section.title }}
                                        </h3>
                                    </div>

                                    <p
                                        class="mt-3 text-sm leading-7 text-slate-600 sm:text-[15px] sm:leading-7 dark:text-white/60"
                                    >
                                        {{ section.content }}
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Contact -->
                    <div
                        class="border-t border-slate-200 bg-slate-50 px-5 py-7 sm:px-8 dark:border-white/10 dark:bg-white/[0.025]"
                    >
                        <div
                            class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-kitb-teal-50 text-kitb-teal-600 dark:bg-kitb-teal-300/10 dark:text-kitb-teal-300"
                                >
                                    <Mail class="size-4.5" />
                                </div>

                                <div>
                                    <h3
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            trans("legal.privacy.contact_title")
                                        }}
                                    </h3>

                                    <p
                                        class="mt-1 text-sm leading-6 text-slate-500 dark:text-white/50"
                                    >
                                        {{
                                            trans(
                                                "legal.privacy.contact_description",
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <Link
                                href="/kontak"
                                class="inline-flex shrink-0 items-center justify-center rounded-xl bg-kitb-navy-900 px-4 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:bg-kitb-navy-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100"
                            >
                                {{ trans("legal.privacy.contact_button") }}
                            </Link>
                        </div>
                    </div>
                </article>
            </div>
        </main>
    </div>
</template>
