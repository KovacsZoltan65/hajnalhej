<script setup>
import { computed } from 'vue';
import PermissionGroupCard from './PermissionGroupCard.vue';

const props = defineProps({
    groups: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Array,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const groupEntries = computed(() =>
    Object.entries(props.groups).map(([name, items]) => ({ name, items }))
);

const togglePermission = (permissionName) => {
    const current = new Set(props.modelValue);
    if (current.has(permissionName)) {
        current.delete(permissionName);
    } else {
        current.add(permissionName);
    }

    emit('update:modelValue', Array.from(current).sort());
};

const toggleGroup = ({ groupName, selectAll }) => {
    const current = new Set(props.modelValue);
    const permissions = props.groups[groupName] ?? [];

    permissions.forEach((permission) => {
        if (selectAll) {
            current.add(permission.name);
            return;
        }

        current.delete(permission.name);
    });

    emit('update:modelValue', Array.from(current).sort());
};
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-2">
        <PermissionGroupCard
            v-for="group in groupEntries"
            :key="group.name"
            :group-name="group.name"
            :items="group.items"
            :selected-permissions="modelValue"
            :disabled="disabled"
            @toggle="togglePermission"
            @toggle-group="toggleGroup"
        />
    </div>
</template>
