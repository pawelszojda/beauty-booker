<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    duration_minutes: '',
    price: '',
});

const submit = () => {
    form.post(route('services.store'));
};
</script>

<template>
    <Head title="Add service" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Add service</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form class="space-y-6 p-6" @submit.prevent="submit">
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required autofocus />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel for="duration_minutes" value="Duration in minutes" />
                                <TextInput id="duration_minutes" v-model="form.duration_minutes" type="number" min="1" class="mt-1 block w-full" required />
                                <InputError class="mt-2" :message="form.errors.duration_minutes" />
                            </div>

                            <div>
                                <InputLabel for="price" value="Price" />
                                <TextInput id="price" v-model="form.price" type="number" min="0" step="0.01" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.price" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link :href="route('services.index')">
                                <SecondaryButton type="button">Cancel</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
