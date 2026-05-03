import { mount } from '@vue/test-utils';
import WeeklyMenuSection from './WeeklyMenuSection.vue';

const translations = vi.hoisted(() => ({
    'common.locale': 'hu-HU',
    'common.currency': 'HUF',
    'weekly_menu.current': 'Aktuális heti menü',
    'weekly_menu.empty_title': 'Heti menü feltöltés alatt',
    'weekly_menu.empty_description': 'Jelenleg nincs publikált heti menü.',
    'home.open_cart': 'Kosár megnyitása',
    'home.go_to_checkout': 'Ugrás a pénztárhoz',
    'common.add_to_card': 'Kosárba',
}));

vi.mock('@inertiajs/vue3', () => ({
    Link: {
        name: 'Link',
        props: ['href'],
        template: '<a :href="href"><slot /></a>',
    },
    useForm: () => ({
        product_id: null,
        quantity: 1,
        processing: false,
        post: vi.fn(),
    }),
}));

vi.mock('laravel-vue-i18n', () => ({
    trans: (key) => translations[key] ?? key,
}));

describe('WeeklyMenuSection', () => {
    const mountWeeklyMenuSection = (options = {}) =>
        mount(WeeklyMenuSection, {
            ...options,
            global: {
                ...(options.global ?? {}),
                mocks: {
                    ...(options.global?.mocks ?? {}),
                    $t: (key) => translations[key] ?? key,
                },
            },
        });

    it('renders empty state without menu', () => {
        const wrapper = mountWeeklyMenuSection({
            props: {
                menu: null,
                groups: [],
                fallbackUsed: false,
            },
        });

        expect(wrapper.text()).toContain('Heti menü feltöltés alatt');
    });

    it('renders menu groups and item data', () => {
        const wrapper = mountWeeklyMenuSection({
            props: {
                menu: { title: 'Aktualis', week_start: '2026-04-20', week_end: '2026-04-26', public_note: null },
                fallbackUsed: false,
                groups: [
                    {
                        category_name: 'Kenyerek',
                        items: [{ id: 1, name: 'Kenyer', short_description: 'Leírás', price: 2500, badge_text: 'Uj', stock_note: null }],
                    },
                ],
            },
        });

        expect(wrapper.text()).toContain('Kenyerek');
        expect(wrapper.text()).toContain('Kenyer');
        expect(wrapper.text()).toContain('2500 Ft');
    });
});

