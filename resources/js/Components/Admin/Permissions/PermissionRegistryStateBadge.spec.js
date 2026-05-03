import { mount } from "@vue/test-utils";
import PermissionRegistryStateBadge from "./PermissionRegistryStateBadge.vue";

const translations = {
    "admin_permissions.registry_states.synced": "Szinkronizált",
    "admin_permissions.registry_states.missing_in_db": "Hiányzik az adatbázisból",
    "admin_permissions.registry_states.orphan_db_only": "Csak adatbázisban",
};

vi.mock("laravel-vue-i18n", () => ({
    trans: (key) => translations[key] ?? key,
}));

describe("PermissionRegistryStateBadge", () => {
    it("renders human readable registry state", () => {
        const wrapper = mount(PermissionRegistryStateBadge, {
            props: { state: "missing_in_db" },
        });

        expect(wrapper.text()).toContain("Hiányzik az adatbázisból");
    });
});
