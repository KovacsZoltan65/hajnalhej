<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import PublicLayout from '../../Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    isVerified: {
        type: Boolean,
        required: true,
    },
});

const resendForm = useForm({});

const resendVerification = () => {
    resendForm.post('/email/verification-notification');
};
</script>

<template>
    <Head :title="$t('verification.title')" />

    <div class="mx-auto max-w-lg rounded-3xl border border-bakery-brown/15 bg-[#fff9f1] p-6 shadow-lg sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-bakery-gold">{{ $t("auth.account_label") }}</p>
        <h1 class="mt-3 font-heading text-4xl text-bakery-dark">{{ $t("verification.title") }}</h1>
        <p class="mt-2 text-sm text-bakery-dark/75">{{ $t("verification.subtitle") }}</p>

        <div class="mt-6 space-y-3">
            <p
                class="rounded-2xl border px-4 py-3 text-sm"
                :class="props.isVerified ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'"
            >
                {{ props.isVerified ? $t("account.email_status_verified") : $t("account.email_status_pending") }}
            </p>

            <Button
                v-if="!props.isVerified"
                type="button"
                :label="$t('verification.send_again')"
                class="w-full"
                :loading="resendForm.processing"
                :disabled="resendForm.processing"
                @click="resendVerification"
            />

            <Link href="/account" class="inline-flex text-sm font-semibold text-bakery-brown hover:underline">
                {{ $t("nav.account") }}
            </Link>
        </div>
    </div>
</template>
