<script setup lang="ts">
import { Form, Head, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

import { Button } from "@/components/ui/button";
import { Spinner } from "@/components/ui/spinner";
import { logout } from "@/routes";
import { send } from "@/routes/verification";

defineOptions({
    layout: {
        title: "Verifikasi Email",
        description:
            "Verifikasi alamat email Anda untuk melanjutkan ke akun Anda.",
    },
});

defineProps<{
    status?: string;
}>();

const page = usePage();

const user = computed(
    () =>
        page.props.auth?.user as
            | {
                  name?: string;
                  email?: string;
              }
            | undefined,
);

const userName = computed(() => user.value?.name || "Pengguna");
const userEmail = computed(() => user.value?.email || "");
</script>

<template>
    <Head title="Verifikasi Email" />

    <div
        class="relative flex min-h-[calc(100vh-4rem)] items-center justify-center overflow-hidden px-4 py-10 sm:px-6"
    >
        <!-- =========================================================
            BACKGROUND
        ========================================================== -->
        <div
            class="pointer-events-none absolute inset-0 -z-20 overflow-hidden"
            aria-hidden="true"
        >
            <!-- Subtle grid -->
            <div
                class="absolute inset-0 bg-[linear-gradient(to_right,rgba(0,0,0,0.035)_1px,transparent_1px),linear-gradient(to_bottom,rgba(0,0,0,0.035)_1px,transparent_1px)] bg-[size:42px_42px] dark:bg-[linear-gradient(to_right,rgba(255,255,255,0.035)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.035)_1px,transparent_1px)]"
            />

            <!-- Large top-left blob -->
            <div
                class="verify-blob absolute -left-40 -top-40 size-80 rounded-full bg-gradient-to-br from-blue-400/25 via-indigo-400/15 to-transparent blur-3xl sm:size-[30rem]"
            />

            <!-- Large bottom-right blob -->
            <div
                class="verify-blob-delayed absolute -bottom-48 -right-40 size-96 rounded-full bg-gradient-to-tl from-sky-400/20 via-blue-400/10 to-transparent blur-3xl sm:size-[32rem]"
            />

            <!-- Center glow -->
            <div
                class="verify-blob-center absolute left-1/2 top-[42%] size-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-indigo-400/10 blur-3xl sm:size-80"
            />

            <!-- Right cyan blob -->
            <div
                class="verify-blob-extra absolute -right-24 top-[18%] size-60 rounded-full bg-gradient-to-br from-cyan-400/10 to-sky-400/5 blur-3xl sm:size-72"
            />

            <!-- Bottom blue glow -->
            <div
                class="verify-blob-bottom absolute -bottom-24 left-[18%] size-52 rounded-full bg-blue-500/5 blur-3xl sm:size-64"
            />
        </div>

        <!-- =========================================================
            CONTENT
        ========================================================== -->
        <div class="relative w-full max-w-md">
            <!-- Outer glow -->
            <div
                class="absolute -inset-1 rounded-[2rem] bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-sky-500/10 blur-xl"
                aria-hidden="true"
            />

            <!-- Card -->
            <div
                class="relative overflow-hidden rounded-[1.75rem] border border-black/5 bg-white/85 shadow-[0_24px_80px_-30px_rgba(0,0,0,0.3)] backdrop-blur-xl dark:border-white/10 dark:bg-[#161615]/90"
            >
                <!-- Top accent -->
                <div
                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-600 via-indigo-500 to-sky-400"
                    aria-hidden="true"
                />

                <div class="px-6 py-8 sm:px-9 sm:py-10">
                    <!-- =================================================
                        ICON
                    ================================================== -->
                    <div class="mb-6 flex justify-center">
                        <div class="relative">
                            <!-- Icon glow -->
                            <div
                                class="absolute inset-0 scale-125 rounded-full bg-blue-500/10 blur-2xl"
                            />

                            <!-- Icon box -->
                            <div
                                class="relative flex size-20 items-center justify-center rounded-[1.35rem] border border-blue-500/10 bg-gradient-to-br from-blue-50 via-indigo-50 to-sky-50 shadow-sm dark:border-blue-400/10 dark:from-blue-500/10 dark:via-indigo-500/10 dark:to-sky-500/5"
                            >
                                <svg
                                    class="size-10 text-blue-600 dark:text-blue-400"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect
                                        width="20"
                                        height="16"
                                        x="2"
                                        y="4"
                                        rx="2"
                                    />
                                    <path
                                        d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"
                                    />
                                </svg>
                            </div>

                            <!-- Check badge -->
                            <div
                                class="absolute -right-1 -top-1 flex size-6 items-center justify-center rounded-full border-2 border-white bg-blue-600 text-white shadow-md dark:border-[#161615]"
                            >
                                <svg
                                    class="size-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="m5 12 4 4L19 6" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- =================================================
                        HEADING
                    ================================================== -->
                    <div class="text-center">
                        <p
                            class="mb-2 text-[11px] font-bold uppercase tracking-[0.22em] text-blue-600 dark:text-blue-400"
                        >
                            Account Security
                        </p>

                        <h1
                            class="text-2xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC] sm:text-3xl"
                        >
                            Verifikasi Email Anda
                        </h1>

                        <p
                            class="mx-auto mt-3 max-w-sm text-sm leading-6 text-muted-foreground"
                        >
                            Selangkah lagi untuk mengaktifkan akun Anda. Silakan
                            periksa email dan klik tautan verifikasi yang telah
                            kami kirimkan.
                        </p>
                    </div>

                    <!-- =================================================
                        EMAIL INFO
                    ================================================== -->
                    <div
                        class="mt-7 rounded-2xl border border-blue-500/10 bg-blue-50/70 px-4 py-4 dark:border-blue-400/10 dark:bg-blue-500/5"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-white/5 dark:text-blue-400"
                            >
                                <svg
                                    class="size-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2Z"
                                    />
                                    <path d="m22 6-10 7L2 6" />
                                </svg>
                            </div>

                            <div class="min-w-0 text-left">
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Email terdaftar
                                </p>

                                <p
                                    class="truncate text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                                >
                                    {{ userEmail || "Alamat email Anda" }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- =================================================
                        SUCCESS MESSAGE
                    ================================================== -->
                    <div
                        v-if="status === 'verification-link-sent'"
                        class="mt-5 flex items-start gap-3 rounded-2xl border border-emerald-500/15 bg-emerald-50 px-4 py-3.5 text-left dark:border-emerald-400/10 dark:bg-emerald-500/5"
                        role="status"
                        aria-live="polite"
                    >
                        <div
                            class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400"
                        >
                            <svg
                                class="size-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </div>

                        <div>
                            <p
                                class="text-sm font-semibold text-emerald-700 dark:text-emerald-400"
                            >
                                Email verifikasi dikirim ulang
                            </p>

                            <p
                                class="mt-0.5 text-xs leading-5 text-emerald-700/80 dark:text-emerald-400/70"
                            >
                                Silakan periksa kotak masuk Anda. Jangan lupa
                                cek folder spam atau junk.
                            </p>
                        </div>
                    </div>

                    <!-- =================================================
                        ACTIONS
                    ================================================== -->
                    <Form
                        v-bind="send.form()"
                        class="mt-7 space-y-3"
                        v-slot="{ processing }"
                    >
                        <!-- Resend -->
                        <Button
                            type="submit"
                            :disabled="processing"
                            class="h-11 w-full rounded-xl bg-blue-600 font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md disabled:pointer-events-none disabled:opacity-60 dark:bg-blue-600 dark:hover:bg-blue-500"
                        >
                            <Spinner v-if="processing" class="mr-2" />

                            <svg
                                v-else
                                class="mr-2 size-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path
                                    d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2-2-2Z"
                                />
                                <path d="m22 6-10 7L2 6" />
                            </svg>

                            {{
                                processing
                                    ? "Mengirim..."
                                    : "Kirim Ulang Email Verifikasi"
                            }}
                        </Button>

                        <!-- =================================================
                            LOGOUT — POST /logout
                        ================================================== -->
                        <Form
                            v-bind="logout.form()"
                            v-slot="{ processing: logoutProcessing }"
                        >
                            <button
                                type="submit"
                                :disabled="logoutProcessing"
                                class="flex h-11 w-full items-center justify-center rounded-xl border border-black/8 bg-transparent text-sm font-medium text-muted-foreground transition-all duration-200 hover:bg-black/[0.03] hover:text-[#1b1b18] disabled:pointer-events-none disabled:opacity-60 dark:border-white/10 dark:hover:bg-white/[0.04] dark:hover:text-[#EDEDEC]"
                            >
                                <Spinner v-if="logoutProcessing" class="mr-2" />

                                <svg
                                    v-else
                                    class="mr-2 size-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"
                                    />
                                    <path d="m16 17 5-5-5-5" />
                                    <path d="M21 12H9" />
                                </svg>

                                {{
                                    logoutProcessing
                                        ? "Keluar..."
                                        : "Keluar dari Akun"
                                }}
                            </button>
                        </Form>
                    </Form>

                    <!-- =================================================
                        HELP
                    ================================================== -->
                    <div
                        class="mt-7 border-t border-black/5 pt-5 dark:border-white/10"
                    >
                        <p
                            class="text-center text-xs leading-5 text-muted-foreground"
                        >
                            Tidak menerima email?

                            <span
                                class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                Periksa folder spam
                            </span>

                            atau kirim ulang email verifikasi.
                        </p>
                    </div>
                </div>

                <!-- Bottom decoration -->
                <div
                    class="h-1 bg-gradient-to-r from-transparent via-blue-500/20 to-transparent"
                    aria-hidden="true"
                />
            </div>

            <!-- Greeting -->
            <p class="mt-5 text-center text-xs text-muted-foreground">
                Halo, {{ userName }}. Terima kasih telah bergabung.
            </p>
        </div>
    </div>
</template>

<style scoped>
.verify-blob {
    animation: verify-blob-in 1.2s ease-out both;
}

.verify-blob-delayed {
    animation: verify-blob-in-delayed 1.5s ease-out 0.15s both;
}

.verify-blob-center {
    animation: verify-blob-pulse 7s ease-in-out 0.4s infinite;
}

.verify-blob-extra {
    animation: verify-blob-float 8s ease-in-out 0.6s infinite;
}

.verify-blob-bottom {
    animation: verify-blob-float-reverse 9s ease-in-out 0.8s infinite;
}

@keyframes verify-blob-in {
    from {
        opacity: 0;
        transform: scale(0.78) translate3d(-20px, -20px, 0);
    }

    to {
        opacity: 1;
        transform: scale(1) translate3d(0, 0, 0);
    }
}

@keyframes verify-blob-in-delayed {
    from {
        opacity: 0;
        transform: scale(0.8) translate3d(20px, 20px, 0);
    }

    to {
        opacity: 1;
        transform: scale(1) translate3d(0, 0, 0);
    }
}

@keyframes verify-blob-pulse {
    0%,
    100% {
        opacity: 0.3;
        transform: translate(-50%, -50%) scale(1);
    }

    50% {
        opacity: 0.55;
        transform: translate(-50%, -50%) scale(1.16);
    }
}

@keyframes verify-blob-float {
    0%,
    100% {
        opacity: 0.28;
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        opacity: 0.5;
        transform: translate3d(-20px, 14px, 0) scale(1.08);
    }
}

@keyframes verify-blob-float-reverse {
    0%,
    100% {
        opacity: 0.2;
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        opacity: 0.4;
        transform: translate3d(22px, -12px, 0) scale(1.1);
    }
}

@media (prefers-reduced-motion: reduce) {
    .verify-blob,
    .verify-blob-delayed,
    .verify-blob-center,
    .verify-blob-extra,
    .verify-blob-bottom {
        animation: none;
    }
}
</style>
