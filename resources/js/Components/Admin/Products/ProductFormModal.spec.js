import { mount } from '@vue/test-utils';
import ProductFormModal from './ProductFormModal.vue';

const stubs = {
    Dialog: {
        props: ['visible'],
        template: '<div v-if="visible"><slot /></div>',
    },
    InputText: {
        props: ['modelValue'],
        emits: ['update:modelValue'],
        template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
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
        props: ['modelValue'],
        emits: ['update:modelValue'],
        template: '<select @change="$emit(\'update:modelValue\', $event.target.value)"><slot /></select>',
    },
    Button: {
        emits: ['click'],
        template: '<button type="button" @click="$emit(\'click\')"><slot />Megse</button>',
    },
};

describe('ProductFormModal', () => {
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

    it('renders form fields when modal is visible', () => {
        const wrapper = mount(ProductFormModal, {
            props: {
                visible: true,
                mode: 'create',
                form: makeForm(),
                categories: [{ id: 1, name: 'Kenyerek' }],
                stockStatuses: [{ value: 'in_stock', label: 'Raktaron' }],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Kategoria');
        expect(wrapper.text()).toContain('Ar (Ft)');
        expect(wrapper.text()).toContain('Keszlet allapot');
    });

    it('shows validation errors', () => {
        const form = makeForm();
        form.errors.price = 'Az ar kotelezo.';

        const wrapper = mount(ProductFormModal, {
            props: {
                visible: true,
                mode: 'create',
                form,
                categories: [{ id: 1, name: 'Kenyerek' }],
                stockStatuses: [{ value: 'in_stock', label: 'Raktaron' }],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Az ar kotelezo.');
    });
});
