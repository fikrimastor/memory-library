<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
const loginUrl = '/auth/github';
const githubLoginUrl = '/auth/github';
const dashboardUrl = '/dashboard';
</script>

<template>
    <Head title="Memory Library - Personal AI Context & Long-Term Memory">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
        <meta
            name="description"
            content="Your personal long-term memory for AI. Never lose context or preferences across AI platforms."
        />
    </Head>

    <div class="flex min-h-screen flex-col bg-slate-50 dark:bg-slate-950">
        <!-- Header -->
        <header
            class="border-b bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center space-x-4">
                    <h1
                        class="text-xl font-bold text-slate-900 dark:text-slate-100"
                    >
                        Memory Library
                    </h1>
                    <Badge
                        variant="secondary"
                        class="border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950 dark:text-red-300"
                    >
                        Beta
                    </Badge>
                </div>

                <nav class="flex items-center space-x-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-900 shadow-sm transition-colors hover:bg-slate-50 hover:text-slate-900 focus-visible:ring-1 focus-visible:ring-slate-950 focus-visible:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50 dark:hover:bg-slate-800 dark:hover:text-slate-50 dark:focus-visible:ring-slate-300"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <a
                            :href="loginUrl"
                            class="inline-flex h-9 items-center justify-center rounded-md px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100"
                        >
                            <svg
                                class="mr-3 h-5 w-5"
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
                            Login with GitHub
                        </a>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
                <!-- Hero Section -->
                <div class="text-center">
                    <h1
                        class="text-4xl font-bold tracking-tight text-slate-900 sm:text-6xl dark:text-slate-100"
                    >
                        Your Personal
                        <span class="text-red-600 dark:text-red-500"
                            >Long-Term Memory</span
                        >
                    </h1>
                    <p
                        class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-400"
                    >
                        Is AI forgetting who you are, your context, or
                        preferences? Memory Library provides seamless context
                        persistence across all your AI interactions.
                    </p>

                    <div
                        class="mt-8 flex items-center justify-center space-x-4"
                    >
                        <a
                            href="https://github.com/fikrimastor/memory-library"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="transition-transform hover:scale-105"
                        >
                            <img
                                src="https://img.shields.io/github/stars/fikrimastor/memory-library?style=social"
                                alt="Star on GitHub"
                            />
                        </a>
                        <a
                            href="https://github.com/fikrimastor"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="transition-transform hover:scale-105"
                        >
                            <img
                                src="https://img.shields.io/badge/Created%20by-@FikriMastor-000000?style=flat&logo=github"
                                alt="Created by @FikriMastor"
                            />
                        </a>
                    </div>
                </div>

                <!-- Features Section -->
                <div class="mt-20">
                    <div class="mb-12 text-center">
                        <h2
                            class="text-3xl font-bold text-slate-900 dark:text-slate-100"
                        >
                            Why Memory Library?
                        </h2>
                    </div>

                    <div class="grid gap-8 md:grid-cols-3">
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center space-x-2">
                                    <span class="text-2xl">🧠</span>
                                    <span>Persistent Memory</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-slate-600 dark:text-slate-400">
                                    Never lose context again. Your AI remembers
                                    your preferences, conversation history, and
                                    important details across sessions.
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center space-x-2">
                                    <span class="text-2xl">🔄</span>
                                    <span>Cross-Platform</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-slate-600 dark:text-slate-400">
                                    Works seamlessly across Cursor, Claude
                                    Desktop, Windsurf, and any MCP-compatible AI
                                    client.
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center space-x-2">
                                    <span class="text-2xl">🔒</span>
                                    <span>Privacy First</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-slate-600 dark:text-slate-400">
                                    Your data stays with you. Self-hosted
                                    solution with complete control over your
                                    memory and context.
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Getting Started -->
                <div class="mt-20 text-center">
                    <Card
                        class="mx-auto max-w-2xl border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/20"
                    >
                        <CardHeader>
                            <CardTitle class="text-red-800 dark:text-red-200">
                                🚀 Ready to Get Started?
                            </CardTitle>
                            <CardDescription
                                class="text-red-700 dark:text-red-300"
                            >
                                Sign up to get access to your personal AI memory
                                configuration.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <p
                                    class="text-sm text-red-600 dark:text-red-400"
                                >
                                    1. Sign up or login to get started<br />
                                    2. Go to your dashboard to set up AI
                                    clients<br />
                                    3. Copy your configuration to your AI
                                    client<br />
                                    4. Start building persistent memory!
                                </p>

                                <div class="flex justify-center space-x-4">
                                    <Button
                                        v-if="$page.props.auth.user"
                                        :href="dashboardUrl"
                                        as="a"
                                        class="bg-red-600 text-white hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700"
                                    >
                                        Go to Dashboard
                                    </Button>
                                    <Button
                                        v-else
                                        :href="githubLoginUrl"
                                        as="a"
                                        class="bg-red-600 text-white hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700"
                                    >
                                        Sign Up to Save Memory
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer
            class="border-t bg-white dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div
                    class="text-center text-sm text-slate-500 dark:text-slate-400"
                >
                    <p>
                        Built with ❤️ using the VILT stack (Vue.js, Inertia.js,
                        Laravel, Tailwind CSS)
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
