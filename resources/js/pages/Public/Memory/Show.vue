<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { useToast } from '@/composables/use-toast';
import { Copy, Link } from 'lucide-vue-next';

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

const props = defineProps<{
    memory: Memory;
}>();

const memory = computed(() => props.memory);
const displayTitle = computed(() => {
    const title = props.memory.title?.trim();
    if (title) {
        return title;
    }

    const metadataTitle = props.memory.metadata?.title?.trim();
    if (metadataTitle) {
        return metadataTitle;
    }

    return 'Untitled Memory';
});

const renderedContent = computed(() => props.memory.sanitized_content || '');
const sharedAuthor = computed(
    () => props.memory.user?.name || 'Anonymous',
);

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

const copyMemoryContent = async () => {
    try {
        await navigator.clipboard.writeText(
            props.memory.thing_to_remember || '',
        );
        toast({
            title: 'Content copied!',
            description: 'Memory content has been copied to clipboard.',
        });
    } catch (err) {
        toast({
            title: 'Error',
            description: 'Failed to copy memory content.',
            variant: 'destructive',
        });
    }
};
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Header with actions -->
        <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <div class="mx-auto max-w-4xl px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Badge v-if="memory.project_name" variant="secondary" class="text-xs">
                            📁 {{ memory.project_name }}
                        </Badge>
                        <Badge v-if="memory.document_type" variant="outline" class="text-xs">
                            {{ memory.document_type }}
                        </Badge>
                    </div>
                    <div class="flex gap-2">
                        <Button @click="copyShareUrl" variant="outline" size="sm">
                            <Link class="mr-2 h-4 w-4" />
                            Copy Link
                        </Button>
                        <Button @click="copyMemoryContent" size="sm">
                            <Copy class="mr-2 h-4 w-4" />
                            Copy Content
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Container -->
        <div class="mx-auto max-w-4xl px-8 py-12">
            <!-- Document Content -->
            <article class="document-content">
                <div
                    v-if="renderedContent"
                    v-html="renderedContent"
                    class="prose prose-lg prose-gray dark:prose-invert max-w-none"
                />
                <div v-else class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400 text-lg">
                        No content available.
                    </p>
                </div>
            </article>

            <!-- Document Footer -->
            <footer class="mt-16 pt-8 border-t border-gray-200 dark:border-gray-700">
                <!-- Tags -->
                <div v-if="memory.tags && memory.tags.length > 0" class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Tags:
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <Badge
                            v-for="tag in memory.tags"
                            :key="tag"
                            variant="secondary"
                            class="text-xs"
                        >
                            #{{ tag }}
                        </Badge>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <p>
                        Shared by {{ sharedAuthor }} on
                        {{ formatDate(memory.shared_at || memory.created_at) }}
                    </p>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.document-content {
    /* Document-style typography */
    font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
    line-height: 1.7;
    color: #1f2937;
}

.dark .document-content {
    color: #f9fafb;
}

.document-content :deep(h1) {
    font-size: 2.25rem;
    font-weight: 700;
    line-height: 1.2;
    margin: 0 0 1.5rem 0;
    color: #111827;
    font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
}

.document-content :deep(h2) {
    font-size: 1.875rem;
    font-weight: 600;
    line-height: 1.3;
    margin: 2.5rem 0 1rem 0;
    color: #111827;
    font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
}

.document-content :deep(h3) {
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1.4;
    margin: 2rem 0 0.75rem 0;
    color: #111827;
    font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
}

.document-content :deep(h4) {
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.4;
    margin: 1.5rem 0 0.5rem 0;
    color: #111827;
    font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
}

.document-content :deep(p) {
    margin: 0 0 1.25rem 0;
    font-size: 1.125rem;
    line-height: 1.7;
    color: #374151;
}

.document-content :deep(ul) {
    margin: 1.25rem 0;
    padding-left: 1.5rem;
}

.document-content :deep(li) {
    margin: 0.5rem 0;
    font-size: 1.125rem;
    line-height: 1.6;
    color: #374151;
}

.document-content :deep(strong) {
    font-weight: 600;
    color: #111827;
}

.document-content :deep(em) {
    font-style: italic;
}

.document-content :deep(blockquote) {
    border-left: 4px solid #e5e7eb;
    margin: 1.5rem 0;
    padding: 0.5rem 0 0.5rem 1.5rem;
    font-style: italic;
    color: #6b7280;
}

.document-content :deep(code) {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 0.875rem;
    color: #dc2626;
}

.document-content :deep(pre) {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    margin: 1.5rem 0;
    overflow-x: auto;
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.document-content :deep(pre code) {
    background: none;
    padding: 0;
    border-radius: 0;
    color: #374151;
}

/* Dark mode styles */
.dark .document-content :deep(h1),
.dark .document-content :deep(h2),
.dark .document-content :deep(h3),
.dark .document-content :deep(h4) {
    color: #f9fafb;
}

.dark .document-content :deep(p),
.dark .document-content :deep(li) {
    color: #d1d5db;
}

.dark .document-content :deep(strong) {
    color: #f9fafb;
}

.dark .document-content :deep(blockquote) {
    border-left-color: #4b5563;
    color: #9ca3af;
}

.dark .document-content :deep(code) {
    background-color: #374151;
    color: #fbbf24;
}

.dark .document-content :deep(pre) {
    background-color: #1f2937;
    border-color: #374151;
}

.dark .document-content :deep(pre code) {
    color: #d1d5db;
}
</style>
