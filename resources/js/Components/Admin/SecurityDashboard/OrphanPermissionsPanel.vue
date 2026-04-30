<script setup>
import { Link } from "@inertiajs/vue3";
import RiskBadge from "./RiskBadge.vue";

defineProps({
    rows: {
        type: Array,
        required: true,
    },
    links: {
        type: Object,
        required: true,
    },
});

const moduleLabel = (moduleName) => moduleName;
</script>

<template>
    <section class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
        <header class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="font-heading text-xl text-bakery-dark">Árva jogosultságok</h3>
                <p class="text-sm text-bakery-dark/70">
                    Registry drift, használat nélküli és meta-anomália permissionok.
                </p>
            </div>
            <Link
                :href="links.permissions"
                class="rounded-full border border-bakery-brown/20 px-3 py-1.5 text-xs font-semibold text-bakery-brown hover:bg-bakery-brown/10"
            >
                Jogosultságkezelés
            </Link>
        </header>

        <div class="overflow-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                    >
                        <th class="px-2 py-2">Jogosultság</th>
                        <th class="px-2 py-2">Allapot</th>
                        <th class="px-2 py-2">Kockázat</th>
                        <th class="px-2 py-2">Használat</th>
                        <th class="px-2 py-2">Javaslat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in rows"
                        :key="row.name"
                        class="border-b border-bakery-brown/10"
                    >
                        <td class="px-2 py-2">
                            <p class="font-medium text-bakery-dark">{{ row.name }}</p>
                            <p class="text-xs text-bakery-dark/60">
                                {{ moduleLabel(row.module) }}
                            </p>
                        </td>
                        <td class="px-2 py-2 text-bakery-dark">{{ row.issue }}</td>
                        <td class="px-2 py-2"><RiskBadge :level="row.risk_level" /></td>
                        <td class="px-2 py-2 text-bakery-dark">
                            role: {{ row.roles_count }} / user: {{ row.users_count }}
                        </td>
                        <td class="px-2 py-2 text-bakery-dark/80">
                            {{ row.suggested_action }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
