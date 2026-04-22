import { mount } from '@vue/test-utils';
import PermissionsIndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn(), post: vi.fn() },
    usePage: () => ({ props: { flash: {} } }),
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/button', () => ({
    default: { props: ['label'], template: '<button>{{ label }}</button>' },
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
    default: { template: '<div class="select-stub"></div>' },
}));
vi.mock('primevue/checkbox', () => ({
    default: { template: '<input type="checkbox" />' },
}));

const stubs = {
    AdminTableToolbar: { template: '<div><slot name="filters" /><slot name="actions" /></div>' },
    PermissionBadge: { props: ['name'], template: '<span>{{ name }}</span>' },
    PermissionDangerBadge: { props: ['dangerous'], template: '<span>{{ dangerous ? "danger" : "safe" }}</span>' },
    PermissionRegistryStateBadge: { props: ['state'], template: '<span>{{ state }}</span>' },
    PermissionSyncSummaryModal: { template: '<div />' },
    SectionTitle: { template: '<div />' },
};

describe('Admin Permissions Index', () => {
    it('renders filters and sync action', () => {
        const wrapper = mount(PermissionsIndexPage, {
            props: {
                permissions: {
                    data: [
                        { name: 'orders.view', module: 'Orders', dangerous: false, registry_state: 'synced', roles_count: 1, users_count: 1, guard_name: 'web' },
                    ],
                    current_page: 1,
                    per_page: 20,
                    total: 1,
                },
                modules: ['Orders'],
                filters: {
                    search: '',
                    module: '',
                    dangerous_only: false,
                    usage_state: '',
                    registry_state: '',
                    sort_field: 'name',
                    sort_direction: 'asc',
                    per_page: 20,
                },
                can: { view_usage: true, sync: true },
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Registry sync');
        expect(wrapper.text()).toContain('Szures');
    });
});
