import { mount } from "@vue/test-utils";
import SecurityDashboardIndex from "./Index.vue";

const { translate } = vi.hoisted(() => {
    const translations = {
        "security_dashboard.actions.apply_filters": "Szűrők alkalmazása",
        "security_dashboard.actions.permissions": "Jogosultságok",
        "security_dashboard.actions.roles": "Szerepkörök",
        "security_dashboard.actions.user_roles": "Felhasználói szerepkörök",
        "security_dashboard.description":
            "Jogosultsági kockázatok, árva anomáliák, kiemelt felhasználók és kritikus audit események egy helyen.",
        "security_dashboard.eyebrow": "Admin / Biztonság",
        "security_dashboard.filters.dangerous_only": "Csak veszélyes",
        "security_dashboard.filters.dangerous_only_items": "Csak veszélyes elemek",
        "security_dashboard.filters.log_domain": "Log domain",
        "security_dashboard.filters.risk_level": "Kockázati szint",
        "security_dashboard.filters.window": "Időablak",
        "security_dashboard.meta_title": "Biztonsági irányítópult",
        "security_dashboard.title": "Biztonsági irányítópult",
    };

    return {
        translate: (key) => translations[key] ?? key,
    };
});

vi.mock("@inertiajs/vue3", () => ({
    Head: { name: "Head", template: "<span />" },
    Link: { name: "Link", props: ["href"], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn() },
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: { props: ["label"], template: "<button>{{ label }}</button>" },
}));
vi.mock("primevue/select", () => ({
    default: { template: '<div class="select-stub"></div>' },
}));
vi.mock("primevue/checkbox", () => ({
    default: { template: '<input type="checkbox" />' },
}));

const stubs = {
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
    SecuritySummaryCard: { props: ["title", "value"], template: "<div>{{ title }} {{ value }}</div>" },
    PermissionRiskPanel: { template: "<div>Permission Risk Panel</div>" },
    OrphanPermissionsPanel: { template: "<div>Orphan Panel</div>" },
    PrivilegedUsersPanel: { template: "<div>Privileged Users Panel</div>" },
    RecentCriticalAuditEventsPanel: { template: "<div>Recent Critical Audit Events Panel</div>" },
};

describe("Admin Security Dashboard Index", () => {
    it("renders summary cards and panels", () => {
        const wrapper = mount(SecurityDashboardIndex, {
            props: {
                summary_cards: [{ title: "Dangerous permissions", value: "5", tone: "high" }],
                permission_risk: { total_permissions: 10, risk_distribution: { critical: 1 } },
                orphan_permissions: [],
                privileged_users: [],
                recent_critical_events: [],
                filters: { window: "7d", risk_level: "all", log_name: "all", dangerous_only: false },
                filter_options: {
                    windows: [{ label: "7d", value: "7d" }],
                    risk_levels: [{ label: "All", value: "all" }],
                    log_names: [{ label: "All domains", value: "all" }],
                },
                links: { permissions: "/admin/permissions", roles: "/admin/roles", user_roles: "/admin/user-roles" },
            },
            global: {
                stubs,
                mocks: {
                    $t: translate,
                },
            },
        });

        expect(wrapper.text()).toContain("Admin / Biztonság");
        expect(wrapper.text()).toContain("Biztonsági irányítópult");
        expect(wrapper.text()).toContain("Időablak");
        expect(wrapper.text()).toContain("Kockázati szint");
        expect(wrapper.text()).toContain("Log domain");
        expect(wrapper.text()).toContain("Csak veszélyes elemek");
        expect(wrapper.text()).toContain("Szűrők alkalmazása");
        expect(wrapper.text()).toContain("Jogosultságok");
        expect(wrapper.text()).toContain("Szerepkörök");
        expect(wrapper.text()).toContain("Felhasználói szerepkörök");
        expect(wrapper.text()).toContain("Dangerous permissions");
        expect(wrapper.text()).toContain("Permission Risk Panel");
        expect(wrapper.text()).toContain("Orphan Panel");
        expect(wrapper.text()).toContain("Privileged Users Panel");
        expect(wrapper.text()).toContain("Recent Critical Audit Events Panel");
    });
});
