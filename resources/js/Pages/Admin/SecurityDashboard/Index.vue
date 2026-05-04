<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Select from 'primevue/select';

import AdminLayout from '@/Layouts/AdminLayout.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import SecuritySummaryCard from '@/Components/Admin/SecurityDashboard/SecuritySummaryCard.vue';
import PermissionRiskPanel from '@/Components/Admin/SecurityDashboard/PermissionRiskPanel.vue';
import OrphanPermissionsPanel from '@/Components/Admin/SecurityDashboard/OrphanPermissionsPanel.vue';
import PrivilegedUsersPanel from '@/Components/Admin/SecurityDashboard/PrivilegedUsersPanel.vue';
import RecentCriticalAuditEventsPanel from '@/Components/Admin/SecurityDashboard/RecentCriticalAuditEventsPanel.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    summary_cards: {
        type: Array,
        required: true,
    },
    permission_risk: {
        type: Object,
        required: true,
    },
    orphan_permissions: {
        type: Array,
        required: true,
    },
    privileged_users: {
        type: Array,
        required: true,
    },
    recent_critical_events: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    filter_options: {
        type: Object,
        required: true,
    },
    links: {
        type: Object,
        required: true,
    },
});

const loading = ref(false);
const form = reactive({
    window: props.filters.window,
    risk_level: props.filters.risk_level,
    log_name: props.filters.log_name,
    dangerous_only: props.filters.dangerous_only,
});

const applyFilters = () => {
    loading.value = true;
    router.get(route('admin.security-dashboard.index'), {
        window: form.window,
        risk_level: form.risk_level,
        log_name: form.log_name,
        dangerous_only: form.dangerous_only ? 1 : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            loading.value = false;
        },
    });
};
</script>

<template>
    <Head title="Biztonsági irányítópult" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Biztonság"
            title="Biztonsági irányítópult"
            description="Jogosultsági kockázatok, árva anomáliák, kiemelt felhasználók és kritikus audit események egy helyen."
        />

        <div class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.12em] text-bakery-brown/80">Idoablak</label>
                    <Select v-model="form.window" :options="filter_options.windows" option-label="label" option-value="value" class="w-full" />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.12em] text-bakery-brown/80">Risk szint</label>
                    <Select v-model="form.risk_level" :options="filter_options.risk_levels" option-label="label" option-value="value" class="w-full" />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.12em] text-bakery-brown/80">Log domain</label>
                    <Select v-model="form.log_name" :options="filter_options.log_names" option-label="label" option-value="value" class="w-full" />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.12em] text-bakery-brown/80">Csak veszélyes</label>
                    <div class="flex h-10 items-center gap-2 rounded-lg border border-bakery-brown/15 px-3">
                        <Checkbox v-model="form.dangerous_only" binary />
                        <span class="text-sm text-bakery-dark">Csak veszelyes elemek</span>
                    </div>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                <Button icon="pi pi-filter" label="Szűrők alkalmazása" :loading="loading" @click="applyFilters" />
                <Link :href="links.permissions" class="rounded-full border border-bakery-brown/20 px-4 py-2 text-sm font-medium text-bakery-brown hover:bg-bakery-brown/10">Jogosultságok</Link>
                <Link :href="links.roles" class="rounded-full border border-bakery-brown/20 px-4 py-2 text-sm font-medium text-bakery-brown hover:bg-bakery-brown/10">Szerepkörök</Link>
                <Link :href="links.user_roles" class="rounded-full border border-bakery-brown/20 px-4 py-2 text-sm font-medium text-bakery-brown hover:bg-bakery-brown/10">Felhasználói szerepkörök</Link>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <SecuritySummaryCard
                v-for="card in summary_cards"
                :key="card.title"
                :title="card.title"
                :value="card.value"
                :tone="card.tone"
            />
        </div>

        <PermissionRiskPanel :stats="permission_risk" />
        <OrphanPermissionsPanel :rows="orphan_permissions" :links="links" />
        <PrivilegedUsersPanel :users="privileged_users" :links="links" />
        <RecentCriticalAuditEventsPanel :events="recent_critical_events" />
    </div>
</template>

