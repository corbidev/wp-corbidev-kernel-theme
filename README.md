# CorbiDev Kernel v1.2.0

## Production-ready WordPress Kernel with Progressive Loading

Kernel PHP 8.1+ pour thèmes WordPress CorbiDev avec système de chargement progressif intégré.

## 🚀 Nouveauté v1.2.0 : Progressive Loading

Le kernel gère automatiquement le chargement progressif des assets pour un **First Contentful Paint ultra-rapide** (< 0.5s).

### 3 Stratégies Disponibles

1. **Progressive** (recommandé) :
   - HTML minimal → Affichage immédiat
   - Spinner pendant le chargement
   - Assets Vite/Vue en différé
   - Performance : ⭐⭐⭐⭐⭐

2. **Critical** :
   - Critical CSS inline
   - Reste en différé
   - Performance : ⭐⭐⭐⭐⭐

3. **Blocking** :
   - Chargement classique
   - Performance : ⭐⭐⭐

## Fonctionnalités

- ✅ Architecture Service Provider
- ✅ Container d'injection de dépendances
- ✅ EventDispatcher complet
- ✅ **Progressive Loading System** (nouveau)
- ✅ Détection automatique d'environnement
- ✅ Compatible WordPress classique & Bedrock
- ✅ Tests unitaires inclus

## Installation

```bash
composer require corbidev/wp-corbidev-kernel-theme
```

## Utilisation de Base

```php
use CorbiDev\Kernel\Theme\Kernel;

Kernel::boot([
    'theme' => 'my-theme',
    'loading_strategy' => 'progressive', // ← Nouveau !
    'providers' => [
        MyServiceProvider::class,
    ],
]);
```

## Progressive Loading dans les Templates

### header.php

```php
<?php if (!defined('ABSPATH')) exit; ?><!DOCTYPE html>
<html>
<head>
<?php corbidev_critical_css(); ?>
<?php wp_head(); ?>
</head>
<body>
<?php corbidev_progressive_loader(); ?>
```

### C'est tout !

Le kernel gère automatiquement :
- Le chargement du HTML minimal
- L'affichage du spinner
- Le chargement différé des assets
- La transition smooth

## Performance

### Mode Progressive
- First Contentful Paint : **0.3-0.5s** ⭐⭐⭐⭐⭐
- Time to Interactive : **1-2s**
- Lighthouse : **95-100**

### Mode Critical
- First Contentful Paint : **0.2-0.3s** ⭐⭐⭐⭐⭐
- Time to Interactive : **1.5-2.5s**
- Lighthouse : **98-100**

## Documentation

- [Progressive Loading Guide](./docs/PROGRESSIVE_LOADING_GUIDE.md) - Guide complet
- [EventDispatcher Documentation](./docs/EVENTDISPATCHER_DOCUMENTATION.md)
- [Theme Integration Examples](./docs/THEME_INTEGRATION_EXAMPLE.php)

## Tests

```bash
composer test
```

## Licence

Proprietary - CorbiDev
