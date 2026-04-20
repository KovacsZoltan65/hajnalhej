import { mount } from '@vue/test-utils';
import RecipeStepModal from './RecipeStepModal.vue';

const stubs = {
    Dialog: { props: ['visible'], template: '<div v-if="visible"><slot /><slot name="footer" /></div>' },
    InputText: { props: ['modelValue'], emits: ['update:modelValue'], template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />' },
    Textarea: { props: ['modelValue'], emits: ['update:modelValue'], template: '<textarea :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />' },
    Select: { props: ['modelValue'], emits: ['update:modelValue'], template: '<select @change="$emit(\'update:modelValue\', $event.target.value)"><slot /></select>' },
    InputNumber: { props: ['modelValue'], emits: ['update:modelValue'], template: '<input type="number" :value="modelValue" @input="$emit(\'update:modelValue\', Number($event.target.value))" />' },
    ToggleSwitch: { props: ['modelValue'], emits: ['update:modelValue'], template: '<input type="checkbox" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />' },
    Button: { template: '<button type="button"><slot /></button>' },
};

describe('RecipeStepModal', () => {
    it('renders step fields', () => {
        const wrapper = mount(RecipeStepModal, {
            props: {
                visible: true,
                item: null,
                stepTypes: [{ value: 'mixing', label: 'Mixing' }],
                errors: {},
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Lepes cim');
        expect(wrapper.text()).toContain('Lepes tipus');
        expect(wrapper.text()).toContain('Aktiv ido (perc)');
        expect(wrapper.text()).toContain('Mit kell csinalni?');
        expect(wrapper.text()).toContain('Mibol latszik, hogy kesz?');
    });

    it('emits submit payload', async () => {
        const wrapper = mount(RecipeStepModal, {
            props: {
                visible: true,
                item: null,
                stepTypes: [{ value: 'mixing', label: 'Mixing' }],
                errors: {},
            },
            global: { stubs },
        });

        await wrapper.find('form').trigger('submit.prevent');

        expect(wrapper.emitted('submit')).toBeTruthy();
        expect(wrapper.emitted('submit')[0][0]).toMatchObject({
            work_instruction: null,
            completion_criteria: null,
            attention_points: null,
            required_tools: null,
            expected_result: null,
        });
    });
});
