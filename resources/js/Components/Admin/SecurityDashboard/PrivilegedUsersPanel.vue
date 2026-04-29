<script setup>
import { Link } from "@inertiajs/vue3";
import RiskBadge from "./RiskBadge.vue";

defineProps({
    users: {
        type: Array,
        required: true,
    },
    links: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <section class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
        <header class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h3 class="font-heading text-xl text-bakery-dark">
                    Kiemelt jogosultságú felhasználók
                </h3>
                <p class="text-sm text-bakery-dark/70">
                    Magas jogosultságú felhasználók és veszélyes öröklési minták.
                </p>
            </div>
            <Link
                :href="links.user_roles"
                class="rounded-full border border-bakery-brown/20 px-3 py-1.5 text-xs font-semibold text-bakery-brown hover:bg-bakery-brown/10"
            >
                Szerepkör-hozzárendelés
            </Link>
        </header>

        <div class="overflow-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                    >
                        <th class="px-2 py-2">Felhasználó</th>
                        <th class="px-2 py-2">Szerepkörök</th>
                        <th class="px-2 py-2">Jogosultságok</th>
                        <th class="px-2 py-2">Veszélyes</th>
                        <th class="px-2 py-2">Kockázat</th>
                        <th class="px-2 py-2">Utolsó aktivitás</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="user in users"
                        :key="user.id"
                        class="border-b border-bakery-brown/10"
                    >
                        <td class="px-2 py-2">
                            <p class="font-medium text-bakery-dark">{{ user.name }}</p>
                            <p class="text-xs text-bakery-dark/60">{{ user.email }}</p>
                        </td>
                        <td class="px-2 py-2 text-bakery-dark">
                            {{ user.roles.join(", ") || "-" }}
                        </td>
                        <td class="px-2 py-2 text-bakery-dark">
                            {{ user.effective_permissions_count }}
                        </td>
                        <td class="px-2 py-2 text-bakery-dark">
                            {{ user.dangerous_permissions_count }}
                        </td>
                        <td class="px-2 py-2"><RiskBadge :level="user.risk_level" /></td>
                        <td class="px-2 py-2 text-bakery-dark/80">
                            {{ user.last_relevant_activity_at ?? "-" }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
