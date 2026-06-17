<?php

/**
 * Plugin Name:       RH SEO
 * Plugin URI:        https://github.com/herbeckrobin/rh-seo
 * Update URI:        https://github.com/herbeckrobin/rh-seo
 * Description:       SEO-Grundgerüst: Firmen-Stammdaten als Single Source, JSON-LD-Graph, Meta-/Open-Graph-Tags, Canonical, Sitemap-/Robots-/Lang-Cleanup. Teil der rh-blueprint Kollektion.
 * Version:           0.2.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Robin Herbeck
 * Author URI:        https://robinherbeck.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rh-seo
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('RHSEO_VERSION', '0.2.0');
define('RHSEO_PLUGIN_FILE', __FILE__);
define('RHSEO_PLUGIN_DIR', plugin_dir_path(__FILE__));

$rhseo_autoload = RHSEO_PLUGIN_DIR . 'vendor/autoload.php';

if (! is_readable($rhseo_autoload)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p><strong>RH SEO:</strong> Composer-Dependencies fehlen. Bitte <code>composer install</code> im Plugin-Verzeichnis ausführen.</p></div>';
    });
    return;
}

require_once $rhseo_autoload;

RhSeo\Plugin::boot();
