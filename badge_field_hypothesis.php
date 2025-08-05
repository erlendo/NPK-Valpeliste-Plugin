<?php
// Test basert på det vi VET finnes i API - let meg ANTA at de individuelle feltene finnes
echo "<h2>HYPOTESE: Individuelle badge-felter finnes som separate felter</h2>\n";

echo "<p>Basert på at du sier avlshund og elitehund er individuelle, må API ha separate felter for far og mor.</p>\n";

echo "<h3>Mulige felt-navnstrukturer:</h3>\n";
echo "<strong>Alternativ 1 - F/M suffikser:</strong><br>\n";
echo "- avlshF (far avlshund)<br>\n";
echo "- avlshM (mor avlshund)<br>\n"; 
echo "- elitehF (far elitehund)<br>\n";
echo "- elitehM (mor elitehund)<br>\n";

echo "<br><strong>Alternativ 2 - Father/Mother suffikser:</strong><br>\n";
echo "- avlshFather<br>\n";
echo "- avlshMother<br>\n";
echo "- elitehFather<br>\n";
echo "- elitehMother<br>\n";

echo "<br><strong>Alternativ 3 - Prefix struktur:</strong><br>\n";
echo "- fatherAvlsh<br>\n";
echo "- motherAvlsh<br>\n";
echo "- fatherEliteh<br>\n";
echo "- motherEliteh<br>\n";

echo "<br><strong>Alternativ 4 - Helt andre feltnavn:</strong><br>\n";
echo "- Kanskje helt andre navn som vi ikke har gjettet?<br>\n";

echo "<h3>SPØRSMÅL TIL DEG:</h3>\n";
echo "<p>Kan du fortelle meg EKSAKT hvilke feltnavn som inneholder de individuelle badge-verdiene i API responsen fra Datahound?</p>\n";

echo "<p>Eller kan du gi meg et eksempel på hvordan JSON responsen ser ut for EN hund som har disse individuelle badge-verdiene?</p>\n";

// Test med det vi har fra før
echo "<h3>DETTE FANT VI I FORRIGE API KALL:</h3>\n";
echo "<pre>\n";
echo "Dog 1: Kennel Villmannsbu\n";
echo "  FatherName: Rypeparadiset's Cacciatore\n";
echo "  MotherName: Myrteigen's Snuppa\n";
echo "  avlsh: '0'  <- dette er kull-nivå\n";
echo "  eliteh: '1' <- dette er kull-nivå\n";
echo "  premie: '39'  <- fars premie\n";
echo "  PremieM: '31' <- mors premie\n";
echo "</pre>\n";

echo "<p><strong>MEN DU SIER at avlsh og eliteh skal være individuelle per hund!</strong></p>\n";
echo "<p>Så det MÅ finnes andre feltnavn for individuelle verdier som vi ikke har funnet ennå.</p>\n";
?>
