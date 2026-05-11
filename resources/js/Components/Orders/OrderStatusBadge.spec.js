import { mount } from "@vue/test-utils";
import OrderStatusBadge from "./OrderStatusBadge.vue";

const { translate } = vi.hoisted(() => {
    const translations = {
        "order_status.ready_for_pickup": "Átvételre kész",
    };

    return {
        translate: (key) => translations[key] ?? key,
    };
});

vi.mock("laravel-vue-i18n", () => ({
    trans: translate,
}));

describe("OrderStatusBadge", () => {
    it("renders known status label", () => {
        const wrapper = mount(OrderStatusBadge, {
            props: { status: "ready_for_pickup" },
        });

        expect(wrapper.text()).toContain("Átvételre kész");
    });

    it("falls back to raw status for unknown status", () => {
        const wrapper = mount(OrderStatusBadge, {
            props: { status: "custom_status" },
        });

        expect(wrapper.text()).toContain("custom_status");
    });
});
