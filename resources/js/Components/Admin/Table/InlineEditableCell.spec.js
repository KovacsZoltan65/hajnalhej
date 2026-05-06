import { mount } from "@vue/test-utils";
import { describe, expect, it, vi } from "vitest";
import InlineEditableCell from "./InlineEditableCell.vue";

const { patch } = vi.hoisted(() => ({
    patch: vi.fn(),
}));

vi.mock("@inertiajs/vue3", () => ({
    router: { patch },
}));

globalThis.route = (name, params) => `/${name}/${params}`;

describe("InlineEditableCell", () => {
    const factory = () =>
        mount(InlineEditableCell, {
            props: {
                modelValue: "10",
                routeName: "admin.products.inline.update",
                routeParams: 1,
                field: "price",
            },
            global: {
                stubs: {
                    InputText: {
                        template:
                            '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" @keyup.enter="$emit(\'keyup.enter\')" @keyup.escape="$emit(\'keyup.escape\')" @blur="$emit(\'blur\')" />',
                        props: ["modelValue"],
                    },
                },
            },
        });

    it("saves on enter", async () => {
        patch.mockClear();
        const wrapper = factory();

        await wrapper.find("button").trigger("click");
        await wrapper.find("input").setValue("12");
        await wrapper.find("input").trigger("keyup.enter");

        expect(patch).toHaveBeenCalledWith(
            "/admin.products.inline.update/1",
            { field: "price", value: "12" },
            expect.objectContaining({ preserveScroll: true })
        );
    });

    it("reverts on escape", async () => {
        const wrapper = factory();

        await wrapper.find("button").trigger("click");
        await wrapper.find("input").setValue("12");
        await wrapper.find("input").trigger("keyup.escape");

        expect(wrapper.text()).toContain("10");
    });

    it("shows loading and cell error state", async () => {
        patch.mockImplementationOnce((url, payload, options) => {
            options.onError({ value: "Invalid value" });
        });
        const wrapper = factory();

        await wrapper.find("button").trigger("click");
        await wrapper.find("input").setValue("nope");
        await wrapper.find("input").trigger("keyup.enter");

        expect(wrapper.text()).toContain("admin.common.inline_edit.failed");
        expect(wrapper.text()).toContain("Invalid value");
    });
});
