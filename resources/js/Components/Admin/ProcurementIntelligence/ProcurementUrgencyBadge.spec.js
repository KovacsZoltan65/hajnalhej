import { mount } from '@vue/test-utils';
import ProcurementUrgencyBadge from './ProcurementUrgencyBadge.vue';

vi.mock('laravel-vue-i18n', () => ({
    trans: (key) =>
        ({
            'admin_procurement_intelligence.urgencies.critical': 'Kritikus',
        })[key] ?? key,
}));

describe('ProcurementUrgencyBadge', () => {
    it('renders Hungarian urgency labels', () => {
        const wrapper = mount(ProcurementUrgencyBadge, {
            props: {
                value: 'critical',
            },
        });

        expect(wrapper.text()).toContain('Kritikus');
    });
});
