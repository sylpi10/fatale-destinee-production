# Fatale Destinée Production

Symfony 8 + React 19 (TypeScript) via Inertia.js, Vite, Sass.

## Installation

```bash
composer install
npm install
```

## Lancer en dev

```bash
symfony serve -d   # serveur PHP → http://127.0.0.1:8000
npm run dev        # Vite : JS + Sass compilés à la volée, rechargement auto
```

## Lancer en SSR

```bash
# .env.local : INERTIA_SSR=1
symfony console cache:clear   # obligatoire après avoir changé INERTIA_SSR
npm run build:ssr             # bundle Node → bootstrap/ssr/ssr.js
symfony console inertia:start-ssr
```

Après chaque modif front : refaire `npm run build:ssr` et relancer `inertia:start-ssr`.

## Sass

Pas de watcher à part : Vite compile `assets/styles/app.scss` (et ses partials).

```bash
npm run dev     # watch + hot reload
npm run build   # compilation prod → public/build (+ bundle SSR)
```
