# CorbiDev Kernel v1.1.0

## Production-ready WordPress Kernel

Kernel PHP 8.1+ pour thèmes WordPress CorbiDev avec système d événements intégré.

## Fonctionnalités

- ✅ Architecture Service Provider
- ✅ Container d injection de dépendances
- ✅ **EventDispatcher complet** (nouveau v1.1.0)
- ✅ Détection automatique d environnement
- ✅ Compatible WordPress classique & Bedrock
- ✅ Tests unitaires inclus

## Installation

```bash
composer require corbidev/wp-corbidev-kernel-theme
```

## Utilisation de base

```php
use CorbiDev\Kernel\Theme\Kernel;

Kernel::boot([
    'theme' => 'my-theme',
    'providers' => [
        MyServiceProvider::class,
    ],
]);
```

## EventDispatcher (v1.1.0)

```php
use CorbiDev\Kernel\Events\EventDispatcher;
use CorbiDev\Kernel\Events\Event;

$dispatcher = $container->get(EventDispatcher::class);

// Enregistrer un listener
$dispatcher->on('user.created', function (Event $event) {
    $user = $event->get('user');
    // Traitement...
});

// Déclencher un événement
$dispatcher->dispatch('user.created', ['user' => $userData]);
```

## Événements du kernel

Le kernel dispatche automatiquement :
- `kernel.created`
- `kernel.provider.registering` / `kernel.provider.registered`
- `kernel.booting` / `kernel.booted`
- `kernel.provider.booting` / `kernel.provider.booted`

## Documentation

Voir la documentation complète dans `/docs`

## Tests

```bash
composer test
```

## Licence

Proprietary - CorbiDev
