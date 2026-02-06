<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Loading;

/**
 * Service de gestion du CSS critique
 *
 * Permet d'inliner le CSS critique dans le <head> pour un
 * First Contentful Paint ultra-rapide, puis de charger
 * le reste du CSS en différé.
 */
final class CriticalCssService
{
    /**
     * Chemin vers le fichier de CSS critique
     */
    private string $criticalCssPath;

    /**
     * CSS critique en cache
     */
    private ?string $criticalCss = null;

    /**
     * Constructeur
     *
     * @param string $themePath Chemin absolu du thème
     */
    public function __construct(string $themePath = '')
    {
        if (empty($themePath)) {
            $themePath = get_template_directory();
        }

        $this->criticalCssPath = $themePath . '/assets/css/critical.css';
    }

    /**
     * Récupère le CSS critique
     *
     * @return string
     */
    public function getCriticalCss(): string
    {
        if ($this->criticalCss !== null) {
            return $this->criticalCss;
        }

        if (!file_exists($this->criticalCssPath)) {
            // Si pas de fichier critical.css, générer un CSS minimal de base
            $this->criticalCss = $this->getDefaultCriticalCss();
            return $this->criticalCss;
        }

        $this->criticalCss = file_get_contents($this->criticalCssPath);
        
        // Minifier le CSS critique
        $this->criticalCss = $this->minifyCss($this->criticalCss);

        return $this->criticalCss;
    }

    /**
     * Génère le CSS critique par défaut
     *
     * CSS minimal pour éviter le FOUC (Flash of Unstyled Content)
     *
     * @return string
     */
    private function getDefaultCriticalCss(): string
    {
        return <<<CSS
body{margin:0;font-family:system-ui,-apple-system,sans-serif;line-height:1.5}
*{box-sizing:border-box}
.corbidev-loader{position:fixed;top:0;left:0;width:100%;height:100vh;display:flex;align-items:center;justify-content:center;background:#fff;z-index:9999}
.corbidev-app-hidden{opacity:0;transition:opacity .3s ease}
.corbidev-app-loaded{opacity:1}
CSS;
    }

    /**
     * Minifie le CSS
     *
     * @param string $css
     * @return string
     */
    private function minifyCss(string $css): string
    {
        // Retirer les commentaires
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Retirer les espaces multiples
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Retirer les espaces autour des caractères spéciaux
        $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css);
        
        // Retirer les points-virgules avant les accolades fermantes
        $css = str_replace(';}', '}', $css);
        
        return trim($css);
    }

    /**
     * Génère le tag <style> avec le CSS critique
     *
     * À placer dans le <head>
     *
     * @return string
     */
    public function renderCriticalCssTag(): string
    {
        $css = $this->getCriticalCss();

        return sprintf(
            '<style id="corbidev-critical-css">%s</style>',
            $css
        );
    }

    /**
     * Génère le preload du CSS complet
     *
     * @param string $cssUrl URL du fichier CSS complet
     * @return string
     */
    public function renderPreloadTag(string $cssUrl): string
    {
        return sprintf(
            '<link rel="preload" href="%s" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">',
            esc_url($cssUrl)
        );
    }

    /**
     * Crée un fichier critical.css avec du contenu par défaut
     *
     * Utile pour initialiser le projet
     *
     * @return bool
     */
    public function createDefaultCriticalCssFile(): bool
    {
        $cssDir = dirname($this->criticalCssPath);

        if (!is_dir($cssDir)) {
            mkdir($cssDir, 0755, true);
        }

        $defaultCss = <<<CSS
/**
 * Critical CSS - Above the Fold
 * 
 * Ce fichier contient le CSS critique qui sera inline dans le <head>
 * pour un First Contentful Paint ultra-rapide.
 * 
 * Règles :
 * - Uniquement le CSS visible "above the fold" (avant scroll)
 * - Taille cible : < 14kb (idéalement < 10kb)
 * - Styles de base : reset, typographie, layout header
 * - PAS de styles pour footer, sidebar, contenu bas de page
 */

/* Reset minimal */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Typographie de base */
body {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    line-height: 1.6;
    color: #1f2937;
    background: #ffffff;
}

/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Header visible immédiatement */
header {
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem 0;
}

/* Navigation principale */
nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Logo */
.logo {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

/* Hero section (si visible sans scroll) */
.hero {
    padding: 3rem 0;
    text-align: center;
}

.hero h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
}

/* Loader (Progressive Loading) */
.corbidev-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    z-index: 9999;
}

.corbidev-app-hidden {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.corbidev-app-loaded {
    opacity: 1;
}

.corbidev-loader-hidden {
    opacity: 0;
    pointer-events: none;
}
CSS;

        return (bool) file_put_contents($this->criticalCssPath, $defaultCss);
    }

    /**
     * Vérifie si le fichier critical.css existe
     *
     * @return bool
     */
    public function hasCriticalCssFile(): bool
    {
        return file_exists($this->criticalCssPath);
    }
}
