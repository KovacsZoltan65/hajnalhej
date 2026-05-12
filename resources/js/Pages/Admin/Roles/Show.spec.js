import { mount } from "@vue/test-utils";
import RolesShowPage from "./Show.vue";

const { put, translate } = vi.hoisted(() => {
    const translations = {
        "admin_roles.role": "Szerepkör",
        "admin_roles.show.back_to_list": "Vissza a listára",
        "admin_roles.show.description": "A szerepkörhöz tartozó jogosultságok szinkronizálása modulonként.",
        "admin_roles.show.eyebrow": "Admin / Szerepkörök",
        "admin_roles.show.guard": "Guard",
        "admin_roles.show.meta_title": "Szerepkör - :name",
        "admin_roles.show.permission_matrix": "Jogosultságmátrix",
        "admin_roles.show.save_permissions": "Jogosultságok mentése",
        "admin_roles.show.selected_permissions_count": "Összesen :count jogosultság kiválasztva.",
        "admin_roles.show.title": "Szerepkör: :name",
        "admin_roles.show.users": "Felhasználók",
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
    Link: { name: "Link", props: ["href"], template: '<a :href="href"><slot /></a>' },
    useForm: (defaults) => ({
        ...defaults,
        processing: false,
        errors: {},
        put,
    }),
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

const localStubs = {
    RoleBadge: { props: ["role"], template: "<span>{{ role }}</span>" },
    RolePermissionMatrix: {
        props: ["modelValue", "groups", "disabled"],
        template: '<div class="permission-matrix">{{ Object.keys(groups).join(", ") }}</div>',
    },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

const baseProps = {
    role: {
        id: 1,
        name: "admin",
        guard_name: "web",
        users_count: 2,
        is_system_role: true,
        permissions: ["products.view", "products.update"],
    },
    permission_groups: {
        products: [
            { name: "products.view", label: "Termékek megtekintése" },
            { name: "products.update", label: "Termékek szerkesztése" },
        ],
    },
    can: {
        assign_permissions: true,
    },
};

const mountPage = (props = {}) =>
    mount(RolesShowPage, {
        props: {
            ...baseProps,
            ...props,
        },
        global: {
            stubs: localStubs,
            mocks: {
                $t: translate,
                route: (name, id = "") => `/${name}${id ? `/${id}` : ""}`,
            },
        },
    });

describe("Admin Roles Show", () => {
    it("renders localized role details and permission summary", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Admin / Szerepkörök");
        expect(wrapper.text()).toContain("Szerepkör: admin");
        expect(wrapper.text()).toContain("Vissza a listára");
        expect(wrapper.text()).toContain("Szerepkör");
        expect(wrapper.text()).toContain("Guard");
        expect(wrapper.text()).toContain("Felhasználók");
        expect(wrapper.text()).toContain("Jogosultságmátrix");
        expect(wrapper.text()).toContain("Összesen 2 jogosultság kiválasztva.");
        expect(wrapper.text()).toContain("Jogosultságok mentése");
    });

    it("hides save action when permission assignment is not allowed", () => {
        const wrapper = mountPage({
            can: {
                assign_permissions: false,
            },
        });

        expect(wrapper.text()).not.toContain("Jogosultságok mentése");
    });
});
