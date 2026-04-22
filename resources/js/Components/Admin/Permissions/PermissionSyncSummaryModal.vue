<script setup>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    summary: {
        type: Object,
        default: () => null,
    },
});

const emit = defineEmits(['update:visible']);

const close = () => emit('update:visible', false);
</script>

<template>
    <Dialog
        :visible="props.visible"
        modal
        header="Jogosultság szinkron összegzés"
        class="w-[95vw] max-w-2xl"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <div v-if="props.summary" class="space-y-4">
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg bg-emerald-50 p-3">
                    <p class="text-xs uppercase tracking-[0.12em] text-emerald-700">Létrehozva</p>
                    <p class="text-xl font-semibold text-emerald-800">{{ props.summary.created_count ?? 0 }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs uppercase tracking-[0.12em] text-slate-700">Már létezett</p>
                    <p class="text-xl font-semibold text-slate-800">{{ props.summary.existing_count ?? 0 }}</p>
                </div>
                <div class="rounded-lg bg-amber-50 p-3">
                    <p class="text-xs uppercase tracking-[0.12em] text-amber-700">Árvák</p>
                    <p class="text-xl font-semibold text-amber-800">{{ props.summary.orphan_count ?? 0 }}</p>
                </div>
            </div>

            <div class="space-y-2 rounded-lg border border-bakery-brown/15 bg-[#fff9f1] p-3 text-sm text-bakery-dark">
                <p><span class="font-medium">Létrehozott jogosultságok:</span> {{ (props.summary.created_permissions ?? []).join(', ') || 'Nincs' }}</p>
                <p><span class="font-medium">Árva jogosultságok:</span> {{ (props.summary.orphan_permissions ?? []).join(', ') || 'Nincs' }}</p>
            </div>
        </div>

        <template #footer>
            <Button label="Rendben" @click="close" />
        </template>
    </Dialog>
</template>
