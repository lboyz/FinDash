<script setup lang="ts">
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import settingsRoutes from '@/routes/settings';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    profile_image: string | null;
    email_verified_at: string | null;
}

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth.user as unknown as User);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const form = useForm({
    name: user.value.name,
    username: user.value.username,
    email: user.value.email,
    profile_image: null as File | null,
    remove_profile_image: false,
    _method: 'PATCH',
});

const submit = () => {
    form.post(settingsRoutes.profile.update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('profile_image', 'remove_profile_image');
        },
    });
};

const removePhoto = () => {
    if (confirm('Are you sure you want to remove your profile photo?')) {
        form.remove_profile_image = true;
        submit();
    }
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your name, username and email address"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Profile Image -->
                    <div class="flex items-center gap-6">
                        <div
                            class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-primary text-2xl font-bold text-primary-foreground"
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
                            <div class="mt-2 flex items-center gap-2">
                                <Input
                                    id="profile_image"
                                    type="file"
                                    accept="image/*"
                                    @change="
                                        (e: any) =>
                                            (form.profile_image =
                                                e.target.files[0])
                                    "
                                    class="w-full"
                                />
                                <Button
                                    v-if="user.profile_image"
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    class="shrink-0 text-red-500 hover:bg-red-500/10 hover:text-red-600"
                                    @click="removePhoto"
                                    title="Remove photo"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Max 2MB. Formats: JPG, PNG
                            </p>
                            <InputError
                                :message="form.errors.profile_image"
                                class="mt-1"
                            />
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="grid gap-2">
                        <Label for="name">Full Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>

                    <!-- Username -->
                    <div class="grid gap-2">
                        <Label for="username">Username</Label>
                        <Input
                            id="username"
                            v-model="form.username"
                            type="text"
                            required
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.username"
                            class="mt-1"
                        />
                    </div>

                    <!-- Email -->
                    <div class="grid gap-2">
                        <Label for="email">Email Address</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.email" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing" type="submit">
                            Save Changes
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="form.recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </form>

                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
