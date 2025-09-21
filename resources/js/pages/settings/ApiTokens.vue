<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { PlusIcon, TrashIcon } from 'lucide-vue-next';
import { useClipboard } from '@vueuse/core';
import { index as apiTokensIndex, store as apiTokensStore, destroy as apiTokensDestroy } from '@/routes/api-tokens';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface ApiToken {
    id: string;
    name: string;
    scopes: string[];
    created_at: string;
    last_used_at: string | null;
}

interface Props {
    tokens: ApiToken[];
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'API Tokens',
        href: apiTokensIndex().url,
    },
];

// State for create token dialog
const showCreateDialog = ref(false);
const tokenName = ref('');
const createdToken = ref<string | null>(null);
const showTokenDialog = ref(false);

// Clipboard functionality
const { copy, copied, isSupported } = useClipboard();

// State for revoke token dialog
const showRevokeDialog = ref(false);
const tokenToRevoke = ref<ApiToken | null>(null);

// Form processing states
const isCreating = ref(false);
const isRevoking = ref(false);
const createError = ref<string | null>(null);
const revokeError = ref<string | null>(null);

// Create token functionality
const createToken = async () => {
    if (!tokenName.value.trim()) {
        createError.value = 'Token name is required';
        return;
    }

    isCreating.value = true;
    createError.value = null;

    try {
        router.post(apiTokensStore().url, {
            name: tokenName.value.trim(),
        }, {
            onSuccess: (page) => {
                const flashData = page.props.flash as any;
                if (flashData?.token) {
                    createdToken.value = flashData.token;
                    showTokenDialog.value = true;
                }
                showCreateDialog.value = false;
                tokenName.value = '';
            },
            onError: (errors) => {
                createError.value = errors.name || errors.error || 'Failed to create token';
            },
            onFinish: () => {
                isCreating.value = false;
            }
        });
    } catch {
        createError.value = 'Failed to create token';
        isCreating.value = false;
    }
};

// Revoke token functionality
const confirmRevokeToken = (token: ApiToken) => {
    tokenToRevoke.value = token;
    showRevokeDialog.value = true;
};

const revokeToken = async () => {
    if (!tokenToRevoke.value) return;

    isRevoking.value = true;
    revokeError.value = null;

    try {
        router.delete(apiTokensDestroy(tokenToRevoke.value.id).url, {
            onError: (errors) => {
                revokeError.value = errors.error || 'Failed to revoke token';
            },
            onFinish: () => {
                isRevoking.value = false;
                showRevokeDialog.value = false;
                tokenToRevoke.value = null;
            }
        });
    } catch {
        revokeError.value = 'Failed to revoke token';
        isRevoking.value = false;
    }
};

// Utility functions
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getTokenStatus = (token: ApiToken) => {
    return token.last_used_at ? 'Used' : 'Unused';
};


const closeTokenDialog = () => {
    showTokenDialog.value = false;
    createdToken.value = null;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="API Tokens" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <HeadingSmall
                        title="API Tokens"
                        description="Manage API tokens for accessing your account programmatically"
                    />
                    
                    <Dialog v-model:open="showCreateDialog">
                        <DialogTrigger asChild>
                            <Button class="gap-2">
                                <PlusIcon class="size-4" />
                                Create Token
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Create API Token</DialogTitle>
                                <DialogDescription>
                                    Give your token a descriptive name to help you identify it later.
                                </DialogDescription>
                            </DialogHeader>
                            
                            <div class="space-y-4">
                                <div class="grid gap-2">
                                    <Label for="token-name">Token Name</Label>
                                    <Input
                                        id="token-name"
                                        v-model="tokenName"
                                        placeholder="My API Token"
                                        :disabled="isCreating"
                                        @keydown.enter="createToken"
                                    />
                                    <InputError v-if="createError" :message="createError" />
                                </div>
                            </div>

                            <DialogFooter>
                                <Button
                                    variant="outline"
                                    @click="showCreateDialog = false"
                                    :disabled="isCreating"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    @click="createToken"
                                    :disabled="isCreating || !tokenName.trim()"
                                >
                                    {{ isCreating ? 'Creating...' : 'Create Token' }}
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                <!-- Token List -->
                <div class="space-y-4">
                    <div v-if="tokens.length === 0" class="text-center py-12">
                        <div class="text-muted-foreground">
                            <p class="text-sm">No API tokens found.</p>
                            <p class="text-xs mt-1">Create your first token to get started.</p>
                        </div>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="token in tokens"
                            :key="token.id"
                            class="flex items-center justify-between p-4 border rounded-lg bg-card"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-3">
                                    <h4 class="font-medium text-sm">{{ token.name }}</h4>
                                    <Badge
                                        :variant="token.last_used_at ? 'secondary' : 'outline'"
                                        class="text-xs"
                                    >
                                        {{ getTokenStatus(token) }}
                                    </Badge>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Created {{ formatDate(token.created_at) }}
                                    <span v-if="token.last_used_at">
                                        • Last used {{ formatDate(token.last_used_at) }}
                                    </span>
                                </p>
                            </div>

                            <Button
                                variant="destructive"
                                size="sm"
                                @click="confirmRevokeToken(token)"
                                class="gap-2"
                            >
                                <TrashIcon class="size-4" />
                                Revoke
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Show Token Dialog -->
                <Dialog v-model:open="showTokenDialog">
                    <DialogContent class="sm:max-w-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
                        <DialogHeader class="flex-shrink-0">
                            <DialogTitle>API Token Created</DialogTitle>
                            <DialogDescription>
                                Copy your new API token. For security reasons, it won't be shown again.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="space-y-4 flex-1 overflow-y-auto min-h-0">
                            <div class="p-4 bg-muted rounded-lg border">
                                <code class="text-xs sm:text-sm font-mono break-all leading-relaxed block">{{ createdToken }}</code>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Make sure to copy your API token now. You won't be able to see it again!
                            </p>
                        </div>

                        <DialogFooter class="flex-shrink-0 flex-col-reverse sm:flex-row gap-2">
                            <Button
                                variant="outline"
                                @click="copy(createdToken || '')"
                                class="w-full sm:w-auto"
                            >
                                {{ copied ? 'Copied!' : 'Copy Token' }}
                            </Button>
                            <div v-if="!isSupported" class="text-xs text-muted-foreground">
                                Clipboard not supported
                            </div>
                            <Button
                                @click="closeTokenDialog"
                                class="w-full sm:w-auto"
                            >
                                Done
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <!-- Revoke Token Confirmation Dialog -->
                <Dialog v-model:open="showRevokeDialog">
                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Revoke API Token</DialogTitle>
                            <DialogDescription>
                                Are you sure you want to revoke the token "{{ tokenToRevoke?.name }}"? 
                                This action cannot be undone and any applications using this token will lose access.
                            </DialogDescription>
                        </DialogHeader>

                        <div v-if="revokeError" class="p-3 bg-destructive/10 border border-destructive/20 rounded-md">
                            <p class="text-sm text-destructive">{{ revokeError }}</p>
                        </div>

                        <DialogFooter>
                            <Button
                                variant="outline"
                                @click="showRevokeDialog = false"
                                :disabled="isRevoking"
                            >
                                Cancel
                            </Button>
                            <Button
                                variant="destructive"
                                @click="revokeToken"
                                :disabled="isRevoking"
                            >
                                {{ isRevoking ? 'Revoking...' : 'Revoke Token' }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>