<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputMask from 'primevue/inputmask';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import PublicLayout from '../../Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    cart: {
        type: Object,
        required: true,
    },
    prefill: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    customer_name: props.prefill.customer_name,
    customer_email: props.prefill.customer_email,
    customer_phone: props.prefill.customer_phone,
    notes: props.prefill.notes,
    pickup_date: props.prefill.pickup_date,
    pickup_time_slot: props.prefill.pickup_time_slot,
    accept_privacy: false,
    accept_terms: false,
});

const submit = () => {
    form.post('/checkout');
};
</script>

<template>
    <Head title="Penztar" />

    <section class="mx-auto max-w-6xl space-y-6">
        <header class="rounded-3xl border border-bakery-brown/15 bg-[#fff7eb] p-6 sm:p-8">
            <h1 class="font-heading text-4xl text-bakery-dark">Penztar</h1>
            <p class="mt-2 text-sm text-bakery-dark/75">Told ki az adatokat, ellenorizd az osszegzest, es add le a rendelest.</p>
        </header>

        <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
            <form class="space-y-4 rounded-2xl border border-bakery-brown/15 bg-white/80 p-5" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2 sm:col-span-2">
                        <label for="customer_name" class="text-sm font-medium text-bakery-dark">Teljes nev</label>
                        <InputText id="customer_name" v-model="form.customer_name" class="w-full" :invalid="Boolean(form.errors.customer_name)" />
                        <p v-if="form.errors.customer_name" class="text-xs text-red-700">{{ form.errors.customer_name }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="customer_email" class="text-sm font-medium text-bakery-dark">Email</label>
                        <InputText id="customer_email" v-model="form.customer_email" type="email" class="w-full" :invalid="Boolean(form.errors.customer_email)" />
                        <p v-if="form.errors.customer_email" class="text-xs text-red-700">{{ form.errors.customer_email }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="customer_phone" class="text-sm font-medium text-bakery-dark">Telefonszam</label>
                        <InputMask id="customer_phone" v-model="form.customer_phone" mask="+36999999999" class="w-full" :invalid="Boolean(form.errors.customer_phone)" />
                        <p v-if="form.errors.customer_phone" class="text-xs text-red-700">{{ form.errors.customer_phone }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="pickup_date" class="text-sm font-medium text-bakery-dark">Atvetel datuma</label>
                        <InputText id="pickup_date" v-model="form.pickup_date" type="date" class="w-full" :invalid="Boolean(form.errors.pickup_date)" />
                        <p v-if="form.errors.pickup_date" class="text-xs text-red-700">{{ form.errors.pickup_date }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="pickup_time_slot" class="text-sm font-medium text-bakery-dark">Atveteli idosav</label>
                        <InputText id="pickup_time_slot" v-model="form.pickup_time_slot" placeholder="pl. 08:00-10:00" class="w-full" :invalid="Boolean(form.errors.pickup_time_slot)" />
                        <p v-if="form.errors.pickup_time_slot" class="text-xs text-red-700">{{ form.errors.pickup_time_slot }}</p>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label for="notes" class="text-sm font-medium text-bakery-dark">Megjegyzes</label>
                        <Textarea id="notes" v-model="form.notes" rows="4" class="w-full" :invalid="Boolean(form.errors.notes)" />
                        <p v-if="form.errors.notes" class="text-xs text-red-700">{{ form.errors.notes }}</p>
                    </div>
                </div>

                <label class="flex items-start gap-2 rounded-xl border border-bakery-brown/15 px-3 py-2 text-sm text-bakery-dark/85">
                    <input v-model="form.accept_privacy" type="checkbox" class="mt-1" />
                    Elfogadom az adatkezelesi tajekoztatot.
                </label>
                <p v-if="form.errors.accept_privacy" class="text-xs text-red-700">{{ form.errors.accept_privacy }}</p>

                <label class="flex items-start gap-2 rounded-xl border border-bakery-brown/15 px-3 py-2 text-sm text-bakery-dark/85">
                    <input v-model="form.accept_terms" type="checkbox" class="mt-1" />
                    Elfogadom az ASZF-et.
                </label>
                <p v-if="form.errors.accept_terms" class="text-xs text-red-700">{{ form.errors.accept_terms }}</p>

                <Button type="submit" label="Rendeles leadasa" class="w-full" :loading="form.processing" :disabled="form.processing" />
            </form>

            <aside class="h-fit rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5 shadow-sm">
                <h2 class="font-heading text-2xl text-bakery-dark">Rendeles osszegzese</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    <li v-for="item in cart.items" :key="item.product_id" class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-medium text-bakery-dark">{{ item.name }}</p>
                            <p class="text-xs text-bakery-dark/70">{{ item.quantity }} x {{ new Intl.NumberFormat('hu-HU').format(item.unit_price) }} Ft</p>
                        </div>
                        <p class="font-semibold text-bakery-dark">{{ new Intl.NumberFormat('hu-HU').format(item.line_total) }} Ft</p>
                    </li>
                </ul>

                <div class="mt-5 border-t border-bakery-brown/10 pt-4 text-sm">
                    <div class="flex justify-between font-semibold text-bakery-dark">
                        <span>Vegosszeg</span>
                        <span>{{ new Intl.NumberFormat('hu-HU').format(cart.summary.total) }} Ft</span>
                    </div>
                </div>

                <Link href="/cart" class="mt-4 inline-flex text-sm font-semibold text-bakery-brown hover:underline">
                    Vissza a kosarhoz
                </Link>
            </aside>
        </div>
    </section>
</template>
