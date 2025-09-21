# Memory Library

Memory Library is a personal memory management application that stores, organizes, and semantically searches your memories using AI-powered embeddings.

It combines intelligent memory management with semantic search capabilities through a modern web interface and AI assistant integration via MCP.

Memory Library allows users to store memories with rich metadata, search them semantically, and access them through the web, API, and MCP tools.

## Important Files

To learn the most from Memory Library and Laravel MCP, take a look at these directories & files:

- `routes/ai.php`
- `app/Mcp/Servers/MemoryLibrary.php`
- `app/Mcp/Tools/`
- `app/Mcp/Actions/`
- `routes/api.php`

## API

Memory Library provides a REST API for programmatic access. API endpoints are available under `/api/` with OAuth2 authentication.

### Getting an API Token

1. Log in to your Memory Library account
2. Go to Settings → Profile (`/settings/api-tokens`)
3. Create a new personal access token in the API Tokens section
4. Use the token in the `Authorization: Bearer YOUR_TOKEN` header for API requests

## Auth

This app uses [Laravel Passport](https://laravel.com/docs/passport) for both MCP OAuth authentication and API token authentication.

Users can manage their API tokens in the user profile settings area (`/settings/api-tokens`).

## MCP

Memory Library comes with an MCP server located at http://memory-library.test/mcp, with tools, resources, and prompts for AI assistant integration.

# Setup

```shell
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate

npm install
npm run build
```

# Development

```shell
# Full development environment
composer dev

# Individual services
php artisan serve
npm run dev
php artisan queue:listen --tries=1
php artisan pail --timeout=0
```

# HTTP Notes

Many AI agents use Node which comes with its own certificate store, meaning they'll fail to connect to an MCP server on `https://`. We recommend leaving Memory Library on `http://` locally for testing with AI agents, and using `https://` on production.