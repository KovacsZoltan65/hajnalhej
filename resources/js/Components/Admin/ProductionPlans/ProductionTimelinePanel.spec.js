import { mount } from '@vue/test-utils';
import ProductionTimelinePanel from './ProductionTimelinePanel.vue';

describe('ProductionTimelinePanel', () => {
    it('renders empty state', () => {
        const wrapper = mount(ProductionTimelinePanel, {
            props: {
                steps: [],
            },
        });

        expect(wrapper.text()).toContain('Nincs generalt timeline');
    });

    it('renders timeline rows including dependency marker', () => {
        const wrapper = mount(ProductionTimelinePanel, {
            props: {
                steps: [
                    {
                        id: 1,
                        title: 'Kovasz etetese',
                        starts_at: '2026-04-21 00:30:00',
                        ends_at: '2026-04-21 01:00:00',
                        duration_minutes: 30,
                        wait_minutes: 0,
                        work_instruction: 'Etess 1:2:2 aranyban.',
                        completion_criteria: 'Duplazodas 4 oran belul.',
                        is_dependency: true,
                        depends_on_product_name: 'Egyszeru kovaszos feher kenyer',
                    },
                    {
                        id: 2,
                        title: 'Sutes',
                        starts_at: '2026-04-21 07:10:00',
                        ends_at: '2026-04-21 08:00:00',
                        duration_minutes: 50,
                        wait_minutes: 0,
                        is_dependency: false,
                    },
                ],
            },
        });

        expect(wrapper.text()).toContain('Kovasz etetese');
        expect(wrapper.text()).toContain('Starter');
        expect(wrapper.text()).toContain('Fo lepes');
        expect(wrapper.text()).toContain('Etess 1:2:2 aranyban.');
    });
});
