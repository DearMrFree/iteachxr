<?php
/**
 * iTeachXR Accessibility Helper Functions
 * Contains functions to enhance accessibility for users with disabilities
 */

/**
 * Get the accessibility settings for the current user
 * In a full implementation, these would be retrieved from the database
 * 
 * @return array User's accessibility settings
 */
function get_user_accessibility_settings() {
    // In a real implementation, retrieve from database based on user ID
    // For now, return default settings
    return [
        'high_contrast' => false,
        'large_text' => false,
        'screen_reader_optimized' => false,
        'reduce_motion' => false,
        'keyboard_navigation' => true,
        'text_to_speech' => false,
        'dyslexia_friendly' => false,
        'link_underline' => false
    ];
}

/**
 * Generate the CSS classes based on user's accessibility settings
 * 
 * @param array $settings User's accessibility settings
 * @return string CSS classes to apply
 */
function get_accessibility_classes($settings = null) {
    if ($settings === null) {
        $settings = get_user_accessibility_settings();
    }
    
    $classes = [];
    
    if (!empty($settings['high_contrast'])) {
        $classes[] = 'high-contrast';
    }
    
    if (!empty($settings['large_text'])) {
        $classes[] = 'large-text';
    }
    
    if (!empty($settings['screen_reader_optimized'])) {
        $classes[] = 'screen-reader-optimized';
    }
    
    if (!empty($settings['reduce_motion'])) {
        $classes[] = 'reduce-motion';
    }
    
    if (!empty($settings['dyslexia_friendly'])) {
        $classes[] = 'dyslexia-friendly';
    }
    
    if (!empty($settings['link_underline'])) {
        $classes[] = 'underline-links';
    }
    
    return implode(' ', $classes);
}

/**
 * Output accessibility CSS styles based on settings
 * 
 * @param array $settings User's accessibility settings
 * @return string CSS styles for accessibility
 */
function get_accessibility_css($settings = null) {
    if ($settings === null) {
        $settings = get_user_accessibility_settings();
    }
    
    ob_start();
    ?>
<style id="accessibility-styles">
    /* High Contrast Mode */
    body.high-contrast {
        --primary-color: #0000FF;
        --primary-light: #1E90FF;
        --primary-dark: #00008B;
        --secondary-color: #FFD700;
        --secondary-light: #FFFF00;
        --secondary-dark: #B8860B;
        --accent-color: #FF00FF;
        --text-color: #000000;
        --light-text: #333333;
        --white: #FFFFFF;
        --light-bg: #FFFFFF;
        --border-color: #000000;
        color: #000000 !important;
        background-color: #FFFFFF !important;
    }
    
    body.high-contrast .navbar,
    body.high-contrast .sidebar {
        background: #000080 !important;
        color: #FFFFFF !important;
    }
    
    body.high-contrast .navbar .nav-link,
    body.high-contrast .sidebar-nav-link,
    body.high-contrast .sidebar a {
        color: #FFFFFF !important;
        background-color: transparent !important;
        text-shadow: none !important;
    }
    
    body.high-contrast .btn-primary {
        background: #0000FF !important;
        color: #FFFFFF !important;
        border: 2px solid #000000 !important;
    }
    
    body.high-contrast .btn-outline-primary {
        color: #0000FF !important;
        border: 2px solid #0000FF !important;
    }
    
    body.high-contrast .sidebar-nav-link:hover,
    body.high-contrast .sidebar-nav-link.active {
        background-color: #4169E1 !important;
        border-left: 3px solid #FFFF00 !important;
    }
    
    body.high-contrast .card {
        border: 1px solid #000000 !important;
    }
    
    body.high-contrast .text-muted {
        color: #333333 !important;
    }
    
    /* Large Text Mode */
    body.large-text {
        font-size: 1.2rem !important;
    }
    
    body.large-text h1 {
        font-size: 2.5rem !important;
    }
    
    body.large-text h2 {
        font-size: 2.2rem !important;
    }
    
    body.large-text h3 {
        font-size: 2rem !important;
    }
    
    body.large-text h4 {
        font-size: 1.8rem !important;
    }
    
    body.large-text h5 {
        font-size: 1.6rem !important;
    }
    
    body.large-text h6 {
        font-size: 1.4rem !important;
    }
    
    body.large-text .btn {
        font-size: 1.2rem !important;
        padding: 0.6rem 1.2rem !important;
    }
    
    body.large-text .nav-link,
    body.large-text .sidebar-nav-link {
        font-size: 1.2rem !important;
        padding: 0.8rem 1.2rem !important;
    }
    
    /* Reduce Motion */
    body.reduce-motion *,
    body.reduce-motion *::before,
    body.reduce-motion *::after {
        animation-duration: 0.001s !important;
        transition-duration: 0.001s !important;
    }
    
    /* Dyslexia Friendly */
    body.dyslexia-friendly {
        font-family: 'OpenDyslexic', 'Comic Sans MS', 'Arial', sans-serif !important;
        letter-spacing: 0.25px !important;
        word-spacing: 1px !important;
        line-height: 1.6 !important;
    }
    
    /* Underline Links */
    body.underline-links a {
        text-decoration: underline !important;
    }
    
    /* Screen Reader Only Elements */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* Focus styles for keyboard navigation */
    a:focus, button:focus, input:focus, select:focus, textarea:focus {
        outline: 3px solid #FFD700 !important;
        outline-offset: 2px !important;
    }

    /* Skip to content link - visible on focus */
    .skip-to-content {
        position: absolute;
        top: -50px;
        left: 0;
        background: var(--primary-color);
        color: white;
        padding: 10px;
        z-index: 10000;
        transition: top 0.3s ease;
    }
    
    .skip-to-content:focus {
        top: 0;
    }
</style>
    <?php
    return ob_get_clean();
}

/**
 * Generate the HTML for the accessibility control panel
 * 
 * @param array $settings Current accessibility settings
 * @return string HTML for the accessibility controls
 */
function get_accessibility_controls($settings = null) {
    if ($settings === null) {
        $settings = get_user_accessibility_settings();
    }
    
    ob_start();
    ?>
<div id="accessibility-panel" class="visually-hidden">
    <div class="accessibility-panel-inner">
        <h3>Accessibility Settings</h3>
        <form id="accessibility-form">
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="highContrast" name="high_contrast" <?php echo !empty($settings['high_contrast']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="highContrast">High Contrast Mode</label>
                <small class="form-text">Enhances color contrast for better visibility</small>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="largeText" name="large_text" <?php echo !empty($settings['large_text']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="largeText">Larger Text</label>
                <small class="form-text">Increases text size for better readability</small>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="reduceMotion" name="reduce_motion" <?php echo !empty($settings['reduce_motion']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="reduceMotion">Reduce Motion</label>
                <small class="form-text">Minimizes animations and movements</small>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="dyslexiaFriendly" name="dyslexia_friendly" <?php echo !empty($settings['dyslexia_friendly']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="dyslexiaFriendly">Dyslexia Friendly Font</label>
                <small class="form-text">Uses a font designed to help readers with dyslexia</small>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="linkUnderline" name="link_underline" <?php echo !empty($settings['link_underline']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="linkUnderline">Underline Links</label>
                <small class="form-text">Makes links more identifiable</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Settings</button>
            <button type="button" class="btn btn-secondary" id="closeAccessibilityPanel">Close</button>
        </form>
    </div>
</div>
    <?php
    return ob_get_clean();
}

/**
 * Generate the HTML for the accessibility toggle button
 * 
 * @return string HTML for the accessibility toggle
 */
function get_accessibility_toggle() {
    ob_start();
    ?>
<div id="accessibility-toggle">
    <button aria-label="Open Accessibility Menu" title="Accessibility Options" class="btn accessibility-btn">
        <i class="fas fa-universal-access"></i>
    </button>
</div>
    <?php
    return ob_get_clean();
}

/**
 * Add JavaScript to handle accessibility features
 * 
 * @return string JavaScript for accessibility functionality
 */
function get_accessibility_javascript() {
    ob_start();
    ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Accessibility panel toggle
        const accessibilityToggle = document.getElementById('accessibility-toggle');
        const accessibilityPanel = document.getElementById('accessibility-panel');
        const closeAccessibilityButton = document.getElementById('closeAccessibilityPanel');
        
        if (accessibilityToggle && accessibilityPanel) {
            // Toggle panel visibility
            accessibilityToggle.addEventListener('click', function() {
                accessibilityPanel.classList.toggle('visually-hidden');
            });
            
            // Close panel button
            if (closeAccessibilityButton) {
                closeAccessibilityButton.addEventListener('click', function() {
                    accessibilityPanel.classList.add('visually-hidden');
                });
            }
            
            // Handle form submission
            const accessibilityForm = document.getElementById('accessibility-form');
            if (accessibilityForm) {
                accessibilityForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Apply settings immediately
                    applyAccessibilitySettings();
                    
                    // Save settings (in a real app, this would save to database)
                    // For now, store in localStorage
                    saveAccessibilitySettings();
                    
                    // Close the panel
                    accessibilityPanel.classList.add('visually-hidden');
                });
            }
        }
        
        // Apply any saved settings on page load
        loadAccessibilitySettings();
        
        // Keyboard navigation support
        document.addEventListener('keydown', function(e) {
            // Skip to content on pressing Tab at page start
            if (e.key === 'Tab' && !e.altKey && !e.ctrlKey && !e.shiftKey) {
                const skipLink = document.querySelector('.skip-to-content');
                if (skipLink && document.activeElement === document.body) {
                    e.preventDefault();
                    skipLink.focus();
                }
            }
        });
        
        // Helper functions
        function applyAccessibilitySettings() {
            const body = document.body;
            
            // Clear existing classes
            body.classList.remove('high-contrast', 'large-text', 'reduce-motion', 'dyslexia-friendly', 'underline-links');
            
            // High Contrast
            if (document.getElementById('highContrast').checked) {
                body.classList.add('high-contrast');
            }
            
            // Large Text
            if (document.getElementById('largeText').checked) {
                body.classList.add('large-text');
            }
            
            // Reduce Motion
            if (document.getElementById('reduceMotion').checked) {
                body.classList.add('reduce-motion');
            }
            
            // Dyslexia Friendly
            if (document.getElementById('dyslexiaFriendly').checked) {
                body.classList.add('dyslexia-friendly');
            }
            
            // Underline Links
            if (document.getElementById('linkUnderline').checked) {
                body.classList.add('underline-links');
            }
        }
        
        function saveAccessibilitySettings() {
            const settings = {
                high_contrast: document.getElementById('highContrast').checked,
                large_text: document.getElementById('largeText').checked,
                reduce_motion: document.getElementById('reduceMotion').checked,
                dyslexia_friendly: document.getElementById('dyslexiaFriendly').checked,
                link_underline: document.getElementById('linkUnderline').checked
            };
            
            localStorage.setItem('accessibility_settings', JSON.stringify(settings));
        }
        
        function loadAccessibilitySettings() {
            const savedSettings = localStorage.getItem('accessibility_settings');
            
            if (savedSettings) {
                const settings = JSON.parse(savedSettings);
                
                // Apply settings to form controls
                document.getElementById('highContrast').checked = settings.high_contrast;
                document.getElementById('largeText').checked = settings.large_text;
                document.getElementById('reduceMotion').checked = settings.reduce_motion;
                document.getElementById('dyslexiaFriendly').checked = settings.dyslexia_friendly;
                document.getElementById('linkUnderline').checked = settings.link_underline;
                
                // Apply settings visually
                applyAccessibilitySettings();
            }
        }
    });
</script>
    <?php
    return ob_get_clean();
}

/**
 * Add ARIA attributes to improve screen reader support
 * 
 * @param string $html HTML content
 * @return string HTML with added ARIA attributes
 */
function add_aria_attributes($html) {
    // This is a simplified example
    // In a real implementation, use DOM manipulation to properly add ARIA attributes
    
    // Add ARIA landmarks
    $html = str_replace('<nav', '<nav role="navigation" aria-label="Main navigation"', $html);
    $html = str_replace('<div class="sidebar"', '<div class="sidebar" role="navigation" aria-label="Sidebar navigation"', $html);
    $html = str_replace('<div class="main-content"', '<div class="main-content" role="main"', $html);
    $html = str_replace('<form', '<form role="form"', $html);
    $html = str_replace('<div class="card-header', '<div role="heading" aria-level="3" class="card-header', $html);
    
    return $html;
}

/**
 * Add a skip to content link for keyboard navigation
 * 
 * @return string HTML for skip to content link
 */
function get_skip_to_content_link() {
    return '<a href="#main-content" class="skip-to-content">Skip to content</a>';
}

/**
 * Get the CSS for the accessibility toggle and panel
 * 
 * @return string CSS styles for accessibility UI
 */
function get_accessibility_ui_css() {
    ob_start();
    ?>
<style id="accessibility-ui-styles">
    #accessibility-toggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }
    
    .accessibility-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 24px;
        transition: all 0.3s ease;
    }
    
    .accessibility-btn:hover {
        background-color: var(--primary-light);
        transform: scale(1.05);
    }
    
    #accessibility-panel {
        position: fixed;
        bottom: 80px;
        right: 20px;
        width: 300px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        z-index: 1000;
        padding: 0;
        overflow: hidden;
    }
    
    #accessibility-panel.visually-hidden {
        display: none;
    }
    
    .accessibility-panel-inner {
        padding: 20px;
    }
    
    #accessibility-panel h3 {
        margin-top: 0;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
        color: var(--primary-color);
    }
    
    #accessibility-panel .form-text {
        display: block;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        color: var(--light-text);
    }
</style>
    <?php
    return ob_get_clean();
}