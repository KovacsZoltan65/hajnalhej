import { mount } from '@vue/test-utils';
import WeeklyMenuSection from './WeeklyMenuSection.vue';

describe('WeeklyMenuSection', () => {
    it('renders empty state without menu', () => {
        const wrapper = mount(WeeklyMenuSection, {
            props: {
                menu: null,
                groups: [],
                fallbackUsed: false,
            },
        });

        expect(wrapper.text()).toContain('Heti menu feltoltes alatt');
    });

    it('renders menu groups and item data', () => {
        const wrapper = mount(WeeklyMenuSection, {
            props: {
                menu: { title: 'Aktualis', week_start: '2026-04-20', week_end: '2026-04-26', public_note: null },
                fallbackUsed: false,
                groups: [
                    {
                        category_name: 'Kenyerek',
                        items: [{ id: 1, name: 'Kenyer', short_description: 'Leiras', price: 2500, badge_text: 'Uj', stock_note: null }],
                    },
                ],
            },
        });

        expect(wrapper.text()).toContain('Kenyerek');
        expect(wrapper.text()).toContain('Kenyer');
    });
});
