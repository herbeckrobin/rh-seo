<?php

declare(strict_types=1);

namespace RhSeo;

use RhSeo\Admin\TechGroup;

/**
 * Technische SEO-Eingriffe: Sitemap entschlacken, Robots-Direktiven erweitern,
 * Sprach-Attribut setzen. Jeder Eingriff an seinem Toggle gated.
 */
final class WpCleanup
{
    public function boot(): void
    {
        if ($this->enabled(TechGroup::FIELD_SITEMAP_DROP_USERS, true)) {
            add_filter('wp_sitemaps_add_provider', [$this, 'dropUsersProvider'], 10, 2);
        }

        if ($this->enabled(TechGroup::FIELD_SITEMAP_DROP_POSTS, false)) {
            add_filter('wp_sitemaps_post_types', [$this, 'dropPosts']);
        }

        if ($this->enabled(TechGroup::FIELD_SITEMAP_DROP_TAXONOMIES, false)) {
            add_filter('wp_sitemaps_taxonomies', '__return_empty_array');
        }

        if ($this->enabled(TechGroup::FIELD_ROBOTS_ENHANCE, true)) {
            add_filter('wp_robots', [$this, 'enhanceRobots']);
        }

        // Default an den Feld-Default angleichen: rhbp_setting() kennt die
        // SettingField-Defaults nicht, vor dem ersten Speichern käme sonst ''.
        $lang = (string) rhbp_setting(TechGroup::GROUP_ID, TechGroup::FIELD_LANG, 'de-DE');
        if (trim($lang) !== '') {
            add_filter('language_attributes', [$this, 'forceLang']);
        }
    }

    /**
     * @param mixed  $provider
     * @return mixed
     */
    public function dropUsersProvider($provider, string $name)
    {
        return $name === 'users' ? false : $provider;
    }

    /**
     * @param array<string, \WP_Post_Type> $postTypes
     * @return array<string, \WP_Post_Type>
     */
    public function dropPosts(array $postTypes): array
    {
        unset($postTypes['post']);

        return $postTypes;
    }

    /**
     * @param array<string, bool|string> $robots
     * @return array<string, bool|string>
     */
    public function enhanceRobots(array $robots): array
    {
        $robots['max-image-preview'] = 'large';
        // Als Strings, nicht als int. wp_robots() verwirft numerische Werte sonst.
        $robots['max-snippet'] = '-1';
        $robots['max-video-preview'] = '-1';

        return $robots;
    }

    public function forceLang(string $output): string
    {
        if (is_admin()) {
            return $output;
        }

        $lang = (string) rhbp_setting(TechGroup::GROUP_ID, TechGroup::FIELD_LANG, 'de-DE');

        return 'lang="' . esc_attr($lang) . '"';
    }

    private function enabled(string $field, bool $default): bool
    {
        return (bool) rhbp_setting(TechGroup::GROUP_ID, $field, $default);
    }
}
