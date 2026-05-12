<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Button from "primevue/button";

import RoleBadge from "@/Components/Admin/Roles/RoleBadge.vue";
import RolePermissionMatrix from "@/Components/Admin/Roles/RolePermissionMatrix.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    role: {
        type: Object,
        required: true,
    },
    permission_groups: {
        type: Object,
        required: true,
    },
    can: {
        type: Object,
        required: true,
    },
});

const selectedPermissions = ref([...(props.role.permissions ?? [])]);
const form = useForm({
    permissions: [...selectedPermissions.value],
});

const submit = () => {
    form.permissions = [...selectedPermissions.value];
    form.put(route("admin.roles.permissions.sync", props.role.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('admin_roles.show.meta_title', { name: props.role.name })" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <SectionTitle
                :eyebrow="$t('admin_roles.show.eyebrow')"
                :title="$t('admin_roles.show.title', { name: props.role.name })"
                :description="$t('admin_roles.show.description')"
            />

            <Link :href="route('admin.roles.index')">
                <Button :label="$t('admin_roles.show.back_to_list')" icon="pi pi-arrow-left" text />
            </Link>
        </div>

        <div class="grid gap-4 rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:grid-cols-3 sm:p-5">
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">
                    {{ $t("admin_roles.role") }}
                </p>
                <div class="mt-2">
                    <RoleBadge :role="props.role.name" :system="props.role.is_system_role" />
                </div>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">
                    {{ $t("admin_roles.show.guard") }}
                </p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">
                    {{ props.role.guard_name }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">
                    {{ $t("admin_roles.show.users") }}
                </p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">
                    {{ props.role.users_count }}
                </p>
            </div>
        </div>

        <div class="space-y-4 rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-lg font-semibold text-bakery-dark">
                        {{ $t("admin_roles.show.permission_matrix") }}
                    </p>
                    <p class="text-sm text-bakery-dark/70">
                        {{
                            $t("admin_roles.show.selected_permissions_count", {
                                count: selectedPermissions.length,
                            })
                        }}
                    </p>
                </div>

                <Button
                    v-if="can.assign_permissions"
                    :label="$t('admin_roles.show.save_permissions')"
                    icon="pi pi-save"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="submit"
                />
            </div>

            <p v-if="form.errors.permissions" class="text-sm text-red-700">
                {{ form.errors.permissions }}
            </p>

            <RolePermissionMatrix
                v-model="selectedPermissions"
                :groups="permission_groups"
                :disabled="!can.assign_permissions || form.processing"
            />
        </div>
    </div>
</template>
