<script setup lang="ts">
import { Form, Head } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import InputError from "@/components/InputError.vue";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from "@/components/ui/input-otp";

import { store } from "@/routes/two-factor/login";
import type { TwoFactorConfigContent } from "@/types";

const showRecoveryInput = ref<boolean>(false);
const code = ref<string>("");

const authConfigContent = computed<TwoFactorConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: "Kode Pemulihan",
            description:
                "Konfirmasi akses akun Anda dengan memasukkan salah satu kode pemulihan darurat Anda.",
            buttonText: "masuk menggunakan kode autentikasi",
        };
    }

    return {
        title: "Kode Autentikasi",
        description:
            "Masukkan kode autentikasi dari aplikasi authenticator Anda.",
        buttonText: "masuk menggunakan kode pemulihan",
    };
});

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = "";
};
</script>

<template>
    <Head title="Autentikasi Dua Faktor" />

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

                <!-- Two Factor Card -->
                <div
                    class="rounded-[28px] border border-white/80 bg-white/95 p-7 shadow-[0_25px_70px_rgba(11,31,58,0.16)] backdrop-blur-xl sm:p-10"
                >
                    <!-- Heading -->
                    <div class="mb-8 text-center">
                        <h1
                            class="text-2xl font-bold tracking-tight text-[#0b1f3a] sm:text-3xl"
                        >
                            {{ authConfigContent.title }}
                        </h1>

                        <p
                            class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-slate-500"
                        >
                            {{ authConfigContent.description }}
                        </p>
                    </div>

                    <!-- Mode: kode autentikasi (OTP) -->
                    <template v-if="!showRecoveryInput">
                        <Form
                            v-bind="store.form()"
                            class="flex flex-col"
                            reset-on-error
                            @error="code = ''"
                            #default="{ errors, processing, clearErrors }"
                        >
                            <input type="hidden" name="code" :value="code" />

                            <div
                                class="flex flex-col items-center justify-center gap-3 text-center"
                            >
                                <div
                                    class="flex w-full items-center justify-center"
                                >
                                    <InputOTP
                                        id="otp"
                                        v-model="code"
                                        :maxlength="6"
                                        :disabled="processing"
                                        autofocus
                                    >
                                        <InputOTPGroup>
                                            <InputOTPSlot
                                                v-for="index in 6"
                                                :key="index"
                                                :index="index - 1"
                                                class="h-12 w-11 text-base font-semibold text-[#0b1f3a] sm:w-12"
                                            />
                                        </InputOTPGroup>
                                    </InputOTP>
                                </div>

                                <InputError :message="errors.code" />
                            </div>

                            <Button
                                type="submit"
                                class="mt-6 h-12 w-full rounded-xl bg-[#0b1f3a] font-semibold text-white shadow-lg shadow-[#0b1f3a]/20 transition-all duration-200 hover:bg-[#163b68] hover:shadow-xl hover:shadow-[#0b1f3a]/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b1f3a] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="processing"
                                data-test="two-factor-submit-button"
                            >
                                Lanjutkan
                            </Button>

                            <div
                                class="mt-6 text-center text-sm text-slate-500"
                            >
                                <span>atau Anda dapat </span>

                                <button
                                    type="button"
                                    class="text-[#5278a8] underline decoration-[#5278a8]/40 underline-offset-4 transition-colors duration-200 hover:text-[#0b1f3a] hover:decoration-[#0b1f3a]"
                                    @click="
                                        () => toggleRecoveryMode(clearErrors)
                                    "
                                >
                                    {{ authConfigContent.buttonText }}
                                </button>
                            </div>
                        </Form>
                    </template>

                    <!-- Mode: kode pemulihan -->
                    <template v-else>
                        <Form
                            v-bind="store.form()"
                            class="login-form flex flex-col"
                            reset-on-error
                            #default="{ errors, processing, clearErrors }"
                        >
                            <div class="grid gap-2">
                                <Input
                                    name="recovery_code"
                                    type="text"
                                    placeholder="Masukkan kode pemulihan"
                                    autocomplete="off"
                                    v-focus
                                    required
                                />

                                <InputError :message="errors.recovery_code" />
                            </div>

                            <Button
                                type="submit"
                                class="mt-6 h-12 w-full rounded-xl bg-[#0b1f3a] font-semibold text-white shadow-lg shadow-[#0b1f3a]/20 transition-all duration-200 hover:bg-[#163b68] hover:shadow-xl hover:shadow-[#0b1f3a]/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b1f3a] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="processing"
                                data-test="two-factor-recovery-button"
                            >
                                Lanjutkan
                            </Button>

                            <div
                                class="mt-6 text-center text-sm text-slate-500"
                            >
                                <span>atau Anda dapat </span>

                                <button
                                    type="button"
                                    class="text-[#5278a8] underline decoration-[#5278a8]/40 underline-offset-4 transition-colors duration-200 hover:text-[#0b1f3a] hover:decoration-[#0b1f3a]"
                                    @click="
                                        () => toggleRecoveryMode(clearErrors)
                                    "
                                >
                                    {{ authConfigContent.buttonText }}
                                </button>
                            </div>
                        </Form>
                    </template>
                </div>

                <!-- Footer -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-slate-500/80">
                        © {{ new Date().getFullYear() }}
                        PT Kawasan Industri Tanjung Buton
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.login-form :deep(input:not([type="checkbox"])) {
    width: 100%;
    height: 3rem;
    padding: 0 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background-color: #ffffff;
    color: #1e293b;
    font-family: "Poppins", ui-sans-serif, system-ui, sans-serif;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.25rem;
    outline: none;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition:
        border-color 180ms ease,
        box-shadow 180ms ease,
        background-color 180ms ease;
}

.login-form :deep(input:not([type="checkbox"])::placeholder) {
    color: #94a3b8;
    opacity: 1;
}

.login-form :deep(input:not([type="checkbox"]):hover) {
    border-color: #cbd5e1;
}

.login-form :deep(input:not([type="checkbox"]):focus) {
    border-color: #0b1f3a;
    box-shadow:
        0 0 0 3px rgba(11, 31, 58, 0.08),
        0 1px 2px rgba(11, 31, 58, 0.04);
}

.login-form :deep(input:not([type="checkbox"]):disabled) {
    cursor: not-allowed;
    background-color: #f8fafc;
    opacity: 0.7;
}

.login-form :deep(input:-webkit-autofill),
.login-form :deep(input:-webkit-autofill:hover),
.login-form :deep(input:-webkit-autofill:focus) {
    -webkit-text-fill-color: #1e293b;
    -webkit-box-shadow:
        0 0 0 1000px #ffffff inset,
        0 1px 2px rgba(15, 23, 42, 0.04);
    transition: background-color 9999s ease-in-out 0s;
}

@media (prefers-reduced-motion: reduce) {
    .login-form :deep(input:not([type="checkbox"])) {
        transition: none;
    }
}
</style>
