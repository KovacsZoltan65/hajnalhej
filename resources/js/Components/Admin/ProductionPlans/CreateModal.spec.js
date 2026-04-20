import { mount } from '@vue/test-utils';
import CreateModal from './CreateModal.vue';

const stubs = {
    Dialog: {
        props: ['visible'],
        emits: ['update:visible'],
        template: '<div><slot /><slot name="footer" /></div>',
    },
    Button: {
        emits: ['click'],
        template: '<button type="button" @click="$emit(\'click\')"><slot /></button>',
    },
    ProductionPlanForm: {
        template: '<div data-test="production-plan-form" />',
    },
};

describe('CreateModal', () => {
    const makeForm = () => ({
        target_ready_at: '',
        status: 'draft',
        is_locked: false,
        notes: '',
        items: [{ product_id: 1, target_quantity: 1, unit_label: 'db', sort_order: 0 }],
        errors: {},
        processing: false,
    });

    it('renders embedded form', () => {
        const wrapper = mount(CreateModal, {
            props: {
                visible: true,
                form: makeForm(),
                products: [{ id: 1, name: 'Teszt kenyer', slug: 'teszt-kenyer' }],
                statuses: [{ value: 'draft', label: 'Draft' }],
            },
            global: { stubs },
        });

        expect(wrapper.find('[data-test="production-plan-form"]').exists()).toBe(true);
    });

    it('emits submit on form submit', async () => {
        const wrapper = mount(CreateModal, {
            props: {
                visible: true,
                form: makeForm(),
                products: [{ id: 1, name: 'Teszt kenyer', slug: 'teszt-kenyer' }],
                statuses: [{ value: 'draft', label: 'Draft' }],
            },
            global: { stubs },
        });

        await wrapper.find('#production-plan-create-form').trigger('submit');

        expect(wrapper.emitted('submit')).toBeTruthy();
    });
});
