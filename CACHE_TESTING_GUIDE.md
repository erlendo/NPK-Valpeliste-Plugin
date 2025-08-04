# NPK Valpeliste Cache Testing Guide

## Problem Identifisert
Data fra datahound.no ble ikke oppdatert på grunn av WordPress transient caching (30 minutter cache).

## Implementerte Løsninger

### 1. Shortcode Force Refresh (Anbefalt)
```
[valpeliste force_refresh="yes"]
```
- **Bruk:** Rediger siden med shortcode og legg til parameter
- **Sikkerhet:** Kan brukes av alle
- **Testing:** Rediger siden, lagre, og sjekk om data oppdateres

### 2. URL Parameter (Admin Only)
```
https://yoursite.com/valpeliste-side/?npk_refresh=1
```
- **Bruk:** Legg til parameter i URL
- **Sikkerhet:** Kun for WordPress administratorer
- **Testing:** Logg inn som admin og besøk URL med parameter

### 3. Admin Panel Cache Control
- **Lokasjon:** WordPress Admin → NPK Valpeliste
- **Funksjoner:** 
  - "Tøm Cache" knapp
  - "Test API" knapp
  - Cache status visning
- **Testing:** Gå til admin panel og klikk knappene

### 4. Direkte Script Tilgang
```
https://yoursite.com/wp-content/plugins/NPK_Valpeliste/clear_cache.php
```
- **Bruk:** Direkte tilgang til cache clearing
- **Sikkerhet:** Basis IP/environment sjekker
- **Testing:** Besøk URL direkte

## Testing Procedure

### Steg 1: Verifiser Cache Eksisterer
1. Last siden med valpeliste
2. Noter timestamp eller spesifikk data
3. Cache skal nå være aktiv i 30 minutter

### Steg 2: Test Force Refresh
1. Endre shortcode til `[valpeliste force_refresh="yes"]`
2. Lagre siden
3. Sjekk at data oppdateres øyeblikkelig
4. Endre tilbake til `[valpeliste]`

### Steg 3: Test URL Parameter (som admin)
1. Logg inn som WordPress administrator
2. Besøk siden med `?npk_refresh=1`
3. Sjekk at data oppdateres
4. Besøk siden uten parameter (skal bruke cache igjen)

### Steg 4: Test Admin Panel
1. Gå til WordPress Admin → NPK Valpeliste
2. Klikk "Tøm Cache"
3. Sjekk meldinger for bekreftelse
4. Besøk valpeliste-siden og verifiser nye data

### Steg 5: Test Debug Mode
1. Som admin, besøk siden med `?npk_debug=1`
2. Sjekk at debug informasjon vises
3. Kombiner med refresh: `?npk_refresh=1&npk_debug=1`

## Cache Nøkkel Informasjon
- **Nøkkel:** `pointer_valpeliste_live_data`
- **Varighet:** 30 minutter (1800 sekunder)
- **Type:** WordPress transient
- **Lagring:** wp_options tabell

## Debugging
Hvis cache ikke tømmes:
1. Sjekk WordPress debug.log
2. Verifiser brukerrettigheter (admin funksjoner)
3. Test direkte script tilgang
4. Sjekk om andre caching plugins interfererer

## API Status
- **URL:** https://pointer.datahound.no/admin/product/newapi
- **Auth:** demo/demo (POST til /admin/doLogin.php)
- **Data Format:** JSON med totalCount og dogs array
- **Siste Test:** ✅ Vellykket (8 valper hentet)

## Anbefalinger
1. **Vanlig bruk:** Shortcode med force_refresh parameter
2. **Admin kontroll:** URL parameter eller admin panel
3. **Feilsøking:** Debug mode med URL parameter
4. **Nødløsning:** Direkte script tilgang
