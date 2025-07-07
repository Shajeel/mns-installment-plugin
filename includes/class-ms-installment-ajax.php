<?php
/**
 * AJAX functionality class
 */
class MS_Installment_Ajax {
    
    public function __construct() {
        // AJAX handlers are already defined in the main plugin class
        // This class can be extended with additional AJAX functionality
    }
    
    /**
     * Calculate installment AJAX handler
     */
    public static function calculate_installment() {
        check_ajax_referer('ms_installment_frontend_nonce', 'nonce');
        
        $product_price = floatval($_POST['product_price']);
        
        if ($product_price <= 0) {
            wp_send_json_error(__('Please enter a valid product price.', 'ms-installment'));
        }
        
        $calculation = MS_Installment_Frontend::calculate_installment_details($product_price);
        
        wp_send_json_success($calculation);
    }
} 