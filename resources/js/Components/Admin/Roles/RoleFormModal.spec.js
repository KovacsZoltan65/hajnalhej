import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import RoleFormModal from './RoleFormModal.vue';

const stubs = {
    Dialog: {
        props: ['visible', 'header'],
        template: '<div><h3>{{ header }}</h3><slot /><slot name="footer" /></div>',
    },
    InputText: {
        props: ['modelValue', 'id', 'invalid'],
        emits: ['update:modelValue'],
        template: '<input :id="id" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
    Button: {
        props: ['label'],
        template: '<button>{{ label }}</button>',
    },
};

describe('RoleFormModal', () => {
    it('renders modal content and validation errors', () => {
        const form = reactive({
            name: 'admin-assistant',
            processing: false,
            errors: { name: 'Role name is invalid.' },
        });

        const wrapper = mount(RoleFormModal, {
            props: {
                visible: true,
                form,
                title: 'Role create',
                submitLabel: 'Save',
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Role create');
        expect(wrapper.text()).toContain('Szerepkör neve');
        expect(wrapper.text()).toContain('Role name is invalid.');
    });
});

