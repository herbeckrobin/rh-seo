<?php

declare(strict_types=1);

namespace RhSeo;

use RhBlueprint\Core\Core;
use RhBlueprint\Core\Settings\SettingsPage;
use RhSeo\Admin\BusinessDataGroup;
use RhSeo\Admin\SeoMetaBox;
use RhSeo\Admin\TechGroup;

/**
 * Bootstrap von rh-seo.
 *
 * Hängt am Core-Hook `rh-blueprint/core/booted` (feuert auf `init`). Baut die
 * Domain-Klassen (BusinessData als Single Source, Graph, Meta-Tags, Cleanup) per
 * DI zusammen und registriert die Settings im Tab "SEO". Braucht nur den Core.
 */
final class Plugin
{
    public static function boot(): void
    {
        if (class_exists(UpdateChecker::class)) {
            (new UpdateChecker())->boot();
        }

        add_action('rh-blueprint/core/booted', [self::class, 'onCoreBooted']);
    }

    public static function onCoreBooted(Core $core): void
    {
        $core->settings()->registerTab('seo', __('SEO', 'rh-seo'), 20);
        $core->settings()->registerGroup(new BusinessDataGroup());
        $core->settings()->registerGroup(new TechGroup());

        $business = new BusinessData();

        (new JsonLdGraph($business))->boot();
        (new MetaTags($business))->boot();
        (new Title($business))->boot();
        (new WpCleanup())->boot();
        (new SeoMetaBox())->boot();

        add_filter('rh-blueprint/dashboard/quick_links', static function (array $links): array {
            $links[] = [
                'label' => __('SEO', 'rh-seo'),
                'url' => admin_url('admin.php?page=' . SettingsPage::MENU_SLUG . '&tab=seo'),
                'icon' => 'search',
            ];
            return $links;
        });
    }
}
