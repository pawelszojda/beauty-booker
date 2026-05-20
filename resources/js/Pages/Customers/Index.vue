<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    customers: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const confirmDelete = () => confirm(trans('Are you sure you want to delete this customer?'));
</script>

<template>
    <Head :title="$t('Customers')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ $t('Customers') }}
                </h2>

                <Link :href="route('customers.create')">
                    <PrimaryButton>{{ $t('Add customer') }}</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="page.props.errors.customer" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ page.props.errors.customer }}
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="props.customers.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Name') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Phone') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Email') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Appointments') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="customer in props.customers" :key="customer.id">
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <div class="font-medium">{{ customer.first_name }} {{ customer.last_name }}</div>
                                            <div v-if="customer.notes" class="mt-1 max-w-md truncate text-xs text-gray-500">{{ customer.notes }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">{{ customer.phone }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">{{ customer.email || '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">{{ customer.appointments_count }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                                            <div class="flex justify-end gap-2">
                                                <Link :href="route('customers.edit', customer.id)">
                                                    <SecondaryButton>{{ $t('Edit') }}</SecondaryButton>
                                                </Link>
                                                <Link
                                                    :href="route('customers.destroy', customer.id)"
                                                    method="delete"
                                                    as="button"
                                                    :onBefore="confirmDelete"
                                                >
                                                    <DangerButton>{{ $t('Delete') }}</DangerButton>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-600">
                            {{ $t('No customers yet.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
