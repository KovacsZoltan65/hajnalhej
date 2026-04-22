import { mount } from '@vue/test-utils';
import SecurityDashboardIndex from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn() },
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/button', () => ({
    default: { props: ['label'], template: '<button>{{ label }}</button>' },
}));
vi.mock('primevue/select', () => ({
    default: { template: '<div class="select-stub"></div>' },
}));
vi.mock('primevue/checkbox', () => ({
    default: { template: '<input type="checkbox" />' },
}));

const stubs = {
    SectionTitle: { template: '<div />' },
    SecuritySummaryCard: { props: ['title', 'value'], template: '<div>{{ title }} {{ value }}</div>' },
    PermissionRiskPanel: { template: '<div>Permission Risk Panel</div>' },
    OrphanPermissionsPanel: { template: '<div>Orphan Panel</div>' },
    PrivilegedUsersPanel: { template: '<div>Privileged Users Panel</div>' },
    RecentCriticalAuditEventsPanel: { template: '<div>Recent Critical Audit Events Panel</div>' },
};

describe('Admin Security Dashboard Index', () => {
    it('renders summary cards and panels', () => {
        const wrapper = mount(SecurityDashboardIndex, {
            props: {
                summary_cards: [{ title: 'Dangerous permissions', value: '5', tone: 'high' }],
                permission_risk: { total_permissions: 10, risk_distribution: { critical: 1 } },
                orphan_permissions: [],
                privileged_users: [],
                recent_critical_events: [],
                filters: { window: '7d', risk_level: 'all', log_name: 'all', dangerous_only: false },
                filter_options: {
                    windows: [{ label: '7d', value: '7d' }],
                    risk_levels: [{ label: 'All', value: 'all' }],
                    log_names: [{ label: 'All domains', value: 'all' }],
                },
                links: { permissions: '/admin/permissions', roles: '/admin/roles', user_roles: '/admin/user-roles' },
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Dangerous permissions');
        expect(wrapper.text()).toContain('Permission Risk Panel');
        expect(wrapper.text()).toContain('Orphan Panel');
        expect(wrapper.text()).toContain('Privileged Users Panel');
        expect(wrapper.text()).toContain('Recent Critical Audit Events Panel');
    });
});

