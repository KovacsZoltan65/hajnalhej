import { mount } from '@vue/test-utils';
import EditModal from './EditModal.vue';

vi.mock('laravel-vue-i18n', () => ({
    trans: (key, replacements = {}) => {
        const translations = {
            'common.cancel': 'Mégse',
            'admin_production_plans.modals.edit_title': 'Gyártási terv szerkesztése',
            'admin_production_plans.actions.save': 'Mentés',
            'admin_production_plans.summary.active_minutes': 'Aktív idő',
            'admin_production_plans.summary.wait_minutes': 'Várakozási idő',
            'admin_production_plans.summary.recipe_minutes': 'Teljes receptidő',
            'admin_production_plans.summary.shortage_ingredients': 'Hiányos alapanyagok',
            'admin_production_plans.units.minutes': ':count perc',
            'admin_production_plans.units.pieces': ':count db',
            'admin_production_plans.timeline.title': 'Timeline',
            'admin_production_plans.timeline.summary': ':steps lépés | dependency: :dependencies | kezdés: :start',
            'admin_production_plans.timeline.real_timeline': 'Valós timeline',
            'admin_production_plans.timeline.steps_count': ':count lépés',
            'admin_production_plans.timeline.empty': 'Nincs generált timeline. Adj meg receptlépéseket a termékekhez.',
            'admin_production_plans.timeline.starter': 'Starter',
            'admin_production_plans.timeline.main_step': 'Fő lépés',
            'admin_production_plans.timeline.duration_line': 'Aktív: :active perc | Várakozás: :wait perc',
            'admin_production_plans.timeline.depends_on_product': 'Függőség céltermék: :product',
            'admin_production_plans.requirements.title': 'Összesített alapanyag igény',
        };

        let value = translations[key] ?? key;
        Object.entries(replacements).forEach(([name, replacement]) => {
            value = value.replace(`:${name}`, replacement);
        });
        return value;
    },
}));

const stubs = {
    Dialog: {
        props: ['visible'],
        emits: ['update:visible'],
        template: '<div><slot /><slot name="footer" /></div>',
    },
    Button: {
        props: ['label'],
        emits: ['click'],
        template: '<button type="button" @click="$emit(\'click\')">{{ label }}<slot /></button>',
    },
    ProductionPlanForm: {
        template: '<div data-test="plan-form" />',
    },
};

describe('EditModal', () => {
    const makeForm = () => ({
        target_ready_at: '',
        status: 'draft',
        is_locked: false,
        notes: '',
        items: [{ product_id: 1, target_quantity: 1, unit_label: 'db', sort_order: 0 }],
        errors: {},
        processing: false,
    });

    it('seedelt timeline adatra nem ures allapotot mutat', () => {
        const wrapper = mount(EditModal, {
            props: {
                visible: true,
                form: makeForm(),
                products: [{ id: 1, name: 'Egyszeru kovaszos feher kenyer', slug: 'egyszeru-kovaszos-feher-kenyer' }],
                statuses: [{ value: 'draft', label: 'Draft' }],
                selectedPlan: {
                    details: {
                        summary: {
                            total_active_minutes: 120,
                            total_wait_minutes: 300,
                            total_recipe_minutes: 420,
                            shortage_ingredients_count: 0,
                            timeline_steps_count: 2,
                            dependency_steps_count: 1,
                            timeline_start_at: '2026-04-21 00:30:00',
                        },
                        ingredient_requirements: [],
                        timeline_steps: [
                            {
                                id: 1,
                                title: 'Kovasz etetese',
                                starts_at: '2026-04-21 00:30:00',
                                ends_at: '2026-04-21 01:00:00',
                                duration_minutes: 30,
                                wait_minutes: 0,
                                is_dependency: true,
                                depends_on_product_name: 'Egyszeru kovaszos feher kenyer',
                            },
                        ],
                    },
                },
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Kovasz etetese');
        expect(wrapper.text()).not.toContain('Nincs generált timeline');
    });
});
