# Repository Guidelines

## Project Structure & Module Organization
Memory Library couples Laravel 12 with an Inertia + Vue 3 front end. Core domain code sits in `app/`, with HTTP controllers in `app/Http` and jobs in `app/Jobs`; routes stay in `routes/`. Vue pages and shared UI live in `resources/js`, Tailwind styles in `resources/css`, and Blade mailers in `resources/views`. Database migrations, factories, and seeders belong in `database/`. Additional references land in `docs/`. Automated coverage is split across `tests/Unit`, `tests/Feature`, and `tests/Browser`.

## Build, Test, and Development Commands
Install dependencies with `composer install` and `npm install`. Use `composer run dev` to launch the Laravel server, queue listener, log tailer, and Vite watcher together. Run `npm run dev` when you only need the asset pipeline, `npm run build` for a production bundle, and `npm run build:ssr` when SSR output is required. Execute `php artisan test` for the full Pest suite or `./vendor/bin/pest --group=feature` to target specific groups.

## Coding Style & Naming Conventions
The root `.editorconfig` enforces UTF-8, LF endings, and four-space indentation. Format Vue and TypeScript sources with `npm run format` (Prettier + Tailwind plugin) and verify with `npm run format:check`; lint using `npm run lint`, which applies the flat ESLint + Vue TS config. Use PascalCase for Vue components, kebab-case for component folders, StudlyCase for PHP classes, and snake_case for database columns. Run `./vendor/bin/pint` before committing PHP changes.

## Testing Guidelines
Pest with PHPUnit powers the test suite (`tests/Pest.php`). Keep isolated logic in `tests/Unit`, HTTP flows in `tests/Feature`, and browser automation in `tests/Browser`. Feature tests already include `RefreshDatabase`, so rely on factories and seeds instead of manual rollbacks. Name files with the `*Test.php` suffix and leverage the in-memory SQLite connection from `phpunit.xml` to keep runs fast and deterministic.

## Commit & Pull Request Guidelines
Follow the observed convention: `feat|fix|chore: imperative summary #issue`. Commit cohesive feature slices that bundle backend, Vue, and migration work together. Pull requests should link the relevant issue, describe behavioural changes, list the commands you ran, and attach UI screenshots or clips when adjusting Inertia pages.

## Environment & Configuration Notes
Copy `.env.example` to `.env`, then run `php artisan key:generate` if the post-create hook has not fired. Local development uses the bundled `database/database.sqlite`; configure `DB_CONNECTION` in `.env` when targeting MySQL or Postgres. Keep credentials out of version control and share secrets through the secure notes documented in `docs/`.
