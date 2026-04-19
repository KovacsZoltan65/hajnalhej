import { mount } from '@vue/test-utils';
import WeeklyMenuFormModal from './WeeklyMenuFormModal.vue';

const stubs = {
    Dialog: { props: ['visible'], template: '<div v-if="visible"><slot /></div>' },
    InputText: { props: ['modelValue'], emits: ['update:modelValue'], template: '<input :value="modelValue" />' },
    Textarea: { props: ['modelValue'], emits: ['update:modelValue'], template: '<textarea :value="modelValue" />' },
    ToggleSwitch: { props: ['modelValue'], emits: ['update:modelValue'], template: '<input type="checkbox" :checked="modelValue" />' },
    Select: { props: ['modelValue'], emits: ['update:modelValue'], template: '<select />' },
    Button: { emits: ['click'], template: '<button type="button" @click="$emit(\'click\')">x</button>' },
};

describe('WeeklyMenuFormModal', () => {
    it('renders fields when visible', () => {
        const wrapper = mount(WeeklyMenuFormModal, {
            props: {
                visible: true,
                mode: 'create',
                statuses: [{ value: 'draft', label: 'Draft' }],
                form: {
                    title: '', slug: '', week_start: '', week_end: '', status: 'draft', public_note: '', internal_note: '', is_featured: false, errors: {}, processing: false,
                },
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Cim');
        expect(wrapper.text()).toContain('Het kezdete');
    });
});
