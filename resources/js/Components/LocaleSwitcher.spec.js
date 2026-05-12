import { mount } from "@vue/test-utils";
import { beforeEach, describe, expect, it, vi } from "vitest";
import LocaleSwitcher from "./LocaleSwitcher.vue";

const { axiosPost, loadLanguageAsync, page } = vi.hoisted(() => ({
    axiosPost: vi.fn(),
    loadLanguageAsync: vi.fn(),
    page: {
        props: {
            locale: "hu",
            auth: { user: { locale: "hu" } },
            available_locales: [
                { code: "hu", label: "Magyar" },
                { code: "en", label: "English" },
            ],
        },
    },
}));

vi.mock("@inertiajs/vue3", () => ({
    usePage: () => page,
}));

vi.mock("laravel-vue-i18n", () => ({
    loadLanguageAsync,
}));

vi.stubGlobal("route", (name) => `/${name}`);
vi.stubGlobal("axios", { post: axiosPost });

const SelectButtonStub = {
    props: ["modelValue", "options", "optionLabel", "optionValue", "disabled"],
    emits: ["update:modelValue"],
    template: `
        <div>
            <button
                v-for="option in options"
                :key="option[optionValue]"
                type="button"
                :class="{ active: option[optionValue] === modelValue }"
                :disabled="disabled"
                @click="$emit('update:modelValue', option[optionValue])"
            >
                {{ option[optionLabel] }}
            </button>
        </div>
    `,
};

const mountSwitcher = () =>
    mount(LocaleSwitcher, {
        global: {
            stubs: {
                SelectButton: SelectButtonStub,
            },
        },
    });

describe("LocaleSwitcher", () => {
    beforeEach(() => {
        axiosPost.mockResolvedValue({ data: { locale: "en" } });
        loadLanguageAsync.mockResolvedValue("en");
        page.props.locale = "hu";
        page.props.auth.user.locale = "hu";
        document.documentElement.setAttribute("lang", "hu");
        vi.clearAllMocks();
    });

    it("renders available locales", () => {
        const wrapper = mountSwitcher();

        expect(wrapper.text()).toContain("Magyar");
        expect(wrapper.text()).toContain("English");
    });

    it("marks the active locale", () => {
        const wrapper = mountSwitcher();

        expect(wrapper.find("button.active").text()).toBe("Magyar");
    });

    it("persists and switches the selected locale without an Inertia visit", async () => {
        const wrapper = mountSwitcher();

        await wrapper.findAll("button")[1].trigger("click");
        await Promise.resolve();
        await Promise.resolve();

        expect(axiosPost).toHaveBeenCalledWith(
            "/locale.switch",
            { locale: "en" },
            expect.objectContaining({
                headers: { Accept: "application/json" },
            }),
        );
        expect(loadLanguageAsync).toHaveBeenCalledWith("en");
        expect(page.props.locale).toBe("en");
        expect(page.props.auth.user.locale).toBe("en");
        expect(document.documentElement.getAttribute("lang")).toBe("en");
    });
});
