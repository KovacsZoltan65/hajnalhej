import { Ziggy } from "@/ziggy";

export function safeRoute(name, params = undefined, absolute = false) {
    const routeDefinition = Ziggy.routes[name];

    if (!routeDefinition) {
        throw new Error(`[safeRoute] Unknown route name: "${name}"`);
    }

    const requiredParams = routeDefinition.parameters ?? [];

    if (requiredParams.length === 0 && params !== undefined) {
        throw new Error(
            `[safeRoute] Route "${name}" does not accept parameters, but params were provided.`,
        );
    }

    if (requiredParams.length > 0) {
        if (params === undefined || params === null) {
            throw new Error(
                `[safeRoute] Route "${name}" requires params: ${requiredParams.join(", ")}`,
            );
        }

        const normalizedParams =
            typeof params === "object" && !Array.isArray(params)
                ? params
                : { [requiredParams[0]]: params };

        const missingParams = requiredParams.filter(
            (param) =>
                normalizedParams[param] === undefined ||
                normalizedParams[param] === null ||
                normalizedParams[param] === "",
        );

        if (missingParams.length > 0) {
            throw new Error(
                `[safeRoute] Route "${name}" is missing params: ${missingParams.join(", ")}`,
            );
        }

        const unknownParams = Object.keys(normalizedParams).filter(
            (param) => !requiredParams.includes(param),
        );

        if (unknownParams.length > 0) {
            throw new Error(
                `[safeRoute] Route "${name}" received unknown params: ${unknownParams.join(", ")}`,
            );
        }

        return route(name, normalizedParams, absolute);
    }

    return route(name, undefined, absolute);
}
