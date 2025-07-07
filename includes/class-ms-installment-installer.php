<?php
/**
 * Plugin installer class
 */
class MS_Installment_Installer {
    
    /**
     * Install plugin
     */
    public function install() {
        $this->create_tables();
        $this->create_pages();
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_name = $wpdb->prefix . 'ms_installment_applications';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_name varchar(255) NOT NULL,
            product_price decimal(10,2) NOT NULL,
            processing_fee_percentage decimal(5,2) NOT NULL,
            processing_fee decimal(10,2) NOT NULL,
            total_amount decimal(10,2) NOT NULL,
            installment_amount decimal(10,2) NOT NULL,
            is_lahore_resident varchar(10) NOT NULL,
            residence varchar(255) NOT NULL,
            profession varchar(255) NOT NULL,
            has_bank_account varchar(10) NOT NULL,
            can_provide_guarantor varchar(10) NOT NULL,
            has_cnic varchar(255) NOT NULL,
            customer_name varchar(255) NOT NULL,
            customer_email varchar(255) NOT NULL,
            customer_phone varchar(255) NOT NULL,
            customer_address text NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            admin_notes text,
            created_at datetime NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create default pages
     */
    private function create_pages() {
        // Create installment calculator page if it doesn't exist
        $calculator_page_id = get_option('ms_installment_calculator_page');
        
        if (!$calculator_page_id || !get_post($calculator_page_id)) {
            $page_data = array(
                'post_title' => __('M&S Installment Calculator', 'ms-installment'),
                'post_content' => '[ms_installment_calculator]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'installment-calculator'
            );
            
            $page_id = wp_insert_post($page_data);
            
            if ($page_id && !is_wp_error($page_id)) {
                update_option('ms_installment_calculator_page', $page_id);
            }
        }
    }
    
    /**
     * Uninstall plugin
     */
    public function uninstall() {
        global $wpdb;
        
        // Drop tables
        $table_name = $wpdb->prefix . 'ms_installment_applications';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        // Delete options
        delete_option('ms_installment_processing_fee');
        delete_option('ms_installment_show_all_products');
        delete_option('ms_installment_calculator_page');
        delete_option('ms_installment_email_settings');
        delete_option('ms_installment_enabled_roles');
        delete_option('ms_installment_next_id');
        
        // Delete product meta
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_ms_installment_enabled'");
        
        // Remove capabilities from roles
        if (class_exists('MS_Installment_Permissions')) {
            MS_Installment_Permissions::remove_capabilities();
        }
    }
} 