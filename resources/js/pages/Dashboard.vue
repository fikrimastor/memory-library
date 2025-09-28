<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

// Types
interface Client {
    id: string;
    name: string;
    description: string;
    icon: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Reactive state
const uuid = ref<string>('');
const copiedStates = ref<Record<string, boolean>>({});
const activeTab = ref<string>('cursor');

// Configuration for different AI clients
const clients: Client[] = [
    {
        id: 'cursor',
        name: 'Cursor',
        description: 'VS Code fork with AI features',
        icon: `<svg
                  height="1.2em"
                  style="flex: none; line-height: 1"
                  viewBox="0 0 24 24"
                  width="1.2em"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <title>Cursor</title>
                  <path
                    d="M11.925 24l10.425-6-10.425-6L1.5 18l10.425 6z"
                    fill="url(#lobe-icons-cursorundefined-fill-0)"
                  ></path>
                  <path d="M22.35 18V6L11.925 0v12l10.425 6z" fill="url(#lobe-icons-cursorundefined-fill-1)"></path>
                  <path d="M11.925 0L1.5 6v12l10.425-6V0z" fill="url(#lobe-icons-cursorundefined-fill-2)"></path>
                  <path d="M22.35 6L11.925 24V12L22.35 6z" fill="#555"></path>
                  <path d="M22.35 6l-10.425 6L1.5 6h20.85z" fill="#000"></path>
                  <defs>
                    <linearGradient
                      gradientUnits="userSpaceOnUse"
                      id="lobe-icons-cursorundefined-fill-0"
                      x1="11.925"
                      x2="11.925"
                      y1="12"
                      y2="24"
                    >
                      <stop offset=".16" stop-color="#000" stop-opacity=".39"></stop>
                      <stop offset=".658" stop-color="#000" stop-opacity=".8"></stop>
                    </linearGradient>
                    <linearGradient
                      gradientUnits="userSpaceOnUse"
                      id="lobe-icons-cursorundefined-fill-1"
                      x1="22.35"
                      x2="11.925"
                      y1="6.037"
                      y2="12.15"
                    >
                      <stop offset=".182" stop-color="#000" stop-opacity=".31"></stop>
                      <stop offset=".715" stop-color="#000" stop-opacity="0"></stop>
                    </linearGradient>
                    <linearGradient
                      gradientUnits="userSpaceOnUse"
                      id="lobe-icons-cursorundefined-fill-2"
                      x1="11.925"
                      x2="1.5"
                      y1="0"
                      y2="18"
                    >
                      <stop stop-color="#000" stop-opacity=".6"></stop>
                      <stop offset=".667" stop-color="#000" stop-opacity=".22"></stop>
                    </linearGradient>
                  </defs>
                </svg>`,
    },
    {
        id: 'claude',
        name: 'Claude',
        description: "Anthropic's desktop application",
        icon: `<svg
                  height="1.2em"
                  style="flex: none; line-height: 1"
                  viewBox="0 0 24 24"
                  width="1.2em"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <title>Claude</title>
                  <path
                    d="M4.709 15.955l4.72-2.647.08-.23-.08-.128H9.2l-.79-.048-2.698-.073-2.339-.097-2.266-.122-.571-.121L0 11.784l.055-.352.48-.321.686.06 1.52.103 2.278.158 1.652.097 2.449.255h.389l.055-.157-.134-.098-.103-.097-2.358-1.596-2.552-1.688-1.336-.972-.724-.491-.364-.462-.158-1.008.656-.722.881.06.225.061.893.686 1.908 1.476 2.491 1.833.365.304.145-.103.019-.073-.164-.274-1.355-2.446-1.446-2.49-.644-1.032-.17-.619a2.97 2.97 0 01-.104-.729L6.283.134 6.696 0l.996.134.42.364.62 1.414 1.002 2.229 1.555 3.03.456.898.243.832.091.255h.158V9.01l.128-1.706.237-2.095.23-2.695.08-.76.376-.91.747-.492.584.28.48.685-.067.444-.286 1.851-.559 2.903-.364 1.942h.212l.243-.242.985-1.306 1.652-2.064.73-.82.85-.904.547-.431h1.033l.76 1.129-.34 1.166-1.064 1.347-.881 1.142-1.264 1.7-.79 1.36.073.11.188-.02 2.856-.606 1.543-.28 1.841-.315.833.388.091.395-.328.807-1.969.486-2.309.462-3.439.813-.042.03.049.061 1.549.146.662.036h1.622l3.02.225.79.522.474.638-.079.485-1.215.62-1.64-.389-3.829-.91-1.312-.329h-.182v.11l1.093 1.068 2.006 1.81 2.509 2.33.127.578-.322.455-.34-.049-2.205-1.657-.851-.747-1.926-1.62h-.128v.17l.444.649 2.345 3.521.122 1.08-.17.353-.608.213-.668-.122-1.374-1.925-1.415-2.167-1.143-1.943-.14.08-.674 7.254-.316.37-.729.28-.607-.461-.322-.747.322-1.476.389-1.924.315-1.53.286-1.9.17-.632-.012-.042-.14.018-1.434 1.967-2.18 2.945-1.726 1.845-.414.164-.717-.37.067-.662.401-.589 2.388-3.036 1.44-1.882.93-1.086-.006-.158h-.055L4.132 18.56l-1.13.146-.487-.456.061-.746.231-.243 1.908-1.312-.006.006z"
                    fill="#D97757"
                    fill-rule="nonzero"
                  ></path>
                </svg>`,
    },
    {
        id: 'qwen',
        name: 'Qwen',
        description: 'Qwen Code CLI application',
        icon: `<svg width="24" height="24" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M174.82 108.75L155.38 75L165.64 57.75C166.46 56.31 166.46 54.53 165.64 53.09L155.38 35.84C154.86 34.91 153.87 34.33 152.78 34.33H114.88L106.14 19.03C105.62 18.1 104.63 17.52 103.54 17.52H83.3C82.21 17.52 81.22 18.1 80.7 19.03L61.26 52.77H41.02C39.93 52.77 38.94 53.35 38.42 54.28L28.16 71.53C27.34 72.97 27.34 74.75 28.16 76.19L45.52 107.5L36.78 122.8C35.96 124.24 35.96 126.02 36.78 127.46L47.04 144.71C47.56 145.64 48.55 146.22 49.64 146.22H87.54L96.28 161.52C96.8 162.45 97.79 163.03 98.88 163.03H119.12C120.21 163.03 121.2 162.45 121.72 161.52L141.16 127.78H158.52C159.61 127.78 160.6 127.2 161.12 126.27L171.38 109.02C172.2 107.58 172.2 105.8 171.38 104.36L174.82 108.75Z" fill="url(#paint0_radial)"/>
        <path d="M119.12 163.03H98.88L87.54 144.71H49.64L61.26 126.39H80.7L38.42 55.29H61.26L83.3 19.03L93.56 37.35L83.3 55.29H161.58L151.32 72.54L170.76 106.28H151.32L141.16 88.34L101.18 163.03H119.12Z" fill="white"/>
        <path d="M127.86 79.83H76.14L101.18 122.11L127.86 79.83Z" fill="url(#paint1_radial)"/>
        <defs>
        <radialGradient id="paint0_radial" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(100 100) rotate(90) scale(100)">
        <stop stop-color="#665CEE"/>
        <stop offset="1" stop-color="#332E91"/>
        </radialGradient>
        <radialGradient id="paint1_radial" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(100 100) rotate(90) scale(100)">
        <stop stop-color="#665CEE"/>
        <stop offset="1" stop-color="#332E91"/>
        </radialGradient>
        </defs>
        </svg>`,
    },
    {
        id: 'copilot',
        name: 'Copilot',
        description: 'AI pair programmer',
        icon: `<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-github-copilot">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M4 18v-5.5c0 -.667 .167 -1.333 .5 -2" />
        <path d="M12 7.5c0 -1 -.01 -4.07 -4 -3.5c-3.5 .5 -4 2.5 -4 3.5c0 1.5 0 4 3 4c4 0 5 -2.5 5 -4z" />
        <path d="M4 12c-1.333 .667 -2 1.333 -2 2c0 1 0 3 1.5 4c3 2 6.5 3 8.5 3s5.499 -1 8.5 -3c1.5 -1 1.5 -3 1.5 -4c0 -.667 -.667 -1.333 -2 -2" />
        <path d="M20 18v-5.5c0 -.667 -.167 -1.333 -.5 -2" />
        <path d="M12 7.5l0 -.297l.01 -.269l.027 -.298l.013 -.105l.033 -.215c.014 -.073 .029 -.146 .046 -.22l.06 -.223c.336 -1.118 1.262 -2.237 3.808 -1.873c2.838 .405 3.703 1.797 3.93 2.842l.036 .204c0 .033 .01 .066 .013 .098l.016 .185l0 .171l0 .49l-.015 .394l-.02 .271c-.122 1.366 -.655 2.845 -2.962 2.845c-3.256 0 -4.524 -1.656 -4.883 -3.081l-.053 -.242a3.865 3.865 0 0 1 -.036 -.235l-.021 -.227a3.518 3.518 0 0 1 -.007 -.215z" />
        <path d="M10 15v2" />
        <path d="M14 15v2" />
        </svg>`,
    },
];

// Computed properties
const mcpUrl = computed(() => {
    if (!uuid.value) return '';
    return `${window.location.origin}/mcp`;
});

const configs = computed(() => ({
    cursor: {
        title: 'Cursor Configuration',
        content: `{
  "mcpServers": {
    "memory-library": {
      "url": "${mcpUrl.value}"
    }
  }
}`,
    },
    claude: {
        title: 'Claude Configuration',
        content: `{
  "mcpServers": {
    "memory-library": {
      "type": "http",
      "url": "${mcpUrl.value}"
    }
  }
}`,
    },
    qwen: {
        title: 'Qwen Code Configuration',
        content: `{
  "mcpServers": {
    "memory-library": {
      "httpUrl": "${mcpUrl.value}"
    }
  }
}`,
    },
    copilot: {
        title: 'GitHub Copilot Configuration',
        content: `{
  "mcpServers": {
    "memory-library": {
      "type": "http",
      "url": "${mcpUrl.value}",
      "requestInit": {
        "headers": {
          "Authorization": "Bearer YOUR_API_KEY_HERE",
          "Content-Type": "application/json"
        }
      }
    }
  }
}`,
    },
}));

// Methods
const generateUUID = (): string => {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
};

const copyToClipboard = async (text: string, key: string): Promise<void> => {
    try {
        await navigator.clipboard.writeText(text);
        copiedStates.value[key] = true;
        setTimeout(() => {
            copiedStates.value[key] = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy text: ', err);
    }
};

const generateNewUUID = (): void => {
    const newUuid = generateUUID();
    uuid.value = newUuid;
    localStorage.setItem('memory-library-uuid', newUuid);
};

// Lifecycle
onMounted(() => {
    const stored = localStorage.getItem('memory-library-uuid');
    if (stored) {
        uuid.value = stored;
    } else {
        generateNewUUID();
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- AI Client Setup Section -->
            <div class="mt-8">
                <div class="mb-8 text-center">
                    <h2
                        class="text-3xl font-bold text-slate-900 dark:text-slate-100"
                    >
                        AI Client Setup
                    </h2>
                    <p class="mt-2 text-slate-600 dark:text-slate-400">
                        Choose your AI client and copy the configuration to get
                        started.
                    </p>
                </div>

                <!-- Client Tabs -->
                <div class="mb-8 flex justify-center">
                    <div
                        class="inline-flex h-10 items-center justify-center rounded-md bg-slate-100 p-1 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                    >
                        <button
                            v-for="client in clients"
                            :key="client.id"
                            @click="activeTab = client.id"
                            :class="[
                                'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-white transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:ring-offset-slate-950 dark:focus-visible:ring-slate-300',
                                activeTab === client.id
                                    ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-950 dark:text-slate-50'
                                    : 'hover:bg-white/60 hover:text-slate-900 dark:hover:bg-slate-800/60 dark:hover:text-slate-50',
                            ]"
                        >
                            <span class="mr-2" v-html="client.icon"></span>
                            {{ client.name }}
                        </button>
                    </div>
                </div>

                <!-- Configuration Cards -->
                <Card class="mx-auto max-w-3xl">
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between">
                            <span>{{ configs[activeTab].title }}</span>
                            <Button
                                @click="
                                    copyToClipboard(
                                        configs[activeTab].content,
                                        `config-${activeTab}`,
                                    )
                                "
                                variant="outline"
                                size="sm"
                                class="ml-4"
                            >
                                <span
                                    v-if="copiedStates[`config-${activeTab}`]"
                                    class="mr-2"
                                    >✓</span
                                >
                                <span v-else class="mr-2">📋</span>
                                Copy Config
                            </Button>
                        </CardTitle>
                        <CardDescription>
                            Add this configuration to your
                            {{ clients.find((c) => c.id === activeTab)?.name }}
                            settings.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="relative">
                            <pre
                                class="overflow-x-auto rounded-md bg-slate-100 p-4 text-sm dark:bg-slate-800"
                            ><code class="language-json">{{ configs[activeTab].content }}</code></pre>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
