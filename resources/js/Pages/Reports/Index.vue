<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps({
    filters: { type: Object, default: () => ({ period: 30, stylist_id: null }) },
    periodOptions: { type: Array, default: () => [7, 14, 21, 30, 60, 90] },
    stylists: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    charts: { type: Object, default: () => ({ daily: [], status: [] }) },
    tables: { type: Object, default: () => ({ stylists: [], services: [], recentAppointments: [] }) },
});

const form = reactive({
    period: props.filters.period,
    stylist_id: props.filters.stylist_id ?? '',
});

const applyFilters = () => {
    router.get(route('reports.index'), {
        period: form.period,
        stylist_id: form.stylist_id || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const formatCurrency = (value) =>
    new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN' }).format(Number(value || 0));

const formatDateTime = (value) => value ? new Date(value).toLocaleString('pl-PL') : '—';

const maxDailyAppointments = computed(() =>
    Math.max(1, ...props.charts.daily.map((day) => Number(day.appointments || 0))),
);

const maxStatus = computed(() =>
    Math.max(1, ...props.charts.status.map((status) => Number(status.value || 0))),
);

const summaryCards = computed(() => [
    { label: 'Appointments', value: props.summary.total ?? 0 },
    { label: 'Confirmed', value: props.summary.confirmed ?? 0 },
    { label: 'Pending', value: props.summary.pending ?? 0 },
    { label: 'Cancelled', value: props.summary.cancelled ?? 0 },
    { label: 'Revenue', value: formatCurrency(props.summary.revenue) },
    { label: 'Avg. per day', value: props.summary.averagePerDay ?? 0 },
]);
</script>

<template>
    <Head :title="$t('Reports')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $t('Reports') }}</h2>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <label class="text-sm font-medium text-gray-700">
                        {{ $t('Period') }}
                        <select v-model="form.period" @change="applyFilters" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option v-for="period in periodOptions" :key="period" :value="period">
                                {{ period }} {{ $t('days') }}
                            </option>
                        </select>
                    </label>

                    <label class="text-sm font-medium text-gray-700">
                        {{ $t('Stylist') }}
                        <select v-model="form.stylist_id" @change="applyFilters" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ $t('All stylists') }}</option>
                            <option v-for="stylist in stylists" :key="stylist.id" :value="stylist.id">
                                {{ stylist.name }}
                            </option>
                        </select>
                    </label>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-4 px-4 sm:grid-cols-2 sm:px-0 lg:grid-cols-6">
                    <div v-for="card in summaryCards" :key="card.label" class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-sm font-medium text-gray-500">{{ $t(card.label) }}</div>
                        <div class="mt-2 text-2xl font-semibold text-gray-900">{{ card.value }}</div>
                    </div>
                </div>

                <div class="grid gap-6 px-4 sm:px-0 lg:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow-sm lg:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $t('Appointments by day') }}</h3>
                        <div class="mt-6 flex h-72 items-end gap-2 overflow-x-auto border-b border-l border-gray-200 px-2 pt-4">
                            <div v-for="day in charts.daily" :key="day.date" class="flex min-w-8 flex-1 flex-col items-center justify-end gap-2">
                                <div class="w-full rounded-t bg-indigo-500" :style="{ height: `${Math.max(4, (day.appointments / maxDailyAppointments) * 220)}px` }" :title="`${day.appointments} ${$t('appointments')}`"></div>
                                <div class="whitespace-nowrap text-xs text-gray-500">{{ day.label }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $t('Appointments by status') }}</h3>
                        <div class="mt-6 space-y-4">
                            <div v-for="status in charts.status" :key="status.label">
                                <div class="mb-1 flex justify-between text-sm text-gray-700">
                                    <span>{{ $t(status.label) }}</span>
                                    <span>{{ status.value }}</span>
                                </div>
                                <div class="h-4 overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${(status.value / maxStatus) * 100}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 px-4 sm:px-0 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="border-b border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $t('Stylist performance') }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Stylist') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Appointments') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Cancelled') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Revenue') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="row in tables.stylists" :key="row.name">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ row.name }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ row.appointments }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ row.cancelled }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ formatCurrency(row.revenue) }}</td>
                                    </tr>
                                    <tr v-if="!tables.stylists.length"><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">{{ $t('No data for selected filters.') }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="border-b border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $t('Services') }}</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Service') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Appointments') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Revenue') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="row in tables.services" :key="row.name">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ row.name }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ row.appointments }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ formatCurrency(row.revenue) }}</td>
                                    </tr>
                                    <tr v-if="!tables.services.length"><td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">{{ $t('No data for selected filters.') }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow-sm mx-4 sm:mx-0">
                    <div class="border-b border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $t('Recent appointments in period') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Customer') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Service') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Stylist') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Status') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ $t('Price') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="appointment in tables.recentAppointments" :key="appointment.id">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ formatDateTime(appointment.start_time) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ appointment.customer }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ appointment.service }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ appointment.stylist }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ $t(appointment.status) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">{{ formatCurrency(appointment.price) }}</td>
                                </tr>
                                <tr v-if="!tables.recentAppointments.length"><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">{{ $t('No data for selected filters.') }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
