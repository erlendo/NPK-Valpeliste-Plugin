<?php
/**
 * NPK Valpeliste Data Extractor
 * 
 * En dedikert PHP-klasse for å hente og strukturere data fra NPK's datahound.no API
 * Kombinerer valpeliste-data med individuelle elite-status oppslag
 * 
 * @author NPK Valpeliste Plugin Team
 * @version 1.0
 * @since 2025-08-05
 */

class NPKDataExtractor {
    
    private $session;
    private $baseUrl = 'https://pointer.datahound.no';
    private $authenticated = false;
    private $cookieFile;
    private $debug = false;
    private $logs = [];
    
    public function __construct(bool $debug = false) {
        $this->debug = $debug;
        $this->cookieFile = sys_get_temp_dir() . '/npk_extractor_cookies_' . uniqid() . '.txt';
        $this->log("NPK Data Extractor initialisert");
    }
    
    public function __destruct() {
        // Rydd opp cookie-fil
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }
    
    /**
     * Logger melding med timestamp
     */
    private function log(string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}";
        $this->logs[] = $logEntry;
        
        if ($this->debug) {
            echo $logEntry . "\n";
        }
    }
    
    /**
     * Autentiser til NPK's admin-system
     * Følger presis sekvens: GET login side -> POST credentials -> HTTP 302 = suksess
     */
    public function authenticate(): bool {
        $this->log("🔐 Starter autentiseringsprosess...");
        
        try {
            // Steg 1: Hent login-side
            $loginUrl = $this->baseUrl . '/admin';
            $this->log("1. Henter login-side: {$loginUrl}");
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $loginUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'NPK Data Extractor v1.0',
                CURLOPT_COOKIEJAR => $this->cookieFile,
                CURLOPT_COOKIEFILE => $this->cookieFile,
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: nb,no;q=0.9,en;q=0.8',
                    'Accept-Encoding: gzip, deflate',
                    'Cache-Control: no-cache, no-store, must-revalidate',
                    'Pragma: no-cache',
                    'Expires: 0'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode !== 200) {
                throw new Exception("Login-side returnerte HTTP {$httpCode}");
            }
            
            $this->log("   ✅ Login-side hentet (" . strlen($response) . " bytes)");
            
            // Steg 2: Ekstraher CSRF token (hvis til stede)
            $csrfToken = '';
            if (preg_match('/<input[^>]*name=["\']_token["\'][^>]*value=["\']([^"\']*)["\']/', $response, $matches)) {
                $csrfToken = $matches[1];
                $this->log("   🔑 CSRF token funnet");
            }
            
            // Steg 3: Send login-forespørsel
            $loginActionUrl = $this->baseUrl . '/admin/index/auth';
            $this->log("2. Sender login til: {$loginActionUrl}");
            
            $loginData = [
                'admin_username' => 'demo',
                'admin_password' => 'demo',
                'login' => 'login'
            ];
            
            if ($csrfToken) {
                $loginData['_token'] = $csrfToken;
                $loginData['csrf_token'] = $csrfToken;
            }
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $loginActionUrl,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($loginData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Referer: ' . $loginUrl,
                    'Origin: ' . $this->baseUrl,
                    'Cache-Control: no-cache'
                ]
            ]);
            
            $loginResponse = curl_exec($ch);
            $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Steg 4: Valider autentisering (HTTP 302 = suksess, men 200 kan også være OK)
            if ($loginHttpCode === 302 || $loginHttpCode === 200) {
                // For HTTP 200, sjekk om vi fikk suksess-indikator
                if ($loginHttpCode === 200) {
                    // Sjekk om response indikerer suksess
                    if (stripos($loginResponse, 'success') !== false || 
                        stripos($loginResponse, 'dashboard') !== false ||
                        strlen($loginResponse) < 100) { // Kort response kan indikere redirect
                        $this->authenticated = true;
                        $this->log("   ✅ Autentisering vellykket (HTTP 200 med suksess-indikator)");
                        return true;
                    }
                } else {
                    $this->authenticated = true;
                    $this->log("   ✅ Autentisering vellykket (HTTP 302 redirect)");
                    return true;
                }
                
                throw new Exception("Login respons ser ikke ut til å være vellykket");
            } else {
                throw new Exception("Login feilet: HTTP {$loginHttpCode}");
            }
            
        } catch (Exception $e) {
            $this->log("   ❌ Autentiseringsfeil: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hent komplett valpeliste fra NPK API
     */
    public function getValpeliste(): array {
        if (!$this->authenticated) {
            throw new Exception("Må autentisere før API-kall");
        }
        
        $apiUrl = $this->baseUrl . '/admin/product/getvalpeliste';
        $this->log("📡 Henter valpeliste fra: {$apiUrl}");
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'NPK Data Extractor v1.0',
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'X-Requested-With: XMLHttpRequest',
                'Referer: ' . $this->baseUrl . '/admin/',
                'Cache-Control: no-cache, no-store, must-revalidate',
                'Pragma: no-cache',
                'Expires: 0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Valpeliste API returnerte HTTP {$httpCode}");
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON parsing feil: " . json_last_error_msg());
        }
        
        if (!isset($data['dogs']) || !is_array($data['dogs'])) {
            throw new Exception("Ugyldig API respons struktur");
        }
        
        $kullCount = count($data['dogs']);
        $this->log("   ✅ Hentet {$kullCount} kull fra valpeliste API");
        
        return $data;
    }
    
    /**
     * Hent elite-status for individuell hund
     */
    public function getEliteStatus(string $registreringsnummer): array {
        if (!$this->authenticated) {
            throw new Exception("Må autentisere før API-kall");
        }
        
        $apiUrl = $this->baseUrl . '/admin/product/getdog?id=' . urlencode($registreringsnummer);
        $this->log("🐕 Henter elite-status for: {$registreringsnummer}");
        
        // Rate limiting - venting mellom kall
        usleep(500000); // 0.5 sekund pause
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'NPK Data Extractor v1.0',
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'X-Requested-With: XMLHttpRequest',
                'Referer: ' . $this->baseUrl . '/admin/',
                'Cache-Control: no-cache'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $this->log("   ⚠️ Elite API feil for {$registreringsnummer}: HTTP {$httpCode}");
            return ['eliteh' => null, 'avlsh' => null, 'error' => "HTTP {$httpCode}"];
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log("   ⚠️ JSON parsing feil for {$registreringsnummer}");
            return ['eliteh' => null, 'avlsh' => null, 'error' => 'JSON parsing feil'];
        }
        
        // Håndter ulike API responser
        $eliteStatus = [
            'eliteh' => null,
            'avlsh' => null,
            'error' => null
        ];
        
        if (isset($data['dogs']) && is_array($data['dogs'])) {
            if (isset($data['dogs']['success']) && $data['dogs']['success'] === 'false') {
                $this->log("   ⚠️ API returnerte success=false for {$registreringsnummer}");
                $eliteStatus['error'] = 'API success=false';
            } else {
                // Søk i dogs array
                foreach ($data['dogs'] as $dog) {
                    if (is_array($dog)) {
                        if (isset($dog['eliteh'])) {
                            $eliteStatus['eliteh'] = $dog['eliteh'];
                        }
                        if (isset($dog['avlsh'])) {
                            $eliteStatus['avlsh'] = $dog['avlsh'];
                        }
                        break;
                    }
                }
            }
        } else {
            // Direkte struktur
            if (isset($data['eliteh'])) {
                $eliteStatus['eliteh'] = $data['eliteh'];
            }
            if (isset($data['avlsh'])) {
                $eliteStatus['avlsh'] = $data['avlsh'];
            }
        }
        
        $elite = $eliteStatus['eliteh'] === '1' ? 'JA' : ($eliteStatus['eliteh'] === '0' ? 'NEI' : 'UKJENT');
        $avl = $eliteStatus['avlsh'] === '1' ? 'JA' : ($eliteStatus['avlsh'] === '0' ? 'NEI' : 'UKJENT');
        $this->log("   📊 Elite: {$elite}, Avl: {$avl}");
        
        return $eliteStatus;
    }
    
    /**
     * Rens HTML fra tekst og konverter entities
     */
    private function cleanHtml(string $html): string {
        // Konverter HTML entities
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Erstatt <br> tags med newlines
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        
        // Fjern alle HTML tags
        $text = strip_tags($text);
        
        // Rens opp whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n+/', "\n", $text);
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Bygg komplett datasett med alle kull og elite-status
     */
    public function buildCompleteDataset(): array {
        $this->log("🏗️ Bygger komplett datasett...");
        
        // Hent valpeliste
        $valpelisteData = $this->getValpeliste();
        $kull = [];
        $eliteStats = ['elite_analyse' => [], 'godkjenning_analyse' => []];
        
        foreach ($valpelisteData['dogs'] as $index => $rawKull) {
            $this->log("Prosesserer kull " . ($index + 1) . "/". count($valpelisteData['dogs']) . " (KUID: {$rawKull['KUID']})");
            
            // Hent elite-status for far og mor
            $farElite = null;
            $morElite = null;
            
            if (!empty($rawKull['father'])) {
                $farElite = $this->getEliteStatus($rawKull['father']);
            }
            
            if (!empty($rawKull['mother'])) {
                $morElite = $this->getEliteStatus($rawKull['mother']);
            }
            
            // Bygg strukturert kull-objekt
            $kullObj = [
                'kull_info' => [
                    'kull_id' => $rawKull['KUID'] ?? '',
                    'kull_indeks' => $rawKull['dwid'] ?? '',
                    'forventet_fodselsdato' => $rawKull['estDate'] ?? '',
                    'godkjent' => filter_var($rawKull['confirmed'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'arkivert' => filter_var($rawKull['arch'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'godkjent_avlskriterier' => !empty($rawKull['comments']) && strpos($rawKull['comments'], 'Godkjent') !== false
                ],
                'mor' => [
                    'navn' => $rawKull['MotherName'] ?? '',
                    'registreringsnummer' => $rawKull['mother'] ?? '',
                    'elitehund' => ($morElite['eliteh'] ?? $rawKull['eliteh']) === '1',
                    'avlshund' => ($morElite['avlsh'] ?? $rawKull['avlsh']) === '1',
                    'hdi' => [
                        'jaktindeks' => (int)($rawKull['jaktindM'] ?? 0),
                        'standindeks' => (int)($rawKull['standindM'] ?? 0),
                        'alt_hd' => (int)($rawKull['althdM'] ?? 0)
                    ],
                    'hd_status' => $this->mapHdStatus($rawKull['althdMother'] ?? null),
                    'premier' => $this->parsePremier($rawKull['MotherPrem'] ?? '')
                ],
                'far' => [
                    'navn' => $rawKull['FatherName'] ?? '',
                    'registreringsnummer' => $rawKull['father'] ?? '',
                    'elitehund' => ($farElite['eliteh'] ?? '0') === '1',
                    'avlshund' => ($farElite['avlsh'] ?? '0') === '1',
                    'hdi' => [
                        'jaktindeks' => (int)($rawKull['jaktindF'] ?? 0),
                        'standindeks' => (int)($rawKull['standindF'] ?? 0),
                        'alt_hd' => (int)($rawKull['althdF'] ?? 0)
                    ],
                    'hd_status' => $this->mapHdStatus($rawKull['althdFather'] ?? null),
                    'premier' => $this->parsePremier($rawKull['FatherPrem'] ?? '')
                ],
                'oppdretter' => [
                    'navn' => $rawKull['EierNavn'] ?? '',
                    'kennel' => $rawKull['kennel'] ?? '',
                    'adresse' => trim(($rawKull['adresse'] ?? '') . ' ' . ($rawKull['zip'] ?? '') . ' ' . ($rawKull['place'] ?? '')),
                    'telefon' => $rawKull['phone'] ?? '',
                    'epost' => $rawKull['email'] ?? '',
                    'web' => $rawKull['web'] ?? ''
                ],
                'annonse_tekst' => $this->cleanHtml($rawKull['note'] ?? '')
            ];
            
            $kull[] = $kullObj;
            
            // Samle statistikk
            $eliteStats['elite_analyse'] = $eliteStats['elite_analyse'] ?? [
                'elite_fedre' => 0, 'elite_modre' => 0, 'avl_fedre' => 0, 'avl_modre' => 0
            ];
            $eliteStats['godkjenning_analyse'] = $eliteStats['godkjenning_analyse'] ?? [
                'godkjent' => 0
            ];
            
            if ($kullObj['far']['elitehund']) $eliteStats['elite_analyse']['elite_fedre']++;
            if ($kullObj['mor']['elitehund']) $eliteStats['elite_analyse']['elite_modre']++;
            if ($kullObj['far']['avlshund']) $eliteStats['elite_analyse']['avl_fedre']++;
            if ($kullObj['mor']['avlshund']) $eliteStats['elite_analyse']['avl_modre']++;
            if ($kullObj['kull_info']['godkjent_avlskriterier']) $eliteStats['godkjenning_analyse']['godkjent']++;
        }
        
        $dataset = [
            'metadata' => [
                'ekstraksjonstidspunkt' => date('c'), // ISO-8601
                'kilde' => 'NPK API kombinert (valpeliste + individuelle oppslag)',
                'antall_kull' => count($kull),
                'api_versjon' => '1.0',
                'extractor_versjon' => '1.0'
            ],
            'kull' => $kull,
            'statistikk' => array_merge($eliteStats, [
                'logs' => $this->logs
            ])
        ];
        
        $this->log("✅ Komplett datasett bygget: " . count($kull) . " kull");
        return $dataset;
    }
    
    /**
     * Map HD status tall til bokstaver
     */
    private function mapHdStatus($hdValue): string {
        if ($hdValue === null) return 'UKJENT';
        
        $hd = (int)$hdValue;
        if ($hd <= 106) return 'A';
        if ($hd <= 112) return 'B'; 
        if ($hd <= 118) return 'C';
        if ($hd <= 125) return 'D';
        return 'E';
    }
    
    /**
     * Parse premie HTML til strukturert array
     */
    private function parsePremier(string $premieHtml): array {
        $premier = [];
        
        if (empty($premieHtml)) {
            return $premier;
        }
        
        // Søk etter utstillingspremier
        if (strpos($premieHtml, 'ribbon_red.gif') !== false) {
            $premier[] = 'Utstillingspremie';
        }
        
        // Søk etter jaktpremier
        if (strpos($premieHtml, 'ribbon_darkblue.gif') !== false) {
            $premier[] = 'Jaktpremie';
        }
        
        return $premier;
    }
    
    /**
     * Eksporter datasett til JSON fil
     */
    public function exportJson(?string $filename = null): string {
        if ($filename === null) {
            $filename = 'npk_valpeliste_' . date('Y-m-d_H-i-s') . '.json';
        }
        
        $dataset = $this->buildCompleteDataset();
        $jsonOutput = json_encode($dataset, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($jsonOutput === false) {
            throw new Exception("JSON encoding feil: " . json_last_error_msg());
        }
        
        file_put_contents($filename, $jsonOutput);
        $this->log("📁 JSON eksportert til: {$filename} (" . strlen($jsonOutput) . " bytes)");
        
        return $filename;
    }
    
    /**
     * Få alle logs
     */
    public function getLogs(): array {
        return $this->logs;
    }
}

// Standalone kjøring hvis ikke inkludert som klasse
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'] ?? '')) {
    echo "=== NPK Data Extractor v1.0 ===\n\n";
    
    try {
        $extractor = new NPKDataExtractor(true); // Debug mode
        
        // Autentiser
        if (!$extractor->authenticate()) {
            throw new Exception("Autentisering feilet");
        }
        
        // Eksporter data
        $filename = $extractor->exportJson();
        
        echo "\n✅ SUKSESS!\n";
        echo "📁 Fil: {$filename}\n";
        echo "📊 Se filen for komplett datasett med elite-status\n";
        
    } catch (Exception $e) {
        echo "\n❌ FEIL: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>
