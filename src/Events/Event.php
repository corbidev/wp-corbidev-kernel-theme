<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Events;

/**
 * Objet événement transportant les données et l'état de propagation
 *
 * Encapsule les données d'un événement et permet de contrôler
 * sa propagation à travers les listeners.
 */
class Event
{
    /**
     * Nom de l'événement
     */
    private string $name;

    /**
     * Données associées à l'événement
     *
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * Indique si la propagation a été arrêtée
     */
    private bool $propagationStopped = false;

    /**
     * Constructeur
     *
     * @param string $name Nom de l'événement
     * @param array<string, mixed> $data Données à transporter
     */
    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * Récupère le nom de l'événement
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Récupère toutes les données de l'événement
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Récupère une donnée spécifique par clé
     *
     * @param string $key Clé de la donnée
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Définit une donnée dans l'événement
     *
     * @param string $key Clé de la donnée
     * @param mixed $value Valeur à stocker
     * @return self Pour chaînage
     */
    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Vérifie si une clé existe dans les données
     *
     * @param string $key Clé à vérifier
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Arrête la propagation de l'événement aux listeners suivants
     *
     * @return self Pour chaînage
     */
    public function stopPropagation(): self
    {
        $this->propagationStopped = true;
        return $this;
    }

    /**
     * Vérifie si la propagation a été arrêtée
     *
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Fusionne des données additionnelles dans l'événement
     *
     * @param array<string, mixed> $data Données à fusionner
     * @return self Pour chaînage
     */
    public function merge(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Retire une donnée de l'événement
     *
     * @param string $key Clé à retirer
     * @return self Pour chaînage
     */
    public function remove(string $key): self
    {
        unset($this->data[$key]);
        return $this;
    }
}
