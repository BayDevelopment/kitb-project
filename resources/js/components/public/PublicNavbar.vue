<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import { ChevronDown, MapPin, Menu, Phone, X } from "lucide-vue-next";
import { loadLanguageAsync, trans } from "laravel-vue-i18n";
import { currentLanguage } from "@/composables/useLocale";

import { kontakLink, navGroups, visitUrl } from "@/data/publicNavigation";

/*
|--------------------------------------------------------------------------
| Language
|--------------------------------------------------------------------------
*/

const languages = [
    {
        code: "id",
        label: "Indonesia",
        flag: "🇮🇩",
    },
    {
        code: "en",
        label: "English",
        flag: "🇬🇧",
    },
    {
        code: "zh",
        label: "中文",
        flag: "🇨🇳",
    },
] as const;

type LanguageCode = (typeof languages)[number]["code"];

const LANGUAGE_STORAGE_KEY = "kitb_locale";

const currentLanguageData = () => {
    return (
        languages.find((language) => language.code === currentLanguage.value) ??
        languages[0]
    );
};

/*
|--------------------------------------------------------------------------
| Language Switcher
|--------------------------------------------------------------------------
*/

const languageOpen = ref(false);
const isChangingLanguage = ref(false);

const toggleLanguage = () => {
    if (isChangingLanguage.value) {
        return;
    }

    languageOpen.value = !languageOpen.value;
};

const changeLanguage = async (locale: LanguageCode) => {
    if (isChangingLanguage.value) {
        return;
    }

    isChangingLanguage.value = true;

    try {
        /*
         * Locale yang dipakai navbar:
         *
         * id -> lang/php_id.json
         * en -> lang/php_en.json
         * zh -> lang/php_zh_CN.json
         *
         * Mapping file dilakukan di app.ts.
         */
        await loadLanguageAsync(locale);

        /*
         * Update state HANYA setelah translation
         * berhasil dimuat.
         */
        currentLanguage.value = locale;

        /*
         * Simpan satu locale saja.
         */
        window.localStorage.setItem(LANGUAGE_STORAGE_KEY, locale);

        /*
         * Update HTML lang.
         */
        document.documentElement.setAttribute(
            "lang",
            locale === "zh" ? "zh-CN" : locale,
        );

        languageOpen.value = false;
    } catch (error) {
        console.error(`[KITB] Failed to change language to "${locale}"`, error);
    } finally {
        isChangingLanguage.value = false;
    }
};

/*
|--------------------------------------------------------------------------
| Mobile Navigation
|--------------------------------------------------------------------------
*/

const mobileMenuOpen = ref(false);
const openMobileGroup = ref<string | null>(null);

const toggleMobileGroup = (key: string) => {
    openMobileGroup.value = openMobileGroup.value === key ? null : key;
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
    openMobileGroup.value = null;
};

const toggleMobileMenu = () => {
    if (mobileMenuOpen.value) {
        closeMobileMenu();
        return;
    }

    mobileMenuOpen.value = true;
};

const handleNavItemClick = () => {
    closeMobileMenu();
    languageOpen.value = false;
};

/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
*/

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key !== "Escape") {
        return;
    }

    if (languageOpen.value) {
        languageOpen.value = false;
        return;
    }

    if (mobileMenuOpen.value) {
        closeMobileMenu();
    }
};

const handleResize = () => {
    if (window.innerWidth >= 1024 && mobileMenuOpen.value) {
        closeMobileMenu();
    }

    if (window.innerWidth < 1024 && languageOpen.value) {
        languageOpen.value = false;
    }
};

const handleDocumentClick = (event: MouseEvent) => {
    const target = event.target as HTMLElement | null;

    if (!target?.closest("[data-language-switcher]")) {
        languageOpen.value = false;
    }
};

/*
|--------------------------------------------------------------------------
| Mobile Body Lock
|--------------------------------------------------------------------------
*/

watch(mobileMenuOpen, (open) => {
    if (typeof document === "undefined") {
        return;
    }

    document.body.style.overflow = open ? "hidden" : "";
});

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(async () => {
    /*
     * Hanya gunakan kitb_locale sebagai sumber locale.
     *
     * Kalau belum ada:
     * Indonesia menjadi default.
     */
    const savedLanguage = window.localStorage.getItem(LANGUAGE_STORAGE_KEY);

    const locale: LanguageCode =
        savedLanguage === "en" ? "en" : savedLanguage === "zh" ? "zh" : "id";

    try {
        /*
         * Load translation pertama kali.
         */
        await loadLanguageAsync(locale);

        currentLanguage.value = locale;

        document.documentElement.setAttribute(
            "lang",
            locale === "zh" ? "zh-CN" : locale,
        );

        /*
         * Pastikan storage selalu memiliki
         * locale yang valid.
         */
        window.localStorage.setItem(LANGUAGE_STORAGE_KEY, locale);
    } catch (error) {
        console.error(
            `[KITB] Failed to initialize language "${locale}"`,
            error,
        );

        /*
         * Fallback ke Indonesia.
         */
        try {
            await loadLanguageAsync("id");
        } catch (fallbackError) {
            console.error(
                "[KITB] Failed to load Indonesian fallback",
                fallbackError,
            );
        }

        currentLanguage.value = "id";

        window.localStorage.setItem(LANGUAGE_STORAGE_KEY, "id");

        document.documentElement.setAttribute("lang", "id");
    }

    /*
     * Browser events.
     */
    window.addEventListener("keydown", handleKeydown);

    window.addEventListener("resize", handleResize);

    document.addEventListener("click", handleDocumentClick);
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeydown);

    window.removeEventListener("resize", handleResize);

    document.removeEventListener("click", handleDocumentClick);

    if (typeof document !== "undefined") {
        document.body.style.overflow = "";
    }
});
</script>

<template>
    <header class="fixed inset-x-0 top-0 z-50 pt-[env(safe-area-inset-top)]">
        <!-- Backdrop Mobile -->
        <Transition name="mobile-backdrop">
            <button
                v-if="mobileMenuOpen"
                type="button"
                :aria-label="trans('navigation.close_menu')"
                class="fixed inset-0 top-[env(safe-area-inset-top)] z-40 bg-slate-950/40 backdrop-blur-sm lg:hidden"
                @click="closeMobileMenu"
            />
        </Transition>

        <!-- Navbar -->
        <div class="relative z-50">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
                <nav
                    class="mx-auto flex h-[72px] max-w-[1440px] items-center justify-between rounded-b-2xl border-x border-b border-white/70 bg-white/95 px-4 shadow-lg shadow-slate-900/5 backdrop-blur-xl dark:border-white/10 dark:bg-card/95 dark:shadow-black/30 sm:px-5 lg:h-[78px] lg:rounded-b-3xl lg:px-6"
                    :aria-label="trans('navigation.main_navigation')"
                >
                    <!-- Logo -->
                    <Link
                        href="/"
                        class="group flex shrink-0 items-center gap-2.5"
                        :aria-label="trans('navigation.kitb_home')"
                        @click="handleNavItemClick"
                    >
                        <div
                            class="flex items-center gap-2 dark:rounded-xl dark:bg-white dark:px-2.5 dark:py-1"
                        >
                            <img
                                src="/images/siak-kabupaten.png"
                                alt="Kabupaten Siak"
                                class="h-8 w-auto object-contain sm:h-9"
                            />

                            <div
                                class="h-8 w-px bg-slate-200 sm:h-9"
                                aria-hidden="true"
                            />

                            <img
                                src="/images/kitb-logo.png"
                                alt="KITB"
                                class="h-9 w-auto object-contain sm:h-10"
                            />
                        </div>

                        <div class="hidden sm:block">
                            <p
                                class="text-sm font-semibold leading-none tracking-tight text-kitb-ink-900"
                            >
                                Tanjung Buton
                            </p>

                            <p
                                class="mt-1 text-[9px] font-medium uppercase tracking-[0.16em] text-kitb-green-700 dark:text-kitb-teal-300"
                            >
                                Industrial Estate
                            </p>
                        </div>
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden items-center gap-1 lg:flex">
                        <div
                            v-for="group in navGroups"
                            :key="group.translationKey ?? group.label"
                            class="group relative"
                        >
                            <button
                                type="button"
                                class="nav-trigger inline-flex items-center gap-1.5 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-700 transition-colors duration-200 hover:bg-kitb-sand-100 hover:text-kitb-green-800 focus:outline-none focus:ring-2 focus:ring-kitb-green-700/20 dark:text-slate-200 dark:hover:bg-white/10 dark:hover:text-white dark:focus:ring-kitb-teal-300/30"
                            >
                                {{ trans(group.translationKey ?? group.label) }}

                                <ChevronDown
                                    class="size-3.5 transition-transform duration-200 group-hover:rotate-180"
                                />
                            </button>

                            <!-- Dropdown -->
                            <div
                                class="pointer-events-none invisible absolute left-1/2 top-full w-72 -translate-x-1/2 translate-y-2 pt-3 opacity-0 transition-all duration-200 group-hover:pointer-events-auto group-hover:visible group-hover:translate-y-0 group-hover:opacity-100"
                            >
                                <div
                                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-2 shadow-xl shadow-slate-900/10 dark:border-white/10 dark:bg-card dark:shadow-black/40"
                                >
                                    <Link
                                        v-for="item in group.items"
                                        :key="item.translationKey ?? item.label"
                                        :href="item.href"
                                        class="group/item flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition-colors duration-150 hover:bg-kitb-sand-50 hover:text-kitb-green-800 dark:text-slate-200 dark:hover:bg-white/10 dark:hover:text-white"
                                        @click="handleNavItemClick"
                                    >
                                        <span class="min-w-0 truncate">
                                            {{
                                                trans(
                                                    item.translationKey ??
                                                        item.label,
                                                )
                                            }}
                                        </span>

                                        <span
                                            v-if="item.badge"
                                            class="shrink-0 rounded-full bg-kitb-green-700/10 px-2 py-0.5 text-[10px] font-semibold text-kitb-green-800 dark:bg-kitb-teal-300/15 dark:text-kitb-teal-300"
                                        >
                                            {{
                                                item.badgeTranslationKey
                                                    ? trans(
                                                          item.badgeTranslationKey,
                                                      )
                                                    : item.badge
                                            }}
                                        </span>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Contact -->
                        <Link
                            :href="kontakLink.href"
                            class="nav-trigger inline-flex items-center gap-1.5 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-700 transition-colors duration-200 hover:bg-kitb-sand-100 hover:text-kitb-green-800 focus:outline-none focus:ring-2 focus:ring-kitb-green-700/20 dark:text-slate-200 dark:hover:bg-white/10 dark:hover:text-white dark:focus:ring-kitb-teal-300/30"
                            @click="handleNavItemClick"
                        >
                            <Phone class="size-3.5" />

                            <span>
                                {{
                                    trans(
                                        kontakLink.translationKey ??
                                            kontakLink.label,
                                    )
                                }}
                            </span>
                        </Link>
                    </div>

                    <!-- Desktop Right Actions -->
                    <div class="hidden items-center gap-2 lg:flex">
                        <!-- Desktop Language Switcher -->
                        <div class="relative" data-language-switcher>
                            <button
                                type="button"
                                :disabled="isChangingLanguage"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] font-medium text-slate-700 dark:border-white/15 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10 dark:hover:text-white dark:focus:ring-kitb-teal-300/30 transition-colors duration-200 hover:bg-kitb-sand-100 hover:text-kitb-green-800 focus:outline-none focus:ring-2 focus:ring-kitb-green-700/20 disabled:cursor-wait disabled:opacity-70"
                                :aria-expanded="languageOpen"
                                aria-haspopup="true"
                                :aria-label="trans('navigation.language')"
                                @click.stop="toggleLanguage"
                            >
                                <span class="text-base leading-none">
                                    {{ currentLanguageData().flag }}
                                </span>

                                <span>
                                    {{ currentLanguage.toUpperCase() }}
                                </span>

                                <ChevronDown
                                    class="size-3.5 transition-transform duration-200"
                                    :class="{
                                        'rotate-180': languageOpen,
                                    }"
                                />
                            </button>

                            <Transition name="language-dropdown">
                                <div
                                    v-if="languageOpen"
                                    class="absolute right-0 top-full z-[60] mt-2 w-48 overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-2 shadow-xl shadow-slate-900/10 dark:border-white/10 dark:bg-card dark:shadow-black/40"
                                >
                                    <p
                                        class="px-3 pb-2 pt-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400"
                                    >
                                        {{ trans("navigation.language") }}
                                    </p>

                                    <button
                                        v-for="language in languages"
                                        :key="language.code"
                                        type="button"
                                        :disabled="isChangingLanguage"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition-colors disabled:cursor-wait disabled:opacity-60"
                                        :class="
                                            currentLanguage === language.code
                                                ? 'bg-kitb-sand-100 font-semibold text-kitb-green-800 dark:bg-white/10 dark:text-white'
                                                : 'text-slate-700 hover:bg-kitb-sand-50 hover:text-kitb-green-800 dark:text-slate-200 dark:hover:bg-white/10 dark:hover:text-white'
                                        "
                                        :aria-current="
                                            currentLanguage === language.code
                                                ? 'true'
                                                : undefined
                                        "
                                        @click="changeLanguage(language.code)"
                                    >
                                        <span class="text-lg leading-none">
                                            {{ language.flag }}
                                        </span>

                                        <span class="flex-1">
                                            {{
                                                trans(
                                                    `navigation.language_${language.code}`,
                                                )
                                            }}
                                        </span>

                                        <span
                                            v-if="
                                                currentLanguage ===
                                                language.code
                                            "
                                            class="text-xs text-kitb-green-700 dark:text-kitb-teal-300"
                                            aria-hidden="true"
                                        >
                                            ✓
                                        </span>
                                    </button>
                                </div>
                            </Transition>
                        </div>

                        <!-- Desktop CTA -->
                        <Link
                            :href="visitUrl"
                            class="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold"
                            @click="handleNavItemClick"
                        >
                            <MapPin class="size-4" />

                            <span>
                                {{ trans("navigation.submit_visit") }}
                            </span>
                        </Link>
                    </div>

                    <!-- Mobile Trigger -->
                    <button
                        type="button"
                        class="inline-flex size-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition-colors hover:bg-kitb-sand-50 focus:outline-none focus:ring-2 focus:ring-kitb-green-700/20 lg:hidden dark:border-white/15 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10 dark:focus:ring-kitb-teal-300/30"
                        :aria-expanded="mobileMenuOpen"
                        aria-controls="public-mobile-menu"
                        :aria-label="
                            mobileMenuOpen
                                ? trans('navigation.close_navigation')
                                : trans('navigation.open_navigation')
                        "
                        @click="toggleMobileMenu"
                    >
                        <Transition name="icon-fade" mode="out-in">
                            <X
                                v-if="mobileMenuOpen"
                                key="close"
                                class="size-5"
                            />

                            <Menu v-else key="menu" class="size-5" />
                        </Transition>
                    </button>
                </nav>
            </div>

            <!-- Mobile Menu -->
            <Transition name="mobile-panel">
                <div
                    v-if="mobileMenuOpen"
                    id="public-mobile-menu"
                    class="absolute inset-x-0 top-[calc(72px+env(safe-area-inset-top))] z-50 px-4 sm:px-6 lg:hidden"
                >
                    <div
                        class="mx-auto max-w-[1440px] overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-900/10 dark:border-white/10 dark:bg-card dark:shadow-black/50"
                    >
                        <div
                            class="max-h-[calc(100vh-110px)] overflow-y-auto p-3"
                        >
                            <!-- Mobile Navigation Groups -->
                            <div
                                v-for="group in navGroups"
                                :key="group.translationKey ?? group.label"
                                class="border-b border-slate-100 last:border-b-0 dark:border-white/10"
                            >
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-3.5 text-left text-sm font-semibold text-slate-800 transition-colors hover:bg-kitb-sand-50 dark:text-slate-100 dark:hover:bg-white/10"
                                    :aria-expanded="
                                        openMobileGroup ===
                                        (group.translationKey ?? group.label)
                                    "
                                    @click="
                                        toggleMobileGroup(
                                            group.translationKey ?? group.label,
                                        )
                                    "
                                >
                                    <span>
                                        {{
                                            trans(
                                                group.translationKey ??
                                                    group.label,
                                            )
                                        }}
                                    </span>

                                    <ChevronDown
                                        class="size-4 shrink-0 transition-transform duration-200"
                                        :class="{
                                            'rotate-180':
                                                openMobileGroup ===
                                                (group.translationKey ??
                                                    group.label),
                                        }"
                                    />
                                </button>

                                <div
                                    v-show="
                                        openMobileGroup ===
                                        (group.translationKey ?? group.label)
                                    "
                                    class="pb-2 pl-2"
                                >
                                    <Link
                                        v-for="item in group.items"
                                        :key="item.translationKey ?? item.label"
                                        :href="item.href"
                                        class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-600 transition-colors hover:bg-kitb-sand-50 hover:text-kitb-green-800 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                                        @click="handleNavItemClick"
                                    >
                                        <span>
                                            {{
                                                trans(
                                                    item.translationKey ??
                                                        item.label,
                                                )
                                            }}
                                        </span>

                                        <span
                                            v-if="item.badge"
                                            class="shrink-0 rounded-full bg-kitb-green-700/10 px-2 py-0.5 text-[10px] font-semibold text-kitb-green-800 dark:bg-kitb-teal-300/15 dark:text-kitb-teal-300"
                                        >
                                            {{
                                                item.badgeTranslationKey
                                                    ? trans(
                                                          item.badgeTranslationKey,
                                                      )
                                                    : item.badge
                                            }}
                                        </span>
                                    </Link>
                                </div>
                            </div>

                            <!-- Mobile Actions -->
                            <div
                                class="mt-3 border-t border-slate-100 pt-3 dark:border-white/10"
                            >
                                <!-- Mobile Language -->
                                <div
                                    class="mb-2 rounded-2xl bg-slate-50 p-2 dark:bg-white/5"
                                >
                                    <p
                                        class="px-2 py-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400"
                                    >
                                        {{ trans("navigation.language") }}
                                    </p>

                                    <div class="grid grid-cols-3 gap-1">
                                        <button
                                            v-for="language in languages"
                                            :key="language.code"
                                            type="button"
                                            :disabled="isChangingLanguage"
                                            class="flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-xs transition-colors disabled:cursor-wait disabled:opacity-60"
                                            :class="
                                                currentLanguage ===
                                                language.code
                                                    ? 'bg-white font-semibold text-kitb-green-800 shadow-sm dark:bg-white/10 dark:text-white dark:shadow-none'
                                                    : 'text-slate-600 hover:bg-white hover:text-kitb-green-800 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white'
                                            "
                                            :aria-current="
                                                currentLanguage ===
                                                language.code
                                                    ? 'true'
                                                    : undefined
                                            "
                                            @click="
                                                changeLanguage(language.code)
                                            "
                                        >
                                            <span class="text-lg leading-none">
                                                {{ language.flag }}
                                            </span>

                                            <span>
                                                {{
                                                    language.code.toUpperCase()
                                                }}
                                            </span>

                                            <span class="text-[10px]">
                                                {{
                                                    trans(
                                                        `navigation.language_${language.code}`,
                                                    )
                                                }}
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Mobile Contact -->
                                <Link
                                    :href="kontakLink.href"
                                    class="flex items-center rounded-xl px-3 py-3 text-sm font-semibold text-slate-800 transition-colors hover:bg-kitb-sand-50 hover:text-kitb-green-800 dark:text-slate-100 dark:hover:bg-white/10 dark:hover:text-white"
                                    @click="handleNavItemClick"
                                >
                                    {{
                                        trans(
                                            kontakLink.translationKey ??
                                                kontakLink.label,
                                        )
                                    }}
                                </Link>

                                <!-- Mobile CTA -->
                                <Link
                                    :href="visitUrl"
                                    class="btn-primary mt-2 flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold"
                                    @click="handleNavItemClick"
                                >
                                    {{ trans("navigation.submit_visit") }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </header>
</template>

<style scoped>
.nav-trigger {
    position: relative;
}

.btn-primary {
    background: #163a70;
    color: white;
    box-shadow: 0 8px 20px rgba(22, 58, 112, 0.16);
    transition:
        transform 200ms ease,
        background-color 200ms ease,
        box-shadow 200ms ease;
}

.btn-primary:hover {
    background: #1f4c91;
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(22, 58, 112, 0.2);
}

.mobile-backdrop-enter-active,
.mobile-backdrop-leave-active {
    transition: opacity 200ms ease;
}

.mobile-backdrop-enter-from,
.mobile-backdrop-leave-to {
    opacity: 0;
}

.mobile-panel-enter-active,
.mobile-panel-leave-active {
    transition:
        opacity 200ms ease,
        transform 200ms ease;
}

.mobile-panel-enter-from,
.mobile-panel-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

.language-dropdown-enter-active,
.language-dropdown-leave-active {
    transition:
        opacity 160ms ease,
        transform 160ms ease;
}

.language-dropdown-enter-from,
.language-dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px) scale(0.98);
}

.icon-fade-enter-active,
.icon-fade-leave-active {
    transition:
        opacity 150ms ease,
        transform 150ms ease;
}

.icon-fade-enter-from {
    opacity: 0;
    transform: scale(0.8) rotate(-10deg);
}

.icon-fade-leave-to {
    opacity: 0;
    transform: scale(0.8) rotate(10deg);
}

:global(.dark) .btn-primary {
    background: #2e6fbf;
    box-shadow: 0 8px 20px rgba(46, 111, 191, 0.3);
}

:global(.dark) .btn-primary:hover {
    background: #4c82c8;
    box-shadow: 0 12px 24px rgba(46, 111, 191, 0.4);
}

@media (prefers-reduced-motion: reduce) {
    .btn-primary,
    .mobile-backdrop-enter-active,
    .mobile-backdrop-leave-active,
    .mobile-panel-enter-active,
    .mobile-panel-leave-active,
    .language-dropdown-enter-active,
    .language-dropdown-leave-active,
    .icon-fade-enter-active,
    .icon-fade-leave-active {
        transition: none;
    }
}
</style>
