<?php

declare(strict_types=1);

namespace RhSeo;

use RhSeo\Admin\BusinessDataGroup as F;

/**
 * Single Source of Truth für die Firmen-Stammdaten. Liest die Settings einmal
 * und liefert normalisierte, typisierte Werte an Graph und Meta-Tags.
 *
 * Domain-Klasse ohne WordPress-Rendering: kennt nur die Settings-Helper, kein
 * wp_head, keine Templates. Das hält sie testbar und wiederverwendbar.
 */
final class BusinessData
{
    /**
     * @var array<string, string>|null
     */
    private ?array $raw = null;

    public function get(string $field, string $default = ''): string
    {
        if ($this->raw === null) {
            $this->raw = (array) rhbp_setting(F::GROUP_ID);
        }

        $value = $this->raw[$field] ?? $default;

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public function name(): string
    {
        return $this->get(F::FIELD_NAME) ?: (string) get_bloginfo('name');
    }

    public function description(): string
    {
        return $this->get(F::FIELD_DESCRIPTION) ?: (string) get_bloginfo('description');
    }

    public function businessType(): string
    {
        return $this->get(F::FIELD_TYPE, 'LocalBusiness') ?: 'LocalBusiness';
    }

    public function logoUrl(): string
    {
        return $this->resolveImage(F::FIELD_LOGO_URL);
    }

    public function imageUrl(): string
    {
        return $this->resolveImage(F::FIELD_IMAGE_URL) ?: $this->logoUrl();
    }

    /**
     * Löst ein Bild-Feld zu einer URL auf. Die Felder speichern eine
     * Attachment-ID (portabel, URL wird hier erzeugt). Ein Altbestand als
     * Legacy-URL wird unverändert durchgereicht.
     */
    private function resolveImage(string $field): string
    {
        $value = $this->get($field);

        if ($value === '') {
            return '';
        }
        if (ctype_digit($value)) {
            $url = wp_get_attachment_image_url((int) $value, 'full');

            return is_string($url) ? $url : '';
        }

        return $value;
    }

    /**
     * PostalAddress-Knoten oder null, wenn keine Adressdaten gepflegt sind.
     *
     * @return array<string, string>|null
     */
    public function address(): ?array
    {
        $street = $this->get(F::FIELD_STREET);
        $locality = $this->get(F::FIELD_LOCALITY);

        if ($street === '' && $locality === '') {
            return null;
        }

        $address = ['@type' => 'PostalAddress'];

        if ($street !== '') {
            $address['streetAddress'] = $street;
        }
        if (($zip = $this->get(F::FIELD_POSTAL_CODE)) !== '') {
            $address['postalCode'] = $zip;
        }
        if ($locality !== '') {
            $address['addressLocality'] = $locality;
        }
        if (($region = $this->get(F::FIELD_REGION)) !== '') {
            $address['addressRegion'] = $region;
        }
        if (($country = $this->get(F::FIELD_COUNTRY)) !== '') {
            $address['addressCountry'] = $country;
        }

        return $address;
    }

    /**
     * GeoCoordinates-Knoten oder null, wenn keine Koordinaten gepflegt sind.
     *
     * @return array<string, float|string>|null
     */
    public function geo(): ?array
    {
        $lat = $this->get(F::FIELD_LATITUDE);
        $lng = $this->get(F::FIELD_LONGITUDE);

        if ($lat === '' || $lng === '') {
            return null;
        }

        return [
            '@type' => 'GeoCoordinates',
            'latitude' => (float) str_replace(',', '.', $lat),
            'longitude' => (float) str_replace(',', '.', $lng),
        ];
    }

    /**
     * Öffnungszeiten als Liste von schema.org-openingHours-Strings.
     *
     * @return array<int, string>
     */
    public function openingHours(): array
    {
        return $this->lines($this->get(F::FIELD_OPENING_HOURS));
    }

    /**
     * Social-Profil-URLs (sameAs).
     *
     * @return array<int, string>
     */
    public function socialProfiles(): array
    {
        return array_values(array_filter(
            $this->lines($this->get(F::FIELD_SOCIAL_PROFILES)),
            static fn (string $url): bool => filter_var($url, FILTER_VALIDATE_URL) !== false
        ));
    }

    public function areaRadius(): string
    {
        return $this->get(F::FIELD_AREA_RADIUS);
    }

    /**
     * @return array<int, string>
     */
    private function lines(string $value): array
    {
        if ($value === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $l): bool => $l !== ''));
    }
}
