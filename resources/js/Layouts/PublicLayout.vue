<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import AppFooter from '../Components/AppFooter.vue';
import AppHeader from '../Components/AppHeader.vue';
import FlashToast from '../Components/FlashToast.vue';
import PublicNavbar from '../Components/PublicNavbar.vue';

const page = usePage();
</script>

<template>
    <div class="min-h-screen text-bakery-dark">
        <FlashToast />
        <AppHeader>
            <template #actions>
                <div class="flex items-center gap-3">
                    <PublicNavbar />
                    <Link
                        v-if="!page.props.auth?.user"
                        href="/login"
                        class="rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream"
                    >
                        Admin belepes
                    </Link>
                    <Link
                        v-else
                        href="/admin/dashboard"
                        class="rounded-full bg-bakery-brown px-4 py-2 text-sm font-semibold text-bakery-cream"
                    >
                        Admin panel
                    </Link>
                </div>
            </template>
        </AppHeader>

        <main class="mx-auto w-full max-w-6xl px-4 pb-10 pt-8 sm:px-6">
            <slot />
        </main>

        <AppFooter />
    </div>
</template>
