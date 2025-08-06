# 🆘 EMERGENCY: DUPLICATE PLUGIN REMOVAL

## CRITICAL SITUATION
You have TWO NPK plugins running simultaneously causing:
- Constant redefinition warnings
- Excessive shortcode registration spam
- Function conflicts

## LOGS SHOW BOTH PLUGINS ACTIVE:
```
/NPK_Valpeliste/npk_valpeliste.php (KEEP THIS ONE)
/NPK_Valpeliste_v1.9/npk_valpeliste.php (DELETE THIS ONE)
```

## 🚨 IMMEDIATE ACTION REQUIRED

### Step 1: Access Server Files
- FTP/cPanel/SSH access to your server
- Navigate to: `/wp-content/plugins/`

### Step 2: Emergency Removal
```bash
# Via SSH/Terminal:
rm -rf /wp-content/plugins/NPK_Valpeliste_v1.9/

# Via FTP/cPanel:
DELETE ENTIRE FOLDER: NPK_Valpeliste_v1.9
```

### Step 3: WordPress Admin Cleanup
```
1. Go to: WordPress Admin → Plugins
2. Look for TWO "NPK Valpeliste" entries
3. Deactivate the DUPLICATE one (usually shows as "NPK_Valpeliste_v1.9")
4. Delete the duplicate plugin entry
5. Keep only ONE "Pointer Valpeliste" active
```

### Step 4: Verify Fix
After removal, logs should show:
- ✅ Single "NPK Valpeliste: Shortcodes registered (v1.9.5)" message
- ❌ NO constant redefinition warnings
- ❌ NO registration spam

## 🔧 EMERGENCY FIXES IN v1.9.5
- Static registration prevention
- Constant definition safety checks  
- One-time logging only
- Duplicate plugin protection

## ⚠️ WHY THIS HAPPENED
WordPress loaded both plugin folders:
- Original: `NPK_Valpeliste` 
- Duplicate: `NPK_Valpeliste_v1.9` (auto-created during installation)

## 🎯 FINAL RESULT
After cleanup you should have:
- ONE plugin folder: `/NPK_Valpeliste/`
- ONE active plugin: "Pointer Valpeliste v1.9.5"  
- Clean debug logs
- Working shortcodes: `[valpeliste]` and `[npk_valpeliste]`

---
**DO NOT IGNORE THIS - MULTIPLE PLUGINS WILL BREAK YOUR SITE!**
