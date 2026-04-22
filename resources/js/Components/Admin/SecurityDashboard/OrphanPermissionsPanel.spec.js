import { mount } from '@vue/test-utils';
import OrphanPermissionsPanel from './OrphanPermissionsPanel.vue';

vi.mock('@inertiajs/vue3', () => ({
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
}));

const stubs = {
    RiskBadge: { props: ['level'], template: '<span>{{ level }}</span>' },
};

describe('OrphanPermissionsPanel', () => {
    it('renders orphan permission rows', () => {
        const wrapper = mount(OrphanPermissionsPanel, {
            props: {
                rows: [
                    {
                        name: 'legacy.custom.permission',
                        module: 'Orphan / Custom',
                        issue: 'DB-ben van, registry-ben nincs',
                        risk_level: 'high',
                        roles_count: 0,
                        users_count: 0,
                        suggested_action: 'Vizsgald felul',
                    },
                ],
                links: { permissions: '/admin/permissions' },
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('legacy.custom.permission');
        expect(wrapper.text()).toContain('DB-ben van, registry-ben nincs');
    });
});

