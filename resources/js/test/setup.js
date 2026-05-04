import { config } from "@vue/test-utils";
import { route as ziggyRoute } from "../../../vendor/tightenco/ziggy";
import { Ziggy } from "../ziggy";

globalThis.route = (name, params, absolute = false, config = Ziggy) =>
    ziggyRoute(name, params, absolute, config);

config.global.mocks = config.global.mocks ?? {};
config.global.mocks.route = globalThis.route;
config.global.mocks.$t = (key) => key;

config.global.stubs = {
    DatePicker: {
        template: "<input />",
    },
    Message: {
        template: "<div><slot /></div>",
    },
};
