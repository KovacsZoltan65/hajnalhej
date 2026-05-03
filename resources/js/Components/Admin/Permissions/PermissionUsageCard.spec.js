import { mount } from "@vue/test-utils";
import PermissionUsageCard from "./PermissionUsageCard.vue";

const translations = {
    "admin_permissions.usage_card.title": "Használat",
    "admin_permissions.usage_card.role_count": "Szerepkör darab",
    "admin_permissions.usage_card.user_count": "Felhasználó darab",
    "admin_permissions.usage_card.roles": "Szerepkörök",
    "admin_permissions.usage_card.no_roles": "Nincs kapcsolódó szerepkör.",
};

describe("PermissionUsageCard", () => {
    it("renders role and user usage numbers", () => {
        const wrapper = mount(PermissionUsageCard, {
            props: {
                rolesCount: 2,
                usersCount: 4,
                roleNames: ["admin", "manager"],
            },
            global: {
                mocks: {
                    $t: (key) => translations[key] ?? key,
                },
            },
        });

        expect(wrapper.text()).toContain("Használat");
        expect(wrapper.text()).toContain("2");
        expect(wrapper.text()).toContain("4");
        expect(wrapper.text()).toContain("admin");
    });
});
