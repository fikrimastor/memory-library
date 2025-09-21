# Memory Library - Project Overview

## Purpose
Memory Library is a Laravel application that provides a persistent memory management system with MCP (Model Context Protocol) integration. It allows users to store, search, and retrieve memories using semantic embedding search powered by Cloudflare's AI models.

## Core Features
1. **Memory Management**: Store and organize personal memories with metadata, tags, and project association
2. **Semantic Search**: Vector-based search using Cloudflare embeddings with cosine similarity
3. **MCP Integration**: Model Context Protocol server for AI assistant integration
4. **GitHub OAuth**: Social authentication and account linking
5. **API Token Management**: Laravel Passport OAuth tokens for API access
6. **Two-Factor Authentication**: Enhanced security with 2FA support
7. **Modern VILT Stack**: Vue.js + Inertia.js + Laravel + Tailwind CSS

## Technology Stack
- **Backend**: Laravel 12.0, PHP 8.4
- **Frontend**: Vue 3.5 + TypeScript, Inertia.js 2.0
- **Styling**: Tailwind CSS 4.1, Reka UI components
- **Database**: SQLite (default), with vector support for embeddings
- **Authentication**: Laravel Fortify + Passport + Socialite
- **Testing**: Pest 4.1 with Browser testing support
- **Build**: Vite 7.0 with Laravel Wayfinder plugin
- **AI/ML**: Cloudflare embedding models (@cf/baai/bge-m3)

## Key Architecture Patterns
- **Action Pattern**: Business logic encapsulated in dedicated Action classes
- **MCP Tools**: Modular tools for memory operations (Add, Search, Configure, Status)
- **Job Queue**: Asynchronous embedding generation with cleanup jobs
- **Service Pattern**: EmbeddingManager for AI model integration
- **Repository Pattern**: Structured data access with Eloquent models

## Recent Development Progress
- ✅ GitHub OAuth integration with social account management
- ✅ API token management system with Laravel Passport
- ✅ Comprehensive test suite with Pest 4.1 (Unit, Feature, Browser)
- ✅ Memory management with vector embeddings
- ✅ MCP server integration for AI assistants
- ✅ Modern VILT stack implementation