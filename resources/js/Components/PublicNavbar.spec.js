import { mount } from "@vue/test-utils";
import PublicNavbar from "./PublicNavbar.vue";

const page = {
    url: "/",
    props: {
        auth: { user: null },
        cart: { total_quantity: 2 },
        ui: { nav: {} },
    },
};

const translations = {
    "nav.home": "Kezdőlap",
    "nav.weekly_menu": "Heti menü",
    "nav.about": "Rólunk",
    "nav.cart": "Kosár",
    "nav.login": "Belépés",
    "nav.register": "Regisztráció",
    "nav.account": "Fiókom",
    "nav.admin": "Admin panel",
    "nav.logout": "Kilépés",
    "nav.open_menu": "Menü megnyitása",
    "nav.close_menu": "Menü bezárása",
};

const globalMocks = {
    $t: (key) => translations[key] ?? key,
    route: (name) => `/${name.replaceAll(".", "/")}`,
};

const globalStubs = {
    LocaleSwitcher: { template: "<div />" },
};

vi.mock("@inertiajs/vue3", () => ({
    Link: {
        name: "Link",
        props: ["href"],
        template: '<a :href="href"><slot /></a>',
    },
    usePage: () => page,
}));

vi.mock("laravel-vue-i18n", () => ({
    trans: (key) => translations[key] ?? key,
}));

vi.mock("primevue/button", () => ({
    default: {
        name: "Button",
        props: ["ariaLabel", "icon"],
        emits: ["click"],
        template:
            '<button type="button" :aria-label="ariaLabel" @click="$emit(\'click\', $event)"><i :class="icon" /></button>',
    },
}));

describe("PublicNavbar", () => {
    it("renders localized public navigation labels", () => {
        const wrapper = mount(PublicNavbar, {
            global: {
                mocks: globalMocks,
                stubs: globalStubs,
            },
        });

        expect(wrapper.text()).toContain("Kezdőlap");
        expect(wrapper.text()).toContain("Heti menü");
        expect(wrapper.text()).toContain("Rólunk");
        expect(wrapper.text()).toContain("Kosár");
        expect(wrapper.text()).toContain("Belépés");
        expect(wrapper.text()).toContain("Regisztráció");
    });

    it("uses client translations instead of stale shared nav props", () => {
        page.props.ui.nav = { cart: "Kosár teszt" };

        const wrapper = mount(PublicNavbar, {
            global: {
                mocks: globalMocks,
                stubs: globalStubs,
            },
        });

        expect(wrapper.text()).toContain("Kosár");
        expect(wrapper.text()).not.toContain("Kosár teszt");

        page.props.ui.nav = {};
    });

    it("highlights the active public menu item with query strings", () => {
        page.url = "/weekly-menu?preview=1";

        const wrapper = mount(PublicNavbar, {
            global: {
                mocks: globalMocks,
                stubs: globalStubs,
            },
        });

        const weeklyMenuLink = wrapper.findAll("a").find((link) => link.text() === "Heti menü");

        expect(weeklyMenuLink.classes()).toContain("bg-bakery-brown");

        page.url = "/";
    });

    it("highlights secondary menu actions", () => {
        page.url = "/cart";

        const wrapper = mount(PublicNavbar, {
            global: {
                mocks: globalMocks,
                stubs: globalStubs,
            },
        });

        const cartLink = wrapper.findAll("a").find((link) => link.text().includes("Kosár"));

        expect(cartLink.classes()).toContain("bg-bakery-brown");

        page.url = "/";
    });

    it("toggles the mobile menu with the PrimeVue button", async () => {
        const wrapper = mount(PublicNavbar, {
            global: {
                mocks: globalMocks,
                stubs: globalStubs,
            },
        });

        const toggle = wrapper.find('button[aria-label="Menü megnyitása"]');

        expect(toggle.exists()).toBe(true);

        await toggle.trigger("click");

        expect(wrapper.find('button[aria-label="Menü bezárása"]').exists()).toBe(true);
    });
});
