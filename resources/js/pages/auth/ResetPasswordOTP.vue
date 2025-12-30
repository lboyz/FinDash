<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    email: string;
    status?: string;
}>();

const form = useForm({
    otp: '',
    password: '',
    password_confirmation: '',
});

const timeLeft = ref(600); // 10 minutes in seconds
let interval: number | null = null;

const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const startTimer = () => {
    interval = window.setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            if (interval) clearInterval(interval);
        }
    }, 1000);
};

const resendOTP = () => {
    router.post(
        '/forgot-password',
        { email: props.email },
        {
            preserveScroll: true,
            onSuccess: () => {
                timeLeft.value = 600;
                if (interval) clearInterval(interval);
                startTimer();
            },
        },
    );
};

const submit = () => {
    form.post('/reset-password');
};

onMounted(() => {
    startTimer();
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});
</script>

<template>
    <AuthLayout
        title="Reset Password"
        description="Enter the OTP code sent to your email and your new password"
    >
        <Head title="Reset Password" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="mb-4 text-center text-sm text-muted-foreground">
                <p>
                    OTP code sent to: <strong>{{ email }}</strong>
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="otp">OTP Code</Label>
                <Input
                    id="otp"
                    type="text"
                    v-model="form.otp"
                    required
                    autofocus
                    maxlength="6"
                    placeholder="000000"
                    class="text-center font-mono text-2xl tracking-widest"
                />
                <InputError :message="form.errors.otp" />
            </div>

            <div class="text-center text-sm">
                <p class="mb-2 text-muted-foreground">
                    Time remaining:
                    <span
                        :class="
                            timeLeft < 60
                                ? 'font-semibold text-red-500'
                                : 'font-semibold'
                        "
                    >
                        {{ formatTime(timeLeft) }}
                    </span>
                </p>
                <p class="text-muted-foreground">
                    Didn't receive the code?
                    <button
                        type="button"
                        @click="resendOTP"
                        class="text-primary underline underline-offset-4 hover:text-primary/80"
                        :disabled="timeLeft > 540"
                    >
                        Resend OTP
                    </button>
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="password">New Password</Label>
                <Input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Enter new password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm Password</Label>
                <Input
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="w-full"
                :disabled="form.processing || form.otp.length !== 6"
            >
                <Spinner v-if="form.processing" />
                Reset Password
            </Button>

            <div class="text-center text-sm text-muted-foreground">
                <TextLink href="/login">Back to login</TextLink>
            </div>
        </form>
    </AuthLayout>
</template>
