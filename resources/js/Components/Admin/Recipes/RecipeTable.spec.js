import { mount } from '@vue/test-utils';
import RecipeTable from './RecipeTable.vue';

const stubs = {
    DataTable: { props: ['value'], template: '<div><slot name="empty" /><slot /></div>' },
    Column: { template: '<div />' },
    Button: { template: '<button />' },
    CategoryStatusBadge: { template: '<span>status</span>' },
};

describe('RecipeTable', () => {
    it('renders empty state', () => {
        const wrapper = mount(RecipeTable, {
            props: {
                recipes: { data: [], per_page: 10, total: 0 },
                loading: false,
                first: 0,
                sortField: 'name',
                sortOrder: 1,
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Nincs megjeleníthető recept.');
    });
});
