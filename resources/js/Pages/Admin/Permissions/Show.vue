<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';

import PermissionBadge from '@/Components/Admin/Permissions/PermissionBadge.vue';
import PermissionDangerBadge from '@/Components/Admin/Permissions/PermissionDangerBadge.vue';
import PermissionRegistryStateBadge from '@/Components/Admin/Permissions/PermissionRegistryStateBadge.vue';
import PermissionUsageCard from '@/Components/Admin/Permissions/PermissionUsageCard.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    permission: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="`Permission - ${props.permission.name}`" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <SectionTitle
                eyebrow="Admin / Permissions"
                :title="`Permission: ${props.permission.name}`"
                description="Registry metadata, usage es drift allapot attekintes."
            />

            <Link href="/admin/permissions">
                <Button label="Vissza a listara" icon="pi pi-arrow-left" text />
            </Link>
        </div>

        <div class="grid gap-4 rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:grid-cols-2 sm:p-5">
            <div class="space-y-2">
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Permission</p>
                <PermissionBadge :name="props.permission.name" />
            </div>
            <div class="space-y-2">
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Registry state</p>
                <PermissionRegistryStateBadge :state="props.permission.registry_state" />
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Label</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ props.permission.label }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Modul</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ props.permission.module }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Leiras</p>
                <p class="mt-2 text-sm text-bakery-dark/90">{{ props.permission.description }}</p>
            </div>
            <div class="space-y-2">
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Dangerous</p>
                <PermissionDangerBadge :dangerous="props.permission.dangerous" />
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Guard</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ props.permission.guard_name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Audit sensitive</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ props.permission.audit_sensitive ? 'Igen' : 'Nem' }}</p>
            </div>
        </div>

        <PermissionUsageCard
            :roles-count="props.permission.roles_count"
            :users-count="props.permission.users_count"
            :role-names="props.permission.role_names"
        />
    </div>
</template>
