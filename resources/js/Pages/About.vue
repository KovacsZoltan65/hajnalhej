<script setup>
import { Head } from '@inertiajs/vue3';
import SectionTitle from '../Components/SectionTitle.vue';
import PublicLayout from '../Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });
</script>

<template>
    <Head title="Rolunk" />

    <section class="space-y-8">
        <SectionTitle
            eyebrow="Rolunk"
            title="Hajnalhej: modern pekseg, klasszikus alapokkal."
            description="Budapest belvarosaban sutunk, kis mennyisegben, minosegi alapanyagokkal es lassu folyamatokkal."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/70 p-6 leading-relaxed text-bakery-dark/80">
            <p>
                A filozofiank roviden: Kovasz. Ido. Turelem. Ez hatarozza meg a teszta allagat, az aromat es a mindennapi minoseget.
            </p>
            <p class="mt-4">
                Ebben a fazisban az oldal celja a stabil alap letrehozasa. A kovetkezo modulokban erkezik a teljes menu-, rendeles-
                es admin funkcionalitas.
            </p>
        </div>
    </section>
</template>
