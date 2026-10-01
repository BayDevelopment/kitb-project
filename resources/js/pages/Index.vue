<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { Head } from "@inertiajs/vue3";

// Halaman ini adalah landing page publik (guest) — jangan pakai
// dashboard/sidebar layout, meskipun app.ts punya default layout global.
defineOptions({ layout: null });

const mobileMenuOpen = ref(false);
const openMobileGroup = ref(null);

/* ---------------- Fade-in on scroll (local directive) ---------------- */
const prefersReducedMotion =
    typeof window !== "undefined" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const vFadeIn = {
    mounted(el) {
        if (prefersReducedMotion) {
            el.classList.add("fade-in-visible");
            return;
        }

        el.classList.add("fade-in");

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        el.classList.add("fade-in-visible");
                        observer.unobserve(el);
                    }
                });
            },
            { threshold: 0.15, rootMargin: "0px 0px -60px 0px" },
        );

        observer.observe(el);
    },
};

/* ---------------- Count-up angka saat masuk viewport ---------------- */
function formatId(num, decimals = 0) {
    return num.toLocaleString("id-ID", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

const vCountUp = {
    mounted(el, binding) {
        const {
            target,
            decimals = 0,
            duration = 1400,
            delay = 0,
        } = binding.value || {};

        el.textContent = formatId(0, decimals);

        if (prefersReducedMotion || typeof target !== "number") {
            el.textContent = formatId(target ?? 0, decimals);
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    observer.unobserve(el);

                    setTimeout(() => {
                        const start = performance.now();

                        const tick = (now) => {
                            const progress = Math.min(
                                (now - start) / duration,
                                1,
                            );
                            const eased = 1 - Math.pow(1 - progress, 3);

                            el.textContent = formatId(target * eased, decimals);

                            if (progress < 1) {
                                requestAnimationFrame(tick);
                            } else {
                                el.textContent = formatId(target, decimals);
                            }
                        };

                        requestAnimationFrame(tick);
                    }, delay);
                });
            },
            { threshold: 0.4, rootMargin: "0px 0px -40px 0px" },
        );

        observer.observe(el);
    },
};

/* ---------------- Ajukan kunjungan lahan ---------------- */
// Tujuan semua tombol "Ajukan Kunjungan". Arahkan ke route form kunjungan
// yang dilindungi middleware auth (belum login -> otomatis ke login,
// lalu kembali ke sini lewat redirect()->intended()).
const visitUrl = "/kunjungan-lahan/buat";

/* ---------------- Statistik ---------------- */
const stats = [
    { target: 6070, decimals: 0, suffix: "Ha", label: "Wilayah pengembangan" },
    {
        target: 636,
        decimals: 0,
        suffix: "K",
        label: "Ton produksi sawit / tahun",
    },
    {
        target: 36,
        decimals: 0,
        suffix: "Mil",
        label: "Alur pelayaran ke Selat Malaka",
    },
    { target: 117, decimals: 0, suffix: "K+", label: "KK petani sawit aktif" },
];

/* ---------------- Visi & Misi ---------------- */
const misi = [
    {
        title: "Melampaui batas industri konvensional",
        desc: "Ekosistem kawasan industri terintegrasi penuh dengan infrastruktur maritim dan logistik global, mendorong hilirisasi komoditas unggulan daerah.",
    },
    {
        title: "Menuju masa depan berkelanjutan",
        desc: "Prinsip Green Industrial Estate melalui energi terbarukan, efisiensi air, dan pengelolaan limbah ramah lingkungan.",
    },
    {
        title: "Menciptakan nilai bersama",
        desc: "Kemudahan berbisnis bagi investor domestik dan internasional, menjadi motor pertumbuhan ekonomi dan lapangan kerja lokal.",
    },
    {
        title: "Tata kelola yang akuntabel",
        desc: "Praktik bisnis transparan, profesional, dan adaptif demi nilai optimal bagi pemegang saham dan pemangku kepentingan.",
    },
];

/* ---------------- Rute pelayaran ---------------- */
const routes = [
    {
        name: "Rute 1",
        path: "Buton Port – Dumai – Selat Malaka",
        distance: "148,8 km",
        time: "6j 30m",
    },
    {
        name: "Rute 2",
        path: "Buton Port – Selat Asam – Selat Malaka",
        distance: "112,2 km",
        time: "4j 41m",
    },
    {
        name: "Rute 3",
        path: "Buton Port – Selat Lalang – Selat Malaka",
        distance: "186,9 km",
        time: "7j 44m",
    },
];

/* ---------------- Ketersediaan lahan ---------------- */
const lahan = [
    {
        target: 6070.3,
        decimals: 1,
        unit: "Ha",
        label: "Total wilayah pengembangan KITB",
    },
    {
        target: 5192,
        decimals: 0,
        unit: "Ha",
        label: "Area yang telah dibebaskan",
    },
    {
        target: 600,
        decimals: 0,
        unit: "Ha",
        label: "Lahan hak yang tersertifikasi",
    },
];

/* ---------------- Pembangunan tahap 1 ---------------- */
const tahap1 = [
    {
        title: "Kawasan industri",
        desc: "Kavling industri, fasilitas power plant, perdagangan & jasa, hingga area perkantoran & sport center dalam satu estate layout terpadu.",
    },
    {
        title: "Kawasan pelabuhan",
        desc: "Area migas, CPO, dry bulk, kontainer & pergudangan, serta galangan kapal — dirancang untuk arus logistik ekspor-impor langsung.",
    },
    {
        title: "Pelabuhan Tanjung Buton",
        desc: "Dermaga aktif yang telah melayani kapal kargo & bulk carrier, terhubung langsung dengan jaringan jalan kawasan.",
    },
];

/* ---------------- Data peta tahapan pengembangan ---------------- */
const masterPlan = {
    judul: "Peta tahapan pengembangan & luas per zona",
    gambar_path: "/images/master-plan-kitb.jpeg",
    keterangan:
        "Development Phase Concept Plan Map, skala 1:65.360 (LPPM UIR).",
    total_luas_ha: 6070,
};

// Warna zona = legenda peta master plan, jangan diubah mengikuti tema.
const kawasanZones = [
    {
        kode: "phase-1",
        label: "Phase 1",
        luas: 340,
        warna: "#7cc4dc",
        catatan: null,
    },
    {
        kode: "phase-2",
        label: "Phase 2",
        luas: 723,
        warna: "#7b4a4f",
        catatan: null,
    },
    {
        kode: "phase-3",
        label: "Phase 3",
        luas: 481,
        warna: "#2f3f8f",
        catatan: null,
    },
    {
        kode: "phase-4",
        label: "Phase 4",
        luas: 344,
        warna: "#6bbf3b",
        catatan: null,
    },
    {
        kode: "phase-5",
        label: "Phase 5",
        luas: 472,
        warna: "#d9743b",
        catatan: null,
    },
    {
        kode: "phase-6",
        label: "Phase 6",
        luas: 993,
        warna: "#1f8f80",
        catatan: null,
    },
    {
        kode: "phase-7",
        label: "Phase 7",
        luas: 747,
        warna: "#e05fb0",
        catatan: null,
    },
    {
        kode: "supporting",
        label: "Supporting",
        luas: 1915,
        warna: "#8a8f2a",
        catatan: null,
    },
    {
        kode: "port",
        label: "Port",
        luas: 270,
        warna: "#b7e3ef",
        catatan: "Joint Venture",
    },
];

/* ---------------- Navigasi (dipakai navbar & footer) ---------------- */
const navGroups = [
    {
        label: "Profil Perusahaan",
        items: [
            { label: "Tentang Kami", href: "#tentang" },
            { label: "Visi, Misi & Nilai", href: "#visi-misi" },
            { label: "Latar Belakang", href: "#" },
            { label: "Struktur Perusahaan", href: "#" },
            { label: "Anak Usaha", href: "#" },
        ],
    },
    {
        label: "Kawasan Industri",
        items: [
            { label: "Master Plan KITB", href: "#kawasan" },
            { label: "Ketersediaan Lahan", href: "#kawasan" },
            { label: "Pembangunan Tahap 1", href: "#" },
            { label: "Kawasan Pelabuhan", href: "#" },
        ],
    },
    {
        label: "Hubungan Investor",
        items: [
            { label: "Ajukan Kunjungan Lahan", href: visitUrl },
            { label: "Peluang Investasi", href: "#" },
            { label: "Ease of Doing Business", href: "#" },
            { label: "Rute Pelayaran & Lokasi", href: "#lokasi" },
        ],
    },
    {
        label: "Pusat Informasi",
        items: [
            { label: "Berita", href: "#" },
            { label: "Galeri", href: "#" },
            { label: "Publikasi", href: "#" },
            { label: "Karier", href: "/karier", badge: "Join Us" },
        ],
    },
];

const kontakLink = { href: "#kontak", label: "Kontak" };

/* ---------------- Mobile navigation ---------------- */
function toggleMobileGroup(i) {
    openMobileGroup.value = openMobileGroup.value === i ? null : i;
}

function toggleMobileMenu() {
    mobileMenuOpen.value = !mobileMenuOpen.value;
    if (!mobileMenuOpen.value) openMobileGroup.value = null;
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
    openMobileGroup.value = null;
}

// Dipakai navbar desktop, navbar mobile, dan footer
function handleNavItemClick() {
    closeMobileMenu();
}

// Kunci scroll halaman saat menu terbuka
watch(mobileMenuOpen, (open) => {
    document.body.style.overflow = open ? "hidden" : "";
});

function onKeydown(e) {
    if (e.key !== "Escape") return;
    closeMobileMenu();
}

function onResize() {
    if (window.innerWidth >= 1024) closeMobileMenu();
}

onMounted(() => {
    window.addEventListener("keydown", onKeydown);
    window.addEventListener("resize", onResize);
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", onKeydown);
    window.removeEventListener("resize", onResize);
    document.body.style.overflow = "";
});

/* Ganti href dengan akun resmi KITB yang sebenarnya */
const socials = [
    { label: "Instagram", type: "instagram", href: "#" },
    { label: "TikTok", type: "tiktok", href: "#" },
    { label: "YouTube", type: "youtube", href: "#" },
];
</script>

<template>
    <Head
        title="PT. Kawasan Industri Tanjung Buton — Beyond Industry, Towards the Future"
    />

    <div
        class="kitb-landing antialiased bg-kitb-sand-50 text-kitb-ink-900 overflow-x-hidden"
    >
        <!-- ================= NAV ================= -->
        <header
            class="fixed top-0 left-0 right-0 z-50"
            style="
                padding-top: env(safe-area-inset-top, 0px);
                padding-left: env(safe-area-inset-left, 0px);
                padding-right: env(safe-area-inset-right, 0px);
            "
        >
            <!-- Backdrop menu mobile -->
            <Transition name="backdrop">
                <div
                    v-if="mobileMenuOpen"
                    class="lg:hidden fixed inset-0 bg-kitb-navy-900/50 backdrop-blur-[2px]"
                    aria-hidden="true"
                    @click="closeMobileMenu"
                />
            </Transition>

            <div
                class="relative backdrop-blur-md bg-kitb-sand-50/85 border-b border-black/5"
            >
                <nav
                    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 h-16 sm:h-20 flex items-center justify-between gap-3"
                >
                    <!-- LOGO -->
                    <a
                        href="#top"
                        class="flex items-center gap-2.5 min-w-0"
                        aria-label="KITB - Beranda"
                        @click="closeMobileMenu"
                    >
                        <img
                            src="/images/siak-kabupaten.png"
                            alt="Lambang Kabupaten Siak"
                            class="h-8 sm:h-9 w-auto"
                        />
                        <img
                            src="/images/kitb-logo.png"
                            alt="Logo KITB"
                            class="h-8 sm:h-9 w-auto"
                        />
                        <div
                            class="leading-tight hidden sm:block border-l border-black/10 pl-2.5 ml-0.5"
                        >
                            <div
                                class="text-[10px] tracking-wide text-black/50"
                            >
                                Tanjung Buton
                            </div>
                        </div>
                    </a>

                    <!-- DESKTOP NAVIGATION -->
                    <div
                        class="hidden lg:flex items-center gap-1 text-[14.5px] font-medium"
                    >
                        <div
                            v-for="group in navGroups"
                            :key="group.label"
                            class="group relative"
                        >
                            <button
                                type="button"
                                class="nav-trigger flex items-center gap-1.5 px-3.5 py-2 rounded-full"
                            >
                                {{ group.label }}
                                <svg
                                    width="10"
                                    height="6"
                                    viewBox="0 0 10 6"
                                    fill="none"
                                    class="mt-px transition-transform duration-200 group-hover:rotate-180"
                                >
                                    <path
                                        d="M1 1l4 4 4-4"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>

                            <div
                                class="absolute left-1/2 -translate-x-1/2 top-full pt-3 w-64 opacity-0 invisible translate-y-1 pointer-events-none transition-all duration-200 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto group-focus-within:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:pointer-events-auto"
                            >
                                <div
                                    class="rounded-2xl bg-kitb-surface shadow-xl border border-black/5 p-2 overflow-hidden"
                                >
                                    <div
                                        class="h-[3px] w-full bg-kitb-teal-500 rounded-full mb-1.5"
                                    />
                                    <a
                                        v-for="item in group.items"
                                        :key="item.label"
                                        :href="item.href || '#'"
                                        class="block px-3.5 py-2.5 rounded-xl text-[14px] text-kitb-ink-900/75 hover:bg-kitb-sand-100 hover:text-kitb-green-800 transition-colors"
                                        @click="
                                            handleNavItemClick(item, $event)
                                        "
                                    >
                                        {{ item.label }}
                                        <span
                                            v-if="item.badge"
                                            class="ml-2 inline-flex items-center rounded-full bg-kitb-teal-500/10 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-kitb-teal-700"
                                            >{{ item.badge }}</span
                                        >
                                    </a>
                                </div>
                            </div>
                        </div>

                        <a
                            :href="kontakLink.href"
                            class="nav-trigger px-3.5 py-2 rounded-full"
                        >
                            {{ kontakLink.label }}
                        </a>
                    </div>

                    <!-- DESKTOP CTA -->
                    <a
                        :href="visitUrl"
                        class="hidden lg:inline-flex items-center gap-2 text-[14px] font-medium text-white px-5 py-2.5 rounded-full btn-primary"
                    >
                        <svg
                            width="15"
                            height="15"
                            viewBox="0 0 16 16"
                            fill="none"
                        >
                            <rect
                                x="2"
                                y="3"
                                width="12"
                                height="11"
                                rx="2"
                                stroke="currentColor"
                                stroke-width="1.4"
                            />
                            <path
                                d="M2 7h12M5.5 1.5v3M10.5 1.5v3"
                                stroke="currentColor"
                                stroke-width="1.4"
                                stroke-linecap="round"
                            />
                        </svg>
                        Ajukan Kunjungan
                    </a>

                    <!-- HAMBURGER -->
                    <button
                        type="button"
                        class="lg:hidden relative w-11 h-11 -mr-2 flex items-center justify-center rounded-full active:bg-black/5"
                        :aria-label="
                            mobileMenuOpen ? 'Tutup menu' : 'Buka menu'
                        "
                        aria-controls="mobile-menu"
                        :aria-expanded="mobileMenuOpen"
                        @click="toggleMobileMenu"
                    >
                        <span
                            class="burger"
                            :class="{ 'is-open': mobileMenuOpen }"
                        >
                            <span /><span /><span />
                        </span>
                    </button>
                </nav>
            </div>

            <!-- ================= MOBILE MENU ================= -->
            <Transition name="menu-panel">
                <div
                    v-if="mobileMenuOpen"
                    id="mobile-menu"
                    class="lg:hidden absolute left-0 right-0 top-full bg-kitb-sand-50 border-b border-black/5 shadow-2xl rounded-b-3xl overflow-hidden"
                >
                    <div
                        class="mobile-scroll px-5 sm:px-8 pt-3 overflow-y-auto overscroll-contain"
                        style="
                            padding-bottom: calc(
                                1.5rem + env(safe-area-inset-bottom, 0px)
                            );
                        "
                    >
                        <div
                            v-for="(group, i) in navGroups"
                            :key="group.label"
                            class="m-item border-b border-black/5"
                            :style="{ '--i': i }"
                        >
                            <button
                                type="button"
                                class="w-full flex items-center justify-between py-4 text-[15.5px] font-medium text-kitb-green-900"
                                :aria-expanded="openMobileGroup === i"
                                @click="toggleMobileGroup(i)"
                            >
                                {{ group.label }}
                                <svg
                                    width="12"
                                    height="8"
                                    viewBox="0 0 12 8"
                                    fill="none"
                                    class="transition-transform duration-300"
                                    :class="{
                                        'rotate-180': openMobileGroup === i,
                                    }"
                                >
                                    <path
                                        d="M1 1l5 5 5-5"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>

                            <div
                                class="acc"
                                :class="{ 'is-open': openMobileGroup === i }"
                            >
                                <div
                                    class="acc-inner"
                                    :inert="openMobileGroup !== i"
                                >
                                    <div class="pb-3 pl-3 flex flex-col">
                                        <a
                                            v-for="item in group.items"
                                            :key="item.label"
                                            :href="item.href || '#'"
                                            class="py-2.5 text-[14.5px] text-kitb-ink-900/70 active:text-kitb-green-800"
                                            @click="
                                                handleNavItemClick(item, $event)
                                            "
                                        >
                                            {{ item.label }}
                                            <span
                                                v-if="item.badge"
                                                class="ml-2 inline-flex items-center rounded-full bg-kitb-teal-500/10 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-kitb-teal-700"
                                                >{{ item.badge }}</span
                                            >
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a
                            :href="kontakLink.href"
                            class="m-item block py-4 border-b border-black/5 text-[15.5px] font-medium text-kitb-green-900"
                            :style="{ '--i': navGroups.length }"
                            @click="closeMobileMenu"
                        >
                            {{ kontakLink.label }}
                        </a>

                        <a
                            :href="visitUrl"
                            class="m-item w-full text-white text-[15px] font-medium text-center py-3.5 rounded-full mt-5 btn-primary block"
                            :style="{ '--i': navGroups.length + 1 }"
                            @click="closeMobileMenu"
                        >
                            Ajukan Kunjungan
                        </a>
                    </div>
                </div>
            </Transition>
        </header>

        <!-- ================= HERO ================= -->
        <section
            id="top"
            class="relative pt-32 pb-20 sm:pt-40 md:pt-48 md:pb-36 hero-blob-field overflow-hidden"
        >
            <div
                class="blob blob-organic-1 drift-a"
                style="
                    width: 620px;
                    height: 620px;
                    top: -180px;
                    right: -220px;
                    background: radial-gradient(
                        circle at 35% 30%,
                        #7fa8e0,
                        #2e6fbf 45%,
                        #163a70 85%
                    );
                    opacity: 0.9;
                "
            />
            <div
                class="blob blob-organic-2 drift-b"
                style="
                    width: 340px;
                    height: 340px;
                    bottom: -120px;
                    right: 120px;
                    background: radial-gradient(
                        circle at 60% 40%,
                        #1f4c91,
                        #0b1f3f 80%
                    );
                    opacity: 0.85;
                "
            />

            <div class="max-w-7xl mx-auto px-5 sm:px-6 md:px-10 relative">
                <div class="max-w-2xl" v-fade-in>
                    <div
                        class="inline-flex items-center gap-2 text-[12.5px] sm:text-[13px] font-medium px-3.5 py-1.5 rounded-full mb-6 sm:mb-8 bg-kitb-green-700/10 text-kitb-green-800"
                    >
                        <span
                            class="w-1.5 h-1.5 rounded-full bg-kitb-teal-500 shrink-0"
                        />
                        Badan Usaha Milik Daerah &middot; Kabupaten Siak
                    </div>

                    <h1
                        class="font-display leading-[1.08] mb-6 sm:mb-7 text-kitb-green-900 font-bold"
                        style="font-size: clamp(2.1rem, 5.4vw, 4.3rem)"
                    >
                        Kawasan industri yang berdiri tepat di bibir Selat
                        Malaka
                    </h1>

                    <p
                        class="text-[16px] md:text-[18px] leading-relaxed max-w-lg mb-8 sm:mb-10 text-kitb-ink-900/70"
                    >
                        PT Kawasan Industri Tanjung Buton mengintegrasikan lahan
                        industri, pelabuhan laut dalam, dan hilirisasi komoditas
                        Riau menjadi satu ekosistem — di jalur pelayaran
                        tersibuk di dunia
                    </p>

                    <div
                        class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 sm:gap-4"
                    >
                        <a
                            :href="visitUrl"
                            class="w-full sm:w-auto inline-flex items-center justify-center text-[15px] font-medium text-white px-7 py-3.5 rounded-full btn-primary"
                        >
                            Ajukan kunjungan lahan
                        </a>
                        <a
                            href="#kawasan"
                            class="w-full sm:w-auto inline-flex items-center justify-center text-[15px] font-medium px-7 py-3.5 rounded-full border border-black/15 text-kitb-ink-900"
                        >
                            Lihat master plan
                        </a>
                    </div>
                </div>

                <div
                    v-fade-in
                    class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-8 mt-16 md:mt-24 pt-10 border-t border-black/10 max-w-4xl"
                >
                    <div v-for="(s, i) in stats" :key="s.label">
                        <div
                            class="stat-number text-2xl sm:text-3xl md:text-4xl text-kitb-green-800"
                        >
                            <span
                                v-count-up="{
                                    target: s.target,
                                    decimals: s.decimals,
                                    delay: i * 120,
                                }"
                            >
                                0
                            </span>
                            <span class="text-base sm:text-lg align-top">{{
                                s.suffix
                            }}</span>
                        </div>
                        <div
                            class="text-[12.5px] sm:text-[13px] mt-1 text-kitb-ink-900/55"
                        >
                            {{ s.label }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= TENTANG ================= -->
        <section
            id="tentang"
            class="relative py-16 sm:py-24 md:py-32 overflow-hidden"
        >
            <div
                class="blob blob-organic-2 drift-b"
                style="
                    width: 420px;
                    height: 420px;
                    top: -60px;
                    left: -200px;
                    background: radial-gradient(
                        circle at 40% 40%,
                        #7fa8e0,
                        transparent 70%
                    );
                    opacity: 0.35;
                "
            />

            <div
                class="max-w-7xl mx-auto px-5 sm:px-6 md:px-10 relative grid md:grid-cols-12 gap-8 md:gap-8"
            >
                <div class="md:col-span-4" v-fade-in>
                    <p class="text-[13px] font-medium mb-4 text-kitb-teal-600">
                        Tentang KITB
                    </p>
                    <h2
                        class="font-display leading-[1.1] text-kitb-green-900 font-bold"
                        style="font-size: clamp(1.7rem, 3.2vw, 2.6rem)"
                    >
                        Bukan sekadar penyedia lahan
                    </h2>
                </div>

                <div
                    v-fade-in
                    class="md:col-span-7 md:col-start-6 space-y-6 text-[16px] sm:text-[16.5px] leading-relaxed text-kitb-ink-900/72"
                >
                    <p>
                        Di tengah dinamika ekonomi global, kebutuhan akan ruang
                        industri yang efisien, terintegrasi, dan adaptif menjadi
                        sebuah keniscayaan. KITB hadir sebagai pusat pertumbuhan
                        ekonomi baru yang mengintegrasikan potensi darat dan
                        keunggulan maritim Riau secara strategis.
                    </p>

                    <p>
                        Mengusung moto
                        <span
                            class="font-display font-semibold text-kitb-green-800"
                        >
                            "Beyond Industry, Towards the Future,"
                        </span>
                        kami membangun ekosistem Smart &amp; Green Industrial
                        Estate — mendorong hilirisasi bernilai tambah tinggi
                        sekaligus menjaga keberlanjutan lingkungan dan
                        kesejahteraan masyarakat sekitar.
                    </p>

                    <div class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="border-l-2 pl-5 border-kitb-teal-500">
                            <div
                                class="font-display font-semibold text-lg mb-1 text-kitb-green-800"
                            >
                                Smart Industrial Park
                            </div>
                            <div class="text-[14.5px] text-kitb-ink-900/55">
                                Digitalisasi &amp; otomasi untuk efisiensi
                                operasional tenant.
                            </div>
                        </div>

                        <div class="border-l-2 pl-5 border-kitb-amber-500">
                            <div
                                class="font-display font-semibold text-lg mb-1 text-kitb-green-800"
                            >
                                Green Industrial Estate
                            </div>
                            <div class="text-[14.5px] text-kitb-ink-900/55">
                                Energi terbarukan, efisiensi air, pengelolaan
                                limbah ramah lingkungan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= VISI MISI ================= -->
        <section
            id="visi-misi"
            class="relative py-16 sm:py-24 md:py-32 overflow-hidden bg-kitb-navy-900"
        >
            <div
                class="blob blob-organic-3 drift-a"
                style="
                    width: 520px;
                    height: 520px;
                    bottom: -220px;
                    right: -160px;
                    background: radial-gradient(
                        circle at 40% 40%,
                        #2e6fbf,
                        transparent 70%
                    );
                    opacity: 0.45;
                "
            />
            <div
                class="blob blob-organic-1"
                style="
                    width: 260px;
                    height: 260px;
                    top: 60px;
                    left: -100px;
                    background: #7fa8e0;
                    opacity: 0.08;
                "
            />

            <div class="max-w-7xl mx-auto px-5 sm:px-6 md:px-10 relative">
                <div class="max-w-xl mb-12 md:mb-16" v-fade-in>
                    <p class="text-[13px] font-medium mb-4 text-kitb-teal-300">
                        Visi
                    </p>
                    <h2
                        class="font-display leading-[1.15] text-white font-bold"
                        style="font-size: clamp(1.7rem, 3.4vw, 2.7rem)"
                    >
                        Menjadi kawasan industri dan maritim terpadu yang
                        berkelanjutan, pintar, dan berdaya saing global
                    </h2>
                </div>

                <p class="text-[13px] font-medium mb-8 text-kitb-teal-300">
                    Misi
                </p>

                <div class="grid md:grid-cols-2 gap-x-10 gap-y-8 md:gap-y-10">
                    <div
                        v-for="(m, i) in misi"
                        :key="m.title"
                        v-fade-in
                        :style="{ transitionDelay: i * 90 + 'ms' }"
                        :class="[
                            i < 2
                                ? 'pb-8 border-b border-white/12'
                                : i === 2
                                  ? 'pb-8 md:pb-0 border-b md:border-b-0 border-white/12'
                                  : '',
                        ]"
                    >
                        <h3
                            class="font-display font-semibold text-white text-lg sm:text-xl mb-2.5"
                        >
                            {{ m.title }}
                        </h3>
                        <p class="text-[15px] leading-relaxed text-white/60">
                            {{ m.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= LOKASI ================= -->
        <section
            id="lokasi"
            class="relative py-16 sm:py-24 md:py-32 overflow-hidden"
        >
            <div class="max-w-7xl mx-auto px-5 sm:px-6 md:px-10">
                <div class="grid md:grid-cols-12 gap-10 md:gap-12 items-start">
                    <div class="md:col-span-5" v-fade-in>
                        <p
                            class="text-[13px] font-medium mb-4 text-kitb-teal-600"
                        >
                            Keunggulan geografis
                        </p>

                        <h2
                            class="font-display leading-[1.1] mb-6 text-kitb-green-900 font-bold"
                            style="font-size: clamp(1.7rem, 3.2vw, 2.6rem)"
                        >
                            Menghadap langsung jalur pelayaran tersibuk di
                            dunia.
                        </h2>

                        <p
                            class="text-[16px] leading-relaxed mb-8 text-kitb-ink-900/70"
                        >
                            Selat Malaka adalah jalur utama lalu lintas
                            perdagangan dari India ke Timur Tengah, dan dari
                            Asia Timur ke Pasifik. Tanjung Buton berdiri tepat
                            di jalur ini — menjadikannya gerbang ekspor-impor
                            yang sangat efisien.
                        </p>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3.5">
                                <div
                                    class="w-1.5 h-1.5 rounded-full mt-2.5 shrink-0 bg-kitb-teal-500"
                                />
                                <p class="text-[15px] text-kitb-ink-900/68">
                                    Panjang alur pelayaran &plusmn; 36 mil,
                                    lebar alur 15&ndash;17 m LWS
                                </p>
                            </div>
                            <div class="flex items-start gap-3.5">
                                <div
                                    class="w-1.5 h-1.5 rounded-full mt-2.5 shrink-0 bg-kitb-teal-500"
                                />
                                <p class="text-[15px] text-kitb-ink-900/68">
                                    Fasilitas kapal labuh dan pelayanan
                                    pemanduan tersedia
                                </p>
                            </div>
                            <div class="flex items-start gap-3.5">
                                <div
                                    class="w-1.5 h-1.5 rounded-full mt-2.5 shrink-0 bg-kitb-teal-500"
                                />
                                <p class="text-[15px] text-kitb-ink-900/68">
                                    Ombak relatif kecil (0,32&ndash;0,98 m),
                                    arus maksimal 0,68&ndash;0,83 m/detik
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="md:col-span-6 md:col-start-7 min-w-0"
                        v-fade-in
                        style="transition-delay: 120ms"
                    >
                        <div
                            class="rounded-2xl overflow-x-auto bg-kitb-sand-100"
                        >
                            <table
                                class="w-full min-w-[520px] text-[14px] sm:text-[14.5px]"
                            >
                                <thead>
                                    <tr class="text-left bg-kitb-green-700">
                                        <th
                                            class="px-4 sm:px-6 py-3.5 sm:py-4 font-medium text-white text-[13px]"
                                        >
                                            Rute
                                        </th>
                                        <th
                                            class="px-4 sm:px-6 py-3.5 sm:py-4 font-medium text-white text-[13px]"
                                        >
                                            Jalur
                                        </th>
                                        <th
                                            class="px-4 sm:px-6 py-3.5 sm:py-4 font-medium text-white text-[13px]"
                                        >
                                            Jarak
                                        </th>
                                        <th
                                            class="px-4 sm:px-6 py-3.5 sm:py-4 font-medium text-white text-[13px]"
                                        >
                                            Waktu tempuh
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="r in routes"
                                        :key="r.name"
                                        class="route-row"
                                    >
                                        <td
                                            class="px-4 sm:px-6 py-3.5 sm:py-4 font-medium text-kitb-green-800"
                                        >
                                            {{ r.name }}
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-3.5 sm:py-4 text-kitb-ink-900/70"
                                        >
                                            {{ r.path }}
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-3.5 sm:py-4 text-kitb-ink-900/70 whitespace-nowrap"
                                        >
                                            {{ r.distance }}
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-3.5 sm:py-4 text-kitb-ink-900/70 whitespace-nowrap"
                                        >
                                            {{ r.time }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p class="text-[13px] mt-4 text-kitb-ink-900/45">
                            Rute 2 melalui Selat Asam adalah jalur tersingkat
                            menuju Selat Malaka
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= KAWASAN / MASTER PLAN ================= -->
        <section
            id="kawasan"
            class="relative py-16 sm:py-24 md:py-32 overflow-hidden bg-kitb-sand-100"
        >
            <div
                class="blob blob-organic-2 drift-b"
                style="
                    width: 460px;
                    height: 460px;
                    top: -140px;
                    right: -180px;
                    background: radial-gradient(
                        circle at 40% 40%,
                        #1f4c91,
                        transparent 70%
                    );
                    opacity: 0.18;
                "
            />

            <div class="max-w-7xl mx-auto px-5 sm:px-6 md:px-10 relative">
                <div class="max-w-xl mb-12 md:mb-16" v-fade-in>
                    <p class="text-[13px] font-medium mb-4 text-kitb-teal-600">
                        Ketersediaan lahan
                    </p>
                    <h2
                        class="font-display leading-[1.1] text-kitb-green-900 font-bold"
                        style="font-size: clamp(1.7rem, 3.2vw, 2.6rem)"
                    >
                        6.070 hektar, terbagi dalam tahapan yang jelas
                    </h2>
                </div>

                <div
                    v-fade-in
                    class="grid grid-cols-1 sm:grid-cols-3 gap-8 sm:gap-10 mb-16 md:mb-20"
                >
                    <div v-for="(l, i) in lahan" :key="l.label">
                        <div
                            class="stat-number text-4xl md:text-5xl mb-2 text-kitb-green-800"
                        >
                            <span
                                v-count-up="{
                                    target: l.target,
                                    decimals: l.decimals,
                                    delay: i * 120,
                                }"
                            >
                                0
                            </span>
                            <span class="text-xl align-top ml-1">{{
                                l.unit
                            }}</span>
                        </div>
                        <div class="text-[14.5px] text-kitb-ink-900/55">
                            {{ l.label }}
                        </div>
                    </div>
                </div>

                <!-- ================= TAHAP 1 ================= -->
                <div class="border-t pt-12 md:pt-16 border-black/10">
                    <p class="text-[13px] font-medium mb-10 text-kitb-teal-600">
                        Pembangunan tahap 1 &middot; 300 Ha
                    </p>

                    <div
                        class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-10 relative"
                    >
                        <div
                            v-for="(t, i) in tahap1"
                            :key="t.title"
                            v-fade-in
                            :style="{ transitionDelay: i * 100 + 'ms' }"
                            class="relative pl-7"
                        >
                            <div
                                v-if="i < tahap1.length - 1"
                                class="absolute left-0 top-1.5 bottom-0 w-px process-line"
                            />
                            <div
                                class="absolute left-[-4.5px] top-1 w-2.5 h-2.5 rounded-full"
                                :class="
                                    i < tahap1.length - 1
                                        ? 'bg-kitb-teal-500'
                                        : 'bg-kitb-green-700'
                                "
                            />
                            <h3
                                class="font-display font-semibold text-lg mb-2 text-kitb-green-900"
                            >
                                {{ t.title }}
                            </h3>
                            <p
                                class="text-[14.5px] leading-relaxed text-kitb-ink-900/62"
                            >
                                {{ t.desc }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ================= PETA TAHAPAN PENGEMBANGAN ================= -->
                <div
                    class="border-t pt-12 md:pt-16 mt-16 md:mt-20 border-black/10"
                >
                    <div
                        v-fade-in
                        class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-start"
                    >
                        <div class="lg:col-span-4">
                            <p
                                class="text-[13px] font-medium mb-4 text-kitb-teal-600"
                            >
                                Master Plan KITB
                            </p>

                            <h3
                                class="font-display leading-[1.1] mb-5 text-kitb-green-900 font-bold"
                                style="font-size: clamp(1.5rem, 2.8vw, 2.3rem)"
                            >
                                {{ masterPlan.judul }}
                            </h3>

                            <p
                                class="text-[15px] leading-relaxed text-kitb-ink-900/65 mb-7"
                            >
                                {{ masterPlan.keterangan }}
                            </p>

                            <div
                                class="rounded-2xl p-5 bg-kitb-navy-900 text-white"
                            >
                                <div class="text-[12px] text-white/55 mb-1">
                                    Total luas kawasan
                                </div>
                                <div class="font-display font-bold text-3xl">
                                    {{
                                        masterPlan.total_luas_ha.toLocaleString(
                                            "id-ID",
                                        )
                                    }}
                                    <span
                                        class="text-lg font-medium text-white/65"
                                        >Ha</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-8 min-w-0">
                            <div
                                class="rounded-2xl overflow-hidden bg-kitb-surface border border-black/5 shadow-sm"
                            >
                                <div
                                    class="overflow-x-auto overscroll-x-contain"
                                >
                                    <a
                                        :href="masterPlan.gambar_path"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="block min-w-[560px] sm:min-w-0"
                                    >
                                        <img
                                            :src="masterPlan.gambar_path"
                                            :alt="masterPlan.judul"
                                            class="w-full h-auto object-contain"
                                            loading="lazy"
                                        />
                                    </a>
                                </div>
                            </div>

                            <p class="text-[12.5px] mt-3 text-kitb-ink-900/45">
                                Ketuk peta untuk melihat gambar dalam ukuran
                                penuh.
                                <span class="sm:hidden"
                                    >Geser ke samping untuk melihat seluruh
                                    peta.</span
                                >
                            </p>
                        </div>
                    </div>

                    <!-- Legend zona -->
                    <div
                        v-fade-in
                        class="mt-10 md:mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-3"
                    >
                        <div
                            v-for="zone in kawasanZones"
                            :key="zone.kode"
                            class="flex items-center justify-between gap-4 rounded-xl bg-kitb-surface border border-black/5 px-4 py-3"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <span
                                    class="w-4 h-4 rounded shrink-0 border border-black/10"
                                    :style="{ backgroundColor: zone.warna }"
                                />
                                <div class="min-w-0">
                                    <div
                                        class="text-[14px] font-medium text-kitb-green-900"
                                    >
                                        {{ zone.label }}
                                    </div>
                                    <div
                                        v-if="zone.catatan"
                                        class="text-[11.5px] text-kitb-ink-900/45"
                                    >
                                        {{ zone.catatan }}
                                    </div>
                                </div>
                            </div>

                            <div
                                class="text-[14px] font-semibold text-kitb-green-800 whitespace-nowrap"
                            >
                                {{ zone.luas.toLocaleString("id-ID") }} Ha
                            </div>
                        </div>
                    </div>

                    <p
                        class="text-[12.5px] leading-relaxed mt-5 text-kitb-ink-900/45"
                    >
                        Pembagian zona menunjukkan tahapan pengembangan kawasan
                        KITB, termasuk area supporting dan Port sebagai bagian
                        dari konsep pengembangan kawasan terintegrasi.
                    </p>
                </div>
            </div>
        </section>

        <!-- ================= CTA ================= -->
        <section class="relative py-14 sm:py-20 md:py-28">
            <div class="max-w-7xl mx-auto px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="rounded-3xl px-6 py-12 sm:px-8 md:px-16 md:py-20 text-center relative overflow-hidden bg-kitb-green-700"
                >
                    <div
                        class="blob blob-organic-1 drift-a"
                        style="
                            width: 300px;
                            height: 300px;
                            top: -100px;
                            left: -80px;
                            background: #2e6fbf;
                            opacity: 0.25;
                        "
                    />
                    <div
                        class="blob blob-organic-3 drift-b"
                        style="
                            width: 260px;
                            height: 260px;
                            bottom: -100px;
                            right: -60px;
                            background: #c8963e;
                            opacity: 0.15;
                        "
                    />

                    <p
                        class="text-[13px] font-medium mb-5 relative text-kitb-teal-300"
                    >
                        Naik ke panggung industri global
                    </p>

                    <h2
                        class="font-display text-white leading-[1.15] max-w-2xl mx-auto relative font-bold"
                        style="font-size: clamp(1.6rem, 3.6vw, 2.8rem)"
                    >
                        Bersama para mitra dan investor, kami siap memimpin
                        transformasi industri Indonesia.
                    </h2>

                    <div class="mt-8 sm:mt-10 relative">
                        <a
                            :href="visitUrl"
                            class="w-full sm:w-auto inline-flex items-center justify-center text-[15px] font-medium px-8 py-3.5 rounded-full bg-white text-[#163a70]"
                        >
                            Mulai diskusi investasi
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= FOOTER / KONTAK ================= -->
        <footer
            id="kontak"
            class="relative pt-16 md:pt-20 pb-8 overflow-hidden bg-kitb-navy-900"
        >
            <div
                class="blob blob-organic-2"
                style="
                    width: 400px;
                    height: 400px;
                    bottom: -200px;
                    left: -150px;
                    background: #2e6fbf;
                    opacity: 0.1;
                "
            />

            <div class="max-w-7xl mx-auto px-5 sm:px-6 md:px-10 relative">
                <!-- Logo, alamat, email -->
                <div
                    class="flex flex-col items-center text-center mb-14 md:mb-16"
                >
                    <div class="flex items-center gap-4 mb-4">
                        <img
                            src="/images/siak-kabupaten.png"
                            alt="Lambang Kabupaten Siak"
                            class="h-12 sm:h-14 w-auto rounded-sm"
                        />
                        <img
                            src="/images/kitb-logo.png"
                            alt="Logo KITB"
                            class="h-12 sm:h-14 w-auto"
                        />
                    </div>

                    <div class="text-[11px] tracking-wide text-white/45 mb-6">
                        Kawasan Industri Tanjung Buton
                    </div>

                    <p
                        class="text-[14.5px] text-white/60 max-w-md leading-relaxed"
                    >
                        <span class="font-medium text-white/85">Alamat:</span>
                        Kampung Mengkapan &amp; Kampung Sungai Rawa, Kecamatan
                        Sungai Apit, Kabupaten Siak, Provinsi Riau
                    </p>

                    <p
                        class="text-[14.5px] text-white/60 mt-1.5 max-w-full break-words"
                    >
                        <span class="font-medium text-white/85">Email:</span>
                        <a
                            href="mailto:info@tanjungbuton-industrial.co.id"
                            class="hover:text-white transition-colors"
                        >
                            info@tanjungbuton-industrial.co.id
                        </a>
                    </p>
                </div>

                <!-- Kolom navigasi (data dari navGroups) -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-x-8 gap-y-10 pb-12 md:pb-14 border-b border-white/10"
                >
                    <div
                        v-for="group in navGroups"
                        :key="group.label"
                        class="text-center sm:text-left"
                    >
                        <h4 class="text-[13px] font-semibold text-white mb-4">
                            {{ group.label }}
                        </h4>
                        <ul
                            class="flex flex-col items-center sm:items-start gap-2.5 text-[14.5px] text-white/55"
                        >
                            <li v-for="item in group.items" :key="item.label">
                                <a
                                    :href="item.href || '#'"
                                    class="hover:text-white transition-colors"
                                    @click="handleNavItemClick(item, $event)"
                                >
                                    {{ item.label }}
                                    <span
                                        v-if="item.badge"
                                        class="ml-2 inline-flex items-center rounded-full bg-kitb-teal-500/10 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-kitb-teal-300"
                                        >{{ item.badge }}</span
                                    >
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Kontak Kami -->
                    <div
                        class="text-center sm:text-left sm:col-span-2 lg:col-span-1 min-w-0"
                    >
                        <h4 class="text-[13px] font-semibold text-white mb-4">
                            Kontak Kami
                        </h4>

                        <a
                            href="mailto:info@tanjungbuton-industrial.co.id"
                            class="inline-flex items-center gap-2 text-[13.5px] font-medium text-white px-5 py-2.5 rounded-full bg-kitb-teal-500 hover:brightness-110 transition mb-5"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2.5"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                />
                                <path
                                    d="M4 7.5l8 6 8-6"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            Kirim Email
                        </a>

                        <div
                            class="flex items-center justify-center sm:justify-start gap-2.5"
                        >
                            <a
                                v-for="soc in socials"
                                :key="soc.label"
                                :href="soc.href"
                                :aria-label="soc.label"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-10 h-10 rounded-full flex items-center justify-center bg-white/10 text-white/70 hover:bg-kitb-teal-500 hover:text-white transition-colors"
                            >
                                <svg
                                    v-if="soc.type === 'instagram'"
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <rect
                                        x="3"
                                        y="3"
                                        width="18"
                                        height="18"
                                        rx="5"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="4"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    />
                                    <circle
                                        cx="17.2"
                                        cy="6.8"
                                        r="1.1"
                                        fill="currentColor"
                                    />
                                </svg>
                                <svg
                                    v-else-if="soc.type === 'tiktok'"
                                    width="17"
                                    height="17"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    width="20"
                                    height="20"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M21.6 7.2a2.5 2.5 0 0 0-1.76-1.77C18.25 5 12 5 12 5s-6.25 0-7.84.43A2.5 2.5 0 0 0 2.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.76 1.77C5.75 19 12 19 12 19s6.25 0 7.84-.43a2.5 2.5 0 0 0 1.76-1.77C22 15.2 22 12 22 12s0-3.2-.4-4.8zM10 9.5v5l4.5-2.5z"
                                    />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bottom bar -->
                <div
                    class="pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-[13px] text-white/40"
                    style="padding-bottom: env(safe-area-inset-bottom, 0px)"
                >
                    <p
                        class="flex flex-wrap items-center gap-x-2 gap-y-1 justify-center md:justify-start text-center md:text-left"
                    >
                        <span>
                            &copy; 2026 PT. Kawasan Industri Tanjung Buton.
                            Badan Usaha Milik Daerah, Kabupaten Siak.
                        </span>
                        <span class="hidden md:inline">/</span>
                        <a
                            href="#"
                            class="underline hover:text-white transition-colors"
                            >Kebijakan Privasi</a
                        >
                        <span class="hidden md:inline">/</span>
                        <a
                            href="#"
                            class="underline hover:text-white transition-colors"
                            >Pengaduan</a
                        >
                    </p>

                    <p class="text-center">
                        Beyond Industry, Towards the Future.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ---------------- Global ---------------- */
:global(html) {
    scroll-behavior: smooth;
    scroll-padding-top: 5.5rem;
}

.kitb-landing {
    font-family: "Poppins", ui-sans-serif, system-ui, sans-serif;
}

.kitb-landing * {
    font-family: inherit;
}

/* Cegah auto-zoom iOS saat fokus input */
.kitb-landing input,
.kitb-landing textarea {
    font-size: 16px;
}

.kitb-landing a:focus-visible,
.kitb-landing button:focus-visible {
    outline: 2px solid #2e6fbf;
    outline-offset: 2px;
}

/* ---------------- Blob ---------------- */
.blob {
    position: absolute;
    pointer-events: none;
    max-width: 90vw;
    max-height: 90vw;
}

.blob-organic-1 {
    border-radius: 42% 58% 65% 35% / 45% 40% 60% 55%;
}

.blob-organic-2 {
    border-radius: 58% 42% 35% 65% / 55% 60% 40% 45%;
}

.blob-organic-3 {
    border-radius: 38% 62% 63% 37% / 41% 44% 56% 59%;
}

@keyframes driftA {
    0%,
    100% {
        transform: translate(0, 0) rotate(0deg);
    }
    50% {
        transform: translate(18px, -24px) rotate(6deg);
    }
}

@keyframes driftB {
    0%,
    100% {
        transform: translate(0, 0) rotate(0deg);
    }
    50% {
        transform: translate(-22px, 20px) rotate(-5deg);
    }
}

.drift-a {
    animation: driftA 16s ease-in-out infinite;
}

.drift-b {
    animation: driftB 19s ease-in-out infinite;
}

.hero-blob-field {
    background: radial-gradient(
        circle at 30% 20%,
        rgba(46, 111, 191, 0.1),
        transparent 55%
    );
}

.stat-number {
    font-family: "Poppins", sans-serif;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.route-row:nth-child(odd) {
    background: rgba(22, 58, 112, 0.05);
}

:global(.dark) .route-row:nth-child(odd) {
    background: rgb(255 255 255 / 0.04);
}

.btn-primary {
    background: #163a70;
    transition:
        background 0.2s ease,
        transform 0.2s ease;
}

.btn-primary:hover {
    background: #1f4c91;
    transform: translateY(-1px);
}

.process-line {
    background: linear-gradient(180deg, #2e6fbf, #163a70);
}

/* ---------------- Navbar desktop ---------------- */
.nav-trigger {
    color: rgba(11, 31, 63, 0.8);
    transition:
        background 0.2s ease,
        color 0.2s ease;
}

.nav-trigger:hover,
.nav-trigger:focus-visible {
    background: rgba(22, 58, 112, 0.07);
    color: #163a70;
}

/* ---------------- Hamburger -> X ---------------- */
.burger {
    position: relative;
    width: 22px;
    height: 16px;
}

.burger span {
    position: absolute;
    left: 0;
    width: 100%;
    height: 2px;
    border-radius: 2px;
    background: #163a70;
    transition:
        transform 0.35s cubic-bezier(0.65, 0, 0.35, 1),
        opacity 0.2s ease,
        top 0.35s cubic-bezier(0.65, 0, 0.35, 1);
}

.burger span:nth-child(1) {
    top: 0;
}

.burger span:nth-child(2) {
    top: 7px;
}

.burger span:nth-child(3) {
    top: 14px;
}

.burger.is-open span:nth-child(1) {
    top: 7px;
    transform: rotate(45deg);
}

.burger.is-open span:nth-child(2) {
    opacity: 0;
    transform: scaleX(0.3);
}

.burger.is-open span:nth-child(3) {
    top: 7px;
    transform: rotate(-45deg);
}

/* ---------------- Panel menu mobile ---------------- */
.mobile-scroll {
    max-height: calc(100dvh - 4rem - env(safe-area-inset-top, 0px) - 1rem);
}

@media (min-width: 640px) {
    .mobile-scroll {
        max-height: calc(100dvh - 5rem - env(safe-area-inset-top, 0px) - 1rem);
    }
}

.menu-panel-enter-active {
    transition:
        opacity 0.3s ease,
        transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.menu-panel-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.25s ease;
}

.menu-panel-enter-from,
.menu-panel-leave-to {
    opacity: 0;
    transform: translateY(-14px);
}

.backdrop-enter-active,
.backdrop-leave-active {
    transition: opacity 0.3s ease;
}

.backdrop-enter-from,
.backdrop-leave-to {
    opacity: 0;
}

/* Item menu muncul berurutan (stagger) */
.m-item {
    animation: menuItemIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    animation-delay: calc(var(--i, 0) * 55ms + 80ms);
}

@keyframes menuItemIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Accordion halus tanpa hitung tinggi manual */
.acc {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.3s ease;
}

.acc.is-open {
    grid-template-rows: 1fr;
}

.acc-inner {
    overflow: hidden;
    min-height: 0;
}

/* ---------------- Fade-in on scroll ---------------- */
.fade-in {
    opacity: 0;
    transform: translateY(28px);
    transition:
        opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1),
        transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-in-visible {
    opacity: 1;
    transform: translateY(0);
}

/* ---------------- Reduced motion ---------------- */
@media (prefers-reduced-motion: reduce) {
    :global(html) {
        scroll-behavior: auto;
    }

    .drift-a,
    .drift-b,
    .m-item {
        animation: none;
    }

    .menu-panel-enter-active,
    .menu-panel-leave-active,
    .backdrop-enter-active,
    .backdrop-leave-active,
    .acc,
    .burger span {
        transition: none;
    }
}
</style>
