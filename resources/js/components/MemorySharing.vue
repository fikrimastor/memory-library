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
import { router, usePage } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { store as apiTokensStore } from '@/routes/api-tokens';

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
}

interface Props {
    memory: Memory;
    open?: boolean;
}

const props = defineProps<Props>();
const page = usePage();
const emit = defineEmits<{
    'update:open': [value: boolean];
    updated: [memory: Memory];
}>();

const { toast } = useToast();
const isOpen = ref(props.open || false);

const shareUrl = computed(() => {
    if (props.memory.visibility === 'private' || !props.memory.share_token) {
        return null;
    }
    return `${window.location.origin}/share/${props.memory.share_token}`;
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

const updateVisibility = async (
    visibility: 'private' | 'public' | 'unlisted',
) => {
    if (props.memory.visibility === visibility) return;

    router.post(`/memories/${props.memory.id}/share/${visibility}`,
        {},
        {
            preserveScroll: true,
            onSuccess: (page) => {
                // const flashData = page.props.flash as any;
                // if (flashData?.token) {
                //     createdToken.value = flashData.token;
                //     showTokenDialog.value = true;
                // }
                // showCreateDialog.value = false;
                // tokenName.value = '';
                // // Update the memory object with the data from the backend response
                // const updatedMemory = {
                //     ...props.memory,
                //     visibility: data.visibility,
                //     share_token: data.share_token,
                //     shared_at:
                //         visibility !== 'private' ? new Date().toISOString() : null,
                // };
                //
                // emit('updated', updatedMemory);

                toast({
                    title: 'Visibility updated',
                    description: page.props.flash as any || `Memory is now ${visibility}.`,
                });
            },
            onError: (errors) => {
                console.error('Visibility update error:', errors.name || errors.error);
                toast({
                    title: 'Error',
                    description: errors.name || errors.error || 'Failed to update memory visibility.',
                    variant: 'destructive',
                });
            },
            onFinish: () => {
                // Refresh the page to get updated data
                router.reload({ only: ['memories'] });
            },
        },
    );
};

const copyShareUrl = async () => {
    if (!shareUrl.value) return;

    try {
        await navigator.clipboard.writeText(shareUrl.value);
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
                <!-- Current Status -->
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Badge
                                :variant="
                                    memory.visibility === 'private'
                                        ? 'secondary'
                                        : 'default'
                                "
                            >
                                {{
                                    memory.visibility === 'private'
                                        ? '🔒'
                                        : memory.visibility === 'public'
                                          ? '🌍'
                                          : '🔗'
                                }}
                                {{ memory.visibility }}
                            </Badge>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{
                                memory.visibility === 'private'
                                    ? 'Only you can see this'
                                    : memory.visibility === 'public'
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
                            :class="{
                                'border-blue-200 bg-blue-50 dark:bg-blue-950':
                                    memory.visibility === 'private',
                            }"
                        >
                            🔒 Private - Only you can see this
                        </Button>

                        <Button
                            @click="updateVisibility('unlisted')"
                            variant="outline"
                            size="sm"
                            class="w-full justify-start"
                            :class="{
                                'border-blue-200 bg-blue-50 dark:bg-blue-950':
                                    memory.visibility === 'unlisted',
                            }"
                        >
                            🔗 Unlisted - Anyone with the link can view
                        </Button>

                        <Button
                            @click="updateVisibility('public')"
                            variant="outline"
                            size="sm"
                            class="w-full justify-start"
                            :class="{
                                'border-blue-200 bg-blue-50 dark:bg-blue-950':
                                    memory.visibility === 'public',
                            }"
                        >
                            🌍 Public - Anyone can find and view this
                        </Button>
                    </div>
                </div>

                <!-- Share Link (if shared) -->
                <div
                    v-if="memory.visibility !== 'private' && shareUrl"
                    class="space-y-2"
                >
                    <div class="text-sm font-medium">Share Link</div>
                    <div class="flex gap-2">
                        <Input :value="shareUrl" readonly class="flex-1" />
                        <Button
                            @click="copyShareUrl"
                            variant="outline"
                            size="sm"
                        >
                            <Copy class="h-4 w-4" />
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
