import { mount } from '@vue/test-utils';
import CreateModal from './CreateModal.vue';
import EditModal from './EditModal.vue';

const form = {
    name: '',
    slug: '',
    category_id: null,
    short_description: '',
    description: '',
    price: 0,
    image_path: '',
    stock_status: 'available',
    sort_order: 0,
    is_active: true,
    errors: {},
    processing: false,
};

const categories = [{ id: 1, name: 'Kenyerek' }];
const stockStatuses = [{ value: 'available', label: 'Elérhető' }];

const stubs = {
    Dialog: {
        props: ['visible', 'header'],
        emits: ['update:visible'],
        template: `
            <section v-if="visible">
                <h2>{{ header }}</h2>
                <slot />
                <slot name="footer" />
            </section>
        `,
    },
    Button: {
        props: ['label', 'type', 'form'],
        emits: ['click'],
        template: '<button :type="type || \'button\'" :form="form" @click="$emit(\'click\')">{{ label }}</button>',
    },
    ProductForm: {
        props: ['form', 'categories', 'stockStatuses'],
        template: '<div data-testid="product-form">{{ categories.length }} kategória</div>',
    },
};

const mountModal = (component) => mount(component, {
    props: {
        visible: true,
        form,
        categories,
        stockStatuses,
    },
    global: { stubs },
});

describe('Product modals', () => {
    it('renders create modal and emits submit', async () => {
        const wrapper = mountModal(CreateModal);

        expect(wrapper.text()).toContain('Uj termek');
        expect(wrapper.find('[data-testid="product-form"]').exists()).toBe(true);

        await wrapper.get('form').trigger('submit');

        expect(wrapper.emitted('submit')).toHaveLength(1);
    });

    it('closes create modal from cancel action', async () => {
        const wrapper = mountModal(CreateModal);

        await wrapper.get('button[type="button"]').trigger('click');

        expect(wrapper.emitted('update:visible')).toEqual([[false]]);
    });

    it('renders edit modal and emits submit', async () => {
        const wrapper = mountModal(EditModal);

        expect(wrapper.text()).toContain('Termek szerkesztese');

        await wrapper.get('form').trigger('submit');

        expect(wrapper.emitted('submit')).toHaveLength(1);
    });
});
