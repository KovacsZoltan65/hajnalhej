import { mount } from "@vue/test-utils";
import { reactive } from "vue";
import RegisterPage from "./Register.vue";

const mockPost = vi.fn();
const mockReset = vi.fn();

let formState = reactive({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
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

vi.mock("primevue/inputtext", () => ({
    default: {
        name: "InputText",
        props: ["modelValue", "invalid"],
        emits: ["update:modelValue"],
        template:
            '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
}));

vi.mock("primevue/password", () => ({
    default: {
        name: "Password",
        props: ["modelValue", "invalid"],
        emits: ["update:modelValue"],
        template:
            '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
}));

vi.mock("primevue/button", () => ({
    default: {
        name: "Button",
        props: ["label", "loading", "disabled"],
        template: '<button :disabled="disabled">{{ label }}</button>',
    },
}));

describe("Register page", () => {
    const mountRegisterPage = () =>
        mount(RegisterPage, {
            global: {
                mocks: {
                    $t: (key) =>
                        ({
                            "auth.account_label": "Hajnalhéj fiók",
                            "register.title": "Hozd létre a fiókodat",
                            "register.subtitle":
                                "Regisztrálj, hogy gyorsabban rendelhesd kedvenceidet.",
                            "register.cta": "Fiók létrehozása",
                            "register.login_link": "Már van fiókod? Lépj be.",
                            "fields.name": "teljes név",
                            "fields.email": "e-mail cím",
                            "fields.password": "jelszó",
                            "fields.password_confirmation": "jelszó megerősítése",
                        })[key] ?? key,
                },
            },
        });

    beforeEach(() => {
        mockPost.mockReset();
        mockReset.mockReset();

        formState = reactive({
            name: "",
            email: "",
            password: "",
            password_confirmation: "",
            errors: {},
            processing: false,
            post: mockPost,
            reset: mockReset,
        });
    });

    it("renders registration fields and CTA", () => {
        const wrapper = mountRegisterPage();

        expect(wrapper.text()).toContain("Hozd létre a fiókodat");
        expect(wrapper.text()).toContain("Fiók létrehozása");
        expect(wrapper.find("#name").exists()).toBe(true);
        expect(wrapper.find("#email").exists()).toBe(true);
        expect(wrapper.find("#password").exists()).toBe(true);
        expect(wrapper.find("#password_confirmation").exists()).toBe(true);
    });

    it("disables submit while processing", () => {
        formState.processing = true;

        const wrapper = mountRegisterPage();

        expect(wrapper.find("button").attributes("disabled")).toBeDefined();
    });
});

