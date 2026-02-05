# EventDispatcher - Documentation

## Vue d'ensemble

L'`EventDispatcher` est le système d'événements du kernel CorbiDev. Il permet une communication découplée entre les différentes parties de l'application via un pattern observer/publish-subscribe.

## Architecture

```
EventDispatcher
├── Event (objet transportant les données)
└── Listeners (callables enregistrés)
```

## Utilisation de base

### Enregistrer un listener

```php
use CorbiDev\Kernel\Events\EventDispatcher;
use CorbiDev\Kernel\Events\Event;

$dispatcher = new EventDispatcher();

$dispatcher->on('user.created', function (Event $event) {
    $userData = $event->get('user');
    // Traitement...
});
```

### Déclencher un événement

```php
$dispatcher->dispatch('user.created', [
    'user' => ['id' => 1, 'email' => 'user@example.com']
]);
```

## Fonctionnalités avancées

### Priorités

Les listeners peuvent avoir des priorités (plus élevé = exécuté en premier) :

```php
// Exécuté en dernier
$dispatcher->on('app.boot', function () {
    // Initialisation de base
}, priority: 1);

// Exécuté en premier
$dispatcher->on('app.boot', function () {
    // Configuration critique
}, priority: 100);

// Exécuté entre les deux (priorité par défaut = 10)
$dispatcher->on('app.boot', function () {
    // Chargement normal
});
```

### Listener unique (once)

Exécuter un listener une seule fois puis le retirer automatiquement :

```php
$dispatcher->once('app.first_boot', function (Event $event) {
    // Ne s'exécute qu'au premier boot
});
```

### Arrêt de propagation

Un listener peut empêcher l'exécution des listeners suivants :

```php
$dispatcher->on('request.validate', function (Event $event) {
    if ($event->get('invalid')) {
        $event->stopPropagation(); // Stop ici
    }
}, priority: 50);

// Ne sera jamais exécuté si invalid = true
$dispatcher->on('request.validate', function (Event $event) {
    // Traitement supplémentaire
}, priority: 10);
```

### Manipulation des données

L'objet `Event` permet de modifier les données durant la propagation :

```php
$dispatcher->on('data.process', function (Event $event) {
    $value = $event->get('value', 0);
    $event->set('value', $value * 2);
});

$dispatcher->on('data.process', function (Event $event) {
    $value = $event->get('value');
    $event->set('value', $value + 10);
});

$result = $dispatcher->dispatch('data.process', ['value' => 5]);
echo $result->get('value'); // 20 (5 * 2 + 10)
```

## Événements du kernel

Le kernel dispatche automatiquement ces événements :

| Événement | Moment | Données disponibles |
|-----------|--------|---------------------|
| `kernel.created` | Après création Application | `config`, `context` |
| `kernel.provider.registering` | Avant register() d'un provider | `provider` (class name) |
| `kernel.provider.registered` | Après register() | `provider` |
| `kernel.booting` | Avant boot global | `providers_count` |
| `kernel.provider.booting` | Avant boot() d'un provider | `provider` |
| `kernel.provider.booted` | Après boot() | `provider` |
| `kernel.booted` | Après boot global | `providers_count` |

### Exemple d'écoute des événements kernel

```php
use CorbiDev\Kernel\Container\Container;
use CorbiDev\Kernel\Events\EventDispatcher;
use CorbiDev\Kernel\Events\Event;

class MonServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $container->get(EventDispatcher::class);
        
        // Logger tous les providers qui boot
        $dispatcher->on('kernel.provider.booted', function (Event $event) {
            error_log('Provider booted: ' . $event->get('provider'));
        });
    }
    
    public function boot(Container $container): void
    {
        // ...
    }
}
```

## API complète

### EventDispatcher

#### on(string $event, callable $callback, int $priority = 10): void
Enregistre un listener permanent.

#### once(string $event, callable $callback, int $priority = 10): void
Enregistre un listener qui s'exécute une seule fois.

#### off(string $event, callable $callback): bool
Retire un listener spécifique. Retourne `true` si trouvé.

#### dispatch(string $event, array $data = []): Event
Déclenche un événement et retourne l'objet Event final.

#### hasListeners(string $event): bool
Vérifie si un événement a des listeners.

#### getListeners(string $event): array
Récupère tous les listeners d'un événement (triés par priorité).

#### countListeners(?string $event = null): int
Compte les listeners d'un événement ou de tous les événements.

#### removeAllListeners(?string $event = null): void
Retire tous les listeners d'un événement ou globalement.

### Event

#### getName(): string
Récupère le nom de l'événement.

#### getData(): array
Récupère toutes les données.

#### get(string $key, mixed $default = null): mixed
Récupère une donnée par clé.

#### set(string $key, mixed $value): self
Définit une donnée (retour fluent).

#### has(string $key): bool
Vérifie l'existence d'une clé.

#### merge(array $data): self
Fusionne des données (retour fluent).

#### remove(string $key): self
Retire une clé (retour fluent).

#### stopPropagation(): self
Arrête la propagation (retour fluent).

#### isPropagationStopped(): bool
Vérifie si la propagation est arrêtée.

## Cas d'usage thème WordPress

### Hook WordPress avec EventDispatcher

```php
// Dans functions.php
use CorbiDev\Kernel\Theme\Kernel;
use CorbiDev\Kernel\Events\EventDispatcher;
use CorbiDev\Kernel\Events\Event;

// Après boot du kernel
$container = /* récupération du container */;
$dispatcher = $container->get(EventDispatcher::class);

// Transformer un hook WP en événement kernel
add_action('init', function () use ($dispatcher) {
    $dispatcher->dispatch('theme.wordpress.init');
});

// Les services peuvent écouter
$dispatcher->on('theme.wordpress.init', function (Event $event) {
    // Initialisation custom
});
```

### Validation de formulaire

```php
$dispatcher->on('form.validate', function (Event $event) {
    $data = $event->get('data');
    $errors = [];
    
    if (empty($data['email'])) {
        $errors[] = 'Email required';
    }
    
    if (!empty($errors)) {
        $event->set('errors', $errors);
        $event->set('valid', false);
        $event->stopPropagation();
    }
}, priority: 100);

$result = $dispatcher->dispatch('form.validate', [
    'data' => $_POST,
    'valid' => true
]);

if (!$result->get('valid', true)) {
    $errors = $result->get('errors', []);
    // Afficher les erreurs
}
```

## Bonnes pratiques

### Convention de nommage

- Utiliser des points pour la hiérarchie : `domain.action.context`
- Exemples : `user.created`, `post.before_save`, `theme.assets.enqueue`

### Éviter les effets de bord

Les listeners ne doivent pas produire d'output directement (echo, print) :

```php
// ❌ Mauvais
$dispatcher->on('event', function () {
    echo "Something";
});

// ✅ Bon
$dispatcher->on('event', function (Event $event) {
    $event->set('output', 'Something');
});
```

### Documenter les événements custom

```php
/**
 * Événement déclenché après enregistrement d'un utilisateur
 *
 * Données disponibles :
 * - user: array (id, email, role)
 * - source: string (admin|frontend)
 */
$dispatcher->dispatch('user.registered', [
    'user' => $userData,
    'source' => 'frontend'
]);
```

## Performance

- Les listeners sont stockés en mémoire (pas de persistance)
- La recherche par priorité utilise un tri natif PHP
- Complexité : O(n) où n = nombre de listeners pour un événement
- Recommandation : < 50 listeners par événement

## Tests

Tous les tests sont dans `tests/` :
- `EventDispatcherTest.php` : Tests complets du dispatcher
- `EventTest.php` : Tests de l'objet Event

```bash
composer test
```

## Compatibilité

- PHP 8.1+
- Aucune dépendance externe
- Compatible WordPress, Bedrock, standalone
