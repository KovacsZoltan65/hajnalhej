import { mount } from "@vue/test-utils";
import UserRolesIndexPage from "./Index.vue";

const { put, translate } = vi.hoisted(() => {
    const translations = {
        "admin_user_roles.actions.manage_roles": "Szerepkörök kezelése",
        "admin_user_roles.columns.effective_permissions": "Effektív jogosultságok",
        "admin_user_roles.description": "Felhasználók szerepköreinek kezelése és effektív jogosultságaik áttekintése.",
        "admin_user_roles.empty": "Nincs megjeleníthető felhasználó.",
        "admin_user_roles.eyebrow": "Admin / Felhasználói szerepkörök",
        "admin_user_roles.filters.search_placeholder": "Név vagy email...",
        "admin_user_roles.meta_title": "Felhasználói szerepkörök",
        "admin_user_roles.title": "Felhasználói szerepkörök",
        "common.actions": "Műveletek",
        "common.clear_filters": "Szűrők törlése",
        "common.email": "Email",
        "common.name": "Név",
        "common.roles": "Szerepkörök",
        "common.rows_per_page": "Találat / oldal",
        "common.search": "Keresés",
    };

    return {
        put: vi.fn(),
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
    useForm: (defaults) => ({
        ...defaults,
        processing: false,
        errors: {},
        clearErrors: vi.fn(),
        put,
    }),
}));

vi.mock("laravel-vue-i18n", () => ({
    trans: translate,
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: {
        props: ["label", "disabled"],
        emits: ["click"],
        template: '<button :disabled="disabled" @click="$emit(\'click\')">{{ label }}</button>',
    },
}));
vi.mock("primevue/column", () => ({
    default: {
        props: ["header"],
        template:
            "<div><span>{{ header }}</span><slot name=\"body\" :data=\"{ id: 1, roles: ['admin'], permissions: ['products.view', 'products.update'] }\" /></div>",
    },
}));
vi.mock("primevue/datatable", () => ({
    default: {
        props: ["value"],
        template:
            '<div><div v-if="!value.length"><slot name="empty" /></div><div v-for="row in value" :key="row.id">{{ row.name }} {{ row.email }}</div><slot /></div>',
    },
}));
vi.mock("primevue/inputtext", () => ({
    default: {
        props: ["placeholder"],
        template: '<input :placeholder="placeholder" />',
    },
}));
vi.mock("primevue/select", () => ({
    default: { template: '<div class="select-stub"></div>' },
}));

const stubs = {
    AdminTableToolbar: {
        template: '<div><slot name="filters" /><slot name="actions" /></div>',
    },
    RoleBadge: {
        props: ["role"],
        template: "<span>{{ role }}</span>",
    },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
    UserRoleAssignmentModal: { template: "<div />" },
};

const baseProps = {
    users: {
        data: [
            {
                id: 1,
                name: "Admin User",
                email: "admin@example.test",
                roles: ["admin"],
                permissions: ["products.view", "products.update"],
            },
        ],
        current_page: 1,
        per_page: 15,
        total: 1,
    },
    role_options: [{ name: "admin", is_system_role: true }],
    filters: {
        search: "",
        per_page: 15,
    },
    can: {
        assign_roles: true,
        view_permissions: true,
    },
};

const mountPage = (props = {}) =>
    mount(UserRolesIndexPage, {
        props: {
            ...baseProps,
            ...props,
        },
        global: {
            stubs,
            mocks: {
                $t: translate,
                route: (name, id = "") => `/${name}${id ? `/${id}` : ""}`,
            },
        },
    });

describe("Admin User Roles Index", () => {
    it("renders localized user role table controls and actions", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Admin / Felhasználói szerepkörök");
        expect(wrapper.text()).toContain("Felhasználói szerepkörök");
        expect(wrapper.text()).toContain("Keresés");
        expect(wrapper.find('[placeholder="Név vagy email..."]').exists()).toBe(true);
        expect(wrapper.text()).toContain("Találat / oldal");
        expect(wrapper.text()).toContain("Név");
        expect(wrapper.text()).toContain("Email");
        expect(wrapper.text()).toContain("Szerepkörök");
        expect(wrapper.text()).toContain("Effektív jogosultságok");
        expect(wrapper.text()).toContain("Műveletek");
        expect(wrapper.text()).toContain("Szerepkörök kezelése");
        expect(wrapper.text()).toContain("Admin User");
    });

    it("renders localized empty state", () => {
        const wrapper = mountPage({
            users: {
                ...baseProps.users,
                data: [],
                total: 0,
            },
        });

        expect(wrapper.text()).toContain("Nincs megjeleníthető felhasználó.");
        expect(wrapper.text()).toContain("Szűrők törlése");
    });
});
