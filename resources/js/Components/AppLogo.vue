<template>
    <div class="inline-flex items-center gap-3">
        <div
            class="grid h-10 w-10 place-items-center rounded-xl bg-bakery-brown text-bakery-cream shadow-md"
        >
            <span class="font-heading text-lg">H</span>
        </div>
        <div>
            <p
                class="font-heading text-xl font-semibold leading-tight tracking-[0.01em] text-bakery-dark"
            >
                {{ $t("brand.name") }}
            </p>
            <p class="text-[0.67rem] uppercase tracking-[0.2em] text-bakery-brown/80">
                {{ $t("brand.slogan") }}
            </p>
        </div>
    </div>
</template>
