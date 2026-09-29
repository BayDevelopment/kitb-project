<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowDown,
    ArrowUp,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    Eye,
    FileText,
    Pencil,
    Plus,
    Target,
    Trash2,
    X,
} from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

/**
 * |--------------------------------------------------------------------------
 * | Interfaces
 * |--------------------------------------------------------------------------
 */

interface Visi {
    id: number;
    isi: string;
    created_at: string;
    updated_at: string;
}

interface Misi {
    id: number;
    visi_id: number;
    isi: string;
    urutan: number;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
}

interface MisiPagination {
    data: Misi[];
    links?: PaginationLink[];
    meta?: PaginationMeta;
    current_page?: number;
    from?: number | null;
    last_page?: number;
    per_page?: number;
    to?: number | null;
    total?: number;
}

const props = defineProps<{
    visi: Visi | null;
    misis: MisiPagination | null;
}>();

/**
 * |--------------------------------------------------------------------------
 * | Page Loading
 * |--------------------------------------------------------------------------
 */

const isPageLoading = ref(true);

let removeRouterStartListener: (() => void) | null = null;
let removeRouterFinishListener: (() => void) | null = null;

onMounted(() => {
    removeRouterStartListener = router.on("start", (event) => {
        if (!event.detail.visit.preserveState) {
            isPageLoading.value = true;
        }
    });

    removeRouterFinishListener = router.on("finish", () => {
        isPageLoading.value = false;
    });

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            isPageLoading.value = false;
        });
    });
});

onBeforeUnmount(() => {
    removeRouterStartListener?.();
    removeRouterFinishListener?.();
});

/**
 * |--------------------------------------------------------------------------
 * | Modal State
 * |--------------------------------------------------------------------------
 */

const showVisiModal = ref(false);
const showMisiModal = ref(false);
const showDetailModal = ref(false);
const showDeleteModal = ref(false);

const visiModalMode = ref<"create" | "edit">("create");
const misiModalMode = ref<"create" | "edit">("create");

const selectedVisi = ref<Visi | null>(null);
const selectedMisi = ref<Misi | null>(null);

/**
 * |--------------------------------------------------------------------------
 * | Form State
 * |--------------------------------------------------------------------------
 */

const visiForm = ref({
    isi: "",
});

const misiForm = ref({
    isi: "",
});

const processingVisi = ref(false);
const processingMisi = ref(false);
const processingDelete = ref(false);
const movingMisiId = ref<number | null>(null);

/**
 * |--------------------------------------------------------------------------
 * | Helpers
 * |--------------------------------------------------------------------------
 */

const truncate = (text: string | null, length = 180): string => {
    if (!text) {
        return "-";
    }

    return text.length > length ? `${text.substring(0, length)}...` : text;
};

const getMisiTotal = (): number => {
    const misis = props.misis;

    if (!misis) {
        return 0;
    }

    if (typeof misis.meta?.total === "number") {
        return misis.meta.total;
    }

    if (typeof misis.total === "number") {
        return misis.total;
    }

    return misis.data?.length ?? 0;
};

const getMisiCurrentPage = (): number => {
    const misis = props.misis;

    if (!misis) {
        return 1;
    }

    return misis.meta?.current_page ?? misis.current_page ?? 1;
};

const getMisiPerPage = (): number => {
    const misis = props.misis;

    if (!misis) {
        return 10;
    }

    return misis.meta?.per_page ?? misis.per_page ?? misis.data?.length ?? 10;
};

const getMisiLastPage = (): number => {
    const misis = props.misis;

    if (!misis) {
        return 1;
    }

    return misis.meta?.last_page ?? misis.last_page ?? 1;
};

const getMisiFrom = (): number => {
    return props.misis?.meta?.from ?? props.misis?.from ?? 0;
};

const getMisiTo = (): number => {
    return props.misis?.meta?.to ?? props.misis?.to ?? 0;
};

const getRowNumber = (index: number): number => {
    return (getMisiCurrentPage() - 1) * getMisiPerPage() + index + 1;
};

/**
 * |--------------------------------------------------------------------------
 * | Misi Ordering
 * |--------------------------------------------------------------------------
 */

const isFirstMisi = (misi: Misi): boolean => {
    return misi.urutan <= 1;
};

const isLastMisi = (misi: Misi): boolean => {
    const total = getMisiTotal();

    if (total <= 0) {
        return true;
    }

    return misi.urutan >= total;
};

/**
 * |--------------------------------------------------------------------------
 * | Pagination
 * |--------------------------------------------------------------------------
 */

const getPaginationLinks = (): PaginationLink[] => {
    return props.misis?.links ?? [];
};

const goToPage = (url: string | null): void => {
    if (!url) {
        return;
    }

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const paginationPageLabel = (label: string): string => {
    return label
        .replace(/&laquo;/g, "")
        .replace(/&raquo;/g, "")
        .replace(/Previous/gi, "")
        .replace(/Next/gi, "")
        .trim();
};

const firstPageUrl = (): string | null => {
    const links = getPaginationLinks();

    return links.length > 2 ? (links[1]?.url ?? null) : null;
};

const lastPageUrl = (): string | null => {
    const links = getPaginationLinks();

    return links.length > 2 ? (links[links.length - 2]?.url ?? null) : null;
};

const previousPageUrl = (): string | null => {
    return getPaginationLinks()[0]?.url ?? null;
};

const nextPageUrl = (): string | null => {
    const links = getPaginationLinks();

    return links.length > 0 ? (links[links.length - 1]?.url ?? null) : null;
};

/**
 * |--------------------------------------------------------------------------
 * | Visi Modal
 * |--------------------------------------------------------------------------
 */

const openCreateVisi = (): void => {
    visiModalMode.value = "create";
    selectedVisi.value = null;

    visiForm.value = {
        isi: "",
    };

    showMisiModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = false;
    showVisiModal.value = true;
};

const openEditVisi = (): void => {
    if (!props.visi) {
        return;
    }

    visiModalMode.value = "edit";
    selectedVisi.value = props.visi;

    visiForm.value = {
        isi: props.visi.isi ?? "",
    };

    showMisiModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = false;
    showVisiModal.value = true;
};

const closeVisiModal = (): void => {
    if (processingVisi.value) {
        return;
    }

    showVisiModal.value = false;
    selectedVisi.value = null;

    visiForm.value = {
        isi: "",
    };
};

const forceCloseVisiModal = (): void => {
    showVisiModal.value = false;
    selectedVisi.value = null;

    visiForm.value = {
        isi: "",
    };
};

/**
 * |--------------------------------------------------------------------------
 * | Submit Visi
 * |--------------------------------------------------------------------------
 */

const submitVisi = (): void => {
    if (processingVisi.value) {
        return;
    }

    const isi = visiForm.value.isi.trim();

    if (!isi) {
        return;
    }

    processingVisi.value = true;

    if (visiModalMode.value === "create") {
        router.post(
            "/profil-perusahaan/visi-misi/visi",
            {
                isi,
            },
            {
                preserveScroll: true,

                onSuccess: () => {
                    forceCloseVisiModal();
                },

                onError: (errors) => {
                    console.error("Gagal menambahkan visi:", errors);
                },

                onFinish: () => {
                    processingVisi.value = false;
                },
            },
        );

        return;
    }

    if (!props.visi) {
        processingVisi.value = false;
        return;
    }

    router.put(
        `/profil-perusahaan/visi-misi/visi/${props.visi.id}`,
        {
            isi,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                forceCloseVisiModal();
            },

            onError: (errors) => {
                console.error("Gagal memperbarui visi:", errors);
            },

            onFinish: () => {
                processingVisi.value = false;
            },
        },
    );
};

/**
 * |--------------------------------------------------------------------------
 * | Misi Modal
 * |--------------------------------------------------------------------------
 */

const openCreateMisi = (): void => {
    if (!props.visi) {
        return;
    }

    misiModalMode.value = "create";
    selectedMisi.value = null;

    misiForm.value = {
        isi: "",
    };

    showVisiModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = false;
    showMisiModal.value = true;
};

const openEditMisi = (misi: Misi): void => {
    selectedMisi.value = misi;
    misiModalMode.value = "edit";

    misiForm.value = {
        isi: misi.isi ?? "",
    };

    showVisiModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = false;
    showMisiModal.value = true;
};

const closeMisiModal = (): void => {
    if (processingMisi.value) {
        return;
    }

    showMisiModal.value = false;
    selectedMisi.value = null;

    misiForm.value = {
        isi: "",
    };
};

const forceCloseMisiModal = (): void => {
    showMisiModal.value = false;
    selectedMisi.value = null;

    misiForm.value = {
        isi: "",
    };
};

/**
 * |--------------------------------------------------------------------------
 * | Submit Misi
 * |--------------------------------------------------------------------------
 */

const submitMisi = (): void => {
    if (processingMisi.value || !props.visi) {
        return;
    }

    const isi = misiForm.value.isi.trim();

    if (!isi) {
        return;
    }

    processingMisi.value = true;

    if (misiModalMode.value === "create") {
        router.post(
            `/profil-perusahaan/visi-misi/${props.visi.id}/misi`,
            {
                isi,
            },
            {
                preserveScroll: true,

                onSuccess: () => {
                    forceCloseMisiModal();
                },

                onError: (errors) => {
                    console.error("Gagal menambahkan misi:", errors);
                },

                onFinish: () => {
                    processingMisi.value = false;
                },
            },
        );

        return;
    }

    if (!selectedMisi.value) {
        processingMisi.value = false;
        return;
    }

    router.put(
        `/profil-perusahaan/visi-misi/${props.visi.id}/misi/${selectedMisi.value.id}`,
        {
            isi,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                forceCloseMisiModal();
            },

            onError: (errors) => {
                console.error("Gagal memperbarui misi:", errors);
            },

            onFinish: () => {
                processingMisi.value = false;
            },
        },
    );
};

/**
 * |--------------------------------------------------------------------------
 * | Detail Misi
 * |--------------------------------------------------------------------------
 */

const openDetail = (misi: Misi): void => {
    selectedMisi.value = misi;

    showVisiModal.value = false;
    showMisiModal.value = false;
    showDeleteModal.value = false;
    showDetailModal.value = true;
};

const closeDetail = (): void => {
    showDetailModal.value = false;
    selectedMisi.value = null;
};

/**
 * |--------------------------------------------------------------------------
 * | Delete Misi
 * |--------------------------------------------------------------------------
 */

const openDelete = (misi: Misi): void => {
    selectedMisi.value = misi;

    showVisiModal.value = false;
    showMisiModal.value = false;
    showDetailModal.value = false;
    showDeleteModal.value = true;
};

const closeDelete = (): void => {
    if (processingDelete.value) {
        return;
    }

    showDeleteModal.value = false;
    selectedMisi.value = null;
};

const deleteMisi = (): void => {
    if (!props.visi || !selectedMisi.value || processingDelete.value) {
        return;
    }

    processingDelete.value = true;

    router.delete(
        `/profil-perusahaan/visi-misi/${props.visi.id}/misi/${selectedMisi.value.id}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                showDeleteModal.value = false;
                selectedMisi.value = null;
            },

            onError: (errors) => {
                console.error("Gagal menghapus misi:", errors);
            },

            onFinish: () => {
                processingDelete.value = false;
            },
        },
    );
};

/**
 * |--------------------------------------------------------------------------
 * | Move Misi
 * |--------------------------------------------------------------------------
 */

const moveMisi = (misi: Misi, direction: "up" | "down"): void => {
    if (
        !props.visi ||
        movingMisiId.value !== null ||
        (isFirstMisi(misi) && direction === "up") ||
        (isLastMisi(misi) && direction === "down")
    ) {
        return;
    }

    movingMisiId.value = misi.id;

    router.patch(
        `/profil-perusahaan/visi-misi/${props.visi.id}/misi/${misi.id}/move`,
        {
            direction,
        },
        {
            preserveScroll: true,

            onError: (errors) => {
                console.error("Gagal mengubah urutan misi:", errors);
            },

            onFinish: () => {
                movingMisiId.value = null;
            },
        },
    );
};
</script>

<template>
    <div
        class="relative min-h-full overflow-hidden bg-slate-50 transition-colors duration-300 dark:bg-[#07111f]"
        :aria-busy="isPageLoading ? 'true' : 'false'"
    >
        <!-- Decorative Background -->
        <div
            class="pointer-events-none absolute inset-0 z-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="blob-shape absolute -left-24 -top-32 h-96 w-96 rounded-full bg-gradient-to-br from-blue-400/25 via-indigo-400/15 to-transparent blur-3xl dark:from-blue-500/20 dark:via-indigo-500/10 dark:to-transparent"
            ></div>

            <div
                class="blob-shape-delayed absolute -right-20 top-0 h-80 w-80 rounded-full bg-gradient-to-tr from-sky-300/25 via-blue-400/15 to-transparent blur-3xl dark:from-sky-500/15 dark:via-blue-500/10 dark:to-transparent"
            ></div>

            <div
                class="blob-shape-slow absolute left-[30%] -top-40 h-72 w-72 rounded-full bg-gradient-to-br from-indigo-300/20 via-blue-300/10 to-transparent blur-3xl dark:from-indigo-500/10 dark:via-blue-500/5 dark:to-transparent"
            ></div>

            <div
                class="blob-shape absolute -bottom-40 right-[20%] h-72 w-72 rounded-full bg-gradient-to-br from-cyan-300/15 via-blue-300/10 to-transparent blur-3xl dark:from-cyan-500/10 dark:via-blue-500/5 dark:to-transparent"
            ></div>

            <div class="absolute inset-0 opacity-[0.32] dark:opacity-[0.12]">
                <div
                    class="h-full w-full bg-[linear-gradient(to_right,#64748b12_1px,transparent_1px),linear-gradient(to_bottom,#64748b12_1px,transparent_1px)] bg-[size:32px_32px]"
                ></div>
            </div>

            <div
                class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-b from-transparent via-slate-50/50 to-slate-50 dark:via-slate-950/30 dark:to-[#07111f]"
            ></div>
        </div>

        <!-- Main Content -->
        <main
            class="relative z-10 mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6 xl:p-8"
        >
            <!-- Page Skeleton -->
            <div v-if="isPageLoading" class="animate-pulse">
                <!-- Header Skeleton -->
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="size-11 shrink-0 rounded-2xl bg-slate-200 dark:bg-slate-800"
                        ></div>

                        <div class="space-y-2">
                            <div
                                class="h-5 w-32 rounded-md bg-slate-200 dark:bg-slate-800"
                            ></div>

                            <div
                                class="h-4 w-56 max-w-full rounded-md bg-slate-200 dark:bg-slate-800"
                            ></div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <div
                            class="h-11 w-full rounded-xl bg-slate-200 dark:bg-slate-800 sm:w-28"
                        ></div>

                        <div
                            class="h-11 w-full rounded-xl bg-slate-200 dark:bg-slate-800 sm:w-32"
                        ></div>
                    </div>
                </div>

                <!-- Visi Skeleton -->
                <div
                    class="mb-6 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="size-10 rounded-xl bg-slate-200 dark:bg-slate-800"
                            ></div>

                            <div class="space-y-2">
                                <div
                                    class="h-4 w-16 rounded bg-slate-200 dark:bg-slate-800"
                                ></div>

                                <div
                                    class="h-3 w-44 rounded bg-slate-200 dark:bg-slate-800"
                                ></div>
                            </div>
                        </div>

                        <div
                            class="size-9 rounded-xl bg-slate-200 dark:bg-slate-800"
                        ></div>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <div class="flex gap-4">
                                <div
                                    class="hidden size-10 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-700 sm:block"
                                ></div>

                                <div class="w-full space-y-3">
                                    <div
                                        class="h-4 w-full rounded bg-slate-200 dark:bg-slate-700"
                                    ></div>

                                    <div
                                        class="h-4 w-[92%] rounded bg-slate-200 dark:bg-slate-700"
                                    ></div>

                                    <div
                                        class="h-4 w-[76%] rounded bg-slate-200 dark:bg-slate-700"
                                    ></div>

                                    <div
                                        class="h-4 w-[55%] rounded bg-slate-200 dark:bg-slate-700"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Misi Skeleton -->
                <div
                    class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="size-10 rounded-xl bg-slate-200 dark:bg-slate-800"
                            ></div>

                            <div class="space-y-2">
                                <div
                                    class="h-4 w-16 rounded bg-slate-200 dark:bg-slate-800"
                                ></div>

                                <div
                                    class="h-3 w-52 rounded bg-slate-200 dark:bg-slate-800"
                                ></div>
                            </div>
                        </div>

                        <div
                            class="h-11 w-full rounded-xl bg-slate-200 dark:bg-slate-800 sm:w-32"
                        ></div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead
                                class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                            >
                                <tr>
                                    <th class="w-20 px-6 py-4">
                                        <div
                                            class="mx-auto h-4 w-8 rounded bg-slate-200 dark:bg-slate-700"
                                        ></div>
                                    </th>

                                    <th class="px-6 py-4">
                                        <div
                                            class="h-4 w-16 rounded bg-slate-200 dark:bg-slate-700"
                                        ></div>
                                    </th>

                                    <th class="w-36 px-6 py-4">
                                        <div
                                            class="mx-auto h-4 w-14 rounded bg-slate-200 dark:bg-slate-700"
                                        ></div>
                                    </th>

                                    <th class="w-48 px-6 py-4">
                                        <div
                                            class="ml-auto h-4 w-16 rounded bg-slate-200 dark:bg-slate-700"
                                        ></div>
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <tr v-for="row in 5" :key="row">
                                    <td class="px-6 py-5">
                                        <div
                                            class="mx-auto size-8 rounded-lg bg-slate-200 dark:bg-slate-800"
                                        ></div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="space-y-2">
                                            <div
                                                class="h-4 w-full rounded bg-slate-200 dark:bg-slate-800"
                                            ></div>

                                            <div
                                                class="h-4 w-[75%] rounded bg-slate-200 dark:bg-slate-800"
                                            ></div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div
                                            class="mx-auto h-9 w-24 rounded-lg bg-slate-200 dark:bg-slate-800"
                                        ></div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="flex justify-end gap-2">
                                            <div
                                                class="size-8 rounded-lg bg-slate-200 dark:bg-slate-800"
                                            ></div>

                                            <div
                                                class="size-8 rounded-lg bg-slate-200 dark:bg-slate-800"
                                            ></div>

                                            <div
                                                class="size-8 rounded-lg bg-slate-200 dark:bg-slate-800"
                                            ></div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Skeleton -->
                    <div
                        class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                    >
                        <div
                            class="h-4 w-48 rounded bg-slate-200 dark:bg-slate-800"
                        ></div>

                        <div class="flex gap-1">
                            <div
                                v-for="item in 5"
                                :key="item"
                                class="size-9 rounded-lg bg-slate-200 dark:bg-slate-800"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actual Content -->
            <template v-else>
                <!-- Header -->
                <div
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-start gap-3 sm:items-center">
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-sm shadow-blue-600/20"
                        >
                            <Target class="size-5" />
                        </div>

                        <div>
                            <h1
                                class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                            >
                                Visi & Misi
                            </h1>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Kelola visi dan misi perusahaan.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <button
                            v-if="props.visi"
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            @click="openEditVisi"
                        >
                            <Pencil class="size-4" />
                            Edit Visi
                        </button>

                        <button
                            v-if="props.visi"
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                            @click="openCreateMisi"
                        >
                            <Plus class="size-4" />
                            Tambah Misi
                        </button>

                        <button
                            v-else
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                            @click="openCreateVisi"
                        >
                            <Plus class="size-4" />
                            Tambah Visi
                        </button>
                    </div>
                </div>

                <!-- Visi Card -->
                <section
                    class="mb-6 overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm backdrop-blur-xl transition-shadow duration-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900/95"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                            >
                                <Target class="size-5" />
                            </div>

                            <div>
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Visi
                                </h2>

                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Pernyataan visi perusahaan.
                                </p>
                            </div>
                        </div>

                        <button
                            v-if="props.visi"
                            type="button"
                            class="rounded-xl p-2 text-slate-500 transition-all duration-200 hover:bg-amber-50 hover:text-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                            title="Edit visi"
                            @click="openEditVisi"
                        >
                            <Pencil class="size-4" />
                        </button>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div
                            v-if="props.visi"
                            class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50/80 via-indigo-50/40 to-white p-5 shadow-sm dark:border-blue-900/40 dark:from-blue-950/30 dark:via-indigo-950/20 dark:to-slate-900"
                        >
                            <div class="flex gap-4">
                                <div
                                    class="hidden size-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm sm:flex"
                                >
                                    <Target class="size-5" />
                                </div>

                                <p
                                    class="whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-200"
                                >
                                    {{ props.visi.isi }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="py-10 text-center">
                            <div
                                class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"
                            >
                                <Target
                                    class="size-6 text-slate-400 dark:text-slate-600"
                                />
                            </div>

                            <p
                                class="font-medium text-slate-700 dark:text-slate-300"
                            >
                                Visi belum tersedia
                            </p>

                            <p
                                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Tambahkan visi perusahaan terlebih dahulu.
                            </p>

                            <button
                                type="button"
                                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md"
                                @click="openCreateVisi"
                            >
                                <Plus class="size-4" />
                                Tambah Visi
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Misi Card -->
                <section
                    class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm backdrop-blur-xl transition-shadow duration-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900/95"
                >
                    <div
                        class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
                            >
                                <FileText class="size-5" />
                            </div>

                            <div>
                                <h2
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Misi
                                </h2>

                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Daftar misi perusahaan berdasarkan urutan.
                                </p>
                            </div>
                        </div>

                        <button
                            v-if="props.visi"
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md"
                            @click="openCreateMisi"
                        >
                            <Plus class="size-4" />
                            Tambah Misi
                        </button>
                    </div>

                    <!-- Table -->
                    <div
                        v-if="
                            props.visi && (props.misis?.data?.length ?? 0) > 0
                        "
                    >
                        <div class="overflow-x-auto">
                            <table
                                class="w-full min-w-[760px] text-left text-sm"
                            >
                                <thead
                                    class="border-b border-slate-200 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/50"
                                >
                                    <tr>
                                        <th
                                            class="w-20 px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            No.
                                        </th>

                                        <th
                                            class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Misi
                                        </th>

                                        <th
                                            class="w-36 px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Urutan
                                        </th>

                                        <th
                                            class="w-48 px-6 py-4 text-right font-semibold text-slate-700 dark:text-slate-200"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800"
                                >
                                    <tr
                                        v-for="(misi, index) in props.misis
                                            ?.data ?? []"
                                        :key="misi.id"
                                        class="transition-colors duration-200 hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                                    >
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex size-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                            >
                                                {{ getRowNumber(index) }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4">
                                            <p
                                                class="whitespace-pre-line leading-6 text-slate-600 dark:text-slate-300"
                                            >
                                                {{ truncate(misi.isi) }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center justify-center gap-1"
                                            >
                                                <button
                                                    type="button"
                                                    title="Naik"
                                                    :disabled="
                                                        isFirstMisi(misi) ||
                                                        movingMisiId === misi.id
                                                    "
                                                    class="rounded-lg p-2 transition-all duration-200"
                                                    :class="
                                                        isFirstMisi(misi) ||
                                                        movingMisiId === misi.id
                                                            ? 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                                            : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                                    "
                                                    @click="
                                                        moveMisi(misi, 'up')
                                                    "
                                                >
                                                    <ArrowUp class="size-4" />
                                                </button>

                                                <span
                                                    class="min-w-8 text-center text-sm font-semibold text-slate-700 dark:text-slate-200"
                                                >
                                                    {{ misi.urutan }}
                                                </span>

                                                <button
                                                    type="button"
                                                    title="Turun"
                                                    :disabled="
                                                        isLastMisi(misi) ||
                                                        movingMisiId === misi.id
                                                    "
                                                    class="rounded-lg p-2 transition-all duration-200"
                                                    :class="
                                                        isLastMisi(misi) ||
                                                        movingMisiId === misi.id
                                                            ? 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                                            : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                                    "
                                                    @click="
                                                        moveMisi(misi, 'down')
                                                    "
                                                >
                                                    <ArrowDown class="size-4" />
                                                </button>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-1">
                                                <button
                                                    type="button"
                                                    title="Lihat detail"
                                                    class="rounded-lg p-2 text-slate-500 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                                    @click="openDetail(misi)"
                                                >
                                                    <Eye class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Edit misi"
                                                    class="rounded-lg p-2 text-slate-500 transition-all duration-200 hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                                    @click="openEditMisi(misi)"
                                                >
                                                    <Pencil class="size-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    title="Hapus misi"
                                                    class="rounded-lg p-2 text-slate-500 transition-all duration-200 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                                    @click="openDelete(misi)"
                                                >
                                                    <Trash2 class="size-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="props.misis && getMisiLastPage() > 1"
                            class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800"
                        >
                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Menampilkan
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ getMisiFrom() }}
                                </span>
                                -
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ getMisiTo() }}
                                </span>
                                dari
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200"
                                >
                                    {{ getMisiTotal() }}
                                </span>
                                misi
                            </p>

                            <div class="flex flex-wrap items-center gap-1">
                                <button
                                    type="button"
                                    title="Halaman pertama"
                                    :disabled="!firstPageUrl()"
                                    class="rounded-lg p-2 transition-all duration-200"
                                    :class="
                                        firstPageUrl()
                                            ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                            : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                    "
                                    @click="goToPage(firstPageUrl())"
                                >
                                    <ChevronsLeft class="size-4" />
                                </button>

                                <button
                                    type="button"
                                    title="Halaman sebelumnya"
                                    :disabled="!previousPageUrl()"
                                    class="rounded-lg p-2 transition-all duration-200"
                                    :class="
                                        previousPageUrl()
                                            ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                            : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                    "
                                    @click="goToPage(previousPageUrl())"
                                >
                                    <ChevronLeft class="size-4" />
                                </button>

                                <button
                                    v-for="(
                                        link, index
                                    ) in getPaginationLinks().slice(1, -1)"
                                    :key="`${link.label}-${index}`"
                                    type="button"
                                    :disabled="!link.url"
                                    class="min-w-9 rounded-lg px-3 py-2 text-sm transition-all duration-200"
                                    :class="
                                        link.active
                                            ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20'
                                            : link.url
                                              ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                              : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                    "
                                    @click="goToPage(link.url)"
                                >
                                    {{ paginationPageLabel(link.label) || "…" }}
                                </button>

                                <button
                                    type="button"
                                    title="Halaman berikutnya"
                                    :disabled="!nextPageUrl()"
                                    class="rounded-lg p-2 transition-all duration-200"
                                    :class="
                                        nextPageUrl()
                                            ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                            : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                    "
                                    @click="goToPage(nextPageUrl())"
                                >
                                    <ChevronRight class="size-4" />
                                </button>

                                <button
                                    type="button"
                                    title="Halaman terakhir"
                                    :disabled="!lastPageUrl()"
                                    class="rounded-lg p-2 transition-all duration-200"
                                    :class="
                                        lastPageUrl()
                                            ? 'text-slate-600 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-300 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                            : 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                    "
                                    @click="goToPage(lastPageUrl())"
                                >
                                    <ChevronsRight class="size-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-else class="px-6 py-14 text-center">
                        <div
                            class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"
                        >
                            <FileText
                                class="size-6 text-slate-400 dark:text-slate-600"
                            />
                        </div>

                        <p
                            class="font-medium text-slate-700 dark:text-slate-300"
                        >
                            {{
                                props.visi
                                    ? "Belum ada misi"
                                    : "Visi belum tersedia"
                            }}
                        </p>

                        <p
                            class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                        >
                            {{
                                props.visi
                                    ? "Tambahkan misi perusahaan untuk mulai mengisi daftar misi."
                                    : "Tambahkan visi terlebih dahulu sebelum membuat misi."
                            }}
                        </p>

                        <button
                            v-if="props.visi"
                            type="button"
                            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md"
                            @click="openCreateMisi"
                        >
                            <Plus class="size-4" />
                            Tambah Misi
                        </button>
                    </div>
                </section>
            </template>
        </main>

        <!-- Modal Visi -->
        <Transition name="modal">
            <div
                v-if="showVisiModal"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeVisiModal"
            >
                <div
                    class="my-auto w-full max-w-2xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    visiModalMode === "create"
                                        ? "Tambah Visi"
                                        : "Edit Visi"
                                }}
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Kelola pernyataan visi perusahaan.
                            </p>
                        </div>

                        <button
                            type="button"
                            :disabled="processingVisi"
                            class="ml-4 shrink-0 rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800"
                            @click="closeVisiModal"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <form class="p-5 sm:p-6" @submit.prevent="submitVisi">
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Pernyataan Visi
                                <span class="text-red-500">*</span>
                            </label>

                            <textarea
                                v-model="visiForm.isi"
                                rows="7"
                                required
                                maxlength="5000"
                                placeholder="Tuliskan visi perusahaan..."
                                class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-blue-500 dark:focus:bg-slate-900"
                            ></textarea>

                            <p
                                class="mt-1.5 text-xs text-slate-400 dark:text-slate-500"
                            >
                                Gunakan kalimat yang jelas, singkat, dan
                                menggambarkan arah perusahaan.
                            </p>
                        </div>

                        <div
                            class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800"
                        >
                            <button
                                type="button"
                                :disabled="processingVisi"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeVisiModal"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="
                                    processingVisi || !visiForm.isi.trim()
                                "
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span
                                    v-if="processingVisi"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                {{
                                    processingVisi
                                        ? "Menyimpan..."
                                        : visiModalMode === "create"
                                          ? "Simpan Visi"
                                          : "Simpan Perubahan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Modal Misi -->
        <Transition name="modal">
            <div
                v-if="showMisiModal"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeMisiModal"
            >
                <div
                    class="my-auto w-full max-w-2xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div class="min-w-0">
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                {{
                                    misiModalMode === "create"
                                        ? "Tambah Misi"
                                        : "Edit Misi"
                                }}
                            </h2>

                            <p
                                class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Kelola pernyataan misi perusahaan.
                            </p>
                        </div>

                        <button
                            type="button"
                            :disabled="processingMisi"
                            class="ml-4 shrink-0 rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800"
                            @click="closeMisiModal"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <form class="p-5 sm:p-6" @submit.prevent="submitMisi">
                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Pernyataan Misi
                                <span class="text-red-500">*</span>
                            </label>

                            <textarea
                                v-model="misiForm.isi"
                                rows="7"
                                required
                                maxlength="5000"
                                placeholder="Tuliskan misi perusahaan..."
                                class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-blue-500 dark:focus:bg-slate-900"
                            ></textarea>

                            <p
                                class="mt-1.5 text-xs text-slate-400 dark:text-slate-500"
                            >
                                Tuliskan tindakan atau komitmen utama perusahaan
                                untuk mewujudkan visi.
                            </p>
                        </div>

                        <div
                            class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800"
                        >
                            <button
                                type="button"
                                :disabled="processingMisi"
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="closeMisiModal"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                :disabled="
                                    processingMisi || !misiForm.isi.trim()
                                "
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span
                                    v-if="processingMisi"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                {{
                                    processingMisi
                                        ? "Menyimpan..."
                                        : misiModalMode === "create"
                                          ? "Simpan Misi"
                                          : "Simpan Perubahan"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Modal Detail Misi -->
        <Transition name="modal">
            <div
                v-if="showDetailModal && selectedMisi"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeDetail"
            >
                <div
                    class="my-auto w-full max-w-2xl overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-slate-800"
                    >
                        <div>
                            <h2
                                class="text-lg font-semibold text-slate-900 dark:text-white"
                            >
                                Detail Misi
                            </h2>

                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                Informasi lengkap misi perusahaan.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                            @click="closeDetail"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div
                            class="mb-5 flex items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50/60 p-4 dark:border-blue-900/40 dark:bg-blue-950/20"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"
                            >
                                <FileText class="size-5" />
                            </div>

                            <div>
                                <p
                                    class="text-xs font-medium uppercase tracking-wide text-blue-600 dark:text-blue-400"
                                >
                                    Misi ke-{{ selectedMisi.urutan }}
                                </p>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Pernyataan misi perusahaan
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-800/50"
                        >
                            <p
                                class="whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-200"
                            >
                                {{ selectedMisi.isi }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Modal Delete -->
        <Transition name="modal">
            <div
                v-if="showDeleteModal && selectedMisi"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
                @click.self="closeDelete"
            >
                <div
                    class="w-full max-w-md rounded-3xl border border-white/10 bg-white p-6 shadow-2xl dark:bg-slate-900"
                >
                    <div
                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                    >
                        <Trash2 class="size-5" />
                    </div>

                    <div class="mt-4 text-center">
                        <h2
                            class="text-lg font-semibold text-slate-900 dark:text-white"
                        >
                            Hapus Misi?
                        </h2>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                        >
                            Yakin ingin menghapus misi ke-{{
                                selectedMisi.urutan
                            }}? Data yang sudah dihapus tidak dapat
                            dikembalikan.
                        </p>
                    </div>

                    <div
                        class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                    >
                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closeDelete"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="processingDelete"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm shadow-red-600/20 transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="deleteMisi"
                        >
                            <span
                                v-if="processingDelete"
                                class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            ></span>

                            {{
                                processingDelete ? "Menghapus..." : "Ya, Hapus"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Page Loading Bar -->
        <Transition name="loading">
            <div
                v-if="isPageLoading"
                class="pointer-events-none fixed inset-0 z-[100] bg-white/30 backdrop-blur-[1px] dark:bg-slate-950/30"
            >
                <div
                    class="absolute left-0 top-0 h-0.5 w-full overflow-hidden bg-blue-100 dark:bg-blue-950"
                >
                    <div
                        class="h-full w-1/3 animate-loading-bar rounded-full bg-blue-600"
                    ></div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Modal Transition */
.modal-enter-active,
.modal-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from > div,
.modal-leave-to > div {
    transform: translateY(8px) scale(0.985);
}

/* Loading Transition */
.loading-enter-active,
.loading-leave-active {
    transition: opacity 0.15s ease;
}

.loading-enter-from,
.loading-leave-to {
    opacity: 0;
}

/* Decorative Blobs */
.blob-shape {
    animation: blob-float 12s ease-in-out infinite;
    transform-origin: center;
    will-change: transform;
}

.blob-shape-delayed {
    animation: blob-float-delayed 15s ease-in-out infinite;
    transform-origin: center;
    will-change: transform;
}

.blob-shape-slow {
    animation: blob-float-slow 18s ease-in-out infinite;
    transform-origin: center;
    will-change: transform;
}

/* Blob Animations */
@keyframes blob-float {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    33% {
        transform: translate3d(25px, 15px, 0) scale(1.05);
    }

    66% {
        transform: translate3d(-15px, 30px, 0) scale(0.96);
    }
}

@keyframes blob-float-delayed {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    40% {
        transform: translate3d(-30px, 20px, 0) scale(1.08);
    }

    75% {
        transform: translate3d(15px, -15px, 0) scale(0.95);
    }
}

@keyframes blob-float-slow {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }

    50% {
        transform: translate3d(0, 35px, 0) scale(1.1);
    }
}

/* Loading Bar */
@keyframes loading-bar {
    0% {
        transform: translateX(-100%);
    }

    100% {
        transform: translateX(400%);
    }
}

.animate-loading-bar {
    animation: loading-bar 1.1s ease-in-out infinite;
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .blob-shape,
    .blob-shape-delayed,
    .blob-shape-slow,
    .modal-enter-active,
    .modal-leave-active,
    .loading-enter-active,
    .loading-leave-active,
    .animate-loading-bar {
        animation: none;
        transition: none;
    }
}

/* Mobile */
@media (max-width: 640px) {
    .blob-shape {
        left: -10rem;
        top: -8rem;
        width: 20rem;
        height: 20rem;
    }

    .blob-shape-delayed {
        right: -8rem;
        width: 17rem;
        height: 17rem;
    }

    .blob-shape-slow {
        left: 35%;
        width: 15rem;
        height: 15rem;
    }
}
</style>
