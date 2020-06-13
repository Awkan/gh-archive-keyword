#!make

# Setup ——————————————————————————————————————————————————————————————————————————————————

DOCKER = docker
DOCKER_COMPOSE = docker-compose

# TODO : Uncomment on registry enabling, in order to use them for PHP NATIVE requests
# DOCKER_IMAGE_NGINX=registry.gitlab.com/xxxxx/nginx:1.0
# DOCKER_IMAGE_PHP=registry.gitlab.com/xxxxx/php:1.0

EXEC_PHP        = $(DOCKER_COMPOSE) exec php
EXEC_PHP_ROOT   = $(DOCKER_COMPOSE) exec -T --user=root php
# TODO : Use real native PHP on registry enabling
# EXEC_PHP_NATIVE = $(DOCKER) run --rm -v $(CURDIR)/app:/var/www/gh-archive-keyword $(DOCKER_NAME_PHP)
EXEC_PHP_NATIVE = $(EXEC_PHP)

SYMFONY = $(EXEC_PHP) bin/console
COMPOSER = $(EXEC_PHP) composer

.DEFAULT_GOAL = help

## —— The Makefile ——————————————————————————————————————————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

wait: ## Sleep 5 seconds
	sleep 5

## —— Docker ————————————————————————————————————————————————————————————————————————————
initialize: ## Initialize a ready to dev Docker stack
initialize: down pullImages start install

build: ## Build local images
	$(DOCKER_COMPOSE) build

start: ## Start Docker stack
start: pullImages
	$(DOCKER_COMPOSE) up -d
	$(DOCKER_COMPOSE) ps

down: ## Down Docker stack
	$(DOCKER_COMPOSE) down

pullImages: ## Pull Docker images
	$(DOCKER_COMPOSE) pull


.PHONY = initialize build start down pullImages

## —— App installation ——————————————————————————————————————————————————————————————————

install: ## Install app thanks to Docker
install: installVendors resetDatabase

installVendors: ## Install app vendors
	$(COMPOSER) install

resetDatabase: ## Delete and re-create clean database
	$(EXEC_PHP_ROOT) bash -c 'until nc -z db 5432; do sleep 1; echo "Waiting for DB to come up..."; done'
	$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) doctrine:schema:drop --force
	$(SYMFONY) doctrine:migrations:migrate --no-interaction

.PHONY = install installVendors resetDatabase

## —— Test ——————————————————————————————————————————————————————————————————————————————

tests: ## Run all tests
tests: unitTests functionalTests

unitTests: ## Run unit tests
	$(EXEC_PHP_NATIVE) ./vendor/bin/simple-phpunit

functionalTests: ## Run functional tests
functionalTests: initialize
	$(EXEC_PHP) bash -c "APP_ENV=test ./vendor/bin/behat"

.PHONY = tests unitTests functionalTests


## —— Quality Assurance —————————————————————————————————————————————————————————————————

lint: ## Lint files
lint: lintYaml

lintYaml: ## Lint YAML files
	$(EXEC_PHP_NATIVE) bin/console lint:yaml config
	$(EXEC_PHP_NATIVE) bin/console lint:yaml src
#	$(EXEC_PHP_NATIVE) bin/console lint:yaml fixtures

## —— PHP
csFixer: ## Apply php-cs-fixer
	$(EXEC_PHP_NATIVE) vendor/bin/php-cs-fixer fix --config=.php_cs.dist --using-cache=no --verbose --diff

csFixerLint: ## Lint php-cs-fixer
	$(EXEC_PHP_NATIVE) vendor/bin/php-cs-fixer fix --config=.php_cs.dist --dry-run --using-cache=no --verbose --diff

phpStan: ## PHPStan Check
	$(EXEC_PHP_NATIVE) vendor/bin/phpstan analyse -c phpstan.neon -l7 src

security: ## Check if there is known vulnerabilities
	$(EXEC_PHP_NATIVE) vendor/bin/security-checker security:check

.PHONY = lint lintYaml csFixer csFixerLint phpStan security
