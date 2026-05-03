import { mount } from '@vue/test-utils';
import HeroSection from './HeroSection.vue';

vi.mock('@inertiajs/vue3', () => ({
    Link: {
        name: 'Link',
        props: ['href'],
        template: '<a :href="href"><slot /></a>',
    },
}));

describe('HeroSection', () => {
    it('renders key headline content', () => {
        const translations = {
            'hero_section.artisan_bakery_budapest': 'Kézműves pékség Budapest',
            'hero_section.freshly_baked_breads':
                'Frissen sült kenyerek, lassú kelesztéssel, hajnalban.',
            'hero_section.bakes_in_small_batches':
                'A Hajnalhéj hétről hétre kis tételben süt, hogy minden reggel ropogós, meleg és valódi legyen.',
            'hero_section.open_weekly_menu': 'Heti menu megnyitása',
            'hero_section.philosophy': 'Kovász. Idő. Türelem.',
            'hero_section.philosophy_description':
                'Minden termékünk több órányi fermentációval készül, hogy az íz, az állag és az illat is emlékezetes maradjon.',
            'hero_section.stat_artisan_value': '100%',
            'hero_section.stat_artisan_label': 'kézműves gyártás',
            'hero_section.stat_menu_value': 'Heti',
            'hero_section.stat_menu_label': 'limitált menü',
            'home.create_account': 'Fiók létrehozása',
            'common.our_history': 'Történetünk',
        };

        const wrapper = mount(HeroSection, {
            global: {
                mocks: {
                    $t: (key) => translations[key] ?? key,
                },
            },
        });

        expect(wrapper.text()).toContain('Frissen sült kenyerek');
        expect(wrapper.text()).toContain('Heti menu megnyitása');
        expect(wrapper.text()).toContain('Kovász. Idő. Türelem.');
        expect(wrapper.text()).toContain('kézműves gyártás');
    });
});
