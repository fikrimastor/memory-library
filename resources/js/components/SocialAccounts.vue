<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/vue3';

interface SocialAccount {
    id: number;
    provider: string;
    provider_id: string;
    provider_data: {
        nickname?: string;
        name?: string;
        email?: string;
        avatar?: string;
    };
    created_at: string;
    updated_at: string;
}

interface Props {
    socialAccounts: SocialAccount[];
}

defineProps<Props>();

const processing = ref(false);

const linkGitHub = () => {
    processing.value = true;
    router.post('/auth/github/link', {}, {
        onFinish: () => {
            processing.value = false;
        },
    });
};

const unlinkGitHub = () => {
    processing.value = true;
    router.delete('/auth/github/unlink', {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col space-y-4">
            <h3 class="text-lg font-medium">Social Accounts</h3>
            <p class="text-sm text-muted-foreground">
                Connect your social accounts to enable login with them.
            </p>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between rounded-lg border p-4">
                <div class="flex items-center space-x-4">
                    <div class="rounded-full bg-gray-100 p-2">
                        <svg
                            class="h-6 w-6"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium">GitHub</h4>
                        <p class="text-sm text-muted-foreground">
                            {{
                                socialAccounts && socialAccounts.length > 0
                                    ? 'Connected'
                                    : 'Not connected'
                            }}
                        </p>
                    </div>
                </div>
                <div>
                    <Button
                        v-if="socialAccounts && socialAccounts.length > 0"
                        variant="outline"
                        :disabled="processing"
                        @click="unlinkGitHub(socialAccounts[0].id)"
                    >
                        Unlink
                    </Button>
                    <Button
                        v-else
                        variant="outline"
                        :disabled="processing"
                        @click="linkGitHub"
                    >
                        Link
                    </Button>
                </div>
            </div>
        </div>

        <div class="text-sm text-muted-foreground">
            <p>
                When you link a social account, you can use it to log in to your
                account.
            </p>
        </div>
    </div>
</template>