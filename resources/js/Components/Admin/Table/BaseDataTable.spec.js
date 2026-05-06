import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import BaseDataTable from "./BaseDataTable.vue";

describe("BaseDataTable", () => {
    it("shows empty state CTA", () => {
        const wrapper = mount(BaseDataTable, {
            props: {
                value: [],
                emptyTitle: "Empty",
                emptyDescription: "Nothing here",
                emptyPrimaryLabel: "Create",
            },
            global: {
                stubs: {
                    DataTable: { template: "<div><slot name='empty' /></div>" },
                    Button: { template: "<button>{{ label }}</button>", props: ["label"] },
                },
            },
        });

        expect(wrapper.text()).toContain("Create");
    });

    it("shows skeleton while loading", () => {
        const wrapper = mount(BaseDataTable, {
            props: {
                value: [],
                loading: true,
                emptyTitle: "Empty",
                emptyDescription: "Nothing here",
            },
            global: {
                stubs: {
                    Skeleton: { template: "<span />" },
                },
            },
        });

        expect(wrapper.find("[data-testid='admin-table-skeleton']").exists()).toBe(true);
    });

    it("shows bulk action bar when rows are selected", () => {
        const wrapper = mount(BaseDataTable, {
            props: {
                value: [{ id: 1 }],
                selectedCount: 1,
                emptyTitle: "Empty",
                emptyDescription: "Nothing here",
            },
            global: {
                stubs: {
                    DataTable: { template: "<div><slot /></div>" },
                    Button: { template: "<button>{{ label }}</button>", props: ["label"] },
                },
            },
        });

        expect(wrapper.find("[data-testid='admin-bulk-action-bar']").exists()).toBe(true);
    });
});
