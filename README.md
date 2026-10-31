# Fatale Destinée Production

Symfony 8 + Twig, Symfony UX (Stimulus, Turbo, TwigComponent), AssetMapper, Sass. Pas de Node.

## Installation

```bash
composer install
php bin/console importmap:install
php bin/console doctrine:migrations:migrate
```

## Lancer en dev

```bash
symfony serve -d                       # http://127.0.0.1:8000
symfony console sass:build --watch     # recompile assets/styles/app.scss à chaque modif
```

Ne pas lancer `asset-map:compile` en local : `public/assets/` prendrait le pas sur les fichiers
servis à la volée et les modifs n'apparaîtraient plus (supprimer `public/assets/` si c'est arrivé).

## Front

- Pages : `templates/<page>/index.html.twig`, layout dans `templates/base.html.twig`.
- Composants Twig anonymes (`<twig:PageHeader />`…) : `templates/components/`.
- Contrôleurs Stimulus : `assets/controllers/` (`site_header`, `lightbox`).
- Formulaires : Symfony Form, thème commun dans `templates/form/site_theme.html.twig`.
- Styles : `assets/styles/app.scss` et ses partials.

## Tests

```bash
php bin/console doctrine:migrations:migrate --env=test   # une fois, base SQLite de test
php bin/phpunit
```

## Déploiement

```bash
make deploy-test   # rsync --dry-run
make deploy        # rsync + composer install + migrations + cache + assets
```
