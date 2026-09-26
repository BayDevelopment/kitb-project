<script setup lang="ts">
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowDown,
    ArrowUp,
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

interface Misi {
    id: number;
    visi_id: number;
    isi: string;
    urutan: number;
    created_at: string;
    updated_at: string;
}

interface Visi {
    id: number;
    isi: string;
    misis: Misi[];
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    visi: Visi | null;
}>();

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const sortedMisis = computed(() => {
    return [...(props.visi?.misis ?? [])].sort((a, b) => {
        if (a.urutan !== b.urutan) {
            return a.urutan - b.urutan;
        }

        return a.id - b.id;
    });
});

/*
|--------------------------------------------------------------------------
| Modal
|--------------------------------------------------------------------------
*/

const showVisiModal = ref(false);
const showMisiModal = ref(false);
const showDetail = ref(false);
const showDelete = ref(false);

const visiModalMode = ref<"create" | "edit">("create");
const misiModalMode = ref<"create" | "edit">("create");

/*
|--------------------------------------------------------------------------
| Selected Data
|--------------------------------------------------------------------------
*/

const selectedMisi = ref<Misi | null>(null);

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const emptyVisiForm = () => ({
    isi: "",
});

const emptyMisiForm = () => ({
    isi: "",
});

const visiForm = ref(emptyVisiForm());
const misiForm = ref(emptyMisiForm());

/*
|--------------------------------------------------------------------------
| Processing State
|--------------------------------------------------------------------------
*/

const processingVisi = ref(false);
const processingMisi = ref(false);
const processingDelete = ref(false);
const movingMisiId = ref<number | null>(null);

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

const truncate = (text: string | null, length = 180) => {
    if (!text) {
        return "-";
    }

    return text.length > length
        ? `${text.substring(0, length)}...`
        : text;
};

/*
|--------------------------------------------------------------------------
| Visi
|--------------------------------------------------------------------------
*/

const openCreateVisi = () => {
    visiForm.value = emptyVisiForm();
    visiModalMode.value = "create";
    showVisiModal.value = true;
};

const openEditVisi = () => {
    if (!props.visi) {
        return;
    }

    visiForm.value = {
        isi: props.visi.isi,
    };

    visiModalMode.value = "edit";
    showVisiModal.value = true;
};

const closeVisiModal = () => {
    if (processingVisi.value) {
        return;
    }

    showVisiModal.value = false;
    visiForm.value = emptyVisiForm();
};

const submitVisi = () => {
    const isi = visiForm.value.isi.trim();

    if (!isi || processingVisi.value) {
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
                    closeVisiModal();
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
                closeVisiModal();
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

/*
|--------------------------------------------------------------------------
| Misi
|--------------------------------------------------------------------------
*/

const openCreateMisi = () => {
    if (!props.visi) {
        openCreateVisi();
        return;
    }

    misiForm.value = emptyMisiForm();
    selectedMisi.value = null;
    misiModalMode.value = "create";
    showMisiModal.value = true;
};

const openEditMisi = (misi: Misi) => {
    selectedMisi.value = misi;

    misiForm.value = {
        isi: misi.isi,
    };

    misiModalMode.value = "edit";
    showMisiModal.value = true;
};

const closeMisiModal = () => {
    if (processingMisi.value) {
        return;
    }

    showMisiModal.value = false;
    selectedMisi.value = null;
    misiForm.value = emptyMisiForm();
};

const submitMisi = () => {
    if (!props.visi) {
        return;
    }

    const isi = misiForm.value.isi.trim();

    if (!isi || processingMisi.value) {
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
                    closeMisiModal();
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
                closeMisiModal();
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

/*
|--------------------------------------------------------------------------
| Detail Misi
|--------------------------------------------------------------------------
*/

const openDetail = (misi: Misi) => {
    selectedMisi.value = misi;
    showDetail.value = true;
};

const closeDetail = () => {
    showDetail.value = false;
    selectedMisi.value = null;
};

/*
|--------------------------------------------------------------------------
| Delete Misi
|--------------------------------------------------------------------------
*/

const openDelete = (misi: Misi) => {
    selectedMisi.value = misi;
    showDelete.value = true;
};

const closeDelete = () => {
    if (processingDelete.value) {
        return;
    }

    showDelete.value = false;
    selectedMisi.value = null;
};

const deleteMisi = () => {
    if (
        !props.visi ||
        !selectedMisi.value ||
        processingDelete.value
    ) {
        return;
    }

    processingDelete.value = true;

    router.delete(
        `/profil-perusahaan/visi-misi/${props.visi.id}/misi/${selectedMisi.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                closeDelete();
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

/*
|--------------------------------------------------------------------------
| Reorder Misi
|--------------------------------------------------------------------------
*/

const moveMisi = (
    misi: Misi,
    direction: "up" | "down",
) => {
    if (!props.visi || movingMisiId.value !== null) {
        return;
    }

    const index = sortedMisis.value.findIndex(
        (item) => item.id === misi.id,
    );

    if (index === -1) {
        return;
    }

    const targetIndex =
        direction === "up"
            ? index - 1
            : index + 1;

    if (
        targetIndex < 0 ||
        targetIndex >= sortedMisis.value.length
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
                console.error(
                    "Gagal mengubah urutan misi:",
                    errors,
                );
            },
            onFinish: () => {
                movingMisiId.value = null;
            },
        },
    );
};

const isFirstMisi = (index: number) => {
    return index === 0;
};

const isLastMisi = (index: number) => {
    return index === sortedMisis.value.length - 1;
};
</script>

<template>
    <div
        class="min-h-full bg-slate-50/50 p-6 dark:bg-slate-950/50"
    >
        <!-- ========================================================= -->
        <!-- HEADER -->
        <!-- ========================================================= -->

        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-xl bg-blue-600/10 text-blue-600 dark:bg-blue-400/10 dark:text-blue-400"
                >
                    <Target class="size-5" />
                </div>

                <div>
                    <h1
                        class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white"
                    >
                        Visi & Misi
                    </h1>

                    <p
                        class="text-sm text-slate-500 dark:text-slate-400"
                    >
                        Kelola visi dan misi perusahaan.
                    </p>
                </div>
            </div>

            <button
                v-if="!props.visi"
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                @click="openCreateVisi"
            >
                <Plus class="size-4" />
                Tambah Visi
            </button>
        </div>

        <!-- ========================================================= -->
        <!-- CONTENT -->
        <!-- ========================================================= -->

        <div class="space-y-5">
            <!-- ===================================================== -->
            <!-- VISI -->
            <!-- ===================================================== -->

            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- HEADER CARD -->
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                        >
                            <Target class="size-4" />
                        </div>

                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Visi Perusahaan
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
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-amber-50 hover:text-amber-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-amber-950/30 dark:hover:text-amber-400"
                        @click="openEditVisi"
                    >
                        <Pencil class="size-4" />
                        Edit Visi
                    </button>
                </div>

                <!-- VISI CONTENT -->
                <div
                    v-if="props.visi"
                    class="p-6"
                >
                    <div
                        class="rounded-xl border border-blue-100 bg-blue-50/50 p-5 dark:border-blue-900/50 dark:bg-blue-950/20"
                    >
                        <p
                            class="whitespace-pre-line text-base leading-7 text-slate-700 dark:text-slate-200"
                        >
                            {{ props.visi.isi }}
                        </p>
                    </div>
                </div>

                <!-- EMPTY VISI -->
                <div
                    v-else
                    class="px-6 py-12 text-center"
                >
                    <Target
                        class="mx-auto mb-3 size-10 text-slate-300 dark:text-slate-700"
                    />

                    <p
                        class="font-medium text-slate-700 dark:text-slate-300"
                    >
                        Visi belum tersedia
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                    >
                        Tambahkan visi perusahaan untuk mulai mengelola
                        data misi.
                    </p>

                    <button
                        type="button"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                        @click="openCreateVisi"
                    >
                        <Plus class="size-4" />
                        Tambah Visi
                    </button>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- MISI -->
            <!-- ===================================================== -->

            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- HEADER -->
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                        >
                            <FileText class="size-4" />
                        </div>

                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Misi Perusahaan
                            </h2>

                            <p
                                class="text-xs text-slate-500 dark:text-slate-400"
                            >
                                Daftar misi dan urutan misi perusahaan.
                            </p>
                        </div>
                    </div>

                    <button
                        v-if="props.visi"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                        @click="openCreateMisi"
                    >
                        <Plus class="size-4" />
                        Tambah Misi
                    </button>
                </div>

                <!-- TABLE -->
                <div
                    v-if="props.visi && sortedMisis.length > 0"
                    class="overflow-x-auto"
                >
                    <table class="w-full text-left text-sm">
                        <!-- TABLE HEADER -->
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
                                    class="w-44 px-6 py-4 text-right font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <!-- TABLE BODY -->
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="(misi, index) in sortedMisis"
                                :key="misi.id"
                                class="transition-colors hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                            >
                                <!-- NOMOR -->
                                <td class="px-6 py-5 text-center">
                                    <div
                                        class="mx-auto flex size-9 items-center justify-center rounded-xl bg-blue-50 font-semibold text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                                    >
                                        {{ index + 1 }}
                                    </div>
                                </td>

                                <!-- ISI MISI -->
                                <td class="max-w-2xl px-6 py-5">
                                    <p
                                        class="whitespace-pre-line leading-6 text-slate-700 dark:text-slate-300"
                                    >
                                        {{ truncate(misi.isi) }}
                                    </p>
                                </td>

                                <!-- URUTAN -->
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        {{ misi.urutan }}
                                    </span>
                                </td>

                                <!-- AKSI -->
                                <td class="px-6 py-5">
                                    <div
                                        class="flex justify-end gap-1"
                                    >
                                        <!-- UP -->
                                        <button
                                            type="button"
                                            :disabled="
                                                isFirstMisi(index) ||
                                                movingMisiId === misi.id
                                            "
                                            class="rounded-lg p-2 transition"
                                            :class="
                                                isFirstMisi(index) ||
                                                movingMisiId === misi.id
                                                    ? 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                                    : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                            "
                                            title="Naikkan urutan"
                                            @click="
                                                moveMisi(
                                                    misi,
                                                    'up',
                                                )
                                            "
                                        >
                                            <ArrowUp
                                                class="size-4"
                                            />
                                        </button>

                                        <!-- DOWN -->
                                        <button
                                            type="button"
                                            :disabled="
                                                isLastMisi(index) ||
                                                movingMisiId === misi.id
                                            "
                                            class="rounded-lg p-2 transition"
                                            :class="
                                                isLastMisi(index) ||
                                                movingMisiId === misi.id
                                                    ? 'cursor-not-allowed text-slate-300 dark:text-slate-700'
                                                    : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400'
                                            "
                                            title="Turunkan urutan"
                                            @click="
                                                moveMisi(
                                                    misi,
                                                    'down',
                                                )
                                            "
                                        >
                                            <ArrowDown
                                                class="size-4"
                                            />
                                        </button>

                                        <!-- DETAIL -->
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Lihat"
                                            @click="
                                                openDetail(misi)
                                            "
                                        >
                                            <Eye class="size-4" />
                                        </button>

                                        <!-- EDIT -->
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                            title="Edit"
                                            @click="
                                                openEditMisi(misi)
                                            "
                                        >
                                            <Pencil
                                                class="size-4"
                                            />
                                        </button>

                                        <!-- DELETE -->
                                        <button
                                            type="button"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                            title="Hapus"
                                            @click="
                                                openDelete(misi)
                                            "
                                        >
                                            <Trash2
                                                class="size-4"
                                            />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- EMPTY MISI -->
                <div
                    v-else
                    class="px-6 py-12 text-center"
                >
                    <FileText
                        class="mx-auto mb-3 size-10 text-slate-300 dark:text-slate-700"
                    />

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
                                ? "Tambahkan misi perusahaan untuk melengkapi informasi."
                                : "Tambahkan visi terlebih dahulu sebelum menambahkan misi."
                        }}
                    </p>

                    <button
                        v-if="props.visi"
                        type="button"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700"
                        @click="openCreateMisi"
                    >
                        <Plus class="size-4" />
                        Tambah Misi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL VISI -->
    <!-- ============================================================= -->

    <div
        v-if="showVisiModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeVisiModal"
    >
        <div
            class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
        >
            <!-- HEADER -->
            <div
                class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
            >
                <div>
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
                        class="text-sm text-slate-500 dark:text-slate-400"
                    >
                        Kelola pernyataan visi perusahaan.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="processingVisi"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800"
                    @click="closeVisiModal"
                >
                    <X class="size-5" />
                </button>
            </div>

            <!-- FORM -->
            <form
                class="p-6"
                @submit.prevent="submitVisi"
            >
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        Visi Perusahaan
                        <span class="text-red-500">*</span>
                    </label>

                    <textarea
                        v-model="visiForm.isi"
                        rows="7"
                        maxlength="10000"
                        required
                        placeholder="Masukkan visi perusahaan..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                    />

                    <div
                        class="mt-1.5 flex justify-between text-xs text-slate-400"
                    >
                        <span>
                            Gunakan kalimat visi yang jelas dan ringkas.
                        </span>

                        <span>
                            {{ visiForm.isi.length }}/10000
                        </span>
                    </div>
                </div>

                <!-- FOOTER -->
                <div
                    class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800"
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
                            processingVisi ||
                            !visiForm.isi.trim()
                        "
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
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

    <!-- ============================================================= -->
    <!-- MODAL MISI -->
    <!-- ============================================================= -->

    <div
        v-if="showMisiModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeMisiModal"
    >
        <div
            class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
        >
            <!-- HEADER -->
            <div
                class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
            >
                <div>
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
                        class="text-sm text-slate-500 dark:text-slate-400"
                    >
                        {{
                            misiModalMode === "create"
                                ? "Tambahkan misi baru ke perusahaan."
                                : "Perbarui informasi misi perusahaan."
                        }}
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="processingMisi"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-800"
                    @click="closeMisiModal"
                >
                    <X class="size-5" />
                </button>
            </div>

            <!-- FORM -->
            <form
                class="p-6"
                @submit.prevent="submitMisi"
            >
                <div>
                    <label
                        class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                        Isi Misi
                        <span class="text-red-500">*</span>
                    </label>

                    <textarea
                        v-model="misiForm.isi"
                        rows="7"
                        maxlength="5000"
                        required
                        placeholder="Masukkan misi perusahaan..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-slate-900"
                    />

                    <div
                        class="mt-1.5 flex justify-between text-xs text-slate-400"
                    >
                        <span>
                            Jelaskan misi perusahaan secara spesifik.
                        </span>

                        <span>
                            {{ misiForm.isi.length }}/5000
                        </span>
                    </div>
                </div>

                <!-- INFO URUTAN -->
                <div
                    class="mt-5 rounded-xl border border-blue-100 bg-blue-50/50 p-4 dark:border-blue-900/50 dark:bg-blue-950/20"
                >
                    <div class="flex gap-3">
                        <FileText
                            class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400"
                        />

                        <p
                            class="text-xs leading-5 text-slate-600 dark:text-slate-300"
                        >
                            Urutan misi akan ditentukan otomatis oleh sistem.
                            Setelah disimpan, posisi misi dapat diubah
                            menggunakan tombol naik dan turun pada tabel.
                        </p>
                    </div>
                </div>

                <!-- FOOTER -->
                <div
                    class="mt-6 flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-800"
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
                            processingMisi ||
                            !misiForm.isi.trim()
                        "
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
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

    <!-- ============================================================= -->
    <!-- MODAL DETAIL MISI -->
    <!-- ============================================================= -->

    <div
        v-if="showDetail && selectedMisi"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeDetail"
    >
        <div
            class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
        >
            <!-- HEADER -->
            <div
                class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
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
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                    @click="closeDetail"
                >
                    <X class="size-5" />
                </button>
            </div>

            <!-- CONTENT -->
            <div class="space-y-5 p-6">
                <!-- URUTAN -->
                <div>
                    <p
                        class="text-xs font-medium uppercase tracking-wide text-slate-400"
                    >
                        Urutan
                    </p>

                    <span
                        class="mt-2 inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
                    >
                        Misi ke-{{ selectedMisi.urutan }}
                    </span>
                </div>

                <!-- ISI -->
                <div>
                    <p
                        class="text-xs font-medium uppercase tracking-wide text-slate-400"
                    >
                        Isi Misi
                    </p>

                    <div
                        class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50"
                    >
                        <p
                            class="whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300"
                        >
                            {{ selectedMisi.isi }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL HAPUS MISI -->
    <!-- ============================================================= -->

    <div
        v-if="showDelete && selectedMisi"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
        @click.self="closeDelete"
    >
        <div
            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900"
        >
            <!-- ICON -->
            <div
                class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
            >
                <Trash2 class="size-5" />
            </div>

            <!-- TEXT -->
            <div class="mt-4 text-center">
                <h2
                    class="text-lg font-semibold text-slate-900 dark:text-white"
                >
                    Hapus Misi?
                </h2>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400"
                >
                    Yakin ingin menghapus misi ke-
                    <span
                        class="font-semibold text-slate-700 dark:text-slate-200"
                    >
                        {{ selectedMisi.urutan }}
                    </span>
                    ? Data yang sudah dihapus tidak dapat dikembalikan.
                </p>

                <div
                    class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-left dark:border-slate-700 dark:bg-slate-800"
                >
                    <p
                        class="line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-300"
                    >
                        {{ selectedMisi.isi }}
                    </p>
                </div>
            </div>

            <!-- BUTTON -->
            <div class="mt-6 flex justify-end gap-3">
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
                    class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="deleteMisi"
                >
                    {{
                        processingDelete
                            ? "Menghapus..."
                            : "Ya, Hapus"
                    }}
                </button>
            </div>
        </div>
    </div>
</template>
