import { mount } from '@vue/test-utils';
import PermissionDangerBadge from './PermissionDangerBadge.vue';

describe('PermissionDangerBadge', () => {
    it('renders dangerous label', () => {
        const wrapper = mount(PermissionDangerBadge, {
            props: { dangerous: true },
        });

        expect(wrapper.text()).toContain('Veszélyes');
    });
});
