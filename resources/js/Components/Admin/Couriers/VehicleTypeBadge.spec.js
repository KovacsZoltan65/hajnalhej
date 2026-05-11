import { mount } from "@vue/test-utils";
import VehicleTypeBadge from "./VehicleTypeBadge.vue";

describe("VehicleTypeBadge", () => {
    it("renders the vehicle label", () => {
        const wrapper = mount(VehicleTypeBadge, {
            props: {
                type: "bicycle",
                label: "Bicikli",
            },
            global: {
                mocks: {
                    $t: (key) => key,
                },
            },
        });

        expect(wrapper.text()).toContain("Bicikli");
    });
});
