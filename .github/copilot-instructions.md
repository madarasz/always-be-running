# AlwaysBeRunning.net (ABR) - AI Coding Instructions

## Project Overview
ABR is a Laravel 5.2 application for managing Netrunner card game tournaments. It's a comprehensive tournament platform with OAuth integration to NetrunnerDB, photo/video management, badge systems, and complex tournament workflows.

## Architecture & Key Patterns

### Core Domain Models
- **Tournament**: Central entity with complex state management (draft→approved→concluded)
- **Entry**: Tournament registrations with deck linking and ranking
- **User**: OAuth-based via NetrunnerDB, includes admin privileges and badge system
- **CardIdentity/CardPack/CardCycle**: Synced from NetrunnerDB API
- **Photo/Video**: User-generated content with approval workflows

### Authentication & Authorization
- OAuth2 integration with NetrunnerDB (`NetrunnerDBController`) - no traditional passwords
- Policy-based authorization (`app/Policies/`) for granular permissions
- Admin users have `admin=1` flag in database
- User profiles sync reputation/deck counts from NetrunnerDB

### Database Patterns
- Soft deletes used extensively (`SoftDeletes` trait)
- Performance-optimized relationship counting (see `Tournament::videosCount()`)
- Manual conflict detection for tournament rankings (`Tournament::updateConflict()`)
- Migration-driven schema with extensive historical migrations

## Development Workflows

### Asset Pipeline (Critical)
```bash
# Must run after any JS/CSS changes
gulp
# Or for production
gulp --production
```
Uses Laravel Elixir with custom gulpfile that copies Vue.js, Bootstrap, and custom assets.

### Database Setup
```bash
php artisan migrate
# Import seed data (badges, tournament types)
mysql -u user -p database < seed.sql
```

### NetrunnerDB Data Sync
Admin panel provides buttons to sync:
- Card identities: `/admin/identities/update`
- Card packs: `/admin/packs/update` 
- Card cycles: `/admin/cycles/update`

### Testing Environment
Dual package.json setup:
- `package.json`: Development with older Node for Elixir
- `test-package.json`: Cypress testing with Node 14
Switch between environments as documented in README.

## Key Integration Points

### NetrunnerDB API Integration
- OAuth token management in `NetrunnerDBController`
- Deck data fetching respects user privacy settings
- Automatic claim submission to NetrunnerDB for tournament results
- Card data synchronization (identities, packs, cycles, MWL)

### External Services
- Google Maps API for timezone/location data
- Facebook event import (`FBController`)
- YouTube video embedding with metadata extraction
- Image processing via Intervention Image

### Frontend Architecture
- Mix of server-rendered Blade templates and Vue.js components
- Vue components in `resources/assets/js/abr-vue.js` (inline templates)
- No build step for Vue - uses CDN version
- Bootstrap 4 alpha with custom Sass compilation

## Common Development Patterns

### Tournament State Management
```php
// Always check authorization
$this->authorize('admin', Tournament::class, $request->user());

// Update conflict status after entry changes
$tournament->updateConflict();

// Handle soft deletion
$tournament->delete(); // Soft delete
$tournament->forceDelete(); // Permanent
```

### API Responses
Most AJAX endpoints follow pattern:
```php
return response()->json(['success' => true, 'data' => $result]);
// or
return response()->json(['error' => 'Error message']);
```

### Policy Checks
```php
// In controllers
$this->authorize('update', $tournament);

// In views
@can('update', $tournament)
    <!-- show edit button -->
@endcan
```

## File Organization Notes
- Controllers organized by domain (`TournamentsController`, `EntriesController`)
- Models in root `app/` directory (Laravel 5.2 style)
- Vue components as global components, not SFC
- CSS/JS assets compiled via Laravel Elixir, not modern build tools
- Database migrations show evolution from GoT tournaments to Netrunner

## Common Gotchas
- Must run `gulp` after any frontend changes - no hot reload
- OAuth tokens stored in session, can expire during development
- Tournament conflicts auto-detected but can be manually relaxed
- Photo/video approval required before public display
- Badge system triggers on various user actions
- Timezone handling requires Google API for non-online tournaments
- Test environment requires separate Node version and package files