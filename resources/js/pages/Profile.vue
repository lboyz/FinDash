<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    profile_image: string | null;
}

const props = defineProps<{
    user: User;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile',
        href: '/profile',
    },
];

const form = useForm({
    name: props.user.name,
    username: props.user.username,
    email: props.user.email,
    profile_image: null as File | null,
});

const submit = () => {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('username', form.username);
    formData.append('email', form.email);
    
    if (form.profile_image) {
        formData.append('profile_image', form.profile_image);
    }

    form.post('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('profile_image');
        },
    });
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <Head title="Profile" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-4xl mx-auto">
            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <h2 class="text-2xl font-bold mb-6">Profile Settings</h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Profile Image -->
                    <div class="flex items-center gap-6">
                        <div 
                            class="h-24 w-24 rounded-full bg-primary flex items-center justify-center text-primary-foreground text-2xl font-bold"
                        >
                            <img 
                                v-if="user.profile_image" 
                                :src="`/storage/${user.profile_image}`" 
                                alt="Profile"
                                class="h-full w-full rounded-full object-cover"
                            />
                            <span v-else>{{ getInitials(user.name) }}</span>
                        </div>
                        <div>
                            <Label for="profile_image">Profile Image</Label>
                            <Input
                                id="profile_image"
                                type="file"
                                accept="image/*"
                                @change="(e: any) => form.profile_image = e.target.files[0]"
                                class="mt-2"
                            />
                            <p class="text-xs text-muted-foreground mt-1">
                                Max 2MB. Formats: JPG, PNG
                            </p>
                            <InputError :message="form.errors.profile_image" class="mt-1" />
                        </div>
                    </div>

                    <!-- Name -->
                    <div>
                        <Label for="name">Full Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-2"
                        />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>

                    <!-- Username -->
                    <div>
                        <Label for="username">Username</Label>
                        <Input
                            id="username"
                            v-model="form.username"
                            type="text"
                            required
                            class="mt-2"
                        />
                        <InputError :message="form.errors.username" class="mt-1" />
                    </div>

                    <!-- Email -->
                    <div>
                        <Label for="email">Email Address</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="mt-2"
                        />
                        <InputError :message="form.errors.email" class="mt-1" />
                    </div>

                    <div class="flex justify-end">
                        <Button type="submit" :disabled="form.processing">
                            Save Changes
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
