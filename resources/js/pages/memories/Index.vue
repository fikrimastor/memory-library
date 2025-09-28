<script setup lang="ts">
import MemorySharing from '@/components/MemorySharing.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    destroy as memoriesDestroy,
    edit as memoriesEdit,
    index as memoriesIndex,
} from '@/routes/memories';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Calendar,
    Edit,
    Folder,
    MoreVertical,
    Plus,
    RefreshCw,
    Search,
    Share2,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Types
interface Memory {
    id: number;
    title: string;
    thing_to_remember: string;
    document_type: string | null;
    project_name: string | null;
    tags: string[] | null;
    created_at: string;
    updated_at: string;
    visibility: 'private' | 'public' | 'unlisted';
    share_token?: string;
    shared_at?: string;
    share_url?: string;
}

interface PaginatedMemories {
    data: Memory[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    memories: PaginatedMemories;
    search?: string | null;
}

const props = defineProps<Props>();

// Reactive state
const searchInput = ref(props.search || '');
const isRefreshing = ref(false);
const shareDialogOpen = ref(false);
const selectedMemory = ref<Memory | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Memories',
        href: memoriesIndex().url,
    },
];

// Methods
const searchMemory = (): void => {
    router.get(
        memoriesIndex().url,
        {
            search: searchInput.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearSearch = (): void => {
    searchInput.value = '';
    searchMemory();
};

const refresh = (): void => {
    isRefreshing.value = true;
    router.reload({
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
};

const deleteMemory = (memory: Memory): void => {
    const title = memory.title || formatTitleFromParts(memory.document_type, memory.project_name);
    if (
        confirm(
            `Are you sure you want to delete "${title}"? This action cannot be undone.`,
        )
    ) {
        router.delete(memoriesDestroy(memory.id).url, {
            preserveScroll: true,
        });
    }
};

const truncateText = (text: string, limit: number = 150): string => {
    if (text.length <= limit) return text;
    return text.substring(0, limit) + '...';
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatTitleFromParts = (documentType: string | null, projectName: string | null): string => {
    const parts = [documentType, projectName].filter(Boolean);
    return parts
        .join(' ')
        .replace(/[-_]/g, ' ')
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
};

// Methods for sharing
const openShareDialog = (memory: Memory): void => {
    selectedMemory.value = memory;
    shareDialogOpen.value = true;
};

// Computed
const hasMemories = computed(() => props.memories.data.length > 0);
const hasFilters = computed(() => props.search);
</script>

<template>
    <Head title="Memories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold text-slate-900 dark:text-slate-100"
                    >
                        Your Memories
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Manage and browse your stored memories
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        @click="refresh"
                        variant="outline"
                        size="sm"
                        :disabled="isRefreshing"
                        class="gap-2"
                    >
                        <RefreshCw
                            :class="[
                                'h-4 w-4',
                                { 'animate-spin': isRefreshing },
                            ]"
                        />
                        Refresh
                    </Button>

                    <Button as-child class="gap-2">
                        <Link href="/memories/create">
                            <Plus class="h-4 w-4" />
                            Add Memory
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <Search
                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />
                    <Input
                        v-model="searchInput"
                        placeholder="Search memories by title, content, type, or project..."
                        class="pl-10"
                        @keydown.enter="searchMemory"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <Button @click="searchMemory" size="sm"> Search </Button>

                    <Button
                        v-if="hasFilters"
                        @click="clearSearch"
                        variant="outline"
                        size="sm"
                    >
                        Clear
                    </Button>
                </div>
            </div>

            <!-- Results Summary -->
            <div
                v-if="hasMemories"
                class="text-sm text-slate-600 dark:text-slate-400"
            >
                Showing {{ memories.data.length }} of
                {{ memories.total }} memories
                <span v-if="hasFilters">(filtered)</span>
            </div>

            <!-- Memories List -->
            <div
                v-if="hasMemories"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Card
                    v-for="memory in memories.data"
                    :key="memory.id"
                    class="group transition-shadow hover:shadow-md"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between gap-2">
                            <CardTitle class="text-lg leading-tight">
                                {{ memory.title }}
                            </CardTitle>

                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0 opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link
                                            :href="`/memories/${memory.id}`"
                                            class="flex items-center gap-2"
                                        >
                                            <Search class="h-4 w-4" />
                                            View
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child>
                                        <Link
                                            :href="memoriesEdit(memory.id).url"
                                            class="flex items-center gap-2"
                                        >
                                            <Edit class="h-4 w-4" />
                                            Edit
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="openShareDialog(memory)"
                                        class="flex items-center gap-2"
                                    >
                                        <Share2 class="h-4 w-4" />
                                        Share
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="deleteMemory(memory)"
                                        class="flex items-center gap-2 text-red-600 focus:text-red-600"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Meta Information -->
                        <div
                            class="flex flex-wrap items-center gap-2 text-xs text-slate-500"
                        >
                            <div class="flex items-center gap-1">
                                <Calendar class="h-3 w-3" />
                                {{ formatDate(memory.created_at) }}
                            </div>

                            <div
                                v-if="memory.document_type"
                                class="flex items-center gap-1"
                            >
                                <Folder class="h-3 w-3" />
                                {{ memory.document_type }}
                            </div>

                            <div
                                v-if="memory.project_name"
                                class="flex items-center gap-1"
                            >
                                <Folder class="h-3 w-3" />
                                {{ memory.project_name }}
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent class="pt-0">
                        <CardDescription class="mb-3 text-sm leading-relaxed">
                            {{ truncateText(memory.thing_to_remember) }}
                        </CardDescription>

                        <!-- Tags -->
                        <div
                            v-if="memory.tags && memory.tags.length > 0"
                            class="mb-3 flex flex-wrap gap-1"
                        >
                            <Badge
                                v-for="tag in memory.tags.slice(0, 3)"
                                :key="tag"
                                variant="secondary"
                                class="text-xs"
                            >
                                {{ tag }}
                            </Badge>
                            <Badge
                                v-if="memory.tags.length > 3"
                                variant="outline"
                                class="text-xs"
                            >
                                +{{ memory.tags.length - 3 }} more
                            </Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center py-12 text-center"
            >
                <div class="rounded-full bg-slate-100 p-6 dark:bg-slate-800">
                    <Folder class="h-12 w-12 text-slate-400" />
                </div>

                <h3
                    class="mt-4 text-lg font-semibold text-slate-900 dark:text-slate-100"
                >
                    {{ hasFilters ? 'No memories found' : 'No memories yet' }}
                </h3>

                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    {{
                        hasFilters
                            ? 'Try adjusting your search criteria or clear the filters.'
                            : 'Get started by creating your first memory.'
                    }}
                </p>

                <div class="mt-6 flex gap-2">
                    <Button
                        v-if="hasFilters"
                        @click="clearSearch"
                        variant="outline"
                    >
                        Clear Filters
                    </Button>

                    <Button as-child>
                        <Link href="/memories/create" class="gap-2">
                            <Plus class="h-4 w-4" />
                            Add Your First Memory
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="hasMemories && memories.last_page > 1"
                class="flex items-center justify-between border-t border-slate-200 pt-6 dark:border-slate-800"
            >
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Page {{ memories.current_page }} of {{ memories.last_page }}
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="memories.prev_page_url"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="memories.prev_page_url"> Previous </Link>
                    </Button>

                    <Button
                        v-if="memories.next_page_url"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="memories.next_page_url"> Next </Link>
                    </Button>
                </div>
            </div>

            <!-- Share Dialog -->
            <MemorySharing
                v-if="selectedMemory"
                :memory="selectedMemory"
                v-model:open="shareDialogOpen"
            />
        </div>
    </AppLayout>
</template>
