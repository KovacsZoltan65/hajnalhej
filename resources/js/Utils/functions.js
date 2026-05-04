export function createDayOptions(trans, days = [7, 14, 30, 90]) {
    return days.map((day) => ({
        label: trans("common.day_count", { count: day }),
        value: day,
    }));
}
