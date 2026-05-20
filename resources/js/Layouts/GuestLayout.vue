<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>

        <div class="mt-6 flex items-center gap-4">
            <a
                href="https://www.facebook.com/"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Facebook profile"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-gray-600 shadow-md transition hover:bg-blue-50 hover:text-blue-600"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M22 12.061C22 6.505 17.523 2 12 2S2 6.505 2 12.061c0 5.022 3.657 9.184 8.438 9.939v-7.03H7.898v-2.909h2.54V9.845c0-2.522 1.493-3.916 3.777-3.916 1.094 0 2.238.196 2.238.196v2.475h-1.26c-1.242 0-1.63.775-1.63 1.57v1.891h2.773l-.443 2.909h-2.33V22C18.343 21.245 22 17.083 22 12.061z" />
                </svg>
            </a>

            <a
                href="https://www.instagram.com/"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Instagram profile"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-gray-600 shadow-md transition hover:bg-pink-50 hover:text-pink-600"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M7.75 2h8.5A5.756 5.756 0 0 1 22 7.75v8.5A5.756 5.756 0 0 1 16.25 22h-8.5A5.756 5.756 0 0 1 2 16.25v-8.5A5.756 5.756 0 0 1 7.75 2zm0 1.5A4.255 4.255 0 0 0 3.5 7.75v8.5a4.255 4.255 0 0 0 4.25 4.25h8.5a4.255 4.255 0 0 0 4.25-4.25v-8.5a4.255 4.255 0 0 0-4.25-4.25h-8.5zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7zm5.25-2.25a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5z" />
                </svg>
            </a>
        </div>
    </div>
</template>
