<script setup>
import { Link } from '@inertiajs/vue3';
import SeverityBadge from './SeverityBadge.vue';

defineProps({
    events: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <section class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
        <header class="mb-4">
            <h3 class="font-heading text-xl text-bakery-dark">Legutóbbi kritikus audit események</h3>
            <p class="text-sm text-bakery-dark/70">Authorization, orders es user-activity domainek friss kritikus esemenyei.</p>
        </header>

        <div class="space-y-3">
            <article
                v-for="event in events"
                :key="event.id"
                class="rounded-xl border border-bakery-brown/15 bg-[#fdf8f1] p-3"
            >
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-bakery-dark">{{ event.label }}</p>
                        <p class="text-xs text-bakery-dark/70">{{ event.log_name }} · {{ event.event_key }}</p>
                    </div>
                    <SeverityBadge :severity="event.severity" />
                </div>
                <p class="mt-2 text-sm text-bakery-dark/85">{{ event.summary }}</p>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-bakery-dark/70">
                    <span>{{ event.timestamp }}</span>
                    <span>Végrehajtó: {{ event.causer }}</span>
                    <span>Érintett elem: {{ event.subject }}</span>
                    <Link
                        :href="`/admin/security-dashboard/events/${event.id}`"
                        class="rounded-full border border-bakery-brown/20 px-2.5 py-1 font-semibold text-bakery-brown hover:bg-bakery-brown/10"
                    >
                        Megnyitás audit részletben
                    </Link>
                </div>
            </article>
        </div>
    </section>
</template>

