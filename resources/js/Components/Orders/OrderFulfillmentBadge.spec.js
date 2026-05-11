import { mount } from "@vue/test-utils";
import OrderFulfillmentBadge from "./OrderFulfillmentBadge.vue";

describe("OrderFulfillmentBadge", () => {
    it("renders pickup fulfillment label", () => {
        const wrapper = mount(OrderFulfillmentBadge, {
            props: { method: "pickup", label: "Átvétel" },
        });

        expect(wrapper.text()).toContain("Átvétel");
    });

    it("falls back to raw method for unknown fulfillment method", () => {
        const wrapper = mount(OrderFulfillmentBadge, {
            props: { method: "custom_method" },
        });

        expect(wrapper.text()).toContain("custom_method");
    });
});
