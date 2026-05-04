<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import Button from "primevue/button";
import InputNumber from "primevue/inputnumber";
import PublicLayout from "../../Layouts/PublicLayout.vue";
import { useConversionTracking } from "@/composables/useConversionTracking";

defineOptions({ layout: PublicLayout });

const props = defineProps({
    cart: {
        type: Object,
        required: true,
    },
});

const { trackCtaClick } = useConversionTracking();

const updateQuantity = (item, quantity) => {
    router.patch(
        route("cart.items.update", item.product_id),
        {
            quantity,
        },
        {
            preserveScroll: true,
        }
    );
};

const removeItem = (item) => {
    router.delete(route("cart.items.destroy", item.product_id), {
        preserveScroll: true,
    });
};

const clearCart = () => {
    router.delete(route("cart.clear"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('nav.cart')" />

    <section class="mx-auto max-w-5xl space-y-6">
        <header class="rounded-3xl border border-bakery-brown/15 bg-[#fff7eb] p-6 sm:p-8">
            <h1 class="font-heading text-4xl text-bakery-dark">Kosar</h1>
            <p class="mt-2 text-sm text-bakery-dark/75">
                {{ $t("cart.check_product") }}
            </p>
        </header>

        <section
            v-if="cart.summary.is_empty"
            class="rounded-2xl border border-dashed border-bakery-brown/30 bg-[#fcf7ef] p-10 text-center"
        >
            <h2 class="font-heading text-3xl text-bakery-dark">
                {{ $t("cart.empty") }}
            </h2>
            <p class="mt-3 text-sm text-bakery-dark/75">
                {{ $t("cart.choose_favorites") }}
            </p>
            <Link
                :href="route('weekly-menu')"
                class="mt-6 inline-flex rounded-full bg-bakery-brown px-6 py-3 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark"
                @click="
                    trackCtaClick('cart.empty_state_weekly_menu', {
                        funnel: 'cart',
                        step: 'empty_state',
                    })
                "
            >
                {{ $t("cart.view_weekly_menu") }}
            </Link>
        </section>

        <div v-else class="grid gap-6 lg:grid-cols-[1fr_20rem]">
            <div class="space-y-4">
                <article
                    v-for="item in cart.items"
                    :key="item.product_id"
                    class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <h3 class="font-heading text-2xl text-bakery-dark">
                                {{ item.name }}
                            </h3>
                            <p
                                v-if="item.short_description"
                                class="mt-1 text-sm text-bakery-dark/70"
                            >
                                {{ item.short_description }}
                            </p>
                            <p class="mt-2 text-sm font-semibold text-bakery-brown">
                                {{
                                    new Intl.NumberFormat("hu-HU").format(item.unit_price)
                                }}
                                Ft / db
                            </p>
                        </div>

                        <div class="flex flex-col items-end gap-2">
                            <InputNumber
                                :model-value="item.quantity"
                                show-buttons
                                button-layout="horizontal"
                                :min="1"
                                :max="99"
                                input-class="w-16 text-center"
                                @update:model-value="
                                    (value) => updateQuantity(item, value)
                                "
                            />
                            <Button
                                type="button"
                                unstyled
                                class="text-xs font-semibold text-rose-700 hover:underline"
                                @click="removeItem(item)"
                            >
                                {{ $t("common.delete") }}
                            </Button>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-bakery-brown/10 pt-4 text-right">
                        <p class="text-sm text-bakery-dark/70">Reszosszeg</p>
                        <p class="text-lg font-semibold text-bakery-dark">
                            {{ new Intl.NumberFormat("hu-HU").format(item.line_total) }}
                            Ft
                        </p>
                    </div>
                </article>
            </div>

            <aside
                class="h-fit rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5 shadow-sm"
            >
                <h2 class="font-heading text-2xl text-bakery-dark">
                    {{ $t("common.summary") }}
                </h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt>{{ $t("admin_weekly_menus.columns.items") }}</dt>
                        <dd>{{ cart.summary.total_quantity }} db</dd>
                    </div>
                    <div class="flex justify-between font-semibold text-bakery-dark">
                        <dt>{{ $t("admin_orders.columns.total") }}</dt>
                        <dd>
                            {{
                                new Intl.NumberFormat("hu-HU").format(cart.summary.total)
                            }}
                            Ft
                        </dd>
                    </div>
                </dl>

                <div class="mt-5 space-y-2">
                    <Link
                        :href="route('checkout.index')"
                        class="block w-full rounded-full bg-bakery-brown px-4 py-2 text-center text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark"
                        @click="
                            trackCtaClick('cart.proceed_to_checkout', {
                                funnel: 'cart',
                                step: 'proceed_checkout',
                            })
                        "
                    >
                        {{ $t("cart.proceed_to_checkout") }}
                    </Link>
                    <Button
                        type="button"
                        label="Kosar uritese"
                        severity="secondary"
                        outlined
                        class="w-full"
                        @click="clearCart"
                    />
                </div>
            </aside>
        </div>
    </section>
</template>
