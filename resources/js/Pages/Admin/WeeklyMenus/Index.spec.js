import { mount } from "@vue/test-utils";
import WeeklyMenusIndexPage from "./Index.vue";

const { confirmRequire, translate } = vi.hoisted(() => {
    const translations = {
        "admin.common.empty.description": "Még nincs megjeleníthető adat ebben a nézetben.",
        "admin.common.empty.no_results_description": "A jelenlegi szűrők mellett nincs találat.",
        "admin.common.empty.title": "Nincs megjeleníthető adat",
        "admin_weekly_menus.actions.create": "Új heti menü",
        "admin_weekly_menus.actions.delete": "Heti menü törlése",
        "admin_weekly_menus.actions.items": "Heti menü tételei",
        "admin_weekly_menus.actions.publish": "Közzététel",
        "admin_weekly_menus.actions.unpublish": "Közzététel visszavonása",
        "admin_weekly_menus.columns.status": "Státusz",
        "admin_weekly_menus.columns.title": "Cím",
        "admin_weekly_menus.columns.week": "Hét",
        "admin_weekly_menus.confirm_delete_header": "Heti menü törlése",
        "admin_weekly_menus.confirm_delete_message": "Biztosan törlöd ezt a heti menüt: :title?",
        "admin_weekly_menus.description":
            "A heti kínálat kezelési modulja termék-hozzárendeléssel és publikációs folyamattal.",
        "admin_weekly_menus.eyebrow": "Admin / Heti menük",
        "admin_weekly_menus.filters.search_placeholder": "Cím vagy slug",
        "admin_weekly_menus.title": "Heti menük",
        "common.actions": "Műveletek",
        "common.all": "Mind",
        "common.cancel": "Mégse",
        "common.clear_filters": "Szűrők törlése",
        "common.delete": "Törlés",
        "common.items": "Tételek",
        "common.rows_per_page": "Találat / oldal",
        "common.search": "Keresés",
        "common.status": "Állapot",
        "common.weekly_menu_edit": "Heti menü szerkesztése",
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
    router: {
        delete: vi.fn(),
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        reload: vi.fn(),
    },
    useForm: (defaults) => ({
        ...defaults,
        processing: false,
        errors: {},
        clearErrors: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        reset: vi.fn(),
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
            "<div><span>{{ header }}</span><slot name=\"body\" :data=\"{ id: 1, title: 'Pünkösdi menü', slug: 'punkosdi-menu', week_start: '2026-05-18', week_end: '2026-05-24', status: 'draft', items_count: 4 }\" /></div>",
    },
}));
vi.mock("primevue/confirmdialog", () => ({
    default: { template: "<div />" },
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
    InlineEditableSelect: {
        props: ["modelValue"],
        template: "<span>{{ modelValue }}</span>",
    },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
    WeeklyMenuItemsModal: { template: "<div />" },
    WeeklyMenuStatusBadge: {
        props: ["status"],
        template: "<span>{{ status }}</span>",
    },
};

const baseProps = {
    weeklyMenus: {
        data: [
            {
                id: 1,
                title: "Pünkösdi menü",
                slug: "punkosdi-menu",
                week_start: "2026-05-18",
                week_end: "2026-05-24",
                status: "draft",
                items_count: 4,
            },
        ],
        current_page: 1,
        per_page: 10,
        total: 1,
    },
    filters: {
        search: "",
        status: "",
        sort_field: "week_start",
        sort_direction: "desc",
        per_page: 10,
    },
    statuses: [
        { value: "draft", label: "Piszkozat" },
        { value: "published", label: "Közzétéve" },
    ],
    products: [],
};

const mountPage = (props = {}) =>
    mount(WeeklyMenusIndexPage, {
        props: {
            ...baseProps,
            ...props,
        },
        global: {
            stubs,
            mocks: {
                $t: translate,
                route: (name, params = "") => `/${name}${params ? `/${params}` : ""}`,
            },
        },
    });

describe("Admin Weekly Menus Index", () => {
    it("renders localized weekly menu controls and table actions", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Admin / Heti menük");
        expect(wrapper.text()).toContain("Heti menük");
        expect(wrapper.text()).toContain("Keresés");
        expect(wrapper.find('[placeholder="Cím vagy slug"]').exists()).toBe(true);
        expect(wrapper.text()).toContain("Mind");
        expect(wrapper.text()).toContain("Találat / oldal");
        expect(wrapper.text()).toContain("Új heti menü");
        expect(wrapper.text()).toContain("Cím");
        expect(wrapper.text()).toContain("Hét");
        expect(wrapper.text()).toContain("Státusz");
        expect(wrapper.text()).toContain("Tételek");
        expect(wrapper.text()).toContain("Műveletek");
        expect(wrapper.text()).toContain("Közzététel");
        expect(wrapper.text()).toContain("Pünkösdi menü");
    });

    it("renders localized empty state", () => {
        const wrapper = mountPage({
            weeklyMenus: {
                ...baseProps.weeklyMenus,
                data: [],
                total: 0,
            },
        });

        expect(wrapper.text()).toContain("Nincs megjeleníthető adat");
        expect(wrapper.text()).toContain("Szűrők törlése");
    });
});
