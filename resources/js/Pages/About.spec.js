import { mount } from "@vue/test-utils";
import AboutPage from "./About.vue";

const translate = (key) =>
    ({
        "about.current_phase": "Ebben a fázisban az oldal célja a stabil alap létrehozása.",
        "about.description": "Budapest belvárosában sütünk, kis mennyiségben.",
        "about.eyebrow": "Rólunk",
        "about.meta_title": "Rólunk",
        "about.philosophy": "A filozófiánk röviden: Kovász. Idő. Türelem.",
        "about.title": "Hajnalhéj: modern pékség, klasszikus alapokkal.",
    })[key] ?? key;

vi.mock("@inertiajs/vue3", () => ({
    Head: {
        name: "Head",
        props: ["title"],
        template: "<span />",
    },
}));

vi.mock("../Layouts/PublicLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

const stubs = {
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

describe("About page", () => {
    it("renders localized about content", () => {
        const wrapper = mount(AboutPage, {
            global: {
                stubs,
                mocks: {
                    $t: translate,
                },
            },
        });

        expect(wrapper.text()).toContain("Rólunk");
        expect(wrapper.text()).toContain("Hajnalhéj: modern pékség, klasszikus alapokkal.");
        expect(wrapper.text()).toContain("Budapest belvárosában sütünk");
        expect(wrapper.text()).toContain("Kovász. Idő. Türelem.");
        expect(wrapper.text()).toContain("stabil alap létrehozása");
    });
});
