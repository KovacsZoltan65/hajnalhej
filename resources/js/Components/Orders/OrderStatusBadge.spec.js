import { mount } from '@vue/test-utils';
import OrderStatusBadge from './OrderStatusBadge.vue';

describe('OrderStatusBadge', () => {
    it('renders known status label', () => {
        const wrapper = mount(OrderStatusBadge, {
            props: { status: 'ready_for_pickup' },
        });

        expect(wrapper.text()).toContain('Átvételre kész');
    });

    it('falls back to raw status for unknown status', () => {
        const wrapper = mount(OrderStatusBadge, {
            props: { status: 'custom_status' },
        });

        expect(wrapper.text()).toContain('custom_status');
    });
});
