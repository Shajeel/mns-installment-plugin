<?php
/**
 * Customer notification email template for rejected application
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$application = $args['application'];
$site_name = get_bloginfo('name');
$site_url = get_site_url();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Application Status Update', 'ms-installment'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .section { margin-bottom: 20px; }
        .field { margin-bottom: 10px; }
        .label { font-weight: bold; color: #555; }
        .value { margin-left: 10px; }
        .highlight { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; }
        .info { background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php _e('Application Status Update', 'ms-installment'); ?></h1>
            <p><?php printf(__('Dear %s, regarding your installment application', 'ms-installment'), esc_html($application->customer_name)); ?></p>
        </div>
        
        <div class="content">
            <div class="highlight">
                <h3><?php _e('Application Review Complete', 'ms-installment'); ?></h3>
                <p><?php _e('We have completed the review of your installment application. Unfortunately, we are unable to approve your application at this time.', 'ms-installment'); ?></p>
            </div>
            
            <div class="section">
                <h3><?php _e('Application Details', 'ms-installment'); ?></h3>
                <div class="field">
                    <span class="label"><?php _e('Application ID:', 'ms-installment'); ?></span>
                    <span class="value">#<?php echo esc_html($application->id); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Product Name:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo esc_html($application->product_name); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Review Date:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($application->updated_at)); ?></span>
                </div>
            </div>
            
            <div class="info">
                <h3><?php _e('Why was my application not approved?', 'ms-installment'); ?></h3>
                <p><?php _e('Applications may not be approved for various reasons including:', 'ms-installment'); ?></p>
                <ul>
                    <li><?php _e('Incomplete or inaccurate information provided', 'ms-installment'); ?></li>
                    <li><?php _e('Failure to meet eligibility criteria', 'ms-installment'); ?></li>
                    <li><?php _e('Unable to verify provided information', 'ms-installment'); ?></li>
                    <li><?php _e('Current business policies and risk assessment', 'ms-installment'); ?></li>
                </ul>
                <p><?php _e('As per our terms and conditions, M&S Electronics reserves the right to reject applications without providing specific reasons.', 'ms-installment'); ?></p>
            </div>
            
            <div class="section">
                <h3><?php _e('Alternative Options', 'ms-installment'); ?></h3>
                <p><?php _e('We understand this may be disappointing. Here are some alternatives you might consider:', 'ms-installment'); ?></p>
                <ul>
                    <li><?php _e('Purchase the product with full payment', 'ms-installment'); ?></li>
                    <li><?php _e('Apply again in the future with updated information', 'ms-installment'); ?></li>
                    <li><?php _e('Consider a different product that might better suit your needs', 'ms-installment'); ?></li>
                    <li><?php _e('Contact our sales team for other payment options', 'ms-installment'); ?></li>
                </ul>
            </div>
            
            <div class="section">
                <h3><?php _e('Contact Information', 'ms-installment'); ?></h3>
                <p><?php _e('If you have any questions about this decision or would like to discuss alternative options, please contact us:', 'ms-installment'); ?></p>
                <?php 
                $email_settings = MS_Installment_Email::get_email_settings();
                ?>
                <p><strong><?php _e('Phone:', 'ms-installment'); ?></strong> <?php echo !empty($email_settings['contact_phone']) ? esc_html($email_settings['contact_phone']) : __('Contact us for phone number', 'ms-installment'); ?></p>
                <p><strong><?php _e('Email:', 'ms-installment'); ?></strong> <?php echo esc_html($email_settings['contact_email']); ?></p>
            </div>
            
            <div class="section">
                <h3><?php _e('Thank You', 'ms-installment'); ?></h3>
                <p><?php _e('We appreciate your interest in our installment program and hope to serve you in the future.', 'ms-installment'); ?></p>
                <p><?php printf(__('Thank you for considering %s for your purchase needs.', 'ms-installment'), $site_name); ?></p>
            </div>
        </div>
        
        <div class="footer">
            <p><?php printf(__('This email was sent from %s', 'ms-installment'), $site_name); ?></p>
        </div>
    </div>
</body>
</html> 