# RH SEO

SEO-Grundgerüst auf Basis zentraler Firmen-Stammdaten. Teil der rh-blueprint Kollektion.

Die Firmendaten werden einmal gepflegt, daraus baut das Plugin den strukturierten Datensatz und die Meta-Tags, statt alles im Theme zu hardcoden.

## Was es macht

- **Stammdaten als Single Source**: Name, Typ, Adresse, Geo, Kontakt, Öffnungszeiten, Social-Profile, Logo.
- **JSON-LD `@graph`** mit @id-Verknüpfung: Organization, LocalBusiness (oder gewählter Typ), WebSite, WebPage, BreadcrumbList auf Unterseiten. Eine Kern-Entity, alles referenziert sie statt zu duplizieren.
- **Meta-Tags**: Meta-Description, Open Graph, Twitter Cards, Canonical (eigenes `rel_canonical`, trailingslashit-konsistent).
- **SEO-Meta-Box pro Seite**: eigene Meta-Description und ein `noindex`-Schalter, unabhängig vom redaktionellen Auszug.
- **Technik-Cleanup**: Autoren/Beiträge/Taxonomien aus der Sitemap, erweiterte Robots-Direktiven (`max-snippet:-1` etc.), konfigurierbares `lang`-Attribut.

## Einstellungen

Im Backend unter **RH Blueprint → SEO**, zwei Gruppen:

- **Stammdaten**: Firmenname, Rechtsname, Unternehmenstyp (LocalBusiness, Dienstleister, Handwerk, Gastronomie, reine Organisation), E-Mail, Telefon, USt-IdNr., Gründer, Adresse, Geo-Koordinaten, Einzugsgebiet-Radius, Logo-URL, Open-Graph-Bild, Preisniveau, Öffnungszeiten (eine Zeile pro Eintrag), Social-Profile (eine URL pro Zeile).
- **Technik**: Sitemap-Bereinigung (Autoren/Beiträge/Taxonomien), Robots-Erweiterung, `lang`- und `og:locale`-Wert.

Pro Seite zusätzlich die **SEO**-Box (Meta-Description + noindex).

## Installation

ZIP unter **Plugins → Plugin hochladen** installieren und aktivieren, dann unter SEO die Stammdaten eintragen. Der geteilte Core ist gebündelt.

## Voraussetzungen

WordPress 6.5+, PHP 8.1+.
