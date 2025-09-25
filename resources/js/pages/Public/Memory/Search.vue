<template>
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800"
    >
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8 text-center">
                <h1
                    class="mb-4 text-3xl font-bold text-gray-900 dark:text-white"
                >
                    Search Public Memories
                </h1>

                <!-- Search Form -->
                <div class="mx-auto max-w-2xl">
                    <form @submit.prevent="performSearch" class="relative mb-4">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search for memories, topics, or keywords..."
                            class="py-3 pr-20 pl-10 text-lg"
                            autofocus
                        />
                        <Search
                            class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400"
                        />
                        <Button
                            type="submit"
                            class="absolute top-1/2 right-2 -translate-y-1/2 transform"
                        >
                            Search
                        </Button>
                    </form>

                    <div class="text-sm text-gray-600 dark:text-gray-300">
                        <p>
                            Search through publicly shared memories and
                            knowledge
                        </p>
                    </div>
                </div>
            </div>

            <!-- Search Results -->
            <div v-if="hasSearched" class="mx-auto max-w-6xl">
                <!-- Results Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">
                        {{ memories.total }}
                        {{ memories.total === 1 ? 'result' : 'results' }}
                        <span v-if="query"> for "{{ query }}"</span>
                    </div>
                    <Link :href="route('memories.public.index')">
                        <Button variant="outline" size="sm">
                            Browse All
                        </Button>
                    </Link>
                </div>

                <!-- Results List -->
                <div v-if="memories.data.length > 0" class="mb-8 space-y-6">
                    <Card
                        v-for="memory in memories.data"
                        :key="memory.id"
                        class="cursor-pointer transition-shadow hover:shadow-lg"
                        @click="viewMemory(memory)"
                    >
                        <CardHeader>
                            <div class="mb-2 flex items-start justify-between">
                                <h3
                                    class="text-xl font-semibold text-gray-900 dark:text-white"
                                >
                                    {{
                                        memory.metadata?.title ||
                                        'Untitled Memory'
                                    }}
                                </h3>
                                <div class="flex gap-2">
                                    <Badge variant="outline">{{
                                        memory.visibility
                                    }}</Badge>
                                    <Badge
                                        v-if="memory.document_type"
                                        variant="secondary"
                                    >
                                        {{ memory.document_type }}
                                    </Badge>
                                </div>
                            </div>

                            <div
                                class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400"
                            >
                                <span
                                    >By
                                    {{ memory.user?.name || 'Anonymous' }}</span
                                >
                                <span>•</span>
                                <span>{{
                                    formatDate(
                                        memory.shared_at || memory.created_at,
                                    )
                                }}</span>
                                <span v-if="memory.project_name">•</span>
                                <Badge
                                    v-if="memory.project_name"
                                    variant="secondary"
                                    size="sm"
                                >
                                    📁 {{ memory.project_name }}
                                </Badge>
                            </div>
                        </CardHeader>

                        <CardContent>
                            <!-- Content Preview -->
                            <p
                                class="mb-4 line-clamp-4 text-gray-700 dark:text-gray-200"
                            >
                                {{ memory.thing_to_remember }}
                            </p>

                            <!-- Tags -->
                            <div
                                v-if="memory.tags && memory.tags.length > 0"
                                class="mb-4"
                            >
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="tag in memory.tags.slice(0, 5)"
                                        :key="tag"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        #{{ tag }}
                                    </Badge>
                                    <Badge
                                        v-if="memory.tags.length > 5"
                                        variant="outline"
                                        size="sm"
                                    >
                                        +{{ memory.tags.length - 5 }} more
                                    </Badge>
                                </div>
                            </div>

                            <!-- Read More Link -->
                            <div class="flex items-center justify-between">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-blue-600 hover:text-blue-800"
                                >
                                    Read Full Memory →
                                </Button>
                                <Button
                                    @click.stop="copyShareUrl(memory)"
                                    variant="ghost"
                                    size="sm"
                                >
                                    <Copy class="mr-1 h-4 w-4" />
                                    Share
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty Results -->
                <div v-else class="py-12 text-center">
                    <SearchX
                        class="mx-auto mb-4 h-16 w-16 text-gray-300 dark:text-gray-600"
                    />
                    <h3
                        class="mb-2 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        No results found
                    </h3>
                    <p class="mb-4 text-gray-600 dark:text-gray-300">
                        No memories found matching "{{ query }}". Try different
                        keywords or browse all public memories.
                    </p>
                    <div class="flex justify-center gap-2">
                        <Button @click="clearSearch" variant="outline">
                            Clear Search
                        </Button>
                        <Link :href="route('memories.public.index')">
                            <Button>Browse All Memories</Button>
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="memories.last_page > 1" class="flex justify-center">
                    <div class="flex gap-2">
                        <Link
                            v-if="memories.prev_page_url"
                            :href="memories.prev_page_url"
                            preserve-scroll
                        >
                            <Button variant="outline">← Previous</Button>
                        </Link>

                        <span
                            class="flex items-center px-4 text-gray-600 dark:text-gray-300"
                        >
                            Page {{ memories.current_page }} of
                            {{ memories.last_page }}
                        </span>

                        <Link
                            v-if="memories.next_page_url"
                            :href="memories.next_page_url"
                            preserve-scroll
                        >
                            <Button variant="outline">Next →</Button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Search Suggestions (when no search performed) -->
            <div v-else class="mx-auto max-w-2xl text-center">
                <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3
                        class="mb-4 text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        Search Tips
                    </h3>
                    <div
                        class="grid gap-4 text-sm text-gray-600 md:grid-cols-2 dark:text-gray-300"
                    >
                        <div>
                            <h4 class="mb-2 font-medium">Try searching for:</h4>
                            <ul class="space-y-1">
                                <li>• Specific topics or keywords</li>
                                <li>• Project names</li>
                                <li>• Technical concepts</li>
                                <li>• Document types</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="mb-2 font-medium">Examples:</h4>
                            <ul class="space-y-1">
                                <li>• "machine learning"</li>
                                <li>• "project setup"</li>
                                <li>• "debugging tips"</li>
                                <li>• "best practices"</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <Link :href="route('memories.public.index')">
                        <Button>Browse All Public Memories</Button>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useToast } from '@/composables/use-toast';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Copy, Search, SearchX } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface User {
    id: number;
    name: string;
}

interface Memory {
    id: number;
    title?: string;
    thing_to_remember: string;
    project_name?: string;
    document_type?: string;
    tags?: string[];
    visibility: 'public' | 'unlisted' | 'private';
    share_token: string;
    shared_at?: string;
    created_at: string;
    user?: User;
    metadata?: {
        title?: string;
    };
}

interface PaginatedMemories {
    data: Memory[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url?: string;
    next_page_url?: string;
}

const props = defineProps<{
    memories: PaginatedMemories;
    query?: string;
}>();

const page = usePage();
const { toast } = useToast();

const searchQuery = ref(props.query || '');
const hasSearched = computed(() => !!props.query);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const viewMemory = (memory: Memory) => {
    router.visit(route('memories.public.show', { memory: memory.share_token }));
};

const performSearch = () => {
    if (searchQuery.value.trim()) {
        router.visit(
            route('memories.public.search', { q: searchQuery.value }),
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }
};

const clearSearch = () => {
    searchQuery.value = '';
    router.visit(route('memories.public.search'));
};

const copyShareUrl = async (memory: Memory) => {
    try {
        const url = route('memories.public.show', {
            memory: memory.share_token,
        });
        await navigator.clipboard.writeText(url);
        toast({
            title: 'Link copied!',
            description: 'Memory share URL has been copied to clipboard.',
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

<style scoped>
.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
