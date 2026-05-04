import { mount } from "@vue/test-utils";
import IndexPage from "./Index.vue";

const { translate } = vi.hoisted(() => {
    const translations = {
        "admin_inventory.filters.per_page_option": ":count / oldal",
        "admin_inventory.meta_title": "Készletmozgások",
        "admin_inventory.eyebrow": "Admin / Készletmozgások",
        "admin_inventory.title": "Készletmozgások",
        "admin_inventory.description":
            "Termékek mintájára egységes, szűrhető és lapozható készlet főkönyv gyors műveletekkel.",
        "admin_inventory.waste_reason_expired": "lejárt",
        "admin_inventory.summary.total_stock_value": "Készletérték",
        "admin_inventory.summary.low_stock_count": "Alacsony készlet",
        "admin_inventory.summary.out_of_stock_count": "Kifogyott",
        "admin_inventory.summary.weekly_waste_cost": "Heti selejtérték",
        "admin_inventory.summary.weekly_purchase_value": "Heti bevételezés",
        "admin_inventory.filters.search": "Keresés",
        "admin_inventory.filters.search_placeholder": "Jegyzet vagy referencia",
        "common.type": "Típus",
        "admin_inventory.filters.ingredient": "Alapanyag",
        "admin_inventory.filters.days": "Nap",
        "admin_inventory.filters.date_from": "Dátumtól",
        "admin_inventory.filters.date_to": "Dátumig",
        "admin_inventory.filters.per_page": "Oldalméret",
        "admin_inventory.actions.search": "Keresés",
        "admin_inventory.actions.waste": "Selejtezés",
        "admin_inventory.actions.adjustment": "Korrekció",
        "admin_inventory.empty": "Nincs megjeleníthető készletmozgás.",
        "admin_inventory.columns.date": "Dátum",
        "admin_inventory.columns.ingredient": "Alapanyag",
        "admin_inventory.columns.type": "Típus",
        "common.quantity": "Mennyiség",
        "admin_inventory.columns.unit_cost": "Egységár",
        "admin_inventory.columns.value": "Érték",
        "admin_inventory.columns.reference": "Referencia",
        "admin_inventory.movement_types.purchase_in": "Bevételezés",
        "common.all": "Mind",
        "common.clear_filters": "Szűrők törlése",
        "common.day_count": ":count nap",
        "common.locale": "hu-HU",
        "common.currency": "HUF",
    };

    return {
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
    router: { get: vi.fn() },
    useForm: (data) => ({
        ...data,
        processing: false,
        errors: {},
        post: vi.fn(),
        reset: vi.fn(),
        clearErrors: vi.fn(),
    }),
}));

vi.mock("laravel-vue-i18n", () => ({
    trans: translate,
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("@/Components/Admin/AdminTableToolbar.vue", () => ({
    default: {
        template: '<div><slot name="filters" /><slot name="actions" /></div>',
    },
}));

vi.mock("@/Components/Admin/Inventory/WasteEntryModal.vue", () => ({
    default: {
        props: ["visible"],
        template: '<div v-if="visible">waste modal</div>',
    },
}));

vi.mock("@/Components/Admin/Inventory/AdjustmentModal.vue", () => ({
    default: {
        props: ["visible"],
        template: '<div v-if="visible">adjustment modal</div>',
    },
}));

vi.mock("primevue/button", () => ({
    default: {
        props: ["label"],
        template: "<button>{{ label }}<slot /></button>",
    },
}));
vi.mock("primevue/inputtext", () => ({ default: { template: "<input />" } }));
vi.mock("primevue/datepicker", () => ({
    default: {
        props: ["modelValue"],
        emits: ["update:modelValue"],
        template: '<div class="datepicker-stub"></div>',
    },
}));
vi.mock("primevue/select", () => ({
    default: {
        props: ["modelValue", "options"],
        emits: ["update:modelValue"],
        template: '<div class="select-stub"></div>',
    },
}));
vi.mock("primevue/datatable", () => ({
    default: { template: '<div><slot /><slot name="empty" /></div>' },
}));
vi.mock("primevue/column", () => ({
    default: { template: '<div><slot name="body" :data="{}" /></div>' },
}));
vi.mock("@/Components/SectionTitle.vue", () => ({
    default: {
        props: ["eyebrow", "title", "description"],
        template: "<div>{{ eyebrow }} {{ title }} {{ description }}</div>",
    },
}));

describe("Admin inventory page", () => {
    it("renders summary cards and ledger section", () => {
        const wrapper = mount(IndexPage, {
            props: {
                dashboard: {
                    summary: {
                        total_stock_value: 1000,
                        low_stock_count: 1,
                        out_of_stock_count: 0,
                        weekly_waste_cost: 50,
                        weekly_purchase_value: 300,
                    },
                },
                ledger: { data: [], current_page: 1, per_page: 15, total: 0 },
                filters: {},
                movement_types: [],
                ingredient_options: [],
                product_options: [],
                waste_reasons: [],
            },
            global: {
                mocks: {
                    $t: translate,
                },
            },
        });

        expect(wrapper.text()).toContain("Készletmozgások");
        expect(wrapper.text()).toContain("Készletérték");
        expect(wrapper.text()).toContain("Nincs megjeleníthető készletmozgás.");
    });
});
