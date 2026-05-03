import { mount } from '@vue/test-utils';
import WeeklyMenuStatusBadge from './WeeklyMenuStatusBadge.vue';

describe('WeeklyMenuStatusBadge', () => {
    it('renders published label', () => {
        const wrapper = mount(WeeklyMenuStatusBadge, {
            props: { status: 'published' },
            global: {
                mocks: {
                    $t: (key) =>
                        ({
                            'admin_weekly_menus.status.published': 'Közzétéve',
                        })[key] ?? key,
                },
            },
        });

        expect(wrapper.text()).toContain('Közzétéve');
    });
});
