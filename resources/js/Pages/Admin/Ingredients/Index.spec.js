import { mount } from "@vue/test-utils";
import IngredientsIndexPage from "./Index.vue";

const { confirmRequire, translate } = vi.hoisted(() => {
    const translations = {
        "admin_ingredients.filters.per_page_option": ":count / oldal",
        "admin_ingredients.meta_title": "Alapanyagok",
        "admin_ingredients.eyebrow": "Admin / Alapanyagok",
        "admin_ingredients.title": "Alapanyagok",
        "admin_ingredients.description":
            "Alapanyag törzs alacsony készlet jelzéssel, készen a készletkezelés és gyártási kalkuláció következő lépéseihez.",
        "common.search": "Keresés",
        "admin_ingredients.filters.search_placeholder": "Név, slug vagy SKU",
        "common.status": "Státusz",
        "admin_ingredients.filters.unit": "Mértékegység",
        "table.rows_per_page": "Találat / oldal",
        "common.ingredient": "Alapanyag",
        "admin_ingredients.columns.unit": "Mértékegység",
        "admin_ingredients.columns.estimated_unit_cost": "Becsült egységköltség",
        "admin_ingredients.columns.stock": "Készlet",
        "common.status": "Státusz",
        "common.search": "Keresés",
        "admin_ingredients.actions.create": "Új alapanyag",
        "admin_ingredients.actions.edit": "Alapanyag szerkesztése",
        "admin_ingredients.actions.delete": "Alapanyag törlése",
        "admin_ingredients.empty": "Nincs megjeleníthető alapanyag.",
        "admin.common.empty.title": "Nincs megjeleníthető adat",
        "admin.common.empty.description": "Még nincs megjeleníthető adat ebben a nézetben.",
        "admin.common.empty.no_results_description": "A jelenlegi szűrők mellett nincs találat.",
        "admin_ingredients.confirm_delete_header": "Alapanyag törlése",
        "admin_ingredients.confirm_delete_message": "Biztosan törlöd ezt az alapanyagot: :name?",
        "common.all": "Mind",
        "common.active": "Aktív",
        "common.inactive": "Inaktív",
        "common.cancel": "Mégse",
        "common.delete": "Törlés",
        "common.clear_filters": "Szűrők törlése",
        "common.actions": "Műveletek",
    };

    return {
        confirmRequire: vi.fn(),
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
    router: { get: vi.fn(), delete: vi.fn() },
    usePage: () => ({
        props: {
            preferences: { locale: "hu-HU", currency: "HUF" },
        },
    }),
    useForm: (defaults) => ({
        ...defaults,
        errors: {},
        reset: vi.fn(),
        clearErrors: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
    }),
}));

vi.mock("laravel-vue-i18n", () => ({
    trans: translate,
}));

vi.mock("primevue/useconfirm", () => ({
    useConfirm: () => ({ require: confirmRequire }),
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: {
        props: ["label", "ariaLabel"],
        emits: ["click"],
        template: '<button :aria-label="ariaLabel" @click="$emit(\'click\')">{{ label }}</button>',
    },
}));
vi.mock("primevue/confirmdialog", () => ({
    default: { template: "<div />" },
}));
vi.mock("primevue/datatable", () => ({
    default: {
        props: ["value"],
        template:
            '<div><slot name="empty" /><div v-for="row in value" :key="row.id">{{ row.name }} {{ row.slug }}</div><slot /></div>',
    },
}));
vi.mock("primevue/column", () => ({
    default: { props: ["header"], template: "<div>{{ header }}<slot /></div>" },
}));
vi.mock("primevue/inputtext", () => ({
    default: { props: ["placeholder"], template: '<input :placeholder="placeholder" />' },
}));
vi.mock("primevue/select", () => ({
    default: { template: "<div />" },
}));

const stubs = {
    AdminTableToolbar: {
        template: '<div><slot name="filters" /><slot name="actions" /></div>',
    },
    CreateModal: { template: "<div />" },
    EditModal: { template: "<div />" },
    IngredientStatusBadge: {
        props: ["active"],
        template: '<span>{{ active ? "active" : "inactive" }}</span>',
    },
    IngredientStockBadge: {
        props: ["currentStock", "minimumStock", "unit"],
        template: "<span>{{ currentStock }} {{ unit }}</span>",
    },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

const mountPage = (ingredients = []) =>
    mount(IngredientsIndexPage, {
        props: {
            ingredients: {
                data: ingredients,
                current_page: 1,
                per_page: 10,
                total: ingredients.length,
            },
            filters: {
                search: "",
                is_active: "",
                unit: "",
                sort_field: "name",
                sort_direction: "asc",
                per_page: 10,
            },
            units: ["kg", "db"],
        },
        global: {
            stubs,
            mocks: {
                $t: translate,
            },
        },
    });

describe("Admin Ingredients Index", () => {
    it("renders localized ingredient controls and rows", () => {
        const wrapper = mountPage([
            {
                id: 1,
                name: "Liszt",
                slug: "liszt",
                sku: "ALP-001",
                unit: "kg",
                estimated_unit_cost: 420,
                current_stock: 12,
                minimum_stock: 5,
                is_active: true,
                notes: null,
            },
        ]);

        expect(wrapper.text()).toContain("Admin / Alapanyagok");
        expect(wrapper.text()).toContain("Keresés");
        expect(wrapper.text()).toContain("Új alapanyag");
        expect(wrapper.text()).toContain("Alapanyag");
        expect(wrapper.text()).toContain("Becsült egységköltség");
        expect(wrapper.text()).toContain("Liszt");
    });

    it("renders localized empty state", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Nincs megjeleníthető adat");
        expect(wrapper.text()).toContain("Szűrők törlése");
    });
});
