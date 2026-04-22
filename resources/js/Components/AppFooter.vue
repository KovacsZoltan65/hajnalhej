<template>
    <footer class="mt-20 border-t border-bakery-brown/15 bg-[#efe4d3]">
        <div
            class="mx-auto flex w-full max-w-6xl flex-col gap-4 px-4 py-8 sm:flex-row sm:items-center sm:justify-between sm:px-6"
        >
            <div>
                <p class="font-heading text-xl text-bakery-dark">Hajnalhej Bakery</p>
                <p class="text-sm text-bakery-dark/75">
                    Kovászos sütemények és kenyerek Budapesten.
                </p>
            </div>
            <div class="text-sm text-bakery-brown/90">
                <p>Kovasz. Ido. Turelem.</p>
                <p>{{ new Date().getFullYear() }} Hajnalhéj. Minden jog fenntartva.</p>
            </div>
        </div>
    </footer>
</template>
