<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import InputMask from "primevue/inputmask";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import BaseDatePicker from "@/Components/BaseDatePicker.vue";
import PublicLayout from "../../Layouts/PublicLayout.vue";
import { useConversionTracking } from "@/composables/useConversionTracking";
import { useLocaleFormat } from "@/composables/useLocaleFormat";
import { trans } from "laravel-vue-i18n";

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
    fulfillmentOptions: {
        type: Array,
        default: () => [],
    },
    pickupBranches: {
        type: Array,
        default: () => [],
    },
});

const { trackCtaClick, trackFunnel } = useConversionTracking();
const { formatCurrency } = useLocaleFormat();

const emptyAddress = {
    name: "",
    country: "Magyarország",
    postal_code: "",
    city: "",
    street: "",
    house_number: "",
    floor: "",
    door: "",
    company_name: "",
    tax_number: "",
    phone: "",
    notes: "",
};

const addressFields = [
    { name: "name", label: trans("fields.name"), span: "sm:col-span-2" },
    { name: "country", label: trans("fields.country") },
    { name: "postal_code", label: trans("orders.address.postal_code") },
    { name: "city", label: trans("orders.address.city") },
    { name: "street", label: trans("orders.address.street") },
    { name: "house_number", label: trans("orders.address.house_number") },
    { name: "floor", label: trans("orders.address.floor") },
    { name: "door", label: trans("orders.address.door") },
    { name: "company_name", label: trans("orders.address.company_name") },
    { name: "tax_number", label: trans("orders.address.tax_number") },
    { name: "phone", label: trans("common.phone_number") },
    {
        name: "notes",
        label: trans("common.notes"),
        span: "sm:col-span-2",
        textarea: true,
    },
];

const form = useForm({
    customer_name: props.prefill.customer_name,
    customer_email: props.prefill.customer_email,
    customer_phone: props.prefill.customer_phone,
    notes: props.prefill.notes,
    pickup_date: props.prefill.pickup_date,
    pickup_time_slot: props.prefill.pickup_time_slot,
    fulfillment_method: props.prefill.fulfillment_method ?? "pickup",
    pickup_branch_id: props.prefill.pickup_branch_id,
    billing_address: { ...emptyAddress, ...(props.prefill.billing_address ?? {}) },
    shipping_address: { ...emptyAddress, ...(props.prefill.shipping_address ?? {}) },
    same_as_billing: props.prefill.same_as_billing ?? true,
    delivery_notes: props.prefill.delivery_notes ?? "",
    accept_privacy: false,
    accept_terms: false,
});

const isPickup = computed(() => form.fulfillment_method === "pickup");
const isDelivery = computed(() => form.fulfillment_method === "delivery");

const pickupBranchOptions = computed(() =>
    props.pickupBranches.map((branch) => ({
        ...branch,
        label: branch.address ? `${branch.name} - ${branch.address}` : branch.name,
    }))
);

const fieldError = (group, field) => form.errors[`${group}.${field}`];

const submit = () => {
    trackFunnel("checkout.submitted", {
        funnel: "checkout",
        step: "submit",
        metadata: {
            total: props.cart.summary?.total ?? null,
            items_count: props.cart.summary?.items_count ?? null,
            has_notes: form.notes.trim().length > 0,
            fulfillment_method: form.fulfillment_method,
        },
    });

    form.post(route("checkout.store"));
};
</script>

<template>
    <Head :title="$t('common.checkout')" />

    <section class="mx-auto max-w-6xl space-y-6">
        <header class="rounded-3xl border border-bakery-brown/15 bg-[#fff7eb] p-6 sm:p-8">
            <h1 class="font-heading text-4xl text-bakery-dark">
                {{ $t("common.checkout") }}
            </h1>
            <p class="mt-2 text-sm text-bakery-dark/75">
                {{ $t("checkout.description") }}
            </p>
        </header>

        <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
            <form class="space-y-5 rounded-2xl border border-bakery-brown/15 bg-white/80 p-5" @submit.prevent="submit">
                <section class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2 sm:col-span-2">
                        <label for="customer_name" class="text-sm font-medium text-bakery-dark">{{
                            $t("fields.name")
                        }}</label>
                        <InputText
                            id="customer_name"
                            v-model="form.customer_name"
                            class="w-full"
                            :invalid="Boolean(form.errors.customer_name)"
                        />
                        <p v-if="form.errors.customer_name" class="text-xs text-red-700">
                            {{ form.errors.customer_name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="customer_email" class="text-sm font-medium text-bakery-dark">Email</label>
                        <InputText
                            id="customer_email"
                            v-model="form.customer_email"
                            type="email"
                            class="w-full"
                            :invalid="Boolean(form.errors.customer_email)"
                        />
                        <p v-if="form.errors.customer_email" class="text-xs text-red-700">
                            {{ form.errors.customer_email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="customer_phone" class="text-sm font-medium text-bakery-dark">{{
                            $t("common.phone_number")
                        }}</label>
                        <InputMask
                            id="customer_phone"
                            v-model="form.customer_phone"
                            mask="+36999999999"
                            class="w-full"
                            :invalid="Boolean(form.errors.customer_phone)"
                        />
                        <p v-if="form.errors.customer_phone" class="text-xs text-red-700">
                            {{ form.errors.customer_phone }}
                        </p>
                    </div>
                </section>

                <section class="space-y-3 border-t border-bakery-brown/10 pt-5">
                    <h2 class="font-heading text-2xl text-bakery-dark">
                        {{ $t("orders.fulfillment.title") }}
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label for="fulfillment_method" class="text-sm font-medium text-bakery-dark">
                                {{ $t("orders.fulfillment.method") }}
                            </label>
                            <Select
                                id="fulfillment_method"
                                v-model="form.fulfillment_method"
                                :options="fulfillmentOptions"
                                option-label="label"
                                option-value="value"
                                class="w-full"
                                :invalid="Boolean(form.errors.fulfillment_method)"
                            />
                            <p v-if="form.errors.fulfillment_method" class="text-xs text-red-700">
                                {{ form.errors.fulfillment_method }}
                            </p>
                        </div>

                        <div v-if="isPickup" class="space-y-2">
                            <label for="pickup_branch_id" class="text-sm font-medium text-bakery-dark">
                                {{ $t("orders.fulfillment.pickup_branch") }}
                            </label>
                            <Select
                                id="pickup_branch_id"
                                v-model="form.pickup_branch_id"
                                :options="pickupBranchOptions"
                                option-label="label"
                                option-value="id"
                                class="w-full"
                                :placeholder="$t('orders.fulfillment.select_pickup_branch')"
                                :invalid="Boolean(form.errors.pickup_branch_id)"
                            />
                            <p v-if="form.errors.pickup_branch_id" class="text-xs text-red-700">
                                {{ form.errors.pickup_branch_id }}
                            </p>
                        </div>

                        <div v-if="isPickup" class="space-y-2">
                            <label for="pickup_date" class="text-sm font-medium text-bakery-dark">
                                {{ $t("common.pickup_date") }}
                            </label>
                            <BaseDatePicker
                                input-id="pickup_date"
                                v-model="form.pickup_date"
                                class="w-full"
                                :invalid="Boolean(form.errors.pickup_date)"
                            />
                            <p v-if="form.errors.pickup_date" class="text-xs text-red-700">
                                {{ form.errors.pickup_date }}
                            </p>
                        </div>

                        <div v-if="isPickup" class="space-y-2">
                            <label for="pickup_time_slot" class="text-sm font-medium text-bakery-dark">
                                {{ $t("common.pickup_time_slot") }}
                            </label>
                            <InputText
                                id="pickup_time_slot"
                                v-model="form.pickup_time_slot"
                                placeholder="pl. 08:00-10:00"
                                class="w-full"
                                :invalid="Boolean(form.errors.pickup_time_slot)"
                            />
                            <p v-if="form.errors.pickup_time_slot" class="text-xs text-red-700">
                                {{ form.errors.pickup_time_slot }}
                            </p>
                        </div>

                        <div v-if="isDelivery" class="space-y-2 sm:col-span-2">
                            <label for="delivery_notes" class="text-sm font-medium text-bakery-dark">
                                {{ $t("orders.fulfillment.delivery_notes") }}
                            </label>
                            <Textarea
                                id="delivery_notes"
                                v-model="form.delivery_notes"
                                rows="3"
                                class="w-full"
                                :invalid="Boolean(form.errors.delivery_notes)"
                            />
                            <p v-if="form.errors.delivery_notes" class="text-xs text-red-700">
                                {{ form.errors.delivery_notes }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="space-y-3 border-t border-bakery-brown/10 pt-5">
                    <h2 class="font-heading text-2xl text-bakery-dark">
                        {{ $t("orders.address.billing") }}
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="field in addressFields"
                            :key="`billing-${field.name}`"
                            class="space-y-2"
                            :class="field.span"
                        >
                            <label :for="`billing_${field.name}`" class="text-sm font-medium text-bakery-dark">
                                {{ field.label }}
                            </label>
                            <Textarea
                                v-if="field.textarea"
                                :id="`billing_${field.name}`"
                                v-model="form.billing_address[field.name]"
                                rows="3"
                                class="w-full"
                                :invalid="Boolean(fieldError('billing_address', field.name))"
                            />
                            <InputText
                                v-else
                                :id="`billing_${field.name}`"
                                v-model="form.billing_address[field.name]"
                                class="w-full"
                                :invalid="Boolean(fieldError('billing_address', field.name))"
                            />
                            <p v-if="fieldError('billing_address', field.name)" class="text-xs text-red-700">
                                {{ fieldError("billing_address", field.name) }}
                            </p>
                        </div>
                    </div>
                </section>

                <section v-if="isDelivery" class="space-y-3 border-t border-bakery-brown/10 pt-5">
                    <label
                        for="same_as_billing"
                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-bakery-brown/15 px-3 py-2 text-sm text-bakery-dark/85"
                    >
                        <Checkbox input-id="same_as_billing" v-model="form.same_as_billing" binary class="mt-0.5" />
                        <span>{{ $t("orders.address.same_as_billing") }}</span>
                    </label>
                    <p v-if="form.errors.same_as_billing" class="text-xs text-red-700">
                        {{ form.errors.same_as_billing }}
                    </p>

                    <div v-if="!form.same_as_billing" class="space-y-3">
                        <h2 class="font-heading text-2xl text-bakery-dark">Szállítási cím</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="field in addressFields"
                                :key="`shipping-${field.name}`"
                                class="space-y-2"
                                :class="field.span"
                            >
                                <label :for="`shipping_${field.name}`" class="text-sm font-medium text-bakery-dark">
                                    {{ field.label }}
                                </label>
                                <Textarea
                                    v-if="field.textarea"
                                    :id="`shipping_${field.name}`"
                                    v-model="form.shipping_address[field.name]"
                                    rows="3"
                                    class="w-full"
                                    :invalid="Boolean(fieldError('shipping_address', field.name))"
                                />
                                <InputText
                                    v-else
                                    :id="`shipping_${field.name}`"
                                    v-model="form.shipping_address[field.name]"
                                    class="w-full"
                                    :invalid="Boolean(fieldError('shipping_address', field.name))"
                                />
                                <p v-if="fieldError('shipping_address', field.name)" class="text-xs text-red-700">
                                    {{ fieldError("shipping_address", field.name) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-3 border-t border-bakery-brown/10 pt-5">
                    <div class="space-y-2">
                        <label for="notes" class="text-sm font-medium text-bakery-dark">{{
                            $t("orders.address.notes")
                        }}</label>
                        <Textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            class="w-full"
                            :invalid="Boolean(form.errors.notes)"
                        />
                        <p v-if="form.errors.notes" class="text-xs text-red-700">
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </section>

                <label
                    for="accept_privacy"
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-bakery-brown/15 px-3 py-2 text-sm text-bakery-dark/85"
                >
                    <Checkbox input-id="accept_privacy" v-model="form.accept_privacy" binary class="mt-0.5" />
                    <span>{{ $t("orders.accept_processing_info") }}</span>
                </label>
                <p v-if="form.errors.accept_privacy" class="text-xs text-red-700">
                    {{ form.errors.accept_privacy }}
                </p>

                <label
                    for="accept_terms"
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-bakery-brown/15 px-3 py-2 text-sm text-bakery-dark/85"
                >
                    <Checkbox input-id="accept_terms" v-model="form.accept_terms" binary class="mt-0.5" />
                    <span>{{ $t("orders.accept_terms_and_conditions") }}</span>
                </label>
                <p v-if="form.errors.accept_terms" class="text-xs text-red-700">
                    {{ form.errors.accept_terms }}
                </p>

                <Button
                    type="submit"
                    :label="$t('orders.place_an_order')"
                    class="w-full"
                    :loading="form.processing"
                    :disabled="form.processing"
                />
            </form>

            <aside class="h-fit rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5 shadow-sm">
                <h2 class="font-heading text-2xl text-bakery-dark">
                    {{ $t("orders.order_summary") }}
                </h2>
                <ul class="mt-4 space-y-3 text-sm">
                    <li
                        v-for="item in cart.items"
                        :key="item.product_id"
                        class="flex items-start justify-between gap-3"
                    >
                        <div>
                            <p class="font-medium text-bakery-dark">{{ item.name }}</p>
                            <p class="text-xs text-bakery-dark/70">
                                {{ item.quantity }} x
                                {{ formatCurrency(item.unit_price) }}
                            </p>
                        </div>
                        <p class="font-semibold text-bakery-dark">
                            {{ formatCurrency(item.line_total) }}
                        </p>
                    </li>
                </ul>

                <div class="mt-5 border-t border-bakery-brown/10 pt-4 text-sm">
                    <div class="flex justify-between font-semibold text-bakery-dark">
                        <span>{{ $t("admin_orders.columns.total") }}</span>
                        <span>{{ formatCurrency(cart.summary.total) }}</span>
                    </div>
                </div>

                <Link
                    :href="route('cart.index')"
                    class="mt-4 inline-flex text-sm font-semibold text-bakery-brown hover:underline"
                    @click="
                        trackCtaClick('checkout.back_to_cart', {
                            funnel: 'checkout',
                            step: 'back_to_cart',
                        })
                    "
                >
                    {{ $t("orders.back_to_cart") }}
                </Link>
            </aside>
        </div>
    </section>
</template>
