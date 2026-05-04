<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import { useConversionTracking } from '@/composables/useConversionTracking';

defineOptions({ layout: PublicLayout });

const { trackCtaClick, trackFunnel } = useConversionTracking();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    trackFunnel('registration.submitted', {
        funnel: 'registration',
        step: 'submit',
        metadata: {
            has_name: form.name.trim().length > 0,
            has_email: form.email.trim().length > 0,
        },
    });

    form.post(route('register.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head :title="$t('register.title')" />

    <div class="mx-auto max-w-lg rounded-3xl border border-bakery-brown/15 bg-[#fff9f1] p-6 shadow-lg sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-bakery-gold">{{ $t("auth.account_label") }}</p>
        <h1 class="mt-3 font-heading text-4xl text-bakery-dark">{{ $t("register.title") }}</h1>
        <p class="mt-2 text-sm text-bakery-dark/75">{{ $t("register.subtitle") }}</p>

        <form class="mt-7 space-y-5" @submit.prevent="submit">
            <div class="space-y-2">
                <label for="name" class="text-sm font-medium text-bakery-dark">{{ $t("fields.name") }}</label>
                <InputText
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="w-full"
                    :invalid="Boolean(form.errors.name)"
                    autocomplete="name"
                />
                <p v-if="form.errors.name" class="text-xs text-red-700">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-2">
                <label for="email" class="text-sm font-medium text-bakery-dark">{{ $t("fields.email") }}</label>
                <InputText
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="w-full"
                    :invalid="Boolean(form.errors.email)"
                    autocomplete="email"
                />
                <p v-if="form.errors.email" class="text-xs text-red-700">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-2">
                <label for="password" class="text-sm font-medium text-bakery-dark">{{ $t("fields.password") }}</label>
                <Password
                    id="password"
                    v-model="form.password"
                    class="w-full"
                    input-class="w-full"
                    toggle-mask
                    :feedback="true"
                    :invalid="Boolean(form.errors.password)"
                    autocomplete="new-password"
                />
                <p v-if="form.errors.password" class="text-xs text-red-700">{{ form.errors.password }}</p>
            </div>

            <div class="space-y-2">
                <label for="password_confirmation" class="text-sm font-medium text-bakery-dark">{{ $t("fields.password_confirmation") }}</label>
                <Password
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    class="w-full"
                    input-class="w-full"
                    :feedback="false"
                    toggle-mask
                    :invalid="Boolean(form.errors.password_confirmation)"
                    autocomplete="new-password"
                />
                <p v-if="form.errors.password_confirmation" class="text-xs text-red-700">{{ form.errors.password_confirmation }}</p>
            </div>

            <Button
                type="submit"
                :label="$t('register.cta')"
                class="w-full"
                :loading="form.processing"
                :disabled="form.processing"
            />
        </form>

        <p class="mt-6 text-center text-xs text-bakery-dark/70">
            <Link
                :href="route('login')"
                class="font-semibold text-bakery-brown hover:underline"
                @click="trackCtaClick('register.login_link', { funnel: 'registration', step: 'redirect_login' })"
            >{{ $t("register.login_link") }}</Link>
        </p>
    </div>
</template>

