import { mount } from "@vue/test-utils";
import StockCountsIndexPage from "./Index.vue";

const { post, translate } = vi.hoisted(() => {
    const translations = {
        "admin_stock_count.actions.create": "Új leltár",
        "admin_stock_count.actions.details": "Részletek",
        "admin_stock_count.actions.filter": "Szűrés",
        "admin_stock_count.actions.save": "Leltár mentése",
        "admin_stock_count.columns.action": "Művelet",
        "admin_stock_count.columns.created_by": "Készítette",
        "admin_stock_count.columns.items": "Tételek",
        "admin_stock_count.description": "Készletszámlálás, különbözet könyvelés és zárás auditált folyamatban.",
        "admin_stock_count.eyebrow": "Admin / Leltár",
        "admin_stock_count.fields.counted_quantity": "Számolt mennyiség",
        "admin_stock_count.fields.expected_quantity": "Várt mennyiség",
        "admin_stock_count.filters.all_statuses": "Mind",
        "admin_stock_count.meta_title": "Leltár",
        "admin_stock_count.title": "Leltárak",
        "common.date": "Dátum",
        "common.notes": "Megjegyzés",
        "common.status": "Állapot",
    };

    return {
        post: vi.fn(),
        translate: (key) => translations[key] ?? key,
    };
});

vi.mock("@inertiajs/vue3", () => ({
    Head: { name: "Head", template: "<span />" },
    Link: { name: "Link", props: ["href"], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn() },
    useForm: (defaults) => ({
        ...defaults,
        processing: false,
        errors: {},
        post,
    }),
}));

vi.mock("laravel-vue-i18n", () => ({
    trans: translate,
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("@/Components/BaseDatePicker.vue", () => ({
    default: { template: '<input type="date" />' },
}));

vi.mock("primevue/button", () => ({
    default: {
        props: ["label", "disabled"],
        emits: ["click"],
        template: '<button :disabled="disabled" @click="$emit(\'click\')">{{ label }}</button>',
    },
}));
vi.mock("primevue/inputtext", () => ({
    default: {
        props: ["placeholder"],
        template: '<input :placeholder="placeholder" />',
    },
}));
vi.mock("primevue/select", () => ({
    default: {
        props: ["options", "optionLabel"],
        template: '<div><span v-for="option in options" :key="option.value">{{ option[optionLabel] }}</span></div>',
    },
}));

const stubs = {
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

const mountPage = () =>
    mount(StockCountsIndexPage, {
        props: {
            stock_counts: {
                data: [
                    {
                        id: 1,
                        count_date: "2026-05-12",
                        status: "draft",
                        items_count: 3,
                        created_by: "Admin",
                    },
                ],
            },
            statuses: ["draft", "closed"],
            ingredient_options: [{ id: 1, name: "Liszt", current_stock: 10 }],
            filters: { status: "" },
        },
        global: {
            stubs,
            mocks: {
                $t: translate,
                route: (name, id = "") => `/${name}${id ? `/${id}` : ""}`,
            },
        },
    });

describe("Admin Stock Counts Index", () => {
    it("renders localized stock count list and create form", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Admin / Leltár");
        expect(wrapper.text()).toContain("Leltárak");
        expect(wrapper.text()).toContain("Mind");
        expect(wrapper.text()).toContain("Szűrés");
        expect(wrapper.text()).toContain("Dátum");
        expect(wrapper.text()).toContain("Állapot");
        expect(wrapper.text()).toContain("Tételek");
        expect(wrapper.text()).toContain("Készítette");
        expect(wrapper.text()).toContain("Művelet");
        expect(wrapper.text()).toContain("Részletek");
        expect(wrapper.text()).toContain("Új leltár");
        expect(wrapper.find('[placeholder="Megjegyzés"]').exists()).toBe(true);
        expect(wrapper.find('[placeholder="Várt mennyiség"]').exists()).toBe(true);
        expect(wrapper.find('[placeholder="Számolt mennyiség"]').exists()).toBe(true);
        expect(wrapper.text()).toContain("Leltár mentése");
    });
});
