import { mount } from '@vue/test-utils';
import RecipeIngredientList from './RecipeIngredientList.vue';

describe('RecipeIngredientList', () => {
    it('renders low stock indicator and quantity', () => {
        const wrapper = mount(RecipeIngredientList, {
            props: {
                items: [
                    {
                        id: 1,
                        ingredient_name: 'Liszt',
                        ingredient_unit: 'kg',
                        ingredient_is_low_stock: true,
                        quantity: 0.75,
                        notes: null,
                    },
                ],
            },
            global: {
                stubs: {
                    Button: { template: '<button />' },
                },
            },
        });

        expect(wrapper.text()).toContain('Alacsony készlet');
        expect(wrapper.text()).toContain('Liszt');
    });

    it('renders empty state when no items', () => {
        const wrapper = mount(RecipeIngredientList, {
            props: { items: [] },
            global: {
                stubs: {
                    Button: { template: '<button />' },
                },
            },
        });

        expect(wrapper.text()).toContain('még nincs recepttétel');
    });
});

