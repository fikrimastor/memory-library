# Memory Library - Project Structure

## Root Directory Structure
```
memory-library/
├── app/                    # Application code
├── bootstrap/              # Application bootstrap
├── config/                 # Configuration files
├── database/               # Database files and migrations
├── public/                 # Public web directory
├── resources/              # Frontend assets and views
├── routes/                 # Route definitions
├── storage/                # Storage directory
├── tests/                  # Test files
├── .env.example           # Environment variables template
├── composer.json          # PHP dependencies
├── package.json           # Node.js dependencies
├── vite.config.ts         # Vite configuration
└── CLAUDE.md              # Project-specific Claude instructions
```

## Application Directory (`app/`)
```
app/
├── Actions/               # Business logic actions
│   ├── AddToMemoryAction.php
│   ├── SearchMemoryAction.php
│   ├── GenerateEmbeddingAction.php
│   └── HealthCheckAction.php
├── Contracts/             # Interfaces and contracts
│   └── EmbeddingDriverInterface.php
├── Drivers/               # Service drivers
│   └── Embedding/
│       └── CloudFlareDriver.php
├── Http/                  # HTTP layer
│   ├── Controllers/       # Controllers
│   │   ├── Auth/          # Authentication controllers
│   │   └── Settings/      # Settings controllers
│   ├── Middleware/        # HTTP middleware
│   └── Requests/          # Form request classes
├── Jobs/                  # Queued jobs
│   ├── GenerateEmbeddingJob.php
│   ├── CleanupFailedEmbeddingsJob.php
│   └── HealthCheckJob.php
├── Mcp/                   # MCP server integration
│   ├── Servers/           # MCP servers
│   │   └── MemoryLibraryServer.php
│   └── Tools/             # MCP tools
│       ├── AddToMemory.php
│       ├── SearchMemory.php
│       ├── ConfigureProvider.php
│       └── MemoryStatus.php
├── Models/                # Eloquent models
│   ├── User.php
│   ├── UserMemory.php
│   ├── SocialAccount.php
│   ├── EmbeddingJob.php
│   └── ProviderHealth.php
├── Providers/             # Service providers
│   ├── AppServiceProvider.php
│   ├── FortifyServiceProvider.php
│   └── EmbeddingServiceProvider.php
└── Services/              # Application services
    └── EmbeddingManager.php
```

## Frontend Directory (`resources/`)
```
resources/
├── css/                   # Stylesheets
│   └── app.css
├── js/                    # JavaScript/TypeScript files
│   ├── components/        # Vue components
│   │   ├── ui/            # UI components (Reka UI)
│   │   ├── AppShell.vue
│   │   ├── AppSidebar.vue
│   │   ├── AppHeader.vue
│   │   ├── NavMain.vue
│   │   ├── NavUser.vue
│   │   └── SocialAccounts.vue
│   ├── composables/       # Vue 3 composables
│   │   ├── useAppearance.ts
│   │   ├── useTwoFactorAuth.ts
│   │   └── useInitials.ts
│   ├── layouts/           # Layout components
│   │   ├── AppLayout.vue
│   │   ├── AuthLayout.vue
│   │   └── settings/
│   ├── lib/               # Utility functions
│   │   └── utils.ts
│   ├── pages/             # Inertia page components
│   │   ├── auth/          # Authentication pages
│   │   ├── settings/      # Settings pages
│   │   ├── Dashboard.vue
│   │   └── Welcome.vue
│   ├── types/             # TypeScript definitions
│   │   ├── index.d.ts
│   │   └── globals.d.ts
│   ├── app.ts             # Main application entry
│   └── ssr.ts             # SSR entry point
└── views/                 # Blade templates
    ├── app.blade.php      # Main application layout
    └── mcp/               # MCP authorization views
```

## Database Structure (`database/`)
```
database/
├── factories/             # Model factories
├── migrations/            # Database migrations
│   ├── *_create_users_table.php
│   ├── *_create_user_memories_table.php
│   ├── *_add_vector_support_to_user_memories.php
│   ├── *_create_social_accounts_table.php
│   ├── *_create_embedding_jobs_table.php
│   ├── *_create_provider_health_table.php
│   └── *_create_oauth_*_table.php (Passport)
├── seeders/               # Database seeders
└── database.sqlite        # SQLite database file
```

## Testing Structure (`tests/`)
```
tests/
├── Browser/               # Browser/E2E tests (Pest 4.1)
│   └── ApiTokenManagementTest.php
├── Feature/               # Feature/Integration tests
│   ├── Api/               # API tests
│   │   └── ApiAuthenticationTest.php
│   ├── Auth/              # Authentication tests
│   ├── Mcp/               # MCP tests
│   │   └── Tools/         # MCP tool tests
│   ├── Settings/          # Settings tests
│   ├── AddToMemoryActionTest.php
│   ├── SearchMemoryActionTest.php
│   ├── GitHubOAuthTest.php
│   ├── CloudFlareDriverTest.php
│   └── EmbeddingManagerTest.php
├── Unit/                  # Unit tests
│   └── ExampleTest.php
├── Pest.php               # Pest configuration
└── TestCase.php           # Base test case
```

## Configuration Directory (`config/`)
```
config/
├── app.php                # Application configuration
├── auth.php               # Authentication configuration
├── cache.php              # Cache configuration
├── database.php           # Database configuration
├── fortify.php            # Laravel Fortify configuration
├── passport.php           # Laravel Passport configuration
├── services.php           # Third-party services configuration
└── ...                    # Other Laravel config files
```

## Routes Directory (`routes/`)
```
routes/
├── web.php                # Web routes (Inertia)
├── auth.php               # Authentication routes
├── settings.php           # Settings routes
└── api.php                # API routes (if needed)
```

## Key Architecture Patterns

### 1. Action Pattern
Business logic is encapsulated in Action classes located in `app/Actions/`. Each action has a single `handle()` method that performs a specific business operation.

### 2. MCP Integration
Model Context Protocol integration through:
- **Server**: `app/Mcp/Servers/MemoryLibraryServer.php`
- **Tools**: Individual tools in `app/Mcp/Tools/`
- **Configuration**: MCP server registration in service providers

### 3. Service Layer
Complex operations are handled by service classes:
- **EmbeddingManager**: Manages AI embedding operations
- **Service Providers**: Register and configure services

### 4. Queue System
Asynchronous processing for:
- Embedding generation (`GenerateEmbeddingJob`)
- Health checks (`HealthCheckJob`)
- Cleanup operations (`CleanupFailedEmbeddingsJob`)

### 5. Authentication Stack
Multi-layered authentication:
- **Fortify**: User authentication and registration
- **Passport**: API token management
- **Socialite**: OAuth integration (GitHub)
- **Two-Factor Authentication**: Enhanced security