<script setup lang="ts">
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { index as memoriesIndex, show as memoriesShow } from '@/routes/memories'
import { type BreadcrumbItem } from '@/types'
import {
  ArrowLeft,
  Save,
  Plus,
  X
} from 'lucide-vue-next'
import InputError from '@/components/InputError.vue'

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

// Reactive state
const tagInput = ref('')
const isAddingTag = ref(false)

const form = useForm({
  title: props.memory.title,
  thing_to_remember: props.memory.thing_to_remember,
  document_type: props.memory.document_type,
  project_name: props.memory.project_name,
  tags: props.memory.tags || []
})

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Memories',
    href: memoriesIndex().url,
  },
  {
    title: 'Edit Memory',
    href: '#',
  },
]

// Methods
const addTag = (): void => {
  if (tagInput.value.trim() && !form.tags.includes(tagInput.value.trim())) {
    form.tags.push(tagInput.value.trim())
  }
  tagInput.value = ''
  isAddingTag.value = false
}

const removeTag = (tag: string): void => {
  form.tags = form.tags.filter(t => t !== tag)
}

const submit = (): void => {
  form.put(`/memories/${props.memory.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Form reset handled by Inertia
    },
    onError: () => {
      // Errors will be displayed by InputError components
    }
  })
}

const cancel = (): void => {
  router.get(memoriesIndex().url)
}
</script>

<template>
  <Head :title="`Edit Memory: ${memory.title}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
      <div class="flex items-center gap-4">
        <Button 
          @click="cancel"
          variant="outline"
          size="sm"
          class="gap-2"
        >
          <ArrowLeft class="h-4 w-4" />
          Back to Memories
        </Button>
      </div>

      <Card class="max-w-3xl">
        <CardHeader>
          <CardTitle class="text-2xl">Edit Memory</CardTitle>
          <CardDescription>
            Update your memory details below.
          </CardDescription>
        </CardHeader>

        <form @submit.prevent="submit">
          <CardContent class="space-y-6">
            <!-- Title -->
            <div class="space-y-2">
              <Label for="title">Title *</Label>
              <Input
                id="title"
                v-model="form.title"
                type="text"
                placeholder="Enter a title for your memory"
                :disabled="form.processing"
              />
              <InputError :message="form.errors.title" />
            </div>

            <!-- Content -->
            <div class="space-y-2">
              <Label for="thing_to_remember">Content *</Label>
              <textarea
                id="thing_to_remember"
                v-model="form.thing_to_remember"
                placeholder="Enter the content you want to remember..."
                :disabled="form.processing"
                class="file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-32 w-full rounded-md border px-3 py-2 text-sm md:text-sm"
                rows="8"
              />
              <InputError :message="form.errors.thing_to_remember" />
            </div>

            <Separator />

            <!-- Document Type -->
            <div class="space-y-2">
              <Label for="document_type">Document Type</Label>
              <Input
                id="document_type"
                v-model="form.document_type"
                type="text"
                placeholder="e.g., PRD, Technical Spec, Meeting Notes"
                :disabled="form.processing"
              />
              <InputError :message="form.errors.document_type" />
            </div>

            <!-- Project Name -->
            <div class="space-y-2">
              <Label for="project_name">Project Name</Label>
              <Input
                id="project_name"
                v-model="form.project_name"
                type="text"
                placeholder="e.g., Memory Library, Project Alpha"
                :disabled="form.processing"
              />
              <InputError :message="form.errors.project_name" />
            </div>

            <!-- Tags -->
            <div class="space-y-2">
              <Label>Tags</Label>
              <div class="flex flex-wrap gap-2">
                <Badge
                  v-for="tag in form.tags"
                  :key="tag"
                  variant="secondary"
                  class="flex items-center gap-1 pl-3 pr-1 py-1"
                >
                  {{ tag }}
                  <Button
                    @click="removeTag(tag)"
                    variant="ghost"
                    size="sm"
                    class="h-5 w-5 p-0 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900 dark:hover:text-red-100"
                  >
                    <X class="h-3 w-3" />
                  </Button>
                </Badge>
                
                <div v-if="isAddingTag" class="flex items-center gap-2">
                  <Input
                    v-model="tagInput"
                    type="text"
                    placeholder="Add a tag..."
                    class="h-8 w-32"
                    @keydown.enter.prevent="addTag"
                    @keydown.escape="isAddingTag = false"
                    @blur="addTag"
                  />
                </div>
                
                <Button
                  v-else
                  @click="isAddingTag = true"
                  variant="outline"
                  size="sm"
                  class="gap-1 h-8"
                >
                  <Plus class="h-4 w-4" />
                  Add Tag
                </Button>
              </div>
              <InputError :message="form.errors.tags" />
            </div>
          </CardContent>

          <CardFooter class="flex justify-between">
            <Button
              @click="cancel"
              variant="outline"
              :disabled="form.processing"
            >
              Cancel
            </Button>
            
            <Button
              type="submit"
              class="gap-2"
              :disabled="form.processing"
              :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
            >
              <Save 
                :class="['h-4 w-4', { 'animate-spin': form.processing }]" 
              />
              Update Memory
            </Button>
          </CardFooter>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>