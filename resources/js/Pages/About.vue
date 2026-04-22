<script setup>
import { Head } from "@inertiajs/vue3";
import SectionTitle from "../Components/SectionTitle.vue";
import PublicLayout from "../Layouts/PublicLayout.vue";

defineOptions({ layout: PublicLayout });
</script>

<template>
    <Head title="Rólunk" />

    <section class="space-y-8">
        <SectionTitle
            eyebrow="Rólunk"
            title="Hajnalhéj: modern pékség, klasszikus alapokkal."
            description="Budapest belvárosaban sütünk, kis mennyiségben, minőségi alapanyagokkal és lassú folyamatokkal."
        />

        <div
            class="rounded-2xl border border-bakery-brown/15 bg-white/70 p-6 leading-relaxed text-bakery-dark/80"
        >
            <p>
                A filozófiánk röviden: Kovász. Idő. Türelem. Ez határozza meg a tészta
                állagát, az aromát és a mindennapi minőséget.
            </p>
            <p class="mt-4">
                Ebben a fázisban az oldal célja a stabil alap létrehozása. A következö
                modulokban érkezik a teljes menü-, rendelés- és admin funkcionalitás.
            </p>
        </div>
    </section>
</template>
