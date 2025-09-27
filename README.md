<p align="center">
    <img height="200" alt="Memory Library MCP Logo" src="/public/favicon.svg" />
</p>

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
Will be documented further in the future, currently in progress.

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
    - Create a token with `Worker AI Read` and `Worker AI Write` permissions

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
php artisan passport:keys

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

# Why did you build Memory Library?

## Primary Pain Points

### 1. Context Gap Problem 🔍
**Issue:** LLMs lack understanding of existing codebase structure and patterns

- **Symptom:** When requesting new features, AI responses are generic and disconnected from project reality
- **Impact:** Solutions don't align with established architecture or coding conventions
- **Example:** Asking for a new Laravel feature results in vanilla Laravel code that ignores existing service patterns, custom abstractions, or project-specific implementations
- **Business Cost:** Development time increases due to rework and architectural misalignment

### 2. Manual Context Loading Overhead ⏱️
**Issue:** Developers must manually copy and explain code to LLMs repeatedly

- **Symptom:** Significant overhead in context preparation before productive work begins
- **Impact:** Breaking development flow and reducing productivity
- **Example:** Need to paste controller code, service classes, migrations, and documentation every time asking for related functionality
- **Business Cost:** Estimated 30-40% of AI interaction time spent on context setup rather than problem-solving

### 3. Inconsistent AI Suggestions 🎯
**Issue:** Without proper context, AI suggestions don't match project patterns

- **Symptom:** Recommendations that require significant modification to fit codebase
- **Impact:** Suboptimal code quality and architectural drift
- **Example:** AI suggests using standard Laravel validation when project uses custom form request patterns with specific error handling
- **Business Cost:** Technical debt accumulation and inconsistent code patterns

### 4. Limited Code Discovery 🔎
**Issue:** LLMs cannot efficiently find relevant code sections or understand project-wide patterns

- **Symptom:** Cannot leverage existing implementations or identify reusable components
- **Impact:** Code duplication and missed optimization opportunities
- **Example:** Re-implementing functionality that already exists in different modules because AI cannot discover or reference existing solutions
- **Business Cost:** Maintenance overhead and missed efficiency gains

### 5. Knowledge Fragmentation 🧩
**Issue:** Project knowledge scattered across different conversations and contexts

- **Symptom:** Loss of accumulated context between sessions
- **Impact:** Repeated explanations and loss of development momentum
- **Example:** Previous architectural decisions, design patterns, or implementation details lost when starting new chat sessions
- **Business Cost:** Knowledge management overhead and decision re-work

### 6. Work With Multiple LLMs 🤖
**Issue:** Different LLMs have varying capabilities and context handling

- **Symptom:** Switching between models leads to inconsistent results
- **Impact:** Fragmented development experience
- **Example:** Some discussions work happen in Claude Desktop, continue finalising the requirements in Claude mobile, and execute the task in claude code CLI. This would lead to loss of context and inconsistent results.
- **Business Cost:** Increased cognitive load and context switching overhead

## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any
contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also
simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Change Log

Please see the [CHANGELOG](./CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to Fikri Mastor via [hello@fikrimastor.com](mailto:hello@fikrimastor.com). All security vulnerabilities will be promptly addressed.

## License

The Memory Library is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).