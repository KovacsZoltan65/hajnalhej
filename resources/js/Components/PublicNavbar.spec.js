import { mount } from '@vue/test-utils';
import PublicNavbar from './PublicNavbar.vue';

const page = {
    url: '/',
    props: {
        auth: { user: null },
        cart: { total_quantity: 2 },
        ui: { nav: {} },
    },
};

const translations = {
    'nav.home': 'Kezdőlap',
    'nav.weekly_menu': 'Heti menü',
    'nav.about': 'Rólunk',
    'nav.cart': 'Kosár',
    'nav.login': 'Belépés',
    'nav.register': 'Regisztráció',
    'nav.account': 'Fiókom',
    'nav.admin': 'Admin panel',
    'nav.logout': 'Kilépés',
    'nav.open_menu': 'Menü megnyitása',
    'nav.close_menu': 'Menü bezárása',
};

vi.mock('@inertiajs/vue3', () => ({
    Link: {
        name: 'Link',
        props: ['href'],
        template: '<a :href="href"><slot /></a>',
    },
    usePage: () => page,
}));

vi.mock('laravel-vue-i18n', () => ({
    trans: (key) => translations[key] ?? key,
}));

describe('PublicNavbar', () => {
    it('renders localized public navigation labels', () => {
        const wrapper = mount(PublicNavbar, {
            global: {
                mocks: {
                    $t: (key) => translations[key] ?? key,
                },
            },
        });

        expect(wrapper.text()).toContain('Kezdőlap');
        expect(wrapper.text()).toContain('Heti menü');
        expect(wrapper.text()).toContain('Rólunk');
        expect(wrapper.text()).toContain('Kosár');
        expect(wrapper.text()).toContain('Belépés');
        expect(wrapper.text()).toContain('Regisztráció');
    });

    it('uses shared nav props before translation fallbacks', () => {
        page.props.ui.nav = { cart: 'Kosár teszt' };

        const wrapper = mount(PublicNavbar, {
            global: {
                mocks: {
                    $t: (key) => translations[key] ?? key,
                },
            },
        });

        expect(wrapper.text()).toContain('Kosár teszt');

        page.props.ui.nav = {};
    });
});
