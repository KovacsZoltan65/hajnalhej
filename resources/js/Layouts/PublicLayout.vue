<script setup>
import AppFooter from "../Components/AppFooter.vue";
import AppHeader from "../Components/AppHeader.vue";
import FlashToast from "../Components/FlashToast.vue";
import PublicNavbar from "../Components/PublicNavbar.vue";
</script>

<template>
    <div class="ui-shell min-h-screen text-bakery-dark">
        <FlashToast />
        <AppHeader>
            <template #actions>
                <PublicNavbar />
            </template>
        </AppHeader>

        <main class="ui-main mx-auto w-full max-w-6xl px-4 pb-12 sm:px-6">
            <slot />
        </main>

        <AppFooter />
    </div>
</template>
