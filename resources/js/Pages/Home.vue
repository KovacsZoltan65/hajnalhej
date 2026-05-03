<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import SectionTitle from "@/Components/SectionTitle.vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { useConversionTracking } from "@/composables/useConversionTracking";
import { trans } from "laravel-vue-i18n";

defineOptions({ layout: PublicLayout });

const props = defineProps({
    heroExperiment: {
        type: Object,
        default: () => ({ variant: "artisan_story" }),
    },
});

const page = usePage();
const { trackCtaClick } = useConversionTracking();

const heroVariant = computed(() => props.heroExperiment?.variant ?? "artisan_story");

const heroTitle = computed(() =>
    heroVariant.value === "speed_checkout"
        ? trans("home.hero_title_01")
        : trans("home.hero_title_02")
);

const heroSubtitle = computed(() =>
    heroVariant.value === "speed_checkout"
        ? trans("home.hero_title_03")
        : trans("home.hero_title_04")
);

const trackLandingCta = (ctaId, href) => {
    trackCtaClick(ctaId, {
        funnel: "landing",
        heroVariant: heroVariant.value,
        metadata: {
            path: page.url,
            href,
        },
    });
};

const heroHighlights = computed(() => [
    {
        label: trans("home.highlight_sourdough_label"),
        value: trans("home.highlight_sourdough_value"),
    },
    {
        label: trans("home.highlight_batch_label"),
        value: trans("home.highlight_batch_value"),
    },
    {
        label: trans("home.highlight_pickup_label"),
        value: trans("home.highlight_pickup_value"),
    },
]);

const trustPillars = computed(() => [
    trans("home.trust_pillar_01"),
    trans("home.trust_pillar_02"),
    trans("home.trust_pillar_03"),
    trans("home.trust_pillar_04"),
]);

const bestsellers = computed(() => [
    {
        title: trans("home.bestseller_01_title"),
        note: trans("home.bestseller_01_note"),
        price: trans("home.bestseller_01_price"),
        tag: trans("home.bestseller_01_tag"),
    },
    {
        title: trans("home.bestseller_02_title"),
        note: trans("home.bestseller_02_note"),
        price: trans("home.bestseller_02_price"),
        tag: trans("home.bestseller_02_tag"),
    },
    {
        title: trans("home.bestseller_03_title"),
        note: trans("home.bestseller_03_note"),
        price: trans("home.bestseller_03_price"),
        tag: trans("home.bestseller_03_tag"),
    },
]);

const steps = computed(() => [
    {
        title: trans("home.step_01_title"),
        text: trans("home.step_01_text"),
    },
    {
        title: trans("home.step_02_title"),
        text: trans("home.step_02_text"),
    },
    {
        title: trans("home.step_03_title"),
        text: trans("home.step_03_text"),
    },
]);

const testimonials = computed(() => [
    {
        quote: trans("home.testimonial_01_quote"),
        name: trans("home.testimonial_01_name"),
        role: trans("home.testimonial_01_role"),
    },
    {
        quote: trans("home.testimonial_02_quote"),
        name: trans("home.testimonial_02_name"),
        role: trans("home.testimonial_02_role"),
    },
    {
        quote: trans("home.testimonial_03_quote"),
        name: trans("home.testimonial_03_name"),
        role: trans("home.testimonial_03_role"),
    },
]);

const faqs = computed(() => [
    {
        question: trans("home.faq_01_question"),
        answer: trans("home.faq_01_answer"),
    },
    {
        question: trans("home.faq_02_question"),
        answer: trans("home.faq_02_answer"),
    },
    {
        question: trans("home.faq_03_question"),
        answer: trans("home.faq_03_answer"),
    },
]);
</script>

<template>
    <Head :title="$t('home.meta_title')" />

    <div class="space-y-14 md:space-y-16">
        <section
            class="ui-card ui-card-elevated relative overflow-hidden p-6 sm:p-8 lg:p-10"
        >
            <div
                class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-bakery-gold/20 blur-3xl"
            />
            <div
                class="absolute -bottom-20 -left-12 h-64 w-64 rounded-full bg-bakery-brown/12 blur-3xl"
            />

            <div class="relative grid gap-8 lg:grid-cols-[1.25fr_0.95fr] lg:items-start">
                <div class="space-y-6">
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.24em] text-bakery-gold"
                    >
                        Hajnalhéj Bakery | Budapest
                    </p>
                    <h1
                        class="font-heading text-[2.1rem] leading-tight text-bakery-dark sm:text-5xl"
                    >
                        {{ $t("home.crispy_mornings") }}.
                        <span class="block text-bakery-brown">{{ heroTitle }}</span>
                    </h1>
                    <p
                        class="max-w-2xl text-base leading-relaxed text-bakery-dark/78 sm:text-lg"
                    >
                        {{ heroSubtitle }}
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <Link
                            href="/weekly-menu"
                            class="inline-flex min-h-11 items-center rounded-full bg-bakery-brown px-6 py-3 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark"
                            @click="
                                trackLandingCta(
                                    'hero.weekly_menu_primary',
                                    '/weekly-menu'
                                )
                            "
                        >
                            {{ $t("home.weekly_menu") }}
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-flex min-h-11 items-center rounded-full bg-bakery-gold px-6 py-3 text-sm font-semibold text-bakery-dark transition hover:bg-[#edbb5a]"
                            @click="trackLandingCta('hero.register_primary', '/register')"
                        >
                            {{ $t("home.create_account") }}
                        </Link>
                        <Link
                            :href="route('cart.index')"
                            class="inline-flex min-h-11 items-center rounded-full border border-bakery-brown/30 px-6 py-3 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                            @click="trackLandingCta('hero.cart_secondary', '/cart')"
                        >
                            {{ $t("home.open_cart") }}
                        </Link>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <article
                            v-for="item in heroHighlights"
                            :key="item.label"
                            class="ui-card-soft p-3"
                        >
                            <p
                                class="text-[0.68rem] uppercase tracking-[0.16em] text-bakery-brown/70"
                            >
                                {{ item.label }}
                            </p>
                            <p class="mt-1 font-heading text-2xl text-bakery-dark">
                                {{ item.value }}
                            </p>
                        </article>
                    </div>
                </div>

                <aside class="ui-card-soft space-y-4 p-5 sm:p-6">
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.2em] text-bakery-gold"
                    >
                        {{ $t("home.why_it_works") }}
                    </p>
                    <h2 class="font-heading text-3xl leading-tight text-bakery-dark">
                        {{ $t("home.philosophy") }}
                    </h2>
                    <p class="text-sm leading-relaxed text-bakery-dark/75">
                        {{ $t("home.why_description") }}
                    </p>
                    <ul class="space-y-2">
                        <li
                            v-for="pillar in trustPillars"
                            :key="pillar"
                            class="flex items-start gap-2 text-sm text-bakery-dark/80"
                        >
                            <span
                                class="mt-1 inline-block h-2 w-2 rounded-full bg-bakery-gold"
                            />
                            <span>{{ pillar }}</span>
                        </li>
                    </ul>
                    <Link
                        href="/about"
                        class="inline-flex min-h-11 items-center rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                        @click="trackLandingCta('hero.about_story', '/about')"
                    >
                        {{ $t("home.about_story") }}
                    </Link>
                </aside>
            </div>
        </section>

        <section class="space-y-7">
            <SectionTitle
                :eyebrow="$t('home.bestsellers_eyebrow')"
                :title="$t('home.bestsellers_title')"
                :description="$t('home.bestsellers_description')"
            />
            <div class="grid gap-4 md:grid-cols-3">
                <article
                    v-for="item in bestsellers"
                    :key="item.title"
                    class="ui-card p-5"
                >
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-heading text-2xl text-bakery-dark">
                            {{ item.title }}
                        </h3>
                        <span class="ui-badge bg-bakery-gold/20 text-bakery-brown">{{
                            item.tag
                        }}</span>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed text-bakery-dark/75">
                        {{ item.note }}
                    </p>
                    <p class="mt-4 text-sm font-semibold text-bakery-brown">
                        {{ item.price }}
                    </p>
                    <Link
                        href="/weekly-menu"
                        class="mt-4 inline-flex min-h-11 items-center rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                        @click="
                            trackLandingCta(`bestseller.${item.title}`, '/weekly-menu')
                        "
                    >
                        {{ $t("home.reserve_cta") }}
                    </Link>
                </article>
            </div>
        </section>

        <section class="ui-card p-6 sm:p-8">
            <SectionTitle
                :eyebrow="$t('home.steps_eyebrow')"
                :title="$t('home.steps_title')"
                :description="$t('home.steps_description')"
            />
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <article
                    v-for="(step, index) in steps"
                    :key="step.title"
                    class="ui-card-soft p-5"
                >
                    <p class="font-heading text-3xl text-bakery-brown">
                        {{ String(index + 1).padStart(2, "0") }}
                    </p>
                    <h3 class="mt-2 font-semibold text-bakery-dark">{{ step.title }}</h3>
                    <p class="mt-2 text-sm text-bakery-dark/75">{{ step.text }}</p>
                </article>
            </div>
        </section>

        <section class="space-y-7">
            <SectionTitle
                :eyebrow="$t('home.testimonials_eyebrow')"
                :title="$t('home.testimonials_title')"
                :description="$t('home.testimonials_description')"
            />
            <div class="grid gap-4 md:grid-cols-3">
                <article
                    v-for="item in testimonials"
                    :key="item.name"
                    class="ui-card p-5"
                >
                    <p class="text-sm leading-relaxed text-bakery-dark/80">
                        {{ item.quote }}
                    </p>
                    <p class="mt-4 text-sm font-semibold text-bakery-dark">
                        {{ item.name }}
                    </p>
                    <p class="text-xs text-bakery-dark/65">{{ item.role }}</p>
                </article>
            </div>
        </section>

        <section
            class="ui-card ui-card-elevated overflow-hidden bg-bakery-brown p-7 text-bakery-cream sm:p-10"
        >
            <p class="text-xs uppercase tracking-[0.22em] text-bakery-gold">
                {{ $t("home.urgency_eyebrow") }}
            </p>
            <h2 class="mt-3 font-heading text-3xl sm:text-4xl">
                {{ $t("home.urgency_title") }}
            </h2>
            <p class="mt-3 max-w-2xl text-bakery-cream/85">
                {{ $t("home.urgency_description") }}
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <Link
                    href="/weekly-menu"
                    class="inline-flex min-h-11 items-center rounded-full bg-bakery-gold px-6 py-3 text-sm font-semibold text-bakery-dark transition hover:bg-[#edbb5a]"
                    @click="trackLandingCta('urgency.weekly_menu', '/weekly-menu')"
                >
                    {{ $t("home.open_weekly_menu") }}
                </Link>
                <Link
                    href="/checkout"
                    class="inline-flex min-h-11 items-center rounded-full border border-bakery-cream/35 px-6 py-3 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-cream/10"
                    @click="trackLandingCta('urgency.checkout', '/checkout')"
                >
                    {{ $t("home.go_to_checkout") }}
                </Link>
            </div>
        </section>

        <section class="ui-card p-6 sm:p-8">
            <SectionTitle
                :eyebrow="$t('home.faq_eyebrow')"
                :title="$t('home.faq_title')"
                :description="$t('home.faq_description')"
            />
            <div class="mt-6 space-y-3">
                <details
                    v-for="item in faqs"
                    :key="item.question"
                    class="ui-card-soft group p-4"
                >
                    <summary
                        class="cursor-pointer list-none text-sm font-semibold text-bakery-dark"
                    >
                        {{ item.question }}
                    </summary>
                    <p class="mt-2 text-sm leading-relaxed text-bakery-dark/75">
                        {{ item.answer }}
                    </p>
                </details>
            </div>
        </section>

        <section class="ui-card p-6 text-center sm:p-8">
            <p class="text-xs uppercase tracking-[0.2em] text-bakery-gold">
                {{ $t("home.final_eyebrow") }}
            </p>
            <h2 class="mt-2 font-heading text-3xl text-bakery-dark sm:text-4xl">
                {{ $t("home.final_title") }}
            </h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-bakery-dark/75 sm:text-base">
                {{ $t("home.final_description") }}
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <Link
                    href="/register"
                    class="inline-flex min-h-11 items-center rounded-full bg-bakery-brown px-6 py-3 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark"
                    @click="trackLandingCta('final.register', '/register')"
                >
                    {{ $t("nav.register") }}
                </Link>
                <Link
                    href="/login"
                    class="inline-flex min-h-11 items-center rounded-full border border-bakery-brown/30 px-6 py-3 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                    @click="trackLandingCta('final.login', '/login')"
                >
                    {{ $t("nav.login") }}
                </Link>
            </div>
        </section>
    </div>
</template>
