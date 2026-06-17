<?php

declare(strict_types=1);

namespace RhSeo\Admin;

use RhBlueprint\Core\Settings\GroupInterface;
use RhBlueprint\Core\Settings\SettingField;

/**
 * Settings-Gruppe für die technischen SEO-Eingriffe (Sitemap, Robots, Sprache).
 *
 * Alle Default an, weil das die sinnvolle Baseline für eine fokussierte
 * Unternehmens-Site ist. Wer Blog/Taxonomien indexiert haben will, schaltet
 * die jeweilige Bereinigung ab.
 */
final class TechGroup implements GroupInterface
{
    public const GROUP_ID = 'seo_tech';

    public const FIELD_SITEMAP_DROP_USERS = 'sitemap_drop_users';
    public const FIELD_SITEMAP_DROP_POSTS = 'sitemap_drop_posts';
    public const FIELD_SITEMAP_DROP_TAXONOMIES = 'sitemap_drop_taxonomies';
    public const FIELD_ROBOTS_ENHANCE = 'robots_enhance';
    public const FIELD_LANG = 'lang_attribute';
    public const FIELD_LOCALE = 'og_locale';

    public function id(): string
    {
        return self::GROUP_ID;
    }

    public function tab(): string
    {
        return 'seo';
    }

    public function title(): string
    {
        return __('Technik', 'rh-seo');
    }

    public function description(): string
    {
        return __('Aufräumen, was WordPress an SEO-Ballast mitbringt, und Sprach-Signale setzen.', 'rh-seo');
    }

    public function fields(): array
    {
        return [
            new SettingField(
                id: self::FIELD_SITEMAP_DROP_USERS,
                type: SettingField::TYPE_BOOLEAN,
                label: __('Autoren aus der Sitemap nehmen', 'rh-seo'),
                description: __('Entfernt den Users-Provider aus der XML-Sitemap. Verhindert nebenbei einen Login-Namen-Leak.', 'rh-seo'),
                default: true,
                keywords: ['sitemap', 'autoren', 'users'],
            ),
            new SettingField(
                id: self::FIELD_SITEMAP_DROP_POSTS,
                type: SettingField::TYPE_BOOLEAN,
                label: __('Beiträge aus der Sitemap nehmen', 'rh-seo'),
                description: __('Für Sites ohne Blog. Lässt nur Seiten in der Sitemap.', 'rh-seo'),
                default: false,
                keywords: ['sitemap', 'beitraege', 'posts', 'blog'],
            ),
            new SettingField(
                id: self::FIELD_SITEMAP_DROP_TAXONOMIES,
                type: SettingField::TYPE_BOOLEAN,
                label: __('Kategorien/Tags aus der Sitemap nehmen', 'rh-seo'),
                description: __('Entfernt alle Taxonomie-Archive aus der Sitemap.', 'rh-seo'),
                default: false,
                keywords: ['sitemap', 'kategorien', 'tags', 'taxonomien'],
            ),
            new SettingField(
                id: self::FIELD_ROBOTS_ENHANCE,
                type: SettingField::TYPE_BOOLEAN,
                label: __('Robots-Direktiven erweitern', 'rh-seo'),
                description: __('Setzt max-image-preview:large, max-snippet:-1 und max-video-preview:-1, damit Google Rich Results voll ausspielen darf.', 'rh-seo'),
                default: true,
                keywords: ['robots', 'snippet', 'preview'],
            ),
            new SettingField(
                id: self::FIELD_LANG,
                type: SettingField::TYPE_TEXT,
                label: __('Sprach-Attribut (lang)', 'rh-seo'),
                description: __('Wert für das <html lang>-Attribut, z.B. de-DE, de-CH. Leer lassen für den WordPress-Standard.', 'rh-seo'),
                default: 'de-DE',
                keywords: ['lang', 'sprache', 'language'],
            ),
            new SettingField(
                id: self::FIELD_LOCALE,
                type: SettingField::TYPE_TEXT,
                label: __('Open-Graph-Locale', 'rh-seo'),
                description: __('Locale für og:locale, z.B. de_DE, de_CH (mit Unterstrich).', 'rh-seo'),
                default: 'de_DE',
                keywords: ['locale', 'og', 'sprache'],
            ),
        ];
    }
}
