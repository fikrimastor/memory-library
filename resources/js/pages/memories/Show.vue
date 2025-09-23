<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { index as memoriesIndex, edit as memoriesEdit, destroy as memoriesDestroy } from '@/routes/memories'
import { type BreadcrumbItem } from '@/types'
import {
  ArrowLeft,
  Edit,
  Trash2,
  Calendar,
  Folder,
  Tag
} from 'lucide-vue-next'

// Types
interface Memory {
  id: number
  title: string
  thing_to_remember: string
  document_type: string | null
  project_name: string | null
  tags: string[] | null
  created_at: string
  updated_at: string
}

interface Props {
  memory: Memory
}

const props = defineProps<Props>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Memories',
    href: memoriesIndex().url,
  },
  {
    title: props.memory.title,
    href: '#',
  },
]

// Methods
const deleteMemory = (): void => {
  if (confirm(`Are you sure you want to delete "${props.memory.title}"? This action cannot be undone.`)) {
    router.delete(memoriesDestroy(props.memory.id).url, {
      preserveScroll: true,
    })
  }
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <Head :title="memory.title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
      <div class="flex items-center gap-4">
        <Button 
          as-child
          variant="outline"
          size="sm"
          class="gap-2"
        >
          <Link :href="memoriesIndex().url">
            <ArrowLeft class="h-4 w-4" />
            Back to Memories
          </Link>
        </Button>
      </div>

      <Card class="max-w-3xl">
        <CardHeader>
          <div class="flex items-start justify-between gap-4">
            <div>
              <CardTitle class="text-2xl">{{ memory.title }}</CardTitle>
              <CardDescription class="mt-2">
                Detailed view of your memory
              </CardDescription>
            </div>
            
            <div class="flex gap-2">
              <Button 
                as-child
                variant="outline"
                size="sm"
                class="gap-2"
              >
                <Link :href="memoriesEdit(memory.id).url">
                  <Edit class="h-4 w-4" />
                  Edit
                </Link>
              </Button>
              
              <Button
                @click="deleteMemory"
                variant="outline"
                size="sm"
                class="gap-2 text-red-600 hover:text-red-700 hover:border-red-300 dark:hover:text-red-500 dark:hover:border-red-700"
              >
                <Trash2 class="h-4 w-4" />
                Delete
              </Button>
            </div>
          </div>
        </CardHeader>

        <CardContent class="space-y-6">
          <!-- Content -->
          <div class="space-y-2">
            <h3 class="text-lg font-semibold">Content</h3>
            <div class="prose prose-slate dark:prose-invert max-w-none rounded-lg border p-4">
              <p class="whitespace-pre-wrap">{{ memory.thing_to_remember }}</p>
            </div>
          </div>

          <Separator />

          <!-- Metadata -->
          <div class="grid gap-4 sm:grid-cols-2">
            <div v-if="memory.document_type" class="space-y-2">
              <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                <Folder class="h-4 w-4" />
                Document Type
              </div>
              <p class="text-sm">{{ memory.document_type }}</p>
            </div>

            <div v-if="memory.project_name" class="space-y-2">
              <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                <Folder class="h-4 w-4" />
                Project Name
              </div>
              <p class="text-sm">{{ memory.project_name }}</p>
            </div>

            <div class="space-y-2">
              <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                <Calendar class="h-4 w-4" />
                Created
              </div>
              <p class="text-sm">{{ formatDate(memory.created_at) }}</p>
            </div>

            <div class="space-y-2">
              <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
                <Calendar class="h-4 w-4" />
                Last Updated
              </div>
              <p class="text-sm">{{ formatDate(memory.updated_at) }}</p>
            </div>
          </div>

          <!-- Tags -->
          <div v-if="memory.tags && memory.tags.length > 0" class="space-y-2">
            <div class="flex items-center gap-2 text-sm font-medium text-slate-500">
              <Tag class="h-4 w-4" />
              Tags
            </div>
            <div class="flex flex-wrap gap-2">
              <Badge
                v-for="tag in memory.tags"
                :key="tag"
                variant="secondary"
              >
                {{ tag }}
              </Badge>
            </div>
          </div>
        </CardContent>

        <CardFooter class="flex justify-between">
          <Button as-child variant="outline">
            <Link :href="memoriesIndex().url">
              <ArrowLeft class="h-4 w-4" />
              Back to Memories
            </Link>
          </Button>
          
          <Button as-child>
            <Link :href="memoriesEdit(memory.id).url" class="gap-2">
              <Edit class="h-4 w-4" />
              Edit Memory
            </Link>
          </Button>
        </CardFooter>
      </Card>
    </div>
  </AppLayout>
</template>