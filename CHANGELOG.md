## v1.1.0 - 2026-02-05

### Added
- EventDispatcher complet avec gestion des priorités
- Classe Event pour encapsulation des données
- Support du stop de propagation
- Listeners `once()` (one-time execution)
- Méthodes `on()`, `off()`, `dispatch()`, `hasListeners()`, `getListeners()`
- Événements automatiques du cycle de vie kernel :
  - `kernel.created`
  - `kernel.provider.registering` / `kernel.provider.registered`
  - `kernel.booting` / `kernel.booted`
  - `kernel.provider.booting` / `kernel.provider.booted`
- Tests unitaires complets (EventDispatcherTest, EventTest)
- Documentation complète en français

### Changed
- Application.php dispatch maintenant des événements durant le boot
- Ajout du flag `$booted` pour empêcher l enregistrement post-boot

## v1.0.0 - 2026-02-03
Initial production release.
