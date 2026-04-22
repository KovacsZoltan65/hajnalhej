<script setup>
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Dialog from 'primevue/dialog';
import RoleBadge from '@/Components/Admin/Roles/RoleBadge.vue';

const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    user: {
        type: Object,
        default: null,
    },
    roleOptions: {
        type: Array,
        required: true,
    },
    selectedRoles: {
        type: Array,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    canViewPermissions: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:visible', 'toggle-role', 'save']);

const close = () => emit('update:visible', false);
</script>

<template>
    <Dialog
        :visible="props.visible"
        modal
        header="Felhasznalo szerepkorok"
        :style="{ width: '38rem', maxWidth: '96vw' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <div v-if="props.user" class="space-y-5">
            <div>
                <p class="text-lg font-semibold text-bakery-dark">{{ props.user.name }}</p>
                <p class="text-sm text-bakery-dark/70">{{ props.user.email }}</p>
            </div>

            <div class="space-y-2">
                <p class="text-sm font-semibold text-bakery-dark">Szerepkörök</p>
                <ul class="space-y-2">
                    <li
                        v-for="role in props.roleOptions"
                        :key="role.name"
                        class="flex items-center justify-between rounded-lg border border-bakery-brown/10 px-3 py-2"
                    >
                        <div class="flex items-center gap-2">
                            <Checkbox
                                :input-id="`user-role-${role.name}`"
                                :model-value="props.selectedRoles.includes(role.name)"
                                binary
                                :disabled="props.loading"
                                @update:model-value="() => emit('toggle-role', role.name)"
                            />
                            <label :for="`user-role-${role.name}`" class="text-sm text-bakery-dark">{{ role.name }}</label>
                        </div>

                        <RoleBadge :role="role.name" :system="role.is_system_role" />
                    </li>
                </ul>
            </div>

            <div v-if="props.canViewPermissions" class="space-y-2">
                <p class="text-sm font-semibold text-bakery-dark">Effektiv jogosultsagok</p>
                <div class="max-h-32 overflow-y-auto rounded-lg border border-bakery-brown/10 bg-white/80 p-3">
                    <p v-if="props.user.permissions.length === 0" class="text-xs text-bakery-dark/60">Nincs jogosultsag.</p>
                    <ul v-else class="grid gap-1 sm:grid-cols-2">
                        <li v-for="permission in props.user.permissions" :key="permission" class="text-xs text-bakery-dark/80">
                            {{ permission }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Mégse" @click="close" />
                <Button
                    type="button"
                    label="Szerepkörök mentese"
                    :loading="props.loading"
                    :disabled="props.loading"
                    @click="emit('save')"
                />
            </div>
        </template>
    </Dialog>
</template>

