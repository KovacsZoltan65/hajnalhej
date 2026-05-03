import { mount } from '@vue/test-utils';
import ProductionTimelinePanel from './ProductionTimelinePanel.vue';

vi.mock('laravel-vue-i18n', () => ({
    trans: (key, replacements = {}) => {
        const translations = {
            'admin_production_plans.timeline.real_timeline': 'Valós timeline',
            'admin_production_plans.timeline.steps_count': ':count lépés',
            'admin_production_plans.timeline.empty': 'Nincs generált timeline. Adj meg receptlépéseket a termékekhez.',
            'admin_production_plans.timeline.starter': 'Starter',
            'admin_production_plans.timeline.main_step': 'Fő lépés',
            'admin_production_plans.timeline.duration_line': 'Aktív: :active perc | Várakozás: :wait perc',
            'admin_production_plans.timeline.work_instruction': 'Mit kell csinálni',
            'admin_production_plans.timeline.completion_criteria': 'Kész állapot',
            'admin_production_plans.timeline.depends_on_product': 'Függőség céltermék: :product',
        };

        let value = translations[key] ?? key;
        Object.entries(replacements).forEach(([name, replacement]) => {
            value = value.replace(`:${name}`, replacement);
        });
        return value;
    },
}));

describe('ProductionTimelinePanel', () => {
    it('renders empty state', () => {
        const wrapper = mount(ProductionTimelinePanel, {
            props: {
                steps: [],
            },
        });

        expect(wrapper.text()).toContain('Nincs generált timeline');
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
        expect(wrapper.text()).toContain('Fő lépés');
        expect(wrapper.text()).toContain('Etess 1:2:2 aranyban.');
    });
});
