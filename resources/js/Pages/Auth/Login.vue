<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import Checkbox from "primevue/checkbox";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Password from "primevue/password";
import PublicLayout from "../../Layouts/PublicLayout.vue";

defineOptions({ layout: PublicLayout });

const page = usePage();
const ui = computed(() => page.props.ui ?? {});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post("/login", {
        onFinish: () => {
            form.reset("password");
        },
    });
};
</script>

<template>
    <Head :title="ui.login?.title ?? 'Belépés'" />

    <div
        class="mx-auto max-w-md rounded-3xl border border-bakery-brown/15 bg-[#fff9f1] p-6 shadow-lg sm:p-8"
    >
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-bakery-gold">
            Hajnalhej account
        </p>
        <h1 class="mt-3 font-heading text-4xl text-bakery-dark">
            {{ ui.login?.title ?? "Belépés" }}
        </h1>
        <p class="mt-2 text-sm text-bakery-dark/75">
            {{ ui.login?.subtitle ?? "Lepj be a fiokodba, es rendelj gyorsabban." }}
        </p>

        <form class="mt-7 space-y-5" @submit.prevent="submit">
            <div class="space-y-2">
                <label for="email" class="text-sm font-medium text-bakery-dark"
                    >Email</label
                >
                <InputText
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="w-full"
                    :invalid="Boolean(form.errors.email)"
                    autocomplete="email"
                />
                <p v-if="form.errors.email" class="text-xs text-red-700">
                    {{ form.errors.email }}
                </p>
            </div>

            <div class="space-y-2">
                <label for="password" class="text-sm font-medium text-bakery-dark"
                    >Jelszo</label
                >
                <Password
                    id="password"
                    v-model="form.password"
                    class="w-full"
                    input-class="w-full"
                    :feedback="false"
                    toggle-mask
                    :invalid="Boolean(form.errors.password)"
                    autocomplete="current-password"
                />
                <p v-if="form.errors.password" class="text-xs text-red-700">
                    {{ form.errors.password }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Checkbox id="remember" v-model="form.remember" binary />
                <label for="remember" class="text-sm text-bakery-dark/80">{{
                    ui.login?.remember ?? "Emlekezz ram"
                }}</label>
            </div>

            <Button
                type="submit"
                :label="ui.login?.cta ?? 'Belepek'"
                class="w-full"
                :loading="form.processing"
                :disabled="form.processing"
            />
        </form>

        <p class="mt-6 text-center text-xs text-bakery-dark/70">
            <Link
                href="/register"
                class="font-semibold text-bakery-brown hover:underline"
                >{{
                    ui.login?.register_link ?? "Meg nincs fiokod? Regisztralj itt."
                }}</Link
            >
        </p>
    </div>
</template>
