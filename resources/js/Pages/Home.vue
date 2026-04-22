<script setup>
import { Head, Link } from "@inertiajs/vue3";
import HeroSection from "@/Components/HeroSection.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";

defineOptions({ layout: PublicLayout });

const featured = [
    {
        title: "Klasszikus kovászos kenyér",
        note: "Hosszú fermentáció, ropogós héj, laza belső.",
        price: "2 450 Ft",
    },
    {
        title: "Rozmaringos focaccia",
        note: "Extra szűz olivával, tengeri sóval.",
        price: "1 990 Ft",
    },
    {
        title: "Fahéjas tekercs",
        note: "Lágy tészta, barna cukor, vajkrém.",
        price: "1 350 Ft",
    },
];
</script>

<template>
    <Head title="Ropogós reggelek." />

    <div class="space-y-16">
        <HeroSection />

        <section class="space-y-8">
            <SectionTitle
                eyebrow="Brand Story"
                title="Egy kis budapesti pékség, ahol a reggel lassan kezdődik."
                description="A Hajnalhéj nem futószalag. Minden sütést úgy tervezünk, hogy a termékek pontosan akkor érkezzenek, amikor a város ébred."
            />
            <div class="grid gap-4 md:grid-cols-3">
                <article class="glass-bakery rounded-2xl p-5">
                    <p class="font-heading text-2xl text-bakery-brown">01</p>
                    <p class="mt-2 text-sm text-bakery-dark/80">
                        Kovászunkat naponta etetjük, hogy az íz karakteres maradjon.
                    </p>
                </article>
                <article class="glass-bakery rounded-2xl p-5">
                    <p class="font-heading text-2xl text-bakery-brown">02</p>
                    <p class="mt-2 text-sm text-bakery-dark/80">
                        Kis szériában sütünk, hogy minden tétel friss legyen.
                    </p>
                </article>
                <article class="glass-bakery rounded-2xl p-5">
                    <p class="font-heading text-2xl text-bakery-brown">03</p>
                    <p class="mt-2 text-sm text-bakery-dark/80">
                        A menü hetente változik a szezon szerint.
                    </p>
                </article>
            </div>
        </section>

        <section class="space-y-8">
            <SectionTitle
                eyebrow="Kiemelt termékek"
                title="Hétről hétre válogatott kedvencek"
                description="A teljes kinálatot a heti menűben találod meg, itt csak egy kis ízelítő."
            />
            <div class="grid gap-4 md:grid-cols-3">
                <article
                    v-for="item in featured"
                    :key="item.title"
                    class="rounded-2xl border border-bakery-brown/15 bg-[#fffaf2] p-5 shadow-sm"
                >
                    <h3 class="font-heading text-2xl text-bakery-dark">
                        {{ item.title }}
                    </h3>
                    <p class="mt-2 text-sm text-bakery-dark/75">{{ item.note }}</p>
                    <p class="mt-4 text-sm font-semibold text-bakery-brown">
                        {{ item.price }}
                    </p>
                </article>
            </div>
        </section>

        <section class="rounded-3xl bg-bakery-brown p-8 text-bakery-cream sm:p-10">
            <p class="text-xs uppercase tracking-[0.22em] text-bakery-gold">Heti menu</p>
            <h2 class="mt-3 font-heading text-3xl sm:text-4xl">
                Foglalj előre, hogy biztosan jusson a kedvencedből.
            </h2>
            <p class="mt-3 max-w-2xl text-bakery-cream/85">
                A rendeléseket heti menüben nyitjuk meg. A mennyiség limitált, átvétel
                előre választott idősávban.
            </p>
            <Link
                href="/weekly-menu"
                class="mt-6 inline-flex rounded-full bg-bakery-gold px-6 py-3 text-sm font-semibold text-bakery-dark transition hover:bg-[#edbb5a]"
            >
                Heti menü megtekintése
            </Link>
        </section>
    </div>
</template>
