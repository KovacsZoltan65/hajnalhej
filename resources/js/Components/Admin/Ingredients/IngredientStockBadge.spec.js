import { mount } from '@vue/test-utils';
import IngredientStockBadge from './IngredientStockBadge.vue';

describe('IngredientStockBadge', () => {
    it('renders low stock state', () => {
        const wrapper = mount(IngredientStockBadge, {
            props: {
                currentStock: 2,
                minimumStock: 5,
                unit: 'kg',
            },
        });

        expect(wrapper.text()).toContain('Alacsony készlet');
        expect(wrapper.text()).toContain('kg');
    });

    it('renders healthy stock state', () => {
        const wrapper = mount(IngredientStockBadge, {
            props: {
                currentStock: 8,
                minimumStock: 2,
                unit: 'l',
            },
        });

        expect(wrapper.text()).toContain('Rendben');
    });
});

