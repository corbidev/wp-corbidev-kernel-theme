<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Loading;

/**
 * Service de gestion du CSS critique
 *
 * PHP pur – aucun WordPress – aucun HTML
 */
final class CriticalCssService
{
    private string $criticalCssPath;
    private ?string $criticalCss = null;

    public function __construct(string $criticalCssPath)
    {
        $this->criticalCssPath = $criticalCssPath;
    }

    /**
     * Retourne le CSS critique minifié
     */
    public function render(): string
    {
        if ($this->criticalCss !== null) {
            return $this->criticalCss;
        }

        if (!file_exists($this->criticalCssPath)) {
            $this->criticalCss = $this->getDefaultCriticalCss();
            return $this->criticalCss;
        }

        $css = file_get_contents($this->criticalCssPath);
        $this->criticalCss = $this->minifyCss($css ?: '');

        return $this->criticalCss;
    }

    private function getDefaultCriticalCss(): string
    {
        return 'body{margin:0}';
    }

    private function minifyCss(string $css): string
    {
        $css = preg_replace('!/\*.*?\*/!s', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        return trim($css);
    }
}
