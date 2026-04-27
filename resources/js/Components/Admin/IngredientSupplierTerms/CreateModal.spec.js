import { mount } from '@vue/test-utils';
import CreateModal from './CreateModal.vue';

const stubs = {
    Button: { props: ['label'], template: '<button>{{ label }}</button>' },
    Dialog: { props: ['visible', 'header'], template: '<div v-if="visible"><h2>{{ header }}</h2><slot /><slot name="footer" /></div>' },
    TermForm: { template: '<div>Term form</div>' },
};

describe('IngredientSupplierTerms CreateModal', () => {
    it('renders create modal', () => {
        const wrapper = mount(CreateModal, {
            props: {
                visible: true,
                form: { processing: false, errors: {} },
                ingredients: [],
                suppliers: [],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Új beszállítói feltétel');
        expect(wrapper.text()).toContain('Létrehozás');
    });
});
