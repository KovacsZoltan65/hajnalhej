import { config } from "@vue/test-utils";
import { route as ziggyRoute } from "../../../vendor/tightenco/ziggy";
import { Ziggy } from "../ziggy";

globalThis.route = (name, params, absolute = false, config = Ziggy) => ziggyRoute(name, params, absolute, config);

config.global.mocks = config.global.mocks ?? {};
config.global.mocks.route = globalThis.route;
const translations = {
    "admin_categories.active_category": "Aktív kategória",
    "admin_categories.columns.sort_order": "Sorrend",
    "admin_ingredients.active_ingredient": "Aktív alapanyag",
    "admin_ingredients.columns.estimated_unit_cost": "Becsult egysegkoltseg",
    "admin_ingredients.filters.unit": "Mertekegyseg",
    "admin_orders.columns.total": "Vegosszeg",
    "admin_orders.fields.name": "Név",
    "admin_permissions.fields.description": "Leírás",
    "admin_procurement_intelligence.columns.current_stock": "Aktualis keszlet",
    "admin_procurement_intelligence.columns.minimum_stock": "Minimum keszlet",
    "admin_procurement_intelligence.reorder_level": "Újrarendelési szint",
    "admin_production_plans.form.notes": "Megjegyzés",
    "admin_weekly_menus.columns.items": "Tételek",
    "cart.check_product": "Ellenőrizd a termékeket",
    "cart.choose_favorites": "Válassz kedvenceket",
    "cart.empty": "A kosarad jelenleg ures",
    "cart.proceed_to_checkout": "Tovább a pénztárhoz",
    "cart.view_weekly_menu": "Heti menü megtekintése",
    "common.automatic_generated": "Automatikusan generált.",
    "common.delete": "Törlés",
    "common.name": "Név",
    "common.sku": "SKU",
    "common.slug": "Slug",
    "common.summary": "Összegzés",
};

config.global.mocks.$t = (key) => translations[key] ?? key;

config.global.stubs = {
    DatePicker: {
        template: "<input />",
    },
    Message: {
        template: "<div><slot /></div>",
    },
};
