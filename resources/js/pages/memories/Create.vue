<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as memoriesIndex } from '@/routes/memories';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Save, X } from 'lucide-vue-next';
import { ref } from 'vue';

// Types
interface MemoryForm {
    title: string;
    thing_to_remember: string;
    document_type: string | null;
    project_name: string | null;
    tags: string[];
}

// Reactive state
const tagInput = ref('');
const isAddingTag = ref(false);

const form = useForm<MemoryForm>({
    title: '',
    thing_to_remember: '',
    document_type: null,
    project_name: null,
    tags: [],
});

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Memories',
        href: memoriesIndex().url,
    },
    {
        title: 'Create Memory',
        href: '#',
    },
];

// Methods
const addTag = (): void => {
    if (tagInput.value.trim() && !form.tags.includes(tagInput.value.trim())) {
        form.tags.push(tagInput.value.trim());
    }
    tagInput.value = '';
    isAddingTag.value = false;
};

const removeTag = (tag: string): void => {
    form.tags = form.tags.filter((t) => t !== tag);
};

const submit = (): void => {
    form.post('/memories', {
        preserveScroll: true,
        onSuccess: () => {
            // Form reset handled by Inertia
        },
        onError: () => {
            // Errors will be displayed by InputError components
        },
    });
};

const cancel = (): void => {
    router.get(memoriesIndex().url);
};
</script>

<template>
    <Head title="Create Memory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center gap-4">
                <Button
                    @click="cancel"
                    variant="outline"
                    size="sm"
                    class="gap-2"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Memories
                </Button>
            </div>

            <Card class="max-w-3xl">
                <CardHeader>
                    <CardTitle class="text-2xl">Create New Memory</CardTitle>
                    <CardDescription>
                        Add a new memory to your library with all relevant
                        details.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <!-- Title -->
                        <div class="space-y-2">
                            <Label for="title">Title *</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                type="text"
                                placeholder="Enter a title for your memory"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <!-- Content -->
                        <div class="space-y-2">
                            <Label for="thing_to_remember">Content *</Label>
                            <Textarea
                                id="thing_to_remember"
                                v-model="form.thing_to_remember"
                                placeholder="Enter the content you want to remember..."
                                :disabled="form.processing"
                                :rows="8"
                                class="min-h-32"
                            />
                            <InputError
                                :message="form.errors.thing_to_remember"
                            />
                        </div>

                        <Separator />

                        <!-- Document Type -->
                        <div class="space-y-2">
                            <Label for="document_type">Document Type</Label>
                            <Input
                                id="document_type"
                                v-model="form.document_type"
                                type="text"
                                placeholder="e.g., PRD, Technical Spec, Meeting Notes"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.document_type" />
                        </div>

                        <!-- Project Name -->
                        <div class="space-y-2">
                            <Label for="project_name">Project Name</Label>
                            <Input
                                id="project_name"
                                v-model="form.project_name"
                                type="text"
                                placeholder="e.g., Memory Library, Project Alpha"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.project_name" />
                        </div>

                        <!-- Tags -->
                        <div class="space-y-2">
                            <Label>Tags</Label>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="tag in form.tags"
                                    :key="tag"
                                    variant="secondary"
                                    class="flex items-center gap-1 py-1 pr-1 pl-3"
                                >
                                    {{ tag }}
                                    <Button
                                        @click="removeTag(tag)"
                                        variant="ghost"
                                        size="sm"
                                        class="h-5 w-5 p-0 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900 dark:hover:text-red-100"
                                    >
                                        <X class="h-3 w-3" />
                                    </Button>
                                </Badge>

                                <div
                                    v-if="isAddingTag"
                                    class="flex items-center gap-2"
                                >
                                    <Input
                                        v-model="tagInput"
                                        type="text"
                                        placeholder="Add a tag..."
                                        class="h-8 w-32"
                                        @keydown.enter.prevent="addTag"
                                        @keydown.escape="isAddingTag = false"
                                        @blur="addTag"
                                    />
                                </div>

                                <Button
                                    v-else
                                    @click="isAddingTag = true"
                                    variant="outline"
                                    size="sm"
                                    class="h-8 gap-1"
                                >
                                    <Plus class="h-4 w-4" />
                                    Add Tag
                                </Button>
                            </div>
                            <InputError :message="form.errors.tags" />
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-between">
                        <Button
                            @click="cancel"
                            variant="outline"
                            :disabled="form.processing"
                        >
                            Cancel
                        </Button>

                        <Button
                            type="submit"
                            class="gap-2"
                            :disabled="form.processing"
                            :class="{
                                'cursor-not-allowed opacity-75':
                                    form.processing,
                            }"
                        >
                            <Save
                                :class="[
                                    'h-4 w-4',
                                    { 'animate-spin': form.processing },
                                ]"
                            />
                            Save Memory
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
