<?php
/**
 * Admin notification email template for new application
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$application = $args['application'];
$site_name = get_bloginfo('name');
$admin_url = admin_url('admin.php?page=ms-installment-applications');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('New Installment Application', 'ms-installment'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .section { margin-bottom: 20px; }
        .field { margin-bottom: 10px; }
        .label { font-weight: bold; color: #555; }
        .value { margin-left: 10px; }
        .button { display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 3px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php _e('New Installment Application', 'ms-installment'); ?></h1>
            <p><?php printf(__('A new installment application has been submitted on %s', 'ms-installment'), $site_name); ?></p>
        </div>
        
        <div class="content">
            <div class="section">
                <h3><?php _e('Application Details', 'ms-installment'); ?></h3>
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
                <h3><?php _e('Product Information', 'ms-installment'); ?></h3>
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
                    <span class="value"><?php echo MS_Installment_Frontend::format_currency($application->total_amount); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Monthly Installment:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo MS_Installment_Frontend::format_currency($application->installment_amount); ?> (3 months)</span>
                </div>
            </div>
            
            <div class="section">
                <h3><?php _e('Customer Information', 'ms-installment'); ?></h3>
                <div class="field">
                    <span class="label"><?php _e('Name:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo esc_html($application->customer_name); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Email:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo esc_html($application->customer_email); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Phone:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo esc_html($application->customer_phone); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Address:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo esc_html($application->customer_address); ?></span>
                </div>
            </div>
            
            <div class="section">
                <h3><?php _e('Eligibility Information', 'ms-installment'); ?></h3>
                <div class="field">
                    <span class="label"><?php _e('Lahore Resident:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo $application->is_lahore_resident === 'yes' ? __('Yes', 'ms-installment') : __('No', 'ms-installment'); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Residence Type:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo $application->residence === 'own' ? __('Own', 'ms-installment') : __('Rented', 'ms-installment'); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Profession:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo $application->profession === 'job' ? __('Job', 'ms-installment') : __('Business', 'ms-installment'); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Bank Account:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo $application->has_bank_account === 'yes' ? __('Yes', 'ms-installment') : __('No', 'ms-installment'); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Can Provide Guarantor:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo $application->can_provide_guarantor === 'yes' ? __('Yes', 'ms-installment') : __('No', 'ms-installment'); ?></span>
                </div>
                <div class="field">
                    <span class="label"><?php _e('Has CNIC:', 'ms-installment'); ?></span>
                    <span class="value"><?php echo $application->has_cnic === 'yes' ? __('Yes', 'ms-installment') : __('No', 'ms-installment'); ?></span>
                </div>
            </div>
            
            <div class="section" style="text-align: center;">
                <a href="<?php echo esc_url($admin_url); ?>" class="button"><?php _e('View Application in Admin Panel', 'ms-installment'); ?></a>
            </div>
        </div>
        
        <div class="footer">
            <p><?php printf(__('This email was sent from %s', 'ms-installment'), $site_name); ?></p>
        </div>
    </div>
</body>
</html> 