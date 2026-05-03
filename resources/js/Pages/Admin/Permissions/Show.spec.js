import { mount } from "@vue/test-utils";
import PermissionsShowPage from "./Show.vue";

const { translate } = vi.hoisted(() => {
    const translations = {
        "admin_permissions.show_meta_title": "Jogosultság - :name",
        "admin_permissions.show_eyebrow": "Admin / Jogosultságok",
        "admin_permissions.show_title": "Jogosultság: :name",
        "admin_permissions.show_description": "Registry metadata, használat és drift állapot áttekintése.",
        "admin_permissions.actions.back_to_list": "Vissza a listára",
        "admin_permissions.fields.permission": "Jogosultság",
        "admin_permissions.fields.registry_state": "Registry állapot",
        "admin_permissions.fields.label": "Label",
        "admin_permissions.fields.module": "Modul",
        "admin_permissions.fields.description": "Leírás",
        "admin_permissions.fields.dangerous": "Veszélyes",
        "admin_permissions.fields.guard": "Guard",
        "admin_permissions.fields.audit_sensitive": "Auditérzékeny",
        "admin_permissions.values.yes": "Igen",
        "admin_permissions.values.no": "Nem",
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
    Link: { name: "Link", props: ["href"], template: '<a :href="href"><slot /></a>' },
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: { props: ["label"], template: "<button>{{ label }}</button>" },
}));

const stubs = {
    PermissionBadge: { props: ["name"], template: "<span>{{ name }}</span>" },
    PermissionDangerBadge: { template: "<span>danger</span>" },
    PermissionRegistryStateBadge: { props: ["state"], template: "<span>{{ state }}</span>" },
    PermissionUsageCard: { template: "<div>usage-card</div>" },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

describe("Admin Permissions Show", () => {
    it("renders localized permission details and usage card", () => {
        const wrapper = mount(PermissionsShowPage, {
            props: {
                permission: {
                    name: "permissions.view",
                    registry_state: "synced",
                    label: "Jogosultságok megtekintése",
                    module: "Roles & Permissions",
                    description: "Permission lista megtekintése",
                    dangerous: false,
                    guard_name: "web",
                    audit_sensitive: false,
                    roles_count: 1,
                    users_count: 1,
                    role_names: ["admin"],
                },
            },
            global: {
                stubs,
                mocks: { $t: translate },
            },
        });

        expect(wrapper.text()).toContain("Jogosultság: permissions.view");
        expect(wrapper.text()).toContain("Registry állapot");
        expect(wrapper.text()).toContain("Vissza a listára");
        expect(wrapper.text()).toContain("usage-card");
    });
});

