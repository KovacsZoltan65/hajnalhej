import { mount } from '@vue/test-utils';
import WeeklyMenuItemsModal from './WeeklyMenuItemsModal.vue';

const stubs = {
    Dialog: { props: ['visible'], template: '<div v-if="visible"><slot /></div>' },
    DataTable: { props: ['value'], template: '<div><slot name="empty" /></div>' },
    Column: { template: '<div />' },
    Button: { props: ['label'], template: '<button @click="$emit(\'click\')">{{ label }}</button>' },
    WeeklyMenuStatusBadge: { template: '<span>badge</span>' },
    CreateWeeklyMenuItemModal: { template: '<div />' },
    EditWeeklyMenuItemModal: { template: '<div />' },
};

describe('WeeklyMenuItemsModal', () => {
    it('renders list-only modal with menu title and empty state', () => {
        const wrapper = mount(WeeklyMenuItemsModal, {
            props: {
                visible: true,
                menu: { id: 1, title: 'Heti menü teszt', week_start: '2026-04-20', week_end: '2026-04-26', items: [] },
                products: [{ id: 1, name: 'Kenyér', category_name: 'Kenyerek', price: 1200 }],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('A heti menühöz tartozó tételek listája.');
        expect(wrapper.text()).toContain('Nincs tétel a heti menühöz.');
    });
});
