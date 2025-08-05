<?php
/**
 * Patch for å legge til godkjenning av kull iht. avlskriterier
 */

// Backup original file
copy('NPKDataExtractorLive.php', 'NPKDataExtractorLive.php.backup');

// Read the original file
$content = file_get_contents('NPKDataExtractorLive.php');

// Add the approved litters array after the sessionCookies declaration
$content = str_replace(
    'private array $sessionCookies = [];',
    'private array $sessionCookies = [];
    
    // Liste over godkjente kull KUID-er i henhold til avlskriterier
    private array $approvedLitters = [2340, 2341];',
    $content
);

// Add the isLitterApproved method after the __destruct method
$content = str_replace(
    '    }

    /**
     * Autentiser mot NPK API
     */',
    '    }

    /**
     * Sjekk om et kull er godkjent i henhold til avlskriterier
     */
    public function isLitterApproved(int $kuid): bool {
        return in_array($kuid, $this->approvedLitters);
    }

    /**
     * Autentiser mot NPK API
     */',
    $content
);

// Add the godkjent_avlskriterier field to kull_info
$content = str_replace(
    "                        'fodt' => \$litter['estDate'] ?? \$litter['BirthDate'] ?? ''
                    ],",
    "                        'fodt' => \$litter['estDate'] ?? \$litter['BirthDate'] ?? '',
                        'godkjent_avlskriterier' => \$this->isLitterApproved((int)(\$litter['KUID'] ?? 0))
                    ],",
    $content
);

// Write the modified content back
file_put_contents('NPKDataExtractorLive.php', $content);

echo "✅ NPKDataExtractorLive.php har blitt oppdatert med godkjenning av kull-funksjonalitet\n";
?>
