<?php
/**
 * Frontend functionality class
 */
class MS_Installment_Frontend {
    
    public function __construct() {
        // This class can be extended with additional frontend functionality
    }
    
    /**
     * Format currency
     */
    public static function format_currency($amount) {
        return wc_price($amount);
    }
    
    /**
     * Calculate installment details
     */
    public static function calculate_installment_details($product_price) {
        $processing_fee_percentage = get_option('ms_installment_processing_fee', 5); // Default 5%
        $processing_fee = ($product_price * $processing_fee_percentage) / 100;
        $total_amount = $product_price + $processing_fee;
        $installment_amount = $total_amount / 3; // 3 months
        
        return array(
            'product_price' => $product_price,
            'processing_fee_percentage' => $processing_fee_percentage,
            'processing_fee' => $processing_fee,
            'total_amount' => $total_amount,
            'installment_amount' => $installment_amount
        );
    }
    
    /**
     * Validate application data
     */
    public static function validate_application_data($data) {
        $errors = array();
        
        // Required fields
        $required_fields = array(
            'product_name' => __('Product name', 'ms-installment'),
            'product_price' => __('Product price', 'ms-installment'),
            'is_lahore_resident' => __('Lahore residence', 'ms-installment'),
            'residence' => __('Residence type', 'ms-installment'),
            'profession' => __('Profession', 'ms-installment'),
            'has_bank_account' => __('Bank account', 'ms-installment'),
            'can_provide_guarantor' => __('Guarantor', 'ms-installment'),
            'has_cnic' => __('CNIC', 'ms-installment'),
            'customer_name' => __('Customer name', 'ms-installment'),
            'customer_email' => __('Customer email', 'ms-installment'),
            'customer_phone' => __('Customer phone', 'ms-installment'),
            'customer_address' => __('Customer address', 'ms-installment')
        );
        
        foreach ($required_fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = sprintf(__('%s is required.', 'ms-installment'), $label);
            }
        }
        
        // Validate email
        if (!empty($data['customer_email']) && !is_email($data['customer_email'])) {
            $errors[] = __('Please enter a valid email address.', 'ms-installment');
        }
        
        // Validate price
        if (!empty($data['product_price']) && !is_numeric($data['product_price'])) {
            $errors[] = __('Please enter a valid product price.', 'ms-installment');
        }
        
        // Validate terms agreement
        if (empty($data['terms_agreed']) || $data['terms_agreed'] !== 'yes') {
            $errors[] = __('You must agree to the terms and conditions.', 'ms-installment');
        }
        
        return $errors;
    }
    
    /**
     * Get status label
     */
    public static function get_status_label($status) {
        $labels = array(
            'pending' => __('Pending', 'ms-installment'),
            'approved' => __('Approved', 'ms-installment'),
            'rejected' => __('Rejected', 'ms-installment')
        );
        
        return isset($labels[$status]) ? $labels[$status] : $status;
    }
    
    /**
     * Get status class
     */
    public static function get_status_class($status) {
        $classes = array(
            'pending' => 'status-pending',
            'approved' => 'status-approved',
            'rejected' => 'status-rejected'
        );
        
        return isset($classes[$status]) ? $classes[$status] : '';
    }
} 