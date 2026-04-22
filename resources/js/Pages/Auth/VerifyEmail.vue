<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from 'primevue/button';
import PublicLayout from '../../Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    isVerified: {
        type: Boolean,
        required: true,
    },
});

const page = usePage();
const ui = computed(() => page.props.ui ?? {});

const resendForm = useForm({});

const resendVerification = () => {
    resendForm.post('/email/verification-notification');
};
</script>

<template>
    <Head :title="ui.verification?.title ?? 'Email megerosites'" />

    <div class="mx-auto max-w-lg rounded-3xl border border-bakery-brown/15 bg-[#fff9f1] p-6 shadow-lg sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-bakery-gold">Hajnalhej account</p>
        <h1 class="mt-3 font-heading text-4xl text-bakery-dark">{{ ui.verification?.title ?? 'Email megerosites' }}</h1>
        <p class="mt-2 text-sm text-bakery-dark/75">{{ ui.verification?.subtitle ?? 'Kuldtunk egy megerosito linket az emailedre.' }}</p>

        <div class="mt-6 space-y-3">
            <p
                class="rounded-2xl border px-4 py-3 text-sm"
                :class="props.isVerified ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'"
            >
                {{ props.isVerified ? (ui.account?.email_status_verified ?? 'Email megerositve') : (ui.account?.email_status_pending ?? 'Email megerosites folyamatban') }}
            </p>

            <Button
                v-if="!props.isVerified"
                type="button"
                :label="ui.verification?.send_again ?? 'Megerosito email ujrakuldese'"
                class="w-full"
                :loading="resendForm.processing"
                :disabled="resendForm.processing"
                @click="resendVerification"
            />

            <Link href="/account" class="inline-flex text-sm font-semibold text-bakery-brown hover:underline">
                {{ ui.nav?.account ?? 'Fiókom' }}
            </Link>
        </div>
    </div>
</template>

