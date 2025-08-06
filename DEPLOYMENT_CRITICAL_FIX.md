# 🚨 CRITICAL: Fix Multiple Plugin Installation Error

## Problem
Fatal error due to multiple NPK plugin versions installed simultaneously:
- `NPK_Valpeliste` (old version)
- `NPK_Valpeliste_v1.9` (new version, causing conflicts)

Error: `Cannot redeclare npk_valpeliste_enqueue_scripts()`

## IMMEDIATE SOLUTION (Live Server)

### Step 1: Deactivate ALL NPK plugins
```
WordPress Admin → Plugins → Deactivate all NPK_Valpeliste versions
```

### Step 2: Remove old plugin folder
```bash
# Via FTP/cPanel File Manager:
Delete: /wp-content/plugins/NPK_Valpeliste_v1.9/
```

### Step 3: Update existing plugin
```bash
# Upload NEW files to existing folder:
/wp-content/plugins/NPK_Valpeliste/
```

### Step 4: Reactivate single plugin
```
WordPress Admin → Plugins → Activate "Pointer Valpeliste" (version 1.9.3)
```

## Files to Upload (OVERWRITE)
- `npk_valpeliste.php` (main plugin file)
- `NPKDataExtractorLive.php` (API extractor)
- `live_display_example.php` (display functions)
- All files in `includes/` folder
- All files in `assets/` folder

## Production Build Available
```
NPK_Valpeliste_v1.9.3_WordPress_Plugin.zip (40K)
```

## Critical Fixes in v1.9.3
✅ Function name conflicts resolved
✅ Debug logging controlled (WP_DEBUG only)
✅ Shortcode registration spam eliminated
✅ Error handling for missing includes
✅ New [npk_valpeliste] shortcode with breeding approval

## Test After Deployment
1. Plugin activates without errors
2. Debug log shows single registration message
3. Shortcodes work: `[valpeliste]` and `[npk_valpeliste]`
4. Breeding approval badges display correctly

## Emergency Rollback
If issues persist:
1. Deactivate plugin
2. Restore from `archive/backups/`
3. Contact developer

---
**URGENT: Do NOT install multiple plugin versions simultaneously!**
