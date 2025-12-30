<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/settings',
    },
];

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.post('/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-4xl flex-1 flex-col gap-6 p-6">
            <!-- Change Password -->
            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <h2 class="mb-6 text-2xl font-bold">Change Password</h2>

                <form @submit.prevent="submitPassword" class="space-y-6">
                    <div>
                        <Label for="current_password">Current Password</Label>
                        <Input
                            id="current_password"
                            v-model="passwordForm.current_password"
                            type="password"
                            required
                            class="mt-2"
                        />
                        <InputError
                            :message="passwordForm.errors.current_password"
                            class="mt-1"
                        />
                    </div>

                    <div>
                        <Label for="password">New Password</Label>
                        <Input
                            id="password"
                            v-model="passwordForm.password"
                            type="password"
                            required
                            class="mt-2"
                        />
                        <InputError
                            :message="passwordForm.errors.password"
                            class="mt-1"
                        />
                    </div>

                    <div>
                        <Label for="password_confirmation"
                            >Confirm New Password</Label
                        >
                        <Input
                            id="password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            required
                            class="mt-2"
                        />
                        <InputError
                            :message="passwordForm.errors.password_confirmation"
                            class="mt-1"
                        />
                    </div>

                    <div class="flex justify-end">
                        <Button
                            type="submit"
                            :disabled="passwordForm.processing"
                        >
                            Update Password
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Notification Settings (Placeholder) -->
            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <h2 class="mb-4 text-2xl font-bold">Notification Settings</h2>
                <p class="text-muted-foreground">
                    Notification preferences will be available in a future
                    update.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
