<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";

const page = usePage();
const isMobileOpen = ref(false);

const links = computed(() => [
    { label: trans("nav.home"), href: "/" },
    { label: trans("nav.weekly_menu"), href: "/weekly-menu" },
    { label: trans("nav.about"), href: "/about" },
]);

const user = computed(() => page.props.auth?.user ?? null);
const navUi = computed(() => page.props.ui?.nav ?? {});
const cartTotalQuantity = computed(() => page.props.cart?.total_quantity ?? 0);

const navLabel = (key) => navUi.value[key] ?? trans(`nav.${key}`);
const isActive = (href) => page.url === href || page.url.startsWith(`${href}/`);
const closeMobile = () => {
    isMobileOpen.value = false;
};
</script>

<template>
    <div class="flex items-center gap-2">
        <nav class="hidden items-center gap-1 md:flex">
            <Link
                v-for="link in links"
                :key="link.href"
                :href="link.href"
                class="rounded-full px-4 py-2 text-sm font-medium transition"
                :class="
                    isActive(link.href)
                        ? 'bg-bakery-brown text-bakery-cream'
                        : 'text-bakery-dark/80 hover:bg-bakery-brown/10'
                "
            >
                {{ link.label }}
            </Link>
        </nav>

        <div class="hidden items-center gap-2 md:flex">
            <Link
                href="/cart"
                class="relative rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream"
            >
                {{ navLabel("cart") }}
                <span
                    v-if="cartTotalQuantity > 0"
                    class="absolute -right-1 -top-1 rounded-full bg-bakery-gold px-1.5 py-0.5 text-[10px] font-bold text-bakery-dark"
                >
                    {{ cartTotalQuantity }}
                </span>
            </Link>
            <template v-if="!user">
                <Link
                    href="/login"
                    class="rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream"
                >
                    {{ navLabel("login") }}
                </Link>
                <Link
                    href="/register"
                    class="rounded-full bg-bakery-brown px-4 py-2 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark"
                >
                    {{ navLabel("register") }}
                </Link>
            </template>

            <template v-else>
                <Link
                    href="/account"
                    class="rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream"
                >
                    {{ navLabel("account") }}
                </Link>
                <Link
                    v-if="user.can_access_admin_panel"
                    href="/admin/dashboard"
                    class="rounded-full bg-bakery-gold px-4 py-2 text-sm font-semibold text-bakery-dark transition hover:bg-[#edbb5a]"
                >
                    {{ navLabel("admin") }}
                </Link>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    type="button"
                    class="rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream"
                >
                    {{ navLabel("logout") }}
                </Link>
            </template>
        </div>

        <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-bakery-brown/20 text-bakery-brown md:hidden"
            @click="isMobileOpen = !isMobileOpen"
        >
            <span class="sr-only">
                {{
                    isMobileOpen
                        ? $t("nav.close_menu")
                        : $t("nav.open_menu")
                }}
            </span>
            <i :class="isMobileOpen ? 'pi pi-times' : 'pi pi-bars'" />
        </button>

        <div
            v-if="isMobileOpen"
            class="absolute left-4 right-4 top-16 z-50 rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-4 shadow-lg md:hidden"
        >
            <nav class="space-y-2">
                <Link
                    v-for="link in links"
                    :key="`mobile-${link.href}`"
                    :href="link.href"
                    class="block rounded-xl px-3 py-2 text-sm font-medium"
                    :class="
                        isActive(link.href)
                            ? 'bg-bakery-brown text-bakery-cream'
                            : 'text-bakery-dark/80 hover:bg-bakery-brown/10'
                    "
                    @click="closeMobile"
                >
                    {{ link.label }}
                </Link>
            </nav>

            <div class="mt-3 space-y-2 border-t border-bakery-brown/15 pt-3">
                <Link
                    href="/cart"
                    class="block rounded-xl px-3 py-2 text-sm font-semibold text-bakery-brown hover:bg-bakery-brown/10"
                    @click="closeMobile"
                >
                    {{ navLabel("cart") }}
                    <span
                        v-if="cartTotalQuantity > 0"
                        class="ml-1 rounded-full bg-bakery-gold px-1.5 py-0.5 text-[10px] font-bold text-bakery-dark"
                        >{{ cartTotalQuantity }}</span
                    >
                </Link>

                <template v-if="!user">
                    <Link
                        href="/login"
                        class="block rounded-xl px-3 py-2 text-sm font-semibold text-bakery-brown hover:bg-bakery-brown/10"
                        @click="closeMobile"
                    >
                        {{ navLabel("login") }}
                    </Link>

                    <Link
                        href="/register"
                        class="block rounded-xl bg-bakery-brown px-3 py-2 text-sm font-semibold text-bakery-cream"
                        @click="closeMobile"
                    >
                        {{ navLabel("register") }}
                    </Link>
                </template>

                <template v-else>
                    <Link
                        href="/account"
                        class="block rounded-xl px-3 py-2 text-sm font-semibold text-bakery-brown hover:bg-bakery-brown/10"
                        @click="closeMobile"
                    >
                        {{ navLabel("account") }}
                    </Link>
                    <Link
                        v-if="user.can_access_admin_panel"
                        href="/admin/dashboard"
                        class="block rounded-xl bg-bakery-gold px-3 py-2 text-sm font-semibold text-bakery-dark"
                        @click="closeMobile"
                    >
                        {{ navLabel("admin") }}
                    </Link>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        type="button"
                        class="block w-full rounded-xl px-3 py-2 text-left text-sm font-semibold text-bakery-brown hover:bg-bakery-brown/10"
                        @click="closeMobile"
                    >
                        {{ navLabel("logout") }}
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>
