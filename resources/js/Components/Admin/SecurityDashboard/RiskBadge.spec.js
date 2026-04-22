import { mount } from '@vue/test-utils';
import RiskBadge from './RiskBadge.vue';

vi.mock('primevue/tag', () => ({
    default: { props: ['value'], template: '<span>{{ value }}</span>' },
}));

describe('RiskBadge', () => {
    it('renders risk level label', () => {
        const wrapper = mount(RiskBadge, { props: { level: 'critical' } });
        expect(wrapper.text()).toContain('CRITICAL');
    });
});

