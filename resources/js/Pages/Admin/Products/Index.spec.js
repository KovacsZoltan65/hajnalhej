import { mount } from "@vue/test-utils";
import ProductsIndexPage from "./Index.vue";

const { confirmRequire, translate } = vi.hoisted(() => {
    const translations = {
        "admin.common.empty.title": "Nincs megjeleníthető adat",
        "admin.common.empty.description": "Még nincs megjeleníthető adat ebben a nézetben.",
        "admin.common.empty.no_results_description": "A jelenlegi szűrők mellett nincs találat.",
        "admin.products.flow.actions.open": "Új termék oldal",
        "admin_product.actions.create": "Új termék",
        "admin_product.actions.delete": "Termék törlése",
        "admin_product.actions.edit": "Termék szerkesztése",
        "admin_product.actions.open_recipes": "Receptek oldal",
        "admin_product.actions.recipe": "Recept",
        "admin_product.columns.actions": "Műveletek",
        "admin_product.columns.name": "Név",
        "admin_product.columns.price": "Ár",
        "admin_product.columns.status": "Státusz",
        "admin_product.delete.header": "Termék törlése",
        "admin_product.delete.message": "Biztosan törlöd ezt a terméket: :name",
        "admin_product.description": "Teljes termékkezelés",
        "admin_product.filters.all_categories": "Mind",
        "common.admin_products": "Admin / Termékek",
        "common.cancel": "Mégse",
        "common.category": "Kategória",
        "common.clear_filters": "Szűrők törlése",
        "common.delete": "Törlés",
        "common.products": "Termékek",
        "common.rows_per_page": "Találat / oldal",
        "common.search": "Keresés",
        "common.search_placeholder": "Keresés",
        "common.status": "Státusz",
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
    Link: { props: ["href"], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn(), delete: vi.fn() },
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
vi.mock("primevue/column", () => ({
    default: {
        props: ["header"],
        template:
            "<div><span>{{ header }}</span><slot name=\"body\" :data=\"{ id: 1, name: 'Kovászos kenyér', slug: 'kovaszos-kenyer', category_id: 1, category_name: 'Kenyér', price: 1200, is_active: true }\" /></div>",
    },
}));
vi.mock("primevue/confirmdialog", () => ({
    default: { template: "<div />" },
}));
vi.mock("primevue/inputtext", () => ({
    default: { props: ["placeholder"], template: '<input :placeholder="placeholder" />' },
}));
vi.mock("primevue/select", () => ({
    default: {
        props: ["options", "optionLabel"],
        template:
            '<div><span v-for="option in options" :key="option.id ?? option.value">{{ option[optionLabel] }}</span></div>',
    },
}));

const stubs = {
    AdminTableToolbar: {
        template: '<div><slot name="filters" /><slot name="actions" /></div>',
    },
    BaseDataTable: {
        props: ["value", "emptyTitle", "emptyDescription", "emptySecondaryLabel"],
        template:
            '<div><div v-if="!value.length">{{ emptyTitle }} {{ emptyDescription }} {{ emptySecondaryLabel }}</div><slot /></div>',
    },
    CreateModal: { template: "<div />" },
    EditModal: { template: "<div />" },
    EntityStatusBadge: {
        props: ["status"],
        template: '<span>{{ status ? "active" : "inactive" }}</span>',
    },
    InlineEditableNumber: {
        props: ["modelValue"],
        template: "<span>{{ modelValue }}</span>",
    },
    InlineEditableSelect: {
        props: ["modelValue"],
        template: "<span>{{ modelValue }}</span>",
    },
    InlineEditableToggle: {
        props: ["modelValue"],
        template: "<span>{{ modelValue }}</span>",
    },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

const mountPage = (products = []) =>
    mount(ProductsIndexPage, {
        props: {
            products: {
                data: products,
                current_page: 1,
                per_page: 10,
                total: products.length,
            },
            categories: [{ id: 1, name: "Kenyér" }],
            stockStatuses: [
                { value: "in_stock", label: "Raktáron" },
                { value: "out_of_stock", label: "Nincs készleten" },
            ],
            filters: {
                search: "",
                category_id: null,
                is_active: "",
                sort_field: "sort_order",
                sort_direction: "asc",
                per_page: 10,
            },
        },
        global: {
            stubs,
            mocks: {
                $t: translate,
                route: (name, params = {}) => `/${name}${params.product_id ? `?product_id=${params.product_id}` : ""}`,
            },
        },
    });

describe("Admin Products Index", () => {
    it("renders localized product table controls and actions", () => {
        const wrapper = mountPage([
            {
                id: 1,
                name: "Kovászos kenyér",
                slug: "kovaszos-kenyer",
                category_id: 1,
                category_name: "Kenyér",
                price: 1200,
                is_active: true,
            },
        ]);

        expect(wrapper.text()).toContain("Admin / Termékek");
        expect(wrapper.text()).toContain("Mind");
        expect(wrapper.text()).toContain("Receptek oldal");
        expect(wrapper.text()).toContain("Keresés");
        expect(wrapper.text()).toContain("Új termék");
        expect(wrapper.text()).toContain("Név");
        expect(wrapper.text()).toContain("Ár");
        expect(wrapper.text()).toContain("Státusz");
        expect(wrapper.text()).toContain("Műveletek");
        expect(wrapper.text()).toContain("Recept");
        expect(wrapper.find('[aria-label="Termék szerkesztése"]').exists()).toBe(true);
        expect(wrapper.find('[aria-label="Termék törlése"]').exists()).toBe(true);
    });

    it("renders localized empty state", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Nincs megjeleníthető adat");
        expect(wrapper.text()).toContain("Szűrők törlése");
    });
});
