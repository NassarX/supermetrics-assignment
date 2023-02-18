# import env variables.
# You can change the default pattern with `make PATTERN="*.env.dist" build`
PATTERN ?= *.env.dist
env_files := $(shell find . -name $(PATTERN))

# HELP - This will output the help for each task
.PHONY: help

help: ## This help.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.DEFAULT_GOAL := help


# DOCKER TASKS

## Copy .env.dist files
define copy_envs
	$(foreach file, $(env_files), cp $(file) $(file:.env.dist=.env);)
endef

## Enable XDEBUG Mode
define enable_xdebug
	$(if [ -f env/php-cli.env ], \
	sed 's/^\(PHP_XDEBUG_ENABLE\)=.*/\1=yes/g' env/php-cli.env > env/php-cli.env.bak \
	&& mv env/php-cli.env.bak env/php-cli.env)

	$(if [ -f env/php-fpm.env ], \
	sed 's/^\(PHP_XDEBUG_ENABLE\)=.*/\1=yes/g' env/php-fpm.env > env/php-fpm.env.bak \
	&& mv env/php-fpm.env.bak env/php-fpm.env)
endef

## Update clientID
define update_clientId
	$(if [ -f app/.env ], \
	sed 's/^\(FICTIONAL_SOCIAL_API_CLIENT_ID\)=.*/\1=ju16a6m81mhid5ue1z3v2g0uh/g' app/.env > app/.env.bak \
	&& mv app/.env.bak app/.env)
endef

up: ## Copy .env.dist file, Starts and Runs the containers
	@$(call copy_envs)
	@$(call enable_xdebug)
	@$(call update_clientId)
	docker-compose up

up-detach: ## Starts and Runs the containers in detached mode
	@$(call copy_envs)
	@$(call enable_xdebug)
	@$(call update_clientId)
	docker-compose up --detach

stop: ## Stop and remove a running container
	docker-compose stop

down: ## Remove running images and delete .env files
	docker-compose down --rmi all
	@rm -f -- $(foreach file, $(env_files), $(file:.env.dist=.env))

restart: down up-detach


##@TODO To fix `host platform (linux/amd64) and (linux/arm64/v8) conflicts on M1 Apple Silicon`


