=== Pointer Valpeliste ===
Contributors: Erlendo
Tags: pointer, puppies, dogs, list, norwegian-pointer, valpeliste, kennel
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

En plugin som viser valpeliste fra pointer.datahound.no.

== Description ==

Pointer Valpeliste lar deg vise valpelistene fra pointer.datahound.no direkte på din WordPress-nettside. Bruk shortcode [valpeliste] for å vise valpelisten der du ønsker.

Hovedfunksjoner:
* Automatisk henting av valpedata fra API eller lokal JSON-fil
* Responsivt design med kortvisning og tabellvisning
* Merker for godkjente parringer, avlshund og elitehund
* Sortering med godkjente parringer først, deretter etter dato
* Caching for bedre ytelse
* Debug-modus for feilsøking
* Støtte for både [valpeliste] og [hent_valper] shortcodes

== Installation ==

1. Last opp "NPK_Valpeliste"-mappen til `/wp-content/plugins/`-katalogen
2. Aktiver pluginen gjennom 'Plugins'-menyen i WordPress
3. Plasser shortcode [valpeliste] på siden der du vil vise valpelisten
4. (Valgfritt) Konfigurer API-innstillinger under Innstillinger > NPK Valpeliste

== Frequently Asked Questions ==

= Hvordan oppdaterer jeg valpelisten manuelt? =

Du kan bruke shortcode [valpeliste force_refresh="yes"] for å tvinge oppdatering av dataene.

= Hvordan kan jeg feilsøke hvis valpelisten ikke vises? =

Du kan aktivere debug-modus med shortcode [valpeliste debug="yes"] for å se detaljert informasjon om API-kallet og databehandling.

= Kan jeg bruke en lokal JSON-fil i stedet for API? =

Ja, plasser en fil kalt `datahound.json` i plugin-mappen. Pluginen vil automatisk bruke denne i stedet for å kalle API-et.

= Hvilke shortcodes er tilgjengelige? =

Pluginen støtter to shortcodes:
* [valpeliste] - hovedshortcode
* [hent_valper] - alternativ shortcode

Begge støtter parametrene `debug="yes"` og `force_refresh="yes"`.

= Hva betyr de forskjellige merkene/badges? =

* Grønn stjerne: Godkjent parring
* Blå merke "Avlshund": Hund som er godkjent for avl
* Gull merke "Elitehund": Hund med elitestatus

== Changelog ==

= 1.1 (2025-05-28) =
* KRITISK REPARASJON: Fikset PHP Fatal errors forårsaket av funksjonsduplikater
* Forbedret include-struktur for å forhindre konflikter
* Lagt til støtte for lokal JSON-fil som datakilde
* Forbedret API-autentisering med session-håndtering
* Lagt til omfattende debug-funksjonalitet
* Forbedret feilhåndtering og sikkerhet
* Lagt til [hent_valper] som alternativ shortcode
* Forbedret badge-filtrering for "Avlshund"-merker

= 1.0 =
* Første versjon av pluginen
* Støtte for visning av valpeliste fra API
* Kortvisning og tabellvisning
* Merker for avlshunder og elitehunder
* Sortering etter godkjent status og dato

== Upgrade Notice ==

= 1.1 =
VIKTIG OPPDATERING: Denne versjonen fikser kritiske PHP Fatal errors som forårsaket at pluginen ikke fungerte. Anbefales sterkt for alle brukere.

= 1.0 =
Første versjon av Pointer Valpeliste plugin.
