import { mount } from '@vue/test-utils';
import SeverityBadge from './SeverityBadge.vue';

vi.mock('primevue/tag', () => ({
    default: { props: ['value'], template: '<span>{{ value }}</span>' },
}));

describe('SeverityBadge', () => {
    it('renders severity label', () => {
        const wrapper = mount(SeverityBadge, { props: { severity: 'high' } });
        expect(wrapper.text()).toContain('HIGH');
    });
});

