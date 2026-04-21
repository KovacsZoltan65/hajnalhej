import { mount } from '@vue/test-utils';
import AuditEventBadge from './AuditEventBadge.vue';

describe('AuditEventBadge', () => {
    it('renders provided label', () => {
        const wrapper = mount(AuditEventBadge, {
            props: {
                eventKey: 'role.created',
                label: 'Role letrehozva',
            },
        });

        expect(wrapper.text()).toContain('Role letrehozva');
    });

    it('falls back to event key when no label is provided', () => {
        const wrapper = mount(AuditEventBadge, {
            props: {
                eventKey: 'user.roles.sync_blocked',
            },
        });

        expect(wrapper.text()).toContain('user.roles.sync_blocked');
    });
});
