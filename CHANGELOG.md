## v1.2.0 - 2026-02-06

### Added - Progressive Loading System
- **ProgressiveLoadingService** : Chargement progressif avec 3 stratégies (blocking, progressive, critical)
- **CriticalCssService** : Gestion du CSS critique inline
- **LoadingServiceProvider** : Auto-enregistrement des services de chargement
- **LoadingHelpers** : Fonctions helper globales (corbidev_progressive_loader, corbidev_critical_css, etc.)
- Support du paramètre `loading_strategy` dans Kernel::boot()
- Documentation complète du système de chargement progressif
- Templates d'exemple (header.php, functions.php)

### Changed
- Kernel.php : Support de la configuration `loading_strategy`
- Auto-enregistrement du LoadingServiceProvider

### Performance
- Mode progressive : First Contentful Paint < 0.5s
- Mode critical : First Contentful Paint < 0.3s
- Lighthouse Performance : 95-100

## v1.1.0 - 2026-02-05

### Added
- EventDispatcher complet avec gestion des priorités
- Classe Event pour encapsulation des données
- Support du stop de propagation
- Listeners `once()` (one-time execution)
- Méthodes `on()`, `off()`, `dispatch()`, `hasListeners()`, `getListeners()`
- Événements automatiques du cycle de vie kernel
- Tests unitaires complets (EventDispatcherTest, EventTest)
- Documentation complète en français

### Changed
- Application.php dispatch maintenant des événements durant le boot
- Ajout du flag `$booted` pour empêcher l'enregistrement post-boot

## v1.0.0 - 2026-02-03
Initial production release.
