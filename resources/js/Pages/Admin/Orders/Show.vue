<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";

import CourierAssignmentCard from "@/Components/Admin/Orders/CourierAssignmentCard.vue";
import CourierAssignPanel from "@/Components/Admin/Orders/CourierAssignPanel.vue";
import BaseDatePicker from "@/Components/BaseDatePicker.vue";
import OrderFulfillmentBadge from "@/Components/Orders/OrderFulfillmentBadge.vue";
import OrderStatusBadge from "@/Components/Orders/OrderStatusBadge.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { trans } from "laravel-vue-i18n";
import { useLocaleFormat } from "@/composables/useLocaleFormat";

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
    courierOptions: {
        type: Array,
        default: () => [],
    },
    deliveryStatusOptions: {
        type: Array,
        default: () => [],
    },
    can: {
        type: Object,
        default: () => ({
            assignCourier: false,
        }),
    },
});

const statusForm = useForm({
    status: props.order.status,
    internal_notes: props.order.internal_notes ?? "",
    pickup_date: props.order.pickup_date ?? "",
    pickup_time_slot: props.order.pickup_time_slot ?? "",
});

const { formatCurrency } = useLocaleFormat();

const updateStatus = () => {
    statusForm.patch(route("admin.orders.status.update", props.order.id));
};

const addressRows = (address) => {
    if (!address) {
        return [];
    }

    return [
        ["common.name", address.name],
        ["orders.address.company_name", address.company_name],
        ["orders.address.tax_number", address.tax_number],
        ["orders.address.address", [address.country, address.postal_code, address.city].filter(Boolean).join(", ")],
        ["orders.address.street", [address.street, address.house_number].filter(Boolean).join(" ")],
        ["orders.address.floor", address.floor],
        ["orders.address.door", address.door],
        ["common.phone", address.phone],
        ["orders.address.notes", address.notes],
    ].filter(([, value]) => value !== null && value !== undefined && value !== "");
};
</script>

<template>
    <Head :title="trans('admin_orders.show_meta_title', { number: order.order_number })" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <SectionTitle
                :eyebrow="$t('admin_orders.eyebrow')"
                :title="trans('admin_orders.show_title', { number: order.order_number })"
                :description="$t('admin_orders.show_description')"
            />

            <Link :href="route('admin.orders.index')">
                <Button :label="$t('common.back_to_list')" icon="pi pi-arrow-left" text />
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
            <section class="space-y-4">
                <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-heading text-2xl text-bakery-dark">
                            {{ $t("admin_orders.sections.customer_details") }}
                        </h2>
                        <OrderStatusBadge :status="order.status" />
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-bakery-dark/60">
                                {{ $t("common.name") }}
                            </dt>
                            <dd class="font-semibold text-bakery-dark">
                                {{ order.customer_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-bakery-dark/60">
                                {{ $t("common.email") }}
                            </dt>
                            <dd class="font-semibold text-bakery-dark">
                                {{ order.customer_email }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-bakery-dark/60">
                                {{ $t("common.phone") }}
                            </dt>
                            <dd class="font-semibold text-bakery-dark">
                                {{ order.customer_phone }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-bakery-dark/60">
                                {{ $t("orders.fulfillment.method") }}
                            </dt>
                            <dd>
                                <OrderFulfillmentBadge
                                    :method="order.fulfillment_method"
                                    :label="order.fulfillment_label"
                                />
                            </dd>
                        </div>
                        <div v-if="order.fulfillment_method === 'pickup'">
                            <dt class="text-bakery-dark/60">
                                {{ $t("common.pickup") }}
                            </dt>
                            <dd class="font-semibold text-bakery-dark">
                                {{ order.pickup_date || "-" }}
                                {{ order.pickup_time_slot || "" }}
                            </dd>
                        </div>
                        <div v-if="order.pickup_branch">
                            <dt class="text-bakery-dark/60">
                                {{ $t("orders.fulfillment.pickup_branch") }}
                            </dt>
                            <dd class="font-semibold text-bakery-dark">
                                {{ order.pickup_branch.name }}
                                <span class="block text-xs font-normal text-bakery-dark/65">
                                    {{ order.pickup_branch.address || order.pickup_branch.code }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </article>

                <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
                    <h2 class="font-heading text-2xl text-bakery-dark">
                        {{ $t("orders.address.billing") }}
                    </h2>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div v-for="[label, value] in addressRows(order.billing_address_snapshot)" :key="label">
                            <dt class="text-bakery-dark/60">{{ $t(label) }}</dt>
                            <dd class="font-semibold text-bakery-dark">{{ value }}</dd>
                        </div>
                    </dl>
                </article>

                <article
                    v-if="order.fulfillment_method === 'delivery'"
                    class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5"
                >
                    <h2 class="font-heading text-2xl text-bakery-dark">
                        {{ $t("orders.address.shipping") }}
                    </h2>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div v-for="[label, value] in addressRows(order.shipping_address_snapshot)" :key="label">
                            <dt class="text-bakery-dark/60">{{ $t(label) }}</dt>
                            <dd class="font-semibold text-bakery-dark">{{ value }}</dd>
                        </div>
                    </dl>
                    <div v-if="order.delivery_notes" class="mt-4 text-sm">
                        <p class="text-bakery-dark/60">{{ $t("orders.fulfillment.delivery_notes") }}</p>
                        <p class="font-semibold text-bakery-dark">{{ order.delivery_notes }}</p>
                    </div>
                </article>

                <CourierAssignmentCard :order="order" :couriers="courierOptions" :can-assign="can.assignCourier" />

                <CourierAssignPanel v-if="order.fulfillment_method === 'delivery'" :order="order" />

                <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
                    <h2 class="font-heading text-2xl text-bakery-dark">
                        {{ $t("admin_orders.sections.items") }}
                    </h2>
                    <div class="mt-4 space-y-3">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="rounded-xl border border-bakery-brown/10 bg-[#fff9f1] p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-bakery-dark">
                                        {{ item.product_name_snapshot }}
                                    </p>
                                    <p class="text-xs text-bakery-dark/65">
                                        {{ item.quantity }} x
                                        {{ formatCurrency(item.unit_price) }}
                                    </p>
                                </div>
                                <p class="font-semibold text-bakery-dark">
                                    {{ formatCurrency(item.line_total) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <aside class="h-fit space-y-4 rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5 shadow-sm">
                <h2 class="font-heading text-2xl text-bakery-dark">
                    {{ $t("common.actions") }}
                </h2>
                <div class="space-y-2 text-sm">
                    <p class="flex justify-between">
                        <span>{{ $t("admin_orders.fields.subtotal") }}</span
                        ><span>{{ formatCurrency(order.subtotal) }}</span>
                    </p>
                    <p class="flex justify-between">
                        <span>{{ $t("orders.fulfillment.delivery_fee") }}</span
                        ><span>{{ formatCurrency(order.delivery_fee) }}</span>
                    </p>
                    <p class="flex justify-between font-semibold text-bakery-dark">
                        <span>{{ $t("admin_orders.fields.total") }}</span
                        ><span>{{ formatCurrency(order.total) }}</span>
                    </p>
                </div>

                <form class="space-y-3" @submit.prevent="updateStatus">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">{{ $t("common.status") }}</label>
                        <Select v-model="statusForm.status" :options="statusOptions" class="w-full" />
                        <p v-if="statusForm.errors.status" class="text-xs text-red-700">
                            {{ statusForm.errors.status }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">{{
                            $t("admin_orders.fields.internal_notes")
                        }}</label>
                        <Textarea v-model="statusForm.internal_notes" rows="4" class="w-full" />
                        <p v-if="statusForm.errors.internal_notes" class="text-xs text-red-700">
                            {{ statusForm.errors.internal_notes }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">{{ $t("common.pickup_date") }}</label>
                        <BaseDatePicker v-model="statusForm.pickup_date" class="w-full" />
                        <p v-if="statusForm.errors.pickup_date" class="text-xs text-red-700">
                            {{ statusForm.errors.pickup_date }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">{{ $t("common.pickup_time_slot") }}</label>
                        <InputText
                            v-model="statusForm.pickup_time_slot"
                            class="w-full"
                            :placeholder="$t('admin_orders.placeholders.pickup_time_slot')"
                        />
                        <p v-if="statusForm.errors.pickup_time_slot" class="text-xs text-red-700">
                            {{ statusForm.errors.pickup_time_slot }}
                        </p>
                    </div>

                    <Button
                        type="submit"
                        :label="$t('admin_orders.actions.update_status')"
                        class="w-full"
                        :loading="statusForm.processing"
                        :disabled="statusForm.processing"
                    />
                </form>
            </aside>
        </div>
    </div>
</template>
