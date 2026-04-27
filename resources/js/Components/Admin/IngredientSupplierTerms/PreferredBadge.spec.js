import { mount } from '@vue/test-utils';
import PreferredBadge from './PreferredBadge.vue';

describe('IngredientSupplierTerms PreferredBadge', () => {
    it('renders preferred state', () => {
        const wrapper = mount(PreferredBadge, {
            props: {
                preferred: true,
                active: true,
            },
        });

        expect(wrapper.text()).toContain('Preferált');
    });

    it('renders normal state for inactive preferred rows', () => {
        const wrapper = mount(PreferredBadge, {
            props: {
                preferred: true,
                active: false,
            },
        });

        expect(wrapper.text()).toContain('Normál');
    });
});
