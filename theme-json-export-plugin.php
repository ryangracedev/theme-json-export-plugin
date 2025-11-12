<?php
/**
 * Plugin Name: Theme JSON Auto Export
 * Description: Automatically exports theme.json to a shared location when theme is updated
 * Version: 1.0
 * Author: Ryan Grace
 */

// Export theme.json to a shared location accessible by both WP and Nuxt
add_action('after_switch_theme', 'export_theme_json');
add_action('wp_update_site_data', 'export_theme_json');

function export_theme_json() {
    $theme = wp_get_theme();
    $theme_json_path = $theme->get_stylesheet_directory() . '/theme.json';
    
    // Path to shared volume (you'll mount this in docker-compose.yml)
    $export_path = '/shared/theme.json';
    
    if (file_exists($theme_json_path)) {
        copy($theme_json_path, $export_path);
        error_log('Theme JSON exported to: ' . $export_path);
    }
}

// Also export when accessing the theme editor
add_action('load-site-editor.php', 'export_theme_json');