<script setup>
import RiskBadge from "./RiskBadge.vue";

defineProps({
    stats: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <section class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
        <header class="mb-4">
            <h3 class="font-heading text-xl text-bakery-dark">Jogosultsági kockázat</h3>
            <p class="text-sm text-bakery-dark/70">
                Jogosultsági kockázatok összesítve, drift és terítési minták alapján.
            </p>
        </header>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-xl border border-bakery-brown/15 p-3">
                <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                    Összes permission
                </p>
                <p class="mt-2 text-2xl font-semibold text-bakery-dark">
                    {{ stats.total_permissions }}
                </p>
            </div>
            <div class="rounded-xl border border-bakery-brown/15 p-3">
                <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                    Veszélyes jogosultságok
                </p>
                <p class="mt-2 text-2xl font-semibold text-bakery-dark">
                    {{ stats.dangerous_permissions }}
                </p>
            </div>
            <div class="rounded-xl border border-bakery-brown/15 p-3">
                <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                    Auditérzékeny jogosultságok
                </p>
                <p class="mt-2 text-2xl font-semibold text-bakery-dark">
                    {{ stats.audit_sensitive_permissions }}
                </p>
            </div>
            <div class="rounded-xl border border-bakery-brown/15 p-3">
                <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                    Veszélyes szerepkör terítés
                </p>
                <p class="mt-2 text-2xl font-semibold text-bakery-dark">
                    {{ stats.roles_with_dangerous_permissions }}
                </p>
            </div>
            <div class="rounded-xl border border-bakery-brown/15 p-3">
                <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                    Veszélyes felhasználói terítés
                </p>
                <p class="mt-2 text-2xl font-semibold text-bakery-dark">
                    {{ stats.users_with_dangerous_permissions }}
                </p>
            </div>
            <div class="rounded-xl border border-bakery-brown/15 p-3">
                <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                    Registry eltérés
                </p>
                <p class="mt-2 text-2xl font-semibold text-bakery-dark">
                    {{ stats.db_without_registry + stats.registry_missing_in_db }}
                </p>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-bakery-brown/15 p-3">
            <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                Kockázati eloszlás
            </p>
            <div class="mt-2 flex flex-wrap gap-2">
                <div
                    class="inline-flex items-center gap-2 rounded-lg border border-bakery-brown/10 px-2 py-1"
                    v-for="(count, level) in stats.risk_distribution"
                    :key="level"
                >
                    <RiskBadge :level="level" />
                    <span class="text-sm font-medium text-bakery-dark">{{ count }}</span>
                </div>
            </div>
        </div>
    </section>
</template>
