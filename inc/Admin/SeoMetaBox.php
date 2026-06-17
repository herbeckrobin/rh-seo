<?php

declare(strict_types=1);

namespace RhSeo\Admin;

/**
 * Eigene SEO-Meta-Box pro Seite/Beitrag: Meta-Description und noindex-Schalter.
 *
 * Bewusst eigenes Feld statt des post_excerpt, damit die SEO-Beschreibung
 * unabhängig vom redaktionellen Auszug gepflegt wird. Klassische Meta-Box,
 * erscheint im Block-Editor in den Dokument-Einstellungen.
 */
final class SeoMetaBox
{
    public const META_TITLE = '_rhseo_title';
    public const META_DESCRIPTION = '_rhseo_description';
    public const META_NOINDEX = '_rhseo_noindex';
    public const META_SERVICE_NAME = '_rhseo_service_name';
    public const META_SERVICE_TYPE = '_rhseo_service_type';

    private const NONCE_ACTION = 'rhseo_save_meta';
    private const NONCE_NAME = 'rhseo_meta_nonce';

    /**
     * @var array<int, string>
     */
    private array $postTypes;

    /**
     * @param array<int, string> $postTypes
     */
    public function __construct(array $postTypes = ['page', 'post'])
    {
        $this->postTypes = $postTypes;
    }

    public function boot(): void
    {
        add_action('add_meta_boxes', [$this, 'register']);
        add_action('save_post', [$this, 'save'], 10, 2);
    }

    public function register(): void
    {
        foreach ($this->postTypes as $postType) {
            add_meta_box(
                'rhseo_meta',
                __('SEO', 'rh-seo'),
                [$this, 'render'],
                $postType,
                'side',
                'default'
            );
        }
    }

    public function render(\WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        $title = (string) get_post_meta($post->ID, self::META_TITLE, true);
        $description = (string) get_post_meta($post->ID, self::META_DESCRIPTION, true);
        $noindex = (bool) get_post_meta($post->ID, self::META_NOINDEX, true);

        echo '<p><label for="rhseo_title"><strong>' . esc_html__('Browser-Title', 'rh-seo') . '</strong></label></p>';
        echo '<input type="text" id="rhseo_title" name="rhseo_title" class="widefat" value="' . esc_attr($title) . '" placeholder="' . esc_attr__('Leer lassen für Theme/Template-Title', 'rh-seo') . '">';
        echo '<p class="description">' . esc_html__('Überschreibt den Title nur für diese Seite. Leer lassen, dann greift das Title-Template bzw. das Theme.', 'rh-seo') . '</p>';

        echo '<p><label for="rhseo_description"><strong>' . esc_html__('Meta-Description', 'rh-seo') . '</strong></label></p>';
        echo '<textarea id="rhseo_description" name="rhseo_description" rows="4" class="widefat" maxlength="320" placeholder="' . esc_attr__('Kurze Beschreibung für Suchergebnisse (ca. 155 Zeichen).', 'rh-seo') . '">' . esc_textarea($description) . '</textarea>';
        echo '<p class="description">' . esc_html__('Leer lassen, dann greift die Firmen-Kurzbeschreibung als Fallback.', 'rh-seo') . '</p>';

        echo '<p><label for="rhseo_noindex"><input type="checkbox" id="rhseo_noindex" name="rhseo_noindex" value="1" ' . checked($noindex, true, false) . '> ' . esc_html__('Von Suchmaschinen ausschließen (noindex)', 'rh-seo') . '</label></p>';

        $serviceName = (string) get_post_meta($post->ID, self::META_SERVICE_NAME, true);
        $serviceType = (string) get_post_meta($post->ID, self::META_SERVICE_TYPE, true);

        echo '<hr style="margin:12px 0;border:0;border-top:1px solid #dcdcde;">';
        echo '<p><label for="rhseo_service_name"><strong>' . esc_html__('Service (optional)', 'rh-seo') . '</strong></label></p>';
        echo '<input type="text" id="rhseo_service_name" name="rhseo_service_name" class="widefat" value="' . esc_attr($serviceName) . '" placeholder="' . esc_attr__('z.B. Heizungsinstallation', 'rh-seo') . '">';
        echo '<p class="description">' . esc_html__('Wenn gesetzt, ergänzt rh-seo ein Service-Schema für diese Seite (Anbieter = dein Unternehmen).', 'rh-seo') . '</p>';
        echo '<p><label for="rhseo_service_type">' . esc_html__('Service-Kategorie', 'rh-seo') . '</label></p>';
        echo '<input type="text" id="rhseo_service_type" name="rhseo_service_type" class="widefat" value="' . esc_attr($serviceType) . '" placeholder="' . esc_attr__('z.B. Sanitär, Heizung (serviceType)', 'rh-seo') . '">';
    }

    public function save(int $postId, \WP_Post $post): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! in_array($post->post_type, $this->postTypes, true)) {
            return;
        }

        $nonce = isset($_POST[self::NONCE_NAME]) ? sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])) : '';
        if ($nonce === '' || ! wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }

        if (! current_user_can('edit_post', $postId)) {
            return;
        }

        $description = isset($_POST['rhseo_description'])
            ? sanitize_textarea_field(wp_unslash($_POST['rhseo_description']))
            : '';

        if ($description !== '') {
            update_post_meta($postId, self::META_DESCRIPTION, $description);
        } else {
            delete_post_meta($postId, self::META_DESCRIPTION);
        }

        if (isset($_POST['rhseo_noindex'])) {
            update_post_meta($postId, self::META_NOINDEX, '1');
        } else {
            delete_post_meta($postId, self::META_NOINDEX);
        }

        $this->saveText($postId, self::META_TITLE, 'rhseo_title');
        $this->saveText($postId, self::META_SERVICE_NAME, 'rhseo_service_name');
        $this->saveText($postId, self::META_SERVICE_TYPE, 'rhseo_service_type');
    }

    /**
     * Speichert ein einzelnes Textfeld oder löscht das Meta, wenn leer.
     */
    private function saveText(int $postId, string $metaKey, string $postKey): void
    {
        $value = isset($_POST[$postKey])
            ? sanitize_text_field(wp_unslash($_POST[$postKey]))
            : '';

        if ($value !== '') {
            update_post_meta($postId, $metaKey, $value);
        } else {
            delete_post_meta($postId, $metaKey);
        }
    }
}
