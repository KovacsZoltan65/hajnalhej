<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputMask from 'primevue/inputmask';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import { useConversionTracking } from '@/composables/useConversionTracking';

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

const { trackCtaClick, trackFunnel } = useConversionTracking();

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
    trackFunnel('checkout.submitted', {
        funnel: 'checkout',
        step: 'submit',
        metadata: {
            total: props.cart.summary?.total ?? null,
            items_count: props.cart.summary?.items_count ?? null,
            has_notes: form.notes.trim().length > 0,
        },
    });

    form.post('/checkout');
};
</script>

<template>
    <Head title="Pénztár" />

    <section class="mx-auto max-w-6xl space-y-6">
        <header class="rounded-3xl border border-bakery-brown/15 bg-[#fff7eb] p-6 sm:p-8">
            <h1 class="font-heading text-4xl text-bakery-dark">Pénztár</h1>
            <p class="mt-2 text-sm text-bakery-dark/75">Töltsd ki az adatokat, ellenőrizd az összegzést, és add le a rendelést.</p>
        </header>

        <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
            <form class="space-y-4 rounded-2xl border border-bakery-brown/15 bg-white/80 p-5" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2 sm:col-span-2">
                        <label for="customer_name" class="text-sm font-medium text-bakery-dark">Teljes név</label>
                        <InputText id="customer_name" v-model="form.customer_name" class="w-full" :invalid="Boolean(form.errors.customer_name)" />
                        <p v-if="form.errors.customer_name" class="text-xs text-red-700">{{ form.errors.customer_name }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="customer_email" class="text-sm font-medium text-bakery-dark">Email</label>
                        <InputText id="customer_email" v-model="form.customer_email" type="email" class="w-full" :invalid="Boolean(form.errors.customer_email)" />
                        <p v-if="form.errors.customer_email" class="text-xs text-red-700">{{ form.errors.customer_email }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="customer_phone" class="text-sm font-medium text-bakery-dark">Telefonszám</label>
                        <InputMask id="customer_phone" v-model="form.customer_phone" mask="+36999999999" class="w-full" :invalid="Boolean(form.errors.customer_phone)" />
                        <p v-if="form.errors.customer_phone" class="text-xs text-red-700">{{ form.errors.customer_phone }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="pickup_date" class="text-sm font-medium text-bakery-dark">Átvétel dátuma</label>
                        <InputText id="pickup_date" v-model="form.pickup_date" type="date" class="w-full" :invalid="Boolean(form.errors.pickup_date)" />
                        <p v-if="form.errors.pickup_date" class="text-xs text-red-700">{{ form.errors.pickup_date }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="pickup_time_slot" class="text-sm font-medium text-bakery-dark">Átvételi idősáv</label>
                        <InputText id="pickup_time_slot" v-model="form.pickup_time_slot" placeholder="pl. 08:00-10:00" class="w-full" :invalid="Boolean(form.errors.pickup_time_slot)" />
                        <p v-if="form.errors.pickup_time_slot" class="text-xs text-red-700">{{ form.errors.pickup_time_slot }}</p>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label for="notes" class="text-sm font-medium text-bakery-dark">Megjegyzés</label>
                        <Textarea id="notes" v-model="form.notes" rows="4" class="w-full" :invalid="Boolean(form.errors.notes)" />
                        <p v-if="form.errors.notes" class="text-xs text-red-700">{{ form.errors.notes }}</p>
                    </div>
                </div>

                <label
                    for="accept_privacy"
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-bakery-brown/15 px-3 py-2 text-sm text-bakery-dark/85"
                >
                    <Checkbox
                        input-id="accept_privacy"
                        v-model="form.accept_privacy"
                        binary
                        class="mt-0.5"
                    />
                    <span>Elfogadom az adatkezelési tájékoztatót.</span>
                </label>
                <p v-if="form.errors.accept_privacy" class="text-xs text-red-700">{{ form.errors.accept_privacy }}</p>

                <label
                    for="accept_terms"
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-bakery-brown/15 px-3 py-2 text-sm text-bakery-dark/85"
                >
                    <Checkbox
                        input-id="accept_terms"
                        v-model="form.accept_terms"
                        binary
                        class="mt-0.5"
                    />
                    <span>Elfogadom az ÁSZF-et.</span>
                </label>
                <p v-if="form.errors.accept_terms" class="text-xs text-red-700">{{ form.errors.accept_terms }}</p>

                <Button type="submit" label="Rendelés leadása" class="w-full" :loading="form.processing" :disabled="form.processing" />
            </form>

            <aside class="h-fit rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5 shadow-sm">
                <h2 class="font-heading text-2xl text-bakery-dark">Rendelés összegzése</h2>
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
                        <span>Végösszeg</span>
                        <span>{{ new Intl.NumberFormat('hu-HU').format(cart.summary.total) }} Ft</span>
                    </div>
                </div>

                <Link
                    href="/cart"
                    class="mt-4 inline-flex text-sm font-semibold text-bakery-brown hover:underline"
                    @click="trackCtaClick('checkout.back_to_cart', { funnel: 'checkout', step: 'back_to_cart' })"
                >
                    Vissza a kosárhoz
                </Link>
            </aside>
        </div>
    </section>
</template>

