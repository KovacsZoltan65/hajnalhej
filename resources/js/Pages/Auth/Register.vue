<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import PublicLayout from '../../Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const page = usePage();
const ui = computed(() => page.props.ui ?? {});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head :title="ui.register?.title ?? 'Regisztráció'" />

    <div class="mx-auto max-w-lg rounded-3xl border border-bakery-brown/15 bg-[#fff9f1] p-6 shadow-lg sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-bakery-gold">Hajnalhej account</p>
        <h1 class="mt-3 font-heading text-4xl text-bakery-dark">{{ ui.register?.title ?? 'Hozd letre a fiokodat' }}</h1>
        <p class="mt-2 text-sm text-bakery-dark/75">{{ ui.register?.subtitle ?? 'Regisztralj, hogy gyorsabban rendelhesd kedvenceidet.' }}</p>

        <form class="mt-7 space-y-5" @submit.prevent="submit">
            <div class="space-y-2">
                <label for="name" class="text-sm font-medium text-bakery-dark">Teljes nev</label>
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
                <label for="email" class="text-sm font-medium text-bakery-dark">Email</label>
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
                <label for="password" class="text-sm font-medium text-bakery-dark">Jelszo</label>
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
                <label for="password_confirmation" class="text-sm font-medium text-bakery-dark">Jelszo megerositese</label>
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
                :label="ui.register?.cta ?? 'Fiók létrehozása'"
                class="w-full"
                :loading="form.processing"
                :disabled="form.processing"
            />
        </form>

        <p class="mt-6 text-center text-xs text-bakery-dark/70">
            <Link href="/login" class="font-semibold text-bakery-brown hover:underline">{{ ui.register?.login_link ?? 'Mar van fiokod? Lepj be.' }}</Link>
        </p>
    </div>
</template>

