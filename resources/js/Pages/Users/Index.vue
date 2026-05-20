<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const confirmDelete = () => confirm('Are you sure you want to delete this user?');

const roleLabel = (role) => role?.replace('_', ' ') ?? '-';
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Users</h2>

                <Link :href="route('users.create')">
                    <PrimaryButton>Add user</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="page.props.errors.user" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ page.props.errors.user }}
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="props.users.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Role</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Created</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="user in props.users" :key="user.id">
                                        <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900">{{ user.name }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">{{ user.email }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">
                                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium capitalize text-gray-700">
                                                {{ roleLabel(user.role) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">
                                            {{ new Date(user.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                                            <div class="flex justify-end gap-2">
                                                <Link :href="route('users.edit', user.id)">
                                                    <SecondaryButton>Edit</SecondaryButton>
                                                </Link>
                                                <Link
                                                    :href="route('users.destroy', user.id)"
                                                    method="delete"
                                                    as="button"
                                                    :onBefore="confirmDelete"
                                                >
                                                    <DangerButton>Delete</DangerButton>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-600">
                            No users yet.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
