<?php
/**
 * Customer notification email template for approved application
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
    <title><?php _e('Application Approved!', 'ms-installment'); ?></title>
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
        .important { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php _e('🎉 Congratulations! Your Application is Approved!', 'ms-installment'); ?></h1>
            <p><?php printf(__('Dear %s, your installment application has been approved!', 'ms-installment'), esc_html($application->customer_name)); ?></p>
        </div>
        
        <div class="content">
            <div class="highlight">
                <h3><?php _e('Your application has been successfully approved!', 'ms-installment'); ?></h3>
                <p><?php _e('All verification points have been confirmed and your installment plan is now active.', 'ms-installment'); ?></p>
            </div>
            
            <div class="section">
                <h3><?php _e('Application Details', 'ms-installment'); ?></h3>
                <div class="field">
                    <span class="label"><?php _e('Application ID:', 'ms-installment'); ?></span>
                    <span class="value">#<?php echo esc_html($application->id); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Approval Date:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($application->updated_at)); ?></span>
                </div>
            </div>
            
            <div class="section">
                <h3><?php _e('Product & Payment Details', 'ms-installment'); ?></h3>
                <div class="field">
                    <span class="label"><?php _e('Product Name:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo esc_html($application->product_name); ?></span>
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
            
            <div class="important">
                <h3><?php _e('🚚 Delivery Information', 'ms-installment'); ?></h3>
                <p><strong><?php _e('Your product will be delivered within 72 hours from the approval date.', 'ms-installment'); ?></strong></p>
                <p><?php _e('Our delivery team will contact you at your provided phone number to arrange the delivery.', 'ms-installment'); ?></p>
            </div>
            
            <div class="section">
                <h3><?php _e('Payment Schedule', 'ms-installment'); ?></h3>
                <p><?php _e('Please ensure you have the first installment amount ready for payment upon delivery:', 'ms-installment'); ?></p>
                <ul>
                    <li><strong><?php _e('1st Installment:', 'ms-installment'); ?></strong> <?php echo MS_Installment_Frontend::format_currency($application->installment_amount); ?> (<?php _e('Due upon delivery', 'ms-installment'); ?>)</li>
                    <li><strong><?php _e('2nd Installment:', 'ms-installment'); ?></strong> <?php echo MS_Installment_Frontend::format_currency($application->installment_amount); ?> (<?php _e('Due after 30 days', 'ms-installment'); ?>)</li>
                    <li><strong><?php _e('3rd Installment:', 'ms-installment'); ?></strong> <?php echo MS_Installment_Frontend::format_currency($application->installment_amount); ?> (<?php _e('Due after 60 days', 'ms-installment'); ?>)</li>
                </ul>
            </div>
            
            <div class="section">
                <h3><?php _e('Important Reminders', 'ms-installment'); ?></h3>
                <ul>
                    <li><?php _e('Please have your CNIC ready for verification during delivery', 'ms-installment'); ?></li>
                    <li><?php _e('Ensure someone is available at the delivery address during business hours', 'ms-installment'); ?></li>
                    <li><?php _e('Keep this email for your records and payment schedule', 'ms-installment'); ?></li>
                    <li><?php _e('For any questions about delivery or payments, contact our customer support', 'ms-installment'); ?></li>
                </ul>
            </div>
            
            <div class="section">
                <h3><?php _e('Contact Information', 'ms-installment'); ?></h3>
                <p><?php _e('If you have any questions or need to update your delivery information, please contact us:', 'ms-installment'); ?></p>
                <?php 
                $email_settings = MS_Installment_Email::get_email_settings();
                ?>
                <p><strong><?php _e('Phone:', 'ms-installment'); ?></strong> <?php echo !empty($email_settings['contact_phone']) ? esc_html($email_settings['contact_phone']) : __('Contact us for phone number', 'ms-installment'); ?></p>
                <p><strong><?php _e('Email:', 'ms-installment'); ?></strong> <?php echo esc_html($email_settings['contact_email']); ?></p>
            </div>
        </div>
        
        <div class="footer">
            <p><?php printf(__('Thank you for choosing %s for your installment needs.', 'ms-installment'), $site_name); ?></p>
            <p><?php _e('We look forward to serving you!', 'ms-installment'); ?></p>
        </div>
    </div>
</body>
</html> 