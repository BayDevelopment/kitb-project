<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Heading from "@/components/Heading.vue";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { useCurrentUrl } from "@/composables/useCurrentUrl";
import { toUrl } from "@/lib/utils";
import { edit as editAppearance } from "@/routes/appearance";
import { edit as editProfile } from "@/routes/profile";
import { edit as editSecurity } from "@/routes/security";

const sidebarNavItems = [
    {
        title: "Profile",
        href: editProfile(),
    },
    {
        title: "Security",
        href: editSecurity(),
    },
    {
        title: "Appearance",
        href: editAppearance(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div
        class="relative min-h-screen overflow-hidden bg-slate-50 transition-colors duration-300 dark:bg-[#07111f]"
    >
        <!-- =========================================================
             BACKGROUND DECORATION
        ========================================================== -->
        <div
            class="pointer-events-none absolute inset-0 z-0 overflow-hidden"
            aria-hidden="true"
        >
            <!-- Left blob -->
            <div
                class="blob-shape absolute -left-24 -top-32 h-96 w-96 rounded-full bg-gradient-to-br from-blue-400/30 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/25 dark:via-indigo-500/15 dark:to-transparent"
            />

            <!-- Right blob -->
            <div
                class="blob-shape-delayed absolute -right-20 top-0 h-80 w-80 rounded-full bg-gradient-to-tr from-sky-300/30 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/20 dark:via-blue-500/10 dark:to-transparent"
            />

            <!-- Bottom blob -->
            <div
                class="absolute -bottom-40 left-1/3 h-96 w-96 rounded-full bg-gradient-to-tr from-indigo-300/15 via-blue-300/10 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/5 dark:to-transparent"
            />

            <!-- Grid -->
            <div class="absolute inset-0 opacity-40 dark:opacity-20">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px]"
                />
            </div>
        </div>

        <!-- =========================================================
             MAIN CONTENT
        ========================================================== -->
        <div class="relative z-10 min-h-screen">
            <div
                class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-5 lg:px-6 lg:py-8 xl:px-8"
            >
                <!-- =====================================================
                     SETTINGS HEADER
                ====================================================== -->
                <div class="mb-8">
                    <div
                        class="mb-3 inline-flex items-center rounded-full border border-blue-200/70 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                    >
                        Account Settings
                    </div>

                    <Heading
                        title="Settings"
                        description="Manage your profile and account settings"
                    />
                </div>

                <!-- =====================================================
                     SETTINGS CONTENT
                ====================================================== -->
                <div
                    class="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-8"
                >
                    <!-- =================================================
                         SIDEBAR
                    ================================================== -->
                    <aside class="w-full shrink-0 lg:w-56 xl:w-60">
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-white/80 p-2 shadow-sm shadow-slate-200/50 backdrop-blur dark:border-slate-800 dark:bg-slate-900/70 dark:shadow-none"
                        >
                            <div
                                class="mb-2 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                            >
                                Settings
                            </div>

                            <nav
                                class="flex flex-col gap-1"
                                aria-label="Settings"
                            >
                                <Button
                                    v-for="item in sidebarNavItems"
                                    :key="toUrl(item.href)"
                                    variant="ghost"
                                    as-child
                                    :class="[
                                        'h-11 w-full justify-start rounded-xl px-3 text-sm font-medium transition-all duration-200',
                                        isCurrentOrParentUrl(item.href)
                                            ? 'bg-blue-50 text-blue-700 shadow-sm hover:bg-blue-50 dark:bg-blue-950/50 dark:text-blue-300 dark:hover:bg-blue-950/60'
                                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
                                    ]"
                                >
                                    <Link
                                        :href="item.href"
                                        class="flex w-full items-center gap-3"
                                    >
                                        <span>
                                            {{ item.title }}
                                        </span>
                                    </Link>
                                </Button>
                            </nav>
                        </div>
                    </aside>

                    <!-- =================================================
                         MOBILE SEPARATOR
                    ================================================== -->
                    <Separator class="lg:hidden dark:bg-slate-800" />

                    <!-- =================================================
                         PAGE CONTENT
                    ================================================== -->
                    <main class="min-w-0 flex-1">
                        <section class="w-full max-w-3xl space-y-6">
                            <slot />
                        </section>
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.blob-shape {
    animation: blob-float 12s ease-in-out infinite;
}

.blob-shape-delayed {
    animation: blob-float-delayed 15s ease-in-out infinite;
}

@keyframes blob-float {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(20px, 15px, 0) scale(1.05);
    }
}

@keyframes blob-float-delayed {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(-18px, 20px, 0) scale(1.08);
    }
}

@media (prefers-reduced-motion: reduce) {
    .blob-shape,
    .blob-shape-delayed {
        animation: none;
    }
}
</style>
