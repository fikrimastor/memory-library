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


# Cloudflare Embedding Model Setup for Production

To use Cloudflare's AI embedding models in production, you'll need to configure the following environment variables in your `.env` file:

```env
# Set Cloudflare as the default embedding provider
EMBEDDING_PROVIDER=cloudflare

# Cloudflare Settings
CLOUDFLARE_API_TOKEN=your_cloudflare_api_token
CLOUDFLARE_ACCOUNT_ID=your_cloudflare_account_id
CLOUDFLARE_EMBEDDING_MODEL="@cf/baai/bge-m3"
CLOUDFLARE_EMBEDDING_MODEL_DIMENSION=1024
```

## Getting Cloudflare Credentials

1. **API Token**:
    - Go to the [Cloudflare dashboard](https://dash.cloudflare.com/)
    - Navigate to **User Profile** → **API Tokens**
    - Create a token with `AI Gateway Read` and `AI Gateway Write` permissions

2. **Account ID**:
    - Available in the Cloudflare dashboard URL after logging in
    - Format: `https://dash.cloudflare.com/{account_id}`

3. **Enable Workers AI**:
    - Go to **Workers & Pages** → **AI** in the Cloudflare dashboard
    - Enable Workers AI if not already enabled

## Supported Models

Memory Library supports these Cloudflare embedding models:
- `@cf/baai/bge-m3` (1024 dimensions) - Recommended
- `@cf/baai/bge-large-en-v1.5` (1024 dimensions)
- `@cf/baai/bge-base-en-v1.5` (768 dimensions)

After setting these environment variables, run the following commands to apply the changes:

```shell
php artisan config:cache
php artisan cache:clear
```

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