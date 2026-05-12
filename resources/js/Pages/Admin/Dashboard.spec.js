import { mount } from "@vue/test-utils";
import DashboardPage from "./Dashboard.vue";

const { translate } = vi.hoisted(() => {
    const translations = {
        "common.admin": "Admin",
        "dashboard.actions.manage_categories": "Kategóriák kezelése",
        "dashboard.actions.manage_ingredients": "Alapanyagok kezelése",
        "dashboard.actions.manage_products": "Termékek kezelése",
        "dashboard.actions.manage_recipes": "Receptek kezelése",
        "dashboard.actions.manage_weekly_menus": "Heti menük kezelése",
        "dashboard.card_most_popular_product": "Legnépszerűbb termék",
        "dashboard.card_next_pickup_lane": "Következő átvételi sáv",
        "dashboard.card_todays_orders": "Mai rendelések",
        "dashboard.card_weekly_revenue": "Heti árbevétel",
        "dashboard.description": "Első alap vezérlőpult valós admin struktúrával.",
        "dashboard.next_steps_description": "A következő fázisban ide csatlakoznak a valós riportok.",
        "dashboard.next_steps_title": "Következő lépések",
        "nav.dashboard": "Napi áttekintés",
    };

    return {
        translate: (key) => translations[key] ?? key,
    };
});

vi.mock("@inertiajs/vue3", () => ({
    Head: { name: "Head", template: "<span />" },
    Link: { name: "Link", props: ["href"], template: '<a :href="href"><slot /></a>' },
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

const stubs = {
    DashboardCard: {
        props: ["title", "value"],
        template: "<article>{{ title }} {{ value }}</article>",
    },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

describe("Admin Dashboard", () => {
    it("renders localized dashboard summary and next-step links", () => {
        const wrapper = mount(DashboardPage, {
            props: {
                stats: {
                    ordersToday: 12,
                    weekRevenue: "120 000 Ft",
                    topProduct: "Kovászos kenyér",
                    nextPickupSlot: "Holnap 08:00",
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

        expect(wrapper.text()).toContain("Admin");
        expect(wrapper.text()).toContain("Napi áttekintés");
        expect(wrapper.text()).toContain("Mai rendelések");
        expect(wrapper.text()).toContain("12");
        expect(wrapper.text()).toContain("Heti árbevétel");
        expect(wrapper.text()).toContain("Legnépszerűbb termék");
        expect(wrapper.text()).toContain("Következő átvételi sáv");
        expect(wrapper.text()).toContain("Következő lépések");
        expect(wrapper.text()).toContain("Kategóriák kezelése");
        expect(wrapper.text()).toContain("Termékek kezelése");
        expect(wrapper.text()).toContain("Receptek kezelése");
        expect(wrapper.text()).toContain("Alapanyagok kezelése");
        expect(wrapper.text()).toContain("Heti menük kezelése");
    });
});
