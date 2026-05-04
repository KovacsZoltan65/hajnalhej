<script setup>
import { ref } from "vue";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Dialog from "primevue/dialog";
import CreateWeeklyMenuItemModal from "./CreateWeeklyMenuItemModal.vue";
import EditWeeklyMenuItemModal from "./EditWeeklyMenuItemModal.vue";
import WeeklyMenuStatusBadge from "./WeeklyMenuStatusBadge.vue";

const props = defineProps({
    visible: { type: Boolean, required: true },
    menu: { type: Object, default: null },
    products: { type: Array, required: true },
});

const emit = defineEmits(["update:visible", "save-item", "delete-item"]);

const createModalVisible = ref(false);
const editModalVisible = ref(false);
const editingItem = ref(null);

const openCreate = () => {
    editingItem.value = null;
    editModalVisible.value = false;
    createModalVisible.value = true;
};

const openEdit = (item) => {
    createModalVisible.value = false;
    editingItem.value = item;
    editModalVisible.value = true;
};

const saveItem = (payload) => {
    emit("save-item", payload);
};

const remove = (item) => emit("delete-item", item);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="menu ? `Tételkezelés: ${menu.title}` : 'Tételkezelés'"
        :style="{ width: '70rem', maxWidth: '98vw' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <div class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-bakery-dark/70">
                        A heti menühöz tartozó tételek listája.
                    </p>
                    <p v-if="menu" class="text-xs text-bakery-dark/50">
                        {{ menu.week_start }} - {{ menu.week_end }}
                    </p>
                </div>

                <Button icon="pi pi-plus" label="Új tétel" @click="openCreate" />
            </div>

            <div class="overflow-x-auto">
                <DataTable :value="menu?.items ?? []" data-key="id" scrollable>
                    <template #empty>
                        <div
                            class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-4 text-center text-sm text-bakery-dark/70"
                        >
                            <p>Nincs tétel a heti menühöz.</p>
                            <div class="mt-3 flex justify-center">
                                <Button
                                    label="Új tétel felvétele"
                                    size="small"
                                    @click="openCreate"
                                />
                            </div>
                        </div>
                    </template>

                    <Column field="product_name" header="Termék" />
                    <Column field="override_name" header="Felülírt név" />
                    <Column field="override_price" header="Felülírt ár">
                        <template #body="{ data }">
                            <span v-if="data.override_price !== null"
                                >{{
                                    new Intl.NumberFormat("hu-HU").format(
                                        data.override_price
                                    )
                                }}
                                Ft</span
                            >
                            <span v-else class="text-bakery-dark/60">-</span>
                        </template>
                    </Column>
                    <Column field="sort_order" header="Sorrend" />
                    <Column field="is_active" header="Státusz">
                        <template #body="{ data }">
                            <WeeklyMenuStatusBadge
                                :status="data.is_active ? 'published' : 'draft'"
                            />
                        </template>
                    </Column>
                    <Column header="Műveletek">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Button
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    class="h-11! w-11!"
                                    aria-label="Tétel szerkesztése"
                                    @click="openEdit(data)"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    class="h-11! w-11!"
                                    aria-label="Tétel törlése"
                                    @click="remove(data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <CreateWeeklyMenuItemModal
            v-model:visible="createModalVisible"
            :products="products"
            @save="saveItem"
        />
        <EditWeeklyMenuItemModal
            v-model:visible="editModalVisible"
            :item="editingItem"
            :products="products"
            @save="saveItem"
        />
    </Dialog>
</template>
