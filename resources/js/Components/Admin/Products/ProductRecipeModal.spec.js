import { mount } from '@vue/test-utils';
import ProductRecipeModal from './ProductRecipeModal.vue';

const stubs = {
    Dialog: { props: ['visible'], template: '<div v-if="visible"><slot /></div>' },
    Select: { template: '<div><slot /></div>' },
    InputNumber: { template: '<input type="number" />' },
    InputText: { template: '<input />' },
    Button: { props: ['label'], template: '<button>{{ label }}</button>' },
    ProductRecipeTable: { props: ['items'], template: '<div>recipe-table {{ items.length }}</div>' },
};

describe('ProductRecipeModal', () => {
    it('renders recipe form and table placeholder', () => {
        const wrapper = mount(ProductRecipeModal, {
            props: {
                visible: true,
                product: {
                    id: 1,
                    name: 'Klasszikus kenyer',
                    product_ingredients: [],
                },
                ingredients: [{ id: 1, name: 'Liszt', unit: 'kg', is_low_stock: false }],
                errors: {},
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Mennyiseg');
        expect(wrapper.text()).toContain('recipe-table 0');
    });

    it('emits save-item on form submit', async () => {
        const wrapper = mount(ProductRecipeModal, {
            props: {
                visible: true,
                product: {
                    id: 1,
                    name: 'Klasszikus kenyer',
                    product_ingredients: [],
                },
                ingredients: [{ id: 1, name: 'Liszt', unit: 'kg', is_low_stock: false }],
                errors: {},
            },
            global: { stubs },
        });

        await wrapper.find('form').trigger('submit.prevent');
        expect(wrapper.emitted('save-item')).toBeTruthy();
    });
});
