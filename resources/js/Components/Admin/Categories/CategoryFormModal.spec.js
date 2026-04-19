import { mount } from '@vue/test-utils';
import CategoryFormModal from './CategoryFormModal.vue';

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
    Button: {
        emits: ['click'],
        template: '<button type="button" @click="$emit(\'click\')"><slot />Megse</button>',
    },
};

describe('CategoryFormModal', () => {
    const makeForm = () => ({
        name: '',
        slug: '',
        description: '',
        is_active: true,
        sort_order: 0,
        errors: {},
        processing: false,
    });

    it('renders fields when open and emits close', async () => {
        const wrapper = mount(CategoryFormModal, {
            props: {
                visible: true,
                mode: 'create',
                form: makeForm(),
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Nev');
        expect(wrapper.text()).toContain('Slug');

        await wrapper.find('button').trigger('click');
        expect(wrapper.emitted('update:visible')).toBeTruthy();
    });

    it('shows validation error text', () => {
        const form = makeForm();
        form.errors.name = 'A kategoria neve kotelezo.';

        const wrapper = mount(CategoryFormModal, {
            props: {
                visible: true,
                mode: 'create',
                form,
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('A kategoria neve kotelezo.');
    });
});
