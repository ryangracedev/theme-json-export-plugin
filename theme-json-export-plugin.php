<?php
/**
 * Plugin Name: Theme JSON Auto Export
 * Description: Automatically exports theme.json to /shared/ directory for Nuxt sync
 * Version: 1.1
 * Author: Ryan Grace
 */

// Don't allow direct access
if (!defined('ABSPATH')) {
    exit;
}

class ThemeJSONAutoExport {
    private $shared_path = '/shared/theme.json';
    
    public function __construct() {
        // Hook into various actions that might indicate theme changes
        add_action('after_switch_theme', [$this, 'export_theme_json']);
        add_action('admin_init', [$this, 'maybe_export_on_admin']);
        
        // Add admin notice to show status
        add_action('admin_notices', [$this, 'show_export_status']);
        
        // Export on plugin activation
        register_activation_hook(__FILE__, [$this, 'export_on_activation']);
        
        // Add manual export button in admin
        add_action('admin_bar_menu', [$this, 'add_toolbar_export_button'], 100);
        add_action('admin_post_export_theme_json', [$this, 'handle_manual_export']);
    }
    
    public function export_on_activation() {
        $this->export_theme_json();
    }
    
    public function maybe_export_on_admin() {
        // Only check occasionally to avoid performance issues
        $last_check = get_transient('theme_json_last_check');
        if ($last_check === false) {
            $this->export_theme_json();
            set_transient('theme_json_last_check', time(), 300); // Check every 5 minutes
        }
    }
    
    public function export_theme_json() {
        $theme = wp_get_theme();
        $theme_json_path = $theme->get_stylesheet_directory() . '/theme.json';
        
        // Log attempt
        error_log('[Theme JSON Export] Attempting export...');
        error_log('[Theme JSON Export] Theme directory: ' . $theme->get_stylesheet_directory());
        error_log('[Theme JSON Export] Looking for theme.json at: ' . $theme_json_path);
        
        if (!file_exists($theme_json_path)) {
            error_log('[Theme JSON Export] ERROR: theme.json not found!');
            update_option('theme_json_export_status', 'error_not_found');
            return false;
        }
        
        // Check if /shared directory exists and is writable
        $shared_dir = dirname($this->shared_path);
        if (!is_dir($shared_dir)) {
            error_log('[Theme JSON Export] ERROR: /shared directory does not exist!');
            update_option('theme_json_export_status', 'error_no_shared_dir');
            return false;
        }
        
        if (!is_writable($shared_dir)) {
            error_log('[Theme JSON Export] ERROR: /shared directory is not writable!');
            update_option('theme_json_export_status', 'error_not_writable');
            return false;
        }
        
        // Copy the file
        $result = copy($theme_json_path, $this->shared_path);
        
        if ($result) {
            error_log('[Theme JSON Export] SUCCESS! Exported to: ' . $this->shared_path);
            update_option('theme_json_export_status', 'success');
            update_option('theme_json_export_time', current_time('mysql'));
            return true;
        } else {
            error_log('[Theme JSON Export] ERROR: Failed to copy file!');
            update_option('theme_json_export_status', 'error_copy_failed');
            return false;
        }
    }
    
    public function show_export_status() {
        $status = get_option('theme_json_export_status');
        $time = get_option('theme_json_export_time');
        
        if ($status === 'success' && $time) {
            echo '<div class="notice notice-success"><p>';
            echo '<strong>Theme JSON Export:</strong> Successfully exported at ' . $time;
            echo '</p></div>';
        } elseif ($status === 'error_not_found') {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>Theme JSON Export ERROR:</strong> theme.json file not found in your active theme directory.';
            echo '</p></div>';
        } elseif ($status === 'error_no_shared_dir') {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>Theme JSON Export ERROR:</strong> /shared directory not found. Check your docker-compose.yml volumes.';
            echo '</p></div>';
        } elseif ($status === 'error_not_writable') {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>Theme JSON Export ERROR:</strong> /shared directory is not writable. Check permissions.';
            echo '</p></div>';
        } elseif ($status === 'error_copy_failed') {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>Theme JSON Export ERROR:</strong> Failed to copy theme.json.';
            echo '</p></div>';
        }
    }
    
    public function add_toolbar_export_button($admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $admin_bar->add_menu([
            'id'    => 'export-theme-json',
            'title' => '🔄 Export theme.json',
            'href'  => admin_url('admin-post.php?action=export_theme_json'),
            'meta'  => [
                'title' => 'Manually export theme.json to /shared/',
            ],
        ]);
    }
    
    public function handle_manual_export() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $this->export_theme_json();
        
        wp_redirect(admin_url('?theme_json_exported=1'));
        exit;
    }
}

// Initialize the plugin
new ThemeJSONAutoExport();

// Show confirmation message after manual export
add_action('admin_notices', function() {
    if (isset($_GET['theme_json_exported'])) {
        echo '<div class="notice notice-success is-dismissible"><p>';
        echo '<strong>Theme JSON manually exported!</strong> Check /shared/theme.json';
        echo '</p></div>';
    }
});