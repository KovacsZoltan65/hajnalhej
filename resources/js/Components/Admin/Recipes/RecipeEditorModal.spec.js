import { mount } from '@vue/test-utils';
import RecipeEditorModal from './RecipeEditorModal.vue';

const stubs = {
    Dialog: { props: ['visible'], template: '<div v-if="visible"><slot /></div>' },
    Button: { props: ['label'], template: '<button>{{ label }}</button>' },
    RecipeIngredientList: { props: ['items'], template: '<div>ingredients {{ items.length }}</div>' },
    RecipeStepList: { props: ['steps'], template: '<div>steps {{ steps.length }}</div>' },
};

describe('RecipeEditorModal', () => {
    it('renders ingredient and step blocks with summary', () => {
        const wrapper = mount(RecipeEditorModal, {
            props: {
                visible: true,
                recipe: {
                    id: 1,
                    name: 'Klasszikus kenyer',
                    category_name: 'Kenyerek',
                    recipe_items_count: 1,
                    recipe_steps_count: 1,
                    low_stock_ingredients_count: 0,
                    product_ingredients: [{ id: 1, ingredient_name: 'Liszt' }],
                    recipe_steps: [{ id: 2, title: 'Pihentetes' }],
                    recipe_summary: {
                        ingredients_count: 1,
                        steps_count: 1,
                        total_active_minutes: 20,
                        total_wait_minutes: 40,
                        total_recipe_minutes: 60,
                    },
                },
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Kategoria: Kenyerek');
        expect(wrapper.text()).toContain('ingredients 1');
        expect(wrapper.text()).toContain('steps 1');
        expect(wrapper.text()).toContain('Hozzavalok');
        expect(wrapper.text()).toContain('Recept lepesek es idozites');
        expect(wrapper.text()).toContain('Teljes ido:');
        expect(wrapper.text()).not.toContain('Mennyiseg');
    });
});
