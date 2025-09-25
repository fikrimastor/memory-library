<template>
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800"
    >
        <div class="container mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8 text-center">
                <h1
                    class="mb-4 text-4xl font-bold text-gray-900 dark:text-white"
                >
                    Public Memory Library
                </h1>
                <p class="mb-6 text-lg text-gray-600 dark:text-gray-300">
                    Discover knowledge and insights shared by our community
                </p>

                <!-- Search -->
                <div class="mx-auto max-w-md">
                    <form @submit.prevent="search" class="relative">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search memories..."
                            class="pr-4 pl-10"
                        />
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400"
                        />
                        <Button
                            type="submit"
                            size="sm"
                            class="absolute top-1/2 right-2 -translate-y-1/2 transform"
                        >
                            Search
                        </Button>
                    </form>
                </div>
            </div>

            <!-- Results -->
            <div v-if="memories.data.length > 0" class="mx-auto max-w-6xl">
                <!-- Results Count -->
                <div class="mb-6 text-gray-600 dark:text-gray-300">
                    {{ memories.total }}
                    {{ memories.total === 1 ? 'memory' : 'memories' }} found
                    <span v-if="query"> for "{{ query }}"</span>
                </div>

                <!-- Memory Grid -->
                <div class="mb-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="memory in memories.data"
                        :key="memory.id"
                        class="cursor-pointer transition-shadow hover:shadow-lg"
                        @click="viewMemory(memory)"
                    >
                        <CardHeader>
                            <div class="mb-2 flex items-start justify-between">
                                <h3
                                    class="line-clamp-2 font-semibold text-gray-900 dark:text-white"
                                >
                                    {{
                                        memory.metadata?.title ||
                                        'Untitled Memory'
                                    }}
                                </h3>
                                <Badge
                                    variant="outline"
                                    class="ml-2 flex-shrink-0"
                                >
                                    {{ memory.visibility }}
                                </Badge>
                            </div>

                            <div class="mb-3 flex flex-wrap gap-1">
                                <Badge
                                    v-if="memory.project_name"
                                    variant="secondary"
                                    size="sm"
                                >
                                    📁 {{ memory.project_name }}
                                </Badge>
                                <Badge
                                    v-if="memory.document_type"
                                    variant="outline"
                                    size="sm"
                                >
                                    {{ memory.document_type }}
                                </Badge>
                            </div>
                        </CardHeader>

                        <CardContent>
                            <!-- Content Preview -->
                            <p
                                class="mb-3 line-clamp-3 text-sm text-gray-600 dark:text-gray-300"
                            >
                                {{ memory.thing_to_remember }}
                            </p>

                            <!-- Tags -->
                            <div
                                v-if="memory.tags && memory.tags.length > 0"
                                class="mb-3"
                            >
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="tag in memory.tags.slice(0, 3)"
                                        :key="tag"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        #{{ tag }}
                                    </Badge>
                                    <Badge
                                        v-if="memory.tags.length > 3"
                                        variant="outline"
                                        size="sm"
                                    >
                                        +{{ memory.tags.length - 3 }}
                                    </Badge>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div
                                class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400"
                            >
                                <span>{{
                                    memory.user?.name || 'Anonymous'
                                }}</span>
                                <span>{{
                                    formatDate(
                                        memory.shared_at || memory.created_at,
                                    )
                                }}</span>
                            </div>
                        </CardContent>
                    </Card>
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

            <!-- Empty State -->
            <div v-else class="py-12 text-center">
                <div class="mx-auto max-w-md">
                    <BookOpen
                        class="mx-auto mb-4 h-16 w-16 text-gray-300 dark:text-gray-600"
                    />
                    <h3
                        class="mb-2 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{
                            query
                                ? 'No memories found'
                                : 'No public memories yet'
                        }}
                    </h3>
                    <p class="mb-4 text-gray-600 dark:text-gray-300">
                        {{
                            query
                                ? `No memories found matching "${query}". Try different keywords.`
                                : 'Be the first to share your knowledge with the community!'
                        }}
                    </p>
                    <Button v-if="query" @click="clearSearch" variant="outline">
                        Clear Search
                    </Button>
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
import { Link, router } from '@inertiajs/vue3';
import { BookOpen, Search } from 'lucide-vue-next';
import { ref } from 'vue';

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

const searchQuery = ref(props.query || '');

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

const search = () => {
    if (searchQuery.value.trim()) {
        router.visit(
            route('memories.public.index', { query: searchQuery.value }),
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }
};

const clearSearch = () => {
    searchQuery.value = '';
    router.visit(route('memories.public.index'), {
        preserveState: true,
    });
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
