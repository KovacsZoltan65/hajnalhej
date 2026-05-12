<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import Button from "primevue/button";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    stock_count: { type: Object, required: true },
});

const closeCount = () =>
    router.post(route("admin.stock-counts.close", props.stock_count.id), {}, { preserveScroll: true });
</script>

<template>
    <Head :title="$t('admin_stock_count.show.meta_title', { id: stock_count.id })" />

    <section class="space-y-6">
        <div class="ui-card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <h1 class="font-heading text-2xl">
                    {{ $t("admin_stock_count.show.title", { id: stock_count.id }) }}
                </h1>
                <Link :href="route('admin.stock-counts.index')" class="text-sm underline">{{
                    $t("common.back_to_list")
                }}</Link>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-3 text-sm">
                <p>
                    <strong>{{ $t("common.date") }}:</strong> {{ stock_count.count_date }}
                </p>
                <p>
                    <strong>{{ $t("common.status") }}:</strong> {{ stock_count.status }}
                </p>
                <p>
                    <strong>{{ $t("admin_stock_count.show.closed_at") }}:</strong>
                    {{ stock_count.closed_at || "-" }}
                </p>
            </div>
            <p v-if="stock_count.notes" class="mt-3 text-sm text-bakery-dark/75">{{ stock_count.notes }}</p>
            <Button
                v-if="stock_count.status === 'draft'"
                :label="$t('admin_stock_count.actions.close_and_post')"
                class="min-h-11! mt-4"
                @click="closeCount"
            />
        </div>

        <div class="ui-card p-4 sm:p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead
                    class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-widest text-bakery-dark/60"
                >
                    <tr>
                        <th class="px-2 py-2">{{ $t("common.ingredient") }}</th>
                        <th class="px-2 py-2 text-right">{{ $t("admin_stock_count.columns.expected") }}</th>
                        <th class="px-2 py-2 text-right">{{ $t("admin_stock_count.columns.counted") }}</th>
                        <th class="px-2 py-2 text-right">{{ $t("admin_stock_count.columns.difference") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in stock_count.items" :key="row.id" class="border-b border-bakery-brown/10">
                        <td class="px-2 py-2">{{ row.ingredient_name }}</td>
                        <td class="px-2 py-2 text-right">{{ row.expected_quantity }} {{ row.unit }}</td>
                        <td class="px-2 py-2 text-right">{{ row.counted_quantity }} {{ row.unit }}</td>
                        <td
                            class="px-2 py-2 text-right"
                            :class="row.difference < 0 ? 'text-red-700' : 'text-green-700'"
                        >
                            {{ row.difference }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
