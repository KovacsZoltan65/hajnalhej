import { mount } from '@vue/test-utils';
import PermissionRegistryStateBadge from './PermissionRegistryStateBadge.vue';

describe('PermissionRegistryStateBadge', () => {
    it('renders human readable registry state', () => {
        const wrapper = mount(PermissionRegistryStateBadge, {
            props: { state: 'missing_in_db' },
        });

        expect(wrapper.text()).toContain('Missing In DB');
    });
});
