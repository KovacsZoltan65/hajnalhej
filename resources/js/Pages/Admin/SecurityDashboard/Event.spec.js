import { mount } from "@vue/test-utils";
import SecurityDashboardEvent from "./Event.vue";

const { translate } = vi.hoisted(() => {
    const translations = {
        "audit_logs.columns.created_at": "Időpont",
        "common.description": "Leírás",
        "security_dashboard.event.back_to_dashboard": "Vissza a biztonsági irányítópultra",
        "security_dashboard.event.description": "Részletes audit nézet a biztonsági irányítópultból.",
        "security_dashboard.event.event_key": "Esemény kulcs",
        "security_dashboard.event.eyebrow": "Admin / Biztonság / Audit",
        "security_dashboard.event.log": "Log",
        "security_dashboard.event.meta_title": "Audit esemény #:id",
        "security_dashboard.event.properties_json": "Tulajdonságok JSON",
        "security_dashboard.event.title": "Audit esemény #:id",
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

const stubs = {
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

describe("Security Dashboard Event page", () => {
    it("renders event details", () => {
        const wrapper = mount(SecurityDashboardEvent, {
            props: {
                event: {
                    id: 12,
                    log_name: "authorization",
                    event_key: "permissions.synced",
                    description: "Permissions synced",
                    created_at: "2026-04-22T09:00:00+02:00",
                    properties: { event_key: "permissions.synced" },
                },
            },
            global: {
                stubs,
                mocks: {
                    $t: translate,
                    route: (name) => `/${name}`,
                },
            },
        });

        expect(wrapper.text()).toContain("Admin / Biztonság / Audit");
        expect(wrapper.text()).toContain("Audit esemény #12");
        expect(wrapper.text()).toContain("Esemény kulcs");
        expect(wrapper.text()).toContain("Időpont");
        expect(wrapper.text()).toContain("Leírás");
        expect(wrapper.text()).toContain("Tulajdonságok JSON");
        expect(wrapper.text()).toContain("Vissza a biztonsági irányítópultra");
        expect(wrapper.text()).toContain("authorization");
        expect(wrapper.text()).toContain("permissions.synced");
    });
});
