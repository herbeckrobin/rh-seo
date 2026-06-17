=== RH SEO ===
Contributors: robinherbeck
Tags: seo, schema, json-ld, open graph, sitemap
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lean SEO foundation: company master data as a single source, JSON-LD graph, meta and Open Graph tags, canonical, sitemap cleanup.

== Description ==

RH SEO is the SEO baseline for a focused company website. Maintain the company master data once and the plugin builds the structured data and meta tags from it, instead of hardcoding everything in the theme.

= Features =

* Company master data as a single source (name, type, address, geo, contact, opening hours, social profiles, logo)
* JSON-LD @graph with @id linking: Organization, LocalBusiness, WebSite, WebPage and BreadcrumbList on subpages
* Meta description, Open Graph and Twitter Card tags, canonical URL
* Per-page SEO meta box: own meta description and noindex toggle
* Sitemap cleanup (drop users, optionally posts and taxonomies), enhanced robots directives, configurable lang attribute

The business type drives the schema.org type (LocalBusiness, ProfessionalService, Store and others). Geo coordinates and an area radius emit areaServed automatically.

Part of the rh-blueprint collection. Settings live under RH Blueprint > SEO.

== Changelog ==

= 0.1.0 =
* Initial release: master data settings, JSON-LD graph, meta/OG/Twitter tags, per-page meta box, sitemap/robots/lang cleanup.
