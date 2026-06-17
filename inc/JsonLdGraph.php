<?php

declare(strict_types=1);

namespace RhSeo;

/**
 * Baut den JSON-LD-@graph aus den Stammdaten und dem aktuellen Seitenkontext.
 *
 * @id-System (h-imports-Muster): Organization ist die Kern-Entity, alle anderen
 * Knoten referenzieren sie statt Daten zu duplizieren:
 *   #organization  ← Kern
 *   #logo          ← ImageObject, von Organization referenziert
 *   #<type>        ← LocalBusiness-Knoten, parentOrganization → #organization
 *   #website       ← publisher → #organization
 *   <url>#webpage  ← isPartOf → #website, about → #organization
 *   #breadcrumb    ← nur Unterseiten
 *
 * Conditional rein über is_front_page()/is_page(), keine hardcoded Post-IDs.
 */
final class JsonLdGraph
{
    public function __construct(private readonly BusinessData $business)
    {
    }

    public function boot(): void
    {
        add_action('wp_head', [$this, 'render'], 20);
    }

    public function render(): void
    {
        if (is_admin() || is_feed() || is_404()) {
            return;
        }

        $graph = $this->buildGraph();

        if ($graph === []) {
            return;
        }

        $json = wp_json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {
            return;
        }

        echo "\n" . '<script type="application/ld+json">' . $json . '</script>' . "\n";
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildGraph(): array
    {
        $site = trailingslashit(home_url('/'));
        $type = $this->business->businessType();

        $orgId = $site . '#organization';
        $logoId = $site . '#logo';
        $businessId = $site . '#localbusiness';
        $websiteId = $site . '#website';
        $pageId = Context::canonical() . '#webpage';

        $graph = [];

        $graph[] = $this->organizationNode($orgId, $logoId, $site);

        // Lokaler Knoten nur, wenn ein lokaler Typ gewählt ist (sonst genügt die Organization).
        if ($type !== 'Organization') {
            $graph[] = $this->localBusinessNode($type, $businessId, $orgId, $site);
        }

        $graph[] = $this->websiteNode($websiteId, $orgId, $site);

        $webpage = $this->webPageNode($pageId, $websiteId, $orgId);

        if (Context::isSubPage()) {
            $breadcrumbId = Context::canonical() . '#breadcrumb';
            $graph[] = $this->breadcrumbNode($breadcrumbId, $site);
            $webpage['breadcrumb'] = ['@id' => $breadcrumbId];
        }

        $graph[] = $webpage;

        return $graph;
    }

    /**
     * @return array<string, mixed>
     */
    private function organizationNode(string $orgId, string $logoId, string $site): array
    {
        $node = [
            '@type' => 'Organization',
            '@id' => $orgId,
            'name' => $this->business->name(),
            'url' => $site,
        ];

        if (($legal = $this->business->get(Admin\BusinessDataGroup::FIELD_LEGAL_NAME)) !== '') {
            $node['legalName'] = $legal;
        }
        if (($desc = $this->business->description()) !== '') {
            $node['description'] = $desc;
        }
        if (($logo = $this->business->logoUrl()) !== '') {
            $node['logo'] = [
                '@type' => 'ImageObject',
                '@id' => $logoId,
                'url' => $logo,
            ];
            $node['image'] = ['@id' => $logoId];
        }
        if (($email = $this->business->get(Admin\BusinessDataGroup::FIELD_EMAIL)) !== '') {
            $node['email'] = $email;
        }
        if (($phone = $this->business->get(Admin\BusinessDataGroup::FIELD_TELEPHONE)) !== '') {
            $node['telephone'] = $phone;
        }
        if (($vat = $this->business->get(Admin\BusinessDataGroup::FIELD_VAT_ID)) !== '') {
            $node['vatID'] = $vat;
        }
        if (($founder = $this->business->get(Admin\BusinessDataGroup::FIELD_FOUNDER)) !== '') {
            $node['founder'] = ['@type' => 'Person', 'name' => $founder];
        }
        if (($founded = $this->business->get(Admin\BusinessDataGroup::FIELD_FOUNDING_DATE)) !== '') {
            $node['foundingDate'] = $founded;
        }
        if (($address = $this->business->address()) !== null) {
            $node['address'] = $address;
        }
        $sameAs = $this->business->socialProfiles();
        if ($sameAs !== []) {
            $node['sameAs'] = $sameAs;
        }

        return $node;
    }

    /**
     * @return array<string, mixed>
     */
    private function localBusinessNode(string $type, string $businessId, string $orgId, string $site): array
    {
        $node = [
            '@type' => $type,
            '@id' => $businessId,
            'name' => $this->business->name(),
            'url' => $site,
            'parentOrganization' => ['@id' => $orgId],
        ];

        if (($phone = $this->business->get(Admin\BusinessDataGroup::FIELD_TELEPHONE)) !== '') {
            $node['telephone'] = $phone;
        }
        if (($email = $this->business->get(Admin\BusinessDataGroup::FIELD_EMAIL)) !== '') {
            $node['email'] = $email;
        }
        if (($image = $this->business->imageUrl()) !== '') {
            $node['image'] = $image;
        }
        if (($price = $this->business->get(Admin\BusinessDataGroup::FIELD_PRICE_RANGE)) !== '') {
            $node['priceRange'] = $price;
        }
        if (($address = $this->business->address()) !== null) {
            $node['address'] = $address;
        }
        if (($geo = $this->business->geo()) !== null) {
            $node['geo'] = $geo;

            $radius = $this->business->areaRadius();
            if ($radius !== '') {
                $node['areaServed'] = [
                    '@type' => 'GeoCircle',
                    'geoMidpoint' => $geo,
                    'geoRadius' => $radius,
                ];
            }
        }
        $hours = $this->business->openingHours();
        if ($hours !== []) {
            $node['openingHours'] = $hours;
        }

        return $node;
    }

    /**
     * @return array<string, mixed>
     */
    private function websiteNode(string $websiteId, string $orgId, string $site): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'name' => $this->business->name(),
            'url' => $site,
            'publisher' => ['@id' => $orgId],
            'inLanguage' => $this->inLanguage(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function webPageNode(string $pageId, string $websiteId, string $orgId): array
    {
        $node = [
            '@type' => 'WebPage',
            '@id' => $pageId,
            'url' => Context::canonical(),
            'name' => Context::title(),
            'isPartOf' => ['@id' => $websiteId],
            'about' => ['@id' => $orgId],
            'inLanguage' => $this->inLanguage(),
        ];

        if (is_singular()) {
            $postId = get_queried_object_id();
            $published = get_the_date('c', $postId);
            $modified = get_the_modified_date('c', $postId);
            if (is_string($published) && $published !== '') {
                $node['datePublished'] = $published;
            }
            if (is_string($modified) && $modified !== '') {
                $node['dateModified'] = $modified;
            }
        }

        return $node;
    }

    /**
     * @return array<string, mixed>
     */
    private function breadcrumbNode(string $breadcrumbId, string $site): array
    {
        return [
            '@type' => 'BreadcrumbList',
            '@id' => $breadcrumbId,
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => __('Startseite', 'rh-seo'),
                    'item' => $site,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => Context::title(),
                    'item' => Context::canonical(),
                ],
            ],
        ];
    }

    private function inLanguage(): string
    {
        $lang = (string) rhbp_setting(Admin\TechGroup::GROUP_ID, Admin\TechGroup::FIELD_LANG, 'de-DE');

        return $lang !== '' ? $lang : 'de-DE';
    }
}
