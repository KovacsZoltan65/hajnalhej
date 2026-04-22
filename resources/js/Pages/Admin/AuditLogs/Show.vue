<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';

import AuditEventBadge from '@/Components/Admin/AuditLogs/AuditEventBadge.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    log: {
        type: Object,
        required: true,
    },
    eventLabels: {
        type: Object,
        required: true,
    },
});

const formatJson = (value) => JSON.stringify(value ?? {}, null, 2);
</script>

<template>
    <Head :title="`Audit bejegyzés #${log.id}`" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <SectionTitle
                eyebrow="Admin / Auditnaplók"
                :title="`Audit bejegyzes #${log.id}`"
                description="Részletes előtte/utána, környezet és eltérés adatok jogosultsági, felhasználói és rendelési eseményekhez."
            />

            <Link href="/admin/audit-logs">
                <Button label="Vissza a listara" icon="pi pi-arrow-left" text />
            </Link>
        </div>

        <div class="grid gap-4 rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:grid-cols-2 sm:p-5">
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Esemeny</p>
                <div class="mt-2">
                    <AuditEventBadge
                        :event-key="log.event"
                        :label="eventLabels[log.event] ?? log.event"
                    />
                </div>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Idopont</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ log.created_at }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Domain</p>
                <p class="mt-2 text-sm font-semibold uppercase tracking-[0.1em] text-bakery-dark">{{ log.log_name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Végrehajtó</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ log.causer?.name ?? '-' }}</p>
                <p class="text-xs text-bakery-dark/70">{{ log.causer?.email ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Érintett elem</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ log.subject?.label ?? '-' }}</p>
                <p class="text-xs text-bakery-dark/70">{{ log.subject?.type ?? '-' }}</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-bakery-brown/80">Előtte</h2>
                <pre class="mt-3 overflow-x-auto rounded-lg bg-[#fff9f1] p-3 text-xs text-bakery-dark">{{ formatJson(log.properties.before) }}</pre>
            </article>

            <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-bakery-brown/80">Utána</h2>
                <pre class="mt-3 overflow-x-auto rounded-lg bg-[#fff9f1] p-3 text-xs text-bakery-dark">{{ formatJson(log.properties.after) }}</pre>
            </article>

            <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-bakery-brown/80">Környezet</h2>
                <pre class="mt-3 overflow-x-auto rounded-lg bg-[#fff9f1] p-3 text-xs text-bakery-dark">{{ formatJson(log.properties.context) }}</pre>
            </article>

            <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-bakery-brown/80">Eltérés / Meta</h2>
                <pre class="mt-3 overflow-x-auto rounded-lg bg-[#fff9f1] p-3 text-xs text-bakery-dark">{{ formatJson({
                    added_permissions: log.properties.added_permissions,
                    removed_permissions: log.properties.removed_permissions,
                    added_roles: log.properties.added_roles,
                    removed_roles: log.properties.removed_roles,
                    status_transition: log.properties.status_transition,
                    pickup_transition: log.properties.pickup_transition,
                    order: log.properties.order,
                    customer_snapshot: log.properties.customer_snapshot,
                    totals_snapshot: log.properties.totals_snapshot,
                    items_summary: log.properties.items_summary,
                    pickup_snapshot: log.properties.pickup_snapshot,
                    note_summary: log.properties.note_summary,
                    blocked_reason: log.properties.blocked_reason,
                    actor_snapshot: log.properties.actor_snapshot,
                    role: log.properties.role,
                    target_user: log.properties.target_user,
                }) }}</pre>
            </article>
        </div>
    </div>
</template>
