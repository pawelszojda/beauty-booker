<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    appointments: { type: Array, default: () => [] },
    appointmentScope: { type: String, default: 'all' },
    services: { type: Array, default: () => [] },
    stylists: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    calendarSlots: { type: Array, default: () => [] },
});

const page = usePage();
const showSlotModal = ref(false);
const weekOffset = ref(0);
const customerSearch = ref('');
const showCustomerDropdown = ref(false);
const slotStylistFilter = ref('');
const appointmentCustomerFilter = ref('');
const appointmentCustomerSearch = ref('');
const showAppointmentCustomerDropdown = ref(false);
const appointmentSortKey = ref('start_time');
const appointmentSortDirection = ref('asc');
const editingAppointment = ref(null);

const form = useForm({
    customer_id: '',
    service_id: '',
    user_id: '',
    start_time: '',
});

const editForm = useForm({
    customer_id: '',
    service_id: '',
    user_id: '',
    start_time: '',
    status: 'oczekująca',
});

const isCustomer = computed(() => page.props.auth.isCustomer);

watch(
    () => form.user_id,
    (userId) => {
        slotStylistFilter.value = userId ? String(userId) : '';
        form.start_time = '';
    },
);

const selectedService = computed(() =>
    props.services.find((service) => service.id === Number(form.service_id)),
);

const selectedStylist = computed(() =>
    props.stylists.find((stylist) => stylist.id === Number(form.user_id)),
);

const selectedSlot = computed(() =>
    props.calendarSlots.find(
        (slot) =>
            slot.start_time === form.start_time &&
            slot.stylist_id === Number(form.user_id),
    ),
);

const selectedCustomer = computed(() =>
    props.customers.find((customer) => customer.id === Number(form.customer_id)),
);

const customerLabel = (customer) =>
    `${customer.first_name} ${customer.last_name} - ${customer.phone}${customer.email ? ` - ${customer.email}` : ''}`;

const selectedCustomerLabel = computed(() =>
    selectedCustomer.value ? customerLabel(selectedCustomer.value) : '',
);

const filteredCustomers = computed(() => {
    const search = customerSearch.value.trim().toLowerCase();

    if (!search || search === selectedCustomerLabel.value.toLowerCase()) {
        return props.customers;
    }

    return props.customers.filter((customer) =>
        `${customer.first_name} ${customer.last_name}`.toLowerCase().includes(search) ||
        `${customer.last_name} ${customer.first_name}`.toLowerCase().includes(search) ||
        customer.email?.toLowerCase().includes(search) ||
        customer.phone?.toLowerCase().includes(search),
    );
});

const visibleSlots = computed(() => {
    const stylistId = slotStylistFilter.value || form.user_id;

    if (!stylistId) {
        return props.calendarSlots;
    }

    return props.calendarSlots.filter((slot) => slot.stylist_id === Number(stylistId));
});

const appointmentSortValue = (appointment, key) => {
    const values = {
        start_time: appointment.start_time,
        customer: `${appointment.customer?.last_name ?? ''} ${appointment.customer?.first_name ?? ''}`,
        service: appointment.service?.name ?? '',
        stylist: appointment.user?.name ?? '',
        status: appointment.status ?? '',
        price: Number(appointment.service?.price ?? 0),
    };

    return values[key] ?? '';
};

const selectedAppointmentFilterCustomer = computed(() =>
    props.customers.find((customer) => customer.id === Number(appointmentCustomerFilter.value)),
);

const selectedAppointmentFilterCustomerLabel = computed(() =>
    selectedAppointmentFilterCustomer.value ? customerLabel(selectedAppointmentFilterCustomer.value) : '',
);

const filteredAppointmentCustomers = computed(() => {
    const search = appointmentCustomerSearch.value.trim().toLowerCase();

    if (!search || search === selectedAppointmentFilterCustomerLabel.value.toLowerCase()) {
        return props.customers;
    }

    return props.customers.filter((customer) =>
        `${customer.first_name} ${customer.last_name}`.toLowerCase().includes(search) ||
        `${customer.last_name} ${customer.first_name}`.toLowerCase().includes(search) ||
        customer.email?.toLowerCase().includes(search) ||
        customer.phone?.toLowerCase().includes(search),
    );
});

const filteredAppointments = computed(() => {
    const customerId = Number(appointmentCustomerFilter.value);

    if (!customerId) {
        return props.appointments;
    }

    return props.appointments.filter((appointment) => appointment.customer?.id === customerId);
});

const sortedAppointments = computed(() => {
    return [...filteredAppointments.value].sort((a, b) => {
        const aValue = appointmentSortValue(a, appointmentSortKey.value);
        const bValue = appointmentSortValue(b, appointmentSortKey.value);

        if (aValue < bValue) {
            return appointmentSortDirection.value === 'asc' ? -1 : 1;
        }

        if (aValue > bValue) {
            return appointmentSortDirection.value === 'asc' ? 1 : -1;
        }

        return 0;
    });
});

const sortAppointmentsBy = (key) => {
    if (appointmentSortKey.value === key) {
        appointmentSortDirection.value = appointmentSortDirection.value === 'asc' ? 'desc' : 'asc';
        return;
    }

    appointmentSortKey.value = key;
    appointmentSortDirection.value = 'asc';
};

const sortIndicator = (key) => {
    if (appointmentSortKey.value !== key) {
        return '';
    }

    return appointmentSortDirection.value === 'asc' ? ' ↑' : ' ↓';
};

const today = () => {
    const date = new Date();
    date.setHours(0, 0, 0, 0);
    return date;
};

const startOfWeek = (date) => {
    const result = new Date(date);
    const day = result.getDay();
    const diff = day === 0 ? -6 : 1 - day;
    result.setDate(result.getDate() + diff);
    result.setHours(0, 0, 0, 0);
    return result;
};

const addDays = (date, days) => {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
};

const toDateKey = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const weekStart = computed(() => {
    const start = startOfWeek(today());
    start.setDate(start.getDate() + weekOffset.value * 7);
    return start;
});

const weekDays = computed(() =>
    Array.from({ length: 7 }, (_, index) => {
        const date = addDays(weekStart.value, index);
        return {
            date,
            key: toDateKey(date),
            label: new Intl.DateTimeFormat('en', { weekday: 'short' }).format(date),
            day: new Intl.DateTimeFormat('en', { day: '2-digit', month: '2-digit' }).format(date),
        };
    }),
);

const weekRangeLabel = computed(() => {
    const end = addDays(weekStart.value, 6);
    const formatter = new Intl.DateTimeFormat('en-GB');
    return `${formatter.format(weekStart.value)} - ${formatter.format(end)}`;
});

const hours = Array.from({ length: 9 }, (_, index) => 9 + index);

const slotsForCell = (dateKey, hour) =>
    visibleSlots.value.filter((slot) => {
        const slotHour = Number(slot.time.split(':')[0]);
        return slot.date === dateKey && slotHour === hour;
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

const toDateTimeLocal = (dateTime) => {
    const date = new Date(dateTime);
    const offset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
};

const chooseSlot = (slot) => {
    if (slot.status !== 'free') {
        return;
    }

    form.user_id = String(slot.stylist_id);
    slotStylistFilter.value = String(slot.stylist_id);
    form.start_time = slot.start_time;
    showSlotModal.value = false;
};

const openCustomerDropdown = (event) => {
    showCustomerDropdown.value = true;
    event.target.select();
};

const closeCustomerDropdown = () => {
    window.setTimeout(() => {
        showCustomerDropdown.value = false;

        if (selectedCustomer.value) {
            customerSearch.value = selectedCustomerLabel.value;
        }
    }, 150);
};

const selectCustomer = (customer) => {
    form.customer_id = customer.id;
    customerSearch.value = customerLabel(customer);
    showCustomerDropdown.value = false;
};

const clearSelectedCustomerIfTyping = () => {
    if (customerSearch.value !== selectedCustomerLabel.value) {
        form.customer_id = '';
    }
};

const openAppointmentCustomerDropdown = (event) => {
    showAppointmentCustomerDropdown.value = true;
    event.target.select();
};

const closeAppointmentCustomerDropdown = () => {
    window.setTimeout(() => {
        showAppointmentCustomerDropdown.value = false;

        if (selectedAppointmentFilterCustomer.value) {
            appointmentCustomerSearch.value = selectedAppointmentFilterCustomerLabel.value;
        }
    }, 150);
};

const selectAppointmentFilterCustomer = (customer) => {
    appointmentCustomerFilter.value = String(customer.id);
    appointmentCustomerSearch.value = customerLabel(customer);
    showAppointmentCustomerDropdown.value = false;
};

const clearAppointmentFilterCustomerIfTyping = () => {
    if (appointmentCustomerSearch.value !== selectedAppointmentFilterCustomerLabel.value) {
        appointmentCustomerFilter.value = '';
    }
};

const clearAppointmentCustomerFilter = () => {
    appointmentCustomerFilter.value = '';
    appointmentCustomerSearch.value = '';
};

const submit = () => {
    form.post(route('dashboard.appointments.store'), {
        preserveScroll: true,
    });
};

const startEditingAppointment = (appointment) => {
    editingAppointment.value = appointment;
    editForm.customer_id = appointment.customer?.id ?? '';
    editForm.service_id = appointment.service?.id ?? '';
    editForm.user_id = appointment.user?.id ?? '';
    editForm.start_time = toDateTimeLocal(appointment.start_time);
    editForm.status = appointment.status ?? 'oczekująca';
};

const closeEditAppointment = () => {
    editingAppointment.value = null;
    editForm.clearErrors();
    editForm.reset();
};

const updateAppointment = () => {
    editForm.put(route('dashboard.appointments.update', editingAppointment.value.id), {
        preserveScroll: true,
        onSuccess: () => closeEditAppointment(),
    });
};

const deleteAppointment = () => {
    if (!confirm('Are you sure you want to delete this appointment?')) {
        return;
    }

    router.delete(route('dashboard.appointments.destroy', editingAppointment.value.id), {
        preserveScroll: true,
        onSuccess: () => closeEditAppointment(),
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <div class="overflow-visible bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold">Book new appointment</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Choose service, stylist and one free calendar slot.
                            </p>
                        </div>

                        <form class="space-y-6" @submit.prevent="submit">
                            <div class="grid gap-6 md:grid-cols-4">
                                <div v-if="!isCustomer" class="relative">
                                    <InputLabel for="customer_search" value="Customer" />
                                    <input
                                        id="customer_search"
                                        v-model="customerSearch"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Click or type name, surname or phone"
                                        autocomplete="off"
                                        required
                                        @focus="openCustomerDropdown"
                                        @input="clearSelectedCustomerIfTyping"
                                        @blur="closeCustomerDropdown"
                                    />

                                    <input v-model="form.customer_id" type="hidden" required />

                                    <div
                                        v-if="showCustomerDropdown"
                                        class="absolute z-50 mt-1 max-h-96 w-[32rem] max-w-[calc(100vw-3rem)] overflow-auto rounded-md border border-gray-200 bg-white shadow-xl"
                                    >
                                        <button
                                            v-for="customer in filteredCustomers"
                                            :key="customer.id"
                                            type="button"
                                            class="block w-full px-3 py-2 text-left text-sm hover:bg-indigo-50"
                                            :class="customer.id === Number(form.customer_id) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700'"
                                            @mousedown.prevent="selectCustomer(customer)"
                                        >
                                            <div class="font-medium">
                                                {{ customer.first_name }} {{ customer.last_name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ customer.phone }}<span v-if="customer.email"> · {{ customer.email }}</span>
                                            </div>
                                        </button>

                                        <div v-if="!filteredCustomers.length" class="px-3 py-3 text-sm text-gray-500">
                                            No customers found.
                                        </div>
                                    </div>

                                    <p v-if="selectedCustomer" class="mt-1 text-xs text-gray-500">
                                        Selected: {{ selectedCustomer.first_name }} {{ selectedCustomer.last_name }}
                                    </p>

                                    <InputError class="mt-2" :message="form.errors.customer_id" />
                                </div>

                                <div>
                                    <InputLabel for="service_id" value="Service" />
                                    <select
                                        id="service_id"
                                        v-model="form.service_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="" disabled>Select service</option>
                                        <option v-for="service in props.services" :key="service.id" :value="service.id">
                                            {{ service.name }} - {{ service.duration_minutes }} min
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.service_id" />
                                </div>

                                <div>
                                    <InputLabel for="user_id" value="Stylist" />
                                    <select
                                        id="user_id"
                                        v-model="form.user_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="" disabled>Select stylist</option>
                                        <option v-for="stylist in props.stylists" :key="stylist.id" :value="stylist.id">
                                            {{ stylist.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.user_id" />
                                </div>

                                <div>
                                    <InputLabel value="Selected slot" />
                                    <button
                                        type="button"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-left text-sm text-gray-700 shadow-sm transition hover:bg-gray-50 focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                                        @click="showSlotModal = true"
                                    >
                                        <span v-if="selectedSlot">
                                            {{ formatDateTime(selectedSlot.start_time) }} with {{ selectedStylist?.name }}
                                        </span>
                                        <span v-else class="text-gray-500">Click to choose slot</span>
                                    </button>
                                    <InputError class="mt-2" :message="form.errors.start_time" />
                                </div>
                            </div>

                            <div v-if="selectedService" class="rounded-md bg-gray-50 p-4 text-sm text-gray-700">
                                Selected service: <strong>{{ selectedService.name }}</strong>,
                                duration <strong>{{ selectedService.duration_minutes }} minutes</strong>,
                                price <strong>{{ formatPrice(selectedService.price) }}</strong>.
                            </div>

                            <div class="flex justify-end">
                                <PrimaryButton :disabled="form.processing || !form.service_id || !form.user_id || !form.start_time">
                                    Book appointment
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">Appointments</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    <span v-if="props.appointmentScope === 'customer'">
                                        Showing appointments assigned to your customer profile.
                                    </span>
                                    <span v-else>Showing all appointments.</span>
                                </p>
                            </div>

                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">
                                {{ sortedAppointments.length }} total
                            </span>
                        </div>

                        <div v-if="!isCustomer" class="mb-4 max-w-xl">
                            <InputLabel for="appointment_customer_filter" value="Filter by customer" />
                            <div class="relative mt-1 flex gap-2">
                                <div class="relative flex-1">
                                    <input
                                        id="appointment_customer_filter"
                                        v-model="appointmentCustomerSearch"
                                        type="text"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Click or type name, surname or phone"
                                        autocomplete="off"
                                        @focus="openAppointmentCustomerDropdown"
                                        @input="clearAppointmentFilterCustomerIfTyping"
                                        @blur="closeAppointmentCustomerDropdown"
                                    />

                                    <div
                                        v-if="showAppointmentCustomerDropdown"
                                        class="absolute z-50 mt-1 max-h-96 w-[32rem] max-w-[calc(100vw-3rem)] overflow-auto rounded-md border border-gray-200 bg-white shadow-xl"
                                    >
                                        <button
                                            v-for="customer in filteredAppointmentCustomers"
                                            :key="customer.id"
                                            type="button"
                                            class="block w-full px-3 py-2 text-left text-sm hover:bg-indigo-50"
                                            :class="customer.id === Number(appointmentCustomerFilter) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700'"
                                            @mousedown.prevent="selectAppointmentFilterCustomer(customer)"
                                        >
                                            <div class="font-medium">
                                                {{ customer.first_name }} {{ customer.last_name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ customer.phone }}<span v-if="customer.email"> · {{ customer.email }}</span>
                                            </div>
                                        </button>

                                        <div v-if="!filteredAppointmentCustomers.length" class="px-3 py-3 text-sm text-gray-500">
                                            No customers found.
                                        </div>
                                    </div>
                                </div>

                                <SecondaryButton type="button" @click="clearAppointmentCustomerFilter">
                                    Clear
                                </SecondaryButton>
                            </div>
                        </div>

                        <div v-if="sortedAppointments.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            <button type="button" @click="sortAppointmentsBy('start_time')">Date{{ sortIndicator('start_time') }}</button>
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            <button type="button" @click="sortAppointmentsBy('customer')">Customer{{ sortIndicator('customer') }}</button>
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            <button type="button" @click="sortAppointmentsBy('service')">Service{{ sortIndicator('service') }}</button>
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            <button type="button" @click="sortAppointmentsBy('stylist')">Stylist{{ sortIndicator('stylist') }}</button>
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            <button type="button" @click="sortAppointmentsBy('status')">Status{{ sortIndicator('status') }}</button>
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            <button type="button" @click="sortAppointmentsBy('price')">Price{{ sortIndicator('price') }}</button>
                                        </th>
                                        <th v-if="!isCustomer" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="appointment in sortedAppointments" :key="appointment.id">
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">
                                            <div>{{ formatDateTime(appointment.start_time) }}</div>
                                            <div class="text-xs text-gray-500">to {{ formatDateTime(appointment.end_time) }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">
                                            <div>{{ appointment.customer?.first_name }} {{ appointment.customer?.last_name }}</div>
                                            <div class="text-xs text-gray-500">{{ appointment.customer?.phone }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">
                                            <div>{{ appointment.service?.name }}</div>
                                            <div class="text-xs text-gray-500">{{ appointment.service?.duration_minutes }} min</div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-900">{{ appointment.user?.name }}</td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm">
                                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                                {{ appointment.status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-900">
                                            {{ formatPrice(appointment.service?.price) }}
                                        </td>
                                        <td v-if="!isCustomer" class="whitespace-nowrap px-4 py-4 text-right text-sm">
                                            <SecondaryButton type="button" @click="startEditingAppointment(appointment)">
                                                Edit
                                            </SecondaryButton>
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

        <div
            v-if="editingAppointment"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeEditAppointment"
        >
            <div class="w-full max-w-3xl rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Edit appointment</h3>
                    <button class="text-2xl leading-none text-gray-400 hover:text-gray-600" type="button" @click="closeEditAppointment">
                        &times;
                    </button>
                </div>

                <form class="space-y-6 p-6" @submit.prevent="updateAppointment">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <InputLabel for="edit_customer_id" value="Customer" />
                            <select
                                id="edit_customer_id"
                                v-model="editForm.customer_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option v-for="customer in props.customers" :key="customer.id" :value="customer.id">
                                    {{ customer.first_name }} {{ customer.last_name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="editForm.errors.customer_id" />
                        </div>

                        <div>
                            <InputLabel for="edit_service_id" value="Service" />
                            <select
                                id="edit_service_id"
                                v-model="editForm.service_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option v-for="service in props.services" :key="service.id" :value="service.id">
                                    {{ service.name }} - {{ service.duration_minutes }} min
                                </option>
                            </select>
                            <InputError class="mt-2" :message="editForm.errors.service_id" />
                        </div>

                        <div>
                            <InputLabel for="edit_user_id" value="Stylist" />
                            <select
                                id="edit_user_id"
                                v-model="editForm.user_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option v-for="stylist in props.stylists" :key="stylist.id" :value="stylist.id">
                                    {{ stylist.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="editForm.errors.user_id" />
                        </div>

                        <div>
                            <InputLabel for="edit_start_time" value="Start time" />
                            <input
                                id="edit_start_time"
                                v-model="editForm.start_time"
                                type="datetime-local"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <InputError class="mt-2" :message="editForm.errors.start_time" />
                        </div>

                        <div>
                            <InputLabel for="edit_status" value="Status" />
                            <select
                                id="edit_status"
                                v-model="editForm.status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="oczekująca">oczekująca</option>
                                <option value="potwierdzona">potwierdzona</option>
                                <option value="odwołana">odwołana</option>
                            </select>
                            <InputError class="mt-2" :message="editForm.errors.status" />
                        </div>
                    </div>

                    <div class="flex justify-between gap-3">
                        <DangerButton type="button" @click="deleteAppointment">Delete appointment</DangerButton>

                        <div class="flex gap-3">
                            <SecondaryButton type="button" @click="closeEditAppointment">Cancel</SecondaryButton>
                            <PrimaryButton :disabled="editForm.processing">Update appointment</PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div
            v-if="showSlotModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="showSlotModal = false"
        >
            <div class="max-h-[90vh] w-full max-w-7xl overflow-hidden rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Choose appointment slot</h3>
                        <p class="text-sm text-gray-600">
                            <span v-if="isCustomer">Free slots and your taken appointments are visible.</span>
                            <span v-else>Taken slots include appointment details.</span>
                        </p>
                    </div>

                    <button class="text-2xl leading-none text-gray-400 hover:text-gray-600" type="button" @click="showSlotModal = false">
                        &times;
                    </button>
                </div>

                <div class="border-b px-6 py-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-2">
                            <SecondaryButton type="button" @click="weekOffset--">Previous week</SecondaryButton>
                            <SecondaryButton type="button" @click="weekOffset++">Next week</SecondaryButton>
                        </div>

                        <div class="text-center text-lg font-semibold text-gray-900">
                            {{ weekRangeLabel }}
                        </div>

                        <div class="w-full md:w-64">
                            <select
                                v-model="slotStylistFilter"
                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">All stylists</option>
                                <option v-for="stylist in props.stylists" :key="stylist.id" :value="String(stylist.id)">
                                    {{ stylist.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="max-h-[65vh] overflow-auto p-6">
                    <div class="min-w-[1000px]">
                        <div class="grid grid-cols-[80px_repeat(7,minmax(120px,1fr))] border-b border-gray-200">
                            <div class="bg-gray-50 p-3 text-xs font-medium uppercase text-gray-500">Hour</div>
                            <div
                                v-for="day in weekDays"
                                :key="day.key"
                                class="border-l bg-gray-50 p-3 text-center"
                            >
                                <div class="font-semibold text-gray-900">{{ day.label }}</div>
                                <div class="text-sm text-gray-600">{{ day.day }}</div>
                            </div>
                        </div>

                        <div
                            v-for="hour in hours"
                            :key="hour"
                            class="grid min-h-24 grid-cols-[80px_repeat(7,minmax(120px,1fr))] border-b border-gray-200"
                        >
                            <div class="bg-gray-50 p-3 text-sm font-medium text-gray-700">
                                {{ String(hour).padStart(2, '0') }}:00
                            </div>

                            <div
                                v-for="day in weekDays"
                                :key="`${day.key}-${hour}`"
                                class="space-y-2 border-l p-2"
                            >
                                <button
                                    v-for="slot in slotsForCell(day.key, hour)"
                                    :key="slot.id"
                                    type="button"
                                    class="w-full rounded-md border px-2 py-2 text-left text-xs transition"
                                    :class="{
                                        'border-green-200 bg-green-50 hover:bg-green-100': slot.status === 'free',
                                        'border-red-200 bg-red-50': slot.status === 'taken',
                                        'ring-2 ring-indigo-500': selectedSlot?.id === slot.id,
                                    }"
                                    :disabled="slot.status !== 'free'"
                                    @click="chooseSlot(slot)"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-semibold text-gray-900">{{ slot.time }}</span>
                                        <span :class="slot.status === 'free' ? 'text-green-700' : 'text-red-700'">
                                            {{ slot.status }}
                                        </span>
                                    </div>
                                    <div class="mt-1 truncate text-gray-600">{{ slot.stylist_name }}</div>

                                    <div v-if="slot.appointment" class="mt-1 space-y-0.5 text-gray-600">
                                        <div class="truncate">{{ slot.appointment.service?.name }}</div>
                                        <div class="truncate">
                                            {{ slot.appointment.customer?.first_name }}
                                            {{ slot.appointment.customer?.last_name }}
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
