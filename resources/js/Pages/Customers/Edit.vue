<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    first_name: props.customer.first_name ?? '',
    last_name: props.customer.last_name ?? '',
    phone: props.customer.phone ?? '',
    email: props.customer.email ?? '',
    notes: props.customer.notes ?? '',
});

const submit = () => {
    form.put(route('customers.update', props.customer.id));
};
</script>

<template>
    <Head :title="$t('Edit customer')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $t('Edit customer') }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form class="space-y-6 p-6" @submit.prevent="submit">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel for="first_name" :value="$t('First name')" />
                                <TextInput id="first_name" v-model="form.first_name" class="mt-1 block w-full" required autofocus />
                                <InputError class="mt-2" :message="form.errors.first_name" />
                            </div>

                            <div>
                                <InputLabel for="last_name" :value="$t('Last name')" />
                                <TextInput id="last_name" v-model="form.last_name" class="mt-1 block w-full" required />
                                <InputError class="mt-2" :message="form.errors.last_name" />
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel for="phone" :value="$t('Phone')" />
                                <TextInput id="phone" v-model="form.phone" class="mt-1 block w-full" required />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>

                            <div>
                                <InputLabel for="email" :value="$t('Email')" />
                                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="notes" :value="$t('Notes')" />
                            <textarea id="notes" v-model="form.notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <InputError class="mt-2" :message="form.errors.notes" />
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route('customers.index')">
                                <SecondaryButton type="button">{{ $t('Cancel') }}</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">{{ $t('Update') }}</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
