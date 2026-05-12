<script setup>
import { Head, Link } from "@inertiajs/vue3";

import AdminLayout from "@/Layouts/AdminLayout.vue";
import SectionTitle from "@/Components/SectionTitle.vue";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    event: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="$t('security_dashboard.event.meta_title', { id: event.id })" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('security_dashboard.event.eyebrow')"
            :title="$t('security_dashboard.event.title', { id: event.id })"
            :description="$t('security_dashboard.event.description')"
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-5">
            <div class="grid gap-3 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                        {{ $t("security_dashboard.event.log") }}
                    </p>
                    <p class="text-sm font-semibold text-bakery-dark">{{ event.log_name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                        {{ $t("security_dashboard.event.event_key") }}
                    </p>
                    <p class="text-sm font-semibold text-bakery-dark">{{ event.event_key || "-" }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                        {{ $t("audit_logs.columns.created_at") }}
                    </p>
                    <p class="text-sm text-bakery-dark">{{ event.created_at }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                        {{ $t("common.description") }}
                    </p>
                    <p class="text-sm text-bakery-dark">{{ event.description }}</p>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-bakery-brown/15 bg-[#fdf8f1] p-4">
                <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                    {{ $t("security_dashboard.event.properties_json") }}
                </p>
                <pre class="mt-2 overflow-auto whitespace-pre-wrap text-xs text-bakery-dark">{{
                    JSON.stringify(event.properties, null, 2)
                }}</pre>
            </div>

            <div class="mt-4">
                <Link
                    :href="route('admin.security-dashboard.index')"
                    class="rounded-full border border-bakery-brown/20 px-4 py-2 text-sm font-semibold text-bakery-brown hover:bg-bakery-brown/10"
                >
                    {{ $t("security_dashboard.event.back_to_dashboard") }}
                </Link>
            </div>
        </div>
    </div>
</template>
