<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useToast } from '@/composables/use-toast';
import { router } from '@inertiajs/vue3';
import { useClipboard } from '@vueuse/core';
import { Loader2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Memory {
    id: number;
    title: string;
    thing_to_remember: string;
    document_type: string | null;
    project_name: string | null;
    tags: string[] | null;
    created_at: string;
    updated_at: string;
    visibility: 'private' | 'public';
    share_token?: string | null;
    shared_at?: string | null;
    share_url?: string | null;
}

interface Props {
    memory: Memory;
    open?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
    updated: [memory: Memory];
}>();

const { toast } = useToast();
const isOpen = ref(props.open || false);
const memoryState = ref<Memory>({ ...props.memory });
const isUpdating = ref(false);
const pendingVisibility = ref<Memory['visibility'] | null>(null);
const statusMessage = ref<string | null>(null);
const statusVariant = ref<'success' | 'error'>('success');

// Clipboard functionality
const { copy, copied, isSupported } = useClipboard();

const isShareable = computed(() => {
    const current = memoryState.value;

    return current.visibility === 'public' && current.share_token;
});

watch(
    () => props.open,
    (newValue) => {
        isOpen.value = newValue || false;
    },
);

watch(isOpen, (newValue) => {
    emit('update:open', newValue);
});

watch(
    () => props.memory,
    (newMemory) => {
        memoryState.value = { ...newMemory };
    },
    { deep: true },
);

const refreshSharingInfo = async (): Promise<void> => {
    const response = await fetch(
        `/memories/${memoryState.value.id}/sharing-info`,
        {
            headers: {
                Accept: 'application/json',
            },
        },
    );

    if (!response.ok) {
        throw new Error('Failed to fetch updated sharing info.');
    }

    const data = await response.json();

    memoryState.value = {
        ...memoryState.value,
        visibility: data.visibility,
        share_token: data.share_token ?? undefined,
        share_url: data.share_url ?? undefined,
        shared_at:
            data.visibility === 'private'
                ? null
                : (data.shared_at ?? memoryState.value.shared_at ?? null),
    };
};

const updateVisibility = async (visibility: 'private' | 'public') => {
    if (memoryState.value.visibility === visibility || isUpdating.value) return;

    const previousState = { ...memoryState.value };

    memoryState.value = {
        ...memoryState.value,
        visibility,
        share_token:
            visibility === 'private'
                ? undefined
                : memoryState.value.share_token,
        shared_at:
            visibility === 'private'
                ? null
                : (memoryState.value.shared_at ?? null),
    };

    pendingVisibility.value = visibility;
    statusMessage.value = null;
    statusVariant.value = 'success';
    isUpdating.value = true;

    router.post(
        `/memories/${props.memory.id}/share/${visibility}`,
        {},
        {
            preserveScroll: true,
            onSuccess: async (page) => {
                try {
                    await refreshSharingInfo();
                    const flash = (
                        page.props.flash as Record<string, string | undefined>
                    )?.success;
                    const message =
                        flash ||
                        `Memory is now ${memoryState.value.visibility}.`;

                    statusVariant.value = 'success';
                    statusMessage.value = message;
                    emit('updated', { ...memoryState.value });

                    toast({
                        title: 'Visibility updated',
                        description: message,
                    });
                } catch (error) {
                    memoryState.value = previousState;
                    statusVariant.value = 'error';
                    statusMessage.value =
                        'Visibility updated, but we could not refresh the latest sharing info. Please try again.';
                }
            },
            onError: (errors) => {
                memoryState.value = previousState;
                statusVariant.value = 'error';
                statusMessage.value =
                    errors.name ||
                    errors.error ||
                    'Failed to update memory visibility.';
                toast({
                    title: 'Error',
                    description: statusMessage.value,
                    variant: 'destructive',
                });
            },
            onFinish: () => {
                isUpdating.value = false;
                pendingVisibility.value = null;
            },
        },
    );
};
</script>
<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Share Memory</DialogTitle>
                <DialogDescription>
                    Make your memory public or generate a shareable link.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <div
                    v-if="statusMessage"
                    :class="[
                        'rounded-md border p-3 text-sm',
                        statusVariant === 'error'
                            ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/40 dark:bg-red-950/50 dark:text-red-200'
                            : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/50 dark:text-emerald-200',
                    ]"
                >
                    {{ statusMessage }}
                </div>

                <!-- Current Status -->
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Badge
                                :variant="
                                    memoryState.visibility === 'private'
                                        ? 'secondary'
                                        : 'default'
                                "
                            >
                                {{
                                    memoryState.visibility === 'private'
                                        ? '🔒'
                                        : memoryState.visibility === 'public'
                                          ? '🌍'
                                          : '🔗'
                                }}
                                {{ memoryState.visibility }}
                            </Badge>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{
                                memoryState.visibility === 'private'
                                    ? 'Only you can see this'
                                    : memoryState.visibility === 'public'
                                      ? 'Anyone can find this'
                                      : 'Anyone with link can view'
                            }}
                        </div>
                    </div>
                </div>

                <!-- Sharing Options -->
                <div class="space-y-3">
                    <div class="text-sm font-medium">Visibility Options</div>

                    <div class="space-y-2">
                        <Button
                            @click="updateVisibility('private')"
                            variant="outline"
                            size="sm"
                            class="w-full justify-start"
                            :disabled="isUpdating"
                            :class="{
                                'border-blue-200 bg-blue-50 dark:bg-blue-950':
                                    memoryState.visibility === 'private',
                            }"
                        >
                            <Loader2
                                v-if="pendingVisibility === 'private'"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            🔒 Private - Only you can see this
                        </Button>

                        <Button
                            @click="updateVisibility('public')"
                            variant="outline"
                            size="sm"
                            class="w-full justify-start"
                            :disabled="isUpdating"
                            :class="{
                                'border-blue-200 bg-blue-50 dark:bg-blue-950':
                                    memoryState.visibility === 'public',
                            }"
                        >
                            <Loader2
                                v-if="pendingVisibility === 'public'"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            🌍 Public - Anyone can find and view this
                        </Button>
                    </div>
                </div>

                <!-- Share Link (if shared) -->
                <div v-if="isShareable" class="space-y-2">
                    <div class="text-sm font-medium">Share Link</div>
                    <div class="flex gap-2">
                        <Input
                            :model-value="memoryState.share_url || ''"
                            readonly
                            class="flex-1 text-current dark:text-white"
                        />
                        <Button
                            variant="outline"
                            v-bind:disabled="!isSupported"
                            @click="copy(memoryState.share_url || '')"
                            class="w-full sm:w-auto"
                        >
                            {{ copied ? 'Copied!' : 'Copy' }}
                        </Button>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button @click="isOpen = false" variant="outline">
                    Close
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
