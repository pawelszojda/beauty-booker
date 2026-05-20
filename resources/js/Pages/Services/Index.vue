<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    services: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const formatPrice = (price) => {
    if (price === null || price === undefined) {
        return '-';
    }

    return new Intl.NumberFormat('en', {
        style: 'currency',
        currency: 'PLN',
    }).format(Number(price));
};

const confirmDelete = () => confirm('Are you sure you want to delete this service?');
</script>

<template>
    <Head title="Services" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Services
                </h2>

                <Link :href="route('services.create')">
                    <PrimaryButton>Add service</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="page.props.errors.service" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ page.props.errors.service }}
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="props.services.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Duration</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Price</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Appointments</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="service in props.services" :key="service.id">
                                        <td class="whitespace-nowrap px-4 py-4 text-sm font-medium text-gray-900">{{ service.name }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">{{ service.duration_minutes }} min</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">{{ formatPrice(service.price) }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">{{ service.appointments_count }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                                            <div class="flex justify-end gap-2">
                                                <Link :href="route('services.edit', service.id)">
                                                    <SecondaryButton>Edit</SecondaryButton>
                                                </Link>
                                                <Link
                                                    :href="route('services.destroy', service.id)"
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
                            No services yet.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
