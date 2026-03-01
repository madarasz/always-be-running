# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AlwaysBeRunning (ABR) is a tournament management platform for the Netrunner card game. It handles tournament creation, player registration, results tracking, badges/achievements, and integrates with NetrunnerDB for card data and OAuth authentication.

**Stack:** PHP/Laravel 5.2, MySQL, jQuery, Vue.js 2.x, Bootstrap 4, Gulp with Laravel Elixir

## Common Commands

### Docker Development (Recommended)

```bash
# Start all services
docker compose up -d

# Build frontend assets (first time and after JS/CSS changes)
docker compose --profile build run --rm node

# Run database migrations
docker compose exec php php artisan migrate

# Stop all services
docker compose down
```

**Docker services:**
- App: http://localhost:8000
- phpMyAdmin: http://localhost:8080 (root/rootsecret)
- MySQL: localhost:3307 (abr/secret)

### Manual Development

```bash
# Install dependencies
npm install && npm install -g gulp
php composer.phar install

# Build assets (run after changing JS/CSS)
gulp

# Development watch mode
npm run dev

# Production build
npm run prod

# Start development server (http://localhost:8000)
php artisan serve

# Database migrations
php artisan migrate

# Download ID card images from NetrunnerDB
./get_id_images.sh
```

## Testing

E2E tests use Vitest + Playwright (via agent-browser). Tests are in `tests/` with separate dependencies (Node.js 20+):

```bash
# Install test dependencies
cd tests && npm install

# Run all E2E tests
cd tests && npm test

# Run in watch mode
cd tests && npm run test:watch
```

**Test credentials:** Copy `tests/e2e/.env.template` to `tests/e2e/.env` and fill in NetrunnerDB test user credentials.

**Test structure:**
```
tests/
├── package.json          # Test dependencies (Vitest, agent-browser)
├── vitest.config.ts
├── e2e/                  # Browser E2E tests
│   ├── tests/            # Test files (*.test.ts)
│   ├── pages/            # Page objects (BasePage, UpcomingPage, etc.)
│   ├── helpers/          # Auth helpers, mocks
│   └── fixtures/         # Test data, SQL seeds
└── api/                  # API tests (future)
```

## Architecture

### Backend (app/)
- **Controllers** (`app/Http/Controllers/`): `TournamentsController` is the largest, handling tournament CRUD and management. Other key controllers: `EntriesController`, `AdminController`, `NetrunnerDBController` (OAuth)
- **Models** (`app/Models/`): Core entities are `Tournament` (soft-deletes enabled), `Entry`, `User`, `Badge`, `Video`, `CardIdentity`
- **Routes** (`app/Http/routes.php`): 130+ routes including RESTful endpoints and API routes

### Frontend (resources/assets/)
- **JavaScript modules** (`js/`): Prefixed `abr-*.js` files - `abr-main.js` (UI interactions), `abr-vue.js` (Vue components), `abr-map.js` (Google Maps), `abr-calendar.js`
- **Vue components**: Defined globally in `abr-vue.js` - includes confirm modals, buttons
- **Views** (`resources/views/`): Blade templates organized by feature (tournaments/, admin/, profile/)

### Database
- 69 migrations in `database/migrations/`
- Key tables: `tournaments`, `entries`, `users`, `badges`, `card_cycles`, `card_packs`, `card_identities`, `videos`, `prizes`
- Seed data in `seed.sql` (badges, tournament types/formats)

### API Endpoints
- `/api/tournaments` - Tournament JSON data
- `/api/entries` - Player entries
- `/api/nrtm` - NRTM (Netrunner Tournament Manager) integration
- `/api/prizes`, `/api/prize-collections`, `/api/tournament-groups`, `/api/artists`

## Key Integrations

- **NetrunnerDB**: OAuth login, card pool data sync (Admin > Update buttons)
- **Google Maps**: Tournament location display via Place ID
- **YouTube/Twitch**: Video embedding and channel integration
- **Facebook**: Event import capability

## Environment Configuration

Copy `.example.env` to `.env`. Required keys:
- Database: `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- NetrunnerDB OAuth: `NETRUNNERDB_CLIENT_ID`, `NETRUNNERDB_CLIENT_SECRET` (ask main dev)
- Google APIs: `GOOGLE_MAP_ID`, `GOOGLE_FRONTEND_API`, `GOOGLE_BACKEND_API`, `GOOGLE_MAPS_API`

## Admin Setup

After first login via NetrunnerDB, set `admin=1` in the `users` table to access Admin section. Use Admin to sync card data from NetrunnerDB.
