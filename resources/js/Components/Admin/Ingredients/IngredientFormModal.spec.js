import { mount } from '@vue/test-utils';
import IngredientFormModal from './IngredientFormModal.vue';

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

describe('IngredientFormModal', () => {
    const makeForm = () => ({
        name: '',
        slug: '',
        sku: '',
        unit: 'kg',
        current_stock: 0,
        minimum_stock: 0,
        is_active: true,
        notes: '',
        errors: {},
        processing: false,
    });

    it('renders core fields', () => {
        const wrapper = mount(IngredientFormModal, {
            props: {
                visible: true,
                mode: 'create',
                form: makeForm(),
                units: ['g', 'kg'],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Nev');
        expect(wrapper.text()).toContain('Mertekegyseg');
        expect(wrapper.text()).toContain('Aktualis keszlet');
    });

    it('shows validation message', () => {
        const form = makeForm();
        form.errors.name = 'A nev kotelezo.';

        const wrapper = mount(IngredientFormModal, {
            props: {
                visible: true,
                mode: 'create',
                form,
                units: ['g', 'kg'],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('A nev kotelezo.');
    });
});
