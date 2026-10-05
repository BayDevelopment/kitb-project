<script setup lang="ts">
import { computed } from "vue";
import { Form, Head, Link, usePage } from "@inertiajs/vue3";

import ProfileController from "@/actions/App/Http/Controllers/Settings/ProfileController";
import DeleteUser from "@/components/DeleteUser.vue";
import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { edit } from "@/routes/profile";
import { send } from "@/routes/verification";

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: "Profile settings",
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

/*
|--------------------------------------------------------------------------
| Styling
|--------------------------------------------------------------------------
*/

const cardClass =
    "rounded-2xl border border-slate-200/80 bg-white/90 shadow-sm shadow-slate-200/50 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80 dark:shadow-none";

const inputClass =
    "mt-1 block min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500";

const labelClass =
    "mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300";
</script>

<template>
    <Head title="Profile settings" />

    <div class="space-y-6">
        <!-- =========================================================
             PROFILE HEADER
        ========================================================== -->
        <div>
            <div
                class="mb-2 inline-flex items-center gap-2 rounded-full border border-blue-200/70 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
            >
                <span
                    class="size-1.5 rounded-full bg-blue-500"
                    aria-hidden="true"
                />

                Account Settings
            </div>

            <h2
                class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white sm:text-3xl"
            >
                Profile
            </h2>

            <p
                class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400"
            >
                Update your name and email address.
            </p>
        </div>

        <!-- =========================================================
             PERSONAL INFORMATION
        ========================================================== -->
        <section :class="[cardClass, 'overflow-hidden']">
            <!-- Card Header -->
            <div
                class="border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="size-5"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"
                            />
                        </svg>
                    </div>

                    <div>
                        <h3
                            class="font-semibold text-slate-900 dark:text-white"
                        >
                            Personal Information
                        </h3>

                        <p
                            class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Informasi dasar akun kamu.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="p-5 sm:p-6">
                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <!-- Name -->
                    <div class="grid gap-2">
                        <Label for="name" :class="labelClass"> Name </Label>

                        <Input
                            id="name"
                            :class="inputClass"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />

                        <InputError class="mt-1" :message="errors.name" />
                    </div>

                    <!-- Email -->
                    <div class="grid gap-2">
                        <Label for="email" :class="labelClass">
                            Email address
                        </Label>

                        <Input
                            id="email"
                            type="email"
                            :class="inputClass"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />

                        <InputError class="mt-1" :message="errors.email" />
                    </div>

                    <!-- Email Verification -->
                    <div
                        v-if="
                            page.props.mustVerifyEmail &&
                            !user.email_verified_at
                        "
                        class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900/50 dark:bg-amber-950/20"
                    >
                        <p
                            class="text-sm leading-6 text-amber-800 dark:text-amber-300"
                        >
                            Your email address is unverified.

                            <Link
                                :href="send()"
                                as="button"
                                class="font-medium underline decoration-amber-300 underline-offset-4 transition hover:decoration-current dark:decoration-amber-700"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="
                                page.props.status === 'verification-link-sent'
                            "
                            class="mt-2 text-sm font-medium text-emerald-600 dark:text-emerald-400"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <!-- Save -->
                    <div
                        class="flex flex-col gap-3 border-t border-slate-100 pt-5 dark:border-slate-800 sm:flex-row sm:items-center"
                    >
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            class="min-h-11 rounded-xl bg-blue-600 px-5 font-medium text-white shadow-sm transition hover:bg-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500/40 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-blue-600 dark:hover:bg-blue-500"
                        >
                            <span
                                v-if="processing"
                                class="mr-2 size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            />

                            {{ processing ? "Saving..." : "Save Changes" }}
                        </Button>

                        <span
                            v-if="processing"
                            class="text-xs text-slate-400 dark:text-slate-500"
                        >
                            Saving your changes...
                        </span>
                    </div>
                </Form>
            </div>
        </section>

        <!-- =========================================================
             DELETE ACCOUNT
        ========================================================== -->
        <section
            class="overflow-hidden rounded-2xl border border-red-200/70 bg-white/90 shadow-sm shadow-slate-200/50 backdrop-blur dark:border-red-900/50 dark:bg-slate-900/80 dark:shadow-none"
        >
            <!-- Card Header -->
            <div
                class="border-b border-red-100 px-5 py-5 dark:border-red-900/40 sm:px-6"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="size-5"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 6h18M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6m-9 0 .75 14.25A1.5 1.5 0 0 0 8.247 21h7.506a1.5 1.5 0 0 0 1.497-.75L18 6M10 10v7m4-7v7"
                            />
                        </svg>
                    </div>

                    <div>
                        <h3
                            class="font-semibold text-red-700 dark:text-red-400"
                        >
                            Delete Account
                        </h3>

                        <p
                            class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Permanently remove your account and data.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Delete Component -->
            <div class="p-5 sm:p-6">
                <DeleteUser />
            </div>
        </section>
    </div>
</template>
