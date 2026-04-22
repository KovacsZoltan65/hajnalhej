import { mount } from '@vue/test-utils';
import RolesIndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn(), delete: vi.fn() },
    useForm: () => ({
        name: '',
        processing: false,
        errors: {},
        reset: vi.fn(),
        clearErrors: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
    }),
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/button', () => ({
    default: { props: ['label', 'disabled'], template: '<button :disabled="disabled">{{ label }}</button>' },
}));
vi.mock('primevue/datatable', () => ({
    default: { template: '<div><slot name="empty" /><slot /></div>' },
}));
vi.mock('primevue/column', () => ({
    default: { template: '<div><slot /></div>' },
}));
vi.mock('primevue/inputtext', () => ({
    default: { template: '<input />' },
}));
vi.mock('primevue/select', () => ({
    default: { props: ['modelValue', 'options'], template: '<div class="select-stub"></div>' },
}));
vi.mock('primevue/confirmdialog', () => ({
    default: { template: '<div />' },
}));
vi.mock('primevue/useconfirm', () => ({
    useConfirm: () => ({ require: vi.fn() }),
}));

const localStubs = {
    RoleFormModal: { template: '<div />' },
    AdminTableToolbar: { template: '<div><slot name="filters" /><slot name="actions" /></div>' },
    RoleBadge: { props: ['role'], template: '<span>{{ role }}</span>' },
    SectionTitle: { template: '<div />' },
};

describe('Admin Roles Index', () => {
    const baseProps = {
        roles: {
            data: [
                {
                    id: 1,
                    name: 'admin',
                    guard_name: 'web',
                    permissions_count: 10,
                    users_count: 2,
                    is_system_role: true,
                },
            ],
            current_page: 1,
            per_page: 15,
            total: 1,
        },
        filters: { search: '', per_page: 15 },
        can: {
            create: true,
            update: true,
            delete: true,
            assign_permissions: true,
        },
    };

    it('renders roles index actions for authorized admin', () => {
        const wrapper = mount(RolesIndexPage, {
            props: baseProps,
            global: {
                stubs: localStubs,
            },
        });

        expect(wrapper.text()).toContain('Új szerepkör');
    });

    it('hides create action when permission is missing', () => {
        const wrapper = mount(RolesIndexPage, {
            props: {
                ...baseProps,
                can: {
                    ...baseProps.can,
                    create: false,
                },
            },
            global: {
                stubs: localStubs,
            },
        });

        expect(wrapper.text()).not.toContain('Új szerepkör');
    });
});
