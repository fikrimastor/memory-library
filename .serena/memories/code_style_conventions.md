# Memory Library - Code Style & Conventions

## PHP Standards (Laravel)

### General PHP
- **PHP Version**: 8.4+ with strict types: `declare(strict_types=1);`
- **PSR Standards**: Follow PSR-4 autoloading and PSR-12 coding style
- **Laravel Pint**: Enforced code formatting with `./vendor/bin/pint`

### Laravel Conventions
- **Action Pattern**: Business logic in `app/Actions/` with single `handle()` method
- **Controllers**: Thin controllers, delegate to Actions or Services
- **Models**: Use Eloquent relationships with proper return type hints
- **Form Requests**: Always use dedicated Form Request classes for validation
- **Routes**: Named routes with clear, descriptive names

### PHP Code Style
```php
<?php
declare(strict_types=1);

namespace App\Actions;

final class ExampleAction
{
    public function __construct(
        protected SomeService $service
    ) {}

    public function handle(User $user, string $data): Result
    {
        return DB::transaction(function () use ($user, $data) {
            // Implementation
        });
    }
}
```

### Naming Conventions
- **Classes**: PascalCase (`UserMemory`, `AddToMemoryAction`)
- **Methods**: camelCase (`handle`, `createMemory`)
- **Variables**: camelCase (`$userData`, `$embeddingResult`)
- **Constants**: SCREAMING_SNAKE_CASE (`EMBEDDING_DIMENSION`)
- **Database**: snake_case (`user_memories`, `created_at`)

## TypeScript/Vue.js Standards

### Vue 3 Composition API
- **Script Setup**: Use `<script setup lang="ts">` syntax
- **Type Safety**: Explicit TypeScript interfaces and types
- **Component Names**: Multi-word PascalCase (`UserProfile`, `MemoryCard`)
- **Props**: Interface-based prop definitions with validation

### TypeScript Style
```typescript
interface UserMemory {
  id: number;
  thing_to_remember: string;
  title?: string;
  tags: string[];
  project_name?: string;
}

const props = defineProps<{
  memories: UserMemory[];
  loading?: boolean;
}>();

const emit = defineEmits<{
  save: [memory: UserMemory];
  delete: [id: number];
}>();
```

### Frontend Code Organization
- **Pages**: `resources/js/pages/` - Inertia page components
- **Components**: `resources/js/components/` - Reusable Vue components  
- **Layouts**: `resources/js/layouts/` - Application layouts
- **Composables**: `resources/js/composables/` - Vue 3 composables
- **Types**: `resources/js/types/` - TypeScript definitions
- **Utils**: `resources/js/lib/` - Utility functions

## CSS/Styling Standards

### Tailwind CSS 4.1
- **Utility First**: Prefer utility classes over custom CSS
- **Responsive**: Mobile-first responsive design patterns
- **Dark Mode**: Support with `dark:` prefix
- **Component Library**: Reka UI components for complex UI elements

### Class Organization
```vue
<template>
  <div class="flex flex-col gap-4 p-6 bg-white dark:bg-gray-900 rounded-lg shadow-sm">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
      Title
    </h1>
    <p class="text-gray-600 dark:text-gray-300">
      Description
    </p>
  </div>
</template>
```

## Database Standards

### Migrations
- **Timestamps**: Always include `timestamps()`
- **Foreign Keys**: Use `foreignId()->constrained()->onDelete('cascade')`
- **Indexes**: Add appropriate indexes for performance
- **Naming**: Descriptive migration names with date prefix

### Eloquent Models
```php
class UserMemory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'thing_to_remember',
        'title',
        'document_type',
        'project_name',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
        'embedding' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

## Testing Standards (Pest 4.1)

### Test Organization
- **Feature Tests**: `tests/Feature/` - Integration tests
- **Unit Tests**: `tests/Unit/` - Isolated unit tests  
- **Browser Tests**: `tests/Browser/` - End-to-end browser tests

### Pest Syntax
```php
it('creates a memory successfully', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/memories', [
            'thing_to_remember' => 'Test memory',
            'tags' => ['test'],
        ]);
    
    $response->assertCreated()
        ->assertJsonStructure(['id', 'thing_to_remember']);
    
    expect($user->memories)->toHaveCount(1);
});
```

## File and Directory Structure

### Backend Structure
```
app/
├── Actions/           # Business logic actions
├── Http/
│   ├── Controllers/   # HTTP controllers
│   ├── Requests/      # Form request validation
│   └── Middleware/    # HTTP middleware
├── Models/            # Eloquent models
├── Services/          # Application services
├── Jobs/              # Queued jobs
└── Mcp/               # MCP server integration
    ├── Tools/         # MCP tools
    └── Servers/       # MCP servers
```

### Frontend Structure
```
resources/js/
├── pages/             # Inertia page components
├── components/        # Reusable Vue components
├── layouts/           # Layout components
├── composables/       # Vue 3 composables
├── types/             # TypeScript types
└── lib/               # Utility functions
```