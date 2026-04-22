import { mount } from '@vue/test-utils';
import WeeklyMenuItemsModal from './WeeklyMenuItemsModal.vue';

const stubs = {
    Dialog: { props: ['visible'], template: '<div v-if="visible"><slot /></div>' },
    DataTable: { template: '<div><slot name="empty" /></div>' },
    Column: { template: '<div />' },
    InputText: { template: '<input />' },
    InputNumber: { template: '<input />' },
    ToggleSwitch: { template: '<input type="checkbox" />' },
    Select: { template: '<div><slot /></div>' },
    Button: { template: '<button />' },
    WeeklyMenuStatusBadge: { template: '<span>badge</span>' },
};

describe('WeeklyMenuItemsModal', () => {
    it('renders with menu title', () => {
        const wrapper = mount(WeeklyMenuItemsModal, {
            props: {
                visible: true,
                menu: { id: 1, title: 'Heti menü teszt', items: [] },
                products: [{ id: 1, name: 'Kenyér', category_name: 'Kenyerek', price: 1200 }],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Nincs tétel a heti menühöz.');
    });
});
