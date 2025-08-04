<?php
/**
 * Admin Settings for NPK Valpeliste Plugin
 */

// Sikkerhetskontroll
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the admin page for the plugin
 */
function npk_valpeliste_add_admin_menu() {
    add_options_page(
        'NPK Valpeliste Innstillinger',
        'NPK Valpeliste',
        'manage_options',
        'npk_valpeliste_settings',
        'npk_valpeliste_settings_page'
    );
}
add_action('admin_menu', 'npk_valpeliste_add_admin_menu');

/**
 * Handle admin actions (API testing only - no cache management needed)
 */
function npk_valpeliste_handle_admin_actions() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_POST['npk_test_api']) && check_admin_referer('npk_test_api_nonce')) {
        // Test API connection
        if (function_exists('fetch_puppy_data')) {
            $test_result = fetch_puppy_data(false, true); // Test with debug
            
            if (is_array($test_result) && isset($test_result['data']) && !empty($test_result['data'])) {
                add_action('admin_notices', function() use ($test_result) {
                    echo '<div class="notice notice-success is-dismissible">';
                    echo '<p><strong>✅ API-test vellykket!</strong> Hentet ' . count($test_result['data']) . ' poster fra datahound.no i real-time.</p>';
                    echo '</div>';
                });
            } else {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error is-dismissible">';
                    echo '<p><strong>❌ API-test mislyktes!</strong> Kunne ikke hente data fra datahound.no. Sjekk tilkobling og autentisering.</p>';
                    echo '</div>';
                });
            }
        }
    }
}
add_action('admin_init', 'npk_valpeliste_handle_admin_actions');

/**
 * Register settings
 */
function npk_valpeliste_register_settings() {
    register_setting(
        'npk_valpeliste_options',
        'npk_valpeliste_criteria',
        array(
            'type' => 'array',
            'sanitize_callback' => 'npk_valpeliste_sanitize_criteria',
            'default' => array(
                'strict_mode' => true,       // Default to strict mode (only use explicit flag)
                'use_hd_status' => false,    // Disable HD status check by default
                'use_name_check' => false,   // Disable name check by default 
                'use_field_checks' => false, // Disable field checks by default
                'use_jakt_scores' => true,   // Keep jakt scores for elite status
                'hd_threshold' => 100,
                'jakt_threshold' => 115
            )
        )
    );
    
    // Legg til en seksjon for avlshund kriterier
    add_settings_section(
        'npk_valpeliste_avlshund_section',
        'Kriterier for Avlshund og Elitehund Status',
        'npk_valpeliste_avlshund_section_callback',
        'npk_valpeliste_settings'
    );
    
    // Strict Mode innstilling
    add_settings_field(
        'strict_mode',
        'Streng modus',
        'npk_valpeliste_render_checkbox',
        'npk_valpeliste_settings',
        'npk_valpeliste_avlshund_section',
        array(
            'option_name' => 'npk_valpeliste_criteria',
            'field_name' => 'strict_mode',
            'label' => 'Bruk kun avlsh="1" for å bestemme Avlshund status',
            'description' => 'Når aktivert, vil kun hunder med avlsh="1" få Avlshund badge.'
        )
    );
    
    // HD Status innstilling
    add_settings_field(
        'use_hd_status',
        'Bruk HD-status',
        'npk_valpeliste_render_checkbox',
        'npk_valpeliste_settings',
        'npk_valpeliste_avlshund_section',
        array(
            'option_name' => 'npk_valpeliste_criteria',
            'field_name' => 'use_hd_status',
            'label' => 'Bruk HD-status for å bestemme Avlshund status',
            'description' => 'Når aktivert, vil hunder med HD-score over terskelen få Avlshund badge.'
        )
    );
    
    // Name Check innstilling
    add_settings_field(
        'use_name_check',
        'Sjekk navn',
        'npk_valpeliste_render_checkbox',
        'npk_valpeliste_settings',
        'npk_valpeliste_avlshund_section',
        array(
            'option_name' => 'npk_valpeliste_criteria',
            'field_name' => 'use_name_check',
            'label' => 'Sjekk om hundens navn inneholder "avlsh" eller "avlshund"',
            'description' => 'Når aktivert, vil hunder med "avlsh" eller "avlshund" i navnet få Avlshund badge.'
        )
    );
    
    // Field Checks innstilling
    add_settings_field(
        'use_field_checks',
        'Sjekk andre felter',
        'npk_valpeliste_render_checkbox',
        'npk_valpeliste_settings',
        'npk_valpeliste_avlshund_section',
        array(
            'option_name' => 'npk_valpeliste_criteria',
            'field_name' => 'use_field_checks',
            'label' => 'Sjekk andre felter for "avlsh" eller "avlshund" indikatorer',
            'description' => 'Når aktivert, vil hunder med "avlsh" eller "avlshund" i andre felter få Avlshund badge.'
        )
    );
    
    // Jakt Scores innstilling
    add_settings_field(
        'use_jakt_scores',
        'Bruk jakt-scores',
        'npk_valpeliste_render_checkbox',
        'npk_valpeliste_settings',
        'npk_valpeliste_avlshund_section',
        array(
            'option_name' => 'npk_valpeliste_criteria',
            'field_name' => 'use_jakt_scores',
            'label' => 'Bruk jakt-scores for å bestemme Elitehund status',
            'description' => 'Når aktivert, vil hunder med jakt-score over terskelen få Elitehund badge.'
        )
    );
    
    // HD Threshold innstilling
    add_settings_field(
        'hd_threshold',
        'HD terskel',
        'npk_valpeliste_render_number',
        'npk_valpeliste_settings',
        'npk_valpeliste_avlshund_section',
        array(
            'option_name' => 'npk_valpeliste_criteria',
            'field_name' => 'hd_threshold',
            'label' => 'HD terskelverdi for Avlshund status',
            'min' => 0,
            'max' => 200,
            'step' => 1,
            'description' => 'HD-score over denne verdien vil gi Avlshund badge (standard: 100).'
        )
    );
    
    // Jakt Threshold innstilling
    add_settings_field(
        'jakt_threshold',
        'Jakt terskel',
        'npk_valpeliste_render_number',
        'npk_valpeliste_settings',
        'npk_valpeliste_avlshund_section',
        array(
            'option_name' => 'npk_valpeliste_criteria',
            'field_name' => 'jakt_threshold',
            'label' => 'Jakt terskelverdi for Elitehund status',
            'min' => 0,
            'max' => 200,
            'step' => 1,
            'description' => 'Jakt-score over denne verdien vil gi Elitehund badge (standard: 115).'
        )
    );
}
add_action('admin_init', 'npk_valpeliste_register_settings');

/**
 * Sanitize the criteria settings
 */
function npk_valpeliste_sanitize_criteria($input) {
    $sanitized = array();
    
    // Checkboxes
    $sanitized['strict_mode'] = isset($input['strict_mode']) ? true : false;
    $sanitized['use_hd_status'] = isset($input['use_hd_status']) ? true : false;
    $sanitized['use_name_check'] = isset($input['use_name_check']) ? true : false;
    $sanitized['use_field_checks'] = isset($input['use_field_checks']) ? true : false;
    $sanitized['use_jakt_scores'] = isset($input['use_jakt_scores']) ? true : false;
    
    // Numeric values
    $sanitized['hd_threshold'] = isset($input['hd_threshold']) ? absint($input['hd_threshold']) : 100;
    $sanitized['jakt_threshold'] = isset($input['jakt_threshold']) ? absint($input['jakt_threshold']) : 115;
    
    // Override dogs - lagre eksisterende overstyringer
    $current_settings = get_option('npk_valpeliste_criteria', []);
    $sanitized['override_dogs'] = isset($current_settings['override_dogs']) ? $current_settings['override_dogs'] : [];
    
    // Legg til nye overstyringer hvis de er sendt inn
    if (isset($input['override_reg_number']) && !empty($input['override_reg_number'])) {
        $reg_number = sanitize_text_field($input['override_reg_number']);
        $avlshund_status = isset($input['override_avlshund']) ? true : false;
        $elitehund_status = isset($input['override_elitehund']) ? true : false;
        $total_override = isset($input['override_total']) ? true : false;
        
        // Legg til eller oppdater overstyringen
        $sanitized['override_dogs'][$reg_number] = [
            'avlshund' => $avlshund_status,
            'elitehund' => $elitehund_status,
            'total_override' => $total_override,
        ];
    }
    
    // Fjern overstyringer som er markert for sletting
    if (isset($input['remove_override']) && !empty($input['remove_override'])) {
        $remove_reg = sanitize_text_field($input['remove_override']);
        if (isset($sanitized['override_dogs'][$remove_reg])) {
            unset($sanitized['override_dogs'][$remove_reg]);
        }
    }
    
    return $sanitized;
}

/**
 * Render the section intro text
 */
function npk_valpeliste_avlshund_section_callback() {
    echo '<p>Konfigurer hvilke kriterier som skal brukes for å avgjøre om en hund får "Avlshund" eller "Elitehund" badge. Aktiver "Streng modus" for å kun bruke det eksplisitte avlsh="1" flagget.</p>';
}

/**
 * Render a checkbox field
 */
function npk_valpeliste_render_checkbox($args) {
    $options = get_option($args['option_name']);
    $field_name = $args['field_name'];
    $checked = isset($options[$field_name]) ? $options[$field_name] : false;
    ?>
    <input type="checkbox" 
           id="<?php echo esc_attr($field_name); ?>" 
           name="<?php echo esc_attr($args['option_name']); ?>[<?php echo esc_attr($field_name); ?>]" 
           <?php checked($checked, true); ?>>
    <label for="<?php echo esc_attr($field_name); ?>"><?php echo esc_html($args['label']); ?></label>
    <?php if (!empty($args['description'])): ?>
        <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php endif; ?>
    <?php
}

/**
 * Render a number field
 */
function npk_valpeliste_render_number($args) {
    $options = get_option($args['option_name']);
    $field_name = $args['field_name'];
    $value = isset($options[$field_name]) ? $options[$field_name] : '';
    $min = isset($args['min']) ? $args['min'] : '';
    $max = isset($args['max']) ? $args['max'] : '';
    $step = isset($args['step']) ? $args['step'] : '1';
    ?>
    <input type="number" 
           id="<?php echo esc_attr($field_name); ?>" 
           name="<?php echo esc_attr($args['option_name']); ?>[<?php echo esc_attr($field_name); ?>]"
           value="<?php echo esc_attr($value); ?>"
           <?php if ($min !== '') echo 'min="' . esc_attr($min) . '"'; ?>
           <?php if ($max !== '') echo 'max="' . esc_attr($max) . '"'; ?>
           step="<?php echo esc_attr($step); ?>">
    <label for="<?php echo esc_attr($field_name); ?>"><?php echo esc_html($args['label']); ?></label>
    <?php if (!empty($args['description'])): ?>
        <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php endif; ?>
    <?php
}

/**
 * Render the settings page
 */
function npk_valpeliste_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <!-- Real-time Data Management Section -->
        <div class="npk-realtime-management" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
            <h2>⚡ Real-time Data og API Kontroll</h2>
            <p>NPK Valpeliste henter nå alltid ferske data direkte fra datahound.no - ingen caching!</p>
            
            <div style="display: flex; gap: 10px; margin: 15px 0;">
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('npk_test_api_nonce'); ?>
                    <input type="submit" name="npk_test_api" class="button button-primary" value="🧪 Test API-tilkobling" />
                </form>
            </div>
            
            <div style="background: #e8f5e8; padding: 15px; border-radius: 3px; margin-top: 15px; border-left: 4px solid #4caf50;">
                <h4>✅ Real-time fordeler:</h4>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li><strong>Alltid oppdatert:</strong> Data hentes direkte fra datahound.no ved hver visning</li>
                    <li><strong>Ingen cache-problemer:</strong> Nye valper vises øyeblikkelig</li>
                    <li><strong>Automatisk synkronisering:</strong> Endringer på datahound.no reflekteres umiddelbart</li>
                </ul>
                
                <h4>🔗 Debug-parametere:</h4>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li><strong>Debug mode:</strong> Legg til <code>?npk_debug=1</code> i URL-en for debug-visning</li>
                    <li><strong>Shortcode debug:</strong> <code>[valpeliste debug="yes"]</code> - Viser debug-informasjon</li>
                </ul>
                
                <h4>⚠️ Ytelse-tips:</h4>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li>Real-time data kan gi litt lengre lastetider enn cached data</li>
                    <li>API-en er optimalisert for rask respons fra datahound.no</li>
                    <li>Debug-mode viser detaljerte API-responsinformasjon</li>
                </ul>
            </div>
        </div>
        
        <!-- Settings Form -->
        <form action="options.php" method="post">
            <?php
            settings_fields('npk_valpeliste_options');
            do_settings_sections('npk_valpeliste_settings');
            submit_button('Lagre innstillinger');
            ?>
        </form>
        
        <div class="npk-valpeliste-help-section" style="margin-top: 30px; background: #f8f8f8; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h2>Hjelp og informasjon</h2>
            <p><strong>Hvordan fungerer Avlshund og Elitehund badges:</strong></p>
            <ul style="list-style-type: disc; margin-left: 20px;">
                <li>Som standard blir en hund merket som "Avlshund" basert på flere kriterier, ikke bare avlsh="1" flagget.</li>
                <li>Denne siden lar deg konfigurere hvilke kriterier som skal brukes i vurderingen.</li>
                <li>Hvis du kun ønsker å bruke det eksplisitte avlsh="1" flagget, aktiver "Streng modus".</li>
                <li>Du kan også skreddersy hvilke andre kriterier som skal brukes (HD-status, navn, andre felter, jakt-scores).</li>
            </ul>
            <p>For å teste hvordan disse innstillingene påvirker visningen, legg til parameteren <code>debug=yes</code> i shortcoden, f.eks. <code>[valpeliste debug=yes]</code>.</p>
        </div>
    </div>
    <?php
}
