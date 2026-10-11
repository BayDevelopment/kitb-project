import { createInertiaApp } from "@inertiajs/vue3";
import { i18nVue } from "laravel-vue-i18n";
import type { DefineComponent } from "vue";
import { initializeTheme } from "@/composables/useAppearance";
import AppLayout from "@/layouts/AppLayout.vue";
import SettingsLayout from "@/layouts/settings/Layout.vue";
import { initializeFlashToast } from "@/lib/flashToast";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

const pages = import.meta.glob("./pages/**/*.vue") as Record<
    string,
    () => Promise<{ default: DefineComponent }>
>;

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: async (name) => {
        const path = `./pages/${name}.vue`;
        const page = pages[path];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        const module = await page();

        return module.default;
    },

    layout: (name) => {
        switch (true) {
            case name === "Welcome":
            case name === "Index":
            case name === "PrivacyPolicy":
            case name === "Complaints":
            case name === "auth/Login":
            case name === "auth/ForgotPassword":
            case name === "auth/ConfirmPassword":
            case name === "auth/ResetPassword":
            case name === "auth/TwoFactorChallenge":
            case name === "auth/VerifyEmail":
                return null;

            case name.startsWith("settings/"):
                return [AppLayout, SettingsLayout];

            case name.startsWith("admin/"):
                return AppLayout;

            default:
                return AppLayout;
        }
    },

    withApp: (app) => {
        const langs = import.meta.glob<{
            default: Record<string, string>;
        }>("../../lang/php_*.json");

        const localeFiles: Record<string, string> = {
            id: "../../lang/php_id.json",
            en: "../../lang/php_en.json",
            zh: "../../lang/php_zh_CN.json",
        };

        app.use(i18nVue, {
            lang: "id",

            resolve: async (lang) => {
                const normalized = String(lang)
                    .toLowerCase()
                    .replace(/_/g, "-");

                let locale: "id" | "en" | "zh";

                if (
                    normalized === "zh" ||
                    normalized === "zh-cn" ||
                    normalized === "cn"
                ) {
                    locale = "zh";
                } else if (normalized.startsWith("en")) {
                    locale = "en";
                } else {
                    locale = "id";
                }

                const path = localeFiles[locale];
                const loader = langs[path];

                if (!loader) {
                    throw new Error(
                        `Translation file not found for locale "${lang}": ${path}`,
                    );
                }

                const module = await loader();

                // laravel-vue-i18n membutuhkan object dengan property `default`
                return {
                    default: module.default,
                };
            },
        });

        app.directive("focus", {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },

    progress: {
        color: "#4B5563",
    },
});

initializeTheme();
initializeFlashToast();
