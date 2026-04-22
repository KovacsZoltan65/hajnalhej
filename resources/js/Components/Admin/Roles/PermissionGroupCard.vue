<script setup>
import Checkbox from 'primevue/checkbox';
import PermissionBadge from './PermissionBadge.vue';

const props = defineProps({
    groupName: {
        type: String,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    selectedPermissions: {
        type: Array,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle', 'toggle-group']);

const selectedSet = () => new Set(props.selectedPermissions);
const isAllSelected = () => props.items.every((item) => selectedSet().has(item.name));
const groupLabels = {
    Admin: 'Admin',
    Orders: 'Rendelések',
    Products: 'Termékek',
    Categories: 'Kategóriák',
    Ingredients: 'Alapanyagok',
    'Weekly Menu': 'Heti menü',
    'Production Plans': 'Gyártási tervek',
    Account: 'Fiók',
    'Roles & Permissions': 'Szerepkörök és jogosultságok',
    Security: 'Biztonság',
};
const groupLabel = (name) => groupLabels[name] ?? name;
</script>

<template>
    <div class="rounded-xl border border-bakery-brown/15 bg-white/70 p-4">
        <div class="mb-3 flex items-center justify-between gap-2">
            <div>
                <p class="text-sm font-semibold text-bakery-dark">{{ groupLabel(groupName) }}</p>
                <p class="text-xs text-bakery-dark/70">{{ items.length }} jogosultsag</p>
            </div>

            <button
                type="button"
                class="rounded-lg border border-bakery-brown/20 px-2.5 py-1 text-xs font-medium text-bakery-brown hover:bg-bakery-brown/10 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="disabled"
                @click="emit('toggle-group', { groupName, selectAll: !isAllSelected() })"
            >
                {{ isAllSelected() ? 'Mindet torli' : 'Mindet kijeloli' }}
            </button>
        </div>

        <ul class="space-y-2">
            <li
                v-for="permission in items"
                :key="permission.name"
                class="rounded-lg border border-bakery-brown/10 bg-white/80 px-3 py-2"
            >
                <div class="flex items-start gap-2">
                    <Checkbox
                        :input-id="`permission-${permission.name}`"
                        :model-value="selectedSet().has(permission.name)"
                        binary
                        :disabled="disabled"
                        @update:model-value="() => emit('toggle', permission.name)"
                    />
                    <label :for="`permission-${permission.name}`" class="flex-1 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-bakery-dark">{{ permission.label }}</span>
                            <PermissionBadge :permission="permission.name" :dangerous="permission.dangerous" />
                        </div>
                        <p class="text-xs text-bakery-dark/70">{{ permission.description }}</p>
                    </label>
                </div>
            </li>
        </ul>
    </div>
</template>
