import { mount } from '@vue/test-utils';
import PermissionSyncSummaryModal from './PermissionSyncSummaryModal.vue';

vi.mock('primevue/dialog', () => ({
    default: { template: '<div><slot /><slot name="footer" /></div>' },
}));

vi.mock('primevue/button', () => ({
    default: { props: ['label'], template: '<button>{{ label }}</button>' },
}));

describe('PermissionSyncSummaryModal', () => {
    it('renders sync summary content', () => {
        const wrapper = mount(PermissionSyncSummaryModal, {
            props: {
                visible: true,
                summary: {
                    created_count: 1,
                    existing_count: 10,
                    orphan_count: 2,
                    created_permissions: ['permissions.view'],
                    orphan_permissions: ['legacy.custom'],
                },
            },
        });

        expect(wrapper.text()).toContain('Created');
        expect(wrapper.text()).toContain('permissions.view');
        expect(wrapper.text()).toContain('legacy.custom');
    });
});
