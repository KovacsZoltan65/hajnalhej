import { config } from "@vue/test-utils";

config.global.stubs = {
    DatePicker: {
        template: "<input />",
    },
    Message: {
        template: "<div><slot /></div>",
    },
};
