# Memory Library - Suggested Commands

## Development Setup
```bash
# Initial setup
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate

# Install Passport keys
php artisan passport:install
```

## Development Workflow
```bash
# Full development environment (recommended)
composer dev
# Runs: server + queue + logs + vite concurrently

# Individual services
php artisan serve                    # Start Laravel server
npm run dev                         # Start Vite dev server
php artisan queue:listen --tries=1  # Start queue worker
php artisan pail --timeout=0       # View logs

# SSR development
composer dev:ssr
```

## Testing
```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test files
php artisan test tests/Feature/GitHubOAuthTest.php
php artisan test tests/Browser/ApiTokenManagementTest.php

# Run with filters
php artisan test --filter=memory
php artisan test --filter=github
```

## Code Quality
```bash
# Format PHP code
./vendor/bin/pint
./vendor/bin/pint --dirty    # Format only changed files

# Frontend linting and formatting
npm run lint                 # ESLint with fixes
npm run format              # Prettier formatting
npm run format:check        # Check formatting without changes
```

## Build Commands
```bash
# Production build
npm run build

# SSR build
npm run build:ssr

# TypeScript checking
npx vue-tsc --noEmit
```

## Database Operations
```bash
# Run migrations
php artisan migrate

# Fresh database with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name

# Create model with migration and factory
php artisan make:model ModelName -mf
```

## Artisan Commands
```bash
# Create controllers
php artisan make:controller ControllerName
php artisan make:controller Api/ResourceController --api

# Create actions
php artisan make:class "App\Actions\ActionName"

# Create jobs
php artisan make:job JobName

# Create tests
php artisan make:test TestName --pest
php artisan make:test TestName --pest --unit

# Tinker (REPL)
php artisan tinker
```

## Git Workflow
```bash
# Standard git commands for macOS
git status
git add .
git commit -m "feat: description"
git push origin branch-name

# View logs
git log --oneline -10
git show --stat
```

## System Utilities (macOS)
```bash
# File operations
ls -la                      # List files with details
find . -name "*.php"        # Find PHP files
grep -r "search_term" .     # Search in files
cp file1 file2             # Copy files
mv file1 file2             # Move/rename files

# Process management
ps aux | grep php          # Find PHP processes
kill -9 PID               # Kill process by PID

# Network
lsof -i :8000             # Check what's using port 8000
curl http://localhost:8000 # Test local server
```