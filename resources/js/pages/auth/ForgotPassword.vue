<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <AuthBase
        title="Forgot password"
        description="Enter your email to receive a password reset link"
    >
        <Head title="Forgot password" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    v-model="form.email"
                    autocomplete="off"
                    autofocus
                    placeholder="email@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-start">
                <Button
                    class="w-full bg-orange-600 font-bold text-white hover:bg-orange-700"
                    :disabled="form.processing"
                    data-test="email-password-reset-link-button"
                >
                    <Spinner v-if="form.processing" />
                    Email password reset link
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Or, return to
                <TextLink href="/login" class="underline underline-offset-4"
                    >log in</TextLink
                >
            </div>
        </form>
    </AuthBase>
</template>
