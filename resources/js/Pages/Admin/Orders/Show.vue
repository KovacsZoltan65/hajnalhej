<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";

import OrderStatusBadge from "@/Components/Orders/OrderStatusBadge.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";

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
    internal_notes: props.order.internal_notes ?? "",
    pickup_date: props.order.pickup_date ?? "",
    pickup_time_slot: props.order.pickup_time_slot ?? "",
});

const formatCurrency = (value) =>
    new Intl.NumberFormat(trans("common.locale"), {
        style: "currency",
        currency: trans("common.currency"),
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const updateStatus = () => {
    statusForm.patch(`/admin/orders/${props.order.id}/status`);
};
</script>

<template>
    <Head :title="trans('admin_orders.show_meta_title', { number: order.order_number })" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_orders.eyebrow')"
            :title="trans('admin_orders.show_title', { number: order.order_number })"
            :description="$t('admin_orders.show_description')"
        />

        <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
            <section class="space-y-4">
                <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-heading text-2xl text-bakery-dark">{{ $t("admin_orders.sections.customer_details") }}</h2>
                        <OrderStatusBadge :status="order.status" />
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-bakery-dark/60">{{ $t("admin_orders.fields.name") }}</dt>
                            <dd class="font-semibold text-bakery-dark">{{ order.customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-bakery-dark/60">{{ $t("admin_orders.fields.email") }}</dt>
                            <dd class="font-semibold text-bakery-dark">{{ order.customer_email }}</dd>
                        </div>
                        <div>
                            <dt class="text-bakery-dark/60">{{ $t("admin_orders.fields.phone") }}</dt>
                            <dd class="font-semibold text-bakery-dark">{{ order.customer_phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-bakery-dark/60">{{ $t("admin_orders.fields.pickup") }}</dt>
                            <dd class="font-semibold text-bakery-dark">{{ order.pickup_date || '-' }} {{ order.pickup_time_slot || '' }}</dd>
                        </div>
                    </dl>
                </article>

                <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
                    <h2 class="font-heading text-2xl text-bakery-dark">{{ $t("admin_orders.sections.items") }}</h2>
                    <div class="mt-4 space-y-3">
                        <div v-for="item in order.items" :key="item.id" class="rounded-xl border border-bakery-brown/10 bg-[#fff9f1] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-bakery-dark">{{ item.product_name_snapshot }}</p>
                                    <p class="text-xs text-bakery-dark/65">{{ item.quantity }} x {{ formatCurrency(item.unit_price) }}</p>
                                </div>
                                <p class="font-semibold text-bakery-dark">{{ formatCurrency(item.line_total) }}</p>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <aside class="h-fit space-y-4 rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5 shadow-sm">
                <h2 class="font-heading text-2xl text-bakery-dark">{{ $t("admin_orders.sections.actions") }}</h2>
                <div class="space-y-2 text-sm">
                    <p class="flex justify-between"><span>{{ $t("admin_orders.fields.subtotal") }}</span><span>{{ formatCurrency(order.subtotal) }}</span></p>
                    <p class="flex justify-between font-semibold text-bakery-dark"><span>{{ $t("admin_orders.fields.total") }}</span><span>{{ formatCurrency(order.total) }}</span></p>
                </div>

                <form class="space-y-3" @submit.prevent="updateStatus">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">{{ $t("admin_orders.fields.status") }}</label>
                        <Select v-model="statusForm.status" :options="statusOptions" class="w-full" />
                        <p v-if="statusForm.errors.status" class="text-xs text-red-700">{{ statusForm.errors.status }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">{{ $t("admin_orders.fields.internal_notes") }}</label>
                        <Textarea v-model="statusForm.internal_notes" rows="4" class="w-full" />
                        <p v-if="statusForm.errors.internal_notes" class="text-xs text-red-700">{{ statusForm.errors.internal_notes }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">{{ $t("admin_orders.fields.pickup_date") }}</label>
                        <InputText v-model="statusForm.pickup_date" type="date" class="w-full" />
                        <p v-if="statusForm.errors.pickup_date" class="text-xs text-red-700">{{ statusForm.errors.pickup_date }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">{{ $t("admin_orders.fields.pickup_time_slot") }}</label>
                        <InputText v-model="statusForm.pickup_time_slot" class="w-full" :placeholder="$t('admin_orders.placeholders.pickup_time_slot')" />
                        <p v-if="statusForm.errors.pickup_time_slot" class="text-xs text-red-700">{{ statusForm.errors.pickup_time_slot }}</p>
                    </div>

                    <Button type="submit" :label="$t('admin_orders.actions.update_status')" class="w-full" :loading="statusForm.processing" :disabled="statusForm.processing" />
                </form>
            </aside>
        </div>
    </div>
</template>

