<script setup lang="ts">
import { onBeforeUnmount, watch, type Component } from "vue";
import { EditorContent, useEditor } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";

import {
    Bold,
    Heading2,
    Heading3,
    Italic,
    List,
    ListOrdered,
    Minus,
    Quote,
    Redo2,
    Strikethrough,
    Undo2,
} from "lucide-vue-next";

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
}>();

const editor = useEditor({
    content: props.modelValue || "",

    extensions: [
        StarterKit.configure({
            heading: {
                levels: [2, 3],
            },

            bulletList: {
                keepMarks: true,
                keepAttributes: true,
            },

            orderedList: {
                keepMarks: true,
                keepAttributes: true,
            },
        }),
    ],

    editorProps: {
        attributes: {
            class: [
                "rich-content",
                "min-h-[240px]",
                "max-h-[420px]",
                "overflow-y-auto",
                "px-4",
                "py-3",
                "text-sm",
                "text-slate-900",
                "outline-none",
                "dark:text-slate-100",
            ].join(" "),
        },
    },

    onUpdate: ({ editor }) => {
        emit("update:modelValue", editor.isEmpty ? "" : editor.getHTML());
    },
});

/**
 * Sinkronisasi ketika modelValue berubah
 * dari parent, misalnya ketika membuka modal edit.
 */
watch(
    () => props.modelValue,
    (value) => {
        const current = editor.value;

        if (!current) {
            return;
        }

        const incoming = value || "";
        const currentHtml = current.isEmpty ? "" : current.getHTML();

        if (incoming === currentHtml) {
            return;
        }

        current.commands.setContent(incoming, {
            emitUpdate: false,
        });
    },
);

/**
 * Destroy editor ketika component dilepas.
 */
onBeforeUnmount(() => {
    editor.value?.destroy();
});

interface ToolbarAction {
    label: string;
    icon: Component;
    isActive: () => boolean;
    run: () => void;
}

const run = (callback: () => void) => {
    if (!editor.value) {
        return;
    }

    callback();
};

const groups: ToolbarAction[][] = [
    // ============================================
    // TEXT
    // ============================================
    [
        {
            label: "Tebal (Ctrl+B)",
            icon: Bold,

            isActive: () => !!editor.value?.isActive("bold"),

            run: () =>
                run(() => editor.value!.chain().focus().toggleBold().run()),
        },

        {
            label: "Miring (Ctrl+I)",
            icon: Italic,

            isActive: () => !!editor.value?.isActive("italic"),

            run: () =>
                run(() => editor.value!.chain().focus().toggleItalic().run()),
        },

        {
            label: "Coret",
            icon: Strikethrough,

            isActive: () => !!editor.value?.isActive("strike"),

            run: () =>
                run(() => editor.value!.chain().focus().toggleStrike().run()),
        },
    ],

    // ============================================
    // HEADING
    // ============================================
    [
        {
            label: "Judul besar",
            icon: Heading2,

            isActive: () =>
                !!editor.value?.isActive("heading", {
                    level: 2,
                }),

            run: () =>
                run(() =>
                    editor
                        .value!.chain()
                        .focus()
                        .toggleHeading({
                            level: 2,
                        })
                        .run(),
                ),
        },

        {
            label: "Judul kecil",
            icon: Heading3,

            isActive: () =>
                !!editor.value?.isActive("heading", {
                    level: 3,
                }),

            run: () =>
                run(() =>
                    editor
                        .value!.chain()
                        .focus()
                        .toggleHeading({
                            level: 3,
                        })
                        .run(),
                ),
        },
    ],

    // ============================================
    // LIST
    // ============================================
    [
        {
            label: "Daftar poin",
            icon: List,

            isActive: () => !!editor.value?.isActive("bulletList"),

            run: () =>
                run(() =>
                    editor.value!.chain().focus().toggleBulletList().run(),
                ),
        },

        {
            label: "Daftar angka",
            icon: ListOrdered,

            isActive: () => !!editor.value?.isActive("orderedList"),

            run: () =>
                run(() =>
                    editor.value!.chain().focus().toggleOrderedList().run(),
                ),
        },

        {
            label: "Kutipan",
            icon: Quote,

            isActive: () => !!editor.value?.isActive("blockquote"),

            run: () =>
                run(() =>
                    editor.value!.chain().focus().toggleBlockquote().run(),
                ),
        },

        {
            label: "Garis pemisah",
            icon: Minus,

            isActive: () => false,

            run: () =>
                run(() =>
                    editor.value!.chain().focus().setHorizontalRule().run(),
                ),
        },
    ],

    // ============================================
    // HISTORY
    // ============================================
    [
        {
            label: "Urungkan (Ctrl+Z)",
            icon: Undo2,

            isActive: () => false,

            run: () => run(() => editor.value!.chain().focus().undo().run()),
        },

        {
            label: "Ulangi (Ctrl+Y)",
            icon: Redo2,

            isActive: () => false,

            run: () => run(() => editor.value!.chain().focus().redo().run()),
        },
    ],
];
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 transition-all focus-within:border-blue-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800 dark:focus-within:bg-slate-900"
    >
        <!-- TOOLBAR -->
        <div
            class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-white/70 px-2 py-1.5 dark:border-slate-700 dark:bg-slate-900/60"
        >
            <template v-for="(group, index) in groups" :key="index">
                <div
                    v-if="index > 0"
                    class="mx-1 h-5 w-px bg-slate-200 dark:bg-slate-700"
                />

                <button
                    v-for="action in group"
                    :key="action.label"
                    type="button"
                    :title="action.label"
                    :aria-label="action.label"
                    :aria-pressed="action.isActive()"
                    class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:text-slate-400 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                    :class="
                        action.isActive()
                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300'
                            : ''
                    "
                    @mousedown.prevent
                    @click="action.run()"
                >
                    <component :is="action.icon" class="size-4" />
                </button>
            </template>
        </div>

        <!-- EDITOR -->
        <EditorContent :editor="editor" />
    </div>
</template>

<style scoped>
/* ============================================
   Tiptap Content
============================================ */

:deep(.rich-content) {
    line-height: 1.75;
}

/* Paragraph */

:deep(.rich-content p) {
    margin: 0.5rem 0;
}

/* Heading */

:deep(.rich-content h2) {
    margin-top: 1.25rem;
    margin-bottom: 0.75rem;
    font-size: 1.5rem;
    line-height: 1.25;
    font-weight: 700;
}

:deep(.rich-content h3) {
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
    line-height: 1.35;
    font-weight: 700;
}

/* ============================================
   BULLET LIST
============================================ */

:deep(.rich-content ul) {
    list-style-type: disc;
    margin-top: 0.75rem;
    margin-bottom: 0.75rem;
    padding-left: 1.75rem;
}

:deep(.rich-content ul li) {
    padding-left: 0.25rem;
}

:deep(.rich-content ul li::marker) {
    color: rgb(59 130 246);
}

/* ============================================
   ORDERED LIST
============================================ */

:deep(.rich-content ol) {
    list-style-type: decimal;
    margin-top: 0.75rem;
    margin-bottom: 0.75rem;
    padding-left: 1.75rem;
}

:deep(.rich-content ol li) {
    padding-left: 0.25rem;
}

:deep(.rich-content ol li::marker) {
    color: rgb(59 130 246);
    font-weight: 600;
}

/* Nested list */

:deep(.rich-content ul ul) {
    list-style-type: circle;
}

:deep(.rich-content ol ol) {
    list-style-type: lower-alpha;
}

/* ============================================
   BLOCKQUOTE
============================================ */

:deep(.rich-content blockquote) {
    margin: 1rem 0;
    border-left: 4px solid rgb(59 130 246);
    padding-left: 1rem;
    font-style: italic;
    color: rgb(71 85 105);
}

.dark :deep(.rich-content blockquote) {
    color: rgb(148 163 184);
}

/* ============================================
   HORIZONTAL RULE
============================================ */

:deep(.rich-content hr) {
    margin: 1.25rem 0;
    border: 0;
    border-top: 1px solid rgb(203 213 225);
}

.dark :deep(.rich-content hr) {
    border-top-color: rgb(51 65 85);
}

/* ============================================
   STRIKE
============================================ */

:deep(.rich-content s) {
    text-decoration: line-through;
}

/* ============================================
   FOCUS
============================================ */

:deep(.rich-content:focus) {
    outline: none;
}

/* ============================================
   SCROLLBAR
============================================ */

:deep(.rich-content::-webkit-scrollbar) {
    width: 6px;
}

:deep(.rich-content::-webkit-scrollbar-track) {
    background: transparent;
}

:deep(.rich-content::-webkit-scrollbar-thumb) {
    border-radius: 9999px;
    background: rgb(148 163 184 / 0.35);
}

:deep(.rich-content::-webkit-scrollbar-thumb:hover) {
    background: rgb(100 116 139 / 0.55);
}
</style>
