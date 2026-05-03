import { mount } from '@vue/test-utils';
import { router } from '@inertiajs/vue3';
import IndexPage from './Index.vue';

const { translate } = vi.hoisted(() => {
    const translations = {
        'common.clear_filters': 'Szűrők törlése',
        'common.locale': 'hu-HU',
        'common.currency': 'HUF',
        'admin_procurement_intelligence.meta_title': 'Beszerzési intelligencia',
        'admin_procurement_intelligence.eyebrow': 'Admin / Beszerzés',
        'admin_procurement_intelligence.title': 'Beszerzési intelligencia',
        'admin_procurement_intelligence.description':
            'Valós beszerzési tételek, készletmozgások és BOM használat alapján számolt ártrendek, fogyási előrejelzés és minimum készlet alapú utánrendelési jelzések.',
        'admin_procurement_intelligence.filters.ingredient': 'Alapanyag',
        'admin_procurement_intelligence.filters.supplier': 'Beszállító',
        'admin_procurement_intelligence.filters.urgency': 'Sürgősség',
        'admin_procurement_intelligence.filters.alert_type': 'Figyelmeztetés típusa',
        'admin_procurement_intelligence.actions.generate_purchase_drafts': 'Beszerzési tervezet készítése',
        'admin_procurement_intelligence.selection.selected': ':count kijelölt javaslat',
        'admin_procurement_intelligence.selection.generatable': ':count generálható javaslat',
        'admin_procurement_intelligence.summary.active_alerts': 'Aktív figyelmeztetés',
        'admin_procurement_intelligence.summary.filtered_hint': 'Szűrők után számolva',
        'admin_procurement_intelligence.summary.critical_reorders': 'Kritikus utánrendelés',
        'admin_procurement_intelligence.summary.critical_hint': 'Azonnali beszerzési figyelem',
        'admin_procurement_intelligence.summary.price_increase': 'Áremelkedés',
        'admin_procurement_intelligence.summary.price_increase_hint': '10% vagy nagyobb növekedés',
        'admin_procurement_intelligence.summary.stockout_risk': 'Elfogyási kockázat',
        'admin_procurement_intelligence.summary.stockout_hint': '7 napon belüli fedezet',
        'admin_procurement_intelligence.counts.rows': ':count sor',
        'admin_procurement_intelligence.counts.points': ':count pont',
        'admin_procurement_intelligence.counts.ingredients': ':count alapanyag',
        'admin_procurement_intelligence.counts.alerts': ':count jelzés',
        'admin_procurement_intelligence.columns.ingredient': 'Alapanyag',
        'admin_procurement_intelligence.columns.supplier': 'Beszállító',
        'admin_procurement_intelligence.columns.last_price': 'Utolsó ár',
        'admin_procurement_intelligence.columns.previous_price': 'Előző ár',
        'admin_procurement_intelligence.columns.change': 'Változás',
        'admin_procurement_intelligence.columns.change_percent': 'Változás %',
        'admin_procurement_intelligence.columns.cheapest': 'Legolcsóbb',
        'admin_procurement_intelligence.columns.most_expensive': 'Legdrágább',
        'admin_procurement_intelligence.columns.trend': 'Trend',
        'admin_procurement_intelligence.columns.date': 'Dátum',
        'admin_procurement_intelligence.columns.average_price': 'Átlagár',
        'admin_procurement_intelligence.columns.weighted_average': 'Súlyozott átlag',
        'admin_procurement_intelligence.columns.quantity': 'Mennyiség',
        'admin_procurement_intelligence.columns.current_stock': 'Aktuális készlet',
        'admin_procurement_intelligence.columns.minimum_stock': 'Minimum készlet',
        'admin_procurement_intelligence.columns.weekly_average_consumption': 'Heti átlagfogyás',
        'admin_procurement_intelligence.columns.recommended_supplier': 'Ajánlott beszállító',
        'admin_procurement_intelligence.columns.lead_time': 'Lead time',
        'admin_procurement_intelligence.columns.pack_size': 'Csomag',
        'admin_procurement_intelligence.columns.minimum_order': 'Minimum rendelés',
        'admin_procurement_intelligence.columns.stock_days': 'Készlet nap',
        'admin_procurement_intelligence.columns.suggested_order': 'Javasolt rendelés',
        'admin_procurement_intelligence.columns.urgency': 'Sürgősség',
        'admin_procurement_intelligence.columns.last_week_consumption': 'Elmúlt heti fogyás',
        'admin_procurement_intelligence.columns.four_week_average': '4 hetes átlag',
        'admin_procurement_intelligence.columns.next_week_forecast': 'Következő heti várható',
        'admin_procurement_intelligence.columns.coverage_days': 'Fedezet nap',
        'admin_procurement_intelligence.supplier_price_trends.title': 'Beszállítói ártrend',
        'admin_procurement_intelligence.supplier_price_trends.empty': 'Nincs beszerzési ártrend a kiválasztott szűrőkkel.',
        'admin_procurement_intelligence.ingredient_cost_trends.title': 'Alapanyag költség-idősor',
        'admin_procurement_intelligence.ingredient_cost_trends.empty': 'Nincs költség-idősor adat a kiválasztott időablakban.',
        'admin_procurement_intelligence.recent_purchases.title': 'Legutóbbi 5 beszerzés',
        'admin_procurement_intelligence.recent_purchases.empty': 'Még nincs friss beszerzési tétel.',
        'admin_procurement_intelligence.minimum_stock.title': 'Utánrendelési javaslat',
        'admin_procurement_intelligence.minimum_stock.select_all': 'Minden utánrendelési javaslat kijelölése',
        'admin_procurement_intelligence.minimum_stock.select_one': ':name kijelölése beszerzési tervezethez',
        'admin_procurement_intelligence.minimum_stock.no_supplier': 'Nincs beszállító',
        'admin_procurement_intelligence.minimum_stock.empty': 'Nincs utánrendelési javaslat a jelenlegi készlet és fogyás alapján.',
        'admin_procurement_intelligence.weekly_forecast.title': 'Heti várható fogyás',
        'admin_procurement_intelligence.weekly_forecast.subtitle': '4 hetes átlag',
        'admin_procurement_intelligence.weekly_forecast.empty': 'Nincs production_out fogyási adat az előrejelzéshez.',
        'admin_procurement_intelligence.alerts.title': 'Beszerzési figyelmeztetések',
        'admin_procurement_intelligence.alerts.empty': 'Nincs beszerzési figyelmeztetés a kiválasztott szűrők mellett.',
        'admin_procurement_intelligence.alert_types.price_increase': 'Áremelkedés',
        'admin_procurement_intelligence.urgencies.critical': 'Kritikus',
        'admin_procurement_intelligence.urgencies.high': 'Magas',
        'admin_procurement_intelligence.urgencies.medium': 'Közepes',
        'admin_procurement_intelligence.urgencies.low': 'Alacsony',
        'admin_procurement_intelligence.supplier_sources.preferred_supplier': 'Preferált',
        'admin_procurement_intelligence.supplier_sources.latest_supplier': 'Legutóbbi',
        'admin_procurement_intelligence.supplier_sources.cheapest_fresh_supplier': 'Legolcsóbb friss',
        'admin_procurement_intelligence.supplier_sources.none': 'Nincs adat',
        'admin_procurement_intelligence.units.days': ':count nap',
        'admin_procurement_intelligence.calculation.title': 'Számítási alapok',
        'admin_procurement_intelligence.calculation.consumption_window': 'Fogyási ablak: :days nap production_out átlag.',
        'admin_procurement_intelligence.calculation.price_increase': 'Áremelkedés jelzés: :percent% felett.',
        'admin_procurement_intelligence.calculation.stockout_risk': 'Elfogyási kockázat: :days napon belül.',
        'admin_procurement_intelligence.calculation.reorder_target':
            'Utánrendelési cél: minimum készlet, lead time igény és :days nap biztonsági puffer maximuma.',
    };

    return {
        translate: (key, replacements = {}) => {
            let value = translations[key] ?? key;
            Object.entries(replacements).forEach(([name, replacement]) => {
                value = value.replace(`:${name}`, replacement);
            });
            return value;
        },
    };
});

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    router: { get: vi.fn(), post: vi.fn() },
}));

vi.mock('laravel-vue-i18n', () => ({
    trans: translate,
}));

vi.mock('primevue/button', () => ({
    default: {
        name: 'Button',
        props: ['label', 'icon', 'loading', 'disabled'],
        emits: ['click'],
        template: '<button :disabled="disabled" @click="$emit(\'click\')">{{ label }}</button>',
    },
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/select', () => ({
    default: {
        name: 'Select',
        props: ['modelValue', 'options', 'optionLabel', 'optionValue', 'placeholder', 'showClear', 'filter'],
        emits: ['update:model-value'],
        template: '<select class="select-stub"></select>',
    },
}));

const filterOptions = {
    ingredients: [{ label: 'Liszt', value: 1 }],
    suppliers: [{ label: 'Malom Kft.', value: 1 }],
    days: [{ label: '30 nap', value: 30 }],
    urgencies: [{ label: 'Kritikus', value: 'critical' }],
    alert_types: [{ label: 'Áremelkedés', value: 'price_increase' }],
};

const dashboard = {
    defaults: {
        consumption_window_days: 28,
        price_increase_alert_percent: 10,
        stockout_warning_days: 7,
        minimum_stock_target_days: 14,
        safety_stock_days: 3,
    },
    summary: {
        alerts_count: 2,
        critical_minimum_stock_count: 1,
        price_increase_count: 1,
        stockout_risk_count: 1,
    },
    supplier_price_trends: [
        {
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            unit: 'kg',
            supplier_id: 1,
            supplier_name: 'Malom Kft.',
            last_unit_cost: 360,
            previous_unit_cost: 300,
            change_amount: 60,
            change_percent: 20,
            cheapest_supplier: { supplier_name: 'Malom Kft.', unit_cost: 360 },
            most_expensive_supplier: { supplier_name: 'Malom Kft.', unit_cost: 360 },
            trend: 'emelkedik',
        },
    ],
    ingredient_cost_trends: [
        {
            period_date: '2026-04-24',
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            unit: 'kg',
            supplier_id: 1,
            supplier_name: 'Malom Kft.',
            average_unit_cost: 360,
            weighted_average_cost: 360,
            last_unit_cost: 360,
            purchased_quantity: 10,
            purchases_count: 1,
        },
    ],
    recent_purchases: [
        {
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            supplier_name: 'Malom Kft.',
            quantity: 10,
            unit: 'kg',
            unit_cost: 360,
            line_total: 3600,
            purchase_date: '2026-04-24',
        },
    ],
    minimum_stock_recommendations: [
        {
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            unit: 'kg',
            current_stock: 2,
            minimum_stock: 5,
            weekly_average_consumption: 7,
            daily_average_consumption: 1,
            lead_time_days: 2,
            lead_time_demand: 2,
            safety_stock: 3,
            target_stock: 5,
            days_on_hand: 2,
            raw_suggested_order_quantity: 3,
            suggested_order_quantity: 12,
            recommended_supplier_id: 1,
            recommended_supplier_name: 'Malom Kft.',
            supplier_source: 'preferred_supplier',
            pack_size: 6,
            minimum_order_quantity: 12,
            unit_cost: 360,
            urgency: 'critical',
        },
    ],
    weekly_consumption_forecast: [
        {
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            unit: 'kg',
            last_week_consumption: 7,
            four_week_average: 7,
            next_week_forecast: 7,
            coverage_days: 2,
        },
    ],
    alerts: [
        {
            type: 'price_increase',
            severity: 'high',
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            message: 'Az utolsó beszerzési ár legalább 10%-kal emelkedett.',
            context: {},
        },
    ],
};

const mountPage = (overrides = {}) =>
    mount(IndexPage, {
        props: {
            filters: { days: 30, ingredient_id: null, supplier_id: null, urgency: '', alert_type: '' },
            dashboard: { ...dashboard, ...overrides },
            filter_options: filterOptions,
        },
        global: {
            mocks: {
                $t: translate,
            },
        },
    });

describe('Admin Procurement Intelligence page', () => {
    it('renders dashboard cards, tables and alert panel', () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain('Beszerzési intelligencia');
        expect(wrapper.text()).toContain('Aktív figyelmeztetés');
        expect(wrapper.text()).toContain('Beszállítói ártrend');
        expect(wrapper.text()).toContain('Alapanyag költség-idősor');
        expect(wrapper.text()).toContain('Utánrendelési javaslat');
        expect(wrapper.text()).toContain('Heti várható fogyás');
        expect(wrapper.text()).toContain('Beszerzési figyelmeztetések');
        expect(wrapper.text()).toContain('Liszt');
        expect(wrapper.text()).toContain('Kritikus');
    });

    it('renders empty states', () => {
        const wrapper = mountPage({
            supplier_price_trends: [],
            ingredient_cost_trends: [],
            recent_purchases: [],
            minimum_stock_recommendations: [],
            weekly_consumption_forecast: [],
            alerts: [],
            summary: {
                alerts_count: 0,
                critical_minimum_stock_count: 0,
                price_increase_count: 0,
                stockout_risk_count: 0,
            },
        });

        expect(wrapper.text()).toContain('Nincs beszerzési ártrend');
        expect(wrapper.text()).toContain('Nincs utánrendelési javaslat');
        expect(wrapper.text()).toContain('Nincs beszerzési figyelmeztetés');
        expect(wrapper.text()).toContain('Nincs production_out fogyási adat');
    });

    it('posts selected recommendations for purchase draft generation', async () => {
        const wrapper = mountPage();

        await wrapper.find('tbody input[type="checkbox"]').setValue(true);
        await wrapper.findAll('button').find((button) => button.text() === 'Beszerzési tervezet készítése').trigger('click');

        expect(router.post).toHaveBeenCalledWith(
            '/admin/procurement-intelligence/purchase-drafts',
            expect.objectContaining({
                days: 30,
                ingredient_ids: [1],
            }),
            expect.any(Object),
        );
    });
});
