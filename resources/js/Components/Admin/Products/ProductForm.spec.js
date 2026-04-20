import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import ProductForm from './ProductForm.vue';

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

describe('ProductForm', () => {
    const makeForm = () => ({
        category_id: null,
        name: '',
        slug: '',
        short_description: '',
        description: '',
        price: 0,
        is_active: true,
        is_featured: false,
        stock_status: 'in_stock',
        image_path: '',
        sort_order: 0,
        errors: {},
        processing: false,
    });

    it('renders form fields', () => {
        const wrapper = mount(ProductForm, {
            props: {
                form: makeForm(),
                categories: [{ id: 1, name: 'Kenyerek' }],
                stockStatuses: [{ value: 'in_stock', label: 'Raktaron' }],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Kategoria');
        expect(wrapper.text()).toContain('Ar (Ft)');
        expect(wrapper.text()).toContain('Keszlet allapot');
        expect(wrapper.text()).toContain('Slug');
    });

    it('shows validation errors', () => {
        const form = reactive(makeForm());
        form.errors.price = 'Az ar kotelezo.';

        const wrapper = mount(ProductForm, {
            props: {
                form,
                categories: [{ id: 1, name: 'Kenyerek' }],
                stockStatuses: [{ value: 'in_stock', label: 'Raktaron' }],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Az ar kotelezo.');
    });

    it('auto-generates slug from name and keeps slug input disabled', async () => {
        const form = reactive(makeForm());

        const wrapper = mount(ProductForm, {
            props: {
                form,
                categories: [{ id: 1, name: 'Kenyerek' }],
                stockStatuses: [{ value: 'in_stock', label: 'Raktaron' }],
            },
            global: { stubs },
        });

        await wrapper.find('#product-name').setValue('Kakaos Csiga Special');
        await Promise.resolve();

        expect(form.slug).toBe('kakaos-csiga-special');
        expect(wrapper.find('#product-slug').attributes('disabled')).toBeDefined();
    });
});
