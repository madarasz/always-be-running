#!/bin/bash
# AlwaysBeRunning Docker Setup Script
# Usage: ./docker/scripts/docker-setup.sh [command]

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"

cd "$PROJECT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Setup .env file from docker template
setup_env() {
    if [ ! -f .env ]; then
        log_info "Creating .env from docker/.env.docker..."
        cp docker/.env.docker .env
    else
        log_warn ".env file already exists, skipping..."
    fi
}

# Build and start containers
start() {
    log_info "Starting Docker containers..."
    setup_env
    docker-compose up -d
    log_info "Waiting for MySQL to be ready..."
    sleep 10
    log_info "Containers started. Application available at http://localhost:8000"
}

# Stop containers
stop() {
    log_info "Stopping Docker containers..."
    docker-compose down
}

# Restart containers
restart() {
    stop
    start
}

# Build/rebuild containers
build() {
    log_info "Building Docker containers..."
    setup_env
    docker-compose build --no-cache
}

# Install PHP dependencies
composer_install() {
    log_info "Installing Composer dependencies..."
    docker-compose exec php composer install
}

# Run database migrations
migrate() {
    log_info "Running database migrations..."
    docker-compose exec php php artisan migrate
}

# Seed database
seed() {
    log_info "Seeding database..."
    docker-compose exec php php artisan db:seed
}

# Run Vite build to prepare assets
build_assets() {
    log_info "Building frontend assets with Vite..."
    docker-compose run --rm node sh -c "npm install && npm run build"
}

# Run artisan commands
artisan() {
    docker-compose exec php php artisan "$@"
}

# Open shell in PHP container
shell() {
    docker-compose exec php bash
}

# View logs
logs() {
    docker-compose logs -f "${1:-}"
}

# Full setup: build, start, install deps, migrate
setup() {
    log_info "Running full Docker setup..."
    setup_env
    build
    start
    log_info "Installing Composer dependencies..."
    sleep 5
    composer_install
    log_info "Running migrations..."
    migrate
    log_info "Building frontend assets..."
    build_assets
    log_info ""
    log_info "Setup complete!"
    log_info "Application available at: http://localhost:8000"
    log_info ""
    log_info "Next steps:"
    log_info "  1. Update .env with your NetrunnerDB OAuth credentials"
    log_info "  2. Login via NetrunnerDB and set admin=1 in users table"
    log_info "  3. Use Admin > Update to sync card data"
}

# Show status
status() {
    docker-compose ps
}

# Clean up everything
clean() {
    log_warn "This will remove all containers, volumes, and images for this project."
    read -p "Are you sure? (y/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        docker-compose down -v --rmi local
        log_info "Cleanup complete."
    fi
}

# Help
show_help() {
    echo "AlwaysBeRunning Docker Helper"
    echo ""
    echo "Usage: $0 [command]"
    echo ""
    echo "Commands:"
    echo "  setup          Full setup (build, start, install deps, migrate, build assets)"
    echo "  start          Start Docker containers"
    echo "  stop           Stop Docker containers"
    echo "  restart        Restart Docker containers"
    echo "  build          Build/rebuild Docker images"
    echo "  composer       Install Composer dependencies"
    echo "  migrate        Run database migrations"
    echo "  seed           Seed database"
    echo "  assets         Build frontend assets with Gulp"
    echo "  artisan [cmd]  Run artisan command"
    echo "  shell          Open shell in PHP container"
    echo "  logs [svc]     View container logs (optional: service name)"
    echo "  status         Show container status"
    echo "  clean          Remove all containers, volumes, and images"
    echo "  help           Show this help message"
    echo ""
}

# Main
case "${1:-help}" in
    setup)
        setup
        ;;
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart
        ;;
    build)
        build
        ;;
    composer)
        composer_install
        ;;
    migrate)
        migrate
        ;;
    seed)
        seed
        ;;
    assets)
        build_assets
        ;;
    artisan)
        shift
        artisan "$@"
        ;;
    shell)
        shell
        ;;
    logs)
        logs "${2:-}"
        ;;
    status)
        status
        ;;
    clean)
        clean
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        log_error "Unknown command: $1"
        show_help
        exit 1
        ;;
esac
