<?php
/**
 * Uninstall file for M&S Installment Plugin
 * 
 * This file is executed when the plugin is deleted from WordPress admin.
 * It removes all plugin data including database tables and options.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include the installer class to access uninstall method
require_once plugin_dir_path(__FILE__) . 'includes/class-ms-installment-installer.php';

// Run uninstall
$installer = new MS_Installment_Installer();
$installer->uninstall();

// Additional cleanup
global $wpdb;

// Remove any remaining options (in case they weren't removed by the installer)
delete_option('ms_installment_processing_fee');
delete_option('ms_installment_show_all_products');
delete_option('ms_installment_calculator_page');

// Remove any remaining product meta
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_ms_installment_enabled'");

// Clear any cached data
wp_cache_flush(); 