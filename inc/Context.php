<?php

declare(strict_types=1);

namespace RhSeo;

/**
 * Geteilte Berechnungen über den aktuellen Request: Canonical-URL und Dokumenttitel.
 *
 * Graph und Meta-Tags konsumieren dieselben Werte, eine Quelle, keine Divergenz
 * (z.B. trailingslashit-Konsistenz der Canonical).
 */
final class Context
{
    public static function canonical(): string
    {
        if (is_singular()) {
            $permalink = get_permalink(get_queried_object_id());
            if (is_string($permalink) && $permalink !== '') {
                return trailingslashit($permalink);
            }
        }

        return trailingslashit(home_url('/'));
    }

    public static function title(): string
    {
        return html_entity_decode(wp_get_document_title(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function isSubPage(): bool
    {
        return is_page() && ! is_front_page();
    }
}
