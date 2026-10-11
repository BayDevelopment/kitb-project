<script setup lang="ts">
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";
import {
    ArrowLeft,
    ArrowUpRight,
    CheckCircle2,
    ClipboardList,
    FileText,
    Home,
    LockKeyhole,
    Mail,
    MessageSquareWarning,
    Phone,
    ShieldCheck,
} from "lucide-vue-next";

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
}

interface SharedPageProps {
    pengaturanKontak?: PengaturanKontak | null;
}

const page = usePage();

const sharedProps = computed(
    () => page.props as typeof page.props & SharedPageProps,
);

const pengaturan = computed(() => sharedProps.value.pengaturanKontak ?? null);

const emailHref = computed(() => {
    const email = pengaturan.value?.email?.trim();

    return email ? `mailto:${email}` : "#";
});

const phoneHref = computed(() => {
    const phone = pengaturan.value?.telepon?.trim();

    if (!phone) {
        return "#";
    }

    return `tel:${phone.replace(/[^\d+]/g, "")}`;
});

const whatsappHref = computed(() => {
    const whatsapp = pengaturan.value?.whatsapp?.trim();

    if (!whatsapp) {
        return "#";
    }

    const number = whatsapp.replace(/\D/g, "");

    return number ? `https://wa.me/${number}` : "#";
});

const sections = [
    {
        number: "01",
        icon: MessageSquareWarning,
        title: "legal.complaints.sections.purpose.title",
        content: "legal.complaints.sections.purpose.content",
    },
    {
        number: "02",
        icon: ClipboardList,
        title: "legal.complaints.sections.types.title",
        content: "legal.complaints.sections.types.content",
    },
    {
        number: "03",
        icon: FileText,
        title: "legal.complaints.sections.submission.title",
        content: "legal.complaints.sections.submission.content",
    },
    {
        number: "04",
        icon: CheckCircle2,
        title: "legal.complaints.sections.process.title",
        content: "legal.complaints.sections.process.content",
    },
    {
        number: "05",
        icon: LockKeyhole,
        title: "legal.complaints.sections.confidentiality.title",
        content: "legal.complaints.sections.confidentiality.content",
    },
];
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-white"
    >
        <!-- Header -->
        <header
            class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/90"
        >
            <div
                class="mx-auto flex h-16 w-full max-w-[1280px] items-center justify-between px-5 sm:px-6 lg:px-8"
            >
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-slate-600 transition-colors hover:text-kitb-teal-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-400 dark:text-slate-300 dark:hover:text-kitb-teal-300"
                >
                    <ArrowLeft class="size-4" aria-hidden="true" />
                    <span>{{ trans("legal.back_home") }}</span>
                </Link>

                <Link
                    href="/"
                    aria-label="KITB"
                    class="flex items-center gap-2"
                >
                    <img
                        src="/images/kitb-logo.png"
                        alt="KITB"
                        class="h-9 w-auto object-contain"
                    />
                </Link>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div
                class="absolute inset-0 bg-gradient-to-br from-kitb-navy-950 via-kitb-navy-900 to-slate-900"
            />

            <div
                class="absolute -right-32 -top-32 h-80 w-80 rounded-full bg-kitb-teal-400/10 blur-3xl"
            />

            <div
                class="absolute -bottom-40 -left-20 h-96 w-96 rounded-full bg-kitb-teal-500/10 blur-3xl"
            />

            <div
                class="relative mx-auto w-full max-w-[1280px] px-5 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24"
            >
                <div class="max-w-3xl">
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.16em] text-kitb-teal-300"
                    >
                        <MessageSquareWarning
                            class="size-3.5"
                            aria-hidden="true"
                        />

                        {{ trans("legal.complaints.badge") }}
                    </div>

                    <h1
                        class="text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl"
                    >
                        {{ trans("legal.complaints.title") }}
                    </h1>

                    <p
                        class="mt-6 max-w-2xl text-base leading-7 text-white/65 sm:text-lg sm:leading-8"
                    >
                        {{ trans("legal.complaints.description") }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Main -->
        <main
            class="mx-auto w-full max-w-[1280px] px-5 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20"
        >
            <div class="grid gap-10 lg:grid-cols-[280px_minmax(0,1fr)]">
                <!-- Sidebar -->
                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500"
                        >
                            {{ trans("legal.complaints.contents") }}
                        </p>

                        <nav class="mt-4 space-y-1.5">
                            <a
                                v-for="section in sections"
                                :key="section.number"
                                :href="`#complaint-${section.number}`"
                                class="group flex items-start gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-600 transition-colors hover:bg-slate-50 hover:text-kitb-teal-600 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-kitb-teal-300"
                            >
                                <span
                                    class="shrink-0 text-[10px] font-bold tracking-wider text-slate-400 transition-colors group-hover:text-kitb-teal-500"
                                >
                                    {{ section.number }}
                                </span>

                                <span class="leading-5">
                                    {{ trans(section.title) }}
                                </span>
                            </a>
                        </nav>
                    </div>
                </aside>

                <!-- Article -->
                <article class="min-w-0">
                    <!-- Introduction -->
                    <div
                        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:p-10 dark:border-white/10 dark:bg-slate-900"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-kitb-teal-50 text-kitb-teal-600 dark:bg-kitb-teal-400/10 dark:text-kitb-teal-300"
                            >
                                <ShieldCheck
                                    class="size-5"
                                    aria-hidden="true"
                                />
                            </div>

                            <div>
                                <h2
                                    class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{
                                        trans(
                                            "legal.complaints.introduction_title",
                                        )
                                    }}
                                </h2>

                                <p
                                    class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300"
                                >
                                    {{ trans("legal.complaints.introduction") }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Sections -->
                    <div class="mt-8 space-y-5">
                        <section
                            v-for="section in sections"
                            :id="`complaint-${section.number}`"
                            :key="section.number"
                            class="scroll-mt-28 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-white/10 dark:bg-slate-900"
                        >
                            <div class="flex gap-5">
                                <div
                                    class="hidden shrink-0 sm:flex sm:size-11 sm:items-center sm:justify-center sm:rounded-xl sm:bg-slate-50 sm:text-kitb-teal-600 dark:sm:bg-white/5 dark:sm:text-kitb-teal-300"
                                >
                                    <component
                                        :is="section.icon"
                                        class="size-5"
                                        aria-hidden="true"
                                    />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="text-[10px] font-bold tracking-[0.16em] text-kitb-teal-600 dark:text-kitb-teal-300"
                                        >
                                            {{ section.number }}
                                        </span>

                                        <div
                                            class="h-px flex-1 bg-slate-100 dark:bg-white/10"
                                        />
                                    </div>

                                    <h2
                                        class="mt-4 text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                                    >
                                        {{ trans(section.title) }}
                                    </h2>

                                    <p
                                        class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ trans(section.content) }}
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Contact CTA -->
                    <section
                        class="mt-8 overflow-hidden rounded-3xl bg-kitb-navy-900 p-6 text-white shadow-sm sm:p-8 lg:p-10"
                    >
                        <div
                            class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <div class="max-w-2xl">
                                <div
                                    class="inline-flex size-11 items-center justify-center rounded-xl bg-white/10 text-kitb-teal-300"
                                >
                                    <Mail class="size-5" aria-hidden="true" />
                                </div>

                                <h2
                                    class="mt-5 text-2xl font-semibold tracking-tight"
                                >
                                    {{
                                        trans("legal.complaints.contact_title")
                                    }}
                                </h2>

                                <p class="mt-3 text-sm leading-7 text-white/60">
                                    {{
                                        trans(
                                            "legal.complaints.contact_description",
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                class="flex flex-col gap-2.5 sm:flex-row lg:shrink-0 lg:flex-col"
                            >
                                <a
                                    v-if="pengaturan?.email"
                                    :href="emailHref"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-kitb-navy-900 transition-all hover:-translate-y-0.5 hover:bg-kitb-sand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-kitb-navy-900"
                                >
                                    <Mail class="size-4" aria-hidden="true" />
                                    <span>{{ pengaturan.email }}</span>
                                    <ArrowUpRight
                                        class="size-4"
                                        aria-hidden="true"
                                    />
                                </a>

                                <a
                                    v-if="pengaturan?.whatsapp"
                                    :href="whatsappHref"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300"
                                >
                                    <Phone class="size-4" aria-hidden="true" />
                                    <span>{{ pengaturan.whatsapp }}</span>
                                </a>

                                <a
                                    v-if="
                                        !pengaturan?.email &&
                                        !pengaturan?.whatsapp &&
                                        pengaturan?.telepon
                                    "
                                    :href="phoneHref"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-kitb-navy-900 transition-all hover:-translate-y-0.5 hover:bg-kitb-sand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-kitb-navy-900"
                                >
                                    <Phone class="size-4" aria-hidden="true" />
                                    <span>{{ pengaturan.telepon }}</span>
                                    <ArrowUpRight
                                        class="size-4"
                                        aria-hidden="true"
                                    />
                                </a>
                            </div>
                        </div>
                    </section>

                    <!-- Back Home -->
                    <div class="mt-8 flex justify-center">
                        <Link
                            href="/"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-slate-300 hover:text-kitb-teal-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-kitb-teal-400 dark:border-white/10 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-white/20 dark:hover:text-kitb-teal-300"
                        >
                            <Home class="size-4" aria-hidden="true" />
                            <span>{{ trans("legal.back_home") }}</span>
                        </Link>
                    </div>
                </article>
            </div>
        </main>
    </div>
</template>
