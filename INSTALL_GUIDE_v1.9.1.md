# NPK Valpeliste - Installasjonsveiledning

## 🚨 Løsning for "Fatal Error" Problemet

**Problem:** Du får fatal error når du aktiverer pluginet?
**Årsak:** Tidligere builds manglet kritiske filer.
**Løsning:** Bruk den nye korrigerte versjonen!

## 📦 Riktig Installasjon

### 1. Last ned riktig versjon
Bruk: `builds/NPK_Valpeliste_v1.9.1_WordPress_Plugin.zip` (40K)
❌ IKKE: eldre versjoner som manglet kritiske filer

### 2. Installer i WordPress
```
WordPress Admin → Plugins → Add New → Upload Plugin
→ Velg fil: NPK_Valpeliste_v1.9.1_WordPress_Plugin.zip
→ Install Now → Activate Plugin
```

### 3. Verifiser installasjon
Plugin skal vise "NPK Valpeliste" i plugin-listen uten feilmeldinger.

### 4. Test funksjonalitet
```
Rediger en side → Legg til shortcode:
[npk_valpeliste]

Publiser siden og sjekk at data vises.
```

## 🔍 Feilsøking

### Fatal Error ved aktivering?
1. **Deaktiver** det gamle pluginet
2. **Slett** plugin-mappen via FTP/cPanel
3. **Upload** ny versjon (v1.9.1)
4. **Aktiver** på nytt

### Shortcode virker ikke?
1. Sjekk at du bruker: `[npk_valpeliste]`
2. Vent 10-15 sekunder (API-kall tar tid)
3. Sjekk WordPress error logs

### Blank side eller timeout?
- API-kall tar 8-10 sekunder
- Øk PHP timeout: `max_execution_time = 30`
- Sjekk server tillater utgående connections

## ✅ Forventet Resultat

Shortcode `[npk_valpeliste]` skal vise:
- 5 kull med hundeinformasjon
- Elite og avlshund badges (gull/grønn)
- Oppdateringstidspunkt
- Responsiv design

## 🆘 Support

Hvis problemet vedvarer:
1. Aktiver WordPress debug: `WP_DEBUG = true`
2. Sjekk error logs
3. Test med `test_core.php` (separat fil for debugging)

**Status:** v1.9.1 - Korrekt versjon med alle filer ✅
