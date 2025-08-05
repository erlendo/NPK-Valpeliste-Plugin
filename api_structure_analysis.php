<?php
echo "<h1>ANALYSE AV FAKTISK DATAHOUND API STRUKTUR</h1>\n";

echo "<h2>VIKTIGE FUNN:</h2>\n";

echo "<h3>1. IDENTIFIKASJON AV KULL:</h3>\n";
echo "<ul>\n";
echo "<li><strong>KUID:</strong> Unik ID for hvert kull (eks: '2334')</li>\n";
echo "<li><strong>kennel:</strong> Kennelnavn (eks: 'Oterbekkens')</li>\n";
echo "<li><strong>father:</strong> Fars registreringsnummer (eks: 'NO50734/22')</li>\n";
echo "<li><strong>mother:</strong> Mors registreringsnummer (eks: 'NO52639/18')</li>\n";
echo "<li><strong>FatherName:</strong> Fars navn</li>\n";
echo "<li><strong>MotherName:</strong> Mors navn</li>\n";
echo "</ul>\n";

echo "<h3>2. BADGE FELTER - KRITISK OPPDAGELSE:</h3>\n";
echo "<div style='background: #ffffcc; padding: 10px; border: 2px solid #ffaa00;'>\n";
echo "<p><strong>avlsh</strong> og <strong>eliteh</strong> finnes på KULL-nivå, IKKE individuelt!</p>\n";
echo "<p>Fra første kull (KUID 2334):</p>\n";
echo "<ul>\n";
echo "<li><strong>avlsh:</strong> '1' (kullet har avlshund)</li>\n";
echo "<li><strong>eliteh:</strong> '1' (kullet har elitehund)</li>\n";
echo "</ul>\n";
echo "<p>Fra andre kull (KUID 2332):</p>\n";
echo "<ul>\n";
echo "<li><strong>avlsh:</strong> null (kullet har IKKE avlshund)</li>\n";
echo "<li><strong>eliteh:</strong> null (kullet har IKKE elitehund)</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h3>3. INDIVIDUELLE DATA FOR FAR/MOR:</h3>\n";
echo "<ul>\n";
echo "<li><strong>premie:</strong> Fars premie-score</li>\n";
echo "<li><strong>PremieM:</strong> Mors premie-score</li>\n";
echo "<li><strong>jakt:</strong> Fars jakt-status</li>\n";
echo "<li><strong>jaktM:</strong> Mors jakt-status</li>\n";
echo "<li><strong>althdFather/althdF:</strong> Fars HD-score</li>\n";
echo "<li><strong>althdMother/althdM:</strong> Mors HD-score</li>\n";
echo "<li><strong>jaktindF/jaktindM:</strong> Fars/mors jaktindeks</li>\n";
echo "<li><strong>standindF/standindM:</strong> Fars/mors standardindeks</li>\n";
echo "</ul>\n";

echo "<h3>4. KONKLUSJON:</h3>\n";
echo "<div style='background: #ccffcc; padding: 10px; border: 2px solid #00aa00;'>\n";
echo "<p><strong>BADGE-LOGIKKEN MÅ VÆRE:</strong></p>\n";
echo "<ol>\n";
echo "<li>Hvis <code>avlsh='1'</code> → Begge foreldre får avlshund-badge</li>\n";
echo "<li>Hvis <code>eliteh='1'</code> → Den med høyest premie-score får elitehund-badge</li>\n";
echo "<li>Badge-status er IKKE lagret individuelt per hund i API</li>\n";
echo "<li>Vi må beregne hvem som får badges basert på kull-flagg + premie-score</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<h3>5. EKSEMPEL FRA FØRSTE KULL:</h3>\n";
echo "<pre>\n";
echo "KUID: 2334 (Oterbekkens)\n";
echo "avlsh: '1' → BEGGE får avlshund\n";
echo "eliteh: '1' → En av dem får elitehund\n";
echo "premie: '21' (far)\n";
echo "PremieM: '4' (mor)\n";
echo "→ FAR får elitehund (høyest premie: 21 > 4)\n";
echo "</pre>\n";

echo "<h3>6. ANDRE KULL EKSEMPEL:</h3>\n";
echo "<pre>\n";
echo "KUID: 2332 (Sølenriket)\n";
echo "avlsh: null → INGEN får avlshund\n";
echo "eliteh: null → INGEN får elitehund\n";
echo "premie: '18' (far)\n";
echo "PremieM: null (mor)\n";
echo "</pre>\n";

echo "<h2>SÅ DU HAR RETT!</h2>\n";
echo "<p>Badge-systemet mitt var feil. API gir oss badge-informasjon på KULL-nivå, ikke individuelt. Men logikken for å fordele dem må vi implementere basert på premie-score!</p>\n";
?>
