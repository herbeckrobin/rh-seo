<?php

declare(strict_types=1);

namespace RhSeo\Admin;

use RhBlueprint\Core\Settings\GroupInterface;
use RhBlueprint\Core\Settings\SettingField;

/**
 * Settings-Gruppe für die Firmen-Stammdaten, die Single Source of Truth für SEO.
 *
 * Aus diesen Feldern speisen sich JSON-LD-Graph, Open-Graph-Tags und Canonical.
 * Eine Site = ein Unternehmen, darum leben die Daten zentral in den Optionen,
 * nicht pro Seite. Der Core rendert die Gruppe automatisch im Tab "SEO".
 */
final class BusinessDataGroup implements GroupInterface
{
    public const GROUP_ID = 'seo_business';

    public const FIELD_NAME = 'name';
    public const FIELD_LEGAL_NAME = 'legal_name';
    public const FIELD_TYPE = 'business_type';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_EMAIL = 'email';
    public const FIELD_TELEPHONE = 'telephone';
    public const FIELD_VAT_ID = 'vat_id';
    public const FIELD_FOUNDER = 'founder';
    public const FIELD_FOUNDING_DATE = 'founding_date';
    public const FIELD_STREET = 'street';
    public const FIELD_POSTAL_CODE = 'postal_code';
    public const FIELD_LOCALITY = 'locality';
    public const FIELD_REGION = 'region';
    public const FIELD_COUNTRY = 'country';
    public const FIELD_LATITUDE = 'latitude';
    public const FIELD_LONGITUDE = 'longitude';
    public const FIELD_AREA_RADIUS = 'area_radius';
    public const FIELD_LOGO_URL = 'logo_url';
    public const FIELD_IMAGE_URL = 'image_url';
    public const FIELD_PRICE_RANGE = 'price_range';
    public const FIELD_OPENING_HOURS = 'opening_hours';
    public const FIELD_SOCIAL_PROFILES = 'social_profiles';

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
        return __('Stammdaten', 'rh-seo');
    }

    public function description(): string
    {
        return __('Die zentralen Firmendaten. Sie speisen JSON-LD, Open Graph und Canonical. Einmal hier pflegen statt im Theme hardcoden.', 'rh-seo');
    }

    public function fields(): array
    {
        return [
            new SettingField(
                id: self::FIELD_NAME,
                type: SettingField::TYPE_TEXT,
                label: __('Firmenname', 'rh-seo'),
                description: __('Der öffentliche Name, wie er in Suchergebnissen und Social-Vorschauen erscheint.', 'rh-seo'),
                keywords: ['name', 'firma', 'unternehmen', 'organisation'],
            ),
            new SettingField(
                id: self::FIELD_LEGAL_NAME,
                type: SettingField::TYPE_TEXT,
                label: __('Rechtlicher Name', 'rh-seo'),
                description: __('Vollständiger juristischer Name, falls abweichend vom Firmennamen (z.B. Inhaber oder GmbH-Firmierung).', 'rh-seo'),
                keywords: ['legalname', 'rechtsform', 'inhaber'],
            ),
            new SettingField(
                id: self::FIELD_TYPE,
                type: SettingField::TYPE_SELECT,
                label: __('Unternehmenstyp (Schema)', 'rh-seo'),
                description: __('Der schema.org-Typ für das lokale Unternehmen. Bestimmt, wie Google das Unternehmen einordnet.', 'rh-seo'),
                default: 'LocalBusiness',
                choices: [
                    'LocalBusiness' => __('Lokales Unternehmen (allgemein)', 'rh-seo'),
                    'ProfessionalService' => __('Dienstleister', 'rh-seo'),
                    'Store' => __('Ladengeschäft', 'rh-seo'),
                    'HomeAndConstructionBusiness' => __('Handwerk / Bau', 'rh-seo'),
                    'FoodEstablishment' => __('Gastronomie', 'rh-seo'),
                    'Organization' => __('Reine Organisation (nicht lokal)', 'rh-seo'),
                ],
                keywords: ['typ', 'schema', 'localbusiness', 'organization'],
            ),
            new SettingField(
                id: self::FIELD_DESCRIPTION,
                type: SettingField::TYPE_TEXTAREA,
                label: __('Kurzbeschreibung', 'rh-seo'),
                description: __('Ein bis zwei Sätze über das Unternehmen. Dient als Fallback-Meta-Description für Seiten ohne eigene Beschreibung.', 'rh-seo'),
                keywords: ['beschreibung', 'description', 'slogan'],
            ),
            new SettingField(
                id: self::FIELD_EMAIL,
                type: SettingField::TYPE_EMAIL,
                label: __('E-Mail', 'rh-seo'),
                keywords: ['email', 'kontakt'],
            ),
            new SettingField(
                id: self::FIELD_TELEPHONE,
                type: SettingField::TYPE_TEXT,
                label: __('Telefon', 'rh-seo'),
                description: __('Im internationalen Format, z.B. +49 7131 123456.', 'rh-seo'),
                keywords: ['telefon', 'phone'],
            ),
            new SettingField(
                id: self::FIELD_VAT_ID,
                type: SettingField::TYPE_TEXT,
                label: __('USt-IdNr.', 'rh-seo'),
                keywords: ['ust', 'vat', 'steuer'],
            ),
            new SettingField(
                id: self::FIELD_FOUNDER,
                type: SettingField::TYPE_TEXT,
                label: __('Gründer / Inhaber', 'rh-seo'),
                keywords: ['gruender', 'inhaber', 'founder'],
            ),
            new SettingField(
                id: self::FIELD_FOUNDING_DATE,
                type: SettingField::TYPE_TEXT,
                label: __('Gründungsjahr', 'rh-seo'),
                description: __('Jahr oder Datum, z.B. 2019 oder 2019-04-01.', 'rh-seo'),
                keywords: ['gruendung', 'founding', 'jahr'],
            ),
            new SettingField(
                id: self::FIELD_STREET,
                type: SettingField::TYPE_TEXT,
                label: __('Straße und Hausnummer', 'rh-seo'),
                keywords: ['strasse', 'adresse', 'street'],
            ),
            new SettingField(
                id: self::FIELD_POSTAL_CODE,
                type: SettingField::TYPE_TEXT,
                label: __('PLZ', 'rh-seo'),
                keywords: ['plz', 'postleitzahl'],
            ),
            new SettingField(
                id: self::FIELD_LOCALITY,
                type: SettingField::TYPE_TEXT,
                label: __('Ort', 'rh-seo'),
                keywords: ['ort', 'stadt', 'locality'],
            ),
            new SettingField(
                id: self::FIELD_REGION,
                type: SettingField::TYPE_TEXT,
                label: __('Region / Bundesland', 'rh-seo'),
                keywords: ['region', 'bundesland'],
            ),
            new SettingField(
                id: self::FIELD_COUNTRY,
                type: SettingField::TYPE_TEXT,
                label: __('Land (ISO-Code)', 'rh-seo'),
                description: __('Zweistelliger Ländercode, z.B. DE, AT, CH.', 'rh-seo'),
                default: 'DE',
                keywords: ['land', 'country', 'iso'],
            ),
            new SettingField(
                id: self::FIELD_LATITUDE,
                type: SettingField::TYPE_TEXT,
                label: __('Breitengrad (Latitude)', 'rh-seo'),
                description: __('Geo-Koordinate, z.B. 49.1427. Optional, aktiviert Geo-Daten im Schema.', 'rh-seo'),
                keywords: ['geo', 'latitude', 'koordinate'],
            ),
            new SettingField(
                id: self::FIELD_LONGITUDE,
                type: SettingField::TYPE_TEXT,
                label: __('Längengrad (Longitude)', 'rh-seo'),
                description: __('Geo-Koordinate, z.B. 9.2109.', 'rh-seo'),
                keywords: ['geo', 'longitude', 'koordinate'],
            ),
            new SettingField(
                id: self::FIELD_AREA_RADIUS,
                type: SettingField::TYPE_TEXT,
                label: __('Einzugsgebiet-Radius (Meter)', 'rh-seo'),
                description: __('Radius des bedienten Gebiets in Metern, z.B. 40000 für 40 km. Braucht Geo-Koordinaten.', 'rh-seo'),
                keywords: ['einzugsgebiet', 'radius', 'areaserved'],
            ),
            new SettingField(
                id: self::FIELD_LOGO_URL,
                type: SettingField::TYPE_URL,
                label: __('Logo-URL', 'rh-seo'),
                description: __('Vollständige URL zum Logo (quadratisch, mind. 112x112 px) für das Organisations-Schema.', 'rh-seo'),
                keywords: ['logo', 'bild'],
            ),
            new SettingField(
                id: self::FIELD_IMAGE_URL,
                type: SettingField::TYPE_URL,
                label: __('Standard-Vorschaubild (Open Graph)', 'rh-seo'),
                description: __('Vollständige URL zum Bild, das beim Teilen in Social Media erscheint (ideal 1200x630 px).', 'rh-seo'),
                keywords: ['og', 'opengraph', 'vorschau', 'social'],
            ),
            new SettingField(
                id: self::FIELD_PRICE_RANGE,
                type: SettingField::TYPE_SELECT,
                label: __('Preisniveau', 'rh-seo'),
                default: '',
                choices: [
                    '' => __('Keine Angabe', 'rh-seo'),
                    '€' => '€',
                    '€€' => '€€',
                    '€€€' => '€€€',
                    '€€€€' => '€€€€',
                ],
                keywords: ['preis', 'pricerange'],
            ),
            new SettingField(
                id: self::FIELD_OPENING_HOURS,
                type: SettingField::TYPE_TEXTAREA,
                label: __('Öffnungszeiten', 'rh-seo'),
                description: __('Eine Zeile pro Eintrag im Format "Mo-Fr 08:00-17:00" oder "Sa 09:00-12:00". Leer lassen, wenn nicht relevant.', 'rh-seo'),
                keywords: ['oeffnungszeiten', 'opening', 'hours'],
            ),
            new SettingField(
                id: self::FIELD_SOCIAL_PROFILES,
                type: SettingField::TYPE_TEXTAREA,
                label: __('Social-Profile', 'rh-seo'),
                description: __('Eine vollständige Profil-URL pro Zeile (Instagram, Facebook, LinkedIn etc.). Landen als sameAs im Schema.', 'rh-seo'),
                keywords: ['social', 'instagram', 'facebook', 'sameas'],
            ),
        ];
    }
}
