<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Plus,
    Pencil,
    Trash2,
    Wallet
} from 'lucide-vue-next';

interface Platform {
    id: number;
    name: string;
}

const props = defineProps<{
    platforms: Platform[];
}>();

const breadcrumbs = [
    { title: 'Wallets', href: '/platforms' },
];

const showModal = ref(false);
const editingPlatform = ref<Platform | null>(null);

const form = useForm({
    name: '',
});

const openAddModal = () => {
    editingPlatform.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (platform: Platform) => {
    editingPlatform.value = platform;
    form.name = platform.name;
    showModal.value = true;
};

const submit = () => {
    if (editingPlatform.value) {
        form.put(`/platforms/${editingPlatform.value.id}`, {
            onSuccess: () => showModal.value = false,
        });
    } else {
        form.post('/platforms', {
            onSuccess: () => showModal.value = false,
        });
    }
};

const deletePlatform = (id: number) => {
    if (confirm('Delete this wallet? Transactions associated with it might be affected.')) {
        router.delete(`/platforms/${id}`);
    }
};
</script>

<template>
    <Head title="Wallets" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-8 p-8 pt-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-white/90">Wallets & Platforms</h2>
                    <p class="text-muted-foreground">Manage your payment methods.</p>
                </div>
                <Button class="bg-orange-600 hover:bg-orange-700 text-white" @click="openAddModal">
                    <Plus class="mr-2 h-4 w-4" /> Add Wallet
                </Button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div v-for="platform in platforms" :key="platform.id" 
                    class="group relative overflow-hidden rounded-xl border border-zinc-800 bg-black p-6 transition-all hover:border-zinc-700 hover:shadow-md flex flex-col justify-between h-32"
                >
                    <div class="flex justify-between items-start">
                        <div class="h-10 w-10 rounded-full flex items-center justify-center bg-zinc-900 border border-zinc-800 text-blue-500">
                            <Wallet class="h-5 w-5" />
                        </div>
                        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <Button @click="openEditModal(platform)" size="icon" variant="ghost" class="h-8 w-8 text-zinc-400 hover:text-white hover:bg-zinc-800">
                                <Pencil class="h-4 w-4" />
                            </Button>
                            <Button @click="deletePlatform(platform.id)" size="icon" variant="ghost" class="h-8 w-8 text-zinc-400 hover:text-red-500 hover:bg-red-500/10">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-lg tracking-tight">{{ platform.name }}</h3>
                        <p class="text-xs text-zinc-500">Payment Method</p>
                    </div>
                    
                    <!-- Decor -->
                     <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-gradient-to-br from-zinc-800 to-transparent opacity-10 rounded-full blur-2xl pointer-events-none"></div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
             <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showModal = false"></div>
             <div class="relative z-50 w-full max-w-md bg-zinc-950 border border-zinc-800 p-6 rounded-xl shadow-2xl">
                <h3 class="text-lg font-semibold text-white mb-4">
                    {{ editingPlatform ? 'Edit Wallet' : 'New Wallet' }}
                </h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <Label class="text-zinc-400">Name</Label>
                        <Input v-model="form.name" class="bg-black border-zinc-800 text-white" placeholder="e.g. BCA, GoPay, Cash" required />
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <Button type="button" variant="ghost" @click="showModal = false" class="text-zinc-400 hover:text-white">Cancel</Button>
                        <Button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white" :disabled="form.processing">Save</Button>
                    </div>
                </form>
             </div>
        </div>

    </AppLayout>
</template>
