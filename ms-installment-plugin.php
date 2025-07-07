<?php
/**
 * Plugin Name: M&S Installment Plugin
 * Plugin URI: https://wa.me/923474323399
 * Description: A WooCommerce plugin that allows customers to buy products on installments with application management.
 * Version: 1.0.12
 * Author: Shajeel ur Rehman +923474323399
 * Text Domain: mns-installment
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MS_INSTALLMENT_PLUGIN_VERSION', '1.0.12');
define('MS_INSTALLMENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MS_INSTALLMENT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MS_INSTALLMENT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('MS_INSTALLMENT_PLUGIN_FILE', __FILE__);

// Check if WooCommerce is active
function ms_installment_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . 
                 __('M&S Installment Plugin requires WooCommerce to be installed and activated.', 'ms-installment') . 
                 '</p></div>';
        });
        return false;
    }
    return true;
}

// Initialize the plugin
function ms_installment_init() {
    if (!ms_installment_check_woocommerce()) {
        return;
    }
    
    // Load text domain
    load_plugin_textdomain('ms-installment', false, dirname(MS_INSTALLMENT_PLUGIN_BASENAME) . '/languages');
    
    // Include required files with error handling
    $required_files = array(
        'includes/class-ms-installment-plugin.php',
        'includes/class-ms-installment-admin.php',
        'includes/class-ms-installment-frontend.php',
        'includes/class-ms-installment-ajax.php',
        'includes/class-ms-installment-email.php',
        'includes/class-ms-installment-permissions.php'
    );
    
    $missing_files = array();
    foreach ($required_files as $file) {
        $file_path = MS_INSTALLMENT_PLUGIN_PATH . $file;
        if (!file_exists($file_path)) {
            $missing_files[] = $file;
        }
    }
    
    if (!empty($missing_files)) {
        add_action('admin_notices', function() use ($missing_files) {
            echo '<div class="notice notice-error"><p>' . 
                 sprintf(__('MS Installment Plugin: Required files are missing: %s. Please reinstall the plugin.', 'ms-installment'), implode(', ', $missing_files)) . 
                 '</p></div>';
        });
        return;
    }
    
    // Load all required files
    foreach ($required_files as $file) {
        $file_path = MS_INSTALLMENT_PLUGIN_PATH . $file;
        require_once $file_path;
    }
    
    try {
        // Initialize the main plugin class and make it globally available
        global $ms_installment_plugin;
        $ms_installment_plugin = new MS_Installment_Plugin();
    } catch (Exception $e) {
        add_action('admin_notices', function() use ($e) {
            echo '<div class="notice notice-error"><p>' . 
                 sprintf(__('MS Installment Plugin: Error initializing plugin: %s', 'ms-installment'), $e->getMessage()) . 
                 '</p></div>';
        });
        
        // Log the error for debugging
        error_log('MS Installment Plugin Error: ' . $e->getMessage());
    }
}
add_action('plugins_loaded', 'ms_installment_init');

// Activation hook
register_activation_hook(__FILE__, 'ms_installment_activate');
function ms_installment_activate() {
    if (!ms_installment_check_woocommerce()) {
        deactivate_plugins(MS_INSTALLMENT_PLUGIN_BASENAME);
        wp_die(__('M&S Installment Plugin requires WooCommerce to be installed and activated.', 'ms-installment'));
    }
    
    try {
        // Include installer class
        $installer_file = MS_INSTALLMENT_PLUGIN_PATH . 'includes/class-ms-installment-installer.php';
        if (!file_exists($installer_file)) {
            deactivate_plugins(MS_INSTALLMENT_PLUGIN_BASENAME);
            wp_die(__('MS Installment Plugin: Installer file is missing. Please reinstall the plugin.', 'ms-installment'));
        }
        
        require_once $installer_file;
        
        // Create database tables
        $installer = new MS_Installment_Installer();
        $installer->install();
        
        // Set default options
        add_option('ms_installment_processing_fee', 5);
        add_option('ms_installment_show_all_products', 'selected');
        add_option('ms_installment_calculator_page', '');
        add_option('ms_installment_next_id', 100000);
        
        // Set default email settings
        $default_email_settings = array(
            'admin_new_application' => 'yes',
            'customer_application_submitted' => 'yes',
            'customer_application_approved' => 'yes',
            'customer_application_rejected' => 'yes',
            'admin_email' => get_option('admin_email'),
            'contact_email' => get_option('admin_email'),
            'contact_phone' => ''
        );
        add_option('ms_installment_email_settings', $default_email_settings);
        
        // Set default enabled roles
        add_option('ms_installment_enabled_roles', array('administrator'));
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
    } catch (Exception $e) {
        deactivate_plugins(MS_INSTALLMENT_PLUGIN_BASENAME);
        wp_die(sprintf(__('MS Installment Plugin: Error during activation: %s', 'ms-installment'), $e->getMessage()));
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'ms_installment_deactivate');
function ms_installment_deactivate() {
    flush_rewrite_rules();
} 