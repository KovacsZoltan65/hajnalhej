import { mount } from '@vue/test-utils';
import PermissionGroupCard from './PermissionGroupCard.vue';

const stubs = {
    Checkbox: {
        props: ['modelValue', 'inputId'],
        emits: ['update:modelValue'],
        template: '<input :id="inputId" type="checkbox" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
    },
    PermissionBadge: {
        props: ['permission'],
        template: '<span>{{ permission }}</span>',
    },
};

describe('PermissionGroupCard', () => {
    it('renders permission group items', () => {
        const wrapper = mount(PermissionGroupCard, {
            props: {
                groupName: 'Orders',
                items: [
                    {
                        name: 'orders.view',
                        label: 'Orders view',
                        description: 'Can view orders',
                        dangerous: false,
                    },
                ],
                selectedPermissions: [],
                disabled: false,
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Orders');
        expect(wrapper.text()).toContain('Orders view');
        expect(wrapper.text()).toContain('orders.view');
    });
});
