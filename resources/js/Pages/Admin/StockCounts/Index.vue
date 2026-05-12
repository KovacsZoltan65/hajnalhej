<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import BaseDatePicker from "@/Components/BaseDatePicker.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import { trans } from "laravel-vue-i18n";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    stock_counts: { type: Object, required: true },
    statuses: { type: Array, required: true },
    ingredient_options: { type: Array, required: true },
    filters: { type: Object, required: true },
});

const status = ref(props.filters.status ?? "");
const statusOptions = computed(() => [
    { label: trans("admin_stock_count.filters.all_statuses"), value: "" },
    ...props.statuses.map((status) => ({ label: status, value: status })),
]);
const load = () => {
    router.get(
        route("admin.stock-counts.index"),
        { status: status.value || undefined },
        { preserveState: true, replace: true, preserveScroll: true }
    );
};

const form = useForm({
    count_date: new Date().toISOString().slice(0, 10),
    notes: "",
    items: props.ingredient_options.slice(0, 5).map((i) => ({
        ingredient_id: i.id,
        expected_quantity: i.current_stock,
        counted_quantity: i.current_stock,
    })),
});
</script>

<template>
    <Head :title="$t('admin_stock_count.meta_title')" />
    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin_stock_count.eyebrow')"
            :title="$t('admin_stock_count.title')"
            :description="$t('admin_stock_count.description')"
        />

        <section class="ui-card p-4 sm:p-5 space-y-4">
            <div class="grid gap-3 md:grid-cols-3">
                <Select v-model="status" :options="statusOptions" option-label="label" option-value="value" />
                <div />
                <Button :label="$t('admin_stock_count.actions.filter')" class="min-h-11!" @click="load" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-widest text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">{{ $t("common.date") }}</th>
                            <th class="px-2 py-2">{{ $t("common.status") }}</th>
                            <th class="px-2 py-2 text-right">{{ $t("admin_stock_count.columns.items") }}</th>
                            <th class="px-2 py-2">{{ $t("admin_stock_count.columns.created_by") }}</th>
                            <th class="px-2 py-2 text-right">{{ $t("admin_stock_count.columns.action") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in stock_counts.data" :key="row.id" class="border-b border-bakery-brown/10">
                            <td class="px-2 py-2">{{ row.count_date }}</td>
                            <td class="px-2 py-2">{{ row.status }}</td>
                            <td class="px-2 py-2 text-right">{{ row.items_count }}</td>
                            <td class="px-2 py-2">{{ row.created_by || "-" }}</td>
                            <td class="px-2 py-2 text-right">
                                <Link :href="route('admin.stock-counts.show', row.id)" class="underline">{{
                                    $t("admin_stock_count.actions.details")
                                }}</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-card p-4 sm:p-5 space-y-3">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">
                {{ $t("admin_stock_count.actions.create") }}
            </h2>
            <BaseDatePicker v-model="form.count_date" class="w-full" />
            <InputText v-model="form.notes" :placeholder="$t('common.notes')" />
            <div class="space-y-2">
                <div v-for="(item, idx) in form.items" :key="idx" class="grid gap-2 md:grid-cols-3">
                    <Select
                        v-model="item.ingredient_id"
                        :options="ingredient_options.map((i) => ({ label: i.name, value: i.id }))"
                        option-label="label"
                        option-value="value"
                    />
                    <InputText
                        v-model="item.expected_quantity"
                        type="number"
                        step="0.001"
                        :placeholder="$t('admin_stock_count.fields.expected_quantity')"
                    />
                    <InputText
                        v-model="item.counted_quantity"
                        type="number"
                        step="0.001"
                        :placeholder="$t('admin_stock_count.fields.counted_quantity')"
                    />
                </div>
            </div>
            <Button
                :label="$t('admin_stock_count.actions.save')"
                class="min-h-11!"
                :disabled="form.processing"
                @click="form.post(route('admin.stock-counts.store'), { preserveScroll: true })"
            />
        </section>
    </div>
</template>
