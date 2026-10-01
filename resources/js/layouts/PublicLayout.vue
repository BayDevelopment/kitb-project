<script setup lang="ts">
import PublicNavbar from "@/components/public/PublicNavbar.vue";
import PublicFooter from "@/components/public/PublicFooter.vue";
</script>

<template>
    <div class="min-h-screen bg-kitb-sand-50 text-kitb-ink-900 antialiased">
        <PublicNavbar />

        <main>
            <slot />
        </main>

        <PublicFooter />
    </div>
</template>
