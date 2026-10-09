<script setup lang="ts">
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import {
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    Check,
    CheckCircle2,
    Clock3,
    Copy,
    FileText,
    LoaderCircle,
    Mail,
    MapPin,
    Phone,
    Send,
    ShieldCheck,
    User,
    Users,
} from "lucide-vue-next";

import PublicLayout from "@/layouts/PublicLayout.vue";
import { currentLanguage } from "@/composables/useLocale";

defineOptions({
    layout: PublicLayout,
});

type LanguageCode = "id" | "en" | "zh";

const translations = {
    id: {
        seoTitle: "Ajukan Kunjungan Lahan | KITB",
        seoDescription:
            "Ajukan kunjungan ke lahan kawasan industri PT Kawasan Industri Tanjung Buton (KITB).",
        pageBadge: "Kunjungan Lahan",
        pageTitle: "Ajukan kunjungan ke kawasan KITB",
        pageDescription:
            "Isi jadwal dan data rombongan Anda. Tim kami akan meninjau pengajuan dan menghubungi Anda melalui email atau telepon.",
        formBadge: "Form Pengajuan",
        formTitle: "Formulir pengajuan",
        requiredPrefix: "Kolom bertanda",
        requiredNote: "wajib diisi.",
        applicantSection: "Data pemohon",
        applicantDescription: "Kontak yang akan kami hubungi untuk konfirmasi.",
        fullName: "Nama lengkap",
        fullNamePlaceholder: "Nama lengkap pemohon",
        organization: "Instansi / perusahaan",
        organizationPlaceholder: "Nama instansi",
        position: "Jabatan",
        positionPlaceholder: "Jabatan di instansi",
        optional: "opsional",
        email: "Email",
        emailPlaceholder: "nama@email.com",
        phone: "Nomor telepon",
        phonePlaceholder: "08xxxxxxxxxx",
        scheduleSection: "Jadwal kunjungan",
        scheduleDescription:
            "Pilih tanggal terlebih dahulu untuk melihat jadwal yang sudah terisi.",
        date: "Tanggal",
        startTime: "Waktu mulai",
        endTime: "Waktu selesai",
        timeOrderError: "Waktu selesai harus setelah waktu mulai.",
        scheduleOverlap:
            "Waktu yang Anda pilih bentrok dengan jadwal yang sudah terisi pada :date. Pilih jam lain.",
        visitDetails: "Detail kunjungan",
        visitDetailsDescription:
            "Informasi rombongan dan area yang ingin dilihat.",
        participants: "Jumlah peserta",
        landArea: "Area lahan yang dikunjungi",
        landAreaPlaceholder: "Contoh: Blok A, kawasan pelabuhan",
        needGuide: "Perlu pendamping dari KITB?",
        yesGuide: "Ya, saya butuh pendamping",
        yesGuideDescription: "Petugas KITB menemani selama kunjungan.",
        noGuide: "Tidak perlu",
        noGuideDescription: "Kami datang dengan pendamping sendiri.",
        purpose: "Keperluan kunjungan",
        purposePlaceholder:
            "Jelaskan tujuan kunjungan, misalnya survei lokasi investasi atau studi lapangan.",
        reviewData: "Periksa kembali data Anda",
        reviewDescription:
            "Pastikan email dan telepon aktif agar kami bisa mengonfirmasi jadwal.",
        sending: "Mengirim...",
        submit: "Kirim pengajuan",
        filledSchedule: "Jadwal yang sudah terisi",
        filledScheduleHint:
            "Pilih tanggal kunjungan untuk melihat jam yang tidak tersedia.",
        allTimesAvailable: "Belum ada jadwal. Semua jam tersedia.",
        booked: "Terisi",
        afterSubmit: "Setelah pengajuan dikirim",
        step1: "Anda menerima nomor registrasi untuk disimpan.",
        step2: "Tim KITB meninjau pengajuan dan mengonfirmasi jadwal.",
        step3: "Anda dihubungi melalui email atau telepon yang diisi.",
        successTitle: "Pengajuan terkirim",
        successDescription:
            "Simpan nomor registrasi berikut. Tim KITB akan menghubungi Anda untuk konfirmasi jadwal.",
        copied: "Tersalin",
        copy: "Salin",
        close: "Tutup",
    },
    en: {
        seoTitle: "Request a Land Visit | KITB",
        seoDescription:
            "Request a visit to the industrial land area of PT Kawasan Industri Tanjung Buton (KITB).",
        pageBadge: "Land Visit",
        pageTitle: "Request a visit to the KITB area",
        pageDescription:
            "Enter your preferred schedule and group details. Our team will review your request and contact you by email or phone.",
        formBadge: "Visit Request",
        formTitle: "Request form",
        requiredPrefix: "Fields marked",
        requiredNote: "are required.",
        applicantSection: "Applicant information",
        applicantDescription: "Contact details we will use for confirmation.",
        fullName: "Full name",
        fullNamePlaceholder: "Applicant's full name",
        organization: "Organization / company",
        organizationPlaceholder: "Organization name",
        position: "Position",
        positionPlaceholder: "Your position in the organization",
        optional: "optional",
        email: "Email",
        emailPlaceholder: "name@email.com",
        phone: "Phone number",
        phonePlaceholder: "Enter your phone number",
        scheduleSection: "Visit schedule",
        scheduleDescription:
            "Choose a date first to see already-booked time slots.",
        date: "Date",
        startTime: "Start time",
        endTime: "End time",
        timeOrderError: "The end time must be later than the start time.",
        scheduleOverlap:
            "Your selected time overlaps with an existing booking on :date. Please choose another time.",
        visitDetails: "Visit details",
        visitDetailsDescription:
            "Information about your group and the area you would like to visit.",
        participants: "Number of participants",
        landArea: "Land area to visit",
        landAreaPlaceholder: "Example: Block A, port area",
        needGuide: "Do you need a KITB guide?",
        yesGuide: "Yes, I need a guide",
        yesGuideDescription: "A KITB staff member will accompany your visit.",
        noGuide: "No, thank you",
        noGuideDescription: "We will bring our own guide.",
        purpose: "Purpose of visit",
        purposePlaceholder:
            "Explain the purpose of your visit, such as an investment site survey or field study.",
        reviewData: "Review your information",
        reviewDescription:
            "Make sure your email and phone number are active so we can confirm the schedule.",
        sending: "Sending...",
        submit: "Submit request",
        filledSchedule: "Booked time slots",
        filledScheduleHint: "Choose a visit date to see unavailable times.",
        allTimesAvailable: "No bookings yet. All times are available.",
        booked: "Booked",
        afterSubmit: "After you submit",
        step1: "You will receive a registration number. Keep it for your records.",
        step2: "The KITB team will review your request and confirm the schedule.",
        step3: "We will contact you using the email address or phone number provided.",
        successTitle: "Request submitted",
        successDescription:
            "Save the registration number below. The KITB team will contact you to confirm the schedule.",
        copied: "Copied",
        copy: "Copy",
        close: "Close",
    },
    zh: {
        seoTitle: "申请参观土地园区 | KITB",
        seoDescription:
            "申请参观 PT Kawasan Industri Tanjung Buton（KITB）工业园区土地。",
        pageBadge: "园区参观",
        pageTitle: "申请参观 KITB 园区",
        pageDescription:
            "请填写参观时间和团队信息。我们的团队将审核申请，并通过电子邮件或电话与您联系。",
        formBadge: "参观申请",
        formTitle: "申请表",
        requiredPrefix: "标有",
        requiredNote: "的字段为必填项。",
        applicantSection: "申请人信息",
        applicantDescription: "我们将使用以下联系方式与您确认。",
        fullName: "姓名",
        fullNamePlaceholder: "申请人姓名",
        organization: "机构 / 公司",
        organizationPlaceholder: "机构名称",
        position: "职务",
        positionPlaceholder: "您在机构中的职务",
        optional: "选填",
        email: "电子邮箱",
        emailPlaceholder: "name@email.com",
        phone: "电话号码",
        phonePlaceholder: "请输入电话号码",
        scheduleSection: "参观时间",
        scheduleDescription: "请先选择日期，以查看已经预约的时间段。",
        date: "日期",
        startTime: "开始时间",
        endTime: "结束时间",
        timeOrderError: "结束时间必须晚于开始时间。",
        scheduleOverlap:
            "您选择的时间与 :date 已有的预约时间冲突。请选择其他时间。",
        visitDetails: "参观详情",
        visitDetailsDescription: "请填写团队信息以及希望参观的区域。",
        participants: "参观人数",
        landArea: "参观土地区域",
        landAreaPlaceholder: "例如：A 区、港口区域",
        needGuide: "是否需要 KITB 工作人员陪同？",
        yesGuide: "是，需要陪同",
        yesGuideDescription: "KITB 工作人员将在参观期间陪同。",
        noGuide: "不需要",
        noGuideDescription: "我们将自行安排陪同人员。",
        purpose: "参观目的",
        purposePlaceholder: "请说明参观目的，例如投资选址调查或实地考察。",
        reviewData: "请检查您填写的信息",
        reviewDescription:
            "请确保电子邮箱和电话号码有效，以便我们确认参观时间。",
        sending: "正在提交...",
        submit: "提交申请",
        filledSchedule: "已预约的时间段",
        filledScheduleHint: "选择参观日期以查看不可用的时间段。",
        allTimesAvailable: "目前暂无预约，所有时间段均可用。",
        booked: "已预约",
        afterSubmit: "提交申请后",
        step1: "您将收到一个登记编号，请妥善保存。",
        step2: "KITB 团队将审核申请并确认参观时间。",
        step3: "我们将通过您填写的电子邮箱或电话号码与您联系。",
        successTitle: "申请已提交",
        successDescription:
            "请保存以下登记编号。KITB 团队将与您联系以确认参观时间。",
        copied: "已复制",
        copy: "复制",
        close: "关闭",
    },
} as const;

const language = computed<LanguageCode>(() => {
    const value = String(currentLanguage.value);

    if (value === "en" || value === "zh") {
        return value;
    }

    // Mendukung kode bahasa zh_CN jika digunakan di bagian lain proyek.
    if (value === "zh_CN") {
        return "zh";
    }

    return "id";
});

const t = computed(() => translations[language.value]);

const dateLocale = computed(() => {
    if (language.value === "en") return "en-US";
    if (language.value === "zh") return "zh-CN";
    return "id-ID";
});

interface JadwalTerisi {
    mulai: string;
    selesai: string;
}

const props = defineProps<{
    jadwal_terisi: JadwalTerisi[];
    nomor_registrasi: string | null;
}>();

const form = useForm({
    nama: "",
    instansi: "",
    jabatan: "",
    email: "",
    telepon: "",
    tanggal_kunjungan: "",
    waktu_mulai: "",
    waktu_selesai: "",
    jumlah_peserta: 1 as number | string,
    area_lahan: "",
    keperluan: "",
    memerlukan_pendamping: true,
});

const inputClass =
    "h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950/50 dark:text-white";

const plainInputClass =
    "h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950/50 dark:text-white";

const todayKey = new Intl.DateTimeFormat("en-CA", {
    timeZone: "Asia/Jakarta",
}).format(new Date());

watch(
    () => form.tanggal_kunjungan,
    (tanggal) => {
        if (!tanggal || tanggal < todayKey) {
            return;
        }

        router.get(
            "/ajukan-kunjungan",
            { tanggal },
            {
                only: ["jadwal_terisi"],
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    },
);

const hasOverlap = computed(() => {
    if (!form.waktu_mulai || !form.waktu_selesai) {
        return false;
    }

    return props.jadwal_terisi.some(
        (slot) =>
            slot.mulai < form.waktu_selesai && slot.selesai > form.waktu_mulai,
    );
});

const timeOrderError = computed(() => {
    if (
        form.waktu_mulai &&
        form.waktu_selesai &&
        form.waktu_selesai <= form.waktu_mulai
    ) {
        return t.value.timeOrderError;
    }

    return "";
});

const formatDateLong = (value: string): string => {
    if (!value) return "";

    const date = new Date(`${value}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat(dateLocale.value, {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(date);
};

const scheduleOverlapMessage = computed(() =>
    t.value.scheduleOverlap.replace(
        ":date",
        formatDateLong(form.tanggal_kunjungan),
    ),
);

const showSuccessModal = ref(false);
const submittedNumber = ref("");
const successCloseButton = ref<HTMLButtonElement | null>(null);
const copied = ref(false);

const canSubmit = computed(
    () => !form.processing && !hasOverlap.value && !timeOrderError.value,
);

const submit = () => {
    if (!canSubmit.value) return;

    form.post("/ajukan-kunjungan", {
        preserveScroll: true,
        onSuccess: async () => {
            submittedNumber.value = props.nomor_registrasi ?? "";
            form.reset();
            form.clearErrors();
            showSuccessModal.value = true;

            await nextTick();
            successCloseButton.value?.focus();
        },
    });
};

const closeSuccessModal = () => {
    showSuccessModal.value = false;
    copied.value = false;
};

const copyNumber = async () => {
    if (!submittedNumber.value) return;

    try {
        await navigator.clipboard.writeText(submittedNumber.value);
        copied.value = true;

        window.setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        copied.value = false;
    }
};

const handleEscape = (event: KeyboardEvent) => {
    if (event.key === "Escape" && showSuccessModal.value) {
        closeSuccessModal();
    }
};

let revealObserver: IntersectionObserver | null = null;

const setupReveal = () => {
    if (typeof window === "undefined") return;

    const elements = Array.from(
        document.querySelectorAll<HTMLElement>(".reveal"),
    );

    if (!elements.length) return;

    const prefersReducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;

    if (prefersReducedMotion) {
        elements.forEach((element) => {
            element.classList.add("is-visible");
        });

        return;
    }

    revealObserver?.disconnect();

    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                const element = entry.target as HTMLElement;
                element.classList.add("is-visible");
                revealObserver?.unobserve(element);
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

onMounted(() => {
    window.addEventListener("keydown", handleEscape);

    requestAnimationFrame(() => {
        setupReveal();
    });
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleEscape);
    revealObserver?.disconnect();
    revealObserver = null;
});

const canonicalUrl = "https://tanjungbuton-industrial.co.id/ajukan-kunjungan";

const ogImage = "https://tanjungbuton-industrial.co.id/logoside.png";
</script>
<template>
    <Head>
        <title>{{ t.seoTitle }}</title>
        <meta name="description" :content="t.seoDescription" />
        <link rel="canonical" :href="canonicalUrl" />
        <meta property="og:title" :content="t.seoTitle" />
        <meta property="og:description" :content="t.seoDescription" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:image" :content="ogImage" />
    </Head>

    <div
        class="relative min-h-screen overflow-x-clip bg-slate-50/70 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
    >
        <!-- Background dekoratif -->
        <div
            class="pointer-events-none absolute inset-x-0 -top-28 h-[900px] overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-blue-100/70 via-blue-50/40 to-transparent dark:from-blue-950/30 dark:via-blue-950/10"
            />
            <div
                class="blob blob-a absolute left-[2%] top-0 size-[26rem] rounded-full bg-gradient-to-br from-blue-400/35 via-indigo-400/20 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/15"
            />
            <div
                class="blob blob-b absolute right-[2%] top-4 size-[22rem] rounded-full bg-gradient-to-tr from-sky-300/35 via-blue-400/20 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10"
            />
            <div
                class="blob blob-c absolute left-1/3 top-56 size-72 rounded-full bg-gradient-to-br from-indigo-300/20 via-blue-300/15 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/10"
            />
            <div
                class="absolute inset-0 opacity-[0.18] dark:opacity-[0.08]"
                style="
                    background-image:
                        linear-gradient(
                            rgba(100, 116, 139, 0.11) 1px,
                            transparent 1px
                        ),
                        linear-gradient(
                            90deg,
                            rgba(100, 116, 139, 0.11) 1px,
                            transparent 1px
                        );
                    background-size: 42px 42px;
                    mask-image: linear-gradient(
                        to bottom,
                        black 0%,
                        black 55%,
                        transparent 100%
                    );
                    -webkit-mask-image: linear-gradient(
                        to bottom,
                        black 0%,
                        black 55%,
                        transparent 100%
                    );
                "
            />
            <div
                class="absolute inset-x-0 bottom-0 h-64 bg-gradient-to-b from-transparent to-slate-50/95 dark:to-slate-950/95"
            />
        </div>

        <!-- Header -->
        <section
            class="relative z-10 border-b border-slate-200/70 bg-transparent dark:border-slate-800/70"
        >
            <div
                class="mx-auto max-w-6xl px-4 pb-10 pt-24 sm:px-6 sm:pb-12 sm:pt-28 lg:px-8 lg:pb-14 lg:pt-32"
            >
                <div class="reveal max-w-3xl" style="--d: 0">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-blue-200/80 bg-blue-50/80 px-3.5 py-1.5 text-xs font-bold text-blue-700 shadow-sm backdrop-blur-sm dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300"
                    >
                        <MapPin class="size-3.5" />
                        {{ t.pageBadge }}
                    </div>

                    <h1
                        class="mt-5 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl lg:text-[2.75rem] dark:text-white"
                    >
                        {{ t.pageTitle }}
                    </h1>

                    <p
                        class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8 dark:text-slate-400"
                    >
                        {{ t.pageDescription }}
                    </p>

                    <div
                        class="mt-7 flex items-center gap-3"
                        aria-hidden="true"
                    >
                        <div
                            class="h-px w-16 bg-gradient-to-r from-blue-500 to-indigo-500"
                        />
                        <div
                            class="h-px flex-1 bg-gradient-to-r from-slate-200/90 via-slate-200/60 to-transparent dark:from-slate-700/90 dark:via-slate-800/60"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Konten utama -->
        <section
            class="relative z-10 mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10"
        >
            <div
                class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:gap-8"
            >
                <!-- Form -->
                <div
                    class="reveal min-w-0 overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/90 shadow-xl shadow-slate-900/[0.04] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                    style="--d: 100"
                >
                    <div
                        class="border-b border-slate-100/90 px-5 py-6 sm:px-7 dark:border-slate-800"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400"
                                >
                                    {{ t.formBadge }}
                                </p>

                                <h2
                                    class="mt-1.5 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                                >
                                    {{ t.formTitle }}
                                </h2>

                                <p
                                    class="mt-1.5 text-sm leading-6 text-slate-500 dark:text-slate-400"
                                >
                                    {{ t.requiredPrefix }}
                                    <span class="text-red-500">*</span>
                                    {{ t.requiredNote }}
                                </p>
                            </div>

                            <div
                                class="hidden size-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 sm:flex dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <FileText class="size-5" />
                            </div>
                        </div>
                    </div>

                    <form
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                        @submit.prevent="submit"
                    >
                        <!-- Data pemohon -->
                        <div class="space-y-6 px-5 py-7 sm:px-7">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <User class="size-4" />
                                </div>

                                <div>
                                    <h3
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ t.applicantSection }}
                                    </h3>
                                    <p
                                        class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                    >
                                        {{ t.applicantDescription }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <!-- Nama -->
                                <div class="sm:col-span-2">
                                    <label
                                        for="nama"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.fullName }}
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <User
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="nama"
                                            v-model="form.nama"
                                            type="text"
                                            required
                                            maxlength="150"
                                            autocomplete="name"
                                            :placeholder="t.fullNamePlaceholder"
                                            :class="inputClass"
                                            :aria-invalid="!!form.errors.nama"
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.nama"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.nama }}
                                    </p>
                                </div>

                                <!-- Instansi -->
                                <div>
                                    <label
                                        for="instansi"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.organization }}
                                        <span
                                            class="font-normal text-slate-400"
                                        >
                                            ({{ t.optional }})
                                        </span>
                                    </label>

                                    <div class="relative">
                                        <Building2
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="instansi"
                                            v-model="form.instansi"
                                            type="text"
                                            maxlength="200"
                                            autocomplete="organization"
                                            :placeholder="
                                                t.organizationPlaceholder
                                            "
                                            :class="inputClass"
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.instansi"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.instansi }}
                                    </p>
                                </div>

                                <!-- Jabatan -->
                                <div>
                                    <label
                                        for="jabatan"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.position }}
                                        <span
                                            class="font-normal text-slate-400"
                                        >
                                            ({{ t.optional }})
                                        </span>
                                    </label>

                                    <div class="relative">
                                        <BriefcaseBusiness
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="jabatan"
                                            v-model="form.jabatan"
                                            type="text"
                                            maxlength="100"
                                            autocomplete="organization-title"
                                            :placeholder="t.positionPlaceholder"
                                            :class="inputClass"
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.jabatan"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.jabatan }}
                                    </p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label
                                        for="email"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.email }}
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <Mail
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            required
                                            maxlength="150"
                                            autocomplete="email"
                                            :placeholder="t.emailPlaceholder"
                                            :class="inputClass"
                                            :aria-invalid="!!form.errors.email"
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.email"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <!-- Telepon -->
                                <div>
                                    <label
                                        for="telepon"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.phone }}
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <Phone
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="telepon"
                                            v-model="form.telepon"
                                            type="tel"
                                            required
                                            maxlength="25"
                                            autocomplete="tel"
                                            :placeholder="t.phonePlaceholder"
                                            :class="inputClass"
                                            :aria-invalid="
                                                !!form.errors.telepon
                                            "
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.telepon"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.telepon }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Jadwal kunjungan -->
                        <div class="space-y-6 px-5 py-7 sm:px-7">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <CalendarDays class="size-4" />
                                </div>

                                <div>
                                    <h3
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ t.scheduleSection }}
                                    </h3>
                                    <p
                                        class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                    >
                                        {{ t.scheduleDescription }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-3">
                                <div>
                                    <label
                                        for="tanggal_kunjungan"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.date }}
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="tanggal_kunjungan"
                                        v-model="form.tanggal_kunjungan"
                                        type="date"
                                        required
                                        :min="todayKey"
                                        :class="plainInputClass"
                                        :aria-invalid="
                                            !!form.errors.tanggal_kunjungan
                                        "
                                    />

                                    <p
                                        v-if="form.errors.tanggal_kunjungan"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.tanggal_kunjungan }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="waktu_mulai"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.startTime }}
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="waktu_mulai"
                                        v-model="form.waktu_mulai"
                                        type="time"
                                        required
                                        :class="plainInputClass"
                                        :aria-invalid="
                                            !!form.errors.waktu_mulai
                                        "
                                    />

                                    <p
                                        v-if="form.errors.waktu_mulai"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.waktu_mulai }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="waktu_selesai"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.endTime }}
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input
                                        id="waktu_selesai"
                                        v-model="form.waktu_selesai"
                                        type="time"
                                        required
                                        :class="plainInputClass"
                                        :aria-invalid="
                                            !!form.errors.waktu_selesai
                                        "
                                    />

                                    <p
                                        v-if="
                                            timeOrderError ||
                                            form.errors.waktu_selesai
                                        "
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{
                                            timeOrderError ||
                                            form.errors.waktu_selesai
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="hasOverlap"
                                class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300"
                                role="alert"
                            >
                                {{ scheduleOverlapMessage }}
                            </div>
                        </div>

                        <!-- Detail kunjungan -->
                        <div class="space-y-6 px-5 py-7 sm:px-7">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <FileText class="size-4" />
                                </div>

                                <div>
                                    <h3
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ t.visitDetails }}
                                    </h3>
                                    <p
                                        class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                    >
                                        {{ t.visitDetailsDescription }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <!-- Jumlah peserta -->
                                <div>
                                    <label
                                        for="jumlah_peserta"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.participants }}
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <Users
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="jumlah_peserta"
                                            v-model.number="form.jumlah_peserta"
                                            type="number"
                                            required
                                            min="1"
                                            max="65535"
                                            inputmode="numeric"
                                            :class="inputClass"
                                            :aria-invalid="
                                                !!form.errors.jumlah_peserta
                                            "
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.jumlah_peserta"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.jumlah_peserta }}
                                    </p>
                                </div>

                                <!-- Area lahan -->
                                <div>
                                    <label
                                        for="area_lahan"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.landArea }}
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <MapPin
                                            class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                        />
                                        <input
                                            id="area_lahan"
                                            v-model="form.area_lahan"
                                            type="text"
                                            required
                                            maxlength="255"
                                            :placeholder="t.landAreaPlaceholder"
                                            :class="inputClass"
                                            :aria-invalid="
                                                !!form.errors.area_lahan
                                            "
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.area_lahan"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.area_lahan }}
                                    </p>
                                </div>

                                <!-- Pendamping -->
                                <div class="sm:col-span-2">
                                    <span
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.needGuide }}
                                        <span class="text-red-500">*</span>
                                    </span>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <button
                                            type="button"
                                            class="flex items-center gap-3 rounded-xl border p-4 text-left text-sm transition focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                                            :class="
                                                form.memerlukan_pendamping
                                                    ? 'border-blue-500 bg-blue-50/60 text-slate-900 dark:border-blue-600 dark:bg-blue-950/30 dark:text-white'
                                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400'
                                            "
                                            :aria-pressed="
                                                form.memerlukan_pendamping
                                            "
                                            @click="
                                                form.memerlukan_pendamping = true
                                            "
                                        >
                                            <CheckCircle2
                                                class="size-5 shrink-0"
                                                :class="
                                                    form.memerlukan_pendamping
                                                        ? 'text-blue-600'
                                                        : 'text-slate-300'
                                                "
                                            />
                                            <span>
                                                <span
                                                    class="block font-semibold"
                                                >
                                                    {{ t.yesGuide }}
                                                </span>
                                                <span
                                                    class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400"
                                                >
                                                    {{ t.yesGuideDescription }}
                                                </span>
                                            </span>
                                        </button>

                                        <button
                                            type="button"
                                            class="flex items-center gap-3 rounded-xl border p-4 text-left text-sm transition focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                                            :class="
                                                !form.memerlukan_pendamping
                                                    ? 'border-blue-500 bg-blue-50/60 text-slate-900 dark:border-blue-600 dark:bg-blue-950/30 dark:text-white'
                                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400'
                                            "
                                            :aria-pressed="
                                                !form.memerlukan_pendamping
                                            "
                                            @click="
                                                form.memerlukan_pendamping = false
                                            "
                                        >
                                            <CheckCircle2
                                                class="size-5 shrink-0"
                                                :class="
                                                    !form.memerlukan_pendamping
                                                        ? 'text-blue-600'
                                                        : 'text-slate-300'
                                                "
                                            />
                                            <span>
                                                <span
                                                    class="block font-semibold"
                                                >
                                                    {{ t.noGuide }}
                                                </span>
                                                <span
                                                    class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400"
                                                >
                                                    {{ t.noGuideDescription }}
                                                </span>
                                            </span>
                                        </button>
                                    </div>

                                    <p
                                        v-if="form.errors.memerlukan_pendamping"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.memerlukan_pendamping }}
                                    </p>
                                </div>

                                <!-- Keperluan -->
                                <div class="sm:col-span-2">
                                    <label
                                        for="keperluan"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{ t.purpose }}
                                        <span
                                            class="font-normal text-slate-400"
                                        >
                                            ({{ t.optional }})
                                        </span>
                                    </label>

                                    <textarea
                                        id="keperluan"
                                        v-model="form.keperluan"
                                        rows="5"
                                        maxlength="5000"
                                        :placeholder="t.purposePlaceholder"
                                        class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950/50 dark:text-white"
                                    />

                                    <p
                                        v-if="form.errors.keperluan"
                                        class="mt-1.5 text-xs font-medium text-red-600"
                                    >
                                        {{ form.errors.keperluan }}
                                    </p>
                                </div>
                            </div>

                            <!-- Tombol submit -->
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/50 sm:p-5"
                            >
                                <div
                                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="flex gap-3">
                                        <ShieldCheck
                                            class="mt-0.5 size-5 shrink-0 text-blue-600 dark:text-blue-400"
                                        />
                                        <div>
                                            <p
                                                class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{ t.reviewData }}
                                            </p>
                                            <p
                                                class="mt-0.5 text-xs leading-5 text-slate-500 dark:text-slate-400"
                                            >
                                                {{ t.reviewDescription }}
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="!canSubmit"
                                        class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <LoaderCircle
                                            v-if="form.processing"
                                            class="size-4 animate-spin"
                                        />
                                        <Send v-else class="size-4" />
                                        {{
                                            form.processing
                                                ? t.sending
                                                : t.submit
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Sidebar -->
                <aside
                    class="min-w-0 space-y-5 lg:sticky lg:top-24 lg:self-start"
                >
                    <!-- Jadwal terisi -->
                    <div
                        class="reveal overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white/90 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 180"
                    >
                        <div
                            class="border-b border-slate-100/90 px-5 py-5 dark:border-slate-800"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                >
                                    <Clock3 class="size-4" />
                                </div>
                                <h3
                                    class="text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    {{ t.filledSchedule }}
                                </h3>
                            </div>
                        </div>

                        <div class="px-5 py-5">
                            <p
                                v-if="!form.tanggal_kunjungan"
                                class="text-sm leading-6 text-slate-500 dark:text-slate-400"
                            >
                                {{ t.filledScheduleHint }}
                            </p>

                            <template v-else>
                                <p class="text-xs font-medium text-slate-400">
                                    {{ formatDateLong(form.tanggal_kunjungan) }}
                                </p>

                                <p
                                    v-if="!jadwal_terisi.length"
                                    class="mt-2 text-sm font-semibold text-emerald-700 dark:text-emerald-400"
                                >
                                    {{ t.allTimesAvailable }}
                                </p>

                                <ul v-else class="mt-3 space-y-2">
                                    <li
                                        v-for="slot in jadwal_terisi"
                                        :key="`${slot.mulai}-${slot.selesai}`"
                                        class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-300"
                                    >
                                        <span>
                                            {{ slot.mulai }} –
                                            {{ slot.selesai }}
                                        </span>
                                        <span
                                            class="text-xs font-normal text-slate-400"
                                        >
                                            {{ t.booked }}
                                        </span>
                                    </li>
                                </ul>
                            </template>
                        </div>
                    </div>

                    <!-- Alur pengajuan -->
                    <div
                        class="reveal rounded-[1.5rem] border border-slate-200/80 bg-white/90 p-5 shadow-lg shadow-slate-900/[0.04] backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90"
                        style="--d: 240"
                    >
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            {{ t.afterSubmit }}
                        </h3>

                        <ol class="mt-4 space-y-4">
                            <li
                                v-for="(step, index) in [
                                    t.step1,
                                    t.step2,
                                    t.step3,
                                ]"
                                :key="index"
                                class="flex gap-3 text-sm leading-5 text-slate-600 dark:text-slate-400"
                            >
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"
                                >
                                    {{ index + 1 }}
                                </span>
                                <span>{{ step }}</span>
                            </li>
                        </ol>
                    </div>
                </aside>
            </div>
        </section>

        <!-- Modal sukses -->
        <Transition name="modal">
            <div
                v-if="showSuccessModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                role="dialog"
                aria-modal="true"
                aria-labelledby="success-title"
                @click.self="closeSuccessModal"
            >
                <div
                    class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="px-6 pb-6 pt-7 text-center sm:px-8">
                        <div
                            class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/70 dark:bg-emerald-950/40 dark:ring-emerald-950/20 dark:text-emerald-400"
                        >
                            <CheckCircle2 class="size-7" />
                        </div>

                        <h2
                            id="success-title"
                            class="mt-6 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            {{ t.successTitle }}
                        </h2>

                        <p
                            class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            {{ t.successDescription }}
                        </p>

                        <div
                            v-if="submittedNumber"
                            class="mt-5 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/60"
                        >
                            <span
                                class="truncate text-sm font-bold tracking-wide text-slate-800 dark:text-slate-200"
                            >
                                {{ submittedNumber }}
                            </span>

                            <button
                                type="button"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/40"
                                @click="copyNumber"
                            >
                                <Check v-if="copied" class="size-3.5" />
                                <Copy v-else class="size-3.5" />
                                {{ copied ? t.copied : t.copy }}
                            </button>
                        </div>
                    </div>

                    <div
                        class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-5 dark:border-slate-800 dark:bg-slate-950/50 sm:px-8"
                    >
                        <button
                            ref="successCloseButton"
                            type="button"
                            class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-bold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                            @click="closeSuccessModal"
                        >
                            {{ t.close }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
<style scoped>
.blob {
    transform: translateZ(0);
    will-change: transform;
}

.blob-a {
    animation: float-a 14s ease-in-out infinite;
}

.blob-b {
    animation: float-b 17s ease-in-out infinite;
}

.blob-c {
    animation: float-c 19s ease-in-out infinite;
}

.reveal {
    opacity: 0;
    transform: translateY(18px);
    transition:
        opacity 700ms ease,
        transform 700ms cubic-bezier(0.22, 1, 0.36, 1);
    transition-delay: calc(var(--d, 0) * 1ms);
    will-change: opacity, transform;
}

.reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

.modal-enter-active,
.modal-leave-active {
    transition:
        opacity 220ms ease,
        transform 220ms cubic-bezier(0.22, 1, 0.36, 1);
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
    transform: translateY(10px) scale(0.98);
}

@keyframes float-a {
    0%,
    100% {
        transform: translate3d(0, 0, 0);
    }

    50% {
        transform: translate3d(16px, 22px, 0);
    }
}

@keyframes float-b {
    0%,
    100% {
        transform: translate3d(0, 0, 0);
    }

    50% {
        transform: translate3d(-18px, 16px, 0);
    }
}

@keyframes float-c {
    0%,
    100% {
        transform: translate3d(0, 0, 0);
    }

    50% {
        transform: translate3d(12px, -18px, 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .blob-a,
    .blob-b,
    .blob-c {
        animation: none;
    }

    .reveal {
        opacity: 1;
        transform: none;
        transition: none;
        will-change: auto;
    }

    .modal-enter-active,
    .modal-leave-active {
        transition: none;
    }

    .modal-enter-from,
    .modal-leave-to {
        opacity: 0;
    }

    .modal-enter-from > div,
    .modal-leave-to > div {
        transform: none;
    }
}
</style>
