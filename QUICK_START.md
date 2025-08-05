# Quick Start Guide - NPK Data Extractor

## 🚀 KJØR UMIDDELBART

```bash
cd /Users/erlendo/Local\ Sites/pointerdatabasen/app/public/wp-content/plugins/NPK_Valpeliste/

# Test kjøring
php test_npk_extractor.php

# Produksjons-kjøring  
php NPKDataExtractor.php
```

## 📁 RESULTAT

Du får automatisk:
- `npk_valpeliste_YYYY-MM-DD_HH-mm-ss.json` - Strukturerte data
- Terminal output med alle detaljer
- Error logging hvis noe feiler

## 🔧 TILPASS SETTINGS

Rediger `NPKDataExtractor.php` toppen:

```php
class NPKDataExtractor {
    private bool $debug = true;  // Sett til false for minder output
    private float $rateLimit = 0.5; // Sekunder mellom API-kall
}
```

## 📊 EXPECTED OUTPUT

- **5 kull** fra NPK valpeliste
- **Elite-data** på kull-nivå (individuelle kall feiler)
- **Komplett JSON** klar for videre bruk
- **20KB** strukturerte data

## ⚡ FERDIG!

Alt er satt opp og testet. JSON-filen kan importeres direkte i andre systemer.
