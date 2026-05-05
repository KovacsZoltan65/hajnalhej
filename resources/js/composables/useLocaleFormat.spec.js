const mockPage = vi.hoisted(() => ({
    props: {
        locale: "hu-HU",
        preferences: {
            currency: "HUF",
            locale: "hu-HU",
        },
    },
}));

vi.mock("@inertiajs/vue3", () => ({
    usePage: () => mockPage,
}));

import { useLocaleFormat } from "./useLocaleFormat";

describe("useLocaleFormat", () => {
    it("formats numbers and currency from Inertia page preferences", () => {
        mockPage.props = {
            locale: "en-US",
            preferences: {
                currency: "EUR",
                locale: "de-DE",
            },
        };

        const { number, formatCurrency } = useLocaleFormat();

        expect(number(1234.5)).toBe("1.234,5");
        expect(formatCurrency(1234.5)).toMatch(/1\.235\s?€/);
    });

    it("does not use legacy page locale when preferences are absent", () => {
        mockPage.props = {
            locale: "en-US",
        };

        const { locale, currency, formatCurrency } = useLocaleFormat();

        expect(locale.value).toBe("hu-HU");
        expect(currency.value).toBe("HUF");
        expect(formatCurrency(1234.5)).toContain("Ft");
    });

    it("returns dash for blank or invalid currency and quantity values", () => {
        mockPage.props = {
            preferences: {
                currency: "HUF",
                locale: "hu-HU",
            },
        };

        const { formatCurrency, formatQuantity } = useLocaleFormat();

        expect(formatCurrency(null)).toBe("-");
        expect(formatCurrency(undefined)).toBe("-");
        expect(formatCurrency("nincs")).toBe("-");
        expect(formatQuantity(null, "kg")).toBe("-");
        expect(formatQuantity(undefined, "kg")).toBe("-");
        expect(formatQuantity("", "kg")).toBe("-");
        expect(formatQuantity("nincs", "kg")).toBe("-");
    });

    it("formats quantities with an optional unit", () => {
        mockPage.props = {
            preferences: {
                currency: "HUF",
                locale: "hu-HU",
            },
        };

        const { formatQuantity } = useLocaleFormat();

        expect(formatQuantity(12.3456, "kg")).toBe("12,346 kg");
        expect(formatQuantity(12.3)).toBe("12,3");
    });

    it("falls back to Hungarian for isolated component tests without page props", () => {
        mockPage.props = undefined;

        const { number, formatCurrency } = useLocaleFormat();

        expect(number(1234.5)).toBe("1234,5");
        expect(formatCurrency(1234.5)).toContain("Ft");
    });
});
