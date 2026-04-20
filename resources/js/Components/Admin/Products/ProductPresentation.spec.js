import { mount } from '@vue/test-utils';
import ProductPrice from './ProductPrice.vue';

describe('Product presentation components', () => {
    it('renders formatted price', () => {
        const wrapper = mount(ProductPrice, {
            props: { price: 2450 },
        });

        expect(wrapper.text()).toContain('2 450 Ft');
    });
});
