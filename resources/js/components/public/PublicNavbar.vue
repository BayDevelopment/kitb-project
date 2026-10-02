<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Link } from "@inertiajs/vue3";
import { ChevronDown, MapPin, Menu, Phone, X } from "lucide-vue-next";
import { kontakLink, navGroups, visitUrl } from "@/data/publicNavigation";

const mobileMenuOpen = ref(false);
const openMobileGroup = ref<string | null>(null);

const toggleMobileGroup = (label: string) => {
    openMobileGroup.value = openMobileGroup.value === label ? null : label;
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
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === "Escape" && mobileMenuOpen.value) {
        closeMobileMenu();
    }
};

const handleResize = () => {
    if (window.innerWidth >= 1024 && mobileMenuOpen.value) {
        closeMobileMenu();
    }
};

watch(mobileMenuOpen, (open) => {
    if (typeof document === "undefined") {
        return;
    }

    document.body.style.overflow = open ? "hidden" : "";
});

onMounted(() => {
    window.addEventListener("keydown", handleKeydown);
    window.addEventListener("resize", handleResize);
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeydown);
    window.removeEventListener("resize", handleResize);

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
                aria-label="Tutup menu"
                class="fixed inset-0 top-[env(safe-area-inset-top)] z-40 bg-slate-950/40 backdrop-blur-sm lg:hidden"
                @click="closeMobileMenu"
            />
        </Transition>

        <!-- Navbar -->
        <div class="relative z-50">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
                <nav
                    class="mx-auto flex h-[72px] max-w-[1440px] items-center justify-between rounded-b-2xl border-x border-b border-white/70 bg-white/95 px-4 shadow-lg shadow-slate-900/5 backdrop-blur-xl sm:px-5 lg:h-[78px] lg:rounded-b-3xl lg:px-6"
                    aria-label="Navigasi utama"
                >
                    <!-- Logo -->
                    <Link
                        href="/"
                        class="group flex shrink-0 items-center gap-2.5"
                        aria-label="KITB - Beranda"
                        @click="handleNavItemClick"
                    >
                        <div class="flex items-center gap-2">
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
                                class="mt-1 text-[9px] font-medium uppercase tracking-[0.16em] text-kitb-green-700"
                            >
                                Industrial Estate
                            </p>
                        </div>
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden items-center gap-1 lg:flex">
                        <div
                            v-for="group in navGroups"
                            :key="group.label"
                            class="group relative"
                        >
                            <button
                                type="button"
                                class="nav-trigger inline-flex items-center gap-1.5 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-700 transition-colors duration-200 hover:bg-kitb-sand-100 hover:text-kitb-green-800 focus:outline-none focus:ring-2 focus:ring-kitb-green-700/20"
                            >
                                {{ group.label }}

                                <ChevronDown
                                    class="size-3.5 transition-transform duration-200 group-hover:rotate-180"
                                />
                            </button>

                            <!-- Dropdown -->
                            <div
                                class="pointer-events-none invisible absolute left-1/2 top-full w-72 -translate-x-1/2 translate-y-2 pt-3 opacity-0 transition-all duration-200 group-hover:pointer-events-auto group-hover:visible group-hover:translate-y-0 group-hover:opacity-100"
                            >
                                <div
                                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-2 shadow-xl shadow-slate-900/10"
                                >
                                    <Link
                                        v-for="item in group.items"
                                        :key="item.label"
                                        :href="item.href"
                                        class="group/item flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition-colors duration-150 hover:bg-kitb-sand-50 hover:text-kitb-green-800"
                                    >
                                        <span class="min-w-0 truncate">
                                            {{ item.label }}
                                        </span>

                                        <span
                                            v-if="item.badge"
                                            class="shrink-0 rounded-full bg-kitb-green-700/10 px-2 py-0.5 text-[10px] font-semibold text-kitb-green-800"
                                        >
                                            {{ item.badge }}
                                        </span>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <Link
                            :href="kontakLink.href"
                            class="nav-trigger inline-flex items-center gap-1.5 rounded-xl px-3 py-2.5 text-[13px] font-medium text-slate-700 transition-colors duration-200 hover:bg-kitb-sand-100 hover:text-kitb-green-800 focus:outline-none focus:ring-2 focus:ring-kitb-green-700/20"
                        >
                            <Phone class="size-3.5" />
                            <span>{{ kontakLink.label }}</span>
                        </Link>
                    </div>

                    <!-- Desktop CTA -->
                    <div class="hidden lg:block">
                        <Link
                            :href="visitUrl"
                            class="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold"
                        >
                            <MapPin class="size-4" />
                            <span>Ajukan Kunjungan</span>
                        </Link>
                    </div>

                    <!-- Mobile Trigger -->
                    <button
                        type="button"
                        class="inline-flex size-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition-colors hover:bg-kitb-sand-50 focus:outline-none focus:ring-2 focus:ring-kitb-green-700/20 lg:hidden"
                        :aria-expanded="mobileMenuOpen"
                        aria-controls="public-mobile-menu"
                        :aria-label="
                            mobileMenuOpen
                                ? 'Tutup menu navigasi'
                                : 'Buka menu navigasi'
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
                        class="mx-auto max-w-[1440px] overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-900/10"
                    >
                        <div
                            class="max-h-[calc(100vh-110px)] overflow-y-auto p-3"
                        >
                            <div
                                v-for="group in navGroups"
                                :key="group.label"
                                class="border-b border-slate-100 last:border-b-0"
                            >
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-3.5 text-left text-sm font-semibold text-slate-800 transition-colors hover:bg-kitb-sand-50"
                                    :aria-expanded="
                                        openMobileGroup === group.label
                                    "
                                    @click="toggleMobileGroup(group.label)"
                                >
                                    <span>{{ group.label }}</span>

                                    <ChevronDown
                                        class="size-4 shrink-0 transition-transform duration-200"
                                        :class="{
                                            'rotate-180':
                                                openMobileGroup === group.label,
                                        }"
                                    />
                                </button>

                                <div
                                    v-show="openMobileGroup === group.label"
                                    class="pb-2 pl-2"
                                >
                                    <Link
                                        v-for="item in group.items"
                                        :key="item.label"
                                        :href="item.href"
                                        class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-600 transition-colors hover:bg-kitb-sand-50 hover:text-kitb-green-800"
                                        @click="handleNavItemClick"
                                    >
                                        <span>{{ item.label }}</span>

                                        <span
                                            v-if="item.badge"
                                            class="shrink-0 rounded-full bg-kitb-green-700/10 px-2 py-0.5 text-[10px] font-semibold text-kitb-green-800"
                                        >
                                            {{ item.badge }}
                                        </span>
                                    </Link>
                                </div>
                            </div>

                            <div class="mt-3 border-t border-slate-100 pt-3">
                                <Link
                                    :href="kontakLink.href"
                                    class="flex items-center rounded-xl px-3 py-3 text-sm font-semibold text-slate-800 transition-colors hover:bg-kitb-sand-50 hover:text-kitb-green-800"
                                    @click="handleNavItemClick"
                                >
                                    {{ kontakLink.label }}
                                </Link>

                                <Link
                                    :href="visitUrl"
                                    class="btn-primary mt-2 flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold"
                                    @click="handleNavItemClick"
                                >
                                    Ajukan Kunjungan
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

@media (prefers-reduced-motion: reduce) {
    .btn-primary,
    .mobile-backdrop-enter-active,
    .mobile-backdrop-leave-active,
    .mobile-panel-enter-active,
    .mobile-panel-leave-active,
    .icon-fade-enter-active,
    .icon-fade-leave-active {
        transition: none;
    }
}
</style>
