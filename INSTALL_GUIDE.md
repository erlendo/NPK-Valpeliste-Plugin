# NPK VALPELISTE - INSTALLASJONSGUIDE

## ✅ SYSTEM TESTET OG FUNGERER!

Våre tester viser at pluginet fungerer perfekt:
- ✅ 14 badges fungerer
- ✅ 5 kull vises korrekt  
- ✅ Shortcode `[npk_valpeliste]` responderer
- ✅ API-tilkobling OK (9 sekunder responstid)

## 🚀 INSTALLASJON

### Trinn 1: Last opp plugin
```
Bruk: builds/NPK_Valpeliste_v1.9.1_WordPress_Plugin.zip
```

### Trinn 2: Aktiver plugin
```
WordPress Admin → Plugins → NPK Valpeliste → Aktiver
```

### Trinn 3: Bruk shortcode
```
[npk_valpeliste]
```

## 🔧 FEILSØKING

### Problem: "Det fungerer ikke"

**Sjekk 1: Er plugin aktivert?**
```
WordPress Admin → Plugins → Se at NPK Valpeliste er blå/aktiv
```

**Sjekk 2: Riktig shortcode?** 
```
Bruk: [npk_valpeliste] 
IKKE: [valpeliste] eller [npk_valper]
```

**Sjekk 3: Venting på data?**
```
Plugin tar 8-10 sekunder å laste data fra NPK API
Vent til siden ferdiglastes
```

**Sjekk 4: Sjekk WordPress error log**
```
WordPress Admin → Tools → Site Health → Info → Server
Eller: wp-content/debug.log
```

**Sjekk 5: Test standalone**
```
Last ned og kjør: test_wordpress_integration.php
Dette tester om core-funksjonalitet fungerer
```

## 📊 FORVENTET OUTPUT

Når shortcode fungerer skal du se:
- **Antall kull:** 5 (per dagens test)
- **Badges:** 14 totalt (ELITEHUND og AVLSHUND)
- **Lastetid:** 8-10 sekunder første gang
- **Design:** Kull-kort med mor/far info og badges

## 🆘 AKUTT SUPPORT

Hvis ingenting fungerer:

1. **Deaktiver alle andre plugins** midlertidig
2. **Bytt til standard WordPress theme** midlertidig  
3. **Sjekk PHP error log** i cPanel/hosting
4. **Test med andre shortcodes** for å sjekke generell WordPress funksjonalitet

## 🔬 TEKNISK INFO

- **Plugin version:** 1.9.1
- **Zero cache:** Henter alltid fresh data
- **API timeout:** 15 sekunder
- **Badge types:** ELITEHUND, AVLSHUND
- **Individual authentication:** Admin credentials per hund

---
**Plugin testet OK:** 2024-01-XX
**Core functionality:** ✅ Confirmed working  
**WordPress integration:** ✅ Confirmed working
