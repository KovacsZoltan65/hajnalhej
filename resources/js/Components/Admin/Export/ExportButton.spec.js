import { mount } from "@vue/test-utils";
import { describe, expect, it, vi } from "vitest";
import ExportButton from "./ExportButton.vue";

vi.mock("@inertiajs/vue3", () => ({
    router: {
        post: vi.fn(),
    },
}));

const stubs = {
    Button: {
        props: ["disabled", "label"],
        emits: ["click"],
        template: '<button :disabled="disabled" @click="$emit(\'click\')">{{ label }}</button>',
    },
    Dialog: {
        props: ["visible"],
        template: '<div v-if="visible"><slot /><slot name="footer" /></div>',
    },
    Select: {
        props: ["modelValue", "options"],
        emits: ["update:modelValue"],
        template: `
            <select :value="modelValue" @change="$emit('update:modelValue', $event.target.value)">
                <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
        `,
    },
};

describe("ExportButton", () => {
    it("renderel es megnyitja a formatum valasztot", async () => {
        const wrapper = mount(ExportButton, {
            props: { type: "products", filters: { search: "kenyer" } },
            global: { stubs },
        });

        expect(wrapper.text()).toContain("common.export");

        await wrapper.find("button").trigger("click");

        expect(wrapper.find("select").exists()).toBe(true);
    });

    it("disabled allapotot atadja", () => {
        const wrapper = mount(ExportButton, {
            props: { type: "orders", disabled: true },
            global: { stubs },
        });

        expect(wrapper.find("button").attributes("disabled")).toBeDefined();
    });

    it("xlsx formatum valaszthato", async () => {
        const wrapper = mount(ExportButton, {
            props: { type: "orders" },
            global: { stubs },
        });

        await wrapper.find("button").trigger("click");
        await wrapper.find("select").setValue("xlsx");

        expect(wrapper.find("select").element.value).toBe("xlsx");
    });
});
