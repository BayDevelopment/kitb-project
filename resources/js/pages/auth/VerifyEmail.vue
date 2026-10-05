<script setup lang="ts">
import { Form, Head, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

import { Button } from "@/components/ui/button";
import { Spinner } from "@/components/ui/spinner";

import { logout } from "@/routes";
import { send } from "@/routes/verification";

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

    <div class="relative min-h-screen w-full overflow-hidden bg-[#f8fafc]">
        <!-- Background -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <!-- Navy blob kiri atas -->
            <div
                class="absolute -left-[260px] -top-[260px] h-[680px] w-[680px] rounded-full bg-[#0b1f3a]"
            />

            <!-- Navy blob kanan bawah -->
            <div
                class="absolute -bottom-[300px] -right-[260px] h-[720px] w-[720px] rounded-full bg-[#0b1f3a]"
            />

            <!-- Navy soft blob -->
            <div
                class="absolute right-[8%] top-[10%] h-[280px] w-[280px] rounded-full bg-[#163b68]/10 blur-3xl"
            />

            <!-- White organic blob kiri -->
            <div
                class="absolute -left-[80px] bottom-[5%] h-[350px] w-[500px] rounded-[45%] bg-white/80 blur-3xl"
            />

            <!-- White organic blob kanan -->
            <div
                class="absolute -right-[100px] top-0 h-[300px] w-[480px] rounded-[45%] bg-white/80 blur-3xl"
            />

            <!-- Lingkaran dekoratif -->
            <div
                class="absolute left-[7%] top-[45%] h-24 w-24 rounded-full bg-[#163b68]/90 shadow-xl shadow-[#0b1f3a]/20"
            />

            <div
                class="absolute left-[4%] top-[54%] h-5 w-5 rounded-full bg-[#0b1f3a]"
            />

            <div
                class="absolute right-[8%] top-[30%] h-5 w-5 rounded-full bg-[#0b1f3a]"
            />

            <!-- Garis dekoratif kiri -->
            <div
                class="absolute -left-[80px] top-[20%] h-px w-[600px] rotate-[135deg] bg-white/50"
            />

            <!-- Garis dekoratif kanan -->
            <div
                class="absolute -right-[80px] bottom-[18%] h-px w-[500px] rotate-[145deg] bg-white/50"
            />
        </div>

        <!-- Main -->
        <main
            class="relative z-10 flex min-h-screen w-full items-center justify-center px-5 py-10 sm:px-8"
        >
            <div class="w-full max-w-lg">
                <!-- Logos -->
                <div
                    class="mb-8 flex items-center justify-center gap-6 sm:gap-10"
                >
                    <!-- KITB -->
                    <div class="flex h-20 w-32 items-center justify-center">
                        <img
                            src="/images/kitb-logo.png"
                            alt="Logo PT Kawasan Industri Tanjung Buton"
                            class="max-h-20 max-w-32 object-contain"
                        />
                    </div>

                    <!-- Divider -->
                    <div class="h-14 w-px bg-[#0b1f3a]/20" aria-hidden="true" />

                    <!-- Kabupaten Siak -->
                    <div class="flex h-20 w-28 items-center justify-center">
                        <img
                            src="/images/siak-kabupaten.png"
                            alt="Logo Kabupaten Siak"
                            class="max-h-20 max-w-28 object-contain"
                        />
                    </div>
                </div>

                <!-- Verify Email Card -->
                <div
                    class="rounded-[28px] border border-white/80 bg-white/95 p-7 shadow-[0_25px_70px_rgba(11,31,58,0.16)] backdrop-blur-xl sm:p-10"
                >
                    <!-- Heading -->
                    <div class="mb-8 text-center">
                        <h1
                            class="text-2xl font-bold tracking-tight text-[#0b1f3a] sm:text-3xl"
                        >
                            Verifikasi Email
                        </h1>

                        <p
                            class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-slate-500"
                        >
                            Selangkah lagi untuk mengaktifkan akun Anda. Periksa
                            email dan klik tautan verifikasi yang telah kami
                            kirimkan.
                        </p>
                    </div>

                    <!-- Email info -->
                    <div
                        class="flex items-center gap-3 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-4 py-3.5"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#0b1f3a] text-white"
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

                        <div class="min-w-0 text-left">
                            <p class="text-xs font-medium text-slate-500">
                                Email terdaftar
                            </p>

                            <p
                                class="truncate text-sm font-semibold text-[#0b1f3a]"
                            >
                                {{ userEmail || "Alamat email Anda" }}
                            </p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div
                        v-if="status === 'verification-link-sent'"
                        role="status"
                        aria-live="polite"
                        class="mt-5 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-center text-sm font-medium text-green-600"
                    >
                        Email verifikasi telah dikirim ulang. Periksa kotak
                        masuk dan folder spam Anda.
                    </div>

                    <!-- Resend -->
                    <Form
                        v-bind="send.form()"
                        v-slot="{ processing }"
                        class="mt-6 flex flex-col"
                    >
                        <Button
                            type="submit"
                            class="h-12 w-full rounded-xl bg-[#0b1f3a] font-semibold text-white shadow-lg shadow-[#0b1f3a]/20 transition-all duration-200 hover:bg-[#163b68] hover:shadow-xl hover:shadow-[#0b1f3a]/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b1f3a] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                            :tabindex="1"
                            :disabled="processing"
                            data-test="resend-verification-button"
                        >
                            <Spinner v-if="processing" />

                            <span v-else> Kirim Ulang Email Verifikasi </span>
                        </Button>
                    </Form>

                    <!-- Logout -->
                    <Form
                        v-bind="logout.form()"
                        v-slot="{ processing }"
                        class="mt-3 flex flex-col"
                    >
                        <button
                            type="submit"
                            class="flex h-12 w-full items-center justify-center rounded-xl border border-[#e2e8f0] bg-white text-sm font-medium text-slate-600 transition-all duration-200 hover:border-[#cbd5e1] hover:bg-[#f8fafc] hover:text-[#0b1f3a] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b1f3a] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                            :tabindex="2"
                            :disabled="processing"
                            data-test="logout-button"
                        >
                            <Spinner v-if="processing" />

                            <span v-else> Keluar dari Akun </span>
                        </button>
                    </Form>

                    <!-- Help -->
                    <p
                        class="mt-6 text-center text-xs leading-relaxed text-slate-500"
                    >
                        Tidak menerima email? Periksa folder spam atau kirim
                        ulang email verifikasi.
                    </p>
                </div>

                <!-- Footer -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-slate-500/80">
                        Halo, {{ userName }}. © {{ new Date().getFullYear() }}
                        PT Kawasan Industri Tanjung Buton
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>
