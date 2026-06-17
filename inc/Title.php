<?php

declare(strict_types=1);

namespace RhSeo;

use RhSeo\Admin\BusinessDataGroup;
use RhSeo\Admin\SeoMetaBox;
use RhSeo\Admin\TechGroup;

/**
 * Optionaler Title-Override über `pre_get_document_title`.
 *
 * Bewusst opt-in: das Modul fasst den <title> NUR an, wenn die Seite ein
 * eigenes Title-Feld hat oder global ein Title-Template gepflegt ist. Ist beides
 * leer, gibt der Filter den leeren String zurück und WordPress/das Theme
 * bestimmen den Title wie gehabt. Kein erzwungenes Title-Schema, kein
 * Theme-Konflikt.
 *
 * Da `Context::title()` über `wp_get_document_title()` läuft, übernehmen Graph
 * und Open-Graph-Tags den Override automatisch, eine Quelle, keine Divergenz.
 */
final class Title
{
    public function __construct(private readonly BusinessData $business)
    {
    }

    public function boot(): void
    {
        add_filter('pre_get_document_title', [$this, 'filter']);
    }

    public function filter(string $title): string
    {
        // 1. Per-Seite-Override gewinnt immer.
        if (is_singular()) {
            $custom = trim((string) get_post_meta(get_queried_object_id(), SeoMetaBox::META_TITLE, true));
            if ($custom !== '') {
                return $custom;
            }
        }

        // 2. Globales Template, nur wenn gepflegt.
        $template = trim((string) rhbp_setting(TechGroup::GROUP_ID, TechGroup::FIELD_TITLE_TEMPLATE, ''));
        if ($template === '') {
            return $title; // Gate: nichts konfiguriert, Theme behält die Hoheit.
        }

        $built = $this->build($template);

        return $built !== '' ? $built : $title;
    }

    /**
     * Ersetzt die Platzhalter und räumt einen durch leere Token entstandenen
     * Rand-Separator weg. Mittelständige optionale Token (%location%) gehören
     * darum ans Template-Ende, das ist in der Feldbeschreibung dokumentiert.
     */
    private function build(string $template): string
    {
        $replacements = [
            '%page%' => $this->pagePart(),
            '%business%' => $this->business->name(),
            '%location%' => $this->business->get(BusinessDataGroup::FIELD_LOCALITY),
        ];

        $title = strtr($template, $replacements);
        $title = (string) preg_replace('/\s+/', ' ', $title);
        // Rand-Separatoren (Pipe, Doppelpunkt, Slash, Mittelpunkt, Komma) trimmen.
        $title = (string) preg_replace('/^[\s|:\/·,]+|[\s|:\/·,]+$/u', '', $title);

        return trim($title);
    }

    /**
     * Der kontextuelle Seitenteil (%page%). Auf der Startseite bewusst leer,
     * damit dort z.B. nur der Firmenname stehen bleibt.
     */
    private function pagePart(): string
    {
        if (is_front_page()) {
            return '';
        }
        if (is_singular()) {
            return (string) get_the_title(get_queried_object_id());
        }
        if (is_post_type_archive()) {
            return (string) post_type_archive_title('', false);
        }
        if (is_category() || is_tag() || is_tax()) {
            return (string) single_term_title('', false);
        }
        if (is_search()) {
            return sprintf(
                /* translators: %s: search query. */
                __('Suche: %s', 'rh-seo'),
                get_search_query()
            );
        }
        if (is_404()) {
            return __('Seite nicht gefunden', 'rh-seo');
        }

        return (string) wp_title('', false);
    }
}
