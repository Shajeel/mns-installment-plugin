<?php
/**
 * Email functionality class for MS Installment Plugin
 */
class MS_Installment_Email {
    
    /**
     * Send email to admin when new application is submitted
     */
    public static function send_admin_new_application_email($application) {
        if (!self::is_email_enabled('admin_new_application')) {
            return false;
        }
        
        $admin_emails = self::get_admin_emails();
        if (empty($admin_emails)) {
            return false;
        }
        
        $subject = sprintf(__('[%s] New Installment Application #%d', 'ms-installment'), html_entity_decode(get_bloginfo('name')), $application->id);
        $message = self::get_email_template('admin-new-application', array('application' => $application));
        
        return self::send_email($admin_emails, $subject, $message);
    }
    
    /**
     * Send email to customer when application is submitted
     */
    public static function send_customer_application_submitted_email($application) {
        if (!self::is_email_enabled('customer_application_submitted')) {
            return false;
        }
        
        $subject = sprintf(__('[%s] Installment Application Submitted Successfully #%d', 'ms-installment'), html_entity_decode(get_bloginfo('name')), $application->id);
        $message = self::get_email_template('customer-application-submitted', array('application' => $application));
        
        return self::send_email(array($application->customer_email), $subject, $message);
    }
    
    /**
     * Send email to customer when application is approved
     */
    public static function send_customer_application_approved_email($application) {
        if (!self::is_email_enabled('customer_application_approved')) {
            return false;
        }
        
        $subject = sprintf(__('[%s] Installment Application Approved! #%d', 'ms-installment'), html_entity_decode(get_bloginfo('name')), $application->id);
        $message = self::get_email_template('customer-application-approved', array('application' => $application));
        
        return self::send_email(array($application->customer_email), $subject, $message);
    }
    
    /**
     * Send email to customer when application is rejected
     */
    public static function send_customer_application_rejected_email($application) {
        if (!self::is_email_enabled('customer_application_rejected')) {
            return false;
        }
        
        $subject = sprintf(__('[%s] Installment Application Status Update #%d', 'ms-installment'), html_entity_decode(get_bloginfo('name')), $application->id);
        $message = self::get_email_template('customer-application-rejected', array('application' => $application));
        
        return self::send_email(array($application->customer_email), $subject, $message);
    }
    
    /**
     * Check if email is enabled for specific type
     */
    private static function is_email_enabled($email_type) {
        $email_settings = get_option('ms_installment_email_settings', array());
        return isset($email_settings[$email_type]) && $email_settings[$email_type] === 'yes';
    }
    
    /**
     * Get admin emails
     */
    private static function get_admin_emails() {
        $admin_emails = array();
        
        // Get admin email from settings
        $email_settings = self::get_email_settings();
        $custom_admin_email = $email_settings['admin_email'];
        if (!empty($custom_admin_email)) {
            $admin_emails[] = $custom_admin_email;
        }
        
        // Add default admin email if different
        $default_admin_email = get_option('admin_email');
        if (!in_array($default_admin_email, $admin_emails)) {
            $admin_emails[] = $default_admin_email;
        }
        
        return array_filter($admin_emails);
    }
    
    /**
     * Get email template
     */
    private static function get_email_template($template_name, $args = array()) {
        $template_path = MS_INSTALLMENT_PLUGIN_PATH . 'templates/emails/' . $template_name . '.php';
        
        if (!file_exists($template_path)) {
            return '';
        }
        
        ob_start();
        include $template_path;
        return ob_get_clean();
    }
    
    /**
     * Send email
     */
    private static function send_email($to_emails, $subject, $message) {
        if (empty($to_emails) || empty($subject) || empty($message)) {
            return false;
        }
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . html_entity_decode(get_bloginfo('name')) . ' <' . get_option('admin_email') . '>'
        );
        
        $to = implode(', ', $to_emails);
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Get email settings with defaults
     */
    public static function get_email_settings() {
        $defaults = array(
            'admin_new_application' => 'yes',
            'customer_application_submitted' => 'yes',
            'customer_application_approved' => 'yes',
            'customer_application_rejected' => 'yes',
            'admin_email' => get_option('admin_email'),
            'contact_email' => get_option('admin_email'),
            'contact_phone' => ''
        );
        
        $settings = get_option('ms_installment_email_settings', array());
        return wp_parse_args($settings, $defaults);
    }
    
    /**
     * Save email settings
     */
    public static function save_email_settings($settings) {
        return update_option('ms_installment_email_settings', $settings);
    }
    
    /**
     * Test email functionality
     */
    public static function test_email($to_email) {
        $subject = sprintf(__('[%s] Email Test - MS Installment Plugin', 'ms-installment'), html_entity_decode(get_bloginfo('name')));
        $message = sprintf(
            '<p>%s</p><p>%s</p>',
            __('This is a test email from the MS Installment Plugin.', 'ms-installment'),
            __('If you received this email, the email functionality is working correctly.', 'ms-installment')
        );
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . html_entity_decode(get_bloginfo('name')) . ' <' . get_option('admin_email') . '>'
        );
        
        return wp_mail($to_email, $subject, $message, $headers);
    }
} 