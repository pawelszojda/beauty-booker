<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    appointments: {
        type: Array,
        default: () => [],
    },
    appointmentScope: {
        type: String,
        default: 'all',
    },
});

const formatDateTime = (dateTime) => {
    return new Intl.DateTimeFormat('en', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(dateTime));
};

const formatPrice = (price) => {
    if (price === null || price === undefined) {
        return '-';
    }

    return new Intl.NumberFormat('en', {
        style: 'currency',
        currency: 'PLN',
    }).format(Number(price));
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">
                                    Appointments
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    <span v-if="props.appointmentScope === 'customer'">
                                        Showing appointments assigned to your customer profile.
                                    </span>
                                    <span v-else>
                                        Showing all appointments.
                                    </span>
                                </p>
                            </div>

                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">
                                {{ props.appointments.length }} total
                            </span>
                        </div>

                        <div v-if="props.appointments.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Date
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Customer
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Service
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Stylist
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Price
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="appointment in props.appointments" :key="appointment.id">
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">
                                            <div>{{ formatDateTime(appointment.start_time) }}</div>
                                            <div class="text-xs text-gray-500">
                                                to {{ formatDateTime(appointment.end_time) }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">
                                            <div>
                                                {{ appointment.customer?.first_name }}
                                                {{ appointment.customer?.last_name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ appointment.customer?.phone }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">
                                            <div>{{ appointment.service?.name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ appointment.service?.duration_minutes }} min
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">
                                            {{ appointment.user?.name }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm">
                                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                                {{ appointment.status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-900">
                                            {{ formatPrice(appointment.service?.price) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-600">
                            No appointments found.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
