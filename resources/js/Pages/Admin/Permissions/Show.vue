<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Button from "primevue/button";

import PermissionBadge from "@/Components/Admin/Permissions/PermissionBadge.vue";
import PermissionDangerBadge from "@/Components/Admin/Permissions/PermissionDangerBadge.vue";
import PermissionRegistryStateBadge from "@/Components/Admin/Permissions/PermissionRegistryStateBadge.vue";
import PermissionUsageCard from "@/Components/Admin/Permissions/PermissionUsageCard.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    permission: {
        type: Object,
        required: true,
    },
});

const moduleLabel = (moduleName) => moduleName;
</script>

<template>
    <Head :title="$t('admin_permissions.show_meta_title', { name: props.permission.name })" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <SectionTitle
                :eyebrow="$t('admin_permissions.show_eyebrow')"
                :title="$t('admin_permissions.show_title', { name: props.permission.name })"
                :description="$t('admin_permissions.show_description')"
            />

            <Link :href="route('admin.permissions.index')">
                <Button :label="$t('admin_permissions.actions.back_to_list')" icon="pi pi-arrow-left" text />
            </Link>
        </div>

        <div class="grid gap-4 rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:grid-cols-2 sm:p-5">
            <div class="space-y-2">
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">{{ $t('admin_permissions.fields.permission') }}</p>
                <PermissionBadge :name="props.permission.name" />
            </div>
            <div class="space-y-2">
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">{{ $t('admin_permissions.fields.registry_state') }}</p>
                <PermissionRegistryStateBadge :state="props.permission.registry_state" />
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">{{ $t('admin_permissions.fields.label') }}</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ props.permission.label }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">{{ $t('admin_permissions.fields.module') }}</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ moduleLabel(props.permission.module) }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">{{ $t('admin_permissions.fields.description') }}</p>
                <p class="mt-2 text-sm text-bakery-dark/90">{{ props.permission.description }}</p>
            </div>
            <div class="space-y-2">
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">{{ $t('admin_permissions.fields.dangerous') }}</p>
                <PermissionDangerBadge :dangerous="props.permission.dangerous" />
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">{{ $t('admin_permissions.fields.guard') }}</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ props.permission.guard_name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">{{ $t('admin_permissions.fields.audit_sensitive') }}</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">
                    {{ props.permission.audit_sensitive ? $t('admin_permissions.values.yes') : $t('admin_permissions.values.no') }}
                </p>
            </div>
        </div>

        <PermissionUsageCard
            :roles-count="props.permission.roles_count"
            :users-count="props.permission.users_count"
            :role-names="props.permission.role_names"
        />
    </div>
</template>

