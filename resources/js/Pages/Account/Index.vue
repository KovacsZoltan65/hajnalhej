<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from 'primevue/button';
import PublicLayout from '../../Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const ui = computed(() => page.props.ui ?? {});

const verificationForm = useForm({});

const resendVerification = () => {
    verificationForm.post('/email/verification-notification');
};
</script>

<template>
    <Head :title="ui.account?.title ?? 'Fiokom'" />

    <section class="mx-auto max-w-3xl space-y-6">
        <header class="rounded-3xl border border-bakery-brown/15 bg-[#fff9f1] p-6 shadow-sm sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-bakery-gold">Hajnalhej account</p>
            <h1 class="mt-3 font-heading text-4xl text-bakery-dark">{{ ui.account?.title ?? 'Fiokom' }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-bakery-dark/75">{{ ui.account?.subtitle ?? 'Itt kezeled a profilodat es a jovobeli gyors rendeles alapjait.' }}</p>
        </header>

        <div class="grid gap-4 sm:grid-cols-2">
            <article class="rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5">
                <p class="text-xs uppercase tracking-[0.18em] text-bakery-dark/60">Nev</p>
                <p class="mt-2 text-base font-semibold text-bakery-dark">{{ props.account.name }}</p>
            </article>

            <article class="rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5">
                <p class="text-xs uppercase tracking-[0.18em] text-bakery-dark/60">Email</p>
                <p class="mt-2 text-base font-semibold text-bakery-dark">{{ props.account.email }}</p>
            </article>
        </div>

        <article class="rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-bakery-dark/60">Email statusz</p>
                    <p class="mt-2 text-sm font-semibold" :class="props.account.is_verified ? 'text-emerald-700' : 'text-amber-700'">
                        {{ props.account.is_verified ? (ui.account?.email_status_verified ?? 'Email megerositve') : (ui.account?.email_status_pending ?? 'Email megerosites folyamatban') }}
                    </p>
                </div>

                <Button
                    v-if="!props.account.is_verified"
                    type="button"
                    :label="ui.verification?.send_again ?? 'Megerosito email ujrakuldese'"
                    :loading="verificationForm.processing"
                    :disabled="verificationForm.processing"
                    @click="resendVerification"
                />
            </div>
        </article>

        <article class="rounded-2xl border border-dashed border-bakery-brown/35 bg-[#fffaf4] p-5">
            <p class="text-sm text-bakery-dark/75">{{ ui.account?.future_features ?? 'Hamarosan: rendelesi elozmenyek, mentett cimek, kedvencek es huseg beallitasok.' }}</p>
        </article>
    </section>
</template>
