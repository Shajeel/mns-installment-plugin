<?php
/**
 * Customer confirmation email template for application submission
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
    <title><?php _e('Application Submitted Successfully', 'ms-installment'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .section { margin-bottom: 20px; }
        .field { margin-bottom: 10px; }
        .label { font-weight: bold; color: #555; }
        .value { margin-left: 10px; }
        .highlight { background: #e8f5e8; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php _e('Application Submitted Successfully!', 'ms-installment'); ?></h1>
            <p><?php printf(__('Dear %s, your installment application has been received.', 'ms-installment'), esc_html($application->customer_name)); ?></p>
        </div>
        
        <div class="content">
            <div class="highlight">
                <h3><?php _e('What happens next?', 'ms-installment'); ?></h3>
                <p><?php _e('Our team will review your application within 24-48 hours. If all 8 points are verified and approved, we will deliver your product within 72 hours minimum after the verification process.', 'ms-installment'); ?></p>
            </div>
            
            <div class="section">
                <h3><?php _e('Application Summary', 'ms-installment'); ?></h3>
                <div class="field">
                    <span class="label"><?php _e('Application ID:', 'ms-installment'); ?></span>
                    <span class="value">#<?php echo esc_html($application->id); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Submission Date:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($application->created_at)); ?></span>
                </div>
            </div>
            
            <div class="section">
                <h3><?php _e('Product Details', 'ms-installment'); ?></h3>
                <div class="field">
                    <span class="label"><?php _e('Product Name:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo esc_html($application->product_name); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Product Price:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo MS_Installment_Frontend::format_currency($application->product_price); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Processing Fee:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo MS_Installment_Frontend::format_currency($application->processing_fee); ?> (<?php echo esc_html($application->processing_fee_percentage ?? 5); ?>%)</span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Total Amount:', 'ms-installment'); ?></span>
                    <span class="value"><strong><?php echo MS_Installment_Frontend::format_currency($application->total_amount); ?></strong></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Monthly Installment (3 months):', 'ms-installment'); ?></span>
                    <span class="value"><strong><?php echo MS_Installment_Frontend::format_currency($application->installment_amount); ?></strong></span>
                </div>
            </div>
            
            <div class="section">
                <h3><?php _e('Important Information', 'ms-installment'); ?></h3>
                <ul>
                    <li><?php _e('Please keep this email for your records', 'ms-installment'); ?></li>
                    <li><?php _e('You will receive another email once your application is reviewed', 'ms-installment'); ?></li>
                    <li><?php _e('For any questions, please contact our customer support', 'ms-installment'); ?></li>
                </ul>
            </div>
            
            <div class="section">
                <h3><?php _e('Terms & Conditions Reminder', 'ms-installment'); ?></h3>
                <ul>
                    <li><?php _e('M&S Electronics reserves the right to reject the request without reason', 'ms-installment'); ?></li>
                    <li><?php _e('No price discounts are possible after agreeing on one price', 'ms-installment'); ?></li>
                    <li><?php _e('In case of non-payment, the company has the right to take legal action', 'ms-installment'); ?></li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p><?php printf(__('Thank you for choosing %s for your installment needs.', 'ms-installment'), $site_name); ?></p>
            <p><?php printf(__('This email was sent from %s', 'ms-installment'), $site_name); ?></p>
        </div>
    </div>
</body>
</html> 