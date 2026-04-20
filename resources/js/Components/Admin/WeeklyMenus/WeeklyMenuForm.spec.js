import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import WeeklyMenuForm from './WeeklyMenuForm.vue';

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
    ToggleSwitch: {
        props: ['modelValue'],
        emits: ['update:modelValue'],
        template: '<input type="checkbox" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
    },
    Select: {
        props: ['modelValue', 'options', 'optionLabel', 'optionValue'],
        emits: ['update:modelValue'],
        template: '<select @change="$emit(\'update:modelValue\', $event.target.value)"><slot /></select>',
    },
};

describe('WeeklyMenuForm', () => {
    const makeForm = () => ({
        title: '',
        slug: '',
        week_start: '',
        week_end: '',
        status: 'draft',
        public_note: '',
        internal_note: '',
        is_featured: false,
        errors: {},
        processing: false,
    });

    it('renders form fields', () => {
        const wrapper = mount(WeeklyMenuForm, {
            props: {
                form: makeForm(),
                statuses: [{ value: 'draft', label: 'Piszkozat' }],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Cim');
        expect(wrapper.text()).toContain('Het kezdete');
        expect(wrapper.text()).toContain('Statusz');
        expect(wrapper.text()).toContain('Slug');
    });

    it('shows validation errors', () => {
        const form = reactive(makeForm());
        form.errors.title = 'A cim kotelezo.';

        const wrapper = mount(WeeklyMenuForm, {
            props: {
                form,
                statuses: [{ value: 'draft', label: 'Piszkozat' }],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('A cim kotelezo.');
    });

    it('auto-generates slug from title and keeps slug input disabled', async () => {
        const form = reactive(makeForm());

        const wrapper = mount(WeeklyMenuForm, {
            props: {
                form,
                statuses: [{ value: 'draft', label: 'Piszkozat' }],
            },
            global: { stubs },
        });

        await wrapper.find('#weekly-menu-title').setValue('Jovo Het Menu');
        await Promise.resolve();

        expect(form.slug).toBe('jovo-het-menu');
        expect(wrapper.find('#weekly-menu-slug').attributes('disabled')).toBeDefined();
    });
});
