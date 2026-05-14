<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Button from "primevue/button";
import Card from "primevue/card";
import Message from "primevue/message";
import Select from "primevue/select";

import CourierStatusBadge from "@/Components/Admin/Couriers/CourierStatusBadge.vue";

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    couriers: {
        type: Array,
        default: () => [],
    },
    canAssign: {
        type: Boolean,
        default: false,
    },
});

const finalDeliveryStatuses = ["delivered", "failed", "cancelled"];
const lockedOrderStatuses = ["completed", "cancelled"];

const isDelivery = computed(() => props.order.fulfillment_method === "delivery");
const deliveryStatus = computed(() => props.order.delivery_status || "pending");
const isLocked = computed(
    () => finalDeliveryStatuses.includes(deliveryStatus.value) || lockedOrderStatuses.includes(props.order.status)
);
const canSubmit = computed(() => props.canAssign && isDelivery.value && !isLocked.value);

const courierOptions = computed(() =>
    props.couriers.map((courier) => ({
        id: courier.id,
        name: courier.name,
        phone: courier.phone,
        status: courier.status,
        label: [courier.name, courier.phone].filter(Boolean).join(" - "),
    }))
);

const form = useForm({
    courier_id: props.order.courier?.id ?? null,
});

watch(
    () => props.order.courier?.id,
    (courierId) => {
        form.courier_id = courierId ?? null;
    }
);

const submit = () => {
    form.patch(route("admin.orders.assign-courier", props.order.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Card>
        <template #title>
            <span class="font-heading text-2xl text-bakery-dark">
                {{ $t("admin_orders.courier_assignment.title") }}
            </span>
        </template>

        <template #content>
            <div class="space-y-4">
                <Message v-if="!isDelivery" severity="info" :closable="false">
                    {{ $t("admin_orders.courier_assignment.pickup_disabled") }}
                </Message>
                <Message v-else-if="isLocked" severity="warn" :closable="false">
                    {{ $t("admin_orders.courier_assignment.locked") }}
                </Message>

                <div v-if="order.courier" class="rounded-lg border border-bakery-brown/10 bg-[#fff9f1] p-3">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-bakery-dark">
                                {{ order.courier.name }}
                            </p>
                            <p class="text-sm text-bakery-dark/65">
                                {{ order.courier.phone || "-" }}
                            </p>
                        </div>
                        <CourierStatusBadge :status="order.courier.status" />
                    </div>
                </div>
                <div
                    v-else
                    class="rounded-lg border border-dashed border-bakery-brown/20 bg-white/70 p-4 text-sm text-bakery-dark/70"
                >
                    {{ $t("admin_orders.courier_assignment.empty") }}
                </div>

                <form class="grid gap-3 md:grid-cols-[1fr_auto]" @submit.prevent="submit">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-bakery-dark">
                            {{ $t("admin_orders.courier_assignment.select_label") }}
                        </label>
                        <Select
                            v-model="form.courier_id"
                            :options="courierOptions"
                            option-label="label"
                            option-value="id"
                            class="w-full"
                            :disabled="!canSubmit || form.processing"
                            :placeholder="$t('admin_orders.courier_assignment.select_placeholder')"
                        />
                        <p v-if="form.errors.courier_id" class="text-xs text-red-700">
                            {{ form.errors.courier_id }}
                        </p>
                    </div>

                    <div class="flex items-end">
                        <Button
                            type="submit"
                            icon="pi pi-save"
                            :label="$t('common.save')"
                            :loading="form.processing"
                            :disabled="!canSubmit || form.processing"
                        />
                    </div>
                </form>
            </div>
        </template>
    </Card>
</template>
