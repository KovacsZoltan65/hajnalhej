import { mount } from "@vue/test-utils";

const mockPage = {
    props: {
        auth: {
            user: { name: "Admin" },
            can: {
                view_admin_users: true,
            },
        },
    },
};

vi.mock("@inertiajs/vue3", () => ({
    Link: { name: "Link", props: ["href"], template: '<a :href="href"><slot /></a>' },
    useForm: () => ({ post: vi.fn() }),
    usePage: () => mockPage,
}));

vi.mock("../Components/AppHeader.vue", () => ({
    default: { template: '<header><slot name="actions" /></header>' },
}));

vi.mock("../Components/AppLogo.vue", () => ({
    default: { template: "<span>Hajnalhéj</span>" },
}));

vi.mock("../Components/FlashToast.vue", () => ({
    default: { template: "<div />" },
}));

vi.mock("../Components/AdminSidebar.vue", () => ({
    default: {
        props: ["groups"],
        template: `
            <nav>
                <template v-for="group in groups" :key="group.label">
                    <a v-for="item in group.items.filter(Boolean)" :key="item.label" :href="item.route">
                        {{ item.label }}
                    </a>
                </template>
            </nav>
        `,
    },
}));

vi.mock("primevue", () => ({
    Button: { template: "<button><slot /></button>" },
}));

import AdminLayout from "./AdminLayout.vue";

describe("AdminLayout", () => {
    it("links the User Handling menu item to the admin users route", () => {
        const wrapper = mount(AdminLayout, {
            slots: {
                default: "<div>Admin content</div>",
            },
        });

        const usersLink = wrapper.find('a[href="/admin/users"]');

        expect(usersLink.exists()).toBe(true);
        expect(usersLink.text()).toContain("nav.users");
        expect(wrapper.text()).not.toContain("admin.users.index");
    });
});
