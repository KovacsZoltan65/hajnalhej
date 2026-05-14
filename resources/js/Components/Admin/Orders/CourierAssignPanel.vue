<script setup>
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import Button from "primevue/button";
import Textarea from "primevue/textarea";

import DeliveryStatusBadge from "@/Components/Admin/Orders/DeliveryStatusBadge.vue";

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const status = computed(() => props.order.delivery_status || "pending");
const finalStatuses = ["delivered", "failed", "cancelled"];

const canAssign = computed(() => ["pending", "assigned"].includes(status.value));
const canStart = computed(() => status.value === "assigned");
const canMarkDelivered = computed(() => status.value === "out_for_delivery");
const canMarkFailed = computed(() => ["assigned", "out_for_delivery"].includes(status.value));
const canCancel = computed(() => !finalStatuses.includes(status.value));

const workflowForm = useForm({});
const failedForm = useForm({
    failed_delivery_reason: "",
});

const startDelivery = () => {
    workflowForm.post(route("admin.orders.delivery.start", props.order.id), {
        preserveScroll: true,
    });
};

const markDelivered = () => {
    workflowForm.post(route("admin.orders.delivery.delivered", props.order.id), {
        preserveScroll: true,
    });
};

const markFailed = () => {
    failedForm.post(route("admin.orders.delivery.failed", props.order.id), {
        preserveScroll: true,
        onSuccess: () => failedForm.reset(),
    });
};

const cancelDelivery = () => {
    workflowForm.post(route("admin.orders.delivery.cancel", props.order.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-heading text-2xl text-bakery-dark">
                {{ $t("delivery.panel_title") }}
            </h2>
            <DeliveryStatusBadge :status="order.delivery_status" :label="order.delivery_status_label" />
        </div>

        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-bakery-dark/60">{{ $t("delivery.fields.courier") }}</dt>
                <dd class="font-semibold text-bakery-dark">
                    {{ order.courier?.name || "-" }}
                </dd>
            </div>
            <div>
                <dt class="text-bakery-dark/60">{{ $t("delivery.fields.delivery_scheduled_at") }}</dt>
                <dd class="font-semibold text-bakery-dark">
                    {{ order.delivery_scheduled_at || "-" }}
                </dd>
            </div>
            <div>
                <dt class="text-bakery-dark/60">{{ $t("delivery.fields.out_for_delivery_at") }}</dt>
                <dd class="font-semibold text-bakery-dark">
                    {{ order.out_for_delivery_at || "-" }}
                </dd>
            </div>
            <div>
                <dt class="text-bakery-dark/60">{{ $t("delivery.fields.delivered_at") }}</dt>
                <dd class="font-semibold text-bakery-dark">
                    {{ order.delivered_at || "-" }}
                </dd>
            </div>
        </dl>

        <div class="mt-4 flex flex-wrap gap-2">
            <Button
                v-if="canStart"
                icon="pi pi-truck"
                :label="$t('delivery.actions.start')"
                :loading="workflowForm.processing"
                :disabled="workflowForm.processing"
                @click="startDelivery"
            />
            <Button
                v-if="canMarkDelivered"
                icon="pi pi-check"
                severity="success"
                :label="$t('delivery.actions.mark_delivered')"
                :loading="workflowForm.processing"
                :disabled="workflowForm.processing"
                @click="markDelivered"
            />
            <Button
                v-if="canCancel"
                icon="pi pi-times"
                severity="secondary"
                outlined
                :label="$t('delivery.actions.cancel')"
                :loading="workflowForm.processing"
                :disabled="workflowForm.processing"
                @click="cancelDelivery"
            />
        </div>

        <form v-if="canMarkFailed" class="mt-4 space-y-3" @submit.prevent="markFailed">
            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">{{
                    $t("delivery.fields.failed_delivery_reason")
                }}</label>
                <Textarea v-model="failedForm.failed_delivery_reason" rows="3" class="w-full" />
                <p v-if="failedForm.errors.failed_delivery_reason" class="text-xs text-red-700">
                    {{ failedForm.errors.failed_delivery_reason }}
                </p>
            </div>
            <Button
                type="submit"
                icon="pi pi-exclamation-triangle"
                severity="danger"
                outlined
                :label="$t('delivery.actions.mark_failed')"
                :loading="failedForm.processing"
                :disabled="failedForm.processing"
            />
        </form>

        <p v-if="order.failed_delivery_reason" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">
            <span class="font-semibold">{{ $t("delivery.fields.failed_delivery_reason") }}:</span>
            {{ order.failed_delivery_reason }}
        </p>
    </section>
</template>
