import { mount } from "@vue/test-utils";
import PermissionDangerBadge from "./PermissionDangerBadge.vue";

const translations = {
    "admin_permissions.risk.dangerous": "Veszélyes",
    "admin_permissions.risk.safe": "Biztonságos",
};

vi.mock("laravel-vue-i18n", () => ({
    trans: (key) => translations[key] ?? key,
}));

describe("PermissionDangerBadge", () => {
    it("renders dangerous label", () => {
        const wrapper = mount(PermissionDangerBadge, {
            props: { dangerous: true },
        });

        expect(wrapper.text()).toContain("Veszélyes");
    });
});
