export function createDayOptions(trans, days = [7, 14, 30, 90]) {
    return days.map((day) => ({
        label: trans("common.day_count", { count: day }),
        value: day,
    }));
}

function translatedOrFallback(trans, key, replacements, fallback) {
    if (typeof trans !== "function") {
        return fallback;
    }

    const translated = trans(key, replacements);

    return translated && translated !== key ? translated : fallback;
}

export function pageOptions(trans, pages = [10, 20, 50]) {
    return pages.map((count) => ({
        label: translatedOrFallback(trans, "common.page_count", { count }, `${count} / oldal`),
        value: count,
    }));
}

export function activeOptions(trans) {
    return [
        { label: trans("common.all"), value: "" },
        { label: trans("common.active"), value: "1" },
        { label: trans("common.inactive"), value: "2" },
    ];
}
