import { mount } from "@vue/test-utils";
import StockCountsShowPage from "./Show.vue";

const { post, translate } = vi.hoisted(() => {
    const translations = {
        "admin_stock_count.actions.close_and_post": "Leltár lezárása és könyvelés",
        "admin_stock_count.columns.counted": "Számolt",
        "admin_stock_count.columns.difference": "Különbözet",
        "admin_stock_count.columns.expected": "Várt",
        "admin_stock_count.show.closed_at": "Lezárva",
        "admin_stock_count.show.meta_title": "Leltár #:id",
        "admin_stock_count.show.title": "Leltár #:id",
        "common.back_to_list": "Vissza a listára",
        "common.date": "Dátum",
        "common.ingredient": "Alapanyag",
        "common.status": "Állapot",
    };

    return {
        post: vi.fn(),
        translate: (key, replacements = {}) => {
            let value = translations[key] ?? key;

            Object.entries(replacements).forEach(([name, replacement]) => {
                value = value.replace(`:${name}`, replacement);
            });

            return value;
        },
    };
});

vi.mock("@inertiajs/vue3", () => ({
    Head: { name: "Head", template: "<span />" },
    Link: { name: "Link", props: ["href"], template: '<a :href="href"><slot /></a>' },
    router: { post },
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: {
        props: ["label"],
        emits: ["click"],
        template: "<button @click=\"$emit('click')\">{{ label }}</button>",
    },
}));

const mountPage = (status = "draft") =>
    mount(StockCountsShowPage, {
        props: {
            stock_count: {
                id: 7,
                count_date: "2026-05-12",
                status,
                closed_at: null,
                notes: "Próba leltár",
                items: [
                    {
                        id: 1,
                        ingredient_name: "Liszt",
                        expected_quantity: 10,
                        counted_quantity: 9,
                        difference: -1,
                        unit: "kg",
                    },
                ],
            },
        },
        global: {
            mocks: {
                $t: translate,
                route: (name, id = "") => `/${name}${id ? `/${id}` : ""}`,
            },
        },
    });

describe("Admin Stock Counts Show", () => {
    it("renders localized stock count details and item table", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Leltár #7");
        expect(wrapper.text()).toContain("Vissza a listára");
        expect(wrapper.text()).toContain("Dátum:");
        expect(wrapper.text()).toContain("Állapot:");
        expect(wrapper.text()).toContain("Lezárva:");
        expect(wrapper.text()).toContain("Leltár lezárása és könyvelés");
        expect(wrapper.text()).toContain("Alapanyag");
        expect(wrapper.text()).toContain("Várt");
        expect(wrapper.text()).toContain("Számolt");
        expect(wrapper.text()).toContain("Különbözet");
        expect(wrapper.text()).toContain("Liszt");
    });

    it("hides close action for closed stock counts", () => {
        const wrapper = mountPage("closed");

        expect(wrapper.text()).not.toContain("Leltár lezárása és könyvelés");
    });
});
