import { mount } from "@vue/test-utils";
import { reactive } from "vue";
import LoginPage from "./Login.vue";

const mockPost = vi.fn();
const mockReset = vi.fn();

let formState = reactive({
    email: "",
    password: "",
    remember: false,
    errors: {},
    processing: false,
    post: mockPost,
    reset: mockReset,
});

vi.mock("@inertiajs/vue3", () => ({
    Head: {
        name: "Head",
        props: ["title"],
        template: "<span><slot /></span>",
    },
    Link: {
        name: "Link",
        props: ["href"],
        template: '<a :href="href"><slot /></a>',
    },
    useForm: () => formState,
}));

vi.mock("../../Layouts/PublicLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: {
        name: "Button",
        props: ["label", "loading", "disabled", "type"],
        template: '<button :type="type" :disabled="disabled">{{ label }}</button>',
    },
}));

vi.mock("primevue/checkbox", () => ({
    default: {
        name: "Checkbox",
        props: ["modelValue"],
        emits: ["update:modelValue"],
        template:
            '<input type="checkbox" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
    },
}));

vi.mock("primevue/inputtext", () => ({
    default: {
        name: "InputText",
        props: ["modelValue", "invalid"],
        emits: ["update:modelValue"],
        template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
}));

vi.mock("primevue/password", () => ({
    default: {
        name: "Password",
        props: ["modelValue", "invalid"],
        emits: ["update:modelValue"],
        template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
}));

const translate = (key) =>
    ({
        "auth.account_label": "Hajnalhéj fiók",
        "fields.email": "e-mail cím",
        "fields.password": "jelszó",
        "login.cta": "Belépés",
        "login.register_link": "Még nincs fiókod? Regisztrálj.",
        "login.remember": "Emlékezz rám",
        "login.subtitle": "Lépj be, hogy folytathasd a rendelést.",
        "nav.login": "Belépés",
    })[key] ?? key;

const mountLoginPage = () =>
    mount(LoginPage, {
        global: {
            mocks: {
                $t: translate,
                route: (name) => `/${name}`,
            },
        },
    });

describe("Login page", () => {
    beforeEach(() => {
        mockPost.mockReset();
        mockReset.mockReset();

        formState = reactive({
            email: "",
            password: "",
            remember: false,
            errors: {},
            processing: false,
            post: mockPost,
            reset: mockReset,
        });
    });

    it("renders localized login form fields and links", () => {
        const wrapper = mountLoginPage();

        expect(wrapper.text()).toContain("Hajnalhéj fiók");
        expect(wrapper.text()).toContain("Belépés");
        expect(wrapper.text()).toContain("Lépj be, hogy folytathasd a rendelést.");
        expect(wrapper.text()).toContain("e-mail cím");
        expect(wrapper.text()).toContain("jelszó");
        expect(wrapper.text()).toContain("Emlékezz rám");
        expect(wrapper.text()).toContain("Még nincs fiókod? Regisztrálj.");
        expect(wrapper.find("#email").exists()).toBe(true);
        expect(wrapper.find("#password").exists()).toBe(true);
    });

    it("submits the login form and resets the password on finish", async () => {
        const wrapper = mountLoginPage();

        await wrapper.find("form").trigger("submit.prevent");

        expect(mockPost).toHaveBeenCalledWith("/login", expect.objectContaining({ onFinish: expect.any(Function) }));

        mockPost.mock.calls[0][1].onFinish();

        expect(mockReset).toHaveBeenCalledWith("password");
    });

    it("disables submit while processing", () => {
        formState.processing = true;

        const wrapper = mountLoginPage();

        expect(wrapper.find('button[type="submit"]').attributes("disabled")).toBeDefined();
    });
});
