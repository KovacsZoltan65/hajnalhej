import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import IngredientForm from './IngredientForm.vue';

const stubs = {
    InputText: {
        props: ['id', 'modelValue', 'disabled'],
        emits: ['update:modelValue'],
        template: '<input :id="id" :value="modelValue" :disabled="disabled" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
    Textarea: {
        props: ['modelValue'],
        emits: ['update:modelValue'],
        template: '<textarea :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
    InputNumber: {
        props: ['modelValue'],
        emits: ['update:modelValue'],
        template: '<input type="number" :value="modelValue" @input="$emit(\'update:modelValue\', Number($event.target.value))" />',
    },
    ToggleSwitch: {
        props: ['modelValue'],
        emits: ['update:modelValue'],
        template: '<input type="checkbox" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
    },
    Select: {
        props: ['id', 'modelValue', 'options', 'optionLabel', 'optionValue'],
        emits: ['update:modelValue'],
        template: '<select :id="id" @change="$emit(\'update:modelValue\', $event.target.value)"><slot /></select>',
    },
};

describe('IngredientForm', () => {
    const makeForm = () => ({
        name: '',
        slug: '',
        sku: '',
        unit: 'kg',
        estimated_unit_cost: 0,
        current_stock: 0,
        minimum_stock: 0,
        is_active: true,
        notes: '',
        errors: {},
        processing: false,
    });

    it('renders form fields', () => {
        const wrapper = mount(IngredientForm, {
            props: {
                form: makeForm(),
                units: ['g', 'kg'],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Név');
        expect(wrapper.text()).toContain('Mertekegyseg');
        expect(wrapper.text()).toContain('Becsult egysegkoltseg');
        expect(wrapper.text()).toContain('Aktualis keszlet');
        expect(wrapper.text()).toContain('Slug');
    });

    it('shows validation errors', () => {
        const form = reactive(makeForm());
        form.errors.name = 'A nev kotelezo.';

        const wrapper = mount(IngredientForm, {
            props: {
                form,
                units: ['g', 'kg'],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('A nev kotelezo.');
    });

    it('auto-generates slug from name and keeps slug input disabled', async () => {
        const form = reactive(makeForm());

        const wrapper = mount(IngredientForm, {
            props: {
                form,
                units: ['g', 'kg'],
            },
            global: { stubs },
        });

        await wrapper.find('#ingredient-name').setValue('Buza Liszt 00');
        await Promise.resolve();

        expect(form.slug).toBe('buza-liszt-00');
        expect(wrapper.find('#ingredient-slug').attributes('disabled')).toBeDefined();
    });
});

