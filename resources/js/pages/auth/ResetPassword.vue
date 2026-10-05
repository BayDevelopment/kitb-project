<script setup lang="ts">
import { Form, Head } from "@inertiajs/vue3";
import { ref } from "vue";

import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";

import { update } from "@/routes/password";

const props = defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <Head title="Reset Password" />

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

                <!-- Reset Password Card -->
                <div
                    class="rounded-[28px] border border-white/80 bg-white/95 p-7 shadow-[0_25px_70px_rgba(11,31,58,0.16)] backdrop-blur-xl sm:p-10"
                >
                    <!-- Heading -->
                    <div class="mb-8 text-center">
                        <h1
                            class="text-2xl font-bold tracking-tight text-[#0b1f3a] sm:text-3xl"
                        >
                            Reset Password
                        </h1>

                        <p
                            class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-slate-500"
                        >
                            Silakan masukkan password baru Anda di bawah ini
                        </p>
                    </div>

                    <!-- Form -->
                    <Form
                        v-bind="update.form()"
                        :transform="(data) => ({ ...data, token, email })"
                        :reset-on-success="[
                            'password',
                            'password_confirmation',
                        ]"
                        v-slot="{ errors, processing }"
                        class="login-form flex flex-col"
                    >
                        <!-- Email -->
                        <div class="grid gap-2">
                            <Label
                                for="email"
                                class="text-sm font-semibold text-[#0b1f3a]"
                            >
                                Email
                            </Label>

                            <Input
                                id="email"
                                type="email"
                                name="email"
                                autocomplete="email"
                                v-model="inputEmail"
                                readonly
                                :tabindex="-1"
                            />

                            <InputError :message="errors.email" />
                        </div>

                        <!-- Password -->
                        <div class="mt-5 grid gap-2">
                            <Label
                                for="password"
                                class="text-sm font-semibold text-[#0b1f3a]"
                            >
                                Password Baru
                            </Label>

                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                v-focus
                                :tabindex="1"
                                autocomplete="new-password"
                                placeholder="Password baru"
                                :passwordrules="passwordRules"
                            />

                            <InputError :message="errors.password" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mt-5 grid gap-2">
                            <Label
                                for="password_confirmation"
                                class="text-sm font-semibold text-[#0b1f3a]"
                            >
                                Konfirmasi Password
                            </Label>

                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                :tabindex="2"
                                autocomplete="new-password"
                                placeholder="Ulangi password baru"
                                :passwordrules="passwordRules"
                            />

                            <InputError
                                :message="errors.password_confirmation"
                            />
                        </div>

                        <!-- Button -->
                        <Button
                            type="submit"
                            class="mt-6 h-12 w-full rounded-xl bg-[#0b1f3a] font-semibold text-white shadow-lg shadow-[#0b1f3a]/20 transition-all duration-200 hover:bg-[#163b68] hover:shadow-xl hover:shadow-[#0b1f3a]/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b1f3a] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                            :tabindex="3"
                            :disabled="processing"
                            data-test="reset-password-button"
                        >
                            <Spinner v-if="processing" />

                            <span v-else> Reset Password </span>
                        </Button>
                    </Form>
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

.login-form :deep(input[readonly]) {
    background-color: #f8fafc;
    color: #64748b;
    cursor: default;
}

.login-form :deep(input#password),
.login-form :deep(input#password_confirmation) {
    padding-right: 3rem;
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
