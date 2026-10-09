<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";

import { Head, Link, useForm } from "@inertiajs/vue3";

import {
    Building2,
    CheckCircle2,
    ChevronRight,
    Clock3,
    ExternalLink,
    Home,
    Mail,
    MapPin,
    MessageSquare,
    Navigation,
    Phone,
    Send,
    Smartphone,
    X,
} from "lucide-vue-next";

import L from "leaflet";
import "leaflet/dist/leaflet.css";

import PublicLayout from "@/layouts/PublicLayout.vue";
import { currentLanguage } from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

/* =========================================================
   Props
========================================================= */

interface Kontak {
    nama_perusahaan: string | null;
    alamat: string | null;
    telepon: string | null;
    whatsapp: string | null;
    email: string | null;
    email_investor: string | null;
    jam_operasional: string | null;
    latitude: number | null;
    longitude: number | null;
    maps_embed_url: string | null;
    facebook: string | null;
    instagram: string | null;
    linkedin: string | null;
    youtube: string | null;
}

interface Props {
    kontak: Kontak;
}

const props = defineProps<Props>();

/* =========================================================
   Static UI Translations
   ========================================================= */

const translations = {
    id: {
        pageTitle: "Kontak - KITB",
        metaDescription:
            "Hubungi :company untuk informasi kawasan industri, investasi, layanan, dan kebutuhan bisnis Anda.",
        home: "Beranda",
        breadcrumbAria: "Navigasi breadcrumb",
        contact: "Kontak",
        contactUs: "Hubungi Kami",
        heroTitle: "Mari Terhubung dengan KITB",
        heroDescription:
            "Sampaikan pertanyaan, kebutuhan informasi, atau peluang kerja sama kepada tim kami. Kami siap membantu Anda mendapatkan informasi yang dibutuhkan.",
        contactInformation: "Informasi Kontak",
        readyToHelp: "Kami siap membantu",
        contactIntro:
            "Gunakan informasi di bawah ini untuk menghubungi tim :company.",
        location: "Lokasi",
        openGoogleMaps: "Buka lokasi di Google Maps",
        email: "Email",
        investorEmail: "Email Investor",
        phoneWhatsapp: "Telepon & WhatsApp",
        whatsapp: "WhatsApp",
        operatingHours: "Jam Operasional",
        mapTitle: "Peta Lokasi",
        mapSubtitle: "Lokasi kawasan KITB",
        mapAria: "Peta lokasi KITB",
        sendMessage: "Kirim Pesan",
        formIntro: "Isi formulir berikut dan tim kami akan menindaklanjutinya.",
        formErrorTitle: "Formulir belum dapat dikirim",
        formErrorDescription:
            "Terdapat beberapa data yang perlu diperbaiki. Silakan periksa keterangan berwarna merah pada setiap kolom.",
        fullName: "Nama Lengkap",
        fullNamePlaceholder: "Nama lengkap",
        emailPlaceholder: "nama@perusahaan.com",
        phone: "Nomor Telepon",
        company: "Perusahaan",
        companyPlaceholder: "Nama perusahaan",
        subject: "Subjek",
        subjectPlaceholder: "Contoh: Informasi investasi kawasan",
        message: "Pesan",
        messagePlaceholder: "Tuliskan kebutuhan atau pertanyaan Anda...",
        consent:
            "Dengan mengirim pesan ini, Anda memberikan informasi yang diperlukan agar tim KITB dapat menghubungi Anda kembali.",
        sending: "Mengirim...",
        bottomTitle: "Ingin mengetahui lebih banyak tentang :company?",
        bottomDescription:
            "Sampaikan kebutuhan Anda melalui formulir di atas dan kami akan membantu mengarahkan informasi yang sesuai.",
        closeDialog: "Tutup dialog",
        successTitle: "Pesan Berhasil Dikirim",
        successDescription:
            "Terima kasih telah menghubungi kami. Tim KITB akan membalas melalui email Anda.",
        successEmailNote:
            "Silakan periksa inbox email Anda. Tim kami akan menindaklanjuti pesan yang telah Anda kirimkan.",
        close: "Tutup",
    },
    en: {
        pageTitle: "Contact - KITB",
        metaDescription:
            "Contact :company for information about the industrial estate, investment, services, and business opportunities.",
        home: "Home",
        breadcrumbAria: "Breadcrumb navigation",
        contact: "Contact",
        contactUs: "Contact Us",
        heroTitle: "Let's Connect with KITB",
        heroDescription:
            "Send us your questions, information requests, or potential collaboration opportunities. Our team is ready to help you find the information you need.",
        contactInformation: "Contact Information",
        readyToHelp: "We're here to help",
        contactIntro: "Use the information below to contact the :company team.",
        location: "Location",
        openGoogleMaps: "Open location in Google Maps",
        email: "Email",
        investorEmail: "Investor Email",
        phoneWhatsapp: "Phone & WhatsApp",
        whatsapp: "WhatsApp",
        operatingHours: "Operating Hours",
        mapTitle: "Location Map",
        mapSubtitle: "KITB industrial estate location",
        mapAria: "Map showing the KITB location",
        sendMessage: "Send a Message",
        formIntro:
            "Complete the form below and our team will follow up with you.",
        formErrorTitle: "Your message could not be submitted",
        formErrorDescription:
            "Some information needs to be corrected. Please check the red messages shown under the relevant fields.",
        fullName: "Full Name",
        fullNamePlaceholder: "Your full name",
        emailPlaceholder: "name@company.com",
        phone: "Phone Number",
        company: "Company",
        companyPlaceholder: "Company name",
        subject: "Subject",
        subjectPlaceholder: "Example: Industrial estate investment information",
        message: "Message",
        messagePlaceholder: "Tell us what you need or ask us a question...",
        consent:
            "By sending this message, you provide the information needed for the KITB team to contact you.",
        sending: "Sending...",
        bottomTitle: "Want to learn more about :company?",
        bottomDescription:
            "Tell us what you need using the form above, and we will help direct you to the relevant information.",
        closeDialog: "Close dialog",
        successTitle: "Message Sent Successfully",
        successDescription:
            "Thank you for contacting us. The KITB team will reply to you by email.",
        successEmailNote:
            "Please check your email inbox. Our team will follow up on the message you sent.",
        close: "Close",
    },
    zh: {
        pageTitle: "联系 KITB",
        metaDescription:
            "联系 :company，了解工业园区、投资、服务及商业合作机会。",
        home: "首页",
        breadcrumbAria: "面包屑导航",
        contact: "联系我们",
        contactUs: "联系我们",
        heroTitle: "与 KITB 建立联系",
        heroDescription:
            "欢迎向我们提出问题、咨询相关信息或探讨合作机会。我们的团队将竭诚协助您获取所需信息。",
        contactInformation: "联系信息",
        readyToHelp: "我们随时为您提供帮助",
        contactIntro: "您可以通过以下信息联系 :company 团队。",
        location: "地址",
        openGoogleMaps: "在 Google 地图中查看位置",
        email: "电子邮箱",
        investorEmail: "投资者邮箱",
        phoneWhatsapp: "电话与 WhatsApp",
        whatsapp: "WhatsApp",
        operatingHours: "办公时间",
        mapTitle: "位置地图",
        mapSubtitle: "KITB 工业园区位置",
        mapAria: "KITB 位置地图",
        sendMessage: "发送消息",
        formIntro: "请填写以下表格，我们的团队将尽快跟进。",
        formErrorTitle: "消息暂时无法提交",
        formErrorDescription:
            "部分信息需要修改。请检查相关输入框下方的红色提示。",
        fullName: "姓名",
        fullNamePlaceholder: "请输入姓名",
        emailPlaceholder: "name@company.com",
        phone: "电话号码",
        company: "公司名称",
        companyPlaceholder: "请输入公司名称",
        subject: "主题",
        subjectPlaceholder: "例如：工业园区投资信息咨询",
        message: "消息内容",
        messagePlaceholder: "请填写您的需求或问题……",
        consent: "发送此消息即表示您提供必要的信息，以便 KITB 团队与您联系。",
        sending: "正在发送……",
        bottomTitle: "想进一步了解 :company？",
        bottomDescription:
            "请通过上方表格告诉我们您的需求，我们将协助您获取相关信息。",
        closeDialog: "关闭对话框",
        successTitle: "消息发送成功",
        successDescription: "感谢您与我们联系。KITB 团队将通过电子邮件回复您。",
        successEmailNote:
            "请查看您的电子邮箱收件箱。我们的团队将跟进您发送的消息。",
        close: "关闭",
    },
} as const;

const t = computed(() => {
    const language = currentLanguage.value as string;
    const normalizedLanguage = language === "zh_CN" ? "zh" : language;
    return (
        translations[normalizedLanguage as keyof typeof translations] ??
        translations.id
    );
});

const withCompany = (template: string) =>
    template.replaceAll(":company", companyName.value);

/* =========================================================
   Contact Helpers
========================================================= */

const companyName = computed(
    () => props.kontak?.nama_perusahaan || "PT. Kawasan Industri Tanjung Buton",
);

const emailHref = computed(() => {
    if (!props.kontak?.email) {
        return "#";
    }

    return `mailto:${props.kontak.email}`;
});

const investorEmailHref = computed(() => {
    if (!props.kontak?.email_investor) {
        return "#";
    }

    return `mailto:${props.kontak.email_investor}`;
});

const phoneHref = computed(() => {
    if (!props.kontak?.telepon) {
        return "#";
    }

    return `tel:${props.kontak.telepon.replace(/[^\d+]/g, "")}`;
});

const whatsappHref = computed(() => {
    if (!props.kontak?.whatsapp) {
        return "#";
    }

    const number = props.kontak.whatsapp.replace(/\D/g, "");

    if (!number) {
        return "#";
    }

    return `https://wa.me/${number}`;
});

const hasCoordinates = computed(() => {
    return (
        props.kontak?.latitude !== null &&
        props.kontak?.latitude !== undefined &&
        props.kontak?.longitude !== null &&
        props.kontak?.longitude !== undefined
    );
});

const googleMapsHref = computed(() => {
    if (props.kontak?.maps_embed_url) {
        return props.kontak.maps_embed_url;
    }

    if (!hasCoordinates.value) {
        return "#";
    }

    return `https://www.google.com/maps/search/?api=1&query=${props.kontak.latitude},${props.kontak.longitude}`;
});

/* =========================================================
   Form
========================================================= */

const form = useForm({
    nama: "",
    email: "",
    telepon: "",
    perusahaan: "",
    subjek: "",
    pesan: "",
    website: "",
});

/* =========================================================
   Refs
========================================================= */

const formRef = ref<HTMLFormElement | null>(null);

const firstInputRef = ref<HTMLInputElement | null>(null);

const mapContainer = ref<HTMLDivElement | null>(null);

const successCloseButtonRef = ref<HTMLButtonElement | null>(null);

/* =========================================================
   Success Modal
========================================================= */

const showSuccessModal = ref(false);

const closeSuccessModal = async () => {
    showSuccessModal.value = false;

    await nextTick();

    firstInputRef.value?.focus();
};

const handleSuccessModalKeydown = (event: KeyboardEvent) => {
    if (!showSuccessModal.value) {
        return;
    }

    if (event.key === "Escape") {
        event.preventDefault();
        closeSuccessModal();
    }
};

/* =========================================================
   Loading State
========================================================= */

const isPageLoading = ref(true);

/* =========================================================
   Reduced Motion
========================================================= */

const prefersReducedMotion = ref(false);

let mediaQuery: MediaQueryList | null = null;

const handleReducedMotionChange = (event: MediaQueryListEvent) => {
    prefersReducedMotion.value = event.matches;

    if (event.matches) {
        document
            .querySelectorAll<HTMLElement>("[data-reveal]")
            .forEach((element) => {
                element.classList.add("is-visible");
            });
    }
};

/* =========================================================
   Reveal Observer
========================================================= */

let revealObserver: IntersectionObserver | null = null;

const initializeReveal = async () => {
    await nextTick();

    const elements = document.querySelectorAll<HTMLElement>("[data-reveal]");

    if (!elements.length) {
        return;
    }

    if (
        prefersReducedMotion.value ||
        typeof IntersectionObserver === "undefined"
    ) {
        elements.forEach((element) => {
            element.classList.add("is-visible");
        });

        return;
    }

    revealObserver?.disconnect();

    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add("is-visible");

                revealObserver?.unobserve(entry.target);
            });
        },
        {
            threshold: 0.08,
            rootMargin: "0px 0px -40px 0px",
        },
    );

    elements.forEach((element) => {
        revealObserver?.observe(element);
    });
};

/* =========================================================
   Leaflet Map
========================================================= */

let map: L.Map | null = null;

let marker: L.Marker | null = null;

const escapeHtml = (value: string) => {
    return value
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
};

const initializeMap = async () => {
    await nextTick();

    if (
        !mapContainer.value ||
        !hasCoordinates.value ||
        typeof window === "undefined"
    ) {
        return;
    }

    if (map) {
        map.remove();

        map = null;
        marker = null;
    }

    const latitude = Number(props.kontak.latitude);

    const longitude = Number(props.kontak.longitude);

    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
        return;
    }

    map = L.map(mapContainer.value, {
        center: [latitude, longitude],
        zoom: 15,
        scrollWheelZoom: false,
        zoomControl: true,
    });

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    marker = L.marker([latitude, longitude])
        .addTo(map)
        .bindPopup(
            `
                <div style="min-width: 180px">
                    <strong>${escapeHtml(companyName.value)}</strong>
                    ${
                        props.kontak.alamat
                            ? `<br><span>${escapeHtml(
                                  props.kontak.alamat,
                              )}</span>`
                            : ""
                    }
                </div>
            `,
        );

    marker.openPopup();

    window.setTimeout(() => {
        map?.invalidateSize();
    }, 150);
};

/* =========================================================
   Form Helpers
========================================================= */

const messageLength = computed(() => form.pesan.length);

const messageProgress = computed(() =>
    Math.min((messageLength.value / 3000) * 100, 100),
);

const hasErrors = computed(() => Object.keys(form.errors).length > 0);

/* =========================================================
   Validation Focus
========================================================= */

const focusFirstError = async () => {
    await nextTick();

    const firstErrorField = document.querySelector<HTMLElement>(
        '[aria-invalid="true"]',
    );

    if (firstErrorField) {
        firstErrorField.focus();
    }
};

/* =========================================================
   Submit
========================================================= */

const submit = () => {
    if (form.processing) {
        return;
    }

    form.clearErrors();

    form.post("/kontak", {
        preserveScroll: true,

        onSuccess: async () => {
            form.reset();

            showSuccessModal.value = true;

            await nextTick();

            successCloseButtonRef.value?.focus();
        },

        onError: async () => {
            await focusFirstError();
        },
    });
};

/* =========================================================
   Lifecycle
========================================================= */

onMounted(async () => {
    mediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");

    prefersReducedMotion.value = mediaQuery.matches;

    mediaQuery.addEventListener("change", handleReducedMotionChange);

    window.addEventListener("keydown", handleSuccessModalKeydown);

    await new Promise<void>((resolve) => {
        window.setTimeout(resolve, 350);
    });

    isPageLoading.value = false;

    await nextTick();

    await initializeReveal();

    if (hasCoordinates.value) {
        await initializeMap();
    }
});

onBeforeUnmount(() => {
    mediaQuery?.removeEventListener("change", handleReducedMotionChange);

    window.removeEventListener("keydown", handleSuccessModalKeydown);

    revealObserver?.disconnect();

    if (map) {
        map.remove();

        map = null;
        marker = null;
    }
});
</script>

<template>
    <Head>
        <title>{{ t.pageTitle }}</title>

        <meta name="description" :content="withCompany(t.metaDescription)" />

        <meta name="robots" content="index, follow" />
    </Head>

    <div
        class="relative min-h-screen overflow-hidden bg-slate-50/70 text-slate-900 dark:bg-slate-950 dark:text-white"
    >
        <!-- =====================================================
             Background Blobs
        ====================================================== -->

        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 overflow-hidden"
        >
            <div
                class="absolute -left-32 top-24 h-80 w-80 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-900/20"
            />

            <div
                class="absolute -right-32 top-[32rem] h-96 w-96 rounded-full bg-slate-300/30 blur-3xl dark:bg-slate-800/30"
            />

            <div
                class="absolute left-1/3 top-[55rem] h-72 w-72 rounded-full bg-blue-100/30 blur-3xl dark:bg-blue-950/20"
            />
        </div>

        <!-- =====================================================
             Skeleton Loading
        ====================================================== -->

        <template v-if="isPageLoading">
            <main class="relative">
                <div
                    class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8 lg:pb-28 lg:pt-14"
                >
                    <div class="mb-6">
                        <div
                            class="h-5 w-48 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                        />
                    </div>

                    <div class="mx-auto mb-14 max-w-3xl text-center">
                        <div
                            class="mx-auto mb-5 h-4 w-28 animate-pulse rounded-full bg-slate-200 dark:bg-slate-800"
                        />

                        <div
                            class="mx-auto h-10 w-72 animate-pulse rounded-xl bg-slate-200 dark:bg-slate-800 sm:h-12 sm:w-96"
                        />

                        <div
                            class="mx-auto mt-5 h-5 w-full max-w-2xl animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                        />

                        <div
                            class="mx-auto mt-3 h-5 w-3/4 max-w-xl animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                        />
                    </div>

                    <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
                        <div class="space-y-5">
                            <div
                                class="h-36 animate-pulse rounded-3xl bg-white shadow-sm dark:bg-slate-900"
                            />

                            <div
                                class="h-36 animate-pulse rounded-3xl bg-white shadow-sm dark:bg-slate-900"
                            />

                            <div
                                class="h-36 animate-pulse rounded-3xl bg-white shadow-sm dark:bg-slate-900"
                            />

                            <div
                                class="h-36 animate-pulse rounded-3xl bg-slate-900 shadow-sm dark:bg-slate-800"
                            />
                        </div>

                        <div
                            class="rounded-3xl bg-white p-6 shadow-sm dark:bg-slate-900 sm:p-8"
                        >
                            <div
                                class="h-7 w-48 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                            />

                            <div
                                class="mt-3 h-4 w-72 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="mt-8 grid gap-5 sm:grid-cols-2">
                                <div
                                    class="h-12 animate-pulse rounded-xl bg-slate-100 dark:bg-slate-800"
                                />

                                <div
                                    class="h-12 animate-pulse rounded-xl bg-slate-100 dark:bg-slate-800"
                                />

                                <div
                                    class="h-12 animate-pulse rounded-xl bg-slate-100 dark:bg-slate-800"
                                />

                                <div
                                    class="h-12 animate-pulse rounded-xl bg-slate-100 dark:bg-slate-800"
                                />
                            </div>

                            <div
                                class="mt-5 h-12 animate-pulse rounded-xl bg-slate-100 dark:bg-slate-800"
                            />

                            <div
                                class="mt-5 h-40 animate-pulse rounded-xl bg-slate-100 dark:bg-slate-800"
                            />

                            <div
                                class="mt-6 h-12 animate-pulse rounded-xl bg-slate-200 dark:bg-slate-800"
                            />
                        </div>
                    </div>
                </div>
            </main>
        </template>

        <!-- =====================================================
             Actual Page
        ====================================================== -->

        <template v-else>
            <main class="relative">
                <div
                    class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8 lg:pb-28 lg:pt-14"
                >
                    <!-- =================================================
                         Breadcrumb
                    ================================================== -->

                    <div data-reveal class="reveal mb-6" style="--d: 0ms">
                        <nav
                            :aria-label="t.breadcrumbAria"
                            class="flex flex-wrap items-center gap-2 text-sm"
                        >
                            <Link
                                href="/"
                                class="inline-flex items-center gap-1.5 rounded-md font-medium text-slate-500 transition hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:text-slate-400 dark:hover:text-blue-400"
                            >
                                <Home
                                    class="size-4 shrink-0"
                                    aria-hidden="true"
                                />

                                <span>{{ t.home }}</span>
                            </Link>

                            <ChevronRight
                                class="size-4 shrink-0 text-slate-400"
                                aria-hidden="true"
                            />

                            <span
                                aria-current="page"
                                class="inline-flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200"
                            >
                                <MessageSquare
                                    class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                                    aria-hidden="true"
                                />

                                <span>{{ t.contact }}</span>
                            </span>
                        </nav>
                    </div>

                    <!-- =================================================
                         Hero
                    ================================================== -->

                    <section
                        class="mx-auto mb-14 max-w-3xl text-center"
                        data-reveal
                        style="--d: 80ms"
                    >
                        <div
                            class="mx-auto mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200/70 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                        >
                            <MessageSquare class="h-4 w-4" aria-hidden="true" />

                            {{ t.contactUs }}
                        </div>

                        <h1
                            class="text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl lg:text-5xl"
                        >
                            {{ t.heroTitle }}
                        </h1>

                        <p
                            class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-400 sm:text-lg"
                        >
                            {{ t.heroDescription }}
                        </p>
                    </section>

                    <!-- =================================================
                         Main Grid
                    ================================================== -->

                    <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
                        <!-- =================================================
                             Contact Information
                        ================================================== -->

                        <section
                            class="space-y-5"
                            aria-labelledby="contact-information-title"
                            data-reveal
                            style="--d: 120ms"
                        >
                            <div class="mb-6">
                                <p
                                    class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-600 dark:text-blue-400"
                                >
                                    {{ t.contactInformation }}
                                </p>

                                <h2
                                    id="contact-information-title"
                                    class="mt-2 text-2xl font-bold tracking-tight text-slate-950 dark:text-white"
                                >
                                    {{ t.readyToHelp }}
                                </h2>

                                <p
                                    class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400"
                                >
                                    {{ withCompany(t.contactIntro) }}
                                </p>
                            </div>

                            <!-- Location -->

                            <div
                                v-if="props.kontak.alamat"
                                class="group rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                            >
                                <div class="flex gap-4">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        <MapPin
                                            class="h-5 w-5"
                                            aria-hidden="true"
                                        />
                                    </div>

                                    <div class="min-w-0">
                                        <h3
                                            class="font-semibold text-slate-950 dark:text-white"
                                        >
                                            {{ t.location }}
                                        </h3>

                                        <p
                                            class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                                        >
                                            {{ props.kontak.alamat }}
                                        </p>

                                        <a
                                            v-if="hasCoordinates"
                                            :href="googleMapsHref"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="mt-4 inline-flex items-center gap-1.5 rounded-md text-sm font-semibold text-blue-600 transition hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            <Navigation
                                                class="size-4"
                                                aria-hidden="true"
                                            />

                                            <span>
                                                {{ t.openGoogleMaps }}
                                            </span>

                                            <ExternalLink
                                                class="size-3.5"
                                                aria-hidden="true"
                                            />
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->

                            <div
                                v-if="props.kontak.email"
                                class="group rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                            >
                                <div class="flex gap-4">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        <Mail
                                            class="h-5 w-5"
                                            aria-hidden="true"
                                        />
                                    </div>

                                    <div class="min-w-0">
                                        <h3
                                            class="font-semibold text-slate-950 dark:text-white"
                                        >
                                            {{ t.email }}
                                        </h3>

                                        <a
                                            :href="emailHref"
                                            class="mt-2 block break-all rounded-md text-sm text-slate-600 transition hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:text-slate-400 dark:hover:text-blue-400"
                                        >
                                            {{ props.kontak.email }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Investor Email -->

                            <div
                                v-if="props.kontak.email_investor"
                                class="group rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                            >
                                <div class="flex gap-4">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        <Building2
                                            class="h-5 w-5"
                                            aria-hidden="true"
                                        />
                                    </div>

                                    <div class="min-w-0">
                                        <h3
                                            class="font-semibold text-slate-950 dark:text-white"
                                        >
                                            {{ t.investorEmail }}
                                        </h3>

                                        <a
                                            :href="investorEmailHref"
                                            class="mt-2 block break-all rounded-md text-sm text-slate-600 transition hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:text-slate-400 dark:hover:text-blue-400"
                                        >
                                            {{ props.kontak.email_investor }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone / WhatsApp -->

                            <div
                                v-if="
                                    props.kontak.telepon ||
                                    props.kontak.whatsapp
                                "
                                class="group rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
                            >
                                <div class="flex gap-4">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        <Phone
                                            class="h-5 w-5"
                                            aria-hidden="true"
                                        />
                                    </div>

                                    <div class="min-w-0">
                                        <h3
                                            class="font-semibold text-slate-950 dark:text-white"
                                        >
                                            {{ t.phoneWhatsapp }}
                                        </h3>

                                        <div class="mt-2 space-y-2">
                                            <a
                                                v-if="props.kontak.telepon"
                                                :href="phoneHref"
                                                class="block rounded-md text-sm text-slate-600 transition hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:text-slate-400 dark:hover:text-blue-400"
                                            >
                                                {{ props.kontak.telepon }}
                                            </a>

                                            <a
                                                v-if="props.kontak.whatsapp"
                                                :href="whatsappHref"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 rounded-md text-sm font-medium text-slate-600 transition hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:text-slate-400 dark:hover:text-blue-400"
                                            >
                                                <Smartphone
                                                    class="size-3.5"
                                                    aria-hidden="true"
                                                />

                                                {{ t.whatsapp }}

                                                <ExternalLink
                                                    class="size-3"
                                                    aria-hidden="true"
                                                />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Operating Hours -->

                            <div
                                v-if="props.kontak.jam_operasional"
                                class="rounded-3xl border border-slate-200/80 bg-slate-900 p-6 text-white shadow-sm dark:border-slate-800 dark:bg-slate-800"
                            >
                                <div class="flex gap-4">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10"
                                    >
                                        <Clock3
                                            class="h-5 w-5"
                                            aria-hidden="true"
                                        />
                                    </div>

                                    <div>
                                        <h3 class="font-semibold">
                                            {{ t.operatingHours }}
                                        </h3>

                                        <p
                                            class="mt-2 text-sm leading-6 text-slate-300"
                                        >
                                            {{ props.kontak.jam_operasional }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Leaflet Map -->

                            <div
                                v-if="hasCoordinates"
                                class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                            >
                                <div
                                    class="flex items-center justify-between gap-4 border-b border-slate-200/80 px-5 py-4 dark:border-slate-800"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                        >
                                            <MapPin
                                                class="size-4"
                                                aria-hidden="true"
                                            />
                                        </div>

                                        <div>
                                            <h3
                                                class="text-sm font-semibold text-slate-950 dark:text-white"
                                            >
                                                Peta {{ t.location }}
                                            </h3>

                                            <p
                                                class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{ t.location }} kawasan KITB
                                            </p>
                                        </div>
                                    </div>

                                    <span
                                        class="hidden text-xs text-slate-400 sm:block"
                                    >
                                        OpenStreetMap
                                    </span>
                                </div>

                                <div
                                    ref="mapContainer"
                                    class="h-72 w-full sm:h-80"
                                    role="img"
                                    :aria-label="t.mapAria"
                                />
                            </div>
                        </section>

                        <!-- =================================================
                             Form
                        ================================================== -->

                        <section
                            data-reveal
                            style="--d: 180ms"
                            aria-labelledby="contact-form-title"
                        >
                            <div
                                class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8"
                            >
                                <div class="mb-8">
                                    <div
                                        class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                                    >
                                        <Send
                                            class="h-5 w-5"
                                            aria-hidden="true"
                                        />
                                    </div>

                                    <h2
                                        id="contact-form-title"
                                        class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white"
                                    >
                                        {{ t.sendMessage }}
                                    </h2>

                                    <p
                                        class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                                    >
                                        {{ t.formIntro }}
                                    </p>
                                </div>

                                <!-- General Error -->

                                <div
                                    v-if="hasErrors"
                                    role="alert"
                                    aria-live="assertive"
                                    class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300"
                                >
                                    <span
                                        class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-600 dark:bg-red-900/40 dark:text-red-300"
                                        aria-hidden="true"
                                    >
                                        !
                                    </span>

                                    <div>
                                        <p class="font-semibold">
                                            {{ t.formErrorTitle }}
                                        </p>

                                        <p class="mt-1 leading-5">
                                            {{ t.formErrorDescription }}
                                        </p>
                                    </div>
                                </div>

                                <form
                                    ref="formRef"
                                    @submit.prevent="submit"
                                    novalidate
                                >
                                    <!-- Basic Fields -->

                                    <div class="grid gap-5 sm:grid-cols-2">
                                        <!-- Name -->

                                        <div>
                                            <label
                                                for="nama"
                                                class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{ t.fullName }}

                                                <span
                                                    class="text-red-500"
                                                    aria-hidden="true"
                                                >
                                                    *
                                                </span>
                                            </label>

                                            <input
                                                id="nama"
                                                ref="firstInputRef"
                                                v-model="form.nama"
                                                type="text"
                                                name="nama"
                                                autocomplete="name"
                                                required
                                                maxlength="100"
                                                :placeholder="
                                                    t.fullNamePlaceholder
                                                "
                                                :aria-invalid="
                                                    !!form.errors.nama
                                                "
                                                :aria-describedby="
                                                    form.errors.nama
                                                        ? 'nama-error'
                                                        : undefined
                                                "
                                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                                :class="{
                                                    'border-red-400 focus:border-red-500 focus:ring-red-500/10':
                                                        form.errors.nama,
                                                }"
                                            />

                                            <p
                                                v-if="form.errors.nama"
                                                id="nama-error"
                                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                                            >
                                                {{ form.errors.nama }}
                                            </p>
                                        </div>

                                        <!-- Email -->

                                        <div>
                                            <label
                                                for="email"
                                                class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                Email

                                                <span
                                                    class="text-red-500"
                                                    aria-hidden="true"
                                                >
                                                    *
                                                </span>
                                            </label>

                                            <input
                                                id="email"
                                                v-model="form.email"
                                                type="email"
                                                name="email"
                                                autocomplete="email"
                                                required
                                                maxlength="150"
                                                :placeholder="
                                                    t.emailPlaceholder
                                                "
                                                :aria-invalid="
                                                    !!form.errors.email
                                                "
                                                :aria-describedby="
                                                    form.errors.email
                                                        ? 'email-error'
                                                        : undefined
                                                "
                                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                                :class="{
                                                    'border-red-400 focus:border-red-500 focus:ring-red-500/10':
                                                        form.errors.email,
                                                }"
                                            />

                                            <p
                                                v-if="form.errors.email"
                                                id="email-error"
                                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                                            >
                                                {{ form.errors.email }}
                                            </p>
                                        </div>

                                        <!-- Phone -->

                                        <div>
                                            <label
                                                for="telepon"
                                                class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{ t.phone }}
                                            </label>

                                            <input
                                                id="telepon"
                                                v-model="form.telepon"
                                                type="tel"
                                                name="telepon"
                                                autocomplete="tel"
                                                maxlength="30"
                                                placeholder="+62 812..."
                                                :aria-invalid="
                                                    !!form.errors.telepon
                                                "
                                                :aria-describedby="
                                                    form.errors.telepon
                                                        ? 'telepon-error'
                                                        : undefined
                                                "
                                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                                :class="{
                                                    'border-red-400 focus:border-red-500 focus:ring-red-500/10':
                                                        form.errors.telepon,
                                                }"
                                            />

                                            <p
                                                v-if="form.errors.telepon"
                                                id="telepon-error"
                                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                                            >
                                                {{ form.errors.telepon }}
                                            </p>
                                        </div>

                                        <!-- Company -->

                                        <div>
                                            <label
                                                for="perusahaan"
                                                class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{ t.company }}
                                            </label>

                                            <input
                                                id="perusahaan"
                                                v-model="form.perusahaan"
                                                type="text"
                                                name="perusahaan"
                                                autocomplete="organization"
                                                maxlength="150"
                                                :placeholder="
                                                    t.companyPlaceholder
                                                "
                                                :aria-invalid="
                                                    !!form.errors.perusahaan
                                                "
                                                :aria-describedby="
                                                    form.errors.perusahaan
                                                        ? 'perusahaan-error'
                                                        : undefined
                                                "
                                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                                :class="{
                                                    'border-red-400 focus:border-red-500 focus:ring-red-500/10':
                                                        form.errors.perusahaan,
                                                }"
                                            />

                                            <p
                                                v-if="form.errors.perusahaan"
                                                id="perusahaan-error"
                                                class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                                            >
                                                {{ form.errors.perusahaan }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Subject -->

                                    <div class="mt-5">
                                        <label
                                            for="subjek"
                                            class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ t.subject }}

                                            <span
                                                class="text-red-500"
                                                aria-hidden="true"
                                            >
                                                *
                                            </span>
                                        </label>

                                        <input
                                            id="subjek"
                                            v-model="form.subjek"
                                            type="text"
                                            name="subjek"
                                            required
                                            maxlength="150"
                                            :placeholder="t.subjectPlaceholder"
                                            :aria-invalid="!!form.errors.subjek"
                                            :aria-describedby="
                                                form.errors.subjek
                                                    ? 'subjek-error'
                                                    : undefined
                                            "
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                            :class="{
                                                'border-red-400 focus:border-red-500 focus:ring-red-500/10':
                                                    form.errors.subjek,
                                            }"
                                        />

                                        <p
                                            v-if="form.errors.subjek"
                                            id="subjek-error"
                                            class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                                        >
                                            {{ form.errors.subjek }}
                                        </p>
                                    </div>

                                    <!-- Message -->

                                    <div class="mt-5">
                                        <div
                                            class="mb-2 flex items-center justify-between gap-4"
                                        >
                                            <label
                                                for="pesan"
                                                class="block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{ t.message }}

                                                <span
                                                    class="text-red-500"
                                                    aria-hidden="true"
                                                >
                                                    *
                                                </span>
                                            </label>

                                            <span
                                                class="text-xs tabular-nums text-slate-400 dark:text-slate-500"
                                                :class="{
                                                    'font-semibold text-red-500':
                                                        messageLength >= 3000,
                                                }"
                                                aria-live="polite"
                                            >
                                                {{ messageLength }}/3000
                                            </span>
                                        </div>

                                        <textarea
                                            id="pesan"
                                            v-model="form.pesan"
                                            name="pesan"
                                            rows="7"
                                            required
                                            minlength="10"
                                            maxlength="3000"
                                            :placeholder="t.messagePlaceholder"
                                            :aria-invalid="!!form.errors.pesan"
                                            :aria-describedby="
                                                form.errors.pesan
                                                    ? 'pesan-error'
                                                    : undefined
                                            "
                                            class="min-h-40 w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500"
                                            :class="{
                                                'border-red-400 focus:border-red-500 focus:ring-red-500/10':
                                                    form.errors.pesan,
                                            }"
                                        />

                                        <div
                                            class="mt-2 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                            aria-hidden="true"
                                        >
                                            <div
                                                class="h-full rounded-full bg-blue-500 transition-all duration-300"
                                                :style="{
                                                    width: `${messageProgress}%`,
                                                }"
                                            />
                                        </div>

                                        <p
                                            v-if="form.errors.pesan"
                                            id="pesan-error"
                                            class="mt-2 text-xs font-medium text-red-600 dark:text-red-400"
                                        >
                                            {{ form.errors.pesan }}
                                        </p>
                                    </div>

                                    <!-- Honeypot -->

                                    <div
                                        class="absolute -left-[9999px] h-0 w-0 overflow-hidden"
                                        aria-hidden="true"
                                    >
                                        <label for="website"> Website </label>

                                        <input
                                            id="website"
                                            v-model="form.website"
                                            type="text"
                                            name="website"
                                            tabindex="-1"
                                            autocomplete="off"
                                        />
                                    </div>

                                    <!-- Submit -->

                                    <div
                                        class="mt-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <p
                                            class="max-w-md text-xs leading-5 text-slate-500 dark:text-slate-500"
                                        >
                                            {{ t.consent }}
                                        </p>

                                        <button
                                            type="submit"
                                            :disabled="form.processing"
                                            :aria-busy="form.processing"
                                            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-slate-950 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 disabled:hover:shadow-sm dark:bg-white dark:text-slate-950 dark:hover:bg-blue-400"
                                        >
                                            <svg
                                                v-if="form.processing"
                                                class="h-4 w-4 animate-spin"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                aria-hidden="true"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="9"
                                                    stroke="currentColor"
                                                    stroke-width="3"
                                                    class="opacity-25"
                                                />

                                                <path
                                                    d="M21 12a9 9 0 0 0-9-9"
                                                    stroke="currentColor"
                                                    stroke-width="3"
                                                    stroke-linecap="round"
                                                />
                                            </svg>

                                            <Send
                                                v-else
                                                class="h-4 w-4"
                                                aria-hidden="true"
                                            />

                                            {{
                                                form.processing
                                                    ? t.sending
                                                    : t.sendMessage
                                            }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </section>
                    </div>

                    <!-- =================================================
                         Bottom CTA
                    ================================================== -->

                    <section class="mt-10" data-reveal style="--d: 240ms">
                        <div
                            class="flex flex-col gap-4 rounded-3xl border border-blue-100 bg-blue-50/70 p-6 dark:border-blue-900/40 dark:bg-blue-950/20 sm:flex-row sm:items-center"
                        >
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400"
                            >
                                <Building2 class="h-5 w-5" aria-hidden="true" />
                            </div>

                            <div>
                                <h2
                                    class="font-semibold text-slate-950 dark:text-white"
                                >
                                    {{ withCompany(t.bottomTitle) }}
                                </h2>

                                <p
                                    class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400"
                                >
                                    {{ t.bottomDescription }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </template>

        <!-- =====================================================
             Success Modal
        ====================================================== -->

        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showSuccessModal"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                role="presentation"
            >
                <!-- Backdrop -->

                <button
                    type="button"
                    tabindex="-1"
                    aria-label="{{ t.close }} dialog"
                    class="absolute inset-0 cursor-default bg-slate-950/60 backdrop-blur-sm"
                    @click="closeSuccessModal"
                />

                <!-- Dialog -->

                <Transition
                    appear
                    enter-active-class="duration-300 ease-out"
                    enter-from-class="scale-95 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="duration-200 ease-in"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div
                        v-if="showSuccessModal"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="success-modal-title"
                        aria-describedby="success-modal-description"
                        class="relative w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <!-- Accent -->

                        <div
                            class="h-1.5 w-full bg-gradient-to-r from-emerald-400 via-emerald-500 to-blue-500"
                            aria-hidden="true"
                        />

                        <div class="p-6 sm:p-8">
                            <!-- Close -->

                            <button
                                type="button"
                                aria-label="{{ t.close }} dialog"
                                class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                @click="closeSuccessModal"
                            >
                                <X class="h-5 w-5" aria-hidden="true" />
                            </button>

                            <!-- Icon -->

                            <div
                                class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/60 dark:bg-emerald-950/40 dark:text-emerald-400 dark:ring-emerald-950/20"
                                aria-hidden="true"
                            >
                                <CheckCircle2 class="h-8 w-8" />
                            </div>

                            <!-- Content -->

                            <div class="mt-6 text-center">
                                <h2
                                    id="success-modal-title"
                                    class="text-xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-2xl"
                                >
                                    {{ t.successTitle }}
                                </h2>

                                <p
                                    id="success-modal-description"
                                    class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400"
                                >
                                    {{ t.successDescription }}
                                </p>
                            </div>

                            <!-- Email Information -->

                            <div
                                class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20"
                            >
                                <div class="flex items-start gap-3">
                                    <Mail
                                        class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                                        aria-hidden="true"
                                    />

                                    <p
                                        class="text-sm leading-6 text-emerald-800 dark:text-emerald-300"
                                    >
                                        {{ t.successEmailNote }}
                                    </p>
                                </div>
                            </div>

                            <!-- Close Button -->

                            <button
                                ref="successCloseButtonRef"
                                type="button"
                                class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 dark:bg-white dark:text-slate-950 dark:hover:bg-blue-400"
                                @click="closeSuccessModal"
                            >
                                {{ t.close }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* =========================================================
   Reveal Animation
========================================================= */

[data-reveal] {
    opacity: 0;
    transform: translateY(18px);
    transition:
        opacity 700ms ease,
        transform 700ms ease;
    transition-delay: var(--d, 0ms);
}

[data-reveal].is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* =========================================================
   Leaflet
========================================================= */

:deep(.leaflet-container) {
    z-index: 0;
    font-family: inherit;
}

:deep(.leaflet-control-zoom) {
    border: 0 !important;
    box-shadow: 0 8px 24px rgb(15 23 42 / 0.12) !important;
}

:deep(.leaflet-control-zoom a) {
    color: rgb(15 23 42) !important;
    background: white !important;
    border: 0 !important;
}

:deep(.leaflet-control-zoom a:hover) {
    background: rgb(248 250 252) !important;
}

:deep(.leaflet-popup-content-wrapper),
:deep(.leaflet-popup-tip) {
    background: white;
}

:deep(.leaflet-popup-content) {
    color: rgb(15 23 42);
    font-size: 13px;
    line-height: 1.6;
}

:deep(.leaflet-control-attribution) {
    font-size: 10px;
}

/* =========================================================
   Reduced Motion
========================================================= */

@media (prefers-reduced-motion: reduce) {
    [data-reveal] {
        opacity: 1;
        transform: none;
        transition: none;
    }
}

/* =========================================================
   Focus
========================================================= */

:focus-visible {
    outline: 2px solid rgb(59 130 246 / 0.7);
    outline-offset: 3px;
}

/* =========================================================
   Selection
========================================================= */

::selection {
    background: rgb(59 130 246 / 0.18);
}
</style>
