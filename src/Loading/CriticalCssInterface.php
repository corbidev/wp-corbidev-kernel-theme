<?php
/**
 * Interface du service Critical CSS
 *
 * Définit le contrat public du service.
 *
 * @package CorbiDevKernel
 */

namespace CorbiDev\Kernel\Loading;

interface CriticalCssInterface
{
    /**
     * Retourne le CSS critique.
     *
     * @return string
     */
    public function render(): string;
}