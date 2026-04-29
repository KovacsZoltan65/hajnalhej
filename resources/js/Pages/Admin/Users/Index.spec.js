import { mount } from '@vue/test-utils';
import UsersIndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn(), delete: vi.fn() },
    useForm: (data) => ({
        ...data,
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

vi.mock('primevue/useconfirm', () => ({
    useConfirm: () => ({ require: vi.fn() }),
}));

vi.mock('primevue/button', () => ({ default: { props: ['label'], template: '<button>{{ label }}<slot /></button>' } }));
vi.mock('primevue/checkbox', () => ({ default: { template: '<div><slot /></div>' } }));
vi.mock('primevue/column', () => ({ default: { template: '<div><slot /><slot name="body" :data="{}" /></div>' } }));
vi.mock('primevue/confirmdialog', () => ({ default: { template: '<div />' } }));
vi.mock('primevue/datatable', () => ({ default: { template: '<div><slot name="empty" /><slot /></div>' } }));
vi.mock('primevue/dialog', () => ({ default: { template: '<div><slot /><slot name="footer" /></div>' } }));
vi.mock('primevue/inputnumber', () => ({ default: { template: '<div><slot /></div>' } }));
vi.mock('primevue/inputtext', () => ({ default: { template: '<input />' } }));
vi.mock('primevue/multiselect', () => ({ default: { template: '<div><slot /></div>' } }));
vi.mock('primevue/select', () => ({ default: { template: '<div><slot /></div>' } }));
vi.mock('primevue/tag', () => ({ default: { props: ['value'], template: '<span>{{ value }}</span>' } }));
vi.mock('primevue/textarea', () => ({ default: { template: '<textarea />' } }));
vi.mock('primevue/toggleswitch', () => ({ default: { template: '<div><slot /></div>' } }));

describe('Admin Users Index', () => {
    const props = {
        users: {
            data: [{
                id: 1,
                name: 'Teszt Vásárló',
                email: 'teszt@example.test',
                phone: '+36301234567',
                status: 'active',
                roles: ['customer'],
            }],
            current_page: 1,
            per_page: 15,
            total: 1,
        },
        roles: [{ name: 'customer' }],
        filters: { search: '', status: '', sort_field: 'created_at', sort_direction: 'desc', per_page: 15 },
        status_options: ['active', 'inactive'],
        can: {
            create: true,
            update: true,
            delete: true,
            manage_roles: true,
        },
    };

    it('renders create action and user management copy', () => {
        const wrapper = mount(UsersIndexPage, {
            props,
            global: {
                stubs: {
                    AdminTableToolbar: { template: '<div><slot name="filters" /><slot name="actions" /></div>' },
                    SectionTitle: { props: ['title'], template: '<h1>{{ title }}</h1>' },
                },
            },
        });

        expect(wrapper.text()).toContain('Felhasználók');
        expect(wrapper.text()).toContain('Új felhasználó');
    });

    it('hides create action without permission', () => {
        const wrapper = mount(UsersIndexPage, {
            props: {
                ...props,
                can: { ...props.can, create: false },
            },
            global: {
                stubs: {
                    AdminTableToolbar: { template: '<div><slot name="filters" /><slot name="actions" /></div>' },
                    SectionTitle: { props: ['title'], template: '<h1>{{ title }}</h1>' },
                },
            },
        });

        expect(wrapper.text()).not.toContain('Új felhasználó');
    });
});
