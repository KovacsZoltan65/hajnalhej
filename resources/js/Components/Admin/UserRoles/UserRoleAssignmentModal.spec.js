import { mount } from '@vue/test-utils';
import UserRoleAssignmentModal from './UserRoleAssignmentModal.vue';

const stubs = {
    Dialog: {
        props: ['visible', 'header'],
        template: '<div><h3>{{ header }}</h3><slot /><slot name="footer" /></div>',
    },
    Checkbox: {
        props: ['modelValue', 'inputId'],
        emits: ['update:modelValue'],
        template: '<input :id="inputId" type="checkbox" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
    },
    Button: {
        props: ['label'],
        template: '<button>{{ label }}</button>',
    },
    RoleBadge: {
        props: ['role'],
        template: '<span>{{ role }}</span>',
    },
};

describe('UserRoleAssignmentModal', () => {
    it('renders user role assignment details', () => {
        const wrapper = mount(UserRoleAssignmentModal, {
            props: {
                visible: true,
                user: {
                    id: 10,
                    name: 'Test User',
                    email: 'test@example.com',
                    permissions: ['orders.view'],
                },
                roleOptions: [
                    { name: 'admin', is_system_role: true },
                    { name: 'customer', is_system_role: true },
                ],
                selectedRoles: ['customer'],
                loading: false,
                canViewPermissions: true,
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Test User');
        expect(wrapper.text()).toContain('orders.view');
        expect(wrapper.text()).toContain('admin');
    });
});
