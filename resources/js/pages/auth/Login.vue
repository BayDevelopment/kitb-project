<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'
import { store } from '@/routes/login'
import { request } from '@/routes/password'


defineProps<{
    status?: string
    canResetPassword: boolean
}>()
</script>

<template>
    <Head title="Login" />

    <div class="relative min-h-screen w-full overflow-hidden bg-[#f8fafc]">

        <!-- =====================================================
             BACKGROUND
        ====================================================== -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">

            <!-- Navy blob kiri atas -->
            <div
                class="absolute -left-[260px] -top-[260px]
                       h-[680px] w-[680px]
                       rounded-full
                       bg-[#0b1f3a]"
            />

            <!-- Navy blob kanan bawah -->
            <div
                class="absolute -bottom-[300px] -right-[260px]
                       h-[720px] w-[720px]
                       rounded-full
                       bg-[#0b1f3a]"
            />

            <!-- Navy soft blob -->
            <div
                class="absolute right-[8%] top-[10%]
                       h-[280px] w-[280px]
                       rounded-full
                       bg-[#163b68]/10
                       blur-3xl"
            />

            <!-- White organic blob kiri -->
            <div
                class="absolute -left-[80px] bottom-[5%]
                       h-[350px] w-[500px]
                       rounded-[45%]
                       bg-white/80
                       blur-3xl"
            />

            <!-- White organic blob kanan -->
            <div
                class="absolute -right-[100px] top-[0]
                       h-[300px] w-[480px]
                       rounded-[45%]
                       bg-white/80
                       blur-3xl"
            />

            <!-- Lingkaran dekoratif -->
            <div
                class="absolute left-[7%] top-[45%]
                       h-24 w-24
                       rounded-full
                       bg-[#163b68]/90
                       shadow-xl shadow-[#0b1f3a]/20"
            />

            <div
                class="absolute left-[4%] top-[54%]
                       h-5 w-5
                       rounded-full
                       bg-[#0b1f3a]"
            />

            <div
                class="absolute right-[8%] top-[30%]
                       h-5 w-5
                       rounded-full
                       bg-[#0b1f3a]"
            />

            <!-- Garis dekoratif kiri -->
            <div
                class="absolute -left-[80px] top-[20%]
                       h-px w-[600px]
                       rotate-[135deg]
                       bg-white/50"
            />

            <!-- Garis dekoratif kanan -->
            <div
                class="absolute -right-[80px] bottom-[18%]
                       h-px w-[500px]
                       rotate-[145deg]
                       bg-white/50"
            />
        </div>

        <!-- =====================================================
             MAIN
        ====================================================== -->
        <main
            class="relative z-10 flex min-h-screen w-full
                   items-center justify-center
                   px-5 py-10 sm:px-8"
        >
            <div class="w-full max-w-lg">

                <!-- =================================================
                     LOGOS
                ================================================== -->
                <div
                    class="mb-8 flex items-center justify-center
                           gap-6 sm:gap-10"
                >

                    <!-- KITB -->
                    <div
                        class="flex h-20 w-32
                               items-center justify-center"
                    >
                        <img
                            src="/images/kitb-logo.png"
                            alt="Logo PT Kawasan Industri Tanjung Buton"
                            class="max-h-20 max-w-32
                                   object-contain"
                        />
                    </div>

                    <!-- Divider -->
                    <div
                        class="h-14 w-px
                               bg-[#0b1f3a]/20"
                    ></div>

                    <!-- Kabupaten Siak -->
                    <div
                        class="flex h-20 w-28
                               items-center justify-center"
                    >
                        <img
                            src="/images/siak-kabupaten.png"
                            alt="Logo Kabupaten Siak"
                            class="max-h-20 max-w-28
                                   object-contain"
                        />
                    </div>

                </div>

                <!-- =================================================
                     LOGIN CARD
                ================================================== -->
                <div
                    class="rounded-[28px]
                           border border-white/80
                           bg-white/95
                           p-7
                           shadow-[0_25px_70px_rgba(11,31,58,0.16)]
                           backdrop-blur-xl
                           sm:p-10"
                >

                    <!-- Heading -->
                    <div class="mb-8 text-center">

                        <h1
                            class="text-2xl font-bold
                                   tracking-tight
                                   text-[#0b1f3a]
                                   sm:text-3xl"
                        >
                            Welcome
                        </h1>

                        <p
                            class="mx-auto mt-2 max-w-sm
                                   text-sm leading-relaxed
                                   text-slate-500"
                        >
                            Masuk untuk mengelola website
                            PT Kawasan Industri Tanjung Buton
                        </p>

                    </div>

                    <!-- Status -->
                    <div
                        v-if="status"
                        class="mb-5 rounded-xl
                               bg-green-50
                               px-4 py-3
                               text-center
                               text-sm font-medium
                               text-green-600"
                    >
                        {{ status }}
                    </div>

                    <!-- =================================================
                         FORM
                    ================================================== -->
                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="flex flex-col"
                    >

                        <!-- EMAIL -->
                        <div class="grid gap-2">
                            <Label
                                for="email"
                                class="text-sm font-semibold
                                       text-[#0b1f3a]"
                            >
                                Email
                            </Label>

                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                v-focus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="email@example.com"
                                class="h-12 rounded-xl
                                       border-slate-200
                                       bg-white
                                       px-4
                                       shadow-sm
                                       transition
                                       focus:border-[#0b1f3a]
                                       focus:ring-[#0b1f3a]"
                            />

                            <InputError
                                :message="errors.email"
                            />
                        </div>

                        <!-- PASSWORD -->
                        <div class="mt-5 grid gap-2">

                            <div
                                class="flex items-center
                                       justify-between"
                            >
                                <Label
                                    for="password"
                                    class="text-sm font-semibold
                                           text-[#0b1f3a]"
                                >
                                    Password
                                </Label>

                                <TextLink
                                    v-if="canResetPassword"
                                    :href="request()"
                                    class="text-sm
                                           text-[#5278a8]
                                           transition
                                           hover:text-[#0b1f3a]"
                                    :tabindex="5"
                                >
                                    Lupa password?
                                </TextLink>
                            </div>

                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Password"
                                class="h-12 rounded-xl"
                            />

                            <InputError
                                :message="errors.password"
                            />

                        </div>

                        <!-- REMEMBER -->
                        <div class="mt-5 flex items-center">
                            <Label
                                for="remember"
                                class="flex cursor-pointer
                                       items-center gap-3
                                       text-sm text-slate-600"
                            >
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    :tabindex="3"
                                />

                                <span>
                                    Ingat saya
                                </span>
                            </Label>
                        </div>

                        <!-- BUTTON -->
                        <Button
                            type="submit"
                            class="mt-6 h-12 w-full
                                   rounded-xl
                                   bg-[#0b1f3a]
                                   font-semibold
                                   text-white
                                   shadow-lg
                                   shadow-[#0b1f3a]/20
                                   transition-all
                                   duration-200
                                   hover:bg-[#163b68]
                                   hover:shadow-xl
                                   hover:shadow-[#0b1f3a]/25"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                        >
                            <Spinner v-if="processing" />

                            <span v-else>
                                Masuk ke Dashboard
                            </span>
                        </Button>

                    </Form>
                </div>

                <!-- =================================================
                     FOOTER
                ================================================== -->
                <div class="mt-6 text-center">
                    <p
                        class="text-xs
                               text-slate-500/80"
                    >
                        © {{ new Date().getFullYear() }}
                        PT Kawasan Industri Tanjung Buton
                    </p>
                </div>

            </div>
        </main>
    </div>
</template>
