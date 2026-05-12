import { mount } from "@vue/test-utils";
import HomePage from "./Home.vue";

const { trackCtaClick, translate } = vi.hoisted(() => {
    const translations = {
        "home.about_story": "Ismerd meg a történetünket",
        "home.bestseller_01_note": "Ropogós héj, nedves bélzet.",
        "home.bestseller_01_price": "2 450 Ft",
        "home.bestseller_01_tag": "Legnépszerűbb",
        "home.bestseller_01_title": "Kovászos fehér vekni",
        "home.bestseller_02_note": "Lágy szerkezet, aranybarna kéreg.",
        "home.bestseller_02_price": "2 190 Ft",
        "home.bestseller_02_tag": "Limitált",
        "home.bestseller_02_title": "Vajas-foszlós kalács",
        "home.bestseller_03_note": "Extra szűz olívaolajjal.",
        "home.bestseller_03_price": "1 990 Ft",
        "home.bestseller_03_tag": "Gyorsan fogy",
        "home.bestseller_03_title": "Rozmaringos focaccia",
        "home.bestsellers_description": "A teljes kínálat dinamikusan frissül.",
        "home.bestsellers_eyebrow": "Bestseller választék",
        "home.bestsellers_title": "Gyorsan fogyó kedvencek minden héten",
        "home.create_account": "Fiók létrehozása",
        "home.crispy_mornings": "Ropogós reggelek",
        "home.faq_01_answer": "A heti menüben jelzett készlet erejéig.",
        "home.faq_01_question": "Meddig tudok rendelni?",
        "home.faq_02_answer": "Ajánlott, de vendégként is indítható.",
        "home.faq_02_question": "Kell regisztráció?",
        "home.faq_03_answer": "Érdemes jelezni nekünk előre.",
        "home.faq_03_question": "Mi történik, ha kések?",
        "home.faq_description": "Rendelés előtt gyors válaszok.",
        "home.faq_eyebrow": "Gyakori kérdések",
        "home.faq_title": "Minden fontos egy helyen",
        "home.final_description": "Rendelj 1 perc alatt a heti kínálatból.",
        "home.final_eyebrow": "Készen állsz?",
        "home.final_title": "Indítsd el az első rendelésed",
        "home.go_to_checkout": "Ugrás a pénztárhoz",
        "home.hero_eyebrow": "Hajnalhéj Bakery | Budapest",
        "home.hero_title_01": "Prémium kézműves pékáru, gyors és kiszámítható átvétellel.",
        "home.hero_title_02": "Prémium kézműves pékáru előrendeléssel.",
        "home.hero_title_03": "Rendelj pár kattintással.",
        "home.hero_title_04": "Lassú kelesztés, kis szériás sütés.",
        "home.highlight_batch_label": "Heti limitált batch",
        "home.highlight_batch_value": "Hetente",
        "home.highlight_pickup_label": "Átvételi pontosság",
        "home.highlight_pickup_value": "15 perc",
        "home.highlight_sourdough_label": "Kovászolt tételek",
        "home.highlight_sourdough_value": "18+ óra",
        "home.meta_title": "Prémium artisan pékség Budapesten",
        "home.open_cart": "Kosár megnyitása",
        "home.open_weekly_menu": "Heti menü megnyitása",
        "home.philosophy": "Kovász. Idő. Türelem.",
        "home.reserve_cta": "Foglalás",
        "home.step_01_text": "Válassz a heti menüből.",
        "home.step_01_title": "Válassz a heti menüből",
        "home.step_02_text": "Checkout gyorsan.",
        "home.step_02_title": "Foglalj 1 perc alatt",
        "home.step_03_text": "Vedd át frissen.",
        "home.step_03_title": "Vedd át frissen",
        "home.steps_description": "Egyszerű rendelési folyamat.",
        "home.steps_eyebrow": "Hogyan működik?",
        "home.steps_title": "Három lépés",
        "home.testimonial_01_name": "Anna",
        "home.testimonial_01_quote": "Minden reggel ropogós.",
        "home.testimonial_01_role": "XIII. kerület",
        "home.testimonial_02_name": "Márk",
        "home.testimonial_02_quote": "Gördülékeny rendelés.",
        "home.testimonial_02_role": "II. kerület",
        "home.testimonial_03_name": "Nóra",
        "home.testimonial_03_quote": "Prémium íz.",
        "home.testimonial_03_role": "XI. kerület",
        "home.testimonials_description": "Valódi visszajelzések.",
        "home.testimonials_eyebrow": "Vásárlói visszajelzések",
        "home.testimonials_title": "A minőség rutin",
        "home.trust_pillar_01": "Kis szériás sütés",
        "home.trust_pillar_02": "Lassú fermentáció",
        "home.trust_pillar_03": "Pontos átvétel",
        "home.trust_pillar_04": "Átlátható rendelés",
        "home.urgency_description": "Limitált heti kínálat.",
        "home.urgency_eyebrow": "Heti batch",
        "home.urgency_title": "Ne maradj le",
        "home.weekly_menu": "Heti menü megtekintése",
        "home.why_description": "Minden tételt frissen készítünk.",
        "home.why_it_works": "Miért működik?",
        "nav.login": "Belépés",
        "nav.register": "Regisztráció",
    };

    return {
        trackCtaClick: vi.fn(),
        translate: (key) => translations[key] ?? key,
    };
});

vi.mock("@inertiajs/vue3", () => ({
    Head: {
        name: "Head",
        props: ["title"],
        template: "<span />",
    },
    Link: {
        name: "Link",
        props: ["href"],
        emits: ["click"],
        template: '<a :href="href" @click.prevent="$emit(\'click\', $event)"><slot /></a>',
    },
    usePage: () => ({
        url: "/",
    }),
}));

vi.mock("laravel-vue-i18n", () => ({
    trans: translate,
}));

vi.mock("@/composables/useConversionTracking", () => ({
    useConversionTracking: () => ({
        trackCtaClick,
    }),
}));

vi.mock("@/Layouts/PublicLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

const stubs = {
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

const mountHomePage = (variant = "artisan_story") =>
    mount(HomePage, {
        props: {
            heroExperiment: {
                variant,
            },
        },
        global: {
            stubs,
            mocks: {
                $t: translate,
                route: (name) => `/${name}`,
            },
        },
    });

describe("Home page", () => {
    it("renders localized landing content for the artisan story variant", () => {
        const wrapper = mountHomePage();

        expect(wrapper.text()).toContain("Hajnalhéj Bakery | Budapest");
        expect(wrapper.text()).toContain("Ropogós reggelek");
        expect(wrapper.text()).toContain("Prémium kézműves pékáru előrendeléssel.");
        expect(wrapper.text()).toContain("Heti menü megtekintése");
        expect(wrapper.text()).toContain("Fiók létrehozása");
        expect(wrapper.text()).toContain("Kosár megnyitása");
        expect(wrapper.text()).toContain("Kovász. Idő. Türelem.");
        expect(wrapper.text()).toContain("Gyorsan fogyó kedvencek minden héten");
        expect(wrapper.text()).toContain("Három lépés");
        expect(wrapper.text()).toContain("Gyakori kérdések");
        expect(wrapper.text()).toContain("Indítsd el az első rendelésed");
    });

    it("renders speed checkout hero copy when that experiment variant is active", () => {
        const wrapper = mountHomePage("speed_checkout");

        expect(wrapper.text()).toContain("Prémium kézműves pékáru, gyors és kiszámítható átvétellel.");
        expect(wrapper.text()).toContain("Rendelj pár kattintással.");
    });

    it("tracks landing CTA clicks", async () => {
        const wrapper = mountHomePage();

        await wrapper.find('a[href="/weekly-menu"]').trigger("click");

        expect(trackCtaClick).toHaveBeenCalledWith(
            "hero.weekly_menu_primary",
            expect.objectContaining({
                funnel: "landing",
                heroVariant: "artisan_story",
            })
        );
    });
});
