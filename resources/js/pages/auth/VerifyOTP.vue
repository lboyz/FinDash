<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{
    email: string;
    type: 'register' | 'login';
}>();

const form = useForm({
    otp: '',
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
        '/resend-otp',
        {},
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
    form.post('/verify-otp', {
        preserveScroll: true,
    });
};

onMounted(() => {
    startTimer();
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});
</script>

<template>
    <AuthBase
        title="Verify OTP"
        :description="`We've sent a 6-digit code to ${email}`"
    >
        <Head title="Verify OTP" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="otp">Enter OTP Code</Label>
                    <Input
                        id="otp"
                        v-model="form.otp"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
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

                <Button
                    type="submit"
                    class="w-full"
                    :tabindex="2"
                    :disabled="form.processing || form.otp.length !== 6"
                >
                    <Spinner v-if="form.processing" />
                    Verify & Continue
                </Button>
            </div>

            <div class="text-center text-xs text-muted-foreground">
                <p>🔒 This is a security measure to protect your account.</p>
                <p class="mt-1">Never share your OTP code with anyone.</p>
            </div>
        </form>
    </AuthBase>
</template>
