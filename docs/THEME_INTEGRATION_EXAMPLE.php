<?php

declare(strict_types=1);

/**
 * Exemple d'intégration de EventDispatcher dans un thème WordPress CorbiDev
 *
 * Ce fichier démontre comment utiliser le système d'événements
 * dans le contexte d'un thème WordPress.
 */

namespace CorbiDev\Theme\Example;

use CorbiDev\Kernel\Container\Container;
use CorbiDev\Kernel\Contracts\ServiceProviderInterface;
use CorbiDev\Kernel\Events\EventDispatcher;
use CorbiDev\Kernel\Events\Event;

/**
 * Service Provider exemple qui utilise les événements
 */
class ThemeEventsProvider implements ServiceProviderInterface
{
    /**
     * Enregistrement des listeners durant la phase register
     */
    public function register(Container $container): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $container->get(EventDispatcher::class);

        // Écouter le boot complet du kernel
        $dispatcher->on('kernel.booted', function (Event $event) {
            // Logger ou initialiser des services après boot complet
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf(
                    '[CorbiDev] Kernel booted with %d providers',
                    $event->get('providers_count', 0)
                ));
            }
        });

        // Événement personnalisé pour validation de données
        $dispatcher->on('theme.validate_contact_form', [$this, 'validateContactForm'], 100);

        // Événement pour modification du menu WordPress
        $dispatcher->on('theme.menu_items', [$this, 'addCustomMenuItems'], 10);
    }

    /**
     * Boot du provider
     */
    public function boot(Container $container): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $container->get(EventDispatcher::class);

        // Intégration avec les hooks WordPress
        $this->registerWordPressHooks($dispatcher);
    }

    /**
     * Validation du formulaire de contact
     */
    public function validateContactForm(Event $event): void
    {
        $data = $event->get('data', []);
        $errors = [];

        // Validation email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = __('Valid email is required', 'corbidevtheme');
        }

        // Validation message
        if (empty($data['message']) || strlen($data['message']) < 10) {
            $errors[] = __('Message must be at least 10 characters', 'corbidevtheme');
        }

        // Si erreurs, on arrête la propagation
        if (!empty($errors)) {
            $event->set('errors', $errors);
            $event->set('valid', false);
            $event->stopPropagation();
        }
    }

    /**
     * Ajout d'items custom au menu
     */
    public function addCustomMenuItems(Event $event): void
    {
        $items = $event->get('items', []);

        // Ajouter un item uniquement pour les admins
        if (current_user_can('manage_options')) {
            $items[] = [
                'title' => __('Admin Panel', 'corbidevtheme'),
                'url' => admin_url(),
                'class' => 'admin-menu-item',
            ];
        }

        $event->set('items', $items);
    }

    /**
     * Enregistre les hooks WordPress qui déclenchent des événements kernel
     */
    private function registerWordPressHooks(EventDispatcher $dispatcher): void
    {
        // Hook WordPress -> Événement Kernel
        add_action('wp_enqueue_scripts', function () use ($dispatcher) {
            $dispatcher->dispatch('theme.enqueue_assets', [
                'is_admin' => is_admin(),
                'is_front' => is_front_page(),
            ]);
        });

        add_action('init', function () use ($dispatcher) {
            $dispatcher->dispatch('theme.init', [
                'locale' => get_locale(),
                'user_logged_in' => is_user_logged_in(),
            ]);
        });

        add_filter('the_content', function (string $content) use ($dispatcher): string {
            $event = $dispatcher->dispatch('theme.content_filter', [
                'content' => $content,
                'post_id' => get_the_ID(),
            ]);

            return $event->get('content', $content);
        });
    }
}

/**
 * Service qui écoute les événements thème
 */
class AssetManager
{
    private EventDispatcher $dispatcher;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->listen();
    }

    private function listen(): void
    {
        // Charger les assets quand l'événement est déclenché
        $this->dispatcher->on('theme.enqueue_assets', function (Event $event) {
            $this->enqueueStyles($event);
            $this->enqueueScripts($event);
        });
    }

    private function enqueueStyles(Event $event): void
    {
        // Enqueue CSS seulement si pas admin
        if (!$event->get('is_admin', false)) {
            wp_enqueue_style(
                'corbidev-theme-main',
                get_template_directory_uri() . '/assets/css/main.css',
                [],
                '1.0.0'
            );
        }
    }

    private function enqueueScripts(Event $event): void
    {
        // Enqueue JS avec module si page d'accueil
        if ($event->get('is_front', false)) {
            wp_enqueue_script(
                'corbidev-theme-app',
                get_template_directory_uri() . '/assets/js/app.js',
                [],
                '1.0.0',
                ['strategy' => 'defer', 'in_footer' => true]
            );
        }
    }
}

/**
 * Exemple d'utilisation dans functions.php
 */
function example_theme_setup(): void
{
    // Boot du kernel avec le provider d'événements
    \CorbiDev\Kernel\Theme\Kernel::boot([
        'theme' => 'example',
        'providers' => [
            ThemeEventsProvider::class,
        ],
    ]);

    // Récupération du container (à adapter selon votre architecture)
    // $container = Application::getInstance()->getContainer();
    // $dispatcher = $container->get(EventDispatcher::class);

    // Instanciation de services qui écoutent les événements
    // new AssetManager($dispatcher);
}

/**
 * Exemple d'utilisation du formulaire de contact avec validation
 */
function example_contact_form_handler(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    // Récupération du dispatcher
    // $dispatcher = /* ... */;

    // Dispatch de la validation
    // $result = $dispatcher->dispatch('theme.validate_contact_form', [
    //     'data' => $_POST,
    //     'valid' => true,
    // ]);

    // if (!$result->get('valid', true)) {
    //     $errors = $result->get('errors', []);
    //     // Afficher les erreurs
    // } else {
    //     // Traiter le formulaire
    // }
}

/**
 * Exemple de filtre de contenu utilisant les événements
 */
function example_content_modifier(): void
{
    // Le hook est déjà enregistré dans ThemeEventsProvider
    // D'autres services peuvent écouter 'theme.content_filter'

    // Exemple de listener qui ajoute une notice en haut du contenu
    // $dispatcher->on('theme.content_filter', function (Event $event) {
    //     if ($event->get('post_id') === 42) {
    //         $content = $event->get('content', '');
    //         $notice = '<div class="special-notice">Article important !</div>';
    //         $event->set('content', $notice . $content);
    //     }
    // }, priority: 50);
}
