.PHONY: help build up down restart setup install env key assets migrate seed fresh test cs-fix analyse quality logs shell

CONTAINER = station_example
EXEC = docker exec $(CONTAINER)

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

# --- Docker ---

build: ## Build and start the container
	docker compose up -d --build

up: ## Start the container
	docker compose up -d

down: ## Stop the container
	docker compose down

restart: ## Restart the container
	docker compose restart

# --- Setup ---

setup: build install env key assets migrate ## Full setup (build, install, env, key, assets, migrate)

install: ## Install composer dependencies
	$(EXEC) composer install --no-interaction --prefer-dist

env: ## Copy .env.example to .env (if missing)
	$(EXEC) sh -c "test -f .env || cp .env.example .env"

key: ## Generate application key
	$(EXEC) php artisan key:generate

assets: ## Publish Station assets
	$(EXEC) php artisan vendor:publish --tag=station-assets --force

migrate: ## Run migrations
	$(EXEC) php artisan migrate --force

seed: ## Seed demo data (fresh migration + seed)
	$(EXEC) php artisan migrate:fresh --seed --force

fresh: ## Fresh migration without seed
	$(EXEC) php artisan migrate:fresh --force

# --- Quality ---

test: ## Run tests
	$(EXEC) php artisan test

cs-fix: ## Fix code style
	$(EXEC) composer cs-fix

cs-check: ## Check code style (dry-run)
	$(EXEC) composer cs-fix -- --dry-run --diff

analyse: ## Run PHPStan static analysis
	$(EXEC) composer analyse

quality: cs-fix analyse test ## Run all quality checks (cs-fix, analyse, test)

ci: cs-check analyse test ## Run CI checks (cs-check, analyse, test)

# --- Utilities ---

logs: ## Tail Laravel logs
	$(EXEC) tail -f storage/logs/laravel.log

shell: ## Open a shell in the container
	docker exec -it $(CONTAINER) sh

clear: ## Clear all caches
	$(EXEC) php artisan config:clear
	$(EXEC) php artisan cache:clear
