# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Memory Library is a Laravel application with Vue.js/TypeScript frontend using Inertia.js for seamless SPA-like experience. The project uses Laravel MCP server integration for model context protocol support.

## Tech Stack

- **Backend**: Laravel 12.0 with PHP 8.2+
- **Frontend**: Vue 3 + TypeScript + Inertia.js
- **Styling**: Tailwind CSS 4.1 + Reka UI components
- **Build Tool**: Vite with Laravel Wayfinder plugin
- **Testing**: Pest PHP testing framework
- **Authentication**: Laravel Fortify
- **Database**: SQLite (default), configurable to other databases

## Development Commands

### Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

### Development Server
```bash
# Full development environment (server + queue + logs + vite)
composer dev

# SSR development environment
composer dev:ssr

# Individual services
php artisan serve
npm run dev
php artisan queue:listen --tries=1
php artisan pail --timeout=0
```

### Testing
```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test
php artisan test --filter=FeatureName
```

### Code Quality
```bash
# PHP linting and formatting
./vendor/bin/pint
./vendor/bin/pint --dirty  # Format only changed files

# Frontend linting and formatting
npm run lint
npm run format
npm run format:check
```

### Build
```bash
# Production build
npm run build

# SSR build
npm run build:ssr
```

## Code Architecture

### Backend Structure
- **Controllers**: `app/Http/Controllers/` - Handle HTTP requests
- **Models**: `app/Models/` - Eloquent models
- **Routes**: `routes/web.php` - Main web routes with Inertia rendering
- **Middleware**: `app/Http/Middleware/` - Request middleware
- **Providers**: `app/Providers/` - Service providers

### Frontend Structure
- **Pages**: `resources/js/pages/` - Inertia.js page components
- **Components**: `resources/js/components/` - Reusable Vue components
- **Layouts**: `resources/js/layouts/` - Page layout components
- **Composables**: `resources/js/composables/` - Vue 3 composables
- **Types**: `resources/js/types/` - TypeScript type definitions
- **Utilities**: `resources/js/lib/` - Utility functions

### Key Components
- **UI Components**: Located in `resources/js/components/ui/` (ignored by ESLint)
- **App Shell**: `AppShell.vue`, `AppSidebar.vue`, `AppHeader.vue` for layout
- **Authentication**: Fortify integration with 2FA support
- **Navigation**: `NavMain.vue`, `NavUser.vue`, `NavFooter.vue`

## Code Style Conventions

### PHP (Laravel)
- Use PSR-4 autoloading
- Follow Laravel naming conventions
- Use PHP 8.2+ features
- Pest testing framework for tests

### TypeScript/Vue
- 4-space indentation (overridden to 2 spaces for YAML)
- Single quotes preferred
- Semicolons required
- Prettier formatting with Tailwind plugin
- ESLint with Vue + TypeScript configuration
- Multi-word component names rule disabled
- TypeScript strict any disabled

### CSS/Styling
- Tailwind CSS 4.1 with custom configuration
- Use utility classes with `clsx`, `cn`, `cva` functions
- Reka UI component library integration
- Responsive design patterns

## Development Workflow

1. **Before starting work**: Ensure `.env` is configured and database is migrated
2. **During development**: Use `composer dev` for full environment
3. **Before committing**: Run linting and formatting commands
4. **Testing**: Always run tests with `composer test`
5. **Production**: Use `npm run build` for optimized assets

## Important Notes

- MCP server integration available through Laravel MCP package
- SSR support available with Inertia.js
- Authentication includes 2FA and user management features
- Concurrent development server setup with queue worker and log tailing
- Wayfinder plugin enables advanced form variant handling