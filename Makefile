.PHONY: help deploy deploy-test remote

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
# supprimés par --delete : le .env.local, le var/ et les fichiers envoyés depuis
# l'admin (public/uploads) du serveur sont préservés.
RSYNC_EXCLUDES = \
	--exclude=/.git/ \
	--exclude=/.idea/ \
	--exclude=/.zed/ \
	--exclude=/vendor/ \
	--exclude=/var/ \
	--exclude=/assets/vendor/ \
	--exclude=/public/assets/ \
	--exclude=/public/uploads/*/* \
	--exclude=/tests/ \
	--exclude=/.env.local \
	--exclude=/.env.dev \
	--exclude=/.env.*.local \
	--exclude=/.env.deploy \
	--exclude=/.phpunit.cache/ \
	--exclude=/tmp/ \
	--exclude=/phpunit.xml

help:
	@echo "make deploy-test  rsync --dry-run : affiche ce qui partirait"
	@echo "make deploy       rsync + composer install + assets + cache + migrations"

deploy-test:
	rsync -av --itemize-changes --dry-run --delete $(RSYNC_EXCLUDES) \
		./ $(SERVER_USER)@$(SERVER_HOST):~/$(APP_PATH)

deploy:
	rsync -av --itemize-changes --delete $(RSYNC_EXCLUDES) \
		./ $(SERVER_USER)@$(SERVER_HOST):~/$(APP_PATH)
	$(MAKE) remote

remote:
	# premier déploiement : crée le .env.local de prod (APP_SECRET généré sur le serveur), jamais écrasé ensuite
	$(SSH) 'cd ~/$(APP_PATH) && test -f .env.local || { printf "APP_ENV=prod\nAPP_SECRET=%s\nDEFAULT_URI=$(APP_URL)\n" "$$($(PHP) -r "echo bin2hex(random_bytes(16));")" > .env.local && chmod 600 .env.local && echo ".env.local créé"; }'
	# le cache prod n'est jamais recompilé tout seul (debug=false) : on le jette avant que composer ne boote le kernel
	$(SSH) 'rm -rf ~/$(APP_PATH)/var/cache/prod'
	$(SSH) 'cd ~/$(APP_PATH) && APP_ENV=prod $(COMPOSER) install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction'
ifeq ($(MIGRATE),1)
	$(SSH) '$(CONSOLE) doctrine:migrations:migrate --allow-no-migration'
endif
	$(SSH) '$(CONSOLE) cache:clear'
	# assets compilés sur le serveur : vendors JS, Sass, puis copie versionnée dans public/assets
	$(SSH) '$(CONSOLE) importmap:install && $(CONSOLE) sass:build && $(CONSOLE) asset-map:compile'
