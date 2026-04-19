import { mount } from '@vue/test-utils';
import RecipeEditorModal from './RecipeEditorModal.vue';

const stubs = {
    Dialog: { props: ['visible'], template: '<div v-if="visible"><slot /></div>' },
    Select: { template: '<div><slot /></div>' },
    InputNumber: { template: '<input type="number" />' },
    InputText: { template: '<input />' },
    Button: { props: ['label'], template: '<button>{{ label }}</button>' },
    RecipeIngredientList: { props: ['items'], template: '<div>ingredients {{ items.length }}</div>' },
};

describe('RecipeEditorModal', () => {
    it('renders editor and recipe items section', () => {
        const wrapper = mount(RecipeEditorModal, {
            props: {
                visible: true,
                recipe: {
                    id: 1,
                    name: 'Klasszikus kenyer',
                    category_name: 'Kenyerek',
                    recipe_items_count: 1,
                    low_stock_ingredients_count: 0,
                    product_ingredients: [{ id: 1, ingredient_name: 'Liszt' }],
                },
                ingredients: [{ id: 1, name: 'Liszt', unit: 'kg', is_low_stock: false }],
                errors: {},
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Kategoria: Kenyerek');
        expect(wrapper.text()).toContain('ingredients 1');
    });
});
