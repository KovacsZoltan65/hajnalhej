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
        const wrapper = mount(HeroSection);

        expect(wrapper.text()).toContain('Frissen sult kenyerek');
        expect(wrapper.text()).toContain('Heti menu megnyitasa');
    });
});
