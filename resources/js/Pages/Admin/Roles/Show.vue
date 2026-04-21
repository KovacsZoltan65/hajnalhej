<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';

import RoleBadge from '@/Components/Admin/Roles/RoleBadge.vue';
import RolePermissionMatrix from '@/Components/Admin/Roles/RolePermissionMatrix.vue';
import SectionTitle from '@/Components/SectionTitle.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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
    form.put(`/admin/roles/${props.role.id}/permissions`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Role - ${props.role.name}`" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <SectionTitle
                eyebrow="Admin / Roles"
                :title="`Szerepkor: ${props.role.name}`"
                description="A role-hoz tartozok jogosultsagok szinkronizalasa modulonkent."
            />

            <Link href="/admin/roles">
                <Button label="Vissza a listara" icon="pi pi-arrow-left" text />
            </Link>
        </div>

        <div class="grid gap-4 rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:grid-cols-3 sm:p-5">
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Szerepkor</p>
                <div class="mt-2">
                    <RoleBadge :role="props.role.name" :system="props.role.is_system_role" />
                </div>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Guard</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ props.role.guard_name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.16em] text-bakery-brown/70">Felhasznalok</p>
                <p class="mt-2 text-sm font-semibold text-bakery-dark">{{ props.role.users_count }}</p>
            </div>
        </div>

        <div class="space-y-4 rounded-2xl border border-bakery-brown/15 bg-white/80 p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-lg font-semibold text-bakery-dark">Jogosultsag matrix</p>
                    <p class="text-sm text-bakery-dark/70">
                        Osszesen {{ selectedPermissions.length }} jogosultsag kivalasztva.
                    </p>
                </div>

                <Button
                    v-if="can.assign_permissions"
                    label="Jogosultsagok mentese"
                    icon="pi pi-save"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="submit"
                />
            </div>

            <p v-if="form.errors.permissions" class="text-sm text-red-700">{{ form.errors.permissions }}</p>

            <RolePermissionMatrix
                v-model="selectedPermissions"
                :groups="permission_groups"
                :disabled="!can.assign_permissions || form.processing"
            />
        </div>
    </div>
</template>
