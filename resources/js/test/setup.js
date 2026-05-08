import { config } from "@vue/test-utils";
import { route as ziggyRoute } from "../../../vendor/tightenco/ziggy";
import { Ziggy } from "../ziggy";
import { vi } from "vitest";

globalThis.route = (name, params, absolute = false, config = Ziggy) => ziggyRoute(name, params, absolute, config);

config.global.mocks = config.global.mocks ?? {};
config.global.mocks.route = globalThis.route;
const translations = {
    "admin_product.actions.create": "Uj termek",
    "admin_product.actions.edit": "Termek szerkesztese",
    "admin_categories.active_category": "Aktív kategória",
    "common.sort_order": "Sorrend",
    "admin_ingredients.active_ingredient": "Aktív alapanyag",
    "admin_ingredients.columns.estimated_unit_cost": "Becsult egysegkoltseg",
    "common.unit": "Mertekegyseg",
    "admin_orders.columns.total": "Vegosszeg",
    "common.name": "Név",
    "admin_permissions.fields.description": "Leírás",
    "admin_procurements.empty": "Nincs megjeleníthető beszerzés",
    "admin_procurements.title": "Beszerzések",
    "admin_procurement_intelligence.columns.current_stock": "Aktualis keszlet",
    "admin_procurement_intelligence.columns.minimum_stock": "Minimum keszlet",
    "admin_procurement_intelligence.reorder_level": "Újrarendelési szint",
    "admin_production_plans.form.notes": "Megjegyzés",
    "admin_supplier_terms.actions.edit": "Beszállítói feltétel szerkesztése",
    "admin_supplier_terms.modal_header": "Új beszállítói feltétel",
    "admin_roles.new": "Új szerepkör",
    "admin_roles.role_name": "Szerepkör neve",
    "admin_roles.used_characters": "Engedélyezett karakterek: betűk, számok, kötőjel",
    "admin_supplier.title": "Beszállítók",
    "admin_supplier.empty": "Nincs megjeleníthető beszállító.",
    "admin_user.new": "Új felhasználó",
    "common.items": "Tételek",
    "common.users": "Felhasználók",
    "common.step_title": "Lépés cím",
    "common.step_type": "Lépés típus",
    "common.active_minutes": "Aktív idő",
    "common.wait_minutes": "Várakozási idő",
    "common.temperature": "Hőmérséklet",
    "common.active_step": "Aktív lépés",
    "admin_production_plans.timeline.work_instruction": "Mit kell csinálni",
    "admin_production_plans.timeline.show_is_ready": "Miből látszik, hogy kész",
    "admin_production_plans.timeline.required_tools": "Szükséges eszközök",
    "admin_production_plans.timeline.expected_result": "Várt eredmény",
    "cart.check_product": "Ellenőrizd a termékeket",
    "cart.choose_favorites": "Válassz kedvenceket",
    "cart.empty": "A kosarad jelenleg ures",
    "cart.proceed_to_checkout": "Tovább a pénztárhoz",
    "cart.view_weekly_menu": "Heti menü megtekintése",
    "common.automatic_generated": "Automatikusan generált.",
    "common.cancel": "Mégse",
    "common.creation": "Létrehozás",
    "common.delete": "Törlés",
    "common.name": "Név",
    "common.notes": "Megjegyzés",
    "common.save": "Mentés",
    "common.sku": "SKU",
    "common.slug": "Slug",
    "common.summary": "Összegzés",
};

vi.mock("laravel-vue-i18n", () => ({
    trans: (key, replacements = {}) => {
        let value = translations[key] ?? key;

        Object.entries(replacements).forEach(([name, replacement]) => {
            value = value.replace(`:${name}`, String(replacement));
        });

        return value;
    },
}));

config.global.mocks.$t = (key) => translations[key] ?? key;

config.global.stubs = {
    DatePicker: {
        template: "<input />",
    },
    Message: {
        template: "<div><slot /></div>",
    },
};
