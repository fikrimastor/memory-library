<template>
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800"
    >
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8 text-center">
                <h1
                    class="mb-2 text-3xl font-bold text-gray-900 dark:text-white"
                >
                    Shared Memory
                </h1>
                <p class="text-gray-600 dark:text-gray-300">
                    Publicly shared from {{ memory.user?.name || 'Anonymous' }}
                </p>
            </div>

            <!-- Memory Card -->
            <div class="mx-auto max-w-4xl">
                <Card class="shadow-lg">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div>
                                <h2
                                    class="mb-2 text-2xl font-semibold text-gray-900 dark:text-white"
                                >
                                    {{
                                        memory.metadata?.title ||
                                        'Untitled Memory'
                                    }}
                                </h2>
                                <div class="mb-4 flex flex-wrap gap-2">
                                    <Badge
                                        v-if="memory.project_name"
                                        variant="secondary"
                                    >
                                        📁 {{ memory.project_name }}
                                    </Badge>
                                    <Badge
                                        v-if="memory.document_type"
                                        variant="outline"
                                    >
                                        {{ memory.document_type }}
                                    </Badge>
                                    <Badge variant="outline">
                                        👁️ {{ memory.visibility }}
                                    </Badge>
                                </div>
                            </div>
                            <Button
                                @click="copyShareUrl"
                                variant="outline"
                                size="sm"
                            >
                                <Copy class="mr-2 h-4 w-4" />
                                Copy Link
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <!-- Memory Content -->
                        <div
                            class="prose prose-lg dark:prose-invert mb-6 max-w-none"
                        >
                            <div
                                class="whitespace-pre-wrap text-gray-900 dark:text-gray-100"
                            >
                                {{
                                    memory.sanitized_content ||
                                    memory.thing_to_remember
                                }}
                            </div>
                        </div>

                        <!-- Tags -->
                        <div
                            v-if="memory.tags && memory.tags.length > 0"
                            class="mb-6"
                        >
                            <h4
                                class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Tags:
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="tag in memory.tags"
                                    :key="tag"
                                    variant="secondary"
                                >
                                    #{{ tag }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Metadata -->
                        <div
                            class="border-t pt-4 text-sm text-gray-500 dark:text-gray-400"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    >Shared
                                    {{
                                        formatDate(
                                            memory.shared_at ||
                                                memory.created_at,
                                        )
                                    }}</span
                                >
                                <Link
                                    :href="route('memories.public.index')"
                                    class="hover:underline"
                                >
                                    View more public memories →
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Back to public memories -->
            <div class="mt-8 text-center">
                <Link :href="route('memories.public.index')">
                    <Button variant="outline">
                        ← Back to Public Memories
                    </Button>
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useToast } from '@/composables/use-toast';
import { Link, usePage } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
}

interface Memory {
    id: number;
    title?: string;
    thing_to_remember: string;
    sanitized_content?: string;
    project_name?: string;
    document_type?: string;
    tags?: string[];
    visibility: 'public' | 'unlisted' | 'private';
    share_token?: string;
    shared_at?: string;
    created_at: string;
    user?: User;
    metadata?: {
        title?: string;
    };
}

defineProps<{
    memory: Memory;
}>();

const page = usePage();
const { toast } = useToast();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const copyShareUrl = async () => {
    try {
        await navigator.clipboard.writeText(window.location.href);
        toast({
            title: 'Link copied!',
            description: 'Share URL has been copied to clipboard.',
        });
    } catch (err) {
        toast({
            title: 'Error',
            description: 'Failed to copy link to clipboard.',
            variant: 'destructive',
        });
    }
};
</script>
