<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SectionTitle from '@/Components/SectionTitle.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const heroHighlights = [
    {
        label: 'Kovászolt tételek',
        value: '18+ óra',
    },
    {
        label: 'Heti limitált batch',
        value: 'Hetente',
    },
    {
        label: 'Átvételi pontosság',
        value: '15 perc',
    },
];

const trustPillars = [
    'Kis szériás sütés, minden hajnalban frissítve',
    'Átlátható összetevők, tiszta receptek',
    'Előrendeléssel gyorsabb átvétel, sorban állás nélkül',
    'Budapesti artisan műhely, prémium minőség',
];

const bestsellers = [
    {
        title: 'Kovászos fehér vekni',
        note: 'Ropogós héj, nedves bélzet, 24 órás fermentáció.',
        price: '2 450 Ft',
        tag: 'Legnépszerűbb',
    },
    {
        title: 'Vajas-foszlós kalács',
        note: 'Lágy szerkezet, aranybarna kéreg, ünnepi reggelekhez.',
        price: '2 190 Ft',
        tag: 'Limitált',
    },
    {
        title: 'Rozmaringos focaccia',
        note: 'Extra szűz olívaolajjal, tengeri sópelyhekkel.',
        price: '1 990 Ft',
        tag: 'Gyorsan fogy',
    },
];

const steps = [
    {
        title: 'Válassz a heti menüből',
        text: 'Minden héten friss kínálatot publikálunk, pontos készletkerettel.',
    },
    {
        title: 'Foglalj 1 perc alatt',
        text: 'Regisztráció után a checkout gyors, a kedvenc adataid mentve maradnak.',
    },
    {
        title: 'Vedd át frissen',
        text: 'A választott idősávban előkészítve vár a rendelésed, stressz nélkül.',
    },
];

const testimonials = [
    {
        quote: '“A kovászos vekni minden héten ugyanazt a magas szintet hozza. Ritka stabil minőség.”',
        name: 'Réka',
        role: 'XIII. kerület',
    },
    {
        quote: '“A rendelés és átvétel annyira gördülékeny, hogy hétköznap reggel is belefér.”',
        name: 'Márk',
        role: 'II. kerület',
    },
    {
        quote: '“A péksütemények íze tényleg prémium, nem tömegtermék-hangulat.”',
        name: 'Nóra',
        role: 'XI. kerület',
    },
];

const faqs = [
    {
        question: 'Meddig tudok rendelni az aktuális hétre?',
        answer: 'A heti menüben jelzett készlet erejéig, jellemzően az átvételi napot megelőző estig.',
    },
    {
        question: 'Kell regisztráció a rendeléshez?',
        answer: 'A gyors és kényelmes visszatérő rendeléshez erősen ajánlott, de a folyamat vendégként is indítható.',
    },
    {
        question: 'Mi történik, ha lekésem az átvételi sávot?',
        answer: 'Az üzlet nyitvatartásán belül rövid türelmi idővel számolunk, de érdemes jelezni nekünk előre.',
    },
];
</script>

<template>
    <Head title="Prémium artisan pékség Budapesten" />

    <div class="space-y-14 md:space-y-16">
        <section class="ui-card ui-card-elevated relative overflow-hidden p-6 sm:p-8 lg:p-10">
            <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-bakery-gold/20 blur-3xl" />
            <div class="absolute -bottom-20 -left-12 h-64 w-64 rounded-full bg-bakery-brown/12 blur-3xl" />

            <div class="relative grid gap-8 lg:grid-cols-[1.25fr_0.95fr] lg:items-start">
                <div class="space-y-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-bakery-gold">
                        Hajnalhéj Bakery | Budapest
                    </p>
                    <h1 class="font-heading text-[2.1rem] leading-tight text-bakery-dark sm:text-5xl">
                        Ropogós reggelek.
                        <span class="block text-bakery-brown">Prémium artisan pékáru előrendeléssel.</span>
                    </h1>
                    <p class="max-w-2xl text-base leading-relaxed text-bakery-dark/78 sm:text-lg">
                        Lassú kelesztés, kis szériás sütés, pontos átvétel. A Hajnalhéjnál a minőség nem kompromisszum,
                        hanem rendszer.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <Link
                            href="/weekly-menu"
                            class="inline-flex min-h-11 items-center rounded-full bg-bakery-brown px-6 py-3 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark"
                        >
                            Heti menü megtekintése
                        </Link>
                        <Link
                            href="/register"
                            class="inline-flex min-h-11 items-center rounded-full bg-bakery-gold px-6 py-3 text-sm font-semibold text-bakery-dark transition hover:bg-[#edbb5a]"
                        >
                            Fiók létrehozása
                        </Link>
                        <Link
                            href="/cart"
                            class="inline-flex min-h-11 items-center rounded-full border border-bakery-brown/30 px-6 py-3 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                        >
                            Kosár megnyitása
                        </Link>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <article
                            v-for="item in heroHighlights"
                            :key="item.label"
                            class="ui-card-soft p-3"
                        >
                            <p class="text-[0.68rem] uppercase tracking-[0.16em] text-bakery-brown/70">{{ item.label }}</p>
                            <p class="mt-1 font-heading text-2xl text-bakery-dark">{{ item.value }}</p>
                        </article>
                    </div>
                </div>

                <aside class="ui-card-soft space-y-4 p-5 sm:p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-bakery-gold">Miért működik?</p>
                    <h2 class="font-heading text-3xl leading-tight text-bakery-dark">Kovász. Idő. Türelem.</h2>
                    <p class="text-sm leading-relaxed text-bakery-dark/75">
                        Minden tételt úgy készítünk, hogy az íz, állag és frissesség az átvétel pillanatában a csúcson legyen.
                    </p>
                    <ul class="space-y-2">
                        <li
                            v-for="pillar in trustPillars"
                            :key="pillar"
                            class="flex items-start gap-2 text-sm text-bakery-dark/80"
                        >
                            <span class="mt-1 inline-block h-2 w-2 rounded-full bg-bakery-gold" />
                            <span>{{ pillar }}</span>
                        </li>
                    </ul>
                    <Link
                        href="/about"
                        class="inline-flex min-h-11 items-center rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                    >
                        Ismerd meg a történetünket
                    </Link>
                </aside>
            </div>
        </section>

        <section class="space-y-7">
            <SectionTitle
                eyebrow="Bestseller választék"
                title="Gyorsan fogyó kedvencek minden héten"
                description="A teljes kínálat dinamikusan frissül, de ez a három tétel szinte minden hétre visszatér."
            />
            <div class="grid gap-4 md:grid-cols-3">
                <article
                    v-for="item in bestsellers"
                    :key="item.title"
                    class="ui-card p-5"
                >
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-heading text-2xl text-bakery-dark">{{ item.title }}</h3>
                        <span class="ui-badge bg-bakery-gold/20 text-bakery-brown">{{ item.tag }}</span>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed text-bakery-dark/75">{{ item.note }}</p>
                    <p class="mt-4 text-sm font-semibold text-bakery-brown">{{ item.price }}</p>
                    <Link
                        href="/weekly-menu"
                        class="mt-4 inline-flex min-h-11 items-center rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                    >
                        Érdekel, lefoglalom
                    </Link>
                </article>
            </div>
        </section>

        <section class="ui-card p-6 sm:p-8">
            <SectionTitle
                eyebrow="Így működik"
                title="Egyszerű rendelés, prémium élmény"
                description="A folyamat úgy lett kialakítva, hogy minimális időráfordítással stabilan magas minőséget kapj."
            />
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <article
                    v-for="(step, index) in steps"
                    :key="step.title"
                    class="ui-card-soft p-5"
                >
                    <p class="font-heading text-3xl text-bakery-brown">{{ String(index + 1).padStart(2, '0') }}</p>
                    <h3 class="mt-2 font-semibold text-bakery-dark">{{ step.title }}</h3>
                    <p class="mt-2 text-sm text-bakery-dark/75">{{ step.text }}</p>
                </article>
            </div>
        </section>

        <section class="space-y-7">
            <SectionTitle
                eyebrow="Vásárlói visszajelzések"
                title="A minőség nem ígéret, hanem rutin"
                description="Valódi visszajelzések törzsvásárlóktól."
            />
            <div class="grid gap-4 md:grid-cols-3">
                <article
                    v-for="item in testimonials"
                    :key="item.name"
                    class="ui-card p-5"
                >
                    <p class="text-sm leading-relaxed text-bakery-dark/80">{{ item.quote }}</p>
                    <p class="mt-4 text-sm font-semibold text-bakery-dark">{{ item.name }}</p>
                    <p class="text-xs text-bakery-dark/65">{{ item.role }}</p>
                </article>
            </div>
        </section>

        <section class="ui-card ui-card-elevated overflow-hidden bg-bakery-brown p-7 text-bakery-cream sm:p-10">
            <p class="text-xs uppercase tracking-[0.22em] text-bakery-gold">Heti menü nyitva</p>
            <h2 class="mt-3 font-heading text-3xl sm:text-4xl">
                Foglalj előre, hogy biztosan jusson a kedvencedből.
            </h2>
            <p class="mt-3 max-w-2xl text-bakery-cream/85">
                A mennyiség limitált, az átvétel idősávos. A legnépszerűbb tételek jellemzően 24-48 órán belül elfogynak.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <Link
                    href="/weekly-menu"
                    class="inline-flex min-h-11 items-center rounded-full bg-bakery-gold px-6 py-3 text-sm font-semibold text-bakery-dark transition hover:bg-[#edbb5a]"
                >
                    Heti menü megnyitása
                </Link>
                <Link
                    href="/checkout"
                    class="inline-flex min-h-11 items-center rounded-full border border-bakery-cream/35 px-6 py-3 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-cream/10"
                >
                    Ugrás a pénztárhoz
                </Link>
            </div>
        </section>

        <section class="ui-card p-6 sm:p-8">
            <SectionTitle
                eyebrow="Gyakori kérdések"
                title="Minden fontos egy helyen"
                description="Rendelés előtt gyors válaszok a leggyakoribb kérdésekre."
            />
            <div class="mt-6 space-y-3">
                <details
                    v-for="item in faqs"
                    :key="item.question"
                    class="ui-card-soft group p-4"
                >
                    <summary class="cursor-pointer list-none text-sm font-semibold text-bakery-dark">
                        {{ item.question }}
                    </summary>
                    <p class="mt-2 text-sm leading-relaxed text-bakery-dark/75">{{ item.answer }}</p>
                </details>
            </div>
        </section>

        <section class="ui-card p-6 text-center sm:p-8">
            <p class="text-xs uppercase tracking-[0.2em] text-bakery-gold">Készen állsz?</p>
            <h2 class="mt-2 font-heading text-3xl text-bakery-dark sm:text-4xl">Indítsd el az első rendelésed</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-bakery-dark/75 sm:text-base">
                Csatlakozz a visszatérő vásárlóinkhoz, és rendelj 1 perc alatt a heti kínálatból.
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <Link
                    href="/register"
                    class="inline-flex min-h-11 items-center rounded-full bg-bakery-brown px-6 py-3 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark"
                >
                    Regisztráció
                </Link>
                <Link
                    href="/login"
                    class="inline-flex min-h-11 items-center rounded-full border border-bakery-brown/30 px-6 py-3 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                >
                    Belépés
                </Link>
            </div>
        </section>
    </div>
</template>
