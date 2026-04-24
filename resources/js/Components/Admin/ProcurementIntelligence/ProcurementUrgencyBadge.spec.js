import { mount } from '@vue/test-utils';
import ProcurementUrgencyBadge from './ProcurementUrgencyBadge.vue';

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
