<script setup>
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    menu: {
        type: Object,
        default: null,
    },
    groups: {
        type: Array,
        default: () => [],
    },
    fallbackUsed: {
        type: Boolean,
        default: false,
    },
});

const cartForm = useForm({
    product_id: null,
    quantity: 1,
});

const addToCart = (productId) => {
    cartForm.product_id = productId;

    cartForm.post('/cart/items', {
        preserveScroll: true,
    });
};
</script>

<template>
    <section v-if="menu" class="space-y-8">
        <div class="rounded-3xl border border-bakery-brown/15 bg-[#fff7eb] p-6 sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-bakery-gold">Aktuális heti menü</p>
            <h2 class="mt-2 font-heading text-3xl text-bakery-dark sm:text-4xl">{{ menu.title }}</h2>
            <p class="mt-2 text-sm text-bakery-dark/75">{{ menu.week_start }} - {{ menu.week_end }}</p>
            <p v-if="menu.public_note" class="mt-4 text-sm text-bakery-dark/80">{{ menu.public_note }}</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <Link href="/cart" class="rounded-full border border-bakery-brown/35 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream">
                    Kosar megnyitasa
                </Link>
                <Link href="/checkout" class="rounded-full bg-bakery-brown px-4 py-2 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark">
                    Penztar
                </Link>
            </div>
            <p v-if="fallbackUsed" class="mt-4 rounded-xl bg-amber-100 px-3 py-2 text-xs font-medium text-amber-800">
                Jelenleg nincs aktív heti menü a mai napra, ezért a legutóbb publikált menü látható.
            </p>
        </div>

        <div class="space-y-6">
            <article v-for="group in groups" :key="group.category_name" class="space-y-3">
                <h3 class="font-heading text-2xl text-bakery-dark">{{ group.category_name }}</h3>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="item in group.items" :key="item.id" class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-heading text-2xl text-bakery-dark">{{ item.name }}</p>
                            <span v-if="item.badge_text" class="rounded-full bg-bakery-gold/20 px-2 py-1 text-xs font-semibold text-bakery-brown">
                                {{ item.badge_text }}
                            </span>
                        </div>
                        <p v-if="item.short_description" class="mt-2 text-sm text-bakery-dark/75">{{ item.short_description }}</p>
                        <p class="mt-4 text-sm font-semibold text-bakery-brown">{{ new Intl.NumberFormat('hu-HU').format(item.price) }} Ft</p>
                        <p v-if="item.stock_note" class="mt-1 text-xs text-bakery-dark/60">{{ item.stock_note }}</p>
                        <button
                            type="button"
                            class="mt-4 inline-flex rounded-full bg-bakery-brown px-4 py-2 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark disabled:cursor-not-allowed disabled:opacity-70"
                            :disabled="cartForm.processing"
                            @click="addToCart(item.product_id)"
                        >
                            Kosarba
                        </button>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section v-else class="rounded-2xl border border-dashed border-bakery-brown/30 bg-[#fcf7ef] p-8 text-center">
        <h3 class="font-heading text-3xl text-bakery-dark">Heti menu feltoltes alatt</h3>
        <p class="mt-3 text-sm text-bakery-dark/75">
            Jelenleg nincs publikált heti menü. Nézz vissza hamarosan, amint frissül a következő ajánlat.
        </p>
    </section>
</template>
