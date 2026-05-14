import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import ExportHistoryTable from "./ExportHistoryTable.vue";
import ExportStatusBadge from "./ExportStatusBadge.vue";

const tableStubs = {
    Button: {
        template: "<button><slot /></button>",
    },
    ExportStatusBadge: true,
};

describe("ExportStatusBadge", () => {
    it("completed statuszt renderel", () => {
        const wrapper = mount(ExportStatusBadge, {
            props: { status: "completed" },
        });

        expect(wrapper.text()).toContain("common.export_completed");
    });

    it("download link csak completed exportnal lathato", () => {
        const wrapper = mount(ExportHistoryTable, {
            props: {
                exports: {
                    data: [
                        { id: 1, type: "orders", format: "csv", status: "completed", is_expired: false },
                        { id: 2, type: "orders", format: "csv", status: "failed", is_expired: false },
                    ],
                },
            },
            global: { stubs: tableStubs },
        });

        expect(wrapper.findAll("a")).toHaveLength(1);
    });
});
