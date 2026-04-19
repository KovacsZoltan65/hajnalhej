<script setup>
import { Head, Link } from '@inertiajs/vue3';
import HeroSection from '../Components/HeroSection.vue';
import SectionTitle from '../Components/SectionTitle.vue';
import PublicLayout from '../Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const featured = [
    {
        title: 'Klasszikus kovaszos kenyer',
        note: 'Hosszu fermentacio, ropogos hej, laza belso.',
        price: '2 450 Ft',
    },
    {
        title: 'Rozmaringos focaccia',
        note: 'Extra szuz olivaval, tengeri soval.',
        price: '1 990 Ft',
    },
    {
        title: 'Fahejas tekercs',
        note: 'Lagy teszta, barna cukor, vajkrem.',
        price: '1 350 Ft',
    },
];
</script>

<template>
    <Head title="Ropogos reggelek." />

    <div class="space-y-16">
        <HeroSection />

        <section class="space-y-8">
            <SectionTitle
                eyebrow="Brand Story"
                title="Egy kis budapesti pekseg, ahol a reggel lassan kezdodik."
                description="A Hajnalhej nem futoszalag. Minden sutest ugy tervezunk, hogy a termekek pontosan akkor erkezzenek, amikor a varos ebred."
            />
            <div class="grid gap-4 md:grid-cols-3">
                <article class="glass-bakery rounded-2xl p-5">
                    <p class="font-heading text-2xl text-bakery-brown">01</p>
                    <p class="mt-2 text-sm text-bakery-dark/80">Kovaszunkat naponta etetjuk, hogy az iz karakteres maradjon.</p>
                </article>
                <article class="glass-bakery rounded-2xl p-5">
                    <p class="font-heading text-2xl text-bakery-brown">02</p>
                    <p class="mt-2 text-sm text-bakery-dark/80">Kis szeriaban sutunk, hogy minden tetel friss legyen.</p>
                </article>
                <article class="glass-bakery rounded-2xl p-5">
                    <p class="font-heading text-2xl text-bakery-brown">03</p>
                    <p class="mt-2 text-sm text-bakery-dark/80">A menu hetente valtozik a szezon szerint.</p>
                </article>
            </div>
        </section>

        <section class="space-y-8">
            <SectionTitle
                eyebrow="Kiemelt termekek"
                title="Hetrol hetre valogatott kedvencek"
                description="A teljes kinalatot a heti menuben talalod meg, itt csak egy kis izelito."
            />
            <div class="grid gap-4 md:grid-cols-3">
                <article
                    v-for="item in featured"
                    :key="item.title"
                    class="rounded-2xl border border-bakery-brown/15 bg-[#fffaf2] p-5 shadow-sm"
                >
                    <h3 class="font-heading text-2xl text-bakery-dark">{{ item.title }}</h3>
                    <p class="mt-2 text-sm text-bakery-dark/75">{{ item.note }}</p>
                    <p class="mt-4 text-sm font-semibold text-bakery-brown">{{ item.price }}</p>
                </article>
            </div>
        </section>

        <section class="rounded-3xl bg-bakery-brown p-8 text-bakery-cream sm:p-10">
            <p class="text-xs uppercase tracking-[0.22em] text-bakery-gold">Heti menu</p>
            <h2 class="mt-3 font-heading text-3xl sm:text-4xl">Foglalj elore, hogy biztosan jusson a kedvencedbol.</h2>
            <p class="mt-3 max-w-2xl text-bakery-cream/85">
                A rendeleseket heti menuben nyitjuk meg. A mennyiseg limitalt, atvetel elore valasztott idosavban.
            </p>
            <Link
                href="/weekly-menu"
                class="mt-6 inline-flex rounded-full bg-bakery-gold px-6 py-3 text-sm font-semibold text-bakery-dark transition hover:bg-[#edbb5a]"
            >
                Heti menu megtekintese
            </Link>
        </section>
    </div>
</template>
