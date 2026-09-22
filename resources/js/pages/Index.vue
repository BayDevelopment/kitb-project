<script setup>
import { ref, reactive } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

// Halaman ini adalah landing page publik (guest) — jangan pakai
// dashboard/sidebar layout, meskipun app.ts punya default layout global.
defineOptions({ layout: null });

const mobileMenuOpen = ref(false);

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
              const elapsed = now - start;
              const progress = Math.min(elapsed / duration, 1);
              const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
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

/* ---------------- Form jadwalkan kunjungan lahan ---------------- */
const showVisitForm = ref(false);
const showSuccessToast = ref(false);

const visitForm = useForm({
  nama: "",
  instansi: "",
  email: "",
  telepon: "",
  tanggal_kunjungan: "",
  jumlah_peserta: "",
  keperluan: "",
});

function openVisitForm() {
  showVisitForm.value = true;
}

function closeVisitForm() {
  showVisitForm.value = false;
  visitForm.clearErrors();
}

function submitVisitForm() {
  // Sesuaikan endpoint ini dengan route yang kamu buat di Laravel,
  // contoh: Route::post('/kunjungan-lahan', [KunjunganController::class, 'store']);
  visitForm.post("/kunjungan-lahan", {
    preserveScroll: true,
    onSuccess: () => {
      visitForm.reset();
      showVisitForm.value = false;
      showSuccessToast.value = true;
      setTimeout(() => (showSuccessToast.value = false), 5000);
    },
  });
}

const stats = [
  { target: 5600, decimals: 0, suffix: "Ha", label: "Wilayah pengembangan" },
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

const lahan = [
  {
    target: 5600.3,
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

const navLinks = [
  { href: "#tentang", label: "Tentang" },
  { href: "#visi-misi", label: "Visi & Misi" },
  { href: "#lokasi", label: "Lokasi" },
  { href: "#kawasan", label: "Kawasan" },
  { href: "#kontak", label: "Kontak" },
];

// Ganti href dengan akun resmi KITB yang sebenarnya
const socials = [
  { label: "Facebook", abbr: "f", href: "#" },
  { label: "Instagram", abbr: "ig", href: "#" },
  { label: "X (Twitter)", abbr: "x", href: "#" },
  { label: "LinkedIn", abbr: "in", href: "#" },
  { label: "YouTube", abbr: "yt", href: "#" },
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
      style="padding-top: env(safe-area-inset-top, 0px)"
    >
      <div class="backdrop-blur-md bg-kitb-sand-50/80 border-b border-black/5">
        <nav
          class="max-w-7xl mx-auto px-6 md:px-10 h-20 flex items-center justify-between"
        >
          <a href="#top" class="flex items-center gap-3">
            <img
              src="/images/siak-kabupaten.png"
              alt="Lambang Kabupaten Siak"
              class="h-10 w-auto object-contain shrink-0"
            />

            <img
              src="/images/kitb-logo.png"
              alt="Logo KITB"
              class="h-10 w-auto object-contain shrink-0"
            />

            <div
              class="hidden sm:block border-l border-black/10 pl-3 leading-tight"
            >
              <div
                class="text-[10px] tracking-wide text-black/50 whitespace-nowrap"
              >
                Tanjung Buton
              </div>
            </div>
          </a>

          <div
            class="hidden md:flex items-center gap-9 text-[14.5px] font-medium"
          >
            <a
              v-for="link in navLinks"
              :key="link.href"
              :href="link.href"
              class="nav-link"
            >
              {{ link.label }}
            </a>
          </div>

          <button
            type="button"
            class="hidden md:inline-flex items-center text-[14px] font-medium text-white px-5 py-2.5 rounded-full btn-primary"
            @click="openVisitForm"
          >
            Hubungi Kami
          </button>

          <button
            class="md:hidden w-10 h-10 flex items-center justify-center"
            aria-label="Menu"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
              <path
                d="M3 6h16M3 11h16M3 16h16"
                stroke="#101915"
                stroke-width="1.6"
                stroke-linecap="round"
              />
            </svg>
          </button>
        </nav>
      </div>

      <div
        v-show="mobileMenuOpen"
        class="md:hidden bg-kitb-sand-50 border-b border-black/5 px-6 py-5 flex flex-col gap-4 text-[15px] font-medium"
      >
        <a
          v-for="link in navLinks"
          :key="link.href"
          :href="link.href"
          @click="mobileMenuOpen = false"
          >{{ link.label }}</a
        >
        <button
          type="button"
          class="text-white text-center py-2.5 rounded-full mt-1 btn-primary"
          @click="
            mobileMenuOpen = false;
            openVisitForm();
          "
        >
          Hubungi Kami
        </button>
      </div>
    </header>

    <!-- ================= HERO ================= -->
    <section
      id="top"
      class="relative pt-40 pb-28 md:pt-48 md:pb-36 hero-blob-field overflow-hidden"
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
            #6fcbe0,
            #1ca3c9 45%,
            #0d6b4f 85%
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
          background: radial-gradient(circle at 60% 40%, #14895f, #0a3d2c 80%);
          opacity: 0.85;
        "
      />

      <div class="max-w-7xl mx-auto px-6 md:px-10 relative">
        <div class="max-w-2xl" v-fade-in>
          <div
            class="inline-flex items-center gap-2 text-[13px] font-medium px-3.5 py-1.5 rounded-full mb-8 bg-kitb-green-700/10 text-kitb-green-800"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-kitb-teal-500" />
            Badan Usaha Milik Daerah &middot; Kabupaten Siak
          </div>

          <h1
            class="font-display leading-[1.05] mb-7 text-kitb-green-900 font-bold"
            style="font-size: clamp(2.4rem, 5.4vw, 4.3rem)"
          >
            Kawasan industri yang berdiri tepat di bibir Selat Malaka.
          </h1>

          <p
            class="text-[17px] md:text-[18px] leading-relaxed max-w-lg mb-10 text-kitb-ink-900/70"
          >
            PT Kawasan Industri Tanjung Buton mengintegrasikan lahan industri,
            pelabuhan laut dalam, dan hilirisasi komoditas Riau menjadi satu
            ekosistem — di jalur pelayaran tersibuk di dunia.
          </p>

          <div class="flex flex-wrap items-center gap-4">
            <button
              type="button"
              class="inline-flex items-center text-[15px] font-medium text-white px-7 py-3.5 rounded-full btn-primary"
              @click="openVisitForm"
            >
              Jadwalkan kunjungan lahan
            </button>
            <a
              href="#kawasan"
              class="inline-flex items-center text-[15px] font-medium px-7 py-3.5 rounded-full border border-black/15 text-kitb-ink-900"
            >
              Lihat master plan
            </a>
          </div>
        </div>

        <div
          v-fade-in
          class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-24 pt-10 border-t border-black/10 max-w-4xl"
        >
          <div v-for="(s, i) in stats" :key="s.label">
            <div class="stat-number text-3xl md:text-4xl text-kitb-green-800">
              <span
                v-count-up="{
                  target: s.target,
                  decimals: s.decimals,
                  delay: i * 120,
                }"
                >0</span
              ><span class="text-lg align-top">{{ s.suffix }}</span>
            </div>
            <div class="text-[13px] mt-1 text-kitb-ink-900/55">
              {{ s.label }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= TENTANG ================= -->
    <section id="tentang" class="relative py-24 md:py-32 overflow-hidden">
      <div
        class="blob blob-organic-2 drift-b"
        style="
          width: 420px;
          height: 420px;
          top: -60px;
          left: -200px;
          background: radial-gradient(
            circle at 40% 40%,
            #6fcbe0,
            transparent 70%
          );
          opacity: 0.35;
        "
      />

      <div
        class="max-w-7xl mx-auto px-6 md:px-10 relative grid md:grid-cols-12 gap-12 md:gap-8"
      >
        <div class="md:col-span-4" v-fade-in>
          <p class="text-[13px] font-medium mb-4 text-kitb-teal-600">
            Tentang KITB
          </p>
          <h2
            class="font-display leading-[1.1] text-kitb-green-900 font-bold"
            style="font-size: clamp(1.9rem, 3.2vw, 2.6rem)"
          >
            Bukan sekadar penyedia lahan.
          </h2>
        </div>
        <div
          v-fade-in
          class="md:col-span-7 md:col-start-6 space-y-6 text-[16.5px] leading-relaxed text-kitb-ink-900/72"
        >
          <p>
            Di tengah dinamika ekonomi global, kebutuhan akan ruang industri
            yang efisien, terintegrasi, dan adaptif menjadi sebuah keniscayaan.
            KITB hadir sebagai pusat pertumbuhan ekonomi baru yang
            mengintegrasikan potensi darat dan keunggulan maritim Riau secara
            strategis.
          </p>
          <p>
            Mengusung moto
            <span class="font-display font-semibold text-kitb-green-800"
              >"Beyond Industry, Towards the Future,"</span
            >
            kami membangun ekosistem Smart &amp; Green Industrial Estate —
            mendorong hilirisasi bernilai tambah tinggi sekaligus menjaga
            keberlanjutan lingkungan dan kesejahteraan masyarakat sekitar.
          </p>
          <div class="pt-4 grid grid-cols-2 gap-6">
            <div class="border-l-2 pl-5 border-kitb-teal-500">
              <div
                class="font-display font-semibold text-lg mb-1 text-kitb-green-800"
              >
                Smart Industrial Park
              </div>
              <div class="text-[14.5px] text-kitb-ink-900/55">
                Digitalisasi &amp; otomasi untuk efisiensi operasional tenant.
              </div>
            </div>
            <div class="border-l-2 pl-5 border-kitb-amber-500">
              <div
                class="font-display font-semibold text-lg mb-1 text-kitb-green-800"
              >
                Green Industrial Estate
              </div>
              <div class="text-[14.5px] text-kitb-ink-900/55">
                Energi terbarukan, efisiensi air, pengelolaan limbah ramah
                lingkungan.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= VISI MISI (dark) ================= -->
    <section
      id="visi-misi"
      class="relative py-24 md:py-32 overflow-hidden bg-kitb-green-900"
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
            #1ca3c9,
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
          background: #6fcbe0;
          opacity: 0.08;
        "
      />

      <div class="max-w-7xl mx-auto px-6 md:px-10 relative">
        <div class="max-w-xl mb-16" v-fade-in>
          <p class="text-[13px] font-medium mb-4 text-kitb-teal-300">Visi</p>
          <h2
            class="font-display leading-[1.15] text-white font-bold"
            style="font-size: clamp(1.9rem, 3.4vw, 2.7rem)"
          >
            Menjadi kawasan industri dan maritim terpadu yang berkelanjutan,
            pintar, dan berdaya saing global.
          </h2>
        </div>

        <p class="text-[13px] font-medium mb-8 text-kitb-teal-300">Misi</p>
        <div class="grid md:grid-cols-2 gap-x-10 gap-y-10">
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
            <h3 class="font-display font-semibold text-white text-xl mb-2.5">
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
    <section id="lokasi" class="relative py-24 md:py-32 overflow-hidden">
      <div class="max-w-7xl mx-auto px-6 md:px-10">
        <div class="grid md:grid-cols-12 gap-12 items-start">
          <div class="md:col-span-5" v-fade-in>
            <p class="text-[13px] font-medium mb-4 text-kitb-teal-600">
              Keunggulan geografis
            </p>
            <h2
              class="font-display leading-[1.1] mb-6 text-kitb-green-900 font-bold"
              style="font-size: clamp(1.9rem, 3.2vw, 2.6rem)"
            >
              Menghadap langsung jalur pelayaran tersibuk di dunia.
            </h2>
            <p class="text-[16px] leading-relaxed mb-8 text-kitb-ink-900/70">
              Selat Malaka adalah jalur utama lalu lintas perdagangan dari India
              ke Timur Tengah, dan dari Asia Timur ke Pasifik. Tanjung Buton
              berdiri tepat di jalur ini — menjadikannya gerbang ekspor-impor
              yang sangat efisien.
            </p>
            <div class="space-y-4">
              <div class="flex items-start gap-3.5">
                <div
                  class="w-1.5 h-1.5 rounded-full mt-2.5 shrink-0 bg-kitb-teal-500"
                />
                <p class="text-[15px] text-kitb-ink-900/68">
                  Panjang alur pelayaran &plusmn; 36 mil, lebar alur 15&ndash;17
                  m LWS
                </p>
              </div>
              <div class="flex items-start gap-3.5">
                <div
                  class="w-1.5 h-1.5 rounded-full mt-2.5 shrink-0 bg-kitb-teal-500"
                />
                <p class="text-[15px] text-kitb-ink-900/68">
                  Fasilitas kapal labuh dan pelayanan pemanduan tersedia
                </p>
              </div>
              <div class="flex items-start gap-3.5">
                <div
                  class="w-1.5 h-1.5 rounded-full mt-2.5 shrink-0 bg-kitb-teal-500"
                />
                <p class="text-[15px] text-kitb-ink-900/68">
                  Ombak relatif kecil (0,32&ndash;0,98 m), arus maksimal
                  0,68&ndash;0,83 m/detik
                </p>
              </div>
            </div>
          </div>

          <div
            class="md:col-span-6 md:col-start-7"
            v-fade-in
            style="transition-delay: 120ms"
          >
            <div class="rounded-2xl overflow-hidden bg-kitb-sand-100">
              <table class="w-full text-[14.5px]">
                <thead>
                  <tr class="text-left bg-kitb-green-700">
                    <th class="px-6 py-4 font-medium text-white text-[13px]">
                      Rute
                    </th>
                    <th class="px-6 py-4 font-medium text-white text-[13px]">
                      Jalur
                    </th>
                    <th class="px-6 py-4 font-medium text-white text-[13px]">
                      Jarak
                    </th>
                    <th class="px-6 py-4 font-medium text-white text-[13px]">
                      Waktu tempuh
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in routes" :key="r.name" class="route-row">
                    <td class="px-6 py-4 font-medium text-kitb-green-800">
                      {{ r.name }}
                    </td>
                    <td class="px-6 py-4 text-kitb-ink-900/70">{{ r.path }}</td>
                    <td class="px-6 py-4 text-kitb-ink-900/70">
                      {{ r.distance }}
                    </td>
                    <td class="px-6 py-4 text-kitb-ink-900/70">{{ r.time }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p class="text-[13px] mt-4 text-kitb-ink-900/45">
              Rute 2 melalui Selat Asam adalah jalur tersingkat menuju Selat
              Malaka.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= KAWASAN / MASTER PLAN ================= -->
    <section
      id="kawasan"
      class="relative py-24 md:py-32 overflow-hidden bg-kitb-sand-100"
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
            #14895f,
            transparent 70%
          );
          opacity: 0.18;
        "
      />

      <div class="max-w-7xl mx-auto px-6 md:px-10 relative">
        <div class="max-w-xl mb-16" v-fade-in>
          <p class="text-[13px] font-medium mb-4 text-kitb-teal-600">
            Ketersediaan lahan
          </p>
          <h2
            class="font-display leading-[1.1] text-kitb-green-900 font-bold"
            style="font-size: clamp(1.9rem, 3.2vw, 2.6rem)"
          >
            5.600 hektar, terbagi dalam tahapan yang jelas.
          </h2>
        </div>

        <div v-fade-in class="grid md:grid-cols-3 gap-10 mb-20">
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
                >0</span
              ><span class="text-xl align-top ml-1">{{ l.unit }}</span>
            </div>
            <div class="text-[14.5px] text-kitb-ink-900/55">{{ l.label }}</div>
          </div>
        </div>

        <div class="border-t pt-16 border-black/10">
          <p class="text-[13px] font-medium mb-10 text-kitb-teal-600">
            Pembangunan tahap 1 &middot; 300 Ha
          </p>
          <div class="grid md:grid-cols-3 gap-x-8 gap-y-12 relative">
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
              <p class="text-[14.5px] leading-relaxed text-kitb-ink-900/62">
                {{ t.desc }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= CTA ================= -->
    <section class="relative py-20 md:py-28">
      <div class="max-w-7xl mx-auto px-6 md:px-10">
        <div
          v-fade-in
          class="rounded-3xl px-8 py-14 md:px-16 md:py-20 text-center relative overflow-hidden bg-kitb-green-700"
        >
          <div
            class="blob blob-organic-1 drift-a"
            style="
              width: 300px;
              height: 300px;
              top: -100px;
              left: -80px;
              background: #1ca3c9;
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
              background: #d98a2b;
              opacity: 0.15;
            "
          />
          <p class="text-[13px] font-medium mb-5 relative text-kitb-teal-300">
            Naik ke panggung industri global
          </p>
          <h2
            class="font-display text-white leading-[1.15] max-w-2xl mx-auto relative font-bold"
            style="font-size: clamp(1.8rem, 3.6vw, 2.8rem)"
          >
            Bersama para mitra dan investor, kami siap memimpin transformasi
            industri Indonesia.
          </h2>
          <div class="mt-10 relative">
            <button
              type="button"
              class="inline-flex items-center text-[15px] font-medium px-8 py-3.5 rounded-full bg-kitb-sand-50 text-kitb-green-800"
              @click="openVisitForm"
            >
              Mulai diskusi investasi
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= FOOTER / KONTAK ================= -->
    <footer
      id="kontak"
      class="relative pt-20 pb-8 overflow-hidden bg-kitb-green-900"
    >
      <div
        class="blob blob-organic-2"
        style="
          width: 400px;
          height: 400px;
          bottom: -200px;
          left: -150px;
          background: #1ca3c9;
          opacity: 0.1;
        "
      />

      <div class="max-w-7xl mx-auto px-6 md:px-10 relative">
        <!-- Logo, alamat, email — center, seperti header Pertamina -->
        <div class="flex flex-col items-center text-center mb-16">
          <div class="flex items-center gap-4 mb-4">
            <img
              src="/images/siak-kabupaten.png"
              alt="Lambang Kabupaten Siak"
              class="h-14 w-auto rounded-sm"
            />
            <img
              src="/images/kitb-logo.png"
              alt="Logo KITB"
              class="h-14 w-auto"
            />
          </div>
          <div class="text-[11px] tracking-wide text-white/45 mb-6">
            Kawasan Industri Tanjung Buton
          </div>
          <p class="text-[14.5px] text-white/60 max-w-md leading-relaxed">
            <span class="font-medium text-white/85">Alamat:</span> Kampung
            Mengkapan &amp; Kampung Sungai Rawa, Kecamatan Sungai Apit,
            Kabupaten Siak, Provinsi Riau
          </p>
          <p class="text-[14.5px] text-white/60 mt-1.5">
            <span class="font-medium text-white/85">Email:</span>
            <a
              href="mailto:info@kitb.co.id"
              class="hover:text-white transition-colors"
              >info@kitb.co.id</a
            >
          </p>
        </div>

        <!-- Kolom navigasi, disesuaikan dari isi compro -->
        <div
          class="grid grid-cols-2 md:grid-cols-5 gap-x-8 gap-y-10 pb-14 border-b border-white/10"
        >
          <div>
            <h4 class="text-[13px] font-semibold text-white mb-4">
              Perusahaan
            </h4>
            <ul class="flex flex-col gap-2.5 text-[14.5px] text-white/55">
              <li>
                <a href="#tentang" class="hover:text-white transition-colors"
                  >Tentang KITB</a
                >
              </li>
              <li>
                <a href="#visi-misi" class="hover:text-white transition-colors"
                  >Visi &amp; Misi</a
                >
              </li>
              <li>
                <a href="#" class="hover:text-white transition-colors"
                  >Struktur Perusahaan</a
                >
              </li>
              <li>
                <a href="#" class="hover:text-white transition-colors"
                  >Latar Belakang</a
                >
              </li>
            </ul>
          </div>

          <div>
            <h4 class="text-[13px] font-semibold text-white mb-4">Kawasan</h4>
            <ul class="flex flex-col gap-2.5 text-[14.5px] text-white/55">
              <li>
                <a href="#kawasan" class="hover:text-white transition-colors"
                  >Master Plan</a
                >
              </li>
              <li>
                <a href="#kawasan" class="hover:text-white transition-colors"
                  >Ketersediaan Lahan</a
                >
              </li>
              <li>
                <a href="#" class="hover:text-white transition-colors"
                  >Pembangunan Tahap 1</a
                >
              </li>
              <li>
                <a href="#" class="hover:text-white transition-colors"
                  >Kawasan Pelabuhan</a
                >
              </li>
            </ul>
          </div>

          <div>
            <h4 class="text-[13px] font-semibold text-white mb-4">Investor</h4>
            <ul class="flex flex-col gap-2.5 text-[14.5px] text-white/55">
              <li>
                <button
                  type="button"
                  class="hover:text-white transition-colors text-left"
                  @click="openVisitForm"
                >
                  Jadwalkan Kunjungan
                </button>
              </li>
              <li>
                <a href="#lokasi" class="hover:text-white transition-colors"
                  >Rute Pelayaran</a
                >
              </li>
              <li>
                <a href="#" class="hover:text-white transition-colors"
                  >Peluang Investasi</a
                >
              </li>
              <li>
                <a href="#" class="hover:text-white transition-colors"
                  >Ease of Doing Business</a
                >
              </li>
            </ul>
          </div>

          <div>
            <h4 class="text-[13px] font-semibold text-white mb-4">
              Anak Usaha
            </h4>
            <ul class="flex flex-col gap-2.5 text-[14.5px] text-white/55">
              <li>
                <a href="#" class="hover:text-white transition-colors"
                  >PT. Siak Industrial Park</a
                >
              </li>
              <li>
                <a href="#" class="hover:text-white transition-colors"
                  >PT. Siak Industrial Outsource</a
                >
              </li>
            </ul>
          </div>

          <div>
            <h4 class="text-[13px] font-semibold text-white mb-4">
              Kontak Kami
            </h4>
            <a
              href="mailto:info@kitb.co.id"
              class="inline-flex items-center text-[13.5px] font-medium text-white px-4 py-2 rounded-full bg-kitb-teal-500 mb-4"
            >
              info@kitb.co.id
            </a>
            <div class="flex items-center gap-2.5">
              <a
                v-for="soc in socials"
                :key="soc.label"
                :href="soc.href"
                :aria-label="soc.label"
                class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-semibold bg-white/10 text-white/70 hover:bg-kitb-teal-500 hover:text-white transition-colors"
              >
                {{ soc.abbr }}
              </a>
            </div>
          </div>
        </div>

        <!-- Bottom bar -->
        <div
          class="pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-[13px] text-white/40"
        >
          <p
            class="flex flex-wrap items-center gap-x-2 justify-center md:justify-start"
          >
            <span
              >&copy; 2026 PT. Kawasan Industri Tanjung Buton. Badan Usaha Milik
              Daerah, Kabupaten Siak.</span
            >
            <span class="hidden md:inline">/</span>
            <a href="#" class="underline hover:text-white transition-colors"
              >Kebijakan Privasi</a
            >
            <span class="hidden md:inline">/</span>
            <a href="#" class="underline hover:text-white transition-colors"
              >Pengaduan</a
            >
          </p>
          <p>Beyond Industry, Towards the Future.</p>
        </div>
      </div>
    </footer>

    <!-- ================= MODAL: JADWALKAN KUNJUNGAN LAHAN ================= -->
    <Transition name="modal-fade">
      <div
        v-if="showVisitForm"
        class="fixed inset-0 z-[100] flex items-center justify-center px-4 py-10"
      >
        <div
          class="absolute inset-0 bg-kitb-ink-900/60 backdrop-blur-sm"
          @click="closeVisitForm"
        />

        <div
          class="relative w-full max-w-lg max-h-[88vh] overflow-y-auto rounded-3xl bg-kitb-sand-50 shadow-2xl"
        >
          <div
            class="sticky top-0 flex items-start justify-between px-7 pt-7 pb-4 bg-kitb-sand-50"
          >
            <div>
              <p class="text-[13px] font-medium text-kitb-teal-600 mb-1">
                Kunjungan Lahan
              </p>
              <h3 class="font-display font-bold text-xl text-kitb-green-900">
                Jadwalkan kunjungan Anda
              </h3>
            </div>
            <button
              type="button"
              aria-label="Tutup"
              class="w-9 h-9 rounded-full flex items-center justify-center hover:bg-black/5 shrink-0"
              @click="closeVisitForm"
            >
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path
                  d="M4 4l10 10M14 4L4 14"
                  stroke="#101915"
                  stroke-width="1.6"
                  stroke-linecap="round"
                />
              </svg>
            </button>
          </div>

          <form class="px-7 pb-7 space-y-4" @submit.prevent="submitVisitForm">
            <div>
              <label
                class="block text-[13px] font-medium mb-1.5 text-kitb-ink-900/70"
                >Nama lengkap</label
              >
              <input
                v-model="visitForm.nama"
                type="text"
                required
                class="w-full rounded-xl border border-black/10 bg-white px-4 py-2.5 text-[15px] outline-none focus:border-kitb-teal-500 focus:ring-2 focus:ring-kitb-teal-500/20"
                placeholder="Nama Anda"
              />
              <p
                v-if="visitForm.errors.nama"
                class="text-[13px] text-red-600 mt-1"
              >
                {{ visitForm.errors.nama }}
              </p>
            </div>

            <div>
              <label
                class="block text-[13px] font-medium mb-1.5 text-kitb-ink-900/70"
                >Instansi / Perusahaan</label
              >
              <input
                v-model="visitForm.instansi"
                type="text"
                class="w-full rounded-xl border border-black/10 bg-white px-4 py-2.5 text-[15px] outline-none focus:border-kitb-teal-500 focus:ring-2 focus:ring-kitb-teal-500/20"
                placeholder="Nama perusahaan / instansi"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label
                  class="block text-[13px] font-medium mb-1.5 text-kitb-ink-900/70"
                  >Email</label
                >
                <input
                  v-model="visitForm.email"
                  type="email"
                  required
                  class="w-full rounded-xl border border-black/10 bg-white px-4 py-2.5 text-[15px] outline-none focus:border-kitb-teal-500 focus:ring-2 focus:ring-kitb-teal-500/20"
                  placeholder="nama@email.com"
                />
                <p
                  v-if="visitForm.errors.email"
                  class="text-[13px] text-red-600 mt-1"
                >
                  {{ visitForm.errors.email }}
                </p>
              </div>
              <div>
                <label
                  class="block text-[13px] font-medium mb-1.5 text-kitb-ink-900/70"
                  >No. Telepon</label
                >
                <input
                  v-model="visitForm.telepon"
                  type="tel"
                  required
                  class="w-full rounded-xl border border-black/10 bg-white px-4 py-2.5 text-[15px] outline-none focus:border-kitb-teal-500 focus:ring-2 focus:ring-kitb-teal-500/20"
                  placeholder="08xx-xxxx-xxxx"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label
                  class="block text-[13px] font-medium mb-1.5 text-kitb-ink-900/70"
                  >Tanggal kunjungan</label
                >
                <input
                  v-model="visitForm.tanggal_kunjungan"
                  type="date"
                  required
                  class="w-full rounded-xl border border-black/10 bg-white px-4 py-2.5 text-[15px] outline-none focus:border-kitb-teal-500 focus:ring-2 focus:ring-kitb-teal-500/20"
                />
              </div>
              <div>
                <label
                  class="block text-[13px] font-medium mb-1.5 text-kitb-ink-900/70"
                  >Jumlah peserta</label
                >
                <input
                  v-model="visitForm.jumlah_peserta"
                  type="number"
                  min="1"
                  class="w-full rounded-xl border border-black/10 bg-white px-4 py-2.5 text-[15px] outline-none focus:border-kitb-teal-500 focus:ring-2 focus:ring-kitb-teal-500/20"
                  placeholder="1"
                />
              </div>
            </div>

            <div>
              <label
                class="block text-[13px] font-medium mb-1.5 text-kitb-ink-900/70"
                >Keperluan / catatan</label
              >
              <textarea
                v-model="visitForm.keperluan"
                rows="3"
                class="w-full rounded-xl border border-black/10 bg-white px-4 py-2.5 text-[15px] outline-none focus:border-kitb-teal-500 focus:ring-2 focus:ring-kitb-teal-500/20 resize-none"
                placeholder="Ceritakan tujuan kunjungan Anda"
              />
            </div>

            <button
              type="submit"
              :disabled="visitForm.processing"
              class="w-full inline-flex items-center justify-center text-[15px] font-medium text-white px-7 py-3.5 rounded-full btn-primary disabled:opacity-60"
            >
              {{
                visitForm.processing ? "Mengirim..." : "Kirim jadwal kunjungan"
              }}
            </button>
          </form>
        </div>
      </div>
    </Transition>

    <!-- ================= TOAST SUKSES ================= -->
    <Transition name="toast-fade">
      <div
        v-if="showSuccessToast"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[110] bg-kitb-green-900 text-white text-[14.5px] px-6 py-3.5 rounded-full shadow-xl"
      >
        Permintaan kunjungan terkirim — tim kami akan menghubungi Anda.
      </div>
    </Transition>
  </div>
</template>

<style scoped>

.kitb-landing {
  font-family: "Poppins", ui-sans-serif, system-ui, sans-serif;
}

.kitb-landing * {
  font-family: inherit;
}

.blob {
  position: absolute;
  pointer-events: none;
}
.blob-organic-1 {
  border-radius: 42% 58% 65% 35% / 45% 40% 60% 55%;
}
.blob-organic-2 {
  border-radius: 63% 37% 30% 70% / 50% 45% 55% 50%;
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

@media (prefers-reduced-motion: reduce) {
  .drift-a,
  .drift-b {
    animation: none;
  }
}

.hero-blob-field {
  background: radial-gradient(
    circle at 30% 20%,
    rgba(28, 163, 201, 0.1),
    transparent 55%
  );
}

.stat-number {
  font-family: "Poppins", sans-serif;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.nav-link {
  position: relative;
}
.nav-link::after {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  bottom: -4px;
  height: 1.5px;
  background: #1ca3c9;
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.25s ease;
}
.nav-link:hover::after {
  transform: scaleX(1);
}

.route-row:nth-child(odd) {
  background: rgba(13, 107, 79, 0.04);
}

.btn-primary {
  background: #0d6b4f;
  transition:
    background 0.2s ease,
    transform 0.2s ease;
}
.btn-primary:hover {
  background: #14895f;
  transform: translateY(-1px);
}

.process-line {
  background: linear-gradient(180deg, #1ca3c9, #14895f);
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

/* ---------------- Modal / toast transitions ---------------- */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-active > div:last-child,
.modal-fade-leave-active > div:last-child {
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}
.modal-fade-enter-from > div:last-child,
.modal-fade-leave-to > div:last-child {
  opacity: 0;
  transform: scale(0.96) translateY(8px);
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition:
    opacity 0.3s ease,
    transform 0.3s ease;
}
.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translate(-50%, 12px);
}
</style>
