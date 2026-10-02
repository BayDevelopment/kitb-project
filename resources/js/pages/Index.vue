<script setup>
import { Head } from "@inertiajs/vue3";
import PublicLayout from "@/layouts/PublicLayout.vue";
import { visitUrl } from "@/data/publicNavigation";

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
   Fade-in on scroll
   ========================================================= */

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
                    if (!entry.isIntersecting) return;

                    el.classList.add("fade-in-visible");
                    observer.unobserve(el);
                });
            },
            {
                threshold: 0.15,
                rootMargin: "0px 0px -60px 0px",
            },
        );

        observer.observe(el);
        el.__fadeObserver = observer;
    },

    unmounted(el) {
        el.__fadeObserver?.disconnect();
        delete el.__fadeObserver;
    },
};

/* =========================================================
   Count-up angka saat masuk viewport
   ========================================================= */

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

                    const timeout = setTimeout(() => {
                        const start = performance.now();

                        const tick = (now) => {
                            const progress = Math.min(
                                (now - start) / duration,
                                1,
                            );

                            const eased = 1 - Math.pow(1 - progress, 3);

                            el.textContent = formatId(target * eased, decimals);

                            if (progress < 1) {
                                el.__countFrame = requestAnimationFrame(tick);
                            } else {
                                el.textContent = formatId(target, decimals);
                            }
                        };

                        el.__countFrame = requestAnimationFrame(tick);
                    }, delay);

                    el.__countTimeout = timeout;
                });
            },
            {
                threshold: 0.4,
                rootMargin: "0px 0px -40px 0px",
            },
        );

        observer.observe(el);
        el.__countObserver = observer;
    },

    unmounted(el) {
        el.__countObserver?.disconnect();

        if (el.__countTimeout) {
            clearTimeout(el.__countTimeout);
        }

        if (el.__countFrame) {
            cancelAnimationFrame(el.__countFrame);
        }

        delete el.__countObserver;
        delete el.__countTimeout;
        delete el.__countFrame;
    },
};

/* =========================================================
   Statistik
   ========================================================= */

const stats = [
    {
        target: 6070,
        decimals: 0,
        suffix: "Ha",
        label: "Wilayah pengembangan",
    },
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
    {
        target: 117,
        decimals: 0,
        suffix: "K+",
        label: "KK petani sawit aktif",
    },
];

/* =========================================================
   Visi & Misi
   ========================================================= */

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

/* =========================================================
   Rute pelayaran
   ========================================================= */

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

/* =========================================================
   Ketersediaan lahan
   ========================================================= */

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

/* =========================================================
   Pembangunan tahap 1
   ========================================================= */

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

/* =========================================================
   Data peta tahapan pengembangan
   ========================================================= */

const masterPlan = {
    judul: "Peta tahapan pengembangan & luas per zona",
    gambar_path: "/images/master-plan-kitb.jpeg",
    keterangan:
        "Development Phase Concept Plan Map, skala 1:65.360 (LPPM UIR).",
    total_luas_ha: 6070,
};

/*
 * Warna zona = legenda peta master plan.
 * Jangan diubah mengikuti tema.
 */

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
</script>

<template>
    <Head
        title="PT. Kawasan Industri Tanjung Buton — Beyond Industry, Towards the Future"
    />

    <div
        class="kitb-landing relative min-h-screen overflow-x-clip bg-kitb-sand-50 text-kitb-ink-900 antialiased"
    >
        <!-- =====================================================
             HERO
             ===================================================== -->

        <section
            id="top"
            class="hero-blob-field relative z-0 overflow-visible border-b border-black/5 pt-32 pb-20 sm:pt-40 sm:pb-24 md:pt-48 md:pb-36 dark:border-white/10"
        >
            <!--
                SINGLE PRIMARY AMBIENT BLOB

                Blob ini sengaja dinaikkan ke atas sehingga dapat
                masuk ke belakang fixed navbar.
            -->
            <div
                class="blob blob-organic-1 drift-a"
                aria-hidden="true"
                style="
                    width: 620px;
                    height: 620px;
                    top: -300px;
                    right: -220px;
                    background: radial-gradient(
                        circle at 35% 30%,
                        #8fb5e8 0%,
                        #4c82c8 35%,
                        #2e6fbf 55%,
                        #163a70 86%,
                        transparent 100%
                    );
                    opacity: 0.72;
                "
            />

            <!-- Secondary hero blob -->
            <div
                class="blob blob-organic-2 drift-b"
                aria-hidden="true"
                style="
                    width: 340px;
                    height: 340px;
                    right: 120px;
                    bottom: -120px;
                    background: radial-gradient(
                        circle at 60% 40%,
                        #2f66b0 0%,
                        #1f4c91 42%,
                        #0b1f3f 82%,
                        transparent 100%
                    );
                    opacity: 0.68;
                "
            />

            <!-- Hero content -->
            <div class="relative z-10 mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div class="max-w-2xl" v-fade-in>
                    <div
                        class="mb-6 inline-flex items-center gap-2 rounded-full bg-kitb-green-700/10 px-3.5 py-1.5 text-[12.5px] font-medium text-kitb-green-800 sm:mb-8 sm:text-[13px]"
                    >
                        <span
                            class="h-1.5 w-1.5 shrink-0 rounded-full bg-kitb-teal-500"
                        />
                        Badan Usaha Milik Daerah &middot; Kabupaten Siak
                    </div>

                    <h1
                        class="mb-6 font-display font-bold leading-[1.08] text-kitb-green-900 sm:mb-7"
                        style="font-size: clamp(2.1rem, 5.4vw, 4.3rem)"
                    >
                        Kawasan industri yang berdiri tepat di bibir Selat
                        Malaka
                    </h1>

                    <p
                        class="mb-8 max-w-lg text-[16px] leading-relaxed text-kitb-ink-900/70 md:text-[18px] sm:mb-10"
                    >
                        PT Kawasan Industri Tanjung Buton mengintegrasikan lahan
                        industri, pelabuhan laut dalam, dan hilirisasi komoditas
                        Riau menjadi satu ekosistem — di jalur pelayaran
                        tersibuk di dunia
                    </p>

                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-4"
                    >
                        <a
                            :href="visitUrl"
                            class="btn-primary inline-flex w-full items-center justify-center rounded-full px-7 py-3.5 text-[15px] font-medium text-white sm:w-auto"
                        >
                            Ajukan kunjungan lahan
                        </a>

                        <a
                            href="#kawasan"
                            class="inline-flex w-full items-center justify-center rounded-full border border-black/15 px-7 py-3.5 text-[15px] font-medium text-kitb-ink-900 transition-colors hover:border-kitb-green-700/30 hover:bg-white/50 sm:w-auto"
                        >
                            Lihat master plan
                        </a>
                    </div>
                </div>

                <!-- Hero statistics -->
                <div
                    v-fade-in
                    class="mt-16 grid max-w-4xl grid-cols-2 gap-x-6 gap-y-8 border-t border-black/10 pt-10 md:mt-24 md:grid-cols-4"
                >
                    <div v-for="(s, i) in stats" :key="s.label">
                        <div
                            class="stat-number text-2xl text-kitb-green-800 sm:text-3xl md:text-4xl"
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

                            <span class="align-top text-base sm:text-lg">
                                {{ s.suffix }}
                            </span>
                        </div>

                        <div
                            class="mt-1 text-[12.5px] text-kitb-ink-900/55 sm:text-[13px]"
                        >
                            {{ s.label }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             TENTANG
             ===================================================== -->

        <section
            id="tentang"
            class="relative overflow-hidden py-16 sm:py-24 md:py-32"
        >
            <div
                class="blob blob-organic-2 drift-b"
                aria-hidden="true"
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
                class="relative mx-auto grid max-w-7xl grid-cols-1 gap-8 px-5 sm:px-6 md:grid-cols-12 md:gap-8 md:px-10"
            >
                <div class="md:col-span-4" v-fade-in>
                    <p class="mb-4 text-[13px] font-medium text-kitb-teal-600">
                        Tentang KITB
                    </p>

                    <h2
                        class="font-display font-bold leading-[1.1] text-kitb-green-900"
                        style="font-size: clamp(1.7rem, 3.2vw, 2.6rem)"
                    >
                        Bukan sekadar penyedia lahan
                    </h2>
                </div>

                <div
                    v-fade-in
                    class="space-y-6 text-[16px] leading-relaxed text-kitb-ink-900/72 sm:text-[16.5px] md:col-span-7 md:col-start-6"
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

                    <div class="grid grid-cols-1 gap-6 pt-4 sm:grid-cols-2">
                        <div class="border-l-2 border-kitb-teal-500 pl-5">
                            <div
                                class="mb-1 font-display text-lg font-semibold text-kitb-green-800"
                            >
                                Smart Industrial Park
                            </div>

                            <div class="text-[14.5px] text-kitb-ink-900/55">
                                Digitalisasi &amp; otomasi untuk efisiensi
                                operasional tenant.
                            </div>
                        </div>

                        <div class="border-l-2 border-kitb-amber-500 pl-5">
                            <div
                                class="mb-1 font-display text-lg font-semibold text-kitb-green-800"
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

        <!-- =====================================================
             VISI MISI
             ===================================================== -->

        <section
            id="visi-misi"
            class="relative overflow-hidden bg-kitb-navy-900 py-16 sm:py-24 md:py-32"
        >
            <div
                class="blob blob-organic-3 drift-a"
                aria-hidden="true"
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
                aria-hidden="true"
                style="
                    width: 260px;
                    height: 260px;
                    top: 60px;
                    left: -100px;
                    background: #7fa8e0;
                    opacity: 0.08;
                "
            />

            <div class="relative mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div class="mb-12 max-w-xl md:mb-16" v-fade-in>
                    <p class="mb-4 text-[13px] font-medium text-kitb-teal-300">
                        Visi
                    </p>

                    <h2
                        class="font-display font-bold leading-[1.15] text-white"
                        style="font-size: clamp(1.7rem, 3.4vw, 2.7rem)"
                    >
                        Menjadi kawasan industri dan maritim terpadu yang
                        berkelanjutan, pintar, dan berdaya saing global
                    </h2>
                </div>

                <p class="mb-8 text-[13px] font-medium text-kitb-teal-300">
                    Misi
                </p>

                <div class="grid gap-x-10 gap-y-8 md:grid-cols-2 md:gap-y-10">
                    <div
                        v-for="(m, i) in misi"
                        :key="m.title"
                        v-fade-in
                        :style="{ transitionDelay: i * 90 + 'ms' }"
                        :class="[
                            i < 2
                                ? 'border-b border-white/12 pb-8'
                                : i === 2
                                  ? 'border-b border-white/12 pb-8 md:border-b-0 md:pb-0'
                                  : '',
                        ]"
                    >
                        <h3
                            class="mb-2.5 font-display text-lg font-semibold text-white sm:text-xl"
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

        <!-- =====================================================
             LOKASI
             ===================================================== -->

        <section
            id="lokasi"
            class="relative overflow-hidden py-16 sm:py-24 md:py-32"
        >
            <div class="mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div class="grid items-start gap-10 md:grid-cols-12 md:gap-12">
                    <div class="md:col-span-5" v-fade-in>
                        <p
                            class="mb-4 text-[13px] font-medium text-kitb-teal-600"
                        >
                            Keunggulan geografis
                        </p>

                        <h2
                            class="mb-6 font-display font-bold leading-[1.1] text-kitb-green-900"
                            style="font-size: clamp(1.7rem, 3.2vw, 2.6rem)"
                        >
                            Menghadap langsung jalur pelayaran tersibuk di
                            dunia.
                        </h2>

                        <p
                            class="mb-8 text-[16px] leading-relaxed text-kitb-ink-900/70"
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
                                    class="mt-2.5 h-1.5 w-1.5 shrink-0 rounded-full bg-kitb-teal-500"
                                />

                                <p class="text-[15px] text-kitb-ink-900/68">
                                    Panjang alur pelayaran &plusmn; 36 mil,
                                    lebar alur 15&ndash;17 m LWS
                                </p>
                            </div>

                            <div class="flex items-start gap-3.5">
                                <div
                                    class="mt-2.5 h-1.5 w-1.5 shrink-0 rounded-full bg-kitb-teal-500"
                                />

                                <p class="text-[15px] text-kitb-ink-900/68">
                                    Fasilitas kapal labuh dan pelayanan
                                    pemanduan tersedia
                                </p>
                            </div>

                            <div class="flex items-start gap-3.5">
                                <div
                                    class="mt-2.5 h-1.5 w-1.5 shrink-0 rounded-full bg-kitb-teal-500"
                                />

                                <p class="text-[15px] text-kitb-ink-900/68">
                                    Ombak relatif kecil (0,32&ndash;0,98 m),
                                    arus maksimal 0,68&ndash;0,83 m/detik
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="min-w-0 md:col-span-6 md:col-start-7"
                        v-fade-in
                        style="transition-delay: 120ms"
                    >
                        <div
                            class="overflow-x-auto rounded-2xl bg-kitb-sand-100"
                        >
                            <table
                                class="w-full min-w-[520px] text-[14px] sm:text-[14.5px]"
                            >
                                <thead>
                                    <tr class="bg-kitb-green-700 text-left">
                                        <th
                                            class="px-4 py-3.5 text-[13px] font-medium text-white sm:px-6 sm:py-4"
                                        >
                                            Rute
                                        </th>

                                        <th
                                            class="px-4 py-3.5 text-[13px] font-medium text-white sm:px-6 sm:py-4"
                                        >
                                            Jalur
                                        </th>

                                        <th
                                            class="px-4 py-3.5 text-[13px] font-medium text-white sm:px-6 sm:py-4"
                                        >
                                            Jarak
                                        </th>

                                        <th
                                            class="px-4 py-3.5 text-[13px] font-medium text-white sm:px-6 sm:py-4"
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
                                            class="px-4 py-3.5 font-medium text-kitb-green-800 sm:px-6 sm:py-4"
                                        >
                                            {{ r.name }}
                                        </td>

                                        <td
                                            class="px-4 py-3.5 text-kitb-ink-900/70 sm:px-6 sm:py-4"
                                        >
                                            {{ r.path }}
                                        </td>

                                        <td
                                            class="whitespace-nowrap px-4 py-3.5 text-kitb-ink-900/70 sm:px-6 sm:py-4"
                                        >
                                            {{ r.distance }}
                                        </td>

                                        <td
                                            class="whitespace-nowrap px-4 py-3.5 text-kitb-ink-900/70 sm:px-6 sm:py-4"
                                        >
                                            {{ r.time }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p class="mt-4 text-[13px] text-kitb-ink-900/45">
                            Rute 2 melalui Selat Asam adalah jalur tersingkat
                            menuju Selat Malaka
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             KAWASAN / MASTER PLAN
             ===================================================== -->

        <section
            id="kawasan"
            class="relative overflow-hidden bg-kitb-sand-100 py-16 sm:py-24 md:py-32"
        >
            <div
                class="blob blob-organic-2 drift-b"
                aria-hidden="true"
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

            <div class="relative mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div class="mb-12 max-w-xl md:mb-16" v-fade-in>
                    <p class="mb-4 text-[13px] font-medium text-kitb-teal-600">
                        Ketersediaan lahan
                    </p>

                    <h2
                        class="font-display font-bold leading-[1.1] text-kitb-green-900"
                        style="font-size: clamp(1.7rem, 3.2vw, 2.6rem)"
                    >
                        6.070 hektar, terbagi dalam tahapan yang jelas
                    </h2>
                </div>

                <div
                    v-fade-in
                    class="mb-16 grid grid-cols-1 gap-8 sm:grid-cols-3 sm:gap-10 md:mb-20"
                >
                    <div v-for="(l, i) in lahan" :key="l.label">
                        <div
                            class="stat-number mb-2 text-4xl text-kitb-green-800 md:text-5xl"
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

                            <span class="ml-1 align-top text-xl">
                                {{ l.unit }}
                            </span>
                        </div>

                        <div class="text-[14.5px] text-kitb-ink-900/55">
                            {{ l.label }}
                        </div>
                    </div>
                </div>

                <!-- Tahap 1 -->
                <div class="border-t border-black/10 pt-12 md:pt-16">
                    <p class="mb-10 text-[13px] font-medium text-kitb-teal-600">
                        Pembangunan tahap 1 &middot; 300 Ha
                    </p>

                    <div
                        class="relative grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3"
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
                                class="process-line absolute bottom-0 left-0 top-1.5 w-px"
                            />

                            <div
                                class="absolute left-[-4.5px] top-1 h-2.5 w-2.5 rounded-full"
                                :class="
                                    i < tahap1.length - 1
                                        ? 'bg-kitb-teal-500'
                                        : 'bg-kitb-green-700'
                                "
                            />

                            <h3
                                class="mb-2 font-display text-lg font-semibold text-kitb-green-900"
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

                <!-- Peta tahapan -->
                <div
                    class="mt-16 border-t border-black/10 pt-12 md:mt-20 md:pt-16"
                >
                    <div
                        v-fade-in
                        class="grid items-start gap-8 lg:grid-cols-12 lg:gap-12"
                    >
                        <div class="lg:col-span-4">
                            <p
                                class="mb-4 text-[13px] font-medium text-kitb-teal-600"
                            >
                                Master Plan KITB
                            </p>

                            <h3
                                class="mb-5 font-display font-bold leading-[1.1] text-kitb-green-900"
                                style="font-size: clamp(1.5rem, 2.8vw, 2.3rem)"
                            >
                                {{ masterPlan.judul }}
                            </h3>

                            <p
                                class="mb-7 text-[15px] leading-relaxed text-kitb-ink-900/65"
                            >
                                {{ masterPlan.keterangan }}
                            </p>

                            <div
                                class="rounded-2xl bg-kitb-navy-900 p-5 text-white"
                            >
                                <div class="mb-1 text-[12px] text-white/55">
                                    Total luas kawasan
                                </div>

                                <div class="font-display text-3xl font-bold">
                                    {{
                                        masterPlan.total_luas_ha.toLocaleString(
                                            "id-ID",
                                        )
                                    }}

                                    <span
                                        class="text-lg font-medium text-white/65"
                                    >
                                        Ha
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="min-w-0 lg:col-span-8">
                            <div
                                class="overflow-hidden rounded-2xl border border-black/5 bg-kitb-surface shadow-sm"
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
                                            class="h-auto w-full object-contain"
                                            loading="lazy"
                                        />
                                    </a>
                                </div>
                            </div>

                            <p class="mt-3 text-[12.5px] text-kitb-ink-900/45">
                                Ketuk peta untuk melihat gambar dalam ukuran
                                penuh.

                                <span class="sm:hidden">
                                    Geser ke samping untuk melihat seluruh peta.
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Legend zona -->
                    <div
                        v-fade-in
                        class="mt-10 grid gap-3 sm:grid-cols-2 lg:mt-12 lg:grid-cols-3"
                    >
                        <div
                            v-for="zone in kawasanZones"
                            :key="zone.kode"
                            class="flex items-center justify-between gap-4 rounded-xl border border-black/5 bg-kitb-surface px-4 py-3"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="h-4 w-4 shrink-0 rounded border border-black/10"
                                    :style="{
                                        backgroundColor: zone.warna,
                                    }"
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
                                class="whitespace-nowrap text-[14px] font-semibold text-kitb-green-800"
                            >
                                {{ zone.luas.toLocaleString("id-ID") }} Ha
                            </div>
                        </div>
                    </div>

                    <p
                        class="mt-5 text-[12.5px] leading-relaxed text-kitb-ink-900/45"
                    >
                        Pembagian zona menunjukkan tahapan pengembangan kawasan
                        KITB, termasuk area supporting dan Port sebagai bagian
                        dari konsep pengembangan kawasan terintegrasi.
                    </p>
                </div>
            </div>
        </section>

        <!-- =====================================================
             CTA
             ===================================================== -->

        <section class="relative py-14 sm:py-20 md:py-28">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 md:px-10">
                <div
                    v-fade-in
                    class="relative overflow-hidden rounded-3xl bg-kitb-green-700 px-6 py-12 text-center sm:px-8 md:px-16 md:py-20"
                >
                    <div
                        class="blob blob-organic-1 drift-a"
                        aria-hidden="true"
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
                        aria-hidden="true"
                        style="
                            width: 260px;
                            height: 260px;
                            right: -60px;
                            bottom: -100px;
                            background: #c8963e;
                            opacity: 0.15;
                        "
                    />

                    <p
                        class="relative mb-5 text-[13px] font-medium text-kitb-teal-300"
                    >
                        Naik ke panggung industri global
                    </p>

                    <h2
                        class="relative mx-auto max-w-2xl font-display font-bold leading-[1.15] text-white"
                        style="font-size: clamp(1.6rem, 3.6vw, 2.8rem)"
                    >
                        Bersama para mitra dan investor, kami siap memimpin
                        transformasi industri Indonesia.
                    </h2>

                    <div class="relative mt-8 sm:mt-10">
                        <a
                            :href="visitUrl"
                            class="inline-flex w-full items-center justify-center rounded-full bg-white px-8 py-3.5 text-[15px] font-medium text-[#163a70] transition-all duration-200 hover:-translate-y-0.5 hover:bg-white/95 sm:w-auto"
                        >
                            Mulai diskusi investasi
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* =========================================================
   GLOBAL
   ========================================================= */

:global(html) {
    scroll-behavior: smooth;
    scroll-padding-top: 5.5rem;
}

.kitb-landing {
    font-family: "Poppins", ui-sans-serif, system-ui, sans-serif;

    /*
     * Penting:
     * gunakan clip horizontal, bukan hidden.
     *
     * Blob Hero perlu keluar secara vertikal ke belakang
     * fixed navbar.
     */
    overflow-x: clip;
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

/* =========================================================
   BLOB
   ========================================================= */

.blob {
    position: absolute;
    pointer-events: none;
    max-width: 90vw;
    max-height: 90vw;
    will-change: transform;
    transform-origin: center;
    z-index: 0;
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

/* =========================================================
   BLOB ANIMATION
   ========================================================= */

@keyframes driftA {
    0%,
    100% {
        transform: translate3d(0, 0, 0) rotate(0deg) scale(1);
    }

    50% {
        transform: translate3d(18px, -24px, 0) rotate(6deg) scale(1.025);
    }
}

@keyframes driftB {
    0%,
    100% {
        transform: translate3d(0, 0, 0) rotate(0deg) scale(1);
    }

    50% {
        transform: translate3d(-22px, 20px, 0) rotate(-5deg) scale(1.03);
    }
}

.drift-a {
    animation: driftA 16s ease-in-out infinite;
}

.drift-b {
    animation: driftB 19s ease-in-out infinite;
}

/* =========================================================
   HERO
   ========================================================= */

.hero-blob-field {
    /*
     * Tidak memakai ambient gradient tambahan.
     * Blob Hero adalah satu-satunya ambient source.
     */
    background: transparent;
}

/* =========================================================
   STAT NUMBER
   ========================================================= */

.stat-number {
    font-family: "Poppins", sans-serif;
    font-weight: 700;
    letter-spacing: -0.02em;
}

/* =========================================================
   ROUTE TABLE
   ========================================================= */

.route-row:nth-child(odd) {
    background: rgba(22, 58, 112, 0.05);
}

:global(.dark) .route-row:nth-child(odd) {
    background: rgb(255 255 255 / 0.04);
}

/* =========================================================
   PRIMARY BUTTON
   ========================================================= */

.btn-primary {
    background: #163a70;
    transition:
        background 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.btn-primary:hover {
    background: #1f4c91;
    transform: translateY(-1px);
    box-shadow: 0 10px 25px rgb(22 58 112 / 0.16);
}

/* =========================================================
   PROCESS LINE
   ========================================================= */

.process-line {
    background: linear-gradient(180deg, #2e6fbf, #163a70);
}

/* =========================================================
   FADE-IN
   ========================================================= */

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

/* =========================================================
   TABLET
   ========================================================= */

@media (max-width: 1024px) {
    .blob-organic-1 {
        width: 520px !important;
        height: 520px !important;
        top: -260px !important;
        right: -210px !important;
    }

    .blob-organic-2 {
        width: 300px !important;
        height: 300px !important;
        right: 70px !important;
    }
}

/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 640px) {
    .blob-organic-1 {
        width: 390px !important;
        height: 390px !important;
        top: -210px !important;
        right: -190px !important;
        opacity: 0.62 !important;
    }

    .blob-organic-2 {
        width: 240px !important;
        height: 240px !important;
        right: -55px !important;
        bottom: -90px !important;
        opacity: 0.55 !important;
    }
}

/* =========================================================
   REDUCED MOTION
   ========================================================= */

@media (prefers-reduced-motion: reduce) {
    :global(html) {
        scroll-behavior: auto;
    }

    .drift-a,
    .drift-b {
        animation: none;
    }

    .fade-in {
        opacity: 1;
        transform: none;
        transition: none;
    }
}
</style>
