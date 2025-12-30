<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import { Folder, Pencil, Plus, Trash2 } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    color?: string;
    icon?: string;
}

const props = defineProps<{
    categories: Category[];
}>();

const breadcrumbs = [{ title: 'Categories', href: '/categories' }];

const activeTab = ref<'income' | 'expense'>('income');
const showModal = ref(false);
const editingCategory = ref<Category | null>(null);

const form = useForm({
    name: '',
    type: 'income' as 'income' | 'expense',
    color: '#ea580c',
});

const filteredCategories = computed(() => {
    return props.categories.filter((c) => c.type === activeTab.value);
});

const openAddModal = () => {
    editingCategory.value = null;
    form.reset();
    form.type = activeTab.value;
    showModal.value = true;
};

const openEditModal = (category: Category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.type = category.type;
    form.color = category.color || '#ea580c';
    showModal.value = true;
};

const submit = () => {
    if (editingCategory.value) {
        form.put(`/categories/${editingCategory.value.id}`, {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post('/categories', {
            onSuccess: () => (showModal.value = false),
        });
    }
};

const deleteCategory = (id: number) => {
    if (confirm('Delete this category?')) {
        router.delete(`/categories/${id}`);
    }
};
</script>

<template>
    <Head title="Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 space-y-8 p-8 pt-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-white/90">
                        Categories
                    </h2>
                    <p class="text-muted-foreground">
                        Organize your financial flows.
                    </p>
                </div>
                <Button
                    class="bg-orange-600 text-white hover:bg-orange-700"
                    @click="openAddModal"
                >
                    <Plus class="mr-2 h-4 w-4" /> Add Category
                </Button>
            </div>

            <!-- Tabs -->
            <div
                class="flex w-fit space-x-1 rounded-xl border border-zinc-800 bg-zinc-900/50 p-1"
            >
                <button
                    v-for="tab in ['income', 'expense']"
                    :key="tab"
                    @click="activeTab = tab as 'income' | 'expense'"
                    class="rounded-lg px-4 py-2 text-sm font-medium capitalize transition-all"
                    :class="
                        activeTab === tab
                            ? 'bg-zinc-800 text-white shadow'
                            : 'text-zinc-500 hover:text-zinc-300'
                    "
                >
                    {{ tab }}
                </button>
            </div>

            <!-- Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="category in filteredCategories"
                    :key="category.id"
                    class="group relative overflow-hidden rounded-xl border border-zinc-800 bg-black p-6 transition-all hover:border-zinc-700 hover:shadow-md"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full border border-zinc-800 bg-zinc-900 text-orange-500"
                            >
                                <Folder class="h-5 w-5" />
                            </div>
                            <span class="font-semibold text-white">{{
                                category.name
                            }}</span>
                        </div>
                        <div
                            class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            <Button
                                @click="openEditModal(category)"
                                size="icon"
                                variant="ghost"
                                class="h-8 w-8 text-zinc-400 hover:bg-zinc-800 hover:text-white"
                            >
                                <Pencil class="h-4 w-4" />
                            </Button>
                            <Button
                                @click="deleteCategory(category.id)"
                                size="icon"
                                variant="ghost"
                                class="h-8 w-8 text-zinc-400 hover:bg-red-500/10 hover:text-red-500"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>
                <!-- Empty State -->
                <div
                    v-if="filteredCategories.length === 0"
                    class="col-span-full rounded-xl border border-dashed border-zinc-800 py-12 text-center"
                >
                    <p class="text-muted-foreground">
                        No {{ activeTab }} categories found.
                    </p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div
                class="fixed inset-0 bg-black/80 backdrop-blur-sm"
                @click="showModal = false"
            ></div>
            <div
                class="relative z-50 w-full max-w-md rounded-xl border border-zinc-800 bg-zinc-950 p-6 shadow-2xl"
            >
                <h3 class="mb-4 text-lg font-semibold text-white">
                    {{ editingCategory ? 'Edit Category' : 'New Category' }}
                </h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <Label class="text-zinc-400">Name</Label>
                        <Input
                            v-model="form.name"
                            class="border-zinc-800 bg-black text-white"
                            required
                        />
                    </div>
                    <div>
                        <Label class="text-zinc-400">Type</Label>
                        <select
                            v-model="form.type"
                            class="h-9 w-full rounded-md border border-zinc-800 bg-black px-3 py-1 text-sm text-white"
                            disabled
                        >
                            <!-- Locked to tab context for simplicity -->
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="ghost"
                            @click="showModal = false"
                            class="text-zinc-400 hover:text-white"
                            >Cancel</Button
                        >
                        <Button
                            type="submit"
                            class="bg-orange-600 text-white hover:bg-orange-700"
                            :disabled="form.processing"
                            >Save</Button
                        >
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
