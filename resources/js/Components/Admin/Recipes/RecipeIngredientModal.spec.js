import { mount } from '@vue/test-utils';
import RecipeIngredientModal from './RecipeIngredientModal.vue';

const stubs = {
    Dialog: { props: ['visible'], template: '<div v-if="visible"><slot /><slot name="footer" /></div>' },
    Select: {
        props: ['modelValue', 'options', 'optionLabel', 'optionValue'],
        emits: ['update:modelValue'],
        template: '<select @change="$emit(\'update:modelValue\', Number($event.target.value))"><slot /></select>',
    },
    InputNumber: { props: ['modelValue'], emits: ['update:modelValue'], template: '<input type="number" :value="modelValue" @input="$emit(\'update:modelValue\', Number($event.target.value))" />' },
    InputText: { props: ['modelValue'], emits: ['update:modelValue'], template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />' },
    Button: { template: '<button type="button"><slot /></button>' },
};

describe('RecipeIngredientModal', () => {
    it('renders ingredient fields', () => {
        const wrapper = mount(RecipeIngredientModal, {
            props: {
                visible: true,
                item: null,
                ingredients: [{ id: 1, name: 'Liszt', unit: 'kg', is_low_stock: false }],
                errors: {},
            },
            global: { stubs },
        });

        expect(wrapper.find('form').exists()).toBe(true);
        expect(wrapper.text()).toContain('Mertekegyseg');
    });

    it('emits submit payload', async () => {
        const wrapper = mount(RecipeIngredientModal, {
            props: {
                visible: true,
                item: null,
                ingredients: [{ id: 1, name: 'Liszt', unit: 'kg', is_low_stock: false }],
                errors: {},
            },
            global: { stubs },
        });

        await wrapper.find('form').trigger('submit.prevent');

        expect(wrapper.emitted('submit')).toBeTruthy();
    });
});
