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
- Composants Twig anonymes (`<twig:PageTitle />`, `<twig:SiteMenu />`, `<twig:FeltBorder />`…) : `templates/components/`.
- Contrôleurs Stimulus : `assets/controllers/` (`menu`, `logo_sound`).
- DA (brief client) : fond `#F5F2E9`, rouge `#A20505`, Oswald (menu), Lora (texte), Bebas Neue (titres), Caveat (accroches). Variables dans `assets/styles/_variables.scss`.
- Formulaires : Symfony Form, thème commun dans `templates/form/site_theme.html.twig`.
- Styles : `assets/styles/app.scss` et ses partials.

## Admin

EasyAdmin sur `/admin` (rôle `ROLE_ADMIN`), connexion via `/login`. Pas d'inscription : les comptes se créent en console.

```bash
php bin/console app:admin:create pseudo                # demande le mot de passe ; le réinitialise si le compte existe
php bin/console doctrine:fixtures:load                # dev : spectacles du brief + admin / admin1234 (vide la base !)
```

Les fichiers envoyés depuis l'admin vont dans `public/uploads/` : ignorés par git et jamais écrasés par `make deploy`.

## Tests

```bash
php bin/console doctrine:database:create --env=test      # une fois : base fataledestinee_test (MariaDB)
php bin/console doctrine:migrations:migrate --env=test
php bin/phpunit
```

## Déploiement

```bash
make deploy-test   # rsync --dry-run
make deploy        # rsync + composer install + migrations + cache + assets
```
