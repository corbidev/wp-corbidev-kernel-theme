<?php
/**
 * Header Template avec support du chargement progressif
 *
 * Ce template utilise le système de chargement progressif du kernel.
 * Le kernel gère automatiquement :
 * - Le chargement du HTML minimal en premier
 * - L'affichage du spinner pendant le chargement
 * - Le chargement différé des assets Vite/Vue
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
/**
 * Critical CSS inline (mode progressive ou critical)
 * 
 * Le kernel inline automatiquement le CSS critique si :
 * - loading_strategy = 'progressive'
 * - loading_strategy = 'critical'
 * 
 * Cela permet un First Contentful Paint ultra-rapide.
 */
corbidev_critical_css();
?>

<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
/**
 * Progressive Loader
 * 
 * Affiche un spinner pendant que les assets sont chargés.
 * Le kernel gère automatiquement :
 * - L'affichage du loader
 * - Le chargement différé des assets
 * - Le masquage du loader une fois chargé
 * 
 * En mode 'blocking', cette fonction ne fait rien (chargement classique).
 */
corbidev_progressive_loader();
?>

<!-- Le contenu du site sera affiché ici -->
<?php
/**
 * En mode progressive, le contenu est dans <div id="app">
 * qui est caché pendant le chargement puis affiché avec une transition.
 */
?>
