<template>
    <footer class="mt-20 border-t border-bakery-brown/15 bg-[#efe4d3]/92 backdrop-blur-sm">
        <div
            class="mx-auto flex w-full max-w-6xl flex-col gap-4 px-4 py-8 sm:flex-row sm:items-center sm:justify-between sm:px-6"
        >
            <div>
                <p class="font-heading text-xl tracking-[0.01em] text-bakery-dark">
                    {{ $t("brand.name") }} {{ $t("bakery") }}
                </p>
                <p class="text-sm text-bakery-dark/75">{{ $t("brand.description") }}.</p>
            </div>
            <div class="text-sm text-bakery-brown/90">
                <p>{{ $t("home.philosophy") }}</p>
                <p>{{ new Date().getFullYear() }} {{ $t("brand.name") }}. {{ $t("all_right") }}.</p>
            </div>
        </div>
    </footer>
</template>
