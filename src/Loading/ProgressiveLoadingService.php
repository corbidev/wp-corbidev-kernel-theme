<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Loading;

/**
 * Service de chargement progressif des assets
 *
 * Gère le chargement en 2 phases :
 * 1. HTML minimal (header + body) → Affichage immédiat
 * 2. Assets Vite/Vue en différé → Avec spinner
 *
 * Cette approche permet un First Contentful Paint ultra-rapide.
 */
final class ProgressiveLoadingService
{
    /**
     * Stratégie de chargement
     *
     * - blocking: Chargement classique (tous assets dans head)
     * - progressive: HTML minimal + spinner + chargement différé
     * - critical: Critical CSS inline + reste différé
     */
    private string $strategy;

    /**
     * Chemin vers le manifest Vite
     */
    private string $manifestPath;

    /**
     * URI de base des assets
     */
    private string $baseUri;

    /**
     * Cache du manifest
     */
    private ?array $manifest = null;

    /**
     * Constructeur
     *
     * @param string $strategy Stratégie de chargement (blocking|progressive|critical)
     * @param string $themePath Chemin absolu du thème
     */
    public function __construct(string $strategy = 'progressive', string $themePath = '')
    {
        $this->strategy = $strategy;
        
        if (empty($themePath)) {
            $themePath = get_template_directory();
        }
        
        $this->manifestPath = $themePath . '/assets/dist/.vite/manifest.json';
        $this->baseUri = get_template_directory_uri() . '/assets/dist/';
    }

    /**
     * Récupère le manifest Vite
     *
     * @return array<string, mixed>
     */
    private function getManifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        if (!file_exists($this->manifestPath)) {
            return [];
        }

        $content = file_get_contents($this->manifestPath);
        $this->manifest = json_decode($content, true) ?: [];

        return $this->manifest;
    }

    /**
     * Génère le HTML minimal pour le chargement progressif
     *
     * À placer dans header.php juste après <body>
     *
     * @return string
     */
    public function renderProgressiveLoader(): string
    {
        if ($this->strategy === 'blocking') {
            return ''; // Mode classique, pas de loader
        }

        $spinner = $this->getSpinnerHtml();
        $script = $this->getProgressiveScript();

        return <<<HTML
<!-- CorbiDev Progressive Loading -->
<div id="corbidev-app-loader" class="corbidev-loader">
    {$spinner}
</div>
<div id="app" class="corbidev-app-hidden"></div>

<style>
.corbidev-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
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
</style>

{$script}
HTML;
    }

    /**
     * Génère le HTML du spinner
     *
     * @return string
     */
    private function getSpinnerHtml(): string
    {
        return <<<HTML
<div class="corbidev-spinner">
    <svg width="50" height="50" viewBox="0 0 50 50">
        <circle cx="25" cy="25" r="20" fill="none" stroke="#3b82f6" stroke-width="4" 
                stroke-dasharray="31.415, 31.415" stroke-linecap="round">
            <animateTransform attributeName="transform" type="rotate" 
                              from="0 25 25" to="360 25 25" dur="1s" repeatCount="indefinite"/>
        </circle>
    </svg>
    <p style="margin-top: 1rem; color: #6b7280; font-family: system-ui, -apple-system, sans-serif;">
        Loading...
    </p>
</div>
HTML;
    }

    /**
     * Génère le script de chargement progressif
     *
     * @return string
     */
    private function getProgressiveScript(): string
    {
        $manifest = $this->getManifest();
        
        if (empty($manifest)) {
            return '<!-- Manifest Vite not found -->';
        }

        // Récupérer les assets
        $assets = $this->getAssetsForLoading($manifest);
        
        if (empty($assets)) {
            return '<!-- No assets to load -->';
        }

        $assetsJson = json_encode($assets, JSON_UNESCAPED_SLASHES);

        return <<<HTML
<script>
(function() {
    'use strict';
    
    const assets = {$assetsJson};
    const baseUri = '{$this->baseUri}';
    
    // Fonction pour charger un asset
    function loadAsset(asset) {
        return new Promise((resolve, reject) => {
            if (asset.type === 'css') {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = baseUri + asset.file;
                link.onload = resolve;
                link.onerror = reject;
                document.head.appendChild(link);
            } else if (asset.type === 'js') {
                const script = document.createElement('script');
                script.type = 'module';
                script.src = baseUri + asset.file;
                script.onload = resolve;
                script.onerror = reject;
                document.body.appendChild(script);
            }
        });
    }
    
    // Charger tous les assets
    Promise.all(assets.map(loadAsset))
        .then(() => {
            // Masquer le loader
            const loader = document.getElementById('corbidev-app-loader');
            const app = document.getElementById('app');
            
            if (loader) {
                loader.classList.add('corbidev-loader-hidden');
                setTimeout(() => loader.remove(), 300);
            }
            
            if (app) {
                app.classList.add('corbidev-app-loaded');
                app.classList.remove('corbidev-app-hidden');
            }
            
            // Event personnalisé
            window.dispatchEvent(new CustomEvent('corbidev:loaded'));
        })
        .catch(err => {
            console.error('Failed to load assets:', err);
            
            // En cas d'erreur, afficher quand même l'app
            const loader = document.getElementById('corbidev-app-loader');
            const app = document.getElementById('app');
            
            if (loader) loader.remove();
            if (app) app.classList.remove('corbidev-app-hidden');
        });
})();
</script>
HTML;
    }

    /**
     * Récupère la liste des assets à charger
     *
     * @param array<string, mixed> $manifest
     * @return array<int, array{type: string, file: string}>
     */
    private function getAssetsForLoading(array $manifest): array
    {
        $assets = [];
        $entryKey = 'assets/vite/front.js';

        // CSS
        if (isset($manifest[$entryKey]['css'])) {
            foreach ($manifest[$entryKey]['css'] as $cssFile) {
                $assets[] = [
                    'type' => 'css',
                    'file' => $cssFile,
                ];
            }
        }

        // JS Principal
        if (isset($manifest[$entryKey]['file'])) {
            $assets[] = [
                'type' => 'js',
                'file' => $manifest[$entryKey]['file'],
            ];
        }

        return $assets;
    }

    /**
     * Enqueue les assets en mode blocking (classique)
     *
     * @return void
     */
    public function enqueueBlockingAssets(): void
    {
        if ($this->strategy !== 'blocking') {
            return;
        }

        $manifest = $this->getManifest();

        if (empty($manifest)) {
            return;
        }

        $entryKey = 'assets/vite/front.js';

        // CSS
        if (isset($manifest[$entryKey]['css'])) {
            foreach ($manifest[$entryKey]['css'] as $index => $cssFile) {
                wp_enqueue_style(
                    'corbidev-front-' . $index,
                    $this->baseUri . $cssFile,
                    [],
                    null
                );
            }
        }

        // JS
        if (isset($manifest[$entryKey]['file'])) {
            wp_enqueue_script(
                'corbidev-front',
                $this->baseUri . $manifest[$entryKey]['file'],
                [],
                null,
                [
                    'strategy' => 'defer',
                    'in_footer' => true,
                ]
            );

            add_filter('script_loader_tag', function ($tag, $handle) {
                if ($handle === 'corbidev-front') {
                    return str_replace(' src', ' type="module" src', $tag);
                }
                return $tag;
            }, 10, 2);
        }
    }

    /**
     * Hook WordPress pour gérer le chargement
     *
     * @return void
     */
    public function registerHooks(): void
    {
        if ($this->strategy === 'blocking') {
            add_action('wp_enqueue_scripts', [$this, 'enqueueBlockingAssets']);
        }
        
        // En mode progressive ou critical, on ne charge rien via wp_enqueue_scripts
        // Tout est géré par le script inline
    }

    /**
     * Récupère la stratégie actuelle
     *
     * @return string
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }
}
