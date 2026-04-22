import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import CategoryForm from './CategoryForm.vue';

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
};

describe('CategoryForm', () => {
    const makeForm = () => ({
        name: '',
        slug: '',
        description: '',
        is_active: true,
        sort_order: 0,
        errors: {},
        processing: false,
    });

    it('renders category fields', () => {
        const wrapper = mount(CategoryForm, {
            props: {
                form: makeForm(),
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Név');
        expect(wrapper.text()).toContain('Slug');
        expect(wrapper.text()).toContain('Sorrend');
    });

    it('shows validation errors', () => {
        const form = reactive(makeForm());
        form.errors.name = 'A kategoria neve kotelezo.';

        const wrapper = mount(CategoryForm, {
            props: {
                form,
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('A kategoria neve kotelezo.');
    });

    it('auto-generates slug from name and keeps slug input disabled', async () => {
        const form = reactive(makeForm());

        const wrapper = mount(CategoryForm, {
            props: {
                form,
            },
            global: { stubs },
        });

        await wrapper.find('#category-name').setValue('Sos Pekaru');
        await Promise.resolve();

        expect(form.slug).toBe('sos-pekaru');
        expect(wrapper.find('#category-slug').attributes('disabled')).toBeDefined();
    });
});

