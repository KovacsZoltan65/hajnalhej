import { reactive } from "vue";

export function createDayOptions(trans, days = [7, 14, 30, 90]) {
    return days.map((day) => ({
        label: trans("common.day_count", { count: day }),
        value: day,
    }));
}

export function pageOptions(trans, pages = [10, 20, 50]) {
    return pages.map((count) => ({
        label: trans("common.page_count", { count: count }),
        value: count,
    }));
}
