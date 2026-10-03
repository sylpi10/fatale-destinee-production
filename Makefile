.PHONY: help build deploy deploy-test remote

# Variables de déploiement (non versionnées) : SERVER_USER, SERVER_HOST, APP_PATH
ifneq (,$(wildcard .env.deploy))
include .env.deploy
export
endif

# Binaires côté serveur, surchargeables dans .env.deploy (ex. PHP=/usr/local/bin/php8.4)
PHP ?= php
COMPOSER ?= composer
# URL publique du site, écrite dans le .env.local du serveur au premier déploiement
APP_URL ?= https://$(APP_PATH)
# 0 tant qu'il n'y a pas de base de données en prod
MIGRATE ?= 1

ifeq ($(strip $(APP_PATH)),)
$(error APP_PATH est vide : sans lui, rsync --delete viserait tout le home du serveur)
endif

SSH = ssh $(SERVER_USER)@$(SERVER_HOST)
CONSOLE = cd ~/$(APP_PATH) && $(PHP) bin/console --env=prod --no-interaction

# Ce qui ne part jamais sur le serveur. Les fichiers exclus ne sont pas non plus
# supprimés par --delete : le .env.local et le var/ du serveur sont préservés.
RSYNC_EXCLUDES = \
	--exclude=/.git/ \
	--exclude=/.idea/ \
	--exclude=/.zed/ \
	--exclude=/node_modules/ \
	--exclude=/vendor/ \
	--exclude=/var/ \
	--exclude=/tests/ \
	--exclude=/.env.local \
	--exclude=/.env.*.local \
	--exclude=/.env.deploy \
	--exclude=/.phpunit.cache/ \
	--exclude=/tmp/ \
	--exclude=/phpunit.xml

help:
	@echo "make build        build Vite client + SSR (public/build, bootstrap/ssr)"
	@echo "make deploy-test  build + rsync --dry-run : affiche ce qui partirait"
	@echo "make deploy       build + rsync + composer install + cache + migrations"

build:
	npm ci
	npm run build

deploy-test: build
	rsync -av --itemize-changes --dry-run --delete $(RSYNC_EXCLUDES) \
		./ $(SERVER_USER)@$(SERVER_HOST):~/$(APP_PATH)

deploy: build
	rsync -av --itemize-changes --delete $(RSYNC_EXCLUDES) \
		./ $(SERVER_USER)@$(SERVER_HOST):~/$(APP_PATH)
	$(MAKE) remote

remote:
	# premier déploiement : crée le .env.local de prod (APP_SECRET généré sur le serveur), jamais écrasé ensuite
	$(SSH) 'cd ~/$(APP_PATH) && test -f .env.local || { printf "APP_ENV=prod\nAPP_SECRET=%s\nDEFAULT_URI=$(APP_URL)\n" "$$($(PHP) -r "echo bin2hex(random_bytes(16));")" > .env.local && chmod 600 .env.local && echo ".env.local créé"; }'
	$(SSH) 'cd ~/$(APP_PATH) && APP_ENV=prod $(COMPOSER) install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction'
ifeq ($(MIGRATE),1)
	$(SSH) '$(CONSOLE) doctrine:migrations:migrate --allow-no-migration'
endif
	$(SSH) '$(CONSOLE) cache:clear'
	# redémarre l'app Node SSR (Passenger) si elle est configurée dans cPanel
	$(SSH) 'mkdir -p ~/$(APP_PATH)/tmp && touch ~/$(APP_PATH)/tmp/restart.txt'
