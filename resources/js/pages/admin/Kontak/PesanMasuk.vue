<script setup lang="ts">
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import { Head, router } from "@inertiajs/vue3";
import {
    AlertCircle,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Clock3,
    Eye,
    FileText,
    LoaderCircle,
    Mail,
    MessageCircleReply,
    RefreshCw,
    Search,
    Send,
    Trash2,
    User,
    X,
} from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

/* =========================================================
   Types
========================================================= */

type PesanStatus = "baru" | "dibaca" | "dibalas";

/**
 * Status yang boleh diubah lewat endpoint updateStatus.
 * "dibalas" hanya bisa terjadi lewat endpoint reply.
 */
type UpdatableStatus = Exclude<PesanStatus, "dibalas">;

interface PesanKontak {
    id: number;
    nama: string;
    email: string;
    telepon?: string | null;
    perusahaan?: string | null;
    subjek: string;
    pesan: string;
    status: PesanStatus;
    dibaca_at?: string | null;
    dibalas_at?: string | null;
    balasan?: string | null;
    created_at: string;
    updated_at?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator<T> {
    current_page: number;
    data: T[];
    first_page_url?: string;
    from: number | null;
    last_page: number;
    last_page_url?: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path?: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

interface Props {
    pesans: Paginator<PesanKontak>;

    counts: {
        semua: number;
        baru: number;
        dibaca: number;
        dibalas: number;
    };

    filters: {
        search?: string;
        status?: PesanStatus | "" | null;
    };
}

const props = defineProps<Props>();

/* =========================================================
   State
========================================================= */

const search = ref(props.filters?.search ?? "");

const selectedStatus = ref<"" | PesanStatus>(props.filters?.status ?? "");

const isLoading = ref(false);
const isRefreshing = ref(false);

const selectedPesan = ref<PesanKontak | null>(null);

const showDetailModal = ref(false);
const showReplyModal = ref(false);
const showDeleteModal = ref(false);

const deleting = ref(false);
const replying = ref(false);
const updatingStatusId = ref<number | null>(null);

const replyMessage = ref("");
const replyError = ref("");

/* =========================================================
   Refs - Modal Accessibility
========================================================= */

const detailCloseButton = ref<HTMLButtonElement | null>(null);
const replyCloseButton = ref<HTMLButtonElement | null>(null);
const deleteCancelButton = ref<HTMLButtonElement | null>(null);

const detailModalPanel = ref<HTMLElement | null>(null);
const replyModalPanel = ref<HTMLElement | null>(null);
const deleteModalPanel = ref<HTMLElement | null>(null);

const replyTextarea = ref<HTMLTextAreaElement | null>(null);

const detailTrigger = ref<HTMLElement | null>(null);
const replyTrigger = ref<HTMLElement | null>(null);
const deleteTrigger = ref<HTMLElement | null>(null);

/* =========================================================
   Search debounce
========================================================= */

let searchTimer: ReturnType<typeof setTimeout> | null = null;

/* =========================================================
   Body Scroll Lock
========================================================= */

let bodyScrollLocked = false;

const lockBodyScroll = () => {
    if (bodyScrollLocked) {
        return;
    }

    document.body.style.overflow = "hidden";
    bodyScrollLocked = true;
};

const unlockBodyScroll = () => {
    if (
        showDetailModal.value ||
        showReplyModal.value ||
        showDeleteModal.value
    ) {
        return;
    }

    document.body.style.overflow = "";
    bodyScrollLocked = false;
};

/* =========================================================
   Trigger / Focus Helpers
========================================================= */

const toHTMLElement = (
    target: EventTarget | null | undefined,
): HTMLElement | null => {
    return target instanceof HTMLElement ? target : null;
};

const resolveTrigger = (target?: EventTarget | null): HTMLElement | null => {
    return (
        toHTMLElement(target) ??
        (document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null)
    );
};

/* =========================================================
   Modal State
========================================================= */

const activeModal = computed<"detail" | "reply" | "delete" | null>(() => {
    /*
     * Modal paling atas diprioritaskan:
     * Delete > Reply > Detail
     */
    if (showDeleteModal.value) {
        return "delete";
    }

    if (showReplyModal.value) {
        return "reply";
    }

    if (showDetailModal.value) {
        return "detail";
    }

    return null;
});

/* =========================================================
   Computed
========================================================= */

/**
 * Pesan dianggap sudah dibalas jika ada isi balasan
 * atau waktu dibalas. Kondisi ini sama dengan guard
 * di controller, sehingga UI dan server selalu konsisten.
 */
const hasReplied = computed(() => {
    const pesan = selectedPesan.value;

    if (!pesan) {
        return false;
    }

    return Boolean(pesan.balasan?.trim()) || Boolean(pesan.dibalas_at);
});

const replySubject = computed(() => {
    if (!selectedPesan.value) {
        return "";
    }

    const subject = selectedPesan.value.subjek?.trim() || "";

    return subject.toLowerCase().startsWith("re:") ? subject : `Re: ${subject}`;
});

const replyCharacterCount = computed(() => {
    return replyMessage.value.length;
});

const canSubmitReply = computed(() => {
    if (replying.value || hasReplied.value) {
        return false;
    }

    const length = replyMessage.value.trim().length;

    return length >= 10 && length <= 5000;
});

const statusCards = computed(() => [
    {
        key: "" as const,
        label: "Semua Pesan",
        value: props.counts?.semua ?? 0,
        icon: Mail,
        active: selectedStatus.value === "",
    },
    {
        key: "baru" as const,
        label: "Pesan Baru",
        value: props.counts?.baru ?? 0,
        icon: Clock3,
        active: selectedStatus.value === "baru",
    },
    {
        key: "dibaca" as const,
        label: "Sudah Dibaca",
        value: props.counts?.dibaca ?? 0,
        icon: Eye,
        active: selectedStatus.value === "dibaca",
    },
    {
        key: "dibalas" as const,
        label: "Sudah Dibalas",
        value: props.counts?.dibalas ?? 0,
        icon: MessageCircleReply,
        active: selectedStatus.value === "dibalas",
    },
]);

/* =========================================================
   Helpers
========================================================= */

const statusLabel = (status: PesanStatus) => {
    const labels: Record<PesanStatus, string> = {
        baru: "Baru",
        dibaca: "Dibaca",
        dibalas: "Dibalas",
    };

    return labels[status];
};

const statusClasses = (status: PesanStatus) => {
    const classes: Record<PesanStatus, string> = {
        baru: "border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300",

        dibaca: "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300",

        dibalas:
            "border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300",
    };

    return classes[status];
};

const formatDate = (date: string | null | undefined) => {
    if (!date) {
        return "-";
    }

    try {
        const parsedDate = new Date(date);

        if (Number.isNaN(parsedDate.getTime())) {
            return date;
        }

        return new Intl.DateTimeFormat("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        }).format(parsedDate);
    } catch {
        return date;
    }
};

const formatShortDate = (date: string | null | undefined) => {
    if (!date) {
        return "-";
    }

    try {
        const parsedDate = new Date(date);

        if (Number.isNaN(parsedDate.getTime())) {
            return date;
        }

        return new Intl.DateTimeFormat("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        }).format(parsedDate);
    } catch {
        return date;
    }
};

const truncate = (text: string, length = 100) => {
    if (!text) {
        return "-";
    }

    if (text.length <= length) {
        return text;
    }

    return `${text.slice(0, length).trim()}…`;
};

const statusIcon = (status: PesanStatus) => {
    if (status === "baru") {
        return Clock3;
    }

    if (status === "dibaca") {
        return Eye;
    }

    return CheckCircle2;
};

/* =========================================================
   Filters
========================================================= */

const applyFilters = () => {
    isLoading.value = true;

    router.get(
        "/admin/kontak/pesan",
        {
            search: search.value.trim() || undefined,
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,

            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

const clearFilters = () => {
    search.value = "";
    selectedStatus.value = "";

    applyFilters();
};

const selectStatusFilter = (status: "" | PesanStatus) => {
    selectedStatus.value = status;

    applyFilters();
};

watch(search, () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        applyFilters();
    }, 450);
});

/* =========================================================
   Pagination
========================================================= */

const goToPage = (url: string | null) => {
    if (!url || isLoading.value) {
        return;
    }

    isLoading.value = true;

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,

        onStart: () => {
            isLoading.value = true;
        },

        onFinish: () => {
            isLoading.value = false;
        },
    });
};

/* =========================================================
   Refresh
========================================================= */

const refreshPage = () => {
    if (isRefreshing.value) {
        return;
    }

    isRefreshing.value = true;

    router.reload({
        only: ["pesans", "counts"],

        onFinish: () => {
            isRefreshing.value = false;
        },
    });
};

/* =========================================================
   Update Status (baru / dibaca saja)
========================================================= */

const updateStatus = (pesan: PesanKontak, status: UpdatableStatus) => {
    if (updatingStatusId.value !== null) {
        return;
    }

    updatingStatusId.value = pesan.id;

    router.patch(
        `/admin/kontak/pesan/${pesan.id}/status`,
        {
            status,
        },
        {
            preserveScroll: true,
            preserveState: true,

            onSuccess: () => {
                if (selectedPesan.value?.id === pesan.id) {
                    selectedPesan.value = {
                        ...selectedPesan.value,

                        status,

                        dibaca_at:
                            status === "baru"
                                ? null
                                : (selectedPesan.value.dibaca_at ??
                                  new Date().toISOString()),
                    };
                }
            },

            onFinish: () => {
                updatingStatusId.value = null;
            },
        },
    );
};

/* =========================================================
   Detail Modal
========================================================= */

const openDetail = async (pesan: PesanKontak, trigger?: EventTarget | null) => {
    selectedPesan.value = pesan;

    detailTrigger.value = resolveTrigger(trigger);

    replyMessage.value = "";
    replyError.value = "";

    showDetailModal.value = true;

    lockBodyScroll();

    await nextTick();

    detailCloseButton.value?.focus();

    /*
     * Pesan baru otomatis menjadi "dibaca"
     * ketika detail dibuka.
     */
    if (pesan.status === "baru") {
        updateStatus(pesan, "dibaca");
    }
};

const closeDetail = async () => {
    /*
     * Jangan menutup modal parent ketika child modal
     * masih aktif.
     */
    if (showReplyModal.value || showDeleteModal.value || replying.value) {
        return;
    }

    showDetailModal.value = false;

    unlockBodyScroll();

    await nextTick();

    const trigger = detailTrigger.value;

    if (trigger && document.contains(trigger)) {
        trigger.focus();
    }

    detailTrigger.value = null;
};

/* =========================================================
   Reply Modal
========================================================= */

const openReplyModal = async (trigger?: EventTarget | null) => {
    if (!selectedPesan.value || replying.value) {
        return;
    }

    replyTrigger.value = resolveTrigger(trigger);

    replyMessage.value = "";
    replyError.value = "";

    showReplyModal.value = true;

    lockBodyScroll();

    await nextTick();

    /*
     * Jika sudah dibalas, hanya tampilkan balasan
     * (read-only). Fokus ke tombol tutup.
     */
    if (hasReplied.value) {
        replyCloseButton.value?.focus();
    } else {
        replyTextarea.value?.focus();
    }
};

const closeReply = async () => {
    if (replying.value) {
        return;
    }

    showReplyModal.value = false;

    replyMessage.value = "";
    replyError.value = "";

    unlockBodyScroll();

    await nextTick();

    const trigger = replyTrigger.value;

    if (trigger && document.contains(trigger)) {
        trigger.focus();
    } else {
        detailCloseButton.value?.focus();
    }

    replyTrigger.value = null;
};

/* =========================================================
   Submit Reply
========================================================= */

const submitReply = () => {
    const pesan = selectedPesan.value;

    /*
     * canSubmitReply sudah memastikan pesan belum dibalas.
     * Server tetap memeriksa ulang sebagai pengaman terakhir.
     */
    if (!pesan || !canSubmitReply.value) {
        return;
    }

    const message = replyMessage.value.trim();

    if (message.length < 10 || message.length > 5000) {
        return;
    }

    replying.value = true;
    replyError.value = "";

    router.post(
        `/admin/kontak/pesan/${pesan.id}/reply`,
        {
            balasan: message,
        },
        {
            preserveScroll: true,
            preserveState: true,

            onSuccess: () => {
                if (selectedPesan.value?.id === pesan.id) {
                    const now = new Date().toISOString();

                    selectedPesan.value = {
                        ...selectedPesan.value,

                        status: "dibalas",

                        balasan: message,

                        dibaca_at: selectedPesan.value.dibaca_at ?? now,

                        dibalas_at: selectedPesan.value.dibalas_at ?? now,
                    };
                }

                replyMessage.value = "";
                replyError.value = "";

                showReplyModal.value = false;

                unlockBodyScroll();

                nextTick(() => {
                    const trigger = replyTrigger.value;

                    if (trigger && document.contains(trigger)) {
                        trigger.focus();
                    } else {
                        detailCloseButton.value?.focus();
                    }

                    replyTrigger.value = null;
                });
            },

            onError: (errors) => {
                /*
                 * Modal tetap terbuka dan isi balasan dipertahankan
                 * agar admin bisa memperbaiki atau mencoba lagi.
                 */
                replyError.value =
                    (errors.balasan as string | undefined) ??
                    "Gagal mengirim balasan. Silakan coba lagi.";
            },

            onFinish: () => {
                replying.value = false;
            },
        },
    );
};

/* =========================================================
   Delete Modal
========================================================= */

const openDelete = async (pesan: PesanKontak, trigger?: EventTarget | null) => {
    selectedPesan.value = pesan;

    deleteTrigger.value = resolveTrigger(trigger);

    showDeleteModal.value = true;

    lockBodyScroll();

    await nextTick();

    deleteCancelButton.value?.focus();
};

const closeDelete = async () => {
    if (deleting.value) {
        return;
    }

    showDeleteModal.value = false;

    unlockBodyScroll();

    await nextTick();

    const trigger = deleteTrigger.value;

    if (trigger && document.contains(trigger)) {
        trigger.focus();
    } else {
        detailCloseButton.value?.focus();
    }

    deleteTrigger.value = null;
};

const confirmDelete = () => {
    const pesan = selectedPesan.value;

    if (!pesan || deleting.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/kontak/pesan/${pesan.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            showDeleteModal.value = false;
            showReplyModal.value = false;
            showDetailModal.value = false;

            selectedPesan.value = null;

            replyMessage.value = "";
            replyError.value = "";

            replyTrigger.value = null;
            deleteTrigger.value = null;
            detailTrigger.value = null;

            unlockBodyScroll();
        },

        onFinish: () => {
            deleting.value = false;
        },
    });
};

/* =========================================================
   Focus Trap
========================================================= */

const getFocusableElements = (container: HTMLElement | null): HTMLElement[] => {
    if (!container) {
        return [];
    }

    return Array.from(
        container.querySelectorAll<HTMLElement>(
            [
                "a[href]",
                "button:not([disabled])",
                "textarea:not([disabled])",
                "input:not([disabled])",
                "select:not([disabled])",
                "[tabindex]:not([tabindex='-1'])",
            ].join(","),
        ),
    ).filter((element) => {
        const style = window.getComputedStyle(element);

        return style.display !== "none" && style.visibility !== "hidden";
    });
};

const getActiveModalPanel = () => {
    if (activeModal.value === "delete") {
        return deleteModalPanel.value;
    }

    if (activeModal.value === "reply") {
        return replyModalPanel.value;
    }

    if (activeModal.value === "detail") {
        return detailModalPanel.value;
    }

    return null;
};

const handleModalKeydown = (event: KeyboardEvent) => {
    const modal = activeModal.value;

    if (!modal) {
        return;
    }

    /*
     * Escape hanya menutup modal paling atas.
     */
    if (event.key === "Escape") {
        event.preventDefault();
        event.stopPropagation();

        if (modal === "delete") {
            closeDelete();
        } else if (modal === "reply") {
            closeReply();
        } else if (modal === "detail") {
            closeDetail();
        }

        return;
    }

    /*
     * Focus trap.
     */
    if (event.key !== "Tab") {
        return;
    }

    const panel = getActiveModalPanel();

    if (!panel) {
        return;
    }

    const focusable = getFocusableElements(panel);

    if (!focusable.length) {
        event.preventDefault();
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
        return;
    }

    if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};

const handleModalPointerDown = (event: PointerEvent) => {
    const panel = getActiveModalPanel();

    if (!panel) {
        return;
    }

    /*
     * Mencegah interaksi pointer keluar modal aktif,
     * terutama ketika modal bertumpuk.
     */
    if (event.target instanceof Node && !panel.contains(event.target)) {
        event.preventDefault();
    }
};

/* =========================================================
   Reveal / Fade
========================================================= */

const revealElements = ref<HTMLElement[]>([]);

let revealObserver: IntersectionObserver | null = null;

const setupRevealObserver = () => {
    if (typeof window === "undefined" || !("IntersectionObserver" in window)) {
        return;
    }

    revealObserver?.disconnect();

    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");

                    revealObserver?.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.08,
            rootMargin: "0px 0px -40px 0px",
        },
    );

    revealElements.value.forEach((element) => {
        revealObserver?.observe(element);
    });
};

const collectRevealElements = () => {
    revealElements.value = Array.from(
        document.querySelectorAll<HTMLElement>("[data-reveal]"),
    );

    setupRevealObserver();
};

/* =========================================================
   Watch Modal
========================================================= */

watch(activeModal, (modal) => {
    if (modal) {
        lockBodyScroll();
    } else {
        unlockBodyScroll();
    }
});

/* =========================================================
   Lifecycle
========================================================= */

onMounted(() => {
    document.addEventListener("keydown", handleModalKeydown, true);

    document.addEventListener("pointerdown", handleModalPointerDown, true);

    nextTick(() => {
        collectRevealElements();
    });
});

onBeforeUnmount(() => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    revealObserver?.disconnect();

    document.removeEventListener("keydown", handleModalKeydown, true);

    document.removeEventListener("pointerdown", handleModalPointerDown, true);

    document.body.style.overflow = "";

    bodyScrollLocked = false;
});
</script>

<template>
    <Head title="Pesan Masuk" />

    <div
        class="relative min-h-screen overflow-x-clip bg-slate-50/70 dark:bg-slate-950"
    >
        <!-- Decorative background -->
        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -left-32 -top-32 h-80 w-80 rounded-full bg-blue-500/10 blur-3xl"
            />

            <div
                class="absolute right-[-120px] top-1/3 h-96 w-96 rounded-full bg-slate-400/10 blur-3xl"
            />

            <div
                class="absolute bottom-[-180px] left-1/3 h-96 w-96 rounded-full bg-blue-400/5 blur-3xl"
            />
        </div>

        <main
            class="relative mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8"
        >
            <!-- Header -->
            <section data-reveal class="reveal-section mb-6">
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div>
                        <div
                            class="mb-2 inline-flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400"
                        >
                            <Mail class="h-4 w-4" />
                            Pusat Informasi
                        </div>

                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl"
                        >
                            Pesan Masuk
                        </h1>

                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-400"
                        >
                            Kelola pesan yang dikirim pengunjung melalui
                            formulir kontak website KITB.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300"
                        :disabled="isRefreshing"
                        @click="refreshPage"
                    >
                        <RefreshCw
                            class="h-4 w-4"
                            :class="isRefreshing ? 'animate-spin' : ''"
                        />

                        Refresh
                    </button>
                </div>
            </section>

            <!-- Stats -->
            <section data-reveal class="reveal-section mb-6">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <button
                        v-for="card in statusCards"
                        :key="card.key || 'semua'"
                        type="button"
                        class="group rounded-2xl border p-4 text-left shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                        :class="
                            card.active
                                ? 'border-blue-200 bg-blue-50 shadow-blue-100 dark:border-blue-900/60 dark:bg-blue-950/30 dark:shadow-none'
                                : 'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700'
                        "
                        @click="selectStatusFilter(card.key)"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl"
                                :class="
                                    card.active
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                "
                            >
                                <component :is="card.icon" class="h-5 w-5" />
                            </div>

                            <span
                                class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >
                                {{ card.value }}
                            </span>
                        </div>

                        <p
                            class="mt-3 text-sm font-medium text-slate-600 dark:text-slate-400"
                        >
                            {{ card.label }}
                        </p>
                    </button>
                </div>
            </section>

            <!-- Search -->
            <section data-reveal class="reveal-section mb-6">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex flex-col gap-3 lg:flex-row lg:items-center"
                    >
                        <div class="relative min-w-0 flex-1">
                            <Search
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="search"
                                type="search"
                                autocomplete="off"
                                placeholder="Cari nama, email, perusahaan, subjek, atau isi pesan..."
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-10 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:bg-slate-950"
                            />

                            <LoaderCircle
                                v-if="isLoading"
                                class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-blue-500"
                            />

                            <button
                                v-else-if="search"
                                type="button"
                                class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                aria-label="Hapus pencarian"
                                @click="search = ''"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="card in statusCards"
                                :key="`filter-${card.key || 'semua'}`"
                                type="button"
                                class="inline-flex h-10 items-center gap-2 rounded-xl border px-3.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                :class="
                                    card.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'
                                "
                                @click="selectStatusFilter(card.key)"
                            >
                                {{ card.label }}
                            </button>

                            <button
                                v-if="search || selectedStatus"
                                type="button"
                                class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="clearFilters"
                            >
                                <X class="h-4 w-4" />
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Desktop Table -->
            <section
                data-reveal
                class="reveal-section hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 md:block"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px]">
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-950/50"
                            >
                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Pengirim
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Subjek
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Pesan
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Tanggal
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            v-if="!isLoading && props.pesans.data.length"
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="pesan in props.pesans.data"
                                :key="pesan.id"
                                class="group transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/30"
                            >
                                <td class="px-5 py-4">
                                    <div
                                        class="flex min-w-0 items-center gap-3"
                                    >
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        >
                                            <User class="h-4 w-4" />
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{ pesan.nama }}
                                            </p>

                                            <p
                                                class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                {{ pesan.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="max-w-[220px] px-5 py-4">
                                    <p
                                        class="truncate text-sm font-medium text-slate-800 dark:text-slate-200"
                                    >
                                        {{ pesan.subjek }}
                                    </p>
                                </td>

                                <td class="max-w-[320px] px-5 py-4">
                                    <p
                                        class="line-clamp-2 text-sm leading-5 text-slate-600 dark:text-slate-400"
                                    >
                                        {{ truncate(pesan.pesan, 120) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold"
                                        :class="statusClasses(pesan.status)"
                                    >
                                        <component
                                            :is="statusIcon(pesan.status)"
                                            class="h-3.5 w-3.5"
                                        />

                                        {{ statusLabel(pesan.status) }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <p
                                        class="text-sm text-slate-700 dark:text-slate-300"
                                    >
                                        {{ formatShortDate(pesan.created_at) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-1.5">
                                        <button
                                            type="button"
                                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300"
                                            @click="
                                                openDetail(
                                                    pesan,
                                                    $event.currentTarget,
                                                )
                                            "
                                        >
                                            <Eye class="h-4 w-4" />

                                            Detail
                                        </button>

                                        <button
                                            type="button"
                                            class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500/30 dark:border-red-900/60 dark:bg-slate-900 dark:text-red-400 dark:hover:bg-red-950/30"
                                            aria-label="Hapus pesan"
                                            @click="
                                                openDelete(
                                                    pesan,
                                                    $event.currentTarget,
                                                )
                                            "
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>

                        <!-- Skeleton -->
                        <tbody v-else-if="isLoading">
                            <tr
                                v-for="n in 6"
                                :key="`skeleton-${n}`"
                                class="border-b border-slate-100 dark:border-slate-800"
                            >
                                <td
                                    v-for="column in 6"
                                    :key="column"
                                    class="px-5 py-5"
                                >
                                    <div
                                        class="h-4 animate-pulse rounded-md bg-slate-200 dark:bg-slate-800"
                                        :class="
                                            column === 1
                                                ? 'w-40'
                                                : column === 2
                                                  ? 'w-44'
                                                  : column === 3
                                                    ? 'w-56'
                                                    : column === 4
                                                      ? 'w-20'
                                                      : column === 5
                                                        ? 'w-24'
                                                        : 'ml-auto w-24'
                                        "
                                    />
                                </td>
                            </tr>
                        </tbody>

                        <!-- Empty -->
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800"
                                    >
                                        <Mail class="h-7 w-7" />
                                    </div>

                                    <h3
                                        class="mt-4 text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        Tidak ada pesan
                                    </h3>

                                    <p
                                        class="mx-auto mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Belum ada pesan yang sesuai dengan
                                        pencarian atau filter yang dipilih.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="props.pesans.last_page > 1"
                    class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Menampilkan

                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ props.pesans.from ?? 0 }}
                        </span>

                        -

                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ props.pesans.to ?? 0 }}
                        </span>

                        dari

                        <span
                            class="font-semibold text-slate-700 dark:text-slate-200"
                        >
                            {{ props.pesans.total }}
                        </span>

                        pesan
                    </p>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                            :disabled="!props.pesans.prev_page_url || isLoading"
                            aria-label="Halaman sebelumnya"
                            @click="goToPage(props.pesans.prev_page_url)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>

                        <template
                            v-for="(link, index) in props.pesans.links.slice(
                                1,
                                -1,
                            )"
                            :key="`${link.label}-${index}`"
                        >
                            <button
                                v-if="link.url"
                                type="button"
                                class="flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-sm font-medium transition"
                                :class="
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'
                                "
                                :disabled="isLoading"
                                @click="goToPage(link.url)"
                            >
                                {{ link.label }}
                            </button>
                        </template>

                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                            :disabled="!props.pesans.next_page_url || isLoading"
                            aria-label="Halaman berikutnya"
                            @click="goToPage(props.pesans.next_page_url)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </section>

            <!-- Mobile List -->
            <section data-reveal class="reveal-section space-y-3 md:hidden">
                <template v-if="!isLoading && props.pesans.data.length">
                    <article
                        v-for="pesan in props.pesans.data"
                        :key="pesan.id"
                        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    <User class="h-4 w-4" />
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ pesan.nama }}
                                    </p>

                                    <p
                                        class="truncate text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ pesan.email }}
                                    </p>
                                </div>
                            </div>

                            <span
                                class="shrink-0 rounded-full border px-2 py-1 text-[11px] font-semibold"
                                :class="statusClasses(pesan.status)"
                            >
                                {{ statusLabel(pesan.status) }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <p
                                class="text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                {{ pesan.subjek }}
                            </p>

                            <p
                                class="mt-1 line-clamp-3 text-sm leading-5 text-slate-600 dark:text-slate-400"
                            >
                                {{ pesan.pesan }}
                            </p>
                        </div>

                        <div
                            class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800"
                        >
                            <span
                                class="text-xs text-slate-500 dark:text-slate-400"
                            >
                                {{ formatShortDate(pesan.created_at) }}
                            </span>

                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                    @click="
                                        openDetail(pesan, $event.currentTarget)
                                    "
                                >
                                    <Eye class="h-4 w-4" />

                                    Detail
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-600 transition hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/30"
                                    aria-label="Hapus pesan"
                                    @click="
                                        openDelete(pesan, $event.currentTarget)
                                    "
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </article>
                </template>

                <!-- Mobile Skeleton -->
                <template v-else-if="isLoading">
                    <div
                        v-for="n in 5"
                        :key="`mobile-skeleton-${n}`"
                        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex gap-3">
                            <div
                                class="h-10 w-10 shrink-0 animate-pulse rounded-full bg-slate-200 dark:bg-slate-800"
                            />

                            <div class="flex-1 space-y-2">
                                <div
                                    class="h-4 w-32 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                />

                                <div
                                    class="h-3 w-48 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                                />
                            </div>
                        </div>

                        <div
                            class="mt-4 h-4 w-3/4 animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                        />

                        <div
                            class="mt-2 h-12 w-full animate-pulse rounded bg-slate-200 dark:bg-slate-800"
                        />
                    </div>
                </template>

                <!-- Mobile Empty -->
                <div
                    v-else
                    class="rounded-2xl border border-slate-200 bg-white px-5 py-14 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800"
                    >
                        <Mail class="h-7 w-7" />
                    </div>

                    <h3
                        class="mt-4 text-sm font-semibold text-slate-900 dark:text-white"
                    >
                        Tidak ada pesan
                    </h3>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Belum ada pesan yang sesuai.
                    </p>
                </div>
            </section>

            <!-- Mobile Pagination -->
            <div
                v-if="!isLoading && props.pesans.last_page > 1"
                class="mt-4 flex items-center justify-between md:hidden"
            >
                <button
                    type="button"
                    class="inline-flex h-10 items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 disabled:opacity-40 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    :disabled="!props.pesans.prev_page_url || isLoading"
                    @click="goToPage(props.pesans.prev_page_url)"
                >
                    <ChevronLeft class="h-4 w-4" />

                    Sebelumnya
                </button>

                <span
                    class="text-sm font-medium text-slate-500 dark:text-slate-400"
                >
                    {{ props.pesans.current_page }}
                    /
                    {{ props.pesans.last_page }}
                </span>

                <button
                    type="button"
                    class="inline-flex h-10 items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 disabled:opacity-40 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    :disabled="!props.pesans.next_page_url || isLoading"
                    @click="goToPage(props.pesans.next_page_url)"
                >
                    Berikutnya

                    <ChevronRight class="h-4 w-4" />
                </button>
            </div>
        </main>

        <!-- =====================================================
             DETAIL MODAL
        ====================================================== -->

        <Transition name="modal">
            <div
                v-if="showDetailModal && selectedPesan"
                class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="detail-modal-title"
            >
                <button
                    type="button"
                    class="absolute inset-0 cursor-default bg-slate-950/60 backdrop-blur-sm"
                    aria-label="Tutup detail"
                    @click="closeDetail"
                />

                <div
                    ref="detailModalPanel"
                    class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Header -->
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <div
                                class="mb-1 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400"
                            >
                                <MessageCircleReply class="h-4 w-4" />

                                Detail Pesan
                            </div>

                            <h2
                                id="detail-modal-title"
                                class="truncate text-lg font-bold text-slate-900 dark:text-white"
                            >
                                {{ selectedPesan.subjek }}
                            </h2>
                        </div>

                        <button
                            ref="detailCloseButton"
                            type="button"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            aria-label="Tutup detail pesan"
                            @click="closeDetail"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div
                        class="min-h-0 flex-1 overflow-y-auto px-5 py-5 sm:px-6"
                    >
                        <!-- Sender -->
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/50"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-slate-600 shadow-sm dark:bg-slate-900 dark:text-slate-300"
                                >
                                    <User class="h-5 w-5" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ selectedPesan.nama }}
                                    </p>

                                    <a
                                        :href="`mailto:${selectedPesan.email}`"
                                        class="mt-0.5 block truncate text-sm text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        {{ selectedPesan.email }}
                                    </a>

                                    <p
                                        v-if="selectedPesan.telepon"
                                        class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{ selectedPesan.telepon }}
                                    </p>

                                    <p
                                        v-if="selectedPesan.perusahaan"
                                        class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{ selectedPesan.perusahaan }}
                                    </p>
                                </div>

                                <span
                                    class="shrink-0 rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    :class="statusClasses(selectedPesan.status)"
                                >
                                    {{ statusLabel(selectedPesan.status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Meta -->
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div
                                class="rounded-xl border border-slate-200 p-3 dark:border-slate-800"
                            >
                                <p
                                    class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Diterima
                                </p>

                                <p
                                    class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-200"
                                >
                                    {{ formatDate(selectedPesan.created_at) }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 p-3 dark:border-slate-800"
                            >
                                <p
                                    class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Dibaca
                                </p>

                                <p
                                    class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-200"
                                >
                                    {{ formatDate(selectedPesan.dibaca_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="mt-5">
                            <div class="mb-2 flex items-center gap-2">
                                <FileText class="h-4 w-4 text-slate-500" />

                                <h3
                                    class="text-sm font-semibold text-slate-900 dark:text-white"
                                >
                                    Isi Pesan
                                </h3>
                            </div>

                            <div
                                class="whitespace-pre-wrap rounded-2xl border border-slate-200 bg-white p-4 text-sm leading-7 text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300"
                            >
                                {{ selectedPesan.pesan }}
                            </div>
                        </div>

                        <!-- Existing Reply -->
                        <div v-if="hasReplied" class="mt-5">
                            <div
                                class="mb-2 flex items-center justify-between gap-3"
                            >
                                <div class="flex items-center gap-2">
                                    <CheckCircle2
                                        class="h-4 w-4 text-emerald-500"
                                    />

                                    <h3
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        Balasan Terkirim
                                    </h3>
                                </div>

                                <span
                                    v-if="selectedPesan.dibalas_at"
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ formatDate(selectedPesan.dibalas_at) }}
                                </span>
                            </div>

                            <div
                                class="whitespace-pre-wrap rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 text-sm leading-7 text-slate-700 dark:border-emerald-900/50 dark:bg-emerald-950/20 dark:text-slate-300"
                            >
                                {{ selectedPesan.balasan || "-" }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex flex-col-reverse gap-2 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-red-200 px-4 text-sm font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500/30 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/30"
                            @click="
                                openDelete(selectedPesan, $event.currentTarget)
                            "
                        >
                            <Trash2 class="h-4 w-4" />

                            Hapus
                        </button>

                        <div class="flex flex-col-reverse gap-2 sm:flex-row">
                            <button
                                type="button"
                                class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeDetail"
                            >
                                Tutup
                            </button>

                            <!-- Sudah dibalas: hanya bisa melihat balasan -->
                            <button
                                v-if="hasReplied"
                                type="button"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300 dark:hover:bg-emerald-950/50"
                                @click="openReplyModal($event.currentTarget)"
                            >
                                <Eye class="h-4 w-4" />

                                Lihat Balasan
                            </button>

                            <!-- Belum dibalas: bisa kirim balasan -->
                            <button
                                v-else
                                type="button"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="replying"
                                @click="openReplyModal($event.currentTarget)"
                            >
                                <Send class="h-4 w-4" />

                                Balas Pesan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- =====================================================
             REPLY MODAL
        ====================================================== -->

        <Transition name="modal">
            <div
                v-if="showReplyModal && selectedPesan"
                class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="reply-modal-title"
                aria-describedby="reply-modal-description"
            >
                <button
                    type="button"
                    class="absolute inset-0 cursor-default bg-slate-950/70 backdrop-blur-sm"
                    aria-label="Tutup modal balasan"
                    :disabled="replying"
                    @click="closeReply"
                />

                <div
                    ref="replyModalPanel"
                    class="relative flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Header -->
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex min-w-0 items-start gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                            >
                                <Send class="h-5 w-5" />
                            </div>

                            <div class="min-w-0">
                                <h2
                                    id="reply-modal-title"
                                    class="text-lg font-bold text-slate-900 dark:text-white"
                                >
                                    {{
                                        hasReplied
                                            ? "Balasan Pesan"
                                            : "Kirim Balasan"
                                    }}
                                </h2>

                                <p
                                    id="reply-modal-description"
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        hasReplied
                                            ? "Pesan ini sudah dibalas dan tidak dapat dibalas lagi."
                                            : "Tulis balasan yang akan dikirim ke email pengirim."
                                    }}
                                </p>
                            </div>
                        </div>

                        <button
                            ref="replyCloseButton"
                            type="button"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            aria-label="Tutup modal balasan"
                            :disabled="replying"
                            @click="closeReply"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div
                        class="min-h-0 flex-1 overflow-y-auto px-5 py-5 sm:px-6"
                    >
                        <!-- Recipient -->
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/50"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                            >
                                Penerima
                            </p>

                            <div class="mt-2 flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm dark:bg-slate-900 dark:text-slate-300"
                                >
                                    <User class="h-4 w-4" />
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{ selectedPesan.nama }}
                                    </p>

                                    <p
                                        class="truncate text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ selectedPesan.email }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="mt-4">
                            <label
                                for="reply-subject"
                                class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                            >
                                Subjek Email
                            </label>

                            <input
                                id="reply-subject"
                                type="text"
                                :value="replySubject"
                                readonly
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 text-sm text-slate-600 outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"
                            />
                        </div>

                        <!-- Sudah dibalas: tampilan read-only -->
                        <div v-if="hasReplied" class="mt-4">
                            <div
                                class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/20"
                            >
                                <div class="flex items-center gap-2">
                                    <CheckCircle2
                                        class="h-5 w-5 text-emerald-600 dark:text-emerald-400"
                                    />

                                    <p
                                        class="text-sm font-semibold text-emerald-800 dark:text-emerald-300"
                                    >
                                        Balasan sudah dikirim
                                    </p>
                                </div>

                                <p
                                    class="mt-3 whitespace-pre-wrap text-sm leading-7 text-slate-700 dark:text-slate-300"
                                >
                                    {{ selectedPesan.balasan || "-" }}
                                </p>

                                <p
                                    v-if="selectedPesan.dibalas_at"
                                    class="mt-3 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Dikirim pada
                                    {{ formatDate(selectedPesan.dibalas_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Belum dibalas: form balasan -->
                        <div v-else class="mt-4">
                            <div
                                class="mb-2 flex items-center justify-between gap-3"
                            >
                                <label
                                    for="reply-message"
                                    class="text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Pesan Balasan
                                </label>

                                <span
                                    class="text-xs tabular-nums"
                                    :class="
                                        replyCharacterCount > 5000
                                            ? 'text-red-600'
                                            : 'text-slate-400'
                                    "
                                >
                                    {{ replyCharacterCount }}/5000
                                </span>
                            </div>

                            <textarea
                                id="reply-message"
                                ref="replyTextarea"
                                v-model="replyMessage"
                                rows="9"
                                maxlength="5000"
                                :disabled="replying"
                                :aria-invalid="replyError ? 'true' : 'false'"
                                placeholder="Tulis balasan untuk pesan ini..."
                                class="w-full resize-y rounded-2xl border bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 disabled:cursor-not-allowed disabled:bg-slate-100 dark:bg-slate-950 dark:text-white dark:disabled:bg-slate-900"
                                :class="
                                    replyError
                                        ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10 dark:border-red-700'
                                        : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/10 dark:border-slate-700'
                                "
                            />

                            <!-- Error dari server -->
                            <div
                                v-if="replyError"
                                class="mt-2 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs leading-5 text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300"
                                role="alert"
                            >
                                <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />

                                <p>{{ replyError }}</p>
                            </div>

                            <div class="mt-2 flex items-start gap-2">
                                <AlertCircle
                                    class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
                                />

                                <p
                                    class="text-xs leading-5 text-slate-500 dark:text-slate-400"
                                >
                                    Balasan minimal 10 karakter dan maksimal
                                    5.000 karakter. Balasan hanya dapat dikirim
                                    satu kali dan akan dikirim ke

                                    <span
                                        class="font-medium text-slate-700 dark:text-slate-300"
                                    >
                                        {{ selectedPesan.email }}
                                    </span>
                                    .
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex flex-col-reverse gap-2 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-end dark:border-slate-800"
                    >
                        <button
                            type="button"
                            class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            :disabled="replying"
                            @click="closeReply"
                        >
                            Tutup
                        </button>

                        <button
                            v-if="!hasReplied"
                            type="button"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!canSubmitReply"
                            @click="submitReply"
                        >
                            <LoaderCircle
                                v-if="replying"
                                class="h-4 w-4 animate-spin"
                            />

                            <Send v-else class="h-4 w-4" />

                            {{ replying ? "Mengirim..." : "Kirim Balasan" }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- =====================================================
             DELETE MODAL
        ====================================================== -->

        <Transition name="modal">
            <div
                v-if="showDeleteModal && selectedPesan"
                class="fixed inset-0 z-[80] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="delete-modal-title"
            >
                <button
                    type="button"
                    class="absolute inset-0 cursor-default bg-slate-950/70 backdrop-blur-sm"
                    aria-label="Tutup konfirmasi hapus"
                    :disabled="deleting"
                    @click="closeDelete"
                />

                <div
                    ref="deleteModalPanel"
                    class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="p-6">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                        >
                            <Trash2 class="h-6 w-6" />
                        </div>

                        <h2
                            id="delete-modal-title"
                            class="mt-5 text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Hapus pesan?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400"
                        >
                            Pesan dari

                            <span
                                class="font-semibold text-slate-800 dark:text-slate-200"
                            >
                                {{ selectedPesan.nama }}
                            </span>

                            akan dihapus secara permanen. Tindakan ini tidak
                            dapat dibatalkan.
                        </p>

                        <div
                            class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/50"
                        >
                            <p
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >
                                {{ selectedPesan.subjek }}
                            </p>

                            <p
                                class="mt-1 line-clamp-2 text-sm text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedPesan.pesan }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50/70 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-800 dark:bg-slate-950/30"
                    >
                        <button
                            ref="deleteCancelButton"
                            type="button"
                            class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                            :disabled="deleting"
                            @click="closeDelete"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-red-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/30 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="deleting"
                            @click="confirmDelete"
                        >
                            <LoaderCircle
                                v-if="deleting"
                                class="h-4 w-4 animate-spin"
                            />

                            <Trash2 v-else class="h-4 w-4" />

                            {{ deleting ? "Menghapus..." : "Ya, Hapus Pesan" }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.reveal-section {
    opacity: 0;
    transform: translateY(14px);
    transition:
        opacity 0.55s ease,
        transform 0.55s ease;
}

.reveal-section.is-visible {
    opacity: 1;
    transform: translateY(0);
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}

.modal-enter-active > div:last-child,
.modal-leave-active > div:last-child {
    transition:
        transform 0.2s ease,
        opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div:last-child,
.modal-leave-to > div:last-child {
    opacity: 0;
    transform: translateY(8px) scale(0.985);
}

@media (prefers-reduced-motion: reduce) {
    .reveal-section,
    .modal-enter-active,
    .modal-leave-active,
    .modal-enter-active > div:last-child,
    .modal-leave-active > div:last-child {
        transition: none !important;
    }

    .reveal-section {
        opacity: 1;
        transform: none;
    }
}
</style>
