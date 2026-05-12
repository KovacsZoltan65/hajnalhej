import { mount } from "@vue/test-utils";
import { reactive } from "vue";
import VerifyEmailPage from "./VerifyEmail.vue";

const mockPost = vi.fn();

let formState = reactive({
    errors: {},
    processing: false,
    post: mockPost,
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
        emits: ["click"],
        template: '<button :type="type" :disabled="disabled" @click="$emit(\'click\')">{{ label }}</button>',
    },
}));

const translate = (key) =>
    ({
        "account.email_status_pending": "Az email címed még nincs megerősítve.",
        "account.email_status_verified": "Az email címed meg van erősítve.",
        "auth.account_label": "Hajnalhéj fiók",
        "nav.account": "Fiókom",
        "verification.send_again": "Megerősítő e-mail újraküldése",
        "verification.subtitle": "Ellenőrizd az email címedet a fiók aktiválásához.",
        "verification.title": "Email megerősítés",
    })[key] ?? key;

const mountVerifyEmailPage = (props = {}) =>
    mount(VerifyEmailPage, {
        props: {
            isVerified: false,
            ...props,
        },
        global: {
            mocks: {
                $t: translate,
                route: (name) => `/${name}`,
            },
        },
    });

describe("Verify email page", () => {
    beforeEach(() => {
        mockPost.mockReset();

        formState = reactive({
            errors: {},
            processing: false,
            post: mockPost,
        });
    });

    it("renders pending verification state and resend action", () => {
        const wrapper = mountVerifyEmailPage();

        expect(wrapper.text()).toContain("Hajnalhéj fiók");
        expect(wrapper.text()).toContain("Email megerősítés");
        expect(wrapper.text()).toContain("Ellenőrizd az email címedet a fiók aktiválásához.");
        expect(wrapper.text()).toContain("Az email címed még nincs megerősítve.");
        expect(wrapper.text()).toContain("Megerősítő e-mail újraküldése");
        expect(wrapper.text()).toContain("Fiókom");
    });

    it("posts resend verification request", async () => {
        const wrapper = mountVerifyEmailPage();

        await wrapper.find("button").trigger("click");

        expect(mockPost).toHaveBeenCalledWith("/email/verification-notification");
    });

    it("renders verified state without resend action", () => {
        const wrapper = mountVerifyEmailPage({ isVerified: true });

        expect(wrapper.text()).toContain("Az email címed meg van erősítve.");
        expect(wrapper.text()).not.toContain("Megerősítő e-mail újraküldése");
    });

    it("disables resend while processing", () => {
        formState.processing = true;

        const wrapper = mountVerifyEmailPage();

        expect(wrapper.find("button").attributes("disabled")).toBeDefined();
    });
});
