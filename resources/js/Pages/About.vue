<script setup>
import { Head } from "@inertiajs/vue3";
import SectionTitle from "../Components/SectionTitle.vue";
import PublicLayout from "../Layouts/PublicLayout.vue";

defineOptions({ layout: PublicLayout });
</script>

<template>
    <Head :title="$t('about.meta_title')" />

    <section class="space-y-8">
        <SectionTitle
            :eyebrow="$t('about.eyebrow')"
            :title="$t('about.title')"
            :description="$t('about.description')"
        />

        <div
            class="rounded-2xl border border-bakery-brown/15 bg-white/70 p-6 leading-relaxed text-bakery-dark/80"
        >
            <p>
                {{ $t("about.philosophy") }}
            </p>
            <p class="mt-4">
                {{ $t("about.current_phase") }}
            </p>
        </div>
    </section>
</template>
