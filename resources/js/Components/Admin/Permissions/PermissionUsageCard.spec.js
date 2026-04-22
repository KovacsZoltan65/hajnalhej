import { mount } from '@vue/test-utils';
import PermissionUsageCard from './PermissionUsageCard.vue';

describe('PermissionUsageCard', () => {
    it('renders role and user usage numbers', () => {
        const wrapper = mount(PermissionUsageCard, {
            props: {
                rolesCount: 2,
                usersCount: 4,
                roleNames: ['admin', 'manager'],
            },
        });

        expect(wrapper.text()).toContain('2');
        expect(wrapper.text()).toContain('4');
        expect(wrapper.text()).toContain('admin');
    });
});
