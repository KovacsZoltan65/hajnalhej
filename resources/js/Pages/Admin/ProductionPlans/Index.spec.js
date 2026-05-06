import { mount } from "@vue/test-utils";
import ProductionPlansIndex from "./Index.vue";

const { translate, routerGet, routerDelete, confirmRequire, formPost, formPut } = vi.hoisted(() => {
    const translations = {
        "common.all": "Mind",
        "common.search": "Keresés",
        "common.clear_filters": "Szűrők törlése",
        "common.cancel": "Mégse",
        "common.delete": "Törlés",
        "common.actions": "Műveletek",
        "admin_production_plans.meta_title": "Gyártási tervek",
        "admin_production_plans.eyebrow": "Admin / Gyártástervezés",
        "admin_production_plans.title": "Gyártástervező",
        "admin_production_plans.description":
            "Célidő alapú gyártástervezés: mennyiségek, időzítés és összesített alapanyagigény egy helyen.",
        "admin_production_plans.summary.total_plans": "Tervek",
        "admin_production_plans.summary.ready_plans": "Kész",
        "admin_production_plans.summary.draft_plans": "Piszkozat",
        "admin_production_plans.summary.total_recipe_minutes": "Össz receptidő",
        "common.search": "Keresés",
        "admin_production_plans.filters.search_placeholder": "Tervszám vagy termék",
        "common.status": "Státusz",
        "admin_production_plans.filters.target_from": "Célidő -tól",
        "admin_production_plans.filters.target_to": "Célidő -ig",
        "table.rows_per_page": "Találat / oldal",
        "admin_production_plans.filters.per_page_option": ":count / oldal",
        "admin_production_plans.columns.plan": "Terv",
        "admin_production_plans.columns.target_time": "Célidő",
        "admin_production_plans.columns.planned_start": "Javasolt kezdés",
        "admin_production_plans.columns.status": "Státusz",
        "admin_production_plans.columns.total_time_minutes": "Teljes idő (perc)",
        "admin_production_plans.columns.items_count": "Tétel db",
        "admin_production_plans.actions.create": "Új gyártási terv",
        "admin_production_plans.actions.create_flow": "Új gyártási terv oldal",
        "admin_production_plans.actions.edit": "Gyártási terv szerkesztése",
        "admin_production_plans.actions.delete": "Gyártási terv törlése",
        "admin_production_plans.empty": "Nincs gyártási terv.",
        "admin_production_plans.units.minutes": ":count perc",
        "admin.production_plans.validation.past_date": "A megadott időpont nem lehet korábbi, mint a jelenlegi idő.",
        "admin.production_plans.validation.too_early_for_recipe":
            "A megadott elkészülési idő túl korai a kiválasztott termékek receptideje alapján. Legkorábbi lehetséges időpont: :datetime.",
    };

    return {
        routerGet: vi.fn(),
        routerDelete: vi.fn(),
        confirmRequire: vi.fn(),
        formPost: vi.fn(),
        formPut: vi.fn(),
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
    router: { get: routerGet, delete: routerDelete },
    useForm: (values) => ({
        ...values,
        errors: {},
        processing: false,
        clearErrors: vi.fn(),
        setError(field, message) {
            this.errors[field] = message;
        },
        transform(callback) {
            this.payload = callback();
            return this;
        },
        post: formPost,
        put: formPut,
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

vi.mock("@/Components/Admin/ProductionPlans/CreateModal.vue", () => ({
    default: { template: "<div />" },
}));

vi.mock("@/Components/Admin/ProductionPlans/EditModal.vue", () => ({
    default: { template: "<div />" },
}));

const stubs = {
    AdminTableToolbar: {
        template: '<div><slot name="filters" /><slot name="actions" /></div>',
    },
    Button: {
        props: ["label", "ariaLabel"],
        emits: ["click"],
        template:
            '<button type="button" :aria-label="ariaLabel" @click="$emit(\'click\')">{{ label }}<slot /></button>',
    },
    Column: {
        props: ["header"],
        template: "<div>{{ header }}<slot /></div>",
    },
    ConfirmDialog: {
        template: "<div />",
    },
    DataTable: {
        props: ["value"],
        template:
            '<div><slot name="empty" /><div v-for="row in value" :key="row.id">{{ row.plan_number }}</div><slot /></div>',
    },
    InputText: {
        props: ["modelValue", "placeholder"],
        template: '<input :value="modelValue" :placeholder="placeholder" />',
    },
    BaseDatePicker: {
        props: ["modelValue"],
        emits: ["update:modelValue"],
        template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
    Select: {
        props: ["modelValue", "options", "optionLabel", "optionValue"],
        template: "<select />",
    },
};

describe("Admin Production Plans Index", () => {
    it("renders localized production planning controls and table headings", () => {
        const wrapper = mount(ProductionPlansIndex, {
            props: {
                productionPlans: {
                    data: [
                        {
                            id: 1,
                            plan_number: "PLAN-001",
                            status: "draft",
                            items: [],
                            details: {},
                        },
                    ],
                    current_page: 1,
                    per_page: 10,
                    total: 1,
                },
                products: [{ id: 1, name: "Kovászos kenyér" }],
                statuses: [{ value: "draft", label: "Piszkozat" }],
                filters: {},
                summary: {
                    total_plans: 3,
                    ready_plans: 1,
                    draft_plans: 2,
                    total_recipe_minutes: 420,
                },
            },
            global: {
                stubs,
                mocks: { $t: translate },
            },
        });

        expect(wrapper.text()).toContain("Admin / Gyártástervezés");
        expect(wrapper.text()).toContain("Gyártástervező");
        expect(wrapper.text()).toContain("Össz receptidő");
        expect(wrapper.text()).toContain("420 perc");
        expect(wrapper.find('input[placeholder="Tervszám vagy termék"]').exists()).toBe(true);
        expect(wrapper.text()).toContain("Új gyártási terv");
        expect(wrapper.text()).toContain("Javasolt kezdés");
        expect(wrapper.text()).toContain("PLAN-001");
    });

    it("blocks modal create submit for a past target time", () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date("2026-05-06T10:00:00"));
        formPost.mockClear();
        const wrapper = mount(ProductionPlansIndex, {
            props: {
                productionPlans: {
                    data: [],
                    current_page: 1,
                    per_page: 10,
                    total: 0,
                },
                products: [
                    {
                        id: 1,
                        name: "Kovászos kenyér",
                        recipe_steps: [{ id: 1, duration_minutes: 30, wait_minutes: 30 }],
                    },
                ],
                statuses: [{ value: "draft", label: "Piszkozat" }],
                filters: {},
                summary: {
                    total_plans: 0,
                    ready_plans: 0,
                    draft_plans: 0,
                    total_recipe_minutes: 0,
                },
            },
            global: {
                stubs,
                mocks: { $t: translate },
            },
        });

        wrapper.vm.form.items = [{ product_id: 1, target_quantity: 1, unit_label: "db", sort_order: 0 }];
        wrapper.vm.form.target_ready_at = new Date("2026-05-06T10:30:00");
        wrapper.vm.submitCreate();

        expect(formPost).not.toHaveBeenCalled();
        expect(wrapper.vm.form.errors.target_ready_at).toContain(
            "A megadott elkészülési idő túl korai a kiválasztott termékek receptideje alapján."
        );
        vi.useRealTimers();
    });

    it("submits modal create ready time as local datetime instead of UTC ISO", () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date("2026-05-06T10:00:00"));
        formPost.mockClear();
        const wrapper = mount(ProductionPlansIndex, {
            props: {
                productionPlans: {
                    data: [],
                    current_page: 1,
                    per_page: 10,
                    total: 0,
                },
                products: [
                    {
                        id: 1,
                        name: "Kovászos kenyér",
                        recipe_steps: [{ id: 1, duration_minutes: 30, wait_minutes: 30 }],
                    },
                ],
                statuses: [{ value: "draft", label: "Piszkozat" }],
                filters: {},
                summary: {
                    total_plans: 0,
                    ready_plans: 0,
                    draft_plans: 0,
                    total_recipe_minutes: 0,
                },
            },
            global: {
                stubs,
                mocks: { $t: translate },
            },
        });

        wrapper.vm.form.items = [{ product_id: 1, target_quantity: 1, unit_label: "db", sort_order: 0 }];
        wrapper.vm.form.target_ready_at = new Date(2026, 4, 6, 17, 0, 0);
        wrapper.vm.submitCreate();

        expect(formPost).toHaveBeenCalledOnce();
        expect(wrapper.vm.form.payload.target_ready_at).toBe("2026-05-06 17:00:00");
        vi.useRealTimers();
    });
});
