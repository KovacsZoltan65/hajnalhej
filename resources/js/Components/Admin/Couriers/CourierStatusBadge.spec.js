import { mount } from "@vue/test-utils";
import CourierStatusBadge from "./CourierStatusBadge.vue";

describe("CourierStatusBadge", () => {
    it("renders active status", () => {
        const wrapper = mount(CourierStatusBadge, {
            props: { status: "active" },
            global: {
                mocks: {
                    $t: (key) => ({ "common.active": "Aktív" })[key] ?? key,
                },
            },
        });

        expect(wrapper.text()).toContain("Aktív");
    });

    it("renders inactive status", () => {
        const wrapper = mount(CourierStatusBadge, {
            props: { status: "inactive" },
            global: {
                mocks: {
                    $t: (key) => ({ "common.inactive": "Inaktív" })[key] ?? key,
                },
            },
        });

        expect(wrapper.text()).toContain("Inaktív");
    });
});
