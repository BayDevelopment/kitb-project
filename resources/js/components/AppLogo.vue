<script setup lang="ts">
const logoSrc = "/logoside.png";
</script>

<template>
    <div
        class="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-lg"
    >
        <img
            :src="logoSrc"
            alt="Logo KITB"
            width="32"
            height="32"
            class="size-full object-contain"
            draggable="false"
        />
    </div>

    <div class="ml-2 grid flex-1 text-left">
        <span
            class="truncate text-[15px] leading-[1.1] font-bold tracking-wide"
        >
            KITB MANAGEMENT
        </span>
        <span
            class="truncate text-[10px] leading-[1.1] font-medium text-slate-500 dark:text-slate-400"
        >
            Tanjung Buton
        </span>
    </div>
</template>
