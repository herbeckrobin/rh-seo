<?php

declare(strict_types=1);

namespace RhSeo;

use RhSeo\Admin\SeoMetaBox;
use RhSeo\Admin\TechGroup;

/**
 * Rendert Meta-Description, Open Graph, Twitter Cards und Canonical im <head>.
 *
 * Description-Quelle: das eigene SEO-Feld der Seite, sonst die Firmen-Kurzbeschreibung.
 * Canonical wird selbst gerendert (rel_canonical aus dem Core-Head entfernt), damit
 * es trailingslashit-konsistent zur JSON-LD-Canonical ist.
 */
final class MetaTags
{
    public function __construct(private readonly BusinessData $business)
    {
    }

    public function boot(): void
    {
        remove_action('wp_head', 'rel_canonical');
        add_action('wp_head', [$this, 'render'], 5);
    }

    public function render(): void
    {
        if (is_admin() || is_feed()) {
            return;
        }

        $title = Context::title();
        $description = $this->description();
        $canonical = Context::canonical();
        $image = $this->business->imageUrl();
        $type = is_singular() && ! is_front_page() ? 'article' : 'website';
        $locale = (string) rhbp_setting(TechGroup::GROUP_ID, TechGroup::FIELD_LOCALE, 'de_DE');

        $out = "\n";

        if ($this->isNoindex()) {
            $out .= '<meta name="robots" content="noindex,follow">' . "\n";
        }

        if ($description !== '') {
            $out .= '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        }

        $out .= '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";

        // Open Graph
        $out .= '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
        $out .= '<meta property="og:site_name" content="' . esc_attr($this->business->name()) . '">' . "\n";
        if ($locale !== '') {
            $out .= '<meta property="og:locale" content="' . esc_attr($locale) . '">' . "\n";
        }
        $out .= '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        if ($description !== '') {
            $out .= '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        }
        $out .= '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
        if ($image !== '') {
            $out .= '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        }

        // Twitter Card
        $out .= '<meta name="twitter:card" content="' . ($image !== '' ? 'summary_large_image' : 'summary') . '">' . "\n";
        $out .= '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
        if ($description !== '') {
            $out .= '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
        }
        if ($image !== '') {
            $out .= '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
        }

        echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- alle Werte einzeln escaped
    }

    /**
     * Description-Quelle, Fallback-Kette:
     *   1. eigenes SEO-Feld der Seite (_rhseo_description)
     *   2. redaktioneller Auszug der Seite (post_excerpt, editor-nativ)
     *   3. Firmen-Kurzbeschreibung
     *
     * post_excerpt wird roh gelesen (nicht get_the_excerpt()), damit kein
     * Auto-Trim des Seiteninhalts greift, sondern nur ein bewusst gepflegter Auszug.
     */
    private function description(): string
    {
        if (is_singular()) {
            $postId = get_queried_object_id();

            $custom = (string) get_post_meta($postId, SeoMetaBox::META_DESCRIPTION, true);
            if (trim($custom) !== '') {
                return wp_strip_all_tags($custom);
            }

            $excerpt = (string) get_post_field('post_excerpt', $postId);
            if (trim($excerpt) !== '') {
                return wp_strip_all_tags($excerpt);
            }
        }

        return wp_strip_all_tags($this->business->description());
    }

    private function isNoindex(): bool
    {
        if (! is_singular()) {
            return false;
        }

        return (bool) get_post_meta(get_queried_object_id(), SeoMetaBox::META_NOINDEX, true);
    }
}
