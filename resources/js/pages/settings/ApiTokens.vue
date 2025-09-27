<script setup lang="ts">
import {
    destroy as apiTokensDestroy,
    index as apiTokensIndex,
    store as apiTokensStore,
} from '@/routes/api-tokens';
import { Head, router } from '@inertiajs/vue3';
import { useClipboard } from '@vueuse/core';
import { PlusIcon, TrashIcon } from 'lucide-vue-next';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface ApiToken {
    id: string;
    name: string;
    scopes: string[];
    created_at: string;
    expires_at: string | null;
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
        router.post(
            apiTokensStore().url,
            {
                name: tokenName.value.trim(),
            },
            {
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
                    createError.value =
                        errors.name || errors.error || 'Failed to create token';
                },
                onFinish: () => {
                    isCreating.value = false;
                },
            },
        );
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
            },
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
                                    Give your token a descriptive name to help
                                    you identify it later.
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
                                    <InputError
                                        v-if="createError"
                                        :message="createError"
                                    />
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
                                    {{
                                        isCreating
                                            ? 'Creating...'
                                            : 'Create Token'
                                    }}
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                <!-- Token List -->
                <div class="space-y-4">
                    <div v-if="tokens.length === 0" class="py-12 text-center">
                        <div class="text-muted-foreground">
                            <p class="text-sm">No API tokens found.</p>
                            <p class="mt-1 text-xs">
                                Create your first token to get started.
                            </p>
                        </div>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="token in tokens"
                            :key="token.id"
                            class="flex items-center justify-between rounded-lg border bg-card p-4"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-3">
                                    <h4 class="text-sm font-medium">
                                        {{ token.name }}
                                    </h4>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Created {{ formatDate(token.created_at) }}
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
                    <DialogContent
                        class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden sm:max-w-2xl"
                    >
                        <DialogHeader class="flex-shrink-0">
                            <DialogTitle>API Token Created</DialogTitle>
                            <DialogDescription>
                                Copy your new API token. For security reasons,
                                it won't be shown again.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="min-h-0 flex-1 space-y-4 overflow-y-auto">
                            <div
                                v-if="($page.props.flash as any)?.token"
                                class="rounded-lg border bg-muted p-4"
                            >
                                <code
                                    class="block font-mono text-xs leading-relaxed break-all sm:text-sm"
                                    >{{
                                        ($page.props.flash as any)?.token
                                    }}</code
                                >
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Make sure to copy your API token now. You won't
                                be able to see it again!
                            </p>
                        </div>

                        <DialogFooter
                            class="flex-shrink-0 flex-col-reverse gap-2 sm:flex-row"
                        >
                            <Button
                                variant="outline"
                                @click="
                                    copy(
                                        ($page.props.flash as any)?.token || '',
                                    )
                                "
                                class="w-full sm:w-auto"
                            >
                                {{ copied ? 'Copied!' : 'Copy Token' }}
                            </Button>

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
                                Are you sure you want to revoke the token "{{
                                    tokenToRevoke?.name
                                }}"? This action cannot be undone and any
                                applications using this token will lose access.
                            </DialogDescription>
                        </DialogHeader>

                        <div
                            v-if="revokeError"
                            class="rounded-md border border-destructive/20 bg-destructive/10 p-3"
                        >
                            <p class="text-sm text-destructive">
                                {{ revokeError }}
                            </p>
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
                                {{
                                    isRevoking ? 'Revoking...' : 'Revoke Token'
                                }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
