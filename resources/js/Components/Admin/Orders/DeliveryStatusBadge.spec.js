import { mount } from "@vue/test-utils";
import DeliveryStatusBadge from "./DeliveryStatusBadge.vue";

vi.mock("laravel-vue-i18n", () => ({
    trans: (key) => ({ "delivery.statuses.out_for_delivery": "Kiszállítás alatt" })[key] ?? key,
}));

vi.mock("primevue/tag", () => ({
    default: {
        props: ["value", "severity"],
        template: '<span :data-severity="severity">{{ value }}</span>',
    },
}));

describe("DeliveryStatusBadge", () => {
    it("renders the delivery status label", () => {
        const wrapper = mount(DeliveryStatusBadge, {
            props: {
                status: "out_for_delivery",
            },
        });

        expect(wrapper.text()).toContain("Kiszállítás alatt");
        expect(wrapper.attributes("data-severity")).toBe("warn");
    });
});
