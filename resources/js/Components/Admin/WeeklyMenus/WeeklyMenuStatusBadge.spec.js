import { mount } from '@vue/test-utils';
import WeeklyMenuStatusBadge from './WeeklyMenuStatusBadge.vue';

describe('WeeklyMenuStatusBadge', () => {
    it('renders published label', () => {
        const wrapper = mount(WeeklyMenuStatusBadge, {
            props: { status: 'published' },
        });

        expect(wrapper.text()).toContain('Közzétéve');
    });
});
