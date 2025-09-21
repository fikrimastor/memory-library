# Memory Library - Task Completion Checklist

## Before Committing Code

### 1. Code Quality Checks
```bash
# Format PHP code with Laravel Pint
./vendor/bin/pint --dirty

# Check frontend code quality
npm run lint
npm run format:check

# TypeScript validation
npx vue-tsc --noEmit
```

### 2. Testing Requirements
```bash
# Run affected tests first
php artisan test --filter=RelevantTestName

# Run full test suite before major commits
composer test
# or
php artisan test

# For frontend changes, check browser tests
php artisan test tests/Browser/
```

### 3. Database Integrity
```bash
# Ensure migrations work
php artisan migrate:fresh --seed

# Check for N+1 queries in development
# Review database queries in logs or debugbar
```

### 4. Build Verification
```bash
# Ensure frontend builds successfully
npm run build

# For SSR applications
npm run build:ssr
```

## Feature Development Checklist

### 1. Backend Development
- [ ] Create Action class in `app/Actions/`
- [ ] Add Form Request validation in `app/Http/Requests/`
- [ ] Create/update Eloquent models with proper relationships
- [ ] Add database migrations with appropriate indexes
- [ ] Create feature tests covering happy and edge cases
- [ ] Add API endpoints if needed with proper authentication

### 2. Frontend Development  
- [ ] Create TypeScript interfaces in `resources/js/types/`
- [ ] Build Vue components with proper prop validation
- [ ] Implement responsive design with Tailwind CSS
- [ ] Add error handling and loading states
- [ ] Create browser tests for critical user flows
- [ ] Ensure accessibility standards (ARIA labels, keyboard navigation)

### 3. MCP Integration (if applicable)
- [ ] Create MCP tool in `app/Mcp/Tools/`
- [ ] Add proper request validation and error handling
- [ ] Write comprehensive tests for MCP functionality
- [ ] Update MCP server registration if needed

### 4. Authentication & Authorization
- [ ] Implement proper route protection middleware
- [ ] Add authorization policies if needed
- [ ] Test authentication flows (login, registration, OAuth)
- [ ] Verify API token functionality for API endpoints

## Deployment Checklist

### 1. Environment Configuration
- [ ] Update `.env.example` with new environment variables
- [ ] Document required environment variables in README
- [ ] Ensure production-safe default values

### 2. Performance Optimization
- [ ] Check for N+1 query problems
- [ ] Optimize database queries with proper indexing
- [ ] Minify and optimize frontend assets
- [ ] Test application performance under load

### 3. Security Review
- [ ] Validate all user inputs are properly sanitized
- [ ] Check for SQL injection vulnerabilities
- [ ] Ensure XSS protection is in place
- [ ] Review authentication and authorization logic
- [ ] Verify API rate limiting is configured

### 4. Documentation
- [ ] Update API documentation if applicable
- [ ] Document new features in code comments
- [ ] Update setup instructions if changed
- [ ] Create/update user documentation

## Git Commit Standards

### Conventional Commits Format
```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Commit Types
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples
```bash
git commit -m "feat: add GitHub OAuth integration with social account management"
git commit -m "fix: resolve memory search pagination issue"
git commit -m "test: add comprehensive browser tests for API token management"
git commit -m "refactor: extract embedding logic into dedicated service class"
```

## Production Deployment Steps

### 1. Pre-deployment
```bash
# Run full test suite
composer test

# Build production assets
npm run build

# Check for security vulnerabilities
composer audit
npm audit
```

### 2. Deployment Commands
```bash
# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart
```

### 3. Post-deployment Verification
- [ ] Check application health endpoints
- [ ] Verify database connections
- [ ] Test critical user flows
- [ ] Monitor error logs
- [ ] Verify queue processing is working
- [ ] Check OAuth integrations are functional