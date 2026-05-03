import { mount } from "@vue/test-utils";
import PermissionSyncSummaryModal from "./PermissionSyncSummaryModal.vue";

vi.mock("primevue/dialog", () => ({
    default: { template: "<div><slot /><slot name=\"footer\" /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: { props: ["label"], template: "<button>{{ label }}</button>" },
}));

const translations = {
    "admin_permissions.sync_summary.title": "Jogosultság szinkron összegzés",
    "admin_permissions.sync_summary.created": "Létrehozva",
    "admin_permissions.sync_summary.existing": "Már létezett",
    "admin_permissions.sync_summary.orphans": "Árvák",
    "admin_permissions.sync_summary.created_permissions": "Létrehozott jogosultságok:",
    "admin_permissions.sync_summary.orphan_permissions": "Árva jogosultságok:",
    "admin_permissions.sync_summary.none": "Nincs",
    "admin_permissions.actions.ok": "Rendben",
};

describe("PermissionSyncSummaryModal", () => {
    it("renders sync summary content", () => {
        const wrapper = mount(PermissionSyncSummaryModal, {
            props: {
                visible: true,
                summary: {
                    created_count: 1,
                    existing_count: 10,
                    orphan_count: 2,
                    created_permissions: ["permissions.view"],
                    orphan_permissions: ["legacy.custom"],
                },
            },
            global: {
                mocks: {
                    $t: (key) => translations[key] ?? key,
                },
            },
        });

        expect(wrapper.text()).toContain("Létrehozva");
        expect(wrapper.text()).toContain("permissions.view");
        expect(wrapper.text()).toContain("legacy.custom");
    });
});
