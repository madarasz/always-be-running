# AlwaysBeRunning.net (ABR)

## Docker Development Environment (Recommended)

The easiest way to run ABR locally is using Docker:

```bash
# Start all services
docker compose up -d

# Build frontend assets (first time and after JS/CSS changes)
docker compose --profile build run --rm node

# Run database migrations
docker compose exec php php artisan migrate

# Import seed data
docker compose exec -T mysql mysql -u root -prootsecret netrunner < seed.sql
```

**Services:**
- App: http://localhost:8000
- phpMyAdmin: http://localhost:8080 (root/rootsecret)
- MySQL: localhost:3307 (abr/secret)

**Environment setup:**
1. Copy `docker/.env.docker` to `.env`
2. Add NetrunnerDB OAuth keys (ask main dev)
3. Add Google Maps API keys

### Manual Installation (Linux/Mac)

You will need the following in order to run ABR locally:
- MySQL (preferably)
- PHP 7.1
- PHP Composer
- NodeJs v10, NPM v6 recommended
- JQ - download via apt-get (Debian) or homebrew (Mac), this is NOT an npm module
- imagemagick

1. Clone ABR from GitHub
2. Install npm dependencies, install npm gulp globally

        npm install
        npm install -g gulp

3. Install PHP dependencies

        php composer.phar install

4. Run gulp to prepare assets (do this every time if you change JS or CSS)

        gulp

5. Configure the settings of your local environment. Rename the **.example.env** file to **.env**. Edit the DB settings to connect to your locally running DB. *Ask the main dev (madarasz / Necro) for NetrunnerDB keys*.
Google API keys, you can create yourself.
6. Prepare ID icons by running this script (downloads from NetrunnerDB, run it regularly)

        ./get_id_images.sh

7. Prepare DB tables

        php artisan migrate

8. Add Badges and Tournament Type and format data to your database by importing `seed.sql`

9. Run the webapp. It should be available at [http://localhost:8000](http://localhost:8000) afterwards.

        php artisan serve

10. Make yourself an admin. Go to the webapp in your browser. Login via NetrunnerDB to enter your user in the DB. Check your DB (use phpMyAdmin), in table **users** set the **admin** value of your user to 1. If you reload the webapp you should see the **Admin** section in the top menu.

12. Download all the data required from NetrunnerDB. Go to [Admin section](http://localhost:8000/admin) and click the **Update Card cycles**, **Update Card packs** and **Update Identities** buttons to get the data. Do this every time a new pack comes out.

13. You are done :)

## Automated E2E Tests (Vitest + Playwright)

Tests are in the `tests/` directory with their own dependencies (Node.js 20+).

```bash
# Install test dependencies (first time)
cd tests && npm install

# Run all E2E tests
cd tests && npm test

# Run tests in watch mode
cd tests && npm run test:watch
```

**Setup for authenticated tests:**
1. Copy `tests/e2e/.env.template` to `tests/e2e/.env`
2. Add NetrunnerDB test user credentials (REGULAR_USERNAME, REGULAR_PASSWORD)
3. Add admin credentials (ADMIN_USERNAME, ADMIN_PASSWORD)

Tests run automatically via GitHub Actions on push to `master` or `migration` branches.

**Test structure:**
```
tests/
├── package.json          # Test dependencies
├── vitest.config.ts
├── e2e/                  # Browser E2E tests
│   ├── tests/            # Test files (*.test.ts)
│   ├── pages/            # Page objects
│   ├── helpers/          # Auth helpers, mocks
│   └── fixtures/         # Test data, SQL seeds
└── api/                  # API tests (future)
```