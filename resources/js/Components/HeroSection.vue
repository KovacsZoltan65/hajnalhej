<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <section
        class="relative overflow-hidden rounded-3xl border border-bakery-brown/15 bg-[#f8efe3] p-8 shadow-lg sm:p-12"
    >
        <div
            class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-bakery-gold/25 blur-2xl"
        />
        <div
            class="absolute -bottom-20 -left-12 h-52 w-52 rounded-full bg-bakery-brown/15 blur-2xl"
        />

        <div class="relative grid gap-10 lg:grid-cols-[1.2fr_1fr] lg:items-center">
            <div class="space-y-6 rise-in">
                <p
                    class="text-xs font-semibold uppercase tracking-[0.24em] text-bakery-gold"
                >
                    Kézműves pékség Budapest
                </p>
                <h1
                    class="font-heading text-4xl leading-tight text-bakery-dark sm:text-5xl"
                >
                    Frissen sült kenyerek, lassú kelesztéssel, hajnalban.
                </h1>
                <p class="max-w-xl text-lg text-bakery-dark/80">
                    A Hajnalhéj hétről hétre kis tételben süt, hogy minden reggel ropogós,
                    meleg és valódi legyen.
                </p>
                <div class="flex flex-wrap gap-3">
                    <Link
                        href="/weekly-menu"
                        class="rounded-full bg-bakery-brown px-6 py-3 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark"
                    >
                        Heti menu megnyitása
                    </Link>
                    <Link
                        href="/register"
                        class="rounded-full bg-bakery-gold px-6 py-3 text-sm font-semibold text-bakery-dark transition hover:bg-[#edbb5a]"
                    >
                        Fiók létrehozása
                    </Link>
                    <Link
                        href="/about"
                        class="rounded-full border border-bakery-brown/35 px-6 py-3 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                    >
                        Történetünk
                    </Link>
                </div>
            </div>

            <div class="glass-bakery grain-overlay rounded-2xl p-6">
                <div class="space-y-3">
                    <p class="font-heading text-2xl text-bakery-dark">
                        Kovász. Idő. Türelem.
                    </p>
                    <p class="text-sm leading-relaxed text-bakery-dark/75">
                        Minden termékünk több órányi fermentációval készül, hogy az íz, az
                        állag és az illat is emlékezetes maradjon.
                    </p>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-bakery-cream px-3 py-2">
                            <p class="font-semibold text-bakery-brown">100%</p>
                            <p class="text-bakery-dark/70">kézműves gyártás</p>
                        </div>
                        <div class="rounded-xl bg-bakery-cream px-3 py-2">
                            <p class="font-semibold text-bakery-brown">Heti</p>
                            <p class="text-bakery-dark/70">limitált menü</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
