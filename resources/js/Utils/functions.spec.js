import { pageOptions } from "./functions";

describe("pageOptions", () => {
    it("uses the common page count translation when available", () => {
        const trans = (key, replacements = {}) => {
            if (key === "common.page_count") {
                return `${replacements.count} / page`;
            }

            return key;
        };

        expect(pageOptions(trans, [10, 20])).toEqual([
            { label: "10 / page", value: 10 },
            { label: "20 / page", value: 20 },
        ]);
    });

    it("falls back to a readable label when the translation key is missing", () => {
        const trans = (key) => key;

        expect(pageOptions(trans, [15])).toEqual([{ label: "15 / oldal", value: 15 }]);
    });
});
