import { mount } from '@vue/test-utils';
import ProductRecipeTable from './ProductRecipeTable.vue';

const stubs = {
    DataTable: { props: ['value'], template: '<div><slot name="empty" /><slot /></div>' },
    Column: { template: '<div />' },
    Button: { template: '<button />' },
};

describe('ProductRecipeTable', () => {
    it('renders empty state text', () => {
        const wrapper = mount(ProductRecipeTable, {
            props: { items: [] },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('A termekhez meg nincs recept tetel.');
    });
});
