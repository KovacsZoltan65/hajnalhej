<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppHeader from '../Components/AppHeader.vue';
import AppLogo from '../Components/AppLogo.vue';
import AdminSidebar from '../Components/AdminSidebar.vue';
import FlashToast from '../Components/FlashToast.vue';

const page = usePage();
const logoutForm = useForm({});

const logout = () => {
    logoutForm.post('/logout');
};
</script>

<template>
    <div class="ui-shell min-h-screen bg-[#f7efe5] text-bakery-dark">
        <FlashToast />
        <AppHeader container-class="max-w-none">
            <template #actions>
                <div class="flex items-center gap-3">
                    <p class="hidden text-sm text-bakery-dark/75 sm:block">
                        Belépve:
                        <span class="font-semibold">{{ page.props.auth?.user?.name }}</span>
                    </p>
                    <button
                        type="button"
                        class="rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bakery-gold/70"
                        @click="logout"
                    >
                        Kijelentkezés
                    </button>
                </div>
            </template>
        </AppHeader>

        <div class="ui-main grid w-full gap-6 px-4 sm:px-6 lg:grid-cols-[16rem_1fr]">
            <div class="ui-card ui-card-elevated glass-bakery p-4">
                <div class="mb-4 border-b border-bakery-brown/10 pb-4">
                    <Link href="/admin/dashboard">
                        <AppLogo />
                    </Link>
                </div>
                <AdminSidebar />
            </div>

            <main class="ui-card ui-card-elevated p-6 sm:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
