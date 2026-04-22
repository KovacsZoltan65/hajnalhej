<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';

import OrderStatusBadge from '@/Components/Orders/OrderStatusBadge.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    statusOptions: {
        type: Array,
        required: true,
    },
});

const statusForm = useForm({
    status: props.order.status,
    internal_notes: props.order.internal_notes ?? '',
    pickup_date: props.order.pickup_date ?? '',
    pickup_time_slot: props.order.pickup_time_slot ?? '',
});

const updateStatus = () => {
    statusForm.patch(`/admin/orders/${props.order.id}/status`);
};
</script>

<template>
    <Head :title="`Rendelés ${order.order_number}`" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Rendelések"
            :title="`Rendelés: ${order.order_number}`"
            description="Rendelési adatok, tételek, státuszfrissítés és belső megjegyzések egy helyen."
        />

        <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
            <section class="space-y-4">
                <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-heading text-2xl text-bakery-dark">Ugyfel adatok</h2>
                        <OrderStatusBadge :status="order.status" />
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-bakery-dark/60">Név</dt>
                            <dd class="font-semibold text-bakery-dark">{{ order.customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-bakery-dark/60">Email</dt>
                            <dd class="font-semibold text-bakery-dark">{{ order.customer_email }}</dd>
                        </div>
                        <div>
                            <dt class="text-bakery-dark/60">Telefon</dt>
                            <dd class="font-semibold text-bakery-dark">{{ order.customer_phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-bakery-dark/60">Átvétel</dt>
                            <dd class="font-semibold text-bakery-dark">{{ order.pickup_date || '-' }} {{ order.pickup_time_slot || '' }}</dd>
                        </div>
                    </dl>
                </article>

                <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
                    <h2 class="font-heading text-2xl text-bakery-dark">Rendelési tetelek</h2>
                    <div class="mt-4 space-y-3">
                        <div v-for="item in order.items" :key="item.id" class="rounded-xl border border-bakery-brown/10 bg-[#fff9f1] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-bakery-dark">{{ item.product_name_snapshot }}</p>
                                    <p class="text-xs text-bakery-dark/65">{{ item.quantity }} x {{ new Intl.NumberFormat('hu-HU').format(item.unit_price) }} Ft</p>
                                </div>
                                <p class="font-semibold text-bakery-dark">{{ new Intl.NumberFormat('hu-HU').format(item.line_total) }} Ft</p>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <aside class="h-fit space-y-4 rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5 shadow-sm">
                <h2 class="font-heading text-2xl text-bakery-dark">Muveletek</h2>
                <div class="space-y-2 text-sm">
                    <p class="flex justify-between"><span>Reszosszeg</span><span>{{ new Intl.NumberFormat('hu-HU').format(order.subtotal) }} Ft</span></p>
                    <p class="flex justify-between font-semibold text-bakery-dark"><span>Vegosszeg</span><span>{{ new Intl.NumberFormat('hu-HU').format(order.total) }} Ft</span></p>
                </div>

                <form class="space-y-3" @submit.prevent="updateStatus">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Státusz</label>
                        <Select v-model="statusForm.status" :options="statusOptions" class="w-full" />
                        <p v-if="statusForm.errors.status" class="text-xs text-red-700">{{ statusForm.errors.status }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Belso megjegyzes</label>
                        <Textarea v-model="statusForm.internal_notes" rows="4" class="w-full" />
                        <p v-if="statusForm.errors.internal_notes" class="text-xs text-red-700">{{ statusForm.errors.internal_notes }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Átvétel dátuma</label>
                        <InputText v-model="statusForm.pickup_date" type="date" class="w-full" />
                        <p v-if="statusForm.errors.pickup_date" class="text-xs text-red-700">{{ statusForm.errors.pickup_date }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">Átvételi idősáv</label>
                        <InputText v-model="statusForm.pickup_time_slot" class="w-full" placeholder="pl. 08:00-10:00" />
                        <p v-if="statusForm.errors.pickup_time_slot" class="text-xs text-red-700">{{ statusForm.errors.pickup_time_slot }}</p>
                    </div>

                    <Button type="submit" label="Státusz frissítése" class="w-full" :loading="statusForm.processing" :disabled="statusForm.processing" />
                </form>
            </aside>
        </div>
    </div>
</template>

