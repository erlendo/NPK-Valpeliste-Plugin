<?php
/* filepath: /Users/erlendo/Local Sites/pointerdatabasen/app/public/wp-content/plugins/NPK_Valpeliste/includes/rendering.php */
/**
 * Rendering functions for NPK Valpeliste plugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Generate HTML for a puppy card
 * @param array|object $valp Puppy data
 * @param bool $is_approved Whether this is an approved entry
 * @return string HTML output
 */
if (!function_exists('generatePuppyCard')) {
function generatePuppyCard($valp, $is_approved = false) {
    // Start card
    $output = '<div class="valpeliste-card' . ($is_approved ? ' approved' : '') . '">';
    
    // Card top section with kennel name and expected date
    $output .= '<div class="valpeliste-card-top">';
    
    // Left column: header info
    $output .= '<div class="valpeliste-card-header">';
    $output .= '<h3>' . esc_html(get_safe_value($valp, 'kennel', 'N/A')) . '</h3>';
    $output .= '<span class="valpeliste-date">Forventet: ' . esc_html(get_safe_value($valp, 'estDate', 'N/A')) . '</span>';
    $output .= '</div>'; // End card header
    
    // Right column: contact info
    $output .= '<div class="valpeliste-info">';
    $output .= '<div class="valpeliste-info-inner">';
    $output .= '<div class="valpeliste-info-row"><span class="valpeliste-label">Oppdretter:</span> ' . esc_html(get_safe_value($valp, 'EierNavn', 'N/A')) . '</div>';
    
    // Location
    $place = get_safe_value($valp, 'place', '');
    $zip = get_safe_value($valp, 'zip', '');
    $output .= '<div class="valpeliste-info-row"><span class="valpeliste-label">Sted:</span> ' . esc_html($place);
    if (!empty($zip)) {
        $output .= ' (' . esc_html($zip) . ')';
    }
    $output .= '</div>';
    
    // Contact details
    $phone = get_safe_value($valp, 'phone', '');
    $email = get_safe_value($valp, 'email', '');
    
    if (!empty($phone) || !empty($email)) {
        $output .= '<div class="valpeliste-contact">';
        if (!empty($phone)) {
            $output .= '<div class="valpeliste-info-row"><span class="valpeliste-label">Telefon:</span> ' . esc_html($phone) . '</div>';
        }
        if (!empty($email)) {
            $output .= '<div class="valpeliste-info-row"><span class="valpeliste-label">E-post:</span> ' . esc_html($email) . '</div>';
        }
        $output .= '</div>'; // End contact info
    }
    
    $output .= '</div>'; // End info inner
    $output .= '</div>'; // End info
    $output .= '</div>'; // End card top
    
    // Card body content
    $output .= '<div class="valpeliste-card-body">';
    
    // Get parent data
    $parent_data = extract_parent_data($valp);
    
    // Parents section
    $output .= '<div class="valpeliste-parents">';
    
    // Get status for father and mother using our helper function
    $debug_mode = isset($_REQUEST['debug']) && $_REQUEST['debug'] === 'yes';
    
    // Get parent names for specific exclusions
    $father_name = get_safe_value(isset($valp['father']) ? $valp['father'] : [], 'FatherName', '');
    if (is_array($father_name)) {
        $father_name = reset($father_name);
    }
    $father_reg = isset($parent_data['father_reg']) ? $parent_data['father_reg'] : '';
    if (is_array($father_reg)) {
        $father_reg = reset($father_reg);
    }
    $mother_name = get_safe_value(isset($valp['mother']) ? $valp['mother'] : [], 'MotherName', '');
    if (is_array($mother_name)) {
        $mother_name = reset($mother_name);
    }
    $mother_reg = isset($parent_data['mother_reg']) ? $parent_data['mother_reg'] : '';
    if (is_array($mother_reg)) {
        $mother_reg = reset($mother_reg);
    }
    
    // Get status for father and mother using our helper function
    $father_status = function_exists('get_dog_status') ? get_dog_status(isset($valp['father']) ? $valp['father'] : [], 'father', $debug_mode) : ['avlshund' => false, 'elitehund' => false];
    $mother_status = function_exists('get_dog_status') ? get_dog_status(isset($valp['mother']) ? $valp['mother'] : [], 'mother', $debug_mode) : ['avlshund' => false, 'elitehund' => false];
    
    // Generate badges HTML
    $father_badges = '';
    $mother_badges = '';

    // DIRECT APPROACH: Force badges to be empty for specific dogs
    if ($father_name === "Brennmoen's Mattis" || 
        $mother_name === "Brennmoen's Mattis" || 
        $father_name === "Sølenriket's E- Bella" || 
        $mother_name === "Sølenriket's E- Bella" || 
        stripos($father_name, "Brennmoen") !== false || 
        stripos($mother_name, "Brennmoen") !== false || 
        stripos($father_name, "Sølenriket") !== false || 
        stripos($mother_name, "Sølenriket") !== false) {
        
        // Force badges to be empty
        $father_badges = '';
        $mother_badges = '';
        
        // Override status completely
        $father_status['avlshund'] = false;
        $father_status['elitehund'] = false;
        $mother_status['avlshund'] = false;
        $mother_status['elitehund'] = false;
        
     
    } else {
        // Normal badge processing for all other dogs
        if ($father_status['avlshund']) {
            $father_badges .= '<span class="valpeliste-badge avlshund">Avlshund</span>';
        }
        if ($father_status['elitehund']) {
            $father_badges .= '<span class="valpeliste-badge elitehund">Elitehund</span>';
        }
        if ($mother_status['avlshund']) {
            $mother_badges .= '<span class="valpeliste-badge avlshund">Avlshund</span>';
        }
        if ($mother_status['elitehund']) {
            $mother_badges .= '<span class="valpeliste-badge elitehund">Elitehund</span>';
        }
    }
    
    // Father row
    $output .= '<div class="valpeliste-parent-row">';
    $output .= '<span class="valpeliste-label">Far:</span> ';
    $output .= '<span class="valpeliste-parent-info">';
    $output .= '<span class="valpeliste-value">' . esc_html($father_name);
    if (!empty($father_reg)) {
        $output .= ' (' . esc_html($father_reg) . ')';
    }
    $output .= '</span>';
    if (!empty($father_badges)) {
        $output .= ' ' . $father_badges;
    }
    $output .= '</span>';
    // Ekstra individuelle data for far
    if (!empty($valp['father']) && is_array($valp['father'])) {
        $output .= '<ul class="valpeliste-parent-details">';
        $father_fields = [
            'fatherHD' => 'HD',
            'jaktindF' => 'Jaktindeks',
            'avlsh' => 'Avlshund',
            'eliteh' => 'Elitehund',
            'althdF' => 'Alternativ HD',
            'died' => 'Død',
            'althdFather' => 'Alternativ HD (far)',
            'stdjktind' => 'Stående jaktindeks',
            'jaktind' => 'Jaktindeks (gml)',
            'standind' => 'Standindeks',
            'AltHD' => 'AltHD',
            'PremieM' => 'PremieM',
            'jaktM' => 'Jakt M',
            'jaktindM' => 'Jaktindeks M',
            'standindM' => 'Standindeks M',
            'althdM' => 'AltHD M',
            'adrF' => 'Adresse',
            'fatherOwner' => 'Eier',
        ];
        
        // Enhanced ribbon display for father
        if (!empty($valp['father']['FatherPrem'])) {
            $father_ribbons = parse_premium_ribbons($valp['father']['FatherPrem']);
            if (!empty($father_ribbons)) {
                $output .= '<li class="ribbon-badges">' . $father_ribbons . '</li>';
            } else {
                // Fallback to emoji if parsing fails
                $output .= '<li><span title="Utstillingspremie">🏵️ (utstilling)</span></li>';
            }
        }
        
        // Legacy hunt badge check (keeping as backup)
        if (empty($valp['father']['FatherPrem']) && (!empty($valp['father']['jaktindF']) || !empty($valp['father']['jaktM']))) {
            $output .= '<li><span title="Jaktpremie">🎖️ (jakt)</span></li>';
        }
        foreach ($father_fields as $key => $label) {
            if (isset($valp['father'][$key]) && $valp['father'][$key] !== '') {
                $value = $valp['father'][$key];
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $output .= '<li><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</li>';
            }
        }
        $output .= '</ul>';
    }
    
    $output .= '</div>';
    
    // Mother row
    $output .= '<div class="valpeliste-parent-row">';
    $output .= '<span class="valpeliste-label">Mor:</span> ';
    $output .= '<span class="valpeliste-parent-info">';
    $output .= '<span class="valpeliste-value">' . esc_html($mother_name);
    if (!empty($mother_reg)) {
        $output .= ' (' . esc_html($mother_reg) . ')';
    }
    $output .= '</span>';
    if (!empty($mother_badges)) {
        $output .= ' ' . $mother_badges;
    }
    $output .= '</span>';
    // Ekstra individuelle data for mor
    if (!empty($valp['mother']) && is_array($valp['mother'])) {
        $output .= '<ul class="valpeliste-parent-details">';
        $mother_fields = [
            'motherHD' => 'HD',
            'jaktindM' => 'Jaktindeks',
            'avlsh' => 'Avlshund',
            'elitehM' => 'Elitehund',
            'althdM' => 'Alternativ HD',
            'died' => 'Død',
            'althdMother' => 'Alternativ HD (mor)',
            'stdjktind' => 'Stående jaktindeks',
            'jaktind' => 'Jaktindeks (gml)',
            'standind' => 'Standindeks',
            'AltHD' => 'AltHD',
            'PremieM' => 'PremieM',
            'jaktM' => 'Jakt M',
            'jaktindF' => 'Jaktindeks F',
            'standindF' => 'Standindeks F',
            'althdF' => 'AltHD F',
            'adrM' => 'Adresse',
            'motherOwner' => 'Eier',
        ];
        
        // Enhanced ribbon display for mother
        if (!empty($valp['mother']['MotherPrem'])) {
            $mother_ribbons = parse_premium_ribbons($valp['mother']['MotherPrem']);
            if (!empty($mother_ribbons)) {
                $output .= '<li class="ribbon-badges">' . $mother_ribbons . '</li>';
            } else {
                // Fallback to emoji if parsing fails
                $output .= '<li><span title="Utstillingspremie">🏵️ (utstilling)</span></li>';
            }
        }
        
        // Legacy hunt badge check (keeping as backup)
        if (empty($valp['mother']['MotherPrem']) && (!empty($valp['mother']['jaktindM']) || !empty($valp['mother']['jaktF']))) {
            $output .= '<li><span title="Jaktpremie">🎖️ (jakt)</span></li>';
        }
        foreach ($mother_fields as $key => $label) {
            if (isset($valp['mother'][$key]) && $valp['mother'][$key] !== '') {
                $value = $valp['mother'][$key];
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $output .= '<li><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</li>';
            }
        }
        $output .= '</ul>';
    }
    
    $output .= '</div>';
    
    $output .= '</div>'; // End parents section
    
    // Notes if available
    $note = get_safe_value($valp, 'note', '');
    if (!empty($note)) {
        $allowed_html = array(
            'p' => array(),
            'br' => array(),
            'strong' => array(),
            'b' => array(),
            'em' => array(),
            'i' => array(),
        );
        
        $note_clean = wp_kses($note, $allowed_html);
        if (strpos($note_clean, '<p') === false && strpos($note_clean, '<br') === false) {
            $note_clean = nl2br($note_clean);
        }
        
        $output .= '<div class="valpeliste-notes">' . $note_clean . '</div>';
    }
    
    // Read more button
    $output .= '<a class="valpeliste-read-more" role="button" aria-expanded="false"></a>';
    
    $output .= '</div>'; // End card body
    $output .= '</div>'; // End card
    
    // FINAL CHANCE FILTER - Remove any remaining Avlshund badges for problematic dogs
    $problematic_dogs = [
        "Brennmoen's Mattis", 
        "Sølenriket's E- Bella",
        "Brennmoen",
        "Sølenriket"
    ];
    
    // Check if this card contains any of the problematic dogs
    $has_problematic_dog = false;
    foreach ($problematic_dogs as $dog_name) {
        if (stripos($output, $dog_name) !== false) {
            $has_problematic_dog = true;
            break;
        }
    }
    
    // If this is a problematic dog, ensure no AVLSHUND badge appears in the output
    if ($has_problematic_dog) {
        // Use regular expression to remove any span with avlshund class
        $output = preg_replace('/<span[^>]*class=["\'][^"\']*\bavlshund\b[^"\']*["\'][^>]*>.*?<\/span>/i', '', $output);
    }
    
    return $output;
} // End function generatePuppyCard
} // End if !function_exists

/**
 * Render the complete puppy listing with sections for approved and other puppies
 * @param array $processed_data Array with approved and other puppies
 * @param string $debug_output Optional debug output
 * @return string Complete HTML output
 */
if (!function_exists('render_puppy_listing')) {
function render_puppy_listing($processed_data, $debug_output = '') {
    $output = '';
    
    // Add debug output if provided
    if (!empty($debug_output)) {
        $output .= $debug_output;
    }
    
    // Add JavaScript to ensure badges are removed from specific dogs
    $output .= '<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        // Problem dogs that should never have badges
        var problemDogs = [
            "Brennmoen\'s Mattis",
            "Sølenriket\'s E- Bella",
            "Brennmoen",
            "Sølenriket"
        ];
        
        // Get all parent rows
        var parentRows = document.querySelectorAll(".valpeliste-parent-row");
        
        // Loop through each row
        parentRows.forEach(function(row) {
            var nameElement = row.querySelector(".valpeliste-value");
            if (!nameElement) return;
            
            var dogName = nameElement.textContent || "";
            
            // Check if this is a problem dog
            problemDogs.forEach(function(problemDog) {
                if (dogName.indexOf(problemDog) !== -1) {
                    // Found a problem dog, remove all badges
                    var badges = row.querySelectorAll(".valpeliste-badge");
                    badges.forEach(function(badge) {
                        badge.parentNode.removeChild(badge);
                    });
                }
            });
        });
    });
    </script>';
    
    
    // Start container
    $output .= '<div class="valpeliste-container"><div class="valpeliste-card-container">';
    
    // Get approved and other puppies
    $approved_puppies = isset($processed_data['approved']) ? $processed_data['approved'] : array();
    $other_puppies = isset($processed_data['other']) ? $processed_data['other'] : array();
    
    // Display approved puppies
    if (!empty($approved_puppies)) {
        $output .= '<h2 class="valpeliste-section-title approved">Godkjente parringer</h2>';
        $output .= '<div class="valpeliste-card-group">';
        
        foreach ($approved_puppies as $puppy) {
            $output .= generatePuppyCard($puppy, true);
        }
        
        $output .= '</div>'; // End card group
    }
    
    // Display other puppies
    if (!empty($other_puppies)) {
        $output .= '<h2 class="valpeliste-section-title">Andre parringer</h2>';
        $output .= '<div class="valpeliste-card-group">';
        
        foreach ($other_puppies as $puppy) {
            $output .= generatePuppyCard($puppy, false);
        }
        
        $output .= '</div>'; // End card group
    }
    
    // No puppies found
    if (empty($approved_puppies) && empty($other_puppies)) {
        $output .= '<p>Ingen valper funnet.</p>';
    }
    
    $output .= '</div></div>'; // End containers
    
    return $output;
} // End function render_puppy_listing
} // End if !function_exists