<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Events;

/**
 * Gestionnaire d'événements pour le kernel CorbiDev
 *
 * Permet l'enregistrement de listeners sur des événements nommés
 * et leur déclenchement avec propagation des données.
 */
class EventDispatcher
{
    /**
     * Liste des listeners enregistrés par événement
     *
     * @var array<string, array<int, array<callable>>>
     */
    private array $listeners = [];

    /**
     * Enregistre un listener pour un événement spécifique
     *
     * @param string $event Nom de l'événement
     * @param callable $callback Fonction à exécuter lors du déclenchement
     * @param int $priority Priorité d'exécution (plus élevé = exécuté en premier)
     * @return void
     */
    public function on(string $event, callable $callback, int $priority = 10): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        if (!isset($this->listeners[$event][$priority])) {
            $this->listeners[$event][$priority] = [];
        }

        $this->listeners[$event][$priority][] = $callback;
    }

    /**
     * Enregistre un listener qui ne s'exécutera qu'une seule fois
     *
     * @param string $event Nom de l'événement
     * @param callable $callback Fonction à exécuter
     * @param int $priority Priorité d'exécution
     * @return void
     */
    public function once(string $event, callable $callback, int $priority = 10): void
    {
        $wrapper = function (Event $eventObject) use (&$wrapper, $event, $callback): void {
            $this->off($event, $wrapper);
            $callback($eventObject);
        };

        $this->on($event, $wrapper, $priority);
    }

    /**
     * Retire un listener spécifique d'un événement
     *
     * @param string $event Nom de l'événement
     * @param callable $callback Callback à retirer
     * @return bool True si le listener a été trouvé et retiré
     */
    public function off(string $event, callable $callback): bool
    {
        if (!isset($this->listeners[$event])) {
            return false;
        }

        $found = false;

        foreach ($this->listeners[$event] as $priority => $callbacks) {
            foreach ($callbacks as $index => $registeredCallback) {
                if ($registeredCallback === $callback) {
                    unset($this->listeners[$event][$priority][$index]);
                    $found = true;

                    // Nettoyage si le niveau de priorité est vide
                    if (empty($this->listeners[$event][$priority])) {
                        unset($this->listeners[$event][$priority]);
                    }
                }
            }
        }

        // Nettoyage si l'événement n'a plus de listeners
        if (empty($this->listeners[$event])) {
            unset($this->listeners[$event]);
        }

        return $found;
    }

    /**
     * Déclenche un événement et exécute tous les listeners enregistrés
     *
     * @param string $event Nom de l'événement
     * @param array<string, mixed> $data Données à passer aux listeners
     * @return Event L'objet Event après propagation
     */
    public function dispatch(string $event, array $data = []): Event
    {
        $eventObject = new Event($event, $data);

        if (!isset($this->listeners[$event])) {
            return $eventObject;
        }

        // Tri des priorités (du plus élevé au plus bas)
        $priorities = array_keys($this->listeners[$event]);
        rsort($priorities, SORT_NUMERIC);

        foreach ($priorities as $priority) {
            if ($eventObject->isPropagationStopped()) {
                break;
            }

            foreach ($this->listeners[$event][$priority] as $callback) {
                if ($eventObject->isPropagationStopped()) {
                    break;
                }

                $callback($eventObject);
            }
        }

        return $eventObject;
    }

    /**
     * Vérifie si un événement a des listeners enregistrés
     *
     * @param string $event Nom de l'événement
     * @return bool
     */
    public function hasListeners(string $event): bool
    {
        return isset($this->listeners[$event]) && !empty($this->listeners[$event]);
    }

    /**
     * Récupère tous les listeners d'un événement
     *
     * @param string $event Nom de l'événement
     * @return array<callable>
     */
    public function getListeners(string $event): array
    {
        if (!isset($this->listeners[$event])) {
            return [];
        }

        $allListeners = [];
        $priorities = array_keys($this->listeners[$event]);
        rsort($priorities, SORT_NUMERIC);

        foreach ($priorities as $priority) {
            foreach ($this->listeners[$event][$priority] as $callback) {
                $allListeners[] = $callback;
            }
        }

        return $allListeners;
    }

    /**
     * Retire tous les listeners d'un événement ou de tous les événements
     *
     * @param string|null $event Nom de l'événement (null = tous les événements)
     * @return void
     */
    public function removeAllListeners(?string $event = null): void
    {
        if ($event === null) {
            $this->listeners = [];
            return;
        }

        if (isset($this->listeners[$event])) {
            unset($this->listeners[$event]);
        }
    }

    /**
     * Récupère le nombre total de listeners enregistrés
     *
     * @param string|null $event Nom de l'événement (null = tous)
     * @return int
     */
    public function countListeners(?string $event = null): int
    {
        if ($event === null) {
            $count = 0;
            foreach ($this->listeners as $eventListeners) {
                foreach ($eventListeners as $callbacks) {
                    $count += count($callbacks);
                }
            }
            return $count;
        }

        if (!isset($this->listeners[$event])) {
            return 0;
        }

        $count = 0;
        foreach ($this->listeners[$event] as $callbacks) {
            $count += count($callbacks);
        }

        return $count;
    }
}
